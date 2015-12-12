<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class performance extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEditPR($lSReportID=0){
   //------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------
      global $glUserID, $gdteNow;

      if (!bTestForURLHack('notVolunteer')) return;

      $displayData = array();
      $displayData['js']         = '';
      $displayData['lSReportID'] = $lSReportID = (integer)$lSReportID;
      $displayData['bNew']       = $bNew = $lSReportID <= 0;

         //-------------------------------------
         // models, libraries, and helpers
         //-------------------------------------
      $this->load->model('staff/mstaff_status', 'cstat');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper('staff/link_staff');
      $this->load->helper('staff/status_report');

         // for new status reports, we need the parent template in
         // order to attach the various entries
      if ($bNew){
         $this->cstat->initNewStatRptTemplate();
         $displayData['lSReportID'] = $lSReportID = $this->cstat->addNewStatusReport();
      }
         // load the status report
      $this->cstat->loadStatusReportViaRptID($lSReportID);
      $displayData['sreport'] = $sreport = &$this->cstat->sreports[0];

         // test for url hack into another's report
      if (!$bNew){
         if ($glUserID != $sreport->lUserID) return;
      }
      if ($bNew) $sreport->bPublished = false;

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('chkPublished',  'Published?', 'trim');

      if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');
         
         $this->load->helper ('js/div_hide_show');
         $displayData['js'] .= showHideDiv();

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['formData']->bPublished      = $sreport->bPublished;
         }else {
            setOnFormError($displayData);
            $displayData['formData']->bPublished      = set_value('chkPublished')=='true';
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      =
                                       anchor('aayhf/main/aayhfMenu', 'AAYHF', 'class="breadcrumb"')
                                .' | '.($bNew ? 'Add New' : 'Edit').' Weekly Report';

         $displayData['title']          = CS_PROGNAME.' | Weekly Report';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'aayhf/aayhf_staff/status_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $sreport->bPublished    = $bPublished = trim(@$_POST['chkPublished']) == 'true';

            //------------------------------------
            // update db tables and return
            //------------------------------------
         $this->cstat->updateStatusReport($lSReportID);
            
         $this->session->set_flashdata('msg', 'Your weekly report was '
                .($bPublished ? '<b>published</b>' : 'saved as a draft').'.');
         redirect('staff/performance/log');
      }
   }
   
   function log(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $this->logViaUserID($glUserID);
   }

   function logViaUserID($lUserID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbAdmin, $glUserID;

      $this->load->helper('dl_util/verify_id');

      verifyID($this, $lUserID, 'user ID');
      $lUserID = (int)$lUserID;

      $displayData = array();
      $displayData['js'] = '';

         // defense against the dark arts
      if (!$gbAdmin && ($lUserID != $glUserID)){
         if (!bAllowAccess('management')){
            $this->session->set_flashdata('error', '<b>ERROR:</b> User ID is not valid.</font>');
            redirect('main/menu/home');
         }
      }

      $displayData['bSelfSame'] = $lUserID == $glUserID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('staff/mstaff_status', 'cstat');
      $this->load->model ('admin/muser_accts',   'clsUser');
      $this->load->model ('admin/mpermissions',  'perms');
      $this->load->helper('staff/link_staff');
      $this->load->helper('staff/status_report');

         //--------------------------
         // Stripes
         //--------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         // load the status report history
      $this->cstat->loadStatusReportViaUserID($lUserID);
      $displayData['lNumSReports'] = $lNumSReports = $this->cstat->lNumSReports;
      $displayData['sreports']     = $sreports     = &$this->cstat->sreports;

         // load the reviews
      if ($lNumSReports > 0){
         foreach ($sreports as $srpt){
            $lSReportID = $srpt->lKeyID;
            $this->cstat->loadReviewsViaRptID($lSReportID, $srpt->lNumReviews, $srpt->reviewLog);
         }
      }

      $this->clsUser->loadSingleUserRecord($lUserID);
      $displayData['uRec'] = &$this->clsUser->userRec[0];

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      =
                                    anchor('aayhf/main/aayhfMenu', 'AAYHF', 'class="breadcrumb"')
                             .' | Weekly Report Log';

      $displayData['title']          = CS_PROGNAME.' | Weekly Report';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'aayhf/aayhf_staff/status_log_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function remove($lSReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      if (!bTestForURLHack('notVolunteer')) return;

      $displayData = array();
      $displayData['js']         = '';
      $displayData['lSReportID'] = $lSReportID = (integer)$lSReportID;

         //-------------------------------------
         // models, libraries, and helpers
         //-------------------------------------
      $this->load->model('staff/mstaff_status', 'cstat');
      $this->load->helper('staff/link_staff');

         // load the status report
      $this->cstat->loadStatusReportViaRptID($lSReportID);
      $displayData['sreport'] = $sreport = &$this->cstat->sreports[0];

      if ($glUserID != $sreport->lUserID) return;
      if ($sreport->bPublished) return;

      $this->cstat->deleteStatusReport($lSReportID);
      $this->session->set_flashdata('msg', 'Your draft status report was deleted');
      redirect('staff/performance/log');
   }

   function testDrafts(){
   //---------------------------------------------------------------------
   // if user has unpublished status reports, give them the option
   // of working on an existing report; else start a new status report
   //---------------------------------------------------------------------   
      global $glUserID;
      
         //-------------------------------------
         // models, libraries, and helpers
         //-------------------------------------
      $this->load->model('staff/mstaff_status', 'cstat');
      $this->load->helper('staff/link_staff');
      
      $this->cstat->loadStatusDrafts($glUserID); 
      if ($this->cstat->lNumSReports == 0){
         redirect('staff/performance/addEditPR/0');
      }
      
      $displayData['sreports'] = $sreport = &$this->cstat->sreports;
      $displayData['lNumReports'] = $this->cstat->lNumSReports;
      
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      =
                                    anchor('aayhf/main/aayhfMenu', 'AAYHF', 'class="breadcrumb"')
                             .' | Weekly Report';

      $displayData['title']          = CS_PROGNAME.' | Weekly Report';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'aayhf/aayhf_staff/status_select_draft_view';
      $this->load->vars($displayData);
      $this->load->view('template');      
   }


}






