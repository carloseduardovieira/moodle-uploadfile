<?php

/**
 * Methods Database Connection
 *
 * @author Carlos Eduardo Vieira <linkedin: https://www.linkedin.com/in/carlos-eduardo-vieira/>
 */

defined('MOODLE_INTERNAL') || die();

class UploadFileModel {
    
    /** '/!\' recommended to use protected method and perform the necessary treatments. 
     * I'm using public method for illustration only.
     * 
     * @param type form object
     * @return boolean
     */
    public function save( $object ) {
        global $DB;
        
        try {
         
            $response = $DB->insert_record( 'uploadfile_files', $object, $returnid = true );
            return $response;   
            
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }

    }
    
    public function update( $object ) {
        global $DB;
        
        try{
            $object->id = $object->idfile;
            $response = $DB->update_record( 'uploadfile_files', $object, false );
            return $response;    
        } catch (Exception $ex) {

        }
        
    }

    public function get( $instance ) {
        global $DB;
        
        try{
            return $DB->get_records( 'uploadfile_files', array('instance' => $instance ), null, 'instance, attachments' )[$instance];
        } catch (Exception $ex) {

        }
        
    }
    
    public function delete( $instance, $itemid ) {
        global $DB;
        try{
            $DB->delete_records( 'uploadfile_files', array( "instance" => $instance ) );
            $DB->delete_records( 'files', array( "itemid" => $itemid ) );                
        } catch (Exception $ex) {

        }
        
    }
}
