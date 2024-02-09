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
    protected $sectionslist = array();
    /** @var array */
    protected $cmdatalist = array();
    /** @var object */
    protected $page = null;
    /** @var object */
    protected $dao = null;
    /** @var object */
    protected $context = null;
    /** @var boolean */
    protected $is_teacher = false;
    /** @var int */
    protected $courseid = 0;
    /** @var string */
    protected $DEFAULT_TARGET = '_self';
    /** @var object */
    protected $stats = null;
    /**
     * Setup function loads teachers and activities.
     *
     * {@inheritDoc}
     * @see moodle_text_filter::setup()
     * @param object $page
     * @param object $context
     */
    public function setup($page, $context) {
        $this->context = $context;
        $this->page = $page;

        if (isset($_GET['autolinkpopup'])){
            $page->set_pagelayout('popup');
        }

        // this filter is only applied where the courseId is greater than 1, it means, a real course.
        $coursectx = $this->context->get_course_context(false);
        if (!$coursectx) {
            return;
        }

        $this->courseid = $coursectx->instanceid;
        if($this->courseid <= 1){
            return;
        }

        $this->dao = filter_recitactivity_dao_factory::getInstance()->getDAO();

        $this->load_course_teachers($this->courseid);
        $this->load_data();
        $this->setStats();
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
     * @return section_info
     */
    protected function getSectionByName($name){
        foreach ($this->sectionslist as $section) {
            $sectionname = (empty($section->name) ? strval($section->section) : format_string($section->name));            

            if ($sectionname == $name || get_string('section') . strval($section->section) == $name) {// Used for atto plugin, if no name, sectionX
                return $section;
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
     * Load course modules list
     * 
     */
    protected function load_data() {
        global $USER;

        $modules = get_fast_modinfo($this->courseid);

        $this->cmdatalist = array();

        if (empty($modules->cms)) {
            return null;
        }

        $this->sectionslist = $modules->get_section_info_all();
        $this->load_cm_completions();

        $avoidModules = array("label");

        foreach ($modules->cms as $cm) {
            if (in_array($cm->__get('modname'), $avoidModules)) {
                continue;
            }

            if (!$cm->has_view()) {
                continue;
            }

            // Avoid empty or unlinkable activity names.
            $rawname = s(trim(strip_tags($cm->__get('name'))));
            if (empty($rawname) || ($cm->deletioninprogress == 1)) {
                continue;
            }            

            $cmData = new stdClass();
            $cmData->rawname = $rawname;
            $cmData->currentname = trim($cm->__get('name'));
            $cmData->cmInfo = $cm;

            // Row not present counts as 'not complete'
            $cmData->completion = new stdClass();
            $cmData->completion->id = 0;
            $cmData->completion->coursemoduleid = $cm->__get('id');
            $cmData->completion->userid = $USER->id;
            $cmData->completion->completionstate = 0;
            $cmData->completion->viewed = 0;
            $cmData->completion->overrideby = null;
            $cmData->completion->timemodified = 0;           

            if (isset($this->cmcompletions[$cm->__get('id')])) {
                $cmData->completion = $this->cmcompletions[$cm->__get('id')];
            }

            $cmData->isrestricted = (!$cm->__get('uservisible') || !empty($cm->availableinfo) || ($cm->__get('visible') == 0));
            if ($this->is_teacher) {
                $cmData->isrestricted = false;
            }

            $this->cmdatalist[] = $cmData;
        }
    }

    protected function setStats(){
        $this->stats = new stdClass();
        $this->stats->section = array();
        $this->stats->course = new stdClass();
        $this->stats->course->nbCmTotal = 0;
        $this->stats->course->nbCmCompleted = 0;

        foreach($this->cmdatalist as $item) {
            if(!isset($this->stats->section[$item->cmInfo->section])){
                $this->stats->section[$item->cmInfo->section] = new stdClass();
                $this->stats->section[$item->cmInfo->section]->nbCmTotal = 0;
                $this->stats->section[$item->cmInfo->section]->nbCmCompleted = 0;
            }

            $this->stats->section[$item->cmInfo->section]->nbCmTotal++;
            $this->stats->course->nbCmTotal++;

            if($this->getCmCompletion($item->cmInfo, $item->completion) == 2){
                $this->stats->section[$item->cmInfo->section]->nbCmCompleted++;
                $this->stats->course->nbCmCompleted++;
            }
        }
    }

    protected function getCmData($name){
        foreach($this->cmdatalist as $item) {
            if ($name == $item->cmInfo->__get('name')) {
                return $item;
            }
        }

        return null;
    }
    
    protected function get_autolink($cmdata, $options = array()) {
        $url = $cmdata->cmInfo->__get('url');
        //if (!$mod->is_visible_on_course_page() || !$url) {
        if (!$url) {
            // Nothing to be displayed to the user.
            return "";
        }

        //Accessibility: for files get description via icon, this is very ugly hack!
        $instancename = $cmdata->cmInfo->__get('name'); //$mod->get_formatted_name();
        
        $title = $instancename;
        if (isset($options['title'])){
            $title = $options['title'];
        } 

        $class = '';
        if (isset($options['class'])){
            $class = $options['class'];
        } 

        if (!isset($options['target'])){
            $options['target'] = $this->DEFAULT_TARGET; 
        } 


        $activityicon = "";
        if(isset($options['icon']) && $options['icon'] == true){
            $activityicon = html_writer::empty_tag('img', array('src' => $cmdata->cmInfo->get_icon_url(), 'class' => 'iconlarge activityicon', 'alt' => '', 
            'role' => 'presentation', 'aria-hidden' => 'true'));
        }
        
        $restrictioninfo = "";

        if ($cmdata->isrestricted) {
            $attributes = array('class' => 'disabled '.$class, 'title' => $title);

            $messageRestricted = "";
            if ($cmdata->cmInfo->availableinfo){
                $messageRestricted = htmlspecialchars(\core_availability\info::format_info($cmdata->cmInfo->availableinfo, $this->page->course->id));
            }
            else if ($cmdata->cmInfo->__get('visible') == 0) {
                $messageRestricted = get_string('hiddenfromstudents');
            }
            
            if (strlen($messageRestricted) > 0) {
                $restrictioninfo .= "<button type='button' class='btn btn-sm btn-link' data-html='true' data-container='body' title='".get_string('restricted')."' data-toggle='popover' data-placement='bottom' data-content=\"$messageRestricted\">";
                $restrictioninfo .= "<i class='fa fa-info-circle'></i>";
                $restrictioninfo .= "</button>";
            }
        }
        else{
            if (isset($options['popup'])){
                $url = 'javascript:recit.filter.autolink.popupIframe("'.$url.'&autolinkpopup=1", "'.$options['popupclass'].'");';
            }
    
            if (isset($options['completion']) && ($options['completion'] == true)){
                $activityicon = $options['cmcompletion'].' '.$activityicon;
            }

            $attributes = array('class' => 'autolink '.$class, 'title' => $title, 'href' => $url, 'target' => $options['target']);
        }

        // Get on-click attribute value if specified and decode the onclick - it
        // has already been encoded for display (puke).
        $onclick = htmlspecialchars_decode($cmdata->cmInfo->__get('onclick'), ENT_QUOTES);
        if (!empty($onclick)){
            $attributes['onclick'] = $onclick;
        }

        $activityName = html_writer::tag('span', $title, array('class' => 'instancename'));

        return html_writer::tag('a', $activityicon . $activityName . $restrictioninfo, $attributes);
    }

    /**
     * Extract activity by name.
     *
     * @param string $name
     * @param string $param
     * @param array $options
     * @return object $result
     */
    protected function get_course_activity($name, $options = array()) {
        $cmData = $this->getCmData($name);

        if($cmData == null){
            return null;
        }

        $result = new stdClass();
        $result->cmData = $cmData; 
        $result->output = new stdClass();
        $result->output->state = true;

        $title = $result->cmData->rawname;
        if (isset($options['title'])) {
            $title = $options['title'];
        }

        $class = '';
        if (isset($options['class'])) {
            $class = $options['class'];
        }

        if (isset($options['roles'])) {
            if(!$this->validateUserRoles($options['roles'])){
                $result->output->state = false;
                return $result;
            }
        }
           
        //$currentname = trim($cm->__get('name'));
        $result->output->cmcompletion = $this->getCmCompletionCheckbox($result->cmData->cmInfo, $result->cmData->completion);
        $options['cmcompletion'] = $result->output->cmcompletion;
        
        $result->output->autolink = $this->get_autolink($result->cmData, $options);        

        return $result;
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
        // This filter is only applied where the courseId is greater than 1, it means, a real course.
        if ($this->courseid <= 1) {
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
                    $attributes['popupclass'] = '';
                    unset($items[$i]);
                }
                if ($param == 'p16x9'){
                    $attributes['popup'] = true;
                    $attributes['popupclass'] = 'recitautolink_popup_16x9';
                    unset($items[$i]);
                }
                
                // In case of /b link to new window
                if($param == 'b'){
                    $attributes['target'] = '_blank';
                    unset($items[$i]);
                }
                
                 // In case of /role:role1,role2
                preg_match('/roles:[a-zA-Z,]+/', $param, $optionRoles, PREG_OFFSET_CAPTURE); 
               
                if(count($optionRoles) > 0){
                    $optionRoles = substr($optionRoles[0][0], 6, strlen($optionRoles[0][0]));
                    $attributes['roles'] = explode(",", $optionRoles);
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
                    $attributes['completion'] = false;
                    $attributes['icon'] = true;
                    $this->filterOptionLink($complement, $attributes, $match, $result);
                    break;
                case "c":
                    $attributes['completion'] = true;
                    $attributes['icon'] = false;
                    $this->filterOptionLink($complement, $attributes, $match, $result);
                    break;
                case "ci":
                case "ic":
                    $attributes['completion'] = true;
                    $attributes['icon'] = true;
                    $this->filterOptionLink($complement, $attributes, $match, $result);
                    break;
                case "l":
                    $attributes['completion'] = false;
                    $attributes['icon'] = false;
                    $this->filterOptionLink($complement, $attributes, $match, $result);
                    break;
                case "s":
                    $this->filterOptionSectionLink($complement, $attributes, $match, $result);
                    break;
                case "spb":
                    $this->filterOptionSectionProgressBar($complement, $attributes, $match, $result);
                    break;
                case "cpb":
                    $this->filterOptionCourseProgressBar($attributes, $match, $result);
                    break;
                case "h5p":
                    $this->filterOptionH5P($complement, $match, $result);
                    break;
                case "d":
                    $this->filterOptionUserData($complement, $match, $result);
                    break;
                case "f": 
                    $this->filterOptionFeedback($complement, $attributes, $match, $result);   
                    break;
            }
        }

        return $result;
    }

    protected function validateUserRoles($roles){
        global $USER;

        $coursecontext = \context_course::instance($this->page->course->id);
        
        $userRoles = get_user_roles($coursecontext, $USER->id);

        foreach($userRoles as $role){
            if(in_array($role->shortname, $roles)){
                return true;
            }
        }

        return false;
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
     * @return int 0 = no completion, 1 = not completed, 2 = completed
     */
    public function getCmCompletion(cm_info $mod, $completiondata) {
        global $CFG;
        $course = $this->page->course;

        if (!$mod->__get('uservisible')) {
            return 0;
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
            return 0;
        }

        $result = 1;

        if ($completion == COMPLETION_TRACKING_MANUAL) {
            switch ($completiondata->completionstate) {
                case COMPLETION_INCOMPLETE:
                    $result = 1;
                    break;
                case COMPLETION_COMPLETE:
                    $result = 2;
                    break;
            }
        } else { // Automatic.
            switch ($completiondata->completionstate) {
                case COMPLETION_INCOMPLETE:
                case COMPLETION_COMPLETE_FAIL:
                    $result = 1;
                    break;
                case COMPLETION_COMPLETE:
                case COMPLETION_COMPLETE_PASS:
                    $result = 2;
                    break;
            }
        }
        
        return $result;
    }

    protected function getCmCompletionCheckbox(cm_info $mod, $completiondata){
        global $PAGE;

        $output = '';
        if ($PAGE->user_is_editing()) {
            $output .= html_writer::span('&nbsp;', 'filler');
        }

        $cmCompletion = $this->getCmCompletion($mod, $completiondata);
        if($cmCompletion != 0){
            $completionicon = ($cmCompletion == 2 ? 'fa-check-square-o' : 'fa-square-o');
            $output .= "<i class='fa $completionicon'></i>";
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

    protected function shouldHideIntCode($activity, $match, &$result){
        if(!$activity->output->state){
            $result = str_replace($match, "", $result);
            return true;
        }

        return false;
    }

    protected function filterOptionLink($complement, $attributes, $match, &$result){
        $activity = $this->get_course_activity($complement, $attributes);
        if ($activity != null) {
            if(!$this->shouldHideIntCode($activity, $match, $result)){
                $result = str_replace($match, $activity->output->autolink, $result);
            }
        }
    }

    protected function filterOptionSectionLink($name, $options, $match, &$result){
        global $PAGE, $COURSE;

        $section = $this->getSectionByName($name);

        if($section == null){
            return;
        }

        $sectionname = (empty($section->name) ?  get_string('section') . ' ' . strval($section->section) : format_string($section->name));
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
        
        $link = html_writer::link($href, $sectionname, $tagattr);
        
        $result = str_replace($match, "<span>$link$availableInfo</span>", $result);
    }

    protected function filterOptionH5P($name, $match, &$result){
        // Return all content bank content that matches the search criteria and can be viewed/accessed by the user.
        $coursecontext = \context_course::instance($this->page->course->id);
        $list = $this->get_h5p_search_contents($name, $coursecontext->id);
        if (!isset($list[0])) {
            return;
        }

        $h5p = $list[0];
        $source = json_decode(base64_decode($h5p['source']));
        autoloader::register();

        $url = \moodle_url::make_pluginfile_url($source->contextid, 'contentbank', 'public', $source->itemid.'/'. $source->filename, null, null);
        $url = $url->out();
        $h5p = "<div class='h5p-placeholder' contenteditable='false'>$url</div>";
        $result = str_replace($match, $h5p, $result);
    }

    protected function filterOptionUserData($complement, $match, &$result){
        global $USER, $OUTPUT, $COURSE;

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
    }

    protected function filterOptionFeedback($complement, $attributes, $match, &$result){        
        $cmData = $this->getCmData($complement);

        if($cmData == null){
            return;
        }

        $cmCompletion = $this->getCmCompletion($cmData->cmInfo, $cmData->completion);

        // cm is completed, nothing to display
        if($cmCompletion == 2){
            $result = str_replace($match, "", $result);
            return;
        }

        // cm is not available, nothing to display
        if(!$this->isCmAvailable($cmData->cmInfo)){ 
            $result = str_replace($match, "", $result);
            return;
        }

        $pageContent = $this->getModulePageContent($cmData->cmInfo);

        $dismissButton = "";

        // cm is not completed or has no completion option
        if($cmCompletion == 1){
            $dismissButton = '<div class="d-flex justify-content-end"><button class="btn btn-sm text-nowrap btn-outline-secondary m-2" data-action="toggle-manual-completion" data-toggletype="manual:mark-done" 
            data-withavailability="1" data-cmid="'.$cmData->cmInfo->id.'"  data-activityname="Ignore"  
            title='.get_string('dismissMsg','filter_recitactivity').'  aria-label='.get_string('dismissMsg','filter_recitactivity').'>'.get_string('dismissMsg','filter_recitactivity').'</button></div>';           
        }

        if (isset($attributes['popup'])){
            $result = str_replace($match, "<div style='display:none' data-filter-recitactivity='feedback'><div>$pageContent</div><div>$dismissButton</div></div>", $result);
        }
        else{  
            $cssClasses = (isset($attributes['class']) ? $attributes['class'] : "");
            $html = "<div class='$cssClasses'>$pageContent $dismissButton</div>";
            $result = str_replace($match, $html, $result);
        }
    }

    protected function filterOptionSectionProgressBar($complement, $attributes, $match, &$result){
        $section = $this->getSectionByName($complement);

        if($section == null){
            return;
        }

        if(($this->stats == null) || (!isset($this->stats->section[$section->id]))){
            return;
        }

        $sectionPct = round($this->stats->section[$section->id]->nbCmCompleted / $this->stats->section[$section->id]->nbCmTotal * 100);

        $cssClasses = (isset($attributes['class']) ? $attributes['class'] : "");
        $html = 
            "<div class=' $cssClasses'>
                <div class='progress'>
                    <div class='progress-bar progress-bar-striped' role='progressbar' style='width: $sectionPct%' aria-valuenow='$sectionPct' 
                    aria-valuemin='0' aria-valuemax='100'>$sectionPct%</div>
                </div>
            </div>";

        $result = str_replace($match, $html, $result);
    }

    protected function filterOptionCourseProgressBar($attributes, $match, &$result){
        if($this->stats == null){
            return;
        }

        $pct = round($this->stats->course->nbCmCompleted / $this->stats->course->nbCmTotal * 100,0);

        $cssClasses = (isset($attributes['class']) ? $attributes['class'] : "");
        $html = 
            "<div class=' $cssClasses'>
                <div class='progress'>
                    <div class='progress-bar progress-bar-striped' role='progressbar' style='width: $pct%' aria-valuenow='$pct' 
                    aria-valuemin='0' aria-valuemax='100'>$pct%</div>
                </div>
            </div>";

        $result = str_replace($match, $html, $result);
    }

    protected function getModulePageContent(cm_info $cmInfo){
        global $DB;

        if (!$cm = get_coursemodule_from_id('page', $cmInfo->id)) {
            throw new \moodle_exception('invalidcoursemodule');
        }

        $page = $DB->get_record('page', array('id'=>$cm->instance), '*', MUST_EXIST);
        $context = context_module::instance($cm->id);
        
        $content = file_rewrite_pluginfile_urls($page->content, 'pluginfile.php', $context->id, 'mod_page', 'content', $page->revision);
        $formatoptions = new stdClass; 
        $formatoptions->noclean = true;
        $formatoptions->overflowdiv = true;
        $formatoptions->context = $context;
        return format_text($content, $page->contentformat, $formatoptions);
    }

    protected function isCmAvailable(cm_info $cmInfo){
        global $USER;

        $info = new \core_availability\info_module($cmInfo);

        $str = "";
        return $info->is_available($str, false, $USER->id);
    }
}
