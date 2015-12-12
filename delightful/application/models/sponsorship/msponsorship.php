<?php
/*---------------------------------------------------------------------
// copyright (c) 2011-2015 Database Austin
// Austin, Texas 78759
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
   initSponClass                   ()

   listOfSponsors                  ($bIncludeInactive)
   bPersonASponsor                 ()
   lNumSponViaPID                  ($lPID, $bActive)

   sponsorInfoViaID                ($lSID)
   sponsorshipInfoViaPID           ($lPID)
   sponsorInfoGeneric              ($bViaSID, $lSID, $bViaPID, $lPID)
   sponsorInfoGenericViaWhere      ($strWhere, $strLimit)

   strShowSponsorSumLiteViaPID     ($bShowTitle, $strReturnIfNoSpon, $lPID)
   strShowSponsorshipSummaryViaPID ($bShowTitle, $strReturnIfNoSpon, $lPID)

   retireSponsorshipsViaPID        ($lPID, $lGroupID)
   retireSingleSponsorship         ($lGiftID, &$lGroupID)
   logSponsorshipRetire            ($lGiftID, &$lGroupID)

   lAddNewSponsorship              ()
   updateSponsorship               ()
   addClientToSponsor              ($lSponID, $lClientID)

   sponsorshipHTMLSummary          ()

   lNumSponsorsViaProgram          ($lProgramID, $bIncludeInactive, $strQualSponLetter)
   removeSponsor                   ($lSponID)

   addRemoveHonoreeToSponsorship   ($lSponID, $lHonPID)

   inactivateSponsorship           ($lSponID, $dteInactive, $lTermTypeID)

   addClientToSponsor              ($lSponID, $lClientID){
   sponsorsViaClientID             ($lClientID, $bActiveOnly)
  ---------------------------------------------------------------------
      $this->load->model('sponsorship/msponsorship', 'clsSpon');
---------------------------------------------------------------------*/

class msponsorship extends CI_Model{

   public
       $lItemID,
       $bInactive,          $dteInactive,         $lDefPaymentType,
       $lDefSponsorCatID,   $bPersonRetired,
       $bClientRetired,     $bHonoreeRetired,     $curCommitment,
       $lInactiveCatID,     $strInactiveCat,      $bInactiveViaXfer,
       $lSponCat,           $strSponCat,
       $strSFConID_Spon,    $strSFConID_Hon;

   public $bUseDateRange, $dteStartDate, $dteEndDate, $strOrderBy;

   public $lNumSponsors, $sponsorList;

