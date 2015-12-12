<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class uf_tables_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEditTable($enumTType, $lTableID=0){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();

      $displayData['enumTType'] = $enumTType = htmlspecialchars($enumTType);

      if ($lTableID.'' != '0'){
         $this->load->helper('dl_util/verify_id');
         if (!vid_bUserTableIDExists($this, $lTableID, $enumTType)) vid_bTestFail($this, false, 'user table ID', $lTableID);
      }

      $displayData['lTableID'] = $lTableID = (integer)$lTableID;

         /*------------------------------------------------
             models/libraries/helpers
         ------------------------------------------------*/
      $this->load->model ('personalization/muser_fields', 'clsUF');
      $this->load->model ('admin/mpermissions',           'perms');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('personalization/validate_custom_verification');

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtTableName',  'Table Name',
                        'trim|callback_userTableVerifyRequired|callback_userTableVerifyUnique['.$enumTType.','.$lTableID.']');
		$this->form_validation->set_rules('txtVerificationModule', 'Validation File',         'callback_verifyVerMod');
		$this->form_validation->set_rules('txtVModEntryPoint',     'Validation Entry Point',  'callback_verifyVModEntry');
                        
      $this->form_validation->set_rules('txtDescription');
      $this->form_validation->set_rules('chkMultiEntry');
      $this->form_validation->set_rules('chkReadOnly');
      $this->form_validation->set_rules('chkHide');
      $this->form_validation->set_rules('chkCollapsible');
      $this->form_validation->set_rules('chkAlertNoDataEntry');
      $this->form_validation->set_rules('txtAlert');      
      

      $displayData['bNew'] = $bNew = $lTableID<=0;

      $this->clsUF->setTType($enumTType);
      if ($bNew) {
         $this->clsUF->lTableID = 0;
      }else {
         $this->clsUF->lTableID = $lTableID;
      }
      $this->clsUF->loadTableViaTableID(false);
      $displayData['userTables']   = $uTable = &$this->clsUF->userTables[0];

      $displayData['strTTypeLabel'] = $strLabel = $this->clsUF->strTTypeLabel;

      if ($this->form_validation->run() == FALSE){

         $displayData['title']        = CS_PROGNAME.' | Personalization';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                 .' | '.anchor('admin/personalization/overview/'.$enumTType, 'Personalization', 'class="breadcrumb"')
                                 .' | '.($bNew ? 'Add New ' : 'Edit ').$strLabel.' Table';
         $displayData['nav']          = $this->mnav_brain_jar->navData();

         $this->load->library('generic_form');

         $displayData['bCurrentlyHidden']  = $uTable->bHidden;
         if (validation_errors()==''){
            $displayData['strTableName']          = htmlspecialchars($uTable->strUserTableName);
            $displayData['bHidden']               = $uTable->bHidden;
            $displayData['bCollapsible']          = $uTable->bCollapsibleHeadings;
            $displayData['bMultiEntry']           = $uTable->bMultiEntry;
            $displayData['bReadOnly']             = $uTable->bReadOnly;
            $displayData['strDescription']        = $uTable->strDescription;
            $displayData['bAlertNoDataEntry']     = $uTable->bAlertIfNoEntry;
            $displayData['strAlert']              = $uTable->strAlertMsg;
            $displayData['txtVerificationModule'] = htmlspecialchars($uTable->strVerificationModule);
            $displayData['txtVModEntryPoint']     = htmlspecialchars($uTable->strVModEntryPoint);
         }else {
            setOnFormError($displayData);
            $displayData['strTableName']          = set_value('txtTableName');
            $displayData['bHidden']               = set_value('chkHide')=='true';
            $displayData['bCollapsible']          = set_value('chkCollapsible')=='true';
            if ($bNew) {
               $displayData['bMultiEntry']        = set_value('chkMultiEntry')=='true';
            }else {
               $displayData['bMultiEntry']        = $uTable->bMultiEntry;
            }
            $displayData['bReadOnly']             = set_value('chkReadOnly')=='true';
            $displayData['strDescription']        = set_value('txtDescription');
            $displayData['bAlertNoDataEntry']     = set_value('chkAlertNoDataEntry')=='true';
            $displayData['strAlert']              = set_value('txtAlert');
            $displayData['txtVerificationModule'] = set_value('txtVerificationModule');
            $displayData['txtVModEntryPoint']     = set_value('txtVModEntryPoint');
         }

         $displayData['mainTemplate'] = 'personalization/uf_add_edit_table_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->load->model('personalization/muser_fields_create', 'clsUFC');
         $this->clsUFC->enumTType             = $enumTType;
         $this->clsUFC->strUserTableName      = xss_clean(trim($_POST['txtTableName']));
         $this->clsUFC->strVerificationModule = xss_clean(trim($_POST['txtVerificationModule']));
         $this->clsUFC->strVModEntryPoint     = xss_clean(trim($_POST['txtVModEntryPoint']));
         if ($bNew){
            $this->clsUFC->bMultiEntry      = trim(@$_POST['chkMultiEntry'])=='true';
         }else {
            $this->clsUFC->bMultiEntry      = $uTable->bMultiEntry;
         }
         $this->clsUFC->bReadOnly            = trim(@$_POST['chkReadOnly'])=='true';
         $this->clsUFC->bCollapsibleHeadings = trim(@$_POST['chkCollapsible'])=='true';
         $this->clsUFC->bCollapseDefaultHide = true;   // should this be a user option?
         $this->clsUFC->bHidden              = trim(@$_POST['chkHide']       )=='true';
         $this->clsUFC->strTableDescription  = xss_clean(trim($_POST['txtDescription']));

         $this->clsUFC->bAlertNoDataEntry = trim(@$_POST['chkAlertNoDataEntry'])=='true';
         $this->clsUFC->strAlert          = trim($_POST['txtAlert']);

         $strMultiLabel = $this->clsUFC->bMultiEntry ? ' multiple-entry ' : ' single-entry ';

         $bRetired = @$_POST['chkRetire']=='YES';
         if ($bRetired) {
            $this->clsUFC->lTableID = $lTableID;
            $this->clsUFC->loadTableViaTableID();
            $this->clsUFC->removeUFTable($lTableID);
            $this->session->set_flashdata('msg', 'The '.$strLabel
                       .$strMultiLabel.' table <b>'.htmlspecialchars($this->clsUFC->strUserTableName).'</b> was removed.');

            redirect('admin/personalization/overview');
         }else {
            if ($bNew){
               $lTableID = $this->clsUFC->lAddNewUFTable();
               $this->session->set_flashdata('msg', 'The new '.$strLabel.$strMultiLabel.' table was added');
               redirect('admin/uf_fields/view/'.$lTableID);
            }else {
               $this->clsUFC->lTableID   = $lTableID;
               $this->clsUFC->updateUFTable();
               $this->session->set_flashdata('msg', 'The '.$strLabel.$strMultiLabel.' table was updated');
               redirect('admin/personalization/overview/'.$enumTType);
            }
         }
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
	function verifyVerMod($strMod){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (customVal\verifyVerMod($strMod, $strErr)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyVerMod', $strErr);
         return(false);
      }
   }

	function verifyVModEntry($strEntry){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strMod    = trim(@$_POST['txtVerificationModule']);
      if (customVal\verifyVModEntry($strEntry, $strMod, $strErr)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyVModEntry', $strErr);
         return(false);
      }
   }
      
   function userTableVerifyRequired($strTableName){
      $bRetired = @$_POST['chkRetire']=='YES';
      if ($bRetired) return(true);
      $strTableName = trim($strTableName);
      return($strTableName != '');
   }

   function userTableVerifyUnique($strTableName, $params){
      $strTableName = trim($strTableName);
      $pArray       = explode(',', $params);
      $enumTType    = trim($pArray[0]);
      $lTableID     = (integer)$pArray[1];

      $bRetired = @$_POST['chkRetire']=='YES';
      if ($bRetired) return(true);

      $this->load->model('util/mverify_unique', 'clsUnique');
      return($this->clsUnique->bVerifyUniqueText(
                $strTableName, 'pft_strUserTableName',
                $lTableID,     'pft_lKeyID',
                true,          'pft_bRetired',
                true, $enumTType, 'pft_enumAttachType',
                false, null, null,
                'uf_tables'));
   }

   function addField($lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();

      $displayData['lTableID'] = $lTableID = (integer)$lTableID;

         //-------------------------
         // models and helpers
         //-------------------------
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);
      $this->load->model  ('admin/mpermissions',           'perms');
      $this->load->model  ('personalization/muser_fields', 'clsUF');
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);
      $this->load->helper('clients/client_program');

      $this->clsUF->lTableID = $lTableID;
      $this->clsUF->loadTableViaTableID(false);

      $uTable = &$this->clsUF->userTables[0];
      $enumTType = $uTable->enumTType;
      $bMultiEntry = $uTable->bMultiEntry;

      $this->clsUF->loadFieldTypes($bMultiEntry);

      setClientProgFields($displayData, $bClientProg, $lCProgID, $cprog, $enumTType, $lTableID);

      $displayData['ddlFields'] = $this->clsUF->strDDLFields('');

      if ($bClientProg){
         $displayData['title']        = CS_PROGNAME.' | Client Programs';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                 .' | '.anchor('cprograms/cprograms/overview', 'Client Programs', 'class="breadcrumb"')
                                 .' | '.anchor('cprograms/cprog_record/view/'.$lCProgID, htmlspecialchars($cprog->strProgramName), 'class="breadcrumb"')
                                 .' | '.anchor('admin/uf_fields/view/'.$lTableID, 'Fields', 'class="breadcrumb"')
                                 .' | Add New Field';
      }else {
         $displayData['title']        = CS_PROGNAME.' | Personalization';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                 .' | '.anchor('admin/personalization/overview/'.$enumTType, 'Personalization', 'class="breadcrumb"')
                                 .' | '.anchor('admin/uf_fields/view/'.$lTableID, 'Fields', 'class="breadcrumb"')
                                 .' | Add New Field';
      }
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = 'personalization/uf_add_field_sel_type_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addField2($lTableID, $lFieldID){
   //----------------------------------------------------------------------
   //
   //----------------------------------------------------------------------
      $enumFieldType = urlencode(trim($_POST['ddlFieldType']));
      $lTableID = (integer)$lTableID;
      $lFieldID = (integer)$lFieldID;
      redirect('admin/uf_tables_add_edit/addField2a/'.$lTableID.'/'.$lFieldID.'/'.$enumFieldType);
   }

   function addField2a($lTableID, $lFieldID, $enumFieldType){
   //----------------------------------------------------------------------
   //
   //----------------------------------------------------------------------
      global $gclsChapterACO;
      if (!bTestForURLHack('adminOnly')) return;

      $displayData = array();
      $displayData['opts']     = new stdClass;
      $displayData['lTableID'] = $lTableID = (integer)$lTableID;
      $displayData['lFieldID'] = $lFieldID = (integer)$lFieldID;
      $displayData['bNew']     = $bNew = $lFieldID <= 0;
      $displayData['enumFieldType'] = $enumFieldType;

         //-------------------------
         // models and helpers
         //-------------------------
      $this->load->model('personalization/muser_fields');
      $this->load->model('personalization/muser_fields_create', 'clsUFC');
      $this->load->model('admin/mpermissions',                  'perms');
      $this->load->helper('clients/client_program');
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);

      $this->clsUFC->lTableID = $lTableID;
      $this->clsUFC->loadTableViaTableID();
      $uTable = &$this->clsUFC->userTables[0];
      $enumTType = $uTable->enumTType;

      $this->clsUF = &$this->clsUFC;  // satisfy the reference in client_program_helper
      setClientProgFields($displayData, $bClientProg, $lCProgID, $cprog, $enumTType, $lTableID);

      $displayData['strUserTableName'] = $uTable->strUserTableName;
      $this->clsUFC->strENPTableName   = $uTable->strDataTableName;
      $displayData['bMultiEntry']      = $bMultiEntry = $uTable->bMultiEntry;

      $displayData['bShowRequired'] = $bShowRequired =
                       $bMultiEntry && !($enumFieldType==CS_FT_CHECKBOX ||
                                         $enumFieldType==CS_FT_LOG      ||
                                         $enumFieldType==CS_FT_HEADING);

         //---------------------
         // field info
         //---------------------
      $this->clsUFC->loadFieldTypes();
      $this->clsUFC->loadSingleField($lFieldID, false);
      $displayData['strFieldTypeLabel'] = $this->clsUFC->strFieldTypeLabel($enumFieldType);
      $uField = &$this->clsUFC->fields[0];

      $this->setVerificationViaFieldType($enumFieldType, $lTableID, $lFieldID);
      if ($bShowRequired) $this->form_validation->set_rules('chkRequired');

      if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');
