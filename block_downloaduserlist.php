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
 * downloaduserlist block caps.
 *
 * @package    block_downloaduserlist
 * @copyright  Vincent Schneider <xx>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
class block_downloaduserlist extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_downloaduserlist');
    }
    public function get_content() {
        global $CFG, $USER, $DB;

        if ($this->content !== null) {
            return $this->content;
        }
        $course = $this->page->course;
        $context = context_course::instance($course->id);
        if (!has_capability('moodle/course:manageactivities', $context)) {
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
        $path = $CFG->wwwroot . '/blocks/downloaduserlist/download.php?courseid=' . $course->id;
        $this->content->text = html_writer::start_tag('a', array('target' => '_blank', 'href' => $path));
        $this->content->text .= get_string('download', 'block_downloaduserlist');
        $this->content->text .= html_writer::end_tag('a');
        return $this->content;
    }
    public function applicable_formats() {
        return array('all' => false,
                     'site' => true,
                     'site-index' => true,
                     'course-view' => true,
                     'course-view-social' => false,
                     'mod' => true,
                     'mod-quiz' => false);
    }
}