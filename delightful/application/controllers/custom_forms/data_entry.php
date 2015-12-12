<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class data_entry extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   private function bCFTestForHack($enumType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch($enumType){
         case CENUM_CONTEXT_CLIENT:
            if (!bTestForURLHack('showClients')) return(false);
            break;
         default:
            screamForHelp($enumType.': Custom forms not available nyet.<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return(true);
   }

   function addFromCForm($lParentID, $lCFID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gErrMessages, $gbShowHiddenVerifyError;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lCFID, 'custom form ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lCFID']     = $lCFID = (integer)$lCFID;
      $displayData['lParentID'] = $lParentID = (integer)$lParentID;
      $gbShowHiddenVerifyError = false;

      $gErrMessages = array();

         /*------------------------------------------------
             models/libraries/helpers
         ------------------------------------------------*/
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->model  ('custom_forms/mcustom_forms',           'cForm');
      $this->load->model  ('personalization/muser_fields',         'clsUF');
      $this->load->model  ('personalization/muser_fields_display', 'clsUFD');
      $this->load->model  ('admin/madmin_aco',                     'clsACO');
      $this->load->model  ('admin/mpermissions',                   'perms');
      $this->load->helper ('dl_util/context');
      $this->load->helper ('dl_util/custom_forms');
      $this->load->helper ('dl_util/time_date');
      $this->load->library('util/dl_date_time', '',                'clsDateTime');

         // load the custom form
      $this->cForm->loadCustomFormsViaCFID($lCFID);
      $displayData['cForm'] = $cForm = &$this->cForm->customForms[0];

      $enumType = $cForm->enumContextType;

         // now that the context is known, verify the parent ID
      verifyIDsViaType($this, $enumType, $lParentID, true);

         // custom verification ?
      $displayData['bCusVerification'] = $bCusVerification = $cForm->strVerificationModule.'' != '';

         //-----------------------------
         // validation rules
         //-----------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      if ($bCusVerification){
         $this->load->helper('path');
         $this->form_validation->set_rules('hVerify',      'Hidden verification',
                   'callback_hiddenVerify['.$cForm->strVerificationModule.','.$cForm->strVModEntryPoint.']');
      }

         // personalized tables and associated fields
      $this->cForm->loadPTablesForDisplay($lCFID, $this->clsUF);
      $displayData['utables']    = $utables    = &$this->cForm->utables;
      $displayData['lNumTables'] = $lNumTables = $this->cForm->lNumTables;
      setValidationUTables($displayData['js'], $lNumTables, $utables);

		if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');
         $this->load->model  ('util/mlist_generic', 'clsList');
         $this->load->helper ('dl_util/web_layout');

         loadSupportModels($enumType, $lParentID);

         initUTableDates($displayData['js'], $lNumTables, $utables);
         initUTableDDLs($lNumTables, $utables);

         if (validation_errors()==''){
            populateCustomTables($lNumTables, $utables, $lParentID);
            setCustomUTableDDLs($lNumTables, $utables);
         }else {
            setOnFormError($displayData);
            repopulateCustomTables($lNumTables, $utables);
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = $this->cForm->strCustomFormsPageTitleAddEdit($enumType, $cForm->strFormName);
         $displayData['strHTMLSummary'] = strContextHTML($enumType, $lParentID, $enumType);

         $displayData['errMessages']    = arrayCopy($gErrMessages);
         $displayData['title']          = CS_PROGNAME.' | Custom Forms';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = array('custom_forms/custom_form_data_entry_view');
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         saveCustomPTables($lParentID, $lNumTables, $utables);
         $this->cForm->lLogFormSave($lParentID, $lCFID);
         $this->fromWhenceYeCame($lParentID, $enumType, $cForm);
      }
   }

   function hiddenVerify($strVal, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('personalization/validate_custom_verification');
      return(customVal\hiddenVerify($strVal, $strOpts));
   }

   function verifyCustomDate($strFieldValue, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return(verifyCustomFormDate($strFieldValue, $strOpts));
   }

   function verifyDDLSelect($strFieldValue, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return(verifyCustomFormDDL($strFieldValue, $strOpts));
   }

   function verifyDDLMultiSelect($strFieldValue, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return(verifyCustomFormMultiDDL($strFieldValue, $strOpts));
   }

   function verifyCurrency($strFieldValue, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return(verifyCustomFormCurrency($strFieldValue, $strOpts));
   }

   function fromWhenceYeCame($lParentID, $enumType, &$cForm){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->session->set_flashdata('msg', 'The form <b>'.htmlspecialchars($cForm->strFormName).'</b> was updated.<br><br>'
                              .htmlspecialchars($cForm->strSubmissionText));

       switch($enumType){
         case CENUM_CONTEXT_CLIENT:
            redirect_Client($lParentID);
            break;
         default:
            screamForHelp($enumType.': Custom forms not available nyet.<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return(true);
  }


}