<?php
/*---------------------------------------------------------------------
// copyright (c) 2012-2015 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model('admin/mpermissions', 'perms');
      $this->load->model('admin/muser_accts',  'cusers');
---------------------------------------------------------------------
   __construct          ()
   verifyUser           ($u,$pw)

   bVerifyDBVersion     (&$strExpected, &$strActual)
   strLoadDBLevel       ()
   versionLevel         ()

   bPWValid             ($lUserID, $strPWord)
   changePWord          ($lUserID, $strPWord)
   loadSingleUserRecord ($lUserID)
   loadUserRecords      ()
   lCountUsers          ($bActive, $strLettersLast='')
   loadUserDirectoryPage($strWhereExtra, $lStartRec, $lRecsPerPage)
   dteMostRecentLogin   ($lUserID)
   loadUserRec          ()
   addUserAccount       ()
   updateUserAccount    ()
   inactivateUserAccount($lUserID)
---------------------------------------------------------------------*/

class muser_accts extends CI_Model{
   public $userRec, $lNumRecs,
       $directoryRecsPerPage, $directoryStartRec, $lNumDirRows,
       $versionInfo, $sqlWhere, $sqlOrder;

	function __construct(){
		parent::__construct();
      $this->directoryRecsPerPage =
      $this->directoryStartRec    =
      $this->lNumDirRows          =
      $this->versionInfo          = null;

      $this->sqlWhere = $this->sqlOrder = '';
	}

	function verifyUser($u,$pw, &$bAdmin){
      $lUserID = 0;
      $bAdmin = false;

      $sqlStr =
         'SELECT us_lKeyID, us_bAdmin
          FROM admin_users
          WHERE NOT us_bInactive
             AND us_strUserName='.strPrepStr($u).'
             AND us_strUserPWord=PASSWORD('.strPrepStr($pw).')
          LIMIT 0,1;';

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
		if ($numRows > 0){
         $row = $query->row();
         $lUserID = (int)$row->us_lKeyID;
			$bAdmin  = (boolean)$row->us_bAdmin;
		}
      return($lUserID);
	}

   public function bVerifyDBVersion(&$strExpected, &$strActual){
      $strExpected = number_format($this->config->item('dl_dbLevel'), 3, '.', '');
      $strActual   = $this->strLoadDBLevel();
      return($strExpected == $strActual);
   }

   public function strLoadDBLevel(){
      $sqlStr =
           'SELECT av_sngVersion
            FROM admin_version
            WHERE 1
            ORDER BY av_lKeyID DESC
            LIMIT 0, 1;';
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return(number_format($row->av_sngVersion, 3, '.', ''));
   }

   public function versionLevel(){
   //---------------------------------------------------------------------
   // return database version info
   //---------------------------------------------------------------------
      $sqlStr =
           'SELECT
               av_sngVersion,
               av_strVersionNotes,
               av_dteInstalled
            FROM admin_version
            WHERE 1
            ORDER BY av_lKeyID DESC
            LIMIT 0, 1;';
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      $this->versionInfo = new stdClass;
      $this->versionInfo->dbVersion       = number_format($row->av_sngVersion, 3, '.', '');
      $this->versionInfo->strVersionNotes = $row->av_strVersionNotes;
      $this->versionInfo->dteDBInstall    = strtotime($row->av_dteInstalled);

      $this->versionInfo->softwareDBCompabilityLevel =  number_format($this->config->item('dl_dbLevel'), 3, '.', '');

      $this->versionInfo->softwareLevel =  number_format($this->config->item('dl_softwareLevel'), 3, '.', '');
      $this->versionInfo->softwareDate  =  $this->config->item('dl_softwareDate');
   }

