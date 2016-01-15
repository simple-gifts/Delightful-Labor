<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012-2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
   cumulativeChargeHistory             ($lSponID)
   loadChargesViaACO                   (&$row, &$chargeList, &$curTotal)
   chargeHistoryTotals                 ($lSponID)

   curCumulativeSponsorshipViaPeopleID ()
   cumulativeSponPayGeneric            ($bViaPID, $lPID, $bViaSponID, $lSponID)
   curCumulativeSponVia_ACOID          ($bViaPID, $lPID, $bViaSponID, $lSponID, $lACOID)
   balanceSummary                      ()
   strBalanceSummaryHTMLTable          ()

   loadChargeRecord                    ()
   sponsorPaymentHistory               ($bViaSponID)

   removePaymentsChargesViaSponID      ($lSponID)

   loadAutoChargeViaMonthYear          ($lMonth, $lYear, &$clsACEntry)
   loadAutoChargeViaACID               ($lACID, &$clsACEntry)
   twelveMonthsOfAutoCharge            ()
   lCreateAutoChargeEntry              ($lMonth, $lYear)
   lApplyAutoCharges                   ($lMonth, $lYear)
   autoChargeHTMLSummary               ($clsACEntry)
   loadChargesViaACID                  ($lACID, &$clsChargeInfo)

   sponsorChargeHTMLSummary            ()

   updateChargeRec                     ()
   lAddNewChargeRec                    ()

   loadPaymentRecord                   ()
   lAddNewPayment                      ()
   updatePayment                       ()

   removeChargeRecord                  ($lChargeID)
   removePaymentRecord                 ($lPayID)

  ---------------------------------------------------------------------
      $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
---------------------------------------------------------------------*/


class msponsor_charge_pay extends CI_Model{

   public $bUseDateRange, $dteStart, $dteEnd, $sponPayCumulative, $chargesTotal,
          $lChargeID, $lPaymentID, $charges, $payHistory,
          $chargeRec, $paymentRec, $strChargeWhere, $strPayWhere, $strPayOrderExtra, $strLimit;

   public function __construct(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->lChargeID = $this->lPaymentID =
      $this->chargeRec = $this->paymentRec = null;
      $this->strChargeWhere = $this->strPayWhere = $this->strPayOrderExtra = $this->strLimit = '';
   }

