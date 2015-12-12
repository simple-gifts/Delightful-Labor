<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class custom_form_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function view($enumType){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return(false);

      $displayData = array();
      $displayData['js'] = '';
      $displayData['enumType'] = $enumType;

         /*------------------------------------------------
             models/libraries/helpers
         ------------------------------------------------*/
      $this->load->model ('custom_forms/mcustom_forms',   'cForm');
      $this->load->model ('admin/mpermissions',           'perms');
      $this->load->model ('personalization/muser_fields', 'clsUF');
      $this->load->helper('dl_util/context');

         // load the custom forms
      $this->cForm->loadCustomFormsViaType($enumType);

      $displayData['lNumCustomForms'] = $lNumCustomForms = $this->cForm->lNumCustomForms;
      $displayData['customForms']     = $cforms = &$this->cForm->customForms;
      contextLabels($enumType, $displayData['contextLabel']);

         /*-------------------------------------
            stripes
         -------------------------------------*/
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         /*--------------------------
           breadcrumbs
         --------------------------*/
      $displayData['title']        = CS_PROGNAME.' | Custom Forms';
      $displayData['pageTitle']    = $this->strCustomFormsPageTitle($enumType);
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'custom_forms/forms_list_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function strCustomFormsPageTitle($enumType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch($enumType){
         case CENUM_CONTEXT_CLIENT:
            $strPageTitle = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"').' | Custom Forms/Clients';
            break;
         default:
            screamForHelp($enumType.': Custom forms not available nyet.<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($strPageTitle);
   }

   function addEditCForm($lCFID, $enumType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapter, $glChapterID;

      if (!bTestForURLHack('adminOnly')) return(false);

      $this->load->helper('dl_util/verify_id');
      if ($lCFID.'' !='0') verifyID($this, $lCFID, 'custom form ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lCFID'] = $lCFID = (integer)$lCFID;

      $displayData['bNew'] = $bNew = $lCFID <= 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('custom_forms/mcustom_forms',   'cForm');
      $this->load->model ('personalization/muser_fields', 'clsUF');
      $this->load->model ('admin/mpermissions',           'perms');
      $this->load->model ('groups/mgroups',               'groups');

      $this->load->helper('groups/groups');
      $this->load->helper('dl_util/context');
      $this->load->helper('img_docs/image_doc');
      $this->load->helper('img_docs/link_img_docs');
      $this->load->helper('js/clear_set_check_on_check');
      $this->load->helper('dl_util/custom_forms');
      $this->load->helper('personalization/validate_custom_verification');

      $displayData['enumType'] = $enumType;
      contextLabels($enumType, $displayData['contextLabel']);

      $displayData['js'] .= clearCheckOnUnCheck();
      $displayData['js'] .= setCheckOnCheck();

      $this->cForm->loadCustomFormsViaCFID($lCFID);
      $cForm = &$this->cForm->customForms[0];

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

      $this->load->helper('js/check_boxes_in_div');
      $displayData['js'] .= checkUncheckInDiv();

         //-----------------------------
         // validation rules
         //-----------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtCustomFormName',     'Name of the Custom Form',
                                  'trim|required|callback_testDupForm['.$enumType.','.$lCFID.']');
		$this->form_validation->set_rules('txtDescription',        'Description', 'trim');
		$this->form_validation->set_rules('txtBannerTitle',        'Banner Text: Custom Form Title', 'trim|required');
		$this->form_validation->set_rules('txtIntro',              'Custom Form Introductory Text', 'trim|required');
		$this->form_validation->set_rules('txtSubmissionText',     'Text Shown After Submission', 'trim|required');
      $this->form_validation->set_rules('ddlParentGroup',        'Parent Group ID',   'trim');
		$this->form_validation->set_rules('txtVerificationModule', 'Validation Module', 'callback_verifyVerMod');
		$this->form_validation->set_rules('txtVModEntryPoint',     'Validation Mod Entry Point', 'callback_verifyVModEntry');

         /*
            personalized  tables
         */
      $this->clsUF->enumTType = $enumType;
      $this->clsUF->loadTablesViaTType();
      $displayData['lNumTables'] = $lNumTables = $this->clsUF->lNumTables;
      if ($lNumTables > 0){
         $displayData['userTables'] = $userTables = &$this->clsUF->userTables;

         foreach ($userTables as $utable){
            $this->clsUF->lTableID = $lTableID = $utable->lKeyID;
            $this->clsUF->loadTableFields();

               // exclude log fields
            if ($this->clsUF->lNumFields == 0){
                  --$displayData['lNumTables'];
            }else {
               foreach ($this->clsUF->fields as $field){
                  if ($field->enumFieldType==CS_FT_LOG){
                     --$this->clsUF->lNumFields;
                  }else {
                     $lFieldID = $field->pff_lKeyID;
                     $field->strFNShow     = 'chkUFShow_'.$lTableID.'_'.$lFieldID;
                     $field->strFNRequired = 'chkUFReq_' .$lTableID.'_'.$lFieldID;
                     $this->cForm->bShowRequiredUFFields($lCFID, $lTableID, $lFieldID, $field->bShow, $field->bRequired);
                     $this->form_validation->set_rules($field->strFNShow, 'Personalized Field/Show', '');
                     $this->form_validation->set_rules($field->strFNRequired, 'Personalized Field/Required', '');
                  }
               }
            }

            $utable->lNumFields = $lNumFields = $this->clsUF->lNumFields;
            if ($lNumFields > 0){
               $utable->fields = arrayCopy($this->clsUF->fields);
            }
            $utable->strTableLabel = $this->cForm->strPublicUFTable($lTableID, $lCFID, $utable->strUserTableName);
         }
      }

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');
         $this->load->model  ('util/mlist_generic', 'clsList');
         $this->load->helper ('dl_util/web_layout');

         $displayData['cForm'] = $cForm;

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($bNew){
               $displayData['formData']->txtCustomFormName      = '';
               $displayData['formData']->txtDescription         = '';
               $displayData['formData']->txtBannerTitle         = '';
               $displayData['formData']->txtIntro               = '';
               $displayData['formData']->txtSubmissionText      = '';
               $displayData['formData']->txtVerificationModule  = '';
               $displayData['formData']->txtVModEntryPoint      = '';
            }else {
               $displayData['formData']->txtCustomFormName      = htmlspecialchars($cForm->strFormName);
               $displayData['formData']->txtDescription         = htmlspecialchars($cForm->strDescription);
               $displayData['formData']->txtBannerTitle         = htmlspecialchars($cForm->strBannerTitle);
               $displayData['formData']->txtIntro               = htmlspecialchars($cForm->strIntro);
               $displayData['formData']->txtSubmissionText      = htmlspecialchars($cForm->strSubmissionText);
               $displayData['formData']->txtVerificationModule  = htmlspecialchars($cForm->strVerificationModule);
               $displayData['formData']->txtVModEntryPoint      = htmlspecialchars($cForm->strVModEntryPoint);
            }

               // client group ddl
            $displayData['formData']->ddlParentGroup =
                       $this->groups->strDDLActiveGroupEntries('ddlParentGroup', $enumType, $cForm->lParentGroupID, true);
            $displayData['formData']->lNumParentGroups = $this->groups->lNumGroupList;
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtCustomFormName      = set_value('txtCustomFormName');
            $displayData['formData']->txtDescription         = set_value('txtDescription');
            $displayData['formData']->txtBannerTitle         = set_value('txtBannerTitle');
            $displayData['formData']->txtIntro               = set_value('txtIntro');
            $displayData['formData']->txtSubmissionText      = set_value('txtSubmissionText');
            $displayData['formData']->txtVerificationModule  = set_value('txtVerificationModule');
            $displayData['formData']->txtVModEntryPoint      = set_value('txtVModEntryPoint');

               // parent group ddl
            $displayData['formData']->ddlParentGroup =
                       $this->groups->strDDLActiveGroupEntries('ddlParentGroup', $enumType, set_value('ddlParentGroup'), true);
            $displayData['formData']->lNumParentGroups = $this->groups->lNumGroupList;

            if ($lNumTables > 0){
               foreach ($userTables as $utable){
                  if ($utable->lNumFields > 0){

                        // exclude log fields
                     foreach ($utable->fields as $field){
                        if ($field->enumFieldType!=CS_FT_LOG){
                           $field->bShow     = set_value($field->strFNShow) =='true';
                           $field->bRequired = set_value($field->strFNRequired) =='true';
                        }
                     }
                  }
               }
            }
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = $this->cForm->strCustomFormsPageTitleAddEdit($enumType, ($bNew ? 'Add New' : 'Edit').'  Form');

         $displayData['title']          = CS_PROGNAME.' | Custom Forms';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'custom_forms/custom_form_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
            // internal fields
         $cForm->strFormName           = trim($_POST['txtCustomFormName']);
         $cForm->strDescription        = trim($_POST['txtDescription']);
         $cForm->strVerificationModule = trim($_POST['txtVerificationModule']);
         $cForm->strVModEntryPoint     = trim($_POST['txtVModEntryPoint']);
         $cForm->lParentGroupID        = (int)@$_POST['ddlParentGroup'];
         if ($cForm->lParentGroupID <= 0) $cForm->lParentGroupID = null;


            // top banner
         $cForm->strBannerTitle        = trim($_POST['txtBannerTitle']);
         $cForm->strIntro              = trim($_POST['txtIntro']);
         $cForm->strSubmissionText     = trim($_POST['txtSubmissionText']);

            // personalized tables
         if ($lNumTables > 0){
            foreach ($userTables as $utable){
               if ($utable->lNumFields > 0){

                  foreach ($utable->fields as $field){
                     if ($field->enumFieldType!=CS_FT_LOG){
                        $field->bShow     = trim(@$_POST[$field->strFNShow]) =='true';
                        $field->bRequired = trim(@$_POST[$field->strFNRequired]) =='true';
                     }
                  }
               }
            }
         }

         if ($bNew){
            $lCFID = addNewCustomForm($enumType, $cForm);
         }else {
            updateCustomForm($enumType, $lCFID, $cForm);
         }
         updateFormPTableFields($enumType, $lCFID, $lNumTables, $userTables);

         $this->session->set_flashdata('msg', 'The custom form was '.($bNew ? 'added' : 'updated').'.');
         redirect('custom_forms/custom_form_add_edit/view/'.$enumType);
      }
   }

   function testDupForm($strFormName, $strOpts){
      $opts = explode(',', $strOpts);
      $enumType = trim($opts[0]);
      $lCFID = (int)$opts[1];

      $this->load->model('util/mverify_unique', 'clsUnique');

      if (!$this->clsUnique->bVerifyUniqueText(
                trim($strFormName), 'cf_strFormName',
                $lCFID,              'cf_lKeyID',
                true,                'cf_bRetired',
                true, $enumType,     'cf_enumContextType',
                false, null, null,
                'custom_forms')){
         $this->form_validation->set_message('testDupForm', 'This form name is already in use.');
         return(false);
      }else {
         return(true);
      }
   }

   function removeCForm($lCFID, $enumType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return(false);

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lCFID, 'custom form ID');
      $lCFID = (int)$lCFID;

         /*------------------------------------------------
             models/libraries/helpers
         ------------------------------------------------*/
      $this->load->helper('dl_util/custom_forms');
      $this->load->model('custom_forms/mcustom_forms', 'cForm');
      $this->load->model ('admin/mpermissions',           'perms');
      $this->load->model ('personalization/muser_fields', 'clsUF');

      $this->cForm->loadCustomFormsViaCFID($lCFID);
      $strFormName = $this->cForm->customForms[0]->strFormName;
      removeCustomForm($enumType, $lCFID);

      $this->session->set_flashdata('msg', 'The custom form <b>'.htmlspecialchars($strFormName).'</b> was removed.');
      redirect('custom_forms/custom_form_add_edit/view/'.$enumType);
//      redirect_CustomForms();
   }

   function verifyVerMod($strMod){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
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



}