      //---------------------------
      // sponsor payment history
      //---------------------------
   public
      $payHistory, $lPayHistoryACOCnt;


   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->initSponClass();
   }

   public function initSponClass(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->sponInfo = $this->lNumSponsors = null;

      $this->bUseDateRange = false;

      $this->strOrderBy = '';
   }

   public function listOfSponsors($bIncludeInactive){
   //---------------------------------------------------------------------
   // list of all sponsors, sorted by name
   //---------------------------------------------------------------------
      $this->sponsorList = array();
      $sqlStr =
        'SELECT sp_lKeyID, sp_curCommitment, sp_lCommitmentACO, sp_bInactive
         FROM sponsor
            INNER JOIN people_names ON sp_lForeignID=pe_lKeyID
         WHERE NOT sp_bRetired
            AND NOT pe_bRetired '
            .($bIncludeInactive ? '' : 'AND NOT sp_bInactive ').'
         ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID;';

      $query = $this->db->query($sqlStr);

      if ($query->num_rows() > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $this->sponsorList[$idx] = new stdClass;
            $this->sponsorList[$idx]->lSponID        = $row->sp_lKeyID;
            $this->sponsorList[$idx]->curCommitment  = $row->sp_curCommitment;
            $this->sponsorList[$idx]->lCommitmentACO = $row->sp_lCommitmentACO;
            $this->sponsorList[$idx]->bInactive      = $row->sp_bInactive;
            ++$idx;
         }
      }
   }

   public function bPersonASponsor(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_null($this->lPeopleID)){
         screamForHelp('FOREIGN KEY NOT SET<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      }
      $sqlStr =
          "SELECT COUNT(*) AS lNumSponsorship
           FROM sponsor
           WHERE sp_lForeignID=$this->lPeopleID
              AND NOT sp_bRetired
              AND NOT sp_bInactive;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(false);
      }else {
         $row = $query->row();
         return($row->lNumSponsorship > 0);
      }
   }

   public function lNumSponViaPID($lPID, $bActive){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
          "SELECT COUNT(*) AS lNumSponsorship
           FROM sponsor
           WHERE sp_lForeignID=$lPID
              AND NOT sp_bRetired
              AND ".($bActive ? ' NOT ' : '').' sp_bInactive;';

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
         return((int)$row->lNumSponsorship);
      }
   }

   public function sponsorInfoViaID($lSID){
   //---------------------------------------------------------------------
   // for a given sponsorshipID, return the sponsor's name and some other
   // sponsorship info
   //---------------------------------------------------------------------
      $this->sponsorInfoGeneric(true, $lSID, false, null);
   }

   function sponsorshipInfoViaPID($lPID){
   //---------------------------------------------------------------------
   // lPID can be people or biz ID
   //---------------------------------------------------------------------
      $this->sponsorInfoGeneric(false, null, true, $lPID);
   }

   public function sponsorInfoGeneric($bViaSID, $lSID, $bViaPID, $lPID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sponInfo = array();

      if ($bViaSID){
         $strWhere = " AND sp_lKeyID=$lSID ";
      }elseif ($bViaPID){
         $strWhere = " AND sp_lForeignID=$lPID ";
      }
      $this->sponsorInfoGenericViaWhere($strWhere, '');
   }

   public function sponsorInfoGenericViaWhere($strWhere, $strLimit){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($this->strOrderBy == ''){
         $strOrder =
           'tblPeopleSpon.pe_strLName, tblPeopleSpon.pe_strFName, tblPeopleSpon.pe_lKeyID,
            cr_strLName, cr_strFName, sp_lClientID,
            sp_lKeyID ';
      }else {
         $strOrder = $this->strOrderBy;
      }

      $sqlStr =
         "SELECT
             sp_lKeyID, sp_lClientID,
             sp_lForeignID, sp_lHonoreeID, sp_bInactive,
             sp_lSponsorProgramID, sp_lDefPayType, sp_curCommitment, sp_lCommitmentACO,
             aco_strFlag, aco_strCurrencySymbol, aco_strName,

             sp_dteStartMoYr, sp_dteInactive, sp_lInactiveCatID,

             sp_strTerminationNote, sp_bInactiveDueToXfer,
             listInactive.lgen_strListItem AS strSponTermType,

             tblPeopleSpon.pe_strLName AS strLName_Spon, tblPeopleSpon.pe_strFName   AS strFName_Spon,
             tblPeopleSpon.pe_strMName AS strMName_Spon, tblPeopleSpon.pe_bRetired   AS bSponRetired,
             tblPeopleSpon.pe_strAddr1 AS strSponAddr1,  tblPeopleSpon.pe_strAddr2   AS strSponAddr2,
             tblPeopleSpon.pe_strCity  AS strSponCity,   tblPeopleSpon.pe_strState   AS strSponState,
             tblPeopleSpon.pe_strZip   AS strSponZip,    tblPeopleSpon.pe_strCountry AS strSponCountry,
             tblPeopleSpon.pe_strEmail AS strSponEmail,
             tblPeopleSpon.pe_strPhone AS strSponPhone,
             tblPeopleSpon.pe_strCell  AS strSponCell,
             tblPeopleSpon.pe_strPreferredName AS strSponPName, tblPeopleSpon.pe_strTitle AS strSponTitle,

             tblPeopleSpon.pe_bBiz     AS bSponBiz,

             tblPeopleHon.pe_strLName  AS strLName_Hon,  tblPeopleHon.pe_strFName   AS strFName_Hon,
             tblPeopleHon.pe_strMName  AS strMName_Hon,  tblPeopleHon.pe_bRetired   AS bHonRetired,
             tblPeopleHon.pe_strAddr1  AS strHonAddr1,   tblPeopleHon.pe_strAddr2   AS strHonAddr2,
             tblPeopleHon.pe_strCity   AS strHonCity,    tblPeopleHon.pe_strState   AS strHonState,
             tblPeopleHon.pe_strZip    AS strHonZip,     tblPeopleHon.pe_strCountry AS strHonCountry,
             tblPeopleHon.pe_strEmail  AS strHonEmail,
             tblPeopleHon.pe_strPhone  AS strHonPhone,
             tblPeopleHon.pe_strCell   AS strHonCell,
             tblPeopleHon.pe_strPreferredName AS strHonPName, tblPeopleHon.pe_strTitle AS strHonTitle,

             tblPeopleHon.pe_bBiz      AS bHonBiz,

             sp_lAttributedTo,
             listAttrib.lgen_strListItem AS strAttributedTo,

             cr_strFName, cr_strLName, cr_dteBirth, cr_lLocationID,
             cr_enumGender,
             cl_strLocation, cl_strCountry,
             cr_bRetired,
             sc_strProgram,

             UNIX_TIMESTAMP(sp_dteOrigin)     AS dteOrigin,
             UNIX_TIMESTAMP(sp_dteLastUpdate) AS dteLastUpdate,
             uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
             ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName

         FROM sponsor
            INNER JOIN people_names  AS tblPeopleSpon ON sp_lForeignID     = tblPeopleSpon.pe_lKeyID
            INNER JOIN admin_users   AS uc ON uc.us_lKeyID=sp_lOriginID
            INNER JOIN admin_users   AS ul ON ul.us_lKeyID=sp_lLastUpdateID
            INNER JOIN admin_aco           ON aco_lKeyID  = sp_lCommitmentACO
            LEFT  JOIN people_names  AS tblPeopleHon  ON sp_lHonoreeID        = tblPeopleHon.pe_lKeyID
            LEFT  JOIN client_records                 ON cr_lKeyID            = sp_lClientID
            LEFT  JOIN client_location                ON cr_lLocationID       = cl_lKeyID
            LEFT  JOIN lists_sponsorship_programs     ON sp_lSponsorProgramID = sc_lKeyID
            LEFT  JOIN lists_generic AS listInactive  ON sp_lInactiveCatID    = listInactive.lgen_lKeyID
            LEFT  JOIN lists_generic AS listAttrib    ON sp_lAttributedTo     = listAttrib.lgen_lKeyID

         WHERE NOT sp_bRetired
            $strWhere
         ORDER BY $strOrder
         $strLimit;";

      $query = $this->db->query($sqlStr);
      $this->lNumSponsors = $numRows = $query->num_rows();
      if ($numRows==0) {
         $this->sponInfo[0] = new stdClass;
         $sRec = &$this->sponInfo[0];

         $sRec->lKeyID              =
         $sRec->lForeignID          =
         $sRec->strSponsorFName     =
         $sRec->strSponsorMName     =
         $sRec->strSponsorLName     =
         $sRec->bSponBiz            =
         $sRec->strSponSafeNameFL   =
         $sRec->strSponSafeNameLF   =
         $sRec->bPersonRetired      =
         $sRec->bClientRetired      =
         $sRec->bHonoreeRetired     =
         $sRec->lHonoreeID          =
         $sRec->strSponsorFNameHon  =
         $sRec->strSponsorMNameHon  =
         $sRec->strSponsorLNameHon  =
         $sRec->bHonBiz             =
         $sRec->strHonSafeNameFL    =
         $sRec->strHonSafeNameLF    =
         $sRec->strSFConID_Spon     =
         $sRec->strSFConID_Hon      =
         $sRec->lClientID           =
         $sRec->lItemID             =
         $sRec->lSponsorProgID      =
         $sRec->strSponProgram      =
         $sRec->lAttributedTo       =
         $sRec->strAttributedTo     =
         $sRec->strClientFName      =
         $sRec->strClientLName      =
         $sRec->strClientSafeNameFL =
         $sRec->strClientSafeNameLF =
         $sRec->dteClientBDayMySQL  =
         $sRec->strGender           =
         $sRec->strLocationName     =
         $sRec->strLocationCountry  =
         $sRec->dteStart            =
         $sRec->bInactive           =
         $sRec->dteInactive         =
         $sRec->lInactiveCatID      =
         $sRec->strInactiveCat      =
         $sRec->bInactiveViaXfer    =
         $sRec->lDefPaymentType     =
         $sRec->curCommitment       =
         $sRec->lCommitACO          =
         $sRec->objClientBirth      =
         $sRec->strAgeBDay          =

         $sRec->dteOrigin           =
         $sRec->dteLastUpdate       =
         $sRec->ucstrFName          =
         $sRec->ucstrLName          =
         $sRec->ulstrFName          =
         $sRec->ulstrLName          = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {

            $this->sponInfo[$idx] = new stdClass;
            $sRec = &$this->sponInfo[$idx];
            $sRec->lKeyID              = $row->sp_lKeyID;

            $sRec->lForeignID          = $row->sp_lForeignID;
            $sRec->strSponsorFName     = $row->strFName_Spon;
            $sRec->strSponsorMName     = $row->strMName_Spon;
            $sRec->strSponsorLName     = $row->strLName_Spon;
            $sRec->strSponsorPName     = $row->strSponPName;
            $sRec->strSponTitle        = $row->strSponTitle;

            $sRec->bSponBiz            = $row->bSponBiz;
            if ($row->bSponBiz){
               $sRec->strSponSafeNameFL =
               $sRec->strSponSafeNameLF = htmlspecialchars($sRec->strSponsorLName);
            }else {
               $sRec->strSponSafeNameFL =
                                         htmlspecialchars(
                                               strBuildName(false, $sRec->strSponTitle, $sRec->strSponsorPName,
                                                            $sRec->strSponsorFName, $sRec->strSponsorLName, $sRec->strSponsorMName));
               $sRec->strSponSafeNameLF =
                                         htmlspecialchars(
                                               strBuildName(true, $sRec->strSponTitle, $sRec->strSponsorPName,
                                                            $sRec->strSponsorFName, $sRec->strSponsorLName, $sRec->strSponsorMName));
            }

            $sRec->bPersonRetired      = (boolean)$row->bSponRetired;
            $sRec->bClientRetired      = (boolean)$row->cr_bRetired;
            $sRec->bHonoreeRetired     = (boolean)$row->bHonRetired;

            $sRec->lHonoreeID          = $row->sp_lHonoreeID;
            $sRec->bHonoree            = !(is_null($row->sp_lHonoreeID));
            $sRec->strSponsorFNameHon  = $row->strFName_Hon;
            $sRec->strSponsorMNameHon  = $row->strMName_Hon;
            $sRec->strSponsorLNameHon  = $row->strLName_Hon;
            $sRec->bHonBiz             = $row->bHonBiz;
            $sRec->strHonPName         = $row->strHonPName;
            $sRec->strHonTitle         = $row->strHonTitle;

            if ($row->bHonBiz){
               $sRec->strHonSafeNameFL =
               $sRec->strHonSafeNameLF = htmlspecialchars($sRec->strSponsorLName);
            }else {
               $sRec->strHonSafeNameFL =
                                         htmlspecialchars(
                                               strBuildName(false, $sRec->strHonTitle, $sRec->strHonPName,
                                                            $sRec->strSponsorFNameHon, $sRec->strSponsorLNameHon, $sRec->strSponsorMNameHon));

//                      htmlspecialchars($sRec->strSponsorFNameHon.' ' .$sRec->strSponsorLNameHon);
               $sRec->strHonSafeNameLF =
                                         htmlspecialchars(
                                               strBuildName(true, $sRec->strHonTitle, $sRec->strHonPName,
                                                            $sRec->strSponsorFNameHon, $sRec->strSponsorLNameHon, $sRec->strSponsorMNameHon));
//                      htmlspecialchars($sRec->strSponsorLNameHon.', '.$sRec->strSponsorFNameHon);
            }

               //------------------------------
               // sponsor contact info
               //------------------------------
            $sRec->strSponAddr1        = $row->strSponAddr1;
            $sRec->strSponAddr2        = $row->strSponAddr2;
            $sRec->strSponCity         = $row->strSponCity;
            $sRec->strSponState        = $row->strSponState;
            $sRec->strSponZip          = $row->strSponZip;
            $sRec->strSponCountry      = $row->strSponCountry;
            $sRec->strSponEmail        = $row->strSponEmail;
            $sRec->strSponPhone        = $row->strSponPhone;
            $sRec->strAddr =
                                    strBuildAddress(
                                             $row->strSponAddr1, $row->strSponAddr2,   $row->strSponCity,
                                             $row->strSponState, $row->strSponCountry, $row->strSponZip,
                                             true);

               //------------------------------
               // honoree contact info
               //------------------------------
            $sRec->strHonAddr1         = $row->strHonAddr1;
            $sRec->strHonAddr2         = $row->strHonAddr2;
            $sRec->strHonCity          = $row->strHonCity;
            $sRec->strHonState         = $row->strHonState;
            $sRec->strHonZip           = $row->strHonZip;
            $sRec->strHonCountry       = $row->strHonCountry;
            $sRec->strHonEmail         = $row->strHonEmail;
            $sRec->strHonPhone         = $row->strHonPhone;

            $sRec->lClientID           = $row->sp_lClientID;
            $sRec->lSponsorProgID      = $row->sp_lSponsorProgramID;
            $sRec->strSponProgram      = $row->sc_strProgram;

            $sRec->lAttributedTo       = $row->sp_lAttributedTo;
            $sRec->strAttributedTo     = $row->strAttributedTo;

            $sRec->strClientFName      = $row->cr_strFName.'';
            $sRec->strClientLName      = $row->cr_strLName.'';
            if (is_null($sRec->lClientID)){
               $sRec->strClientSafeNameFL =
               $sRec->strClientSafeNameLF = null;
            }else {
               $sRec->strClientSafeNameFL = htmlspecialchars($row->cr_strFName.' ' .$row->cr_strLName);
               $sRec->strClientSafeNameLF = htmlspecialchars($row->cr_strLName.', '.$row->cr_strFName);
            }
            $sRec->dteClientBDayMySQL  = $row->cr_dteBirth;
            $sRec->enumGender          = $row->cr_enumGender;

            $sRec->strLocation         = $row->cl_strLocation.'';
            $sRec->strLocationCountry  = $row->cl_strCountry.'';

            $sRec->dteStart            = dteMySQLDate2Unix($row->sp_dteStartMoYr);
            $sRec->bInactive           = (boolean)$row->sp_bInactive;
            $sRec->dteInactive         = dteMySQLDate2Unix($row->sp_dteInactive);
            $sRec->lInactiveCatID      = $row->sp_lInactiveCatID;
            $sRec->strSponTermType     = $row->strSponTermType;
            $sRec->bInactiveViaXfer    = (boolean)$row->sp_bInactiveDueToXfer;

            $sRec->lDefPaymentType     = $row->sp_lDefPayType;
            $sRec->curCommitment       = $row->sp_curCommitment;
            $sRec->lCommitACO          = $row->sp_lCommitmentACO;
            $sRec->strCommitACOFlagImg =
                         '<img src="'.DL_IMAGEPATH.'/flags/'.$row->aco_strFlag
                                   .'" alt="flag icon" title="'.$row->aco_strName.'">';
            $sRec->strCommitACOCurSym  = $row->aco_strCurrencySymbol;
            $sRec->strCommitACOName    = $row->aco_strName;

               //------------------------------
               // client age/birth day info
               //------------------------------
            if (is_null($sRec->dteClientBDayMySQL)){
               $sRec->objClientBirth = null;
               $sRec->strClientAgeBDay = 'n/a';
            }else {
               $sRec->objClientBirth = new dl_date_time;
               $sRec->objClientBirth->setDateViaMySQL(0, $sRec->dteClientBDayMySQL);
               $sRec->strClientAgeBDay = $sRec->objClientBirth->strPeopleAge(0, $sRec->dteClientBDayMySQL, $lAgeYears);
            }

            $sRec->dteOrigin         = $row->dteOrigin;
            $sRec->dteLastUpdate     = $row->dteLastUpdate;
            $sRec->ucstrFName        = $row->strUCFName;
            $sRec->ucstrLName        = $row->strUCLName;
            $sRec->ulstrFName        = $row->strULFName;
            $sRec->ulstrLName        = $row->strULLName;

            ++$idx;
         }
      }
   }

   public function strSponsorSumLiteViaPID($lPID, $bShowLink=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strHold = '';
      $lNumActive   = $this->lNumSponViaPID($lPID, true);
      $lNumInActive = $this->lNumSponViaPID($lPID, false);

      if ($lNumActive==0 && $lNumInActive==0){
         $strHold = 'n/a';
      }else {
         $strHold .= $lNumActive.' active<br>';
         $strHold .= $lNumInActive.' inactive<br>';
         if ($bShowLink){
            $strHold .= strLinkView_SponsorshipViaPID($lPID, 'View Sponsorships', true).'&nbsp;'
                       .strLinkView_SponsorshipViaPID($lPID, 'View', false);
         }
      }
      return($strHold);
   }

   public function strShowSponsorshipSummaryViaPID($bShowTitle, $strReturnIfNoSpon, $lPID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strHold = '';

      if ($this->bPersonASponsor()) {
         $strHold = '<table border="0">';
         if ($bShowTitle) $strHold .= '<tr><td colspan="3">Sponsorships:</td></tr>';

         $this->sponsorshipInfoViaPID($lPID);
         foreach ($this->sponInfo as $clsSponInfo){

            if ($clsSponInfo->bInactive){
               $strInactive     = '<i><font color="gray">';
               $strStopInactive = ' (inactive)</i></font>';
            }else {
               $strInactive     = '';
               $strStopInactive = '';
            }
            $strHold .=
               '<tr>
                    <td>'
                      .$strInactive
                      .strLinkView_Sponsorship($clsSponInfo->lKeyID, 'view sponsorship record', true)
                      .str_pad($clsSponInfo->lKeyID, 5, '0', STR_PAD_LEFT)
                      .$strStopInactive.'
                    </td>
                 </tr>
                 <tr>
                    <td>'
                      .$strInactive
                      .htmlspecialchars($clsSponInfo->strSponProgram)
                      .$strStopInactive.'
                    </td>
                 </tr>';

           if (is_null($clsSponInfo->lClientID)){
              $strHold .= '<tr><td>'.$strInactive.'<i>Client not set</i>'.$strStopInactive.'</td></tr>';
           }else {
              $strHold .=
                   '<tr>
                       <td>'
                         .$strInactive
                         .'Client: '
                         .$clsSponInfo->strClientSafeNameFL.'<br>('
                         .$clsSponInfo->strLocation.')'
                         .$strStopInactive
                     .'</td>'
                  .'</tr>';
              }
            }
         $strHold .= '</table>';
      }else {
         $strHold = $strReturnIfNoSpon;
      }
      return($strHold);
   }

   public function retireSponsorshipsViaPID($lPID, $lGroupID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "SELECT sp_lKeyID FROM sponsor WHERE sp_lForeignID=$lPID AND NOT sp_bRetired;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows > 0){
         $idx = 0;
         foreach ($query->result() as $row) {
            $row = mysql_fetch_array($result);
            $this->retireSingleSponsorship($row->sp_lKeyID, $lGroupID);
            ++$idx;
         }
      }
   }

   public function retireSingleSponsorship($lSponID, &$lGroupID){
   //---------------------------------------------------------------------
   // $lGroupID is the recyle bin group id; set to null if deleting
   // a single gift
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        "UPDATE sponsor
         SET
            sp_bRetired=1,
            sp_lLastUpdateID=$glUserID
         WHERE sp_lKeyID=$lSponID;";

      $this->db->query($sqlStr);
      $this->logSponsorshipRetire($lSponID, $lGroupID);

         // remove UF table entries
      $uf = new muser_fields;
      $uf->deleteForeignViaUFTableType(CENUM_CONTEXT_SPONSORSHIP, $lSponID);
   }

   private function logSponsorshipRetire($lSponID, &$lGroupID){
   //-----------------------------------------------------------------------
   // caller must first call $this->peopleInfoLight();
   //-----------------------------------------------------------------------
      $clsRecycle = new mrecycle_bin;

      $clsRecycle->lForeignID      = $lSponID;
      $clsRecycle->strTable        = 'sponsor';
      $clsRecycle->strRetireFN     = 'sp_bRetired';
      $clsRecycle->strKeyIDFN      = 'sp_lKeyID';
      $clsRecycle->strNotes        = 'Retired sponsorship '.str_pad($lSponID, 5, '0', STR_PAD_LEFT);
      $clsRecycle->lGroupID        = $lGroupID;
      $clsRecycle->enumRecycleType = 'Sponsorship';

      $clsRecycle->addRecycleEntry();
   }

   public function lAddNewSponsorship(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      $sponRec = $this->sponInfo[0];

      $sqlStr =
           'INSERT INTO sponsor
            SET '.$this->sqlCommonSP().',
               sp_lForeignID         = '.$sponRec->lForeignID.',
               sp_lHonoreeID         = NULL,
               sp_lClientID          = NULL,
               sp_lSponsorProgramID  = '.$sponRec->lSponsorProgID.',
               sp_bInactive          = 0,
               sp_dteInactive        = NULL,
               sp_lInactiveCatID     = NULL,
               sp_strTerminationNote = "",
               sp_bInactiveDueToXfer = 0,
               sp_lxferSponsorID     = NULL,
               sp_bRetired           = 0,
               sp_lOriginID          = '.$glUserID.',
               sp_dteOrigin          = NOW();';

      $query = $this->db->query($sqlStr);

      $sponRec->lKeyID = $this->db->insert_id();
      return($sponRec->lKeyID);
   }

   public function updateSponsorship(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      $sponRec = $this->sponInfo[0];

      $sqlStr =
           'UPDATE sponsor
            SET '.$this->sqlCommonSP()."
            WHERE sp_lKeyID=$sponRec->lKeyID;";
      $query = $this->db->query($sqlStr);
   }

   private function sqlCommonSP(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;
      $sponRec = $this->sponInfo[0];
      return('
         sp_curCommitment  = '.$sponRec->curCommitment.',
         sp_lCommitmentACO = '.$sponRec->lCommitACO.',
         sp_lAttributedTo  = '.strDBValueConvert_INT($sponRec->lAttributedTo).',
         sp_dteStartMoYr   = '.strPrepDate($sponRec->dteStart).',
         sp_lLastUpdateID  = '.$glUserID.',
         sp_dteLastUpdate  = NOW() ');
   }

   public function sponsorshipHTMLSummary(){
   //-----------------------------------------------------------------------
   // assumes user has called $clsSpon->sponsorInfoViaID($lSID);
   //-----------------------------------------------------------------------
      global $gdteNow, $genumDateFormat;

      $params = array('enumStyle' => 'terse');
      $clsRpt  = new generic_rpt($params);
      $clsRpt->setEntrySummary();

      $sponRec = $this->sponInfo[0];

      $bBiz = $sponRec->bSponBiz;
      if ($bBiz){
         $strLinkPeopleBiz = strLinkView_BizRecord($sponRec->lForeignID, 'View business record', true);
         $strLabel = '(business ID: ';
      }else {
         $strLinkPeopleBiz = strLinkView_PeopleRecord($sponRec->lForeignID, 'View people record', true);
         $strLabel = '(people ID: ';
      }

      $lSponID = $sponRec->lKeyID;
      $strOut =
          $clsRpt->openReport('', '')

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel(' Sponsor:')
         .$clsRpt->writeCell (
                              $strLinkPeopleBiz.'&nbsp;'
                             .$sponRec->strSponSafeNameFL
                             .'&nbsp;&nbsp;'
                             .$strLabel.str_pad($sponRec->lForeignID, 5, '0', STR_PAD_LEFT)
                             .')')
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Sponsorship:')
         .$clsRpt->writeCell (
                             strLinkView_Sponsorship($lSponID, 'View sponsorship', true).'&nbsp;'
                            .htmlspecialchars($sponRec->strSponProgram)
                            .'&nbsp;&nbsp;(sponsor ID: '.str_pad($lSponID, 5, '0', STR_PAD_LEFT)
                            .')')
         .$clsRpt->closeRow  ();

         if (is_null($sponRec->lClientID)){
            $strClient = '<i>not set</i>';
         }else {
            $strClient =
                      strLinkView_ClientRecord($sponRec->lClientID, 'View client record', true).'&nbsp;'
                     .$sponRec->strClientSafeNameFL
                     .'&nbsp;(client ID: '
                     .str_pad($sponRec->lClientID, 5, '0', STR_PAD_LEFT).')'
                     ;
         }

      $strOut .=
          $clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Client:')
         .$clsRpt->writeCell ($strClient)
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Status:')
         .$clsRpt->writeCell (($sponRec->bInactive ? 'Inactive since '.date($genumDateFormat, $sponRec->dteInactive) : 'Active'))
         .$clsRpt->closeRow  ()

         .$clsRpt->closeReport('<br>');
      return($strOut);
   }

   public function lNumSponsorsViaProgram($lProgramID, $bIncludeInactive, $strQualSponLetter){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strWhereExtra = '';
      if ($lProgramID > 0) {
         $strWhereExtra .= " AND sp_lSponsorProgramID=$lProgramID ";
      }

      if (!$bIncludeInactive){
         $strWhereExtra .= ' AND NOT sp_bInactive ';
      }

      if ($strQualSponLetter.'' != ''){
         $strWhereExtra .= strNameWhereClauseViaLetter('pe_strLName', $strQualSponLetter);
      }

      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM sponsor
            INNER JOIN people_names ON pe_lKeyID=sp_lForeignID
         WHERE 1
            $strWhereExtra
            AND NOT sp_bRetired
            AND NOT sp_bRetired;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
         return((integer)$row->lNumRecs);
      }
   }

   public function removeSponsor($lSponID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        "UPDATE sponsor
         SET
            sp_bRetired=1,
            sp_lLastUpdateID=$glUserID
         WHERE sp_lKeyID=$lSponID;";

      $this->db->query($sqlStr);
   }

   public function addRemoveHonoreeToSponsorship($lSponID, $lHonPID){
   //---------------------------------------------------------------------
   // set $lHonPID to null to remove honoree
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
           'UPDATE sponsor
            SET sp_lHonoreeID = '.strDBValueConvert_INT($lHonPID).",
               sp_lLastUpdateID=$glUserID
            WHERE sp_lKeyID=$lSponID;";
      $this->db->query($sqlStr);
   }

   public function inactivateSponsorship($lSponID, $dteInactive, $lTermTypeID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
           'UPDATE sponsor
            SET sp_bInactive    = 1,
               sp_dteInactive   = '.strPrepDate($dteInactive).",
               sp_lInactiveCatID= $lTermTypeID,
               sp_lLastUpdateID=$glUserID
            WHERE sp_lKeyID=$lSponID;";
      $this->db->query($sqlStr);
   }

   function addClientToSponsor($lSponID, $lClientID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
           "UPDATE sponsor
            SET sp_lClientID    = $lClientID,
               sp_lLastUpdateID = $glUserID
            WHERE sp_lKeyID=$lSponID;";
      $this->db->query($sqlStr);
   }

   public function sponsorsViaClientID($lClientID, $bActiveOnly){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlWhere =
              " AND sp_lClientID=$lClientID "
              .($bActiveOnly ? ' AND NOT sp_bInactive ' : '').' ';
      $this->sponsorInfoGenericViaWhere($sqlWhere, '');
   }



   /*------------------------------------------------------------
                       R E P O R T S
   ------------------------------------------------------------*/

   function lNumRecsSponMonthlyChargeReport(
                                    &$sRpt,
                                    $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sponIncomeViaMonthVars($sRpt, $lYear, $lMonth, $lACOID);
      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      $sqlStr =
        "SELECT spc_lKeyID
         FROM sponsor_charges
         WHERE NOT spc_bRetired
            AND spc_lACOID=$lACOID
            AND MONTH(spc_dteCharge)=$lMonth
            AND YEAR (spc_dteCharge)=$lYear
            AND spc_lSponsorshipID IS NOT NULL
         $strLimit;";
      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strSponChargeMonthReportExport(
                                 &$sRpt,   &$displayData,
                                 $bReport, $lStartRec,   $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         return($this->strSponMonthChargeReport($sRpt,   $displayData,
                                                $lStartRec,   $lRecsPerPage));
      }else {
         return($this->strSponMonthChargeExport($sRpt));
      }
   }


   function strSponMonthChargeReport(&$sRpt, &$displayData,
                                     $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $strOut = '';
      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";

      $this->sponIncomeViaMonthVars($sRpt, $lYear, $lMonth, $lACOID);
      $cACO = new madmin_aco;
      $cACO->loadCountries(false, true, true, $lACOID);

      $cCountry = $cACO->countries[0];
      $strOut =
         '<table class="enpView">
            <tr>
               <td class="enpViewLabel" colspan="2" style="text-align: left; font-size: 13pt;">
                  Sponsorship Charges
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Accounting Country:
               </td>
               <td class="enpView">'
                  .$cCountry->strName.' '.$cCountry->strCurrencySymbol.' '.$cCountry->strFlagImg.'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Month:
               </td>
               <td class="enpView">'
                  .strXlateMonth($lMonth).' '.$lYear.'
               </td>
            </tr>
         </table>';

      $sqlStr =
        "SELECT
            spc_lKeyID, spc_lSponsorshipID,
            spc_dteCharge,

            spc_lAutoGenID, spc_lAutoGenACOID, spc_curAutoGenCommitAmnt,
            spc_curChargeAmnt, spc_lACOID, spc_strNotes,
            sp_lForeignID,
            spon.pe_bBiz AS bSponBiz, spon.pe_strLName AS strSponLName, spon.pe_strFName AS strSponFName
         FROM sponsor_charges
            INNER JOIN sponsor               ON spc_lSponsorshipID=sp_lKeyID
            INNER JOIN people_names AS spon  ON sp_lForeignID=spon.pe_lKeyID

         WHERE NOT spc_bRetired
            AND spc_lACOID=$lACOID
            AND MONTH(spc_dteCharge)=$lMonth
            AND YEAR (spc_dteCharge)=$lYear
            AND spc_lSponsorshipID IS NOT NULL
         ORDER BY spc_dteCharge, spc_lKeyID
         $strLimit;";
      $query = $this->db->query($sqlStr);

      $lNumRows = $query->num_rows();
      if ($lNumRows > 0){
         $strOut .= '
            <table class="enpRptC">
               <tr>
                  <td class="enpRptLabel">
                     Charge ID
                  </td>
                  <td class="enpRptLabel">
                     Sponsor ID
                  </td>
                  <td class="enpRptLabel">
                     Amount
                  </td>
                  <td class="enpRptLabel">
                     Date
                  </td>
                  <td class="enpRptLabel">
                     Autocharge ID
                  </td>
                  <td class="enpRptLabel">
                     People/Biz ID
                  </td>
                  <td class="enpRptLabel">
                     Sponsor
                  </td>
               </tr>';
         foreach ($query->result() as $row){
            $lChargeID      = $row->spc_lKeyID;
            $lSponsorID  = $row->spc_lSponsorshipID;
            $lSponsorFID = $row->sp_lForeignID;

            $lAutoGenID = $row->spc_lAutoGenID;
            if (is_null($lAutoGenID)){
               $strAutoGen = 'n/a';
            }else {
               $strAutoGen =
                   strLinkView_SponsorAutoCharge($lAutoGenID, 'View Auto-Charge Log', true).'&nbsp;'
                  .str_pad($lAutoGenID, 5, '0', STR_PAD_LEFT);
            }

               // chargbe ID
            $strOut .= '
                <tr>
                   <td class="enpRpt" style="text-align: center;">'
                      .strLinkView_SponsorCharge($lChargeID, 'View charge record', true).'&nbsp;'
                      .str_pad($lChargeID, 5, '0', STR_PAD_LEFT).'
                   </td>';

               // sponsor ID
            $strOut .= '
                   <td class="enpRpt" style="text-align: center;">'
                      .strLinkView_Sponsorship($lSponsorID, 'View sponsorship record', true).'&nbsp;'
                      .str_pad($lSponsorID, 5, '0', STR_PAD_LEFT).'
                   </td>';

               // Amount
            $strOut .= '
                   <td class="enpRpt" style="text-align: right; padding-left: 14px;">'
                      .number_format($row->spc_curChargeAmnt, 2).'
                   </td>';

               // Date
            $strOut .= '
                   <td class="enpRpt" style="text-align: right;">'
                      .date($genumDateFormat, dteMySQLDate2Unix($row->spc_dteCharge)).'
                   </td>';

               // Autogen ID
            $strOut .= '
                   <td class="enpRpt" style="text-align: center;">'
                      .$strAutoGen.'
                   </td>';

               // People/Biz ID
            if ($row->bSponBiz){
               $strLink = strLinkView_BizRecord($lSponsorFID, 'View business record', true).'&nbsp;'
                         .str_pad($lSponsorFID, 5, '0', STR_PAD_LEFT);
            }else {
               $strLink = strLinkView_PeopleRecord($lSponsorFID, 'View people record', true).'&nbsp;'
                         .str_pad($lSponsorFID, 5, '0', STR_PAD_LEFT);
            }
            $strOut .= '
                   <td class="enpRpt" style="text-align: center;">'
                      .$strLink.'
                   </td>';

               // Sponsor
            if ($row->bSponBiz){
               $strName = htmlspecialchars($row->strSponLName).' (business)';
            }else {
               $strName = htmlspecialchars($row->strSponLName.', '.$row->strSponFName);
            }
            $strOut .= '
                   <td class="enpRpt" >'
                      .$strName.'
                   </td>';

            $strOut .'
                </tr>';
         }
         $strOut .= '</table>';
      }else {
         $strOut .= '<br><br><i>There are no records that match your search criteria</i><br>';
      }

      return($strOut);
   }


   function lNumRecsSponMonthlyIncomeReport(
                                    &$sRpt,
                                    $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sponIncomeViaMonthVars($sRpt, $lYear, $lMonth, $lACOID);
      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      $sqlStr =
        "SELECT gi_lKeyID
         FROM gifts
         WHERE NOT gi_bRetired
            AND gi_lACOID=$lACOID
            AND MONTH(gi_dteDonation)=$lMonth
            AND YEAR (gi_dteDonation)=$lYear
            AND gi_lSponsorID IS NOT NULL
         $strLimit;";
      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strSponIncomeMonthReportExport(
                                 &$sRpt,   &$displayData,
                                 $bReport, $lStartRec,   $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         return($this->strSponMonthIncomeReport($sRpt,   $displayData,
                                                $lStartRec,   $lRecsPerPage));
      }else {
         return($this->strSponMonthIncomeExport($sRpt));
      }
   }

   function strSponMonthIncomeExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sponIncomeViaMonthVars($sRpt, $lYear, $lMonth, $lACOID);

      $sqlStr =
        'SELECT '.strExportFields_SponsorPayments()."
         FROM gifts
            INNER JOIN people_names AS payer       ON gi_lForeignID          = payer.pe_lKeyID
            INNER JOIN sponsor                     ON gi_lSponsorID          = sp_lKeyID
            INNER JOIN people_names AS spon        ON sp_lForeignID          = spon.pe_lKeyID
            INNER JOIN admin_aco                   ON gi_lACOID              = aco_lKeyID
            INNER JOIN lists_sponsorship_programs  ON sc_lKeyID              = sp_lSponsorProgramID

            LEFT  JOIN client_records              ON cr_lKeyID              = sp_lClientID
            LEFT  JOIN client_location             ON cr_lLocationID         = cl_lKeyID
            LEFT  JOIN lists_generic AS giftAttrib ON giftAttrib.lgen_lKeyID = gi_lAttributedTo
            LEFT  JOIN lists_generic AS payCat     ON payCat.lgen_lKeyID     = gi_lPaymentType

         WHERE NOT gi_bRetired
            AND gi_lACOID=$lACOID
            AND MONTH(gi_dteDonation)=$lMonth
            AND YEAR (gi_dteDonation)=$lYear
            AND gi_lSponsorID IS NOT NULL
         ORDER BY gi_lKeyID;";
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   function strSponMonthIncomeReport(&$sRpt, &$displayData,
                                     $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $strOut = '';
      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";

      $this->sponIncomeViaMonthVars($sRpt, $lYear, $lMonth, $lACOID);
      $cACO = new madmin_aco;
      $cACO->loadCountries(false, true, true, $lACOID);

      $cCountry = $cACO->countries[0];

      $strOut =
         '<table class="enpView">
            <tr>
               <td class="enpViewLabel" colspan="2" style="text-align: left; font-size: 13pt;">
                  Sponsorship Income
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Accounting Country:
               </td>
               <td class="enpView">'
                  .$cCountry->strName.' '.$cCountry->strCurrencySymbol.' '.$cCountry->strFlagImg.'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Month:
               </td>
               <td class="enpView">'
                  .strXlateMonth($lMonth).' '.$lYear.'
               </td>
            </tr>
         </table>';

      $sqlStr =
        "SELECT
            gi_lKeyID, gi_lSponsorID,
            gi_dteDonation,
            gi_curAmnt, gi_lForeignID, sp_lForeignID,
            payer.pe_bBiz AS bPayerBiz, payer.pe_strLName AS strPayerLName, payer.pe_strFName AS strPayerFName,
            payer.pe_strAddr1, payer.pe_strAddr2, payer.pe_strCity, payer.pe_strState, payer.pe_strZip, payer.pe_strCountry,
            spon.pe_bBiz AS bSponBiz, spon.pe_strLName AS strSponLName, spon.pe_strFName AS strSponFName
         FROM gifts
            INNER JOIN people_names AS payer ON gi_lForeignID=payer.pe_lKeyID
            INNER JOIN sponsor               ON gi_lSponsorID=sp_lKeyID
            INNER JOIN people_names AS spon  ON sp_lForeignID=spon.pe_lKeyID

         WHERE NOT gi_bRetired
            AND gi_lACOID=$lACOID
            AND MONTH(gi_dteDonation)=$lMonth
            AND YEAR (gi_dteDonation)=$lYear
            AND gi_lSponsorID IS NOT NULL
         ORDER BY gi_dteDonation, gi_lKeyID
         $strLimit;";
      $query = $this->db->query($sqlStr);

      $lNumRows = $query->num_rows();
      if ($lNumRows > 0){
         $strOut .= '
            <table class="enpRptC">
               <tr>
                  <td class="enpRptLabel">
                     Payment ID
                  </td>
                  <td class="enpRptLabel">
                     Sponsor ID
                  </td>
                  <td class="enpRptLabel">
                     Amount
                  </td>
                  <td class="enpRptLabel">
                     Date
                  </td>
                  <td class="enpRptLabel">
                     Payer
                  </td>
                  <td class="enpRptLabel">
                     Sponsor
                  </td>
               </tr>';
         foreach ($query->result() as $row){
            $lPayID      = $row->gi_lKeyID;
            $lSponsorID  = $row->gi_lSponsorID;
            $lSponsorFID = $row->sp_lForeignID;
            $lPayerID    = $row->gi_lForeignID;

               // gift ID
            $strOut .= '
                <tr>
                   <td class="enpRpt" style="text-align: center;">'
                      .strLinkView_SponsorPayment($lPayID, 'View payment record', true).'&nbsp;'
                      .str_pad($lPayID, 5, '0', STR_PAD_LEFT).'
                   </td>';

               // sponsor ID
            $strOut .= '
                   <td class="enpRpt" style="text-align: center;">'
                      .strLinkView_Sponsorship($lSponsorID, 'View sponsorship record', true).'&nbsp;'
                      .str_pad($lSponsorID, 5, '0', STR_PAD_LEFT).'
                   </td>';

               // Amount
            $strOut .= '
                   <td class="enpRpt" style="text-align: right; padding-left: 14px;">'
                      .number_format($row->gi_curAmnt, 2).'
                   </td>';

               // Date
            $strOut .= '
                   <td class="enpRpt" style="text-align: right;">'
                      .date($genumDateFormat, dteMySQLDate2Unix($row->gi_dteDonation)).'
                   </td>';

               // Payer
            if ($row->bPayerBiz){
               $strName = strLinkView_BizRecord($lPayerID, 'View business record', true).'&nbsp;'
                         .str_pad($lPayerID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                         .htmlspecialchars($row->strPayerLName).' (business)';
            }else {
               $strName = strLinkView_PeopleRecord($lPayerID, 'View people record', true).'&nbsp;'
                         .str_pad($lPayerID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                         .htmlspecialchars($row->strPayerLName.', '.$row->strPayerFName);
            }
            $strOut .= '
                   <td class="enpRpt" >'
                      .$strName.'
                   </td>';

               // Sponsor
            if ($lPayerID==$lSponsorFID){
               $strName = '<span style="color: #999; font-style: italic;">(same as payer)';
            }else {
               if ($row->bSponBiz){
                  $strName = strLinkView_BizRecord($lSponsorFID, 'View business record', true).'&nbsp;'
                            .str_pad($lSponsorFID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                            .htmlspecialchars($row->strSponLName).' (business)';
               }else {
                  $strName = strLinkView_PeopleRecord($lSponsorFID, 'View people record', true).'&nbsp;'
                            .str_pad($lSponsorFID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                            .htmlspecialchars($row->strSponLName.', '.$row->strSponFName);
               }
            }
            $strOut .= '
                   <td class="enpRpt" >'
                      .$strName.'
                   </td>';

            $strOut .'
                </tr>';
         }
         $strOut .= '</table>';
      }else {
         $strOut .= '<br><br><i>There are no records that match your search criteria</i><br>';
      }

      return($strOut);
   }

   private function sponIncomeViaMonthVars(&$sRpt, &$lYear, &$lMonth, &$lACOIC){
      $lYear  = $sRpt->lYear;
      $lMonth = $sRpt->lMonth;
      $lACOIC = $sRpt->lACOID;
   }

   function lNumRecsInSponWOClientReport(
                           &$sRpt,
                           $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      $sqlStr =
        "SELECT sp_lKeyID
         FROM sponsor
         WHERE
                NOT sp_bInactive
            AND sp_lClientID IS NULL
            AND NOT sp_bRetired
         $strLimit;";

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strSponWOClientReportExport(&$sRpt,   &$displayData,
                                        $bReport, $lStartRec,   $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         $this->strSponWOClientReport($sRpt,      $displayData,
                                      $lStartRec, $lRecsPerPage);
      }else {
         return($this->strSponWOClientExport($sRpt));
      }
   }

   function strSponWOClientExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
           'SELECT '.strExportFields_Sponsor()."
            FROM sponsor
               INNER JOIN people_names AS sponpeep   ON sponpeep.pe_lKeyID   = sp_lForeignID
               INNER JOIN admin_aco AS commitACO     ON commitACO.aco_lKeyID = sp_lCommitmentACO
               INNER JOIN lists_sponsorship_programs ON sc_lKeyID            = sp_lSponsorProgramID
               LEFT  JOIN client_records             ON cr_lKeyID            = sp_lClientID
               LEFT  JOIN client_location            ON cr_lLocationID       = cl_lKeyID
               LEFT  JOIN lists_client_vocab         ON cv_lKeyID            = cr_lVocID
               LEFT  JOIN people_names AS honpeep    ON honpeep.pe_lKeyID    = sp_lHonoreeID
               LEFT  JOIN lists_generic AS atab      ON sp_lAttributedTo     = lgen_lKeyID
            WHERE NOT sp_bRetired
               AND sp_lClientID IS NULL AND (NOT sp_bInactive)
            ORDER BY sp_lKeyID;";
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   function strSponWOClientReport(&$sRpt,     &$displayData,
                                  $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData['strRptTitle'] = 'Sponsors Without Clients';

      $displayData['showFields'] = new stdClass;
      $displayData['showFields']->bSponsorID    = true;
      $displayData['showFields']->bName         = true;
      $displayData['showFields']->bSponsorInfo  = true;
      $displayData['showFields']->bClient       = true;
      $displayData['showFields']->bLocation     = false;
      $displayData['showFields']->bSponAddr     = true;

      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";

      $strWhere = " AND sp_lClientID IS NULL AND (NOT sp_bInactive) ";
      $this->sponsorInfoGenericViaWhere($strWhere, $strLimit);
      $displayData['sponInfo'] = &$this->sponInfo;
      $displayData['lNumSpon'] = $this->lNumSponsors;
   }


   function lNumRecsInSponViaLocReport(
                           &$sRpt,
                           $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lLocID        = $sRpt->lLocID;
      $bShowInactive = $sRpt->bShowInactive;
      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      $sqlStr =
        "SELECT sp_lKeyID
         FROM sponsor
            INNER JOIN client_records ON sp_lClientID=cr_lKeyID
         WHERE
            NOT sp_bRetired
            AND cr_lLocationID=$lLocID "
            .($bShowInactive ? '' : ' AND NOT sp_bInactive ')."
         $strLimit;";

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strSponViaLocReportExport(&$sRpt,   &$displayData,
                                      $bReport, $lStartRec,   $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         $this->strSponViaLocReport($sRpt,      $displayData,
                                    $lStartRec, $lRecsPerPage);
      }else {
         return($this->strSponViaLocExport($sRpt));
      }
   }

   function strSponViaLocReport(&$sRpt,     &$displayData,
                                $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lLocID = $sRpt->lLocID;
      $bShowInactive = $sRpt->bShowInactive;

      $cLoc = new mclient_locations;
      $cLoc->loadLocationRec($lLocID);
      $displayData['strRptTitle'] = 'Sponsors of the <b>"'
                       .htmlspecialchars($cLoc->strLocation.' / '.$cLoc->strCountry).'"</b> location';

      $displayData['showFields'] = new stdClass;
      $displayData['showFields']->bSponsorID    = true;
      $displayData['showFields']->bName         = true;
      $displayData['showFields']->bSponsorInfo  = true;
      $displayData['showFields']->bClient       = true;
      $displayData['showFields']->bLocation     = true;
      $displayData['showFields']->bSponAddr     = true;

      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";

      $strWhere = " AND (cr_lLocationID=$lLocID) ".($bShowInactive ? '' : ' AND (NOT sp_bInactive) ');
      $this->sponsorInfoGenericViaWhere($strWhere, $strLimit);
      $displayData['sponInfo'] = &$this->sponInfo;
      $displayData['lNumSpon'] = $this->lNumSponsors;
   }

   function strSponViaLocExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lLocID = $sRpt->lLocID;
      $bShowInactive = $sRpt->bShowInactive;

      $sqlStr =
           'SELECT '.strExportFields_Sponsor()."
            FROM sponsor
               INNER JOIN people_names AS sponpeep   ON sponpeep.pe_lKeyID   = sp_lForeignID
               INNER JOIN admin_aco AS commitACO     ON commitACO.aco_lKeyID = sp_lCommitmentACO
               INNER JOIN lists_sponsorship_programs ON sc_lKeyID            = sp_lSponsorProgramID
               LEFT  JOIN client_records             ON cr_lKeyID            = sp_lClientID
               LEFT  JOIN client_location            ON cr_lLocationID       = cl_lKeyID
               LEFT  JOIN lists_client_vocab         ON cv_lKeyID            = cr_lVocID
               LEFT  JOIN people_names AS honpeep    ON honpeep.pe_lKeyID    = sp_lHonoreeID
               LEFT  JOIN lists_generic AS atab      ON sp_lAttributedTo     = lgen_lKeyID
            WHERE NOT sp_bRetired
                AND (cr_lLocationID=$lLocID)  "
               .($bShowInactive ? '' : ' AND NOT sp_bInactive ')."
            ORDER BY sp_lKeyID;";
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }





}

?>