//         $displayData['entrySummary'] = $this->clsUFC->strUFTableSummaryDisplay(true);

         if (validation_errors()==''){
            $displayData['strFieldNameUser']    = htmlspecialchars($uField->pff_strFieldNameUser);
            $displayData['strFieldNotes']       = htmlspecialchars($uField->strFieldNotes);
            $displayData['opts']->lDDLDefault   = $uField->pff_lDDLDefault;
            $displayData['opts']->bCheckDef     = $uField->pff_bCheckDef;
            $displayData['opts']->curDef        = $uField->pff_curDef;
            $displayData['opts']->lCurrencyACO  = $uField->pff_lCurrencyACO;
            $displayData['opts']->bHidden       = $uField->pff_bHidden;
            $displayData['opts']->bPrefill      = $uField->bPrefilled;
            $displayData['opts']->strTxtDef     = htmlspecialchars($uField->pff_strTxtDef);
            $displayData['opts']->lDef          = $uField->pff_lDef;
            if ($bShowRequired) $displayData['opts']->bRequired = $uField->pff_bRequired; ;
         }else {
            setOnFormError($displayData);

            $displayData['strFieldNameUser']    = set_value('txtFieldName');
            $displayData['strFieldNotes']       = set_value('txtFieldNotes');
            $displayData['opts']->lDDLDefault   = 0;
            $displayData['opts']->bCheckDef     = @$_POST['rdoDefaultYN']=='YES';
            $displayData['opts']->curDef        = @$_POST['txtDefaultCur'];
            $displayData['opts']->lCurrencyACO  = @$_POST['rdoACO'];
            $displayData['opts']->bHidden       = @$_POST['chkHidden']=='true';
            $displayData['opts']->bPrefill      = @$_POST['chkPrefill']=='true';
            $displayData['opts']->strTxtDef     = @$_POST['txtDefaultText'];
            $displayData['opts']->lDef          = @$_POST['txtDefaultInt'];
            if ($bShowRequired) $displayData['opts']->bRequired = set_value('chkRequired')=='true';
         }

         $displayData['lTableID']     = $lTableID = (integer)$lTableID;

            //-----------------------------
            // breadcrumbs and headers
            //-----------------------------
         if ($bClientProg){
            $displayData['title']        = CS_PROGNAME.' | Client Programs';
            $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                    .' | '.anchor('cprograms/cprograms/overview', 'Client Programs', 'class="breadcrumb"')
                                    .' | '.anchor('cprograms/cprog_record/view/'.$lCProgID, htmlspecialchars($cprog->strProgramName), 'class="breadcrumb"')
                                    .' | '.anchor('admin/uf_fields/view/'.$lTableID, 'Fields', 'class="breadcrumb"')
                                    .' | '.($bNew ? 'Add new ' : 'Update ').'Field';
         }else {
            $displayData['title']        = CS_PROGNAME.' | Personalization';
            $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                    .' | '.anchor('admin/personalization/overview/'.$enumTType, 'Personalization', 'class="breadcrumb"')
                                    .' | '.anchor('admin/uf_fields/view/'.$lTableID, 'Fields', 'class="breadcrumb"')
                                    .' | '.($bNew ? 'Add new ' : 'Update ').'Field';
         }
         $displayData['nav']          = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate'] = 'personalization/uf_add_field_info_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->load->helper('dl_util/util_db');
         $this->strENPTableName = $this->clsUFC->userTables[0]->strUserTableName;

         $uField->pff_strFieldNameUser = $strUserFieldName = trim($_POST['txtFieldName']);
         $uField->strFieldNotes        = trim($_POST['txtFieldNotes']);
         $uField->pff_bCheckDef        = false;
         $uField->pff_bDDLMasterList   = false;
         $uField->pff_bHidden          = @$_POST['chkHidden']=='true';
         $uField->bPrefilled           = @$_POST['chkPrefill']=='true';
         $uField->pff_curDef           = 0;
         $uField->pff_strTxtDef        = '';
         $uField->pff_lDef             = -1;
         $uField->pff_lCurrencyACO     = $gclsChapterACO->lKeyID;
         if ($bShowRequired) {
            $uField->pff_bRequired     = trim(@$_POST['chkRequired'])=='true';
         }
         if ($bNew){
            $uField->enumFieldType = $enumFieldType;
            $this->session->set_flashdata('msg', 'New field <b>"'.htmlspecialchars($strUserFieldName).'"</b> added');
         }else {
            if (isset($_POST['rdoACO'])){
               $uField->pff_lCurrencyACO  = (integer)$_POST['rdoACO'];
            }
            $this->session->set_flashdata('msg', 'Field <b>"'.htmlspecialchars($strUserFieldName).'"</b> updated');
         }
         switch ($enumFieldType) {
            case CS_FT_CHECKBOX:
               $this->setup_CheckBox($bNew, $this->clsUFC, @$_POST['rdoDefaultYN']=='YES');
               break;

            case CS_FT_DATE:
            case CS_FT_DATETIME:
               $this->setup_DateTime($bNew, $this->clsUFC);
               break;

            case CS_FT_TEXT255:
            case CS_FT_TEXT80:
            case CS_FT_TEXT20:
               $this->setup_Text($bNew, $this->clsUFC);
               break;

            case CS_FT_TEXTLONG:
               $this->setup_TextLong($bNew, $this->clsUFC);
               break;

            case CS_FT_HEADING:
               $this->setup_TextLong($bNew, $this->clsUFC);
               break;

            case CS_FT_INTEGER:
               $this->setup_Integer($bNew, $this->clsUFC, (integer)trim($_POST['txtDefaultInt']));
               break;

            case CS_FT_CLIENTID:
               $this->setup_Integer($bNew, $this->clsUFC, 0);
               break;

            case CS_FT_CURRENCY:
               $this->setup_Currency($bNew, $this->clsUFC, trim($_POST['txtDefaultCur']), $_POST['rdoACO']);
               break;

            case CS_FT_DDL:
            case CS_FT_DDLMULTI:
               $this->setup_DDL_PartOne($bNew, $this->clsUFC);
               break;

            case CS_FT_LOG:
               $this->setup_Log($bNew, $this->clsUFC);
               break;
            default:
               screamForHelp('Invalid field type '.$lFieldType.' detected, error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__);
               break;
         }
         redirect('admin/uf_fields/view/'.$lTableID);
      }
   }

   function setup_Currency($bNew, $clsUFC, $strCur, $strRDOACO){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsUFC->fields[0]->pff_curDef       = number_format($strCur, 2, '.', '');
      $clsUFC->fields[0]->pff_lCurrencyACO = (integer)$strRDOACO;
      if ($bNew) {
         $clsUFC->addNewField();
      }else {
         $clsUFC->updateField();
      }
   }

   function setup_CheckBox($bNew, $clsUFC, $bDefault){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsUFC->fields[0]->pff_bCheckDef = $bDefault;

      if ($bNew) {
         $clsUFC->addNewField();
      }else {
         $clsUFC->updateField();
      }
   }

   function setup_Text($bNew, $clsUFC){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsUFC->fields[0]->pff_strTxtDef = trim($_POST['txtDefaultText']);

      if ($bNew) {
         $clsUFC->addNewField();
      }else {
         $clsUFC->updateField();
      }
   }

   function setup_TextLong($bNew, $clsUFC){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsUFC->fields[0]->pff_strTxtDef = '';

      if ($bNew) {
         $clsUFC->addNewField();
      }else {
         $clsUFC->updateField();
      }
   }

   function setup_DateTime($bNew, $clsUFC){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bNew) {
         $clsUFC->addNewField();
      }else {
         $clsUFC->updateField();
      }
   }

   function setup_Integer($bNew, $clsUFC, $lDefault){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsUFC->fields[0]->pff_lDef = $lDefault;

      if ($bNew) {
         $clsUFC->addNewField();
      }else {
         $clsUFC->updateField();
      }
   }

   function setup_Log($bNew, $clsUFC){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bNew) {
         $clsUFC->addNewField();
      }else {
         $clsUFC->updateField();
      }
   }

   function setup_DDL_PartOne($bNew, $clsUFC){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bNew) {
         $clsUFC->addNewField();
      }else {
         $clsUFC->updateField();
      }
   }


      //-------------------------
      // Form Verification
      //-------------------------
   private function setVerificationViaFieldType($enumFieldType, $lTableID, $lFieldID){
   //----------------------------------------------------------------------
   //
   //----------------------------------------------------------------------
         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');

      $this->form_validation->set_rules('txtFieldName', 'Field Name',
                               'trim|callback_fieldNameRqrtTest'
                                  .'|callback_fieldNameDupTest['.$lTableID.','.$lFieldID.']');
      $this->form_validation->set_rules('txtFieldNotes', 'Field Notes', 'trim');
      $this->form_validation->set_rules('chkHidden', 'Hidden', 'trim');

      switch ($enumFieldType){
         case CS_FT_CHECKBOX:
            $this->form_validation->set_rules('rdoDefaultYN', 'Checkbox Default Value', 'trim|required');
            break;
         case CS_FT_DATE:
         case CS_FT_DATETIME:
         case CS_FT_TEXT255:
         case CS_FT_TEXT80:
         case CS_FT_TEXT20:
         case CS_FT_TEXTLONG:
         case CS_FT_DDL:
         case CS_FT_DDLMULTI:
         case CS_FT_LOG:
         case CS_FT_HEADING:
         break;

         case CS_FT_INTEGER:
         case CS_FT_CLIENTID:
            $this->form_validation->set_rules('txtDefaultInt', 'Default Integer Value', 'trim|callback_stripCommas|integer');
            break;

         case CS_FT_CURRENCY:
            $this->load->model('admin/madmin_aco', 'clsACO');
            $this->form_validation->set_rules('txtDefaultCur', 'Default Currency Value', 'trim|callback_stripCommas|required|numeric');
            $this->form_validation->set_rules('rdoACO', 'Accounting Country', 'trim|required');
            break;

         default:
            screamForHelp($enumFieldType.': Invalid field type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function ufFieldAddAcctCountry($strField){
      return($strField != '');
   }

   function fieldNameRqrtTest($strField){
      return($strField != '');
   }

   function fieldNameDupTest($strField, $params){
      $arrayParams = explode(',', $params);
      $lTID = (integer)$arrayParams[0];
      $lFID = (integer)$arrayParams[1];

      $this->load->model('util/mverify_unique', 'clsUnique');
      return($this->clsUnique->bVerifyUniqueText(
                $strField, 'pff_strFieldNameUser',
                $lFID,     'pff_lKeyID',
                false,             '',
                true,              $lTID,       'pff_lTableID',
                false,             -1,          '',
                'uf_fields'));

   }

   function stripCommas(&$strAmount){
      $strAmount = str_replace (',', '', $strAmount);
      return(true);
   }


   function remField($lTableID, $lFieldID, $enumFieldType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $lTableID = (integer)$lTableID;
      $lFieldID = (integer)$lFieldID;

      $this->load->model('personalization/muser_fields');
      $this->load->model('personalization/muser_fields_create', 'clsUFC');
      $this->load->model('admin/mpermissions',                  'perms');

      $this->clsUFC->lTableID = $lTableID;
      $this->clsUFC->loadTableViaTableID();
      $this->clsUFC->strENPTableName = $this->clsUFC->userTables[0]->strDataTableName;

      $this->clsUFC->loadSingleField($lFieldID, false);

      $this->clsUFC->removeField();

      $this->session->set_flashdata('msg', 'The field was removed');
      redirect('admin/uf_fields/view/'.$lTableID);

   }

}

/* End of file uf_tables_add_edit.php */
/* Location: ./application/controllers/admin/uf_tables_add_edit.php */