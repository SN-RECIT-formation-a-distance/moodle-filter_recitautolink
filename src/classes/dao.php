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
 * This filter must be put before Auto-linking with Manage Filters to work properly.
 *
 * @package    filter_recitactivity
 * @copyright  2019 RECIT
 * @license    {@link http://www.gnu.org/licenses/gpl-3.0.html} GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * DB helper class
 *
 * @copyright  2019 RECIT
 * @license    {@link http://www.gnu.org/licenses/gpl-3.0.html} GNU GPL v3 or later
 */
class filter_recitactivity_dao {
     /**
      * This function gets all teachers for a course.
      *
      * @param int $courseid
      * @param bool $group
      */
    public function load_course_teachers($courseid, $group = false) {
        global $USER;
        
        if ($group){
            $groups = groups_get_user_groups($courseid, $USER->id);
            if (isset($groups[0])){
                $group = array_values($groups[0]);
            }
        }
        $coursecontext = context_course::instance($courseid);
        $users = get_users_by_capability($coursecontext, 'filter/recitactivity:teacher', '', '', '', '', $group, null, false);

        return array_values($users);
    }

     /**
      * This function gets module completion for student
      *
      * @param int $courseid
      */
    public function load_cm_completions($courseid) {
        global $USER, $DB;

        $query = "SELECT cmc.* FROM {course_modules} cm
        INNER JOIN {course_modules_completion} cmc ON cmc.coursemoduleid=cm.id
        WHERE cm.course = ? AND cmc.userid = ?";

        $rst = $DB->get_records_sql($query, [$courseid, $USER->id]);

        $result = array();
        foreach($rst as $obj) {
            $result[$obj->coursemoduleid] = $obj;
        }

        return $result;
    }
}

/**
 * DB helper factory
 *
 * @copyright  2019 RECIT
 * @license    {@link http://www.gnu.org/licenses/gpl-3.0.html} GNU GPL v3 or later
 */
class filter_recitactivity_dao_factory{
    /** @var object $instance store dao instance */
    private static $instance;

     /**
      * This function gets dao instance
      */
    public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self;
        }

		return self::$instance;
	}
 
     /**
      * This function gets dao object
      */
	public function getDAO() {
        return new filter_recitactivity_dao();
	}
}