   function bPWValid($lUserID, $strPWord){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = 'SELECT us_lKeyID
                 FROM admin_users
                 WHERE us_strUserPWord=PASSWORD('.strPrepStr($strPWord).")
                    AND us_lKeyID=$lUserID
                 LIMIT 0,1;";
      $query = $this->db->query($sqlStr);
      return($query->num_rows() > 0);
   }

   function changePWord($lUserID, $strPWord){
      global $glUserID;
      $sqlStr = 'UPDATE admin_users
                 SET
                    us_strUserPWord=PASSWORD('.strPrepStr($strPWord)."),
                    us_lLastUpdateID=$glUserID
                 WHERE us_lKeyID=$lUserID;";
      $this->db->query($sqlStr);
   }

   public function strSafeUserNameViaID($lUserID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT us_strFirstName,  us_strLastName
         FROM admin_users
         WHERE us_lKeyID=$lUserID;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return('#error#');
      }else {
         $row = $query->row();
         return(htmlspecialchars($row->us_strFirstName.' '.$row->us_strLastName));
      }
   }

   function loadSingleUserRecord($lUserID){
      $this->sqlWhere = " AND us_lKeyID=$lUserID ";
      $this->loadUserRecords();
   }

   public function loadUserRecords(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cperm = new mpermissions;

      $this->userRec = array();
      $this->lNumRecs = 0;

      if ($this->sqlOrder == ''){
         $sqlOrder = ' us_strLastName, us_strFirstName, us_strUserName, us_lKeyID ';
      }else {
         $sqlOrder = $this->sqlOrder;
      }

		$sqlStr =
         "SELECT
            us_lKeyID,       us_bInactive,     us_lChapterID,
            us_lOriginID,    us_lLastUpdateID, us_strUserName,
            us_bAdmin,       us_bDebugger,     us_bVolAccount,

            us_bVolEditContact,   us_bVolPassReset,      us_bVolViewGiftHistory,
            us_bVolEditJobSkills, us_bVolViewHrsHistory, us_bVolAddVolHours,
            us_bVolShiftSignup,

            us_bUserDataEntryPeople, us_bUserDataEntryGifts, us_bUserEditPeople,
            us_bUserEditGifts,       us_bUserViewPeople,     us_bUserViewGiftHistory,
            us_bUserViewReports,     us_bUserAllowExports,

            us_bUserAllowSponsorship, us_bUserAllowSponFinancial, us_bUserAllowClient,
            us_bUserAllowAuctions, us_bUserAllowGrants, us_bUserAllowInventory,
            us_bUserVolManager,

            us_lPeopleID,

            us_strFirstName, us_strLastName,   us_strTitle, us_strPhone,
            us_strCell,      us_strEmail,      us_strAddr1,
            us_strAddr2,     us_strCity,       us_strState,
            us_strCountry,   us_strZip,

            us_enumDateFormat, us_enumMeasurePref,
            UNIX_TIMESTAMP(us_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(us_dteLastUpdate) AS dteLastUpdate
         FROM admin_users
         WHERE 1 $this->sqlWhere
         ORDER BY $sqlOrder;";

      $query = $this->db->query($sqlStr);
      $this->lNumRecs = $numRows = $query->num_rows();
      if ($numRows == 0){
         $this->userRec[0] = new stdClass;
         $uRec = &$this->userRec[0];

         $uRec->us_lKeyID            =
         $uRec->us_bInactive         =
         $uRec->dteOrigin            =
         $uRec->dteLastUpdate        =
         $uRec->us_lOriginID         =
         $uRec->us_lLastUpdateID     =
         $uRec->us_strUserName       =
         $uRec->us_lChapterID        =

         $uRec->us_bAdmin            =
         $uRec->us_bDebugger         =

         $uRec->bVolAccount          =
         $uRec->bVolEditContact      =
         $uRec->bVolPassReset        =
         $uRec->bVolViewGiftHistory  =
         $uRec->bVolEditJobSkills    =
         $uRec->bVolViewHrsHistory   =
         $uRec->bVolAddVolHours      =
         $uRec->bVolShiftSignup      = null;

         $uRec->bUserDataEntryPeople    =
         $uRec->bUserDataEntryGifts     =
         $uRec->bUserEditPeople         =
         $uRec->bUserEditGifts          =
         $uRec->bUserViewPeople         =
         $uRec->bUserViewGiftHistory    =
         $uRec->bUserViewReports        =
         $uRec->bUserAllowExports       =
         $uRec->bUserAllowSponsorship   =
         $uRec->bUserAllowSponFinancial =
         $uRec->bUserAllowClient        =
         $uRec->bUserAllowGrants        =
         $uRec->bUserAllowInventory     =
         $uRec->bUserAllowAuctions      =
         $uRec->bUserVolManager         = null;

         $uRec->bStandardUser        = true;

         $uRec->lPeopleID            =

         $uRec->us_strFirstName      =
         $uRec->us_strLastName       =
         $uRec->strSafeName          =
         $uRec->us_strTitle          =
         $uRec->us_strPhone          =
         $uRec->us_strCell           =
         $uRec->us_strEmail          =
         $uRec->us_strAddr1          =
         $uRec->us_strAddr2          =
         $uRec->us_strCity           =
         $uRec->us_strState          =
         $uRec->us_strCountry        =
         $uRec->us_strZip            =
         $uRec->strAddress           =
         $uRec->us_enumDateFormat    =
         $uRec->us_enumMeasurePref   = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->userRec[$idx] = new stdClass;
            $uRec = &$this->userRec[$idx];

            $uRec->us_lKeyID               = $lUserID = (int)$row->us_lKeyID;
            $uRec->us_bInactive            = (bool)$row->us_bInactive;
            $uRec->dteOrigin               = $row->dteOrigin;
            $uRec->dteLastUpdate           = $row->dteLastUpdate;
            $uRec->us_lOriginID            = $row->us_lOriginID;
            $uRec->us_lLastUpdateID        = $row->us_lLastUpdateID;
            $uRec->us_strUserName          = $row->us_strUserName;
            $uRec->us_lChapterID           = $row->us_lChapterID;

            $uRec->us_bAdmin               = (bool)$row->us_bAdmin;
            $uRec->us_bDebugger            = (bool)$row->us_bDebugger;

            $uRec->bVolAccount             = (bool)$row->us_bVolAccount;
            $uRec->bVolEditContact         = (bool)$row->us_bVolEditContact;
            $uRec->bVolPassReset           = (bool)$row->us_bVolPassReset;
            $uRec->bVolViewGiftHistory     = (bool)$row->us_bVolViewGiftHistory;
            $uRec->bVolEditJobSkills       = (bool)$row->us_bVolEditJobSkills;
            $uRec->bVolViewHrsHistory      = (bool)$row->us_bVolViewHrsHistory;
            $uRec->bVolAddVolHours         = (bool)$row->us_bVolAddVolHours;
            $uRec->bVolShiftSignup         = (bool)$row->us_bVolShiftSignup;

            $uRec->bStandardUser           = !$uRec->bVolAccount && !$uRec->us_bAdmin;

            $uRec->bUserDataEntryPeople    = (bool)$row->us_bUserDataEntryPeople;
            $uRec->bUserDataEntryGifts     = (bool)$row->us_bUserDataEntryGifts;
            $uRec->bUserEditPeople         = (bool)$row->us_bUserEditPeople;
            $uRec->bUserEditGifts          = (bool)$row->us_bUserEditGifts;
            $uRec->bUserViewPeople         = (bool)$row->us_bUserViewPeople;
            $uRec->bUserViewGiftHistory    = (bool)$row->us_bUserViewGiftHistory;
            $uRec->bUserViewReports        = (bool)$row->us_bUserViewReports;
            $uRec->bUserAllowExports       = (bool)$row->us_bUserAllowExports;

            $uRec->bUserAllowSponsorship   = (bool)$row->us_bUserAllowSponsorship;
            $uRec->bUserAllowSponFinancial = (bool)$row->us_bUserAllowSponFinancial;
            $uRec->bUserAllowClient        = (bool)$row->us_bUserAllowClient;
            $uRec->bUserAllowAuctions      = (bool)$row->us_bUserAllowAuctions;
            $uRec->bUserAllowInventory     = (bool)$row->us_bUserAllowInventory;
            $uRec->bUserAllowGrants        = (bool)$row->us_bUserAllowGrants;
            $uRec->bUserVolManager         = (bool)$row->us_bUserVolManager;

            $uRec->lPeopleID               = $row->us_lPeopleID;

            $uRec->us_strFirstName         = $row->us_strFirstName;
            $uRec->us_strLastName          = $row->us_strLastName;
            $uRec->strSafeName             = htmlspecialchars($row->us_strFirstName.' '.$row->us_strLastName);
            $uRec->strSafeNameLF           = htmlspecialchars($row->us_strLastName.', '.$row->us_strFirstName);
            $uRec->us_strTitle             = $row->us_strTitle;
            $uRec->us_strPhone             = $row->us_strPhone;
            $uRec->us_strCell              = $row->us_strCell;
            $uRec->us_strEmail             = $row->us_strEmail;
            $uRec->us_strAddr1             = $row->us_strAddr1;
            $uRec->us_strAddr2             = $row->us_strAddr2;
            $uRec->us_strCity              = $row->us_strCity;
            $uRec->us_strState             = $row->us_strState;
            $uRec->us_strCountry           = $row->us_strCountry;
            $uRec->us_strZip               = $row->us_strZip;
            $uRec->strAddress              =
                        strBuildAddress(
                           $uRec->us_strAddr1, $uRec->us_strAddr2,   $uRec->us_strCity,
                           $uRec->us_strState, $uRec->us_strCountry, $uRec->us_strZip,
                           true);
            $uRec->us_enumDateFormat    = $row->us_enumDateFormat;
            $uRec->us_enumMeasurePref   = $row->us_enumMeasurePref;

            $cperm->loadUserGroups($lUserID, $uRec->userGroups, $uRec->lGroupIDs);
            ++$idx;
         }
      }
   }

   public function lCountUsers($bActive, $strLettersLast=''){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strWhereExtra = '  ';
      if (is_null($bActive)){
         $strActive = '';
      }else {
         $strActive = ' AND '.($bActive ? ' NOT ' : ' ').' us_bInactive ';
      }

      if (!($strLettersLast.'' == '')||($strLettersLast.'' == '*')){
         $strWhereExtra .= strNameWhereClauseViaLetter('us_strLastName', $strLettersLast);
      }

      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM admin_users
         WHERE 1
            $strWhereExtra $strActive;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
         return($row->lNumRecs);
      }
   }

