<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class reports extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function crunExport($lReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      set_time_limit(100);
      $this->crunGeneric($lReportID, true);
   }

   function crun($lReportID, $lStartRec=null, $lRecsPerPage=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->crunGeneric($lReportID, false, $lStartRec, $lRecsPerPage);
   }

   function crunGeneric($lReportID, $bExport, $lStartRec=null, $lRecsPerPage=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $gdteNow;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lReportID'] = $lReportID = (int)$lReportID;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper('reports/search');
      $this->load->helper('dl_util/context');
      $this->load->helper('reports/creport_util');
      $this->load->helper('creports/creport_field');
      $this->load->helper('creports/link_creports');
      $this->load->helper('creports/creport_special_ddl');
      $this->load->helper('dl_util/rs_navigate');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('creports/creport_tables');
      $this->load->helper('personalization/field_display');

      $this->load->model  ('admin/mpermissions', 'perms');
      $this->load->model ('admin/madmin_aco');
      $this->load->model ('creports/mcreports');
      $this->load->model ('creports/mcrpt_search_terms');
      $this->load->model ('creports/mcrpt_run', 'crptRun');
      $this->load->model ('personalization/muser_fields');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

         //------------------------------------------------
         // load report
         //------------------------------------------------
      $this->crptRun->loadReportViaID($lReportID, true);
      $displayData['report']    = $report = &$this->crptRun->reports[0];

      if (!$report->bUserHasReadAccess){
         vid_bTestFail($this, false, 'Custom Report', $lReportID);
         return;
      }
      if (!$this->crptRun->bVerifyUserAccessToReport($report, $lNumFails, $failTables)){
         vid_bTestFail($this, false, 'Permissions for Custom Report', $lReportID);
         return;
      }

      $report = &$this->crptRun->reports[0];

      if (!$bExport){
         $displayData['contextSummary'] = $this->crptRun->strCReportHTMLSummary();

         $sRpt = new stdClass;
         $sRpt->lReportID = $lReportID;
         $sRpt->bShowRecNav = true;
         $sRpt->lStartRec = $sRpt->lRecsPerPage = null;

         $this->setLimits($sRpt, $lStartRec, $lRecsPerPage);
      }

         // build the sql string for all records
      $this->crptRun->strBuildCReportSQL($report, '', false);


      if ($bExport){
            // life is good - this is so easy....
         $this->load->dbutil();
         $this->load->helper('download');
         $this->load->model('reports/mexports', 'clsExports');
         $strFN = 'creport_'.str_pad($report->lKeyID, 5, '0', STR_PAD_LEFT).'_'
               .date('Ymd_His', $gdteNow)
               .'.csv';
         force_download($strFN, $this->clsExports->exportCReport($this->crptRun->strSQL.';'));
      }else {
            // prep the navigation
         $displayData['lTotRecs']      = $lNumRecsTot  = $this->crptRun->lCountRecs(false, null, null);
         $displayData['lNumThisPage']  = $lNumThisPage = $this->crptRun->lCountRecs(true, $lStartRec, $lRecsPerPage);

         $displayData['strNavLinkExtra'] = '';

         $displayData['lStartRec']       = $lStartRec = (integer)$lStartRec;
         $displayData['lRecsPerPage']    = $lRecsPerPage = (integer)$lRecsPerPage;
         $displayData['strNavRptTitle']  = 'Record selection';
         $displayData['strExport']       = false;   // compatibility with reports/record_nav_view
         $displayData['bShowRecNav']     = true;    // compatibility with reports/record_nav_view
         $displayData['bSuppressNavBr']  = true;
         $displayData['strLinkBase']     = 'reports/reports/crun/'.$lReportID;

         if ($lNumThisPage > 0){
            $this->crptRun->strSQL = $this->crptRun->strSQL."\nLIMIT $lStartRec, $lRecsPerPage;";
            $this->crptRun->loadCReportRecords($report->fields, $displayData['lNumCRecs'], $displayData['crecs']);
         }

            //------------------------------------------------
            // stripes
            //------------------------------------------------
         $this->load->model('util/mbuild_on_ready', 'clsOnReady');
         $this->clsOnReady->addOnReadyTableStripes();
         $this->clsOnReady->closeOnReady();
         $displayData['js'] .= $this->clsOnReady->strOnReady;

            //------------------------------------------------
            // breadcrumbs, page set-up
            //------------------------------------------------
         $displayData['mainTemplate'] = array('reports/record_nav_view', 'reports/creport_run_nav');
         $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                   .' | '.anchor('creports/custom_directory/view/'.$glUserID, 'Custom Report Directory', 'class="breadcrumb"')
                                   .' | '.anchor('creports/custom_directory/viewRec/'.$lReportID, 'Report Record', 'class="breadcrumb"')
                                   .' | Run: '.$report->strSafeName;

         $displayData['title']        = CS_PROGNAME.' | Reports';
         $displayData['nav']          = $this->mnav_brain_jar->navData();

         $this->load->vars($displayData);
         $this->load->view('template');
      }
   }

   function setLimits(&$sRpt, &$lStartRec, &$lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($sRpt->bShowRecNav){
         if (is_null($lStartRec)){
            if (is_null($sRpt->lStartRec)){
               $sRpt->lStartRec = $lStartRec = 0;
            }else {
               $lStartRec = $sRpt->lStartRec;
            }
         }else {
            $sRpt->lStartRec = $lStartRec;
         }
         if (is_null($lRecsPerPage)){
            if (is_null($sRpt->lRecsPerPage)){
               $sRpt->lRecsPerPage = $lRecsPerPage = 50;
            }else {
               $lRecsPerPage = $sRpt->lRecsPerPage;
            }
         }else {
            $sRpt->lRecsPerPage = $lRecsPerPage;
         }
      }
   }

   function run($reportID, $lStartRec=null, $lRecsPerPage=null,
                $v1=null,  $v2=null, $v3=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $this->load->helper('reports/report_util');
      $this->load->model ('reports/mreports', 'clsReports');
      $displayData['contextSummary'] = '';

      if (!isset($_SESSION[CS_NAMESPACE.'Reports'][$reportID])){
         $this->session->set_flashdata('error', 'The report you requested is no longer available. Please run the report again.');
         redirect_Reports();
      }

      $sRpt = $_SESSION[CS_NAMESPACE.'Reports'][$reportID];
      $sRpt->timeStamp = time();

         // clear out stale reports
      if ((rand(1, 10) % 10)==1) $this->testStaleReports();

      modelLoadViaRptType($this, $sRpt);

         //---------------------------------------------------
         // for screen reports, set the record navigation
         //---------------------------------------------------
      $this->screenRpt($reportID, $sRpt, $lStartRec, $lRecsPerPage,
                       $v1,       $v2,   $v3);
   }

   function screenRpt($reportID, &$sRpt, $lStartRec, $lRecsPerPage,
                      $v1,       $v2,    $v3){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $displayData['strLinkBase']  = 'reports/reports/run/'.$reportID;
      if (bAllowAccess('allowExports')){
         $displayData['strExport'] = $this->strExportAnchor($reportID, $sRpt->strExportLabel, $v1, $v2, $v3);
      }else {
         $displayData['strExport'] = '';
      }
      $displayData['bShowRecNav']  = true;
      $displayData['js']           = '';

      $this->setLimits($sRpt, $lStartRec, $lRecsPerPage);

      if ($sRpt->bShowRecNav){
         $displayData['strNavLinkExtra'] = '';
         $displayData['lTotRecs']        = $lNumRecsTot  =
                                                $this->lNumRecsViaRptType($sRpt, false, $lStartRec, $lRecsPerPage,
                                                                          $v1,   $v2,   $v3);
         $displayData['lNumThisPage']    = $lNumThisPage =
                                                $this->lNumRecsViaRptType($sRpt, true, $lStartRec, $lRecsPerPage,
                                                                          $v1,   $v2,  $v3);

         $displayData['lStartRec']       = $lStartRec = (integer)$lStartRec;
         $displayData['lRecsPerPage']    = $lRecsPerPage = (integer)$lRecsPerPage;
         $displayData['strNavRptTitle']  = 'Record selection';
      }

      $displayData['strReportPage']   = $this->strReportPage($reportID, $sRpt, $lStartRec, $lRecsPerPage,
                                                             $v1,       $v2,   $v3,
                                                             $displayData);

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      if ($sRpt->bShowRecNav){
         $displayData['bSuppressNavBr'] = false;
         $displayData['mainTemplate'] = array('reports/record_nav_view',
                                              'reports/'.$sRpt->viewFile);
      }else {
         $displayData['mainTemplate'] = 'reports/'.$sRpt->viewFile;
      }
      $displayData['pageTitle']    = strRptBreadCrumb($sRpt);

      $displayData['title']        = CS_PROGNAME.' | Reports';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function strExportAnchor($reportID, $strExportLabel, $v1, $v2, $v3){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($strExportLabel.''=='') return('');
      $strAnchor = 'reports/exports/run/'.$reportID;
      if (!is_null($v1)) $strAnchor .= '/'.$v1;
      if (!is_null($v2)) $strAnchor .= '/'.$v2;
      if (!is_null($v3)) $strAnchor .= '/'.$v3;
      return(anchor($strAnchor, $strExportLabel));
   }

   function strReportPage($reportID, &$sRpt, $lStartRec, $lRecsPerPage,
                          $v1,       $v2,    $v3,
                          &$displayData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strNavLinkExtra = &$displayData['strNavLinkExtra'];
      $this->load->helper('dl_util/rs_navigate');
      if (isset($sRpt->contextSummary)) $displayData['contextSummary'] = $sRpt->contextSummary;

      switch ($sRpt->rptName){
         case CENUM_REPORTNAME_GROUP:
            if (bAllowAccess('showReports')){
               return($this->groups->strGroupReportPage(
                                   $sRpt->enumContext, $sRpt->groupIDs, $sRpt->bShowAny,
                                   true,               $lStartRec,      $lRecsPerPage));

            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_VOLJOBSKILL:
            if (bAllowAccess('showReports')){
               return($this->clsVolSkills->strJobSkillsReportPage(
                                   $sRpt->skillIDs, $sRpt->bShowAny, $sRpt->bIncludeInactive,
                                   true,            $lStartRec,      $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_VOLHOURS:
            if (bAllowAccess('showReports')){
               $displayData['strExport'] = null;
               return($this->clsVolHours->strVolHoursReportPage(
                                   $sRpt,
                                   true,     $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_VOL_JOBCODE_YEAR:
            if (bAllowAccess('showReports')){
               $displayData['strExport'] = null;
               return($this->cVJobCodes->strVolJobCodes(
                                   $sRpt,
                                   true,     $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_VOL_JOBCODE_MONTH:
            if (bAllowAccess('showReports')){
               $displayData['strExport'] = null;
               return($this->cVJobCodes->strVolJobCodeMonthlyDetail(
                                   $sRpt,
                                   true,     $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_VOLHRS_PVA:
            if (bAllowAccess('showReports')){
               return($this->clsVolHours->strVolHoursPVAReportExport(
                                   $sRpt,
                                   true,     $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_VOL_HRS_MON:
            if (bAllowAccess('showReports')){
               return($this->clsVolHours->strVolHoursViaMonthReportExport(
                                   $sRpt,
                                   true,     $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_VOLHRSDETAIL_PVA:
            if (bAllowAccess('showReports')){
               return($this->clsVolHours->strVolHoursPVADetailReportExport(
                                   $sRpt,
                                   true,     $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_VOL_HRS_SUM:
            if (bAllowAccess('showReports')){
               return($this->clsVolHours->strVolHoursSumReportExport(
                                   $sRpt,
                                   true,     $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;
            
         case CENUM_REPORTNAME_VOLHOURSTFSUM:
            if (bAllowAccess('showReports')){
               return($this->clsVolHours->strVolHoursTFSumReportExport(
                                   $sRpt,
                                   true,     $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;
            

         case CENUM_REPORTNAME_VOL_HRS_DETAIL:
            if (bAllowAccess('showReports')){
               return($this->clsVolHours->strVolHoursDetailReportExport(
                                   $sRpt,
                                   true,     $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_VOL_HRS_YEAR:
            if (bAllowAccess('showReports')){
               return($this->clsVolHours->strVolHoursViaYearReport($sRpt));
            }else {
               return('');
            }
            break;

            // v1=volID, v2=eventID
         case CENUM_REPORTNAME_VOLHOURSDETAIL:
            if (bAllowAccess('showReports')){
               $displayData['strExport'] = null;
               $strNavLinkExtra = '/'.$v1.'/'.$v2;
               return($this->clsVolHours->strVolHoursDetailReportPage(
                                   $sRpt,
                                   true,            $lStartRec,    $lRecsPerPage,
                                   $v1,             $v2));
            }else {
               return('');
            }
            break;

            // v1=sort
         case CENUM_REPORTNAME_VOLHOURSVIAVID:
            if (bAllowAccess('showReports')){
               $strNavLinkExtra = '/'.$v1;
               return($this->clsVolHours->strHoursViaVIDReport(
                                                $sRpt,
                                                true,  $lStartRec,    $lRecsPerPage,
                                                $v1));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_VOLHOURSCHEDULE:
            if (bAllowAccess('showReports')){
               return($this->clsShifts->strVolScheduleReport(
                                                $sRpt, $reportID,
                                                true,  $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_VOLEVENTSCHEDULE:
            if (bAllowAccess('showReports')){
               return($this->clsVolEvents->scheduleReportExport($sRpt, $displayData, true));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_SPONPASTDUE:
            if (bAllowAccess('showSponsors') && (bAllowAccess('showFinancials') || bAllowAccess('showSponsorFinancials'))){
               $displayData['bShowRecNav'] = false;
               $displayData['lNumPastDue'] = $displayData['pastDue'] = null;
               return($this->clsSCP->strSponsorPastDueReport($this, $sRpt, $reportID, true, $displayData));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_GIFTACK:
            if (bAllowAccess('showReports') && bAllowAccess('showFinancials')){
               return($this->clsGifts->strGiftAckReportExport(
                                                $sRpt, $reportID,
                                                true,  $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_HONMEMGIFTLIST:
            if (bAllowAccess('showReports') && bAllowAccess('showFinancials')){
               return($this->clsGifts->strHonMemReportExport(
                                                $sRpt,
                                                true,     $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_GIFTRECENT:
            if (bAllowAccess('showReports') && bAllowAccess('showFinancials')){
               return($this->clsGifts->strGiftRecentReportExport(
                                                $sRpt, $reportID,
                                                true,  $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_GIFTTIMEFRAME:
         case CENUM_REPORTNAME_GIFTACCOUNT:
         case CENUM_REPORTNAME_GIFTCAMP:
         case CENUM_REPORTNAME_GIFTYEAREND:
            if (bAllowAccess('showReports') && bAllowAccess('showFinancials')){
               return($this->clsGifts->strGiftTimeFrameReportExport(
                                                $sRpt, $reportID,
                                                true,  $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_GIFTAGG:
            if (bAllowAccess('showReports') && bAllowAccess('showFinancials')){
               return($this->clsAgg->strGiftAggReport($sRpt, $displayData));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_GIFTSPONAGG:
            if (bAllowAccess('showSponsors') && (bAllowAccess('showFinancials') || bAllowAccess('showSponsorFinancials'))){
               return($this->clsAgg->strSponPayAggReport($sRpt, $displayData));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_SPONVIAPROG:
            if (bAllowAccess('showSponsors')){
               return($this->clsSponProg->strSponViaProgReportExport(
                                                $sRpt, $displayData,
                                                true,  $lStartRec,   $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_SPONVIALOCID:
            if (bAllowAccess('showSponsors') && bAllowAccess('showClients')){
            return($this->clsSpon->strSponViaLocReportExport(
                                                $sRpt, $displayData,
                                                true,  $lStartRec,   $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_SPONWOCLIENT:
            if (bAllowAccess('showSponsors')){
               return($this->clsSpon->strSponWOClientReportExport(
                                                $sRpt, $displayData,
                                                true,  $lStartRec,   $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_SPONINCOMEMONTH:
            if (bAllowAccess('showSponsors') && bAllowAccess('showFinancials')){
               return($this->clsSpon->strSponIncomeMonthReportExport(
                                                $sRpt, $displayData,
                                                true,  $lStartRec,   $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_SPONCHARGEMONTH:
            if (bAllowAccess('showSponsors') && bAllowAccess('showFinancials')){
               return($this->clsSpon->strSponChargeMonthReportExport(
                                                $sRpt, $displayData,
                                                true,  $lStartRec,   $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_ADMINUSAGE:
            if (bTestForURLHack('adminOnly')){
               $displayData['strExport'] = null;
               return($this->clsUserLog->strLoginLogUserReportExport(
                                                $sRpt, $displayData,
                                                true,  $lStartRec,   $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_ADMINUSERLOGIN:
            if (bTestForURLHack('adminOnly')){
               $displayData['strExport'] = null;
               return($this->clsUserLog->strLoginLogReportExport(
                                                $sRpt, $displayData,
                                                true,  $lStartRec,   $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_ATTRIB:
            if (bAllowAccess('showReports')){
               return($this->clsAttrib->strAttribReportExport(
                                                $sRpt, $displayData,
                                                true,  $lStartRec,   $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_CLIENTAGE:
            if (bAllowAccess('showClients')){
               return($this->cCRpt->strClientAgeReportExport(
                                                $sRpt,
                                                true,  $lStartRec,   $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_CLIENTVIASTATUS:
            if (bAllowAccess('showClients')){
               return($this->cCRpt->strCViaStatIDReportExport(
                                                $sRpt,
                                                true,  $lStartRec,   $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_CLIENTVIASTATCAT:
            if (bAllowAccess('showClients')){
               return($this->cCRpt->strCViaStatCatIDReportExport(
                                                $sRpt,
                                                true,  $lStartRec,   $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_CLIENTBDAY:
            if (bAllowAccess('showClients')){
               return($this->cCRpt->strClientBDayReportExport(
                                                $sRpt,
                                                true,  $lStartRec,   $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_DEPOSITLOG:
            if (bAllowAccess('showReports') && bAllowAccess('showFinancials')){
               return($this->clsDeposits->strDepositLogReportExport(
                                                $sRpt,
                                                true,     $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_CLIENT_PREPOST:
            if (bAllowAccess('showReports') && bAllowAccess('showClients')){
               return($this->cpptests->strPrePostTestReportExport($sRpt, true));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_CLIENT_PREPOSTDIR:
            if (bAllowAccess('showReports') && bAllowAccess('showClients')){
               return($this->cpptests->strPrePostClientDirReportExport(
                                                $sRpt, $reportID,
                                                true,  $lStartRec,    $lRecsPerPage));
            }else {
               return('');
            }
            break;

         default:
            screamForHelp($sRpt->rptName.': Unknow report type<br>error on line <b>'.__LINE__.'</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function lNumRecsViaRptType($sRpt, $bUseLimits, $lStartRec, $lRecsPerPage,
                               $v1,   $v2,         $v3){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($sRpt->rptType == CENUM_REPORTTYPE_PREDEFINED){
         switch ($sRpt->rptName){
            case CENUM_REPORTNAME_GROUP:
               $lNumRecs = $this->groups->lNumRecsInReport(
                                              $sRpt->enumContext, $sRpt->groupIDs, $sRpt->bShowAny,
                                              $bUseLimits,        $lStartRec,      $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_VOLJOBSKILL:
               $lNumRecs = $this->clsVolSkills->lNumRecsInReport(
                                              $sRpt->skillIDs, $sRpt->bShowAny, $sRpt->bIncludeInactive,
                                              $bUseLimits,     $lStartRec,      $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_VOLHOURS:
               $lNumRecs = $this->clsVolHours->lNumRecsInHoursReport(
                                                   $sRpt->dteStart, $sRpt->dteEnd, $sRpt->bSortEvent,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_VOLHOURSTFSUM:
               $lNumRecs = $this->clsVolHours->lNumRecsInHoursTFSumReport(
                                                   $sRpt->tmpTable, $sRpt->strBetween,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_VOLHRS_PVA:
               $lNumRecs = $this->clsVolHours->lNumRecsInHoursPVAReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_VOLHRSDETAIL_PVA:
               $lNumRecs = $this->clsVolHours->lNumRecsInHoursPVADetailReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_VOL_HRS_MON:
               $lNumRecs = $this->clsVolHours->lNumRecsInHoursMonthDetailReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_VOL_HRS_SUM:
               $lNumRecs = $this->clsVolHours->lNumRecsInHoursMonthSumReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_VOL_HRS_DETAIL:
               $lNumRecs = $this->clsVolHours->lNumRecsInHoursVolDetailReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

               // v1=volID, v2=eventID
            case CENUM_REPORTNAME_VOLHOURSDETAIL:
               $lNumRecs = $this->clsVolHours->lNumRecsInHoursDetailReport(
                                                   $sRpt->dteStart, $sRpt->dteEnd, $sRpt->bSortEvent,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage,
                                                   $v1,             $v2);
               break;

               // v1=sort
            case CENUM_REPORTNAME_VOLHOURSVIAVID:
               $lNumRecs = $this->clsVolHours->lNumRecsInHoursViaVIDReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage,
                                                   $v1);
               break;

            case CENUM_REPORTNAME_VOLHOURSCHEDULE:
               $lNumRecs = $this->clsShifts->lNumRecsInVolScheduleReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_SPONPASTDUE:
               $lNumRecs = null;
               break;

            case CENUM_REPORTNAME_GIFTACK:
               $lNumRecs = $this->clsGifts->lNumRecsInAckReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_GIFTRECENT:
               $lNumRecs = $this->clsGifts->lNumRecsInRecentReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_GIFTTIMEFRAME:
            case CENUM_REPORTNAME_GIFTACCOUNT:
            case CENUM_REPORTNAME_GIFTCAMP:
            case CENUM_REPORTNAME_GIFTYEAREND:
               $lNumRecs = $this->clsGifts->lNumRecsInTimeFrameReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_HONMEMGIFTLIST:
               $lNumRecs = $this->clsGifts->lNumRecsInHonMemReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_GIFTAGG:
            case CENUM_REPORTNAME_GIFTSPONAGG:
            case CENUM_REPORTNAME_VOLEVENTSCHEDULE:
               $lNumRecs = 0;
               break;

            case CENUM_REPORTNAME_SPONVIAPROG:
               $lNumRecs = $this->clsSponProg->lNumRecsInSponViaProgReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_SPONVIALOCID:
               $lNumRecs = $this->clsSpon->lNumRecsInSponViaLocReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_SPONWOCLIENT:
               $lNumRecs = $this->clsSpon->lNumRecsInSponWOClientReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_SPONINCOMEMONTH:
               $lNumRecs = $this->clsSpon->lNumRecsSponMonthlyIncomeReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_SPONCHARGEMONTH:
               $lNumRecs = $this->clsSpon->lNumRecsSponMonthlyChargeReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_ADMINUSAGE:
               $lNumRecs = $this->clsUserLog->lNumRecsUsageViaUserReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_ADMINUSERLOGIN:
               $lNumRecs = $this->clsUserLog->lNumRecsLoginLogReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_ATTRIB:
               $lNumRecs = $this->clsAttrib->lNumRecsAttribReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_CLIENTAGE:
               $lNumRecs = $this->cCRpt->lNumRecsAgeReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_CLIENTBDAY:
               $lNumRecs = $this->cCRpt->lNumRecsBDayReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_CLIENTVIASTATUS:
               $lNumRecs = $this->cCRpt->lNumRecsCViaStatReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_CLIENTVIASTATCAT:
               $lNumRecs = $this->cCRpt->lNumRecsCViaStatCatReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_DEPOSITLOG:
               $lNumRecs = $this->clsDeposits->lNumRecsDepositLogReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            case CENUM_REPORTNAME_CLIENT_PREPOSTDIR:
               $lNumRecs = $this->cpptests->lNumRecsPPTestDirReport(
                                                   $sRpt,
                                                   $bUseLimits,     $lStartRec,    $lRecsPerPage);
               break;

            default:
               screamForHelp($sRpt->rptName.': Unknow report type<br>error on line <b>'.__LINE__.'</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }
      }else {
      }

      return($lNumRecs);
   }

   function testStaleReports(){
   //---------------------------------------------------------------------
   // rationale - keep the session report array from becoming huge
   // if user never logs off
   //---------------------------------------------------------------------
      $lOneDay = 24*60*60;
      $dteNow = time();
      $lNumRpts = count($_SESSION[CS_NAMESPACE.'Reports']);

      if ($lNumRpts > 0){
         $rptIDs = array_keys($_SESSION[CS_NAMESPACE.'Reports']);

         foreach ($rptIDs as $rID){
            $rpt = $_SESSION[CS_NAMESPACE.'Reports'][$rID];
            if (!isset($rpt->timeStamp)){
               unset($_SESSION[CS_NAMESPACE.'Reports'][$rID]);
            }else {
               if (($dteNow - $rpt->timeStamp) > $lOneDay){
                  unset($_SESSION[CS_NAMESPACE.'Reports'][$rID]);
               }
            }
         }
      }
   }



}