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
 * Unit tests.
 *
 * @package filter_recitactivity
 * @category test
 * @copyright 2019 RECIT
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/filter/recitactivity/filter.php'); // Include the code to test.

/**
 * Test case for the recit activity filter.
 *
 */
class filter_recitactivity_filter_testcase extends advanced_testcase {

    protected function setUp(): void {
        global $DB;
        $this->resetAfterTest(true);

        // Create a test course.
        $this->course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $this->context = context_course::instance($this->course->id);
        // Create two pages that will be linked to.
        $this->page1 = $this->getDataGenerator()->create_module('page',
                ['course' => $this->course->id, 'name' => 'Test 1','completion' => COMPLETION_TRACKING_MANUAL,]);
        $this->page2 = $this->getDataGenerator()->create_module('page',
                ['course' => $this->course->id, 'name' => 'Test (2)']);
        $this->section1 = $this->getDataGenerator()->create_course_section(array('course'=> $this->course->id, 'section' => 1));
        $DB->set_field('course_sections', 'name', 'Section 0', array('id' => $this->section1->id));

        $this->filter = new filter_recitactivity($this->context, []);
        $this->setupFilter();
    }

    protected function setupFilter(){
        $PAGE = new stdClass();
        $PAGE->course = $this->course;
        $this->filter->setup($PAGE, $this->context);
    }

    public function test_section_links() {


        // Format text with all three entries in HTML.
        $html = '<p>Please read the page [[s/Section 0]].</p>';
        $filtered = $this->filter->filter($html);

        // Find all the glossary links in the result.
        $matches = [];
        $filtered = str_replace("&amp;", "&", $filtered);
        preg_match_all('~<a class="([^"]*)" title="([^"]*)" target="([^<]*)" href="[^"]*/course/view.php\?id=([0-9]+)&section=([0-9]+)#([^"]*)">([^<]*)</a>~',
                $filtered, $matches);

        // There should be 1 link.
        $this->assertCount(1, $matches[1]);

        // Check the ids in the links.
        $this->assertEquals($this->section1->section, $matches[5][0]);

        // Check the link text.
        $this->assertEquals('Section 0', $matches[7][0]);
    }

    public function test_activity_links() {


        $editingteacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');

        // Format text with all three entries in HTML.
        $html = '<p>Please read the two pages [[Test 1]] and <i>[[Test (2)]]</i>.</p>';
        $filtered = $this->filter->filter($html);

        // Find all the glossary links in the result.
        $matches = [];
        preg_match_all('~<a class="([^"]*)" title="([^"]*)" href="[^"]*/mod/page/view.php\?id=([0-9]+)" target="([^<]*)">([^<]*)</a>~',
                $filtered, $matches);

        // There should be 2 links links.
        $this->assertCount(2, $matches[1]);

        // Check text of title attribute.
        $this->assertEquals($this->page1->name, $matches[2][0]);
        $this->assertEquals($this->page2->name, $matches[2][1]);

        // Check the ids in the links.
        $this->assertEquals($this->page1->cmid, $matches[3][0]);
        $this->assertEquals($this->page2->cmid, $matches[3][1]);

        // Check the link text.
        $this->assertEquals($this->page1->name, $matches[5][0]);
        $this->assertEquals($this->page2->name, $matches[5][1]);
    }

    public function test_activity_links_desc_param() {

        $desc = "123desc";

        $editingteacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');

        // Format text with all three entries in HTML.
        $html = '<p>Please read the two pages [[desc:"'.$desc.'"/Test 1]] and <i>[[Test (2)]]</i>.</p>';
        $filtered = $this->filter->filter($html);

        // Find all the glossary links in the result.
        $matches = [];
        preg_match_all('~<a class="([^"]*)" title="([^"]*)" href="[^"]*/mod/page/view.php\?id=([0-9]+)" target="([^<]*)">([^<]*)</a>~',
                $filtered, $matches);

        // There should be 2 links links.
        $this->assertCount(2, $matches[1]);

        // Check text of title attribute.
        $this->assertEquals($desc, $matches[2][0]);
        $this->assertEquals($this->page2->name, $matches[2][1]);

        // Check the ids in the links.
        $this->assertEquals($this->page1->cmid, $matches[3][0]);
        $this->assertEquals($this->page2->cmid, $matches[3][1]);

        // Check the link text.
        $this->assertEquals($desc, $matches[5][0]);
        $this->assertEquals($this->page2->name, $matches[5][1]);
    }

