<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class mgr_perf_rpt extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function rptOpts($enumRptType){
   //------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------
      global $glUserID;

      $this->load->helper('dl_util/permissions');    // in autoload
      if (!bAllowAccess('management')) return('');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['enumRptType'] = $enumRptType;
      $displayData['bViaUser']    = $bViaUser = $enumRptType=='byStaff';

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper ('dl_util/web_layout');
      $this->load->library('generic_form');
      $this->load->model  ('admin/madmin_aco',    'clsACO');
      $this->load->model  ('admin/morganization', 'clsChapter');
      $this->load->model  ('groups/mgroups', 'groups');
      $this->load->helper ('groups/groups');
      $this->load->helper ('dl_util/time_date');
      if ($bViaUser){
         $this->load->model  ('admin/mpermissions', 'perms');
         $this->load->model  ('admin/muser_accts',  'cusers');
         $this->load->helper ('aayhf/aayhf_staff');
      }

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
      if (!$bViaUser){
         $this->form_validation->set_rules('ddlStaffGroup', 'Staff Group', 'callback_verifyStaffGroup');
         $this->form_validation->set_rules('ddlSort',       'Sort',        'trim|required');
      }

      if ($bViaUser){
         $this->loadUserRecs($lNumUsers, $users);
         aayhfStaff\buildUserTable($users, $displayData['usersBEACON']);
         aayhfStaff\buildUserTable($users, $displayData['usersJJMI']);
         aayhfStaff\buildUserTable($users, $displayData['usersSHIFTA']);
         aayhfStaff\buildUserTable($users, $displayData['usersOperations']);
         aayhfStaff\buildUserTable($users, $displayData['usersManagement']);
         aayhfStaff\buildUserTable($users, $displayData['usersPrograms']);
      }

      if ($this->form_validation->run() == FALSE){
         $displayData['frmLink'] = 'staff/mgr_perf_rpt/rptOpts/'.$enumRptType;
         $this->load->library('generic_form');
         $displayData['viewOpts']->blockLabel = 'Consolidated Status Reports / '
                           .($bViaUser ? 'by Staff Member' : 'by Project Group');

         if (validation_errors()==''){
            $dummy = array();
            $this->setInitialRptVal($displayData['viewOpts'], $displayData['formData']);
            if ($bViaUser){
               aayhfStaff\defaultCheckViaStaffGroup($displayData['usersBEACON'],     'BEACON');
               aayhfStaff\defaultCheckViaStaffGroup($displayData['usersJJMI'],       'JJMI');
               aayhfStaff\defaultCheckViaStaffGroup($displayData['usersSHIFTA'],     'SHIFT Ascension');
               aayhfStaff\defaultCheckViaStaffGroup($displayData['usersOperations'], 'Operations');
               aayhfStaff\defaultCheckViaStaffGroup($displayData['usersManagement'], 'Management');
               aayhfStaff\defaultCheckViaStaffGroup($displayData['usersPrograms'],   'Programs');

               $displayData['bRadioBeacon']   =
               $displayData['bRadioJJMI']     =
               $displayData['bRadioShiftA']   =
               $displayData['bRadioManage']   =
               $displayData['bRadioOps']      = false;
               $displayData['bRadioPrograms'] = true;
            }else {
               $displayData['strStaffGroupDDL'] = $this->groups->strDDLActiveGroupEntries('ddlStaffGroup', CENUM_CONTEXT_STAFF, $dummy, false, true);
               $this->setInitialRptVal($displayData['viewOpts'], $displayData['formData']);
               $displayData['strReportSortDDL'] = $this->strSortDDL('staff');
            }
         }else {
            setOnFormError($displayData);
            $this->setErrRptVal($displayData['viewOpts'], $displayData['formData']);

            if ($bViaUser){
               $this->loadViaUserSettings($displayData);
            }else {
               if (!isset($_POST['ddlStaffGroup'])) $_POST['ddlStaffGroup'] = array();

               $displayData['strStaffGroupDDL'] = $this->groups->strDDLActiveGroupEntries(
                                  'ddlStaffGroup', CENUM_CONTEXT_STAFF, $_POST['ddlStaffGroup'], false, true);
               $displayData['strReportSortDDL'] = $this->strSortDDL($_POST['ddlSort']);
            }
               // time frame support
            $this->tf_setTFOptsOnFormError($displayData['viewOpts']);
         }
            // time frame support
         $displayData['dateRanges'] = $strRange = tf_strDateRangeMenu($displayData['viewOpts']);

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('aayhf/main/aayhfMenu', 'AAYHF', 'class="breadcrumb"')
                                   .' | Consolidated Status Reports / '.($bViaUser ? 'Via Staff' : 'Via Project');

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         if ($bViaUser){
            $displayData['mainTemplate']   = 'aayhf/aayhf_reports/stat_rpt_via_staff_opts_view';
         }else {
            $displayData['mainTemplate']   = 'aayhf/aayhf_reports/stat_rpt_opts_view';
         }
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         if ($bViaUser){
            $reportID = $this->strLoadPostStaffViaUserStatusRpt($displayData, $displayData['viewOpts'], CENUM_REPORTNAME_AAYHF_STATCONSOL_STAFF);
         }else {
            $reportID = $this->strLoadPostStaffStatusRpt($displayData['viewOpts'], CENUM_REPORTNAME_AAYHF_STATCONSOL);
         }
         redirect('aayhf/aayhf_reports/aayhf_reports/run/'.$reportID);
      }
   }

   function strLoadPostStaffViaUserStatusRpt(&$displayData, &$viewOpts, $enumRptType){
   //---------------------------------------------------------------------
   // return reportID
   //---------------------------------------------------------------------
      global $gbDateFormatUS;
      $this->load->model('reports/mreports',    'clsReports');

      $this->loadViaUserSettings($displayData);
      if ($displayData['bRadioBeacon']){         $userArray = &$displayData['usersBEACON'];
      }elseif ($displayData['bRadioJJMI']    ){  $userArray = &$displayData['usersJJMI'];
      }elseif ($displayData['bRadioShiftA']  ){  $userArray = &$displayData['usersSHIFTA'];
      }elseif ($displayData['bRadioManage']  ){  $userArray = &$displayData['usersManagement'];
      }elseif ($displayData['bRadioOps']     ){  $userArray = &$displayData['usersOperations'];
      }elseif ($displayData['bRadioPrograms']){  $userArray = &$displayData['usersPrograms'];
      }
      
      $userIDs = array();
      foreach ($userArray as $ua){
         if ($ua->bChecked) $userIDs[] = $ua->lUserID;
      }

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => $enumRptType,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'userIDs'        => arrayCopy($userIDs),
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => false);
      $reportAttributes['viewFile'] = 'pre_generic_rpt_view';
      if ($viewOpts->bShowTimeFrame){
         tf_getDateRanges($viewOpts, $formDates);
         $reportAttributes['dteStart']     = $formDates->dteStart;
         $reportAttributes['dteEnd']       = $formDates->dteEnd;
         $reportAttributes['strDateRange'] = $formDates->strDateRange;
         $reportAttributes['strBetween']   = $formDates->strBetween;
      }

      $this->clsReports->createReportSessionEntry($reportAttributes);
      return($this->clsReports->sRpt->reportID);
   }

   function strLoadPostStaffStatusRpt(&$viewOpts, $enumRptType){
   //---------------------------------------------------------------------
   // return reportID
   //---------------------------------------------------------------------
      global $gbDateFormatUS;
      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => $enumRptType,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStaffGroup'    => $_POST['ddlStaffGroup'],
                             'strSort'        => trim($_POST['ddlSort']),
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => false);
      $reportAttributes['viewFile'] = 'pre_generic_rpt_view';
      if ($viewOpts->bShowTimeFrame){
         tf_getDateRanges($viewOpts, $formDates);
         $reportAttributes['dteStart']     = $formDates->dteStart;
         $reportAttributes['dteEnd']       = $formDates->dteEnd;
         $reportAttributes['strDateRange'] = $formDates->strDateRange;
         $reportAttributes['strBetween']   = $formDates->strBetween;
      }

      $this->clsReports->createReportSessionEntry($reportAttributes);
      return($this->clsReports->sRpt->reportID);
   }

   private function strSortDDL($strMatch){
      return(
         '<select name="ddlSort">
             <option value="date" ' .($strMatch == 'date'  ? 'selected' : '').'>Date</option>
             <option value="staff" '.($strMatch == 'staff' ? 'selected' : '').'>Staff Member</option>
          </select>');
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
      $viewOpts->blockLabel          = 'Consolidated Status Report';
   }

      /*-----------------------------
         verification routines
      -----------------------------*/
   function verifyStaffGroup($strGroupID){
      if (!isset($strGroupID)){
         $this->form_validation->set_message('verifyStaffGroup', 'Please select one or more staff groups.');
         return(false);
      }else {
         return(true);
      }
   }

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

   private function setErrRptVal(&$viewOpts, &$formData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS, $gdteNow;

      $formData = new stdClass;
      if ($viewOpts->bShowSortBy){
         $formData->enumSort = set_value('rdoSort');
      }
      if ($viewOpts->bShowTimeFrame){
         $formData->txtSDate = set_value('txtSDate');
         $formData->txtEDate = set_value('txtEDate');
      }
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

   private function setValidationRules(&$viewOpts){
   //---------------------------------------------------------------------
   // validation rules
   //---------------------------------------------------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');

      if ($viewOpts->bShowTimeFrame){
         $this->tf_setValidationTFRules($viewOpts);
      }
      if ($viewOpts->bShowSortBy){
         $this->form_validation->set_rules('rdoSort', 'Sort');
      }
      if ($viewOpts->bShowAggregateDonor){
         $this->form_validation->set_rules('rdoAggDonor', 'Grouping');
      }
   }

   function tf_setValidationTFRules($opts){
      $strRadioFN = $opts->strRadioFN;
      $this->form_validation->set_rules($strRadioFN,       'Report Range Option', 'trim|required');
      $this->form_validation->set_rules($opts->strSDateFN, 'Report start date',   'trim|required|callback_rptStart['.$strRadioFN.']');
      $this->form_validation->set_rules($opts->strEDateFN, 'Report end  date',    'trim|required'
                                                 .'|callback_rptEnd['.$strRadioFN.']|callback_rptCBH['.$strRadioFN.']');
   }

   private function setInitialRptVal(&$viewOpts, &$formData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS, $gdteNow;

      $formData = new stdClass;

      if ($viewOpts->bShowTimeFrame){
         $dteStart                = strtotime('1/1/2014');
         $dteEnd                  = $gdteNow;
         if ($gbDateFormatUS){
            $viewOpts->txtSDate = date('m/d/Y', $dteStart);
            $viewOpts->txtEDate = date('m/d/Y', $dteEnd);
         }else {
            $viewOpts->txtSDate = date('d/m/Y', $dteStart);
            $viewOpts->txtEDate = date('d/m/Y', $dteEnd);
         }
      }

         // set default for "This Week"
      $viewOpts->bDatePreDefined = true;
      $viewOpts->bDateRange      =
      $viewOpts->bNoDateRange    = false;
      $viewOpts->strDatePredefinedID = CI_DATERANGE_THIS_WEEK;

      if ($viewOpts->bShowYear){
         $formData->lYearSel = (integer)date('Y', $gdteNow);
      }
   }

   function loadUserRecs(&$lNumUsers, &$users){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $users = array();
         // Goodbye, Sally
      $this->cusers->sqlWhere = ' AND NOT us_bInactive AND NOT (us_lKeyID IN (47, 45, 7, 36)) ';
      $this->cusers->loadUserRecords();
      $lNumUsers = $this->cusers->lNumRecs;
      if ($lNumUsers > 0){
         $idx = 0;
         foreach ($this->cusers->userRec as $urec){
            $users[$idx] = new stdClass;
            $u = &$users[$idx];
            $u->lUserID       = $urec->us_lKeyID;
            $u->strSafeNameLF = $urec->strSafeNameLF;
            ++$idx;
         }
      }
   }

   function loadViaUserSettings(&$displayData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // create index array by user ID; all user group arrays contain
         // all the user IDs, so we'll just pick SHIFTA for grins
      $userIDAssoc = array();
      $idx = 0; $lNumUserIDs = count($displayData['usersSHIFTA']);
      for ($idx=0; $idx < $lNumUserIDs; ++$idx){
         $userIDAssoc[$displayData['usersSHIFTA'][$idx]->lUserID] = $idx;
      }

      $strStaffGroup = $_POST['rdoStaffGroup'];
      $displayData['bRadioBeacon']   = $strStaffGroup=='BEACON';
      $displayData['bRadioJJMI']     = $strStaffGroup=='JJMI';
      $displayData['bRadioShiftA']   = $strStaffGroup=='SHIFTA';
      $displayData['bRadioManage']   = $strStaffGroup=='MANAGEMENT';
      $displayData['bRadioOps']      = $strStaffGroup=='OPS';
      $displayData['bRadioPrograms'] = $strStaffGroup=='PROGRAMS';

      $this->clearViaUserCheckStatus($displayData['usersBEACON']);
      $this->clearViaUserCheckStatus($displayData['usersJJMI']);
      $this->clearViaUserCheckStatus($displayData['usersSHIFTA']);
      $this->clearViaUserCheckStatus($displayData['usersOperations']);
      $this->clearViaUserCheckStatus($displayData['usersManagement']);
      $this->clearViaUserCheckStatus($displayData['usersPrograms']);

      foreach ($_POST as $strVar=>$strVal){
         if (substr($strVar, 0, 9)=='chkUsers_'){
            $chkInfo = explode('_', $strVar);
            $lUserID = (int)$chkInfo[2];
            switch ($chkInfo[1]){
               case 'BEACON':     $displayData['usersBEACON']    [$userIDAssoc[$lUserID]]->bChecked = true; break;
               case 'JJMI':       $displayData['usersJJMI']      [$userIDAssoc[$lUserID]]->bChecked = true; break;
               case 'SHIFTA':     $displayData['usersSHIFTA']    [$userIDAssoc[$lUserID]]->bChecked = true; break;
               case 'MANAGEMENT': $displayData['usersManagement'][$userIDAssoc[$lUserID]]->bChecked = true; break;
               case 'OPS':        $displayData['usersOperations'][$userIDAssoc[$lUserID]]->bChecked = true; break;
               case 'PROGRAMS':   $displayData['usersPrograms']  [$userIDAssoc[$lUserID]]->bChecked = true; break;
               default:
                  screamForHelp($chkInfo[1].': program not found<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }
         }
      }
   }

   function clearViaUserCheckStatus(&$users){
      foreach ($users as $u){
         $u->bChecked = false;
      }
   }


   function pdf($reportID, $enumType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         //------------------------------------------------
         // libraries/models/helpers
         //------------------------------------------------
      $this->load->model('reports/mreports',    'clsReports');
      $this->load->model('staff/mstaff_status', 'cstat');
      $this->load->model('staff/mstaff_pdf',    'cstpdf');
//      $this->load->model('groups/mgroups',      'groups');
   
         // load the report
      $this->clsReports->loadReportSessionEntry($reportID, $sRpt);
      
      if ($enumType=='byProj'){
         $this->pdf_byProject($reportID, $sRpt);
      }else {
         $this->pdf_byUserID($reportID, $sRpt);
      }
   }
   
   function pdf_byUserID($reportID, $sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         //------------------------------------------------
         // libraries/models/helpers
         //------------------------------------------------
//      $this->load->model('reports/mreports',    'clsReports');
//      $this->load->model('staff/mstaff_status', 'cstat');
//      $this->load->model('staff/mstaff_pdf',    'cstpdf');
//      $this->load->model('groups/mgroups',      'groups');
      $this->load->model('admin/mpermissions',  'perms');
      $this->load->model('admin/muser_accts',   'cusers');
      
      $this->cstpdf->createStaffRptViaUserIDPDF($sRpt);
   }
   
   function pdf_byProject($reportID, $sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         //------------------------------------------------
         // libraries/models/helpers
         //------------------------------------------------
//      $this->load->model('reports/mreports',    'clsReports');
//      $this->load->model('staff/mstaff_status', 'cstat');
//      $this->load->model('staff/mstaff_pdf',    'cstpdf');
      $this->load->model('groups/mgroups',      'groups');

//         // load the report
//      $this->clsReports->loadReportSessionEntry($reportID, $sRpt);

         // load staff group info
      $this->groups->loadGroupInfo($sRpt->lStaffGroup);
      $this->cstpdf->createStaffRptPDF($sRpt);
   }


   function maagOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gUserPerms, $glUserID;

      if (!bAllowAccess('management')) return('');

      $displayData = array();
      $displayData['js'] = '';

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('js/jq_month_picker');
      $this->load->model ('admin/mpermissions',           'perms');

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtMonth', 'Report Month', 'trim|required|callback_reportMonth');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;

         $this->load->library('generic_form');
         if (validation_errors()==''){
            $displayData['txtMonth']  = date('m/Y', $gdteNow);
         }else {
            setOnFormError($displayData);
            $displayData['txtMonth']  = set_value('txtMonth');
         }

            //------------------------------------------------
            // breadcrumbs / page setup
            //------------------------------------------------
         $displayData['js'] .= strMonthPicker(true);

         $displayData['mainTemplate'] = 'staff/mgr_perf_maag_opts_view';
         $displayData['pageTitle']    =
                                           anchor('aayhf/main/aayhfMenu',              'AAYHF',         'class="breadcrumb"')
                                    .' | Status Reports: Month-at-a-Glance';

         $displayData['title']        = CS_PROGNAME.' | Status Reports';
         $displayData['nav']          = $this->mnav_brain_jar->navData();

         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
            // show status month-at-a-glance report
         $strDate = trim($_POST['txtMonth']);
         $monYr = explode('/', $strDate);
         redirect('staff/mgr_performance/maagRpt/'.(int)$monYr[0].'/'.(int)$monYr[1]);
      }

   }

   function reportMonth($strMonth){
      if (bValidPickerMonth($strMonth, $strErr)){
         return(true);
      }else {
         $this->form_validation->set_message('reportMonth', $strErr);
         return(false);
      }
   }

}



