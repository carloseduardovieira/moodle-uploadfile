<?php

require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require (dirname(__FILE__) . '/lib.php');
require './classes/uploadForm.class.php';
require './model/UploadFileModel.class.php';

$id = optional_param('id', 0, PARAM_INT);
$n = optional_param('n', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_RAW);
$delete = optional_param('delete', '', PARAM_RAW);
$fileid = optional_param('idfile', 0, PARAM_INT);

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
define('VIEW_URL_LINK', "./view.php?id=" . $id);

echo $OUTPUT->header();

echo html_writer::start_tag( 'a', array( 'href' => "./view.php?id={$id}" ) )
        .html_writer::start_tag( 'button', array( 'type' => 'button', 'class' => 'btn btn-primary', 'style' =>'margin:3%; width:20%' ) )
        .format_string( 'View Files' )
        .html_writer::end_tag('button')
        .html_writer::end_tag( 'a' );

echo html_writer::start_tag( 'a', array( 'href' => "./upload.php?id={$id}&action=DELETE" ) )
        .html_writer::start_tag( 'button', array( 'type' => 'button', 'class' => 'btn btn-danger', 'style' =>'margin:3%; width:20%' ) )
        .format_string( 'Delete File' )
        .html_writer::end_tag('button')
        .html_writer::end_tag( 'a' );

$model = new UploadFileModel();
$file = $model->get( $uploadfile->id );

if ($action == 'DELETE' ) {
    
    if ( $delete == 'ConfirmDelete' ) {
        $model->delete( $uploadfile->id, $file->attachments );
        redirect( VIEW_URL_LINK );    
    }
    
    echo $OUTPUT->confirm( format_string( "Are you sure you want to delete this file" ),
            "upload.php?id={$id}&action=DELETE&delete=ConfirmDelete", $CFG->wwwroot . '/mod/uploadfile/view.php?id=' . $id );

    echo $OUTPUT->footer();
    die();
}

// EN - Prepare the data to pass into the form with instance actualy
// PT_BR Obtendo dados do arquivo gravados no banco, da instancia atual.
if( $file ) {
    
    $action = 'UPDATE';
    
}

// EN - Create a new form object (found in lib.php) 
// PT_BR - Cria um formulario usando a api moodleform
$mform = new uploadForm( './upload.php?id='.$id. "&action={$action}&idfile={$file->id}" );

// ---------
// CONFIGURE FILE MANAGER
// ---------

// EN - Copy all the files from the 'real' area, into the draft area.
// PT_BR - Prepara o arquivo para ser manipulado, obtendo-o do bd para uma area de rascunho.
file_prepare_draft_area($file->attachments, $context->id, 'mod_uploadfile', 'attachment', $file->attachments, null);

// EN - Set form data: This will load the file manager with your previous files
// PT_BR -  Seta no formulario os dados do possivel upload ja feito.
$mform->set_data($file);

if ( $mform->is_cancelled() ) {
    
     redirect( VIEW_URL_LINK );
    
} else if ( $formdata = $mform->get_data() ) {
    
    // EN - Saves the form loaded file to the database in the files table.
    // PT_BR - Salva o arquivo carregado do formulario na tabela de arquivos do moodle BD.
    file_save_draft_area_files( $formdata->attachments, $context->id, 'mod_uploadfile', 'attachment', $formdata->attachments, $mform->get_filemanager_options_array() );

    // EN - Save or update in local table uploadfile_files.
    // PT_BR - Salva ou atualiza na tabela local uploadfile_files.
    $formdata->instance = $cm->instance;
    if ( $action == 'ADD' ) {
        
        $model->save( $formdata );        
        
    } else {
        
        $formdata->id = $fileid;
        $model->update( $formdata );
    }    

    redirect( VIEW_URL_LINK );
}


$mform->display();
echo $OUTPUT->footer();
