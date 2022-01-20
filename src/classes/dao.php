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

class filter_recitactivity_dao {
     /**
      * This function gets all teachers for a course.
      *
      * @param int $courseid
      * @param bool $group
      */
    public function load_course_teachers($courseid, $group = false) {
        global $USER, $DB;

        $where = "where t3.courseid = :courseid";

        $query = "select concat(t1.id, t2.id, t4.id) uniqueId, t1.id as id, t1.firstname, t1.lastname, t1.email, t5.shortname as role, concat(t1.firstname, ' ', t1.lastname) imagealt,
        t1.picture, t1.firstnamephonetic, t1.lastnamephonetic, t1.middlename, t1.alternatename
        from {user} t1
        inner join {user_enrolments} t2 on t1.id = t2.userid
        inner join {enrol} t3 on t2.enrolid = t3.id
        inner join {role_assignments} t4 on t1.id = t4.userid and t4.contextid in (select id from {context} where instanceid = :courseid2)
        inner join {role} t5 on t4.roleid = t5.id and t5.shortname in ('teacher', 'editingteacher', 'noneditingteacher') ";
        if ($group){
            $query .= "inner join {groups_members} t6 on t6.userid = t1.id ";
            $where .= " and t6.groupid IN (select groupid from {groups_members} where userid=:userid)";
        }

        $query .= "$where ORDER BY CONCAT(t1.firstname, t1.lastname) ASC";

        $rst = $DB->get_records_sql($query, array('courseid' => $courseid, 'courseid2' => $courseid, 'userid' => $USER->id));

        $result = array();
		foreach($rst as $obj) {
            $result[] = $obj;
        }    

        return $result;
    }

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

class filter_recitactivity_dao_factory{
    /* @var Object: store dao instance */
    private static $instance;

    public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self;
        }

		return self::$instance;
	}
 
	public function getDAO() {
        return new filter_recitactivity_dao();
	}
}
