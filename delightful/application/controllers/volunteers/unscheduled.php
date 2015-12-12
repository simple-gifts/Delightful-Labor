<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class unscheduled extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEditActivityAsVol($lVolID, $lActivityID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbVolLogin, $glVolPeopleID, $gdteNow;
      if (!bTestForURLHack('volEditHours')) return;

      $this->load->model ('vols/mvol', 'clsVol');
      $lVolID = (int)$lVolID;
      $lVolSessionID = $this->clsVol->lVolIDViaPeopleID($glVolPeopleID);
      if ($lVolSessionID != $lVolID) {
         bTestForURLHack('forceFail');
         return;
      }
      $this-> addEditActivityCommon(true, $lVolID, $lActivityID);
   }

   function addEditActivity($lVolID, $lActivityID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('dataEntryPeopleBizVol')) return;

      $this-> addEditActivityCommon(false, $lVolID, $lActivityID);
   }

   private function addEditActivityCommon($bAsVol, $lVolID, $lActivityID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lVolID, 'volunteer ID');

      $displayData = array();
      $displayData['lVolID']      = $lVolID = (integer)$lVolID;
      $displayData['lActivityID'] = $lActivityID = (integer)$lActivityID;
      $displayData['bNew']        = $bNew = $lActivityID <= 0;
      $displayData['bAsVol']      = $bAsVol;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model('vols/mvol_event_hours', 'clsVolHours');
      $this->load->model('vols/mvol',             'clsVol');
      $this->load->model('people/mpeople',        'clsPeople');
      $this->load->model('util/mlist_generic',    'clsList');
      $this->load->helper('dl_util/time_date');  // for date verification
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper('dl_util/web_layout');

      $this->clsVolHours->loadVolActivitiesViaID($lActivityID);
      $act = &$this->clsVolHours->unActivity[0];

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('ddlStart',    'Start Time', 'trim|callback_vactVerifyStart');
		$this->form_validation->set_rules('ddlDuration', 'Duration',   'trim|callback_vactVerifyDuration');
		$this->form_validation->set_rules('ddlActivity', 'Activity',   'trim|callback_vactVerifyAct');
      $this->form_validation->set_rules('ddlJobCode',  'Job Code',   'trim');
      $this->form_validation->set_rules('txtDate',     'Date of Volunteer Activity',  'trim|required'
                                                                    .'|callback_verifyUnDateValid');
		$this->form_validation->set_rules('txtNotes',    'Notes', 'trim');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');


            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if (is_null($act->dteActivityDate)){
               $displayData['formData']->txtDate    = '';
               $displayData['formData']->lStartTime = strDisplayTimeDDL(null, true, true);
            }else {
               $displayData['formData']->txtDate    = strNumericDateViaMysqlDate($act->mysqlActivityDate, $gbDateFormatUS);
               $displayData['formData']->lStartTime = strDisplayTimeDDL($act->dteActivityDate, true, true);
            }

            $displayData['formData']->strNotes = $act->strNotes;
            $displayData['formData']->enumDuration = strDurationDDL   (true, false,
                                                             lDurationHrsToQuarters($act->dHoursWorked), true);

               // activity generic list
            $this->clsList->strBlankDDLName = '&nbsp;';
            $this->clsList->enumListType = CENUM_LISTTYPE_VOLACT;
            $displayData['formData']->strVolActivity     =
                            $this->clsList->strLoadListDDL('ddlActivity', true, $act->lActivityID);

               // job code generic list
            $this->clsList->enumListType = CENUM_LISTTYPE_VOLJOBCODES;
            $this->clsList->strBlankDDLName = '(no job code)';
            $displayData['strDDLJobCode'] = $this->clsList->strLoadListDDL('ddlJobCode', true, $act->lJobCode);
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtDate      = set_value('txtDate');            
            $displayData['formData']->lStartTime   = $lStartTime = strDisplayTimeDDL(set_value('ddlStart'), true, true);            
            $displayData['formData']->strNotes     = set_value('txtNotes');
            $displayData['formData']->enumDuration = strDurationDDL(true, false, set_value('ddlDuration'), true);

               // activity generic list
            $this->clsList->strBlankDDLName = '&nbsp;';
            $this->clsList->enumListType = CENUM_LISTTYPE_VOLACT;
            $displayData['formData']->strVolActivity     =
                            $this->clsList->strLoadListDDL('ddlActivity', true, set_value('ddlActivity'));

               // job code generic list
            $this->clsList->enumListType = CENUM_LISTTYPE_VOLJOBCODES;
            $this->clsList->strBlankDDLName = '(no job code)';
            $displayData['strDDLJobCode'] = $this->clsList->strLoadListDDL('ddlJobCode', true, set_value('ddlJobCode'));
         }

         $this->clsVol->loadVolRecsViaVolID($lVolID, true);
         $displayData['contextSummary'] = $this->clsVol->volHTMLSummary(0);

            //--------------------------
            // breadcrumbs
            //--------------------------
         if ($bAsVol){
            $displayData['pageTitle'] = 'Log volunteer hours';
         }else {
            $displayData['pageTitle'] = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                              .' | '.anchor('/volunteers/vol_record/volRecordView/'.$lVolID, 'Record', 'class="breadcrumb"')
                              .' | Log volunteer hours';
         }

         $displayData['title']          = CS_PROGNAME.' | Volunteers';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'vols/hrs_unscheduled_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $act->lVolID       = $lVolID;
         $act->strNotes     = trim($_POST['txtNotes']);
         $act->lActivityID  = (integer)trim($_POST['ddlActivity']);
         $act->dHoursWorked = ((integer)trim($_POST['ddlDuration']))/4;
         $strDate           = trim($_POST['txtDate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);

         $lDDLStart = (int)$_POST['ddlStart'];
         quartersToHrsMin($lDDLStart, $lHours, $lMinutes);

         $act->dteActivityDate = mktime($lHours, $lMinutes, 0, $lMon, $lDay, $lYear);
         
         $act->lJobCode = (integer)$_POST['ddlJobCode'];
         if ($act->lJobCode <= 0) $act->lJobCode = null;         

            //------------------------------------
            // update db tables and return
            //------------------------------------
         if ($bNew){
            $lActivityID = $this->clsVolHours->addUnscheduledHrs();
            $this->session->set_flashdata('msg', 'Volunteer hours were recorded.');
         }else {
            $this->clsVolHours->updateUnscheduledHrs($lActivityID);
            $this->session->set_flashdata('msg', 'Record updated');
         }
         if ($bAsVol){
            redirect('vol_reg/vol_hours/view');
         }else {
            redirect('reports/pre_vol_hours/viaVolID/'.$lVolID.'/false');
         }
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
   function verifyUnDateValid($strDate){
      return(bValidVerifyDate($strDate));
   }
   function vactVerifyStart($lDDLSel){
      return(((integer)$lDDLSel) >= 0);
   }
   function vactVerifyDuration($lDDLSel){
      return(((integer)$lDDLSel) > 0);
   }
   function vactVerifyAct($lDDLSel){
      return(((integer)$lDDLSel) > 0);
   }


   function removeUnscheduled($lVolID, $lActivityID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lVolID, 'volunteer ID');

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model('vols/mvol_event_hours', 'clsVolHours');
      $this->clsVolHours->removeUnscheduledActivity($lActivityID);

      $this->session->set_flashdata('msg', 'Activity removed');
      redirect('reports/pre_vol_hours/viaVolID/'.$lVolID.'/false');
   }


}

