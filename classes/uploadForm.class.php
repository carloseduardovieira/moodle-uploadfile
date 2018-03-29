<?php

/**
 * Class with form for upload of files
 *
 * @author Carlos Eduardo Vieira <linkedin: https://www.linkedin.com/in/carlos-eduardo-vieira/>
 */

require($CFG->dirroot . '/course/moodleform_mod.php');

class uploadForm extends moodleform {

    function definition() {
        
        $mform = $this->_form; // Don't forget the underscore!
        $mform->addElement('hidden', 'idfile', true);
        
        // FILE MANAGER        
        $mform->addElement('filemanager', 'attachments', format_string('File Manager Example'), 
                null, $this->get_filemanager_options_array());
        
        // Buttons
        $this->add_action_buttons();
    }
    
    /**Set here the options available for your file manager
     * https://docs.moodle.org/dev/Using_the_File_API_in_Moodle_forms
     * @return options for file manager
     */
    function get_filemanager_options_array () {
        return array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1,
                'accepted_types' => array('*'));
    }

}
