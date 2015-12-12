<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class add_edit_tst_record extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEditTST($lTSTID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bAllowAccess('adminOnly')) return;

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lTSTID'] = $lTSTID = (int)$lTSTID;
      $displayData['bNew']   = $bNew = $lTSTID <= 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('staff/link_staff');
      $this->load->helper('dl_util/web_layout');
      $this->load->model ('staff/mtime_sheets', 'cts');
      $this->load->helper('staff/timesheet');

      if ($bNew){
         $tst = new stdClass;
      }else {
         $this->cts->loadTimeSheetTemplateViaTSTID($lTSTID);
         $displayData['tst'] = $tst = &$this->cts->timeSheetTemplates[0];
      }

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtTemplateName', 'Template Name',        'trim|required|callback_verifyUniqueTemplate['.$lTSTID.']');
      if ($bNew){
		   $this->form_validation->set_rules('ddlTP',           'Time Period',          'callback_verifyDDLSet');
		   $this->form_validation->set_rules('ddlStart',        'Starting Day of Week', 'callback_verifyStartDOW');
      }
		$this->form_validation->set_rules('ddlTimeGrain',    'Time Granularity',     'callback_verifyDDLSet');
		$this->form_validation->set_rules('chk24Hour',       '24-Hour Format',       '');
		$this->form_validation->set_rules('chkHidden',       'Hidden',               '');
		$this->form_validation->set_rules('txtNotes',        'Notes',                'trim');
		$this->form_validation->set_rules('txtAck',          'Acknowledgement Text', 'trim');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($bNew){
               $displayData['formData']->txtTemplateName  = '';
               $displayData['formData']->txtNotes         = '';
               $displayData['formData']->txtAck           = '';
               $displayData['formData']->b24HrTime        = false;
               $displayData['formData']->strTimePeriodDDL = ts_util\strTimePeriodDDL('ddlTP', true, -1);
               $displayData['formData']->strStartSOWDDL   = ts_util\strDaysOfWeekDDL('ddlStart', true, -1);
               $displayData['formData']->strTimeGrainDDL  = ts_util\strTimeGrainularityDDL('ddlTimeGrain', true, -1);
            }else {
               $displayData['formData']->txtTemplateName  = $tst->strTSName;
               $displayData['formData']->txtNotes         = $tst->strNotes;
               $displayData['formData']->txtAck           = $tst->strAckText;
               $displayData['formData']->b24HrTime        = $tst->b24HrTime;
               $displayData['formData']->bHidden          = $tst->bHidden;
               $displayData['formData']->strTimePeriodDDL = ts_util\strTimePeriodDDL      ('ddlTP',        true, $tst->enumRptPeriod);
               $displayData['formData']->strStartSOWDDL   = ts_util\strDaysOfWeekDDL      ('ddlStart',     true, $tst->lFirstDayOfWeek);
               $displayData['formData']->strTimeGrainDDL  = ts_util\strTimeGrainularityDDL('ddlTimeGrain', true, (int)$tst->enumGranularity);
            }
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtTemplateName  = set_value('txtTemplateName');
            $displayData['formData']->txtNotes         = set_value('txtNotes');
            $displayData['formData']->txtAck           = set_value('txtAck');
            $displayData['formData']->b24HrTime        = set_value('chk24Hour')=='true';
            $displayData['formData']->bHidden          = set_value('chkHidden')=='true';
            $displayData['formData']->strTimePeriodDDL = ts_util\strTimePeriodDDL      ('ddlTP',        true, @$_POST['ddlTP']);
            $displayData['formData']->strStartSOWDDL   = ts_util\strDaysOfWeekDDL      ('ddlStart',     true, (int)@$_POST['ddlStart']);
            $displayData['formData']->strTimeGrainDDL  = ts_util\strTimeGrainularityDDL('ddlTimeGrain', true, (int)@$_POST['ddlTimeGrain']);
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                   .' | '.anchor('admin/timesheets/view_tst_record/viewTSTList', 'Staff Time Sheet Templates', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').' Time Sheet Template';

         $displayData['title']          = CS_PROGNAME.' | Admin';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'admin/staff_tst_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->cts->timeSheetTemplates    = array();
         $this->cts->timeSheetTemplates[0] = new stdClass;

         $outTst = &$this->cts->timeSheetTemplates[0];
         $outTst->strTSName = trim($_POST['txtTemplateName']);
         if ($bNew){
            $outTst->enumRptPeriod   = trim($_POST['ddlTP']);
            $outTst->lFirstDayOfWeek = (int)trim($_POST['ddlStart']);
         }else {
            $outTst->enumRptPeriod   = $tst->enumRptPeriod;
            $outTst->lFirstDayOfWeek = $tst->lFirstDayOfWeek;
         }
         $outTst->enumGranularity = (int)trim($_POST['ddlTimeGrain']);
         $outTst->b24HrTime       = trim(@$_POST['chk24Hour'])=='true';
         $outTst->bHidden         = trim(@$_POST['chkHidden'])=='true';
         $outTst->strNotes        = trim($_POST['txtNotes']);
         $outTst->strAckText      = trim($_POST['txtAck']);

            //------------------------------------
            // update db tables and return
            //------------------------------------
         if ($bNew){
            $lTSTID = $this->cts->addTimeSheetTemplate();
            $this->session->set_flashdata('msg', 'Time Sheet Template added');
         }else {
            $this->cts->updateTimeSheetTemplate($lTSTID);
            $this->session->set_flashdata('msg', 'Time Sheet Template updated');
         }
         redirect('admin/timesheets/view_tst_record/viewTSTRecord/'.$lTSTID);
      }
   }

   function verifyDDLSet($strVal){
      $strVal = trim($strVal);
      if ($strVal=='' || (int)$strVal < 0){
         $this->form_validation->set_message('verifyDDLSet', 'Please make a selection.');
         return(false);
      }else {
         return(true);
      }
   }

   function verifyStartDOW($strVal){
      $enumTP = @$_POST['ddlTP'];
      if ($enumTP=='Weekly'){
         if ($strVal=='' || (int)$strVal < 0){
            $this->form_validation->set_message('verifyStartDOW', 'Please make a selection for <b>Starting Day of the Week</b>.');
            return(false);
         }else {
            return(true);
         }
      }else {
         return(true);
      }
   }

   function verifyUniqueTemplate($strVal, $params){
      $arrayParams = explode(',', $params);
      $lTSTID   = (integer)$arrayParams[0];

      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                $strVal,    'ts_strTSName',
                $lTSTID,    'ts_lKeyID',
                true,       'ts_bRetired',
                false,      null, null,
                false,      null, null,
                'staff_timesheets')){
         $this->form_validation->set_message('verifyUniqueTemplate',
                  'The Time Sheet Template Name you specified is already being used.');
         return(false);
      }else {
         return(true);
      }
   }

   function setTSTUsers($lTSTID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bAllowAccess('adminOnly')) return;

      $lTSTID = (int)$lTSTID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('staff/mtime_sheets', 'cts');
      $this->load->helper('staff/timesheet');

         // out with the old
      $this->cts->removeTSUsersViaTSTID($lTSTID);

         // in with the new
      if (isset($_POST['chkGroup'])){
         $userIDs = array();
         foreach ($_POST['chkGroup'] as $lUserID){
            $userIDs[] = (int)$lUserID;
         }
         $this->cts->addTSUsersViaTSTID($lTSTID, $userIDs);
      }

      $this->session->set_flashdata('msg', 'Time Sheet Assignments updated');
      redirect('admin/timesheets/view_tst_record/viewTSTRecord/'.$lTSTID);
   }

   function setTSTAdmins(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bAllowAccess('adminOnly')) return;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('staff/mtime_sheets', 'cts');
      $this->load->helper('staff/timesheet');

         // out with the old
      $this->cts->removeTSAdmins();

         // in with the new
      if (isset($_POST['chkGroup'])){
         $userIDs = array();
         foreach ($_POST['chkGroup'] as $lUserID){
            $userIDs[] = (int)$lUserID;
         }
         $this->cts->addTSAdmins($userIDs);
      }

      $this->session->set_flashdata('msg', 'Time Sheet Administrator List updated');
      redirect('admin/timesheets/view_tst_record/viewTSTList');
   }




}




