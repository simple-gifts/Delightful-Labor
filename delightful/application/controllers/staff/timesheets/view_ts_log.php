<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class view_ts_log extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }


   function viewTS($lTSLogID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lTSLogID'] = $lTSLogID = (int)$lTSLogID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('staff/link_staff');
      $this->load->helper('dl_util/web_layout');
      $this->load->model ('staff/mtime_sheets', 'cts');
      $this->load->helper('staff/timesheet');
      $this->load->helper('dl_util/time_date');
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);
      
         //---------------------------
         // stripes
         //---------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;      
   
      $this->cts->loadUserTSLogByLogID($lTSLogID, $lNumLogRecs, $logRecs, true);
      if ($lNumLogRecs == 0){
         redirect('staff/timesheets/ts_log_edit/error_tst_access');
      }
      $displayData['logRec'] = $logRec = &$logRecs[0];
      $lUserID = $logRec->lStaffID;
      $lTSTemplateID = $logRec->lTimeSheetID;

         // if time sheet is being edited by a 3rd party, make sure they are authorized
      if ($glUserID != $lUserID){
         if (!bAllowAccess('timeSheetAdmin')){
//         if (!$this->cts->bIsUserAuthorizedToViewEdit($lTSTID, $glUserID)){
            redirect('staff/timesheets/ts_log_edit/error_tst_access');
         }
      }
      $displayData['lUserID'] = $lUserID;
      
         // load project assignments for this time sheet
      $this->cts->projectsViaLogID($lTSLogID, $displayData['lNumProjects'], $displayData['projects']); 
   
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                                .' | '.anchor('staff/timesheets/ts_log/viewLog', 'Time Sheet Log', 'class="breadcrumb"')
                                .' | View Time Sheet';

      $displayData['title']          = CS_PROGNAME.' | Staff';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'staff/timesheets/view_timesheet_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   
   
   }
   
   
   
   
}
   
   
