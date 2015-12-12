<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class events_cal extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewEventsCalendar(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow;
      $displayData = array();
      $displayData['js'] = '';      
      
         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('js/jq_month_picker');

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtMonth',     'Report starting month', 'trim|required|callback_eventsStartMonth');
      $this->form_validation->set_rules('ddlDuration',  '# of months', 'trim');
      
		if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');
         if (validation_errors()==''){
            $displayData['txtMonth']  = date('m/Y', $gdteNow);
            $displayData['lDuration'] = 3;
         }else {
            setOnFormError($displayData);
            $displayData['txtMonth']  = set_value('txtMonth');
            $displayData['lDuration'] = (integer)set_value('ddlDuration');;
         }

            //------------------------------------------------
            // breadcrumbs / page setup
            //------------------------------------------------
         $displayData['js'] .= strMonthPicker(true);

         $displayData['mainTemplate'] = 'vols/event_cal_view';
         $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                                    .' | Event Calendar';

         $displayData['title']        = CS_PROGNAME.' | Volunteers | Events Calendar';
         $displayData['nav']          = $this->mnav_brain_jar->navData();

         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $moYr = explode('/', $_POST['txtMonth']);
         $lMonth = (integer)$moYr[0];
         $lYear  = (integer)$moYr[1];

         $this->eventCal((integer)$_POST['ddlDuration'], $lMonth, $lYear);
      }
   }
   
   function eventsStartMonth($strMonth){
      if (bValidPickerMonth($strMonth, $strErr)){
         return(true);
      }else {
         $this->form_validation->set_message('eventsStartMonth', $strErr);
         return(false);
      }
   }  

   function eventCal($lNumMonths, $lMonth, $lYear){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->model('vols/mvol_event_dates', 'clsVolEventDates');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/time_date');
      
      $events = array();
      for ($idx=0; $idx<$lNumMonths; ++$idx){      
         $this->clsVolEventDates->eventsByMonth($lMonth, $lYear, $events[$idx]);
         ++$lMonth;
         if ($lMonth > 12){
            $lMonth = 1;
            ++$lYear;
         }
      }
      $displayData['events'] = &$events;
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['title']        = CS_PROGNAME.' | Events Calendar';
      $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                              .' | '.anchor('volunteers/events_cal/viewEventsCalendar', 'Event Calendar', 'class="breadcrumb"');
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'vols/event_calendar_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function viewVolAssignViaEvent($lEventID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lEventID,  'event ID');
   
         //------------------------------------------------
         // libraries / models / utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->model('vols/mvol_event_dates',             'clsVolEventDates');
      $this->load->model('vols/mvol_events',                  'clsVolEvents');
      $this->load->model('vols/mvol_event_dates_shifts',      'clsShifts');
      $this->load->model('vols/mvol_event_dates_shifts_vols', 'clsSV');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/time_date');

      $this->clsVolEvents->entireEventInfo($lEventID, $lNumEvents, $events);
      $displayData['events'] = &$events;
      
      $displayData['contextSummary'] = $this->clsVolEvents->volEventHTMLSummary(0);
      
/*      
      $this->clsVolEvents->loadEventsViaEID($lEventID);
      $displayData['strEventName']   = $this->clsVolEvents->events[0]->strEventName;
      
      $this->clsVolEventDates->loadEventDates($lEventID);      

      $displayData['lNumDates'] = $lNumDates = $this->clsVolEventDates->lNumDates;
      if ($lNumDates > 0){
         foreach ($this->clsVolEventDates->dates as $edate){
            $lEventDateID = $edate->lKeyID;
            if ($edate->lNumShifts > 0){
               $this->clsShifts->loadShiftsViaEventDateID($lEventDateID);
               $edate->shifts = arrayCopy($this->clsShifts->shifts);
               foreach ($edate->shifts as $shift){
                  $lShiftID = $shift->lKeyID;
                  $this->clsSV->loadVolsViaShiftID($lShiftID);
                  $shift->lNumVols = $lNumVols = $this->clsSV->lNumVols;
                  if ($lNumVols > 0){
                     $shift->vols = arrayCopy($this->clsSV->vols);
                  }
               }
            }
         }
      }  
      $displayData['dates'] = &$this->clsVolEventDates->dates;
*/ 
            
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/vols', 'Volunteers',                 'class="breadcrumb"')
                                .' | '.anchor('volunteers/events_schedule/viewEventsList',     'Event List', 'class="breadcrumb"')
                                .' | '.anchor('volunteers/events_record/viewEvent/'.$lEventID, 'Event',      'class="breadcrumb"')
                                .' | Event Date';

      $displayData['title']          = CS_PROGNAME.' | Volunteers';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'vols/vol_assign_via_event_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   
   
   }
}