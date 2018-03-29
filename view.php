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
 * @copyright  2018 Carlos Eduardo Vieira <https://www.linkedin.com/in/carlos-eduardo-vieira/>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require './model/UploadFileModel.class.php';

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
$context = context_system::instance();
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
$model = new UploadFileModel();
$file = $model->get($uploadfile->id);

echo $OUTPUT->header();

echo html_writer::start_tag( 'a', array( 'href' => "./upload.php?id={$id}&action=ADD" ) )
        .html_writer::start_tag( 'button', array( 'type' => 'button', 'class' => 'btn btn-primary', 'style' =>'margin:3%; width:20%' ) )
        .format_string( 'Manage Files' )
        .html_writer::end_tag('button')
        .html_writer::end_tag( 'a' );

if ( $imageurl = print_image_uploadfile( $file->attachments, $context->id ) ) {
    
    // EN - for this plugin I am using to display an image, if you have imported another type of file,
    // treat it the way you want.
    // PT_BR para este plugin estou utilizando para exibicao uma imagem, caso voce tenha importado outro tipo de arquivo,
    // trate-o da maneira desejada.    
    echo html_writer::empty_tag('img', array('width' => '100%', 'height' => '100%', 'src' => $imageurl));    

} else {
    echo $OUTPUT->notification(format_string('Please upload an image first'));
}

echo $OUTPUT->footer();
