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

require_once($CFG->dirroot . "/local/recitcommon/php/PersistCtrl.php");

/**
 * Activity name filtering
 */

class filter_recitactivity extends moodle_text_filter {
	// Trivial-cache - keyed on $cachedcourseid and $cacheduserid.
	
	static $cachedcourseid;
	static $cacheduserid;
	
	static $userinfofilters;
	static $all_filters_used;
	
	public function setup($page, $context) {
		global $DB, $USER, $COURSE, $OUTPUT ;
						
		$coursectx = $context->get_course_context(false);
		
		if (!$coursectx) {
			return ;
		}
		
		$s = get_config('filter_recitactivity', 'character');
		
		$filter_cf = '[[d'.$s.'course.fullname'.']]';
		self::$userinfofilters['coursefullname'] = new filterobject($filter_cf, '', '', false, true, $COURSE->fullname);
		
		$filter_cs = '[[d'.$s.'course.shortname'.']]';
		self::$userinfofilters['courseshortname'] = new filterobject($filter_cs, '', '', false, true, $COURSE->shortname);
			
		$teachers = PersistCtrl::getInstance($DB, $USER)->getCourseTeachers($COURSE->id);
		
		$index = 1;
		
		foreach ($teachers as $teacher){
			
			// Filters for teacher data information.
			$filter_df = '[[d'.$s.'teacher'.$index.'.firstname'.']]';
			self::$userinfofilters['firstname'.$index] = new filterobject($filter_df, '', '', false, true, $teacher->firstname);
			
			$filter_dl = '[[d'.$s.'teacher'.$index.'.lastname'.']]';
			self::$userinfofilters['lastname'.$index] = new filterobject($filter_dl, '', '', false, true, $teacher->lastname);
			
			$filter_de = '[[d'.$s.'teacher'.$index.'.email'.']]';
			self::$userinfofilters['email'.$index] = new filterobject($filter_de, '', '', false, true, $teacher->email);
			
			$picture = $OUTPUT->user_picture($teacher, array('courseid' => $coursectx->instanceid, 'link' => false));
			
			$filter_dp = '[[d'.$s.'teacher'.$index.'.picture'.']]';
			self::$userinfofilters['picture'.$index] = new filterobject($filter_dp, $picture , '', false, true , ' ');
			
			$index++;
		}
	}
	
