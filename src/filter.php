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

// Activity name filtering.

/**
 * This filter must be put before Auto-linking with Manage Filters to work properly.
 *
 * @package    filter_recitactivity
 * @copyright  2019 RECIT
 * @license    {@link http://www.gnu.org/licenses/gpl-3.0.html} GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__."/classes/dao.php");
require_once(__DIR__."/lib.php");

require_once(__DIR__ . '/../../h5p/lib.php');
use core_h5p\local\library\autoloader;

/**
 * Main class for filtering text.
 *
 * Attention: do not utilise the global variables $PAGE and $COURSE. Instead, use $this->page and $this->page->course.
 * When the filter is used by some ajax service (like TreeTopics) the global variables are not set as it should but $this->page is so.
 */
class filter_recitactivity extends moodle_text_filter {
    /** @var array teachers list */
    protected $teacherslist = array();
    /** @var array */
    protected $cmcompletions = array();
    /** @var array */
    protected $modules = array();
    /** @var array */
    protected $sectionslist = array();
    /** @var object */
    protected $page = null;
    /** @var object */
    protected $dao = null;
    /** @var object */
    protected $context = null;
    /** @var boolean */
    protected $is_teacher = false;
    /** @var string */
    protected $DEFAULT_TARGET = '_self';

    /**
     * Setup function loads teachers and activities.
     *
     * {@inheritDoc}
     * @see moodle_text_filter::setup()
     * @param object $page
     * @param object $context
     */
    public function setup($page, $context) {
        global $CFG;
        $this->context = $context;
        $this->page = $page;

        // this filter is only applied where the courseId is greater than 1, it means, a real course.
        if($this->page->course->id <= 1){
            return;
        }

        $this->dao = filter_recitactivity_dao_factory::getInstance()->getDAO();
        $this->modules = get_fast_modinfo($this->page->course);
        $this->sectionslist = $this->modules->get_section_info_all();

        $this->load_course_teachers($this->page->course->id);

        if (isset($_GET['autolinkpopup'])){
            $page->set_pagelayout('popup');
        }
    }

    /**
     * This function gets all teachers for a course.
     *
     * @param int $courseid
     */
    protected function load_course_teachers($courseid) {
        global $USER;

        if (count($this->teacherslist) > 0) {
            return;
        }
        	
        $showteacherbygroup = get_config('filter_recitactivity', 'teacherbygroup');
		$this->teacherslist = $this->dao->load_course_teachers($courseid, $showteacherbygroup);
		
        foreach($this->teacherslist as $item){
            if ($USER->id == $item->id){
                $this->is_teacher = true;
            } 
        }
    } 

    /**
     * This function gets section by name
     *
     * @param string $name
     * @param array $options
     */
    protected function get_section($name, $options = array()){
        global $CFG, $PAGE, $COURSE;

        foreach ($this->sectionslist as $section) {
            $sectionname = (empty($section->name) ? strval($section->section) : $section->name);            

            if ($sectionname == $name || get_string('section') . strval($section->section) == $name) {// Used for atto plugin, if no name, sectionX

                $sectionname = (empty($section->name) ?  get_string('section') . ' ' . strval($section->section) : $section->name);
                $title = $sectionname;
                if (isset($options['title'])) {
                    $sectionname = $options['title'];
                    $title = $sectionname.' - '.$name;
                }
                $class = '';
                if (isset($options['class'])) {
                    $class = $options['class'];
                }
                if (!isset($options['target'])) {
                    $options['target'] = $this->DEFAULT_TARGET;
                }
                $anchor = sprintf("%s-%ld", strtolower(get_string('section')), $section->section);
                
                $isrestricted = (!$this->is_teacher) && !is_null($section->availability) && !$section->available;

                $availableInfo = "";
                if ($isrestricted) {
                    $courseFormat = course_get_format($COURSE);
                    $renderer = $courseFormat->get_renderer($PAGE);
                    $infoMsg = $renderer->section_availability($section);
                    $infoMsg = htmlspecialchars($infoMsg);
                    
                    $availableInfo = sprintf("<button type='button' class='btn btn-sm btn-link' data-html='true' title='%s' data-container='body' data-toggle='popover' data-placement='bottom' data-content=\"%s\">", get_string('restricted'), $infoMsg);
                    $availableInfo .= "<i class='fa fa-info-circle'></i>";
                    $availableInfo .= "</button>";
                    $class .= " disabled";
                }
                
                $tagattr = array('class' => 'autolink '.$class, 'title' => $title, 'target' => $options['target'], 'onclick' => 'this.search == document.location.search && setTimeout(location.reload.bind(location), 50)');
                $href = "#";
                $href = new moodle_url('/course/view.php', array('id' => $this->page->course->id, 'section' => $section->section), $anchor);
                
                $result = html_writer::link($href, $sectionname, $tagattr);

                return "<span>$result$availableInfo</span>";
            }
        }

        return null;
    }

