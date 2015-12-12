<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ts_projects extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function assignHrsToProject($lTSLogID){
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
      $this->load->model ('groups/mgroups', 'cgroups');
      $this->load->helper('groups/groups');
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);

      $this->cts->loadUserTSLogByLogID($lTSLogID, $lNumLogRecs, $logRecs, false);
      if ($lNumLogRecs == 0){
         redirect('staff/timesheets/ts_log_edit/error_tst_access');
      }

         // if this time sheet has already been submitted, error off (possibly a URL hack)
      if (!is_null($logRecs[0]->dteSubmitted)){
         redirect('staff/timesheets/ts_log_edit/error_edit_submitted');
      }

      $displayData['logRec'] = $logRec = &$logRecs[0];
      $lUserID = $logRec->lStaffID;
      $lTSTemplateID = $logRec->lTimeSheetID;

         // if time sheet is being edited by a 3rd party, make sure they are authorized
      if ($glUserID != $lUserID){
         if (!bAllowAccess('timeSheetAdmin')){
            redirect('staff/timesheets/ts_log_edit/error_tst_access');
         }
      }
      $displayData['lUserID'] = $lUserID;

         // load projects associated with this time sheet template
      $this->cgroups->groupMembershipViaFID(CENUM_CONTEXT_STAFF_TS_PROJECTS, $lTSTemplateID);
      $displayData['projects'] = $projects = &$this->cgroups->arrMemberInGroups;
      $lNumProjects = count($projects);
      if ($lNumProjects == 0){
         redirect('staff/timesheets/ts_log_edit/error_tst_noprojects');
      }

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      for ($idx=0; $idx<$lNumProjects; ++$idx){
         $this->form_validation->set_rules('txtPMin'.$idx,  'Project', 'callback_verifyProjectMinutes');
         $this->form_validation->set_rules('txtNotes'.$idx, 'Notes',   'trim');
      }

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            foreach ($projects as $project){
               $project->lAssignedMin = $this->cts->lProjectMinutesViaProjLog(
                              $project->lGroupID, $lTSLogID, $strNotes);
               $project->notes = htmlspecialchars($strNotes);
            }
         }else {
            setOnFormError($displayData);
            $idx = 0;
            foreach ($projects as $project){
               $project->lAssignedMin = set_value('txtPMin'.$idx);
               $project->notes = set_value('txtNotes'.$idx);
               ++$idx;
            }
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                                   .' | '.anchor('staff/timesheets/ts_log_edit/add_edit_ts/'.$lTSLogID.'/'.$lUserID, 'Time Sheet Log', 'class="breadcrumb"')
                                   .' | Project Hours';

         $displayData['title']          = CS_PROGNAME.' | Staff';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'staff/timesheets/project_hours_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $idx = 0;
         foreach ($projects as $project){
            $strVal = trim($_POST['txtPMin'.$idx]);
            $tFields = explode(':', $strVal);
            $project->lMinutes = ((int)$tFields[0])*60 + (int)$tFields[1];
            $project->notes = trim($_POST['txtNotes'.$idx]);
            ++$idx;
         }

         $this->cts->updateProjectAssignments($lTSLogID, $projects);
         $this->session->set_flashdata('msg', 'Your project assignments were updated.');
         redirect('staff/timesheets/ts_log_edit/add_edit_ts/'.$lTSLogID);
      }
   }

   function verifyProjectMinutes($strVal){
      $strVal = trim($strVal);
      $tFields = explode(':', $strVal);

      if (count($tFields)!=2){
         $this->form_validation->set_message('verifyProjectMinutes', 'Please use the format <b>hh:mm</b>');
         return(false);
      }elseif (!is_numeric($tFields[0]) || !is_numeric($tFields[0])) {
         $this->form_validation->set_message('verifyProjectMinutes', 'Please use the format <b>hh:mm</b>');
         return(false);
      }elseif (($tFields[0] < 0) || ($tFields[1] < 0)  || ($tFields[1] >= 60)){
         $this->form_validation->set_message('verifyProjectMinutes', 'Your times are out of range!');
         return(false);
      }else {
         return(true);
      }
   }


}