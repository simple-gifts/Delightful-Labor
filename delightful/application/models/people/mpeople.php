<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2011-2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
  clearPeopleRecord          ()
  bBizRec                    ($lPID)
  lCreateNewPeopleRec        ()

  peopleInfoLight            ()

  setHeadOfHousehold         ()
  resetHeadOfHousehold       ()
  forceNullToHeadOfHousehold ()
  strHouseholdName           ($strFName, $strLName)
  loadPIDsViaHouseholdHID    ()
  loadHouseholdViaPID        ()
  loadHouseholdNameViaHID    ()
  removeFromHousehold        ($lPID)
  addToHousehold             ($lPID, $lHID)

  removePersonBiz            ($bBiz=false)
  logPeopleRetire            (&$lGroupID)

  peopleHTMLSummary          ()

  loadPeople                 ($bIncludeSpon, $bIncludeGiftSum, $bPeople)

  peopleBizInfoViaPID        ($lPID, &$pbInfo)

---------------------------------------------------------------------
//      $this->load->helper('dl_util/email_web');
      $this->load->model('admin/madmin_aco', 'clsACO');
      $this->load->model('people/mpeople',   'clsPeople');
---------------------------------------------------------------------*/

class mpeople extends CI_Model{

   public $sqlInnerExtra, $sqlWhereExtra, $sqlOrderExtra,
          $sqlLimitExtra;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->clearPeopleRecord();
   }

   function clearPeopleRecord(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->sqlInnerExtra = $this->sqlWhereExtra = $this->sqlOrderExtra =
      $this->sqlLimitExtra = '';
   }

   public function bBizRec($lFID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
        "SELECT pe_bBiz
         FROM people_names
         WHERE pe_lKeyID=$lFID;";
      $query = $this->db->query($sqlStr);

      if ($query->num_rows() == 0){
         screamForHelp('UNEXPECTED EOF<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__, true);
      }else {
         $row = $query->row();
         return((boolean)$row->pe_bBiz);
      }
   }

   public function bValidBizPeopleID($lFID, $bBiz){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
        "SELECT pe_lKeyID
         FROM people_names
         WHERE ".($bBiz ? '' : ' NOT ')." pe_bBiz
            AND pe_lKeyID=$lFID
            AND NOT pe_bRetired;";
      $query = $this->db->query($sqlStr);

      return($query->num_rows() > 0);
   }

   public function lCreateNewPeopleRec(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID, $glChapterID;

      $clsUFC = new muser_fields_create;
      $lHID = $this->people[0]->lHouseholdID;
      $sqlStr =
       "INSERT INTO people_names
        SET
             pe_lChapterID   = $glChapterID,
             pe_lHouseholdID = ".strDBValueConvert_INT($lHID).",
             pe_lOriginID    = $glUserID,
             pe_dteOrigin    = NOW(), "
            .$this->strPeopleSQLCommon().';';
      $query = $this->db->query($sqlStr);
      $this->people[0]->lKeyID = $lKeyID = $this->db->insert_id();

         //------------------------------------------------------------
         // if not part of a household, make person head of household
         //------------------------------------------------------------
      if ($lHID==0){
         $sqlStr =
            "UPDATE people_names
             SET
                pe_lHouseholdID=$lKeyID
             WHERE pe_lKeyID=$lKeyID;";
         $this->db->query($sqlStr);
      }

         //--------------------------------------------------------
         // create blank/default records for all the personalized
         // people tables
         //--------------------------------------------------------
      $clsUFC->enumTType = CENUM_CONTEXT_PEOPLE;
      $clsUFC->loadTablesViaTType();
      if ($clsUFC->lNumTables > 0){
         foreach ($clsUFC->userTables as $clsTable){
            $clsUFC->createSingleEmptyRec($clsTable, $lKeyID);
         }
      }
      return($lKeyID);
   }

   public function updatePeopleRec($lPID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID, $glChapterID;

      $sqlStr =
          'UPDATE people_names
           SET '.$this->strPeopleSQLCommon()
         ."WHERE pe_lKeyID=$lPID;";
      $this->db->query($sqlStr);
   }

   private function strPeopleSQLCommon(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;
      $people = $this->people[0];
      if (is_null($people->lAttributedTo)){
         $strAttrib = 'null';
      }else {
         $strAttrib = (integer)$people->lAttributedTo;
      }
      return('
             pe_strTitle        = '.strPrepStr($people->strTitle, 80).',

             pe_strFName        = '.strPrepStr($people->strFName, 80).',
             pe_strMName        = '.strPrepStr($people->strMName, 80).',
             pe_strLName        = '.strPrepStr($people->strLName, 80).',

             pe_strPreferredName = '.strPrepStr($people->strPreferredName, 80).',
             pe_strSalutation   = '.strPrepStr($people->strSalutation, 80).',
             pe_strAddr1        = '.strPrepStr($people->strAddr1, 80).',

             pe_strAddr2        = '.strPrepStr($people->strAddr2, 80).',
             pe_strCity         = '.strPrepStr($people->strCity, 80).',
             pe_strState        = '.strPrepStr($people->strState, 80).',

             pe_strCountry      = '.strPrepStr($people->strCountry, 80).',
             pe_strZip          = '.strPrepStr($people->strZip, 40).',
             pe_strPhone        = '.strPrepStr($people->strPhone, 40).',
             pe_strCell         = '.strPrepStr($people->strCell, 40).',
             pe_strNotes        = '.strPrepStr($people->strNotes).',

             pe_strEmail        = '.strPrepStr($people->strEmail, 120).',
             pe_enumGender      = '.strPrepStr($people->enumGender).',
             pe_dteExpire       = '.strDBValueConvert_Date($people->dteExpire).",
             pe_lACO            = $people->lACO,
             pe_bBiz            = 0,
             pe_lAttributedTo   = $strAttrib,

             pe_lLastUpdateID   = $glUserID,

             pe_dteBirthDate    = ".strDBValueConvert_String($people->dteMysqlBirthDate).',
             pe_dteDeathDate    = '.strDBValueConvert_String($people->dteMysqlDeath).' ');
   }

   function setHeadOfHousehold(){
   //-----------------------------------------------------------------------
   // use only on new records; for changing HOH of an exisiting
   // household, use resetHeadOfHousehold()
   //-----------------------------------------------------------------------
      global $glUserID;
      if (is_null($this->lHouseholdID)) screamForHelp('HOUSEHOLD ID NOT SET!<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      $sqlStr =
         "UPDATE people_names
          SET
             pe_lHouseholdID=$this->lHouseholdID,
             pe_lLastUpdateID=$glUserID
          WHERE pe_lKeyID=$this->lPeopleID AND NOT pe_bRetired;";
      $query = $this->db->query($sqlStr);
   }

   function resetHeadOfHousehold($lPID, $lHID){
   //-----------------------------------------------------------------------
   // set all members of household $this->lHouseholdID to the household
   // headed by $this->lPeopleID
   //-----------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         "UPDATE people_names
          SET
             pe_lHouseholdID=$lPID,
             pe_lLastUpdateID=$glUserID
          WHERE pe_lHouseholdID=$lHID AND NOT pe_bRetired;";
      $this->db->query($sqlStr);
   }

   function forceNullToHeadOfHousehold(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'UPDATE people_names
         SET
            pe_lHouseholdID=pe_lKeyID,
            pe_dteLastUpdate=pe_dteLastUpdate
         WHERE pe_lHouseholdID IS NULL;';
      $this->db->query($sqlStr);
   }

   function peopleInfoLight(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (is_null($this->lPeopleID)) screamForHelp('$this->lPeopleID NOT SET!<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      $sqlStr =
        "SELECT
            pe_lHouseholdID, pe_strFName, pe_strLName
         FROM people_names
         WHERE pe_lKeyID=$this->lPeopleID
            AND NOT pe_bBiz
            AND NOT pe_bRetired;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0) {
         echo('<font face="monospace" style="font-size: 8pt;">'.__FILE__.' Line: <b>'.__LINE__.":</b><br><b>\$sqlStr=</b><br>".nl2br($sqlStr)."<br><br></font>\n");
         screamForHelp('UNEXPECTED EOF<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      }else {
         $row = $query->row();

         $this->lHouseholdID     = $row->pe_lHouseholdID;
         $this->strFName         = $row->pe_strFName;
         $this->strLName         = $row->pe_strLName;
         $this->strSafeName      = htmlspecialchars($this->strFName.' '.$this->strLName);
         $this->strHouseholdName = $this->strHouseholdNameViaHID($this->lHouseholdID);
      }
   }

   function loadHouseholdViaPID(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (is_null($this->lPeopleID)) screamForHelp('PEOPLE ID NOT SET!<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      $sqlStr =
         "SELECT pe_lHouseholdID
          FROM people_names
          WHERE pe_lKeyID=$this->lPeopleID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0) {
         echo('<font face="monospace" style="font-size: 8pt;">'.__FILE__.' Line: <b>'.__LINE__.":</b><br><b>\$sqlStr=</b><br>".nl2br($sqlStr)."<br><br></font>\n");
         screamForHelp('UNEXPECTED EOF<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      }else {
         $row = $query->row();
         $this->lHouseholdID = (int)$row->pe_lHouseholdID;
         if (is_null($this->lHouseholdID)){
             $this->lHouseholdID = $this->lPeopleID;
             $this->setHeadOfHousehold();
         }
         $this->strHouseholdName = $this->strHouseholdNameViaHID($this->lHouseholdID);
      }
   }

   public function strHouseholdNameViaHID($lHID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
        "SELECT pe_strFName, pe_strLName
         FROM people_names
         WHERE pe_lKeyID=$lHID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0) {
         echo('<font face="monospace" style="font-size: 8pt;">'.__FILE__.' Line: <b>'.__LINE__.":</b><br><b>\$sqlStr=</b><br>".nl2br($sqlStr)."<br><br></font>\n");
         screamForHelp('UNEXPECTED EOF<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      }else {
         $row = $query->row();
         return($this->strHouseholdName($row->pe_strFName, $row->pe_strLName));
      }
   }

   function strHouseholdName($strFName, $strLName){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      return("The $strFName $strLName Household");
   }

   function loadPIDsViaHouseholdHID(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (is_null($this->lHouseholdID)) screamForHelp('HOUSEHOLD ID NOT SET!<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      $sqlStr =
        "SELECT pe_lKeyID, pe_strFName, pe_strLName, pe_strMName,
           pe_strPreferredName, pe_strTitle,
           pe_enumGender, pe_strAddr1, pe_strAddr2, pe_strCity, pe_strState,
           pe_strCountry, pe_strZip, pe_strPhone, pe_strCell, pe_strEmail
         FROM people_names
         WHERE pe_lHouseholdID=$this->lHouseholdID
            AND NOT pe_bRetired
         ORDER BY pe_lKeyID=pe_lHouseholdID DESC, pe_strLName, pe_strFName, pe_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumInHousehold = $numRows = $query->num_rows();

      $this->arrHouseholds = array();
      if ($numRows==0) {
         $this->arrHouseholds[0] = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->arrHouseholds[$idx] = new stdClass;
            $this->arrHouseholds[$idx]->PID          = $row->pe_lKeyID;
            $this->arrHouseholds[$idx]->FName        = $strFName = $row->pe_strFName;
            $this->arrHouseholds[$idx]->LName        = $strLName = $row->pe_strLName;
            $this->arrHouseholds[$idx]->MName        = $strMName = $row->pe_strMName;
            $this->arrHouseholds[$idx]->strTitle     = $strTitle = $row->pe_strTitle;
            $this->arrHouseholds[$idx]->strPreferred = $strPreferred = $row->pe_strPreferredName;
            $this->arrHouseholds[$idx]->enumGender   = $row->pe_enumGender;
            $this->arrHouseholds[$idx]->strAddr1     = $row->pe_strAddr1;
            $this->arrHouseholds[$idx]->strAddr2     = $row->pe_strAddr2;
            $this->arrHouseholds[$idx]->strCity      = $row->pe_strCity;
            $this->arrHouseholds[$idx]->strState     = $row->pe_strState;
            $this->arrHouseholds[$idx]->strCountry   = $row->pe_strCountry;
            $this->arrHouseholds[$idx]->strZip       = $row->pe_strZip;

            $this->arrHouseholds[$idx]->strSafeName   = htmlspecialchars(
                                                            strBuildName(false, $strTitle, $strPreferred,
                                                                            $strFName, $strLName, $strMName));
            $this->arrHouseholds[$idx]->strSafeNameLF = htmlspecialchars(
                                                            strBuildName(true, $strTitle, $strPreferred,
                                                                            $strFName, $strLName, $strMName));

            $this->arrHouseholds[$idx]->strAddress   =
                     strBuildAddress(
                              $this->arrHouseholds[$idx]->strAddr1, $this->arrHouseholds[$idx]->strAddr2,   $this->arrHouseholds[$idx]->strCity,
                              $this->arrHouseholds[$idx]->strState, $this->arrHouseholds[$idx]->strCountry, $this->arrHouseholds[$idx]->strZip,
                              true);

            $this->arrHouseholds[$idx]->strPhone   = $row->pe_strPhone;
            $this->arrHouseholds[$idx]->strCell    = $row->pe_strCell;
            $this->arrHouseholds[$idx]->strEmail   = $row->pe_strEmail;
            ++$idx;
         }
      }
   }

   public function removeFromHousehold($lPID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
        "UPDATE people_names
         SET
            pe_lHouseholdID=pe_lKeyID,
            pe_lLastUpdateID=$glUserID
         WHERE pe_lKeyID=$lPID;";

      $this->db->query($sqlStr);
   }

   public function addToHousehold($lPID, $lHID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
        "UPDATE people_names
         SET
            pe_lHouseholdID=$lHID,
            pe_lLastUpdateID=$glUserID
         WHERE pe_lKeyID=$lPID;";

      $this->db->query($sqlStr);
   }

   public function removePersonBiz($bBiz=false){
   //-----------------------------------------------------------------------
   // caller must first set
   //    $this->lPeopleID (set to bizID if business)
   //
   // note that both businesses and people are removed from this routine
   //-----------------------------------------------------------------------
      global $glUserID;

      if (is_null($this->lPeopleID)) screamForHelp('PEOPLE ID NOT SET!<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      $lPID = $this->lPeopleID;

      $sqlStr =
           "UPDATE people_names
            SET
               pe_bRetired=1,
               pe_lLastUpdateID=$glUserID
            WHERE pe_lKeyID=$lPID;";
      $this->db->query($sqlStr);

      $lGroupID = null;
      $this->logPeopleRetire($lGroupID);

         //-----------------------------
         // remove associated gifts
         //-----------------------------
      $clsGifts = new mdonations;
      $clsGifts->retireGiftsViaPID($lPID, $lGroupID);

         //---------------------------------
         // remove associated sponsorships
         //---------------------------------
      $clsSpon = new msponsorship;
      $clsSpon->retireSponsorshipsViaPID($lPID, $lGroupID);

         //---------------------------------
         // remove business contacts
         //---------------------------------
      $clsBiz = new mbiz;
      $clsBiz->lPID = $lPID;
      $clsBiz->deleteBizContact(false, true, false, $lGroupID);

         //---------------------------------
         // remove group membership
         //---------------------------------
      $clsGroups = new mgroups;
      $clsGroups->removeMemFromAllGroups(($bBiz ? CENUM_CONTEXT_BIZ : CENUM_CONTEXT_PEOPLE), $lPID);

         // delete people/biz entries in personalized tables
      $uf = new muser_fields;
      $uf->deleteForeignViaUFTableType(CENUM_CONTEXT_PEOPLE,    $lPID);
      $uf->deleteForeignViaUFTableType(CENUM_CONTEXT_BIZ,       $lPID);
      $uf->deleteForeignViaUFTableType(CENUM_CONTEXT_VOLUNTEER, $lPID);
   }

   private function logPeopleRetire(&$lGroupID){
   //-----------------------------------------------------------------------
   // caller must first call $this->peopleInfoLight();
   //-----------------------------------------------------------------------
      $clsRecycle = new mrecycle_bin;

      $clsRecycle->lForeignID      = $this->lPeopleID;
      $clsRecycle->strTable        = 'people_names';
      $clsRecycle->strRetireFN     = 'pe_bRetired';
      $clsRecycle->strKeyIDFN      = 'pe_lKeyID';
      $clsRecycle->strNotes        = 'Retired people record '.str_pad($this->lPeopleID, 5, '0', STR_PAD_LEFT)
                                    .': '.$this->strSafeName;
      $clsRecycle->lGroupID        = $lGroupID;
      $clsRecycle->enumRecycleType = 'People';

      $clsRecycle->addRecycleEntry();
   }

   public function peopleHTMLSummary($idx){
   //-----------------------------------------------------------------------
   // assumes user has called $clsPeople->loadPeople($bIncludeSpon, $bIncludeGiftSum,
   //-----------------------------------------------------------------------
      global $gdteNow, $genumDateFormat;

      $strOut = '';
      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $clsRpt->setEntrySummary();

      $lPID = $this->people[$idx]->lKeyID;
      $strOut .=
          $clsRpt->openReport('', '')

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Name:')
         .$clsRpt->writeCell ($this->people[$idx]->strSafeName.'&nbsp;&nbsp;&nbsp;(people ID '
                                 .str_pad($lPID, 5, '0', STR_PAD_LEFT)
                                 .strLinkView_PeopleRecord($lPID, 'View People Record', true).')')
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Address:')
         .$clsRpt->writeCell ($this->people[$idx]->strAddress)
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Household:')
         .$clsRpt->writeCell (
                          htmlspecialchars($this->people[$idx]->strHouseholdName)
                         .strLinkView_Household($this->people[$idx]->lHouseholdID, $lPID, 'View household', true))
         .$clsRpt->closeRow  ();

      if (!is_null($this->people[$idx]->dteExpire)){
         $dteExpire     = $this->people[$idx]->dteExpire;
         $strDateExpire = date($genumDateFormat, $dteExpire);

         $strOut .=
             $clsRpt->openRow   (false)
            .$clsRpt->writeLabel('Expiration:');

         if ($dteExpire > $gdteNow){
            $strOut .=
               $clsRpt->writeCell ('THIS PEOPLE RECORD WILL EXPIRE ON '.$strDateExpire);
         }else {
            $strOut .=
               $clsRpt->writeCell ('<font color="red"><b>EXPIRED ON '.$strDateExpire.'</b></font>');
         }
         $strOut .= $clsRpt->closeRow  ();
      }
      $strOut .= $clsRpt->closeReport('<br>');
      return($strOut);
   }

   function loadPeopleViaPIDs($lPIDs, $bIncludeSpon=true, $bIncludeGiftSum=true){
   //------------------------------------------------------------------------------
   // $lPIDs can either be a scalar (single PID) or array of PIDs
   //------------------------------------------------------------------------------
      if (is_array($lPIDs)){
         $this->sqlWhereExtra = ' AND pe_lKeyID IN ('.implode(',', $lPIDs).') ';
      }else {
         $this->sqlWhereExtra = " AND pe_lKeyID=$lPIDs ";
      }
      $this->loadPeople($bIncludeSpon, $bIncludeGiftSum, true);
   }

   function loadPeople($bIncludeSpon, $bIncludeGiftSum, $bPeople){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      $clsACO = new madmin_aco;
      $this->people = array();

      if ($bIncludeSpon){
         $clsSpon = new msponsorship;
      }

      $sqlStr =
        "SELECT
            pe_lKeyID,
            pe_lHouseholdID,   pe_strTitle,      pe_strFName,
            pe_strMName,       pe_strLName,      pe_strPreferredName,
            pe_strSalutation,  pe_strAddr1,      pe_strAddr2,
            pe_strCity,        pe_strState,      pe_strCountry,
            pe_strZip,         pe_strPhone,      pe_strCell, pe_strEmail,
            pe_enumGender,     pe_strNotes,      pe_bNoGiftAcknowledge,
            pe_lAttributedTo,  pe_strImportID,
            pe_lOriginID,      pe_lLastUpdateID,
            tblAttrib.lgen_strListItem AS strAttrib,

            pe_lACO, aco_strFlag, aco_strName, aco_strCurrencySymbol,

            pe_dteBirthDate,
            pe_dteDeathDate,
            usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
            usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,
            pe_dteExpire,
            UNIX_TIMESTAMP(pe_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(pe_dteLastUpdate) AS dteLastUpdate
         FROM people_names
            INNER JOIN admin_users AS usersC ON pe_lOriginID    = usersC.us_lKeyID
            INNER JOIN admin_users AS usersL ON pe_lLastUpdateID= usersL.us_lKeyID
            INNER JOIN admin_aco             ON pe_lACO         = aco_lKeyID
            $this->sqlInnerExtra
            LEFT  JOIN lists_generic AS tblAttrib ON pe_lAttributedTo=tblAttrib.lgen_lKeyID

         WHERE 1
            $this->sqlWhereExtra
            AND ".($bPeople ? 'NOT ' : '')."pe_bBiz
            AND NOT pe_bRetired
         $this->sqlOrderExtra
         $this->sqlLimitExtra;";

      $query = $this->db->query($sqlStr);

      $this->lNumPeople = $query->num_rows();

      if ($this->lNumPeople == 0){
         $this->people[0] = new stdClass;
         $pRec = &$this->people[0];

         $pRec->lKeyID             =
         $pRec->lHouseholdID       =
         $pRec->bHOH               =
         $pRec->strHouseholdName   =

         $pRec->strTitle           =
         $pRec->strFName           =
         $pRec->strMName           =
         $pRec->strLName           =
         $pRec->strPreferredName   =
         $pRec->strSafeName        =

         $pRec->strSafeNameLF      = null;

         $pRec->strSalutation      =
         $pRec->strAddr1           =
         $pRec->strAddr2           =
         $pRec->strCity            =
         $pRec->strState           =

         $pRec->strCountry         =
         $pRec->strZip             =
         $pRec->strPhone           =
         $pRec->strCell            =
         $pRec->strAddress         = null;

         $pRec->strEmail           =
         $pRec->strEmailFormatted  =
         $pRec->enumGender         =

         $pRec->lACO               =
         $pRec->strACO             =
         $pRec->strCurSymbol       =
         $pRec->strFlag            =
         $pRec->strFlagImage       =

         $pRec->bNoGiftAcknowledge =
         $pRec->lAttributedTo      =
         $pRec->strAttrib          =
         $pRec->lImportID          =

         $pRec->strImportRecID     =
         $pRec->dteExpire          =

         $pRec->lOriginID          =
         $pRec->lLastUpdateID      =

         $pRec->dteMysqlBirthDate  =
         $pRec->dteMysqlDeath      =

         $pRec->dteOrigin          =
         $pRec->dteLastUpdate      =

         $pRec->strStaffCFName     =
         $pRec->strStaffCLName     =
         $pRec->strStaffLFName     =
         $pRec->strStaffLLName     = null;

      }else {
         $idx = 0;
         if ($bIncludeGiftSum){
            $clsGifts = new mdonations;
            $clsGifts->bUseDateRange = false;
            $clsGifts->cumulativeOpts = new stdClass;
            $clsGifts->cumulativeOpts->enumCumulativeSource = 'people';
         }

         foreach ($query->result() as $row){
            $this->people[$idx] = new stdClass;
            $pRec = &$this->people[$idx];

            $pRec->lKeyID             = $lPID = $row->pe_lKeyID;
            $pRec->lHouseholdID       = $lHID = $row->pe_lHouseholdID;
            $pRec->bHOH               = $lHID == $lPID;
            $pRec->strHouseholdName   = $this->strHouseholdNameViaHID($lHID);

            $pRec->strTitle           = $strTitle = $row->pe_strTitle;
            $pRec->strFName           = $strFName = $row->pe_strFName;
            $pRec->strMName           = $strMName = $row->pe_strMName;
            $pRec->strLName           = $strLName = $row->pe_strLName;
            $pRec->strPreferredName   = $strPreferred = $row->pe_strPreferredName;
            $pRec->strSafeName        = htmlspecialchars(
                                                               strBuildName(false, $strTitle, $strPreferred,
                                                                            $strFName, $strLName, $strMName));
            $pRec->strSafeNameLF      = htmlspecialchars(
                                                               strBuildName(true, $strTitle, $strPreferred,
                                                                            $strFName, $strLName, $strMName));

            $pRec->strSalutation      = $row->pe_strSalutation;
            $pRec->strAddr1           = $row->pe_strAddr1;
            $pRec->strAddr2           = $row->pe_strAddr2;
            $pRec->strCity            = $row->pe_strCity;
            $pRec->strState           = $row->pe_strState;

            $pRec->strCountry         = $row->pe_strCountry;
            $pRec->strZip             = $row->pe_strZip;
            $pRec->strPhone           = $row->pe_strPhone;
            $pRec->strCell            = $row->pe_strCell;
            $pRec->strAddress         =
                        strBuildAddress(
                                 $pRec->strAddr1, $pRec->strAddr2,   $pRec->strCity,
                                 $pRec->strState, $pRec->strCountry, $pRec->strZip,
                                 true);

            $pRec->strEmail           = $row->pe_strEmail;
            $pRec->strEmailFormatted  = strBuildEmailLink($pRec->strEmail, '', false, '');
            $pRec->enumGender         = $row->pe_enumGender;

            $pRec->lACO               = $row->pe_lACO;
            $pRec->strACO             = $row->aco_strName;
            $pRec->strCurSymbol       = $row->aco_strCurrencySymbol;
            $pRec->strFlag            = $row->aco_strFlag;
            $pRec->strFlagImage       = $clsACO->strFlagImage($pRec->strFlag, $pRec->strACO);

            $pRec->bNoGiftAcknowledge = $row->pe_bNoGiftAcknowledge;
            $pRec->lAttributedTo      = $row->pe_lAttributedTo;
            $pRec->strAttrib          = $row->strAttrib;
            $pRec->lImportID          = $row->pe_strImportID;

            $pRec->strImportRecID     = $row->pe_strImportID;
            $pRec->dteExpire          = dteMySQLDate2Unix($row->pe_dteExpire);

            $pRec->lOriginID          = $row->pe_lOriginID;
            $pRec->lLastUpdateID      = $row->pe_lLastUpdateID;

            $pRec->dteMysqlBirthDate  = $row->pe_dteBirthDate;
            $pRec->dteMysqlDeath      = $row->pe_dteDeathDate;

            $pRec->strNotes           = $row->pe_strNotes;

            $pRec->dteOrigin          = $row->dteOrigin;
            $pRec->dteLastUpdate      = $row->dteLastUpdate;

            $pRec->strStaffCFName     = $row->strCFName;
            $pRec->strStaffCLName     = $row->strCLName;
            $pRec->strStaffLFName     = $row->strLFName;
            $pRec->strStaffLLName     = $row->strLLName;

               //-------------------
               // sponsorship
               //-------------------
            if ($bIncludeSpon){
               $clsSpon->sponsorshipInfoViaPID($lPID);
               $pRec->lNumSponsorship = $lNumSpons = $clsSpon->lNumSponsors;
               if ($lNumSpons == 0){
                  $pRec->sponInfo = null;
               }else {
                  $pRec->sponInfo = $clsSpon->sponInfo;
               }
            }

               //-------------------
               // cumulative gifts
               //-------------------
            if ($bIncludeGiftSum){
               $clsGifts->lPeopleID = $lPID;
               $clsGifts->cumulativeOpts->enumMoneySet = 'all';

               $clsGifts->cumulativeOpts->bSoft        = false;
               $clsGifts->cumulativeDonation($clsACO, $pRec->lTotHardGifts);
               $pRec->lNumACODonationGroups_hard = $clsGifts->lNumCumulative;
               $pRec->donationsViaACO_hard       = $clsGifts->cumulative;

               $clsGifts->cumulativeOpts->bSoft        = true;
               $clsGifts->cumulativeDonation($clsACO, $pRec->lTotSoftGifts);
               $pRec->lNumACODonationGroups_soft = $clsGifts->lNumCumulative;
               $pRec->donationsViaACO_soft       = $clsGifts->cumulative;
            }else {
               $pRec->lNumACODonationGroups_hard =
               $pRec->donationsViaACO_hard       =
               $pRec->lNumACODonationGroups_soft =
               $pRec->donationsViaACO_soft       = null;
            }
            ++$idx;
         }
      }
   }

   function strWhereByLetter($strLetter, $enumContext, $strFieldPrefix=''){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($strLetter=='#'){
         $sqlWhere = "AND ( (LEFT(".$strFieldPrefix."pe_strLName, 1) > 'Z')
                        OR ((LEFT(".$strFieldPrefix."pe_strLName, 1) < 'A'))) ";
      }elseif ($strLetter=='' || $strLetter=='*'){
         $sqlWhere = '';
      }else {
         $sqlWhere = 'AND (LEFT('.$strFieldPrefix.'pe_strLName, 1)='.strPrepStr($strLetter).' ) ';
      }
      switch ($enumContext){
         case CENUM_CONTEXT_HOUSEHOLD:
            $sqlWhere .= ' AND '.$strFieldPrefix.'pe_lHouseholdID=pe_lKeyID ';
         case CENUM_CONTEXT_PEOPLE:
            $sqlWhere .= ' AND NOT '.$strFieldPrefix.'pe_bBiz ';
            break;
         case CENUM_CONTEXT_BIZ:
            $sqlWhere .= ' AND '.$strFieldPrefix.'pe_bBiz ';
            break;
         default:
            screamForHelp($enumContext.': invalid context type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($sqlWhere);
   }

   function loadPeopleDirectoryPage($strWhereExtra, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlLimitExtra = " LIMIT $lStartRec, $lRecsPerPage ";
      $this->sqlWhereExtra = $strWhereExtra;
      $this->sqlOrderExtra = 'ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID ';
      $this->loadPeople(false, true, true);
   }

   function loadHouseholdDirectoryPage($strWhereExtra, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlLimitExtra = " LIMIT $lStartRec, $lRecsPerPage ";
      $this->sqlWhereExtra = $strWhereExtra;
      $this->sqlOrderExtra = 'ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID ';
      $this->loadPeople(false, false, true);
      if ($this->lNumPeople > 0){
         $clsRel = new mrelationships;
         foreach($this->people as $person){
            $lPID = $this->lHouseholdID = $person->lKeyID;
            $this->loadPIDsViaHouseholdHID();
            $person->household = arrayCopy($this->arrHouseholds);

            foreach ($person->household as $member){
               $clsRel->reciprocalRelInfoViaPeopleIDs(
                             $lPID,                $member->PID,
                             $member->lNumRel_A2B, $member->relA2B,
                             $member->lNumRel_B2A, $member->relB2A);
            }
         }
      }
   }

   function peopleBizInfoViaPID($lPID, &$pbInfo){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $pbInfo = new stdClass;
      $sqlStr =
         "SELECT pe_bBiz, pe_strFName, pe_strLName
          FROM people_names
          WHERE NOT pe_bRetired AND pe_lKeyID=$lPID;";

      $query = $this->db->query($sqlStr);
      $row = $query->row();
      $pbInfo->lKeyID        = $lPID;
      $pbInfo->bBiz          = $bBiz = $row->pe_bBiz;
      $pbInfo->strFName      = $row->pe_strFName;
      $pbInfo->strLName      = $row->pe_strLName;
      if ($bBiz){
         $pbInfo->strSafeNameFL = $pbInfo->strSafeNameLF = htmlspecialchars($row->pe_strLName);
         $pbInfo->strLink       = strLinkView_BizRecord($lPID, 'View business record', true);
      }else {
         $pbInfo->strSafeNameFL = htmlspecialchars($row->pe_strFName.' '.$row->pe_strLName);
         $pbInfo->strSafeNameLF = htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName);
         $pbInfo->strLink       = strLinkView_PeopleRecord($lPID, 'View people record', true);
      }
   }

   function addPeopleRecFromUserRec($lUserID){
   //---------------------------------------------------------------------
   // create a people record based on the account record
   //---------------------------------------------------------------------
      global $gclsChapterACO;

      $this->people = array();
      $this->people[0] = new stdClass;
      $pRec = &$this->people[0];
      $pRec->lHouseholdID      = 0;
      $pRec->lAttributedTo     = null;
      $pRec->enumGender        = 'Unknown';
      $pRec->dteExpire         = null;
      $pRec->lACO              = $gclsChapterACO->lKeyID;
      $pRec->dteMysqlBirthDate = null;
      $pRec->dteMysqlDeath     = null;
      $pRec->strNotes          = 'Auto-generated from user account '.str_pad($lUserID, 5, '0', STR_PAD_LEFT);

         // load the user account
      $sqlStr =
        "SELECT
            us_strFirstName, us_strLastName, us_strTitle,
            us_strPhone, us_strCell, us_strEmail,
            us_strAddr1, us_strAddr2, us_strCity,
            us_strState, us_strCountry, us_strZip
         FROM admin_users
         WHERE us_lKeyID=$lUserID;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows != 1) {
         echo('<font face="monospace" style="font-size: 8pt;">'.__FILE__.' Line: <b>'.__LINE__.":</b><br><b>\$sqlStr=</b><br>".nl2br($sqlStr)."<br><br></font>\n");
         screamForHelp('UNEXPECTED EOF<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      }else {
         $row = $query->row();

         $pRec->strTitle           = $row->us_strTitle;
         $pRec->strFName           = $row->us_strFirstName;
         $pRec->strMName           = '';
         $pRec->strLName           = $row->us_strLastName;
         $pRec->strPreferredName   = $row->us_strFirstName;
         $pRec->strSalutation      = $row->us_strFirstName;
         $pRec->strAddr1           = $row->us_strAddr1;
         $pRec->strAddr2           = $row->us_strAddr2;
         $pRec->strCity            = $row->us_strCity;
         $pRec->strState           = $row->us_strState;
         $pRec->strCountry         = $row->us_strCountry;
         $pRec->strZip             = $row->us_strZip;
         $pRec->strPhone           = $row->us_strPhone;
         $pRec->strCell            = $row->us_strCell;
         $pRec->strEmail           = $row->us_strEmail;

         $lPeopleID = $this->lCreateNewPeopleRec();
         return($lPeopleID);
      }
   }


}

?>