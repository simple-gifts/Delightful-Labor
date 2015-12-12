<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_client_pre_post extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function opts(){
   //------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------
      global $glUserID, $gdteNow;
      if (!bTestForURLHack('showClients')) return;

      $displayData = array();
      $displayData['js'] = '';

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $this->load->model  ('client_features/mcpre_post_tests', 'cpptests');
      $this->load->model  ('admin/mpermissions', 'perms');
      $this->load->helper ('dl_util/web_layout');
      $this->load->library('generic_form');
      $this->load->helper ('dl_util/time_date');

         // time frame support
      $this->load->helper ('reports/date_range_def');
      $this->load->helper ('reports/date_range');
      $this->load->library('js_build/java_joe_radio');
      $this->load->model  ('util/mserial_objects', 'cSO');
      $displayData['js'] .= $this->java_joe_radio->insertJavaJoeRadio().$this->java_joe_radio->insertSetDateRadio();
      tf_initDateRangeMenu($displayData['viewOpts']);

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->tf_setValidationTFRules($displayData['viewOpts']);
      $this->form_validation->set_rules('ddlTests', 'List of tests', 'trim|required|callback_verifyTestDDL');

      if ($this->form_validation->run() == FALSE){
         $displayData['frmLink'] = 'reports/pre_client_pre_post/opts';
         $this->load->library('generic_form');
         $displayData['viewOpts']->blockLabel = 'Client Pre/Post Test Results';

            // load available tests
         $this->cpptests->loadPPTestsAvailToClient();
         $displayData['lNumPPTests'] = $lNumPPTests = $this->cpptests->lNumPPTests;

         if (validation_errors()==''){
            $this->setInitialRptVal($displayData['viewOpts'], $displayData['formData']);
            if ($lNumPPTests > 0){
               $displayData['formData']->strPrePostList = $this->cpptests->strBuildTestDDL('ddlTests', true, -1);
            }
         }else {
            setOnFormError($displayData);
            $displayData['formData'] = new stdClass;
            $displayData['formData']->txtSDate = set_value('txtSDate');
            $displayData['formData']->txtEDate = set_value('txtEDate');
            if ($lNumPPTests > 0){
               $displayData['formData']->strPrePostList =
                        $this->cpptests->strBuildTestDDL('ddlTests', true, (int)set_value('ddlTests'));
            }

               // time frame support
            $this->tf_setTFOptsOnFormError($displayData['viewOpts']);
         }
            // time frame support
         $displayData['dateRanges'] = $strRange = tf_strDateRangeMenu($displayData['viewOpts']);

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                   .' | Client Pre/Post Tests';

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'reports/pre_client_pre_post_test';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $reportID = $this->lLoadPrePostTestRpt($displayData['viewOpts'], CENUM_REPORTNAME_CLIENT_PREPOST);
         redirect('reports/reports/run/'.$reportID);
      }
   }

   function lLoadPrePostTestRpt(&$viewOpts, $enumRptType){
   //---------------------------------------------------------------------
   // return reportID
   //---------------------------------------------------------------------
      global $gbDateFormatUS;
      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => $enumRptType,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'viewFile'       => 'pre_generic_rpt_view',   //'pre_client_pre_post_test_view',
                             'bShowRecNav'    => false);
      tf_getDateRanges($viewOpts, $formDates);
      $reportAttributes['dteStart']     = $formDates->dteStart;
      $reportAttributes['dteEnd']       = $formDates->dteEnd;
      $reportAttributes['strDateRange'] = $formDates->strDateRange;
      $reportAttributes['strBetween']   = $formDates->strBetween;
      $reportAttributes['lPPTestID']    = (int)$_REQUEST['ddlTests'];

      $this->clsReports->createReportSessionEntry($reportAttributes);
      return($this->clsReports->sRpt->reportID);
   }

   function tf_setValidationTFRules($opts){
      $strRadioFN = $opts->strRadioFN;
      $this->form_validation->set_rules($strRadioFN,       'Report Range Option', 'trim|required');
      $this->form_validation->set_rules($opts->strSDateFN, 'Report start date',   'trim|required|callback_rptStart['.$strRadioFN.']');
      $this->form_validation->set_rules($opts->strEDateFN, 'Report end  date',    'trim|required'
                                                 .'|callback_rptEnd['.$strRadioFN.']|callback_rptCBH['.$strRadioFN.']');
   }

   function tf_setTFOptsOnFormError(&$opts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $opts->txtSDate = set_value($opts->strSDateFN);
      $opts->txtEDate = set_value($opts->strEDateFN);

      $strRdoVal = set_value($opts->strRadioFN);
      $opts->bDatePreDefined = $strRdoVal == 'DDL';
      $opts->bDateRange      = $strRdoVal == 'User';
      $opts->bNoDateRange    = $strRdoVal == 'None';

      $opts->strDatePredefinedID = $_REQUEST[$opts->strCTRL_Prefix.'ddl_TimeFrame'];
   }

   function stripCommas(&$strAmount){
      $strAmount = str_replace (',', '', $strAmount);
      return(true);
   }

   private function setInitialRptVal(&$viewOpts, &$formData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS, $gdteNow;

      $formData = new stdClass;
      $dteStart                = strtotime('1/1/2000');
      $dteEnd                  = $gdteNow;
      if ($gbDateFormatUS){
         $formData->txtSDate = date('m/d/Y', $dteStart);
         $formData->txtEDate = date('m/d/Y', $dteEnd);
      }else {
         $formData->txtSDate = date('d/m/Y', $dteStart);
         $formData->txtEDate = date('d/m/Y', $dteEnd);
      }
   }

      /*-----------------------------
         verification routines
      -----------------------------*/
   function rptStart($strSDate, $strRadioFN){
      if ($_REQUEST[$strRadioFN] != 'User') return(true);
      if(bValidVerifyDate($strSDate)){
         return(true);
      }else{
         $this->form_validation->set_message('rptStart', 'The report start date is not valid');
         return(false);
      }
   }
   function rptEnd($strEDate, $strRadioFN){
      if ($_REQUEST[$strRadioFN] != 'User') return(true);
      if(bValidVerifyDate($strEDate)){
         return(true);
      }else{
         $this->form_validation->set_message('rptEnd', 'The report end date is not valid');
         return(false);
      }
   }
   function rptCBH($strEDate, $strRadioFN){
   // CBH: cart before horse
      if ($_REQUEST[$strRadioFN] != 'User') return(true);
      if (bVerifyCartBeforeHorse(trim($_POST['txtSDate']), $strEDate)){
         return(true);
      }else {
         $this->form_validation->set_message('rptCBH', 'The end date is before the start date!');
         return(false);
      }
   }
   function verifyTestDDL($strDDLOpt){
      $lOptID = (int)$strDDLOpt;
      if ($lOptID <= 0){
         $this->form_validation->set_message('verifyTestDDL', 'Please select a test.');
         return(false);
      }else {
         return(true);
      }
   }

   
   function dirViaTest(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $gdteNow;
      if (!bTestForURLHack('showClients')) return;

      $displayData = array();
      $displayData['js'] = '';

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $this->load->model  ('client_features/mcpre_post_tests', 'cpptests');
      $this->load->model  ('admin/mpermissions', 'perms');
      $this->load->helper ('dl_util/web_layout');
      $this->load->library('generic_form');

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('ddlTests', 'List of tests', 'trim|required|callback_verifyTestDDL');

      if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');

            // load available tests
         $this->cpptests->loadPPTestsAvailToClient();
         $displayData['lNumPPTests'] = $lNumPPTests = $this->cpptests->lNumPPTests;

         if (validation_errors()==''){
            $this->setInitialRptVal($displayData['viewOpts'], $displayData['formData']);
            if ($lNumPPTests > 0){
               $displayData['formData']->strPrePostList = $this->cpptests->strBuildTestDDL('ddlTests', true, -1);
            }
         }else {
            setOnFormError($displayData);
            $displayData['formData'] = new stdClass;
            if ($lNumPPTests > 0){
               $displayData['formData']->strPrePostList =
                        $this->cpptests->strBuildTestDDL('ddlTests', true, (int)set_value('ddlTests'));
            }
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                   .' | Client Pre/Post Test Directory';

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'reports/pre_client_pre_post_dir';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $reportID = $this->lLoadPrePostTestDirRpt(CENUM_REPORTNAME_CLIENT_PREPOSTDIR);
         redirect('reports/reports/run/'.$reportID);
      }   
   }
   
   function lLoadPrePostTestDirRpt($enumRptType){
   //---------------------------------------------------------------------
   // return reportID
   //---------------------------------------------------------------------
      global $gbDateFormatUS;
      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => $enumRptType,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'viewFile'       => 'pre_generic_rpt_view',   //'pre_client_pre_post_test_view',
                             'bShowRecNav'    => true);
      $reportAttributes['lPPTestID']    = (int)$_REQUEST['ddlTests'];

      $this->clsReports->createReportSessionEntry($reportAttributes);
      return($this->clsReports->sRpt->reportID);
   }
   
   
   
}