    public function test_activity_links_class_param() {

        $desc = "123desc";

        $editingteacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');

        // Format text with all three entries in HTML.
        $html = '<p>Please read the two pages [[class:"btn"/desc:"'.$desc.'"/Test 1]] and <i>[[Test (2)]]</i>.</p>';
        $filtered = $this->filter->filter($html);

        // Find all the glossary links in the result.
        $matches = [];
        preg_match_all('~<a class="([^"]*)" title="([^"]*)" href="[^"]*/mod/page/view.php\?id=([0-9]+)" target="([^<]*)">([^<]*)</a>~',
                $filtered, $matches);

        // There should be 2 links links.
        $this->assertCount(2, $matches[1]);

        // Check text of title attribute.
        $this->assertEquals($desc, $matches[2][0]);
        $this->assertEquals($this->page2->name, $matches[2][1]);

        // Check text of class attribute.
        $this->assertEquals("autolink btn", $matches[1][0]);
        $this->assertEquals("autolink ", $matches[1][1]);

        // Check the ids in the links.
        $this->assertEquals($this->page1->cmid, $matches[3][0]);
        $this->assertEquals($this->page2->cmid, $matches[3][1]);

        // Check the link text.
        $this->assertEquals($desc, $matches[5][0]);
        $this->assertEquals($this->page2->name, $matches[5][1]);
    }

    public function test_activity_links_icon_param() {

        $desc = "123desc";

        $editingteacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');

        // Format text with all three entries in HTML.
        $html = '<p>Please read the two pages [[i/class:"btn"/desc:"'.$desc.'"/Test 1]] and <i>[[Test (2)]]</i>.</p>';
        $filtered = $this->filter->filter($html);

        // Find all the glossary links in the result.
        $matches = [];
        preg_match_all('~<a class="([^"]*)" title="([^"]*)" href="[^"]*/mod/page/view.php\?id=([0-9]+)" target="([^<]*)">(.*?)</a>~',
                $filtered, $matches);

        // There should be 2 links links.
        $this->assertCount(2, $matches[1]);

        // Check text of title attribute.
        $this->assertEquals($desc, $matches[2][0]);
        $this->assertEquals($this->page2->name, $matches[2][1]);

        // Check text of class attribute.
        $this->assertEquals("autolink btn", $matches[1][0]);
        $this->assertEquals("autolink ", $matches[1][1]);

        // Check the ids in the links.
        $this->assertEquals($this->page1->cmid, $matches[3][0]);
        $this->assertEquals($this->page2->cmid, $matches[3][1]);

        // Check the link text.
        $this->assertStringContainsString('<img', $matches[5][0]);
        $this->assertStringContainsString('activityicon', $matches[5][0]);
        $this->assertEquals($this->page2->name, $matches[5][1]);
    }

    public function test_activity_links_completion_param() {

        $desc = "123desc";

        $editingteacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');

        // Format text with all three entries in HTML.
        $html = '<p>Please read the two pages [[c/class:"btn"/desc:"'.$desc.'"/Test 1]] and <i>[[Test (2)]]</i>.</p>';
        $filtered = $this->filter->filter($html);

        // Find all the glossary links in the result.
        $matches = [];
        preg_match_all('~<a class="([^"]*)" title="([^"]*)" href="[^"]*/mod/page/view.php\?id=([0-9]+)" target="([^<]*)">(.*?)</a>~',
                $filtered, $matches);

        // There should be 2 links links.
        $this->assertCount(2, $matches[1]);

        // Check text of title attribute.
        $this->assertEquals($desc, $matches[2][0]);
        $this->assertEquals($this->page2->name, $matches[2][1]);

        // Check text of class attribute.
        $this->assertEquals("autolink btn", $matches[1][0]);
        $this->assertEquals("autolink ", $matches[1][1]);

        // Check the ids in the links.
        $this->assertEquals($this->page1->cmid, $matches[3][0]);
        $this->assertEquals($this->page2->cmid, $matches[3][1]);

        // Check the link text.
        $this->assertStringContainsString('class="togglecompletion"', $filtered);
        $this->assertEquals($this->page2->name, $matches[5][1]);
    }

