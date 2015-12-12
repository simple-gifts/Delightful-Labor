<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class sort_terms extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function add_edit($lReportID, $lTermID=0){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
   
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');
      verifyIDsViaType($this, CENUM_CONTEXT_CUSTOMREPORTTERM, $lTermID, true);

      $displayData = array();
      $displayData['lReportID'] = $lReportID = (integer)$lReportID;
      $displayData['lTermID']   = $lTermID   = (integer)$lTermID;
      $displayData['bNew']      = $bNew      = $lTermID <= 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper ('reports/creport_util');
      $this->load->helper ('creports/link_creports');
      $this->load->helper ('reports/search');
      $this->load->helper ('dl_util/context');
      $this->load->model  ('personalization/muser_schema',  'cUFSchema');
      $this->load->model  ('personalization/muser_fields',  'clsUF');
      $this->load->model  ('admin/mpermissions',            'perms');
      $this->load->model  ('admin/madmin_aco'); 
      $this->load->model  ('creports/mcreports',            'clsCReports');
      $this->load->helper ('dl_util/web_layout');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

         //------------------------------------------------
         // load report
         //------------------------------------------------
      $this->clsCReports->loadReportViaID($lReportID, false);
      $displayData['report']    = $report = &$this->clsCReports->reports[0];
      $enumRptType = $report->enumRptType;
      $displayData['contextSummary'] = $this->clsCReports->strCReportHTMLSummary();

      $displayData['tables'] = $this->clsCReports->loadTableStructures($enumRptType);

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('rdoField', 'Field', 'trim');
      if ($this->form_validation->run() == FALSE){

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
         }else {
            setOnFormError($displayData);
         }

            // set up the generic field selection view
         $displayData['strFormLink']    = 'creports/sort_terms/add_edit/'.$lReportID.'/'.$lTermID;
         $displayData['strLabel']       = 'Select a field to <b>order</b> your report:<br>';
         $displayData['bShowAscending'] = true;

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                   .' | '.anchor('creports/custom_directory/view/'.$glUserID, 'Custom Report Directory', 'class="breadcrumb"')
                                   .' | '.anchor('creports/custom_directory/viewRec/'.$lReportID, 'Custom Report: '.$report->strSafeName, 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').' Sort Field';

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'creports/search_sort_term_add_edit_view.php';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $bAscending = @$_POST['rdoAscend']=='true';
         redirect('creports/sort_terms/orderTermSelected/'.$lReportID
                           .'/'.$_POST['rdoField']
                           .'/'.($bAscending ? 'true' : 'false')
                           .'/'.$lTermID
                           );
      }
   }

   function orderTermSelected($lReportID, $strField, $bAscending, $lTermID=0){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');
      verifyIDsViaType($this, CENUM_CONTEXT_CUSTOMREPORTTERM, $lTermID, true);

      $lReportID  = (integer)$lReportID;
      $lTermID    = (integer)$lTermID;
      $bAscending = $bAscending == 'true';
      $bNew       = $lTermID <= 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper ('reports/search');
      $this->load->helper ('reports/creport_util');
      $this->load->helper ('dl_util/context');
      $this->load->model  ('admin/mpermissions', 'perms');
      $this->load->model  ('personalization/muser_fields');
      $this->load->model  ('personalization/muser_schema');
      $this->load->model  ('admin/madmin_aco'); 
      $this->load->model  ('creports/mcreports', 'crpt');

         //------------------------------------------------
         // load report
         //------------------------------------------------
      $this->crpt->loadReportViaID($lReportID, false);
      $report = &$this->crpt->reports[0];

      $enumRptType = $report->enumRptType;

      $tables = $this->crpt->loadTableStructures($enumRptType);
      $this->crpt->findFieldInTables($strField, $tables, $lTableIDX, $lFieldIDX);
      $table = &$tables[$lTableIDX];

      $opts = new stdClass;
      $opts->lReportID  = $lReportID;
      $opts->bAscending = $bAscending;
      $opts->strFieldID = $strField;
      $opts->lFieldID   = $table->fields[$lFieldIDX]->lFieldID;
      $opts->lTableID   = $table->lTableID;
      $this->crpt->addSortTerm($opts);

      redirect('creports/custom_directory/viewRec/'.$lReportID);
   }
   
   function remove($lReportID, $lSortTermID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');

      $lReportID   = (integer)$lReportID;
      $lSortTermID = (integer)$lSortTermID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper ('reports/creport_util');
      $this->load->model  ('admin/madmin_aco'); 
      $this->load->model  ('creports/mcreports', 'crpt');
      
      if (!$this->crpt->bSortTermGoesWithReport($lReportID, $lSortTermID)){
         vid_bTestFail($this, false, 'Sort Order', $lSortTermID);
         return;
      }
      
      $this->crpt->deleteSortOrderTerm($lSortTermID);
      redirect('creports/custom_directory/viewRec/'.$lReportID);
   }


}