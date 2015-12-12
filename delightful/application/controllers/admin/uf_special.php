<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class uf_special extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function cloneTable($lTableID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $glUserID;
      
      if (!bTestForURLHack('adminOnly')) return;
      $this->load->helper('dl_util/verify_id');
      if (!vid_bUserTableIDExists($this, $lTableID, $enumType)) vid_bTestFail($this, false, 'user table ID', $lTableID);
      
      
         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model('personalization/muser_fields',        'clsUF');
      $this->load->model('personalization/muser_fields_create', 'clsUFC');
      $this->load->model('personalization/muser_clone',         'cUFClone');
      
      
// most of the work is complete in the model muser_clone;
// just need the gui to get the new table name, then create the new
// table (probably can use the add/edit user table routine)      
echoT('Work in progress<br>');      
      
   }
   
   
}