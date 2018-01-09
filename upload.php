<?php

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
$context = context_system::instance();
$event = \mod_uploadfile\event\course_module_viewed::create(array(
            'objectid' => $PAGE->cm->instance,
            'context' => $PAGE->context,
        ));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $uploadfile);
$event->trigger();

$PAGE->set_url('/mod/uploadfile/upload.php', array('id' => $cm->id));
$PAGE->set_title(format_string($uploadfile->name));
$PAGE->set_heading(format_string($course->fullname));
$modcontext = context_module::instance($cm->id);

/* Create some options for the file manager - pt_br cria algumas opcoes para o filemanager você pode consultar o conjunto de opcoes disponiveis
  como imagem, zip, audio etc consultando esta pagina: https://docs.moodle.org/dev/Using_the_File_API_in_Moodle_forms
 */
$filemanageropts = array('subdirs' => 0, 'maxbytes' => '0', 'maxfiles' => 50, 'context' => $context);
$customdata = array('filemanageropts' => $filemanageropts);

// Create a new form object (found in lib.php) - pt_br neste momento ele cria o formulario usando a api moodleform e passa esse conjunto de opcoes para o filemanager
$mform = new uploadForm(null, $customdata);

// ---------
// CONFIGURE FILE MANAGER
// ---------

$itemid = 0; // This is used to distinguish between multiple file areas, e.g. different student's assignment submissions, or attachments to different forum posts, in this case we use '0' as there is no relevant id to use
// Fetches the file manager draft area, called 'attachments' - pt_br Obtem a area de rascunho do gerenciador de arquivos, chamada "attachments"
$draftitemid = file_get_submitted_draft_itemid('attachments');

// Copy all the files from the 'real' area, into the draft area pt_br Copie todos os arquivos da area "real" onde estao salvos, para a area de rascunho
file_prepare_draft_area($draftitemid, $context->id, 'mod_uploadfile', 'attachment', $itemid, $filemanageropts);

// Prepare the data to pass into the form - normally we would load this from a database, but, here, we have no 'real' record to load
//pt_br Prepare os dados para passar no formulario - normalmente devemos carregar isso a partir de um banco de dados, mas, aqui, nao temos registro "real" para carregar
$entry = new stdClass();
$entry->attachments = $draftitemid; // Add the draftitemid to the form, so that 'file_get_submitted_draft_itemid' can retrieve it - pt_br Adicione o draftitemid ao formulario, de modo que 'file_get_submitted_draft_itemid' possa recupera-lo
// --------- 
// Set form data
// This will load the file manager with your previous files - pt_br neste momento ele preenche o formulario com o dado do upload jah salvo anteriormente caso haja.
$mform->set_data($entry);
// ===============
//
//
// PAGE OUTPUT
//
//
// ===============
echo $OUTPUT->header();

echo "<a href='./upload.php?id={$id}'><input class='btn btn-primary' type='button' value='Manage Files'></a>";
echo "<a style='padding-left:1%' href='./view.php?id={$id}'><input class='btn btn-primary' type='button' value='View Files'></a>";
echo "<br /><br /><br />";
// ----------
// Form Submit Status
// ----------
if ($mform->is_cancelled()) {
    // CANCELLED
    echo '<h1>Cancelled</h1>';
    echo '<p><p>';
    echo $OUTPUT->notification(format_string('Handle form cancel operation, if cancel button is present on form'));
    echo "<a href='./upload.php?id={$id}'><input type='button' value='Try Again' /><a>";
} else if ($data = $mform->get_data()) {
    // SUCCESS
    echo '<h1>Success!</h1>';
    echo '<p>In this case you process validated data. $mform->get_data() returns data posted in form.<p>';
    // Save the files submitted
    file_save_draft_area_files($draftitemid, $context->id, 'mod_uploadfile', 'attachment', $itemid, $filemanageropts);
} else {
    // FAIL / DEFAULT
    echo '<h1 style="text-align:center">Upload file</h1>';
    $mform->display();
}
echo $OUTPUT->footer();
