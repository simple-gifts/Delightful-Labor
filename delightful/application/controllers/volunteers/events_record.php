<?php
class events_record extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewEvent($lEventID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gbDateFormatUS;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lEventID, 'event ID');

      $displayData = array();
      $displayData['lEventID'] = $lEventID = (integer)$lEventID;
      $displayData['js'] = '';

         //------------------------------------------------
         // libraries / models / utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
      $this->load->model('vols/mvol_events',               'clsVolEvents');
      $this->load->model('vols/mvol_event_dates',           'clsVolEventDates');
      $this->load->model('vols/mvol_event_dates_shifts',     'clsShifts');
      $this->load->model('vols/mvol_event_dates_shifts_vols', 'clsSV');
      $this->load->model('vols/mvol_event_hours',           'clsVolHours');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/record_view');
      $this->load->helper('img_docs/link_img_docs');
      $this->load->helper('dl_util/time_date');      

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();
      
      $this->clsVolEvents->loadEventsViaEID($lEventID);
      $displayData['event'] = $event = &$this->clsVolEvents->events[0];
      
      $this->clsVolEventDates->loadEventDates($lEventID);
      $displayData['eventDates'] = &$this->clsVolEventDates->dates;
      $displayData['lNumDates']  = $lNumDates = &$this->clsVolEventDates->lNumDates;
      $displayData['dNumHrs']    = $this->clsVolHours->dTotHoursWorkedViaEventID($lEventID);
      
      if ($lNumDates > 0){
         $idx = 0;
         foreach ($displayData['eventDates'] as $edate){
            if ($edate->lNumShifts > 0){
               $lEventDateID = $edate->lKeyID;
               $this->clsShifts->loadShiftsViaEventDateID($lEventDateID);
               $displayData['eventDates'][$idx]->shiftInfo = array();
               $jIdx = 0;
               foreach ($this->clsShifts->shifts as $shift){                  
                  $displayData['eventDates'][$idx]->shiftInfo[$jIdx] = new stdClass;
                  $dispShift = &$displayData['eventDates'][$idx]->shiftInfo[$jIdx];
                  $dispShift->shiftID           = $lShiftID = $shift->lKeyID;
                  $dispShift->shiftName         = $shift->strShiftName;
                  $dispShift->lNumVolsNeeded    = $shift->lNumVolsNeeded;
                  $dispShift->lNumVolsAssigned  = $this->clsSV->lTotVolsAssignedViaShiftID($lShiftID);
                  $dispShift->enumDuration      = $shift->enumDuration;
                  $dispShift->dteEventStartTime = $shift->dteEventStartTime;
                  $dispShift->strJobCode        = $shift->strJobCode;
                  $dispShift->hoursLogged       = $dHours = $this->clsVolHours->dTotHoursWorkedViaShiftID($lShiftID);
                  
                  ++$jIdx;
               }               
            }
            ++$idx;
         }         
      }      
        //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['title']        = CS_PROGNAME.' | Vol Event';
      $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                              .' | '.anchor('volunteers/events_schedule/viewEventsList', 'Event List', 'class="breadcrumb"')
                              .' | Event';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'vols/vol_event_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');      
   }



}