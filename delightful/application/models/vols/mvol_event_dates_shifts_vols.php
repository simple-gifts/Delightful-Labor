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
/---------------------------------------------------------------------
      $this->load->model('vols/mvol_event_dates_shifts_vols', 'clsSV');
---------------------------------------------------------------------
   public  loadVolsViaShiftID                  ($lShiftID)
   public  function loadAvailVols              ($strExcludeIDs, $bIncludeInactive){
   public  function loadVolAssignmentsGeneric  ($strWhere){
   private function loadVolInfoClass           (&$numRows, $result){
   public  function strVolIDsAsStr             (){
   public  function lAddVolToShift             ($lShiftID, $lVolID, $strNotes){
   public  function removeVolFromShift         ($lVolAssignID){
   public  function lTotVolsAssignedViaEventID ($lEventID)
---------------------------------------------------------------------*/

class mvol_event_dates_shifts_vols extends CI_Model{
   public
       $lEventShiftID, $lNumVols, $vols, $volsA;

   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->lEventShiftID = $this->lNumVols = $this->vols = null;
   }

   public function loadVolsViaShiftID($lShiftID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->loadVolAssignmentsGeneric(" AND vsa_lEventDateShiftID=$lShiftID ");
   }

   public function loadAvailVols($strExcludeIDs, $bIncludeInactive){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if ($strExcludeIDs==''){
         $strWhereExclude = '';
      }else {
         $strWhereExclude = " AND NOT (vol_lKeyID IN ($strExcludeIDs) ) ";
      }

      $strInactive = $bIncludeInactive ? '' : ' AND NOT vol_bInactive ';

      $this->volsA = array();
      $sqlStr =
        "SELECT
            vol_lKeyID,
            vol_lPeopleID,
            pe_lHouseholdID, pe_strFName, pe_strLName,
            pe_strAddr1, pe_strAddr2, pe_strCity, pe_strState, pe_strCountry, pe_strZip

         FROM  volunteers
            INNER JOIN people_names ON vol_lPeopleID = pe_lKeyID
         WHERE
            NOT pe_bRetired
            AND NOT vol_bRetired
            $strWhereExclude
            $strInactive
         ORDER BY pe_strLName, pe_strFName, vol_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumVolsAvail = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->volsA[0] = new stdClass;

         $this->volsA[0]->lVolID            =
         $this->volsA[0]->lPeopleID         =
         $this->volsA[0]->lHouseholdID      =
         $this->volsA[0]->strFName          =
         $this->volsA[0]->strLName          =
         $this->volsA[0]->strSafeNameFL     =
         $this->volsA[0]->strAddr1          =
         $this->volsA[0]->strAddr2          =
         $this->volsA[0]->strCity           =
         $this->volsA[0]->strState          =
         $this->volsA[0]->strCountry        =
         $this->volsA[0]->strZip            =
         $this->volsA[0]->strAddr           = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {

            $this->volsA[$idx] = new stdClass;

            $this->volsA[$idx]->lVolID            = $row->vol_lKeyID;
            $this->volsA[$idx]->lPeopleID         = $row->vol_lPeopleID;
            $this->volsA[$idx]->lHouseholdID      = $row->pe_lHouseholdID;
            $this->volsA[$idx]->strFName          = $row->pe_strFName;
            $this->volsA[$idx]->strLName          = $row->pe_strLName;
            $this->volsA[$idx]->strSafeNameFL     =
                              htmlspecialchars($row->pe_strFName.' '.$row->pe_strLName);
            $this->volsA[$idx]->strAddr1          = $row->pe_strAddr1;
            $this->volsA[$idx]->strAddr2          = $row->pe_strAddr2;
            $this->volsA[$idx]->strCity           = $row->pe_strCity;
            $this->volsA[$idx]->strState          = $row->pe_strState;
            $this->volsA[$idx]->strCountry        = $row->pe_strCountry;
            $this->volsA[$idx]->strZip            = $row->pe_strZip;
            $this->volsA[$idx]->strAddr           =
                           strBuildAddress(
                                 $row->pe_strAddr1, $row->pe_strAddr2,   $row->pe_strCity,
                                 $row->pe_strState, $row->pe_strCountry, $row->pe_strZip,
                                 true);
            ++$idx;
         }
      }
   }

   public function loadVolAssignmentsGeneric($strWhere){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->vols = array();
      $sqlStr =
        "SELECT
            vsa_lKeyID, vsa_lEventDateShiftID, vsa_lVolID,
            vsa_strNotes, vsa_dHoursWorked,
            vol_lPeopleID,
            pe_lHouseholdID, pe_strFName, pe_strLName,
            pe_strAddr1, pe_strAddr2, pe_strCity, pe_strState, pe_strCountry, pe_strZip,
            pe_strPhone, pe_strCell, pe_strEmail

         FROM vol_events_dates_shifts_assign
            INNER JOIN volunteers   ON vsa_lVolID    = vol_lKeyID
            INNER JOIN people_names ON vol_lPeopleID = pe_lKeyID
         WHERE
            NOT vsa_bRetired
            $strWhere
         ORDER BY pe_strLName, pe_strFName, vsa_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumVols = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->vols[0] = new stdClass;
         $vol = &$this->vols[0];

         $vol->lKeyID            =
         $vol->lEventDateShiftID =
         $vol->lVolID            =
         $vol->strNotes          =
         $vol->dHoursWorked      =
         $vol->lPeopleID         =
         $vol->lHouseholdID      =
         $vol->strFName          =
         $vol->strLName          =
         $vol->strSafeNameFL     =
         $vol->strAddr1          =
         $vol->strAddr2          =
         $vol->strCity           =
         $vol->strState          =
         $vol->strCountry        =
         $vol->strZip            =
         $vol->strAddr           =
         $vol->strPhone          =
         $vol->strCell           =
         $vol->strEmail          = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->vols[$idx] = new stdClass;
            $vol = &$this->vols[$idx];
            $vol->lKeyID            = $row->vsa_lKeyID;
            $vol->lEventDateShiftID = $row->vsa_lEventDateShiftID;
            $vol->lVolID            = $row->vsa_lVolID;
            $vol->strNotes          = $row->vsa_strNotes;
            $vol->dHoursWorked      = $row->vsa_dHoursWorked;
            $vol->lPeopleID         = $row->vol_lPeopleID;
            $vol->lHouseholdID      = $row->pe_lHouseholdID;
            $vol->strFName          = $row->pe_strFName;
            $vol->strLName          = $row->pe_strLName;
            $vol->strSafeNameFL     =
                 htmlspecialchars($row->pe_strFName.' '.$row->pe_strLName);

            $vol->strAddr1          = $row->pe_strAddr1;
            $vol->strAddr2          = $row->pe_strAddr2;
            $vol->strCity           = $row->pe_strCity;
            $vol->strState          = $row->pe_strState;
            $vol->strCountry        = $row->pe_strCountry;
            $vol->strZip            = $row->pe_strZip;
            $vol->strAddr           =
                       strBuildAddress(
                              $row->pe_strAddr1, $row->pe_strAddr2,   $row->pe_strCity,
                              $row->pe_strState, $row->pe_strCountry, $row->pe_strZip,
                              true);

            $vol->strPhone          = $row->pe_strPhone;
            $vol->strCell           = $row->pe_strCell;
            $vol->strEmail          = $row->pe_strEmail;

            ++$idx;
         }
      }
   }

   public function strVolIDsAsStr(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $strVolStr = '';
      foreach($this->vols as $clsV){
         $strVolStr .= ', '.$clsV->lVolID;
      }
      if ($strVolStr!='') $strVolStr = substr($strVolStr, 2);
      return($strVolStr);
   }

   public function lAddVolToShift($lShiftID, $lVolID, $strNotes){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
        "INSERT INTO vol_events_dates_shifts_assign
         SET
            vsa_lEventDateShiftID = $lShiftID,
            vsa_lVolID            = $lVolID,
            vsa_strNotes          = '',
            vsa_dHoursWorked      = 0,
            vsa_bRetired          = 0;";

      $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   public function removeVolFromShift($lVolAssignID){
    //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
        "DELETE FROM vol_events_dates_shifts_assign
         WHERE vsa_lKeyID = $lVolAssignID;";

      $this->db->query($sqlStr);
   }

   public function lTotVolsAssignedViaEventID($lEventID){
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM vol_events_dates_shifts_assign
            INNER JOIN  vol_events_dates_shifts ON vsa_lEventDateShiftID=vs_lKeyID
            INNER JOIN  vol_events_dates        ON vs_lEventDateID=ved_lKeyID
         WHERE ved_lVolEventID=$lEventID;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((integer)$row->lNumRecs);
   }

   public function lTotVolsAssignedViaShiftID($lShiftID){
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM vol_events_dates_shifts_assign
         WHERE vsa_lEventDateShiftID=$lShiftID;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((integer)$row->lNumRecs);
   }

   public function loadShiftsViaVolIDMonth($lMonth, $lYear, $lVolID, &$lNumShifts, &$shifts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow;
      $lNumShifts = 0;
      $shifts     = array();
      $sqlStr =
        "SELECT
            vsa_lKeyID, vsa_lEventDateShiftID, vsa_lVolID, vsa_strNotes,

            vs_lKeyID, vs_lEventDateID, vs_strShiftName, vs_strDescription,
            TIME_FORMAT(vs_dteShiftStartTime, '%l:%i %p') AS dteShiftStartTime, vs_enumDuration,

            DATE(ved_dteEvent)=DATE(NOW()) AS bToday,
            DATE(ved_dteEvent)>DATE(NOW()) AS bFuture,
            DATE(ved_dteEvent)<DATE(NOW()) AS bPast,

            ved_lKeyID, ved_lVolEventID, ved_dteEvent,
            vem_lKeyID, vem_strEventName, vem_strDescription
         FROM vol_events_dates_shifts_assign
            INNER JOIN  vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
            INNER JOIN  vol_events_dates        ON vs_lEventDateID       = ved_lKeyID
            INNER JOIN  vol_events              ON ved_lVolEventID       = vem_lKeyID
         WHERE NOT vsa_bRetired
            AND NOT vs_bRetired
            AND NOT vem_bRetired
            AND vsa_lVolID          = $lVolID
            AND MONTH(ved_dteEvent) = $lMonth
            AND YEAR(ved_dteEvent)  = $lYear
         ORDER BY ved_dteEvent, vem_strEventName, vs_dteShiftStartTime, vsa_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumShifts = $query->num_rows();

      if ($lNumShifts > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $shifts[$idx] = new stdClass;
            $shift = &$shifts[$idx];

            $shift->lAssignKeyID         = $row->vsa_lKeyID;
            $shift->lEventDateShiftID    = $row->vsa_lEventDateShiftID;
            $shift->lVolID               = $row->vsa_lVolID;
            $shift->strNotes             = $row->vsa_strNotes;
            $shift->lEventDateID         = $row->vs_lEventDateID;
            $shift->strShiftName         = $row->vs_strShiftName;
            $shift->strShiftDescription  = $row->vs_strDescription;
            $shift->dteShiftStartTime    = $row->dteShiftStartTime;
            $shift->enumDuration         = $row->vs_enumDuration;
            $shift->lVolEventID          = $row->ved_lVolEventID;
            $shift->mdteEvent            = $row->ved_dteEvent;
            $shift->dteEvent             = dteMySQLDate2Unix($row->ved_dteEvent);
            $shift->strEventName         = $row->vem_strEventName;
            $shift->strEventDescription  = $row->vem_strDescription;

            $shift->bToday               = $row->bToday;
            $shift->bFuture              = $row->bFuture;
            $shift->bPast                = $row->bPast;
            ++$idx;
         }
      }
   }

}

?>