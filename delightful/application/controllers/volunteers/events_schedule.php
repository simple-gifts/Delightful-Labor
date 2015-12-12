<?php
class events_schedule extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewEventsList($strPastEvents='false'){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();

      $bShowPast = strtoupper($strPastEvents)=='TRUE';

         //-----------------------------
         // models and helpers
         //-----------------------------
      $this->load->model('vols/mvol_events',               'clsVolEvents');
      $this->load->model('vols/mvol_event_dates_shifts',     'clsShifts');
      $this->load->model('vols/mvol_event_dates',           'clsVolEventDates');
      $this->load->model('vols/mvol_event_dates_shifts_vols', 'clsSV');
      
      
      if ($bShowPast){
         $this->clsVolEvents->bCurrentFuture = false;
         $this->clsVolEvents->bPastEvents    = true;
         $displayData['strAltView'] =
                 'You are viewing past events. To view current and future events, click '
                 .strLinkView_VolEventsList(false, 'here', false).'.<br><br>';
      }else {
         $this->clsVolEvents->bCurrentFuture = true;
         $this->clsVolEvents->bPastEvents    = false;
         $displayData['strAltView'] =
                   'You are viewing current and future events. To view past events, click '
                  .strLinkView_VolEventsList(true, 'here', false).'.<br><br>';
      }
      $this->clsVolEvents->loadEvents();

      $displayData['lNumEvents'] = $lNumEvents = $this->clsVolEvents->lNumEvents;
      $displayData['events']     = &$this->clsVolEvents->events;

      if ($lNumEvents > 0){
         foreach($displayData['events'] as $event){
            $lEventID = $event->lKeyID;
            $event->lNumShifts       = $this->clsShifts->lNumShifsTotViaEventID  ($lEventID);
            $event->lTotVolsNeeded   = $this->clsShifts->lTotVolsNeededViaEventID($lEventID);
            $event->lTotVolsAssigned = $this->clsSV->lTotVolsAssignedViaEventID  ($lEventID);
         }
      }

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Events';
      $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                              .' | Event List';

      $displayData['mainTemplate'] = 'vols/event_schedule_list_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }


}
