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

namespace local_honorlockproctoring;

use core\check\result;
use local_honorlockproctoring\check\deprecated;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/honorlockproctoring/lib.php');

/**
 * Tests for the deprecation status check.
 *
 * @package    local_honorlockproctoring
 * @copyright  2026 Honorlock (https://honorlock.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_honorlockproctoring\check\deprecated
 */
class check_deprecated_test extends \advanced_testcase {

    /**
     * The check is registered with the system status report.
     */
    public function test_check_is_registered(): void {
        $checks = \core\check\manager::get_status_checks();
        $ids = array_map(function($check) {
            return $check->get_ref();
        }, $checks);

        $this->assertContains('local_honorlockproctoring_deprecated', $ids);
    }

    /**
     * Without Honorlock-enabled quizzes the check still warns, with no details.
     */
    public function test_result_without_honorlock_quizzes(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_module('quiz', ['course' => $course->id, 'quizpassword' => 'regular']);

        $result = (new deprecated())->get_result();

        $this->assertEquals(result::WARNING, $result->get_status());
        $this->assertEquals(
            get_string('check_deprecated_summary_noquizzes', 'local_honorlockproctoring'),
            $result->get_summary()
        );
        $this->assertEmpty($result->get_details());
    }

    /**
     * Honorlock-enabled quizzes are counted and listed; other quizzes and passwords are not shown.
     */
    public function test_result_lists_honorlock_quizzes(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        // The quiz generator takes the form field 'quizpassword' and stores it in quiz.password.
        $hlquiz =$this->getDataGenerator()->create_module('quiz', [
            'course' => $course->id,
            'name' => 'Proctored midterm',
            'quizpassword' => HL_NO_EDIT . 'placeholder',
        ]);
        $this->getDataGenerator()->create_module('quiz', [
            'course' => $course->id,
            'name' => 'Practice quiz',
            'quizpassword' => 'HLXNOXEDIT',
        ]);

        $result = (new deprecated())->get_result();

        $this->assertEquals(result::WARNING, $result->get_status());
        $this->assertEquals(
            get_string('check_deprecated_summary_quizzes', 'local_honorlockproctoring', 1),
            $result->get_summary()
        );
        $this->assertStringContainsString('Proctored midterm', $result->get_details());
        $this->assertStringContainsString('id=' . $hlquiz->cmid, $result->get_details());
        $this->assertStringNotContainsString('Practice quiz', $result->get_details());
        $this->assertStringNotContainsString('placeholder', $result->get_details());
    }

    /**
     * Details are capped and the remainder is summarised.
     */
    public function test_result_caps_listed_quizzes(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $total = deprecated::MAX_LISTED_QUIZZES + 2;
        for ($i = 0; $i < $total; $i++) {
            $this->getDataGenerator()->create_module('quiz', [
                'course' => $course->id,
                'quizpassword' => HL_NO_EDIT . $i,
            ]);
        }

        $result = (new deprecated())->get_result();

        $this->assertEquals(
            get_string('check_deprecated_summary_quizzes', 'local_honorlockproctoring', $total),
            $result->get_summary()
        );
        $this->assertStringContainsString(
            get_string('check_deprecated_details_more', 'local_honorlockproctoring', 2),
            $result->get_details()
        );
    }
}
