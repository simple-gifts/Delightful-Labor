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
      $this->load->model('vols/mvol_event_dates', 'clsVolEventDates');
---------------------------------------------------------------------
   __construct             ()
   lInsertEventDate        ($lEventID, $dteEvent)
   updateEventDate         ($lEventDateID, $dteEvent)
   loadEventDates          ($lEventID)
   loadEventDateViaDateID  ($lDateID)
   updateStartStopDates    ()
   deleteEventDate         ($lEventDateID)
   lNumDatesViaEventID     ($lEventID)

   eventsByMonth           ($lMonth, $lYear, &$monthEvent)
---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class mvol_event_dates extends CI_Model{
   public
       $lEventID, $lNumDates, $dates;

   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->lEventID = $this->lNumDates = $this->dates = null;
   }

   public function lInsertEventDate($lEventID, $dteEvent, $bmysqlDate=false){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if ($bmysqlDate) {
         $strEDate = strPrepStr($dteEvent);
      }else {
         $strEDate = strPrepDate($dteEvent);
      }
      $sqlStr =
          "INSERT INTO vol_events_dates
           SET
              ved_lVolEventID = $lEventID,
              ved_dteEvent    = $strEDate;";

      $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   public function updateEventDate($lEventDateID, $dteEvent, $bmysqlDate=false){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if ($bmysqlDate) {
         $strEDate = strPrepStr($dteEvent);
      }else {
         $strEDate = strPrepDate($dteEvent);
      }
      $sqlStr =
         "UPDATE vol_events_dates
          SET
             ved_dteEvent = $strEDate
          WHERE ved_lKeyID=$lEventDateID;";
      $this->db->query($sqlStr);
   }

   public function loadEventDates($lEventID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->loadEventDatesArray(" AND ved_lVolEventID=$lEventID ");
   }

   public function loadEventDateViaDateID($lDateID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->loadEventDatesArray(" AND ved_lKeyID=$lDateID ");
   }

   public function loadEventDatesArray($strWhere){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->dates = array();
      $sqlStr =
        "SELECT
            ved_lKeyID, ved_lVolEventID, ved_dteEvent,
            COUNT(vol_events_dates_shifts.vs_lKeyID) AS lNumShifts
         FROM vol_events_dates
            LEFT JOIN vol_events_dates_shifts ON vs_lEventDateID=ved_lKeyID
         WHERE 1 $strWhere
         GROUP BY ved_lKeyID
         ORDER BY ved_dteEvent, ved_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumDates = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->dates[0] = new stdClass;
         $this->dates[0]->lKeyID      =
         $this->dates[0]->lVolEventID =
         $this->dates[0]->dteEvent    =
         $this->dates[0]->mDteEvent   =
         $this->dates[0]->lNumShifts  = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->dates[$idx] = new stdClass;
            $this->dates[$idx]->lKeyID      = $row->ved_lKeyID;
            $this->dates[$idx]->lVolEventID = $row->ved_lVolEventID;
            $this->dates[$idx]->dteEvent    = dteMySQLDate2Unix($row->ved_dteEvent);
            $this->dates[$idx]->mDteEvent   = $row->ved_dteEvent;
            $this->dates[$idx]->lNumShifts  = $row->lNumShifts;
            ++$idx;
         }
      }
   }

   public function updateStartStopDates(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->updateStartOrStop('vem_dteEventStartDate', 'MIN');
      $this->updateStartOrStop('vem_dteEventEndDate',   'MAX');
   }

   private function updateStartOrStop($strField, $strFunction){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
        "UPDATE vol_events
         SET
            $strField=(
               SELECT $strFunction(ved_dteEvent) AS dteMinMax
               FROM vol_events_dates
               WHERE ved_lVolEventID=vem_lKeyID
               )
         WHERE NOT vem_bRetired;";
      $this->db->query($sqlStr);
   }

   public function deleteEventDate($lEventDateID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        "DELETE
             vol_events_dates,
             vol_events_dates_shifts,
             vol_events_dates_shifts_assign
         FROM vol_events_dates
            LEFT JOIN vol_events_dates_shifts        ON ved_lKeyID = vs_lEventDateID
            LEFT JOIN vol_events_dates_shifts_assign ON vs_lKeyID  = vsa_lEventDateShiftID
         WHERE ved_lKeyID=$lEventDateID;";

      $this->db->query($sqlStr);
   }

   public function lNumDatesViaEventID($lEventID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
           "SELECT COUNT(*) AS lNumRecs
            FROM vol_events_dates
            WHERE ved_lVolEventID=$lEventID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
         return((integer)$row->lNumRecs);
      }
   }

   public function eventsByMonth($lMonth, $lYear, &$monthEvent, $opts=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $monthEvent = new stdClass;
      $monthEvent->lMonth = $lMonth;
      $monthEvent->lYear  = $lYear;
      $monthEvent->dates  = array();

      $strOrder = ' ved_dteEvent, vem_strEventName, vem_lKeyID ';
      if (isset($opts->bOrderViaEvent)){
         if ($opts->bOrderViaEvent) $strOrder = ' vem_strEventName, vem_lKeyID, ved_dteEvent, vem_lKeyID ';
      }

      $sqlStr =
        "SELECT
            ved_lKeyID, ved_lVolEventID, ved_dteEvent,
            vem_strEventName
         FROM vol_events_dates
            INNER JOIN vol_events ON ved_lVolEventID=vem_lKeyID

         WHERE MONTH(ved_dteEvent) = $lMonth
            AND YEAR(ved_dteEvent) = $lYear
            AND NOT vem_bRetired
         ORDER BY $strOrder;";

      $query = $this->db->query($sqlStr);
      $monthEvent->lNumDates  = $lNumDates = $query->num_rows();
      if ($lNumDates > 0){
         $idx = 0;
         foreach ($query->result() as $row)   {
            $monthEvent->dates[$idx] = new stdClass;
            $monthEvent->dates[$idx]->lDateKeyID   = $lDateID = $row->ved_lKeyID;
            $monthEvent->dates[$idx]->lVolEventID  = $row->ved_lVolEventID;
            $monthEvent->dates[$idx]->dteEvent     = dteMySQLDate2Unix($row->ved_dteEvent);
            $monthEvent->dates[$idx]->strEventName = $row->vem_strEventName;
            $monthEvent->dates[$idx]->shifts = array();
            $this->shiftsByDateID($lDateID, $monthEvent->dates[$idx]->shifts, $monthEvent->dates[$idx]->lNumShifts);
            ++$idx;
         }
      }
   }

   function eventIDsViaMonth($lMonth, $lYear, &$eventIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $eventIDs = new stdClass;
      $eventIDs->lMonth   = $lMonth;
      $eventIDs->strMonth = strXlateMonth($lMonth);
      $eventIDs->lYear    = $lYear;
      $eventIDs->events   = array();

      $sqlStr =
        "SELECT
            ved_dteEvent,
            vem_strEventName, vem_lKeyID, COUNT(ved_lKeyID) AS lNumDates
         FROM vol_events_dates
            INNER JOIN vol_events ON ved_lVolEventID=vem_lKeyID

         WHERE MONTH(ved_dteEvent) = $lMonth
            AND YEAR(ved_dteEvent) = $lYear
            AND NOT vem_bRetired
         GROUP BY vem_lKeyID
         ORDER BY  vem_strEventName, vem_lKeyID;";

      $query = $this->db->query($sqlStr);
      $eventIDs->lNumEvents  = $lNumEvents = $query->num_rows();
      if ($lNumEvents > 0){
         $idx = 0;
         foreach ($query->result() as $row)   {
            $eventIDs->events[$idx] = new stdClass;
            $ev = &$eventIDs->events[$idx];
            $ev->lNumDates = $row->lNumDates;
            $ev->strEvent  = $row->vem_strEventName;
            $ev->lEventID  = $row->vem_lKeyID;

            ++$idx;
         }
      }
   }

   function shiftsByDateID($lDateID, &$shifts, &$lNumShifts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT
            vs_lKeyID, vs_lEventDateID, vs_strShiftName, vs_strDescription,
            vs_dteShiftStartTime,
            TIME_FORMAT(vs_dteShiftStartTime, '%l:%i %p') AS dteStartTime,
            vs_enumDuration, vs_lNumVolsNeeded

         FROM vol_events_dates_shifts

         WHERE vs_lEventDateID=$lDateID

         ORDER BY vs_dteShiftStartTime, vs_strShiftName, vs_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumShifts = $query->num_rows();
      $shifts = array();
      if ($lNumShifts > 0){
         $idx = 0;
         foreach ($query->result() as $row)   {
            $shifts[$idx] = new stdClass;
            $shifts[$idx]->lShiftID        = $lShiftID = $row->vs_lKeyID;
            $shifts[$idx]->lEventDateID    = $row->vs_lEventDateID;
            $shifts[$idx]->strShiftName    = $row->vs_strShiftName;
            $shifts[$idx]->strDescription  = $row->vs_strDescription;
            $shifts[$idx]->dteStartTime    = $row->dteStartTime;
            $shifts[$idx]->enumDuration    = $row->vs_enumDuration;
            $shifts[$idx]->lNumVolsNeeded  = $row->vs_lNumVolsNeeded;
            $shifts[$idx]->vols            = array();
            $this->volsViaShiftID($lShiftID, $shifts[$idx]->vols, $shifts[$idx]->lNumVols);

            ++$idx;
         }
      }
   }

   function volsViaShiftID($lShiftID, &$vols, &$lNumVols){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT
            vsa_lKeyID, vsa_lEventDateShiftID, vsa_lVolID, vsa_strNotes, vsa_dHoursWorked,
            vol_lKeyID, vol_lPeopleID,
            pe_strFName, pe_strLName
         FROM vol_events_dates_shifts_assign
            INNER JOIN volunteers   ON vsa_lVolID = vol_lKeyID
            INNER JOIN people_names ON pe_lKeyID  = vol_lPeopleID
         WHERE vsa_lEventDateShiftID=$lShiftID
            AND NOT vsa_bRetired
            AND NOT pe_bRetired
         ORDER BY pe_strLName, pe_strFName, pe_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumVols = $query->num_rows();
      $vols = array();
      if ($lNumVols > 0){
         $idx = 0;
         foreach ($query->result() as $row)   {
            $vols[$idx] = new stdClass;
            $vols[$idx]->lAssignID         = $row->vsa_lKeyID;
            $vols[$idx]->lEventDateShiftID = $row->vsa_lEventDateShiftID;
            $vols[$idx]->lVolID            = $row->vsa_lVolID;
            $vols[$idx]->strNotes          = $row->vsa_strNotes;
            $vols[$idx]->dHoursWorked      = $row->vsa_dHoursWorked;
            $vols[$idx]->lPeopleID         = $row->vol_lPeopleID;
            $vols[$idx]->strFName          = $row->pe_strFName;
            $vols[$idx]->strLName          = $row->pe_strLName;
            ++$idx;
         }
      }
   }








}

?>