    /**
     * This function loads module completion
     */
    protected function load_cm_completions() {
        if(count($this->cmcompletions) > 0){
            return;
        }

        $this->cmcompletions = $this->dao->load_cm_completions($this->page->course->id);
    }

    /**
     * Get array variable course activities list
     * 
     * @param string $activityname
     * @param string $param
     * @param array $options
     */
    protected function load_course_activities_list($activityname, $param = '', $options = array()) {
        global $USER;

        if (empty($this->modules->cms)) {
            return null;
        }
        $avoidModules = array("label");

        foreach ($this->modules->cms as $cm) {
            if (in_array($cm->__get('modname'), $avoidModules)) {
                continue;
            }

            // load only the wanted activity
            if ($activityname != $cm->__get('name')) {
                continue;
            }

            if (!$cm->has_view()) {
                continue;
            }

            $name = s(trim(strip_tags($cm->__get('name'))));
            $title = $name;
            if (isset($options['title'])) {
                $title = $options['title'];
            }
            $class = '';
            if (isset($options['class'])) {
                $class = $options['class'];
            }
            $currentname = trim($cm->__get('name'));

            // Avoid empty or unlinkable activity names.
            if (empty($name) || ($cm->deletioninprogress == 1)) {
                continue;
            }
            $cmname = $this->get_cm_name($cm, $options);

            // Row not present counts as 'not complete'
            $completiondata = new stdClass();
            $completiondata->id = 0;
            $completiondata->coursemoduleid = $cm->__get('id');
            $completiondata->userid = $USER->id;
            $completiondata->completionstate = 0;
            $completiondata->viewed = 0;
            $completiondata->overrideby = null;
            $completiondata->timemodified = 0;

            if (isset($this->cmcompletions[$cm->__get('id')])) {
                $completiondata = $this->cmcompletions[$cm->__get('id')];
            }

            $cmcompletion = $this->course_section_cm_completion($cm, $completiondata);
            $isrestricted = (!$cm->__get('uservisible') || !empty($cm->availableinfo) || ($cm->__get('visible') == 0));
            if ($this->is_teacher) {
                $isrestricted = false;
            }

            $courseactivity = new stdClass();
            $courseactivity->cmname = $cmname;
            $courseactivity->currentname = $currentname;
            $courseactivity->cmcompletion = $cmcompletion;
            $courseactivity->id = $cm->__get('id');
            $courseactivity->uservisible = $cm->uservisible;

            if ($isrestricted) {
                $courseactivity->href_tag_begin = html_writer::start_tag('a', array('class' => "$class disabled ",
                    'title' => $title, 'href' => '#'));
                $courseactivity->href_tag_end = '</a>';

                $messageRestricted = "";
                if ($cm->availableinfo){
                    $messageRestricted = htmlspecialchars(\core_availability\info::format_info($cm->availableinfo, $this->page->course->id));
                }
                else if ($cm->__get('visible') == 0) {
                    $messageRestricted = get_string('hiddenfromstudents');
                }
                
                if (strlen($messageRestricted) > 0) {
                    $courseactivity->href_tag_end .= "<button type='button' class='btn btn-sm btn-link' data-html='true' data-container='body' title='".get_string('restricted')."' data-toggle='popover' data-placement='bottom' data-content=\"$messageRestricted\">";
                    $courseactivity->href_tag_end .= "<i class='fa fa-info-circle'></i>";
                    $courseactivity->href_tag_end .= "</button>";
                }
                
                $courseactivity->cmname = "<a class='disabled' href='#'>$title</a>";
                $courseactivity->cmcompletion = "";
            }
            else{
                $tagattr = array('class' => 'autolink '.$class, 'title' => $title, 'href' => $cm->__get('url'), 'target' => $options['target']);
                if (isset($options['popup'])){
                    $tagattr['href'] = 'javascript:recit.filter.autolink.popupIframe("'.$tagattr['href'].'&autolinkpopup=1");';
                }
                $courseactivity->href_tag_begin = html_writer::start_tag('a', $tagattr);
                $courseactivity->href_tag_end = '</a>';
            }

            return $courseactivity;
        }

        return null;
    }