   public function cumulativeChargeHistory($lSponID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if ($this->bUseDateRange){
         $strWhereDate = ' AND spc_dteCharge BETWEEN ('.strPrepDate($this->dteStart)
                                               .' AND '.strPrepDate($this->dteEnd).') ';
      }else {
         $strWhereDate = '';
      }
      $sqlStr =
         "SELECT
              spc_lKeyID, spc_lAutoGenID, spc_lAutoGenACOID,
              spc_curAutoGenCommitAmnt, spc_curChargeAmnt, spc_lACOID,
              spc_lSponsorshipID, spc_strNotes,
              spc_bRetired, spc_lOriginID, spc_lLastUpdateID,
              spc_dteCharge,  spcl_dteDateOfCharges,
              UNIX_TIMESTAMP(spc_dteOrigin)         AS dteOrigin,
              UNIX_TIMESTAMP(spc_dteLastUpdate)     AS dteLastUpdate,

              aco_strFlag, aco_strCurrencySymbol, aco_strName
          FROM sponsor_charges
             INNER JOIN admin_aco              ON aco_lKeyID     = spc_lACOID
             LEFT  JOIN sponsor_autocharge_log ON spc_lAutoGenID = spcl_lKeyID
          WHERE spc_lSponsorshipID=$lSponID
             AND NOT spc_bRetired
             $strWhereDate
          ORDER BY spc_dteCharge, spc_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumCharges = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->charges = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->charges[$idx] = new stdClass;

            $this->charges[$idx]->lKeyID                = $row->spc_lKeyID;
            $this->charges[$idx]->lAutoGenID            = $row->spc_lAutoGenID;
            $this->charges[$idx]->lAutoGenACOID         = $row->spc_lAutoGenACOID;
            $this->charges[$idx]->curAutoGenCommitAmnt  = $row->spc_curAutoGenCommitAmnt;
            $this->charges[$idx]->dteAutoCharge         = dteMySQLDate2Unix($row->spcl_dteDateOfCharges);
            $this->charges[$idx]->curChargeAmnt         = $row->spc_curChargeAmnt;
            $this->charges[$idx]->dteCharge             = dteMySQLDate2Unix($row->spc_dteCharge);
            $this->charges[$idx]->lSponsorshipID        = $row->spc_lSponsorshipID;
            $this->charges[$idx]->strNotes              = $row->spc_strNotes;
            $this->charges[$idx]->bRetired              = $row->spc_bRetired;
            $this->charges[$idx]->lOriginID             = $row->spc_lOriginID;
            $this->charges[$idx]->lLastUpdateID         = $row->spc_lLastUpdateID;
            $this->charges[$idx]->dteOrigin             = $row->dteOrigin;
            $this->charges[$idx]->dteLastUpdate         = $row->dteLastUpdate;

            $this->charges[$idx]->lACOID                = $row->spc_lACOID;
            $this->charges[$idx]->strACOFlagImg         =
                         '<img src="'.DL_IMAGEPATH.'/flags/'.$row->aco_strFlag
                                   .'" alt="flag icon" title="'.$row->aco_strName.'">';
            $this->charges[$idx]->strACOCurSym          = $row->aco_strCurrencySymbol;
            $this->charges[$idx]->strACOName            = $row->aco_strName;

            ++$idx;
         }
      }
   }

   public function loadChargesViaACO(&$row, &$chargeList, &$curTotal){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $chargeList->lKeyID                = $row->spc_lKeyID;
      $chargeList->lAutoGenID            = $row->spc_lAutoGenID;
      $chargeList->lAutoGenACOID         = $row->spc_lAutoGenACOID;
      $chargeList->curAutoGenCommitAmnt  = $row->spc_curAutoGenCommitAmnt;
      $chargeList->curChargeAmnt         = $row->spc_curChargeAmnt;
      $chargeList->lACOID                = $row->spc_lACOID;
      $chargeList->dteCharge             = $row->dteCharge;
      $chargeList->lSponsorshipID        = $row->spc_lSponsorshipID;
      $chargeList->strNotes              = $row->spc_strNotes;
      $chargeList->bRetired              = $row->spc_bRetired;
      $chargeList->lOriginID             = $row->spc_lOriginID;
      $chargeList->lLastUpdateID         = $row->spc_lLastUpdateID;
      $chargeList->dteOrigin             = $row->dteOrigin;
      $chargeList->dteLastUpdate         = $row->dteLastUpdate;

      $curTotal += $chargeList->curChargeAmnt;
   }

   public function chargeHistoryTotals($lSponID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $clsACO = new madmin_aco();
      $clsACO->loadCountries(true, false, false, null);

      if ($this->bUseDateRange){
         $strWhereDate = ' AND spc_dteCharge BETWEEN ('.strPrepDate($this->dteStart)
                                               .' AND '.strPrepDate($this->dteEnd).') ';
      }else {
         $strWhereDate = '';
      }

      $this->lNumChargesTotal = 0;
      $this->chargesTotal     = array();

      $sqlStr =
         "SELECT spc_lACOID, SUM(spc_curChargeAmnt) AS curTotCharge, COUNT(*) AS lNumRecs,
             aco_strFlag, aco_strCurrencySymbol, aco_strName
          FROM sponsor_charges
             INNER JOIN admin_aco ON aco_lKeyID  = spc_lACOID
          WHERE
             NOT spc_bRetired
             AND spc_lSponsorshipID=$lSponID
             $strWhereDate
          GROUP BY spc_lACOID
          ORDER BY aco_bDefault, aco_strName, aco_lKeyID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      $this->lNumChargesTotal = 0;
      if ($numRows > 0) {
         $idx = 0;
         foreach ($query->result() as $row) {
            if ((integer)$row->curTotCharge > 0.0){

               $this->chargesTotal[$this->lNumChargesTotal] = new stdClass;
               $this->chargesTotal[$this->lNumChargesTotal]->lNumCharges = $numRows;
               $this->chargesTotal[$this->lNumChargesTotal]->curTotal    = $row->curTotCharge;

               $this->chargesTotal[$this->lNumChargesTotal]->lACOID      = $row->spc_lACOID;
               $this->chargesTotal[$this->lNumChargesTotal]->strACOFlagImg =
                            '<img src="'.DL_IMAGEPATH.'/flags/'.$row->aco_strFlag
                                      .'" alt="flag icon" title="'.$row->aco_strName.'">';
               $this->chargesTotal[$this->lNumChargesTotal]->strACOCurSym   = $row->aco_strCurrencySymbol;
               $this->chargesTotal[$this->lNumChargesTotal]->strACOName     = $row->aco_strName;

               ++$this->lNumChargesTotal;
            }
            ++$idx;
         }
      }
   }

   public function cumulativeSponsorshipViaPeopleID($clsACO, $lPID) {
   //---------------------------------------------------------------------
   // caller needs to set:
   //   $this->bUseDateRange
   //   $this->dteStartDate
   //   $this->dteEndDate
   //---------------------------------------------------------------------
      $this->cumulativeSponPayGeneric($clsACO, true, $lPID, false, null);
   }

   public function cumulativeSponsorshipViaSponID($clsACO, $lSponID) {
   //---------------------------------------------------------------------
   // caller needs to set:
   //   $this->bUseDateRange
   //   $this->dteStartDate
   //   $this->dteEndDate
   //---------------------------------------------------------------------
      $this->cumulativeSponPayGeneric($clsACO, false, null, true, $lSponID);
   }

   public function cumulativeSponPayGeneric($clsACO, $bViaPID, $lPID, $bViaSponID, $lSponID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsACO->loadCountries(true, false, false, null);

      $this->lNumSponPayCumulative = 0;
      $this->sponPayCumulative     = array();
      foreach($clsACO->countries as $clsCountry){
         $lACOID = $clsCountry->lKeyID;
         $curCum = $this->curCumulativeSponVia_ACOID($bViaPID, $lPID, $bViaSponID, $lSponID, $lACOID);

         if ($curCum != 0.0){
            $this->sponPayCumulative[$this->lNumSponPayCumulative] = new stdClass;
            $objC = $this->sponPayCumulative[$this->lNumSponPayCumulative];
            $objC->curCumulative = $curCum;
            $objC->lACOID        = $lACOID;
            $objC->strFlagImg    = $clsCountry->strFlagImg;
            $objC->strCurSymbol  = $clsCountry->strCurrencySymbol;
            $objC->strACOName    = $clsCountry->strName;

            ++$this->lNumSponPayCumulative;
         }
      }
   }

   public function curCumulativeSponVia_ACOID($bViaPID, $lPID, $bViaSponID, $lSponID, $lACOID) {
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bViaPID){
         $strWhereVia = " AND gi_lForeignID=$lPID";
      }elseif ($bViaSponID){
         $strWhereVia = " AND gi_lSponsorID=$lSponID";
      }else {
         $strWhereVia = '';
      }

      if ($this->bUseDateRange) {
         $strWhereDate =
             ' AND (gi_dteDonation BETWEEN '.strPrepDate($this->dteStart).'
                                       AND '.strPrepDateTime($this->dteEnd).') ';
      }else {
         $strWhereDate = '';
      }

      $sqlStr =
           "SELECT SUM(gi_curAmnt) AS curSum
            FROM gifts
               INNER JOIN sponsor ON gi_lSponsorID=sp_lKeyID
            WHERE
               gi_lACOID=$lACOID
               $strWhereDate
               $strWhereVia
               AND NOT gi_bRetired
               AND NOT sp_bRetired;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
         $vHold = $row->curSum;
         if (is_null($vHold)) {
            return(0.0);
         }else {
            return($vHold);
         }
      }
   }

   public function curCumulativeChargeVia_ACOID($lSponID, $lACOID) {
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_null($lSponID)){
         $strWhereSID = '';
      }else {
         $strWhereSID = " AND spc_lSponsorshipID=$lSponID ";
      }

      if ($this->bUseDateRange) {
         $strWhereDate =
             ' AND (spc_dteCharge BETWEEN '.strPrepDate($this->dteStart).'
                                      AND '.strPrepDateTime($this->dteEnd).') ';
      }else {
         $strWhereDate = '';
      }

      $sqlStr =
           "SELECT SUM(spc_curChargeAmnt) AS curCharges
            FROM sponsor_charges
            WHERE 1
               $strWhereSID $strWhereDate
               AND spc_lACOID=$lACOID
               AND NOT spc_bRetired;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
         $vHold = $row->curCharges;
         if (is_null($vHold)) {
            return(0.0);
         }else {
            return($vHold);
         }
      }
   }

   public function balanceSummary(){
   //---------------------------------------------------------------------
   // assumes the caller has called
   //    $clsSCP->chargeHistoryTotals($lSponID);
   //    $clsSCP->cumulativeSponsorshipViaSponID($lSponID)
   //---------------------------------------------------------------------

      $sqlStr =
           "CREATE TEMPORARY TABLE tmp_spon_balance (
              spb_lKeyID            int(11) NOT NULL auto_increment,
              spb_lACOID            int(11) NOT NULL,
              spb_curCharge         decimal(10,2) NOT NULL default '0.00',
              spb_curPayment        decimal(10,2) NOT NULL default '0.00',
              spb_strFlag           varchar(255)  NOT NULL default '',
              spb_strCurrencySymbol varchar(10)   NOT NULL default '',
              spb_strACOName        varchar(25)   NOT NULL default '',

              PRIMARY KEY  (spb_lKeyID),
              UNIQUE KEY spb_lACOID (spb_lACOID),  KEY spb_strACOName (spb_strACOName)
            ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

      $this->db->query($sqlStr);

         //----------------------
         // insert charge totals
         //----------------------
      foreach($this->chargesTotal as $clsACOCharge){

         $sqlStr =
            'INSERT INTO tmp_spon_balance
             SET
              spb_lACOID            = '.$clsACOCharge->lACOID.',
              spb_curCharge         = '.$clsACOCharge->curTotal.',
              spb_strFlag           = '.strPrepStr($clsACOCharge->strACOFlagImg).',
              spb_strCurrencySymbol = '.strPrepStr($clsACOCharge->strACOCurSym).',
              spb_strACOName        = '.strPrepStr($clsACOCharge->strACOName).';';

         $this->db->query($sqlStr);
      }

         //------------------------
         // insert payment totals
         //------------------------
      foreach($this->sponPayCumulative as $clsACOPay){
         $sqlStr =
            'INSERT INTO tmp_spon_balance
             SET
              spb_lACOID            = '.$clsACOPay->lACOID.',
              spb_curPayment        = '.$clsACOPay->curCumulative.',
              spb_strFlag           = '.strPrepStr($clsACOPay->strFlagImg).',
              spb_strCurrencySymbol = '.strPrepStr($clsACOPay->strCurSymbol).',
              spb_strACOName        = '.strPrepStr($clsACOPay->strACOName).'
              ON DUPLICATE KEY UPDATE spb_curPayment='.$clsACOPay->curCumulative.';';
         $this->db->query($sqlStr);
      }

         //------------------------
         // build balance class
         //------------------------
      $sqlStr =
         'SELECT
              spb_lACOID, spb_curCharge, spb_curPayment, spb_strFlag,
              spb_strCurrencySymbol, spb_strACOName
           FROM tmp_spon_balance
           ORDER BY spb_strACOName;';

      $query = $this->db->query($sqlStr);
      $this->lNumBalanceSummary = $numRows = $query->num_rows();
      $this->balanceSummary = array();
      if ($numRows > 0){
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->balanceSummary[$idx] = new stdClass;
            $this->balanceSummary[$idx]->lACOID            = (int)$row->spb_lACOID;
            $this->balanceSummary[$idx]->curChargeTot      = (float)$row->spb_curCharge;
            $this->balanceSummary[$idx]->curPaymentTot     = (float)$row->spb_curPayment;
            $this->balanceSummary[$idx]->strFlag           = $row->spb_strFlag;
            $this->balanceSummary[$idx]->strCurrencySymbol = $row->spb_strCurrencySymbol;
            $this->balanceSummary[$idx]->strACOName        = $row->spb_strACOName;

            ++$idx;
         }
      }

      $sqlStr = 'DROP TABLE tmp_spon_balance;';
      $this->db->query($sqlStr);
   }

   public function strBalanceSummaryHTMLTable(){
   //---------------------------------------------------------------------
   // assumes the caller has called
   //    $clsSCP->chargeHistoryTotals($lSponID);
   //    $clsSCP->cumulativeSponsorshipViaSponID($lSponID);
   //    $clsSCP->balanceSummary();
   //---------------------------------------------------------------------
      $strTable = '';
      if ($this->lNumBalanceSummary == 0){
         $strTable = 'n/a';
      }else {
         $strTable .= '
              <table class="enpRpt">
                 <tr>
                    <td class="enpRptLabel" style="text-align: center;">
                       ACO
                    </td>
                    <td class="enpRptLabel" style="text-align: center;">
                       Charges
                    </td>
                    <td class="enpRptLabel" style="text-align: center;">
                       Payments
                    </td>
                    <td class="enpRptLabel" style="text-align: center;">
                       Balance Due
                    </td>
                 </tr>';
         foreach ($this->balanceSummary as $clsBal){
            $curBalance = $clsBal->curChargeTot - $clsBal->curPaymentTot;
            $strTable .= '
                 <tr>
                    <td class="enpRpt" style="text-align: center;">'
                       .$clsBal->strFlag.'
                    </td>
                    <td class="enpRpt" style="text-align: right;">'
                       .number_format($clsBal->curChargeTot, 2).'
                    </td>
                    <td class="enpRpt" style="text-align: right;">'
                       .number_format($clsBal->curPaymentTot, 2).'
                    </td>
                    <td class="enpRpt" style="text-align: right;" id="sponBalDue">'
                       .number_format($curBalance, 2).'
                    </td>
                 </tr>';
         }
         $strTable .= '</table>';
      }

      return($strTable);
   }

   public function loadChargeRecordViaCRID($lChargeID){
      $this->strChargeWhere = " spc_lKeyID=$lChargeID ";
      $this->loadChargeRecords();
   }

   public function loadChargeRecords(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsACO = new madmin_aco();
      $this->chargeRec = array();

      $sqlStr =
           "SELECT
               spc_lKeyID, spc_lAutoGenID, spc_lAutoGenACOID, spc_curAutoGenCommitAmnt,
               spc_curChargeAmnt, spc_lACOID,
               spc_lSponsorshipID,
               spc_strNotes, spc_bRetired,
               spc_lOriginID, spc_lLastUpdateID,
               spc_dteCharge, spcl_dteDateOfCharges,
               UNIX_TIMESTAMP(spc_dteOrigin)     AS dteOrigin,
               UNIX_TIMESTAMP(spc_dteLastUpdate) AS dteLastUpdate,
               aco_strFlag, aco_strName, aco_strCurrencySymbol,

               sp_lForeignID, sp_bInactive, sp_lClientID,
               pe_bBiz, pe_strFName, pe_strLName,
               cr_strFName, cr_strLName,
               cr_lLocationID, cl_strLocation,

               usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
               usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName
            FROM sponsor_charges
               INNER JOIN admin_aco              ON spc_lACOID       = aco_lKeyID
               INNER JOIN sponsor                ON sp_lKeyID        = spc_lSponsorshipID
               INNER JOIN people_names           ON sp_lForeignID    = pe_lKeyID
               LEFT  JOIN client_records         ON sp_lClientID     = cr_lKeyID
               LEFT  JOIN client_location        ON cr_lLocationID   = cl_lKeyID
               LEFT  JOIN sponsor_autocharge_log ON spc_lAutoGenID   = spcl_lKeyID
               INNER JOIN admin_users AS usersC  ON spc_lOriginID    = usersC.us_lKeyID
               INNER JOIN admin_users AS usersL  ON spc_lLastUpdateID= usersL.us_lKeyID
            WHERE NOT spc_bRetired AND $this->strChargeWhere;";

      $query = $this->db->query($sqlStr);
      $this->lNumChargeRecs = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->chargeRec[0] = new stdClass;
         $this->chargeRec[0]->lKeyID               =
         $this->chargeRec[0]->lAutoGenID           =
         $this->chargeRec[0]->lAutoGenACOID        =
         $this->chargeRec[0]->curAutoGenCommitAmnt =
         $this->chargeRec[0]->lSponsorshipID       =
         $this->chargeRec[0]->strNotes             =
         $this->chargeRec[0]->bRetired             =
         $this->chargeRec[0]->lOriginID            =
         $this->chargeRec[0]->lLastUpdateID        =
         $this->chargeRec[0]->dteCharge            =
         $this->chargeRec[0]->dteOrigin            =
         $this->chargeRec[0]->dteLastUpdate        =
         $this->chargeRec[0]->curChargeAmnt        =
         $this->chargeRec[0]->lACOID               =
         $this->chargeRec[0]->strACO               =
         $this->chargeRec[0]->strCurSymbol         =
         $this->chargeRec[0]->strFlag              =
         $this->chargeRec[0]->strFlagImage         =
         $this->chargeRec[0]->lForeignID           =
         $this->chargeRec[0]->bInactive            =
         $this->chargeRec[0]->lClientID            =
         $this->chargeRec[0]->bBiz                 =
         $this->chargeRec[0]->strSponFName         =
         $this->chargeRec[0]->strSponLName         =
         $this->chargeRec[0]->strSponSafeNameFL    =
         $this->chargeRec[0]->strClientFName       =
         $this->chargeRec[0]->strClientLName       =
         $this->chargeRec[0]->strClientSafeNameFL  =
         $this->chargeRec[0]->lLocationID          =
         $this->chargeRec[0]->strLocation          =
         $this->chargeRec[0]->strStaffCFName       =
         $this->chargeRec[0]->strStaffCLName       =
         $this->chargeRec[0]->strStaffLFName       =
         $this->chargeRec[0]->strStaffLLName       = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->chargeRec[$idx] = new stdClass;
            $this->chargeRec[$idx]->lKeyID               = $row->spc_lKeyID;
            $this->chargeRec[$idx]->lAutoGenID           = $row->spc_lAutoGenID;
            $this->chargeRec[$idx]->lAutoGenACOID        = $row->spc_lAutoGenACOID;
            $this->chargeRec[$idx]->curAutoGenCommitAmnt = $row->spc_curAutoGenCommitAmnt;
            $this->chargeRec[$idx]->dteAutoCharge        = dteMySQLDate2Unix($row->spcl_dteDateOfCharges);
//               $this->chargeRec[$idx]->lACOID               = $row->spc_lACOID;
            $this->chargeRec[$idx]->lSponsorshipID       = $row->spc_lSponsorshipID;
            $this->chargeRec[$idx]->strNotes             = $row->spc_strNotes;
            $this->chargeRec[$idx]->bRetired             = $row->spc_bRetired;
            $this->chargeRec[$idx]->lOriginID            = $row->spc_lOriginID;
            $this->chargeRec[$idx]->lLastUpdateID        = $row->spc_lLastUpdateID;
            $this->chargeRec[$idx]->dteCharge            = dteMySQLDate2Unix($row->spc_dteCharge);
            $this->chargeRec[$idx]->dteOrigin            = $row->dteOrigin;
            $this->chargeRec[$idx]->dteLastUpdate        = $row->dteLastUpdate;

            $this->chargeRec[$idx]->curChargeAmnt        = $row->spc_curChargeAmnt;
            $this->chargeRec[$idx]->lACOID               = $row->spc_lACOID;
            $this->chargeRec[$idx]->strACO               = $row->aco_strName;
            $this->chargeRec[$idx]->strCurSymbol         = $row->aco_strCurrencySymbol;
            $this->chargeRec[$idx]->strFlag              = $row->aco_strFlag;
            $this->chargeRec[$idx]->strFlagImage         = $clsACO->strFlagImage($row->aco_strFlag, $row->aco_strName);

            $this->chargeRec[$idx]->lForeignID           = $row->sp_lForeignID;
            $this->chargeRec[$idx]->bInactive            = $row->sp_bInactive;
            $this->chargeRec[$idx]->lClientID            = $row->sp_lClientID;
            $this->chargeRec[$idx]->bBiz                 = $row->pe_bBiz;
            $this->chargeRec[$idx]->strSponFName         = $row->pe_strFName;
            $this->chargeRec[$idx]->strSponLName         = $row->pe_strLName;
            if ($this->chargeRec[$idx]->bBiz){
               $this->chargeRec[$idx]->strSponSafeNameFL = htmlspecialchars($row->pe_strLName);
            }else {
               $this->chargeRec[$idx]->strSponSafeNameFL = htmlspecialchars($row->pe_strFName.' '.$row->pe_strLName);
            }
            $this->chargeRec[$idx]->strClientFName       = $row->cr_strFName;
            $this->chargeRec[$idx]->strClientLName       = $row->cr_strLName;
            $this->chargeRec[$idx]->strClientSafeNameFL  = htmlspecialchars($row->cr_strFName.' '.$row->cr_strLName);
            $this->chargeRec[$idx]->lLocationID          = $row->cr_lLocationID;
            $this->chargeRec[$idx]->strLocation          = $row->cl_strLocation;

            $this->chargeRec[$idx]->strStaffCFName       = $row->strCFName;
            $this->chargeRec[$idx]->strStaffCLName       = $row->strCLName;
            $this->chargeRec[$idx]->strStaffLFName       = $row->strLFName;
            $this->chargeRec[$idx]->strStaffLLName       = $row->strLLName;

            ++$idx;
         }
      }
   }

   public function sponsorPaymentHistory($bViaSponID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsACO = new madmin_aco();

      if ($bViaSponID){
         if (is_null($this->lSponID)) screamForHelp('CLASS NOT INITIALIZED<br></b>error on <b>line: </b>'.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
         $strWhere = " gi_lSponsorID = $this->lSponID ";
      }else {
         if (is_null($this->lFID))    screamForHelp('CLASS NOT INITIALIZED<br></b>error on <b>line: </b>'.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
         $strWhere = " gi_lForeignID = $this->lFID ";
      }

      if ($this->bUseDateRange) {
         $strWhereDate =
             ' AND (sd_dteDonation BETWEEN '.strPrepDate($this->dteStartDate).'
                                       AND '.strPrepDateTime($this->dteEndDate).') ';
      }else {
         $strWhereDate = '';
      }

      $this->paymentHistory = array();

      $sqlStr =
           "SELECT
               gi_lKeyID,

               gi_lForeignID, gi_lSponsorID, gi_curAmnt, gi_lACOID,
               gi_dteDonation,
               gi_strCheckNum,
               gi_lPaymentType, listPayType.lgen_strListItem AS strPaymentType,
               gi_bRetired,

               gi_lOriginID, gi_lLastUpdateID,
               UNIX_TIMESTAMP(gi_dteOrigin)    AS dteOrigin,
               UNIX_TIMESTAMP(gi_dteLastUpdate) AS dteLastUpdate,

               aco_strFlag, aco_strName, aco_strCurrencySymbol,

               pDon.pe_bBiz  AS bDonorBiz, pDon.pe_strFName  AS strDonorFName, pDon.pe_strLName  AS strDonorLName,

               usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
               usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName

            FROM gifts
               INNER JOIN admin_aco               ON gi_lACOID         = aco_lKeyID
               INNER JOIN people_names AS pDon    ON gi_lForeignID     = pDon.pe_lKeyID

               INNER JOIN admin_users AS usersC   ON gi_lOriginID      = usersC.us_lKeyID
               INNER JOIN admin_users AS usersL   ON gi_lLastUpdateID  = usersL.us_lKeyID

               LEFT  JOIN lists_generic AS listPayType ON gi_lPaymentType  = listPayType.lgen_lKeyID
            WHERE $strWhere $strWhereDate AND NOT gi_bRetired;";

      $query = $this->db->query($sqlStr);
      $this->lPayTot = $this->lPayHistoryACOCnt = $numRows = $query->num_rows();

      if ($numRows==0){
         $this->payHistory = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->payHistory[$idx] = new stdClass;

            $this->payHistory[$idx]->lKeyID        = $row->gi_lKeyID;
            $this->payHistory[$idx]->lPayerID      = $row->gi_lForeignID;
            $this->payHistory[$idx]->lSponsorID    = $row->gi_lSponsorID;
            $this->payHistory[$idx]->curPayment    = $row->gi_curAmnt;
            $this->payHistory[$idx]->lACOID        = $row->gi_lACOID;
            $this->payHistory[$idx]->dtePayment    = dteMySQLDate2Unix($row->gi_dteDonation);
            $this->payHistory[$idx]->bDonorBiz     = $row->bDonorBiz;
            $this->payHistory[$idx]->strPayerFName = $row->strDonorFName;
            $this->payHistory[$idx]->strPayerLName = $row->strDonorLName;
            $this->payHistory[$idx]->strPayerSafeNameFL =
                           htmlspecialchars($row->strDonorFName.' '.$row->strDonorLName);

            $this->payHistory[$idx]->strACO        = $row->aco_strName;
            $this->payHistory[$idx]->strCurSymbol  = $row->aco_strCurrencySymbol;
            $this->payHistory[$idx]->strFlag       = $row->aco_strFlag;
            $this->payHistory[$idx]->strFlagImage  = $clsACO->strFlagImage($row->aco_strFlag, $row->aco_strName);

            ++$idx;
         }
      }
   }

   public function removePaymentsChargesViaSponID($lSponID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

         //---------------------
         // remove charges
         //---------------------
      $sqlStr =
        "UPDATE sponsor_charges
         SET
            spc_bRetired=1,
            spc_lLastUpdateID=$glUserID
         WHERE spc_lSponsorshipID=$lSponID;";
      $this->db->query($sqlStr);

         //---------------------
         // remove payments
         //---------------------
      $sqlStr =
        "UPDATE gifts
         SET
            gi_bRetired=1,
            gi_lLastUpdateID=$glUserID
         WHERE gi_lSponsorID=$lSponID;";
      $this->db->query($sqlStr);
   }

   public function loadAutoChargeViaMonthYear($lMonth, $lYear, &$clsACEntry){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->loadAutoChargeGeneric(true, $lMonth, $lYear, false, null, $clsACEntry);
   }

   public function loadAutoChargeViaACID($lACID, &$clsACEntry){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->loadAutoChargeGeneric(false, null, null, true, $lACID, $clsACEntry);
   }

   private function loadAutoChargeGeneric($bViaMonthYear, $lMonth, $lYear, $bViaACID, $lACID, &$clsACEntry){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bViaMonthYear){
         $strWhere = " AND MONTH(spcl_dteDateOfCharges)=$lMonth
                       AND YEAR(spcl_dteDateOfCharges)=$lYear ";
      }elseif ($bViaACID){
         $strWhere = " AND spcl_lKeyID=$lACID ";
      }

      $sqlStr =
        "SELECT
            spcl_dteDateOfCharges,
            MONTH(spcl_dteDateOfCharges) AS lMonthCharge,
            YEAR(spcl_dteDateOfCharges)  AS lYearCharge,
            spcl_lKeyID,
            UNIX_TIMESTAMP(spcl_dteOrigin) AS dteOrigin,
            spcl_lOriginID, us_strFirstName, us_strLastName
         FROM sponsor_autocharge_log
            INNER JOIN admin_users ON spcl_lOriginID=us_lKeyID
         WHERE 1 $strWhere
         LIMIT 0,1;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0) {
         if ($bViaMonthYear){
            $clsACEntry->lMonth = $lMonth;
            $clsACEntry->lYear  = $lYear;
         }else {
            $clsACEntry->lMonth =
            $clsACEntry->lYear  = null;
         }
         $clsACEntry->lKeyID      =
         $clsACEntry->dteCharge   =
         $clsACEntry->dteOrigin   =
         $clsACEntry->lOriginID   =
         $clsACEntry->strUserSafe = null;
      }else {
         $row = $query->row();
         $clsACEntry->lKeyID      = $row->spcl_lKeyID;
         $clsACEntry->lMonth      = $row->lMonthCharge;
         $clsACEntry->lYear       = $row->lYearCharge;
         $clsACEntry->dteCharge   = dteMySQLDate2Unix($row->spcl_dteDateOfCharges);
         $clsACEntry->dteOrigin   = $row->dteOrigin;
         $clsACEntry->lOriginID   = $row->spcl_lOriginID;
         $clsACEntry->strUserSafe = htmlspecialchars($row->us_strFirstName.' '.$row->us_strLastName);
      }
   }

   public function twelveMonthsOfAutoCharge(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow;

      $this->autoCharges12Mo = array();

      numsMoDaYr($gdteNow, $lMonth, $lDay, $lYear);

      for ($idx=0; $idx<12; ++$idx){
         $this->autoCharges12Mo[$idx] = new stdClass;
         $this->loadAutoChargeViaMonthYear($lMonth, $lYear, $this->autoCharges12Mo[$idx]);
         --$lMonth;
         if ($lMonth <= 0){
            $lMonth = 12;
            --$lYear;
         }
      }
   }

   private function lCreateAutoChargeEntry($lMonth, $lYear){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $strMySQLDate = strMoDaYr2MySQLDate($lMonth, 1, $lYear);

      $sqlStr =
           "INSERT INTO sponsor_autocharge_log
            SET
               spcl_dteDateOfCharges='$strMySQLDate',
               spcl_lOriginID=$glUserID;";

      $query = $this->db->query($sqlStr);

      return($this->db->insert_id());
   }

   public function lApplyAutoCharges($lMonth, $lYear){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $lAutoChargeID = $this->lCreateAutoChargeEntry($lMonth, $lYear);
      $mysqlAutoDate = strPrepDate(strtotime($lMonth.'/1/'.$lYear));

         //-----------------------------
         // load all active sponsors
         //-----------------------------
      $sqlStr =
        "INSERT INTO sponsor_charges (
            spc_lAutoGenID,
            spc_lAutoGenACOID,
            spc_curAutoGenCommitAmnt,

            spc_curChargeAmnt,
            spc_lACOID,
            spc_dteCharge,

            spc_lSponsorshipID,
            spc_strNotes,
            spc_lOriginID,

            spc_lLastUpdateID,
            spc_dteOrigin
         )

            SELECT
               $lAutoChargeID AS lAutoChargeID,
               sp_lCommitmentACO,
               sp_curCommitment,

               sp_curCommitment,
               sp_lCommitmentACO,
               $mysqlAutoDate AS dteCharge,

               sp_lKeyID,
               'Auto-generated charge' AS strNote,
               $glUserID AS lOriginID,

               $glUserID AS lLastUpdateID,
               NOW() AS dteOrigin

            FROM sponsor
            WHERE
               NOT sp_bInactive
               AND sp_dteStartMoYr <= $mysqlAutoDate
               AND NOT sp_bRetired;";
      $this->db->query($sqlStr);
      return($lAutoChargeID);
   }

   public function autoChargeHTMLSummary($clsACEntry){
   //-----------------------------------------------------------------------
   // assumes user has called $clsSCP->loadAutoChargeViaACID($lACID, $clsACEntry);
   //-----------------------------------------------------------------------
      global $gdteNow, $genumDateFormat;

      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $clsRpt->setEntrySummary();

      $lACID = $clsACEntry->lKeyID;
      $strOut =
          $clsRpt->openReport('', '')

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('AutoCharge ID:')
         .$clsRpt->writeCell (str_pad($lACID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Month of Charge:')
         .$clsRpt->writeCell ($clsACEntry->lMonth.'/'.$clsACEntry->lYear)
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Charges Created:')
         .$clsRpt->writeCell (date($genumDateFormat.' H:i:s', $clsACEntry->dteOrigin)
                             .' by '.$clsACEntry->strUserSafe)
         .$clsRpt->closeRow  ()

         .$clsRpt->closeReport('');
      return($strOut);
   }

   public function loadChargesViaACID($lACID, &$autoChargeInfo, &$lNumAutoCharges){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $clsACO = new madmin_aco();

      $autoChargeInfo = array();
      $sqlStr =
        "SELECT
            spc_lKeyID, spc_lAutoGenACOID, spc_curAutoGenCommitAmnt, spc_curChargeAmnt,
            spc_lACOID, spc_lSponsorshipID, spc_bRetired,
            aco_strFlag, aco_strName, aco_strCurrencySymbol,
            sp_lForeignID, sp_lClientID,
            sp_lSponsorProgramID, sc_strProgram,
            pe_bBiz, pe_strFName, pe_strLName,
            cr_strFName, cr_strLName, cr_lLocationID,
            cl_strLocation

         FROM sponsor_charges
            INNER JOIN sponsor                    ON spc_lSponsorshipID   = sp_lKeyID
            INNER JOIN lists_sponsorship_programs ON sp_lSponsorProgramID = sc_lKeyID
            INNER JOIN people_names               ON sp_lForeignID        = pe_lKeyID
            INNER JOIN admin_aco                  ON spc_lACOID           = aco_lKeyID
            LEFT  JOIN client_records             ON sp_lClientID         = cr_lKeyID
            LEFT  JOIN client_location            ON cr_lLocationID       = cl_lKeyID
         WHERE spc_lAutoGenID=$lACID
         ORDER BY pe_strLName, pe_strFName, sp_lForeignID, spc_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumAutoCharges = $numRows = $query->num_rows();

      if ($numRows==0) {
         $autoChargeInfo[0] = new stdClass;
         $autoChargeInfo[0]->lKeyID               =
         $autoChargeInfo[0]->lAutoGenACOID        =
         $autoChargeInfo[0]->curAutoGenCommitAmnt =
         $autoChargeInfo[0]->curChargeAmnt        =
         $autoChargeInfo[0]->lACOID               =
         $autoChargeInfo[0]->lSponsorshipID       =
         $autoChargeInfo[0]->bChargeRetired       =
         $autoChargeInfo[0]->lForeignID           =
         $autoChargeInfo[0]->lClientID            =
         $autoChargeInfo[0]->lSponsorProgramID    =
         $autoChargeInfo[0]->strProgram           =
         $autoChargeInfo[0]->bBiz                 =
         $autoChargeInfo[0]->strSponFName         =
         $autoChargeInfo[0]->strSponLName         =
         $autoChargeInfo[0]->strSponSafeNameFL    =
         $autoChargeInfo[0]->strClientFName       =
         $autoChargeInfo[0]->strClientLName       =
         $autoChargeInfo[0]->lLocationID          =
         $autoChargeInfo[0]->strLocation          = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $autoChargeInfo[$idx] = new stdClass;

            $autoChargeInfo[$idx]->lKeyID               = $row->spc_lKeyID;
            $autoChargeInfo[$idx]->lAutoGenACOID        = $row->spc_lAutoGenACOID;
            $autoChargeInfo[$idx]->curAutoGenCommitAmnt = $row->spc_curAutoGenCommitAmnt;

            $autoChargeInfo[$idx]->curChargeAmnt        = $row->spc_curChargeAmnt;
            $autoChargeInfo[$idx]->lACOID               = $row->spc_lACOID;
            $autoChargeInfo[$idx]->strACO               = $row->aco_strName;
            $autoChargeInfo[$idx]->strCurSymbol         = $row->aco_strCurrencySymbol;
            $autoChargeInfo[$idx]->strFlag              = $row->aco_strFlag;
            $autoChargeInfo[$idx]->strFlagImage         = $clsACO->strFlagImage($row->aco_strFlag, $row->aco_strName);

            $autoChargeInfo[$idx]->lSponsorshipID       = $row->spc_lSponsorshipID;
            $autoChargeInfo[$idx]->bChargeRetired       = $row->spc_bRetired;
            $autoChargeInfo[$idx]->lForeignID           = $row->sp_lForeignID;
            $autoChargeInfo[$idx]->lClientID            = $row->sp_lClientID;
            $autoChargeInfo[$idx]->lSponsorProgramID    = $row->sp_lSponsorProgramID;
            $autoChargeInfo[$idx]->strProgram           = $row->sc_strProgram;
            $autoChargeInfo[$idx]->bBiz                 = $row->pe_bBiz;
            $autoChargeInfo[$idx]->strSponFName         = $row->pe_strFName;
            $autoChargeInfo[$idx]->strSponLName         = $row->pe_strLName;
            if ($autoChargeInfo[$idx]->bBiz){
               $autoChargeInfo[$idx]->strSponSafeNameFL = htmlspecialchars($row->pe_strLName);
            }else {
               $autoChargeInfo[$idx]->strSponSafeNameFL = htmlspecialchars($row->pe_strFName.' '.$row->pe_strLName);
            }
            $autoChargeInfo[$idx]->strClientFName       = $row->cr_strFName;
            $autoChargeInfo[$idx]->strClientSafeNameFL  = htmlspecialchars($row->cr_strFName.' '.$row->cr_strLName);
            $autoChargeInfo[$idx]->strClientLName       = $row->cr_strLName;
            $autoChargeInfo[$idx]->lLocationID          = $row->cr_lLocationID;
            $autoChargeInfo[$idx]->strLocation          = $row->cl_strLocation;

            ++$idx;
         }
      }
   }

   public function sponsorChargeHTMLSummary(){
   //-----------------------------------------------------------------------
   // assumes user has called $clsSCP->loadChargeRecord()
   //-----------------------------------------------------------------------
      global $gdteNow, $genumDateFormat;

      $params = array('enumStyle' => 'terse');
      $clsRpt  = new generic_rpt($params);
      $clsRpt->setBreadCrumbs();

      $cRec = $this->chargeRec;

      $bBiz = $cRec->bBiz;
      if ($bBiz){
         $strLinkPeopleBiz = strLinkView_Biz($cRec->lForeignID, 'View business record', true);
      }else {
         $strLinkPeopleBiz = strLinkView_People($cRec->lForeignID, 'View people record', true);
      }

      $lSponID = $cRec->lSponsorshipID;
      $clsRpt->openReport('', '');

      $clsRpt->openRow   (false);
      $clsRpt->writeLabel(' Sponsor:');
      $clsRpt->writeCell ($strLinkPeopleBiz.' '.str_pad($cRec->lForeignID, 5, '0', STR_PAD_LEFT)
                          .' '.$cRec->strSponSafeNameFL);
      $clsRpt->closeRow  ();

      $clsRpt->openRow   (false);
      $clsRpt->writeLabel('Sponsor ID:');
      $clsRpt->writeCell (strLinkView_Sponsorship($lSponID, 'View sponsorship', true)
                         .' '.str_pad($lSponID, 5, '0', STR_PAD_LEFT));
      $clsRpt->closeRow  ();

      if (is_null($cRec->lClientID)){
         $strClient = '<i>not set</i>';
      }else {
         $strClient = strLinkView_Client($cRec->lClientID, 'View client record', true).' '
                  .str_pad($cRec->lClientID, 5, '0', STR_PAD_LEFT).' '.$cRec->strClientSafeNameFL;
      }

      $clsRpt->openRow   (false);
      $clsRpt->writeLabel('Client:');
      $clsRpt->writeCell ($strClient);
      $clsRpt->closeRow  ();

      $clsRpt->openRow   (false);
      $clsRpt->writeLabel('Status:');
      $clsRpt->writeCell (($cRec->bInactive ? 'Inactive' : 'Active'));
      $clsRpt->closeRow  ();

      $clsRpt->openRow   (false);
      $clsRpt->writeLabel('Charge ID:');
      $clsRpt->writeCell (str_pad($cRec->lKeyID, 5, '0', STR_PAD_LEFT));
      $clsRpt->closeRow  ();

      $clsRpt->openRow   (false);
      $clsRpt->writeLabel('Amount:');
      $clsRpt->writeCell ($cRec->strCurSymbol.' '.number_format($cRec->curChargeAmnt, 2).' '.$cRec->strFlagImage);
      $clsRpt->closeRow  ();

      $clsRpt->openRow   (false);
      $clsRpt->writeLabel('Date of Charge:');
      $clsRpt->writeCell (date($genumDateFormat, $cRec->dteCharge));
      $clsRpt->closeRow  ();

      $clsRpt->closeReport(true);
   }

   public function updateChargeRec($lChargeID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
         'UPDATE sponsor_charges
          SET '.$this->sqlCommonChargeAddUpdate().'
          WHERE spc_lKeyID='.$lChargeID.';';

      $query = $this->db->query($sqlStr);
   }

   public function lAddNewChargeRec(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
           'INSERT INTO sponsor_charges
            SET '.$this->sqlCommonChargeAddUpdate().',
               spc_lAutoGenID           = NULL,
               spc_lAutoGenACOID        = NULL,
               spc_curAutoGenCommitAmnt = NULL,
               spc_lSponsorshipID       = '.$this->chargeRec[0]->lSponsorshipID.',
               spc_strNotes             = "",
               spc_bRetired             = 0,
               spc_lOriginID            = '.$glUserID.',
               spc_dteOrigin            = NOW();';

      $query = $this->db->query($sqlStr);
      $this->chargeRec[0]->lKeyID = $lChargeID = $this->db->insert_id();
      return($lChargeID);
   }

   private function sqlCommonChargeAddUpdate(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      return(
          'spc_curChargeAmnt = '.number_format($this->chargeRec[0]->curChargeAmnt, 2).',
           spc_lACOID        = '.(integer)$this->chargeRec[0]->lACOID.',
           spc_dteCharge     = '.strPrepDate($this->chargeRec[0]->dteCharge).",
           spc_lLastUpdateID = $glUserID,
           spc_dteLastUpdate=NOW() ");
   }

   public function loadPayRecordViaPayID($lPayID, $strOrder=''){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_array($lPayID)){
         $this->strPayWhere = " AND gi_lKeyID IN ( ".implode(',', $lPayID)." ) ";
      }else {
         $this->strPayWhere = " AND gi_lKeyID=$lPayID ";
      }
      if ($strOrder==''){
         $strOrder = ' ORDER BY gi_lKeyID ';
      }
      $this->strPayOrderExtra = $strOrder;
      $this->loadPaymentRecords();
   }

   public function loadPaymentRecords(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsACO = new madmin_aco();
      $this->paymentRec = array();

      $sqlStr =
           "SELECT
               gi_lKeyID,

               gi_lForeignID, gi_lSponsorID, gi_curAmnt, gi_lACOID,
               gi_dteDonation,
               gi_strCheckNum, gi_strImportID,
               gi_lPaymentType, listPayType.lgen_strListItem AS strPaymentType,
               gi_bRetired,

               gi_lOriginID, gi_lLastUpdateID,
               UNIX_TIMESTAMP(gi_dteOrigin)     AS dteOrigin,
               UNIX_TIMESTAMP(gi_dteLastUpdate) AS dteLastUpdate,

               aco_strFlag, aco_strName, aco_strCurrencySymbol,

               sp_lForeignID, sp_bInactive, sp_lClientID,
               pSpon.pe_bBiz AS bSponBiz,  pSpon.pe_strFName AS strSponFName,  pSpon.pe_strLName AS strSponLName,
               pDon.pe_bBiz  AS bDonorBiz, pDon.pe_strFName  AS strDonorFName, pDon.pe_strLName  AS strDonorLName,
               cr_strFName, cr_strLName,
               cr_lLocationID, cl_strLocation,

               usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
               usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName

            FROM gifts
               INNER JOIN admin_aco               ON gi_lACOID         = aco_lKeyID
               INNER JOIN sponsor                 ON sp_lKeyID         = gi_lSponsorID
               INNER JOIN people_names AS pSpon   ON sp_lForeignID     = pSpon.pe_lKeyID
               INNER JOIN people_names AS pDon    ON gi_lForeignID     = pDon.pe_lKeyID

               INNER JOIN admin_users AS usersC   ON gi_lOriginID      = usersC.us_lKeyID
               INNER JOIN admin_users AS usersL   ON gi_lLastUpdateID  = usersL.us_lKeyID

               LEFT  JOIN lists_generic AS listPayType ON gi_lPaymentType  = listPayType.lgen_lKeyID
               LEFT  JOIN client_records               ON sp_lClientID     = cr_lKeyID
               LEFT  JOIN client_location              ON cr_lLocationID   = cl_lKeyID
            WHERE NOT gi_bRetired $this->strPayWhere
            $this->strPayOrderExtra
            $this->strLimit;";

      $query = $this->db->query($sqlStr);
      $this->lNumPayRecs = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->paymentRec[0] = new stdClass;
         $payRec = &$this->paymentRec[0];

         $payRec->lKeyID               =
         $payRec->curPaymentAmnt       =
         $payRec->dtePayment           =
         $payRec->strCheckNum          =
         $payRec->lPaymentType         =
         $payRec->strPaymentType       =
         $payRec->strImportID          =
         $payRec->bRetired             =

            //---------------
            // ACO fields
            //---------------
         $payRec->lACOID               =
         $payRec->strACO               =
         $payRec->strCurSymbol         =
         $payRec->strFlag              =
         $payRec->strFlagImage         = null;

            //--------------------------
            // Sponsor/client fields
            //--------------------------
         $payRec->lSponsorshipID       =
         $payRec->lSponPeopleID        =
         $payRec->bInactive            =
         $payRec->lClientID            =
         $payRec->bSponBiz             =
         $payRec->strSponFName         =
         $payRec->strSponLName         =
         $payRec->strSponSafeNameFL    =
         $payRec->strClientFName       =
         $payRec->strClientLName       =
         $payRec->strClientSafeNameFL  =
         $payRec->lLocationID          =
         $payRec->strLocation          = null;

            //--------------------------
            // Donor fields
            //--------------------------
         $payRec->lDonorID             =
         $payRec->bDonorBiz            =
         $payRec->strDonorFName        =
         $payRec->strDonorLName        =
         $payRec->strDonorSafeNameFL   =

            //--------------------------
            // Record info
            //--------------------------
         $payRec->lOriginID            =
         $payRec->lLastUpdateID        =
         $payRec->dteOrigin            =
         $payRec->dteLastUpdate        =

         $payRec->strStaffCFName       =
         $payRec->strStaffCLName       =
         $payRec->strStaffLFName       =
         $payRec->strStaffLLName       = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {

            $this->paymentRec[$idx] = new stdClass;
            $payRec = &$this->paymentRec[$idx];
            $payRec->lKeyID               = (int)$row->gi_lKeyID;
            $payRec->curPaymentAmnt       = (float)$row->gi_curAmnt;
            $payRec->dtePayment           = dteMySQLDate2Unix($row->gi_dteDonation);
            $payRec->strCheckNum          = $row->gi_strCheckNum;
            $payRec->lPaymentType         = $row->gi_lPaymentType;
            $payRec->strPaymentType       = $row->strPaymentType;
            $payRec->strImportID          = $row->gi_strImportID;
            $payRec->bRetired             = $row->gi_bRetired;

               //---------------
               // ACO fields
               //---------------
            $payRec->lACOID               = (int)$row->gi_lACOID;
            $payRec->strACO               = $row->aco_strName;
            $payRec->strCurSymbol         = $row->aco_strCurrencySymbol;
            $payRec->strFlag              = $row->aco_strFlag;
            $payRec->strFlagImage         = $clsACO->strFlagImage($row->aco_strFlag, $row->aco_strName);

               //--------------------------
               // Sponsor/client fields
               //--------------------------
            $payRec->lSponsorshipID       = $row->gi_lSponsorID;
            $payRec->lSponPeopleID        = $row->sp_lForeignID;
            $payRec->bInactive            = $row->sp_bInactive;
            $payRec->lClientID            = $row->sp_lClientID;
            $payRec->bSponBiz             = $row->bSponBiz;
            $payRec->strSponFName         = $row->strSponFName;
            $payRec->strSponLName         = $row->strSponLName;
            if ($payRec->bSponBiz){
               $payRec->strSponSafeNameFL = $payRec->strSponSafeNameLF = htmlspecialchars($row->strSponLName);
            }else {
               $payRec->strSponSafeNameFL = htmlspecialchars($row->strSponFName.' '.$row->strSponLName);
               $payRec->strSponSafeNameLF = htmlspecialchars($row->strSponLName.', '.$row->strSponFName);
            }
            $payRec->strClientFName       = $row->cr_strFName;
            $payRec->strClientLName       = $row->cr_strLName;
            $payRec->strClientSafeNameFL  = htmlspecialchars($row->cr_strFName.' '.$row->cr_strLName);
            $payRec->lLocationID          = $row->cr_lLocationID;
            $payRec->strLocation          = $row->cl_strLocation;

               //--------------------------
               // Donor fields
               //--------------------------
            $payRec->lDonorID             = $row->gi_lForeignID;
            $payRec->bDonorBiz            = $row->bDonorBiz;
            $payRec->strDonorFName        = $row->strDonorFName;
            $payRec->strDonorLName        = $row->strDonorLName;
            if ($payRec->bSponBiz){
               $payRec->strDonorSafeNameFL = $payRec->strDonorSafeNameLF = htmlspecialchars($row->strSponLName);
            }else {
               $payRec->strDonorSafeNameFL = htmlspecialchars($row->strDonorFName.' '.$row->strDonorLName);
               $payRec->strDonorSafeNameLF = htmlspecialchars($row->strDonorLName.', '.$row->strDonorFName);
            }

               //--------------------------
               // Record info
               //--------------------------
            $payRec->lOriginID            = $row->gi_lOriginID;
            $payRec->lLastUpdateID        = $row->gi_lLastUpdateID;
            $payRec->dteOrigin            = $row->dteOrigin;
            $payRec->dteLastUpdate        = $row->dteLastUpdate;

            $payRec->strStaffCFName       = $row->strCFName;
            $payRec->strStaffCLName       = $row->strCLName;
            $payRec->strStaffLFName       = $row->strLFName;
            $payRec->strStaffLLName       = $row->strLLName;

            ++$idx;
         }
      }
   }

   public function lSponCampID(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsAC = new maccts_camps;
      return($clsAC->lCampIDViaCampName(
                         $clsAC->lAcctIDViaAcctName('Sponsorship'), 'Sponsorship Payment'));
   }

   public function lAddNewPayment(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $lSponCampID = $this->lSponCampID();
      $sqlStr =
        'INSERT INTO gifts
         SET '.$this->strSQLPayCommon().',
            gi_lForeignID    ='.$this->paymentRec[0]->lDonorID.',
            gi_lSponsorID    ='.$this->paymentRec[0]->lSponsorshipID.",
            gi_lCampID       = $lSponCampID,
            gi_lAttributedTo = NULL,
            gi_bGIK          = 0,
            gi_bHon          = 0,
            gi_bMem          = 0,
            gi_lGIK_ID       = null,
            gi_strNotes      = 'Sponsorship payment',
            gi_lMajorGiftCat = null,
            gi_bAck          = 0,
            gi_bRetired      = 0,
            gi_lOriginID     = $glUserID,
            gi_dteOrigin     = NOW();";

      $query = $this->db->query($sqlStr);
      return($this->paymentRec[0]->lKeyID = $this->db->insert_id());
   }

   public function updatePayment($lPayID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        'UPDATE gifts
         SET '.$this->strSQLPayCommon().'
         WHERE gi_lKeyID = '.$lPayID.';';

      $this->db->query($sqlStr);
   }

   private function strSQLPayCommon(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      return(
        'gi_curAmnt       = '.number_format($this->paymentRec[0]->curPaymentAmnt, 2, '.', '').',
         gi_strCheckNum   = '.strPrepStr($this->paymentRec[0]->strCheckNum).',
         gi_lPaymentType  = '.(integer)$this->paymentRec[0]->lPaymentType.',
         gi_lACOID        = '.(integer)$this->paymentRec[0]->lACOID.',
         gi_dteDonation   = '.strPrepDate($this->paymentRec[0]->dtePayment).",
         gi_lLastUpdateID = $glUserID,
         gi_dteLastUpdate = NOW() ");
   }

   public function removeChargeRecord($lChargeID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        "UPDATE sponsor_charges
         SET
            spc_bRetired      = 1,
            spc_lLastUpdateID = $glUserID,
            spc_dteLastUpdate = NOW()
         WHERE spc_lKeyID=$lChargeID;";
      $this->db->query($sqlStr);
   }

   public function removePaymentRecord($lPayID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        "UPDATE gifts
         SET
            gi_bRetired      = 1,
            gi_lLastUpdateID = $glUserID,
            gi_dteLastUpdate = NOW()
         WHERE gi_lKeyID=$lPayID;";
      $this->db->query($sqlStr);
   }

   public function mostRecentPayment($lSponID, $bViaTimestamp){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strPayWhere = " AND gi_lSponsorID=$lSponID ";
      $this->strLimit = ' LIMIT 0,1 ';
      $this->strPayOrderExtra = ' ORDER BY '.($bViaTimestamp ? 'gi_dteOrigin' : 'gi_dteDonation')
                   .' DESC, gi_lKeyID DESC ';
      $this->loadPaymentRecords();
   }

   /*-----------------------------------------------------------------
                                R E P O R T S
   -----------------------------------------------------------------*/
   public function strSponsorPastDueReport(
                                  &$cLocal, &$sRpt, $reportID,
                                  $bReport, &$displayData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lMonthsPastDue   = $sRpt->lMonthsPastDue;
      $lACOID           = $sRpt->lACOID;
      $bIncludeInactive = $sRpt->bIncludeInactive;
      $cLocal->clsSpon->listOfSponsors($bIncludeInactive);
      $lNumSponsors = count($cLocal->clsSpon->sponsorList);
      $cLocal->clsACO->loadCountries(false, true, true, $lACOID);

      if ($bReport){

         $displayData['cACO']             = &$cLocal->clsACO->countries[0];
         $displayData['lMonthsPastDue']   = $lMonthsPastDue;
         $displayData['bIncludeInactive'] = $bIncludeInactive;

         $lNumPastDue = 0; $pastDue = array();
         if ($lNumSponsors > 0){
            foreach ($cLocal->clsSpon->sponsorList as $sponRec){
               $lSponID = $sponRec->lSponID;

               if ($sponRec->lCommitmentACO == $lACOID){
                  $curCommit   = $sponRec->curCommitment;
                  $curPayments = $cLocal->clsSCP->curCumulativeSponVia_ACOID(false, null, true, $lSponID, $lACOID);
                  $curCharges  = $cLocal->clsSCP->curCumulativeChargeVia_ACOID($lSponID, $lACOID);
                  if (($curCharges - $curPayments) >= ($lMonthsPastDue*$curCommit)){
                     $cLocal->clsSpon->sponsorInfoViaID($lSponID);
                     $sRec = $cLocal->clsSpon->sponInfo[0];

                     $pastDue[$lNumPastDue] = new stdClass;
                     $pd = &$pastDue[$lNumPastDue];
                     $pd->lSponID             = $lSponID;
                     $pd->curCharges          = $curCharges;
                     $pd->curPayments         = $curPayments;
                     $pd->curCommit           = $curCommit;
                     $pd->bInactive           = $sponRec->bInactive;

                     $pd->lForeignID          =   $sRec->lForeignID;
                     $pd->strSponsorFName     =   $sRec->strSponsorFName;
                     $pd->strSponsorLName     =   $sRec->strSponsorLName;
                     $pd->bSponBiz            =   $sRec->bSponBiz;
                     $pd->strSponSafeNameFL   =   $sRec->strSponSafeNameFL;
                     $pd->strSponSafeNameLF   =   $sRec->strSponSafeNameLF;
                     $pd->lClientID           =   $sRec->lClientID;
                     $pd->lSponsorProgID      =   $sRec->lSponsorProgID;
                     $pd->strSponProgram      =   $sRec->strSponProgram;
                     $pd->strClientFName      =   $sRec->strClientFName;
                     $pd->strClientLName      =   $sRec->strClientLName;
                     $pd->strClientSafeNameFL =   $sRec->strClientSafeNameFL;
                     $pd->strClientSafeNameLF =   $sRec->strClientSafeNameLF;

                     ++$lNumPastDue;
                  }
               }
            }
         }
         $displayData['lTotRecs'] = $displayData['lNumPastDue'] = $lNumPastDue;
         $displayData['pastDue']  = &$pastDue;
      }else {
         $rptExport = '';

            // create temporary table to hold foreign ID of report results
         $sqlStr = 'DROP TABLE IF EXISTS tmpPastDue;';
         $this->db->query($sqlStr);
         $sqlStr =
              'CREATE TEMPORARY TABLE tmpPastDue (  -- TEMPORARY
                 pd_lKeyID        int(11) NOT NULL AUTO_INCREMENT,
                 pd_lSponID       int(11) NOT NULL DEFAULT \'0\',
                 pd_curCommitment decimal(10,2) NOT NULL DEFAULT \'0.00\',
                 pd_curCharges    decimal(10,2) NOT NULL DEFAULT \'0.00\',
                 pd_curPayments   decimal(10,2) NOT NULL DEFAULT \'0.00\',
                 pd_curBalanceDue decimal(10,2) NOT NULL DEFAULT \'0.00\',
                 PRIMARY KEY (pd_lKeyID),
                 KEY pd_lSponID (pd_lSponID)
               ) ENGINE=MyISAM;';

         $this->db->query($sqlStr);

         if ($lNumSponsors > 0){
            foreach ($cLocal->clsSpon->sponsorList as $sponRec){
               $lSponID = $sponRec->lSponID;

               if ($sponRec->lCommitmentACO == $lACOID){
                  $curCommit   = $sponRec->curCommitment;
                  $curPayments = $cLocal->clsSCP->curCumulativeSponVia_ACOID(false, null, true, $lSponID, $lACOID);
                  $curCharges  = $cLocal->clsSCP->curCumulativeChargeVia_ACOID($lSponID, $lACOID);
                  $curBalance  = $curCharges - $curPayments;
                  if ($curBalance >= ($lMonthsPastDue*$curCommit)){
                     $sqlStr =
                        "INSERT INTO tmpPastDue
                         SET pd_lSponID       = $lSponID,
                             pd_curCommitment = $curCommit,
                             pd_curCharges    = $curCharges,
                             pd_curPayments   = $curPayments,
                             pd_curBalanceDue = $curBalance;";
                     $this->db->query($sqlStr);
                  }
               }
            }
            $sqlStr =
               'SELECT
                   pd_curCharges    AS `Charges`,
                   pd_curPayments   AS `Payments`,
                   pd_curBalanceDue AS `Balance Due`, '
                   .strExportFields_Sponsor().'
                FROM tmpPastDue
                   INNER JOIN sponsor                    ON sp_lKeyID            = pd_lKeyID
                   INNER JOIN people_names AS sponpeep   ON sponpeep.pe_lKeyID   = sp_lForeignID
                   INNER JOIN admin_aco AS commitACO     ON commitACO.aco_lKeyID = sp_lCommitmentACO
                   INNER JOIN lists_sponsorship_programs ON sc_lKeyID            = sp_lSponsorProgramID
                   LEFT  JOIN client_records             ON cr_lKeyID            = sp_lClientID
                   LEFT  JOIN client_location            ON cr_lLocationID       = cl_lKeyID
                   LEFT  JOIN lists_client_vocab         ON cv_lKeyID            = cr_lVocID
                   LEFT  JOIN people_names AS honpeep    ON honpeep.pe_lKeyID    = sp_lHonoreeID
                   LEFT  JOIN lists_generic AS atab      ON sp_lAttributedTo     = lgen_lKeyID
                ORDER BY sponpeep.pe_strLName, sponpeep.pe_strFName, sponpeep.pe_strMName, sp_lKeyID;';

            $query = $this->db->query($sqlStr);
            $rptExport = $this->dbutil->csv_from_result($query);
         }
         return($rptExport);
      }
   }


}


?>