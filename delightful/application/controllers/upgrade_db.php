<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2012 Database Austin
  
   author: John Zimmerman 
  
   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------*/

class upgrade_db extends CI_CONTROLLER {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);    
   }


   public function index(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------  
      global $glChapterID, $glUserID, $gstrFName;
      $displayData = array();
      $this->load->model('admin/muser_accts', 'clsUser');
      
      $this->clsUser->versionLevel();
      $displayData['versionInfo'] = &$this->clsUser->versionInfo;

      $this->load->view('upgrade_db_view', $displayData);
   }


}
