<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_vol_schedule extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function volSchedule($lVolID, $strShowType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->model('reports/mreports', 'clsReports');
      $this->load->model('vols/mvol', 'clsVol');
      $this->load->model('people/mpeople',            'clsPeople');
//      $this->load->helper('dl_util/email_web');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

      $this->clsVol->loadVolRecsViaVolID($lVolID, true);
      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_VOLHOURSCHEDULE,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => true,
                             'viewFile'       => 'pre_generic_rpt_view',
                             'lVolID'         => $lVolID,
                             'contextSummary' => $this->clsVol-> volHTMLSummary(0),
                             'strShowType'    => $strShowType);

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }

   function past(){
      $this->eventSchedulePastFuture(true);
   }

   function current(){
      $this->eventSchedulePastFuture(false);
   }

   function eventSchedulePastFuture($bPast){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('vols/mvol_events', 'clsVolEvents');
      $this->load->model('vols/mvol_event_dates', 'clsVolEventDates');
      $this->load->helper('dl_util/web_layout');
      $this->load->library('generic_form');

         //------------------------------------
         // load the check/uncheck support
         //------------------------------------
      $this->load->helper('js/set_check_boxes');
      $displayData['js'] = insertCheckSet();
      $this->load->helper('js/verify_check_set');
      $displayData['js'] .= verifyCheckSet();


      $this->clsVolEvents->bCurrentFuture = !$bPast;
      $this->clsVolEvents->bPastEvents    = $bPast;
      $this->clsVolEvents->loadEvents();

      $displayData['events']     = &$this->clsVolEvents->events;
      $displayData['lNumEvents'] = $this->clsVolEvents->lNumEvents;
      $displayData['strLabel']   = $strLabel = ($bPast ? 'Past' : 'Current and Future');

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Volunteer Events: '.$strLabel;

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_spon_schedule_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function run(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lEventIDs = array();
      foreach ($_POST['chkEvent'] as $strEventID){
         $lEventIDs[] = (integer)$strEventID;
      }

      $this->load->model('reports/mreports', 'clsReports');
      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_VOLEVENTSCHEDULE,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => false,
                             'viewFile'       => 'pre_vol_schedule_view',
                             'lEventIDs'      => arrayCopy($lEventIDs));

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);

   }











}



