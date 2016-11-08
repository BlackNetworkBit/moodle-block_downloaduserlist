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

require_once('./../../config.php');
require_once($CFG->libdir.'/moodlelib.php');
require_once("$CFG->dirroot/enrol/locallib.php");
require_once("$CFG->dirroot/enrol/users_forms.php");
require_once("$CFG->dirroot/enrol/renderer.php");
require_once("$CFG->dirroot/group/lib.php");
require_once("$CFG->dirroot/cohort/locallib.php");
global $OUTPUT, $DB, $USER;
$courseid = required_param('courseid', PARAM_INT);
require_login($courseid);
$context = context_course::instance($courseid);
if (!has_capability('moodle/course:manageactivities', $context)) {
    die(get_string('notrainer', "block_downloaduserlist"));
}
$PAGE->set_url($CFG->wwwroot . '/blocks/downloaduserlist/download.php?courseid=' . $courseid);
// This code part search's for groups and put them into a list based on the userid.
$userpool = array();
$sql = "SELECT c.*, ".context_helper::get_preload_record_columns_sql('ctx') . " FROM {cohort} c JOIN {context} ";
$sql .= "ctx ON ctx.id = c.contextid ";
$cohortdata = $DB->get_records_sql($sql);
foreach ($cohortdata as $cohort) {
    $cohort = $DB->get_record('cohort', array('id' => $cohort->id), '*', MUST_EXIST);
    $contextcohort = context::instance_by_id($cohort->contextid, MUST_EXIST);
    $existinguserselector = new cohort_existing_selector('', array('cohortid' => $cohort->id, 'accesscontext' => $contextcohort));
    $userdata = array_values($existinguserselector->find_users(0))[0];
    foreach ($userdata as $user) {
        $userpool[$user->id][] = $cohort->name; // Add another group to a user-array.
    }
}
// End grouppart.

$csvdata  = get_string("username", "block_downloaduserlist") . "," . get_string("firstname", "block_downloaduserlist") . ",";
$csvdata .= get_string("lastname", "block_downloaduserlist") . "," . get_string("loginmethod", "block_downloaduserlist") . ",";
$csvdata .= get_string("firstlogin", "block_downloaduserlist") . "," . get_string("role", "block_downloaduserlist") . ",";
$csvdata .= get_string("groups", "block_downloaduserlist") . "\n";
$coursedata = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$manager = new course_enrolment_manager($PAGE, $coursedata);
$users = $manager->get_users_for_display($manager, "id", "asc", 0, count_enrolled_users($context));
foreach ($users as $userid => &$user) {
    $userintid = intval($user['userid']);
    $data = array_values($user['enrolments'])[0];
    $enrolmentdate = array_values($manager->get_user_enrolments($userintid))[0]->timecreated;
    $enrolmentmethod = $data['text'];
    $username = $DB->get_record('user', array('id' => $userintid))->username;
    $firstname = $user['picture']->user->firstname;
    $lastname = $user['picture']->user->lastname;
    $roles = "";
    foreach ($user['roles'] as $r) {
        $roles .= $r['text'] . " ";
    }
    $groups = "";
    if (!empty($userpool[$userintid])) {
        $groups = implode(" ", $userpool[$userintid]);
    }
    $csvdata .= $username . ',' . $firstname . "," . $lastname . ',' . $enrolmentmethod . "," . date("d.m.Y H:i:s", $enrolmentdate);
    $csvdata .= "," . $roles . "," . $groups . "\n";
}
header("Content-Type: text/csv");
header("Content-Length: " . strlen($csvdata));
echo $csvdata;
exit;
