<?php

interface i_filter_recitautolink_dao{
	public function load_course_teachers($courseid);
    public function load_cm_completions($courseId);
}

abstract class dao_filter_recitautolink implements i_filter_recitautolink_dao{
    protected $dbconn = null;

    protected function setDbConn($propName){
        global $DB;

		$moodleDB = $DB;
		$refMoodleDB = new ReflectionObject($moodleDB);
		$refProp1 = $refMoodleDB->getProperty($propName);
		$refProp1->setAccessible(TRUE);
		$this->dbconn = $refProp1->getValue($moodleDB);
    }

     /**
     * This function gets all teachers for a course.
     *
     * @param int $courseid
     */
    public function load_course_teachers($courseid, $group = false){
        global $CFG, $USER, $DB;

        $where = "where t3.courseid = :courseid";
        
        $query = "select t1.id as id, t1.firstname, t1.lastname, t1.email, t5.shortname as role, concat(t1.firstname, ' ', t1.lastname) as imagealt,
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

        /*$result = array();
        foreach(array('teacher', 'editingteacher', 'noneditingteacher') as $r){
            $role = $DB->get_record('role', array('shortname' => $r));
            if ($role){
                $context = get_context_instance(CONTEXT_COURSE, $courseid);
                $teachers = get_role_users($role->id, $context);
                foreach($teachers as $t){
                    if (!$group){
                        $result[] = $t;
                    }else{
                        $groupd = $DB->get_record('groups_members', array('userid' => $t->id));
                        $user_group = $DB->get_record('groups_members', array('userid' => $USER->id));
                        if ($groupd && $user_group && $groupd->groupid == $user_group->groupid){
                            $result[] = $t;
                        }
                    }
                }
            }
        }*/
        

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

    abstract protected function exec_query($query);
    abstract protected function fetch_object($rst);
}

class mysql_filter_recitautolink extends dao_filter_recitautolink{
    public function __construct(){
        $this->setDbConn('mysqli');
    }

    protected function exec_query($query){
        return $this->dbconn->query($query);
    }

    protected function fetch_object($rst){
        return $rst->fetch_object();
    }
}

class postgresql_filter_recitautolink extends dao_filter_recitautolink{
    public function __construct(){
        $this->setDbConn('pgsql');
    }

    protected function exec_query($query){
        return pg_query($this->dbconn, $query);
    }

    protected function fetch_object($rst){
        return pg_fetch_object($rst);
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
        global $CFG;

        switch($CFG->dbtype){
            case "pgsql":
                return new postgresql_filter_recitautolink();
            case "mysqli":
            default:
                return new mysql_filter_recitautolink();
	}
	}
}
