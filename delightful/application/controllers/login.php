<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2012-2015 Database Austin

   author: John Zimmerman

   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------*/

class login extends CI_CONTROLLER {

   function __construct(){
      parent::__construct();
      session_start();
   }

   public function index(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      phpVersionTestDL();  // catch php version error immediately

      if ($this->input->post('username')){
         $u  = $this->input->post('username');
         $pw = $this->input->post('password');
         $this->load->model('admin/muser_accts',   'clsUserAccts');
         $this->load->model('admin/muser_log',     'clsUserLog');
         $this->load->model('admin/mpermissions',  'perms');
         $this->load->model ('staff/mtime_sheets', 'cts');
         $this->load->helper('dl_util/util_db');
         setNameSpace(false);   // also loads database

         $this->clsUserLog->el_lUserID = $lUserID = $this->clsUserAccts->verifyUser($u, $pw, $bAdmin);

         $bSuccess = $lUserID > 0;
         $this->clsUserLog->lAddLogEntry($bSuccess, $u);
         if ($lUserID > 0){
            if (!$this->clsUserAccts->bVerifyDBVersion($strExpected, $strActual)){
               if ($bAdmin){
                  $_SESSION[CS_NAMESPACE.'user'] = new stdClass;
                  $_SESSION[CS_NAMESPACE.'user']->lUserID           = $lUserID;
                  $_SESSION[CS_NAMESPACE.'user']->bAdmin            = true;
                  $this->session->set_flashdata('error', 'Your database is not the correct level for this version of the Delightful Labor!<br><br>
                                             expected db level: <b>'.$strExpected.'</b><br>
                                             actual db level: <b>'.$strActual.'</b><br><br>
                                             Please upgrade your database before continuing.');
                  redirect('upgrade_db');
               }else {
                  $this->session->set_flashdata('error', 'Your database is not the correct level for this version of the Delightful Labor!<br><br>
                                             expected db level: <b>'.$strExpected.'</b><br>
                                             actual db level: <b>'.$strActual.'</b><br><br>
                                             Please contact your system administrator.');
                  redirect('login');
               }
            }

            $this->clsUserAccts->loadSingleUserRecord($lUserID);
            $clsUser = $this->clsUserAccts->userRec[0];

            $lChapterID = $clsUser->us_lChapterID;
            $this->setChapterSession($lChapterID, $lACOID);

               // initialize custom navigation
            $_SESSION[CS_NAMESPACE.'nav'] = new stdClass;
            $_SESSION[CS_NAMESPACE.'nav']->lCnt = 0;
            $_SESSION[CS_NAMESPACE.'nav']->navFiles = array();

            $this->setBrowserInfo();
            $_SESSION[CS_NAMESPACE.'user'] = new stdClass;
            $_SESSION[CS_NAMESPACE.'user']->lUserID           = $lUserID;
            $_SESSION[CS_NAMESPACE.'user']->strUserName       = $clsUser->us_strUserName;
            $_SESSION[CS_NAMESPACE.'user']->bAdmin            = $bAdmin = $clsUser->us_bAdmin;
            $_SESSION[CS_NAMESPACE.'user']->bVolLogin         = $bVolLogin = $clsUser->bVolAccount;
            $_SESSION[CS_NAMESPACE.'user']->bStandardUser     = $bStandardUser = $clsUser->bStandardUser;
            $_SESSION[CS_NAMESPACE.'user']->bDebugger         = $clsUser->us_bDebugger;
            $_SESSION[CS_NAMESPACE.'user']->strFirstName      = $clsUser->us_strFirstName;
            $_SESSION[CS_NAMESPACE.'user']->strLastName       = $clsUser->us_strLastName;
            $_SESSION[CS_NAMESPACE.'user']->strSafeName       = $clsUser->strSafeName;
            $_SESSION[CS_NAMESPACE.'user']->enumDateFormat    = $clsUser->us_enumDateFormat;
            $_SESSION[CS_NAMESPACE.'user']->enumMeasurePref   = $clsUser->us_enumMeasurePref;
            $_SESSION[CS_NAMESPACE.'user']->lRecsPerPage      = 50;

            if ($bVolLogin){
               $_SESSION[CS_NAMESPACE.'user']->volPerms = new stdClass;
               $_SESSION[CS_NAMESPACE.'user']->volPerms->bVolEditContact      = $clsUser->bVolEditContact;
               $_SESSION[CS_NAMESPACE.'user']->volPerms->bVolPassReset        = $clsUser->bVolPassReset;
               $_SESSION[CS_NAMESPACE.'user']->volPerms->bVolViewGiftHistory  = $clsUser->bVolViewGiftHistory;
               $_SESSION[CS_NAMESPACE.'user']->volPerms->bVolEditJobSkills    = $clsUser->bVolEditJobSkills;
               $_SESSION[CS_NAMESPACE.'user']->volPerms->bVolViewHrsHistory   = $clsUser->bVolViewHrsHistory;
               $_SESSION[CS_NAMESPACE.'user']->volPerms->bVolAddVolHours      = $clsUser->bVolAddVolHours;
               $_SESSION[CS_NAMESPACE.'user']->volPerms->bVolShiftSignup      = $clsUser->bVolShiftSignup;

                  // if volunteer log-in and no associated people ID, add people and volunteer record
               if (is_null($clsUser->lPeopleID)){
//                  $this->load->helper('dl_util/email_web');
                  $this->load->model('admin/madmin_aco');
                  $this->load->model ('personalization/muser_fields');
                  $this->load->model('personalization/muser_fields_create');
                  $this->load->model('people/mpeople');
                  $this->load->model('vols/mvol');
                  $clsUser->lPeopleID = $this->clsUserAccts->lPeopleVolRecViaAcct($lChapterID, $lACOID, $lUserID);
               }
               $_SESSION[CS_NAMESPACE.'user']->lPeopleID = $clsUser->lPeopleID;
            }elseif ($bStandardUser){
               $_SESSION[CS_NAMESPACE.'user']->userPerms = new stdClass;
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserDataEntryPeople    = $clsUser->bUserDataEntryPeople;
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserDataEntryGifts     = $clsUser->bUserDataEntryGifts;
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserEditPeople         = $clsUser->bUserEditPeople;
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserEditGifts          = $clsUser->bUserEditGifts;
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserViewPeople         = $clsUser->bUserViewPeople;
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserViewGiftHistory    = $clsUser->bUserViewGiftHistory;
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserViewReports        = $clsUser->bUserViewReports;
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserAllowExports       = $clsUser->bUserAllowExports;
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserAllowSponsorship   = $clsUser->bUserAllowSponsorship;
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserAllowSponFinancial = $clsUser->bUserAllowSponFinancial;
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserAllowClient        = $clsUser->bUserAllowClient;
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserAllowAuctions      = $clsUser->bUserAllowAuctions;
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserAllowGrants        = $clsUser->bUserAllowGrants;
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserAllowInventory     = $clsUser->bUserAllowInventory;
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserVolManager         = $clsUser->bUserVolManager;

                  // time sheet admin?
               $_SESSION[CS_NAMESPACE.'user']->userPerms->bTimeSheetAdmin  = $this->cts->bIsUserTSAdmin($lUserID);

                  // personalized table access
               $this->load->model('personalization/muser_table_perms', 'tperms');
               $_SESSION[CS_NAMESPACE.'user']->userPerms->ptables =
                        $this->tperms->loadUserTableAccess($lUserID, $_SESSION[CS_NAMESPACE.'user']->userPerms->lNumPTables);

            }else {
               $_SESSION[CS_NAMESPACE.'user']->volPerms  = null;
               $_SESSION[CS_NAMESPACE.'user']->userPerms = null;
            }

            if (!$bVolLogin){
               $this->setCustomNavigation();

                  // determine if this user is in the "Management" staff group
               $this->load->model('staff/mstaff_status', 'cstat');
               $lMgrGroup = $this->cstat->lStaffManagementGroupID();
               if (is_null($lMgrGroup)){
                  $_SESSION[CS_NAMESPACE.'user']->bUserMgrStaffGroup = false;
               }else {
                  $_SESSION[CS_NAMESPACE.'user']->bUserMgrStaffGroup =
                                        $this->cstat->bIsUserInManagement($lUserID, $lMgrGroup);
               }

                  // load user groups
               $this->load->model  ('groups/mgroups',           'groups');
               $this->load->helper ('groups/groups');
               $this->groups->groupMembershipViaFID(CENUM_CONTEXT_USER, $lUserID);
               $_SESSION[CS_NAMESPACE.'user']->lNumUserGroups = $lCntUG = count($this->groups->arrMemberInGroups);
               if ($lCntUG > 0){
                  $_SESSION[CS_NAMESPACE.'user']->strUGroups = array();
                  $_SESSION[CS_NAMESPACE.'user']->lUGroupIDs = array();
                  foreach ($this->groups->arrMemberInGroups as $UGrp){
                     $_SESSION[CS_NAMESPACE.'user']->strUGroups[] = $UGrp->strGroupName;
                     $_SESSION[CS_NAMESPACE.'user']->lUGroupIDs[] = $UGrp->lGroupID;
                  }
               }


               if (CB_AAYHF){
                     // update folding stats
                  if ($lUserID==1 || $lUserID==22){
                     $this->load->model ('aayhf/aayhf_programs/dell_lab/mfolding', 'cfolding');
                     $this->cfolding->writeFoldingStats($lUserID);
                  }

                     //------------------------------------------------------------
                     // set up special access for SHIFT Ascension users
                     //------------------------------------------------------------
                  $bShiftAccess = false;
                  $_SESSION[CS_NAMESPACE.'user']->shiftAscension = new stdClass;
                  $_SESSION[CS_NAMESPACE.'user']->shiftAscension->bShiftAdmin =
                      $bShiftAdmin = $bAdmin || ($lUserID==8) || ($lUserID==4)
                                             || ($lUserID==6) || ($lUserID==49);
                  if ($bShiftAdmin){
                     $_SESSION[CS_NAMESPACE.'user']->shiftAscension->bShiftAccess = $bShiftAccess = true;
                  }else {
                     $_SESSION[CS_NAMESPACE.'user']->shiftAscension->bShiftAccess = false;
                     foreach ($this->groups->arrMemberInGroups as $UGrp){
                        if (strtoupper(substr($UGrp->strGroupName, 0, 15))=='SHIFT ASCENSION'){
                           $_SESSION[CS_NAMESPACE.'user']->shiftAscension->bShiftAccess = $bShiftAccess = true;
                           break;
                        }
                     }
                  }
                     // load the shift groups the user belongs to
                     // not needed for shift admins - they have access to all programs
                  if (!$bShiftAdmin && $_SESSION[CS_NAMESPACE.'user']->shiftAscension->bShiftAccess){
                     $_SESSION[CS_NAMESPACE.'user']->shiftAscension->shiftGroups = array();
                     $shiftIdx = 0;
                     foreach ($this->groups->arrMemberInGroups as $UGrp){
                        if (strtoupper(substr($UGrp->strGroupName, 0, 15))=='SHIFT ASCENSION'){
                           $_SESSION[CS_NAMESPACE.'user']->shiftAscension->shiftGroups[$shiftIdx] = new stdClass;
                           $_SESSION[CS_NAMESPACE.'user']->shiftAscension->shiftGroups[$shiftIdx]->lGroupID = $UGrp->lGroupID;
                           $_SESSION[CS_NAMESPACE.'user']->shiftAscension->shiftGroups[$shiftIdx]->strGroupName = $UGrp->strGroupName;
                           ++$shiftIdx;
                        }
                     }
                  }
               }
            }
            redirect('welcome');
         }else {
            $this->session->set_flashdata('error', 'Your login information was not correct.');
            redirect('login');
         }
      }
      $data['main'] = 'login';
      $this->load->view('login', $data);
   }

   function setBrowserInfo(){
   //---------------------------------------------------------------------
   // http://www.mydigitallife.info/how-to-detect-browser-type-in-php/
   //---------------------------------------------------------------------
      $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

      $_SESSION[CS_NAMESPACE.'browser'] = 'Unknown';

      if (strpos($user_agent, 'msie') !== false) {
         $_SESSION[CS_NAMESPACE.'browser'] = 'IE';
      } elseif (strpos($user_agent, 'chrome') !== false) {
         $_SESSION[CS_NAMESPACE.'browser'] = 'Chrome';
      } elseif (strpos($user_agent, 'firefox') !== false) {
         $_SESSION[CS_NAMESPACE.'browser'] = 'Firefox';
      } elseif (strpos($user_agent, 'safari') !== false) {
         $_SESSION[CS_NAMESPACE.'browser'] = 'Safari';
      }
   }

   function setChapterSession($lChapterID, &$lACOID){
   //---------------------------------------------------------------------------------
   //
   //---------------------------------------------------------------------------------
      $this->load->model('admin/madmin_aco', 'clsACO');
      $this->load->model('admin/morganization', 'clsChapter');

      $this->clsChapter->lChapterID = $lChapterID;
      $this->clsChapter->loadChapterInfo();
      
      $_SESSION[CS_NAMESPACE.'_chapter'] = new stdClass;
      $_SESSION[CS_NAMESPACE.'_chapter']->lChapterID     = $this->clsChapter->chapterRec->lKeyID;
      $_SESSION[CS_NAMESPACE.'_chapter']->strChapterName = $this->clsChapter->chapterRec->strChapterName;
      $_SESSION[CS_NAMESPACE.'_chapter']->strDefAreaCode = $this->clsChapter->chapterRec->strDefAreaCode;
      $_SESSION[CS_NAMESPACE.'_chapter']->strDefState    = $this->clsChapter->chapterRec->strDefState;
      $_SESSION[CS_NAMESPACE.'_chapter']->strDefCountry  = $this->clsChapter->chapterRec->strDefCountry;
      $_SESSION[CS_NAMESPACE.'_chapter']->strBanner      = $this->clsChapter->chapterRec->strBannerTagLine;
      $_SESSION[CS_NAMESPACE.'_chapter']->bUS_DateFormat = $this->clsChapter->chapterRec->bUS_DateFormat;

      $_SESSION[CS_NAMESPACE.'_chapter']->lTimeZoneID    = $this->clsChapter->chapterRec->lTimeZoneID;
      $_SESSION[CS_NAMESPACE.'_chapter']->strTimeZone    = $this->clsChapter->chapterRec->strTimeZone;
      
      $_SESSION[CS_NAMESPACE.'_chapter']->vocZip       = $this->clsChapter->chapterRec->vocabulary->vocZip;
      $_SESSION[CS_NAMESPACE.'_chapter']->vocState     = $this->clsChapter->chapterRec->vocabulary->vocState;
      $_SESSION[CS_NAMESPACE.'_chapter']->vocJobSkills = $this->clsChapter->chapterRec->vocabulary->vocJobSkills;
      

         //-------------------------------
         // set the default ACO info
         //-------------------------------
      $lACOID = $this->clsACO->lLoadDefaultCountryID();
      if (is_null($lACOID)) $lACOID = 1;
      $this->clsACO->loadCountries(false, false, true, $lACOID);

      $_SESSION[CS_NAMESPACE.'_chapter']->ACO_lKeyID            = $lACOID;
      $_SESSION[CS_NAMESPACE.'_chapter']->ACO_strFlag           = $this->clsACO->countries[0]->strFlag;
      $_SESSION[CS_NAMESPACE.'_chapter']->ACO_strName           = $this->clsACO->countries[0]->strName;
      $_SESSION[CS_NAMESPACE.'_chapter']->ACO_strCurrencySymbol = $this->clsACO->countries[0]->strCurrencySymbol;
      $_SESSION[CS_NAMESPACE.'_chapter']->ACO_bInUse            = $this->clsACO->countries[0]->bInUse;
      $_SESSION[CS_NAMESPACE.'_chapter']->ACO_bDefault          = $this->clsACO->countries[0]->bDefault;
      $_SESSION[CS_NAMESPACE.'_chapter']->ACO_strFlagImg        = $this->clsACO->countries[0]->strFlagImg;
   }

   function setCustomNavigation(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $navFiles = scandir('./application/models/custom_navigation');
      if ($navFiles !== false){
         foreach ($navFiles as $strFN){
            if (strrev(substr(strrev($strFN), 0, 4)) == '.php'){
               ++$_SESSION[CS_NAMESPACE.'nav']->lCnt;
               $_SESSION[CS_NAMESPACE.'nav']->navFiles[] = substr($strFN, 0, strlen($strFN)-4);
            }
         }
      }
   }

   function signout(){
//      setNameSpace(false);
//      unset($_SESSION[CS_NAMESPACE.'user']);
      $_SESSION = array();
      $this->session->sess_destroy();

      redirect('login');
   }


}
