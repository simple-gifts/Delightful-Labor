<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class custom_directory extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($lUserLibID, $enumRpt='all', $sort='rptDate'){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lUserLibID, 'user ID');

      $displayData = array();
      $displayData['js'] = '';

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->library('generic_form');
      $this->load->helper ('reports/creport_util');
      $this->load->model  ('admin/madmin_aco');
      $this->load->model  ('creports/mcreports',   'clsCReports');
      $this->load->model  ('util/mbuild_on_ready', 'clsOnReady');
      $this->load->model  ('admin/muser_accts',    'clsUser');
      $this->load->model  ('admin/mpermissions',   'perms');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('creports/link_creports');

      $displayData['cRptTypes'] = loadCReportTypeArray();

         //------------------------------------------------
         // users and their report counts
         //------------------------------------------------
      $this->clsCReports->loadUserDirectoryCounts();
      $displayData['cUserRpts'] = &$this->clsCReports->cUserRpts;
      $displayData['lNumUsers'] = $this->clsCReports->lNumUsers;
      $this->clsUser->loadSingleUserRecord($lUserLibID);
      $displayData['userRec'] = &$this->clsUser->userRec;

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         //------------------------------------------------
         // load reports
         //------------------------------------------------
      $this->clsCReports->loadReportDirViaUserID($lUserLibID, $sort);
      $displayData['cRpts']    = &$this->clsCReports->cRptDir;
      $displayData['lNumRpts'] = $lNumRpts = $this->clsCReports->lNumRptsInDir;
      if ($lNumRpts > 0){
         foreach ($displayData['cRpts'] as $report){
            $report->lNumFields = $this->clsCReports->lFieldCount($report->lKeyID);
         }
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Custom Report Directory';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'creports/custom_dir_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function viewUserSel(){
      $lUserLibID = $_POST['ddlUser'];
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lUserLibID, 'user ID');

      $this->view($lUserLibID);
   }

   function viewRec($lReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');

      $displayData = array();
      $displayData['lReportID'] = $lReportID = (int)$lReportID;
      $displayData['js'] = '';

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper ('reports/creport_util');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

      $this->load->helper ('creports/link_creports');
      $this->load->library('generic_form');
      $this->load->helper ('reports/creport_util');
      $this->load->helper ('reports/search');
      $this->load->helper ('creports/creport_field');
      $this->load->helper ('creports/creport_tables');
      $this->load->helper ('dl_util/context');
      $this->load->helper ('dl_util/special_ddl');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('dl_util/time_date');

      $this->load->model  ('admin/madmin_aco');
      $this->load->model  ('creports/mcreports');
      $this->load->model  ('creports/mcrpt_search_terms');
      $this->load->model  ('creports/mcrpt_run',           'crptRun');
      $this->load->model  ('creports/mcrpt_terms_display', 'crptTD');
      $this->load->model  ('personalization/muser_fields', 'clsUF');
      $this->load->model  ('admin/mpermissions');

      $this->load->model  ('util/mbuild_on_ready',         'clsOnReady');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //------------------------------------------------
         // load report
         //------------------------------------------------
      $displayData['cRptTypes'] = loadCReportTypeArray();
      $this->crptRun->loadReportViaID($lReportID, true);
      $displayData['report']    = $report = &$this->crptRun->reports[0];

      if (!$report->bUserHasReadAccess){
         vid_bTestFail($this, false, 'Custom Report', $lReportID);
         return;
      }

      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | '.anchor('creports/custom_directory/view/'.$glUserID, 'Custom Report Directory', 'class="breadcrumb"')
                                .' | Custom Report: '.$report->strSafeName;

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

         // verify user has access to all tables referenced in report
      if (!$this->crptRun->bVerifyUserAccessToReport($report, $displayData['lNumFails'], $displayData['failTables'])){
         $displayData['mainTemplate']   = 'creports/report_notableaccess_view';
         $this->load->vars($displayData);
         $this->load->view('template');
         return;
      }

      $displayData['bReadOnly'] = $bReadOnly = !$report->bUserHasWriteAccess;

         //----------------
         // sort terms
         //----------------
      $this->crptRun->loadSortFieldsViaReportID($lReportID, $displayData['lNumSortTerms'], $displayData['sortTerms']);
      if ($displayData['lNumSortTerms'] > 0){
         foreach ($displayData['sortTerms'] as $term){
            $term->strUserFTypeLabel = $this->clsUF->strFieldTypeLabel($term->enumFieldType);
         }
      }

      $displayData['lNumFields'] =   $report->lNumFields ; //$this->crptRun->lFieldCount($lReportID);

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         //------------------------------------------------
         // load formatted search expression
         //------------------------------------------------
      $attributes = new stdClass;
      $attributes->lReportID = $lReportID;
      $attributes->bShowParenEditLink = !$bReadOnly;
      $attributes->showEditDelete     = !$bReadOnly;
      $attributes->bShowSortLink      = !$bReadOnly;
      $displayData['strSearchExpression'] =
                     $this->crptTD->strFormattedSearchExpression($lReportID, $attributes, $displayData['bBalanced']);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['mainTemplate']   = 'creports/report_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function remove($lReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');

      $lReportID = (int)$lReportID;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper('reports/search');
      $this->load->helper('dl_util/context');
      $this->load->helper('creports/creport_field');
      $this->load->helper('reports/creport_util');
      $this->load->model ('admin/madmin_aco');
      $this->load->model ('creports/mcreports', 'clsCReports');

          //------------------------------------------------
         // load report
         //------------------------------------------------
      $displayData['cRptTypes'] = loadCReportTypeArray();
      $this->clsCReports->loadReportViaID($lReportID, true);
      $displayData['lNumReports'] = $lNumReports = $this->clsCReports->lNumReports;
      $displayData['report']      = $report = &$this->clsCReports->reports[0];

      if (!$report->bUserHasWriteAccess){
         vid_bTestFail($this, false, 'Custom Report', $lReportID);
         return;
      }

      $this->clsCReports->removeCReport($lReportID);
      redirect('creports/custom_directory/view/'.$glUserID);
  }


}