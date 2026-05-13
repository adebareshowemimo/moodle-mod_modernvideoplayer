<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External web service tests for mod_modernvideoplayer.
 *
 * @package    mod_modernvideoplayer
 * @category   test
 * @copyright  2026 Adebare Showemimo <adebareshowemimo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_modernvideoplayer\external;

use context_module;
use core_external\external_api;
use externallib_advanced_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Tests for the plugin's external web service endpoints.
 *
 * @covers \mod_modernvideoplayer\external\get_progress
 * @covers \mod_modernvideoplayer\external\heartbeat
 * @covers \mod_modernvideoplayer\external\mark_complete
 * @covers \mod_modernvideoplayer\external\get_next_activity
 * @covers \mod_modernvideoplayer\external\reset_progress
 */
final class external_test extends externallib_advanced_testcase {
    /** @var \stdClass */
    private $course;

    /** @var \stdClass */
    private $cm;

    /** @var \stdClass */
    private $instance;

    /** @var context_module */
    private $context;

    /** @var \stdClass */
    private $student;

    /**
     * Shared fixture: course + activity + enrolled student.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();

        $this->course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $this->instance = $this->getDataGenerator()->create_module('modernvideoplayer', [
            'course' => $this->course->id,
            'name' => 'External WS test video',
            'requiredpercent' => 100.0,
            'completion' => \COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => 0,
            'completionvideopercentenabled' => 1,
            'completionvideopercent' => 100.0,
            'graceseconds' => 3,
            'heartbeatinterval' => 15,
            'forceservervalidation' => 1,
        ]);
        $this->cm = get_coursemodule_from_instance('modernvideoplayer', $this->instance->id);
        $this->context = context_module::instance($this->cm->id);

        $this->student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, 'student');
    }

    /**
     * get_progress returns a fresh session token and zeroed state for a new learner.
     */
    public function test_get_progress_initial_state(): void {
        $this->setUser($this->student);

        $result = get_progress::execute($this->cm->id);
        $result = external_api::clean_returnvalue(get_progress::execute_returns(), $result);

        $this->assertEquals($this->cm->id, $result['cmid']);
        $this->assertNotEmpty($result['sessiontoken']);
        $this->assertSame(0.0, (float) $result['lastposition']);
        $this->assertSame(0.0, (float) $result['maxverifiedposition']);
        $this->assertFalse((bool) $result['completed']);
        $this->assertIsFloat((float) $result['requiredpercent']);
        $this->assertSame(15, (int) $result['heartbeatinterval']);
    }

    /**
     * get_progress blocks a user who is not enrolled in the course.
     */
    public function test_get_progress_requires_course_access(): void {
        $otheruser = $this->getDataGenerator()->create_user();
        $this->setUser($otheruser);

        // Unenrolled users hit require_course_login, which either throws a
        // moodle_exception (redirect blocked in CLI) or a require_login_exception.
        $this->expectException(\core\exception\moodle_exception::class);
        get_progress::execute($this->cm->id);
    }

    /**
     * Heartbeat returns a valid status and session token for an authenticated learner.
     */
    public function test_heartbeat_returns_status(): void {
        $this->setUser($this->student);

        $initial = get_progress::execute($this->cm->id);
        $initial = external_api::clean_returnvalue(get_progress::execute_returns(), $initial);
        $token = $initial['sessiontoken'];

        $beat = heartbeat::execute(
            $this->cm->id,
            10.0,
            120.0,
            true,
            1.0,
            'visible',
            $token
        );
        $beat = external_api::clean_returnvalue(heartbeat::execute_returns(), $beat);

        $this->assertArrayHasKey('status', $beat);
        $this->assertArrayHasKey('maxverifiedposition', $beat);
        $this->assertArrayHasKey('sessiontoken', $beat);
        $this->assertNotEmpty($beat['sessiontoken']);
        $this->assertGreaterThanOrEqual(0.0, (float) $beat['maxverifiedposition']);
    }

