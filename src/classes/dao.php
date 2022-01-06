<?php

class dao_filter_recitautolink{
     /**
     * This function gets all teachers for a course.
     *
     * @param int $courseid
     */
    public function load_course_teachers($courseid, $group = false){
        global $USER, $DB;

        $where = "where t3.courseid = :courseid";
        
        $query = "select  concat(t1.id, t2.id) as uniqueId, t1.id as id, t1.firstname, t1.lastname, t1.email, t5.shortname as role, concat(t1.firstname, ' ', t1.lastname) as imagealt,
        t1.picture, t1.firstnamephonetic, t1.lastnamephonetic, t1.middlename, t1.alternatename   
        from {user} as t1  
        inner join {user_enrolments} as t2 on t1.id = t2.userid
        inner join {enrol} as t3 on t2.enrolid = t3.id
        inner join {role_assignments} as t4 on t1.id = t4.userid and t4.contextid in (select id from {context} where instanceid = :courseid2)
        inner join {role} as t5 on t4.roleid = t5.id and t5.shortname in ('teacher', 'editingteacher', 'noneditingteacher') ";
        if ($group){
            $query .= "inner join {groups_members} as t6 on t6.userid = t1.id ";
            $where .= " and t6.groupid IN (select groupid from {groups_members} where userid=:userid)";
        }

        $query .= "$where ORDER BY CONCAT(t1.firstname, t1.lastname) ASC";
        
        $rst = $DB->get_records_sql($query, array('courseid' => $courseid, 'courseid2' => $courseid, 'userid' => $USER->id));

        $result = array();
		foreach($rst as $obj){
            $result[] = $obj;
        }    

        return $result;
    }

    public function load_cm_completions($courseid){
        global $USER, $DB;

        $query = "SELECT cmc.* FROM {course_modules} as cm
        INNER JOIN {course_modules_completion} cmc ON cmc.coursemoduleid=cm.id 
        WHERE cm.course = ? AND cmc.userid = ?";

        $rst = $DB->get_records_sql($query, [$courseid, $USER->id]);

        $result = array();
        foreach($rst as $obj){
            $result[$obj->coursemoduleid] = $obj;
        }

        return $result;
    }
}

class dao_filter_recitautolink_factory{
    private static $_instance;

    public static function getInstance()
	{
		if(!self::$_instance)
			self::$_instance = new self;
 
		return self::$_instance;
	}
 
	public function getDAO(){
        return new dao_filter_recitautolink();
	}
}
