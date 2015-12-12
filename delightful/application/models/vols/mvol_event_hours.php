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
      $this->load->model('vols/mvol_event_hours', 'clsVolHours');
---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class mvol_event_hours extends CI_Model{
   public
       $volEHrs;

      // unscheduled activities
   public $strWhereUnScheduled,
          $lNumUnActivity, $unActivity;


   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->volEHrs = null;

      $this->strWhereUnScheduled = '';
      $this->lNumUnActivity = $this->unActivity = null;
   }

   public function volEventHoursViaVolID($lVolID){
   //---------------------------------------------------------------------
   // scheduled hours only
   //---------------------------------------------------------------------
      $sqlStr =
           "SELECT SUM(vsa_dHoursWorked) AS dHours
            FROM vol_events_dates_shifts_assign
               INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
               INNER JOIN vol_events_dates        ON vs_lEventDateID = ved_lKeyID
               INNER JOIN vol_events              ON ved_lVolEventID = vem_lKeyID
            WHERE NOT vsa_bRetired
               AND NOT vs_bRetired
               AND NOT vem_bRetired
               AND vsa_lVolID=$lVolID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return(0.0);
      }else {
         $row = $query->row();
         return($row->dHours);
      }
   }

   public function dTotUnscheduledHoursViaVolID($lVolID){
   //---------------------------------------------------------------------
   // unscheduled hours only
   //---------------------------------------------------------------------
      $sqlStr =
           "SELECT SUM(vsa_dHoursWorked) AS dHours
            FROM vol_events_dates_shifts_assign
            WHERE NOT vsa_bRetired
               AND vsa_lEventDateShiftID IS NULL
               AND vsa_lVolID=$lVolID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return(0.0);
      }else {
         $row = $query->row();
         return($row->dHours);
      }
   }

   public function unscheduledHoursViaVolIDMonthYear($lVolID, $lMonth, $lYear, &$lNumUnscheduled, &$unscheduled){
   //---------------------------------------------------------------------
   // unscheduled hours only
   //---------------------------------------------------------------------
      $unscheduled = array();
      $sqlStr =
           "SELECT
               vsa_lKeyID, vsa_strNotes, vsa_dHoursWorked, vsa_lVolID,
               vsa_dteActivityDate,
               vsa_lActivityID, lgen_strListItem
            FROM vol_events_dates_shifts_assign
               INNER JOIN lists_generic ON vsa_lActivityID=lgen_lKeyID
            WHERE NOT vsa_bRetired
               AND vsa_lEventDateShiftID IS NULL
               AND vsa_lVolID=$lVolID
               AND MONTH(vsa_dteActivityDate) = $lMonth
               AND YEAR(vsa_dteActivityDate)  = $lYear
            ORDER BY vsa_dteActivityDate, vsa_lKeyID;";
      $query = $this->db->query($sqlStr);
      $lNumUnscheduled = $query->num_rows();
      if ($lNumUnscheduled > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $unscheduled[$idx] = new stdClass;
            $act = &$unscheduled[$idx];

            $act->lKeyID            = $row->vsa_lKeyID;
            $act->lVolID            = $row->vsa_lVolID;
            $act->strNotes          = $row->vsa_strNotes;
            $act->dHoursWorked      = $row->vsa_dHoursWorked;
            $act->mysqlActivityDate = $row->vsa_dteActivityDate;
            $act->dteActivityDate   = strtotime($row->vsa_dteActivityDate);
            $act->lActivityID       = $row->vsa_lActivityID;
            $act->strActivity       = $row->lgen_strListItem;
            ++$idx;
         }
      }
   }

   public function volEventHoursViaShift($lShiftID, $bQualViaVolID=false, $lVolID=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bQualViaVolID){
         $strWhereExtra = " AND vsa_lVolID=$lVolID ";
      }else {
         $strWhereExtra = '';
      }
      $this->volEHrs = array();
      $sqlStr =
        "SELECT
            vsa_lKeyID, vsa_lEventDateShiftID, vsa_lVolID, vsa_strNotes, vsa_dHoursWorked,
            vs_lEventDateID, vs_strShiftName, vs_strDescription,
            ved_dteEvent,
            UNIX_TIMESTAMP(vs_dteShiftStartTime) AS timeStartTime,
            vs_enumDuration, vs_lNumVolsNeeded,
            ved_lVolEventID,

            vol_lPeopleID,
            pe_strFName, pe_strMName, pe_strLName, pe_strAddr1, pe_strAddr2, pe_strCity, pe_strState,
            pe_strCountry, pe_strZip, pe_strPhone, pe_strCell, pe_strEmail

         FROM vol_events_dates_shifts_assign
            INNER JOIN volunteers              ON vsa_lVolID            = vol_lKeyID
            INNER JOIN people_names            ON pe_lKeyID             = vol_lPeopleID
            INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
            INNER JOIN vol_events_dates        ON vs_lEventDateID       = ved_lKeyID

         WHERE NOT vsa_bRetired
            AND vs_lKeyID=$lShiftID $strWhereExtra
         ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumVolsInShift = $lNumVols = $query->num_rows();

      if ($query->num_rows() == 0){
         $this->volEHrs[0] = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->volEHrs[$idx] = new stdClass;
            $this->volEHrs[$idx]->lVolShiftAssignID   = $row->vsa_lKeyID;
            $this->volEHrs[$idx]->lEventDateShiftID   = $row->vsa_lEventDateShiftID;
            $this->volEHrs[$idx]->lVolID              = $row->vsa_lVolID;
            $this->volEHrs[$idx]->strShiftAssignNotes = $row->vsa_strNotes;
            $this->volEHrs[$idx]->dHoursWorked        = $row->vsa_dHoursWorked;
            $this->volEHrs[$idx]->lEventDateID        = $row->vs_lEventDateID;
            $this->volEHrs[$idx]->strShiftName        = $row->vs_strShiftName;
            $this->volEHrs[$idx]->strShiftDescription = $row->vs_strDescription;
            $this->volEHrs[$idx]->dteEvent            = strtotime($row->ved_dteEvent);
            $this->volEHrs[$idx]->timeStartTime       = $row->timeStartTime;
            $this->volEHrs[$idx]->enumDuration        = $row->vs_enumDuration;
            $this->volEHrs[$idx]->lNumVolsNeeded      = $row->vs_lNumVolsNeeded;
            $this->volEHrs[$idx]->lEventID            = $row->ved_lVolEventID;
            $this->volEHrs[$idx]->lPeopleID           = $row->vol_lPeopleID;
            $this->volEHrs[$idx]->strFName            = $row->pe_strFName;
            $this->volEHrs[$idx]->strMName            = $row->pe_strMName;
            $this->volEHrs[$idx]->strLName            = $row->pe_strLName;
            $this->volEHrs[$idx]->strAddr1            = $row->pe_strAddr1;
            $this->volEHrs[$idx]->strAddr2            = $row->pe_strAddr2;
            $this->volEHrs[$idx]->strCity             = $row->pe_strCity;
            $this->volEHrs[$idx]->strState            = $row->pe_strState;
            $this->volEHrs[$idx]->strCountry          = $row->pe_strCountry;
            $this->volEHrs[$idx]->strZip              = $row->pe_strZip;
            $this->volEHrs[$idx]->strPhone            = $row->pe_strPhone;
            $this->volEHrs[$idx]->strCell             = $row->pe_strCell;
            $this->volEHrs[$idx]->strEmail            = $row->pe_strEmail;

            ++$idx;
         }
      }
   }

   function dTotHoursWorkedViaEventID($lEventID){
      $sqlStr =
        "SELECT SUM(`vsa_dHoursWorked`) AS sumHrs
         FROM `vol_events_dates_shifts_assign`
            INNER JOIN vol_events_dates_shifts  ON `vsa_lEventDateShiftID`=vs_lKeyID
            INNER JOIN vol_events_dates  ON vs_lEventDateID=ved_lKeyID
         WHERE
            NOT `vsa_bRetired`
            AND NOT vs_bRetired
            AND ved_lVolEventID=$lEventID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return(0.0);
      }else {
         $row = $query->row();
         return((float)$row->sumHrs);
      }
   }

   function dTotHoursWorkedViaShiftID($lShiftID, $bQualViaVolID=false, $lVolID=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bQualViaVolID){
         $strWhereExtra = " AND vsa_lVolID=$lVolID ";
      }else {
         $strWhereExtra = '';
      }

      $sqlStr =
        "SELECT
            SUM(vsa_dHoursWorked) AS sumHrs
         FROM vol_events_dates_shifts_assign
            INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
            INNER JOIN vol_events_dates        ON vs_lEventDateID       = ved_lKeyID

         WHERE NOT vsa_bRetired
            AND vs_lKeyID=$lShiftID $strWhereExtra;";

      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return(0.0);
      }else {
         $row = $query->row();
         return((float)$row->sumHrs);
      }
   }

   function setVolHours($lVolShiftAssignID, $dHours){
      $sqlStr =
        'UPDATE vol_events_dates_shifts_assign
         SET vsa_dHoursWorked='.(float)$dHours."
         WHERE vsa_lKeyID=$lVolShiftAssignID;";
      $this->db->query($sqlStr);
   }

   function volHoursViaVolIDShiftID($lVolID, $lShiftID, &$enumHrsScheduled, &$dHrsScheduled, &$dHrsWorked){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $dHrsScheduled = $dHrsWorked = 0.0;
      $enumHrsScheduled = 'not scheduled';
      $sqlStr = "
            SELECT vsa_dHoursWorked, vs_enumDuration
            FROM vol_events_dates_shifts_assign
               INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID=vs_lKeyID
            WHERE NOT vsa_bRetired
               AND vsa_lEventDateShiftID=$lShiftID
               AND vsa_lVolID=$lVolID;";

      $query = $this->db->query($sqlStr);
      if ($query->num_rows() > 0){
         $row = $query->row();
         $enumHrsScheduled = $row->vs_enumDuration;
         $dHrsScheduled    = tdh\sngXlateDuration($enumHrsScheduled);
         $dHrsWorked       = (float)$row->vsa_dHoursWorked;
      }
   }



      /* --------------------------------------------------------
               U n s c h e d u l e d    V o l   H o u r s
         -------------------------------------------------------- */

   public function volUnscheduledEventHoursViaVolID($lVolID){
   //---------------------------------------------------------------------
   // scheduled hours only
   //---------------------------------------------------------------------
      $sqlStr =
           "SELECT SUM(vsa_dHoursWorked) AS dHours
            FROM vol_events_dates_shifts_assign
            WHERE NOT vsa_bRetired
               AND vsa_lEventDateShiftID IS NULL
               AND vsa_lVolID=$lVolID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return(0.0);
      }else {
         $row = $query->row();
         return($row->dHours);
      }
   }

   public function loadVolActivitiesViaID($lActivityID){
   //---------------------------------------------------------------------
   // unscheduled activities
   //---------------------------------------------------------------------
      $this->strWhereUnScheduled = " AND vsa_lKeyID=$lActivityID ";
      $this->loadActivities();
   }

   public function loadActivities(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->unActivity = array();
      $this->lNumUnActivity = 0;

      $sqlStr =
        "SELECT
            vsa_lKeyID,  vsa_lVolID, vsa_strNotes, vsa_dHoursWorked,
            vsa_dteActivityDate, vsa_lJobCode, jc.lgen_strListItem AS strJobCode,
            act.lgen_strListItem AS strActivity,
            vsa_lActivityID, vsa_bRetired,
            vsa_lOriginID, vsa_lLastUpdateID,
            UNIX_TIMESTAMP(vsa_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(vsa_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName

         FROM vol_events_dates_shifts_assign
            INNER JOIN lists_generic AS act ON vsa_lActivityID    = act.lgen_lKeyID
            INNER JOIN admin_users   AS uc ON uc.us_lKeyID = vsa_lOriginID
            INNER JOIN admin_users   AS ul ON ul.us_lKeyID = vsa_lLastUpdateID
            LEFT  JOIN lists_generic AS jc ON vsa_lJobCode = jc.lgen_lKeyID

         WHERE vsa_lEventDateShiftID IS NULL $this->strWhereUnScheduled

         ORDER BY vsa_dteActivityDate DESC, vsa_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumUnActivity = $query->num_rows();
      if ($this->lNumUnActivity == 0){
         $this->unActivity[0] = new stdClass;
         $act = &$this->unActivity[0];

         $act->lKeyID            =
         $act->lVolID            =
         $act->strNotes          =
         $act->dHoursWorked      =
         $act->mysqlActivityDate =
         $act->dteActivityDate   =
         $act->lActivityID       =
         $act->lJobCode          =
         $act->strJobCode        =
         $act->bRetired          =
         $act->strActivity       =
         $act->dteOrigin         =
         $act->dteLastUpdate     =
         $act->strUCLName        =
         $act->strULLName        = null;

      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->unActivity[$idx] = new stdClass;
            $act = &$this->unActivity[$idx];

            $act->lKeyID            = $row->vsa_lKeyID;
            $act->lVolID            = $row->vsa_lVolID;
            $act->strNotes          = $row->vsa_strNotes;
            $act->dHoursWorked      = $row->vsa_dHoursWorked;
            $act->mysqlActivityDate = $row->vsa_dteActivityDate;
            $act->dteActivityDate   = strtotime($row->vsa_dteActivityDate);
            $act->lActivityID       = $row->vsa_lActivityID;
            $act->lJobCode          = $row->vsa_lJobCode;
            $act->strJobCode        = $row->strJobCode;
            $act->bRetired          = $row->vsa_bRetired;
            $act->strActivity       = $row->strActivity;
            $act->dteOrigin         = $row->dteOrigin;
            $act->dteLastUpdate     = $row->dteLastUpdate;
            $act->strUCLName        = $row->strUCLName;
            $act->strULLName        = $row->strULLName;

            ++$idx;
         }
      }
   }

   public function addUnscheduledHrs(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $act = &$this->unActivity[0];

      $sqlStr = '
         INSERT INTO vol_events_dates_shifts_assign
         SET '.$this->sqlUnscheduledCommon().",
            vsa_lEventDateShiftID = NULL,
            vsa_lVolID    = $act->lVolID,
            vsa_bRetired  = 0,
            vsa_lOriginID = $glUserID,
            vsa_dteOrigin = NOW();";
      $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   public function updateUnscheduledHrs($lActivityID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $act = &$this->unActivity[0];

      $sqlStr = '
         UPDATE vol_events_dates_shifts_assign
         SET '.$this->sqlUnscheduledCommon()."
         WHERE vsa_lKeyID=$lActivityID;";
      $this->db->query($sqlStr);
   }

   private function sqlUnscheduledCommon(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $act = &$this->unActivity[0];

      return('
            vsa_strNotes        = '.strPrepStr($act->strNotes).',
            vsa_dHoursWorked    = '.number_format($act->dHoursWorked, 2, '.', '').',
            vsa_lJobCode        = '.(is_null($act->lJobCode) ? 'NULL' : (int)$act->lJobCode).',
            vsa_dteActivityDate = '.strPrepDateTime($act->dteActivityDate).",
            vsa_lActivityID     = $act->lActivityID,
            vsa_lLastUpdateID   = $glUserID,
            vsa_dteLastUpdate   = NOW()  ");
   }

   function removeUnscheduledActivity($lActivityID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "DELETE FROM vol_events_dates_shifts_assign WHERE vsa_lKeyID=$lActivityID;";
      $this->db->query($sqlStr);
   }





      /* -----------------------------------------------------------------------
                                 R E P O R T S
      --------------------------------------------------------------------------*/
   function lNumRecsInHoursPVADetailReport(
                               &$sRpt,
                               $bUseLimits, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->pvaHoursDetailVars($sRpt, $dteStart, $dteEnd, $lVolID);

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }
      $sqlStr =
           'SELECT vsa_lKeyID
            FROM vol_events_dates_shifts_assign
               INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
               INNER JOIN vol_events_dates        ON vs_lEventDateID = ved_lKeyID
               INNER JOIN vol_events              ON ved_lVolEventID = vem_lKeyID
            WHERE NOT vsa_bRetired
               AND NOT vs_bRetired
               AND NOT vem_bRetired
               AND (ved_dteEvent BETWEEN '.strPrepDate($dteStart).' AND '.strPrepDateTime($dteEnd)." )
               AND vsa_lVolID=$lVolID
            ORDER BY ved_dteEvent, vs_dteShiftStartTime, vsa_lKeyID
            $strLimit;";
      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strVolHoursPVADetailReportExport(&$sRpt,
                                 $bReport, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         return($this->strVolHoursPVADetailReport($sRpt, $lStartRec, $lRecsPerPage));
      }else {
         return($this->strVolHoursPVADetailExport($sRpt));
      }
   }

   private function strVolHoursPVADetailExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat     = strMysqlDateFormat(false);
      $strTimeFormat     = strMysqlTimeFormat();

      $this->pvaHoursDetailVars($sRpt, $dteStart, $dteEnd, $lVolID);

      $strTabName = 'tmp_vol_pvad';
      $this->buildTmpPVADetailTable($strTabName);
      $strWhereOrder =
           'WHERE NOT vsa_bRetired
               AND NOT vs_bRetired
               AND NOT vem_bRetired
               AND (ved_dteEvent BETWEEN '.strPrepDate($dteStart).' AND '.strPrepDateTime($dteEnd)." )
               AND vsa_lVolID=$lVolID
            ORDER BY ved_dteEvent, vs_dteShiftStartTime, vsa_lKeyID;";


      $sqlStr =
           "SELECT vsa_lKeyID, vs_enumDuration
            FROM vol_events_dates_shifts_assign
               INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
               INNER JOIN vol_events_dates        ON vs_lEventDateID = ved_lKeyID
               INNER JOIN vol_events              ON ved_lVolEventID = vem_lKeyID
            $strWhereOrder;";
      $query = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();
      if ($lNumRows > 0){
         foreach ($query->result() as $row){
            $this->addPVADetailRow($row->vsa_lKeyID, $row->vs_enumDuration, $strTabName);
         }
      }
      $sqlStr =
           "SELECT
              vsa_dHoursWorked     AS `Hours Worked`,
              tmp_dHoursScheduled  AS `Scheduled Hours`,
              vem_lKeyID       AS `event ID`,
              vem_strEventName AS `Event`,
              vsa_lKeyID       AS `shift ID`,
              DATE_FORMAT(ved_dteEvent, $strDateFormat) AS `Date of Shift`,
              vs_strShiftName      AS `Shift`,
              DATE_FORMAT(vs_dteShiftStartTime, $strTimeFormat) AS `Start Time`, "
              .strExportFields_People()."
            FROM $strTabName
               INNER JOIN vol_events_dates_shifts_assign ON vsa_lKeyID            = tmp_lShiftID
               INNER JOIN vol_events_dates_shifts        ON vsa_lEventDateShiftID = vs_lKeyID
               INNER JOIN vol_events_dates               ON vs_lEventDateID       = ved_lKeyID
               INNER JOIN vol_events                     ON ved_lVolEventID       = vem_lKeyID
               INNER JOIN volunteers                     ON vsa_lVolID            = vol_lKeyID
               INNER JOIN people_names AS peepTab        ON vol_lPeopleID         = peepTab.pe_lKeyID
            $strWhereOrder;";
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   private function addPVADetailRow($vsa_lKeyID, $enumDuration, $strTabName){
      $sqlStr =
         "INSERT INTO $strTabName
          SET tmp_lShiftID=$vsa_lKeyID,
             tmp_dHoursScheduled = ".tdh\sngXlateDuration($enumDuration).';';
      $this->db->query($sqlStr);
   }

   private function buildTmpPVADetailTable($strTabName){
      $sqlStr = "DROP TABLE IF EXISTS $strTabName;";
      $this->db->query($sqlStr);

      $sqlStr = "
         CREATE TEMPORARY TABLE IF NOT EXISTS $strTabName (
           tmp_lKeyID          int(11) NOT NULL AUTO_INCREMENT,
           tmp_lShiftID        int(11) NOT NULL ,
           tmp_dHoursScheduled decimal(10,2) NOT NULL DEFAULT '0.00',

           PRIMARY KEY (tmp_lKeyID),
           KEY tmp_lShiftID        (tmp_lShiftID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
      $this->db->query($sqlStr);
   }

   private function strVolHoursPVADetailReport($sRpt, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $strTimeFormat = strMysqlTimeFormat();
      $this->pvaHoursDetailVars($sRpt, $dteStart, $dteEnd, $lVolID);

      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";

      $strOut = $this->strHrsPVADetailsLabel($sRpt, $dteStart, $dteEnd, $lVolID);

      $sqlStr =
           'SELECT vsa_lKeyID,
               vem_lKeyID, vem_strEventName,
               ved_lKeyID, ved_dteEvent,
               vsa_dHoursWorked,
               vs_lKeyID, vs_strShiftName, vs_enumDuration,
               DATE_FORMAT(vs_dteShiftStartTime, '.$strTimeFormat.') AS strStart
            FROM vol_events_dates_shifts_assign
               INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
               INNER JOIN vol_events_dates        ON vs_lEventDateID = ved_lKeyID
               INNER JOIN vol_events              ON ved_lVolEventID = vem_lKeyID
            WHERE NOT vsa_bRetired
               AND NOT vs_bRetired
               AND NOT vem_bRetired
               AND (ved_dteEvent BETWEEN '.strPrepDate($dteStart).' AND '.strPrepDateTime($dteEnd)." )
               AND vsa_lVolID=$lVolID
            ORDER BY ved_dteEvent, vs_dteShiftStartTime, vsa_lKeyID
            $strLimit;";
      $query = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();
      if ($lNumRows==0){
         return($strOut.'<br><br><i>There are no records that match your search criteria.</i>');
      }

      $strOut .= '
           <table class="enpRptC">
              <tr>
                 <td class="enpRptLabel">
                    event ID
                 </td>
                 <td class="enpRptLabel">
                    Event
                 </td>
                 <td class="enpRptLabel">
                    Date
                 </td>
                 <td class="enpRptLabel">
                    Shift
                 </td>
                 <td class="enpRptLabel">
                    Start Time
                 </td>
                 <td class="enpRptLabel">
                    Hours Logged
                 </td>
                 <td class="enpRptLabel">
                    Hours Scheduled
                 </td>
              </tr>';
      foreach ($query->result() as $row){
         $lEventID = $row->vem_lKeyID;
         $lEDateID = $row->ved_lKeyID;
         $lShiftID = $row->vs_lKeyID;
         $strOut .=
            '<tr class="makeStripe">
               <td class="enpRpt" style="text-align: center;">'
                  .strLinkView_VolEvent($lEventID, 'View event record', true).'&nbsp;'
                  .str_pad($lEventID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td class="enpRpt" >'
                  .htmlspecialchars($row->vem_strEventName).'
               </td>
               <td class="enpRpt" style="text-align: right">'
                  .date($genumDateFormat, strtotime($row->ved_dteEvent)).'&nbsp;'
                  .strLinkView_VolEventDate($lEDateID, 'View event date/shifts', true).'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($row->vs_strShiftName).'
               </td>
               <td class="enpRpt" style="text-align: right">'
                  .$row->strStart.'
               </td>
               <td class="enpRpt" style="text-align: right">'
                  .number_format($row->vsa_dHoursWorked, 2).'&nbsp;'
                  .strLinkEdit_VolEventHrs($lEventID, $lShiftID, 'Edit volunteer hours for this shift', true).'
               </td>
               <td class="enpRpt" style="text-align: right">'
                  .number_format(tdh\sngXlateDuration($row->vs_enumDuration), 2).'
               </td>
             </tr>';
      }

      $strOut .= '</table><br>';
      return($strOut);
   }

   private function strHrsPVADetailsLabel(&$sRpt, $dteStart, $dteEnd, $lVolID){
      global $genumDateFormat;

      $cVol = new mvol;
      $cVol->loadVolRecsViaVolID($lVolID, true);
      $vRec = &$cVol->volRecs[0];

      return(
        '<table class="enpView">
            <tr>
               <td class="enpViewLabel">
                  Reporting Period:
               </td>
               <td class="enpView">'
                  .$sRpt->strDateRange.'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Volunteer ID:
               </td>
               <td class="enpView">'
                  .strLinkView_Volunteer($vRec->lKeyID, 'View volunteer record', true).'&nbsp;'
                  .str_pad($vRec->lKeyID, 5, '0', STR_PAD_LEFT).'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Volunteer:
               </td>
               <td class="enpView">'
                  .$vRec->strSafeName.'
               </td>
            </tr>
         </table>');
   }

   private function pvaHoursDetailVars(&$sRpt, &$dteStart, &$dteEnd, &$lVolID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $dteStart = $sRpt->dteStart;
      $dteEnd   = $sRpt->dteEnd;
      $lVolID   = $sRpt->lVolID;
   }














   function lNumRecsInHoursPVAReport(
                               &$sRpt,
                               $bUseLimits, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->pvaHoursVars($sRpt, $dteStart, $dteEnd, $bSortVol, $bSortPHrs, $bSortLHours);

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }
      $sqlStr =
           'SELECT vsa_lVolID
            FROM vol_events_dates_shifts_assign
               INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
               INNER JOIN vol_events_dates        ON vs_lEventDateID = ved_lKeyID
               INNER JOIN vol_events              ON ved_lVolEventID = vem_lKeyID
            WHERE NOT vsa_bRetired
               AND NOT vs_bRetired
               AND NOT vem_bRetired
               AND (ved_dteEvent BETWEEN '.strPrepDate($dteStart).' AND '.strPrepDateTime($dteEnd)." )
            GROUP BY vsa_lVolID
            ORDER BY vsa_lVolID, ved_lVolEventID
            $strLimit;";
      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   private function pvaHoursVars(&$sRpt, &$dteStart, &$dteEnd, &$bSortVol, &$bSortPHrs, &$bSortLHours){
      $dteStart    = $sRpt->dteStart;
      $dteEnd      = $sRpt->dteEnd;
      $bSortVol    = $sRpt->bSortVol;
      $bSortPHrs   = $sRpt->bSortPHrs;
      $bSortLHours = $sRpt->bSortLHrs;
   }

   function strVolHoursPVAReportExport(&$sRpt,
                                 $bReport, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         return($this->strVolHoursPVAReport($sRpt, $lStartRec, $lRecsPerPage));
      }else {
         return($this->strVolHoursPVAExport($sRpt));
      }
   }

   private function strHrsPVALabel(&$sRpt, $strLabel){
      global $genumDateFormat;
      $this->pvaHoursVars($sRpt, $dteStart, $dteEnd, $bSortVol, $bSortPHrs, $bSortLHours);

      return('
         <table class="enpView">
            <tr>
               <td class="enpViewLabel">
                  Reporting Period:
               </td>
               <td class="enpView">'
                  .$sRpt->strDateRange.'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Sorted By:
               </td>
               <td class="enpView">'
                  .$strLabel.'
               </td>
            </tr>
         </table>');
   }

   function strVolHoursPVAExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->pvaHoursVars($sRpt, $dteStart, $dteEnd, $bSortVol, $bSortPHrs, $bSortLHours);
      $strTabName = 'tmp_vol_pva';
      $this->buildTmpPVATable($strTabName);
      $this->tmpPVATableInserts('', $strTabName, $dteStart, $dteEnd);
      $this->tmpPVA_ShiftDuration($strTabName, $dteStart, $dteEnd);

      $sqlStr =
          'SELECT
               tmp_lVolID          AS `volunteer ID`,
               tmp_dHoursWorked    AS `Hours Worked`,
               tmp_dHoursScheduled AS `Hours Scheduled`, '
               .strExportFields_People()."
           FROM $strTabName
              INNER JOIN volunteers              ON tmp_lVolID    = vol_lKeyID
              INNER JOIN people_names AS peepTab ON vol_lPeopleID = peepTab.pe_lKeyID
           ORDER BY tmp_strLName, tmp_strFName, tmp_lPeopleID;";
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   function strVolHoursPVAReport(&$sRpt, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $this->pvaHoursVars($sRpt, $dteStart, $dteEnd, $bSortVol, $bSortPHrs, $bSortLHours);

      if ($bSortVol){
         $strLabel = 'Volunteer';
         $strOrder = ' tmp_strLName, tmp_strFName, tmp_lPeopleID ';
      }elseif ($bSortPHrs){
         $strLabel = 'Projected Hours';
         $strOrder = ' tmp_dHoursScheduled, tmp_strLName, tmp_strFName, tmp_lPeopleID ';
      }elseif ($bSortLHours){
         $strLabel = 'Logged Hours';
         $strOrder = ' tmp_dHoursWorked, tmp_strLName, tmp_strFName, tmp_lPeopleID ';
      }

      $strOut = $this->strHrsPVALabel($sRpt, $strLabel);

      $strTabName = 'tmp_vol_pva';
      $this->buildTmpPVATable($strTabName);

      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      $this->tmpPVATableInserts($strLimit, $strTabName, $dteStart, $dteEnd);
      $this->tmpPVA_ShiftDuration($strTabName, $dteStart, $dteEnd);

      $sqlStr = "SELECT * FROM $strTabName ORDER BY $strOrder;";
      $query = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();
      if ($lNumRows==0){
         return($strOut.'<br><br><i>There are no records that match your search criteria.</i>');
      }

      $strOut .= '
           <table class="enpRptC">
              <tr>
                 <td class="enpRptLabel">
                    vol ID
                 </td>
                 <td class="enpRptLabel">
                    people ID
                 </td>
                 <td class="enpRptLabel">
                    Name
                 </td>
                 <td class="enpRptLabel">
                    Hours Logged
                 </td>
                 <td class="enpRptLabel">
                    Hours Scheduled
                 </td>
              </tr>';
      foreach ($query->result() as $row){
         $lVolID    = $row->tmp_lVolID;
         $lPeopleID = $row->tmp_lPeopleID;
         $strOut .=
            '<tr class="makeStripe">
               <td class="enpRpt" style="text-align: center;">'
                  .strLinkView_Volunteer($lVolID, 'View volunteer record', true).'&nbsp;'
                  .str_pad($lVolID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .strLinkView_PeopleRecord($lPeopleID, 'View volunteer record', true).'&nbsp;'
                  .str_pad($lPeopleID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($row->tmp_strLName.', '.$row->tmp_strFName).'
               </td>
               <td class="enpRpt" style="text-align: right">'
                  .number_format($row->tmp_dHoursWorked, 2).'
               </td>
               <td class="enpRpt" style="text-align: right">'
                  .number_format($row->tmp_dHoursScheduled, 2).'&nbsp;'
                  .strLinkView_VolHrsPVA($lVolID, $dteStart, $dteEnd, 'View details', true).'
               </td>
             </tr>';
      }

      $strOut .= '</table><br>';
      return($strOut);
   }

   private function tmpPVA_ShiftDuration($strTabName, $dteStart, $dteEnd){
   //---------------------------------------------------------------------
   // total the projected hours based on shift duration
   //---------------------------------------------------------------------
      $sqlStr =
           "SELECT
               vsa_lVolID, vs_enumDuration
            FROM vol_events_dates_shifts_assign
               INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
               INNER JOIN $strTabName             ON vsa_lVolID       = tmp_lVolID
               INNER JOIN vol_events_dates        ON vs_lEventDateID       = ved_lKeyID
               INNER JOIN vol_events              ON ved_lVolEventID       = vem_lKeyID
            WHERE NOT vsa_bRetired
               AND NOT vs_bRetired
               AND NOT vem_bRetired
               AND (ved_dteEvent BETWEEN ".strPrepDate($dteStart).' AND '.strPrepDateTime($dteEnd)." )
            ORDER BY vsa_lVolID;";

      $query = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();
      if ($lNumRows > 0){
         $lVolGroup = -1;
         $sngVolProjected = 0.0;
         foreach ($query->result() as $row){
            $lVolID = $row->vsa_lVolID;
            if ($lVolGroup != $lVolID){
               if ($lVolGroup > 0){
                  $this->updateProjectedHrs($lVolGroup, $sngVolProjected, $strTabName);
               }
               $lVolGroup = $lVolID;
               $sngVolProjected = 0.0;
            }
            $sngVolProjected += tdh\sngXlateDuration($row->vs_enumDuration);
         }
         $this->updateProjectedHrs($lVolID, $sngVolProjected, $strTabName);
      }
   }

   private function tmpPVATableInserts($strLimit, $strTabName, $dteStart, $dteEnd){
      $sqlStr =
         "INSERT INTO $strTabName
            (
               tmp_lVolID,
               tmp_lPeopleID,
               tmp_strFName,
               tmp_strLName,
               tmp_dHoursWorked
            )
            SELECT
               vsa_lVolID, pe_lKeyID,
               pe_strFName, pe_strLName,
               SUM(vsa_dHoursWorked) AS hoursWorked
            FROM vol_events_dates_shifts_assign
               INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
               INNER JOIN vol_events_dates        ON vs_lEventDateID       = ved_lKeyID
               INNER JOIN vol_events              ON ved_lVolEventID       = vem_lKeyID
               INNER JOIN volunteers              ON vsa_lVolID            = vol_lKeyID
               INNER JOIN people_names            ON vol_lPeopleID         = pe_lKeyID
            WHERE NOT vsa_bRetired
               AND NOT vs_bRetired
               AND NOT vem_bRetired
               AND (ved_dteEvent BETWEEN ".strPrepDate($dteStart).' AND '.strPrepDateTime($dteEnd)." )
               AND NOT pe_bRetired
            GROUP BY vsa_lVolID
            $strLimit;";
      $this->db->query($sqlStr);
   }

   private function updateProjectedHrs($lVolID, $sngVolProjected, $strTabName){
      $sqlStr =
         "UPDATE $strTabName SET tmp_dHoursScheduled=$sngVolProjected WHERE tmp_lVolID=$lVolID;";
      $this->db->query($sqlStr);
   }

   private function buildTmpPVATable($strTabName){
      $sqlStr = "DROP TABLE IF EXISTS $strTabName;";
      $this->db->query($sqlStr);

      $sqlStr = "
         CREATE TEMPORARY TABLE IF NOT EXISTS $strTabName (
           tmp_lKeyID          int(11) NOT NULL AUTO_INCREMENT,
           tmp_lVolID          int(11) NOT NULL ,
           tmp_lPeopleID       int(11) NOT NULL ,
           tmp_strFName        varchar(255) NOT NULL DEFAULT '',
           tmp_strLName        varchar(255) NOT NULL DEFAULT '',
           tmp_dHoursWorked    decimal(10,2) NOT NULL DEFAULT '0.00',
           tmp_dHoursScheduled decimal(10,2) NOT NULL DEFAULT '0.00',

           PRIMARY KEY (tmp_lKeyID),
           KEY tmp_strLName        (tmp_strLName),
           KEY tmp_strFName        (tmp_strFName),
           KEY tmp_dHoursWorked    (tmp_dHoursWorked),
           KEY tmp_dHoursScheduled (tmp_dHoursScheduled)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
      $this->db->query($sqlStr);
   }







   function lNumRecsInHoursReport($dteStart,   $dteEnd,    $bSortEvent,
                                  $bUseLimits, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }
      $sqlStr =
           'SELECT DISTINCT  vsa_lVolID, ved_lVolEventID
            FROM vol_events_dates_shifts_assign
               INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
               INNER JOIN vol_events_dates        ON vs_lEventDateID = ved_lKeyID
               INNER JOIN vol_events              ON ved_lVolEventID = vem_lKeyID
            WHERE NOT vsa_bRetired
               AND NOT vs_bRetired
               AND NOT vem_bRetired
               AND (ved_dteEvent BETWEEN '.strPrepDate($dteStart).' AND '.strPrepDateTime($dteEnd)." )
               AND vsa_dHoursWorked > 0
            ORDER BY vsa_lVolID, ved_lVolEventID
            $strLimit;";
      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   private function strVolHrsLabel(&$sRpt){
      global $genumDateFormat;

      return('
         <table class="enpView">
            <tr>
               <td class="enpViewLabel">
                  Report:
               </td>
               <td class="enpView">
                  Volunteer Hours
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Reporting Period:
               </td>
               <td class="enpView">'
                  .$sRpt->strDateRange.'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Sorted By:
               </td>
               <td class="enpView">'
                  .($sRpt->bSortEvent ? 'Event' : 'Volunteer').'
               </td>
            </tr>
         </table>');
   }

   function strVolHoursReportPage(&$sRpt,
                                  $bReport,  $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $strRptID   = $sRpt->reportID;
      $dteStart   = $sRpt->dteStart;
      $dteEnd     = $sRpt->dteEnd;
      $bSortEvent = $sRpt->bSortEvent;

      if ($bReport){
         $strOut = $this->strVolHrsLabel($sRpt);
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strExport = '';
         $strLimit = '';
      }

      if ($bSortEvent){
         $strOrder = ' vem_strEventName, pe_strLName, pe_strFName, pe_lKeyID';
         $strGroup = ' ved_lVolEventID, pe_lKeyID ';
      }else {
         $strOrder = ' pe_strLName, pe_strFName, vem_strEventName, pe_lKeyID';
         $strGroup = ' pe_lKeyID, ved_lVolEventID ';
      }

      $sqlStr =
        'SELECT
            vem_strEventName, pe_strLName, pe_strFName, vsa_lVolID,
            SUM(vsa_dHoursWorked) AS dHours,
            ved_lVolEventID, pe_lKeyID, vem_dteEventStartDate
         FROM vol_events_dates_shifts_assign
            INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
            INNER JOIN vol_events_dates ON vs_lEventDateID = ved_lKeyID
            INNER JOIN vol_events ON ved_lVolEventID = vem_lKeyID
            INNER JOIN volunteers ON vsa_lVolID = vol_lKeyID
            INNER JOIN people_names ON vol_lPeopleID=pe_lKeyID
         WHERE NOT vsa_bRetired
            AND NOT vs_bRetired
            AND NOT vem_bRetired
            AND (ved_dteEvent BETWEEN '.strPrepDate($dteStart).' AND '.strPrepDateTime($dteEnd)." )

         GROUP BY $strGroup
         HAVING SUM(vsa_dHoursWorked) > 0
         ORDER BY $strOrder
         $strLimit;";

      $query = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();

      if ($lNumRows==0){
         return($strOut.'<br><br><i>There are no records that match your search criteria.</i>');
      }

      if ($bReport){
         $strOut .= $this->strVolHoursRptHTML($query, $bSortEvent, $strRptID);
         return($strOut);
      }else {
         $strExport = $this->strVolHoursRptExport($query, $strRptID);
         return($strExport);
      }
   }

   private function strVolHoursRptHTML(&$query, $bSortEvent, $reportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cReport = new mreports;

      $cReport->loadReportSessionEntry($reportID, $origRpt);

         // setup for link to detailed report
      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_VOLHOURSDETAIL,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => true,
                             'viewFile'       => 'pre_generic_rpt_view',
                             'strDateRange'   => $origRpt->strDateRange,
                             'dteStart'       => $origRpt->dteStart,
                             'dteEnd'         => $origRpt->dteEnd,
                             'bSortEvent'     => $origRpt->bSortEvent);

      $cReport->createReportSessionEntry($reportAttributes);
      $newReportID = $cReport->sRpt->reportID;

      if ($bSortEvent){
         return($this->strVolHoursRptViaEventHTML($reportID, $query, $newReportID));
      }else {
         return($this->strVolHoursRptViaVolHTML($reportID, $query, $newReportID));
      }
   }

   private function strVolHoursRptViaVolHTML($reportID, &$query, $newReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lVolBase = -999;
      $strOut = '';
      $dSumHours  = 0;

      foreach ($query->result() as $row){
         $lEventID = $row->ved_lVolEventID;
         $lVolID = $row->vsa_lVolID;
         $lPID   = $row->pe_lKeyID;
         $dHours = $row->dHours;
         $fIDs = array($lVolID, $lEventID);

         if ($lVolBase != $lVolID){
            if ($lVolBase > 0) {
               $strOut .= '
                    <tr>
                       <td class="enpRpt" style="text-align: left; font-weight: bold;" colspan="1">
                          Total Hours
                       </td>
                       <td class="enpRpt" style="width: 50px; text-align: right; font-weight: bold;">'
                          .number_format($dSumHours, 2).'
                       </td>
                    </tr>';

               $strOut .= '</table><br><br>';
            }
            $lVolBase = $lVolID;
            $dSumHours  = 0;
            $strOut .= '<table class="enpRptC" style="width: 500px;">
                          <tr>
                             <td class="enpRptTitle" colspan="6">'
                                .strLinkView_Volunteer($lVolID, 'View Volunteer Record', true).'&nbsp;'
                                .htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName).'
                             </td>
                          </tr>
                          <tr>
                             <td class="enpRptLabel">
                                Event
                             </td>
                             <td class="enpRptLabel" style="width: 50px;">
                                Hours
                             </td>
                          </tr>';
         }
         $dSumHours += $dHours;
         $strOut .= '
              <tr class="makeStripe">
                 <td class="enpRpt">
                    Event: '.htmlspecialchars($row->vem_strEventName).'&nbsp;'
                    .strLinkView_VolEvent($lEventID, 'View Event', true).'
                 </td>

                 <td class="enpRpt" style="width: 70px; text-align: right;">'
                    .number_format($dHours, 2).'&nbsp;'
                    .strLinkView_RptDetailGeneric($newReportID, $fIDs, 'View details', true).'
                 </td>
              </tr>';
      }

      $strOut .= '
           <tr>
              <td class="enpRpt" style="text-align: left; font-weight: bold;" colspan="1">
                 Total Hours
              </td>
              <td class="enpRpt" style="width: 50px; text-align: right; font-weight: bold;">'
                 .number_format($dSumHours, 2).'
              </td>
           </tr>';

      $strOut .= '</table><br><br>';

      return($strOut);

   }

   private function strVolHoursRptViaEventHTML($reportID, &$query, $newReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lEventBase = -999;
      $strOut = '';

      foreach ($query->result() as $row){
         $lEventID = $row->ved_lVolEventID;
         if ($lEventBase != $lEventID){
            if ($lEventBase > 0) {
               $strOut .= '
                    <tr>
                       <td class="enpRpt" style="text-align: left; font-weight: bold;" colspan="3">
                          Total Hours
                       </td>
                       <td class="enpRpt" style="width: 50px; text-align: right; font-weight: bold;">'
                          .number_format($dSumHours, 2).'
                       </td>
                    </tr>';

               $strOut .= '</table><br><br>';
            }
            $lEventBase = $lEventID;
            $dSumHours  = 0;
            $strOut .= '<table class="enpRptC" style="width: 500px;">
                          <tr>
                             <td class="enpRptTitle" colspan="6">
                                Event: '.htmlspecialchars($row->vem_strEventName).'&nbsp;'
                                .strLinkView_VolEvent($lEventID, 'View Event', true).'
                             </td>
                          </tr>
                          <tr>
                             <td class="enpRptLabel" style="width: 60px;">
                                vol ID
                             </td>
                             <td class="enpRptLabel" style="width: 70px;">
                                people ID
                             </td>
                             <td class="enpRptLabel">
                                Name
                             </td>
                             <td class="enpRptLabel" style="width: 50px;">
                                Hours
                             </td>
                          </tr>';
         }
         $lVolID = $row->vsa_lVolID;
         $lPID   = $row->pe_lKeyID;
         $dHours = $row->dHours;
         $dSumHours += $dHours;
         $fIDs = array($lVolID, $lEventID);
         $strOut .= '
              <tr class="makeStripe">
                 <td class="enpRpt" style="width: 60px; text-align: center;">'
                    .strLinkView_Volunteer($lVolID, 'View Volunteer Record', true).'&nbsp;'
                    .str_pad($lVolID, 5, '0', STR_PAD_LEFT).'
                 </td>
                 <td class="enpRpt" style="width: 70px; text-align: center;">'
                    .strLinkView_PeopleRecord($lPID, 'View People Record', true).'&nbsp;'
                    .str_pad($lPID, 5, '0', STR_PAD_LEFT).'
                 </td>
                 <td class="enpRpt">'
                    .htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName).'
                 </td>
                 <td class="enpRpt" style="width: 50px; text-align: right;">'
                    .number_format($dHours, 2).'&nbsp;'

                    .strLinkView_RptDetailGeneric($newReportID, $fIDs, 'View details', true).'
                 </td>
              </tr>';
      }

      $strOut .= '
           <tr>
              <td class="enpRpt" style="text-align: left; font-weight: bold;" colspan="3">
                 Total Hours
              </td>
              <td class="enpRpt" style="width: 50px; text-align: right; font-weight: bold;">'
                 .number_format($dSumHours, 2).'
              </td>
           </tr>';

      $strOut .= '</table><br><br>';
      return($strOut);
   }

   function lNumRecsInHoursDetailReport($dteStart,   $dteEnd,    $bSortEvent,
                                        $bUseLimits, $lStartRec, $lRecsPerPage,
                                        $lVolID,     $lEventID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }
      $sqlStr =
           'SELECT DISTINCT vsa_lKeyID
            FROM vol_events_dates_shifts_assign
               INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
               INNER JOIN vol_events_dates        ON vs_lEventDateID = ved_lKeyID
               INNER JOIN vol_events              ON ved_lVolEventID = vem_lKeyID
            WHERE NOT vsa_bRetired
               AND NOT vs_bRetired
               AND NOT vem_bRetired
               AND (ved_dteEvent BETWEEN '.strPrepDate($dteStart).' AND '.strPrepDateTime($dteEnd)." )
               AND vsa_dHoursWorked > 0
               AND vsa_lVolID=$lVolID
               AND ved_lVolEventID=$lEventID
            ORDER BY vsa_lVolID, ved_lVolEventID
            $strLimit;";
      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   private function strVolHrsDetailLabel(&$sRpt,
//                            $dteStart, $dteEnd,       $bSortEvent,
                            $lEventID, $strEventName, $lVolID,
                            $strName){
      global $genumDateFormat;

//                  .date($genumDateFormat, $dteStart).' - '.date($genumDateFormat, $dteEnd).'
      return('
         <table class="enpView">
            <tr>
               <td class="enpViewLabel">
                  Report:
               </td>
               <td class="enpView">
                  Volunteer Hours
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
                  Volunteer:
               </td>
               <td class="enpView">'
                  .strLinkView_Volunteer($lVolID, 'View volunteer record', true).'&nbsp;'
                  .str_pad($lVolID, 5, '0', STR_PAD_LEFT).'&nbsp;'.$strName.'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Event:
               </td>
               <td class="enpView">'
                  .strLinkView_VolEvent($lEventID, 'View event', true)
                  .htmlspecialchars($strEventName).'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Sorted By:
               </td>
               <td class="enpView">'
                  .($sRpt->bSortEvent ? 'Event' : 'Volunteer').'
               </td>
            </tr>
         </table>');
   }


   function strVolHoursDetailReportPage(
                                  &$sRpt,
                                  $bReport,  $lStartRec, $lRecsPerPage,
                                  $lVolID,   $lEventID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $cEvent = new mvol_events;
      $cEvent->loadEventsViaEID($lEventID);
      $strEventName  = $cEvent->events[0]->strEventName;
      $dteEventStart = $cEvent->events[0]->dteEventStart;

      $cVol = new mvol;
      $cVol->loadVolRecsViaVolID($lVolID, true);
      $strName = htmlspecialchars($cVol->volRecs[0]->strLName.', '.$cVol->volRecs[0]->strFName);

      if ($bReport){
         $strOut = $this->strVolHrsDetailLabel($sRpt,   //$dteStart, $dteEnd, $bSortEvent,
                              $lEventID, $strEventName, $lVolID, $strName);
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strExport = '';
         $strLimit = '';
      }

      $sqlStr =
        'SELECT
            vsa_dHoursWorked, vs_strShiftName, vs_enumDuration,
            ved_lKeyID, ved_dteEvent,
            TIME_FORMAT(vs_dteShiftStartTime, \'%l:%i %p\') AS dteStartTime
         FROM vol_events_dates_shifts_assign
            INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
            INNER JOIN vol_events_dates ON vs_lEventDateID = ved_lKeyID
         WHERE NOT vsa_bRetired
            AND NOT vs_bRetired
            AND (ved_dteEvent BETWEEN '.strPrepDate($sRpt->dteStart).' AND '.strPrepDateTime($sRpt->dteEnd)." )
            AND vsa_lVolID=$lVolID
            AND vsa_dHoursWorked > 0
            AND ved_lVolEventID=$lEventID
         ORDER BY ved_dteEvent, vs_dteShiftStartTime
         $strLimit;";

      $query = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();
      if ($lNumRows==0){
         return($strOut.'<br><br><i>There are no records that match your search criteria.</i>');
      }

      if ($bReport){
         $strOut .= $this->strVolHoursDetailRptHTML($query, $sRpt->reportID);
         return($strOut);
      }else {
         $strExport = $this->strVolHoursDetailRptExport($query, $sRpt->reportID);
         return($strExport);
      }
   }

   private function strVolHoursDetailRptHTML(&$query, $strRptID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $strOut =
         '<table class="enpRptC">
                <tr>
                   <td class="enpRptLabel">
                      Date
                   </td>
                   <td class="enpRptLabel">
                      Shift
                   </td>
                   <td class="enpRptLabel">
                      Shift Start
                   </td>
                   <td class="enpRptLabel">
                      Duration
                   </td>
                   <td class="enpRptLabel">
                      Hours Worked
                   </td>
                </tr>';

      foreach ($query->result() as $row){
         $strOut .= '
                   <tr class="makeStripe">
                      <td class="enpRpt" style="text-align: center; width: 70pt;">'
                        .date($genumDateFormat, $row->dteEvent).'&nbsp;'
                        .strLinkView_VolEventDate($row->ved_lKeyID, 'View event date', true).'
                      </td>
                      <td class="enpRpt" style="text-align: left; width: 160pt;">'
                         .htmlspecialchars($row->vs_strShiftName).'
                      </td>
                      <td class="enpRpt" style="width: 70px; text-align: center; ">'
                         .$row->dteStartTime.'
                      </td>
                      <td class="enpRpt" style="width: 130px; text-align: center; ">'
                         .$row->vs_enumDuration.'
                     </td>
                      <td class="enpRpt" style="width: 40px; text-align: center; ">'
                         .number_format($row->vsa_dHoursWorked, 2).'
                      </td>

                   </tr>';
      }

      $strOut .= '</table><br>';
      return($strOut);
   }


   function lNumRecsInHoursViaVIDReport(&$sRpt,
                                        $bUseLimits,     $lStartRec,    $lRecsPerPage,
                                        $enumSort){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lVolID = $sRpt->lVolID;
      $bScheduled = $sRpt->bScheduled;
      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }
      if ($bScheduled){
         $sqlStr =
             "SELECT vsa_lKeyID
              FROM vol_events_dates_shifts_assign
                 INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
                 INNER JOIN vol_events_dates        ON vs_lEventDateID = ved_lKeyID
                 INNER JOIN vol_events              ON ved_lVolEventID = vem_lKeyID
              WHERE NOT vsa_bRetired
                 AND NOT vs_bRetired
                 AND NOT vem_bRetired
                 AND vsa_lVolID=$lVolID
              ORDER BY vsa_lVolID, ved_lVolEventID
              $strLimit;";
      }else {
         $sqlStr =
             "SELECT vsa_lKeyID
              FROM vol_events_dates_shifts_assign
              WHERE NOT vsa_bRetired
                 AND vsa_lEventDateShiftID IS NULL
                 AND vsa_lVolID=$lVolID
              ORDER BY vsa_lKeyID
              $strLimit;";
      }

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strHoursViaVIDReport(&$sRpt,
                                 $bReport, $lStartRec, $lRecsPerPage,
                                 $enumSort){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $bScheduled = $sRpt->bScheduled;

      if (!$bReport){
         if ($bScheduled){
            return($this->strVolHoursViaVIDRptExport($sRpt));
         }else {
            return($this->strVolUnHoursViaVIDRptExport($sRpt));
         }
      }

      $lVolID     = $sRpt->lVolID;
      $strRptID   = $sRpt->reportID;
      $cVol = new mvol;
      $cVol->loadVolRecsViaVolID($lVolID, true);
      $strName    = htmlspecialchars($cVol->volRecs[0]->strFName.' '.$cVol->volRecs[0]->strLName);
      $strVolLink = strLinkView_Volunteer($lVolID, 'View volunteer record', true);

      $strOut = '';
      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";

      switch ($enumSort){
         case 'date':
            $strSort = ' ved_dteEvent DESC, vs_dteShiftStartTime, vem_strEventName, vem_lKeyID ';
            break;
         case 'event':
         default:
            $strSort = ' vem_strEventName, ved_dteEvent, vs_dteShiftStartTime, vem_lKeyID ';
            break;
      }

      if ($bScheduled){
         $sqlStr =
             "SELECT
                 vem_lKeyID, vem_strEventName, ved_lKeyID,
                 vsa_dHoursWorked, vs_strShiftName, vs_enumDuration,
                 ved_dteEvent, vem_dteEventStartDate,
                 TIME_FORMAT(vs_dteShiftStartTime, '%l:%i %p') AS dteShiftStartTime,
                 jc.lgen_strListItem AS strJobCode
              FROM vol_events_dates_shifts_assign
                 INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
                 INNER JOIN vol_events_dates        ON vs_lEventDateID = ved_lKeyID
                 INNER JOIN vol_events              ON ved_lVolEventID = vem_lKeyID
                 LEFT  JOIN lists_generic AS jc     ON vs_lJobCode     = jc.lgen_lKeyID
              WHERE NOT vsa_bRetired
                 AND NOT vs_bRetired
                 AND NOT vem_bRetired
                 AND vsa_lVolID=$lVolID
              ORDER BY $strSort
              $strLimit;";
      }else {
         $sqlStr =
           "SELECT
               vsa_lKeyID, vsa_strNotes, vsa_dHoursWorked,
               vsa_dteActivityDate,
               TIME_FORMAT(vsa_dteActivityDate, '%l:%i %p') AS strActivityStartTime,
               vsa_lActivityID, act.lgen_strListItem AS strActivity,
               vsa_lJobCode, jc.lgen_strListItem AS strJobCode

            FROM vol_events_dates_shifts_assign
               INNER JOIN lists_generic AS act ON vsa_lActivityID = act.lgen_lKeyID
               LEFT  JOIN lists_generic AS jc  ON vsa_lJobCode    = jc.lgen_lKeyID
            WHERE
               NOT vsa_bRetired
               AND vsa_lVolID=$lVolID
               AND vsa_lEventDateShiftID IS NULL
            ORDER BY vsa_dteActivityDate DESC, vsa_lKeyID
            $strLimit;";
      }

      $query = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();
      if ($lNumRows==0){
         return($strOut.'<br><br><i>There are no records that match your search criteria.</i>');
      }

      if ($bScheduled){
         $strOut .= $this->strVolHoursViaVIDRptHTML($query, $strRptID, $lStartRec, $lRecsPerPage, $strName, $strVolLink);
      }else {
         $strOut .= $this->strVolUnHoursViaVIDRptHTML($query, $sRpt, $lStartRec, $lRecsPerPage, $strName, $strVolLink);
      }
      return($strOut);
   }

   private function strVolUnHoursViaVIDRptHTML(&$query, &$sRpt, $lStartRec, $lRecsPerPage, $strName, $strVolLink){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $lVolID     = $sRpt->lVolID;

      $strOut =
         '<table class="enpRptC"style="width: 800px;">
            <tr>
               <td class="enpRptTitle" colspan="7">
                  Unscheduled Volunteer Hours for '.$strName.'&nbsp;'.$strVolLink.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                  .strLinkAdd_VolUnschedHrs($lVolID, 'Add new volunteer activity', true).'
               </td>
            </tr>
            <tr class="makeStripe">
               <td class="enpRptLabel">
                  activityID
               </td>
               <td class="enpRptLabel">
                  &nbsp;
               </td>
               <td class="enpRptLabel">
                  Date
               </td>
               <td class="enpRptLabel">
                  Time
               </td>
               <td class="enpRptLabel" >
                  Hours<br>Logged
               </td>
               <td class="enpRptLabel" >
                  Activity / Job Code
               </td>
               <td class="enpRptLabel" style="width: 220pt;">
                  Notes
               </td>
            </tr>';

      foreach ($query->result() as $row){

         $dHours = $row->vsa_dHoursWorked;
         if ($dHours==0){
            $strHours = '-';
            $strAlign = 'center';
         }else {
            $strHours = number_format($dHours, 2);
            $strAlign = 'right';
         }
         $dteActivity = dteMySQLDate2Unix($row->vsa_dteActivityDate);
         $lActivityID = $row->vsa_lKeyID;

         $strJobCode = $row->strJobCode.'';
         if ($strJobCode == ''){
            $strJobCode = '<i>(not set)</i>';
         }else {
            $strJobCode = htmlspecialchars($strJobCode);
         }

         $strOut .='
            <tr class="makeStripe">
               <td class="enpRpt" style="width: 65px; text-align: center;">'
                  .strLinkEdit_VolUnschedHrs($lVolID, $lActivityID, 'Edit volunteer activity', true).'&nbsp;'
                  .str_pad($lActivityID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td class="enpRpt" style="width: 25px; text-align: center;">'
                  .strLinkRem_VolUnschedHrs($lVolID, $lActivityID, 'Remove volunteer activity', true, true).'
               </td>
               <td class="enpRpt" style="width: 100px;">'
                  .date($genumDateFormat, $dteActivity).'
               </td>
               <td class="enpRpt" style="width: 130px;">'
                  .$row->strActivityStartTime.'
               </td>
               <td class="enpRpt" style="text-align:'.$strAlign.'; width: 60px;">'
                  .$strHours.'
               </td>
               <td class="enpRpt" style="width: 160px;">'
                  .htmlspecialchars($row->strActivity).'<br>
                  <b>Job code:</b> '.$strJobCode.'
               </td>
               <td class="enpRpt" style="width: 220pt;">'
                  .nl2br(htmlspecialchars($row->vsa_strNotes)).'
               </td>
            </tr>';
      }
      $strOut .= '</table>';
      return($strOut);
   }

   function strVolHoursViaVIDRptExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat     = strMysqlDateFormat(false);

      $lVolID = $sRpt->lVolID;

      $sqlStr =
          "SELECT
              vsa_lVolID          AS `Volunteer ID`,
              pe_lKeyID           AS `People ID`,
              pe_strFName         AS `First Name`,
              pe_strLName         AS `Last Name`,
              vem_lKeyID          AS `Event ID`,
              vem_strEventName    AS `Event Name`,
              ved_lKeyID          AS `Event Date ID`,
              vs_strShiftName     AS `Shift Name`,
              jc.lgen_strListItem AS `Job Code`,
              DATE_FORMAT(ved_dteEvent, $strDateFormat)          AS `Date of Shift`,
              TIME_FORMAT(vs_dteShiftStartTime, '%l:%i %p')      AS `Shift Start Time`,
              vs_enumDuration                                    AS `Duration`,
              FORMAT(vsa_dHoursWorked, 2)                        AS `Hours Worked`
           FROM vol_events_dates_shifts_assign
              INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
              INNER JOIN vol_events_dates        ON vs_lEventDateID = ved_lKeyID
              INNER JOIN vol_events              ON ved_lVolEventID = vem_lKeyID
              INNER JOIN volunteers              ON vol_lKeyID      = vsa_lVolID
              INNER JOIN people_names            ON pe_lKeyID       = vol_lPeopleID
              LEFT  JOIN lists_generic   AS jc   ON vs_lJobCode     = jc.lgen_lKeyID
           WHERE NOT vsa_bRetired
              AND NOT vs_bRetired
              AND NOT vem_bRetired
              AND vsa_lVolID=$lVolID
           ORDER BY vsa_lKeyID;";
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   function strVolUnHoursViaVIDRptExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat     = strMysqlDateFormat(false);

      $lVolID = $sRpt->lVolID;

      $sqlStr =
           "SELECT
               vsa_lKeyID AS `Activity ID`,
               act.lgen_strListItem AS `Activity`,
               jc.lgen_strListItem AS `Job Code`,
               DATE_FORMAT(vsa_dteActivityDate, $strDateFormat) AS `Date of Activity`,
               TIME_FORMAT(vsa_dteActivityDate, '%l:%i %p') AS `Activity Start Time`,
               FORMAT(vsa_dHoursWorked, 2) AS `Hours Logged`,
               vsa_strNotes AS `Notes`, "
               .strExportFields_Vol()."
            FROM vol_events_dates_shifts_assign
               INNER JOIN lists_generic AS act     ON vsa_lActivityID = act.lgen_lKeyID
               INNER JOIN volunteers               ON vol_lKeyID      = vsa_lVolID
               INNER JOIN people_names  AS peepTab ON pe_lKeyID       = vol_lPeopleID
               LEFT  JOIN lists_generic AS jc      ON vsa_lJobCode    = jc.lgen_lKeyID
            WHERE
               NOT vsa_bRetired
               AND vsa_lVolID=$lVolID
               AND vsa_lEventDateShiftID IS NULL
            ORDER BY vsa_dteActivityDate DESC, vsa_lKeyID;";

      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   private function strVolHoursViaVIDRptHTML(&$query, $strRptID, $lStartRec, $lRecsPerPage, $strName, $strVolLink){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $strLinkSortBase = 'reports/reports/run/'.$strRptID.'/'.$lStartRec.'/'.$lRecsPerPage.'/';
      $strOut =
         '<table class="enpRptC"style="width: 800px;">
            <tr>
               <td class="enpRptTitle" colspan="5">
                  Scheduled Volunteer Hours for '.$strName.'&nbsp;'.$strVolLink.'
               </td>
            </tr>
            <tr class="makeStripe">
               <td class="enpRptLabel" style="text-align:center;">
                  Event<br><span style="font-weight: normal;">'
                     .anchor($strLinkSortBase.'event', '(sort)').'
                  </span>
               </td>
               <td class="enpRptLabel" style="text-align:center;">
                  Shift / Job Code
               </td>
               <td class="enpRptLabel" style="text-align:center;">
                  Date<br><span style="font-weight: normal;">'
                     .anchor($strLinkSortBase.'date', '(sort)').'
                  </span>
               </td>
               <td class="enpRptLabel" style="text-align:center;">
                  Time/Duration
               </td>
               <td class="enpRptLabel" style="text-align:center;">
                  Hours<br>Logged
               </td>
            </tr>';
      foreach ($query->result() as $row){
         $lEventID = $row->vem_lKeyID;
         $lDateID  = $row->ved_lKeyID;

         $dHours = $row->vsa_dHoursWorked;
         if ($dHours==0){
            $strHours = '-';
            $strAlign = 'center';
         }else {
            $strHours = number_format($dHours, 2);
            $strAlign = 'right';
         }

         $strJobCode = $row->strJobCode.'';
         if ($strJobCode == ''){
            $strJobCode = '<i>(not set)</i>';
         }else {
            $strJobCode = htmlspecialchars($strJobCode);
         }

         $strOut .='
            <tr class="makeStripe">
               <td class="enpRpt" style="width: 240px;">'
                  .strLinkView_VolEvent($lEventID, 'View Event', true).'&nbsp;'
                  .htmlspecialchars($row->vem_strEventName).'
               </td>
               <td class="enpRpt" >'
                  .htmlspecialchars($row->vs_strShiftName).'<br>
                  <b>Job code:</b> '.$strJobCode.'
               </td>
               <td class="enpRpt" style="width: 100px;">'
                  .strLinkView_VolEventDate($lDateID, 'View event date', true).'&nbsp'
                  .date($genumDateFormat, dteMySQLDate2Unix($row->ved_dteEvent)).'
               </td>
               <td class="enpRpt" style="width: 130px;">'
                  .$row->vs_enumDuration.'
               </td>
               <td class="enpRpt" style="text-align:'.$strAlign.'; width: 60px;">'
                  .$strHours.'
               </td>
            </tr>';
      }
      $strOut .= '</table>';
      return($strOut);
   }

   function strVolHoursViaYearReport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lYear = $sRpt->lYear;

         // non-scheduled
      $hoursNonS = array();
      for ($idx=1; $idx<=12; ++$idx){
         $sqlStr =
              "SELECT SUM(vsa_dHoursWorked) AS dNumHrs
               FROM vol_events_dates_shifts_assign
               WHERE
                  NOT vsa_bRetired
                  AND vsa_lEventDateShiftID IS NULL
                  AND YEAR(vsa_dteActivityDate)=$lYear
                  AND MONTH(vsa_dteActivityDate)=$idx;";
         $query = $this->db->query($sqlStr);
         $row = $query->row();
         $hoursNonS[$idx]  = $row->dNumHrs;
      }

         // scheduled
      $hoursS = array();
      for ($idx=1; $idx<=12; ++$idx){
         $sqlStr =
              "SELECT SUM(vsa_dHoursWorked) AS dNumHrs
               FROM vol_events_dates_shifts_assign
                  INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
                  INNER JOIN vol_events_dates        ON vs_lEventDateID = ved_lKeyID
                  INNER JOIN vol_events              ON ved_lVolEventID = vem_lKeyID
               WHERE NOT vsa_bRetired
                  AND NOT vs_bRetired
                  AND NOT vem_bRetired
                  AND YEAR( ved_dteEvent)=$lYear
                  AND MONTH(ved_dteEvent)=$idx;";
         $query = $this->db->query($sqlStr);
         $row = $query->row();
         $hoursS[$idx]  = $row->dNumHrs;
      }

      $strOut = '<br>
         <table class="enpRptC">
            <tr>
               <td colspan="5" class="enpRptTitle">
                  Volunteer Hours for the Year '.$lYear.'
               </td>
            </tr>';

      $strOut .= '
            <tr>
               <td class="enpRptLabel">
                  Month
               </td>
               <td class="enpRptLabel">
                  Unscheduled Hours
               </td>
               <td class="enpRptLabel">
                  Scheduled Hours
               </td>
               <td class="enpRptLabel">
                  Total
               </td>
               <td class="enpRptLabel">
                  Vol.<br>Summary
               </td>
            </tr>';

      $dTotS = $dTotNonS = 0.0;
      for ($idx=1; $idx<=12; ++$idx){
         $strOut .= '
               <tr class="makeStripe">
                  <td class="enpRpt">'
                     .strXlateMonth($idx).'
                  </td>
                  <td class="enpRpt" style="text-align: right; padding-right: 10px;">'
                     .number_format($hoursNonS[$idx], 2).'
                  </td>
                  <td class="enpRpt" style="text-align: right; padding-right: 10px;">'
                     .number_format($hoursS[$idx], 2).'
                  </td>
                  <td class="enpRpt" style="text-align: right; padding-right: 10px; padding-left: 15px; font-weight: bold;">'
                     .number_format($hoursS[$idx]+$hoursNonS[$idx], 2).'&nbsp;'
                     .strLinkView_VolHrsViaYrMon($lYear, $idx, 'date', 'View monthly volunteer hours', true).'
                  </td>
                  <td class="enpRpt" style="text-align: center;">'
                     .strLinkView_VolSumHrsViaYrMon($lYear, $idx, 'View volunteer summary', true).'
                  </td>
               </tr>';
         $dTotS    += $hoursS[$idx];
         $dTotNonS += $hoursNonS[$idx];
      }

      $strOut .= '
            <tr class="makeStripe">
               <td class="enpRpt"><b>
                  Total</b>
               </td>
               <td class="enpRpt" style="text-align: right; padding-right: 10px; font-weight: bold;">'
                  .number_format($dTotNonS, 2).'
               </td>
               <td class="enpRpt" style="text-align: right; padding-right: 10px; font-weight: bold;">'
                  .number_format($dTotS, 2).'
               </td>
               <td class="enpRpt" style="text-align: right; padding-right: 10px; font-weight: bold;">'
                  .number_format($dTotNonS+$dTotS, 2).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
               </td>
                  <td class="enpRpt" style="text-align: center;">'
//                     .strLinkView_VolSumHrsViaYrMon($lYear, null, 'View annual volunteer summary', true).'
                 .'&nbsp;</td>
            </tr>';


      $strOut .= '</table><br>';
      return($strOut);
   }

   function lNumRecsInHoursMonthDetailReport(
                           &$sRpt,
                           $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lYear = $sRpt->lYear;
      $lMon  = $sRpt->lMon;
      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      $strTab = 'tmp_vol_hrs';
      $this->buildPopHrsTmp($strTab, $lYear, $lMon);

      $sqlStr =
          "SELECT tmphr_lKeyID
           FROM $strTab
           $strLimit;";

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strVolHoursViaMonthReportExport(
                                   &$sRpt,
                                   $bReport,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lYear = $sRpt->lYear;
      $lMon  = $sRpt->lMon;

      if ($bReport){
         return($this->strVolHoursViaMonthReport($sRpt, $lYear, $lMon, $lStartRec, $lRecsPerPage));
      }else {
         return($this->strVolHoursViaMonthExport($sRpt, $lYear, $lMon));
      }
   }

   function strVolHoursViaMonthExport($sRpt, $lYear, $lMon){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat = strMysqlDateFormat(false);

      $strTab = 'tmp_vol_hrs';
      $this->buildPopHrsTmp($strTab, $lYear, $lMon);

      $sqlStr =
           "SELECT
               vsa_lKeyID       AS `Activity ID`,
               lgen_strListItem AS `Activity`,
               DATE_FORMAT(tmphr_dteVolActivity, $strDateFormat) AS `Date of Activity`,
               FORMAT(vsa_dHoursWorked, 2) AS `Hours Logged`,
               IF (vem_lKeyID IS NULL, 'n/a', vem_lKeyID) AS `Event ID`,
               IF (vem_lKeyID IS NULL, '(unscheduled)', vem_strEventName) AS `Event Name`,
               IF (vem_lKeyID IS NULL, 'n/a', vs_strShiftName) AS `Shift Name`,
               vsa_strNotes AS `Notes`, "
                  .strExportFields_Vol()."
             FROM $strTab
                INNER JOIN vol_events_dates_shifts_assign ON vsa_lKeyID            = tmphr_lEventAssignID
                INNER JOIN volunteers                     ON tmphr_lVolID          = vol_lKeyID
                INNER JOIN people_names AS peepTab        ON vol_lPeopleID         = peepTab.pe_lKeyID
                LEFT  JOIN vol_events_dates_shifts        ON vsa_lEventDateShiftID = vs_lKeyID
                LEFT  JOIN vol_events_dates               ON vs_lEventDateID       = ved_lKeyID
                LEFT  JOIN vol_events                     ON ved_lVolEventID       = vem_lKeyID
                LEFT  JOIN lists_generic                  ON vsa_lActivityID       = lgen_lKeyID

            ORDER BY tmphr_dteVolActivity, vsa_lKeyID;";

      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   function strVolHoursViaMonthReport($sRpt, $lYear, $lMon, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      $strTab = 'tmp_vol_hrs';

      switch ($sRpt->strSort){
         case 'name':
            $strOrder = ' pe_strLName, pe_strFName, pe_strMName, pe_lKeyID, tmphr_dteVolActivity, tmphr_lEventAssignID ';
            break;
         case 'event':
            $strOrder = ' vem_strEventName, vem_lKeyID, tmphr_dteVolActivity, tmphr_lEventAssignID ';
            break;
         case 'date':
         default:
            $strOrder = ' tmphr_dteVolActivity, tmphr_lEventAssignID ';
            break;
      }

      $strTab = 'tmp_vol_hrs';
      $this->buildPopHrsTmp($strTab, $lYear, $lMon);

      $sqlStr =
         "SELECT
             vsa_lKeyID, tmphr_dteVolActivity,
             tmphr_lVolID, vsa_dHoursWorked, vsa_lEventDateShiftID,
             lgen_strListItem,
             vs_strShiftName, vs_lEventDateID, vem_lKeyID, vem_strEventName,
             pe_strFName, pe_strLName

          FROM $strTab
             INNER JOIN vol_events_dates_shifts_assign ON vsa_lKeyID            = tmphr_lEventAssignID
             INNER JOIN volunteers                     ON tmphr_lVolID          = vol_lKeyID
             INNER JOIN people_names                   ON vol_lPeopleID         = pe_lKeyID
             LEFT  JOIN vol_events_dates_shifts        ON vsa_lEventDateShiftID = vs_lKeyID
             LEFT  JOIN vol_events_dates               ON vs_lEventDateID       = ved_lKeyID
             LEFT  JOIN vol_events                     ON ved_lVolEventID       = vem_lKeyID
             LEFT  JOIN lists_generic                  ON vsa_lActivityID=lgen_lKeyID

          ORDER BY $strOrder
          $strLimit;";
      $query  = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();
      if ($lNumRows==0) return('<br><i>There are no volunteer hours for '.strXlateMonth($lMon).' '.$lYear.'.</i><br>');

      $strOut = '
         <table class="enpRptC">
            <tr>
               <td colspan="7" class="enpRptTitle">
                  Volunteer activities for '.strXlateMonth($lMon).' '.$lYear.'
               </td>
            </tr>';

      $strOut .= '
            <tr>
               <td class="enpRptLabel">
                  Date<br>'
                  .strLinkView_VolHrsViaYrMon($lYear, $lMon, 'date', '(sort)', false, 'style="font-weight: normal;"').'
               </td>
               <td class="enpRptLabel">
                  Vol ID
               </td>
               <td class="enpRptLabel">
                  Volunteer<br>'
                  .strLinkView_VolHrsViaYrMon($lYear, $lMon, 'name', '(sort)', false, 'style="font-weight: normal;"').'
               </td>
               <td class="enpRptLabel">
                  Event<br>'
                  .strLinkView_VolHrsViaYrMon($lYear, $lMon, 'event', '(sort)', false, 'style="font-weight: normal;"').'
               </td>
               <td class="enpRptLabel">
                  Shift/Activity
               </td>
               <td class="enpRptLabel">
                  Hours
               </td>
            </tr>';

      foreach ($query->result() as $row){
         $lVolID = $row->tmphr_lVolID;
         if (is_null($row->vsa_lEventDateShiftID)){
            $strEvent    = '<i>(unscheduled)</i>';
            $strActivity =
                           strLinkEdit_VolUnschedHrs($lVolID, $row->vsa_lKeyID, 'Edit activity', true, '', 12).'&nbsp;'
                          .htmlspecialchars($row->lgen_strListItem);
         }else {
            $strEvent    =
                           strLinkView_VolEvent($row->vem_lKeyID, 'View event', true).'&nbsp;'
                          .htmlspecialchars($row->vem_strEventName);
            $strActivity =
                           strLinkView_VolEventDate($row->vs_lEventDateID, 'View event date/shifts', true).'&nbsp;'
                          .htmlspecialchars($row->vs_strShiftName);
         }
         $strOut .= '
            <tr class="makeStripe">
               <td class="enpRpt">'
                  .date($genumDateFormat, dteMySQLDate2Unix($row->tmphr_dteVolActivity)).'
               </td>
               <td class="enpRpt">'
                  .str_pad($lVolID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkView_Volunteer($lVolID, 'View volunteer record', true).'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName).'
               </td>
               <td class="enpRpt">'
                  .$strEvent.'
               </td>
               <td class="enpRpt">'
                  .$strActivity.'
               </td>
               <td class="enpRpt" style="text-align: right; padding-right: 3px;">'
                  .number_format($row->vsa_dHoursWorked, 2).'
               </td>
            </tr>';
      }

      $strOut .= '</table><br>';
      return($strOut);
   }




   /* --------------------------------------------------------
         Temporary Table utilities for consolidating
         volunteer hours
      -------------------------------------------------------- */
   function buildPopHrsTmp($strTab, $lYear, $lMon, $lVolID=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->buildVolHrsTmpTable($strTab);

         // unscheduled
      $sqlWhere = " AND vsa_dHoursWorked > 0 ";
      if (!is_null($lYear))  $sqlWhere .= " AND YEAR(vsa_dteActivityDate)=$lYear ";
      if (!is_null($lMon))   $sqlWhere .= " AND MONTH(vsa_dteActivityDate)=$lMon ";
      if (!is_null($lVolID)) $sqlWhere .= " AND vsa_lVolID=$lVolID ";
      $this->addUnscheduledToTmp($strTab, $sqlWhere);

         // scheduled / event
      $sqlWhere = " AND vsa_dHoursWorked > 0 ";
      if (!is_null($lYear))  $sqlWhere .= " AND YEAR(ved_dteEvent)=$lYear ";
      if (!is_null($lMon))   $sqlWhere .= " AND MONTH(ved_dteEvent)=$lMon ";
      if (!is_null($lVolID)) $sqlWhere .= " AND vsa_lVolID=$lVolID ";
      $this->addScheduledToTmp($strTab, $sqlWhere);
   }

   private function buildVolHrsTmpTable($strTableName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "DROP TABLE IF EXISTS $strTableName;";
      $this->db->query($sqlStr);

      $sqlStr =
//        "CREATE TABLE $strTableName (
        "CREATE TEMPORARY TABLE $strTableName (
           tmphr_lKeyID            int(11) NOT NULL AUTO_INCREMENT,
           tmphr_lEventAssignID    int(11) DEFAULT NULL,
           tmphr_lVolID            int(11) NOT NULL COMMENT 'Foreign key to volunteers',
           tmphr_dteVolActivity    datetime DEFAULT NULL ,
           tmphr_lEventDateShiftID int(11) DEFAULT NULL COMMENT 'Foreign key to vol_events_dates_shifts / null for simple vol hrs',

           PRIMARY KEY (tmphr_lKeyID),
           KEY tmphr_lEventAssignID    (tmphr_lEventAssignID),
           KEY tmphr_lEventDateShiftID (tmphr_lEventDateShiftID),
           KEY tmphr_dteVolActivity    (tmphr_dteVolActivity),
           KEY tmphr_lVolID            (tmphr_lVolID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
      $this->db->query($sqlStr);
   }

   private function addUnscheduledToTmp($strTableName, $sqlWhere){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "INSERT INTO $strTableName
             (
               tmphr_lEventAssignID,
               tmphr_lVolID,
               tmphr_dteVolActivity,
               tmphr_lEventDateShiftID
             )

             SELECT
                vsa_lKeyID,
                vsa_lVolID,
                vsa_dteActivityDate,
                NULL
             FROM vol_events_dates_shifts_assign
             WHERE NOT vsa_bRetired
                AND vsa_lEventDateShiftID IS NULL
                $sqlWhere;";
      $this->db->query($sqlStr);
   }

   private function addScheduledToTmp($strTableName, $sqlWhere){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "INSERT INTO $strTableName
             (
               tmphr_lEventAssignID,
               tmphr_lVolID,
               tmphr_dteVolActivity,
               tmphr_lEventDateShiftID
             )

            SELECT
                vsa_lKeyID,
                vsa_lVolID,
                ved_dteEvent,
                vsa_lEventDateShiftID
            FROM vol_events_dates_shifts_assign
               INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
               INNER JOIN vol_events_dates        ON vs_lEventDateID = ved_lKeyID
               INNER JOIN vol_events              ON ved_lVolEventID = vem_lKeyID
            WHERE NOT vsa_bRetired
               AND NOT vs_bRetired
               AND NOT vem_bRetired
             $sqlWhere;";
      $this->db->query($sqlStr);
   }

   private function hrsSumOpts(&$sRpt, &$bUseYear, &$lYear, &$bUseMon, &$lMon, &$bUseVolID, &$lVolID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $bUseYear = !is_null($sRpt->lYear);
      if ($bUseYear){
         $lYear = (integer)$sRpt->lYear;
      }else {
         $lYear = null;
      }

      $bUseMon = !is_null($sRpt->lMon);
      if ($bUseMon){
         $lMon = (integer)$sRpt->lMon;
      }else {
         $lMon = null;
      }

      $bUseVolID = !is_null($sRpt->lVolID);
      if ($bUseVolID){
         $lVolID = (integer)$sRpt->lVolID;
      }else {
         $lVolID = null;
      }
   }

   public function lNumRecsInHoursMonthSumReport(
                                       &$sRpt,
                                       $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->hrsSumOpts($sRpt, $bUseYear, $lYear, $bUseMon, $lMon, $bUseVolID, $lVolID);

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      $strTab = 'tmp_vol_hrs';
      $this->buildPopHrsTmp($strTab, $lYear, $lMon, $lVolID);

      $sqlStr =
          "SELECT DISTINCTROW tmphr_lVolID
           FROM $strTab
           ORDER BY tmphr_lVolID
           $strLimit;";

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strVolHoursSumReportExport(
                                   &$sRpt,
                                   $bReport,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         return($this->strVolHoursSumReport($sRpt, $lStartRec, $lRecsPerPage));
      }else {
         return($this->strVolHoursSumExport($sRpt));
      }
   }

   function strVolHoursSumExport($sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat = strMysqlDateFormat(false);
      $this->hrsSumOpts($sRpt, $bUseYear, $lYear, $bUseMon, $lMon, $bUseVolID, $lVolID);

      $strTab = 'tmp_vol_hrs';
      $this->buildPopHrsTmp($strTab, $lYear, $lMon, $lVolID);
      if ($bUseMon){
         $strMonth = strXlateMonth($lMon);
      }else {
         $strMonth = '(all year)';
      }
      $sqlStr =
           "SELECT
               SUM(vsa_dHoursWorked) AS `Hours Worked`,
               '$lYear' AS `Year`,
               '$strMonth' AS `Month`, "
                  .strExportFields_Vol()."
             FROM $strTab
                INNER JOIN vol_events_dates_shifts_assign ON vsa_lKeyID            = tmphr_lEventAssignID
                INNER JOIN volunteers                     ON tmphr_lVolID          = vol_lKeyID
                INNER JOIN people_names AS peepTab        ON vol_lPeopleID         = peepTab.pe_lKeyID

            GROUP BY tmphr_lVolID
            ORDER BY pe_strLName, pe_strFName, tmphr_lVolID;";

      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   private function strVolHoursSumReport(&$sRpt, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->hrsSumOpts($sRpt, $bUseYear, $lYear, $bUseMon, $lMon, $bUseVolID, $lVolID);
      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";

      $strTab = 'tmp_vol_hrs';

      $strWhere = '1';
      if ($bUseVolID){
         $cVol = new mvol;
whereAmI(); die;
      }elseif ($bUseMon){
         $strMonth = strXlateMonth($lMon);
         $strTitle = 'Volunteer Summary for '.$strMonth.' '.$lYear;
         $strFail = 'There are no volunteer hours for '.$strMonth.' '.$lYear;
      }else {
         $strTitle = 'Volunteer Summary for the Year '.$lYear;
         $strFail = 'There are no volunteer hours for the year '.$lYear;
      }

      $sqlStr =
          "SELECT SUM(vsa_dHoursWorked) AS dHours,
              pe_strFName, pe_strLName, tmphr_lVolID
           FROM $strTab
              INNER JOIN vol_events_dates_shifts_assign ON vsa_lKeyID            = tmphr_lEventAssignID
              INNER JOIN volunteers                     ON tmphr_lVolID          = vol_lKeyID
              INNER JOIN people_names                   ON vol_lPeopleID         = pe_lKeyID

           WHERE $strWhere
           GROUP BY tmphr_lVolID
           ORDER BY pe_strLName, pe_strFName, tmphr_lVolID;";

      $query  = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();
      if ($lNumRows==0) return('<br><i>'.$strFail.'.</i><br>');

      $strOut = '
          <table class="enpRptC">
             <tr>
                <td class="enpRptTitle" colspan="5">'
                   .$strTitle.'
                </td>
             </tr>';

      $strOut .= '
             <tr>
                <td class="enpRptLabel" >
                   Vol ID
                </td>
                <td class="enpRptLabel" >
                   Volunteer
                </td>
                <td class="enpRptLabel" >
                   Total Hours
                </td>
                <td class="enpRptLabel" >
                   Details
                </td>
             </tr>';

      foreach ($query->result() as $row){
         $lVolID = $row->tmphr_lVolID;
         $strOut .= '
            <tr class="makeStripe">
               <td class="enpRpt">'
                  .str_pad($lVolID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkView_Volunteer($lVolID, 'View volunteer record', true).'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName).'
               </td>
               <td class="enpRpt" style="text-align: right; padding-right: 3px;">'
                  .number_format($row->dHours, 2).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .strLinkView_VolDetailHrsViaYrMon($lYear, $lMon, $lVolID, 'View details', true).'
            </tr>';
      }

      $strOut .= '</table><br>';
      return($strOut);
   }



      /* -----------------------------------------------------------------
               Volunteer hours detail - scheduled and unscheduled
               Based on Year, Month, and Volunteer ID
         ----------------------------------------------------------------- */
   function lNumRecsInHoursVolDetailReport(
                                       &$sRpt,
                                       $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->hrsSumOpts($sRpt, $bUseYear, $lYear, $bUseMon, $lMon, $bUseVolID, $lVolID);

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      $strTab = 'tmp_vol_hrs';
      $this->buildPopHrsTmp($strTab, $lYear, $lMon, $lVolID);

      $sqlStr =
          "SELECT tmphr_lKeyID
           FROM $strTab
           ORDER BY tmphr_lVolID
           $strLimit;";

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strVolHoursDetailReportExport(
                                   &$sRpt,
                                   $bReport,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         return($this->strVolHoursDetailReport($sRpt, $lStartRec, $lRecsPerPage));
      }else {
         return($this->strVolHoursDetailExport($sRpt));
      }
   }

   function strVolHoursDetailReport(&$sRpt, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $this->hrsSumOpts($sRpt, $bUseYear, $lYear, $bUseMon, $lMon, $bUseVolID, $lVolID);
      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";

      $strTab = 'tmp_vol_hrs';
      $this->buildPopHrsTmp($strTab, $lYear, $lMon, $lVolID);

      $strWhere = '1';
      $strTitle = 'Volunteer Details for ';
      $strFail = 'There are no volunteer hours for ';

      if ($bUseVolID){
         $cVol = new mvol;
         $cVol->loadVolRecsViaVolID($lVolID, true);
         $vRec = &$cVol->volRecs[0];
         $strSafeName = $vRec->strSafeName;
         $strTitle .= $strSafeName.'&nbsp; &nbsp;';
         $strFail  .= $strSafeName.'&nbsp; &nbsp;';
      }
      if ($bUseMon){
         $strMonth  = strXlateMonth($lMon);
         $strTitle .= $strMonth.' '.$lYear;
         $strFail  .= $strMonth.' '.$lYear;
      }else {
         $strTitle .= $lYear;
         $strFail  .= $lYear;
      }

      $sqlStr =
         "SELECT
             vsa_lKeyID, tmphr_dteVolActivity,
             tmphr_lVolID, vsa_dHoursWorked, vsa_lEventDateShiftID,
             lgen_strListItem,
             vs_strShiftName, vs_lEventDateID, vem_lKeyID, vem_strEventName,
             pe_strFName, pe_strLName

          FROM $strTab
             INNER JOIN vol_events_dates_shifts_assign ON vsa_lKeyID            = tmphr_lEventAssignID
             INNER JOIN volunteers                     ON tmphr_lVolID          = vol_lKeyID
             INNER JOIN people_names                   ON vol_lPeopleID         = pe_lKeyID
             LEFT  JOIN vol_events_dates_shifts        ON vsa_lEventDateShiftID = vs_lKeyID
             LEFT  JOIN vol_events_dates               ON vs_lEventDateID       = ved_lKeyID
             LEFT  JOIN vol_events                     ON ved_lVolEventID       = vem_lKeyID
             LEFT  JOIN lists_generic                  ON vsa_lActivityID=lgen_lKeyID

          ORDER BY tmphr_dteVolActivity, vsa_lKeyID
          $strLimit;";
      $query  = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();
      if ($lNumRows==0) return('<br><i>'.$strFail.'.</i><br>');

      $strOut = '
         <table class="enpRptC">
            <tr>
               <td colspan="7" class="enpRptTitle">'
                  .$strTitle.'
               </td>
            </tr>';

      $strOut .= '
            <tr>
               <td class="enpRptLabel">
                  Date
               </td>
               <td class="enpRptLabel">
                  Vol ID
               </td>
               <td class="enpRptLabel">
                  Volunteer
               </td>
               <td class="enpRptLabel">
                  Event
               </td>
               <td class="enpRptLabel">
                  Shift/Activity
               </td>
               <td class="enpRptLabel">
                  Hours
               </td>
            </tr>';

      foreach ($query->result() as $row){
         $lVolID = $row->tmphr_lVolID;
         if (is_null($row->vsa_lEventDateShiftID)){
            $strEvent    = '<i>(unscheduled)</i>';
            $strActivity =
                           strLinkEdit_VolUnschedHrs($lVolID, $row->vsa_lKeyID, 'Edit activity', true, '', 12).'&nbsp;'
                          .htmlspecialchars($row->lgen_strListItem);
         }else {
            $strEvent    =
                           strLinkView_VolEvent($row->vem_lKeyID, 'View event', true).'&nbsp;'
                          .htmlspecialchars($row->vem_strEventName);
            $strActivity =
                           strLinkView_VolEventDate($row->vs_lEventDateID, 'View event date/shifts', true).'&nbsp;'
                          .htmlspecialchars($row->vs_strShiftName);
         }
         $strOut .= '
            <tr class="makeStripe">
               <td class="enpRpt">'
                  .date($genumDateFormat, dteMySQLDate2Unix($row->tmphr_dteVolActivity)).'
               </td>
               <td class="enpRpt">'
                  .str_pad($lVolID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkView_Volunteer($lVolID, 'View volunteer record', true).'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName).'
               </td>
               <td class="enpRpt">'
                  .$strEvent.'
               </td>
               <td class="enpRpt">'
                  .$strActivity.'
               </td>
               <td class="enpRpt" style="text-align: right; padding-right: 3px;">'
                  .number_format($row->vsa_dHoursWorked, 2).'
               </td>
            </tr>';
      }

      $strOut .= '</table><br>';
      return($strOut);
   }

   function strVolHoursDetailExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat = strMysqlDateFormat(false);

      $this->hrsSumOpts($sRpt, $bUseYear, $lYear, $bUseMon, $lMon, $bUseVolID, $lVolID);

      $strTab = 'tmp_vol_hrs';
      $this->buildPopHrsTmp($strTab, $lYear, $lMon, $lVolID);

      $sqlStr =
           "SELECT
               vsa_lKeyID       AS `Activity ID`,
               lgen_strListItem AS `Activity`,
               DATE_FORMAT(tmphr_dteVolActivity, $strDateFormat) AS `Date of Activity`,
               FORMAT(vsa_dHoursWorked, 2) AS `Hours Logged`,
               IF (vem_lKeyID IS NULL, 'n/a', vem_lKeyID) AS `Event ID`,
               IF (vem_lKeyID IS NULL, '(unscheduled)', vem_strEventName) AS `Event Name`,
               IF (vem_lKeyID IS NULL, 'n/a', vs_strShiftName) AS `Shift Name`,
               vsa_strNotes AS `Notes`, "
                  .strExportFields_Vol()."
             FROM $strTab
                INNER JOIN vol_events_dates_shifts_assign ON vsa_lKeyID            = tmphr_lEventAssignID
                INNER JOIN volunteers                     ON tmphr_lVolID          = vol_lKeyID
                INNER JOIN people_names AS peepTab        ON vol_lPeopleID         = peepTab.pe_lKeyID
                LEFT  JOIN vol_events_dates_shifts        ON vsa_lEventDateShiftID = vs_lKeyID
                LEFT  JOIN vol_events_dates               ON vs_lEventDateID       = ved_lKeyID
                LEFT  JOIN vol_events                     ON ved_lVolEventID       = vem_lKeyID
                LEFT  JOIN lists_generic                  ON vsa_lActivityID       = lgen_lKeyID

            ORDER BY tmphr_dteVolActivity, vsa_lKeyID;";

      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }



      /* -----------------------------------------------------------------
               Volunteer hours by timeframe, summary
         ----------------------------------------------------------------- */

   function lNumRecsInHoursTFSumReport($strTmpTable, $strBetween, $bUseLimits, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }
      $sqlStr =
          "SELECT tmp_lKeyID
           FROM $strTmpTable
              INNER JOIN volunteers   ON tmp_lVolID    = vol_lKeyID
              INNER JOIN people_names ON vol_lPeopleID = pe_lKeyID
           ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID
           $strLimit;";

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function buildVolHoursTempTable($strBetween, $strTabName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $vols = array();

         // unscheduled volunteers
      $sqlStr =
           "SELECT vsa_lVolID, SUM(vsa_dHoursWorked) AS dHoursWorked
            FROM vol_events_dates_shifts_assign

            WHERE NOT vsa_bRetired
               AND vsa_dteActivityDate $strBetween
            GROUP BY vsa_lVolID;";
      $query  = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();
      if ($lNumRows > 0){
         foreach ($query->result() as $row){
            $lVolID = (int)$row->vsa_lVolID;
            $vols[$lVolID] = new stdClass;
            $vols[$lVolID]->unscheduled = (float)$row->dHoursWorked;
         }
      }

         // scheduled volunteers
      $sqlStr =
           "SELECT vsa_lVolID, SUM(vsa_dHoursWorked) AS dHoursWorked
            FROM vol_events_dates_shifts_assign
               INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
               INNER JOIN  vol_events_dates       ON vs_lEventDateID       = ved_lKeyID
            WHERE NOT vsa_bRetired
               AND ved_dteEvent $strBetween
            GROUP BY vsa_lVolID;";
      $query  = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();
      if ($lNumRows > 0){
         foreach ($query->result() as $row){
            $lVolID = (int)$row->vsa_lVolID;
            if (isset($vols[$lVolID])){
               $vols[$lVolID]->scheduled = (float)$row->dHoursWorked;
            }else {
               $vols[$lVolID] = new stdClass;
               $vols[$lVolID]->unscheduled = 0.0;
               $vols[$lVolID]->scheduled = (float)$row->dHoursWorked;
            }
         }
      }

      $sqlStr = "DROP TABLE IF EXISTS $strTabName;";
      $this->db->query($sqlStr);

      $sqlStr = "
         CREATE TEMPORARY TABLE IF NOT EXISTS $strTabName (
           tmp_lKeyID            int(11) NOT NULL AUTO_INCREMENT,
           tmp_lVolID            int(11) NOT NULL ,
           tmp_dHoursScheduled   decimal(10,2) NOT NULL DEFAULT '0.00',
           tmp_dHoursUnscheduled decimal(10,2) NOT NULL DEFAULT '0.00',

           PRIMARY KEY    (tmp_lKeyID),
           KEY tmp_lVolID (tmp_lVolID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
      $this->db->query($sqlStr);

      if (count($vols) > 0){
         foreach ($vols as $lVolID=>$vHours){
            $sqlStr =
              "INSERT INTO $strTabName
               SET tmp_lVolID=$lVolID,
                   tmp_dHoursScheduled=$vHours->scheduled,
                   tmp_dHoursUnscheduled=$vHours->unscheduled;";
            $this->db->query($sqlStr);
         }
      }
   }

   function strVolHoursTFSumReportExport(
                                   &$sRpt,
                                   $bReport,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         return($this->strVolHoursTFSumReport($sRpt, $lStartRec, $lRecsPerPage));
      }else {
         return($this->strVolHoursTFSumExport($sRpt));
      }
   }

   function strVolHoursTFSumReport($sRpt, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '<b>Time Frame: '. $sRpt->strDateRange.'</b><br><br>';

      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";

      $sqlStr =
          "SELECT pe_strLName, pe_strFName, tmp_lVolID,
              tmp_dHoursScheduled, tmp_dHoursUnscheduled
           FROM $sRpt->tmpTable
              INNER JOIN volunteers   ON tmp_lVolID    = vol_lKeyID
              INNER JOIN people_names ON vol_lPeopleID = pe_lKeyID
           ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID
           $strLimit;";

      $query  = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();
      if ($lNumRows == 0){
         $strOut .= '<i>There are no records that meet your search criteria.</i>';
      }else {
         $strOut .=
            '<table class="enpRptC" style="width: 450pt;">
               <tr>
                  <td class="enpRptTitle" colspan="5">
                     Volunteer Hours
                  </td>
               </tr>
               <tr>
                  <td class="enpRptLabel">
                     vol ID
                  </td>
                  <td class="enpRptLabel">
                     Volunteer
                  </td>
                  <td class="enpRptLabel">
                     Scheduled<br>Hours
                  </td>
                  <td class="enpRptLabel">
                     Unscheduled<br>Hours
                  </td>
                  <td class="enpRptLabel">
                     Total
                  </td>
               </tr>';

         $sngTotS = $sngTotU = 0.0;
         foreach ($query->result() as $row){
            $lVolID = (int)$row->tmp_lVolID;
            $strOut .=
               '
                  <tr class="makeStripe">
                     <td class="enpRpt" style="text-align: center; width: 40pt;">'
                        .str_pad($lVolID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                        .strLinkView_Volunteer($lVolID, 'View volunteer record', true, ' id="vrec'.$lVolID.'" ').'
                     </td>
                     <td class="enpRpt">'
                        .htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName).'
                     </td>
                     <td class="enpRpt" style="text-align: right; width: 40pt;">'
                        .number_format($row->tmp_dHoursScheduled, 2).'&nbsp;'
                        .strLinkView_VolHrsViaVolID($lVolID, true, 'View details', true).'
                     </td>
                     <td class="enpRpt" style="text-align: right; width: 40pt;">'
                        .number_format($row->tmp_dHoursUnscheduled, 2).'&nbsp;'
                        .strLinkView_VolHrsViaVolID($lVolID, false, 'View details', true).'
                     </td>
                     <td class="enpRpt" style="text-align: right; width: 40pt;">'
                        .number_format($row->tmp_dHoursScheduled + $row->tmp_dHoursUnscheduled, 2).'
                     </td>
                  </tr>';
            $sngTotS += $row->tmp_dHoursScheduled;
            $sngTotU += $row->tmp_dHoursUnscheduled;
         }
         $strOut .=
               '
                  <tr >
                     <td class="enpRptLabel" colspan="2">
                        Total
                     </td>
                     <td class="enpRpt" style="text-align: right; padding-right: 15pt;"><b>'
                        .number_format($sngTotS, 2).'</b>
                     </td>
                     <td class="enpRpt" style="text-align: right; padding-right: 15pt;"><b>'
                        .number_format($sngTotU, 2).'</b>
                     </td>
                     <td class="enpRpt" style="text-align: right;"><b>'
                        .number_format($sngTotS + $sngTotU, 2).'</b>
                     </td>
                  </tr>';
         $strOut .= '</table>';
      }
      return($strOut);
   }

   function strVolHoursTFSumExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterVoc;

      $this->buildVolHoursTempTable($sRpt->strBetween, $sRpt->tmpTable);
      $sqlStr =
           "SELECT
              tmp_lVolID AS `vol ID`,
              pe_lKeyID AS `people ID`,
              pe_strLName AS `Last Name`,
              pe_strFName AS `First Name`,
              tmp_dHoursScheduled AS `Scheduled Hours`,
              tmp_dHoursUnscheduled AS `Unscheduled Hours`,
              (tmp_dHoursScheduled+tmp_dHoursUnscheduled) AS `Total Hours`,
              pe_strAddr1 AS `Address 1`, 
              pe_strAddr2 AS `Address 2`,
              pe_strCity AS `City`, 
              pe_strState AS `$gclsChapterVoc->vocState`,
              pe_strCountry AS `Country`, 
              pe_strZip AS `$gclsChapterVoc->vocZip`,
              pe_strPhone AS `Phone`, 
              pe_strCell AS `Cell`, 
              pe_strFax AS `Fax`,
              pe_strEmail AS `Email`
           FROM $sRpt->tmpTable
              INNER JOIN volunteers   ON tmp_lVolID    = vol_lKeyID
              INNER JOIN people_names ON vol_lPeopleID = pe_lKeyID
           ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID";

      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }



}
