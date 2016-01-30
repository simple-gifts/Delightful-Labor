<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class search_terms extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function add_edit($lReportID, $lTermID=0){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');
      verifyIDsViaType($this, CENUM_CONTEXT_CUSTOMREPORTTERM, $lTermID, true);

      $displayData = array();
      $displayData['lReportID'] = $lReportID = (integer)$lReportID;
      $displayData['lTermID']   = $lTermID   = (integer)$lTermID;
      $displayData['bNew']      = $bNew      = $lTermID <= 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper ('reports/creport_util');
      $this->load->helper ('creports/link_creports');
      $this->load->helper ('reports/search');
      $this->load->helper ('dl_util/context');
      $this->load->helper ('dl_util/web_layout');
      $this->load->model  ('personalization/muser_schema',  'cUFSchema');
      $this->load->model  ('personalization/muser_fields',  'clsUF');
      $this->load->model  ('admin/mpermissions',            'perms');
      $this->load->model  ('admin/madmin_aco');
      $this->load->model  ('creports/mcreports',            'clsCReports');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

         //------------------------------------------------
         // load report
         //------------------------------------------------
      $this->clsCReports->loadReportViaID($lReportID, false);
      $displayData['report']    = $report = &$this->clsCReports->reports[0];
      $enumRptType = $report->enumRptType;
      $displayData['contextSummary'] = $this->clsCReports->strCReportHTMLSummary();

      $displayData['tables'] = $tables = $this->clsCReports->loadTableStructures($enumRptType);
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$tables   <pre>');
echo(htmlspecialchars( print_r($tables, true))); echo('</pre></font><br>');
// ------------------------------------- */
      
//         // for single entry tables, allow user to qualify based on if the record has been initialized (written)
//      addField_CheckForWritten($tables);

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('rdoField', 'Field', 'trim');
      if ($this->form_validation->run() == FALSE){

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
         }else {
            setOnFormError($displayData);
         }

            // set up the generic field selection view
         $displayData['strFormLink']    = 'creports/search_terms/add_edit/'.$lReportID.'/'.$lTermID;
         $displayData['strLabel']       = 'Select a field to <b>qualify</b> your report:<br>';
         $displayData['bShowAscending'] = false;

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                   .' | '.anchor('creports/custom_directory/view/'.$glUserID, 'Custom Report Directory', 'class="breadcrumb"')
                                   .' | '.anchor('creports/custom_directory/viewRec/'.$lReportID, 'Custom Report: '.$report->strSafeName, 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').' Search Field';

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'creports/search_sort_term_add_edit_view.php';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         redirect('creports/search_terms/term_selected/'.$lReportID
                           .'/'.$_POST['rdoField'].'/'.$lTermID);
      }
   }

   function editTerm($lReportID, $lTermID){
   }

   function term_selected($lReportID, $strField, $lTermID=0){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $gbDateFormatUS;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');
      verifyIDsViaType($this, CENUM_CONTEXT_CUSTOMREPORTTERM, $lTermID, true);

      $displayData = array();
      $displayData['lReportID'] = $lReportID = (integer)$lReportID;
      $displayData['lTermID']   = $lTermID   = (integer)$lTermID;
      $displayData['bNew']      = $bNew      = $lTermID <= 0;

      $displayData['strField']  = $strField = trim($strField);

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper ('reports/creport_util');
      $this->load->helper ('creports/link_creports');
      $this->load->helper ('dl_util/special_ddl');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('dl_util/context');
      $this->load->helper ('reports/search');
      $this->load->helper ('dl_util/time_date');
      $this->load->model  ('admin/madmin_aco');
      $this->load->model  ('personalization/muser_schema', 'cUFSchema');
      $this->load->model  ('personalization/muser_fields', 'clsUF');
      $this->load->model  ('admin/mpermissions',           'perms');
      $this->load->model  ('creports/mcreports',           'clsCReports');
      $this->load->model  ('creports/mcrpt_search_terms',  'crptTerms');
      $this->load->library('generic_form');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

         //------------------------------------------------
         // load report
         //------------------------------------------------
      $this->clsCReports->loadReportViaID($lReportID, false);
      $displayData['report']    = $report = &$this->clsCReports->reports[0];

      $enumRptType = $report->enumRptType;
      $displayData['contextSummary'] = $this->clsCReports->strCReportHTMLSummary();

      $tables = $this->clsCReports->loadTableStructures($enumRptType);
      $displayData['bCheckForWritten'] = $bCheckForWritten = bTestFor_CheckForWritten($strField);
      if ($bCheckForWritten){         
         $lTableIDX = lTableIDX_ViaTName_CFW($tables, substr($strField, 0, 9));
         $table = &$tables[$lTableIDX];
         $displayData['lFieldIDX'] = $lFieldIDX = createCFW_Field($table);
      }else {
         $this->clsCReports->findFieldInTables($strField, $tables, $lTableIDX, $lFieldIDX);
         $table = &$tables[$lTableIDX];
         $displayData['lFieldIDX'] = $lFieldIDX;
      }
      $displayData['lFieldID']  = $lFieldID = $table->fields[$lFieldIDX]->lFieldID;
      $displayData['lTableID']  = $table->lTableID;
      $displayData['lTableIDX'] = $lTableIDX;

         //-----------------------
         // search expression
         //-----------------------
      $this->crptTerms->loadSearchTermViaTermID($lTermID);
      $displayData['term']      = $term = &$this->crptTerms->terms[0];
      $displayData['field']     = $field = &$tables[$lTableIDX]->fields[$lFieldIDX];

         // special case for currency for new search terms
      if (is_null($term->lKeyID) && $field->enumType == CS_FT_CURRENCY){
         $displayData['term']->lCurrencyACO = $field->lCurrencyACO;
         $displayData['term']->ACO = clone $field->ACO;
      }

      if ($table->lTableID > 0){
         $displayData['tableName'] = '['.$table->strAttachLabel.'] ['.htmlspecialchars($table->name).']';
      }else {
         $displayData['tableName'] = '['.$table->name.']';
      }
      $this->crptTerms->loadFieldTermOpts($lReportID, $term, $field);

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->setRulesViaFieldType($field->enumType);

      if ($this->form_validation->run() == FALSE){
         if (validation_errors()==''){
            $term->strCompVal = htmlspecialchars($term->strCompVal);
         }else {
            setOnFormError($displayData);
            $this->loadSearchCriteriaViaType($term, $field->enumType);
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                   .' | '.anchor('creports/custom_directory/view/'.$glUserID, 'Custom Report Directory', 'class="breadcrumb"')
                                   .' | '.anchor('creports/custom_directory/viewRec/'.$lReportID, 'Custom Report', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New ': 'Edit ').'Search Expression';

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'creports/search_expression_add_edit_view.php';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->load->helper('dl_util/util_db');

         if ($bNew){
            $term->lReportID  = $lReportID;
            $term->lTableID   = (int)$_POST['lTableID'];
            $term->lFieldID   = (int)$_POST['lFieldID'];
            $term->strFieldID = $_POST['internalFieldID'];
            $term->lNumRParen = 0;
            $term->lNumLParen = 0;
            $term->lSortIDX   = $this->crptTerms->lMaxSortIDXViaReport($lReportID) + 1;
         }else {
         }

         $this->loadSearchCriteriaViaType($term, $field->enumType);
         if ($field->enumType == CS_FT_DATE){
            MDY_ViaUserForm($term->strDteCompVal, $lMon, $lDay, $lYear, $gbDateFormatUS);
            $term->mdteCompVal = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);
         }

         if ($bNew){
            $lTermID = $this->crptTerms->lAddNewSearchTerm();
         }else {
            $this->crptTerms->updateSearchTerm($lTermID);
         }
         $this->session->set_flashdata('msg', 'The search term was '.($bNew ? 'added' : 'updated').'.');
         redirect('creports/custom_directory/viewRec/'.$lReportID);
      }
   }

   function loadSearchCriteriaViaType(&$term, $enumFieldType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumFieldType){
         case CS_FT_CHECKBOX:
            $term->lCompareOpt = CL_SRCH_EQ;
            $term->bCompareBool = $_POST['ddlCompValue1']==CL_SRCH_CHK_YES;
            break;

         case CS_FT_ID:
         case CS_FT_INTEGER:
            $term->lCompareOpt  = (int)$_POST['ddlCompare'];
            $term->lCompVal     = $_POST['txtCompValue'];
            $this->setTermCompareDDL($term, $term->lCompareOpt);
            break;

         case CS_FT_CURRENCY:
            $term->lCompareOpt  = (int)$_POST['ddlCompare'];
            $term->curCompVal   = (float)$_POST['txtCompValue'];
            $this->setTermCompareDDL($term, $term->lCompareOpt);
            break;

         case CS_FT_DATE:
            $term->lCompareOpt   = (int)$_POST['ddlCompare'];
            $term->strDteCompVal = trim($_POST['txtDate']);
            $this->setTermCompareDDL($term, $term->lCompareOpt);
            break;

         case CS_FT_DDL:
         case CS_FT_DDLMULTI:
            $term->lCompareOpt  = (int)$_POST['ddlCompare'];
            $term->lCompVal     = (int)$_POST['ddlCompareTo'];
            $this->setTermCompareDDL($term, $term->lCompareOpt);
            break;

         case CS_FT_DDL_SPECIAL:
            $term->lCompareOpt  = (int)$_POST['ddlCompare'];
            $term->strCompVal   = $_POST['ddlCompareTo'];
            $this->setTermCompareDDL($term, $term->lCompareOpt);
            break;

         case CS_FT_TEXTLONG:
         case CS_FT_TEXT255:
         case CS_FT_TEXT80:
         case CS_FT_TEXT20:
         case CS_FT_TEXT:
            $term->lCompareOpt   = (int)$_POST['ddlCompare'];
            $term->strCompVal    = trim($_POST['txtCompValue']);
            $this->setTermCompareDDL($term, $term->lCompareOpt);
            break;

         default:
            screamForHelp($enumFieldType.': unexpected field type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function setTermCompareDDL(&$term, $lCompareOpt){
      foreach ($term->ddlCompare as $dc){
         $dc->bSel = $lCompareOpt == $dc->optVal;
      }
   }

   function setRulesViaFieldType($enumFieldType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->form_validation->set_rules('lFieldID', 'Field ID', 'trim');  // dummy to force verification

      switch ($enumFieldType){
         case CS_FT_CHECKBOX:
            break;

         case CS_FT_ID:
         case CS_FT_INTEGER:
            $this->form_validation->set_rules('txtCompValue', 'Comparison Value', 'trim|required|integer');
            break;

         case CS_FT_CURRENCY:
            $this->form_validation->set_rules('txtCompValue', 'Comparison Value', 'trim|required|numeric');
            break;

         case CS_FT_DATE:
            $this->form_validation->set_rules('txtDate', 'Comparison Date', 'trim|required|callback_verifySearchDate');
            break;

         case CS_FT_DDL:
         case CS_FT_DDLMULTI:
            $this->form_validation->set_rules('ddlCompareTo', 'Compare To', 'trim|required|callback_verifyDDLSelect');
            break;

         case CS_FT_DDL_SPECIAL:
            $this->form_validation->set_rules('ddlCompareTo', 'Compare To', 'trim|required|callback_verifySpecialDDLSelect');
            break;

         case CS_FT_TEXTLONG:
         case CS_FT_TEXT255:
         case CS_FT_TEXT80:
         case CS_FT_TEXT20:
         case CS_FT_TEXT:
            $this->form_validation->set_rules('txtCompValue', 'Comparison Text', 'trim|required');
            break;
         default:
            screamForHelp($enumFieldType.': unexpected field type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function verifySpecialDDLSelect($strValue){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_numeric($strValue)){
         $lSelectID = (int)$strValue;
         if ($lSelectID <= 0){
            $this->form_validation->set_message('verifySpecialDDLSelect', 'Please make a selection from the drop-down list.');
            return(false);
         }else {
            return(true);
         }
      }else {
         return(true);
      }
   }

   function verifyDDLSelect($strValue){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lSelectID = (int)$strValue;
      if ($lSelectID <= 0){
         $this->form_validation->set_message('verifyDDLSelect', 'Please make a selection from the drop-down list.');
         return(false);
      }else {
         return(true);
      }
   }

   function verifySearchDate($strDate){
      if (!bValidVerifyDate($strDate)){
         $this->form_validation->set_message('verifySearchDate', 'The <b>Comparison Date</b> is not valid.');
         return(false);
      }else {
         return(true);
      }
   }

   function term_save($lReportID, $lTermID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');
      verifyIDsViaType($this, CENUM_CONTEXT_CUSTOMREPORTTERM, $lTermID, true);
      $lTermID = (integer)$lTermID;
      $lReportID = (integer)$lReportID;

      $bNew = $lTermID <= 0;

      $strFieldInternal = $_POST['internalFieldID'];

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper ('reports/creport_util');
      $this->load->helper ('reports/search');
      $this->load->model  ('admin/madmin_aco');
      $this->load->model  ('personalization/muser_schema',         'cUFSchema');
      $this->load->model  ('personalization/muser_fields',         'clsUF');
      $this->load->model  ('admin/mpermissions',                   'perms');
      $this->load->model  ('creports/mcreports',                   'clsCReports');
      $this->load->model  ('creports/mcrpt_search_terms',          'crptTerms');

      $this->clsCReports->loadReportViaID($lReportID, false);
      $report = &$this->clsCReports->reports[0];
      $enumRptType = $report->enumRptType;

      $tables = $this->clsCReports->loadTableStructures($enumRptType);
      $this->clsCReports->findFieldInTables($strFieldInternal, $tables, $lTableIDX, $lFieldIDX);

      $this->crptTerms->loadSearchTermViaTermID($lTermID);
      $term = &$this->crptTerms->terms[0];

      if ($bNew){
         $term->lReportID  = $lReportID;
         $term->strFieldID = $strFieldInternal;
      }else {
      }
   }

   function remove($lReportID, $lTermID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lReportID, 'custom report ID');
      verifyIDsViaType($this, CENUM_CONTEXT_CUSTOMREPORTTERM, $lTermID, false);

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('reports/search');
      $this->load->helper('dl_util/context');
      $this->load->helper('creports/creport_field');
      $this->load->helper('reports/creport_util');
      $this->load->model ('admin/madmin_aco');
      $this->load->model ('creports/mcreports');
      $this->load->model ('creports/mcrpt_search_terms', 'crptTerms');
      $this->crptTerms->removeTerm($lTermID);

      $this->session->set_flashdata('msg', 'The search term was removed.');
      redirect('creports/custom_directory/viewRec/'.$lReportID);
   }

}



