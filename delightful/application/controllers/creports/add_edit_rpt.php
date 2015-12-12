<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class add_edit_rpt extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function add_edit($lCRptID=0){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyIDsViaType($this, CENUM_CONTEXT_CUSTOMREPORT, $lCRptID, false);
   
      $displayData = array();
      $displayData['bNew']      = $bNew = $lCRptID <= 0;
      $displayData['lReportID'] = (integer)$lCRptID;
      $displayData['js']        = '';

         //-------------------------
         // models & helpers
         //-------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->model ('admin/madmin_aco'); 
      $this->load->model ('creports/mcreports', 'clsCReports');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('reports/creport_util');

      $cRptTypes = loadCReportTypeArray();
      $this->clsCReports->loadReportViaID($lCRptID, false);
      $report = &$this->clsCReports->reports[0];
      
      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtName', 'Report Name', 'trim|required|callback_cRptNameDupTest['.$lCRptID.']');
		$this->form_validation->set_rules('txtNotes', 'Notes',      'trim');
		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');
         
         if (!$bNew){
            $displayData['formData']->strRptType = $report->strXlatedRptType;
         }

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['formData']->strName   = $report->strName;
            $displayData['formData']->strNotes  = $report->strNotes;
            $displayData['formData']->bPrivate  = $report->bPrivate;

            if ($bNew){
               $displayData['formData']->strCRptTypeDDL  =
                               strCRptTypesDDL($cRptTypes, false, $report->enumRptType);
            }
         }else {
            setOnFormError($displayData);

            $displayData['formData']->strName   = set_value('txtName');
            $displayData['formData']->strNotes  = set_value('txtNotes');
            $displayData['formData']->bPrivate  = @$_POST['chkPrivate'] == 'TRUE';
            if ($bNew){
               $displayData['formData']->strCRptTypeDDL  =
                               strCRptTypesDDL($cRptTypes, false, @$_POST['ddlCRpt']);
            }
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle'] = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | '.($bNew ? 'Add' : 'Edit').' Custom Report';

         $displayData['title']          = CS_PROGNAME.' | Custom Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'creports/add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $report->strName     = trim($_POST['txtName']);
         $report->strNotes    = trim($_POST['txtNotes']);
         $report->bPrivate    = @$_POST['chkPrivate'] == 'TRUE';
         if ($bNew) $report->enumRptType = trim($_POST['ddlCRpt']);

            //------------------------------------
            // update db tables and return
            //------------------------------------
         if ($bNew){
            $lCRptID = $this->clsCReports->addNewCReport();
            $this->session->set_flashdata('msg', 'Custom report added');
            redirect('creports/view_fields/view/'.$lCRptID.'/true');
         }else {
            $this->clsCReports->updateCReport($lCRptID);
            $this->session->set_flashdata('msg', 'Custom report updated');
            redirect('creports/custom_directory/viewRec/'.$lCRptID);
         }
      }
   }

   function cRptNameDupTest($strName, $cRptID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                trim($strName),  'crd_strName',
                $cRptID,         'crd_lKeyID',
                true,            'crd_bRetired',
                false, null, null,
                false, null, null,
                'creport_dir')){
         return(false);
      }else {
         return(true);
      }
   }


}
