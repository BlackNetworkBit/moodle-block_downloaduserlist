<?php
require_once('./../../config.php');
require_once($CFG->libdir.'/moodlelib.php');
require_once("$CFG->dirroot/enrol/locallib.php");
require_once("$CFG->dirroot/enrol/users_forms.php");
require_once("$CFG->dirroot/enrol/renderer.php");
require_once("$CFG->dirroot/group/lib.php");
require_once("$CFG->dirroot/cohort/locallib.php");
global $OUTPUT, $DB,$USER;
$courseid = required_param('courseid', PARAM_INT);
require_login($courseid);
$context= get_context_instance(CONTEXT_COURSE,$courseid);
if(!has_capability('moodle/course:manageactivities',$context)){
	die(get_string('notrainer',"block_downloaduserlist"));
}

// This code part search's for groups and put them into a list based on the userid
$userpool=array();
$sql = "SELECT c.*, ".context_helper::get_preload_record_columns_sql('ctx') . " FROM {cohort} c JOIN {context} ctx ON ctx.id = c.contextid ";
$cohort_data=$DB->get_records_sql($sql);
foreach($cohort_data as $cohort){
	$cohort = $DB->get_record('cohort', array('id'=>$cohort->id), '*', MUST_EXIST);
	$context_cohort = context::instance_by_id($cohort->contextid, MUST_EXIST);
	$existinguserselector = new cohort_existing_selector('', array('cohortid'=>$cohort->id, 'accesscontext'=>$context_cohort));
	$userdata=array_values($existinguserselector->find_users())[0];
	foreach($userdata as $user){
		$userpool[$user->id][] = $cohort->name; // add another group to a user-array
	}
}
//end grouppart

$csvdata  =get_string("username","block_downloaduserlist") . "," . get_string("firstname","block_downloaduserlist") . "," . get_string("lastname","block_downloaduserlist");
$csvdata .="," . get_string("loginmethod","block_downloaduserlist") . "," . get_string("firstlogin","block_downloaduserlist") . "," . get_string("role","block_downloaduserlist") . "," . get_string("groups","block_downloaduserlist") . "\n";
$coursedata=$DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$manager = new course_enrolment_manager($PAGE,$coursedata);
$users = $manager->get_users_for_display($manager,"id","asc",0,count_enrolled_users($context));
foreach ($users as $userid=>&$user) {
	$userintid=intval($user['userid']);
	$data = array_values($user['enrolments'])[0];
	$enrolment_date=array_values($manager->get_user_enrolments($userintid));
	$enrolment_date=$enrolment_date[0]->timecreated;
	$enrolment_method=$data['text'];
	$username=$DB->get_record('user',array('id' =>$userintid))->username;
	$firstname=$user['picture']->user->firstname;
	$lastname=$user['picture']->user->lastname;
	$roles="";
	foreach($user['roles'] as $r){
		$roles .=$r['text'] . " ";
	}
	$groups="";
	if(!empty($userpool[$userintid])){
		$groups=implode(" ",$userpool[$userintid]);
	}
	$csvdata .= $username . ',' . $firstname . "," . $lastname . ',' . $enrolment_method . "," . date("d.m.Y H:i:s",$enrolment_date) . "," . $roles . "," . $groups . "\n";

}
header("Content-Type: text/csv");
header("Content-Length: " . strlen($csvdata));
echo $csvdata ;
exit;