   public function loadUserDirectoryPage($strWhereExtra, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cperm = new mpermissions;

      $this->directory = array();
      $sqlStr =
          "SELECT
              us_lKeyID, us_strUserName, us_strFirstName, us_strLastName,
              us_bInactive, us_bAdmin, us_bVolAccount, us_bDebugger,
              us_strEmail, us_strPhone
           FROM admin_users
           WHERE 1
               $strWhereExtra
           ORDER BY us_strLastName, us_strFirstName, us_lKeyID
           LIMIT $lStartRec, $lRecsPerPage;";

      $query = $this->db->query($sqlStr);

      $this->lNumDirRows = $numRows = $query->num_rows();
      if ($numRows==0) {
//            nobodyHome($strDirLetter);
      }else {
         $idx = 0;
         foreach ($query->result() as $row){

            $this->directory[$idx] = new stdClass;
            $uDir = &$this->directory[$idx];

            $uDir->lUserID         = $lUserID = $row->us_lKeyID;
            $uDir->bAdmin          = (boolean)$row->us_bAdmin;
            $uDir->bVolAccount     = (boolean)$row->us_bVolAccount;
            $uDir->us_bDebugger    = (boolean)$row->us_bDebugger;
            $uDir->dteLastLogin    = $this->dteMostRecentLogin($lUserID);
            $uDir->us_strLastName  = $row->us_strLastName;
            $uDir->us_strFirstName = $row->us_strFirstName;
            $uDir->us_strUserName  = $row->us_strUserName;
            $uDir->us_strPhone     = $row->us_strPhone;
            $uDir->us_strEmail     = $row->us_strEmail;
            $uDir->bInactive       = $row->us_bInactive;
            $cperm->loadUserGroups($lUserID, $uDir->userGroups, $uDir->lGroupIDs);

            ++$idx;
         }
      }
   }

