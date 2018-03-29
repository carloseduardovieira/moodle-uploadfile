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

class uploadfile {

    const COMPONENT_NAME = 'mod_uploadfile';
    const IMAGE_FILE_AREA = 'image';

    /**
     * Get params for object
     * 
     * @param mixed $context The module context object or id
     * @return the first page background image fileinfo
     */
    public static function get_uploadfile_fileinfo($context) {
        if (is_object($context)) {
            $contextid = $context->id;
        } else {
            $contextid = $context;
        }

        return array('contextid' => $contextid, // ID of context
            'component' => self::COMPONENT_NAME, // usually = table name
            'filearea' => self::IMAGE_FILE_AREA, // usually = table name
            'itemid' => 1, // usually = ID of row in table
            'filepath' => '/'); // any path beginning and ending in /
    }

    /**
     * Save upload files in $fileinfo array and return the filename
     * 
     * @param string $form_item_id Upload file form id
     * @param array $fileinfo The file info array, where to store uploaded file
     * @return string filename
     */
    public function save_upload_file($form_item_id, $context) {

        $fileinfo = $this->get_uploadfile_fileinfo($context);

        // Clear file area
        if (empty($fileinfo['itemid'])) {
            $fileinfo['itemid'] = '';
        }

        $fs = get_file_storage();
        $fs->delete_area_files($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid']);
        file_save_draft_area_files($form_item_id, $fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid']);
        // Get only files, not directories
        $files = $fs->get_area_files($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], '', false);
        $file = array_shift($files);
        return $file->get_filename();
    }

    public function get_image($imagem, $context, $cm, $course) {
        global $CFG;

        $fs = get_file_storage();

        if (!empty($imagem)) {

            $fileinfo = self::get_uploadfile_fileinfo($context);

            $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'], $imagem);

            if ( !$file ) {
                return false;
            }
        }
    }

}