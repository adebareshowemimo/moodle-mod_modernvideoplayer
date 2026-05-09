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
 * Paginated, sortable, downloadable learner-progress table.
 *
 * @package    mod_modernvideoplayer
 * @copyright  2026 Adebare Showemimo <adebareshowemimo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_modernvideoplayer\local;

use stdClass;
use table_sql;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');

/**
 * Learner-progress table backed by Moodle's table_sql so we get pagination,
 * click-to-sort, and CSV/Excel/ODS download for free.
 */
class learners_table extends table_sql {

    /**
     * Build the table.
     *
     * @param string $uniqueid table unique id
     * @param stdClass $instance modernvideoplayer activity row
     * @param string $completionfilter 'all'|'completed'|'incomplete'
     * @param bool $suspiciousonly only show learners with suspicious / integrity issues
     * @param string $search free-text learner search
     */
    public function __construct(
        string $uniqueid,
        stdClass $instance,
        string $completionfilter = 'all',
        bool $suspiciousonly = false,
        string $search = ''
    ) {
        parent::__construct($uniqueid);

        $columns = [
            'fullname' => get_string('fullname', 'modernvideoplayer'),
            'email' => get_string('email', 'modernvideoplayer'),
            'duration' => get_string('duration', 'modernvideoplayer'),
            'lastposition' => get_string('lastposition', 'modernvideoplayer'),
            'maxverifiedposition' => get_string('maxverifiedposition', 'modernvideoplayer'),
            'totalsecondswatched' => get_string('totalsecondswatched', 'modernvideoplayer'),
            'percentcomplete' => get_string('percentcomplete', 'modernvideoplayer'),
            'completed' => get_string('completed', 'modernvideoplayer'),
            'completiontime' => get_string('completiontime', 'modernvideoplayer'),
            'lastheartbeat' => get_string('lastheartbeat', 'modernvideoplayer'),
            'lastplaybackrate' => get_string('lastplaybackrate', 'modernvideoplayer'),
            'lastvisibility' => get_string('lastvisibility', 'modernvideoplayer'),
            'suspiciousflags' => get_string('suspiciousflags', 'modernvideoplayer'),
            'integrityfailures' => get_string('integrityfailures', 'modernvideoplayer'),
        ];
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));

        // `fullname` is composed in PHP and has no SQL alias to ORDER BY,
        // so disable sorting on that header. Default sort uses lastname asc.
        $this->no_sorting('fullname');
        $this->collapsible(false);
        $this->sortable(true, 'lastname', SORT_ASC);
        $this->set_attribute('class', 'generaltable mod-modernvideoplayer-report-table');

        [$where, $params] = reporting::build_filter(
            $instance, $completionfilter, $suspiciousonly, $search);

        // Pull all `core_user` name fields plus email; aliased without prefix
        // so `lastname`, `firstname`, etc. land on the row directly — that
        // lets `fullname()` work and lets click-to-sort target real columns.
        $userfields = \core_user\fields::for_name()
            ->including('email')
            ->get_sql('u', false, '', '', false);

        // `get_sql(..., false)` returns selects without a leading comma, so
        // we have to put one in ourselves between `p.*` and the user fields.
        $fields = 'p.*, ' . $userfields->selects;
        $from = '{modernvideoplayer_progress} p JOIN {user} u ON u.id = p.userid';

        $this->set_sql($fields, $from, $where, $params);
        $this->set_count_sql(
            "SELECT COUNT(1) FROM $from WHERE $where", $params);
    }

    /**
     * Composed learner name. Signature matches `flexible_table::col_fullname()`
     * — the parent declares it without type hints, so a strict override would
     * fail LSP at class load.
     *
     * @param mixed $row stdClass row from table_sql
     * @return string
     */
    public function col_fullname($row) {
        return fullname($row);
    }

    /**
     * Email column — escape for HTML, raw for downloads.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_email(stdClass $row): string {
        return $this->is_downloading() ? (string) $row->email : s($row->email);
    }

    /**
     * Two-decimal video duration in seconds.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_duration(stdClass $row): string {
        return format_float((float) $row->duration, 2);
    }

    /**
     * Two-decimal current playback position in seconds.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_lastposition(stdClass $row): string {
        return format_float((float) $row->lastposition, 2);
    }

    /**
     * Two-decimal furthest server-validated position.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_maxverifiedposition(stdClass $row): string {
        return format_float((float) $row->maxverifiedposition, 2);
    }

    /**
     * Two-decimal total validated watch seconds.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_totalsecondswatched(stdClass $row): string {
        return format_float((float) $row->totalsecondswatched, 2);
    }

    /**
     * Coverage percent. Suffix `%` is suppressed in download mode so CSV/Excel
     * cells stay numeric and sortable downstream.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_percentcomplete(stdClass $row): string {
        $val = format_float((float) $row->percentcomplete, 2);
        return $this->is_downloading() ? $val : $val . '%';
    }

    /**
     * Completed flag — Yes/No on screen, 1/0 in downloads.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_completed(stdClass $row): string {
        if ($this->is_downloading()) {
            return $row->completed ? '1' : '0';
        }
        return $row->completed
            ? get_string('yes', 'modernvideoplayer')
            : get_string('no', 'modernvideoplayer');
    }

    /**
     * Localised completion timestamp.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_completiontime(stdClass $row): string {
        return $row->completiontime ? userdate((int) $row->completiontime) : '-';
    }

    /**
     * Localised last-heartbeat timestamp.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_lastheartbeat(stdClass $row): string {
        return $row->lastheartbeat ? userdate((int) $row->lastheartbeat) : '-';
    }

    /**
     * Last reported playback rate.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_lastplaybackrate(stdClass $row): string {
        return format_float((float) $row->lastplaybackrate, 2);
    }

    /**
     * Last reported tab visibility ('visible'/'hidden').
     *
     * @param stdClass $row
     * @return string
     */
    public function col_lastvisibility(stdClass $row): string {
        $val = (string) ($row->lastvisibility ?? '');
        if ($val === '') {
            return '-';
        }
        return $this->is_downloading() ? $val : s($val);
    }

    /**
     * Suspicious-seek count.
     *
     * @param stdClass $row
     * @return int
     */
    public function col_suspiciousflags(stdClass $row): int {
        return (int) $row->suspiciousflags;
    }

    /**
     * Integrity-failure count.
     *
     * @param stdClass $row
     * @return int
     */
    public function col_integrityfailures(stdClass $row): int {
        return (int) $row->integrityfailures;
    }
}
