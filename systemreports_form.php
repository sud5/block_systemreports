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
require_once("{$CFG->libdir}/formslib.php");

/**
 * Class for add a learning plan.
 *
 * @copyright 3i Logic<lms@3ilogic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class learningplan_form extends moodleform {

    public function definition() {
    }

    public function validation($data, $files) {
    }

    public function display_list() {
        global $DB, $OUTPUT, $CFG;
        // Page parameters.
        $yearid = optional_param('year', 0, PARAM_INT);
        $course = optional_param('course', 1, PARAM_INT);

        $table = new html_table();
        if($course == 1){
        $table->head = array(get_string('s_no', 'block_systemreports'), get_string('period', 'block_systemreports'),
            get_string('students_enrolled', 'block_systemreports'), get_string('courses_name', 'block_systemreports'),
            get_string('no_of_student', 'block_systemreports'));
        } else{
        $table->head = array(get_string('s_no', 'block_systemreports'), get_string('period', 'block_systemreports'),
            get_string('students_enrolled', 'block_systemreports'), get_string('no_of_student', 'block_systemreports')); 
        }
        $table->size = array('10%', '30', '45%');
        $table->attributes = array('class' => 'display');
        $table->align = array('center', 'left', 'left', 'center', 'center', 'center');
        $table->width = '100%';
        $yearlist = array();
        for($i = 2019;$i<= date("Y");$i++)
        $yearlist["$i"] = $i;
        if($yearid != 0){
         $startyear = $yearid;
         $currentyear = $yearid;
        } else {
         $startyear = 2019;
         $currentyear = date("Y");
        }
        $courselist = array(1 => 'All Courses');
        $courses = get_courses();
        if(!empty($courses)) {
	foreach($courses as $courseid) {
		if($courseid->id == 1)
			continue;
		$courselist[$courseid->id] = $courseid->fullname;
	}
        }

        echo $OUTPUT->single_select(new moodle_url('?viewpage=1', array()), 'year', $yearlist, $yearid, 'Select Year', '', array('label'=>'Select Year'));
        echo $OUTPUT->single_select(new moodle_url('?viewpage=1', array('year'=>$yearid)), 'course', $courselist, $course, 'Choose Course', array(), array('label'=>'Select Course'));
        $no_of_rows = 4 + ($currentyear-$startyear)*4;
        $year_array = array($startyear);
        $inc = 1;
        for ($inc = 1; $inc <= ($currentyear-$startyear); $inc++){
            $year_array[] =   $startyear+$inc;
        }
        $quater = ['Oct-Dec','Jan-Mar','Apr-Jun','Jul-Sep'];
        if (($currentyear-$startyear) !=  -1) {
         for ($inc = 1; $inc <= $no_of_rows; $inc++) {
            $no_of_completion = 0; 
            $row = array();
            $row[] = $inc;
            $quater_index = $inc%4;
            $year_index = ($inc-1)/4;
            $row[] = "$quater[$quater_index],$year_array[$year_index]";//format_string($log->systemreports, false);
            switch($quater_index){
                case 0 :
                    $startdate = strtotime("01-10-".$year_array[$year_index]. " 12:00 AM") ;
                    $enddate = strtotime("31-12-".$year_array[$year_index] . " 11:59:59 PM");
                    break;
                case 1 :
                    $startdate = strtotime("01-01-".$year_array[$year_index]. " 12:00 AM") ;
                    $enddate = strtotime("31-03-".$year_array[$year_index]. " 11:59:59 PM") ;
                    break;
                case 2 :
                    $startdate = strtotime("01-04-".$year_array[$year_index]. " 12:00 AM") ;
                    $enddate = strtotime("30-06-".$year_array[$year_index]. " 11:59:59 PM") ;
                    break;
                 case 3 :
                    $startdate = strtotime("01-07-".$year_array[$year_index]. " 12:00 AM") ;
                    $enddate = strtotime("30-09-".$year_array[$year_index]. " 11:59:59 PM") ;
                    break;
                    
            }
            if($course == 1)
            $query = "SELECT count(id) as count FROM {course_completions} WHERE `timecompleted` < $enddate and `timecompleted` > $startdate";
            else 
            $query = "SELECT count(id) as count FROM {course_completions} WHERE `timecompleted` < $enddate and `timecompleted` > $startdate and course = $course";
            $record = $DB->get_record_sql($query);
            if($course == 1)
            $query = "SELECT count(id) as enrolled FROM {user_enrolments}  WHERE `timemodified` < $enddate and `timemodified` > $startdate";
            else
            $query = "SELECT count(ue.id) as enrolled FROM `mdl_user_enrolments` as ue left join `mdl_enrol` as e on ue.enrolid = e.id"
                    . " WHERE ue.timemodified < $enddate and ue.timemodified > $startdate and e.courseid = $course";
            $record_enrolled = $DB->get_record_sql($query);
            $row[] = $record_enrolled->enrolled;
            if($course == 1){
            $row[] = '<button type="button" id="systemenrolledcourses" class="btn btn-primary" startyear="'.$startdate.'" currentyear="'.$enddate.'"data-toggle="modal" data-target="#myenrolledcourses">
                         View Courses
                      </button>';
                        ?>
<html lang="en">
<body>

<div class="container">
  <!-- Modal -->
  <div class="modal fade" id="myenrolledcourses" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
<div id = "modal-body-enrolledcourses" class="modal-body">
</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
        </div>
      </div>
      
    </div>
  </div>
  
</div>

</body>
</html>
<?php
            }
            $row[] = $record->count; 
            $table->data[] = $row;
        }
		}
		else {
            $table->data[] = array('', '', get_string('notfound', 'block_systemreports'), '', '');
        }
        // $rs->close();
        return $table;
        // echo html_writer::table($table);
    }

}

/**
 * Class for assign users in to a learning plan.
 *
 * @copyright 3i Logic<lms@3ilogic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignlerningplan_user_form extends moodleform {

    public function definition() {

    }

    public function validation($data, $files) {

    }

    public function display_list() {
global $DB, $OUTPUT, $CFG;
$course = optional_param('course', 1, PARAM_INT);

$courselist = array();
$courses = get_courses();
if(!empty($courses)) {
	foreach($courses as $courseid) {
		if($courseid->id == 1)
			continue;
		$courselist[$courseid->id] = $courseid->fullname;
	}
}
echo $OUTPUT->single_select(new moodle_url('/blocks/systemreports/view.php?viewpage=5', array()), 'course', $courselist, $course, 'Choose Course', array(), array('label'=>'Select Course'));

        // Page parameters.
        $userlist = array();
        $users = $DB->get_records_sql("SELECT u.id as id, c.id as courseid, CONCAT(u.firstname,' ', u.lastname) as fullname FROM {user} u INNER JOIN {role_assignments} ra ON ra.userid = u.id INNER JOIN {context} ct ON ct.id = ra.contextid INNER JOIN {course} c ON c.id =ct.instanceid INNER JOIN {role} r ON r.id = ra.roleid  WHERE r.id =5 and c.id = $course 
            and u.suspended = 0 and u.deleted = 0");

        if(!empty($users)) {
            foreach($users as $user) {
                $userlist[$user->id] = $user->fullname;
            }
        }
        $table = new html_table();
        $table->head = array(get_string('s_no', 'block_systemreports'), get_string('student_name', 'block_systemreports'),
            get_string('attendance', 'block_systemreports'));
        $table->size = array('10%', '35%', '25%');
        $table->attributes = array('class' => 'display');
        $table->align = array('center', 'left', 'left', 'center');
        $table->width = '100%';
        $course_access_log = $DB->get_records_sql("SELECT count(id) as id,userid FROM {logstore_standard_log} WHERE action = 'viewed' and "
                 . "courseid = $course and target = 'course' group by userid");
         $course_access_count = array();
       if(!empty($course_access_log)) {
            foreach($course_access_log as $course_access) {
                $course_access_count[$course_access->userid] = $course_access->id;
            }
        }
        $inc = 1;
        if (!empty($userlist)){
        foreach( $userlist as $userlist_key => $userlist_value) {
            $row = array();
            $row[] = $inc;
            $row[] = $userlist_value;
            $row[] = isset($course_access_count[$userlist_key])? $course_access_count[$userlist_key] : 0;//($quiz_grade_id_value != 0) ? substr($quiz_grade_id_value,0,5) : '0.0';

            $table->data[] = $row;
            $inc++;
        }
        }else if($course == 1) {
            $table->data[] = array('',  get_string('choose_course_first', 'block_systemreports'), '');
        }else{
            $table->data[] = array('',  get_string('no_one_visited', 'block_systemreports'), '');
        }
		return $table;
    }
   

}

/**
 * Class to set user's trainings status.
 *
 * @copyright 3i Logic<lms@3ilogic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class trainingstatus_form extends moodleform {

    public function definition() {
    }

    public function display_list() {
         global $DB, $OUTPUT, $CFG;
        // Page parameters.
        $yearid = optional_param('year', 0, PARAM_INT);
        $table = new html_table();
        $table->head = array(get_string('s_no', 'block_systemreports'), get_string('max_course_completed', 'block_systemreports'),
            get_string('student_name', 'block_systemreports'), get_string('courses', 'block_systemreports'));
        $table->size = array('10%', '35%', '25%');
        $table->attributes = array('class' => 'display');
        $table->align = array('center', 'left', 'left', 'center');
        $table->width = '100%';
                $yearlist = array();
        for($i = 2019;$i<= date("Y");$i++)
        $yearlist["$i"] = $i;
        if($yearid != 0){
         $startyear = strtotime("01-01-".$yearid);
         $currentyear = strtotime("31-12-".$yearid);
        } else {
         $startyear =  strtotime("01-01-2019");
         $currentyear =  strtotime("31-12-".date("Y"));
        }
        echo $OUTPUT->single_select(new moodle_url('?viewpage=6', array()), 'year', $yearlist, $yearid, 'Select Year', '', array('label'=>'Select Year'));
        $query = "SELECT concat(c.id,u.id) as roll,  concat(u.firstname ,' ', u.lastname) as fullname, u.id
                  FROM {course} AS c JOIN {context} AS ctx ON c.id = ctx.instanceid JOIN {role_assignments} 
                  AS ra ON ra.contextid = ctx.id JOIN {user} AS u ON u.id = ra.userid WHERE ra.roleid =5 AND ctx.instanceid = c.id";
        $record = $DB->get_records_sql($query);
        $system_students = array();
        $system_students_name = array();
        
        foreach ($record as $recordkey => $recordvalue){
                 $system_students[] = $recordvalue->id;
                 $system_students_name[$recordvalue->id] = $recordvalue->fullname;
        }
        $student_unique = array_unique($system_students);
        $ids = join("','",$student_unique);   
        $query = "SELECT userid, count(*) as completed FROM {course_completions} WHERE (userid) IN ('$ids') AND `timecompleted` <= $currentyear and `timecompleted` >= $startyear and timecompleted is not NULL group by userid ORDER BY completed DESC";
        $completed = $DB->get_records_sql($query);
        $student_completed = array();
        foreach($student_unique as $student_id){
            $student_completed[$student_id] = isset($completed[$student_id]->completed)?$completed[$student_id]->completed:0;
        }
        arsort($student_completed);
        $inc = 1;
        if (!empty($student_unique)){
        foreach($student_completed as $student_completed_key => $student_completed_value) {
            $row = array();
            $row[] = $inc;
            $row[] = $student_completed_value;
            $row[] = "$system_students_name[$student_completed_key]";
            $row[] = '<button type="button" id="courses-list" class="btn btn-primary" userid="'.$student_completed_key.'" startyear="'.$startyear.'" currentyear="'.$currentyear.'"data-toggle="modal" data-target="#myModal">
                         View Courses
                      </button>';
            ?>
<html lang="en">
<body>

<div class="container">
  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
<div class="modal-body">
</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
        </div>
      </div>
      
    </div>
  </div>
  
</div>

</body>
</html>
<?php
            $table->data[] = $row;
            $inc++;
        }
		}
		else {
            $table->data[] = array('', '', get_string('notfound', 'block_systemreports'), '', '');
        }
		return $table;
    }

}

/**
 * Class to set user's trainings status.
 *
 * @copyright 3i Logic<lms@3ilogic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class highest_lowest_grade extends moodleform {

    public function definition() {
        
    }

    public function display_list() {
        global $DB, $OUTPUT, $CFG;
        $course = optional_param('course', 1, PARAM_INT);
        $quizid = optional_param('quiz', 0, PARAM_INT);
        $yearid = optional_param('year', 0, PARAM_INT);

        $courselist = array();
        $courses = get_courses();
        if(!empty($courses)) {
	foreach($courses as $courseid) {
		if($courseid->id == 1)
			continue;
		$courselist[$courseid->id] = $courseid->fullname;
	}
        }

        $quizmoduleid = $DB->get_field('modules', 'id', array('name'=>'quiz'));
        $quizsql = "SELECT q.id, q.name, cm.id as cmid, q.course as courseid 
			from {course_modules} cm
			JOIN {quiz} q ON cm.instance = q.id
			WHERE q.course = $course 
			AND cm.module= $quizmoduleid
			";			
        $quizzes = $DB->get_records_sql($quizsql);
        $quizlist = array();
        if(!empty($quizzes)) {
	foreach($quizzes as $quiz) {
		$quizlist[$quiz->id] = $quiz->name;
	}
}
        $yearlist = array();
        for($i = 2019;$i<= date("Y");$i++)
        $yearlist["$i"] = $i;
        if($yearid != 0){
         $startyear = strtotime("01-01-".$yearid);
         $currentyear = strtotime("31-12-".$yearid);
        } else {
         $startyear =  strtotime("01-01-2019");
         $currentyear =  strtotime("31-12-".date("Y"));
        }
        echo $OUTPUT->single_select(new moodle_url('?viewpage=7', array('quiz'=>$quizid)), 'course', $courselist, $course, 'Choose Course', array(), array('label'=>'Select Course'));
        echo $OUTPUT->single_select(new moodle_url('?viewpage=7', array('course'=>$course)), 'quiz', $quizlist, $quizid, 'Choose Assessment', '', array('label'=>'Select Assessment'));
                echo $OUTPUT->single_select(new moodle_url('?viewpage=7', array('course'=>$course,'quiz'=>$quizid)), 'year', $yearlist, $yearid, 'Select Year', '', array('label'=>'Select Year'));
        // Page parameters.
        $userlist = array();
        $users = $DB->get_records_sql("SELECT u.id as id, c.id as courseid, CONCAT(u.firstname,' ', u.lastname) as fullname FROM {user} u INNER JOIN {role_assignments} ra ON ra.userid = u.id INNER JOIN {context} ct ON ct.id = ra.contextid INNER JOIN {course} c ON c.id =ct.instanceid INNER JOIN {role} r ON r.id = ra.roleid  WHERE r.id =5 and c.id = $course 
            and u.suspended = 0 and u.deleted = 0");

        if(!empty($users)) {
            foreach($users as $user) {
                $userlist[$user->id] = $user->fullname;
            }
        }
        $table = new html_table();
        $table->head = array(get_string('s_no', 'block_systemreports'), get_string('student_name', 'block_systemreports'),
            get_string('marks_in_quiz', 'block_systemreports'));
        $table->size = array('10%', '35%', '25%');
        $table->attributes = array('class' => 'display');
        $table->align = array('center', 'left', 'left', 'center');
        $table->width = '100%';
        $inc = 1;
        $query = "SELECT id, userid, grade from {quiz_grades} where `timemodified` < $currentyear and `timemodified` > $startyear and quiz = $quizid ORDER BY grade DESC ";
//       echo $query;die;
        $quiz_grades = $DB->get_records_sql($query);
        $quiz_grade_id = array();
        if(!empty($quiz_grades)) {
            foreach($quiz_grades as $quiz_grade) {
                $quiz_grade_id[$quiz_grade->userid] = $quiz_grade->grade;
            }
        }
        if (!empty($quiz_grade_id)){
        foreach($quiz_grade_id as $quiz_grade_id_key => $quiz_grade_id_value) {
            $row = array();
            $row[] = $inc;
            $row[] = $userlist[$quiz_grade_id_key];
            $row[] = ($quiz_grade_id_value != 0) ? substr($quiz_grade_id_value,0,5) : '0.0';

            $table->data[] = $row;
            $inc++;
        }
	}else if($quizid == 0) {
            $table->data[] = array('',  get_string('choose_assessment', 'block_systemreports'), '');
        }else{
            $table->data[] = array('',  get_string('no_one_graded', 'block_systemreports'), '');
        }
		return $table;
    }

}

/**
 * Class for assign trainings in to a learning plan.
 *
 * @copyright 3i Logic<lms@3ilogic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assigntraining_learningplan__form extends moodleform {

    public function definition() {
    }

    public function validation($data, $files) {
    }

    public function display_list() {
        global $DB, $OUTPUT, $CFG;
        // Page parameters.
        $yearid = optional_param('year', 0, PARAM_INT);

        $table = new html_table();
        $table->head = array(get_string('s_no', 'block_systemreports'), get_string('student_name', 'block_systemreports'), 
        get_string('course_enrolled', 'block_systemreports'), get_string('course_completed', 'block_systemreports'));
        $table->size = array('10%', '35%', '25%', '15%');
        $table->attributes = array('class' => 'display');
        $table->align = array('center', 'left', 'left', 'center');
        $table->width = '100%';
        $yearlist = array();
        for($i = 2019;$i<= date("Y");$i++)
        $yearlist["$i"] = $i;
        if($yearid != 0){
         $startyear = strtotime("01-01-".$yearid);
         $currentyear = strtotime("31-12-".$yearid);
        } else {
         $startyear =  strtotime("01-01-2019");
         $currentyear =  strtotime("31-12-".date("Y"));
        }
        echo $OUTPUT->single_select(new moodle_url('?viewpage=4', array()), 'year', $yearlist, $yearid, 'Select Year', '', array('label'=>'Select Year'));
        $query = "SELECT concat(c.id,u.id) as roll,  concat(u.firstname , u.lastname) as fullname, u.id
                  FROM {course} AS c JOIN {context} AS ctx ON c.id = ctx.instanceid JOIN {role_assignments} 
                  AS ra ON ra.contextid = ctx.id JOIN {user} AS u ON u.id = ra.userid WHERE ra.roleid =5 AND ctx.instanceid = c.id";
        $record = $DB->get_records_sql($query);
        $system_students = array();
        $system_students_name = array();
        
        foreach ($record as $recordkey => $recordvalue){
                 $system_students[] = $recordvalue->id;
                 $system_students_name[$recordvalue->id] = $recordvalue->fullname;
        }
        $student_unique = array_unique($system_students);
        array_unique($system_students_name);
        $ids = join("','",$student_unique);   
        $query = "SELECT userid, count(*) as enrolled FROM {user_enrolments}  WHERE `timemodified` < $currentyear and `timemodified` > $startyear and (userid) IN ('$ids') group by userid";
        $enrolled = $DB->get_records_sql($query);
        $query = "SELECT userid, count(*) as completed FROM {course_completions} WHERE `timecompleted` < $currentyear and `timecompleted` > $startyear and (userid) IN ('$ids') AND timecompleted is not NULL group by userid";
        $completed = $DB->get_records_sql($query);
        $inc = 1;
        if (!empty($student_unique)){
        foreach($student_unique as $student_id) {
            $row = array();
            $row[] = $inc;
            $row[] = "$system_students_name[$student_id]";
            
            $row[] = isset($enrolled[$student_id]->enrolled)?$enrolled[$student_id]->enrolled:0; //format_string($log->description, false);
            $row[] = isset($completed[$student_id]->completed)?$completed[$student_id]->completed:0;

            $table->data[] = $row;
            $inc++;
        }
		}
		else {
            $table->data[] = array('', '', get_string('notfound', 'block_systemreports'), '', '');
        }
		return $table;
    }

}

?>