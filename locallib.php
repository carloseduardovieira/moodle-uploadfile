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
 * Internal library of functions for module uploadfile
 *
 * All the uploadfile specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_uploadfile
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/*
 * Does something really useful with the passed things
 *
 * @param array $things
 * @return object
 *function uploadfile_do_something_useful(array $things) {
 *    return new stdClass();
 *}
 */

class uploadfile{     
    
    const CERTIFICATE_COMPONENT_NAME = 'mod_uploadfile';
    const CERTIFICATE_IMAGE_FILE_AREA = 'image';
    
    public static function get_image_fileinfo($context) {
        if (is_object($context)) {
            $contextid = $context->id;
        } else {
            $contextid = $context;
        }
        
        return array('contextid' => $contextid, //id contexto
                          'component' => self::CERTIFICATE_COMPONENT_NAME, // nome do plugin
                          'filearea' => self::CERTIFICATE_IMAGE_FILE_AREA, // nome do tipo de arquivo
                          'itemid' => 1, 
                          'filepath' => '/'); 
    }
    
    
}

function uploadfile_set_mainfile($data) {
    global $DB;
    $fs = get_file_storage();
    $cmid = $data->coursemodule;
    $draftitemid = $data->files;

    $context = context_module::instance($cmid);
    if ($draftitemid) {
        $options = array('subdirs' => true, 'embed' => false);
        file_save_draft_area_files($draftitemid, $context->id, 'mod_uploadfile', 'content', 0, $options);
    }
    $files = $fs->get_area_files($context->id, 'mod_uploadfile', 'content', 0, 'sortorder', false);
    if (count($files) == 1) {
        // only one file attached, set it as main file automatically
        $file = reset($files);
        file_set_sortorder($context->id, 'mod_uploadfile', 'content', 0, $file->get_filepath(), $file->get_filename(), 1);
    }
}