<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class search_order extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function add_edit($lReportID){
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
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper ('reports/creport_util');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      $this->load->library('generic_form');
      $this->load->helper ('reports/creport_util');
      $this->load->helper ('reports/search');
      $this->load->helper ('dl_util/special_ddl');
      $this->load->helper ('creports/creport_field');
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('dl_util/context');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('creports/link_creports');
      $this->load->model  ('admin/madmin_aco'); 
      $this->load->model  ('admin/mpermissions');
      $this->load->model  ('personalization/muser_fields');
      $this->load->model  ('creports/mcreports',           'clsCReports');
      $this->load->model  ('creports/mcrpt_search_terms',  'crptTerms');
      $this->load->model  ('creports/mcrpt_terms_display', 'crptTD');
      $this->load->library('util/up_down_top_bottom');

         //------------------------------------------------
         // load report
         //------------------------------------------------
      $displayData['cRptTypes'] = loadCReportTypeArray();
      $this->clsCReports->loadReportViaID($lReportID, true);
      $displayData['report']    = $report = &$this->clsCReports->reports[0];

      if (!$report->bUserHasWriteAccess){
         vid_bTestFail($this, false, 'Custom Report', $lReportID);
         return;
      }

      $displayData['lNumFields'] =   $report->lNumFields ; //$this->clsCReports->lFieldCount($lReportID);
      $displayData['contextSummary'] = $this->clsCReports->strCReportHTMLSummary();

         //------------------------------------------------
         // load formatted search expression
         //------------------------------------------------
      $attributes = new stdClass;
      $attributes->lReportID          = $lReportID;
      $attributes->bShowSortLink      = false;
      $attributes->bShowParenEditLink = true;
      $attributes->bShowUpDownLink    = true;
      $displayData['strSearchExpression'] =
                     $this->crptTD->strFormattedSearchExpression($lReportID, $attributes, $bBalanced);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | '.anchor('creports/custom_directory/view/'.$glUserID, 'Custom Report Directory', 'class="breadcrumb"')
                                .' | '.anchor('creports/custom_directory/viewRec/'.$lReportID, 'Custom Report: '.$report->strSafeName, 'class="breadcrumb"')
                                .' | Search Order';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'creports/search_order_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   public function move($lReportID, $lFieldID, $enumMove){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');

      $lReportID = (integer)$lReportID;
      $lFieldID = (integer)$lFieldID;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper('reports/search');
      $this->load->helper('dl_util/context');
      $this->load->helper('reports/creport_util');
      $this->load->helper('creports/creport_field');
      $this->load->model ('admin/madmin_aco'); 
      $this->load->model ('creports/mcreports', 'clsCReports');

         //------------------------------------------------
         // load report
         //------------------------------------------------
      $displayData['cRptTypes'] = loadCReportTypeArray();
      $this->clsCReports->loadReportViaID($lReportID, true);
      $report = &$this->clsCReports->reports[0];

      if (!$report->bUserHasWriteAccess){
         vid_bTestFail($this, false, 'Custom Report', $lReportID);
         return;
      }

      $this->load->library('util/up_down_top_bottom', '', 'upDown');

      $this->upDown->enumMove            = $enumMove;
      $this->upDown->enumRecType         = 'creport fields';

      $this->upDown->strUfieldDDL        = 'creport_search';
      $this->upDown->strUfieldDDLKey     = 'crs_lKeyID';
      $this->upDown->strUfieldDDLSort    = 'crs_lSortIDX';
      $this->upDown->strUfieldDDLQual1   = 'crs_lReportID';
      $this->upDown->strUfieldDDLRetired = '';
      $this->upDown->lUfieldDDLQual1Val  = $lReportID;
      $this->upDown->lKeyID              = $lFieldID;

      $this->upDown->moveRecs();

      $this->session->set_flashdata('msg', 'The search fields were re-ordered');
      redirect('creports/search_order/add_edit/'.$lReportID);
   }

   function addEditSortOrder($lReportID){
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
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper('reports/search');
      $this->load->helper('dl_util/context');
      $this->load->helper('creports/creport_field');
      $this->load->helper('reports/creport_util');
      $this->load->helper('creports/link_creports');
      $this->load->model ('admin/madmin_aco'); 
      $this->load->model ('creports/mcreports', 'clsCReports');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/up_down_top_bottom');

         //------------------------------------------------
         // load report
         //------------------------------------------------
      $displayData['cRptTypes'] = loadCReportTypeArray();
      $this->clsCReports->loadReportViaID($lReportID, true);
      $displayData['report']    = $report = &$this->clsCReports->reports[0];

      if (!$report->bUserHasWriteAccess){
         vid_bTestFail($this, false, 'Custom Report', $lReportID);
         return;
      }

         //------------------------------------------------
         // load sorting terms
         //------------------------------------------------
      $this->clsCReports->loadSortFieldsViaReportID($lReportID, $displayData['lNumSortTerms'], $displayData['sortTerms']);

      $displayData['contextSummary'] = $this->clsCReports->strCReportHTMLSummary();

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | '.anchor('creports/custom_directory/view/'.$glUserID, 'Custom Report Directory', 'class="breadcrumb"')
                                .' | '.anchor('creports/custom_directory/viewRec/'.$lReportID, 'Custom Report: '.$report->strSafeName, 'class="breadcrumb"')
                                .' | Sorting Order';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'creports/sort_term_order_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   public function moveSort($lReportID, $lFieldID, $enumMove){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');

      $lReportID = (integer)$lReportID;
      $lFieldID = (integer)$lFieldID;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper('reports/search');
      $this->load->helper('dl_util/context');
      $this->load->helper('reports/creport_util');
      $this->load->helper('creports/creport_field');
      $this->load->model ('admin/madmin_aco'); 
      $this->load->model ('creports/mcreports', 'clsCReports');

         //------------------------------------------------
         // load report
         //------------------------------------------------
      $displayData['cRptTypes'] = loadCReportTypeArray();
      $this->clsCReports->loadReportViaID($lReportID, true);
      $report = &$this->clsCReports->reports[0];

      if (!$report->bUserHasWriteAccess){
         vid_bTestFail($this, false, 'Custom Report', $lReportID);
         return;
      }

      $this->load->library('util/up_down_top_bottom', '', 'upDown');

      $this->upDown->enumMove            = $enumMove;
      $this->upDown->enumRecType         = 'creport fields';

      $this->upDown->strUfieldDDL        = 'creport_sort';
      $this->upDown->strUfieldDDLKey     = 'crst_lKeyID';
      $this->upDown->strUfieldDDLSort    = 'crst_lSortIDX';
      $this->upDown->strUfieldDDLQual1   = 'crst_lReportID';
      $this->upDown->strUfieldDDLRetired = '';
      $this->upDown->lUfieldDDLQual1Val  = $lReportID;
      $this->upDown->lKeyID              = $lFieldID;

      $this->upDown->moveRecs();

      $this->session->set_flashdata('msg', 'The sort fields were re-ordered');
      redirect('creports/search_order/addEditSortOrder/'.$lReportID);
   }
   

}



