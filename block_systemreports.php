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
defined('MOODLE_INTERNAL') || die();

require_once(realpath(dirname(__FILE__) . '/lib.php'));

/**
 * Class to display content on block to access a page.
 *
 * @copyright 3i Logic<lms@3ilogic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_systemreports extends block_base {

    public function init() {
        global $CFG, $USER, $COURSE;
        $this->title = get_string('systemreports', 'block_systemreports');
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        global $CFG, $USER, $COURSE, $PAGE, $DB;
        if (has_capability('block/systemreports:managepages', $this->context)) {
            $this->title = get_string('systemreports', 'block_systemreports');
        } else if (has_capability('block/systemreports:viewpages', $this->context)) {
            $this->title = get_string('myview', 'block_systemreports');
        }
        $this->content = new stdClass;
        $this->content->text = '';

        if (has_capability('block/systemreports:managepages', $this->context)) {
            $pageurl = new moodle_url('/blocks/systemreports/view.php?viewpage');
            if (!strpos($pageurl, '=')) {
                $pageurl .= '=';
            }
            $this->content->text .= html_writer::link($pageurl . '1', get_string('quater_wise_systemreports', 'block_systemreports')) . '<br>';
            $this->content->text .= html_writer::link($pageurl . '4', get_string('enrol_cert_comp', 'block_systemreports')) . '<br>';
            // $this->content->text .= html_writer::link($pageurl . '5', get_string('assign_learningplan_user', 'block_systemreports')) . '<br>';
            $this->content->text .= html_writer::link($pageurl . '5', get_string('user_high_low_att', 'block_systemreports')) . '<br>';
            
        } else if (has_capability('block/systemreports:viewpages', $this->context)) {
            $pageurl = new moodle_url('/blocks/systemreports/student/view.php?id');
            if (!strpos($pageurl, '=')) {
                $pageurl .= '=';
            }
            $systemreports = user_learningplan($USER->id);
            foreach ($systemreports as $lp) {
                $this->content->text .= html_writer::link($pageurl . $lp->id, format_string($lp->learningplan, false)) . '<br>';
            }
            $systemreports->close();
        }
        return $this->content;
    }

    public function applicable_formats() {
        return array(
            'all' => true);
    }

}