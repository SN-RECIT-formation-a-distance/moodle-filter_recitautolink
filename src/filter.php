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
    // Trivial-cache - keyed on $cachedcourseid and $cacheduserid.
    static $activitylist = null;

    static $cachedcourseid;
    static $cacheduserid;
    
    function filter($text, array $options = array()) {
        global $USER; // Since 2.7 we can finally start using globals in filters.
        global $PAGE;
        
        $s = get_config('filter_recitactivity', 'character');

        $coursectx = $this->context->get_course_context(false);
        if (!$coursectx) {
            return $text;
        }
        $courseid = $coursectx->instanceid;
        
        $renderer = $PAGE->get_renderer('core','course');

        // Initialise/invalidate our trivial cache if dealing with a different course.
        if (!isset(self::$cachedcourseid) || self::$cachedcourseid !== (int)$courseid) {
            self::$activitylist = null;
        }
        self::$cachedcourseid = (int)$courseid;
        // And the same for user id.
        if (!isset(self::$cacheduserid) || self::$cacheduserid !== (int)$USER->id) {
            self::$activitylist = null;
        }
        self::$cacheduserid = (int)$USER->id;

        /// It may be cached
        if (self::$activitylist == null) {
            $modinfo = get_fast_modinfo($courseid);
            $course = $modinfo->get_course();
            if (!empty($modinfo->cms)) {
                self::$activitylist = array(); // We will store all the created filters here.

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
                    $entitisedname  = s($currentname);
                    // Avoid empty or unlinkable activity names.
                    if (!empty($title)) {
                        $href_tag_begin = html_writer::start_tag('a',
                                array('class' => 'autolink', 'title' => $title,
                                    'href' => $cm->url));
                        $href_tag_end = '</a>';
                        $completioninfo = new completion_info($course);
                        
                        $filter_a = '[['.$currentname.']]';
                        $filter_ai = '[['.$currentname.$s.'i]]';
                        $filter_ia = '[[i'.$s.$currentname.']]';
                        $filter_aic = '[['.$currentname.$s.'i'.$s.'c]]';
                        $filter_aci = '[['.$currentname.$s.'c'.$s.'i]]';
                        $filter_iac = '[[i'.$s.$currentname.$s.'c]]';
                        $filter_ica = '[[i'.$s.'c'.$s.$currentname.']]';
                        $filter_cia = '[[c'.$s.'i'.$s.$currentname.']]';
                        $filter_cai = '[[c'.$s.$currentname.$s.'i]]';
                        $filter_ca = '[[c'.$s.$currentname.']]';
                        $filter_ac = '[['.$currentname.$s.'c]]';
                        
                        $cmcurrentname = $currentname;
                        $cmentitisedname = $entitisedname;
                        $cmname = $renderer->course_section_cm_name($cm->cminfo);
                        $cmcompletion = $this->course_section_cm_completion($course, $completioninfo, $cm->cminfo);
                        
                        if(!$cm->visible)
                        {
                            $cmname = '';
                            $cmcompletion = '';
                            $cmcurrentname = ' ';
                            $cmentitisedname = ' ';
                            $href_tag_begin = '';
                            $href_tag_end = '';
                        }
                        
                        //only activity
                        self::$activitylist[$cm->id] = new filterobject($filter_a, $href_tag_begin, $href_tag_end, false, true, $cmcurrentname);
                        
                        //with icon no checkbox
                        self::$activitylist[$cm->id.'-ai'] = new filterobject($filter_ai, $cmname, '', false, true, ' ');
                        self::$activitylist[$cm->id.'-ia'] = new filterobject($filter_ia, $cmname, '', false, true, ' ');
                        
                        //with icon checkbox right side
                        self::$activitylist[$cm->id.'-aic'] = new filterobject($filter_aic, $cmname, false, true, ' ');
                        self::$activitylist[$cm->id.'-aci'] = new filterobject($filter_aci, $cmname, $cmcompletion, false, true, ' ');
                        self::$activitylist[$cm->id.'-iac'] = new filterobject($filter_iac, $cmname, $cmcompletion, false, true, ' ');
                        
                        //with icon checkbox left side
                        self::$activitylist[$cm->id.'-ica'] = new filterobject($filter_ica, $cmcompletion, $cmname, false, true, ' ');
                        self::$activitylist[$cm->id.'-cia'] = new filterobject($filter_cia, $cmcompletion, $cmname, false, true, ' ');
                        self::$activitylist[$cm->id.'-cai'] = new filterobject($filter_cai, $cmcompletion, $cmname, false, true, ' ');
                        
                        //without icon checkbox left/right
                        self::$activitylist[$cm->id.'-ca'] = new filterobject($filter_ca, $cmcompletion.$href_tag_begin, $href_tag_end, false, true, $cmcurrentname);
                        self::$activitylist[$cm->id.'-ac'] = new filterobject($filter_ac, $href_tag_begin, $href_tag_end.$cmcompletion, false, true, $cmcurrentname);
                        
                        if ($currentname != $entitisedname) {
                            $efilter_a = '[['.$entitisedname.']]';
                            $efilter_ai = '[['.$entitisedname.$s.'i]]';
                            $efilter_ia = '[[i'.$s.$entitisedname.']]';
                            $efilter_aic = '[['.$entitisedname.$s.'i'.$s.'c]]';
                            $efilter_aci = '[['.$entitisedname.$s.'c'.$s.'i]]';
                            $efilter_iac = '[[i'.$s.$entitisedname.$s.'c]]';
                            $efilter_ica = '[[i'.$s.'c'.$s.$entitisedname.']]';
                            $efilter_cia = '[[c'.$s.'i'.$s.$entitisedname.']]';
                            $efilter_cai = '[[c'.$s.$entitisedname.$s.'i]]';
                            $efilter_ca = '[[c'.$s.$entitisedname.']]';
                            $efilter_ac = '[['.$entitisedname.$s.'c]]';
                            // If name has some entity (&amp; &quot; &lt; &gt;) add that filter too. MDL-17545.
                            //only activity
                            self::$activitylist[$cm->id.'-e'] = new filterobject($efilter_a, $href_tag_begin, $href_tag_end, false, true, $cmentitisedname);
                            
                            //with icon no checkbox
                            self::$activitylist[$cm->id.'-ai'.'-e'] = new filterobject($efilter_ai, $cmname, '', false, true, ' ');
                            self::$activitylist[$cm->id.'-ia'.'-e'] = new filterobject($efilter_ia, $cmname, '', false, true, ' ');
                            
                            //with icon checkbox right side
                            self::$activitylist[$cm->id.'-aic'.'-e'] = new filterobject($efilter_aic, $cmname, $cmcompletion, false, true, ' ');
                            self::$activitylist[$cm->id.'-aci'.'-e'] = new filterobject($efilter_aci, $cmname, $cmcompletion, false, true, ' ');
                            self::$activitylist[$cm->id.'-iac'.'-e'] = new filterobject($efilter_iac, $cmname, $cmcompletion, false, true, ' ');
                            
                            //with icon checkbox left side
                            self::$activitylist[$cm->id.'-ica'.'-e'] = new filterobject($efilter_ica, $cmcompletion, $cmname, false, true, ' ');
                            self::$activitylist[$cm->id.'-cia'.'-e'] = new filterobject($efilter_cia, $cmcompletion, $cmname, false, true, ' ');
                            self::$activitylist[$cm->id.'-cai'.'-e'] = new filterobject($efilter_cai, $cmcompletion, $cmname, false, true, ' ');
                            
                            //without icon checkbox left/right
                            self::$activitylist[$cm->id.'-ca'.'-e'] = new filterobject($efilter_ca, $cmcompletion.$href_tag_begin, $href_tag_end, false, true, $cmentitisedname);
                            self::$activitylist[$cm->id.'-ac'.'-e'] = new filterobject($efilter_ac, $href_tag_begin, $href_tag_end.$cmcompletion, false, true, $cmentitisedname);
                        }
                    }
                }
            }
        }

        $filterslist = array();
        if (self::$activitylist) {
            $cmid = $this->context->instanceid;
            if ($this->context->contextlevel == CONTEXT_MODULE && isset(self::$activitylist[$cmid])) {
                // remove filterobjects for the current module
                $filterslist = array_values(array_diff_key(self::$activitylist, array($cmid => 1, $cmid.'-e' => 1)));
            } else {
                $filterslist = array_values(self::$activitylist);
            }
        }

        if ($filterslist) {
            return $text = filter_phrases($text, $filterslist);
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
