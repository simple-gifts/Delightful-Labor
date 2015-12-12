<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model('admin/madmin_aco', 'clsACO');
      $this->load->model('biz/mbiz', 'clsBiz');
---------------------------------------------------------------------
   lNumBizRecords            ()
   clearBizRecord            ()
   lCreateNewBizRec          ()
   updateBizRec              ()
   bizInfoLight              ()

   loadBizRecsViaBID         ($lBID)
   loadBizDirectoryPage      ($strWhereExtra, $lStartRec, $lRecsPerPage){
   loadBizRecs               ($bIncludeSpon, $bIncludeGiftSum){

   loadBasicBizInfo          ()

   contactList               ($bViaBID, $bViaPID, $bViaConRecID)
   strBizContactPeopleIDList ()
   lNumContacts              ($bViaBID, $bViaPID)
   deleteBizContact          ($bViaBID, $bViaPID, $bViaConRecID, $lGroupID)
   retireSingleBizCon        ($lBizConID, &$lGroupID)
   lAddNewBizContact         ($lPID, $lBizRelID)
   updateBizContact          ($lConID, $lBizRelID, $bSoftCash)
   removeBizContact          ($lConID)

   logBizConRetire           ($lBizConID, &$lGroupID)
   strBizHTMLSummary         ()

---------------------------------------------------------------------*/


//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class mbiz extends CI_Model{

   public
      $lBID,
      $strBizName,     $strSafeName,
      $strAddr1,       $strAddr2,       $strCity,
      $strState,       $strCountry,     $strZip,
      $strPhone,       $strEmail,       $strEmailFormatted,
      $lACO,           $strACO,         $strCurSymbol,
      $strFlag,        $strFlagImage,   $bNoGiftAcknowledge,
      $lAttributedTo,  $lImportID,      $lImportRecID,
      $lOriginID,      $lLastUpdateID,  $dteMysqlBirthDate,
      $dteMysqlDeath,  $dteOrigin,      $dteLastUpdate,
      $strStaffCFName, $strStaffCLName, $strStaffLFName,
      $strStaffLLName;

   public
      $lIndustryID, $strIndustry, $lPID;

   public
      $lConRecID, $contacts, $lNumContacts;

   public $bizRecs, $sqlWhereExtra, $sqlOrderExtra, $sqlLimitExtra;

   public $lNumContactsNames, $contactsNames;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->clearBizRecord();
   }

   public function lNumBizRecords(){
      $sqlStr =
        'SELECT COUNT(*) AS lNumRecs
         FROM people_names
         WHERE pe_bBiz AND NOT pe_bRetired;';

      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((integer)$row->lNumRecs);
   }

   private function clearBizRecord(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->lIndustryID    = $this->strIndustry = $this->lPID = null;
      $this->contacts = $this->lNumContacts = $this->lConRecID = null;
      $this->sqlSelectExtra = $this->sqlWhereExtra = $this->sqlOrderExtra = $this->sqlLimitExtra = '';
   }

   public function lCreateNewBizRec(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID, $glChapterID;

      $clsUFC = new muser_fields_create;

      $sqlStr =
       "INSERT INTO people_names
        SET
             pe_lOriginID    = $glUserID,
             pe_dteOrigin    = NOW(), "
            .$this->strBizSQLCommon();
      $query = $this->db->query($sqlStr);
      $this->lBID = $lBID = $this->db->insert_id();

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }
//
//      $this->lBID = $lBID = mysql_insert_id();

         //--------------------------------------------------------
         // create blank/default records for all the personalized
         // people tables
         //--------------------------------------------------------
      $clsUFC->enumTType = CENUM_CONTEXT_BIZ;
      $clsUFC->loadTablesViaTType();
      if ($clsUFC->lNumTables > 0){
         foreach ($clsUFC->userTables as $clsTable){
            $clsUFC->createSingleEmptyRec($clsTable, $lBID);
         }
      }
      return($this->lBID);
   }

   public function updateBizRec($lBID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID, $glChapterID;

      if (is_null($this->lBID)) screamForHelp('BIZ ID NOT SET!<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      $sqlStr =
       'UPDATE people_names
        SET '.$this->strBizSQLCommon()."
        WHERE pe_lKeyID=$lBID;";
      $query = $this->db->query($sqlStr);

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }
   }

   private function strBizSQLCommon(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;
      $biz = &$this->bizRecs[0];
      if (is_null($biz->lAttributedTo)){
         $strAttrib = 'null';
      }else {
         $strAttrib = (integer)$biz->lAttributedTo;
      }

      return('
          pe_strLName        = '.strPrepStr($biz->strBizName).',
          pe_strAddr1        = '.strPrepStr($biz->strAddr1).',
          pe_strAddr2        = '.strPrepStr($biz->strAddr2).',
          pe_strCity         = '.strPrepStr($biz->strCity).',
          pe_strState        = '.strPrepStr($biz->strState).',
          pe_strCountry      = '.strPrepStr($biz->strCountry).',
          pe_strZip          = '.strPrepStr($biz->strZip).',
          pe_strPhone        = '.strPrepStr($biz->strPhone).',
          pe_strCell         = '.strPrepStr($biz->strCell).',
          pe_strFax          = '.strPrepStr($biz->strFax).',
          pe_strWebSite      = '.strPrepStr($biz->strWebSite).',
          pe_strNotes        = '.strPrepStr($biz->strNotes).',
          pe_strSalutation   = '.strPrepStr('').',

          pe_strEmail        = '.strPrepStr($biz->strEmail).",
          pe_lACO            = $biz->lACO,
          pe_lAttributedTo   = $strAttrib,
          pe_bBiz            = 1,
          pe_lBizIndustryID  = $biz->lIndustryID,

          pe_lLastUpdateID   = $glUserID ");
   }

   public function bizInfoLight(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (is_null($this->lBID)) screamForHelp('BIZ ID NOT SET!<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      $sqlStr =
        "SELECT
            pe_strLName
         FROM people_names
         WHERE pe_lKeyID=$this->lBID
            AND NOT pe_bRetired;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }else{
//         $numRows = mysql_num_rows($result);
      if ($numRows==0) {
         echo('<font face="monospace" style="font-size: 8pt;">'.__FILE__.' Line: <b>'.__LINE__.":</b><br><b>\$sqlStr=</b><br>".nl2br($sqlStr)."<br><br></font>\n");
         screamForHelp('UNEXPECTED EOF<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      }else {
//         $row = mysql_fetch_array($result);
         $row = $query->row();
         $this->strBizName         = $row->pe_strLName;
         $this->strSafeName        = htmlspecialchars($this->strBizName);
      }
//      }
   }

   public function loadBizRecsViaBID($lBID, $bIncludeSpon=false, $bIncludeGiftSum=false){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (is_array($lBID)){
         $this->sqlWhereExtra = ' AND pe_lKeyID IN ('.implode(',', $lBID).') ';
      }else {
         $this->sqlWhereExtra = " AND pe_lKeyID=$lBID ";
      }
      $this->loadBizRecs($bIncludeSpon, $bIncludeGiftSum);
   }

   function loadBizDirectoryPage($strWhereExtra, $lStartRec, $lRecsPerPage,
                                 $bIncludeSpon,  $bIncludeGiftSum){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlLimitExtra = " LIMIT $lStartRec, $lRecsPerPage ";
      $this->sqlWhereExtra = $strWhereExtra;
      $this->sqlOrderExtra = 'ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID ';
      $this->loadBizRecs($bIncludeSpon, $bIncludeGiftSum);
   }

   public function loadBizRecs($bIncludeSpon, $bIncludeGiftSum){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $clsACO = new madmin_aco;
      $this->bizRecs = array();

      $sqlStr =
        "SELECT
            pe_lKeyID,
            pe_strLName,
            pe_strAddr1,       pe_strAddr2,
            pe_strCity,        pe_strState,      pe_strCountry,
            pe_strZip,         pe_strPhone,      pe_strCell,      pe_strEmail,
            pe_lAttributedTo,  pe_strImportID,   pe_strImportID,  pe_strNotes,
            pe_strFax,         pe_strWebSite,
            pe_lOriginID,      pe_lLastUpdateID, pe_lBizIndustryID,
            tblIndustry.lgen_strListItem AS strIndustry,

            pe_lACO, aco_strFlag, aco_strName, aco_strCurrencySymbol,
            tblAttrib.lgen_strListItem AS strAttrib,

            usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
            usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,
            UNIX_TIMESTAMP(pe_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(pe_dteLastUpdate) AS dteLastUpdate
            $this->sqlSelectExtra
         FROM people_names
            INNER JOIN admin_users AS usersC ON pe_lOriginID      = usersC.us_lKeyID
            INNER JOIN admin_users AS usersL ON pe_lLastUpdateID  = usersL.us_lKeyID
            INNER JOIN admin_aco             ON pe_lACO           = aco_lKeyID
            LEFT  JOIN lists_generic AS tblIndustry ON pe_lBizIndustryID = tblIndustry.lgen_lKeyID
            LEFT  JOIN lists_generic AS tblAttrib   ON pe_lAttributedTo  = tblAttrib.lgen_lKeyID
         WHERE pe_bBiz
            $this->sqlWhereExtra
            AND NOT pe_bRetired
         $this->sqlOrderExtra
         $this->sqlLimitExtra;";
      $query = $this->db->query($sqlStr);

      $this->lNumBizRecs = $query->num_rows();
      if ($this->lNumBizRecs == 0) {
         $this->bizRecs[0] = null;
      }else {
         $idx = 0;
         if ($bIncludeGiftSum){
            $clsGifts = new mdonations;
            $clsGifts->bUseDateRange = false;
            $clsGifts->cumulativeOpts = new stdClass;
            $clsGifts->cumulativeOpts->enumCumulativeSource = 'biz';
         }

         if ($bIncludeSpon){
            $clsSpon = new msponsorship;
         }

         foreach ($query->result() as $row){
            $this->bizRecs[$idx] = new stdclass;
            $biz = &$this->bizRecs[$idx];
            $biz->lKeyID             = $lBID = $this->lBID = $row->pe_lKeyID;
            $biz->strBizName         = $row->pe_strLName;
            $biz->strSafeName        = htmlspecialchars($biz->strBizName);
            $biz->lIndustryID        = $row->pe_lBizIndustryID;
            $biz->strIndustry        = $row->strIndustry;

            $biz->strAddr1           = $row->pe_strAddr1;
            $biz->strAddr2           = $row->pe_strAddr2;
            $biz->strCity            = $row->pe_strCity;
            $biz->strState           = $row->pe_strState;
            $biz->strCountry         = $row->pe_strCountry;
            $biz->strZip             = $row->pe_strZip;
            $biz->strPhone           = $row->pe_strPhone;
            $biz->strCell            = $row->pe_strCell;
            $biz->strFax             = $row->pe_strFax;     
            $biz->strWebSite         = $row->pe_strWebSite;

            $biz->strAddress         =
                        strBuildAddress(
                                 $biz->strAddr1, $biz->strAddr2,   $biz->strCity,
                                 $biz->strState, $biz->strCountry, $biz->strZip,
                                 true);

            $biz->strEmail           = $row->pe_strEmail;
            $biz->strEmailFormatted  = strBuildEmailLink($biz->strEmail, '', false, '');

            $biz->strNotes           = $row->pe_strNotes;

            $biz->lACO               = $row->pe_lACO;
            $biz->strACO             = $row->aco_strName;
            $biz->strCurSymbol       = $row->aco_strCurrencySymbol;
            $biz->strFlag            = $row->aco_strFlag;
            $biz->strFlagImage       = $clsACO->strFlagImage($biz->strFlag, $biz->strACO);

            $biz->lAttributedTo      = $row->pe_lAttributedTo;
            $biz->strAttrib          = $row->strAttrib;
            $biz->lImportID          = $row->pe_strImportID;

            $biz->strImportRecID     = $row->pe_strImportID;
            $biz->lOriginID          = $row->pe_lOriginID;
            $biz->lLastUpdateID      = $row->pe_lLastUpdateID;

            $biz->dteOrigin          = $row->dteOrigin;
            $biz->dteLastUpdate      = $row->dteLastUpdate;

            $biz->strStaffCFName     = $row->strCFName;
            $biz->strStaffCLName     = $row->strCLName;
            $biz->strStaffLFName     = $row->strLFName;
            $biz->strStaffLLName     = $row->strLLName;

            $biz->contactList = $this->contactList(true, false, false);

               //-------------------
               // sponsorship
               //-------------------
            if ($bIncludeSpon){
               $clsSpon->sponsorshipInfoViaPID($lBID);
               $biz->lNumSponsorship = $lNumSpons = $clsSpon->lNumSponsors;
               if ($lNumSpons == 0){
                  $biz->sponInfo = null;
               }else {
                  $biz->sponInfo = $clsSpon->sponInfo;
               }
            }

               //-------------------
               // cumulative gifts
               //-------------------
            if ($bIncludeGiftSum){
               $clsGifts->lPeopleID = $lBID;
               $clsGifts->cumulativeOpts->enumMoneySet = 'all';

               $clsGifts->cumulativeOpts->bSoft = false;
               $clsGifts->cumulativeDonation($clsACO, $biz->lNumHardGifts);
               $biz->lNumACODonationGroups_hard = $clsGifts->lNumCumulative;
               $biz->donationsViaACO_hard       = $clsGifts->cumulative;

               $clsGifts->cumulativeOpts->bSoft = true;
               $clsGifts->cumulativeDonation($clsACO, $biz->lNumSoftGifts);
               $biz->lNumACODonationGroups_soft = $clsGifts->lNumCumulative;
               $biz->donationsViaACO_soft       = $clsGifts->cumulative;
            }else {
               $biz->lNumACODonationGroups_hard =
               $biz->donationsViaACO_hard       =
               $biz->lNumACODonationGroups_soft =
               $biz->donationsViaACO_soft       = null;
            }
            ++$idx;
         }
      }
   }

   public function loadBasicBizInfo(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $gbDev;

      $clsACO = new madmin_aco;
      if (is_null($this->lBID)) screamForHelp('BIZ ID NOT SET!<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      $sqlStr =
        "SELECT
            pe_strLName,
            pe_strAddr1,       pe_strAddr2,
            pe_strCity,        pe_strState,      pe_strCountry,
            pe_strZip,         pe_strPhone,      pe_strCell,      pe_strEmail,
            pe_lAttributedTo,  pe_strImportID,   pe_strImportID,
            pe_lOriginID,      pe_lLastUpdateID, pe_lBizIndustryID, lgen_strListItem,

            pe_lACO, aco_strFlag, aco_strName, aco_strCurrencySymbol,

            usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
            usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,
            UNIX_TIMESTAMP(pe_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(pe_dteLastUpdate) AS dteLastUpdate
         FROM people_names
            INNER JOIN admin_users AS usersC ON pe_lOriginID      = usersC.us_lKeyID
            INNER JOIN admin_users AS usersL ON pe_lLastUpdateID  = usersL.us_lKeyID
            INNER JOIN admin_aco             ON pe_lACO           = aco_lKeyID
            LEFT  JOIN lists_generic         ON pe_lBizIndustryID = lgen_lKeyID
         WHERE pe_lKeyID=$this->lBID
            AND NOT pe_bRetired;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }else{
//         $numRows = mysql_num_rows($result);
      if ($numRows==0) {
         if ($gbDev) echo('<font face="monospace" style="font-size: 8pt;">'.__FILE__.' Line: <b>'.__LINE__.":</b><br><b>\$sqlStr=</b><br>".nl2br($sqlStr)."<br><br></font>\n");
         screamForHelp('UNEXPECTED EOF<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__, true);
      }else {
//         $row = mysql_fetch_array($result);
         $row = $query->row();

         $this->strBizName         = $row->pe_strLName;
         $this->strSafeName        = htmlspecialchars($this->strBizName);
         $this->lIndustryID        = $row->pe_lBizIndustryID;
         $this->strIndustry        = $row->lgen_strListItem;

         $this->strAddr1           = $row->pe_strAddr1;
         $this->strAddr2           = $row->pe_strAddr2;
         $this->strCity            = $row->pe_strCity;
         $this->strState           = $row->pe_strState;
         $this->strCountry         = $row->pe_strCountry;
         $this->strZip             = $row->pe_strZip;
         $this->strPhone           = $row->pe_strPhone;
         $this->strCell            = $row->pe_strCell;
         $this->strAddress         =
                     strBuildAddress(
                              $this->strAddr1, $this->strAddr2,   $this->strCity,
                              $this->strState, $this->strCountry, $this->strZip,
                              true);

         $this->strEmail           = $row->pe_strEmail;
         $this->strEmailFormatted  = strBuildEmailLink($this->strEmail, '', false, '');

         $this->lACO               = $row->pe_lACO;
         $this->strACO             = $row->aco_strName;
         $this->strCurSymbol       = $row->aco_strCurrencySymbol;
         $this->strFlag            = $row->aco_strFlag;
         $this->strFlagImage       = $clsACO->strFlagImage($this->strFlag, $this->strACO);

         $this->lAttributedTo      = $row->pe_lAttributedTo;
         $this->lImportID          = $row->pe_strImportID;

         $this->strImportRecID     = $row->pe_strImportID;
         $this->lOriginID          = $row->pe_lOriginID;
         $this->lLastUpdateID      = $row->pe_lLastUpdateID;

         $this->dteOrigin          = $row->dteOrigin;
         $this->dteLastUpdate      = $row->dteLastUpdate;

         $this->strStaffCFName     = $row->strCFName;
         $this->strStaffCLName     = $row->strCLName;
         $this->strStaffLFName     = $row->strLFName;
         $this->strStaffLLName     = $row->strLLName;

         $this->contactList(true, false, false);
      }
//      }
   }

   public function contactList($bViaBID, $bViaPID, $bViaConRecID, $strWhere = '', $strLimit = ''){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->contacts = array();

      if ($bViaBID){
         $strWhere = " AND bc_lBizID=$this->lBID ";
      }elseif ($bViaPID){
         $strWhere = " AND bc_lContactID=$this->lPID ";
      }elseif ($bViaConRecID){
         $strWhere = " AND bc_lKeyID=$this->lConRecID ";
      }

      $sqlStr =
        "SELECT
            bc_lKeyID, bc_lBizID, bc_lContactID, bc_lBizContactRelID, bc_bSoftCash,
            lgen_strListItem,
            tblBiz.pe_strLName      AS strBizName,

            tblPeople.pe_strFName   AS strFName,
            tblPeople.pe_strMName   AS strMName,
            tblPeople.pe_strLName   AS strLName,
            tblPeople.pe_strTitle   AS strTitle,
            tblPeople.pe_strPreferredName AS strPName,

            tblPeople.pe_strAddr1   AS strAddr1,
            tblPeople.pe_strAddr2   AS strAddr2,
            tblPeople.pe_strCity    AS strCity,
            tblPeople.pe_strState   AS strState,
            tblPeople.pe_strCountry AS strCountry,
            tblPeople.pe_strZip     AS strZip,
            tblPeople.pe_strPhone   AS strPhone,
            tblPeople.pe_strCell    AS strCell,
            tblPeople.pe_strEmail   AS strEmail,

            bc_lOriginID, bc_lLastUpdateID,
            UNIX_TIMESTAMP(bc_dteOrigin) AS dteOrigin,
            UNIX_TIMESTAMP(bc_dteLastUpdate) AS dteLastUpdate
         FROM biz_contacts
            INNER JOIN people_names as tblPeople ON bc_lContactID=tblPeople.pe_lKeyID
            INNER JOIN people_names as tblBiz    ON bc_lBizID    =tblBiz.pe_lKeyID
            LEFT  JOIN lists_generic             ON lgen_lKeyID  =bc_lBizContactRelID
         WHERE 1 $strWhere
            AND NOT tblPeople.pe_bRetired
            AND NOT tblBiz.pe_bRetired
            AND NOT bc_bRetired
         ORDER BY
            tblBiz.pe_strLName, bc_lBizID,
            tblPeople.pe_strLName, tblPeople.pe_strFName, tblPeople.pe_strMName, bc_lContactID, bc_lKeyID
         $strLimit;";
      $query = $this->db->query($sqlStr);

      $this->lNumContacts = $numRows = $query->num_rows();
      $this->contacts = array();
      if ($numRows > 0){
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->contacts[$idx] = new stdClass;
            $cRec = &$this->contacts[$idx];

            $cRec->lBizConRecID      = $row->bc_lKeyID;
            $cRec->lBizID            = $row->bc_lBizID;
            $cRec->strBizName        = $row->strBizName;
            $cRec->lPeopleID         = $row->bc_lContactID;
            $cRec->lBizRelID         = $row->bc_lBizContactRelID;
            $cRec->strRelationship   = $row->lgen_strListItem;
            $cRec->bSoftCash         = $row->bc_bSoftCash;
            $cRec->strFName          = $strFName = $row->strFName;
            $cRec->strLName          = $strLName = $row->strLName;
            $cRec->strMName          = $strMName = $row->strMName;
            $cRec->strTitle          = $strTitle = $row->strTitle;
            $cRec->strPreferred      = $strPreferred = $row->strPName;

            $cRec->strSafeName       = htmlspecialchars(
                                                            strBuildName(false, $strTitle, $strPreferred,
                                                                            $strFName, $strLName, $strMName));
            $cRec->strSafeNameLF     = htmlspecialchars(
                                                            strBuildName(true, $strTitle, $strPreferred,
                                                                            $strFName, $strLName, $strMName));


            $cRec->strAddr1          = $row->strAddr1;
            $cRec->strAddr2          = $row->strAddr2;
            $cRec->strCity           = $row->strCity;
            $cRec->strState          = $row->strState;
            $cRec->strCountry        = $row->strCountry;
            $cRec->strZip            = $row->strZip;
            $cRec->strPhone          = $row->strPhone;
            $cRec->strCell           = $row->strCell;
            $cRec->strEmail          = $row->strEmail;
            $cRec->strEmailFormatted = strBuildEmailLink($cRec->strEmail, '', false, '');

            $cRec->strAddress        =
                        strBuildAddress(
                                 $cRec->strAddr1, $cRec->strAddr2,   $cRec->strCity,
                                 $cRec->strState, $cRec->strCountry, $cRec->strZip,
                                 true);

            $cRec->lOriginID       = $row->bc_lOriginID;
            $cRec->dteOrigin       = $row->dteOrigin;
            $cRec->lLastUpdateID   = $row->bc_lLastUpdateID;
            $cRec->dteLastUpdate   = $row->dteLastUpdate;

            ++$idx;
         }
      }
   }

   public function strBizContactPeopleIDList(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $strList = '';
      $this->contactList(true, false, false);
      if ($this->lNumContacts > 0) {
         foreach($this->contacts as $clsID){
            $strList .= ', '.$clsID->lPeopleID;
         }
         $strList = substr($strList, 2);
      }
      return($strList);
   }

   public function lNumContacts($bViaBID, $bViaPID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if ($bViaBID){
         $strWhere = " AND bc_lBizID=$this->lBID ";
      }elseif ($bViaPID){
         $strWhere = " AND bc_lContactID=$this->lPID ";
      }else {
         $strWhere = '';
      }

      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM biz_contacts
            INNER JOIN people_names as tblPeople ON bc_lContactID=tblPeople.pe_lKeyID
            INNER JOIN people_names as tblBiz    ON bc_lBizID    =tblBiz.pe_lKeyID
         WHERE 1 $strWhere
            AND NOT tblPeople.pe_bRetired
            AND NOT tblBiz.pe_bRetired
            AND NOT bc_bRetired;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
//         $query = $this->db->query($sqlStr);
         return((integer)$row->lNumRecs);
      }
   }

   public function deleteBizContact($bViaBID, $bViaPID, $bViaConRecID, $lGroupID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->contactList($bViaBID, $bViaPID, $bViaConRecID);
      foreach ($this->contacts as $clsCon){
         $this->retireSingleBizCon($clsCon->lBizConRecID, $lGroupID);
      }
   }

   public function retireSingleBizCon($lBizConID, &$lGroupID){
   //---------------------------------------------------------------------
   // $lGroupID is the recyle bin group id; set to null if deleting
   // a single contact
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        "UPDATE biz_contacts
         SET
            bc_bRetired=1,
            bc_lLastUpdateID=$glUserID
         WHERE bc_lKeyID=$lBizConID;";
      $this->db->query($sqlStr);
   }

   private function logBizConRetire($lBizConID, &$lGroupID){
   //-----------------------------------------------------------------------
   // caller must first call $this->peopleInfoLight();
   //-----------------------------------------------------------------------
      $clsRecycle = new recycleBin;

      $clsRecycle->lForeignID      = $lBizConID;
      $clsRecycle->strTable        = 'biz_contacts';
      $clsRecycle->strRetireFN     = 'bc_bRetired';
      $clsRecycle->strKeyIDFN      = 'bc_lKeyID';
      $clsRecycle->strNotes        = 'Retired business contact '.str_pad($lBizConID, 5, '0', STR_PAD_LEFT);
      $clsRecycle->lGroupID        = $lGroupID;
      $clsRecycle->enumRecycleType = 'Business Contact';

      $clsRecycle->addRecycleEntry();
   }

   public function strBizHTMLSummary(){
   //-----------------------------------------------------------------------
   // assumes user has called $clsBiz->loadBizRecsViaBID($lBID)
   //-----------------------------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $clsRpt->setEntrySummary();

      $lBID = $this->lBID;
      $biz = &$this->bizRecs[0];
      $strOut =
          $clsRpt->openReport('', '')
         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Business Name:')
         .$clsRpt->writeCell (strLinkView_BizRecord($lBID, 'View business record', true).'&nbsp;'
                              .$biz->strSafeName
                              .'&nbsp;&nbsp;(business ID: '
                              .str_pad($lBID, 5, '0', STR_PAD_LEFT).')'
                             )
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Address:')
         .$clsRpt->writeCell ($biz->strAddress)
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Industry:')
         .$clsRpt->writeCell (htmlspecialchars($biz->strIndustry))
         .$clsRpt->closeRow  ()

         .$clsRpt->closeReport('<br>');

      return($strOut);
   }

   public function lAddNewBizContact($lPID, $lBizRelID){
   //-----------------------------------------------------------------------
   // assumes user has called $clsBiz->loadBasicBizInfo()
   //-----------------------------------------------------------------------
      global $glUserID;

      if (is_null($this->lBID)) screamForHelp('BIZ ID NOT SET!<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      if (is_null($lBizRelID)) $lBizRelID = 'null';
      $sqlStr =
           "INSERT INTO
               biz_contacts
            SET
               bc_lBizID           = $this->lBID,
               bc_lContactID       = $lPID,
               bc_lBizContactRelID = $lBizRelID,
               bc_bRetired         = 0,
               bc_lOriginID        = $glUserID,
               bc_dteOrigin        = NOW(),
               bc_lLastUpdateID    = $glUserID,
               bc_dteLastUpdate    = NOW();";
      $query = $this->db->query($sqlStr);
               
//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }
      return($this->db->insert_id());
   }

   public function updateBizContact($lConID, $lBizRelID, $bSoftCash){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      if (is_null($lBizRelID)) $lBizRelID = 'null';
      $strSoftCash = $bSoftCash ? '1' : '0';
      $sqlStr =
           "UPDATE biz_contacts
            SET
               bc_lBizContactRelID = $lBizRelID,
               bc_lLastUpdateID    = $glUserID,
               bc_bSoftCash        = $strSoftCash,
               bc_dteLastUpdate    = NOW()
            WHERE bc_lKeyID=$lConID;";
      $query = $this->db->query($sqlStr);
//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }
   }

   public function removeBizContact($lConID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
           "UPDATE biz_contacts
            SET
               bc_bRetired      = 1,
               bc_lLastUpdateID = $glUserID,
               bc_dteLastUpdate = NOW()
            WHERE bc_lKeyID=$lConID;";
      $query = $this->db->query($sqlStr);
//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }
   }

   function loadContactNameDirectoryPage($strWhereExtra, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlLimitExtra = " LIMIT $lStartRec, $lRecsPerPage ";
      $this->sqlWhereExtra = $strWhereExtra;

      $sqlStr =
           "SELECT
               pe_lKeyID, pe_strLName, pe_strFName, pe_strMName,
               pe_strTitle, pe_strPreferredName,
               pe_strAddr1,
               pe_strAddr2,
               pe_strCity,
               pe_strState,
               pe_strCountry,
               pe_strZip,
               pe_strPhone,
               pe_strCell,
               pe_strEmail
            FROM biz_contacts
               INNER JOIN people_names ON bc_lContactID=pe_lKeyID
            WHERE NOT pe_bBiz
               AND NOT pe_bRetired
               AND NOT bc_bRetired
               $strWhereExtra
            GROUP BY pe_lKeyID
            ORDER BY
               pe_strLName, pe_strFName, pe_strMName, pe_lKeyID
            $sqlLimitExtra;";

      $query = $this->db->query($sqlStr);

      $this->lNumContactsNames = $numRows = $query->num_rows();
      $this->contactsNames = array();
      if ($numRows > 0){
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->contactsNames[$idx] = new stdClass;
            $cRec = &$this->contactsNames[$idx];

            $cRec->lPeopleID         = $row->pe_lKeyID;
            $cRec->strFName          = $strFName = $row->pe_strFName;
            $cRec->strLName          = $strLName = $row->pe_strLName;
            $cRec->strMName          = $strMName = $row->pe_strMName;
            $cRec->strTitle          = $strTitle = $row->pe_strTitle;
            $cRec->strPreferred      = $strPreferred = $row->pe_strPreferredName;

            $cRec->strSafeName       = htmlspecialchars(
                                                            strBuildName(false, $strTitle, $strPreferred,
                                                                            $strFName, $strLName, $strMName));
            $cRec->strSafeNameLF     = htmlspecialchars(
                                                            strBuildName(true, $strTitle, $strPreferred,
                                                                            $strFName, $strLName, $strMName));


            $cRec->strAddr1          = $row->pe_strAddr1;
            $cRec->strAddr2          = $row->pe_strAddr2;
            $cRec->strCity           = $row->pe_strCity;
            $cRec->strState          = $row->pe_strState;
            $cRec->strCountry        = $row->pe_strCountry;
            $cRec->strZip            = $row->pe_strZip;
            $cRec->strPhone          = $row->pe_strPhone;
            $cRec->strCell           = $row->pe_strCell;
            $cRec->strEmail          = $row->pe_strEmail;
            $cRec->strEmailFormatted = strBuildEmailLink($cRec->strEmail, '', false, '');

            $cRec->strAddress        =
                        strBuildAddress(
                                 $cRec->strAddr1, $cRec->strAddr2,   $cRec->strCity,
                                 $cRec->strState, $cRec->strCountry, $cRec->strZip,
                                 true);

            $cRec->biz = $this->bizViaContactPID($cRec->lPeopleID, $cRec->lNumBiz);

            ++$idx;
         }
      }
   }

    public function bizViaContactPID($lPeopleID, &$lNumBiz){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $biz = array();
      $sqlStr =
        "SELECT
            bc_lKeyID, bc_lBizID, bc_lContactID, bc_lBizContactRelID, bc_bSoftCash,
            pe_strLName, lgen_strListItem
         FROM biz_contacts
            INNER JOIN people_names  ON pe_lKeyID           = bc_lBizID
            LEFT  JOIN lists_generic ON bc_lBizContactRelID = lgen_lKeyID
         WHERE NOT bc_bRetired
            AND bc_lContactID=$lPeopleID
            AND NOT pe_bRetired
            ORDER BY pe_strLName, lgen_strListItem, bc_lKeyID;";
      $query = $this->db->query($sqlStr);
      $lNumBiz = $query->num_rows();
      if ($lNumBiz > 0){
         $idx = 0;
         foreach ($query->result() as $row) {
            $biz[$idx] = new stdClass;
            $b = &$biz[$idx];
            $b->contactID       = $row->bc_lKeyID;
            $b->bizID           = $row->bc_lBizID;
            $b->peopleID        = $row->bc_lContactID;
            $b->relationshipID  = $row->bc_lBizContactRelID;
            $b->bSoftCash       = $row->bc_bSoftCash;
            $b->strBizName      = $row->pe_strLName;
            $b->strBizSafeName  = htmlspecialchars($row->pe_strLName);
            $b->strRelationship = $row->lgen_strListItem;

            ++$idx;
         }
      }
      return($biz);
   }



}

?>