<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_vol_hours extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function showOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->showOptsGeneric('hours');
   }
   
   function showOptsTFSum(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->showOptsGeneric('tfSum');
   }
   
   function showOptsGeneric($enumOptsType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;
      $displayData = array();
      $displayData['js'] = '';
      $displayData['enumOptsType'] = $enumOptsType;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('vols/mvol_event_hours', 'clsVolHours');
      $this->load->model('vols/mvol_events',     'clsVolEvents');
      $this->load->model('vols/mvol_event_dates', 'clsVolEventDates');
      $this->load->model('vols/mvol',           'clsVol');
      $this->load->model('reports/mreports',    'clsReports');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/form_verification');

         // time frame support
      $this->load->helper ('reports/date_range_def');
      $this->load->helper ('reports/date_range');
      $this->load->library('js_build/java_joe_radio');
      $this->load->model  ('util/mserial_objects', 'cSO');
      $displayData['js'] .= $this->java_joe_radio->insertJavaJoeRadio().$this->java_joe_radio->insertSetDateRadio();
      tf_initDateRangeMenu($displayData['viewOpts']);

      $this->clsVolEvents->bCurrentFuture = $this->clsVolEvents->bPastEvents = false;
      $this->clsVolEvents->bAllEvents = true;
      $this->clsVolEvents->loadEvents();
      $displayData['lNumEvents'] = $lNumEvents = $this->clsVolEvents->lNumEvents;

      if ($lNumEvents > 0){
         $displayData['lNumVols'] = $lNumVols = $this->clsVol->lNumVols('all');
      }
      
      switch ($enumOptsType){
         case 'hours':
            $displayData['viewOpts']->strFormName = 'frmVHrsRpt';
            $displayData['viewOpts']->strID       = 'vhrsRpt';
            $displayData['viewOpts']->strTitle    = 'Volunteer Hours';
            break;
            
         case 'tfSum':
            $displayData['viewOpts']->strFormName = 'frmVHrsTFSumRpt';
            $displayData['viewOpts']->strID       = 'vhrsTFSumRpt';
            $displayData['viewOpts']->strTitle    = 'Volunteer Hours Summary';
            break;
            
         default:
            screamForHelp($enumOptsType.': invalid report type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->tf_setValidationTFRules($displayData['viewOpts']);
      if ($enumOptsType=='hours'){
         $this->form_validation->set_rules('rdoSort',      'Sorting Option', 'trim|required');
      }

		if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');
         if (validation_errors()==''){
            if ($enumOptsType=='hours'){
               $displayData['bSortEvent'] = true;
               $displayData['bSortVol']   = $displayData['bSortHrs'] = false;
            }
            $dteStart = strtotime('1/1/2000');
            $dteEnd   = time();
            if ($gbDateFormatUS){
               $displayData['txtSDate'] = date('m/d/Y', $dteStart);
               $displayData['txtEDate'] = date('m/d/Y', $dteEnd);
            }else {
               $displayData['txtSDate'] = date('d/m/Y', $dteStart);
               $displayData['txtEDate'] = date('d/m/Y', $dteEnd);
            }
         }else {
            setOnFormError($displayData);
            if ($enumOptsType=='hours'){
               $displayData['bSortEvent'] = set_value('rdoSort')=='event';
               $displayData['bSortVol']   = set_value('rdoSort')=='vol';
               $displayData['bSortHrs']   = set_value('rdoSort')=='hrs';
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
                                   .' | Volunteer Hours';

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'reports/pre_vol_hours_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         tf_getDateRanges($displayData['viewOpts'], $formDates);
         
         

         $reportAttributes = array(
                                'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                                'rptDestination' => CENUM_REPORTDEST_SCREEN,
                                'lStartRec'      => 0,
                                'lRecsPerPage'   => 50,
                                'bShowRecNav'    => true,
                                'viewFile'       => 'pre_generic_rpt_view',

                                'dteStart'       => $formDates->dteStart,
                                'dteEnd'         => $formDates->dteEnd,
                                'strDateRange'   => $formDates->strDateRange,
                                'strBetween'     => $formDates->strBetween
                                );
                                
      switch ($enumOptsType){
         case 'hours':
            $reportAttributes['rptName']    = CENUM_REPORTNAME_VOLHOURS;
            $reportAttributes['bSortEvent'] = (trim($_POST['rdoSort'])=='event');
            break;
            
         case 'tfSum':
            $reportAttributes['rptName']    = CENUM_REPORTNAME_VOLHOURSTFSUM;
            $reportAttributes['tmpTable']   = 'tmpVolTFSum';
            break;
            
         default:
            screamForHelp($enumOptsType.': invalid report type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
                                

         $this->clsReports->createReportSessionEntry($reportAttributes);
         $reportID = $this->clsReports->sRpt->reportID;
         redirect('reports/reports/run/'.$reportID);
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

   function showOptsPVA(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;
      $displayData = array();
      $displayData['js'] = '';

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('reports/mreports',    'clsReports');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/time_date');

         // time frame support
      $this->load->helper ('reports/date_range_def');
      $this->load->helper ('reports/date_range');
      $this->load->library('js_build/java_joe_radio');
      $this->load->model  ('util/mserial_objects', 'cSO');
      $displayData['js'] .= $this->java_joe_radio->insertJavaJoeRadio().$this->java_joe_radio->insertSetDateRadio();
      tf_initDateRangeMenu($displayData['viewOpts']);
      $displayData['viewOpts']->bShowAggregateDonor = true;

      $displayData['viewOpts']->strFormName = 'frmVHrsPVARpt';
      $displayData['viewOpts']->strID       = 'vhrsPVARpt';

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->tf_setValidationTFRules($displayData['viewOpts']);
      $this->form_validation->set_rules('rdoSort',      'Sorting Option', 'trim|required');

		if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');
         if (validation_errors()==''){
            $dteStart = strtotime('1/1/2000');
            $dteEnd   = time();
            $displayData['bSortVol'] = true;
            $displayData['bSortPHrs']   = $displayData['bSortLHrs'] = false;
            if ($gbDateFormatUS){
               $displayData['txtSDate'] = date('m/d/Y', $dteStart);
               $displayData['txtEDate'] = date('m/d/Y', $dteEnd);
            }else {
               $displayData['txtSDate'] = date('d/m/Y', $dteStart);
               $displayData['txtEDate'] = date('d/m/Y', $dteEnd);
            }
         }else {
            setOnFormError($displayData);
            $displayData['txtSDate']   = set_value('txtSDate');
            $displayData['txtEDate']   = set_value('txtEDate');
            $displayData['bSortVol']   = set_value('rdoSort')=='vol';
            $displayData['bSortPHrs']  = set_value('rdoSort')=='phrs';
            $displayData['bSortLHrs']  = set_value('rdoSort')=='lhrs';

               // time frame support
            $this->tf_setTFOptsOnFormError($displayData['viewOpts']);
         }
            // time frame support
         $displayData['dateRanges'] = $strRange = tf_strDateRangeMenu($displayData['viewOpts']);

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                   .' | Volunteer Hours - Scheduled vs. Actual';

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'reports/pre_vol_hours_pva_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
/*
         $strSDate   = trim($_POST['txtSDate']);
         MDY_ViaUserForm($strSDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $dteStart = strtotime($lMon.'/'.$lDay.'/'.$lYear);

         $strEndDate = trim($_POST['txtEDate']);
         MDY_ViaUserForm($strEndDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $dteEnd = strtotime($lMon.'/'.$lDay.'/'.$lYear.' 23:59:59');
*/
         tf_getDateRanges($displayData['viewOpts'], $formDates);

         $reportAttributes = array(
                                'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                                'rptName'        => CENUM_REPORTNAME_VOLHRS_PVA,
                                'rptDestination' => CENUM_REPORTDEST_SCREEN,
                                'lStartRec'      => 0,
                                'lRecsPerPage'   => 50,
                                'bShowRecNav'    => true,
                                'viewFile'       => 'pre_generic_rpt_view',
                                'bSortVol'       => (trim($_POST['rdoSort'])=='vol'),
                                'bSortPHrs'      => (trim($_POST['rdoSort'])=='phrs'),
                                'bSortLHrs'      => (trim($_POST['rdoSort'])=='lhrs'),

                                'dteStart'       => $formDates->dteStart,
                                'dteEnd'         => $formDates->dteEnd,
                                'strDateRange'   => $formDates->strDateRange,
                                'strBetween'     => $formDates->strBetween
                                );

         $this->clsReports->createReportSessionEntry($reportAttributes);
         $reportID = $this->clsReports->sRpt->reportID;
         redirect('reports/reports/run/'.$reportID);
      }
   }

   function viaVolID($lVolID, $bScheduled){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $bScheduled = $bScheduled=='true';

      $this->load->model('reports/mreports',    'clsReports');

      $this->load->model('vols/mvol', 'clsVol');
      $this->load->model('people/mpeople',            'clsPeople');
//      $this->load->helper('dl_util/email_web');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

      $this->clsVol->loadVolRecsViaVolID($lVolID, true);

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_VOLHOURSVIAVID,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => true,
                             'bScheduled'     => $bScheduled,
                             'contextSummary' => $this->clsVol-> volHTMLSummary(0).'<br>',
                             'viewFile'       => 'pre_generic_rpt_view',
                             'lVolID'         => (integer)$lVolID);

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }

   function pvaViaVolID($lVolID, $dteStart, $dteEnd){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lVolID, 'volunteer ID');

      $this->load->model('vols/mvol', 'clsVol');
      $this->load->model('people/mpeople',            'clsPeople');
//      $this->load->helper('dl_util/email_web');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

      $this->clsVol->loadVolRecsViaVolID($lVolID, true);

      $this->load->model('reports/mreports',    'clsReports');
      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_VOLHRSDETAIL_PVA,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => true,
                             'viewFile'       => 'pre_generic_rpt_view',
                             'contextSummary' => $this->clsVol-> volHTMLSummary(0).'<br>',
                             'dteStart'       => $dteStart,
                             'dteEnd'         => $dteEnd,
                             'lVolID'         => (integer)$lVolID
                             );

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }

   function showOptsYear(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper ('dl_util/web_layout');
      $this->load->library('generic_form');

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Volunteer Hours by Year';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_vol_hours_year_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function yearRunY($lYear){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->yearRunCommon((int)$lYear);
   }

   function yearRun(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lYear = (int)$_POST['ddlYear'];
      $bViaVol = @$_POST['chkViaVol']=='true';
      if ($bViaVol){
         $this->volSumYrMon($lYear, null, null);
      }else {
         $this->yearRunCommon($lYear);
      }
   }

   function yearRunCommon($lYear){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_VOL_HRS_YEAR,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => false,
                             'lYear'          => $lYear,
                             'viewFile'       => 'pre_generic_rpt_view');

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }

   function viaYrMon($lYear, $lMon, $strSort='date'){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lYear = (int)$lYear;
      $lMon  = (int)$lMon;

      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_VOL_HRS_MON,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => true,
                             'strSort'        => $strSort,
                             'lYear'          => $lYear,
                             'lMon'           => $lMon,
                             'viewFile'       => 'pre_generic_rpt_view');

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }

   function volSumYrMon($lYear, $lMon=null, $lVolID=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lYear = (int)$lYear;
      if (!is_null($lMon)) $lMon  = (int)$lMon;

      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_VOL_HRS_SUM,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => true,
                             'lYear'          => (integer)$lYear,
                             'lMon'           => $lMon,
                             'lVolID'         => $lVolID,
                             'viewFile'       => 'pre_generic_rpt_view');
      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }

   function volDetYrMonVID($lYear, $lMon, $lVolID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($lYear=='null'){
         $lYear = null;
      }else {
         $lYear = (int)$lYear;
      }
      if ($lMon=='null'){
         $lMon = null;
      }else {
         $lMon = (int)$lMon;
      }
      if ($lVolID=='null'){
         $lVolID = null;
      }else {
         $lVolID = (int)$lVolID;
      }

      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_VOL_HRS_DETAIL,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => true,
                             'lYear'          => $lYear,
                             'lMon'           => $lMon,
                             'lVolID'         => $lVolID,
                             'viewFile'       => 'pre_generic_rpt_view');
      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }

}
