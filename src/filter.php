<?php
// This file is part of a plugin written to be used on the free teaching platform : Moodle
// Copyright (C) 2019 recit
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <https://www.gnu.org/licenses/>.
//
// This filter must be put over Auto-linking with Manage Filters to work properly.
//
// @package    filter_recitactivity
// @subpackage RECIT
// @copyright  RECIT {@link https://recitfad.ca}
// @author     RECIT {@link https://recitfad.ca}
// @license    {@link http://www.gnu.org/licenses/gpl-3.0.html} GNU GPL v3 or later
// @developer  Studio XP : {@link https://www.studioxp.ca}

defined('MOODLE_INTERNAL') || die();

/**
 * Activity name filtering
 */

class filter_recitactivity extends moodle_text_filter {
    protected $courseActivityList = array();
    protected $teacherList = array();
    
    protected function loadCourseTeachers($courseId){
        global $DB;

        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $context = context_course::instance($courseId);
        $result = get_role_users($role->id, $context);
        
        foreach($result as $teacher){
            $teacher->imagealt = sprintf("%s %s", $teacher->firstname, $teacher->lastname);
        }

        $this->teacherList = array_values($result);
    }

	public function setup($page, $context) {
		global $COURSE;
						
		$coursectx = $context->get_course_context(false);
		
		if (!$coursectx) {
			return ;
		}
		
		$this->loadCourseTeachers($COURSE->id);
				        
        $this->loadCourseActivityList();
    }
    
    function loadCourseActivityList(){
        global $COURSE, $PAGE;

        $this->courseActivityList = array();

        $modinfo = get_fast_modinfo($COURSE->id);
        $course = $modinfo->get_course();
        $renderer = $PAGE->get_renderer('core','course');
        
        if (!empty($modinfo->cms)) {
            // Create array of visible activities sorted by the name length (we are only interested in properties name and url).
            $sortedactivities = array();
            foreach ($modinfo->cms as $cm) {
                // Use normal access control and visibility, but exclude labels and hidden activities.
                if ($cm->has_view()) {
                    $sortedactivities[] = (object)array(
                            'cminfo' => $cm,
                            'name' => $cm->name,
                            'url' => $cm->url,
                            'id' => $cm->id,
                            'visible' => $cm->uservisible,
                            'namelen' => -strlen($cm->name) // Negative value for reverse sorting.
                    );
                }
            }
            foreach ($sortedactivities as $cm) {
                $title = s(trim(strip_tags($cm->name)));
                $currentname = trim($cm->name);

                // Avoid empty or unlinkable activity names.
                if (!empty($title)) {
                    $completioninfo = new completion_info($course);
                    
                    $cmcurrentname = $currentname;
                    $cmname = $renderer->course_section_cm_name($cm->cminfo);
                    $cmcompletion = $this->course_section_cm_completion($course, $completioninfo, $cm->cminfo);
                    
                    if(!$cm->visible)
                    {
                        $cmname = '';
                        $cmcompletion = '';
                        $cmcurrentname = ' ';
                        $href_tag_begin = '';
                        $href_tag_end = '';
                    }

                    $courseActivity = new stdClass();
                    $courseActivity->cmname = $cmname;
                    $courseActivity->currentname = $currentname;
                    $courseActivity->cmcompletion = $cmcompletion;
                    $courseActivity->cmcompletion = $cmcompletion;
                    $courseActivity->href_tag_begin = html_writer::start_tag('a', array('class' => 'autolink', 'title' => $title, 'href' => $cm->url));
					$courseActivity->href_tag_end = '</a>';
                    $this->courseActivityList[] = $courseActivity;				
                }
            }
        }
    }
    
    function getCourseActivity($name){
        $result = null;
        
        foreach($this->courseActivityList as $item){
            if($item->currentname == $name){
                return $item;
            }
        }

        return null;
    }
    
