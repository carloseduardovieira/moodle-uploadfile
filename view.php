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
require (dirname(__FILE__) . '/lib.php');

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

echo $OUTPUT->header();

echo "<a href='./upload.php?id={$id}'><input class='btn btn-primary' type='button' value='Manage Files'></a>";
echo "<a style='padding-left:1%' href='./view.php?id={$id}'><input class='btn btn-primary' type='button' value='View Files'></a>";
echo "<br /><br /><br />";

// ---------
// Display Managed Files!
// ---------
$fs = get_file_storage();
if ($files = $fs->get_area_files($context->id, 'mod_uploadfile', 'attachment', '0', 'sortorder', false)) {

    // Look through each file being managed - pt_br verificar todos os arquivos que estao sendo gerenciados pelo filemanager
    foreach ($files as $file) {
        // Build the File URL. Long process! But extremely accurate. - pt_br cria uma url para o arquivo
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
        // Display link for file download - pt_bt exibe link para download do arquivo
        $download_url = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path();
        echo '<a href="' . $download_url . '">' . $file->get_filename() . '</a><br/>';

        // Display for file - pt_bt exibe o arquivo em caso de imagem.
        if (file_extension_in_typegroup($file->get_filename(), 'web_image')) {
            echo html_writer::empty_tag('img', array('src' => $download_url));
        }
    }
} else {
    echo $OUTPUT->notification(format_string('Please upload an image first'));
}

echo $OUTPUT->footer();
