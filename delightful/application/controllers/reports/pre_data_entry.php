<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_data_entry extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function daOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gbDateFormatUS, $glChapterID;

      $displayData = array();
      $displayData['js'] = '';

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
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

      $this->initViewOpts($displayData['viewOpts']);

         // validation rules
      $this->setValidationRules($displayData['viewOpts']);

      if ($this->form_validation->run() == FALSE){
         $displayData['frmLink'] = 'reports/pre_data_entry/daOpts';
         $this->load->library('generic_form');
         $displayData['viewOpts']->blockLabel = 'Data Entry Log';

         if (validation_errors()==''){
            $displayData['strRdoSrc']   = 'client';
            $displayData['strRdoGroup'] = 'individual';
            $this->setInitialRptVal($displayData['viewOpts'], $displayData['formData']);
         }else {
            setOnFormError($displayData);
            $this->setErrRptVal($displayData['viewOpts'], $displayData['formData']);
            $displayData['strRdoSrc']   = set_value('rdoSrc');
            $displayData['strRdoGroup'] = set_value('rdoGroup');

               // time frame support
            $this->de_setDEOptsOnFormError($displayData['viewOpts']);
         }
            // time frame support
         $displayData['dateRanges'] = $strRange = tf_strDateRangeMenu($displayData['viewOpts']);

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                   .' | Data Entry Log';

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'reports/pre_data_entry_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $reportID = $this->strDataEntryLogRpt($displayData['viewOpts'], CENUM_REPORTNAME_DATAENTRYLOG);
         redirect('reports/pre_data_entry/run/'.$reportID);
      }
   }

   function de_setValidationDERules($opts){
      $strRadioFN = $opts->strRadioFN;
      $this->form_validation->set_rules($strRadioFN,       'Report Range Option', 'trim|required');
      $this->form_validation->set_rules($opts->strSDateFN, 'Report start date',   'trim|required|callback_rptStart['.$strRadioFN.']');
      $this->form_validation->set_rules($opts->strEDateFN, 'Report end  date',    'trim|required'
                                                 .'|callback_rptEnd['.$strRadioFN.']|callback_rptCBH['.$strRadioFN.']');
   }

   function de_setDEOptsOnFormError(&$opts){
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

   private function setValidationRules(&$viewOpts){
   //---------------------------------------------------------------------
   // validation rules
   //---------------------------------------------------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('rdoSrc',   'Data entry source', 'trim|required');
      $this->form_validation->set_rules('rdoGroup', 'Group', 'trim|required');

      if ($viewOpts->bShowTimeFrame){
         $this->de_setValidationDERules($viewOpts);
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
   }

   private function initViewOpts(&$viewOpts){
      $viewOpts->bShowTimeFrame = true;
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
      $viewOpts->blockLabel          = 'Data Entry Log';
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
   function rptCBH($strEDate){
   // CBH: cart before horse
      if ($_REQUEST[$strRadioFN] != 'User') return(true);
      if (bVerifyCartBeforeHorse(trim($_POST['txtSDate']), $strEDate)){
         return(true);
      }else {
         $this->form_validation->set_message('rptCBH', 'The report end date is before the start date!');
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
   }

   function strDataEntryLogRpt(&$viewOpts, $enumRptType){
   //---------------------------------------------------------------------
   // return reportID
   //---------------------------------------------------------------------
      global $gbDateFormatUS;
      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => $enumRptType,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'enumSource'     => @$_POST['rdoSrc'],
                             'enumGroup'      => @$_POST['rdoGroup'],
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

   function run($reportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $displayData['js'] = '';

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $this->load->model ('reports/mreports', 'clsReports');
      $this->load->model ('groups/mgroups', 'groups');
      $this->load->helper('groups/groups');
      $this->load->helper('reports/report_util');

      if (!isset($_SESSION[CS_NAMESPACE.'Reports'][$reportID])){
         $this->session->set_flashdata('error', 'The report you requested is no longer available. Please run the report again.');
         redirect_Reports();
      }

      $sRpt = $_SESSION[CS_NAMESPACE.'Reports'][$reportID];
      $sRpt->timeStamp = time();
      $displayData['dateRange'] = $sRpt->strDateRange;
      $displayData['enumSource'] = $enumSource = $sRpt->enumSource;
      if ($enumSource != 'client') $sRpt->enumGroup = 'individual';
      $displayData['enumGroup']  = $enumGroup  = $sRpt->enumGroup;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
      $this->load->library('generic_form');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/web_layout');
      $this->load->model('reports/mdata_entry_log', 'cde');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      
         // client programs
      $this->load->model('admin/mpermissions',           'perms');
      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->model('personalization/muser_schema', 'cUFSchema');
      $this->load->model('client_features/mcprograms',   'cprograms');      

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $this->cde->loadDataEntryStats($sRpt);
      
      switch ($enumSource){
         case 'client':
            $displayData['strRptType'] = 'Client record entry';
            $displayData['strTotCnt']  = 'Tot. Client recs during timeframe';
            $displayData['lTotRecCnt'] = $this->cde->lTotClientCnt;
            break;
         case 'enroll':
            $displayData['strRptType'] = 'Client Programs: Enrollment Records';
            $displayData['strTotCnt']  = 'Tot. Enroll. recs during timeframe';
            $displayData['lTotRecCnt'] = $this->cde->lTotEnrollAttendCnt;
            break;
         case 'attend':
            $displayData['strRptType'] = 'Client Programs: Attendance Records';
            $displayData['strTotCnt']  = 'Tot. Attend. recs during timeframe';
            $displayData['lTotRecCnt'] = $this->cde->lTotEnrollAttendCnt;
            break;
         default:
            screamForHelp($enumSource.': invalid report source<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

      switch ($enumGroup){
         case 'individual':
            $displayData['strRptGroup'] = 'Grouped by Individual Users';
            switch ($enumSource){
               case 'client':
                  $displayData['lNumEntries'] = $this->cde->lNumEntries;
                  $displayData['entries']     = &$this->cde->entries;
                  break;
               case 'attend':
               case 'enroll':
                  $displayData['lNumEntries'] = $this->cde->lTotEnrollAttendCnt;
                  $displayData['cprogs']      = &$this->cde->ccprogs->cprogs;
                  break;
               default:
                  screamForHelp($enumSource.': invalid report source<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }
            break;
         case 'staffGroup':
            $displayData['strRptGroup']     = 'By Staff Group';
            switch ($enumSource){
               case 'client':            
                  $displayData['lNumStaffGroups'] = $this->cde->lNumStaffGroups;
                  $displayData['staffGroups']     = &$this->cde->staffGroups;
                  break;
               default:
                  screamForHelp($enumSource.': invalid report source<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }
            break;
         default:
            screamForHelp($enumGroup.': invalid report group<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | '.anchor('reports/pre_data_entry/daOpts', 'Data Entry Log',
                                               'class="breadcrumb"')
                                .' | View Report';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_data_entry_log_rpt_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }


}