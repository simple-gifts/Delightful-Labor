<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class custom_fields extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewRecFields($lReportID, $strAsNew=''){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');

      $bAsNew = $strAsNew=='true';

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lReportID'] = $lReportID;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper ('creports/link_creports');
      $this->load->helper ('reports/creport_util');
      $this->load->helper ('dl_util/context');
      $this->load->library('generic_form');
      $this->load->model  ('admin/madmin_aco'); 
      $this->load->model  ('personalization/muser_schema',         'cUFSchema');
      $this->load->model  ('personalization/muser_fields',         'clsUF');
      $this->load->model  ('personalization/muser_fields_display', 'clsUFD');
      $this->load->model  ('creports/mcreports',                   'clsCReports');
      $this->load->model  ('admin/mpermissions',                   'perms');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('js/div_hide_show');
      $this->load->helper ('js/check_boxes_in_div');
      $this->load->helper ('reports/search');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

      $displayData['js'] .= showHideDiv();
      $displayData['js'] .= checkUncheckInDiv();

         //------------------------------------------------
         // load report
         //------------------------------------------------
      $this->clsCReports->loadReportViaID($lReportID, false);
      $displayData['report']    = $report = &$this->clsCReports->reports[0];
      
      if (!$report->bUserHasReadAccess){
         vid_bTestFail($this, false, 'Custom Report', $lReportID);
         return;
      }   
      
      $displayData['bReadOnly'] = !$report->bUserHasWriteAccess;
      
      $enumRptType = $report->enumRptType;
      $displayData['contextSummary'] = $this->clsCReports->strCReportHTMLSummary();

      $displayData['tables'] = $this->clsCReports->loadTableStructures($enumRptType);

         // fields currently checked
      $chkFields = $this->clsCReports->cReportFields($lReportID, $lNumFields, true);
      foreach ($displayData['tables'] as $table){
         foreach ($table->fields as $field){
            if ($bAsNew){
               $field->bChecked = true;
            }else {
               $field->bChecked = in_array ($field->internalName, $chkFields);
            }
         }
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | '.anchor('creports/custom_directory/view/'.$glUserID, 'Custom Report Directory', 'class="breadcrumb"')
                                .' | '.anchor('creports/custom_directory/viewRec/'.$lReportID, 'Custom Report: '.$report->strSafeName, 'class="breadcrumb"')
                                .' | Report Fields';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'creports/custom_fields_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function saveFields($lReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper ('reports/creport_util');
      $this->load->model  ('personalization/muser_schema', 'cUFSchema');
      $this->load->model  ('admin/madmin_aco'); 
      $this->load->model  ('creports/mcreports',           'clsCReports');

      $lNumFields = count(@$_POST['chkFields']);

      if ($lNumFields==0){
         $this->clsCReports->deleteReportFields($lReportID);
      }else {
         $this->clsCReports->saveFields($lReportID, $_POST['chkFields']);
      }

      $this->session->set_flashdata('msg', 'The report fields were updated');
      redirect('creports/custom_directory/viewRec/'.$lReportID);
   }

}