    /**
     * Heartbeat returns a well-formed response even when given a bogus session token.
     */
    public function test_heartbeat_accepts_any_token_shape(): void {
        $this->setUser($this->student);

        // Prime a session so the user has a valid progress row.
        $initial = get_progress::execute($this->cm->id);
        external_api::clean_returnvalue(get_progress::execute_returns(), $initial);

        $beat = heartbeat::execute(
            $this->cm->id,
            5.0,
            120.0,
            true,
            1.0,
            'visible',
            'definitely-not-a-real-token'
        );
        $beat = external_api::clean_returnvalue(heartbeat::execute_returns(), $beat);

        // Manager always returns a well-formed struct; we only assert the shape
        // since token-mismatch handling may re-issue a token rather than error.
        $this->assertArrayHasKey('status', $beat);
        $this->assertArrayHasKey('sessiontoken', $beat);
        $this->assertNotEmpty($beat['sessiontoken']);
    }

    /**
     * mark_complete delegates to heartbeat and returns a completion snapshot.
     */
    public function test_mark_complete_returns_snapshot(): void {
        $this->setUser($this->student);

        $initial = get_progress::execute($this->cm->id);
        $initial = external_api::clean_returnvalue(get_progress::execute_returns(), $initial);
        $token = $initial['sessiontoken'];

        $result = mark_complete::execute($this->cm->id, 120.0, 120.0, $token);
        $result = external_api::clean_returnvalue(mark_complete::execute_returns(), $result);

        $this->assertArrayHasKey('completed', $result);
        $this->assertArrayHasKey('percentcomplete', $result);
        $this->assertArrayHasKey('sessiontoken', $result);
        $this->assertNotEmpty($result['sessiontoken']);
    }

    /**
     * get_next_activity resolves the next visible course module for the learner.
     */
    public function test_get_next_activity_returns_next_visible_module(): void {
        $this->setUser($this->student);

        $page = $this->getDataGenerator()->create_module('page', [
            'course' => $this->course->id,
            'name' => 'Next lesson page',
        ]);

        rebuild_course_cache((int) $this->course->id, true);

        $result = get_next_activity::execute($this->cm->id);
        $result = external_api::clean_returnvalue(get_next_activity::execute_returns(), $result);

        $this->assertTrue((bool) $result['enabled']);
        $this->assertSame('Next lesson page', $result['name']);
        $this->assertStringContainsString('/mod/page/view.php', $result['url']);
        $this->assertStringContainsString('id=' . $page->cmid, $result['url']);
        $this->assertStringContainsString('forceview=1', $result['url']);
        $this->assertFalse((bool) $result['isfallback']);
    }

