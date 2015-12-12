<?php
/*---------------------------------------------------------------------
 Delightful Labor!

 copyright (c) 2012-2013 by Database Austin

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.
/*---------------------------------------------------------------------
      $this->load->model('admin/minstall_db', 'clsIDB');
---------------------------------------------------------------------*/
class minstall_db extends CI_Model{

   public $bError, $strErrMsg;

   function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
		parent::__construct();

      $this->bError    = false;
      $this->strErrMsg = '';
   }

   function loadInstallDBFile($dbObj){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDev;

      $gbDev = true;

         //------------------------------------
         // verify that the database is empty
         //------------------------------------
      $sqlStr = 'SHOW TABLES;';
      $query = $dbObj->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows!=0) {
         $this->bError    = true;
         $this->strErrMsg = 'Your database already contains tables! This '
                       .'install requires an empty mySQL database.';
         return;
      }

         //------------------------------------
         // load the sql installation file
         //------------------------------------
      $strInstallFN = set_realpath('.').'README/delightful_labor.sql';

      $fInfo = get_file_info($strInstallFN);
      if ($fInfo===false){
         $this->bError    = true;
         $this->strErrMsg = 'Can not find the database installation file '.$strInstallFN.'<br>';
         return;
      }

         //------------------------------------------------------
         // split the sql file into individual sql statements
         //------------------------------------------------------
      $strInstallDB = read_file($strInstallFN);
      $sqlInserts = explode('[BREAK]', $strInstallDB);

         //------------------------------------
         // apply sql statements
         //------------------------------------
      foreach ($sqlInserts as $strInsert){
         if (trim($strInsert)!=''){
            $query = $dbObj->query($strInsert);
         }
      }
   }

   function updateDBConfigFile($strDBName, $strDBHost, $strUserName, $strPWord){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDBConfigFN = set_realpath('.').'application/config/database.php';
      $fInfo = get_file_info($strDBConfigFN);
      if ($fInfo===false){
         $this->bError    = true;
         $this->strErrMsg = 'Can not find the database configuration file '.$strDBConfigFN.'<br>';
         return;
      }

      $strDBConfig =
            '<?php'."\n"
           .'/* ----------------------------'."\n"
           .'  Database configuration entries updated on '.date('Y-m-d H:i:s'). ' for the '."\n"
           .'  Delightful Labor installation. '."\n\n"
           .'  Updates can be found at the bottom of this file.'."\n"
           .' ----------------------------*/'."\n"
           ."?>"

           .read_file($strDBConfigFN)

           ."\n\n"
           .'/* ----------------------------'."\n"
           .'  Delightful Labor database settings. '."\n"
           .' ----------------------------*/'."\n"
               ."\$db['default']['hostname'] = '$strDBHost';\n"
               ."\$db['default']['username'] = '$strUserName';\n"
               ."\$db['default']['password'] = '$strPWord';\n"
               ."\$db['default']['database'] = '$strDBName';\n";

      write_file($strDBConfigFN, $strDBConfig);
   }


}


