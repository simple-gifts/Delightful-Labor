<?php
/*---------------------------------------------------------------------
shortcut to the session array
note: load after session_start
---------------------------------------------------------------------*/

   function setNameSpace($bInstall = false){
      if ($bInstall){
         define('CS_NAMESPACE', 'dl_install');
      }else {
         $CI =& get_instance();
         $CI->load->database();
         define('CS_NAMESPACE', 'dl_'.$CI->db->database);
         define('CS_NAMESPACE_VOLREG', 'dlv_'.$CI->db->database);
      }
   }

   function setGlobals(&$clsLocal) {
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      setNameSpace(false);   // also loads the database

         //--------------------------------------------------------------------------------
         // to insure global scope, global vars defined in helpers/lnpConfig_helper.php
         //--------------------------------------------------------------------------------
      global $gstrFName, $gstrLName, $gstrUserName, $gstrSafeName,
             $gbAdmin, $gbStandardUser, $gbDev, $gbVolLogin, $gVolPerms, $gUserPerms,
             $glVolPeopleID, $gbUserInMgrGroup,
             $genumDateFormat, $glclsDTDateFormat, $glclsDTDateTimeFormat,
             $gstrDateFormatLabel, $gbDateFormatUS, $gstrFormatDatePicker,
             $genumMeasurePref, $gbMetric,
             $gstrBrowser, $gstrBrowser, $gbBrowserIE, $gbBrowserChrome,
             $gbBrowserFirefox, $gbBrowserSafari,
             $gdteNow, $glUserID, $gclsChapter, $glChapterID;

         // did we lose our session?
      if (!isset($_SESSION[CS_NAMESPACE.'user'])) {
         redirect('login');
      }

      $glChapterID      = 1;  // reserved

      $glUserID         = @$_SESSION[CS_NAMESPACE.'user']->lUserID;

      $gstrFName        = @$_SESSION[CS_NAMESPACE.'user']->strFirstName;
      $gstrLName        = @$_SESSION[CS_NAMESPACE.'user']->strLastName;
      $gstrUserName     = @$_SESSION[CS_NAMESPACE.'user']->strUserName;
      $gstrSafeName     = @$_SESSION[CS_NAMESPACE.'user']->strSafeName;
      $gbAdmin          = @$_SESSION[CS_NAMESPACE.'user']->bAdmin;
      $gbStandardUser   = @$_SESSION[CS_NAMESPACE.'user']->bStandardUser;
      $gbVolLogin       = @$_SESSION[CS_NAMESPACE.'user']->bVolLogin;
      $gbDev            = @$_SESSION[CS_NAMESPACE.'user']->bDebugger;
      $genumDateFormat  = @$_SESSION[CS_NAMESPACE.'user']->enumDateFormat;
      $genumMeasurePref = @$_SESSION[CS_NAMESPACE.'user']->enumMeasurePref;
      $gbMetric         = $genumMeasurePref == 'metric';

      if ($gbVolLogin){
         $gVolPerms = new stdClass;
         $gVolPerms->bVolEditContact      = $_SESSION[CS_NAMESPACE.'user']->volPerms->bVolEditContact;
         $gVolPerms->bVolPassReset        = $_SESSION[CS_NAMESPACE.'user']->volPerms->bVolPassReset;
         $gVolPerms->bVolViewGiftHistory  = $_SESSION[CS_NAMESPACE.'user']->volPerms->bVolViewGiftHistory;
         $gVolPerms->bVolEditJobSkills    = $_SESSION[CS_NAMESPACE.'user']->volPerms->bVolEditJobSkills;
         $gVolPerms->bVolViewHrsHistory   = $_SESSION[CS_NAMESPACE.'user']->volPerms->bVolViewHrsHistory;
         $gVolPerms->bVolAddVolHours      = $_SESSION[CS_NAMESPACE.'user']->volPerms->bVolAddVolHours;
         $gVolPerms->bVolShiftSignup      = $_SESSION[CS_NAMESPACE.'user']->volPerms->bVolShiftSignup;

         $glVolPeopleID = $_SESSION[CS_NAMESPACE.'user']->lPeopleID;

      }elseif ($gbStandardUser){
         $gUserPerms = new stdClass;
         $gUserPerms->bUserDataEntryPeople      = $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserDataEntryPeople;
         $gUserPerms->bUserDataEntryGifts       = $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserDataEntryGifts;
         $gUserPerms->bUserEditPeople           = $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserEditPeople;
         $gUserPerms->bUserEditGifts            = $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserEditGifts;
         $gUserPerms->bUserViewPeople           = $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserViewPeople;
         $gUserPerms->bUserViewGiftHistory      = $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserViewGiftHistory;
         $gUserPerms->bUserViewReports          = $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserViewReports;
         $gUserPerms->bUserAllowExports         = $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserAllowExports;
         $gUserPerms->bUserAllowSponsorship     = $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserAllowSponsorship;
         $gUserPerms->bUserAllowSponFinancial   = $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserAllowSponFinancial;
         $gUserPerms->bUserAllowClient          = $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserAllowClient;
         $gUserPerms->bUserAllowAuctions        = $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserAllowAuctions;
         $gUserPerms->bUserAllowGrants          = $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserAllowGrants;
         $gUserPerms->bUserAllowInventory       = $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserAllowInventory;
         $gUserPerms->bTimeSheetAdmin           = $_SESSION[CS_NAMESPACE.'user']->userPerms->bTimeSheetAdmin;
         $gUserPerms->bUserVolManager           = $_SESSION[CS_NAMESPACE.'user']->userPerms->bUserVolManager;

            // personalized table access
         $gUserPerms->ptables     = @$_SESSION[CS_NAMESPACE.'user']->userPerms->ptables;
         $gUserPerms->lNumPTables = @$_SESSION[CS_NAMESPACE.'user']->userPerms->lNumPTables;

         $gUserPerms->lNumUserGroups = $lNumGroups = @$_SESSION[CS_NAMESPACE.'user']->lNumUserGroups;
         if ($lNumGroups > 0){
            $gUserPerms->strUGroups = arrayCopy($_SESSION[CS_NAMESPACE.'user']->strUGroups);
         }
      }else {
         $gVolPerms  =
         $gUserPerms = null;
      }
      if (!$gbVolLogin){
         if (isset($_SESSION[CS_NAMESPACE.'user']->bUserMgrStaffGroup)){
            $gbUserInMgrGroup = $_SESSION[CS_NAMESPACE.'user']->bUserMgrStaffGroup;
         }else {
            $gbUserInMgrGroup = $_SESSION[CS_NAMESPACE.'user']->bUserMgrStaffGroup = false;
         }
      }

      $gstrBrowser      = @$_SESSION[CS_NAMESPACE.'browser'];
      $gbBrowserIE      = $gstrBrowser=='IE';
      $gbBrowserChrome  = $gstrBrowser=='Chrome';
      $gbBrowserFirefox = $gstrBrowser=='Firefox';
      $gbBrowserSafari  = $gstrBrowser=='Safari';

      $gdteNow = time();

         // php error testing
      if ($gbDev){
         error_reporting(E_ALL | E_STRICT);
      }else {
         error_reporting(0);
      }

      switch ($genumDateFormat){
         case 'j M Y':
         case 'j F Y':
         case 'd/m/Y':
            $gstrDateFormatLabel  = 'dd/mm/yyyy';
            $gbDateFormatUS       = false;
            $gstrFormatDatePicker = 'd/m/Y';
            break;
         case 'M j Y':
         case 'F j Y':
         case 'm/d/Y':
         default:
            $gstrDateFormatLabel  = 'mm/dd/yyyy';
            $gbDateFormatUS       = true;
            $gstrFormatDatePicker = 'm/d/Y';
            break;
      }

      switch ($genumDateFormat){
         case 'M j Y':
            $glclsDTDateFormat     = 6;
            $glclsDTDateTimeFormat = 4;
            break;
         case 'F j Y':
            $glclsDTDateFormat     = 7;
            $glclsDTDateTimeFormat = 4;
            break;
         case 'm/d/Y':
            $glclsDTDateFormat     = 1;
            $glclsDTDateTimeFormat = 4;
            break;

         case 'j M Y':
            $glclsDTDateFormat     = 16;
            $glclsDTDateTimeFormat = 14;
            break;
         case 'j F Y':
            $glclsDTDateFormat     = 17;
            $glclsDTDateTimeFormat = 14;
            break;
         case 'd/m/Y':
            $glclsDTDateFormat     = 11;
            $glclsDTDateTimeFormat = 14;
            break;
         default:
            $glclsDTDateFormat     = 2;
            $glclsDTDateTimeFormat = 4;
            break;
      }

      setChapterGlobals();

      if (CB_AAYHF){
         aayhf\setSession();
      }
         // set the time zone
//      date_default_timezone_set($clsLocal->config->item('dl_timezone'));
      date_default_timezone_set($gclsChapter->strTimeZone);
//$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
//.":\date_default_timezone_get  = ".date_default_timezone_get ()."<br></font>\n");

   }

   function setChapterGlobals(){
   //---------------------------------------------------------------------------------
   //
   //---------------------------------------------------------------------------------
      global $gclsChapter, $glChapterID, $gbDefaultUS_DateFormat,
         $gclsChapterACO, $gclsChapterVoc;

      $gclsChapter      = $_SESSION[CS_NAMESPACE.'_chapter'];
      $glChapterID      = $gclsChapter->lChapterID;
      $gbDefaultUS_DateFormat = @$_SESSION[CS_NAMESPACE.'_chapter']->bUS_DateFormat;

      $gclsChapterVoc = new stdClass;
      $gclsChapterVoc->vocZip       = @$_SESSION[CS_NAMESPACE.'_chapter']->vocZip;
      $gclsChapterVoc->vocState     = @$_SESSION[CS_NAMESPACE.'_chapter']->vocState;
      $gclsChapterVoc->vocJobSkills = @$_SESSION[CS_NAMESPACE.'_chapter']->vocJobSkills;

      $gclsChapterACO = new stdClass;
      $gclsChapterACO->lKeyID            = @$_SESSION[CS_NAMESPACE.'_chapter']->ACO_lKeyID;
      $gclsChapterACO->strFlag           = @$_SESSION[CS_NAMESPACE.'_chapter']->ACO_strFlag;
      $gclsChapterACO->strName           = @$_SESSION[CS_NAMESPACE.'_chapter']->ACO_strName;
      $gclsChapterACO->strCurrencySymbol = @$_SESSION[CS_NAMESPACE.'_chapter']->ACO_strCurrencySymbol;
      $gclsChapterACO->bInUse            = @$_SESSION[CS_NAMESPACE.'_chapter']->ACO_bInUse;
      $gclsChapterACO->bDefault          = @$_SESSION[CS_NAMESPACE.'_chapter']->ACO_bDefault;
      $gclsChapterACO->strFlagImg        = @$_SESSION[CS_NAMESPACE.'_chapter']->ACO_strFlagImg;
   }

   function setVolRegGlobals(&$cACO, &$cOrg){
   //---------------------------------------------------------------------
   // used by the volunteer registration form
   //---------------------------------------------------------------------
      global
         $gclsChapter, $glChapterID, $gbDateFormatUS, $gclsChapterVoc,
         $gclsChapterACO, $gdteNow, $gbDev;

      setNameSpace(false);
      $cOrg->lChapterID = CL_DEFAULT_CHAPTERID;
      $cOrg->loadChapterInfo();

      $gdteNow = time();

         // php error testing
      if ($gbDev){
         error_reporting(E_ALL | E_STRICT);
      }else {
         error_reporting(0);
      }

      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter'] = new stdClass;
      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->lChapterID     = $cOrg->chapterRec->lKeyID;
      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->strChapterName = $cOrg->chapterRec->strChapterName;
      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->strDefAreaCode = $cOrg->chapterRec->strDefAreaCode;
      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->strDefState    = $cOrg->chapterRec->strDefState;
      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->strDefCountry  = $cOrg->chapterRec->strDefCountry;
      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->strBanner      = $cOrg->chapterRec->strBannerTagLine;
      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->bUS_DateFormat = $gbDateFormatUS = $cOrg->chapterRec->bUS_DateFormat;

      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->vocZip       = $cOrg->chapterRec->vocabulary->vocZip;
      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->vocState     = $cOrg->chapterRec->vocabulary->vocState;
      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->vocJobSkills = $cOrg->chapterRec->vocabulary->vocJobSkills;      

         //-------------------------------
         // set the default ACO info
         //-------------------------------
      $lACOID = $cACO->lLoadDefaultCountryID();
      if (is_null($lACOID)) $lACOID = 1;
      $cACO->loadCountries(false, false, true, $lACOID);

      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->ACO_lKeyID            = $lACOID;
      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->ACO_strFlag           = $cACO->countries[0]->strFlag;
      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->ACO_strName           = $cACO->countries[0]->strName;
      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->ACO_strCurrencySymbol = $cACO->countries[0]->strCurrencySymbol;
      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->ACO_bInUse            = $cACO->countries[0]->bInUse;
      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->ACO_bDefault          = $cACO->countries[0]->bDefault;
      $_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->ACO_strFlagImg        = $cACO->countries[0]->strFlagImg;

      $gclsChapter      = $_SESSION[CS_NAMESPACE_VOLREG.'_chapter'];
      $glChapterID      = $gclsChapter->lChapterID;
      $gbDateFormatUS   = @$_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->bUS_DateFormat;

      $gclsChapterACO = new stdClass;
      $gclsChapterACO->lKeyID            = @$_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->ACO_lKeyID;
      $gclsChapterACO->strFlag           = @$_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->ACO_strFlag;
      $gclsChapterACO->strName           = @$_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->ACO_strName;
      $gclsChapterACO->strCurrencySymbol = @$_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->ACO_strCurrencySymbol;
      $gclsChapterACO->bInUse            = @$_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->ACO_bInUse;
      $gclsChapterACO->bDefault          = @$_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->ACO_bDefault;
      $gclsChapterACO->strFlagImg        = @$_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->ACO_strFlagImg;

      $gclsChapterVoc = new stdClass;
      $gclsChapterVoc->vocZip       = @$_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->vocZip;
      $gclsChapterVoc->vocState     = @$_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->vocState;
      $gclsChapterVoc->vocJobSkills = @$_SESSION[CS_NAMESPACE_VOLREG.'_chapter']->vocJobSkills;
   }


?>