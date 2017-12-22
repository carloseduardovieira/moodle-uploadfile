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
 * Prints a particular instance of uploadfile
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_uploadfile
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Replace uploadfile with the name of your module and remove this line.

require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require (dirname(__FILE__) . '/lib.php');
require './classes/uploadForm.class.php';

$id = optional_param('id', 0, PARAM_INT);
$n = optional_param('n', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('uploadfile', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $uploadfile = $DB->get_record('uploadfile', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $uploadfile = $DB->get_record('uploadfile', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $uploadfile->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('uploadfile', $uploadfile->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context_course = context_course::instance($course->id);

$event = \mod_uploadfile\event\course_module_viewed::create(array(
            'objectid' => $PAGE->cm->instance,
            'context' => $PAGE->context,
        ));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $uploadfile);
$event->trigger();

$PAGE->set_url('/mod/uploadfile/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($uploadfile->name));
$PAGE->set_heading(format_string($course->fullname));
$modcontext = context_module::instance($cm->id);

echo $OUTPUT->header();

    $uploaded = new uploadForm('./view.php?id='.$id);
    $uploaded->display();
    
    $file = $uploaded->get_data(); 
    
    if($file){
        
        echo '<pre>';
        print_r($file);
        echo '</pre>';
    }
    
    
    
    
    
    
    
echo $OUTPUT->footer();