    /**
     * This function gets course module name
     *
     * @param cm_info $mod
     * @param array $options
     */
    protected function get_cm_name(cm_info $mod, $options = array()) {
        $output = '';
        $url = $mod->__get('url');
        //if (!$mod->is_visible_on_course_page() || !$url) {
        if (!$url) {
            // Nothing to be displayed to the user.
            return $output;
        }

        //Accessibility: for files get description via icon, this is very ugly hack!
        $instancename = $mod->__get('name'); //$mod->get_formatted_name();
        $altname = $mod->__get('modfullname');
        
        $title = $instancename;
        if (isset($options['title'])) $title = $options['title'];
        $class = '';
        if (isset($options['class'])) $class = $options['class'];
        if (!isset($options['target'])) $options['target'] = $this->DEFAULT_TARGET; 
        // Avoid unnecessary duplication: if e.g. a forum name already
        // includes the word forum (or Forum, etc) then it is unhelpful
        // to include that in the accessible description that is added.
        if (false !== strpos(core_text::strtolower($instancename),
                core_text::strtolower($altname))) {
            $altname = '';
        }
        // File type after name, for alphabetic lists (screen reader).
        if ($altname) {
            $altname = get_accesshide(' '.$altname);
        }

        // Get on-click attribute value if specified and decode the onclick - it
        // has already been encoded for display (puke).
        $onclick = htmlspecialchars_decode($mod->__get('onclick'), ENT_QUOTES);

        // Display link itself.
        $activitylink = html_writer::empty_tag('img', array('src' => $mod->get_icon_url(), 'class' => 'iconlarge activityicon', 'alt' => '', 'role' => 'presentation', 'aria-hidden' => 'true')) . html_writer::tag('span', $title, array('class' => 'instancename'));
        if ($mod->__get('uservisible')) {
            if (isset($options['popup'])){
                $url = 'javascript:recit.filter.autolink.popupIframe("'.$url.'&autolinkpopup=1");';
            }
            $attributes = array('class' => 'autolink '.$class, 'title' => $title, 'href' => $url, 'target' => $options['target']);
            if (!empty($onclick)){
                $attributes['onclick'] = $onclick;
            }
            $output .= html_writer::tag('a', $activitylink, $attributes);
        } else {
            // We may be displaying this just in order to show information
            // about visibility, without the actual link ($mod->is_visible_on_course_page()).
            $output .= html_writer::tag('div', $activitylink);
        }
        return $output;
    }

    /**
     * Extract activity by name.
     *
     * @param string $name
     * @param string $param
     * @param array $options
     * @return $item from array course activities list|null
     */
    protected function get_course_activity($name, $param = '', $options = array()) {
        return $this->load_course_activities_list($name, $param, $options);
    }

