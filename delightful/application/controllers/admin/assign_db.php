<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2012-2013 Database Austin

   author: John Zimmerman

   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------*/

class assign_db extends CI_CONTROLLER {

   function __construct(){
      parent::__construct();
      session_start();
      setNameSpace(true);
   }

   public function dbform($strConnectErr='', $strDBSelErr='', $strOtherError=''){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      phpVersionTestDL();  // catch php version error immediately

      $displayData = array();
      $displayData['strErr'] = '';
      $bConnectErr = $strConnectErr == 'true';
      $bDBSelErr   = $strDBSelErr   == 'true';
      $bDBOtherErr = $strOtherError == 'true';
      
      date_default_timezone_set('America/Chicago');

      if ($bConnectErr){
         $displayData['strErr'] = '<b>Unable to create a database connection!</b><br><br>
                                   Please verify your username, host, and password.';
      }elseif ($bDBSelErr){
         $displayData['strErr'] = '<b>Unable to open your database!</b><br><br>
                                   Please verify your database name. This database should exist and be empty.';
      }elseif ($strOtherError){
         $displayData['strErr'] = @$_SESSION[CS_NAMESPACE.'install']->strError;
      }

         //-------------------------
         // models & helpers
         //-------------------------

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<br><span class="formErrorInstall">', '</span>');
		$this->form_validation->set_rules('dbName',     'Database Name', 'trim|required');
		$this->form_validation->set_rules('dbHostName', 'Host Name', 'trim|required');
		$this->form_validation->set_rules('dbUserName', 'Database User Name', 'trim|required');
		$this->form_validation->set_rules('dbPassword', 'Database Password', 'trim|required');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($bConnectErr || $bDBSelErr || $bDBOtherErr){
               $displayData['formData']->dbName       = $_SESSION[CS_NAMESPACE.'install']->dbName;
               $displayData['formData']->dbHostName   = $_SESSION[CS_NAMESPACE.'install']->dbHostName;
               $displayData['formData']->dbUserName   = $_SESSION[CS_NAMESPACE.'install']->dbUserName;
               $displayData['formData']->dbPWord      = $_SESSION[CS_NAMESPACE.'install']->dbPassword;
            }else {
               $displayData['formData']->dbName       = '';
               $displayData['formData']->dbHostName   = 'localhost';
               $displayData['formData']->dbUserName   = '';
               $displayData['formData']->dbPWord      = '';
            }
         }else {
            $displayData['formData']->dbName       = set_value('dbName');
            $displayData['formData']->dbHostName   = set_value('dbHostName');
            $displayData['formData']->dbUserName   = set_value('dbUserName');
            $displayData['formData']->dbPWord      = set_value('dbPassword');
         }

            //--------------------------
            // view
            //--------------------------
         $displayData['viewType'] = 'dbInstall';
         $this->load->vars($displayData);
         $this->load->view('install/db_info');
      }else {
         $_SESSION[CS_NAMESPACE.'install'] = new stdClass;

         $strDBName   = $_SESSION[CS_NAMESPACE.'install']->dbName     = trim($_POST['dbName']);
         $strDBHost   = $_SESSION[CS_NAMESPACE.'install']->dbHostName = trim($_POST['dbHostName']);
         $strUserName = $_SESSION[CS_NAMESPACE.'install']->dbUserName = trim($_POST['dbUserName']);
         $strPWord    = $_SESSION[CS_NAMESPACE.'install']->dbPassword = trim($_POST['dbPassword']);
         
            // http://stackoverflow.com/questions/12483861/codeigniter-php-check-if-can-connect-to-database
         $conTest = array();    // connection test
         $conTest['hostname'] = $strDBHost;
         $conTest['username'] = $strUserName;
         $conTest['password'] = $strPWord;
         $conTest['database'] = $strDBName;
         $conTest['autoinit'] = false;
         $conTest['db_debug'] = false;
         $conTest['dbdriver'] = 'mysqli';
         $conTest['dbprefix'] = '';
         $conTest['pconnect'] = TRUE;
         $conTest['cache_on'] = FALSE;
         $conTest['cachedir'] = '';
         $conTest['char_set'] = 'utf8';
         $conTest['dbcollat'] = 'utf8_general_ci';
         $conTest['swap_pre'] = '';
         $conTest['stricton'] = FALSE;

         $dbObj = $this->load->database($conTest, true);
         $connected = $dbObj->initialize();
         if ($connected === false) {
            redirect('admin/assign_db/dbform/true');
         }else {
            $this->installEmptyDB($dbObj,
                            $strDBName, $strDBHost, $strUserName,
                            $strPWord);
         }
      }
   }

   function installEmptyDB($dbObj,
                           $strDBName, $strDBHost, $strUserName,
                           $strPWord){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->model('admin/minstall_db', 'clsIDB');
      $this->load->helper('path');
      $this->clsIDB->loadInstallDBFile($dbObj);
      if ($this->clsIDB->bError){
         $_SESSION[CS_NAMESPACE.'install']->strError = $this->clsIDB->strErrMsg;
         redirect('admin/assign_db/dbform/false/false/true');
      }

      $this->clsIDB->updateDBConfigFile($strDBName, $strDBHost, $strUserName, $strPWord);
      if ($this->clsIDB->bError){
         $_SESSION[CS_NAMESPACE.'install']->strError = $this->clsIDB->strErrMsg;
         redirect('admin/assign_db/dbform/false/false/true');
      }

      $displayData['viewType'] = 'dbInstallComplete';
      $this->load->vars($displayData);
      $this->load->view('install/db_info');
   }


}



