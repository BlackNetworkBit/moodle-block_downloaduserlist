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
 * vsuserlist block caps.
 *
 * @package    block_vsuserlist
 * @copyright  Daniel Neis <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
error_reporting(true);
defined('MOODLE_INTERNAL') || die();

class block_downloaduserlist extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_downloaduserlist');
    }

    function get_content() {
        global $CFG, $USER,$DB;

        if ($this->content !== null) {
            return $this->content;
        }
        $course=$this->page->course;
        $context= get_context_instance(CONTEXT_COURSE,$course->id);
        if(!has_capability('moodle/course:manageactivities',$context)){
        		return;
        }
        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }
        $this->content->text='<a target="_blank" href="/blocks/downloaduserlist/download.php?courseid=' . $course->id . '">Download</a>';      
        //user/view.php?id=3&course=2
        //$this->content->text = "Hier werde ich alles Anzeigen !";
		  /*


        // user/index.php expect course context, so get one if page has module context.
        $currentcontext = $this->page->context->get_course_context(false);

        if (! empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }

        $this->content = '';
        if (empty($currentcontext)) {
            return $this->content;
        }
        if ($this->page->course->id == SITEID) {
            $this->content->text .= "site context";
        }

        if (! empty($this->config->text)) {
            $this->content->text .= $this->config->text;
        }*/
		
        return $this->content;
    }

    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => false,
                     'site' => true,
                     'site-index' => true,
                     'course-view' => true, 
                     'course-view-social' => false,
                     'mod' => true, 
                     'mod-quiz' => false);
    }

    public function instance_allow_multiple() {
          return true;
    }

    function has_config() {return true;}

    public function cron() {
            mtrace( "Hey, my cron script is running" );
             
                 // do something
                  
                      return true;
    }
}
