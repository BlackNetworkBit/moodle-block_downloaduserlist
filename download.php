<?php
require_once('./../../config.php');
require_once($CFG->libdir.'/moodlelib.php');
require_once("$CFG->dirroot/enrol/locallib.php");
require_once("$CFG->dirroot/enrol/users_forms.php");
require_once("$CFG->dirroot/enrol/renderer.php");
require_once("$CFG->dirroot/group/lib.php");
global $OUTPUT, $DB,$USER;
$courseid = required_param('courseid', PARAM_INT);
require_login($courseid);
$context= get_context_instance(CONTEXT_COURSE,$courseid);
if(!has_capability('moodle/course:manageactivities',$context)){
	die(get_string('notrainer',"block_downloaduserlist"));
}
$csvdata  =get_string("username","block_downloaduserlist") . "," . get_string("firstname","block_downloaduserlist") . "," . get_string("lastname","block_downloaduserlist");
$csvdata .="," . get_string("loginmethod","block_downloaduserlist") . "," . get_string("firstlogin","block_downloaduserlist") . "," . get_string("role","block_downloaduserlist") . "\n";
$coursedata=$DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$manager = new course_enrolment_manager($PAGE,$coursedata);
$users = $manager->get_users_for_display($manager,"id","asc",0,99999);
foreach ($users as $userid=>&$user) {
	$data = array_values($user['enrolments'])[0];
	$enrolment_date=array_values($manager->get_user_enrolments(intval($user['userid'])));
	$enrolment_date=$enrolment_date[0]->timecreated;
	$enrolment_method=$data['text'];
	$username=$DB->get_record('user',array('id' =>intval($user['userid'])))->username;
	$firstname=$user['picture']->user->firstname;
	$lastname=$user['picture']->user->lastname;
	$roles="";
	foreach($user['roles'] as $r){
		$roles .=$r['text'] . " ";
	}
	$csvdata .= $username . ',' . $firstname . "," . $lastname . ',' . $enrolment_method . "," . date("d.m.Y H:i:s",$enrolment_date) . "," . $roles . "\n";

}
header("Content-Type: text/csv");
header("Content-Length: " . strlen($csvdata));
echo $csvdata ;
exit;