    public function test_activity_links_popup_param() {

        $desc = "123desc";

        $editingteacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');

        // Format text with all three entries in HTML.
        $html = '<p>Please read the two pages [[p/class:"btn"/desc:"'.$desc.'"/Test 1]] and <i>[[Test (2)]]</i>.</p>';
        $filtered = $this->filter->filter($html);

        // Find all the glossary links in the result.
        $matches = [];
        preg_match_all('~<a class="([^"]*)" title="([^"]*)" href="(.*?)" target="([^<]*)">(.*?)</a>~',
                $filtered, $matches);

        // There should be 2 links links.
        $this->assertCount(2, $matches[1]);

        // Check text of title attribute.
        $this->assertEquals($desc, $matches[2][0]);
        $this->assertEquals($this->page2->name, $matches[2][1]);

        // Check text of class attribute.
        $this->assertEquals("autolink btn", $matches[1][0]);
        $this->assertEquals("autolink ", $matches[1][1]);

        // Check the ids in the links.
        $this->assertStringContainsString('popup', $matches[3][0]);
        $this->assertStringContainsString($this->page2->cmid, $matches[3][1]);

        // Check the link text.
        $this->assertEquals($desc, $matches[5][0]);
        $this->assertEquals($this->page2->name, $matches[5][1]);
    }

    public function test_teacher_param() {

        $editingteacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');//teacher1
        //$teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'teacher');//teacher2, order is asc
        $user = $this->getDataGenerator()->create_and_enrol($this->course, 'user');
        $this->setupFilter();
        $this->setUser($user);

        // Format text with param in HTML.
        $html = '<p><a class="autolinktest">[[d/teacher1.firstname]]</a><a class="autolinktest">[[d/teacher1.lastname]]</a><a class="autolinktest">[[d/teacher2.lastname]]</a></p>';
        $filtered = $this->filter->filter($html);

        $matches = [];
        preg_match_all("'<a class=\"autolinktest\">(.*?)</a>'si",
                $filtered, $matches);

        // Check name
        $this->assertEquals($editingteacher->firstname, $matches[1][0]);
        $this->assertEquals($editingteacher->lastname, $matches[1][1]);
        //$this->assertEquals($teacher->lastname, $matches[1][2]);
    }

    public function test_user_param() {

        $editingteacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $user = $this->getDataGenerator()->create_and_enrol($this->course, 'user');
        $this->setUser($user);

        // Format text with param in HTML.
        $html = '<p><a class="autolinktest">[[d/user.firstname]]</a><a class="autolinktest">[[d/user.lastname]]</a></p>';
        $filtered = $this->filter->filter($html);

        $matches = [];
        preg_match_all("'<a class=\"autolinktest\">(.*?)</a>'si",
                $filtered, $matches);

        // Check name
        $this->assertEquals($user->firstname, $matches[1][0]);
        $this->assertEquals($user->lastname, $matches[1][1]);
    }

    public function test_course_param() {
        global $COURSE;

        $COURSE = $this->course;
        $user = $this->getDataGenerator()->create_and_enrol($this->course, 'user');
        $this->setUser($user);

        // Format text with param in HTML.
        $html = '<p><a class="autolinktest">[[d/course.fullname]]</a><a class="autolinktest">[[d/user.lastname]]</a></p>';
        $filtered = $this->filter->filter($html);

        $matches = [];
        preg_match_all("'<a class=\"autolinktest\">(.*?)</a>'si",
                $filtered, $matches);

        // Check name
        $this->assertEquals($this->course->fullname, $matches[1][0]);
    }
}