    /**
     * get_next_activity re-resolves availability-gated next modules after completion.
     */
    public function test_get_next_activity_unlocks_after_mark_complete(): void {
        global $DB;

        $this->setUser($this->student);

        $availability = json_encode([
            'op' => '&',
            'showc' => [true],
            'c' => [[
                'type' => 'completion',
                'cm' => (int) $this->cm->id,
                'e' => \COMPLETION_COMPLETE,
            ]],
        ]);
        $page = $this->getDataGenerator()->create_module('page', [
            'course' => $this->course->id,
            'name' => 'Locked next lesson',
            'availability' => $availability,
        ]);

        rebuild_course_cache((int) $this->course->id, true);
        get_fast_modinfo((int) $this->course->id, 0, true);

        $locked = get_next_activity::execute($this->cm->id);
        $locked = external_api::clean_returnvalue(get_next_activity::execute_returns(), $locked);
        $this->assertTrue((bool) $locked['isfallback']);

        $initial = get_progress::execute($this->cm->id);
        $initial = external_api::clean_returnvalue(get_progress::execute_returns(), $initial);

        $progress = $DB->get_record('modernvideoplayer_progress', [
            'modernvideoplayerid' => $this->instance->id,
            'userid' => $this->student->id,
        ], '*', MUST_EXIST);
        $progress->duration = 120.0;
        $progress->lastposition = 119.0;
        $progress->maxverifiedposition = 119.0;
        $progress->totalsecondswatched = 119.0;
        $progress->percentcomplete = 99.17;
        $progress->completed = 0;
        $progress->completiontime = null;
        $progress->lastheartbeat = null;
        $DB->update_record('modernvideoplayer_progress', $progress);
        $DB->insert_record('modernvideoplayer_segments', (object) [
            'progressid' => $progress->id,
            'segmentstart' => 0.0,
            'segmentend' => 119.0,
            'watchedseconds' => 119.0,
            'timecreated' => time(),
        ]);

        $complete = mark_complete::execute($this->cm->id, 120.0, 120.0, $initial['sessiontoken']);
        $complete = external_api::clean_returnvalue(mark_complete::execute_returns(), $complete);
        $this->assertTrue((bool) $complete['completed']);

        $result = get_next_activity::execute($this->cm->id);
        $result = external_api::clean_returnvalue(get_next_activity::execute_returns(), $result);

        $this->assertTrue((bool) $result['enabled']);
        $this->assertSame('Locked next lesson', $result['name']);
        $this->assertStringContainsString('/mod/page/view.php', $result['url']);
        $this->assertStringContainsString('id=' . $page->cmid, $result['url']);
        $this->assertStringContainsString('forceview=1', $result['url']);
        $this->assertFalse((bool) $result['isfallback']);
    }

    /**
     * mark_complete can finalise a near-end mobile completion and unlock the next module.
     */
    public function test_mark_complete_from_near_end_position_unlocks_next_activity(): void {
        global $DB;

        $this->setUser($this->student);

        $availability = json_encode([
            'op' => '&',
            'showc' => [true],
            'c' => [[
                'type' => 'completion',
                'cm' => (int) $this->cm->id,
                'e' => \COMPLETION_COMPLETE,
            ]],
        ]);
        $page = $this->getDataGenerator()->create_module('page', [
            'course' => $this->course->id,
            'name' => 'Near-end unlocked lesson',
            'availability' => $availability,
        ]);

        rebuild_course_cache((int) $this->course->id, true);
        get_fast_modinfo((int) $this->course->id, 0, true);

        $initial = get_progress::execute($this->cm->id);
        $initial = external_api::clean_returnvalue(get_progress::execute_returns(), $initial);

        $progress = $DB->get_record('modernvideoplayer_progress', [
            'modernvideoplayerid' => $this->instance->id,
            'userid' => $this->student->id,
        ], '*', MUST_EXIST);
        $progress->duration = 120.0;
        $progress->lastposition = 119.65;
        $progress->maxverifiedposition = 119.65;
        $progress->totalsecondswatched = 119.65;
        $progress->percentcomplete = 99.71;
        $progress->completed = 0;
        $progress->completiontime = null;
        $progress->lastheartbeat = null;
        $DB->update_record('modernvideoplayer_progress', $progress);
        $DB->insert_record('modernvideoplayer_segments', (object) [
            'progressid' => $progress->id,
            'segmentstart' => 0.0,
            'segmentend' => 119.65,
            'watchedseconds' => 119.65,
            'timecreated' => time(),
        ]);

        $complete = mark_complete::execute($this->cm->id, 120.0, 120.0, $initial['sessiontoken']);
        $complete = external_api::clean_returnvalue(mark_complete::execute_returns(), $complete);
        $this->assertTrue((bool) $complete['completed']);

        $result = get_next_activity::execute($this->cm->id);
        $result = external_api::clean_returnvalue(get_next_activity::execute_returns(), $result);

        $this->assertTrue((bool) $result['enabled']);
        $this->assertSame('Near-end unlocked lesson', $result['name']);
        $this->assertStringContainsString('/mod/page/view.php', $result['url']);
        $this->assertStringContainsString('id=' . $page->cmid, $result['url']);
        $this->assertStringContainsString('forceview=1', $result['url']);
        $this->assertFalse((bool) $result['isfallback']);
    }

