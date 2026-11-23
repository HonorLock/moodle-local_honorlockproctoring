<?php
// This file is part of the honorlockproctoring module for Moodle - http://moodle.org/
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

namespace local_honorlockproctoring\check;

use action_link;
use context_module;
use core\check\check;
use core\check\result;
use html_writer;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/honorlockproctoring/lib.php');

/**
 * Status check that flags this plugin as deprecated and lists quizzes that still depend on it.
 *
 * Shown under Site administration > Reports > System status.
 *
 * @package    local_honorlockproctoring
 * @copyright  2026 Honorlock (https://honorlock.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deprecated extends check {

    /** @var string Moodle Marketplace listing of the plugin that replaces this one. */
    const REPLACEMENT_URL = 'https://marketplace.moodle.com/plugins/quizaccess_honorlock';

    /** @var int Maximum number of affected quizzes listed in the check details. */
    const MAX_LISTED_QUIZZES = 50;

    /**
     * Check id.
     *
     * @return string
     */
    public function get_id(): string {
        return 'deprecated';
    }

    /**
     * Check name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('check_deprecated_name', 'local_honorlockproctoring');
    }

    /**
     * Link to the replacement plugin.
     *
     * @return action_link|null
     */
    public function get_action_link(): ?action_link {
        return new action_link(
            new moodle_url(self::REPLACEMENT_URL),
            get_string('check_deprecated_action', 'local_honorlockproctoring')
        );
    }

    /**
     * Always a warning: the plugin is deprecated. The summary says whether quizzes still depend on it.
     *
     * Quizzes are Honorlock-enabled when their password starts with HL_NO_EDIT. Those passwords are
     * managed by Honorlock, so after this plugin is removed students would be asked for a password
     * they were never given. The check only reports these quizzes; it never changes them.
     *
     * @return result
     */
    public function get_result(): result {
        global $DB;

        $like = $DB->sql_like('q.password', ':prefix', true, true);
        $params = ['prefix' => $DB->sql_like_escape(HL_NO_EDIT) . '%'];

        $count = $DB->count_records_sql("SELECT COUNT(1) FROM {quiz} q WHERE {$like}", $params);

        if ($count === 0) {
            return new result(result::WARNING,
                get_string('check_deprecated_summary_noquizzes', 'local_honorlockproctoring'));
        }

        $sql = "SELECT q.id, q.name, cm.id AS cmid
                  FROM {quiz} q
                  JOIN {modules} m ON m.name = :modname
                  JOIN {course_modules} cm ON cm.module = m.id AND cm.instance = q.id
                 WHERE {$like}
              ORDER BY q.course, q.id";
        $quizzes = $DB->get_records_sql($sql, $params + ['modname' => 'quiz'], 0, self::MAX_LISTED_QUIZZES);

        $items = [];
        foreach ($quizzes as $quiz) {
            $context = context_module::instance($quiz->cmid);
            $items[] = html_writer::link(
                new moodle_url('/mod/quiz/view.php', ['id' => $quiz->cmid]),
                format_string($quiz->name, true, ['context' => $context])
            );
        }

        $details = html_writer::alist($items);
        if ($count > count($items)) {
            $details .= html_writer::tag('p',
                get_string('check_deprecated_details_more', 'local_honorlockproctoring', $count - count($items)));
        }

        return new result(result::WARNING,
            get_string('check_deprecated_summary_quizzes', 'local_honorlockproctoring', $count),
            $details);
    }
}
