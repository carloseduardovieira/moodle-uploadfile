<?php

/**
 * Plugin desenvolvido para estudo de como realizar upload de arquivo no moodle.
 *
 * @author carlos
 */
require($CFG->dirroot . '/course/moodleform_mod.php');

class uploadForm extends moodleform {

    function definition() {
        $mform = $this->_form;

        $mform->addElement('filemanager', 'arquivo', format_string('Arquivo'), null,
                $this->get_filemanager_options_array());


        $this->add_action_buttons($cancel = true, format_string('Submeter'));
    }
    
    public function data_preprocessing(&$data) {
        global $CFG;
        require (dirname(__FILE__) . '/locallib.php');
        parent::data_preprocessing($data);
        if ($this->current->instance) {
            // editing an existing certificate - let us prepare the added editor elements (intro done automatically), and files            
            //First Page
            
            //Get firstimage
            $imagedraftitemid = file_get_submitted_draft_itemid('arquivo');
            //Get firtsimage filearea information
            $imagefileinfo = uploadfile::get_image_fileinfo($this->context);
            file_prepare_draft_area($imagedraftitemid, $imagefileinfo['contextid'], $imagefileinfo['component'], $imagefileinfo['filearea'], $imagefileinfo['itemid'],
                         $this->get_filemanager_options_array());
            
            $data['arquivo'] = $imagedraftitemid;
                        
        }
                        
    }
    
    function validation($data, $files) {
        return array();
    }
    
    private function get_filemanager_options_array () {
        return array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1,
                'accepted_types' => array('image'));
    }

}
