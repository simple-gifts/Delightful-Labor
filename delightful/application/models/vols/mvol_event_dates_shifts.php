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
      $this->load->model('vols/mvol_event_dates_shifts', 'clsShifts');
---------------------------------------------------------------------
   lNumShifsTotViaEventID       ($lEventID)
   lTotVolsNeededViaEventID     ($lEventID)

   loadShiftsViaEventID         ($lEventID)
   loadShiftsViaEventDateID     ($lEventDateID)
   loadShiftsViaEventShiftID    ($lEventShiftID)
   loadShiftsGeneric            ($strWhere)

   lAddNewEventShift            ()
   updateEventShift             ()
   deleteEventDateShift         ($lEventShiftID)
   volEventDateShiftHTMLSummary ()
   cloneShift                   ($lEventShiftID, $lDateID)

   lNumShiftsViaVolID           ($lVolID, $bPast)
---------------------------------------------------------------------*/


class mvol_event_dates_shifts extends CI_Model{
   public
       $lEventID, $lNumShifts, $shifts;

   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->lEventID = $this->lNumShifts = $this->shifts = null;
   }

   public function lNumShifsTotViaEventID($lEventID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM vol_events_dates_shifts
            INNER JOIN vol_events_dates  ON vs_lEventDateID=ved_lKeyID
         WHERE NOT vs_bRetired
            AND ved_lVolEventID=$lEventID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
         return((integer)$row->lNumRecs);
      }
   }

   public function lTotVolsNeededViaEventID($lEventID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
        "SELECT SUM(vs_lNumVolsNeeded) AS lNumVols
         FROM vol_events_dates_shifts
            INNER JOIN vol_events_dates  ON vs_lEventDateID=ved_lKeyID
         WHERE NOT vs_bRetired
            AND ved_lVolEventID=$lEventID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
         return((integer)$row->lNumVols);
      }
   }

   public function loadShiftsViaEventID($lEventID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->loadShiftsGeneric(" AND ved_lVolEventID=$lEventID ");
   }

   public function loadShiftsViaEventDateID($lEventDateID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->loadShiftsGeneric(" AND vs_lEventDateID=$lEventDateID ");
   }

   public function loadShiftsViaEventShiftID($lEventShiftID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->loadShiftsGeneric(" AND vs_lKeyID=$lEventShiftID ");
   }

   private function loadShiftsGeneric($strWhere){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->shifts = array();
      $sqlStr =
        "SELECT
            vs_lKeyID, vs_lEventDateID, vs_strShiftName, vs_strDescription,
            vs_enumDuration, vs_lNumVolsNeeded, vs_lJobCode, lgen_strListItem,
            ved_lVolEventID, vem_strEventName,
            vs_dteShiftStartTime, ved_dteEvent
         FROM vol_events_dates_shifts
            INNER JOIN vol_events_dates ON vs_lEventDateID = ved_lKeyID
            INNER JOIN vol_events       ON vem_lKeyID      = ved_lVolEventID
            LEFT  JOIN lists_generic    ON vs_lJobCode     = lgen_lKeyID
         WHERE
            NOT vs_bRetired
            $strWhere
         ORDER BY ved_dteEvent, vs_dteShiftStartTime, vs_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumShifts = $numRows = $query->num_rows();
      if ($numRows==0) {
         $this->shifts[0] = new stdClass;
         $shift = &$this->shifts[0];
         $shift->lKeyID            =
         $shift->lEventDateID      =
         $shift->strShiftName      =
         $shift->strDescription    =
         $shift->enumDuration      =
         $shift->lNumVolsNeeded    =
         $shift->lJobCode          =
         $shift->strJobCode        =
         $shift->lVolEventID       =
         $shift->dteEventStartTime =
         $shift->dteEvent          = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->shifts[$idx] = new stdClass;
            $shift = &$this->shifts[$idx];

            $shift->lKeyID            = $row->vs_lKeyID;
            $shift->lEventDateID      = $row->vs_lEventDateID;
            $shift->strShiftName      = $row->vs_strShiftName;
            $shift->strDescription    = $row->vs_strDescription;
            $shift->enumDuration      = $row->vs_enumDuration;
            $shift->lNumVolsNeeded    = $row->vs_lNumVolsNeeded;
            $shift->lJobCode          = $row->vs_lJobCode;
            $shift->strJobCode        = $row->lgen_strListItem;
            $shift->lVolEventID       = $row->ved_lVolEventID;
            $shift->strEventName      = $row->vem_strEventName;
            $shift->dteEventStartTime = strtotime($row->vs_dteShiftStartTime);
            $shift->dteEvent          = dteMySQLDate2Unix($row->ved_dteEvent);
            ++$idx;
         }
      }
   }

   public function lNumVolScheduledViaShiftID($lShiftID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM vol_events_dates_shifts_assign
         WHERE
            NOT vsa_bRetired
            AND vsa_lEventDateShiftID=$lShiftID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return(0);
      }else {
         $row = $query->row();
         return($row->lNumRecs);
      }
   }

   public function lAddNewEventShift(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $clsES = &$this->shifts[0];
      $sqlStr =
         'INSERT INTO vol_events_dates_shifts
          SET '.$this->sqlESCommon($clsES).",
             vs_lEventDateID = $clsES->lEventDateID,
             vs_bRetired     = 0;";

      $this->db->query($sqlStr);
      return($clsES->lKeyID = $this->db->insert_id());
   }

   public function updateEventShift(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $clsES = &$this->shifts[0];
      $sqlStr =
         'UPDATE vol_events_dates_shifts
          SET '.$this->sqlESCommon($clsES)."
          WHERE vs_lKeyID={$clsES->lKeyID};";

      $this->db->query($sqlStr);
   }

   private function sqlESCommon($clsES){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      return('
            vs_strShiftName      = '.strPrepStr($clsES->strShiftName).',
            vs_strDescription    = '.strPrepStr($clsES->strDescription).',
            vs_dteShiftStartTime = '.strPrepTime($clsES->dteEventStartTime).',
            vs_lJobCode          = '.(is_null($clsES->lJobCode) ? 'NULL' : (int)$clsES->lJobCode).',
            vs_enumDuration      = '.strPrepStr($clsES->enumDuration).",
            vs_lNumVolsNeeded    = $clsES->lNumVolsNeeded ");
   }

   public function deleteEventDateShift($lEventShiftID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        "DELETE
             vol_events_dates_shifts,
             vol_events_dates_shifts_assign
         FROM vol_events_dates_shifts
            LEFT JOIN vol_events_dates_shifts_assign ON vs_lKeyID  = vsa_lEventDateShiftID
         WHERE vs_lKeyID=$lEventShiftID;";

      $this->db->query($sqlStr);
   }

   public function volEventDateShiftHTMLSummary(){
   //-----------------------------------------------------------------------
   // assumes user has called $clsShifts->loadShiftsViaEventShiftID($lEventShiftID)
   //-----------------------------------------------------------------------
      global $gdteNow, $genumDateFormat;
      $strOut = '';

      $clsS = $this->shifts[0];
      $lEventID = $clsS->lVolEventID;
      $lShiftID = $clsS->lKeyID;
      $lDateID  = $clsS->lEventDateID;

      $clsEvent = new mvol_events;
      $clsRpt   = new generic_rpt(array('enumStyle' => 'terse'));
      $clsRpt->setEntrySummary();

      $clsEvent->loadEventsViaEID($lEventID);

      $clsVE = $clsEvent->events[0];
      $lEventID = $clsVE->lKeyID;

      $strOut =
          $clsRpt->openReport('', '')

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Event ID:')
         .$clsRpt->writeCell (strLinkView_VolEvent($lEventID, 'View event', true).'&nbsp;'
                             .str_pad($lEventID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Date:')
         .$clsRpt->writeCell (strLinkView_VolEventDate($lDateID, 'View event date', true).'&nbsp;'
                             .date($genumDateFormat.' (D)', $clsS->dteEvent))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Volunteer Event:')
         .$clsRpt->writeCell (htmlspecialchars($clsVE->strEventName))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Shift:')
         .$clsRpt->writeCell (htmlspecialchars($clsS->strShiftName))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Start/Duration:')
         .$clsRpt->writeCell (date('g:i a', $clsS->dteEventStartTime).' / '
                           .$clsS->enumDuration)
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('# Vols Needed:')
         .$clsRpt->writeCell ($clsS->lNumVolsNeeded)
         .$clsRpt->closeRow  ()

         .$clsRpt->closeReport('<br>');

      return($strOut);
   }

   public function cloneShift($lEventShiftID, $lDateID, $bCloneVols){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->loadShiftsViaEventShiftID($lEventShiftID);

      $clsES = &$this->shifts[0];
      $clsES->lEventDateID = $lDateID;
      $lNewShiftID = $this->lAddNewEventShift();
      $clsSV = new mvol_event_dates_shifts_vols;

      if ($bCloneVols){
         $clsSV->loadVolsViaShiftID($lEventShiftID);
         $lNumVols = $clsSV->lNumVols;

         if ($lNumVols > 0){
            foreach ($clsSV->vols as $vol){
               $clsSV->lAddVolToShift($lNewShiftID, $vol->lVolID, '');
            }
         }
      }
   }

   public function lNumShiftsViaVolID($lVolID, $bPast){
   //---------------------------------------------------------------------
   // if bPast, return count of shifts prior to today
   //---------------------------------------------------------------------
      global $gdteNow;
      $sqlStr =
         "SELECT COUNT(*) AS lNumShifts
         FROM vol_events
            INNER JOIN vol_events_dates               ON vem_lKeyID = ved_lVolEventID
            INNER JOIN vol_events_dates_shifts        ON ved_lKeyID = vs_lEventDateID
            INNER JOIN vol_events_dates_shifts_assign ON vs_lKeyID  = vsa_lEventDateShiftID
         WHERE
            NOT vem_bRetired
            AND NOT vs_bRetired
            AND NOT vsa_bRetired
            AND vsa_lVolID=$lVolID
            AND ved_dteEvent ".($bPast ? '<' : '>=').strPrepDate($gdteNow).';';

      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return(0);
      }else {
         $row = $query->row();
         return((integer)$row->lNumShifts);
      }
   }

   /*------------------------------------------------------
                    R E P O R T S
   ------------------------------------------------------*/

   function lNumRecsInVolScheduleReport(&$sRpt, $bUseLimits, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow;

      $lVolID      = $sRpt->lVolID;
      $strShowType = $sRpt->strShowType;

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      if ($strShowType == 'cur'){
         $strWhere = ' AND ved_dteEvent >= '.strPrepDate($gdteNow).' ';
      }elseif ($strShowType == 'past'){
         $strWhere = ' AND ved_dteEvent < '.strPrepDate($gdteNow).' ';
      }else {
         $strWhere = '';
      }

      $sqlStr =
        "SELECT vsa_lKeyID
         FROM vol_events
            INNER JOIN vol_events_dates               ON vem_lKeyID = ved_lVolEventID
            INNER JOIN vol_events_dates_shifts        ON ved_lKeyID = vs_lEventDateID
            INNER JOIN vol_events_dates_shifts_assign ON vs_lKeyID  = vsa_lEventDateShiftID
         WHERE
            NOT vem_bRetired
            AND NOT vs_bRetired
            AND NOT vsa_bRetired
            AND vsa_lVolID=$lVolID
            $strWhere
            $strLimit;";

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strVolScheduleReport(&$sRpt,   $reportID,
                                 $bReport, $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow;

      $lVolID      = $sRpt->lVolID;
      $strShowType = $sRpt->strShowType;

      $clsVols = new mvol;
      $clsVols->loadVolRecsViaVolID($lVolID, true);

      if ($strShowType == 'cur'){
         $strLabel = 'Current and future';
         $strWhere = ' AND ved_dteEvent >= '.strPrepDate($gdteNow).' ';
      }elseif ($strShowType == 'past'){
         $strLabel = 'Past';
         $strWhere = ' AND ved_dteEvent < '.strPrepDate($gdteNow).' ';
      }else {
         $strLabel = 'Entire';
         $strWhere = ' ';
      }

      if ($bReport){
         $strOut =
             $strLabel.' schedule for the volunteer '.$clsVols->volRecs[0]->strSafeNameFL.'<br><br>';
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strExport = '';
         $strLimit = '';
      }

      $sqlStr =
        "SELECT
           vsa_lKeyID,
           vsa_lEventDateShiftID, vsa_strNotes, vsa_dHoursWorked,
           vs_strShiftName,
           TIME_FORMAT(vs_dteShiftStartTime, '%l:%i %p') AS dteStartTime,
           vs_enumDuration AS strDuration,
           ved_lVolEventID, ved_dteEvent,
           vem_strEventName, vs_lEventDateID
         FROM vol_events
            INNER JOIN vol_events_dates               ON vem_lKeyID = ved_lVolEventID
            INNER JOIN vol_events_dates_shifts        ON ved_lKeyID = vs_lEventDateID
            INNER JOIN vol_events_dates_shifts_assign ON vs_lKeyID  = vsa_lEventDateShiftID
         WHERE
            NOT vem_bRetired
            AND NOT vs_bRetired
            AND NOT vsa_bRetired
            AND vsa_lVolID=$lVolID
            $strWhere
            ORDER BY ved_dteEvent, vs_dteShiftStartTime, vem_strEventName, vem_lKeyID
            $strLimit;";

      $query = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();

      if ($lNumRows==0){
         return($strOut.'<br><br><i>There are no records that match your search criteria.</i>');
      }

      if ($bReport){
         $strOut .= $this->strVolScheduleRptHTML($query, $strLabel, $clsVols->volRecs[0]->strSafeNameFL, $clsVols->volRecs[0]->lKeyID);
         return($strOut);
      }else {
         $strExport = $this->strVolScheduleRptExport($query, $strLabel, $clsVols->volRecs[0]->strSafeNameFL, $clsVols->volRecs[0]->lKeyID);
         return($strExport);
      }
   }

   private function strVolScheduleRptHTML(&$query, $strLabel, $strSafeNameFL, $lVolID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $strOut =
         '<table class="enpRptC">
            <tr>
               <td class="enpRptTitle" colspan="7">'
                  .$strLabel.' Volunteer Schedule for '.$strSafeNameFL.'&nbsp;'
                  .strLinkView_Volunteer($lVolID, 'View volunteer record', true).'
               </td>
            <tr>';

      $strOut .= '
            <tr>
               <td class="enpRptLabel" style="vertical-align: bottom;">
                  Date
               </td>
               <td class="enpRptLabel" style="vertical-align: bottom;">
                  Event
               </td>
               <td class="enpRptLabel" style="vertical-align: bottom;">
                  Shift
               </td>
               <td class="enpRptLabel" style="vertical-align: bottom;">
                  Start
               </td>
               <td class="enpRptLabel" style="vertical-align: bottom;">
                  Duration
               </td>
               <td class="enpRptLabel" style="vertical-align: bottom;">
                  Hours Logged
               </td>
            <tr>';

      foreach ($query->result() as $row){
         $lEventID = $row->ved_lVolEventID;
         $lEdateID = $row->vs_lEventDateID;
         $strOut .= '
             <tr class="makeStripe">
                <td class="enpRpt" style="text-align: center; width: 80pt;">'
                   .date($genumDateFormat, dteMySQLDate2Unix($row->ved_dteEvent)).'
                </td>
                <td class="enpRpt" style="text-align: left; width: 200pt;">'
                   .strLinkView_VolEvent($lEventID, 'View Event', true)
                   .htmlspecialchars($row->vem_strEventName).'
                </td>
                <td class="enpRpt" style="text-align: left; width: 120pt;">'
                   .strLinkView_VolEventDate($lEdateID, 'View shifts for this date', true)
                   .htmlspecialchars($row->vs_strShiftName).'
                </td>
                <td class="enpRpt" style="text-align: left; width: 50pt;">'
                   .$row->dteStartTime.'
                </td>
                <td class="enpRpt" style="text-align: left; width: 100pt;">'
                   .$row->strDuration.'
                </td>
                <td class="enpRpt" style="text-align: right; padding-right: 10px;; width: 30pt;">'
                   .number_format($row->vsa_dHoursWorked, 2).'
                </td>
             </tr>';
      }
      $strOut .= '</table>';
      return($strOut);
   }


}

?>