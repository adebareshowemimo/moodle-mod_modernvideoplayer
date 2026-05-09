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
 * Teacher progress report.
 *
 * @package    mod_modernvideoplayer
 * @copyright  2025 Adebare Showemmo | adebareshowemimo@gmail.com | support@agunfoninteractivity.com | www.agunfoninteractivity.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(__DIR__ . '/locallib.php');

use mod_modernvideoplayer\local\learners_table;
use mod_modernvideoplayer\local\reporting;

$id = required_param('id', PARAM_INT);
$completionfilter = optional_param('completionfilter', 'all', PARAM_ALPHA);
$suspiciousonly = optional_param('suspiciousonly', 0, PARAM_BOOL);
$search = trim(optional_param('search', '', PARAM_TEXT));
$pagesize = max(5, min(500, optional_param('pagesize', 25, PARAM_INT)));
$download = optional_param('download', '', PARAM_ALPHA);

[$course, $cm, $instance] = modernvideoplayer_get_course_module_and_instance($id, 0);
require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/modernvideoplayer:viewreports', $context);

$pageparams = [
    'id' => $cm->id,
    'completionfilter' => $completionfilter,
    'suspiciousonly' => $suspiciousonly,
    'search' => $search,
    'pagesize' => $pagesize,
];

$baseurl = new moodle_url('/mod/modernvideoplayer/report.php', $pageparams);

// Download negotiation must run before any page output.
$table = new learners_table('mod-modernvideoplayer-learners', $instance, $completionfilter, (bool) $suspiciousonly, $search);
$table->define_baseurl($baseurl);
$table->is_downloadable(true);
$table->show_download_buttons_at([TABLE_P_BOTTOM]);
$filename = clean_filename(get_string('downloadreportfilename', 'modernvideoplayer') . '-' . $cm->id);
$table->is_downloading($download, $filename, format_string($instance->name));

if ($table->is_downloading()) {
    // Download path: stream rows + exit. No header/footer.
    $table->out($pagesize, false);
    exit;
}

$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_title(format_string($instance->name) . ': ' . get_string('report', 'modernvideoplayer'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_pagelayout('report');

$service = new reporting();
$summary = $service->get_summary($instance, $completionfilter, (bool) $suspiciousonly, $search);

$renderer = $PAGE->get_renderer('mod_modernvideoplayer');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reportheader', 'modernvideoplayer'));
echo $renderer->render_report([
    'id' => $cm->id,
    'totallearners' => $summary['totallearners'],
    'completedlearners' => $summary['completedlearners'],
    'completionrate' => format_float($summary['completionrate'], 2) . '%',
    'averagecoverage' => format_float($summary['averagecoverage'], 2) . '%',
    'suspiciousflags' => $summary['suspiciousflags'],
    'integrityfailures' => $summary['integrityfailures'],
    'isall' => $completionfilter === 'all',
    'iscompleted' => $completionfilter === 'completed',
    'isincomplete' => $completionfilter === 'incomplete',
    'suspiciousonly' => !empty($suspiciousonly),
    'search' => s($search),
    'pagesize' => $pagesize,
    'pagesizes' => array_map(
        fn($n) => ['value' => $n, 'selected' => $n === $pagesize],
        [10, 25, 50, 100, 250]
    ),
    'filterurl' => (new moodle_url('/mod/modernvideoplayer/report.php'))->out(false),
    'clearurl' => (new moodle_url('/mod/modernvideoplayer/report.php', ['id' => $cm->id]))->out(false),
    'hasactivefilters' => $completionfilter !== 'all' || $suspiciousonly || $search !== '' || $pagesize !== 25,
]);

// Table_sql echoes its own pagination, sortable headers, and download buttons.
$table->out($pagesize, true);

echo $OUTPUT->footer();