    /**
     * get_next_activity self-heals stale Moodle completion on a fresh completed view.
     */
    public function test_get_next_activity_syncs_existing_completed_progress(): void {
        global $DB;

        $this->setUser($this->student);

        $availability = json_encode([
            'op' => '&',
            'showc' => [true],
            'c' => [[
                'type' => 'completion',
                'cm' => (int) $this->cm->id,
                'e' => \COMPLETION_COMPLETE,
            ]],
        ]);
        $page = $this->getDataGenerator()->create_module('page', [
            'course' => $this->course->id,
            'name' => 'Fresh view next lesson',
            'availability' => $availability,
        ]);

        $initial = get_progress::execute($this->cm->id);
        external_api::clean_returnvalue(get_progress::execute_returns(), $initial);

        $progress = $DB->get_record('modernvideoplayer_progress', [
            'modernvideoplayerid' => $this->instance->id,
            'userid' => $this->student->id,
        ], '*', MUST_EXIST);
        $progress->duration = 120.0;
        $progress->lastposition = 120.0;
        $progress->maxverifiedposition = 120.0;
        $progress->totalsecondswatched = 120.0;
        $progress->percentcomplete = 100.0;
        $progress->completed = 1;
        $progress->completiontime = time();
        $DB->update_record('modernvideoplayer_progress', $progress);
        $DB->delete_records('course_modules_completion', [
            'coursemoduleid' => $this->cm->id,
            'userid' => $this->student->id,
        ]);

        rebuild_course_cache((int) $this->course->id, true);
        get_fast_modinfo((int) $this->course->id, 0, true);

        $result = get_next_activity::execute($this->cm->id);
        $result = external_api::clean_returnvalue(get_next_activity::execute_returns(), $result);

        $this->assertTrue((bool) $result['enabled']);
        $this->assertSame('Fresh view next lesson', $result['name']);
        $this->assertStringContainsString('/mod/page/view.php', $result['url']);
        $this->assertStringContainsString('id=' . $page->cmid, $result['url']);
        $this->assertStringContainsString('forceview=1', $result['url']);
        $this->assertFalse((bool) $result['isfallback']);
    }

    /**
     * get_next_activity blocks users who are not enrolled.
     */
    public function test_get_next_activity_requires_course_access(): void {
        $guest = $this->getDataGenerator()->create_user();
        $this->setUser($guest);

        $this->expectException(\core\exception\moodle_exception::class);
        get_next_activity::execute($this->cm->id);
    }

    /**
     * reset_progress zeroes the learner state and issues a fresh session token.
     */
    public function test_reset_progress_zeroes_state(): void {
        global $DB;
        $this->setUser($this->student);

        // Prime a session + advance progress manually so reset has something to clear.
        $initial = get_progress::execute($this->cm->id);
        $initial = external_api::clean_returnvalue(get_progress::execute_returns(), $initial);
        $oldtoken = $initial['sessiontoken'];

        $DB->set_field(
            'modernvideoplayer_progress',
            'maxverifiedposition',
            45.0,
            ['modernvideoplayerid' => $this->instance->id, 'userid' => $this->student->id]
        );

        $result = reset_progress::execute($this->cm->id);
        $result = external_api::clean_returnvalue(reset_progress::execute_returns(), $result);

        $this->assertSame(0.0, (float) $result['lastposition']);
        $this->assertSame(0.0, (float) $result['maxverifiedposition']);
        $this->assertSame(0.0, (float) $result['percentcomplete']);
        $this->assertFalse((bool) $result['completed']);
        $this->assertNotEmpty($result['sessiontoken']);
        $this->assertNotSame($oldtoken, $result['sessiontoken']);
    }

    /**
     * reset_progress blocks users who are not enrolled.
     */
    public function test_reset_progress_requires_course_access(): void {
        $guest = $this->getDataGenerator()->create_user();
        $this->setUser($guest);

        $this->expectException(\core\exception\moodle_exception::class);
        reset_progress::execute($this->cm->id);
    }
}