   function dteMostRecentLogin($lUserID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT el_dteLogDate
         FROM admin_usage_log
         WHERE el_lUserID=$lUserID
            AND el_bLoginSuccessful
         ORDER BY el_dteLogDate DESC
         LIMIT 0,1;";
      $query = $this->db->query($sqlStr);

      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(null);
      }else {
         $row = $query->row();
         return(dteMySQLDate2Unix($row->el_dteLogDate));
      }
   }

   public function loadUserRec(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_null($this->us_lKeyID)) screamForHelp('Uninitialized class!</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);

      $sqlStr =
        "SELECT
            userBase.us_lKeyID,       userBase.us_bInactive,
            userBase.us_lOriginID,    userBase.us_lLastUpdateID, userBase.us_strUserName,
            userBase.us_bAdmin,       userBase.us_bVolAccount,

            userBase.us_bVolEditContact, userBase.us_bVolPassReset,
            userBase.us_bVolViewGiftHistory,
            userBase.us_bVolEditJobSkills, userBase.us_bVolViewHrsHistory,
            userBase.us_bVolAddVolHours,   userBase.us_bVolShiftSignup,

            userBase.us_bUserAllowSponsorship, userBase.us_bUserAllowSponFinancial,
            userBase.us_bUserAllowClient, userBase.us_bUserAllowAuctions, userBase.us_bUserAllowGrants,
            userBase.us_bUserAllowInventory,

            userBase.us_bUserDataEntryPeople,
            userBase.us_bUserDataEntryGifts,
            userBase.us_bUserEditPeople,
            userBase.us_bUserEditGifts,
            userBase.us_bUserViewPeople,
            userBase.us_bUserViewGiftHistory,
            userBase.us_bUserViewReports,
            userBase.us_bUserAllowExports,
            userBase.us_bUserVolManager,

            userBase.us_lPeopleID,

            userBase.us_bDebugger,

            userBase.us_strFirstName,
            userBase.us_strLastName,  userBase.us_strTitle,      userBase.us_strPhone,
            userBase.us_strCell,      userBase.us_strEmail,      userBase.us_strAddr1,
            userBase.us_strAddr2,     userBase.us_strCity,       userBase.us_strState,
            userBase.us_strCountry,   userBase.us_strZip,

            userBase.us_enumDateFormat, userBase.us_enumMeasurePref,

            usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
            usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,
            UNIX_TIMESTAMP(userBase.us_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(userBase.us_dteLastUpdate) AS dteLastUpdate

         FROM admin_users AS userBase
            INNER JOIN admin_users AS usersC ON userBase.us_lOriginID    = usersC.us_lKeyID
            INNER JOIN admin_users AS usersL ON userBase.us_lLastUpdateID= usersL.us_lKeyID
         WHERE userBase.us_lKeyID=$this->us_lKeyID;";

      $query = $this->db->query($sqlStr);

      $numRows = $query->num_rows();
      if ($numRows==0) {
      }else {
         $row = $query->row();
         $this->userRec = new stdClass;

         $this->userRec->us_lKeyID               = $row->us_lKeyID;
         $this->userRec->us_bInactive            = $row->us_bInactive;
         $this->userRec->dteOrigin               = $row->dteOrigin;
         $this->userRec->dteLastUpdate           = $row->dteLastUpdate;
         $this->userRec->us_lOriginID            = $row->us_lOriginID;
         $this->userRec->us_lLastUpdateID        = $row->us_lLastUpdateID;
         $this->userRec->us_strUserName          = $row->us_strUserName;
         $this->userRec->us_bAdmin               = $row->us_bAdmin;
         $this->userRec->us_bVolAccount          = $row->us_bVolAccount;

         $this->userRec->us_bVolEditContact      = $row->us_bVolEditContact;
         $this->userRec->us_bVolPassReset        = $row->us_bVolPassReset;
         $this->userRec->us_bVolViewGiftHistory  = $row->us_bVolViewGiftHistory;
         $this->userRec->us_bVolEditJobSkills    = $row->us_bVolEditJobSkills;
         $this->userRec->us_bVolViewHrsHistory   = $row->us_bVolViewHrsHistory;
         $this->userRec->us_bVolAddVolHours      = $row->us_bVolAddVolHours;
         $this->userRec->us_bVolShiftSignup      = $row->us_bVolShiftSignup;
         $this->userRec->bStandardUser           = !$this->userRec->us_bVolAccount && !$this->userRec->us_bAdmin;

         $this->userRec->us_bUserDataEntryPeople = $row->us_bUserDataEntryPeople;
         $this->userRec->us_bUserDataEntryGifts  = $row->us_bUserDataEntryGifts;
         $this->userRec->us_bUserEditPeople      = $row->us_bUserEditPeople;
         $this->userRec->us_bUserEditGifts       = $row->us_bUserEditGifts;
         $this->userRec->us_bUserViewPeople      = $row->us_bUserViewPeople;
         $this->userRec->us_bUserViewGiftHistory = $row->us_bUserViewGiftHistory;
         $this->userRec->us_bUserViewReports     = $row->us_bUserViewReports;
         $this->userRec->us_bUserAllowExports    = $row->us_bUserAllowExports;

         $this->userRec->us_bUserAllowSponsorship   = $row->us_bUserAllowSponsorship;
         $this->userRec->us_bUserAllowSponFinancial = $row->us_bUserAllowSponFinancial;
         $this->userRec->us_bUserAllowClient        = $row->us_bUserAllowClient;
         $this->userRec->us_bUserAllowAuctions      = $row->us_bUserAllowAuctions;
         $this->userRec->us_bUserAllowGrants        = $row->us_bUserAllowGrants;
         $this->userRec->us_bUserAllowInventory     = $row->us_bUserAllowInventory;
         $this->userRec->us_bUserVolManager         = $row->us_bUserVolManager;

         $this->userRec->us_lPeopleID            = $row->us_lPeopleID;

         $this->userRec->us_bDebugger            = $row->us_bDebugger;
         $this->userRec->us_strFirstName         = $row->us_strFirstName;
         $this->userRec->us_strLastName          = $row->us_strLastName;
         $this->userRec->strSafeName             = htmlspecialchars($row->us_strFirstName.' '.$row->us_strLastName);
         $this->userRec->us_strTitle             = $row->us_strTitle;
         $this->userRec->us_strPhone             = $row->us_strPhone;
         $this->userRec->us_strCell              = $row->us_strCell;
         $this->userRec->us_strEmail             = $row->us_strEmail;
         $this->userRec->us_strAddr1             = $row->us_strAddr1;
         $this->userRec->us_strAddr2             = $row->us_strAddr2;
         $this->userRec->us_strCity              = $row->us_strCity;
         $this->userRec->us_strState             = $row->us_strState;
         $this->userRec->us_strCountry           = $row->us_strCountry;
         $this->userRec->us_strZip               = $row->us_strZip;
         $this->userRec->strAddress              =
                     strBuildAddress(
                        $this->userRec->us_strAddr1, $this->userRec->us_strAddr2,   $this->userRec->us_strCity,
                        $this->userRec->us_strState, $this->userRec->us_strCountry, $this->userRec->us_strZip,
                        true);
         $this->userRec->us_enumDateFormat      = $row->us_enumDateFormat;
         $this->userRec->us_enumMeasurePref     = $row->us_enumMeasurePref;

         $this->userRec->us_strUserPWord        = null;

         $this->userRec->strStaffCFName         = $row->strCFName;
         $this->userRec->strStaffCLName         = $row->strCLName;
         $this->userRec->strStaffLFName         = $row->strLFName;
         $this->userRec->strStaffLLName         = $row->strLLName;
      }
   }

   public function addUserAccount(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $glChapterID;
      $sqlStr =
         'INSERT INTO admin_users
          SET '.$this->strSQL_Common().",
             us_bInactive  = 0,
             us_lOriginID  = $glUserID,
             us_lChapterID = $glChapterID,
             us_dteOrigin = NOW();";
      $query = $this->db->query($sqlStr);

      $this->us_lKeyID = $this->db->insert_id();
      return($this->us_lKeyID);
   }

   function updateUserAccount(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         'UPDATE admin_users
          SET '.$this->strSQL_Common().',
             us_bInactive = '.($this->userRec[0]->us_bInactive ? '1' : '0').'
          WHERE us_lKeyID='.$this->userRec[0]->us_lKeyID.';';
      $query = $this->db->query($sqlStr);
   }

   private function strSQL_Common(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $uRec = &$this->userRec[0];
      if (($uRec->us_strUserPWord.'')==''){
         $strPWord = '';
      }else {
         $strPWord = ', us_strUserPWord=PASSWORD('.strPrepStr($uRec->us_strUserPWord).') ';
      }

      if (is_null($uRec->bVolAccount))           $uRec->bVolAccount         = false;
      if (is_null($uRec->bVolEditContact))       $uRec->bVolEditContact     = false;
      if (is_null($uRec->bVolPassReset))         $uRec->bVolPassReset       = false;
      if (is_null($uRec->bVolViewGiftHistory))   $uRec->bVolViewGiftHistory = false;
      if (is_null($uRec->bVolEditJobSkills))     $uRec->bVolEditJobSkills   = false;
      if (is_null($uRec->bVolViewHrsHistory))    $uRec->bVolViewHrsHistory  = false;
      if (is_null($uRec->bVolAddVolHours))       $uRec->bVolAddVolHours     = false;
      if (is_null($uRec->bVolShiftSignup))       $uRec->bVolShiftSignup     = false;

      if (is_null($uRec->bUserDataEntryPeople)) $uRec->bUserDataEntryPeople = false;
      if (is_null($uRec->bUserDataEntryGifts))  $uRec->bUserDataEntryGifts  = false;
      if (is_null($uRec->bUserEditPeople))      $uRec->bUserEditPeople      = false;
      if (is_null($uRec->bUserEditGifts))       $uRec->bUserEditGifts       = false;
      if (is_null($uRec->bUserViewPeople))      $uRec->bUserViewPeople      = false;
      if (is_null($uRec->bUserViewGiftHistory)) $uRec->bUserViewGiftHistory = false;
      if (is_null($uRec->bUserViewReports))     $uRec->bUserViewReports     = false;
      if (is_null($uRec->bUserAllowExports))    $uRec->bUserAllowExports    = false;

      if (is_null($uRec->bUserAllowSponsorship  )) $uRec->bUserAllowSponsorship   = false;
      if (is_null($uRec->bUserAllowSponFinancial)) $uRec->bUserAllowSponFinancial = false;
      if (is_null($uRec->bUserAllowClient       )) $uRec->bUserAllowClient        = false;
      if (is_null($uRec->bUserAllowAuctions     )) $uRec->bUserAllowAuctions      = false;
      if (is_null($uRec->bUserAllowGrants       )) $uRec->bUserAllowGrants        = false;
      if (is_null($uRec->bUserAllowInventory    )) $uRec->bUserAllowInventory     = false;
      if (is_null($uRec->bUserVolManager        )) $uRec->bUserVolManager         = false;

      return(
          " us_dteLastUpdate   = NOW(),
            us_lLastUpdateID   = $glUserID,
            us_strUserName     = ".strPrepStr($uRec->us_strUserName).',
            us_strFirstName    = '.strPrepStr($uRec->us_strFirstName).',
            us_strLastName     = '.strPrepStr($uRec->us_strLastName).',
            us_strTitle        = '.strPrepStr($uRec->us_strTitle).',
            us_strPhone        = '.strPrepStr($uRec->us_strPhone).',
            us_strCell         = '.strPrepStr($uRec->us_strCell).',
            us_strEmail        = '.strPrepStr($uRec->us_strEmail).',
            us_strAddr1        = '.strPrepStr($uRec->us_strAddr1).',
            us_strAddr2        = '.strPrepStr($uRec->us_strAddr2).',
            us_strCity         = '.strPrepStr($uRec->us_strCity).',
            us_strState        = '.strPrepStr($uRec->us_strState).',
            us_strCountry      = '.strPrepStr($uRec->us_strCountry).',
            us_strZip          = '.strPrepStr($uRec->us_strZip).',
            us_enumDateFormat  = '.strPrepStr($uRec->us_enumDateFormat).',
            us_enumMeasurePref = '.strPrepStr($uRec->us_enumMeasurePref).',

            us_bAdmin          = '.($uRec->us_bAdmin          ? '1' : '0').',

            us_lPeopleID       = '.(is_null($uRec->lPeopleID) ? 'null' : (int)$uRec->lPeopleID).',

            us_bUserDataEntryPeople =  '.($uRec->bUserDataEntryPeople ? '1' : '0').',
            us_bUserDataEntryGifts  =  '.($uRec->bUserDataEntryGifts  ? '1' : '0').',
            us_bUserEditPeople      =  '.($uRec->bUserEditPeople      ? '1' : '0').',
            us_bUserEditGifts       =  '.($uRec->bUserEditGifts       ? '1' : '0').',
            us_bUserViewPeople      =  '.($uRec->bUserViewPeople      ? '1' : '0').',
            us_bUserViewGiftHistory =  '.($uRec->bUserViewGiftHistory ? '1' : '0').',
            us_bUserViewReports     =  '.($uRec->bUserViewReports     ? '1' : '0').',
            us_bUserAllowExports    =  '.($uRec->bUserAllowExports    ? '1' : '0').',

            us_bUserAllowSponsorship     = '.($uRec->bUserAllowSponsorship    ? '1' : '0').',
            us_bUserAllowSponFinancial   = '.($uRec->bUserAllowSponFinancial  ? '1' : '0').',
            us_bUserAllowClient          = '.($uRec->bUserAllowClient         ? '1' : '0').',
            us_bUserAllowAuctions        = '.($uRec->bUserAllowAuctions       ? '1' : '0').',
            us_bUserAllowGrants          = '.($uRec->bUserAllowGrants         ? '1' : '0').',
            us_bUserAllowInventory       = '.($uRec->bUserAllowInventory      ? '1' : '0').',
            us_bUserVolManager           = '.($uRec->bUserVolManager          ? '1' : '0').',

            us_bVolAccount          = '.($uRec->bVolAccount           ? '1' : '0').',
            us_bVolEditContact      = '.($uRec->bVolEditContact       ? '1' : '0').',
            us_bVolPassReset        = '.($uRec->bVolPassReset         ? '1' : '0').',
            us_bVolViewGiftHistory  = '.($uRec->bVolViewGiftHistory   ? '1' : '0').',
            us_bVolEditJobSkills    = '.($uRec->bVolEditJobSkills     ? '1' : '0').',
            us_bVolViewHrsHistory   = '.($uRec->bVolViewHrsHistory    ? '1' : '0').',
            us_bVolAddVolHours      = '.($uRec->bVolAddVolHours       ? '1' : '0').',
            us_bVolShiftSignup      = '.($uRec->bVolShiftSignup       ? '1' : '0')." \n"

           .$strPWord);
   }

   function inactivateUserAccount($lUserID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->actInactUserAccount($lUserID, true);
   }

   function activateUserAccount($lUserID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->actInactUserAccount($lUserID, false);
   }

   private function actInactUserAccount($lUserID, $bSetToInactive){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         "UPDATE admin_users
          SET
             us_bInactive = ".($bSetToInactive ? '1' : '0').",
             us_dteLastUpdate   = NOW(),
             us_lLastUpdateID   = $glUserID
          WHERE us_lKeyID=$lUserID;";

      $query = $this->db->query($sqlStr);
   }

   function updateUserID($lUserID, $strUserName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         "UPDATE admin_users
          SET
             us_strUserName = ".strPrepStr($strUserName).",
             us_dteLastUpdate   = NOW(),
             us_lLastUpdateID   = $glUserID
          WHERE us_lKeyID=$lUserID;";

      $query = $this->db->query($sqlStr);
   }

   function updateUserAcctViaPeopleInfo($lUserID, &$pRec){
   //---------------------------------------------------------------------
   // special case where user has corresponding people record; user
   // name is email address
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        'UPDATE admin_users
         SET
            us_strFirstName  = '.strPrepStr($pRec->strFName).',
            us_strLastName   = '.strPrepStr($pRec->strLName).',
            us_strTitle      = '.strPrepStr($pRec->strTitle).',
            us_strPhone      = '.strPrepStr($pRec->strPhone).',
            us_strCell       = '.strPrepStr($pRec->strCell).',
            us_strEmail      = '.strPrepStr($pRec->strEmail).',
            us_strAddr1      = '.strPrepStr($pRec->strAddr1).',
            us_strAddr2      = '.strPrepStr($pRec->strAddr2).',
            us_strCity       = '.strPrepStr($pRec->strCity).',
            us_strState      = '.strPrepStr($pRec->strState).',
            us_strCountry    = '.strPrepStr($pRec->strCountry).',
            us_strZip        = '.strPrepStr($pRec->strZip).",

            us_dteLastUpdate   = NOW(),
            us_lLastUpdateID   = $glUserID
         WHERE us_lKeyID=$lUserID;";

      $query = $this->db->query($sqlStr);
   }

   public function userHTMLSummary($idx){
   //-----------------------------------------------------------------------
   // assumes user has called $this->loadSingleUserRecord($lUserID)
   //-----------------------------------------------------------------------
      global $gdteNow, $genumDateFormat, $gbAdmin;

      $strOut = '';
      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $clsRpt->setEntrySummary();

      $userRec = &$this->userRec[$idx];
      $lUID = $userRec->us_lKeyID;

      $strAcctType = '';
      if ($userRec->us_bAdmin){
         $strAcctType = 'Admin';
      }elseif ($userRec->bVolAccount){
         $strAcctType = 'Volunteer';
      }else {
         $strAcctType = 'User';
         if ($userRec->bUserVolManager) $strAcctType .= ' / Volunteer Manager';
      }
      if ($userRec->us_bDebugger) $strAcctType .= ' / Debugger';

      if ($gbAdmin){
         $strAcctLink = strLinkView_User($lUID, 'View User Record', true).'&nbsp;';
      }else {
         $strAcctLink = '';
      }

      $strOut .=
          $clsRpt->openReport('', '')

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('user ID:')
         .$clsRpt->writeCell ($strAcctLink.str_pad($lUID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Name:')
         .$clsRpt->writeCell ($userRec->strSafeName)
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('User Name:')
         .$clsRpt->writeCell ($userRec->us_strUserName)
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Account Type:')
         .$clsRpt->writeCell ($strAcctType)
         .$clsRpt->closeRow  ();

      $strOut .= $clsRpt->closeReport('<br>');
      return($strOut);
   }

   public function loadUsersByAccess(&$access){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $access = array();
      $this->loadUsersByPermission('us_bAdmin',                  'Administrators',                                    $access[ 0]);
      $this->loadUsersByPermission('us_bDebugger',               'Debuggers',                                         $access[ 1]);

      $this->loadUsersByPermission('us_bAdmin OR us_bUserDataEntryPeople',    'User Acct: Data Entry/People',                      $access[ 2]);
      $this->loadUsersByPermission('us_bAdmin OR us_bUserDataEntryGifts',     'User Acct: Data Entry/Gifts',                       $access[ 3]);
      $this->loadUsersByPermission('us_bAdmin OR us_bUserEditPeople',         'User Acct: Edit/People & Biz',                      $access[ 4]);
      $this->loadUsersByPermission('us_bAdmin OR us_bUserEditGifts',          'User Acct: Edit/Gifts',                             $access[ 5]);
      $this->loadUsersByPermission('us_bAdmin OR us_bUserViewPeople',         'User Acct: View/People & Biz. Records',             $access[ 6]);
      $this->loadUsersByPermission('us_bAdmin OR us_bUserViewGiftHistory',    'User Acct: View/Gift Histories',                    $access[ 7]);
      $this->loadUsersByPermission('us_bAdmin OR us_bUserViewReports',        'User Acct: View Reports (limited by other perms.)', $access[ 8]);
      $this->loadUsersByPermission('us_bAdmin OR us_bUserAllowExports',       'User Acct: Export (limited by other perms.)',       $access[ 9]);
      $this->loadUsersByPermission('us_bAdmin OR us_bUserAllowAuctions',      'User Acct: Access to Silent Auctions',              $access[10]);
      $this->loadUsersByPermission('us_bAdmin OR us_bUserAllowInventory',     'User Acct: Access to Inventory Management',         $access[11]);
      $this->loadUsersByPermission('us_bAdmin OR us_bUserAllowGrants',        'User Acct: Access to Grants',                       $access[12]);
      $this->loadUsersByPermission('us_bAdmin OR us_bUserEditPeople',         'User Acct: Edit/People',                            $access[13]);
      $this->loadUsersByPermission('us_bAdmin OR us_bUserVolManager',         'User Acct: Volunteer Manager',                      $access[14]);

      $this->loadUsersByPermission('us_bAdmin OR us_bUserAllowSponsorship',   'User Acct: Sponsorships',                           $access[15]);
      $this->loadUsersByPermission('us_bAdmin OR us_bUserAllowSponFinancial', 'User Acct: Sponsorship Financials',                 $access[16]);
      $this->loadUsersByPermission('us_bAdmin OR us_bUserAllowClient',        'User Acct: Clients',                                $access[17]);

      $this->loadUsersByPermission('us_bVolAccount',             'Volunteer Accounts',                                $access[18]);
      $this->loadUsersByPermission('us_bVolEditContact',         'Vol. Acct: edit contact',                           $access[19]);
      $this->loadUsersByPermission('us_bVolPassReset',           'Vol. Acct: reset password',                         $access[20]);
      $this->loadUsersByPermission('us_bVolViewGiftHistory',     'Vol. Acct: view personal gift history',             $access[21]);
      $this->loadUsersByPermission('us_bVolEditJobSkills',       'Vol. Acct: edit personal job skills',               $access[22]);
      $this->loadUsersByPermission('us_bVolViewHrsHistory',      'Vol. Acct: view personal vol. hours',               $access[23]);
      $this->loadUsersByPermission('us_bVolShiftSignup',         'Vol. Acct: shift sign-up',                          $access[24]);
   }

   function loadUsersByPermission($strFN, $strLabel, &$users){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $users = new stdClass;
      $users->strFN     = $strFN;
      $users->strLabel  = $strLabel;
      $users->lNumAccts = 0;
      $users->accounts  = array();

      $sqlStr =
        "SELECT us_lKeyID, us_strUserName, us_strFirstName, us_strLastName, us_bInactive
         FROM admin_users
         WHERE $strFN
         ORDER BY us_strLastName, us_strFirstName, us_lKeyID;";

      $query = $this->db->query($sqlStr);
      $users->lNumAccts = $numRows = $query->num_rows();
      if ($numRows > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $users->accounts[$idx] = new stdClass;
            $ua = &$users->accounts[$idx];
            $ua->lUserID      = (int)$row->us_lKeyID;
            $ua->strUserName  = $row->us_strUserName;
            $ua->strFirstName = $row->us_strFirstName;
            $ua->strLastName  = $row->us_strLastName;
            $ua->bInactive    = (bool)$row->us_bInactive;
            ++$idx;
         }
      }
   }

   function lPeopleVolRecViaAcct($lChapterID, $lACOID, $lUserID){
   //---------------------------------------------------------------------
   // create a people/volunteer rec based on the the info in
   // $this->userRec[0]; Update the peopleID in the db record;
   // return the new people ID.
   //---------------------------------------------------------------------
      global $glUserID, $glChapterID;
      $holdUserID = $glUserID;
      $holdChapterID = $glChapterID;
      $glUserID = $lACOID;
      $glChapterID = $lChapterID;

      $ur = &$this->userRec[0];

      $cp = new mpeople;
      $cv = new mvol;

      $cp->loadPeopleViaPIDs(-1, false, false);
      $pr = &$cp->people[0];

      $pr->lHouseholdID      = 0;
      $pr->lAttributedTo     = null;
      $pr->strTitle          = $ur->us_strTitle;
      $pr->strFName          = $ur->us_strFirstName;
      $pr->strMName          = '';
      $pr->strLName          = $ur->us_strLastName;
      $pr->strPreferredName  = $ur->us_strFirstName;
      $pr->strSalutation     = $ur->us_strFirstName;
      $pr->strAddr1          = $ur->us_strAddr1;
      $pr->strAddr2          = $ur->us_strAddr2;
      $pr->strCity           = $ur->us_strCity;
      $pr->strState          = $ur->us_strState;
      $pr->strCountry        = $ur->us_strCountry;
      $pr->strZip            = $ur->us_strZip;
      $pr->strPhone          = $ur->us_strPhone;
      $pr->strCell           = $ur->us_strCell;
      $pr->strNotes          = '';
      $pr->strEmail          = $ur->us_strEmail;
      $pr->enumGender        = 'Unknown';
      $pr->dteExpire         = null;
      $pr->lACO              = $lACOID;
      $pr->dteMysqlBirthDate = null;
      $pr->dteMysqlDeath     = null;

      $lPID = $cp->lCreateNewPeopleRec();

      $cv->loadVolRecsViaVolID(-1, true);
      $vr = &$cv->volRecs[0];
      $vr->lRegFormID = null;
      $vr->lPeopleID = $lPID;
      $vr->Notes = '';
      $cv->lAddNewVolunteer();
      $glUserID = $holdUserID;
      $glChapterID = $holdChapterID;

      return($lPID);
   }


}


