<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cprog_enrollees extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function opts(){
   //--------------------------------------------------------------------
   //
   //--------------------------------------------------------------------
      global $glUserID;

      $displayData = array();
      $displayData['js'] = '';

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper ('dl_util/web_layout');
      $this->load->library('generic_form');
      $this->load->model ('personalization/muser_fields',     'clsUF');
      $this->load->model ('admin/mpermissions',               'perms');
      $this->load->model ('client_features/mcprograms',       'cprograms');
      $this->load->helper ('dl_util/time_date');

         // time frame support
      $this->load->helper ('reports/date_range_def');
      $this->load->helper ('reports/date_range');
      $this->load->library('js_build/java_joe_radio');
      $this->load->model  ('util/mserial_objects', 'cSO');
      $displayData['js'] .= $this->java_joe_radio->insertJavaJoeRadio().$this->java_joe_radio->insertSetDateRadio();
      tf_initDateRangeMenu($displayData['viewOpts']);

      $this->initViewOpts($displayData['viewOpts']);

         // validation rules
      $this->setValidationRules($displayData['viewOpts']);

      if ($this->form_validation->run() == FALSE){

            //--------------------------
            // load the client programs
            //--------------------------
         $this->cprograms->loadClientPrograms(false);
         $lNumCProgs = $this->cprograms->lNumCProgs;
         $displayData['cprogs'] = &$this->cprograms->cprogs;
         $displayData['lNumCProgs'] = 0;
         $this->perms->loadUserAcctInfo($glUserID, $acctAccess);

         if ($lNumCProgs > 0){
            foreach ($this->cprograms->cprogs as $cprog){
               $cprog->bShowCProgLink = $this->perms->bDoesUserHaveAccess(
                                             $acctAccess, $cprog->lNumPerms, $cprog->perms);
               if ($cprog->bShowCProgLink){
                  ++$displayData['lNumCProgs'];
               }
            }
         }

         $displayData['frmLink'] = 'cprograms/cprog_enrollees/opts';
         $this->load->library('generic_form');
         $displayData['viewOpts']->blockLabel = 'Enrollees in Client Programs';

         if (validation_errors()==''){
            $this->setInitialRptVal($displayData['viewOpts'], $displayData['formData']);
         }else {
            setOnFormError($displayData);
            $this->setErrRptVal($displayData['viewOpts'], $displayData['formData']);

               // time frame support
            $this->setCPEOptsOnFormError($displayData['viewOpts']);
         }
            // time frame support
         $displayData['dateRanges'] = $strRange = tf_strDateRangeMenu($displayData['viewOpts']);

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                   .' | Client Programs: Enrollees';

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'cprograms/cprograms_enrollee_rpt_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $reportID = $this->strCProgEnrolleeRpt($displayData['viewOpts'], CENUM_REPORTNAME_CPROG_ENROLLEES);
         redirect('cprograms/cprog_enrollees/run/'.$reportID);
      }
   }

   function strCProgEnrolleeRpt(&$viewOpts, $enumRptType){
   //---------------------------------------------------------------------
   // return reportID
   //---------------------------------------------------------------------
      global $gbDateFormatUS;
      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => $enumRptType,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'cProgIDs'       => arrayCopy($_POST['chkCProgs']),
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => false);
      $reportAttributes['viewFile'] = 'pre_generic_rpt_view';

      tf_getDateRanges($viewOpts, $formDates);
      $reportAttributes['dteStart']     = $formDates->dteStart;
      $reportAttributes['dteEnd']       = $formDates->dteEnd;
      $reportAttributes['strDateRange'] = $formDates->strDateRange;
      $reportAttributes['strBetween']   = $formDates->strBetween;

      $this->clsReports->createReportSessionEntry($reportAttributes);
      return($this->clsReports->sRpt->reportID);
   }

   private function setValidationRules(&$viewOpts){
   //---------------------------------------------------------------------
   // validation rules
   //---------------------------------------------------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('chkCProgs', 'Client Programs', 'callback_verifyCProgSel');

      if ($viewOpts->bShowTimeFrame){
         $this->setValidationCPERules($viewOpts);
      }
   }

   private function setInitialRptVal(&$viewOpts, &$formData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS, $gdteNow;

      $formData = new stdClass;

      if ($viewOpts->bShowTimeFrame){
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

      $formData->cProgSelectedIDs = array();
   }

   function setValidationCPERules($opts){
      $strRadioFN = $opts->strRadioFN;
      $this->form_validation->set_rules($strRadioFN,       'Report Range Option', 'trim|required');
      $this->form_validation->set_rules($opts->strSDateFN, 'Report start date',   'trim|required|callback_rptStart['.$strRadioFN.']');
      $this->form_validation->set_rules($opts->strEDateFN, 'Report end  date',    'trim|required'
                                                 .'|callback_rptEnd['.$strRadioFN.']|callback_rptCBH['.$strRadioFN.']');
   }

   private function initViewOpts(&$viewOpts){
      $viewOpts->bShowTimeFrame      = true;
      $viewOpts->bShowSortBy         =
      $viewOpts->bShowIncludes       =
      $viewOpts->bShowMinAmnt        =
      $viewOpts->bShowMaxAmnt        =
      $viewOpts->bShowACO            =
      $viewOpts->bShowAggregateDonor =
      $viewOpts->bShowACOAll         =
      $viewOpts->bShowAcct           =
      $viewOpts->bShowCamp           =
      $viewOpts->bShowYear           = false;
      $viewOpts->blockLabel          = 'Client Program Enrollees';
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
         $this->form_validation->set_message('rptCBH', 'The report end date is before the start date!');
         return(false);
      }
   }
   function verifyCProgSel($dummy){
      if (isset($_POST['chkCProgs'])){
         return(true);
      }else {
         $this->form_validation->set_message('verifyCProgSel', 'Please select one or more client programs.');
         return(false);
      }
   }

   private function setErrRptVal(&$viewOpts, &$formData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS, $gdteNow;

      $formData = new stdClass;
      if ($viewOpts->bShowTimeFrame){
         $formData->txtSDate = set_value('txtSDate');
         $formData->txtEDate = set_value('txtEDate');
      }

      $formData->cProgSelectedIDs = array();
      if (isset($_POST['chkCProgs'])){
         foreach ($_POST['chkCProgs'] as $lCPID){
            $formData->cProgSelectedIDs[] = (int)$lCPID;
         }
      }
   }

   function setCPEOptsOnFormError(&$opts){
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


   function run($reportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $displayData['js'] = '';

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $this->load->model ('admin/mpermissions',                  'perms');
      $this->load->model ('personalization/muser_fields',        'clsUF');
      $this->load->model ('personalization/muser_schema',        'cUFSchema');
      $this->load->model ('client_features/mcprograms',          'cprograms');
      $this->load->model ('client_features/mcprog_enrollee_rpt', 'cperpt');
      $this->load->helper('clients/link_client_features');
      
      $this->load->helper('dl_util/web_layout');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

      if (!isset($_SESSION[CS_NAMESPACE.'Reports'][$reportID])){
         $this->session->set_flashdata('error', 'The report you requested is no longer available. Please run the report again.');
         redirect_Reports();
      }
      
         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $sRpt = $_SESSION[CS_NAMESPACE.'Reports'][$reportID];
      $sRpt->timeStamp = time();
      $sRpt->strCProgIn = ' IN ('.implode(',', $sRpt->cProgIDs).') ';
      $displayData['dateRange'] = $sRpt->strDateRange;
      
      $this->cperpt->loadReportEnrolless($sRpt);
      $displayData['lNumEnrollees'] = $this->cperpt->lNumEnrollees;
      $displayData['enrollees']     = &$this->cperpt->enrollees;
      $displayData['cprogs']        = &$this->cperpt->cprogs;


         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | '.anchor('cprograms/cprog_enrollees/opts', ' Client Programs: Enrollees', 'class="breadcrumb"')
                                .' | Run';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'cprograms/cprograms_enrollee_rpt_run_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

}