	function filter($text, array $options = array()) {
		global $USER, $PAGE, $OUTPUT, $COURSE;
			
		// Check if we need to build filters.
		if(strpos($text,'[[') === false or !is_string($text) or empty($text)){;
			return $text;
		}
				
		$coursectx = $this->context->get_course_context(false);
		
		if (!$coursectx) {
			return $text;
		}
		
		$courseid = $coursectx->instanceid;
			
		$sep = get_config('filter_recitactivity', 'character');
		
		preg_match_all('#(\[\[)([^\]]+)(\]\])#', $text, $matches);
        
        $matches = $matches[0]; // it will match the wanted RE, for instance [[i/Activité 3]]
        
        $result = $text;
        foreach($matches as $match){
            $item = explode($sep, $match);

            // in case [[ActivityName]]
            if(count($item) == 1){
                $item[0] = str_replace("[[", "", $item[0]);
                $complement = str_replace("]]", "", $item[0]);
                $param = "l";
            }
            else{
                $complement = str_replace("]]", "", array_pop($item));
                $param = str_replace("[[", "", implode("", $item));
            }
            
            
            switch($param){
                case "i":                            
                    $activity = $this->getCourseActivity($complement);
                    if($activity != null){
                        $result = str_replace($match, $activity->cmname, $result);
                    }
                    break;
                case "c":
                    $activity = $this->getCourseActivity($complement);
                    if($activity != null){
                        $result = str_replace($match, sprintf("%s %s %s %s", $activity->cmcompletion, $activity->href_tag_begin, $activity->currentname, $activity->href_tag_end), $result);
                    }
                    break;
                case "ci":
                case "ic":
                    $activity = $this->getCourseActivity($complement);
                    if($activity != null){
                        $result = str_replace($match, sprintf("%s %s", $activity->cmcompletion, $activity->cmname), $result);
                    }
                    break;
                case "l":
                    $activity = $this->getCourseActivity($complement);
                    if($activity != null){
                        $result = str_replace($match, sprintf("%s%s%s", $activity->href_tag_begin, $activity->currentname, $activity->href_tag_end), $result);
                    }
                    break;
                case "d":
                    if($complement == "user.firstname"){
                        $result = str_replace($match, $USER->firstname, $result);
                    }
                    else if($complement == "user.lastname"){
                        $result = str_replace($match, $USER->lastname, $result);
                    }
                    else if($complement == "user.email"){
                        $result = str_replace($match, $USER->email, $result);
                    }
                    else if($complement == "user.picture"){
                        $picture = $OUTPUT->user_picture($USER, array('courseid' => $coursectx->instanceid, 'link' => false));
                        $result = str_replace($match, $picture, $result);
                    }
                    else if($complement == "course.shortname"){
                        $result = str_replace($match, $COURSE->shortname, $result);
                    }
                    else if($complement == "course.fullname"){
                        $result = str_replace($match, $COURSE->fullname, $result);
                    }
                    else{
                        foreach($this->teacherList as $index => $teacher){
                            $nb = $index + 1;
                            if($complement == "teacher$nb.firstname"){
                                $result = str_replace($match, $teacher->firstname, $result);
                            }
                            else if($complement == "teacher$nb.lastname"){
                                $result = str_replace($match, $teacher->lastname, $result);
                            }
                            else if($complement == "teacher$nb.email"){
                                $result = str_replace($match, $teacher->email, $result);
                            }
                            else if($complement == "teacher$nb.picture"){
                                $picture = $OUTPUT->user_picture($teacher, array('courseid' => $coursectx->instanceid, 'link' => false));
                                $result = str_replace($match, $picture, $result);
                            }
                        }
                    }
                    break;
            }
        }

        return $result;
	}
	
