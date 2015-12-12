<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class event_dates_view extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function viewDates($lDateID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lDateID,  'event date ID');

      $displayData = array();
      $displayData['lDateID'] = $lDateID = (integer)$lDateID;

         //------------------------------------------------
         // libraries / models / utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->model  ('vols/mvol_event_dates',             'clsVolEventDates');
      $this->load->model  ('vols/mvol_events',                  'clsVolEvents');
      $this->load->model  ('vols/mvol_event_dates_shifts',      'clsShifts');
      $this->load->model  ('vols/mvol_event_dates_shifts_vols', 'clsSV');
      $this->load->model  ('vols/mvol_event_hours',             'clsVolHours');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('dl_util/time_date');

      $this->clsVolEventDates->loadEventDateViaDateID($lDateID);
      $displayData['edate']    = $edate = &$this->clsVolEventDates->dates[0];
      $displayData['lEventID'] = $lEventID = $edate->lVolEventID;
      $this->clsVolEvents->loadEventsViaEID($lEventID);
      $displayData['contextSummary'] = $this->clsVolEvents->volEventHTMLSummary(0);

      $displayData['lNumEventDates'] = $lNumEventDates = $this->clsVolEventDates->lNumDatesViaEventID($lEventID);
      $this->clsShifts->loadShiftsViaEventDateID($lDateID);
      $displayData['lNumShifts'] = $lNumShifts = $this->clsShifts->lNumShifts;
      $displayData['lNumVolsTot'] = 0;

      if ($lNumShifts > 0){
         foreach ($this->clsShifts->shifts as $shift){
            $lShiftID = $shift->lKeyID;
            $this->clsSV->loadVolsViaShiftID($lShiftID);
            $shift->lNumVols = $this->clsSV->lNumVols;
            $displayData['lNumVolsTot'] += $shift->lNumVols;
            $shift->vols = $this->clsSV->vols;
            $shift->hoursLogged = $this->clsVolHours->dTotHoursWorkedViaShiftID($lShiftID);
         }
      }

      $displayData['shifts']     = &$this->clsShifts->shifts;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/vols', 'Volunteers',                  'class="breadcrumb"')
                                .' | '.anchor('volunteers/events_schedule/viewEventsList',     'Event List', 'class="breadcrumb"')
                                .' | '.anchor('volunteers/events_record/viewEvent/'.$lEventID, 'Event',      'class="breadcrumb"')
                                .' | Event Date';

      $displayData['title']          = CS_PROGNAME.' | Volunteers';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'vols/vol_event_date_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }









}