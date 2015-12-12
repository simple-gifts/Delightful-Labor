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
      $this->load->model('donations/mpledges', 'clsPledges');
---------------------------------------------------------------------

---------------------------------------------------------------------*/


class Mpledges extends CI_Model{


   public $pledges, $lNumPledges, $sqlExtraWhere, $sqlOrder, $sqlLimit;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->pledges = null;
      $this->lNumPledges = 0;
      $this->sqlExtraWhere = $this->sqlOrder = $this->sqlLimit = '';

   }

   public function loadPledgeViaPledgeID($lPledgeID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlExtraWhere = " AND gp_lKeyID = $lPledgeID ";
      $this->loadPledges();
   }

   public function loadPledgeViaFID($lFID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlExtraWhere = " AND gp_lForeignID = $lFID ";
      $this->loadPledges();
   }

   public function loadPledges(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->pledges = array();
      $this->lNumPledges = 0;

      $clsACO = new madmin_aco;

      if ($this->sqlOrder == ''){
         $strOrder = ' gp_lKeyID ';
      }else {
         $strOrder = $this->sqlOrder;
      }

      $sqlStr =
        "SELECT
            gp_lKeyID, gp_lForeignID, gp_lCampID, gp_curCommitment, gp_lNumCommit,
            gp_enumFreq, gp_lACOID,
            gp_dteStart,
            gp_strNotes,
            gp_lAttributedTo, listAttrib.lgen_strListItem       AS strAttribTo,
            pe_lKeyID, pe_bBiz, pe_strFName, pe_strLName,

            gc_lKeyID, gc_strCampaign,
            ga_lKeyID, ga_strAccount,

            aco_strFlag, aco_strCurrencySymbol, aco_strName,

            usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
            usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,
            UNIX_TIMESTAMP(gp_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(gp_dteLastUpdate) AS dteLastUpdate

         FROM gifts_pledges
            INNER JOIN gifts_campaigns ON gp_lCampID = gc_lKeyID
            INNER JOIN gifts_accounts  ON gc_lAcctID = ga_lKeyID
            INNER JOIN people_names    ON pe_lKeyID  = gp_lForeignID
            INNER JOIN admin_aco       ON gp_lACOID  = aco_lKeyID

            INNER JOIN admin_users AS usersC ON gp_lOriginID    = usersC.us_lKeyID
            INNER JOIN admin_users AS usersL ON gp_lLastUpdateID= usersL.us_lKeyID

            LEFT  JOIN lists_generic AS listAttrib       ON gp_lAttributedTo = listAttrib.lgen_lKeyID
         WHERE NOT gp_bRetired
            $this->sqlExtraWhere
         ORDER BY $strOrder
         $this->sqlLimit;";

      $query = $this->db->query($sqlStr);

      $this->lNumPledges = $query->num_rows();
      if ($this->lNumPledges == 0) {
         $this->pledges[0] = new stdClass;
         $pledge = &$this->pledges[0];

         $pledge->lKeyID            =
         $pledge->lForeignID        =
         $pledge->curCommitment     =
         $pledge->lNumCommit        =
         $pledge->enumFreq          =
         $pledge->dteStart          =
         $pledge->mdteStart         =

         $pledge->lAttributedTo     =
         $pledge->strAttribTo       =
         $pledge->strNotes          =

//         $pledge->lForeignID        =
         $pledge->bBiz              =
         $pledge->strFName          =
         $pledge->strLName          =
         $pledge->strSafeName       =
         $pledge->strSafeNameLF     =

         $pledge->lCampaignID       =
         $pledge->strCampaign       =
         $pledge->lAccountID        =
         $pledge->strAccount        =

         $pledge->lACOID            =
         $pledge->strFlagImg        =
         $pledge->strACOCurSymbol   =
         $pledge->strACOCountry     =
         $pledge->strFormattedAmnt  =

         $pledge->dteOrigin         =
         $pledge->dteLastUpdate     =

         $pledge->strStaffCFName    =
         $pledge->strStaffCLName    =
         $pledge->strStaffLFName    =
         $pledge->strStaffLLName    = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->pledges[$idx] = new stdClass;
            $pledge = &$this->pledges[$idx];

            $pledge->lKeyID         = (int)$row->gp_lKeyID;
            $pledge->lForeignID     = (int)$row->gp_lForeignID;
            $pledge->curCommitment  = (float)$row->gp_curCommitment;
            $pledge->lNumCommit     = (int)$row->gp_lNumCommit;
            $pledge->enumFreq       = $row->gp_enumFreq;
            $pledge->dteStart       = dteMySQLDate2Unix($row->gp_dteStart);
            $pledge->mdteStart      = $row->gp_dteStart;

            $pledge->lAttributedTo  = $row->gp_lAttributedTo;
            $pledge->strAttribTo    = $row->strAttribTo;
            $pledge->strNotes       = $row->gp_strNotes;

//            $pledge->lForeignID     = $row->pe_lKeyID;
            $pledge->bBiz           = (boolean)$row->pe_bBiz;
            $pledge->strFName       = $row->pe_strFName;
            $pledge->strLName       = $row->pe_strLName;
            if ($pledge->bBiz){
               $pledge->strSafeName = $pledge->strSafeNameLF = htmlspecialchars($row->pe_strLName);
            }else {
               $pledge->strSafeName    = htmlspecialchars($row->pe_strFName.' ' .$row->pe_strLName);
               $pledge->strSafeNameLF  = htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName);
            }

            $pledge->lCampaignID    = (int)$row->gc_lKeyID;
            $pledge->strCampaign    = $row->gc_strCampaign;
            $pledge->lAccountID     = (int)$row->ga_lKeyID;
            $pledge->strAccount     = $row->ga_strAccount;

            $pledge->lACOID            = (int)$row->gp_lACOID;
            $pledge->strFlagImg        = $clsACO->strFlagImage($row->aco_strFlag, $row->aco_strName);
            $pledge->strACOCurSymbol   = $row->aco_strCurrencySymbol;
            $pledge->strACOCountry     = $row->aco_strName;
            $pledge->strFormattedAmnt  =
                                   $pledge->strACOCurSymbol.' '.number_format($pledge->curCommitment, 2).' '
                                  .$pledge->strFlagImg;

            $pledge->dteOrigin      = (int)$row->dteOrigin;
            $pledge->dteLastUpdate  = (int)$row->dteLastUpdate;

            $pledge->strStaffCFName = $row->strCFName;
            $pledge->strStaffCLName = $row->strCLName;
            $pledge->strStaffLFName = $row->strLFName;
            $pledge->strStaffLLName = $row->strLLName;

            ++$idx;
         }
      }
   }

   function strDDLPledgeFrequecy($strDDLName, $enumMatch, $bAddBlank){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut =
         '<select name="'.$strDDLName.'">'."\n";

      if ($bAddBlank){
         $strOut .= '<option value="-1">&nbsp;</option>'."\n";
      }

      $strOut .= $this->strAddPFreqDDL($enumMatch, 'one-time');
      $strOut .= $this->strAddPFreqDDL($enumMatch, 'weekly');
      $strOut .= $this->strAddPFreqDDL($enumMatch, 'monthly');
      $strOut .= $this->strAddPFreqDDL($enumMatch, 'quarterly');
      $strOut .= $this->strAddPFreqDDL($enumMatch, 'annually');
//      $strOut .= $this->strAddPFreqDDL($enumMatch, , 'other'
      return($strOut.'</select>'."\n");
   }

   private function strAddPFreqDDL($enumMatch, $strValue){
      return('<option value="'.$strValue.'" '.($enumMatch == $strValue ? 'selected' : '').'>'.$strValue.'</option>'."\n");
   }

   function lAddNewPledgeRecord(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $pledge = &$this->pledges[0];

      $sqlStr =
           'INSERT INTO gifts_pledges
            SET '.$this->sqlCommonAddUpdate().",
               gp_lForeignID = $pledge->lForeignID,
               gp_bRetired   = 0,
               gp_lOriginID  = $glUserID,
               gp_dteOrigin  = NOW();";

      $this->db->query($sqlStr);
      $this->pledge->lKeyID = $lPledgeID = $this->db->insert_id();
      return($lPledgeID);
   }

   function updatePledgeRecord($lPledgeID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $pledge = &$this->pledges[0];

      $sqlStr =
           'UPDATE gifts_pledges
            SET '.$this->sqlCommonAddUpdate()."
            WHERE gp_lKeyID=$lPledgeID;";

      $this->db->query($sqlStr);
   }

   private function sqlCommonAddUpdate(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $pledge = &$this->pledges[0];

      return('
            gp_lCampID            = '.$pledge->lCampaignID.',
            gp_curCommitment      = '.$pledge->curCommitment.',
            gp_lNumCommit         = '.$pledge->lNumCommit.',
            gp_enumFreq           = '.strPrepStr($pledge->enumFreq).',
            gp_lACOID             = '.$pledge->lACOID.',
            gp_dteStart           = '.strPrepStr($pledge->mdteStart).',
            gp_lAttributedTo      = '.(is_null($pledge->lAttributedTo) ? 'null' : $pledge->lAttributedTo).',
            gp_strNotes           = '.strPrepStr($pledge->strNotes).',
            gp_lLastUpdateID      = '.$glUserID.',
            gp_dteLastUpdate      = NOW() ');

   }

   function pledgeSchedule(&$pledge, &$schedule){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
//      $pledge = &$this->pledges[0];

      $schedule  = array();
      $dteStart  = $pledge->dteStart;
      $enumFreq  = $pledge->enumFreq;
      $lCnt      = $pledge->lNumCommit;
      $lACOID    = $pledge->lACOID;
      $lPledgeID = $pledge->lKeyID;

      $startDate = getdate($dteStart);
      $lMonth   = $startDate['mon'];
      $lYear    = $startDate['year'];
      $lDay     = $startDate['mday'];
      $lYearDay = $startDate['yday'];
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$startDate   <pre>');
echo(htmlspecialchars( print_r($startDate, true))); echo('</pre></font><br>');
// mktime ([ int $hour = date("H") [, int $minute = date("i") [, int $second = date("s") [, int $month = date("n") [, int $day = date("j") [, int $year = date("Y") [, int $is_dst = -1 ]]]]]]] )
// ------------------------------------- */


      switch ($enumFreq){
         case 'one-time':
            for ($idx=0; $idx<$lCnt; ++$idx){
               $schedule[$idx] = new stdClass;
               $sch = &$schedule[$idx];
               $sch->pDate = $pDate = mktime(0, 0, 0, $lMonth, $lDay, $lYear);
//               $sch->pFulfilled = $this->bPledgeFulfilledByDate($pDate, $lPledgeID, $lACOID, $sch->lGiftID);
            }
            break;
         case 'weekly':
            for ($idx=0; $idx<$lCnt; ++$idx){
               $schedule[$idx] = new stdClass;
               $sch = &$schedule[$idx];
               $sch->pDate = $pDate = mktime(0, 0, 0, $lMonth, $lDay, $lYear);
//               $sch->pFulfilled = $this->bPledgeFulfilledByDate($pDate, $lPledgeID, $lACOID, $sch->lGiftID);
               $lDay += 7;
            }
            break;
         case 'monthly':
            for ($idx=0; $idx<$lCnt; ++$idx){
               $schedule[$idx] = new stdClass;
               $sch = &$schedule[$idx];
               $sch->pDate = $pDate = mktime(0, 0, 0, $lMonth, $lDay, $lYear);
//               $sch->pFulfilled = $this->bPledgeFulfilledByDate($pDate, $lPledgeID, $lACOID, $sch->lGiftID);
               ++$lMonth;
            }
            break;
         case 'quarterly':
            for ($idx=0; $idx<$lCnt; ++$idx){
               $schedule[$idx] = new stdClass;
               $sch = &$schedule[$idx];
               $sch->pDate = $pDate = mktime(0, 0, 0, $lMonth, $lDay, $lYear);
//               $sch->pFulfilled = $this->bPledgeFulfilledByDate($pDate, $lPledgeID, $lACOID, $sch->lGiftID);
               $lMonth += 3;
            }
            break;
         case 'annually':
            for ($idx=0; $idx<$lCnt; ++$idx){
               $schedule[$idx] = new stdClass;
               $sch = &$schedule[$idx];
               $sch->pDate = $pDate = mktime(0, 0, 0, $lMonth, $lDay, $lYear);
//               $sch->pFulfilled = $this->bPledgeFulfilledByDate($pDate, $lPledgeID, $lACOID, $sch->lGiftID);
               ++$lYear;
            }
            break;
         default:
            screamForHelp($enumFreq.': invalid pledge frequency<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }


         /* gift fulfillment:
             one-time pledge: any donation tagged to the pledge ID
             other pledges:
               first schedule date:
                  any donation since the beginning of time to the day before the next
                  pledge date
               last schedule date
                  any donation on or after the last schedule date
               other schedule dates
                  any donation on or after the pledge date and before the next schedule date
         */
      if ($lCnt == 1){
         $this->getFulfillments($schedule[0]->lNumFulfill, $schedule[0]->fulfillment, $lPledgeID, null, null);
      }else {
         for ($idx=0; $idx<$lCnt; ++$idx){
            $sch = &$schedule[$idx];
            if ($idx==0){
               $this->getFulfillments($sch->lNumFulfill, $sch->fulfillment, $lPledgeID,
                            null, $schedule[$idx+1]->pDate);
            }elseif ($idx == ($lCnt-1)){
               $this->getFulfillments($sch->lNumFulfill, $sch->fulfillment, $lPledgeID,
                            $sch->pDate, null);
            }else {
               $this->getFulfillments($sch->lNumFulfill, $sch->fulfillment, $lPledgeID,
                            $sch->pDate, $schedule[$idx+1]->pDate);
            }
         }
      }
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$schedule   <pre>');
echo(htmlspecialchars( print_r($schedule, true))); echo('</pre></font><br>');
die;
// ------------------------------------- */
   }

   function getFulfillments(&$lNumFulfill, &$fulfillment, $lPledgeID, $dteOnOrAfter, $dteBefore){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsACO = new madmin_aco();
      $lNumFulfill = 0;
      $fulfillment = array();
      $sqlStr =
          "SELECT gi_lKeyID, gi_lForeignID,
             gi_dteDonation,
             gi_curAmnt, gi_strCheckNum,
             pe_lKeyID, pe_bBiz, pe_strFName, pe_strLName,
             gi_lACOID, aco_strFlag, aco_strCurrencySymbol, aco_strName
           FROM gifts
            INNER JOIN people_names    ON pe_lKeyID  = gi_lForeignID
            INNER JOIN admin_aco       ON gi_lACOID  = aco_lKeyID
           WHERE gi_lPledgeID=$lPledgeID
              AND NOT gi_bRetired ";

      if (!is_null($dteOnOrAfter)){
         $sqlStr .= ' AND gi_dteDonation >= '.strPrepDate($dteOnOrAfter)."\n";
      }
      if (!is_null($dteBefore)){
         $sqlStr .= ' AND gi_dteDonation < '.strPrepDate($dteBefore)."\n";
      }

      $sqlStr .=
          'ORDER BY gi_dteDonation, gi_lKeyID;';

      $query = $this->db->query($sqlStr);
      $lNumFulfill = $query->num_rows();
      if ($lNumFulfill > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $fulfillment[$idx] = new stdClass;
            $gift = &$fulfillment[$idx];

            $gift->lKeyID         = (int)$row->gi_lKeyID;
            $gift->lForeignID     = (int)$row->gi_lForeignID;
            $gift->curAmnt        = (float)$row->gi_curAmnt;
            $gift->dteDonation    = dteMySQLDate2Unix($row->gi_dteDonation);
            $gift->mdteDonation   = $row->gi_dteDonation;
            $gift->strCheckNum    = $row->gi_strCheckNum;

            $gift->lFID           = (int)$row->pe_lKeyID;
            $gift->bBiz           = (boolean)$row->pe_bBiz;
            $gift->strFName       = $row->pe_strFName;
            $gift->strLName       = $row->pe_strLName;
            if ($gift->bBiz){
               $gift->strSafeName    = $gift->strSafeNameLF = htmlspecialchars($row->pe_strLName);
            }else {
               $gift->strSafeName    = htmlspecialchars($row->pe_strFName.' ' .$row->pe_strLName);
               $gift->strSafeNameLF  = htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName);
            }

            $gift->lACOID            = (int)$row->gi_lACOID;
            $gift->strFlagImg        = $clsACO->strFlagImage($row->aco_strFlag, $row->aco_strName);
            $gift->strACOCurSymbol   = $row->aco_strCurrencySymbol;
            $gift->strACOCountry     = $row->aco_strName;
            $gift->strFormattedAmnt  =
                                   $gift->strACOCurSymbol.' '
                                  .number_format($gift->curAmnt, 2).' '
                                  .$gift->strFlagImg;
            ++$idx;
         }
      }
   }

   function curTotalFulfillmentViaPledgeID($lPledgeID, $lACOID){
   //---------------------------------------------------------------------
   // note - only pledge payments made to the original ACO are considered
   // pledge payments.
   //---------------------------------------------------------------------
      $sqlStr =
           "SELECT SUM(gi_curAmnt) AS curSum
            FROM gifts
            WHERE NOT gi_bRetired
               AND gi_lPledgeID=$lPledgeID
               AND gi_lACOID=$lACOID;";
      $query = $this->db->query($sqlStr);

      $row = $query->row();
      return((float)$row->curSum);
   }

   function curFillmentViaPledgeID($lPledgeID, $lACOID, &$cgifts){
   //---------------------------------------------------------------------
   // note - only pledge payments made to the original ACO are considered
   // pledge payments.
   //---------------------------------------------------------------------
      $cgifts->sqlExtraWhere = " AND gi_lPledgeID=$lPledgeID AND gi_lACOID=$lACOID ";
      $cgifts->sqlExtraSort  = ' ORDER BY gi_dteDonation, gi_lKeyID ';
      $cgifts->sqlLimit = '';
      $cgifts->loadGifts();
   }

   function bPledgeFulfilledByDate($pDate, $lPledgeID, $lACOID, &$lGiftID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lGiftID = null;
      $sqlStr =
           "SELECT gi_lKeyID
            FROM gifts
            WHERE NOT gi_bRetired
               AND gi_lPledgeID   = $lPledgeID
               AND gi_lACOID      = $lACOID
               AND gi_dteDonation = ".strPrepDate($pDate).';';

      $query = $this->db->query($sqlStr);

      if ($query->num_rows() > 0){
         $row = $query->row();
         $lGiftID = $row->gi_lKeyID;
         return(true);
      }else {
         return(false);
      }
   }

   function lNumPledgesViaFID($lFID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM gifts_pledges
         WHERE gp_lForeignID=$lFID
            AND NOT gp_bRetired;";
      $query = $this->db->query($sqlStr);

      if ($query->num_rows() > 0){
         $row = $query->row();
         return((int)$row->lNumRecs);
      }else {
         return(0);
      }
   }

}