	public function course_section_cm_completion($course, &$completioninfo, cm_info $mod) {
		global $CFG, $DB, $PAGE;
		$renderer = $PAGE->get_renderer('core','course');
		$output = '';
		if (!isloggedin() || isguestuser() || !$mod->uservisible) {
			return $output;
		}
		if ($completioninfo === null) {
			$completioninfo = new completion_info($course);
		}
		$completion = $completioninfo->is_enabled($mod);
		if ($completion == COMPLETION_TRACKING_NONE) {
			if ($PAGE->user_is_editing()) {
				$output .= html_writer::span('&nbsp;', 'filler');
			}
			return $output;
		}
		
		$completiondata = $completioninfo->get_data($mod, true);
		$completionicon = '';
		
		if ($PAGE->user_is_editing()) {
			switch ($completion) {
				case COMPLETION_TRACKING_MANUAL :
					$completionicon = 'manual-enabled'; break;
				case COMPLETION_TRACKING_AUTOMATIC :
					$completionicon = 'auto-enabled'; break;
			}
		} else if ($completion == COMPLETION_TRACKING_MANUAL) {
			switch($completiondata->completionstate) {
				case COMPLETION_INCOMPLETE:
					$completionicon = 'manual-n' . ($completiondata->overrideby ? '-override' : '');
					break;
				case COMPLETION_COMPLETE:
					$completionicon = 'manual-y' . ($completiondata->overrideby ? '-override' : '');
					break;
			}
		} else { // Automatic
			switch($completiondata->completionstate) {
				case COMPLETION_INCOMPLETE:
					$completionicon = 'auto-n' . ($completiondata->overrideby ? '-override' : '');
					break;
				case COMPLETION_COMPLETE:
					$completionicon = 'auto-y' . ($completiondata->overrideby ? '-override' : '');
					break;
				case COMPLETION_COMPLETE_PASS:
					$completionicon = 'auto-pass'; break;
				case COMPLETION_COMPLETE_FAIL:
					$completionicon = 'auto-fail'; break;
			}
		}
		if ($completionicon) {
			$formattedname = html_entity_decode($mod->get_formatted_name(), ENT_QUOTES, 'UTF-8');
			if ($completiondata->overrideby) {
				$args = new stdClass();
				$args->modname = $formattedname;
				$overridebyuser = \core_user::get_user($completiondata->overrideby, '*', MUST_EXIST);
				$args->overrideuser = fullname($overridebyuser);
				$imgalt = get_string('completion-alt-' . $completionicon, 'completion', $args);
			} else {
				$imgalt = get_string('completion-alt-' . $completionicon, 'completion', $formattedname);
			}
			
			if ($PAGE->user_is_editing()) {
				// When editing, the icon is just an image.
				$completionpixicon = new pix_icon('i/completion-'.$completionicon, $imgalt, '',
						array('title' => $imgalt, 'class' => 'iconsmall'));
				$output .= html_writer::tag('span', $renderer->render($completionpixicon),
						array('class' => 'autocompletion'));
			} else if ($completion == COMPLETION_TRACKING_MANUAL) {
				$newstate =
				$completiondata->completionstate == COMPLETION_COMPLETE
				? COMPLETION_INCOMPLETE
				: COMPLETION_COMPLETE;
				// In manual mode the icon is a toggle form...
				
				// If this completion state is used by the
				// conditional activities system, we need to turn
				// off the JS.
				$extraclass = '';
				if (!empty($CFG->enableavailability) &&
						core_availability\info::completion_value_used($course, $mod->id)) {
							$extraclass = ' preventjs';
						}
						$output .= html_writer::start_tag('form', array('method' => 'post',
								'action' => new moodle_url('/course/togglecompletion.php'),
								'class' => 'togglecompletion'. $extraclass, 'style' => 'display: inline;'));
						$output .= html_writer::start_tag('div', array('style' => 'display: inline;'));
						$output .= html_writer::empty_tag('input', array(
								'type' => 'hidden', 'name' => 'id', 'value' => $mod->id));
						$output .= html_writer::empty_tag('input', array(
								'type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
						$output .= html_writer::empty_tag('input', array(
								'type' => 'hidden', 'name' => 'modulename', 'value' => $formattedname));
						$output .= html_writer::empty_tag('input', array(
								'type' => 'hidden', 'name' => 'completionstate', 'value' => $newstate));
						$output .= html_writer::tag('button',
								$renderer->pix_icon('i/completion-' . $completionicon, $imgalt),
								array('class' => 'btn btn-link', 'aria-live' => 'assertive', 'style' => 'padding: 0px;'));
						$output .= html_writer::end_tag('div');
						$output .= html_writer::end_tag('form');
			} else {
				// In auto mode, the icon is just an image.
				$completionpixicon = new pix_icon('i/completion-'.$completionicon, $imgalt, '',
						array('title' => $imgalt, 'style' => 'margin: 0px;'));
				$output .= html_writer::tag('span', $renderer->render($completionpixicon),
						array('class' => 'autocompletion'));
			}
		}
		return $output;
	}
}
