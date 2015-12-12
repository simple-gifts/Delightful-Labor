<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_log_search extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function searchOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model  ('personalization/muser_fields', 'clsUF');
      $this->load->model  ('admin/mpermissions',           'perms');
      $this->load->helper ('dl_util/web_layout');
      $this->load->library('generic_form');

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtSearch',   'Search Text',     'trim|required');
      $this->form_validation->set_rules('chkLogs[]',   'Check Boxes',     'callback_verifyCheckLogValid');
      $this->form_validation->set_rules('rdoCriteria', 'Search Criteria', 'trim');

		if ($this->form_validation->run() == FALSE){
         $displayData['bCheckedLog'] = new stdClass;

            // personalized tables
         $idx = $lNumTabsTot = 0;
         $pTabs = array();
         foreach ($this->clsUF->enumTableTypes as $enumTType){
            $pTabs[$idx] = new stdClass;
            $this->clsUF->setTType($enumTType);
            $pTabs[$idx]->strTTypeLabel = $this->clsUF->strTTypeLabel;
            $pTabs[$idx]->enumTType     = $enumTType;
            $displayData['bCheckedLog']->$enumTType = false;

            $this->clsUF->loadTablesViaTType();
            $pTabs[$idx]->lNumTables = $lNumTables = $this->clsUF->lNumTables;
            $pTabs[$idx]->lNumLogFields = 0;
            $lNumTabsTot += $lNumTables;
            if ($lNumTables > 0){
               $pTabs[$idx]->userTables = $this->clsUF->userTables;
               $jIdx = 0;
               foreach ($this->clsUF->userTables as $clsUTable){
                  $clsUTable->logInfo = new stdClass;
                  $logInfo = &$clsUTable->logInfo;

                  $pTabs[$idx]->userTables[$jIdx]->lNumFields =
                              $this->clsUF->lNumUF_TableFields($clsUTable->lKeyID);
                  $lTableID = $clsUTable->lKeyID;
                  $this->clsUF->fieldEntriesViaTableID($lTableID, CS_FT_LOG, $logInfo->lNumFields, $logInfo->fieldArray);
                  $pTabs[$idx]->lNumLogFields += $logInfo->lNumFields;

                  ++$jIdx;
               }
            }
            ++$idx;
         }
         $displayData['criteria'] = new stdClass;
         if (validation_errors()==''){
            $this->setAllChecksFalse($displayData, $pTabs);
            $displayData['criteria']->bPhrase = true;
            $displayData['criteria']->bAll    =
            $displayData['criteria']->bAny    = false;
            $displayData['strSearch']         = '';

         }else {
            setOnFormError($displayData);
            if (isset($_POST['chkLogs'])){
               $displayData['bCheckedLog']->clientStatus = in_array('clientStatus', $_POST['chkLogs']);
               $displayData['bCheckedLog']->clientBio    = in_array('clientBio',    $_POST['chkLogs']);
               $displayData['bCheckedLog']->docsImages   = in_array('docsImages',   $_POST['chkLogs']);
               $displayData['bCheckedLog']->giftNotes    = in_array('giftNotes',    $_POST['chkLogs']);
               foreach ($pTabs as $pTab){
                  $enumTType = $pTab->enumTType;
                  $displayData['bCheckedLog']->$enumTType = in_array($enumTType, $_POST['chkLogs']);
               }
            }else {
               $this->setAllChecksFalse($displayData, $pTabs);
            }
            $displayData['criteria']->bPhrase = set_value('rdoCriteria')=='phrase';
            $displayData['criteria']->bAll    = set_value('rdoCriteria')=='all';
            $displayData['criteria']->bAny    = set_value('rdoCriteria')=='any';
            $displayData['strSearch']         = set_value('txtSearch');
            
         }
         $displayData['pTabs'] = &$pTabs;
         
            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                   .' | Log Search';

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'reports/pre_log_search_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else{
         $this->logSearchReportRun();
      }
   }

   function setAllChecksFalse(&$displayData, &$pTabs){
      $displayData['bCheckedLog']->clientStatus = false;
      $displayData['bCheckedLog']->clientBio    = false;
      $displayData['bCheckedLog']->docsImages   = false;
      $displayData['bCheckedLog']->giftNotes    = false;
   }
   
   function verifyCheckLogValid($chkArray){
      return(isset($_POST['chkLogs']));
   }
   
   function logSearchReportRun(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      $displayData = array();
      
      $strSearchType   = strtoupper(trim($_POST['rdoCriteria']));

      $bTypeExact = $strSearchType=='PHRASE';
      $bTypeAll   = $strSearchType=='ALL';
      $bTypeAny   = $strSearchType=='ANY';
      $displayData['strSearch'] = $strSearch  = trim($_POST['txtSearch']);


         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model  ('util/msearch_log',             'cLogSearch');
      $this->load->model  ('personalization/muser_fields', 'clsUF');
      $this->load->model  ('admin/mpermissions',           'perms');
      $this->load->model  ('admin/madmin_aco',             'clsACO'); 
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->helper ('dl_util/string');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('dl_util/context');
      
      $this->cLogSearch->initSearch($strSearch, $bTypeExact, $bTypeAll, $bTypeAny);
      $displayData['strSearchTypeLabel'] = $this->cLogSearch->strSearchTypeLabel;
      
      $idx = 0;
      $searchResults = array();
      foreach ($_POST['chkLogs'] as $searchTarget){
         $searchResults[$idx] = $this->cLogSearch->runSearch($searchTarget);
         ++$idx;
      }
      $displayData['searchResults'] = &$searchResults;
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports',                 'Reports',    'class="breadcrumb"')
                                .' | '.anchor('reports/pre_log_search/searchOpts', 'Log Search', 'class="breadcrumb"')
                                .' | Log Search Results';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_log_search_results_view';
      $this->load->vars($displayData);
      $this->load->view('template');
      
/*      
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<pre>'); print_r($searchResults); echo('</pre></font><br>');   
*/   
   }

}