	function filter($text, array $options = array()) {
		global $USER; // Since 2.7 we can finally start using globals in filters.
		global $PAGE, $OUTPUT, $DB;
			
		// Check if we need to build filters.
		if(strpos($text,'[[') === false or !is_string($text) or empty($text)){;
			return $text;
		}
				
		$coursectx = $this->context->get_course_context(false);
		
		if (!$coursectx) {
			return $text;
		}
		
		$courseid = $coursectx->instanceid;
			
		$renderer = $PAGE->get_renderer('core','course');
				
		$s = get_config('filter_recitactivity', 'character');
		
		$filters_list_used = $filter = $out = array();
		
		// List of characters filters we want to develop.
		$filter_chars = ['d', 'i', 'c'];
		
		preg_match_all('#(\[\[)([^\]]+)(\]\])#', $text, $out);
		
		foreach ($out[2] as $chain){
			$filter = null; $pos = null ; $max_pos = 0;
			
			foreach ($filter_chars as $char){
				if( strpos($chain, $char.$s ) !== false ) {
					$pos = strpos($chain, $char.$s );
					$filter[$pos] = $char.$s;
					$max_pos = max($max_pos, $pos);
				}
			}
			
			ksort($filter);
			
			if($filter){
				$filter_used='';
				
				foreach($filter as $value){
					$filter_used .= $value;
				}
								
				if(strcmp($filter_used, '') !== 0) {
					$filters_list_used[] = $filter_used;
					
					$substring_1 = substr($chain, 0, $max_pos+2);
					$substring_2 = substr($chain, $max_pos+2);
					
					$substring_2_strip_space_tag = trim(str_replace("&nbsp;", ' ', $substring_2));
					
					if(strcmp($substring_1, $filter_used) !== 0 or strcmp($substring_2, $substring_2_strip_space_tag) !== 0){
						$text = str_replace('[['.$substring_1.$substring_2.']]' , '[['.$filter_used.$substring_2_strip_space_tag.']]', $text);
					}
				}
				
			}else{
				$substring_2_strip_space_tag = trim(str_replace("&nbsp;", ' ', $chain));
				
				if(strcmp($chain, $substring_2_strip_space_tag) !== 0){
					$text = str_replace('[['.$chain.']]' , '[['.$substring_2_strip_space_tag.']]', $text);
				}
			}
		}
		
		$filters_list_used_unique = array_unique($filters_list_used);
				
		if(in_array('d'.$s, $filters_list_used_unique )){
			// Filters for user data information.
			$filter_df = '[[d'.$s.'user.firstname'.']]';
			self::$userinfofilters['firstname'] = new filterobject($filter_df, '', '', false, true, $USER->firstname);
			
			$filter_dl = '[[d'.$s.'user.lastname'.']]';
			self::$userinfofilters['lastname'] = new filterobject($filter_dl, '', '', false, true, $USER->lastname);
			
			$filter_de = '[[d'.$s.'user.email'.']]';
			self::$userinfofilters['email'] = new filterobject($filter_de, '', '', false, true, $USER->email);
						
			$picture = $OUTPUT->user_picture($USER, array('courseid' => $coursectx->instanceid, 'link' => false));
			
			$filter_dp = '[[d'.$s.'user.picture'.']]';
			self::$userinfofilters['picture'] = new filterobject($filter_dp, $picture , '', false, true , ' ');
			
		}
		
		// Initialise/invalidate our trivial cache if dealing with a different course.
		if (!isset(self::$cachedcourseid) || self::$cachedcourseid !== (int)$courseid) {
			self::$all_filters_used = null;
		}
		self::$cachedcourseid = (int)$courseid;
		// And the same for user id.
		if (!isset(self::$cacheduserid) || self::$cacheduserid !== (int)$USER->id) {
			self::$all_filters_used = null;
		}
		self::$cacheduserid = (int)$USER->id;
		
		/// It may be cached
		if (self::$all_filters_used == null) {
			$modinfo = get_fast_modinfo($courseid);
			$course = $modinfo->get_course();
			
			if (!empty($modinfo->cms)) {
				self::$all_filters_used = array(); // We will store all the created filters here.
				
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
				// Sort activities by the length of the activity name in reverse order.
				core_collator::asort_objects_by_property($sortedactivities, 'namelen', core_collator::SORT_NUMERIC);
				
				foreach ($sortedactivities as $cm) {
					$title = s(trim(strip_tags($cm->name)));
					$currentname = trim($cm->name);
					
					// Avoid empty or unlinkable activity names.
					if (!empty($title)) {
												
						$href_tag_begin = html_writer::start_tag('a',
								array('class' => 'autolink', 'title' => $title,
										'href' => $cm->url));
						$href_tag_end = '</a>';
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
						
						// Build all filters used in the page.
						self::$all_filters_used[$cm->id] = new filterobject('[['.$currentname.']]', $href_tag_begin, $href_tag_end, false, true, $cmcurrentname);
						foreach ($filters_list_used_unique as $filter){
							if(strcmp($filter, 'i'.$s) === 0)
								self::$all_filters_used[$cm->id.$filter] = new filterobject('[['.$filter.$currentname.']]', $cmname, '', false, true, ' ');
							if(strcmp($filter, 'c'.$s) === 0)
								self::$all_filters_used[$cm->id.$filter] = new filterobject('[['.$filter.$currentname.']]',$cmcompletion.$href_tag_begin, $href_tag_end, false, true, $cmcurrentname);
							if(strcmp($filter, 'i'.$s.'c'.$s) === 0 or strcmp($filter, 'c'.$s.'i'.$s) === 0 )
								self::$all_filters_used[$cm->id.$filter] = new filterobject('[['.$filter.$currentname.']]',$cmcompletion, $cmname, false, true, ' ');
						}
					}
				}
			}
		}
		
		if (self::$userinfofilters) {
			$text = filter_phrases($text, self::$userinfofilters);
		}
		
		if (self::$all_filters_used) {
			//$intCode = strip_tags($text);
			//$text = str_replace($intCode, filter_phrases($intCode, $filterslist), $text);
			$text = filter_phrases($text, self::$all_filters_used);
			return $text;
		} else {
			return $text;
		}
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
