<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2011-2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('donations/mdonations', 'clsGifts');
---------------------------------------------------------------------
   __construct                   ()
   bGiftViaPerson                ($lGiftID)
   loadGiftViaGID                ($lGiftID)
   loadGifts                     ()
   cumulativeDonation            ($clsACO)
   curCumulativeDonationViaACOID ($lACOID)
   curTotalSoftDonations         ($lACOID)
   loadGiftHistory               ($lPID, $enumSortType, $lACOID)
   updateGiftRecord              ()
   addNewGiftRecord              ()
   retireGiftsViaPID             ($lPID, $lGroupID)
   retireSingleGift              ($lGiftID, &$lGroupID)
   logGiftRetire                 ($lGiftID, &$lGroupID)
   giftHTMLSummary               ()
   loadGiftListViaACO            (&$lNumGifts,   &$gifts,  ...
   lNumTotGiftsInList            ()
---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class Mdonations extends CI_Model{

   public $lGiftID, $lPeopleID, $lBizID,
       $bUseDateRange, $dteStartDate, $dteEndDate;

   public
      $lNumGiftsGH, $giftHistory, $giftInfo,
      $lAcctID, $lCampID;

   public
      $lNumCumulative, $cumulative, $cumulativeOpts;

   public
      $lNumGiftLists, $giftLists, $lListsAcctID, $lListsCampID, $strGLSortClause;

   public $gifts, $lNumGifts, $sqlExtraWhere, $sqlExtraSort, $sqlLimit;
   public $bDebug;


   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->lPeopleID          = $this->lBizID       =
      $this->lGiftID            =
      $this->dteStartDate       = $this->dteEndDate   =
      $this->lNumGiftsGH        = $this->giftHistory  =
      $this->giftInfo           = null;

      $this->bUseDateRange = $this->bDebug = false;

      $this->lNumCumulative = $this->cumulative = $this->cumulativeOpts = null;

      $this->lAcctID = $this->lCampID = null;

      $this->lNumGiftLists = $this->giftLists = $this->lListsAcctID = $this->lListsCampID = null;

      $this->gifts = null;
      $this->lNumGifts = 0;
      $this->sqlExtraWhere = $this->sqlExtraSort = $this->strGLSortClause = $this->sqlLimit = '';

   }

   function bGiftViaPerson($lGiftID){
   //---------------------------------------------------------------------
   // return true for people gift, false for biz gift
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT pe_bBiz
         FROM gifts
            INNER JOIN people_names ON gi_lForeignID=pe_lKeyID
         WHERE gi_lKeyID=$lGiftID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0) screamForHelp('Unexpected EOF, giftID: '.$lGiftID, true);
      $row = $query->row();
      return(!((boolean)$row->pe_bBiz));
   }

   function loadGiftViaGID($lGiftID, $strSort=''){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (is_array($lGiftID)){
         $this->sqlExtraWhere = " AND gi_lKeyID IN (".implode(',', $lGiftID).") ";
      }else {
         $this->sqlExtraWhere = " AND gi_lKeyID=$lGiftID ";
      }
      if ($strSort==''){
         $strSort = ' ORDER BY gi_lKeyID ';
      }
      $this->sqlExtraSort = $strSort;
      $this->loadGifts();
   }

   public function strGiftSQL(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      return(
        "SELECT
            gi_lKeyID, gi_lForeignID, gi_lSponsorID, gi_curAmnt,
            gi_dteDonation,
            gi_bHon, gi_bMem, gi_lACOID, gi_strNotes,
            gi_bAck, gi_dteAck,
            gi_strCheckNum, gi_bGIK, gi_strImportID,
            gi_lGIK_ID,       listInKind.lgen_strListItem       AS strGIK,
            gi_lPaymentType,  listPayType.lgen_strListItem      AS strPaymentType,
            gi_lMajorGiftCat, listMajorGiftCat.lgen_strListItem AS strMajorGiftCat,
            gi_lAttributedTo, listAttrib.lgen_strListItem       AS strAttribTo,
            gi_lPledgeID,
            pe_lKeyID, pe_bBiz, pe_strFName, pe_strLName,
            gc_lKeyID, gc_strCampaign,
            ga_lKeyID, ga_strAccount,

            gi_lDepositLogID, dl_strBank, dl_strAccount,
            dl_dteOrigin AS mdteDeposit,
            aco_strFlag, aco_strCurrencySymbol, aco_strName,

            usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
            usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,
            UNIX_TIMESTAMP(gi_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(gi_dteLastUpdate) AS dteLastUpdate

         FROM gifts
            INNER JOIN gifts_campaigns ON gi_lCampID = gc_lKeyID
            INNER JOIN gifts_accounts  ON gc_lAcctID = ga_lKeyID
            INNER JOIN people_names    ON pe_lKeyID  = gi_lForeignID
            INNER JOIN admin_aco       ON gi_lACOID  = aco_lKeyID

            INNER JOIN admin_users AS usersC ON gi_lOriginID    = usersC.us_lKeyID
            INNER JOIN admin_users AS usersL ON gi_lLastUpdateID= usersL.us_lKeyID

            LEFT  JOIN deposit_log                       ON gi_lDepositLogID = dl_lKeyID
            LEFT  JOIN lists_generic AS listInKind       ON gi_lGIK_ID       = listInKind.lgen_lKeyID
            LEFT  JOIN lists_generic AS listPayType      ON gi_lPaymentType  = listPayType.lgen_lKeyID
            LEFT  JOIN lists_generic AS listMajorGiftCat ON gi_lMajorGiftCat = listMajorGiftCat.lgen_lKeyID
            LEFT  JOIN lists_generic AS listAttrib       ON gi_lAttributedTo = listAttrib.lgen_lKeyID
         WHERE NOT gi_bRetired
            $this->sqlExtraWhere
         $this->sqlExtraSort
         $this->sqlLimit;");
   }

   public function loadGifts() {
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $clsACO = new madmin_aco();
      $this->gifts = array();

      $sqlStr = $this->strGiftSQL();

      $query = $this->db->query($sqlStr);

      $this->lNumGifts = $query->num_rows();
      if ($this->lNumGifts == 0) {
         $this->gifts[0] = new stdClass;
         $gift = &$this->gifts[0];

         $gift->gi_lKeyID        =
         $gift->gi_lForeignID    =
         $gift->gi_lSponsorID    =
         $gift->gi_curAmnt       =
         $gift->gi_dteDonation   =
         $gift->mdteDonation     =
         $gift->lDepositLogID    =

         $gift->gi_lAttributedTo =
         $gift->strAttribTo      =
         $gift->strNotes         =
         $gift->strImportID      =
         $gift->lPledgeID        =

         $gift->bAck             =
         $gift->dteAck           =

         $gift->gi_strCheckNum   =
         $gift->gi_lPaymentType  =
         $gift->strPaymentType   =

         $gift->pe_lKeyID        =
         $gift->pe_bBiz          =
         $gift->pe_strFName      =
         $gift->pe_strLName      =
         $gift->gc_lKeyID        =
         $gift->gc_strCampaign   =
         $gift->ga_lKeyID        =
         $gift->ga_strAccount    =

         $gift->gi_bGIK          =
         $gift->gi_lGIK_ID       =
         $gift->strInKind        =

         $gift->gi_lMajorGiftCat =
         $gift->strMajorGiftCat  =

         $gift->lACOID           =
         $gift->strFlagImg       =
         $gift->strACOCurSymbol  =
         $gift->strACOCountry    = null;

         $gift->dteOrigin        =
         $gift->dteLastUpdate    =

         $gift->strStaffCFName   =
         $gift->strStaffCLName   =
         $gift->strStaffLFName   =
         $gift->strStaffLLName   = null;

      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->gifts[$idx] = new stdClass;
            $gift = &$this->gifts[$idx];

            $gift->gi_lKeyID         = (int)$row->gi_lKeyID;
            $gift->gi_lForeignID     = (int)$row->gi_lForeignID;
            $gift->gi_lSponsorID     = $row->gi_lSponsorID;
            $gift->gi_curAmnt        = (float)$row->gi_curAmnt;
//            $gift->gi_dteDonation    = (int)$row->dteDonation;
            $gift->mdteDonation      = $row->gi_dteDonation;
            $gift->gi_dteDonation    = dteMySQLDate2Unix($gift->mdteDonation);

            $gift->gi_lAttributedTo  = $row->gi_lAttributedTo;
            $gift->strAttribTo       = $row->strAttribTo;
            $gift->strNotes          = $row->gi_strNotes;
            $gift->strImportID       = $row->gi_strImportID;
            $gift->lPledgeID         = $row->gi_lPledgeID;

            $gift->gi_strCheckNum    = $row->gi_strCheckNum;
            $gift->gi_lPaymentType   = $row->gi_lPaymentType;
            $gift->strPaymentType    = $row->strPaymentType;

            $gift->lDepositLogID     = $row->gi_lDepositLogID;
            $gift->strDepositBank    = $row->dl_strBank;
            $gift->strDepositAccount = $row->dl_strAccount;
            $gift->dteDeposit        = dteMySQLDate2Unix($row->mdteDeposit);

            $gift->bAck              = $row->gi_bAck;
            $gift->dteAck            = dteMySQLDate2Unix($row->gi_dteAck);          // $row->dteAck;

            $gift->pe_lKeyID         = $row->pe_lKeyID;
            $gift->pe_bBiz           = $row->pe_bBiz;
            $gift->pe_strFName       = $row->pe_strFName;
            $gift->pe_strLName       = $row->pe_strLName;
            if ($gift->pe_bBiz){
               $gift->strSafeName    = $gift->strSafeNameLF = htmlspecialchars($row->pe_strLName);
            }else {
               $gift->strSafeName    = htmlspecialchars($row->pe_strFName.' ' .$row->pe_strLName);
               $gift->strSafeNameLF  = htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName);
            }

            $gift->gc_lKeyID         = $row->gc_lKeyID;
            $gift->gc_strCampaign    = $row->gc_strCampaign;
            $gift->ga_lKeyID         = $row->ga_lKeyID;
            $gift->ga_strAccount     = $row->ga_strAccount;

            $gift->gi_bGIK           = $row->gi_bGIK;
            $gift->gi_lGIK_ID        = $row->gi_lGIK_ID;
            $gift->strInKind         = $row->strGIK;

            $gift->gi_lMajorGiftCat  = $row->gi_lMajorGiftCat;
            $gift->strMajorGiftCat   = $row->strMajorGiftCat;

            $gift->lACOID            = $row->gi_lACOID;
            $gift->strFlagImg        = $clsACO->strFlagImage($row->aco_strFlag, $row->aco_strName);
            $gift->strACOCurSymbol   = $row->aco_strCurrencySymbol;
            $gift->strACOCountry     = $row->aco_strName;
            $gift->strFormattedAmnt  =
                                   $gift->strACOCurSymbol.' '
                                  .number_format($gift->gi_curAmnt, 2).' '
                                  .$gift->strFlagImg;

            $gift->dteOrigin         = $row->dteOrigin;
            $gift->dteLastUpdate     = $row->dteLastUpdate;

            $gift->strStaffCFName    = $row->strCFName;
            $gift->strStaffCLName    = $row->strCLName;
            $gift->strStaffLFName    = $row->strLFName;
            $gift->strStaffLLName    = $row->strLLName;

            ++$idx;
         }
      }
   }

   public function cumulativeDonation($clsACO, &$lTotGifts){
   //---------------------------------------------------------------------
   // if $bAll, combine in-kind and monitary, else use $bMonetary flag
   //---------------------------------------------------------------------
      if (is_null($this->cumulativeOpts)) screamForHelp('UNITIALIZED CLASS<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      $clsACO->loadCountries(true, false, false, null);
      $lTotGifts = 0;

      $this->lNumCumulative = 0;
      $this->cumulative = array();
      foreach($clsACO->countries as $clsCountry){
         $lACOID = $clsCountry->lKeyID;
         if ($this->cumulativeOpts->bSoft){
            $curCum = $this->curTotalSoftDonations($lACOID, $lNumGifts);
            $lTotGifts += $lNumGifts;
         }else {
            $curCum = $this->curCumulativeDonationViaACOID($lACOID, $lNumGifts);
            $lTotGifts += $lNumGifts;
         }
         if ($curCum != 0.0){
            $this->cumulative[$this->lNumCumulative] = new stdClass;
            $objC = $this->cumulative[$this->lNumCumulative];
            $objC->curCumulative = $curCum;
            $objC->lACOID        = $lACOID;
            $objC->strFlagImg    = $clsCountry->strFlagImg;
            $objC->strCurSymbol  = $clsCountry->strCurrencySymbol;

            ++$this->lNumCumulative;
         }
      }
   }

   public function curCumulativeDonationViaACOID($lACOID, &$lNumGifts) {
   //---------------------------------------------------------------------
   // caller needs to set:
   //   $this->lPeopleID
   //   $this->bUseDateRange
   //   $this->dteStartDate
   //   $this->dteEndDate
   //
   // $this->cumulativeOpts->enumCumulativeSource:
   //        biz, people, account, campaign, sponsorPayments
   //
   // $this->cumulativeOpts->enumMoneySet:
   //        all, monetaryOnly, gikOnly
   //
   //
   //
   //---------------------------------------------------------------------
      $lNumGifts = 0;

      switch ($this->cumulativeOpts->enumCumulativeSource){
         case 'people':
         case 'biz':
            $lForeignID = $this->lPeopleID;
            if (is_null($lForeignID)) screamForHelp('FOREIGN KEY NOT SET<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
            $strWhereFID = " AND (gi_lForeignID=$lForeignID) ";
            $strInner    = '';
            break;

         case 'account':
            if (is_null($this->lAcctID)) screamForHelp('ACCOUNT ID NOT SET<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
            $strInner    = ' INNER JOIN gifts_campaigns ON gi_lCampID=gc_lKeyID ';
            $strWhereFID = " AND (gc_lAcctID=$this->lAcctID) ";
            break;

         case 'campaign':
            if (is_null($this->lCampID)) screamForHelp('CAMPAIGN ID NOT SET<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
            $strInner    = '';
            $strWhereFID = " AND (gi_lCampID=$this->lCampID) ";
            break;

         case 'sponsorPayments':
            $lForeignID = $this->lPeopleID;
            if (is_null($lForeignID)) screamForHelp('FOREIGN KEY NOT SET<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
            $strWhereFID = " AND (gi_lForeignID=$lForeignID) AND (gi_lSponsorID IS NOT NULL) ";
            $strInner    = '';
            break;

         default:
            screamForHelp($this->cumulativeOpts->enumCumulativeSource.': Invalid switch type<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
            break;
      }

      $strWhereDate = $this->strSetDateClauseSQL();
      $strWhereMon  = $this->strMoneyGIKSQL($this->cumulativeOpts->enumMoneySet);

      $sqlStr =
           "SELECT SUM(gi_curAmnt) AS curTotGift, COUNT(*) AS lNumRecs
            FROM gifts
               $strInner
            WHERE NOT gi_bRetired
               $strWhereDate
               $strWhereMon
               $strWhereFID
               AND gi_lACOID=$lACOID;";

      $query = $this->db->query($sqlStr);
      $lNumRecs = $query->num_rows();
      $curTotal = 0.0;
      if ($lNumRecs > 0){
         $row = $query->row();
         $vHold = $row->curTotGift;
         if (!is_null($vHold)){
            $curTotal   = (float)$vHold;
            $lNumGifts  = (int)$row->lNumRecs;
         }
      }
      return($curTotal);
   }

   private function strSetDateClauseSQL(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($this->bUseDateRange) {
         $strWhereDate =
             ' AND (gi_dteDonation BETWEEN '.strPrepDate($this->dteStartDate).'
                                       AND '.strPrepDateTime($this->dteEndDate).') ';
      }else {
         $strWhereDate = '';
      }
      return($strWhereDate);
   }

   private function strMoneyGIKSQL($enumMoneySet){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumMoneySet){
         case 'all':
            $strSqlMon = '';
            break;
         case 'monetaryOnly':
            $strSqlMon = ' AND NOT gi_bGIK ';
            break;
         case 'gikOnly':
            $strSqlMon = ' AND gi_bGIK ';
            break;
         default:
            screamForHelp($this->cumulativeOpts->enumMoneySet.': invalid switch setting<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
            break;
      }
      return($strSqlMon);
   }

   public function curTotalSoftDonations($lACOID, &$lNumGifts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $curSoftTot = 0.0;
      $lNumGifts  = 0;
      $strWhereDate = $this->strSetDateClauseSQL();
      $strWhereMon  = $this->strMoneyGIKSQL($this->cumulativeOpts->enumMoneySet);

      if (! ($this->cumulativeOpts->enumCumulativeSource=='biz' ||
             $this->cumulativeOpts->enumCumulativeSource=='people')){
         screamForHelp('Soft donations only apply to people/biz<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      }
      $bBiz = $this->cumulativeOpts->enumCumulativeSource=='biz';

         //-------------------------------------------------
         // people-to-people soft cash relationships
         //-------------------------------------------------
      if (!$bBiz){
         $sqlStr =
           "SELECT SUM(gi_curAmnt) AS curSumSoft, COUNT(*) AS lNumRecs
            FROM gifts
               INNER JOIN people_relationships ON pr_lPerson_A_ID=gi_lForeignID
            WHERE pr_lPerson_B_ID=$this->lPeopleID
               AND pr_bSoftDonations
               AND gi_lACOID=$lACOID
               $strWhereDate
               $strWhereMon
               AND NOT gi_bRetired;";
         $query = $this->db->query($sqlStr);
         $lNumRecs = $query->num_rows();
         if ($lNumRecs > 0){
            $row = $query->row();
            $vHold = $row->curSumSoft;
            if (!is_null($vHold)){
               $curSoftTot += (float)$vHold;
               $lNumGifts  += (int)$row->lNumRecs;
            }
         }
      }

         //-------------------------------------------------
         // people-to-business soft cash relationships
         //-------------------------------------------------
      if (!$bBiz){
         $sqlStr =
           "SELECT SUM(gi_curAmnt) AS curSumSoft, COUNT(*) AS lNumRecs
            FROM gifts
               INNER JOIN biz_contacts ON bc_lBizID=gi_lForeignID
            WHERE bc_lContactID=$this->lPeopleID
               AND bc_bSoftCash
               AND gi_lACOID=$lACOID
               $strWhereDate
               $strWhereMon
               AND NOT bc_bRetired
               AND NOT gi_bRetired;";
         $query = $this->db->query($sqlStr);
         $lNumRecs = $query->num_rows();
         if ($lNumRecs > 0){
            $row = $query->row();
            $vHold = $row->curSumSoft;
            if (!is_null($vHold)){
               $curSoftTot += (float)$vHold;
               $lNumGifts  += (int)$row->lNumRecs;
            }
         }
      }

         //-------------------------------------------------
         // business-to-people soft cash relationships
         //-------------------------------------------------
      if ($bBiz){
         $sqlStr =
           "SELECT SUM(gi_curAmnt) AS curSumSoft, COUNT(*) AS lNumRecs
            FROM gifts
               INNER JOIN biz_contacts ON bc_lContactID=gi_lForeignID
            WHERE bc_lBizID=$this->lPeopleID
               AND bc_bSoftCash
               AND gi_lACOID=$lACOID
               $strWhereDate
               $strWhereMon
               AND NOT bc_bRetired
               AND NOT gi_bRetired;";
         $query = $this->db->query($sqlStr);
         $lNumRecs = $query->num_rows();
         if ($lNumRecs > 0){
            $row = $query->row();
            $vHold = $row->curSumSoft;
            if (!is_null($vHold)){
               $curSoftTot += (float)$vHold;
               $lNumGifts  += (int)$row->lNumRecs;
            }
         }
      }
      return($curSoftTot);
   }

   public function loadGiftHistory(
                         $lPID,    $enumSortType, $lACOID,
                         &$clsACO, &$lNumGiftsGH, &$giftHistory){
   //---------------------------------------------------------------------
   // $enumSortType - date, giftID, name, amount, acctCamp
   //---------------------------------------------------------------------
      $cPeople = new mpeople;
      $bBiz = $cPeople->bBizRec($lPID);

      $clsHonMem = new mhon_mem;

      $clsACO->loadCountries(false, false, true, $lACOID);
      $strFlag      = $clsACO->countries[0]->strFlagImg;
      $strCurSymbol = $clsACO->countries[0]->strCurrencySymbol;
      $strCountry   = $clsACO->countries[0]->strName;

      $strTableName = 'tmpGift';
      $this->createTempGiftTable($strTableName);

         //-------------------------------------------
         // straight donations/sponsorship payments
         //-------------------------------------------
      $sqlStr =
         "INSERT INTO $strTableName (tgi_lGiftID, tgi_lPID)
             SELECT gi_lKeyID, gi_lForeignID
             FROM gifts
             WHERE gi_lForeignID=$lPID
                AND gi_lACOID=$lACOID
                AND NOT gi_bRetired;";
      $this->db->query($sqlStr);

         //-------------------------------------------
         // soft donations/sponsorship payments
         //-------------------------------------------
      $sqlStr =
         "INSERT INTO $strTableName (tgi_lGiftID, tgi_lPID)
             SELECT gi_lKeyID, gi_lForeignID
             FROM gifts
                INNER JOIN people_relationships ON pr_lPerson_A_ID=gi_lForeignID
             WHERE pr_lPerson_B_ID=$lPID
                AND gi_lACOID=$lACOID
                AND pr_bSoftDonations
                AND NOT gi_bRetired;";
      $this->db->query($sqlStr);

         //-------------------------------------------
         // people-to-business soft cash relationships
         //-------------------------------------------
      if ($bBiz){
         $sqlStr =
            "INSERT INTO $strTableName (tgi_lGiftID, tgi_lPID)
               SELECT gi_lKeyID, gi_lForeignID
               FROM gifts
               INNER JOIN biz_contacts ON bc_lContactID=gi_lForeignID
               WHERE bc_lBizID=$lPID
                  AND bc_bSoftCash
                  AND gi_lACOID=$lACOID
                  AND NOT bc_bRetired
                  AND NOT gi_bRetired;";
      }else {
         $sqlStr =
            "INSERT INTO $strTableName (tgi_lGiftID, tgi_lPID)
               SELECT gi_lKeyID, gi_lForeignID
               FROM gifts
                  INNER JOIN biz_contacts ON bc_lBizID=gi_lForeignID
               WHERE bc_lContactID=$lPID
                  AND bc_bSoftCash
                  AND gi_lACOID=$lACOID
                  AND NOT bc_bRetired
                  AND NOT gi_bRetired;";
      }

      $this->db->query($sqlStr);

      if ($this->bDebug) $this->dumpTempTable($strTableName);

         //-------------------------
         // set the sort order
         //-------------------------
      $strSort = $this->strSetSortOrder($enumSortType);

      $sqlStr =
        "SELECT
            gi_lKeyID, gi_lForeignID, gi_lSponsorID,
            gi_curAmnt, gi_dteDonation, 
            gi_bHon, gi_bMem, gi_lACOID,
            pe_lKeyID, pe_bBiz, pe_strFName, pe_strLName,
            gc_lKeyID, gc_strCampaign, gi_bGIK, lgen_strListItem AS strGIK,
            ga_lKeyID, ga_strAccount

         FROM $strTableName
            INNER JOIN gifts           ON gi_lKeyID  = tgi_lGiftID
            INNER JOIN gifts_campaigns ON gi_lCampID = gc_lKeyID
            INNER JOIN gifts_accounts  ON gc_lAcctID = ga_lKeyID
            INNER JOIN people_names    ON pe_lKeyID  = gi_lForeignID
            LEFT  JOIN lists_generic   ON gi_lGIK_ID = lgen_lKeyID

         ORDER BY $strSort;";
      $query = $this->db->query($sqlStr);

      $lNumGiftsGH = $numRows = $query->num_rows();
      $giftHistory = array();
      if ($lNumGiftsGH > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $giftHistory[$idx] = new stdClass;

            $giftHistory[$idx]->gi_lKeyID       = $lGiftID = $row->gi_lKeyID;
            $giftHistory[$idx]->gi_lForeignID   = $row->gi_lForeignID;
            $giftHistory[$idx]->gi_lSponsorID   = $row->gi_lSponsorID;
            $giftHistory[$idx]->gi_curAmnt      = $row->gi_curAmnt;
            $giftHistory[$idx]->gi_dteDonation  = dteMySQLDate2Unix($row->gi_dteDonation);
            $giftHistory[$idx]->gi_bGIK         = $row->gi_bGIK;
            $giftHistory[$idx]->strGIK          = $row->strGIK;
            $giftHistory[$idx]->pe_lKeyID       = $row->pe_lKeyID;
            $giftHistory[$idx]->pe_bBiz         = $row->pe_bBiz;
            $giftHistory[$idx]->pe_strFName     = $row->pe_strFName;
            $giftHistory[$idx]->pe_strLName     = $row->pe_strLName;
            $giftHistory[$idx]->gc_lKeyID       = $row->gc_lKeyID;
            $giftHistory[$idx]->gc_strCampaign  = $row->gc_strCampaign;
            $giftHistory[$idx]->ga_lKeyID       = $row->ga_lKeyID;
            $giftHistory[$idx]->ga_strAccount   = $row->ga_strAccount;

            $giftHistory[$idx]->bHon = ($clsHonMem->lNumHonViaGID($lGiftID) > 0);
            $giftHistory[$idx]->bMem = ($clsHonMem->lNumMemViaGID($lGiftID) > 0);

            $giftHistory[$idx]->lACOID          = $lACOID;
            $giftHistory[$idx]->strACOFlag      = $strFlag;
            $giftHistory[$idx]->strACOCurSymbol = $strCurSymbol;
            $giftHistory[$idx]->strACOCountry   = $strCountry;
            ++$idx;
         }
      }
      $this->dropTempGiftTable($strTableName);
   }

   private function strSetSortOrder($enumSortType){
   //---------------------------------------------------------------------
   // $enumSortType - date, giftID, name, amount, acctCamp
   //---------------------------------------------------------------------
      switch ($enumSortType){
         case 'giftID':
            $strSort = ' gi_lKeyID ';
            break;

         case 'name':
            $strSort = ' pe_strLName, pe_strFName, pe_lKeyID, gi_dteDonation, gi_curAmnt, gi_lKeyID ';
            break;

         case 'amount':
            $strSort = ' ga_strAccount, gc_strCampaign, gi_dteDonation, gi_curAmnt, gi_lKeyID ';
            break;

         case 'acctCamp':
            $strSort = ' ga_strAccount, gc_strCampaign, gi_dteDonation, gi_curAmnt, gi_lKeyID ';
            break;

         case 'date':
         default:
            $strSort = ' gi_dteDonation, gi_curAmnt, gi_lKeyID ';
            break;
      }
      return($strSort);
   }

   private function dropTempGiftTable($strTableName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "DROP TABLE $strTableName;";
      $query = $this->db->query($sqlStr);
   }

   private function createTempGiftTable($strTableName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "DROP TABLE IF EXISTS $strTableName;";
      $this->db->query($sqlStr);

      $sqlStr =
        "CREATE TEMPORARY TABLE $strTableName (
           tgi_lKeyID    int(11) NOT NULL auto_increment,
           tgi_lGiftID   int(11) default NULL,
           tgi_lPID      int(11) default NULL,

           PRIMARY KEY       (tgi_lKeyID),
           KEY tgi_lGiftID   (tgi_lGiftID),
           KEY tgi_lPID      (tgi_lPID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
      $this->db->query($sqlStr);
   }

   private function dumpTempTable($strTableName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT("<pre>Dumping temp gift table $strTableName");
      $sqlStr =
        "SELECT * FROM $strTableName ORDER BY tgi_lKeyID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         echoT('No donations');
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            print_r($row);
         }
      }
      echoT('</pre><br><br>');
   }

   public function updateGiftRecord(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlCommon = $this->strCommonGiftSQL();
      $sqlStr =
          "UPDATE gifts
           SET $sqlCommon
           WHERE gi_lKeyID=".$this->gifts[0]->gi_lKeyID.';';

      $query = $this->db->query($sqlStr);
   }

   public function lAddNewGiftRecord(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlCommon = $this->strCommonGiftSQL();

      $sqlStr =
             "INSERT INTO gifts
              SET $sqlCommon,
                 gi_lOriginID   = $glUserID,
                 gi_lForeignID  = ".$this->gifts[0]->gi_lForeignID.',
                 gi_bRetired    = 0,
                 gi_dteOrigin   = NOW();';
      $this->db->query($sqlStr);
      $this->gifts[0]->gi_lKeyID = $lGiftID = $this->db->insert_id();

         //--------------------------------------------------------
         // create blank/default records for all the personalized
         // gift tables
         //--------------------------------------------------------
      $clsUFC = new muser_fields_create;
      $clsUFC->enumTType = CENUM_CONTEXT_GIFT;
      $clsUFC->loadTablesViaTType();
      if ($clsUFC->lNumTables > 0){
         foreach ($clsUFC->userTables as $clsTable){
            $clsUFC->createSingleEmptyRec($clsTable, $lGiftID);
         }
      }
      return($lGiftID);
   }

   private function strCommonGiftSQL(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $gift = $this->gifts[0];

      return(
           'gi_lCampID       = '.$gift->gc_lKeyID.',
            gi_lLastUpdateID = '.$glUserID.',
            gi_bGIK          = '.($gift->gi_bGIK            ? '1' : '0').',
            gi_lSponsorID    = '.strDBValueConvert_INT($gift->gi_lSponsorID).',
            gi_curAmnt       = '.number_format($gift->gi_curAmnt, 2, '.', '').',
            gi_lACOID        = '.($gift->lACOID).',
            gi_dteDonation   = '.strPrepStr($gift->mdteDonation).',
            gi_lAttributedTo = '.strDBValueConvert_INT($gift->gi_lAttributedTo).',
            gi_lGIK_ID       = '.strDBValueConvert_INT($gift->gi_lGIK_ID).',
            gi_lPledgeID     = '.strDBValueConvert_INT($gift->lPledgeID).',
            gi_strNotes      = '.strPrepStr($gift->strNotes).',
            gi_strCheckNum   = '.strPrepStr($gift->gi_strCheckNum).',
            gi_lPaymentType  = '.$gift->gi_lPaymentType.',
            gi_lMajorGiftCat = '.$gift->gi_lMajorGiftCat.' ');
   }

   public function retireGiftsViaPID($lPID, $lGroupID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "SELECT gi_lKeyID FROM gifts WHERE gi_lForeignID=$lPID AND NOT gi_bRetired;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }else{
//         $numRows = mysql_num_rows($result);
//         for ($idx=0; $idx<$numRows; ++$idx) {
//            $row = mysql_fetch_array($result);
      $idx = 0;
      foreach ($query->result() as $row){
         $this->retireSingleGift($row->gi_lKeyID, $lGroupID);
         ++$idx;
      }
   }

   function giftIDsViaFID($lFID, &$lNumGifts, &$GIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $GIDs = array();
      $sqlStr =
         "SELECT gi_lKeyID FROM gifts WHERE gi_lForeignID=$lFID AND NOT gi_bRetired;";
      $query = $this->db->query($sqlStr);
      $lNumGifts = $query->num_rows();
      if ($lNumGifts > 0){
         foreach ($query->result() as $row){
            $GIDs[] = (int)$row->gi_lKeyID;
         }
      }
   }

   public function retireSingleGift($lGiftID, $lGroupID){
   //---------------------------------------------------------------------
   // $lGroupID is the recyle bin group id; set to null if deleting
   // a single gift
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        "UPDATE gifts
         SET
            gi_bRetired=1,
            gi_lLastUpdateID=$glUserID
         WHERE gi_lKeyID=$lGiftID;";
      $this->db->query($sqlStr);

         // if this is a silent auction donation,
         // remove gift ID
      $sqlStr =
        "UPDATE gifts_auctions_packages
         SET
            ap_lGiftID       = NULL,
            ap_lLastUpdateID = $glUserID
         WHERE ap_lGiftID = $lGiftID;";
      $this->db->query($sqlStr);

      $uf = new muser_fields;
      $uf->deleteForeignViaUFTableType(CENUM_CONTEXT_GIFT, $lGiftID);

      $this->logGiftRetire($lGiftID, $lGroupID);
   }

   private function logGiftRetire($lGiftID, $lGroupID){
   //-----------------------------------------------------------------------
   // caller must first call $this->peopleInfoLight();
   //-----------------------------------------------------------------------
      $clsRecycle = new mrecycle_bin;

      $clsRecycle->lForeignID      = $lGiftID;
      $clsRecycle->strTable        = 'gifts';
      $clsRecycle->strRetireFN     = 'gi_bRetired';
      $clsRecycle->strKeyIDFN      = 'gi_lKeyID';
      $clsRecycle->strNotes        = 'Retired gift '.str_pad($lGiftID, 5, '0', STR_PAD_LEFT);
      $clsRecycle->lGroupID        = $lGroupID;
      $clsRecycle->enumRecycleType = 'Gifts';

      $clsRecycle->addRecycleEntry();
   }

   public function giftHTMLSummary(){
   //-----------------------------------------------------------------------
   // assumes user has called $clsGift->loadGift($lGID);
   //-----------------------------------------------------------------------
      global $genumDateFormat;

      $strOut = '';
      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $clsRpt->setEntrySummary();

      $gift    =  &$this->gifts[0];
      $lGID    = $gift->gi_lKeyID;
      $lFID    = $gift->gi_lForeignID;
      $lSponID = $gift->gi_lSponsorID;

      if ($gift->pe_bBiz){
         $strLinkDonor = strLinkView_BizRecord($lFID, 'View business/organization record', true);
      }else {
         $strLinkDonor = strLinkView_PeopleRecord($lFID, 'View business/organization record', true);
      }

      $strOut .=
          $clsRpt->openReport('', '')

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Gift ID:')
         .$clsRpt->writeCell (strLinkView_GiftsRecord($lGID, 'View Gift Record', true)
                            .' '.str_pad($lGID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ()."\n"

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Donor:')
         .$clsRpt->writeCell ($strLinkDonor.' '.$gift->strSafeName)
         .$clsRpt->closeRow  ()."\n"

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Amount:')
         .$clsRpt->writeCell ($gift->strFormattedAmnt)
         .$clsRpt->closeRow  ()."\n"

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Date:')
         .$clsRpt->writeCell (date($genumDateFormat, $gift->gi_dteDonation))
         .$clsRpt->closeRow  ()."\n"

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Account/Campaign:')
         .$clsRpt->writeCell (
                          htmlspecialchars($gift->ga_strAccount.' / '.$gift->gc_strCampaign))
         .$clsRpt->closeRow  ()."\n";

      if (!is_null($lSponID)){
         $strOut .=
             $clsRpt->openRow   (false)
            .$clsRpt->writeLabel('Sponsor ID:')
            .$clsRpt->writeCell (
                           strLinkView_Sponsorship($lSponID, 'View sponsorship record', true).' '
                          .str_pad($lSponID, 5, '0', STR_PAD_LEFT))
            .$clsRpt->closeRow  ()."\n";
      }

      $strOut .=
         $clsRpt->closeReport("<br>\n");
      return($strOut);
   }

   public function loadGiftListViaACO(
                           &$lNumGifts,   &$gifts,  $lACOID,
                           $enumListType, $strSort){
   //---------------------------------------------------------------------
   // $enumSortType - date, giftID, name, amount, acctCamp
   //---------------------------------------------------------------------
      switch ($enumListType){
         case 'viaAccounts':
            $sqlWhere = " AND gc_lAcctID = $this->lListsAcctID ";
            break;
         default:
            screamForHelp($enumListType.': Invalid switch option<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
            break;
      }

      if ($this->strGLSortClause==''){
         $strSort = '';
      }else {
         $strSort = " ORDER BY $this->strGLSortClause ";
      }

      $sqlStr =
        "SELECT
            gi_lKeyID, gi_lForeignID, gi_lSponsorID,
            gi_curAmnt, gi_dteDonation, 
            gi_lACOID,
            pe_lKeyID, pe_bBiz, pe_strFName, pe_strLName,
            gc_lKeyID, gc_strCampaign, gi_bGIK, lgen_strListItem AS strGIK,
            ga_lKeyID, ga_strAccount

         FROM gifts
            INNER JOIN gifts_campaigns ON gi_lCampID = gc_lKeyID
            INNER JOIN gifts_accounts  ON gc_lAcctID = ga_lKeyID
            INNER JOIN people_names    ON pe_lKeyID  = gi_lForeignID
            LEFT  JOIN lists_generic   ON gi_lGIK_ID = lgen_lKeyID
         WHERE NOT gi_bRetired
            AND gi_lACOID=$lACOID
            $sqlWhere

         $strSort ;";

      $query = $this->db->query($sqlStr);
      $lNumGifts = $numRows = $query->num_rows();

         $gifts = array();
         if ($numRows==0) {
         }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $gifts[$idx] = new stdClass;

            $gifts[$idx]->gi_lKeyID       = $row->gi_lKeyID;
            $gifts[$idx]->gi_lForeignID   = $row->gi_lForeignID;
            $gifts[$idx]->gi_lSponsorID   = $row->gi_lSponsorID;
            $gifts[$idx]->gi_curAmnt      = $row->gi_curAmnt;
            $gifts[$idx]->gi_dteDonation  = dteMySQLDate2Unix($row->gi_dteDonation);
            $gifts[$idx]->gi_bGIK         = $row->gi_bGIK;
            $gifts[$idx]->strGIK          = $row->strGIK;
            $gifts[$idx]->pe_lKeyID       = $row->pe_lKeyID;
            $gifts[$idx]->pe_bBiz         = $row->pe_bBiz;
            $gifts[$idx]->pe_strFName     = $row->pe_strFName;
            $gifts[$idx]->pe_strLName     = $row->pe_strLName;
            $gifts[$idx]->gc_lKeyID       = $row->gc_lKeyID;
            $gifts[$idx]->gc_strCampaign  = $row->gc_strCampaign;
            $gifts[$idx]->ga_lKeyID       = $row->ga_lKeyID;
            $gifts[$idx]->ga_strAccount   = $row->ga_strAccount;

            if ($gifts[$idx]->pe_bBiz){
               $gifts[$idx]->strSafeName   = $gifts[$idx]->strSafeNameLF = htmlspecialchars($row->pe_strLName);
            }else {
               $gifts[$idx]->strSafeName   = htmlspecialchars(
                             $row->pe_strFName.' '.$row->pe_strLName);
               $gifts[$idx]->strSafeNameLF = htmlspecialchars(
                             $row->pe_strLName.', '.$row->pe_strFName);
            }
            ++$idx;
         }
      }
   }

   public function lNumTotGiftsInList(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumGifts = 0;

      foreach($this->giftLists as $clsGL){
         $lNumGifts += $clsGL->lNumGifts;
      }
      return($lNumGifts);
   }

   public function setGiftAck($lGiftID, $bSet){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'UPDATE gifts
          SET gi_dteAck   = '.($bSet ? 'NOW()' : " '0000-00-00 00:00:00' ").',
              gi_bAck     = '.($bSet ? '1'     : '0').",
              gi_lAckByID = $glUserID,
              gi_lLastUpdateID = $glUserID
          WHERE gi_lKeyID=$lGiftID;";
      $this->db->query($sqlStr);
   }



      /*----------------------------------------------------
                R E P O R T S
      ----------------------------------------------------*/
   function lNumRecsInHonMemReport(&$sRpt,
                                   $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lHMID = $sRpt->lHMID;

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      $sqlStr =
           "SELECT ghml_lKeyID
            FROM gifts_hon_mem_links
               INNER JOIN gifts ON gi_lKeyID=ghml_lGiftID
            WHERE ghml_lHonMemID=$lHMID AND NOT gi_bRetired
            $strLimit;";
      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strHonMemReportExport(&$sRpt,
                                  $bReport, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         return($this->strHonMemReport($sRpt, $lStartRec, $lRecsPerPage));
      }else {
         return($this->strHonMemExport($sRpt));
      }
   }

   private function strHonMemExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lHMID = $sRpt->lHMID;
      $sqlStr =
           'SELECT '.strExportFields_Gifts()."
            FROM gifts_hon_mem_links
               INNER JOIN gifts ON gi_lKeyID=ghml_lGiftID
               INNER JOIN gifts_campaigns             ON gi_lCampID             = gc_lKeyID
               INNER JOIN gifts_accounts              ON gc_lAcctID             = ga_lKeyID
               INNER JOIN people_names   AS donor     ON donor.pe_lKeyID        = gi_lForeignID
               INNER JOIN admin_aco                   ON gi_lACOID              = aco_lKeyID

               LEFT  JOIN lists_generic AS payCat     ON payCat.lgen_lKeyID     = gi_lPaymentType
               LEFT  JOIN lists_generic AS giftCat    ON giftCat.lgen_lKeyID    = gi_lMajorGiftCat
               LEFT  JOIN lists_generic AS giftAttrib ON giftAttrib.lgen_lKeyID = gi_lAttributedTo
               LEFT  JOIN sponsor                     ON sp_lKeyID              = gi_lSponsorID
               LEFT  JOIN people_names   AS spon      ON spon.pe_lKeyID         = sp_lForeignID
               LEFT  JOIN lists_generic  AS gik       ON gi_lGIK_ID             = gik.lgen_lKeyID
               LEFT  JOIN admin_users AS ackUser      ON gi_lAckByID            = ackUser.us_lKeyID
            WHERE ghml_lHonMemID=$lHMID
               AND NOT gi_bRetired
               AND NOT donor.pe_bRetired
            ORDER BY gi_lKeyID;";

      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   private function strHonMemReport($sRpt, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cHM = new mhon_mem;
      $lHMID = $cHM->lHMID = $sRpt->lHMID;

      $cHM->loadHonMem('via HMID');
      $cHMTable = &$cHM->honMemTable[0];

      $strOut = ($cHMTable->ghm_bHon ? 'Honorarium' : 'Memorial').' Donations for '
                      .'<b>'.$cHMTable->ghm_strSafeName.'&nbsp;'
                      .'</b>'
                      .strLinkView_PeopleRecord($cHMTable->ghm_lFID, 'View people record', true)
                      .'<br><br>';
      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      $sqlStr =
           "SELECT

               gi_lKeyID, gi_lSponsorID, gi_lCampID, gi_curAmnt,
               gi_lACOID,
               gi_dteDonation,
               gi_bGIK, gi_lGIK_ID,
               gi_strCheckNum, gi_lPaymentType, gi_lMajorGiftCat,
               aco_strFlag, aco_strName,

               gi_bAck, gi_lAckByID, us_strFirstName, us_strLastName,
               gi_dteAck,

               pe_lKeyID, pe_bBiz, pe_strFName, pe_strLName,
               gc_lKeyID, gc_strCampaign, gi_bGIK, lgen_strListItem AS strGIK,
               ga_lKeyID, ga_strAccount

            FROM gifts_hon_mem_links
               INNER JOIN gifts ON gi_lKeyID=ghml_lGiftID
               INNER JOIN gifts_campaigns ON gi_lCampID = gc_lKeyID
               INNER JOIN gifts_accounts  ON gc_lAcctID = ga_lKeyID
               INNER JOIN people_names    ON pe_lKeyID  = gi_lForeignID
               INNER JOIN admin_aco       ON gi_lACOID  = aco_lKeyID
               LEFT  JOIN lists_generic   ON gi_lGIK_ID = lgen_lKeyID
               LEFT  JOIN admin_users     ON gi_lAckByID= us_lKeyID
            WHERE ghml_lHonMemID=$lHMID
               AND NOT gi_bRetired
               AND NOT pe_bRetired
            ORDER BY gi_dteDonation, gi_curAmnt, gi_lKeyID
            $strLimit;";

      return($strOut.$this->strDonationViewTable($sqlStr, true, true));
   }

   public function lNumRecsInAckReport(
                            &$sRpt,
                            $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->extractAckRptOpts($sRpt,
                            $dteStart,     $dteEnd,     $bFindNonAck,
                            $bIncludeSpon, $bMarkAsAck, $enumSort,
                            $enumAckType,  $lACO,       $lYear);
      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      switch ($enumAckType){
         case 'gifts':
            $sqlStr =
                 'SELECT gi_lKeyID
                  FROM gifts
                     INNER JOIN people_names ON pe_lKeyID = gi_lForeignID
                  WHERE NOT pe_bRetired '.$this->strRptAckWhere($dteStart, $dteEnd, $bFindNonAck, $bIncludeSpon, $lACO, $enumAckType)."
                  $strLimit;";
            break;

         case 'hon':
         case 'mem':
            $sqlStr =
                 'SELECT gi_lKeyID
                  FROM gifts
                     INNER JOIN people_names        ON pe_lKeyID      = gi_lForeignID
                     INNER JOIN gifts_hon_mem_links ON gi_lKeyID      = ghml_lGiftID
                     INNER JOIN lists_hon_mem       ON ghml_lHonMemID = ghm_lKeyID
                  WHERE NOT pe_bRetired '.$this->strRptAckWhere($dteStart, $dteEnd, $bFindNonAck, $bIncludeSpon, $lACO, $enumAckType).'
                     AND '.($enumAckType=='hon' ? '' : ' NOT ' )." ghm_bHon
                  $strLimit;";
            break;


         default:
            screamForHelp($enumAckType.': ack type not found<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   private function strRptAckWhere($dteStart, $dteEnd, $bFindNonAck, $bIncludeSpon, $lACO, $enumAckType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strWhere = " AND NOT gi_bRetired
                    AND gi_lACOID=$lACO
                    AND gi_dteDonation BETWEEN ".strPrepDate($dteStart).' AND '.strPrepDateTime($dteEnd)."\n";
      if ($bFindNonAck){
         if ($enumAckType=='gifts'){
            $strWhere .= ' AND NOT gi_bAck '."\n";
         }else {
            $strWhere .= ' AND NOT ghml_bAck '."\n";
         }
      }

      if ($enumAckType=='hon'){
         $strWhere .= ' AND ghm_bHon '."\n";
      }elseif ($enumAckType=='mem') {
         $strWhere .= ' AND NOT ghm_bHon '."\n";
      }

      if (!$bIncludeSpon) $strWhere .= 'AND gi_lSponsorID IS NULL '."\n";
      return($strWhere);
   }

   private function strRptAckOrder($enumSort){
      return($this->strRptOrderGeneric($enumSort, false));
   }

   private function strRptRecentOrder(&$sRpt, $enumSort){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->groupedGiftOpts($sRpt, $bGroupedDonor, $bShowAccts, $bShowCamps);
      $strSort = '';
      if ($bGroupedDonor){
         $strSort = ' ORDER BY  pe_strLName, pe_strFName, pe_strMName, pe_lKeyID ';
         if ($bShowAccts){
            $strSort .= ', ga_strAccount ';
         }
         if ($bShowCamps){
            $strSort .= ', gc_strCampaign ';
         }
      }else {
         $strSort = $this->strRptOrderGeneric($enumSort, true);
      }
      return($strSort);
   }

   private function strRptOrderGeneric($enumSort, $bDateDesc){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDesc = ($bDateDesc ? 'DESC' : '');
      switch ($enumSort){
         case 'date':
            $strOrder = ' gi_dteDonation '.$strDesc.', pe_strLName, pe_strFName, pe_lKeyID, gi_lKeyID ';
            break;
         case 'amnt':
            $strOrder = ' gi_curAmnt DESC, pe_strLName, pe_strFName, pe_lKeyID, gi_lKeyID ';
            break;
         case 'donor':
         default:
            $strOrder = ' pe_strLName, pe_strFName, pe_strMName, pe_lKeyID, gi_dteDonation '.$strDesc.', gi_lKeyID ';
            break;
      }
      return('ORDER BY '.$strOrder);
   }

   private function extractAckRptOpts(&$sRpt,
                                   &$dteStart,     &$dteEnd,     &$bFindNonAck,
                                   &$bIncludeSpon, &$bMarkAsAck, &$enumSort,
                                   &$enumAckType,  &$lACO,       &$lYear){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $dteStart     = $dteEnd     = $bFindNonAck =
      $bIncludeSpon = $bMarkAsAck = $enumSort    =
      $enumAckType  = $lACO       = $lYear       = null;

      if (isset($sRpt->dteStart))     $dteStart     = $sRpt->dteStart;
      if (isset($sRpt->dteEnd))       $dteEnd       = strtotime(date('m/d/Y 23:59:59'), $sRpt->dteEnd);
      if (isset($sRpt->bFindNonAck))  $bFindNonAck  = $sRpt->bFindNonAck;
      if (isset($sRpt->bIncludeSpon)) $bIncludeSpon = $sRpt->bIncludeSpon;
      if (isset($sRpt->bMarkAsAck))   $bMarkAsAck   = $sRpt->bMarkAsAck;
      if (isset($sRpt->enumSort))     $enumSort     = $sRpt->enumSort;
      if (isset($sRpt->enumAckType))  $enumAckType  = $sRpt->enumAckType;
      if (isset($sRpt->lACO))         $lACO         = $sRpt->lACO;
      if (isset($sRpt->lYear))        $lYear        = $sRpt->lYear;
   }

   function strGiftAckReportExport(&$sRpt,   $reportID,
                                   $bReport, $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         return($this->strGiftAckReport($sRpt, $lStartRec, $lRecsPerPage));
      }else {
         return($this->strAckExport($sRpt));
      }
   }

   function strGiftAckReport(&$sRpt, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $enumAckType = $sRpt->enumAckType;
      $cACO = new madmin_aco;
      $cACO->loadCountries(false, true, true, $sRpt->lACO);
      $sRpt->strFlag       = $cACO->countries[0]->strFlagImg;
      $sRpt->strACOCountry = $cACO->countries[0]->strName;
      switch ($enumAckType){
         case 'gifts':
            return($this->strAckRptGifts($sRpt, $lStartRec, $lRecsPerPage));
            break;

         case 'hon':
         case 'mem':
            return($this->strAckRptHonMem($enumAckType=='hon', $sRpt, $lStartRec, $lRecsPerPage));
            break;

         default:
            screamForHelp($enumAckType.': ack type not found<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   private function strAckLabel($strType, $sRpt){
      global $genumDateFormat;

      return('
         <table class="enpView">
            <tr>
               <td class="enpViewLabel">
                  Type:
               </td>
               <td class="enpView">'
                  .$strType.'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Accounting Country:
               </td>
               <td class="enpView">'
                  .$sRpt->strACOCountry.'&nbsp;'.$sRpt->strFlag.'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Reporting Period:
               </td>
               <td class="enpView">'.$sRpt->strDateRange.'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Include Spon. Payments?:
               </td>
               <td class="enpView">'
                  .($sRpt->bIncludeSpon ? 'Yes' : 'No').'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Included Donations:
               </td>
               <td class="enpView">'
                  .($sRpt->bFindNonAck ? 'Unacknowledged Donations' : 'All').'
               </td>
            </tr>
         </table>');
   }

   function strAckRptHonMem($bHon, &$sRpt, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $strOut = $this->strAckLabel(($bHon ? 'Honorariums' : 'Memorials'), $sRpt).'<br><br>';

      $strLabel = $bHon ? 'Honorarium' : 'Memorial';
      $this->extractAckRptOpts($sRpt,
                            $dteStart,     $dteEnd,     $bFindNonAck,
                            $bIncludeSpon, $bMarkAsAck, $enumSort,
                            $enumAckType,  $lACO,       $lYear);
      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      if ($bHon){
         $strExtraJoin = $strExtraSel  = '';
      }else {
         $strExtraJoin = ' LEFT JOIN people_names AS mailCon ON mailCon.pe_lKeyID = ghm_lMailContactID '."\n";
         $strExtraSel  = '
               mailCon.pe_lKeyID   AS mcPID,
               mailCon.pe_strFName AS strMCFName,
               mailCon.pe_strLName AS strMCLName, '."\n";
      }

      $sqlStr =
           "SELECT
               gi_lKeyID, gi_lSponsorID, gi_lCampID, gi_curAmnt,
               gi_lACOID,
               gi_dteDonation,
               gi_bGIK, gi_lGIK_ID,
               gi_strCheckNum, gi_lPaymentType, gi_lMajorGiftCat,

               ghml_bAck, ghml_lAckByID, us_strFirstName, us_strLastName,
               ghml_dteAck, 

               donor.pe_lKeyID   AS donorID,
               donor.pe_bBiz     AS bBiz,
               donor.pe_strFName AS strDFName,
               donor.pe_strLName AS strDLName,

               honMem.pe_lKeyID   AS honMemPID,
               honMem.pe_strFName AS strHMFName,
               honMem.pe_strLName AS strHMLName,
               $strExtraSel

               gc_lKeyID, gc_strCampaign, gi_bGIK, lgen_strListItem AS strGIK,
               ga_lKeyID, ga_strAccount
            FROM gifts
               INNER JOIN people_names AS donor  ON donor.pe_lKeyID  = gi_lForeignID
               INNER JOIN gifts_campaigns        ON gi_lCampID       = gc_lKeyID
               INNER JOIN gifts_accounts         ON gc_lAcctID       = ga_lKeyID
               INNER JOIN gifts_hon_mem_links    ON gi_lKeyID        = ghml_lGiftID
               INNER JOIN lists_hon_mem          ON ghml_lHonMemID   = ghm_lKeyID
               INNER JOIN people_names AS honMem ON honMem.pe_lKeyID = ghm_lFID
               LEFT  JOIN lists_generic          ON gi_lGIK_ID       = lgen_lKeyID
               LEFT  JOIN admin_users            ON ghml_lAckByID    = us_lKeyID
               $strExtraJoin
            WHERE NOT donor.pe_bRetired
                ".$this->strRptAckWhere($dteStart, $dteEnd, $bFindNonAck, $bIncludeSpon, $lACO, $enumAckType)."
            $strLimit;";

      $query = $this->db->query($sqlStr);
      if ($query->num_rows()==0){
         return($strOut.'<br><br><i>There are no donations that match your search criteria.</i>');
      }else {
         $strOut .= '
            <table class="enpRptC" style="width: 900px;">
               <tr>
                  <td class="enpRptLabel" style="width: 70px;">
                     giftID
                  </td>
                  <td class="enpRptLabel" style="width: 80px;">
                     Amount
                  </td>
                  <td class="enpRptLabel" style="width: 90px;">
                     Date
                  </td>
                  <td class="enpRptLabel" style="width: 110px;">
                     Account/
                     Campaign
                  </td>
                  <td class="enpRptLabel" style="width: 200px;">
                     Donor
                  </td>
                  <td class="enpRptLabel" style="width: 200px;">'
                     .($bHon ? 'Honoree' : 'Memorial').'
                  </td>';

         if (!$bHon){
            $strOut .= '
                  <td class="enpRptLabel" style="width: 200px;">
                     Mail Contact
                  </td>';
         }
         $strOut .= '
                  <td class="enpRptLabel" style="">'.$strLabel.'<br>
                     Acknowledged?
                  </td>
               </tr>';

         foreach ($query->result() as $row){
            $lGiftID = $row->gi_lKeyID;
            $bBiz = $row->bBiz;
            $bSpon = !is_null($row->gi_lSponsorID);
            $lPeopleID = $row->donorID;

            if ($row->ghml_bAck){
               $strAlignAck = 'left';
               $strAck = 'Yes - '.date($genumDateFormat, dteMySQLDate2Unix($row->ghml_dteAck)).' by '
                         .htmlspecialchars($row->us_strFirstName.' '.$row->us_strLastName);
            }else {
               $strAlignAck = 'center';
               $strAck = '-';
            }
            if ($row->gi_bGIK){
               $strAlign = 'left';
               $strGIK   = htmlspecialchars($row->strGIK);
            }else {
               $strAlign = 'center';
               $strGIK   = '-';
            }
            if ($bBiz){
               $strName = htmlspecialchars($row->strDLName);
               $strLink = strLinkView_BizRecord($lPeopleID, 'View business/organization record', true);
            }else {
               $strName = htmlspecialchars($row->strDLName.', '.$row->strDFName);
               $strLink = strLinkView_PeopleRecord($lPeopleID, 'View people record', true);
            }

            if (!$bHon){
               if (is_null($row->mcPID)){
                  $strMC = '<font color="red"><i>Mail contact not set!</i></font>';
               }else {
                  $strMC = strLinkView_PeopleRecord($row->mcPID, 'View people record for mail contact', true)
                          .htmlspecialchars($row->strMCLName.', '.$row->strMCFName);
               }
            }

            $strOut .= '
                  <tr class="makeStripe">
                     <td class="enpRpt" style="width: 50px; text-align: center;">'
                        .strLinkView_GiftsRecord($lGiftID, 'View gift record', true).'&nbsp;'
                        .str_pad($lGiftID, 5, '0', STR_PAD_LEFT).'
                     </td>

                     <td class="enpRpt" style="width: 60px; text-align: right; padding-right: 5px;">'
                        .number_format($row->gi_curAmnt, 2).'
                     </td>

                     <td class="enpRpt" style="text-align: left;">'
                        .date($genumDateFormat, dteMySQLDate2Unix($row->gi_dteDonation)).'
                     </td>


                     <td class="enpRpt" style="text-align: left;">'
                        .htmlspecialchars($row->ga_strAccount).' / '
                        .htmlspecialchars($row->gc_strCampaign).'
                     </td>

                     <td class="enpRpt" style="text-align: left;">'
                        .$strLink.str_pad($lPeopleID, 5, '0', STR_PAD_LEFT).'&nbsp'.$strName.'
                     </td>

                     <td class="enpRpt" style="text-align: left;">'
                        .strLinkView_PeopleRecord($row->honMemPID, 'View people record for '.($bHon ? 'honorarium' : 'memorial'), true)
                        .htmlspecialchars($row->strHMLName.', '.$row->strHMFName).'
                     </td>';

            if (!$bHon){
               $strOut .= '
                     <td class="enpRpt" style="text-align: left;">'
                        .$strMC.'
                     </td>';
            }
            $strOut .= '
                     <td class="enpRpt" style="text-align: '.$strAlignAck.';">'
                        .$strAck.'
                     </td>

                  </tr>';
         }
      }

      $strOut .= '</table><br>';
      return($strOut);
   }

   private function strAckRptGifts(&$sRpt, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $this->extractAckRptOpts($sRpt,
                            $dteStart,     $dteEnd,     $bFindNonAck,
                            $bIncludeSpon, $bMarkAsAck, $enumSort,
                            $enumAckType,  $lACO,       $lYear);

      $strOut = $this->strAckLabel('Donations', $sRpt).'<br><br>';
      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      $sqlStr =
           'SELECT
               gi_lKeyID, gi_lSponsorID, gi_lCampID, gi_curAmnt,
               gi_lACOID,
               gi_dteDonation, 
               gi_bGIK, gi_lGIK_ID,
               gi_strCheckNum, gi_lPaymentType, gi_lMajorGiftCat,

               gi_bAck, gi_lAckByID, us_strFirstName, us_strLastName,
               gi_dteAck, 

               pe_lKeyID, pe_bBiz, pe_strFName, pe_strLName,
               gc_lKeyID, gc_strCampaign, gi_bGIK, lgen_strListItem AS strGIK,
               ga_lKeyID, ga_strAccount

            FROM gifts
               INNER JOIN gifts_campaigns ON gi_lCampID = gc_lKeyID
               INNER JOIN gifts_accounts  ON gc_lAcctID = ga_lKeyID
               INNER JOIN people_names    ON pe_lKeyID  = gi_lForeignID
               LEFT  JOIN lists_generic   ON gi_lGIK_ID = lgen_lKeyID
               LEFT  JOIN admin_users     ON gi_lAckByID= us_lKeyID
            WHERE NOT pe_bRetired '.$this->strRptAckWhere($dteStart, $dteEnd, $bFindNonAck, $bIncludeSpon, $lACO, $enumAckType).' '
            .$this->strRptAckOrder($enumSort)."
            $strLimit;";

      return($strOut.$this->strDonationViewTable($sqlStr, true, false));
   }

   function strAckExport(&$sRpt){
      $this->extractAckRptOpts($sRpt,
                               $dteStart,     $dteEnd,     $bFindNonAck,
                               $bIncludeSpon, $bMarkAsAck, $enumSort,
                               $enumAckType,  $lACO,       $lYear);
      switch ($enumAckType){
         case 'gifts':
            $strOut = $this->strExportGiftAck($sRpt);
            break;

         case 'hon':
         case 'mem':
            $strOut = $this->strExportHonMemAck($sRpt, $enumAckType=='hon');
            break;

         default:
            screamForHelp($enumAckType.': ack type not found<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      if ($bMarkAsAck) $this->markAsAcked($sRpt, $enumAckType);
      return($strOut);
   }

   private function markAsAcked(&$sRpt, $enumAckType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $this->extractAckRptOpts($sRpt,
                               $dteStart,     $dteEnd,     $bFindNonAck,
                               $bIncludeSpon, $bMarkAsAck, $enumSort,
                               $enumAckType,  $lACO,       $lYear);
      switch ($enumAckType){
         case 'gifts':
            $sqlStr =
                 "UPDATE gifts
                  SET
                     gi_bAck=1,
                     gi_dteAck=NOW(),
                     gi_lAckByID=$glUserID,
                     gi_lLastUpdateID=$glUserID
                  WHERE 1 ".$this->strRptAckWhere($dteStart, $dteEnd, $bFindNonAck,
                                                  $bIncludeSpon, $lACO, $enumAckType).'
                     AND NOT gi_bAck;';
            break;

         case 'hon':
         case 'mem':
            $sqlStr =
                 "UPDATE gifts_hon_mem_links
                     INNER JOIN lists_hon_mem ON ghm_lKeyID = ghml_lHonMemID
                     INNER JOIN gifts         ON gi_lKeyID  = ghml_lGiftID
                  SET
                     ghml_bAck=1,
                     ghml_dteAck=NOW(),
                     ghml_lAckByID=$glUserID
                  WHERE 1 ".$this->strRptAckWhere($dteStart, $dteEnd, $bFindNonAck,
                                                  $bIncludeSpon, $lACO, $enumAckType).'
                     AND NOT ghml_bAck;';
            break;

         default:
            screamForHelp($enumAckType.': ack type not found<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      $this->db->query($sqlStr);
   }

   private function strExportGiftAck($sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->extractAckRptOpts($sRpt,
                               $dteStart,     $dteEnd,     $bFindNonAck,
                               $bIncludeSpon, $bMarkAsAck, $enumSort,
                               $enumAckType,  $lACO,       $lYear);
      $sqlStr =
           'SELECT '.strExportFields_Gifts().'
            FROM gifts
               INNER JOIN gifts_campaigns             ON gi_lCampID             = gc_lKeyID
               INNER JOIN gifts_accounts              ON gc_lAcctID             = ga_lKeyID
               INNER JOIN people_names   AS donor     ON donor.pe_lKeyID        = gi_lForeignID
               INNER JOIN admin_aco                   ON gi_lACOID              = aco_lKeyID

               LEFT  JOIN lists_generic AS payCat     ON payCat.lgen_lKeyID     = gi_lPaymentType
               LEFT  JOIN lists_generic AS giftCat    ON giftCat.lgen_lKeyID    = gi_lMajorGiftCat
               LEFT  JOIN lists_generic AS giftAttrib ON giftAttrib.lgen_lKeyID = gi_lAttributedTo
               LEFT  JOIN sponsor                     ON sp_lKeyID              = gi_lSponsorID
               LEFT  JOIN people_names   AS spon      ON spon.pe_lKeyID         = sp_lForeignID
               LEFT  JOIN lists_generic  AS gik       ON gi_lGIK_ID             = gik.lgen_lKeyID
               LEFT  JOIN admin_users AS ackUser      ON gi_lAckByID            = ackUser.us_lKeyID
            WHERE 1 '.$this->strRptAckWhere($dteStart, $dteEnd, $bFindNonAck, $bIncludeSpon, $lACO, $enumAckType).'
            ORDER BY gi_lKeyID;';

      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   private function strExportHonMemAck(&$sRpt, $bHon){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->extractAckRptOpts($sRpt,
                               $dteStart,     $dteEnd,     $bFindNonAck,
                               $bIncludeSpon, $bMarkAsAck, $enumSort,
                               $enumAckType,  $lACO,       $lYear);
      $sqlStr =
        'SELECT '.strExportFields_GiftsHonMem($bHon).'
         FROM gifts_hon_mem_links
            INNER JOIN lists_hon_mem               ON ghm_lKeyID        = ghml_lHonMemID
            INNER JOIN gifts                       ON gi_lKeyID         = ghml_lGiftID
            INNER JOIN gifts_campaigns             ON gi_lCampID        = gc_lKeyID
            INNER JOIN gifts_accounts              ON gc_lAcctID        = ga_lKeyID
            INNER JOIN admin_aco                   ON gi_lACOID         = aco_lKeyID
            INNER JOIN people_names AS peepDonor   ON gi_lForeignID     = peepDonor.pe_lKeyID
            INNER JOIN people_names AS peepHM      ON ghm_lFID          = peepHM.pe_lKeyID

            LEFT  JOIN lists_generic AS payCat    ON payCat.lgen_lKeyID  = gi_lPaymentType
            LEFT  JOIN lists_generic AS giftCat   ON giftCat.lgen_lKeyID = gi_lMajorGiftCat
            LEFT  JOIN lists_generic AS gik       ON gi_lGIK_ID          = gik.lgen_lKeyID
            LEFT  JOIN people_names  AS peepMC    ON ghm_lMailContactID  = peepMC.pe_lKeyID
            LEFT JOIN admin_users    AS ackUser   ON ackUser.us_lKeyID   = ghml_lAckByID
         WHERE 1 '.$this->strRptAckWhere($dteStart, $dteEnd, $bFindNonAck, $bIncludeSpon, $lACO, $enumAckType).'
         ORDER BY gi_lKeyID, ghm_lKeyID;';

      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }


   function lNumRecsInRecentReport(&$sRpt,
                                   $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->extractRecentRptOpts($sRpt, $dteStart, $dteEnd, $lDaysPast, $enumSort);

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      $sqlStr =
           'SELECT gi_lKeyID
            FROM gifts
               INNER JOIN people_names ON pe_lKeyID = gi_lForeignID
            WHERE NOT pe_bRetired '.$this->strRptRecentWhere($dteStart)."
            $strLimit;";

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strGiftRecentReportExport(&$sRpt,   $reportID,
                                      $bReport, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         return($this->strGiftRecentReport($sRpt, $lStartRec, $lRecsPerPage));
      }else {
         return($this->strGiftRecentExport($sRpt));
      }
   }

   private function strGiftRecentExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->extractRecentRptOpts($sRpt, $dteStart, $dteEnd, $lDaysPast, $enumSort);
      $sqlStr =
           'SELECT '.strExportFields_Gifts().'
            FROM gifts
               INNER JOIN gifts_campaigns             ON gi_lCampID             = gc_lKeyID
               INNER JOIN gifts_accounts              ON gc_lAcctID             = ga_lKeyID
               INNER JOIN people_names   AS donor     ON donor.pe_lKeyID        = gi_lForeignID
               INNER JOIN admin_aco                   ON gi_lACOID              = aco_lKeyID

               LEFT  JOIN lists_generic AS payCat     ON payCat.lgen_lKeyID     = gi_lPaymentType
               LEFT  JOIN lists_generic AS giftCat    ON giftCat.lgen_lKeyID    = gi_lMajorGiftCat
               LEFT  JOIN lists_generic AS giftAttrib ON giftAttrib.lgen_lKeyID = gi_lAttributedTo
               LEFT  JOIN sponsor                     ON sp_lKeyID              = gi_lSponsorID
               LEFT  JOIN people_names   AS spon      ON spon.pe_lKeyID         = sp_lForeignID
               LEFT  JOIN lists_generic  AS gik       ON gi_lGIK_ID             = gik.lgen_lKeyID
               LEFT  JOIN admin_users AS ackUser      ON gi_lAckByID            = ackUser.us_lKeyID
            WHERE NOT donor.pe_bRetired '.$this->strRptRecentWhere($dteStart).'
            ORDER BY gi_lKeyID;';

      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   private function strGiftRecentReport($sRpt, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->extractRecentRptOpts($sRpt, $dteStart, $dteEnd, $lDaysPast, $enumSort);

      $strOut = '<b>Recent Donations</b><br><br>';
      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      $sqlStr =
           'SELECT
               gi_lKeyID, gi_lSponsorID, gi_lCampID, gi_curAmnt,
               gi_lACOID,
               gi_dteDonation, 
               gi_bGIK, gi_lGIK_ID,
               gi_strCheckNum, gi_lPaymentType, gi_lMajorGiftCat,
               aco_strFlag, aco_strName,

               gi_bAck, gi_lAckByID, us_strFirstName, us_strLastName,
               gi_dteAck, 

               pe_lKeyID, pe_bBiz, pe_strFName, pe_strLName,
               gc_lKeyID, gc_strCampaign, gi_bGIK, lgen_strListItem AS strGIK,
               ga_lKeyID, ga_strAccount

            FROM gifts
               INNER JOIN gifts_campaigns ON gi_lCampID = gc_lKeyID
               INNER JOIN gifts_accounts  ON gc_lAcctID = ga_lKeyID
               INNER JOIN people_names    ON pe_lKeyID  = gi_lForeignID
               INNER JOIN admin_aco       ON gi_lACOID  = aco_lKeyID
               LEFT  JOIN lists_generic   ON gi_lGIK_ID = lgen_lKeyID
               LEFT  JOIN admin_users     ON gi_lAckByID= us_lKeyID
            WHERE NOT pe_bRetired '.$this->strRptRecentWhere($dteStart).' '
            .$this->strRptRecentOrder($sRpt, $enumSort)."
            $strLimit;";

      return($strOut.$this->strDonationViewTable($sqlStr, true, true));
   }

   public function strDonationGroupedDonorViewTable(&$sRpt, $sqlStr, $bShowACO, $strTitle=''){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $this->groupedGiftOpts($sRpt, $bAggregateDonor, $bShowAccts, $bShowCamps);
      if ($bShowACO) $clsACO = new madmin_aco();

      $query = $this->db->query($sqlStr);
      if ($query->num_rows()==0){
         return('<br><br><i>There are no donations that match your search criteria.</i>');
      }else {
         $strOut = '
            <table class="enpRptC" style="">'
               .$strTitle.'
               <tr>
                  <td class="enpRptLabel" style="width: 70px;">
                     # Gifts
                  </td>
                  <td class="enpRptLabel" style="width: 110px;">
                     Total Amount
                  </td>
                  <td class="enpRptLabel" style="width: 250px;">
                     Donor
                  </td>';

         if ($bShowAccts || $bShowCamps){
            $strOut .= '
                  <td class="enpRptLabel" style="width: 150px;">
                     Account
                  </td>';
         }
         if ($bShowCamps){
            $strOut .= '
                  <td class="enpRptLabel" style="width: 150px;">
                     Campaign
                  </td>';
         }

         $strOut .= '
                  <td class="enpRptLabel" style="width: 80px;">
                     Gift History
                  </td>
               </tr>';

         foreach ($query->result() as $row){
            $bBiz = $row->pe_bBiz;
            $lPeopleID = $row->pe_lKeyID;
            if ($bShowACO){
               $strFlagImg = '&nbsp;'.$clsACO->strFlagImage($row->aco_strFlag, $row->aco_strName);
            }else {
               $strFlagImg = '';
            }

            if ($bBiz){
               $strName = htmlspecialchars($row->pe_strLName);
               $strLink = strLinkView_BizRecord($lPeopleID, 'View business/organization record', true);
            }else {
               $strName = htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName);
               $strLink = strLinkView_PeopleRecord($lPeopleID, 'View people record', true);
            }

            $strOut .= '
                  <tr class="makeStripe">
                     <td class="enpRpt" style="width: 50px; text-align: center;">'
                        .number_format($row->lNumRec).'
                     </td>

                     <td class="enpRpt" style="text-align: right; padding-right: 5px;">'
                        .number_format($row->curSumAmnt , 2).$strFlagImg.'
                     </td>

                     <td class="enpRpt" style="text-align: left;">'
                        .$strLink.str_pad($lPeopleID, 5, '0', STR_PAD_LEFT).'&nbsp'.$strName.'
                     </td>';
            if ($bShowAccts || $bShowCamps){
               $strOut .= '
                     <td class="enpRpt" style="text-align: left;">'
                        .htmlspecialchars($row->ga_strAccount).'
                     </td>';
            }

            if ($bShowCamps){
               $strOut .= '
                     <td class="enpRpt" style="text-align: left;">'
                        .htmlspecialchars($row->gc_strCampaign).'
                     </td>';
            }

            $strOut .= '
                     <td class="enpRpt" style="text-align: center;" nowrap>'
                        .strLinkView_GiftsHistory($lPeopleID, 'View gift history', true).'&nbsp;'
                        .strLinkView_GiftsHistory($lPeopleID, 'View history', false).'
                     </td>

                  </tr>';
         }
         $strOut .= '</table>'."\n";

         return($strOut);
      }

   }

   public function strDonationViewTable($sqlStr, $bIncludeAck, $bShowACO, $strTitle=''){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      if ($bShowACO) $clsACO = new madmin_aco();

      $query = $this->db->query($sqlStr);
      if ($query->num_rows()==0){
         return('<br><br><i>There are no donations that match your search criteria.</i>');
      }else {
         $strOut = '
            <table class="enpRptC" style="width: 100%;">'
               .$strTitle.'
               <tr>
                  <td class="enpRptLabel" style="width: 70px;">
                     giftID
                  </td>
                  <td class="enpRptLabel" style="width: 80px;">
                     Amount
                  </td>
                  <td class="enpRptLabel" style="width: 90px;">
                     Date
                  </td>
                  <td class="enpRptLabel" style="width: 200px;">
                     Donor
                  </td>
                  <td class="enpRptLabel" style="width: 110px;">
                     Account
                  </td>
                  <td class="enpRptLabel" style="width: 130px;">
                     Campaign
                  </td>
                  <td class="enpRptLabel" style="width: 90px;">
                     In-Kind?
                  </td>';

         if ($bIncludeAck){
            $strOut .= '
                  <td class="enpRptLabel" style="">
                     Acknowledged?
                  </td>';
         }
         $strOut .= '
               </tr>';

         foreach ($query->result() as $row){
            $lGiftID = $row->gi_lKeyID;
            $bBiz = $row->pe_bBiz;
            $bSpon = !is_null($row->gi_lSponsorID);
            $lPeopleID = $row->pe_lKeyID;
            if ($bShowACO){
               $strFlagImg = '&nbsp;'.$clsACO->strFlagImage($row->aco_strFlag, $row->aco_strName);
            }else {
               $strFlagImg = '';
            }
            if ($bIncludeAck){
               if ($row->gi_bAck){
                  $strAlignAck = 'left';
                  $strAck = 'Yes - '.date($genumDateFormat, dteMySQLDate2Unix($row->gi_dteAck)).' by '
                            .htmlspecialchars($row->us_strFirstName.' '.$row->us_strLastName);
               }else {
                  $strAlignAck = 'center';
                  $strAck = '-';
               }
            }

            if ($bSpon){
               $strLinkViewRec = strLinkView_SponsorPayment($lGiftID, 'View sponsorship payment record', true);
               $sponLink = '&nbsp;'.strLinkView_Sponsorship($row->gi_lSponsorID, 'View sponsorship', true);
            }else {
               $strLinkViewRec = strLinkView_GiftsRecord($lGiftID, 'View gift record', true);
               $sponLink = '';
            }

            if ($row->gi_bGIK){
               $strAlign = 'left';
               $strGIK   = htmlspecialchars($row->strGIK);
            }else {
               $strAlign = 'center';
               $strGIK   = '-';
            }
            if ($bBiz){
               $strName = htmlspecialchars($row->pe_strLName);
               $strLink = strLinkView_BizRecord($lPeopleID, 'View business/organization record', true);
            }else {
               $strName = htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName);
               $strLink = strLinkView_PeopleRecord($lPeopleID, 'View people record', true);
            }

            $strOut .= '
                  <tr class="makeStripe">
                     <td class="enpRpt" style="width: 50px; text-align: center;">'
                        .$strLinkViewRec.'&nbsp;'
                        .str_pad($lGiftID, 5, '0', STR_PAD_LEFT).'
                     </td>

                     <td class="enpRpt" style="width: 60px; text-align: right; padding-right: 5px;">'
                        .number_format($row->gi_curAmnt, 2).$strFlagImg.'
                     </td>

                     <td class="enpRpt" style="text-align: left;">'
                        .date($genumDateFormat, dteMySQLDate2Unix($row->gi_dteDonation)).'
                     </td>

                     <td class="enpRpt" style="text-align: left;">'
                        .$strLink.str_pad($lPeopleID, 5, '0', STR_PAD_LEFT).'&nbsp'.$strName.'
                     </td>

                     <td class="enpRpt" style="text-align: left;">'
                        .htmlspecialchars($row->ga_strAccount).'
                     </td>

                     <td class="enpRpt" style="text-align: left;">'
                        .htmlspecialchars($row->gc_strCampaign).$sponLink.'
                     </td>

                     <td class="enpRpt" style="text-align: '.$strAlign.';">'
                        .$strGIK.'
                     </td>';
            if ($bIncludeAck){
               $strOut .= '
                     <td class="enpRpt" style="text-align: '.$strAlignAck.';">'
                        .$strAck.'
                     </td>';
            }
            $strOut .= '
                  </tr>';
         }
         $strOut .= '</table>'."\n";
         return($strOut);
      }

   }

   private function strRptRecentWhere($dteStart){   //, $dteEnd){
      $strWhere = " AND NOT gi_bRetired \n";
      if (!is_null($dteStart)){
         $strWhere .= "
                    AND gi_dteDonation >= ".strPrepDate($dteStart)." \n";
//                    AND gi_dteDonation BETWEEN ".strPrepDate($dteStart).' AND '.strPrepDateTime($dteEnd)."\n";
      }
      return($strWhere);
   }

   private function extractRecentRptOpts(
                            &$sRpt,
                            &$dteStart,     &$dteEnd, &$lDaysPast, &$enumSort){
      global $gdteNow;

      $lDaysPast = $sRpt->lDaysPast;
      if ($lDaysPast==0){  // 0 == All
         $dteEnd = $dteStart = null;
      }else {
         $dteEnd    = strtotime(date('m/d/Y 23:59:59'));
         $dteStart  = $gdteNow - (24*60*60*$lDaysPast);
      }
      $enumSort  = $sRpt->enumSort;
   }

   function lNumRecsInTimeFrameReport(&$sRpt,
                                       $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->extractTimeFrameRptOpts($sRpt,
                                     $lACO,     $bUseACO, $enumInc,
                                     $dteStart, $dteEnd,  $enumSort,
                                     $curMin,   $curMax,  $lYear);

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      $sqlStr =
           'SELECT '.$this->strRptTimeFrameSelectCount($sRpt).'
            FROM gifts '.$this->strRptTimeFrameInner($sRpt).'
            WHERE NOT pe_bRetired '.$this->strRptTimeFrameWhere($sRpt).' '
            .$this->strRptTimeFrameGroup($sRpt)."
            $strLimit;";
      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strGiftTimeFrameReportExport(
                                    &$sRpt,   $reportID,
                                    $bReport, $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         return($this->strGiftTimeFrameReport($sRpt, $lStartRec, $lRecsPerPage));
      }else {
         return($this->strGiftTimeFrameExport($sRpt));
      }
   }

   private function strGiftTimeFrameExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->groupedGiftOpts($sRpt, $bAggregateDonor, $bShowAccounts, $bShowCamps);
      if ($bAggregateDonor){
         $bGroupViaDonor = $sRpt->bAggregateDonor;
      }else {
         $bGroupViaDonor = false;
      }

      if ($bGroupViaDonor){
         $sqlStr = '
            SELECT '.strExportFields_GiftsDonorAggregate($bShowAccounts, $bShowCamps).'
            FROM gifts '.$this->strRptTimeFrameInner($sRpt).'
            WHERE NOT pe_bRetired '.$this->strRptTimeFrameWhere($sRpt)
               .$this->strRptTimeFrameGroup($sRpt).'
               ORDER BY pe_lKeyID;';

      }else {
         $sqlStr =
              'SELECT '.strExportFields_Gifts().'
               FROM gifts
                  INNER JOIN gifts_campaigns             ON gi_lCampID             = gc_lKeyID
                  INNER JOIN gifts_accounts              ON gc_lAcctID             = ga_lKeyID
                  INNER JOIN people_names   AS donor     ON donor.pe_lKeyID        = gi_lForeignID
                  INNER JOIN admin_aco                   ON gi_lACOID              = aco_lKeyID

                  LEFT  JOIN lists_generic AS payCat     ON payCat.lgen_lKeyID     = gi_lPaymentType
                  LEFT  JOIN lists_generic AS giftCat    ON giftCat.lgen_lKeyID    = gi_lMajorGiftCat
                  LEFT  JOIN lists_generic AS giftAttrib ON giftAttrib.lgen_lKeyID = gi_lAttributedTo
                  LEFT  JOIN sponsor                     ON sp_lKeyID              = gi_lSponsorID
                  LEFT  JOIN people_names   AS spon      ON spon.pe_lKeyID         = sp_lForeignID
                  LEFT  JOIN lists_generic  AS gik       ON gi_lGIK_ID             = gik.lgen_lKeyID
                  LEFT  JOIN admin_users AS ackUser      ON gi_lAckByID            = ackUser.us_lKeyID
               WHERE NOT donor.pe_bRetired '.$this->strRptTimeFrameWhere($sRpt).'
               ORDER BY gi_lKeyID;';
      }
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   private function strGiftTimeFrameReport(&$sRpt, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->groupedGiftOpts($sRpt, $bAggregateDonor, $bShowAccounts, $bShowCamps);

      if ($bAggregateDonor){
         $bGroupViaDonor = $sRpt->bAggregateDonor;
      }else {
         $bGroupViaDonor = false;
      }

      $this->extractTimeFrameRptOpts($sRpt,
                                     $lACO,     $bUseACO, $enumInc,
                                     $dteStart, $dteEnd,  $enumSort,
                                     $curMin,   $curMax,  $lYear);

      $strOut = $this->strReportTitle($sRpt); // 'Donation Report By Timeframe<br><br>';
      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      $sqlStr =
           'SELECT
               gi_lACOID,
               aco_strFlag, aco_strName,
               pe_lKeyID, pe_bBiz, pe_strFName, pe_strLName, '
               .$this->strRptTimeFrameSelect($sRpt).'
            FROM gifts '
               .$this->strRptTimeFrameInner($sRpt).'
            WHERE NOT pe_bRetired '.$this->strRptTimeFrameWhere($sRpt).' '
            .$this->strRptTimeFrameGroup($sRpt).' '
            .$this->strRptRecentOrder($sRpt, $enumSort)."
            $strLimit;";

      if ($bGroupViaDonor){
         return($strOut.$this->strDonationGroupedDonorViewTable($sRpt, $sqlStr, true));
      }else {
         return($strOut.$this->strDonationViewTable($sqlStr, true, true));
      }
   }


   private function extractTimeFrameRptOpts(
                            &$sRpt,
                            &$lACO,     &$bUseACO, &$enumInc,
                            &$dteStart, &$dteEnd,  &$enumSort,
                            &$curMin,   &$curMax,  &$lYear){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lACO     = $enumInc  =
      $dteStart = $dteEnd  = $enumSort =
      $curMin   = $curMax  = $lYear    = null;
      $bUseACO  = false;

      if (isset($sRpt->lACO)){
         $lACO      = $sRpt->lACO;
         $bUseACO   = $lACO > 0;
      }
      if (isset($sRpt->enumInc))  $enumInc   = $sRpt->enumInc;
      if (isset($sRpt->dteEnd))   $dteEnd    = strtotime(date('m/d/Y 23:59:59'), $sRpt->dteEnd);
      if (isset($sRpt->dteStart)) $dteStart  = $sRpt->dteStart;
      if (isset($sRpt->enumSort)) $enumSort  = $sRpt->enumSort;
      if (isset($sRpt->curMin))   $curMin    = $sRpt->curMin;
      if (isset($sRpt->curMax))   $curMax    = $sRpt->curMax;
      if (isset($sRpt->lYear))    $lYear     = $sRpt->lYear;
   }

   private function strReportTitle(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $strOut = '';
      $strLIStyle = 'margin-left: 20pt; line-height:12pt;';
      switch ($sRpt->rptName){
         case CENUM_REPORTNAME_GIFTTIMEFRAME:
            $strOut .= '<b>Donation Timeframe Report</b>
                        <ul style="list-style-type: square; display:inline; margin-left: 0; padding: 0pt;">';
            break;
         case CENUM_REPORTNAME_GIFTYEAREND:
            $strOut .= '<b>Donation Year-end Report</b>
                        <ul style="list-style-type: square; display:inline; margin-left: 0; padding: 0pt;">';
            break;
         case CENUM_REPORTNAME_GIFTACCOUNT:
            $cAcct = new maccts_camps;
            $cAcct->loadAccounts(true, true, $sRpt->acctIDs);
            $strOut .= '<b>Donation Account Report</b>
                        <ul style="list-style-type: square; display:inline; margin-left: 0; padding: 0pt;">
                           <li style="'.$strLIStyle.'"><b>Selected Accounts:</b>
                              <ul style="display:inline; margin-left: 0; padding: 0pt;">';
            foreach ($cAcct->accounts as $acct){
               $strOut .= '<li style="'.$strLIStyle.'">'.$acct->strSafeName.'</li>'."\n";
            }
            $strOut .= '</ul></li>';
            break;
         case CENUM_REPORTNAME_GIFTCAMP:
            $cCamp = new maccts_camps;
            $cCamp->loadCampaigns(false, false, null, true, $sRpt->campIDs);
            $strOut = '<b>Donation Campaign Report</b>
                        <ul style="list-style-type: square; display:inline; margin-left: 0; padding: 0pt;">
                           <li style="'.$strLIStyle.'"><b>Selected Campaigns:</b>
                              <ul style="display:inline; margin-left: 0; padding: 0pt;">';
            foreach ($cCamp->campaigns as $camp){
               $strOut .= '<li style="'.$strLIStyle.'">'.$camp->strAcctSafeName.': '.$camp->strSafeName.'</li>'."\n";
            }
            $strOut .= '</ul></li>';
            break;
         case CENUM_REPORTNAME_GIFTAGG:
            $strOut .= '<b>Donation Aggregate Report</b>
                        <ul style="list-style-type: square; display:inline; margin-left: 0; padding: 0pt;">';
            break;

         default: screamForHelp($sRpt->rptName.': invalid report type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);            break;
      }
      if (isset($sRpt->strDateRange)){
         $strOut .= '<li style="'.$strLIStyle.'"><b>Date range:</b> '.$sRpt->strDateRange.'</li>';
/*
         $strOut .= '<li style="'.$strLIStyle.'"><b>Date range:</b> '
                          .date($genumDateFormat, $sRpt->dteStart).' - '
                          .date($genumDateFormat, $sRpt->dteEnd).'
                     </li>';
*/
      }
      if (isset($sRpt->curMin)){
         $strOut .= '<li style="'.$strLIStyle.' "><b>Gift range:</b> '
                          .number_format($sRpt->curMin, 2).' - '
                          .number_format($sRpt->curMax, 2).'
                     </li>'."\n";
      }

      if (isset($sRpt->lACO)){
         $strOut .= '<li style="'.$strLIStyle.' "><b>Accounting Country:</b> ';
         if ($sRpt->lACO <= 0){
            $strOut .= 'All countries</li>';
         }else {
            $cACO = new madmin_aco;
            $cACO->loadCountries(false, false, true, $sRpt->lACO);
            $strOut .= $cACO->countries[0]->strName.' '.$cACO->countries[0]->strFlagImg.'</li>';
         }
      }

      if (isset($sRpt->enumInc)){
         $strOut .= '<li style="'.$strLIStyle.' "><b>Included Donations: </b>';
         switch ($sRpt->enumInc){
            case 'all' : $strOut .= 'All donations</li>'."\n";                       break;
            case 'gift': $strOut .= 'Gifts only (no sponsorship payments)</li>'."\n"; break;
            case 'spon': $strOut .= 'Only sponsorship payments</li>'."\n";           break;
            default: screamForHelp($sRpt->enumInc.': invalid include type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);            break;
         }
      }
      if (isset($sRpt->lYear)){
         $strOut .= '<li style="'.$strLIStyle.' "><b>Year: </b>'.$sRpt->lYear.'</li>'."\n";
      }

      if (isset($sRpt->bAggregateDonor)){
         $strOut .= '<li style="'.$strLIStyle.' "><b>Grouping: </b>'.($sRpt->bAggregateDonor ? 'By Donor' : 'Individual Donations').'</li>'."\n";
      }

      $strOut .= '</ul><br>'."\n";

      return($strOut);
   }

   private function strRptTimeFrameWhere($sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->extractTimeFrameRptOpts($sRpt,
                                     $lACO,     $bUseACO, $enumInc,
                                     $dteStart, $dteEnd,  $enumSort,
                                     $curMin,   $curMax,  $lYear);
      $strOut = ' AND NOT gi_bRetired '."\n";
      if (!is_null($dteStart)){
         $strOut .= '
                  AND gi_dteDonation BETWEEN '.strPrepDate($dteStart).' AND '.strPrepDateTime($dteEnd)."\n";
      }
      if (!is_null($curMin)){
         $strOut .= "
                  AND gi_curAmnt     BETWEEN $curMin AND $curMax  \n";
      }
      if ($bUseACO){
         $strOut .= " AND gi_lACOID=$lACO \n";
      }
      if (!is_null($enumInc)){
         switch ($enumInc){
            case 'all':
               break;
            case 'spon':
            case 'gift':
               $strOut .= ' AND gi_lSponsorID IS '.($enumInc=='spon' ? ' NOT ' : '')." NULL \n";
               break;
            default:
               screamForHelp($enumInc.': invalid report option<br>error on line <b>-- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }
      }
      if (!is_null($lYear)){
         $strOut .= " AND YEAR(gi_dteDonation)=$lYear \n";
      }

      if ($sRpt->rptName == CENUM_REPORTNAME_GIFTACCOUNT){
         $strOut .= ' AND ga_lKeyID IN ('.implode(',', $sRpt->acctIDs).') ';
      }elseif ($sRpt->rptName == CENUM_REPORTNAME_GIFTCAMP){
         $strOut .= ' AND gc_lKeyID IN ('.implode(',', $sRpt->campIDs).') ';
      }

      return($strOut);
   }




   private function groupedGiftOpts(&$sRpt, &$bAggregateDonor, &$bShowAccounts, &$bShowCamps){
      $bAggregateDonor = isset($sRpt->bAggregateDonor);
      $bShowAccounts   = isset($sRpt->lNumAccts);
      $bShowCamps      = isset($sRpt->lNumCamps);
   }

   private function strRptTimeFrameGroup(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->groupedGiftOpts($sRpt, $bAggregateDonor, $bShowAccounts, $bShowCamps);
      $strInner = '';
      if ($bAggregateDonor){
         if ($sRpt->bAggregateDonor){
            $strInner = "\n".'GROUP BY pe_lKeyID ';

            if ($bShowAccounts){
               $strInner .= ', ga_lKeyID ';
            }
            if ($bShowCamps){
               $strInner .= ', gc_lKeyID ';
            }
         }
      }
      return($strInner."\n");
   }

   private function strRptTimeFrameSelect(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->groupedGiftOpts($sRpt, $bAggregateDonor, $bShowAccounts, $bShowCamps);
      $strSelect = '
               gi_lKeyID,
               gi_curAmnt,
               gi_lSponsorID, gi_lCampID,
               gi_dteDonation, 
               gi_bGIK, gi_lGIK_ID,
               gi_strCheckNum, gi_lPaymentType, gi_lMajorGiftCat,
               gc_lKeyID, gc_strCampaign, gi_bGIK, lgen_strListItem AS strGIK,
               ga_lKeyID, ga_strAccount,
               gi_bAck, gi_lAckByID, us_strFirstName, us_strLastName,
               gi_dteAck 
            ';
      if ($bAggregateDonor){
         if ($sRpt->bAggregateDonor){
            $strSelect = ' COUNT(gi_lKeyID) AS lNumRec, SUM(gi_curAmnt) AS curSumAmnt ';
         }
         if ($bShowAccounts || $bShowCamps){
            $strSelect .= ', ga_strAccount ';
         }
         if ($bShowCamps){
            $strSelect .= ', gc_strCampaign ';
         }
      }
      return($strSelect);
   }

   private function strRptTimeFrameSelectCount(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->groupedGiftOpts($sRpt, $bAggregateDonor, $bShowAccounts, $bShowCamps);
      $strDefault = ' gi_lKeyID ';
      if ($bAggregateDonor){
         if ($sRpt->bAggregateDonor){
            return(' COUNT(gi_lKeyID) AS lNumRec ');
         }else {
            return($strDefault);
         }
      }else {
            return($strDefault);
      }
   }

   private function strRptTimeFrameInner(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->groupedGiftOpts($sRpt, $bAggregateDonor, $bShowAccounts, $bShowCamps);
      $strInner = '
               INNER JOIN gifts_campaigns ON gi_lCampID  = gc_lKeyID
               INNER JOIN gifts_accounts  ON gc_lAcctID  = ga_lKeyID
               INNER JOIN people_names    ON pe_lKeyID   = gi_lForeignID
               INNER JOIN admin_aco       ON gi_lACOID   = aco_lKeyID
               LEFT  JOIN lists_generic   ON gi_lGIK_ID  = lgen_lKeyID
               LEFT  JOIN admin_users     ON gi_lAckByID = us_lKeyID
      ';
      if ($bAggregateDonor){
         if ($sRpt->bAggregateDonor){
            $strInner = '
               INNER JOIN people_names    ON pe_lKeyID   = gi_lForeignID
               INNER JOIN admin_aco       ON gi_lACOID   = aco_lKeyID
            ';
            if ($bShowAccounts || $bShowCamps){
               $strInner = '
                  INNER JOIN gifts_campaigns ON gi_lCampID  = gc_lKeyID
                  INNER JOIN gifts_accounts  ON gc_lAcctID  = ga_lKeyID '.$strInner;
            }
         }
      }
      return($strInner);
   }


}

?>