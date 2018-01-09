<?php

/**
 * Plugin desenvolvido para estudo de como realizar upload de arquivo no moodle.
 *
 * @author carlos
 */
require($CFG->dirroot . '/course/moodleform_mod.php');

class uploadForm extends moodleform {

    function definition() {
        $mform = $this->_form; // Don't forget the underscore!
        $filemanageropts = $this->_customdata['filemanageropts'];
        // FILE MANAGER
        $mform->addElement('filemanager', 'attachments', 'File Manager Example', null, $filemanageropts);
        // Buttons
        $this->add_action_buttons();
    }

}