    /**
     * Main filter function.
     *
     * {@inheritDoc}
     * @see moodle_text_filter::filter()
     * @param string $text
     * @param array $options
     */
    public function filter($text, array $options = array()) {
        global $USER, $OUTPUT, $COURSE;

        // This filter is only applied where the courseId is greater than 1, it means, a real course.
        if ($this->page->course->id <= 1) {
            return $text;
        }

        // Check if we need to build filters.
        if (strpos($text, '[[') === false or !is_string($text) or empty($text)) {
            return $text;
        }

        $matches = array();

        $sep = get_config('filter_recitactivity', 'character');
        if(empty($sep)){
            $sep = "/"; // Char to split string into parameters. Default : /
        }

        preg_match_all('#(\[\[)([^\]]+)(\]\])#', $text, $matches);

        $matches = $matches[0]; // It will match the wanted RE, for instance [[i/Activité 3]].


        $result = $text;
        foreach ($matches as $match) {
            $attributes = array();


            $attributes['target'] = $this->DEFAULT_TARGET;
            $items = explode($sep, $match);

            // Build options array
            foreach ($items as $i => $item){
                $complement = str_replace("]]", "", $item);
                $param = str_replace("[[", "", $item);
                
                // In case of /class:name
                if(substr($param, 0, 5) == 'class' && strpos($param, ':') !== false && strpos($param, '"') !== false){
                    $param = str_replace('"', '', $param);
                    $str = explode(":", $param);
                    $attributes['class'] = $str[1];
                    unset($items[$i]);
                }
      
                // In case of /desc:name
                if(substr($param, 0, 4) == 'desc' && strpos($param, ':') !== false && strpos($param, '"') !== false){
                    $param = str_replace('"', '', $param);
                    $str = explode(":", $param);
                    $attributes['title'] = $str[1];
                    unset($items[$i]);
                }

                // In case of /p popup
                if ($param == 'p'){
                    $attributes['popup'] = true;
                    unset($items[$i]);
                }
                
                // In case of /b link to new window
                if($param == 'b'){
                    $attributes['target'] = '_blank';
                    unset($items[$i]);
                }
            }


            // In case of "[[ActivityName]]"
            if (count($items) == 1 && isset($items[0]) && strpos($items[0], '[[') !== false) {
                $items[0] = str_replace("[[", "", $items[0]);
                $complement = str_replace("]]", "", $items[0]);
                $param = "l";
            } else if (count($items) == 1){
                $param = "l";
            } else {
                $complement = str_replace("]]", "", array_pop($items));
                $param = str_replace("[[", "", implode("", $items));
            }

            switch ($param) {
                case "i":
                    $activity = $this->get_course_activity($complement, $param, $attributes);
                    if ($activity != null) {
                        $url = $activity->cmname;
                        $result = str_replace($match, $url, $result);
                    }
                    break;
                case "c":
                    $this->load_cm_completions();
                    $activity = $this->get_course_activity($complement, $param, $attributes);
                    if ($activity != null) {
                        $title = $activity->currentname;
                        if (isset($attributes['title'])) $title = $attributes['title'];
                        $result = str_replace($match, sprintf("%s %s %s %s", $activity->cmcompletion,
                                $activity->href_tag_begin, $title, $activity->href_tag_end), $result);
                    }
                    break;
                case "ci":
                case "ic":
                    $this->load_cm_completions();
                    $activity = $this->get_course_activity($complement, $param, $attributes);
                    if ($activity != null) {
                        $result = str_replace($match, sprintf("%s %s", $activity->cmcompletion, $activity->cmname), $result);
                    }
                    break;
                case "l":
                    $activity = $this->get_course_activity($complement, $param, $attributes);
                    if ($activity != null) {
                        $title = $activity->currentname;
                        if (isset($attributes['title'])) $title = $attributes['title'];
                        $result = str_replace($match, sprintf("%s%s%s", $activity->href_tag_begin, $title,
                                $activity->href_tag_end), $result);
                    }
                    break;
                case "s":
                    $link = $this->get_section($complement, $attributes);
                    if ($link != null) {
                        $result = str_replace($match, $link, $result);
                    }
                    break;
                case "h5p":
                    $h5p = $this->getH5PFromName($complement);
                    if ($h5p){
                        $result = str_replace($match, $h5p, $result);
                    }
                    break;
                case "d":

                    if ($complement == "user.firstname") {
                        $result = str_replace($match, $USER->firstname, $result);
                    } else if ($complement == "user.lastname") {
                        $result = str_replace($match, $USER->lastname, $result);
                    } else if ($complement == "user.email") {
                        $result = str_replace($match, $USER->email, $result);
                    } else if ($complement == "user.picture") {
                        $picture = $OUTPUT->user_picture($USER, array('courseid' => $this->page->course->id, 'link' => false));
                        $result = str_replace($match, $picture, $result);
                    } else if ($complement == "course.shortname") {
                        $result = str_replace($match, $COURSE->shortname, $result);
                    } else if ($complement == "course.fullname") {
                        $result = str_replace($match, $COURSE->fullname, $result);
                    } else {
                        if (empty($this->teacherslist) && substr($complement, 0, 8) == "teacher1"){                            
                            $result = str_replace($match, "($match <button type='button' class='btn btn-sm btn-link' data-html='true' title='' data-container='body' data-toggle='popover' data-placement='bottom' 
                                                                    data-content='".get_string('noteacheringroup','filter_recitactivity')."' data-original-title=''><i class='fa fa-info-circle'></i></button></button>", $result);
                        }
                        foreach ($this->teacherslist as $index => $teacher) {
                            $nb = $index + 1;
                            if ($complement == "teacher$nb.firstname") {
                                $result = str_replace($match, $teacher->firstname, $result);
                            } else if ($complement == "teacher$nb.lastname") {
                                $result = str_replace($match, $teacher->lastname, $result);
                            } else if ($complement == "teacher$nb.email") {
                                $result = str_replace($match, $teacher->email, $result);
                            } else if ($complement == "teacher$nb.picture") {
                                $picture = $OUTPUT->user_picture($teacher, array('courseid' => $this->page->course->id,
                                    'link' => false));
                                $result = str_replace($match, $picture, $result);
                            }
                        }
                    }
                    break;
            }
        }

        return $result;
    }

    /**
     * Extract h5p by name.
     *
     * @param string $name
     */
    public function getH5PFromName($name){
        global $PAGE;
        // Return all content bank content that matches the search criteria and can be viewed/accessed by the user.
        $coursecontext = \context_course::instance($PAGE->course->id);
        $list = $this->get_h5p_search_contents($name, $coursecontext->id);
        if (!isset($list[0])) {
            return;
        }
        $h5p = $list[0];
        $source = json_decode(base64_decode($h5p['source']));
        autoloader::register();

        $url = \moodle_url::make_pluginfile_url($source->contextid, 'contentbank', 'public', $source->itemid.'/'. $source->filename, null, null);
        $url = $url->out();
        return "<div class='h5p-placeholder' contenteditable='false'>$url</div>";
    }

    /**
     * Search H5P in context
     *
     * @param string $search
     * @param int $contextid
     */
    public function get_h5p_search_contents($search, $contextid) {
        $contentbank = new \core_contentbank\contentbank();
        // Return all content bank content that matches the search criteria and can be viewed/accessed by the user.
        $contents = $contentbank->search_contents($search, $contextid);
        return array_reduce($contents, function($list, $content) {
            if ($contentnode = \repository_contentbank\helper::create_contentbank_content_node($content)) {
                $list[] = $contentnode;
            }
            return $list;
        }, []);
    }

    /**
     * Generate cm completion checkbox
     *
     * @param cm_info $mod
     * @param object $completiondata
     */
    public function course_section_cm_completion(cm_info $mod, $completiondata) {
        global $CFG, $PAGE;
        $course = $this->page->course;

        $output = '';
        if (!$mod->__get('uservisible')) {
            return $output;
        }
        
        $completion = $mod->__get('completion'); // Return course-module completion value

        // First check global completion
        if (!isset($CFG->enablecompletion) || $CFG->enablecompletion == COMPLETION_DISABLED) {
            $completion = COMPLETION_DISABLED;
        }
        else if ($course->enablecompletion == COMPLETION_DISABLED) {   // Check course completion
            $completion = COMPLETION_DISABLED;
        }
        
        if ($completion == COMPLETION_TRACKING_NONE) {
            if ($PAGE->user_is_editing()) {
                $output .= html_writer::span('&nbsp;', 'filler');
            }
            return $output;
        }

        $completionicon = '';

        if ($PAGE->user_is_editing()) {
            switch ($completion) {
                case COMPLETION_TRACKING_MANUAL :
                    $completionicon = 'manual-enabled';
                    break;
                case COMPLETION_TRACKING_AUTOMATIC :
                    $completionicon = 'auto-enabled';
                    break;
            }
        } else if ($completion == COMPLETION_TRACKING_MANUAL) {
            switch ($completiondata->completionstate) {
                case COMPLETION_INCOMPLETE:
                    $completionicon = 'manual-n' . ($completiondata->overrideby ? '-override' : '');
                    break;
                case COMPLETION_COMPLETE:
                    $completionicon = 'manual-y' . ($completiondata->overrideby ? '-override' : '');
                    break;
            }
        } else { // Automatic.
            switch ($completiondata->completionstate) {
                case COMPLETION_INCOMPLETE:
                    $completionicon = 'auto-n' . ($completiondata->overrideby ? '-override' : '');
                    break;
                case COMPLETION_COMPLETE:
                    $completionicon = 'auto-y' . ($completiondata->overrideby ? '-override' : '');
                    break;
                case COMPLETION_COMPLETE_PASS:
                    $completionicon = 'auto-pass';
                    break;
                case COMPLETION_COMPLETE_FAIL:
                    $completionicon = 'auto-fail';
                    break;
            }
        }
        if ($completionicon) {
            //$formattedname = html_entity_decode($mod->get_formatted_name(), ENT_QUOTES, 'UTF-8');
            $formattedname = html_entity_decode($mod->__get('name'), ENT_QUOTES, 'UTF-8');
            $imgalt = $formattedname;
            /*if ($completiondata->overrideby) {
                $args = new stdClass();
                $args->modname = $formattedname;
                $overridebyuser = \core_user::get_user($completiondata->overrideby, '*', MUST_EXIST);
                $args->overrideuser = fullname($overridebyuser);
                $imgalt = get_string('completion-alt-' . $completionicon, 'completion', $args);
            } else {
                $imgalt = get_string('completion-alt-' . $completionicon, 'completion', $formattedname);
            }*/

            if ($PAGE->user_is_editing()) {
                // When editing, the icon is just an image.
                $completionpixicon = new pix_icon('i/completion-'.$completionicon, $imgalt, '',
                    array('title' => $imgalt, 'class' => 'iconsmall'));
                $output .= html_writer::tag('span', $this->renderPixIcon($completionpixicon),
                    array('class' => 'autocompletion'));
            } else if ($completion == COMPLETION_TRACKING_MANUAL) {
                $newstate = $completiondata->completionstate == COMPLETION_COMPLETE ? COMPLETION_INCOMPLETE : COMPLETION_COMPLETE;
                // In manual mode the icon is a toggle form...

                // If this completion state is used by the
                // conditional activities system, we need to turn
                // off the JS.
                $extraclass = '';
                if (!empty($CFG->enableavailability) &&
                        core_availability\info::completion_value_used($course, $mod->__get('id'))) {
                    $extraclass = ' preventjs';
                }
                $output .= html_writer::start_tag('form', array('method' => 'post',
                    'action' => new moodle_url('/course/togglecompletion.php'),
                    'class' => 'togglecompletion'. $extraclass, 'style' => 'display: inline;'));
                $output .= html_writer::start_tag('div', array('style' => 'display: inline;'));
                $output .= html_writer::empty_tag('input', array(
                    'type' => 'hidden', 'name' => 'id', 'value' => $mod->__get('id')));
                $output .= html_writer::empty_tag('input', array(
                    'type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
                $output .= html_writer::empty_tag('input', array(
                    'type' => 'hidden', 'name' => 'modulename', 'value' => $formattedname));
                $output .= html_writer::empty_tag('input', array(
                    'type' => 'hidden', 'name' => 'completionstate', 'value' => $newstate));
                    
                $completionpixicon = new pix_icon('i/completion-'.$completionicon, $imgalt, '');

                $output .= html_writer::tag('button', $this->renderPixIcon($completionpixicon), array('class' => 'btn btn-link', 'aria-live' => 'assertive', 'style' => 'padding: 0px;'));
                $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('form');
            } else {
                // In auto mode, the icon is just an image.
                $completionpixicon = new pix_icon('i/completion-'.$completionicon, $imgalt, '',
                    array('title' => $imgalt));
                $output .= html_writer::tag('span', $this->renderPixIcon($completionpixicon),
                    array('class' => 'autocompletion'));
            }
        }
        return $output;

    }

    /**
     * Render pix icon
     *
     * @param pix_icon $icon
     */
    protected function renderPixIcon(pix_icon $icon) {
        global $OUTPUT;

        $template = $icon->export_for_template($OUTPUT);

        $attrs = array();
        foreach($template['attributes'] as $item){
            $attrs[] = sprintf("%s='%s'", $item['name'], $item['value']);
        }

        return sprintf("<img %s style='width: 16px; height: 16px;'/>", implode(" ", $attrs));
    }
}
