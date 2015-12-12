<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class search_parens extends CI_Controller {

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
         // models, libraries and utilities
         //------------------------------------------------
      $this->load->helper ('reports/creport_util');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      $this->load->library('generic_form');
      $this->load->helper ('js/toggle_paren');
      $this->load->helper ('reports/creport_util');
      $this->load->helper ('reports/search');
      $this->load->helper ('dl_util/special_ddl');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('creports/link_creports');
      $this->load->helper ('creports/creport_field');
      $this->load->helper ('dl_util/context');
      $this->load->model  ('admin/madmin_aco'); 
      $this->load->model  ('personalization/muser_fields');
      $this->load->model  ('creports/mcreports',           'clsCReports');
      $this->load->model  ('creports/mcrpt_search_terms',  'crptTerms');
      $this->load->model  ('creports/mcrpt_terms_display', 'crptTD');
      $this->load->library('util/up_down_top_bottom');

      $displayData['js'] .= strToggleParen();

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
      $attributes->lReportID = $lReportID;
      $attributes->bShowParenEditLink = false;
      $attributes->bShowSortLink      = false;
      $attributes->bParenAsTextInput  = true;
      $displayData['strSearchExpression'] =
                     $this->crptTD->strFormattedSearchExpression($lReportID, $attributes, $bBalanced);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | '.anchor('creports/custom_directory/view/'.$glUserID, 'Custom Report Directory', 'class="breadcrumb"')
                                .' | Custom Report: '.$report->strSafeName;

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'creports/paren_edit_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function update($lReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');
      
         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $this->load->helper('dl_util/context');
      $this->load->helper('reports/creport_util');
      $this->load->model ('admin/madmin_aco'); 
      $this->load->model ('creports/mcreports');
      $this->load->model ('creports/mcrpt_search_terms', 'crptTerms');

      $terms = array();
      foreach ($_POST as $strFN=>$vValue){
         if (substr($strFN, 0, 8)=='txtParen'){
            $parens = explode('_', $strFN);
            $bLeft = $parens[1] == 'L';
            $lTermID = (int)$parens[2];
            $lTermIDX = (int)$parens[3];
            $this->setParenTermEntry($terms, $lTermID);
            if ($vValue.'' != ''){
               if ($bLeft){
                  ++$terms[$lTermID]->left;
               }else {
                  ++$terms[$lTermID]->right;
               }
            }

         }elseif (substr($strFN, 0, 8)=='ddlAndOr'){
            $strAndOr = explode('_', $strFN);
            $lTermID = (int)$strAndOr[1];
            $this->setParenTermEntry($terms, $lTermID);
            $terms[$lTermID]->bAnd  = $vValue=='AND';
         }
      }      
      $this->crptTerms->updateParens($terms);

      $this->session->set_flashdata('msg', 'The search expression was updated.');
      redirect('creports/custom_directory/viewRec/'.$lReportID);
   }

   function setParenTermEntry(&$terms, &$lTermID){
      if (!isset($terms[$lTermID])){
         $terms[$lTermID] = new stdClass;
         $terms[$lTermID]->left  = 0;
         $terms[$lTermID]->right = 0;
         $terms[$lTermID]->bAnd  = false;
      }
   }


}



