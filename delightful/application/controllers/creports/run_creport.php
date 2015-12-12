<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class run_creport extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function debugSQLGen($lReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');

      $displayData = array();
      $displayData['lReportID'] = $lReportID;
      $displayData['js'] = '';

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $this->load->helper ('reports/creport_util');
      $this->load->helper ('creports/link_creports');
      $this->load->helper ('creports/creport_field');
      $this->load->helper ('creports/creport_special_ddl');
      $this->load->helper ('creports/creport_tables');
      $this->load->helper ('reports/search');
      $this->load->helper ('dl_util/special_ddl');
      $this->load->helper ('dl_util/context');
      $this->load->helper ('dl_util/time_date');
      $this->load->model  ('admin/mpermissions', 'perms');
      $this->load->model  ('admin/madmin_aco');
      $this->load->model  ('personalization/muser_fields');
      $this->load->model  ('creports/mcreports',           'clsCReports');
      $this->load->model  ('creports/mcrpt_search_terms',  'crptTerms');
      $this->load->model  ('creports/mcrpt_run',           'crptRun');
      $this->load->model  ('creports/mcrpt_terms_display', 'crptTD');

      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

         //------------------------------------------------
         // load report
         //------------------------------------------------
      $displayData['cRptTypes'] = loadCReportTypeArray();
      $this->clsCReports->loadReportViaID($lReportID, true);
      $displayData['report']    = $report = &$this->clsCReports->reports[0];

         // verify user has access to all tables referenced in report
      if (!$this->crptRun->bVerifyUserAccessToReport($report, $displayData['lNumFails'], $displayData['failTables'])){
         $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                   .' | '.anchor('creports/custom_directory/view/'.$glUserID, 'Custom Report Directory', 'class="breadcrumb"')
                                   .' | '.anchor('creports/custom_directory/viewRec/'.$lReportID, 'Report Record', 'class="breadcrumb"')
                                   .' | Custom Report SQL: '.$report->strSafeName;

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'creports/report_notableaccess_view';
         $this->load->vars($displayData);
         $this->load->view('template');
         return;
      }

      $displayData['lNumFields'] =   $report->lNumFields ; //$this->clsCReports->lFieldCount($lReportID);
      $displayData['contextSummary'] = $this->clsCReports->strCReportHTMLSummary();

         //------------------------------------------------
         // load formatted search expression
         //------------------------------------------------
      $attributes = new stdClass;
      $attributes->lReportID = $lReportID;
      $attributes->bShowParenEditLink = false;
      $attributes->bShowSortLink      = false;
      $attributes->bParenAsTextInput  = false;
      $attributes->showEditDelete     = false;

      $displayData['strSearchExpression'] =
                     $this->crptTD->strFormattedSearchExpression($lReportID, $attributes, $bBalanced);


      $this->crptRun->strBuildCReportSQL($report);
      $displayData['sqlFrom']   = $this->crptRun->strJoins;
      $displayData['sqlWhere']  = $this->crptRun->strWhere;
      $displayData['sqlSelect'] = $this->crptRun->strSelect;
      $displayData['sqlSQL']    = $this->crptRun->strSQL;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | '.anchor('creports/custom_directory/view/'.$glUserID, 'Custom Report Directory', 'class="breadcrumb"')
                                .' | '.anchor('creports/custom_directory/viewRec/'.$lReportID, 'Report Record', 'class="breadcrumb"')
                                .' | Custom Report SQL: '.$report->strSafeName;

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'creports/debug_sql_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function reviewReport($lReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $displayData = array();

      $this->load->helper ('creports/link_creports');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      $this->load->model  ('admin/mpermissions', 'perms');

      $this->genericCReportReview(
                         $lReportID, $displayData, $bFail, $fails, $bFieldsOK,
                         $bTablePermissionOK, $displayData['lNumFails'], $displayData['failTables'],
                         $lNumDDLJoins);

      $report = &$this->crptRun->reports[0];

      $displayData['contextSummary'] = $this->crptRun->strCReportHTMLSummary();
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | '.anchor('creports/custom_directory/view/'.$glUserID, 'Custom Report Directory', 'class="breadcrumb"')
                                .' | '.anchor('creports/custom_directory/viewRec/'.$lReportID, 'Report Record', 'class="breadcrumb"')
                                .' | Custom Report Error: '.$report->strSafeName;

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();


         //-------------------------------------------------------------------------------
         // one of four ways to progress:
         //   1) general fail - return to record with error message
         //   2) personalized field not available - give user the chance to auto-repair
         //   3) user doesn't have access to one or more personalized tables
         //   4) no fails - run report
         //-------------------------------------------------------------------------------
      if ($bFail){
         $strError = 'The following error(s) were found in your report. Please correct and try again:'
                  .'<ul>'
                     .implode('<li>'.$fails).'
                    </ul>';
         $this->session->set_flashdata('error', $strError);
         redirect('creports/custom_directory/viewRec/'.$lReportID);
      }elseif (!$bTablePermissionOK){
         $displayData['mainTemplate']   = 'creports/report_notableaccess_view';
         $this->load->vars($displayData);
         $this->load->view('template');
         return;
      }elseif ($lNumDDLJoins > 45){
         $displayData['lNumDDLJoins'] = $lNumDDLJoins;
         $displayData['mainTemplate']   = 'creports/report_err_joins_view';
         $this->load->vars($displayData);
         $this->load->view('template');
         return;
      }elseif (!$bFieldsOK){

            //--------------------------
            // breadcrumbs
            //--------------------------

         $displayData['mainTemplate']   = 'creports/run_report_bad_fields_view';

         $this->load->vars($displayData);
         $this->load->view('template');
         return;
      }else {
         redirect('reports/reports/crun/'.$lReportID);
      }
   }

   function removeBadFields($lReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $bfInfo = array();
      $this->genericCReportReview($lReportID, $bfInfo, $bFail, $fails, $bFieldsOK,
                         $bTablePermissionOK, $lNumTablePermFails,  $failTablePerms,
                         $lNumDDLJoins);
      $report = &$this->crptRun->reports[0];

      if ($bfInfo['lNumBad_Display'] > 0){
         foreach ($bfInfo['badFields_Display'] as $term){
            $this->crptRun->deleteSingleReportDisplayField($lReportID, $term->lFieldID);
         }
      }

      if ($bfInfo['lNumBad_Search'] > 0){
         foreach ($bfInfo['badFields_Search'] as $term){
            $this->crptRun->removeTermViaRptIDFieldID($lReportID, $term->lFieldID);
         }
      }

      if ($bfInfo['lNumBad_Sort'] > 0){
         foreach ($bfInfo['badFields_Sort'] as $term){
            $this->crptRun->deleteSortOrderTermViaRptIDFieldID($lReportID, $term->lFieldID);
         }
      }
      $this->session->set_flashdata('msg', 'Unavailable fields were removed from your report.');
      redirect('creports/custom_directory/viewRec/'.$lReportID);
   }

   private function genericCReportReview(
                        &$lReportID, &$displayData, &$bFail, &$fails, &$bFieldsOK,
                        &$bTablePermissionOK, &$lNumTablePermFails, &$failTablePerms,
                        &$lNumDDLJoins){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');

      $displayData['lReportID'] = $lReportID = (int)$lReportID;
      $displayData['js'] = '';

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper('reports/search');
      $this->load->helper('dl_util/context');
      $this->load->helper('reports/creport_util');
      $this->load->helper('creports/creport_field');
      $this->load->helper('creports/creport_tables');
      $this->load->helper('dl_util/time_date');
      $this->load->model ('admin/madmin_aco');
      $this->load->model ('creports/mcreports');
      $this->load->model ('creports/mcrpt_search_terms');
      $this->load->model ('creports/mcrpt_run', 'crptRun');
      $this->load->model ('personalization/muser_fields');

      $this->crptRun->creportReviewUtility(
                      $lReportID, $displayData, $bFail, $fails, $bFieldsOK,
                      $bTablePermissionOK, $lNumTablePermFails, $failTablePerms,
                      $lNumDDLJoins);
   }


}