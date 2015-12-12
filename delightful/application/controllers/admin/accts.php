<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class accts extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function userAcctDir($strLookupLetter, $lStartRec=0, $lRecsPerPage=50){
   //------------------------------------------------------------------------
   // User directory
   //------------------------------------------------------------------------
      $displayData = array();
      $displayData['js'] = '';
      
      if (!bTestForURLHack('adminOnly')) return;
      $strLookupLetter = urldecode($strLookupLetter);

         //-------------------------------------
         // models, libraries, and helpers
         //-------------------------------------
      $this->load->model ('admin/muser_accts',  'clsUsers');
      $this->load->model ('admin/mpermissions', 'perms');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/directory');
      $this->load->helper('dl_util/rs_navigate');
      
      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

      $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                   .' | User Accounts';
      $displayData['title']          = CS_PROGNAME.' | User Accounts';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

         //------------------------------------------------
         // display option / sanitize the lookup letter
         //------------------------------------------------
      $bShowAll = $strLookupLetter=='showAll' || is_null($strLookupLetter) || $strLookupLetter=='*';
      if ($bShowAll){
         $strLookupLetter = null;
      }else {
         $strLookupLetter = strtoupper(substr($strLookupLetter, 0, 1));
         if ($strLookupLetter < 'A' || $strLookupLetter > 'Z' ) $strLookupLetter = '#';
      }
      $displayData['strDirLetter'] = $strLookupLetter;
      $strLinkBase = 'admin/accts/userAcctDir/';
      $displayData['strDirTitle'] = strDisplayDirectory(
                                            $strLinkBase, ' class="directoryLetters" ', $strLookupLetter,
                                            true, $lStartRec, $lRecsPerPage);

      $displayData['lNumRecsTot'] = $lNumRecsTot = $this->clsUsers->lCountUsers(null, $strLookupLetter);

      if ($lNumRecsTot==0) {
         $displayData['strNobodyHome'] = 'There are no user records that begin with <b>"'
                   .htmlspecialchars($strLookupLetter).'"</b>';
      }else {
         if ($bShowAll) {
            $strWhereExtra = '  ';
         }else {
            $strWhereExtra = strNameWhereClauseViaLetter('us_strLastName', $strLookupLetter)
                      .' AND NOT us_bInactive '; 
         }
         $this->clsUsers->loadUserDirectoryPage($strWhereExtra, $lStartRec, $lRecsPerPage);
         $displayData['lNumDisplayRows']      = $this->clsUsers->lNumDirRows;
         $displayData['directory']            = $this->clsUsers->directory;
         $displayData['directoryRecsPerPage'] = $lRecsPerPage;
         $displayData['directoryStartRec']    = $lStartRec;
      }

      $displayData['mainTemplate'] = 'admin/user_directory_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function view($lUserID){
   //------------------------------------------------------------------------
   // View User Record
   //------------------------------------------------------------------------
      $this->load->helper('user/user_acct');

      commonAcctView($lUserID, true, $displayData);
   }

   function addEdit($lUserID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('user/user_acct');

      commonAcctEdit($lUserID, true);
   }

      //-------------------------------
      // verification callbacks
      //-------------------------------
   function verifyUniqueUserID($strUserName, $id){
      $id = (integer)$id;
      $strUserName = xss_clean(trim($strUserName));
      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                $strUserName, 'us_strUserName',
                $id,   'us_lKeyID',
                true,  'us_bInactive',
                false, null, null,
                false, null, null,
                'admin_users')){
         return(false);
      }else {
         return(true);
      }
   }
   
   function verifyPID($strPID){
   //---------------------------------------------------------------------
   // 
   //---------------------------------------------------------------------
      $strPID = trim($strPID.'');
      if ($strPID == '') return(true);
      
      $this->load->model('admin/madmin_aco');
      $this->load->model('people/mpeople',   'cPTest');
      $this->cPTest->loadPeopleViaPIDs((int)$strPID, false, false);
      if ($this->cPTest->lNumPeople == 0){
         $this->form_validation->set_message('verifyPID',
                   'The People ID is not valid.');
         return(false);
      }else {
         return(true);
      }
   }

   function verifyPWordRequired($strPWord1, $id){
      $id = (integer)$id;
      $strPWord2 =  trim($_POST['txtPWord2']); // set_value('txtPWord2');
      if ($id==0){
         return($strPWord1!='');
      }else {
         return(true);
      }
   }

   function verifyPWordsMatch($strPWord2){
      $strPWord1 =  trim($_POST['txtPWord1']);
      return($strPWord1==$strPWord2);
   }

   function deactivate($lUserID){
      $this->activeInactive($lUserID, true);
   }
   
   function activate($lUserID){
      $this->activeInactive($lUserID, false);
   }
   
   function activeInactive($lUserID, $bSetToInactive){
      $lUserID = (integer)$lUserID;
      $this->load->model('admin/muser_accts',  'clsUser');
      $this->load->model('admin/mpermissions', 'perms');
      $this->clsUser->loadSingleUserRecord($lUserID);
      $userRec = $this->clsUser->userRec[0];

      if ($bSetToInactive){
         $this->clsUser->inactivateUserAccount($lUserID);
      }else {
         $this->clsUser->activateUserAccount($lUserID);
      }
      $this->session->set_flashdata('msg', 'The user account for '.$userRec->strSafeName
               .' was '.($bSetToInactive ? 'INACTIVATED' : 'ACTIVATED'));
      redirect('admin/accts/view/'.$lUserID);
   }

   function userAccess(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      $displayData = array();
      $displayData['js'] = '';
      
      if (!bTestForURLHack('adminOnly')) return;
   
         //--------------------------------------------
         // models/helpers
         //--------------------------------------------
      $this->load->helper('dl_util/web_layout');
      $this->load->model ('admin/mpermissions', 'perms');
      $this->load->model ('admin/muser_accts',  'cuser');      
      
      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();
   
      $this->cuser->loadUsersByAccess($displayData['access']);
   
         //-------------------------------------
         // stripes
         //-------------------------------------
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;
   
         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['title']        = CS_PROGNAME.' | Admin';
      $displayData['pageTitle']    =
                        anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                 .' | Users Via Access';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = 'admin/user_via_access_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   
   }
   
   
   
   
   
   
   
   
   
}