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
 * This plugin serves as a database and plan for all learning activities in the organization,
 * where such activities are organized for a more structured learning program.
 * @package    block_systemreports
 * @copyright  3i Logic<lms@3ilogic.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @author     Azmat Ullah <azmat@3ilogic.com>
 */
require_once('../../config.php');
require_once('systemreports_form.php');
require_once("lib.php");
global $DB;
$attributes = array();

//print_object($_POST);
// $data = required_param('data', PARAM_ARRA);
$u_id = optional_param('u_id', null, PARAM_INT);
$action = optional_param('action', null, PARAM_RAW);
$data = optional_param('data', null, PARAM_RAW);
//print_object(json_decode($data));//die();
$courseid = optional_param('courseid', null, PARAM_INT);
$t = optional_param('t', null, PARAM_INT);
$t_id = optional_param('t_id', null, PARAM_INT);
$status = optional_param('status', null, PARAM_INT);
$hidetraining = optional_param('hidetraining', null, PARAM_INT);
$hideusers = optional_param('hideusers', null, PARAM_INT);
$t_type_id = optional_param('t_type_id', null, PARAM_INT);
$g_id = optional_param('g_id', null, PARAM_INT);
$lpid = optional_param('lpid', null, PARAM_INT);
$courseid = optional_param('courseid', null, PARAM_INT);
if (!isloggedin()) {
    redirect($CFG->wwwroot);
}
require_login(null, false);
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/blocks/systemreports/ajax_bridge.php');



if(isset($action) && $action == "savecourseorder") {
    global $DB;
    $decoded_data = json_decode($data);
    $DB->set_debug(true);
    //print_object($decoded_data);//die();
    foreach($decoded_data as $value) {  
    //echo $value->lpid;
        if(!empty($value->courseorder)) {     
            echo $sql = 'Update {systemreports_courses} set courseorder = '.$value->courseorder.' where learningplanid= '.$value->lpid.' and course='.$value-> course;
            $DB->execute($sql);
        }
    }
    
}


if(isset($action) && $action == "removecourse") {
    global $DB;
    $DB->set_debug(true);
    $DB->delete_records('systemreports_courses', array('learningplanid'=>$lpid, 'course'=>$courseid));
    
}

if(isset($action) && $action == "getCourses") {
    $userid = optional_param('userid', 0, PARAM_INT);
    $startyear = optional_param('startyear', 0, PARAM_INT);
    $endyear = optional_param('endyear', 0, PARAM_INT);
    
    global $DB;
    
    $outcome = new stdClass();
    $outcome->success = true;
    $outcome->response = new stdClass();
    $outcome->error = '';
    
    $sql = "SELECT cc.id, co.fullname as name FROM {course_completions} cc left join {course} co on (cc.course = co.id) WHERE cc.userid = $userid AND "
            . "cc.timecompleted <= $endyear and cc.timecompleted >= $startyear and cc.timecompleted is not NULL";
    
    $records = $DB->get_records_sql($sql);
    $courses = '';
    $course_number = 1;
    foreach ($records as $id => $coursename){
          $courses .= "$course_number)".$coursename->name." ";
          $course_number++;
    }
   if(empty($courses)){
      $courses = "Haven't complete any course";
    }
    $outcome->result= json_encode($courses);
    echo json_encode($outcome);
    die();

    
}
if(isset($action) && $action == "getenrolledCourses") {
    $startyear = optional_param('startyear', 0, PARAM_INT);
    $endyear = optional_param('endyear', 0, PARAM_INT);
    
    global $DB;
    
    $outcome = new stdClass();
    $outcome->success = true;
    $outcome->response = new stdClass();
    $outcome->error = '';
    
    $sql = "SELECT ue.id, co.fullname as name FROM {user_enrolments} ue left join {enrol} en on (ue.enrolid = en.id)"
            . " left join {course} co on (en.courseid = co.id) WHERE "
            . "ue.timemodified <= $endyear and ue.timemodified >= $startyear and ue.timemodified is not NULL group by co.fullname";
    
    $records = $DB->get_records_sql($sql);
    $courses = '';
    $course_number = 1;
    foreach ($records as $id => $coursename){
          $courses .= "$course_number)".$coursename->name." ";
          $course_number++;
    }
    if(empty($courses)){
        $courses = "No student enrolled in this quater";
    }
    $outcome->result= json_encode($courses);
    echo json_encode($outcome);
    die();

    
}
