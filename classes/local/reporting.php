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
 * Reporting helpers for mod_modernvideoplayer.
 *
 * @package    mod_modernvideoplayer
 * @copyright  2026 Adebare Showemimo <adebareshowemimo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_modernvideoplayer\local;

use stdClass;

/**
 * Reporting query helper.
 * @package mod_modernvideoplayer
 */
class reporting {
    /**
     * Build the shared WHERE clause + named-params for the report.
     *
     * Used by both `learners_table` (paginated row query) and
     * `get_summary()` (aggregate KPIs) so the table and KPI cards always
     * reflect the same filtered cohort.
     *
     * @param stdClass $instance activity instance
     * @param string $completionfilter 'all'|'completed'|'incomplete'
     * @param bool $suspiciousonly suspicious-only checkbox state
     * @param string $search free-text learner search
     * @return array{0: string, 1: array} [where-fragment, named-params]
     */
    public static function build_filter(
        stdClass $instance,
        string $completionfilter = 'all',
        bool $suspiciousonly = false,
        string $search = ''
    ): array {
        global $DB;

        $conditions = ['p.modernvideoplayerid = :instanceid'];
        $params = ['instanceid' => (int) $instance->id];

        if ($completionfilter === 'completed') {
            $conditions[] = 'p.completed = :completed';
            $params['completed'] = 1;
        } else if ($completionfilter === 'incomplete') {
            $conditions[] = 'p.completed = :incomplete';
            $params['incomplete'] = 0;
        }

        if ($suspiciousonly) {
            $conditions[] = '(p.suspiciousflags > 0 OR p.integrityfailures > 0)';
        }

        if ($search !== '') {
            $like = '%' . $DB->sql_like_escape($search) . '%';
            $conditions[] = '(' . $DB->sql_like('u.firstname', ':search1', false) .
                ' OR ' . $DB->sql_like('u.lastname', ':search2', false) .
                ' OR ' . $DB->sql_like('u.email', ':search3', false) . ')';
            $params['search1'] = $like;
            $params['search2'] = $like;
            $params['search3'] = $like;
        }

        return [implode(' AND ', $conditions), $params];
    }

    /**
     * Compute KPI totals for the filtered cohort via a single aggregate query.
     *
     * Replaces the previous PHP-side fold over a fully-loaded row set so the
     * KPI cards stay correct when the table itself is paginated and only
     * holds one page of rows in PHP at a time.
     *
     * @param stdClass $instance activity instance
     * @param string $completionfilter 'all'|'completed'|'incomplete'
     * @param bool $suspiciousonly suspicious-only checkbox state
     * @param string $search free-text learner search
     * @return array {totallearners,completedlearners,completionrate,averagecoverage,suspiciousflags,integrityfailures}
     */
    public function get_summary(
        stdClass $instance,
        string $completionfilter = 'all',
        bool $suspiciousonly = false,
        string $search = ''
    ): array {
        global $DB;

        [$where, $params] = self::build_filter(
            $instance,
            $completionfilter,
            $suspiciousonly,
            $search
        );

        $sql = "SELECT
                    COUNT(1) AS totallearners,
                    COALESCE(SUM(CASE WHEN p.completed = 1 THEN 1 ELSE 0 END), 0) AS completedlearners,
                    COALESCE(AVG(p.percentcomplete), 0) AS averagecoverage,
                    COALESCE(SUM(p.suspiciousflags), 0) AS suspiciousflags,
                    COALESCE(SUM(p.integrityfailures), 0) AS integrityfailures
                  FROM {modernvideoplayer_progress} p
                  JOIN {user} u ON u.id = p.userid
                 WHERE $where";

        $row = $DB->get_record_sql($sql, $params);
        $count = (int) ($row->totallearners ?? 0);
        $completed = (int) ($row->completedlearners ?? 0);

        return [
            'totallearners' => $count,
            'completedlearners' => $completed,
            'completionrate' => $count ? round(($completed / $count) * 100, 2) : 0.0,
            'averagecoverage' => round((float) ($row->averagecoverage ?? 0), 2),
            'suspiciousflags' => (int) ($row->suspiciousflags ?? 0),
            'integrityfailures' => (int) ($row->integrityfailures ?? 0),
        ];
    }
}
