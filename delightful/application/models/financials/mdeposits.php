<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2013 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('financials/mdeposits', 'clsDeposits');
---------------------------------------------------------------------


---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class mdeposits extends CI_Model{

   public $strWhereExtra, $strLimit, $strOrder,
          $deposits, $lNumDeposits,
          $depositSum, $lNumDepositSummary;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->strWhereExtra = $this->strLimit = $this->strOrder = '';
   }

   function loadDepositReports(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->deposits = array();
      $clsACO = new madmin_aco();

      $strOrder = $this->strOrder;
      if ($strOrder == ''){
         $strOrder = ' dl_dteEnd DESC, dl_dteStart DESC, dl_lKeyID ';
      }
      $sqlStr =
           "SELECT
               dl_lKeyID,
               dl_lACOID, dl_dteStart, dl_dteEnd, 
               dl_strBank, dl_strAccount, dl_strNotes,  dl_bRetired,
               dl_lOriginID, dl_lLastUpdateID,
               usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
               usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,

               aco_strFlag, aco_strCurrencySymbol, aco_strName,

               UNIX_TIMESTAMP(dl_dteOrigin)     AS dteOrigin,
               UNIX_TIMESTAMP(dl_dteLastUpdate) AS dteLastUpdate

            FROM deposit_log
               INNER JOIN admin_users AS usersC ON dl_lOriginID     = usersC.us_lKeyID
               INNER JOIN admin_users AS usersL ON dl_lLastUpdateID = usersL.us_lKeyID
               INNER JOIN admin_aco             ON dl_lACOID        = aco_lKeyID

            WHERE NOT dl_bRetired
               $this->strWhereExtra
            ORDER BY $strOrder
            $this->strLimit;";

      $query = $this->db->query($sqlStr);
      $this->lNumDeposits = $lNumDeposits = $query->num_rows();
      if ($lNumDeposits == 0){
         $this->deposits[0] = new stdClass;
         $deposit = &$this->deposits[0];
         $deposit->lKeyID            =
         $deposit->dteStart          =
         $deposit->dteEnd            =
         $deposit->strBank           =
         $deposit->strAccount        =
         $deposit->strNotes          =

         $deposit->lACOID            =
         $deposit->strFlag           =
         $deposit->strFlagImg        =
         $deposit->strCurrencySymbol =
         $deposit->strCountryName    =

         $deposit->bRetired          =
         $deposit->lOriginID         =
         $deposit->lLastUpdateID     =
         $deposit->strCFName         =
         $deposit->strCLName         =
         $deposit->strLFName         =
         $deposit->strLLName         =
         $deposit->dteOrigin         =
         $deposit->dteLastUpdate     = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->deposits[$idx] = new stdClass;
            $deposit = &$this->deposits[$idx];
            $deposit->lKeyID            = $row->dl_lKeyID;
            $deposit->dteStart          = dteMySQLDate2Unix($row->dl_dteStart);
            $deposit->dteEnd            = dteMySQLDate2Unix($row->dl_dteEnd);
            $deposit->strBank           = $row->dl_strBank;
            $deposit->strAccount        = $row->dl_strAccount;
            $deposit->strNotes          = $row->dl_strNotes;

            $deposit->lACOID            = $row->dl_lACOID;
            $deposit->strFlag           = $row->aco_strFlag;
            $deposit->strFlagImg        = $clsACO->strFlagImage($row->aco_strFlag, $row->aco_strName);
            $deposit->strCurrencySymbol = $row->aco_strCurrencySymbol;
            $deposit->strCountryName    = $row->aco_strName;

            $deposit->bRetired          = $row->dl_bRetired;
            $deposit->lOriginID         = $row->dl_lOriginID;
            $deposit->lLastUpdateID     = $row->dl_lLastUpdateID;
            $deposit->strCFName         = $row->strCFName;
            $deposit->strCLName         = $row->strCLName;
            $deposit->strLFName         = $row->strLFName;
            $deposit->strLLName         = $row->strLLName;
            $deposit->dteOrigin         = $row->dteOrigin;
            $deposit->dteLastUpdate     = $row->dteLastUpdate;

            ++$idx;
         }
      }
   }

   function loadGiftsViaDIDPayID($lDepositID, $lPaymentID, &$lNumGifts, &$gifts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cGifts = new mdonations;
      $cGifts->sqlExtraWhere = " AND gi_lDepositLogID=$lDepositID AND gi_lPaymentType=$lPaymentID ";
      $cGifts->loadGifts();
      $lNumGifts = $cGifts->lNumGifts;
      $gifts = arrayCopy($cGifts->gifts);
   }

   function lAddNewDeposit(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $deposit = &$this->deposits[0];

      $sqlStr =
         'INSERT INTO deposit_log
          SET '.$this->strSqlCommonAddEdit().",
             dl_lACOID    = $deposit->lACOID,
             dl_lOriginID = $glUserID,
             dl_dteStart  = ".strPrepDate($deposit->dteStart).',
             dl_dteEnd    = '.strPrepDateTime($deposit->dteEnd).',
             dl_dteOrigin = NOW(),
             dl_bRetired  = 0;';

      $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   function updateDeposit($lDepositID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $deposit = &$this->deposits[0];

      $sqlStr =
         'UPDATE deposit_log
          SET '.$this->strSqlCommonAddEdit()."
          WHERE dl_lKeyID=$lDepositID;";

      $this->db->query($sqlStr);
   }
   
   private function strSqlCommonAddEdit(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $deposit = &$this->deposits[0];
      return('
            dl_strBank       = '.strPrepStr($deposit->strBank).',
            dl_strAccount    = '.strPrepStr($deposit->strAccount).',
            dl_strNotes      = '.strPrepStr($deposit->strNotes).",
            dl_lLastUpdateID = $glUserID,
            dl_dteLastUpdate = NOW() ");
   }

   function removeDeposit($lDepositID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         "UPDATE deposit_log
          SET
             dl_bRetired  = 1,
             dl_lLastUpdateID=$glUserID
          WHERE dl_lKeyID=$lDepositID;";
      $this->db->query($sqlStr);

         // remove deposit id from gifts
      $sqlStr =
         "UPDATE gifts
          SET
             gi_lDepositLogID = null,
             gi_lLastUpdateID = $glUserID
          WHERE gi_lDepositLogID=$lDepositID;";
      $this->db->query($sqlStr);
   }

   function removeGiftFromDeposit($lGiftID){
      global $glUserID;
      $sqlStr =
         "UPDATE gifts
          SET gi_lDepositLogID=null,
              gi_lLastUpdateID=$glUserID
          WHERE gi_lKeyID = $lGiftID;";
      $this->db->query($sqlStr);
   }

   function lNumGiftsViaDeposit($lDepositID, &$curTot){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumGifts, SUM(gi_curAmnt) AS curTotal
         FROM gifts
         WHERE NOT gi_bRetired
            AND gi_lDepositLogID=$lDepositID;";
      $query  = $this->db->query($sqlStr);
      $row    = $query->row();
      $curTot = $row->curTotal;
      return($row->lNumGifts);
   }

   function setGiftToDeposit($lGiftID, $lDepositID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr = "UPDATE gifts
                 SET gi_lDepositLogID=$lDepositID, gi_lLastUpdateID=$glUserID
                 WHERE gi_lKeyID=$lGiftID;";

      $this->db->query($sqlStr);
   }

   public function depositHTMLSummary(){
   //-----------------------------------------------------------------------
   // assumes user has called $this->clsDeposits->loadDepositReports();
   //-----------------------------------------------------------------------
      global $genumDateFormat;

      $strOut = '';
      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $clsRpt->setEntrySummary();
      $deposit = &$this->deposits[0];

      $lDepositID = $deposit->lKeyID;

      $strOut .=
          $clsRpt->openReport('', '')

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Deposit ID:')
         .$clsRpt->writeCell (strLinkView_DepositEntry($lDepositID, 'View Deposit', true)
                            .' '.str_pad($lDepositID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ()."\n"

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Deposit Range:')
         .$clsRpt->writeCell (date($genumDateFormat, $deposit->dteStart).' - '
                             .date($genumDateFormat, $deposit->dteEnd))
         .$clsRpt->closeRow  ()."\n"

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Created:')
         .$clsRpt->writeCell (date($genumDateFormat, $deposit->dteOrigin)
                   .' by '.htmlspecialchars($deposit->strCFName.' '.$deposit->strCLName))
         .$clsRpt->closeRow  ()."\n"

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('# Entries:')
         .$clsRpt->writeCell (number_format($this->lNumGiftsViaDeposit($lDepositID, $curTot))
                                .' ('.$deposit->strCurrencySymbol.' '.$curTot.')'
                             )
         .$clsRpt->closeRow  ()."\n"

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('ACO:')
         .$clsRpt->writeCell ($deposit->strCountryName.'&nbsp;'.$deposit->strFlagImg)
         .$clsRpt->closeRow  ()."\n";

      $strOut .=
         $clsRpt->closeReport("<br>\n");
      return($strOut);
   }

   public function loadGroupedDepositReportsViaDepositID($lDepositID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->depositSum = array();
      $sqlStr =
        "SELECT
            COUNT(*) AS lNumRecs, SUM(gi_curAmnt) AS curTotal,
            gi_lPaymentType, listPayType.lgen_strListItem AS strPaymentType
         FROM gifts
             INNER JOIN lists_generic AS listPayType ON gi_lPaymentType = listPayType.lgen_lKeyID
         WHERE
            NOT gi_bRetired
            AND gi_lDepositLogID=$lDepositID
         GROUP BY gi_lPaymentType
         ORDER BY listPayType.lgen_strListItem, gi_lPaymentType;";

      $query = $this->db->query($sqlStr);
      $this->lNumDepositSummary = $lNumDepositSummary = $query->num_rows();
      if ($lNumDepositSummary == 0){
         $this->depositSum[0] = new stdClass;
         $deposit = &$this->depositSum[0];
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->depositSum[$idx] = new stdClass;
            $deposit = &$this->depositSum[$idx];
            $deposit->lNumEntries        = $row->lNumRecs;
            $deposit->curTotal           = $row->curTotal;
            $deposit->lPaymentType       = $row->gi_lPaymentType;
            $deposit->strPaymentType     = $row->strPaymentType;
            $deposit->strSafePaymentType = htmlspecialchars($row->strPaymentType);

            ++$idx;
         }
      }
   }






   //-------------------------------------------------------------------
   //       R E P O R T S
   //-------------------------------------------------------------------

   function lNumRecsDepositLogReport(
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
           "SELECT dl_lKeyID
            FROM deposit_log
            WHERE NOT dl_bRetired
            ORDER BY dl_dteEnd DESC
            $strLimit;";
      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strDepositLogReportExport(
                           &$sRpt,
                           $bReport,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         return($this->strDepositLogReport($sRpt, $lStartRec, $lRecsPerPage));
      }else {
         return($this->strDepositLogExport($sRpt));
      }
   }

   function strDepositLogReport(&$sRpt, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      $clsACO = new madmin_aco();

      $sqlStr =
            "SELECT dl_lKeyID,
               dl_lACOID, dl_dteStart, dl_dteEnd, 
               dl_strBank, dl_strAccount, dl_strNotes,  dl_bRetired,
               dl_lOriginID, dl_lLastUpdateID,
               usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
               usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,

               aco_strFlag, aco_strCurrencySymbol, aco_strName,

               UNIX_TIMESTAMP(dl_dteOrigin)     AS dteOrigin,
               UNIX_TIMESTAMP(dl_dteLastUpdate) AS dteLastUpdate

             FROM deposit_log
               INNER JOIN admin_users AS usersC ON dl_lOriginID     = usersC.us_lKeyID
               INNER JOIN admin_users AS usersL ON dl_lLastUpdateID = usersL.us_lKeyID
               INNER JOIN admin_aco             ON dl_lACOID        = aco_lKeyID
            WHERE NOT dl_bRetired
            ORDER BY dl_dteEnd DESC, dl_lKeyID DESC
            $strLimit;";
      $query = $this->db->query($sqlStr);

      $lNumRows = $query->num_rows();
      if ($lNumRows == 0){
         return('<br><i>There are no deposits in your database.</i><br><br>');
      }

      $strOut =
                   
          strLinkAdd_Deposit('Add new deposit', true).'&nbsp;'
         .strLinkAdd_Deposit('Add new deposit', false).'<br>
          <table class="enpRptC">
             <tr>
                <td class="enpRptTitle" colspan="7">
                   Deposit Log
                </td>
             </tr>
             <tr>
                <td class="enpRptLabel">
                   Deposit ID
                </td>
                <td class="enpRptLabel">
                   ACO
                </td>
                <td class="enpRptLabel">
                   Period
                </td>
                <td class="enpRptLabel">
                   # Entries
                </td>
                <td class="enpRptLabel">
                   Total
                </td>
                <td class="enpRptLabel">
                   Bank / Account
                </td>
                <td class="enpRptLabel" style="width: 150pt;">
                   Notes
                </td>
             </tr>';

      foreach ($query->result() as $row){
         $lDepositID = $row->dl_lKeyID;
         $lNumEntries = $this->lNumGiftsViaDeposit($lDepositID, $curTot);

         if ($row->dl_strBank == '' & $row->dl_strAccount==''){
            $strBA = '&nbsp;';
         }else {
            $strBA = '<b>Bank: </b>'.htmlspecialchars($row->dl_strBank).'<br>'
                    .'<b>Account: </b>'.htmlspecialchars($row->dl_strAccount);
         }
         $strOut .= '
             <tr class="makeStripe">
                <td class="enpRpt" style="text-align: center;">'
                   .str_pad($lDepositID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                   .strLinkView_DepositEntry($lDepositID, 'View deposit entry', true).'
                </td>
                <td class="enpRpt">'
                   .htmlspecialchars($row->aco_strName).'&nbsp;'.$row->aco_strCurrencySymbol.'&nbsp;'
                   .$clsACO->strFlagImage($row->aco_strFlag, $row->aco_strName).'
                </td>
                <td class="enpRpt">'
                   .date($genumDateFormat, dteMySQLDate2Unix($row->dl_dteStart)).' - '
                   .date($genumDateFormat, dteMySQLDate2Unix($row->dl_dteEnd)).'
                </td>
                <td class="enpRpt" style="text-align: center;">'
                   .number_format($lNumEntries).'
                </td>
                <td class="enpRpt" style="text-align: right;">'
                   .number_format($curTot, 2).'
                </td>
                <td class="enpRpt">'
                   .$strBA.'
                </td>
                <td class="enpRpt" style="width: 150pt;">'
                   .nl2br(htmlspecialchars($row->dl_strNotes)).'
                </td>
             </tr>';
      }
      $strOut .= '</table><br><br>';
      return($strOut);
   }

   function strDepositLogExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat     = strMysqlDateFormat(false);
      $strDateTimeFormat = strMysqlDateFormat(true);

      $strTable = 'tmpDepExport';
      $this->createDepRptTmpTable($strTable);

      $sqlStr =
            "SELECT 
               dl_lKeyID AS `Deposit ID`,
               tmp_lNumEntriesID AS `Number of Entries`,
               tmp_curAmnt       AS `Total Deposit Amount`,
               dl_lACOID         AS `ACO ID`,                
               aco_strCurrencySymbol                    AS `Currency Symbol`, 
               aco_strName                              AS `Accounting Country`,
               DATE_FORMAT(dl_dteStart, $strDateFormat) AS `Deposit Start`,
               DATE_FORMAT(dl_dteEnd,   $strDateFormat) AS `Deposit End`,
               dl_strBank                               AS `Bank`, 
               dl_strAccount                            AS `Bank Account`, 
               dl_strNotes                              AS `Notes`,  
               CONCAT(usersC.us_strFirstName, ' ', usersC.us_strLastName) AS `Created By`,
               CONCAT(usersL.us_strFirstName, ' ', usersL.us_strLastName) AS `Last Updated By`,

               DATE_FORMAT(dl_dteOrigin, $strDateTimeFormat)     AS `Created On`,
               DATE_FORMAT(dl_dteLastUpdate, $strDateTimeFormat) AS `Last Updated On`

             FROM $strTable
               INNER JOIN  deposit_log          ON dl_lKeyID        = tmp_lDepositID
               INNER JOIN admin_users AS usersC ON dl_lOriginID     = usersC.us_lKeyID
               INNER JOIN admin_users AS usersL ON dl_lLastUpdateID = usersL.us_lKeyID
               INNER JOIN admin_aco             ON dl_lACOID        = aco_lKeyID
            WHERE NOT dl_bRetired
            ORDER BY dl_dteEnd DESC, dl_lKeyID DESC;";
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   private function createDepRptTmpTable($strTable){
      $sqlStr = "DROP TABLE IF EXISTS $strTable;";
      $this->db->query($sqlStr);

      $sqlStr =
        "CREATE TEMPORARY TABLE IF NOT EXISTS $strTable (
           tmp_lKeyID        int(11) NOT NULL AUTO_INCREMENT,
           tmp_lDepositID    int(11) NOT NULL DEFAULT '0',
           tmp_lNumEntriesID int(11) NOT NULL DEFAULT '0',
           tmp_curAmnt       decimal(10,2) NOT NULL DEFAULT '0.00',
           PRIMARY KEY (tmp_lKeyID),
           KEY tmp_lDepositID (tmp_lDepositID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
      $this->db->query($sqlStr);

      $sqlStr =
            'SELECT
                dl_lKeyID
             FROM deposit_log
             WHERE NOT dl_bRetired;';
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() > 0){
         foreach ($query->result() as $row){
            $lDepositID = $row->dl_lKeyID;
            $lNumEntries = $this->lNumGiftsViaDeposit($lDepositID, $curTot);
            if (is_null($curTot)) $curTot = 0.0;
            $sqlInsert =
               "INSERT INTO $strTable
                   SET
                      tmp_lDepositID    = $lDepositID,
                      tmp_lNumEntriesID = $lNumEntries,
                      tmp_curAmnt       = $curTot;";
            $this->db->query($sqlInsert);
         }
      }
   }

   function strDepositEntryExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      $lDepositID = $sRpt->lDepositID;
      
      $sqlStr =
           'SELECT '.strExportFields_Gifts()."
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
            WHERE NOT gi_bRetired AND NOT donor.pe_bRetired AND gi_lDepositLogID=$lDepositID
            ORDER BY gi_lKeyID;";
      
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));   
   }





}
