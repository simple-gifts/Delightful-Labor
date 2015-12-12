<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class uf_user_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function userAddEdit($lTableID, $lForeignID, $lEditFieldID){
   //-----------------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------------
      global $gbDateFormatUS;

      $this->load->helper('dl_util/verify_id');
      if (!vid_bUserTableIDExists($this, $lTableID, $enumTabType)) vid_bTestFail($this, false, 'user table ID', $lTableID);
      verifyIDsViaType($this, $enumTabType, $lForeignID, false);
      if (!is_numeric($lEditFieldID)) vid_bTestFail($this, false, 'user field ID', $lEditFieldID);

      $displayData  = array();

      $displayData['lTableID']     = $lTableID     = (integer)$lTableID;
      $displayData['lForeignID']   = $lForeignID   = (integer)$lForeignID;
      $displayData['lEditFieldID'] = $lEditFieldID = (integer)$lEditFieldID;

         //-----------------------
         // load table info
         //-----------------------
      $bEditMode = $lEditFieldID > 0;
      $this->load->model('personalization/muser_fields',         'clsUF');
      $this->load->model('personalization/muser_fields_display', 'clsUFD');
      $this->load->model('admin/mpermissions',                   'perms');
      $this->load->library('util/dl_date_time', '',              'clsDateTime');
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);
      $this->load->model('admin/madmin_aco');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/context');

      $this->clsUFD->lTableID   = $lTableID;
      $this->clsUFD->lForeignID = $lForeignID;
      $this->clsUFD->loadTableViaTableID();
      $enumTType = $this->clsUFD->userTables[0]->enumTType;
      loadSupportModels($enumTType, $lForeignID);

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtxxxx');  // dummy
      if ($bEditMode){

         $this->clsUFD->loadSingleField($lEditFieldID);
         $enumFType = $this->clsUFD->fields[0]->enumFieldType;
         $strFName   = 'var'.$lEditFieldID;
         if ($enumFType == CS_FT_DATE){
            $this->form_validation->set_rules($strFName, 'Date Field', 'trim|callback_ufFieldVerifyDateValid');
         }
         if ($enumFType == CS_FT_INTEGER){
            $this->form_validation->set_rules($strFName, 'Number Field', 'trim|required|callback_stripCommas|integer');
         }
         if ($enumFType == CS_FT_CURRENCY){
            $this->form_validation->set_rules($strFName, 'Currency Field', 'trim|required|callback_stripCommas|numeric');
         }
      }

      if ($this->form_validation->run() == FALSE){


            //------------------------------------------------------
            // set form validation based on field type being edited
            //------------------------------------------------------
         if ($bEditMode){
            $this->load->helper('dl_util/web_layout');
         }

         $displayData['title']        = CS_PROGNAME.' | Personalized Fields';
         $displayData['pageTitle']    = $this->clsUFD->strBreadcrumbsTableDisplay(0);
         $displayData['nav']          = $this->mnav_brain_jar->navData();

         $displayData['strTableDisplay'] = $this->clsUFD->strEditUserTableEntries($lEditFieldID);
         $displayData['strHTMLSummary']  = $this->clsUFD->strHTMLSummary;
         $displayData['mainTemplate'] = 'admin/user_table_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->clsUFD->loadSingleField($lEditFieldID);
         $enumType = $this->clsUFD->fields[0]->enumFieldType;

         $strFieldID = 'var'.$lEditFieldID;
         switch ($enumType){
            case CS_FT_CHECKBOX:
               $varUserVal = @$_POST[$strFieldID]=='TRUE';
               break;
            case CS_FT_DATE    :
               $varUserVal = trim($_POST[$strFieldID]);
               if ($varUserVal==''){
                  $varUserVal = ' null ';
               }else {
                  MDY_ViaUserForm($varUserVal, $lMon, $lDay, $lYear, $gbDateFormatUS);
                  $varUserVal = ' "'.strMoDaYr2MySQLDate($lMon, $lDay, $lYear).'" ';
               }
               break;
            case CS_FT_DATETIME:
               break;
            case CS_FT_TEXTLONG:
            case CS_FT_TEXT255:
            case CS_FT_TEXT80:
            case CS_FT_TEXT20:
               $varUserVal = trim($_POST[$strFieldID]);
               break;
            case CS_FT_INTEGER :
               $varUserVal = (integer)$_POST[$strFieldID];
               break;
            case CS_FT_CURRENCY:
               $varUserVal = number_format($_POST[$strFieldID], 2, '.', '');
               break;
            case CS_FT_DDL:
               $varUserVal = (integer)$_POST[$strFieldID];
               break;
            default:
               screamForHelp($enumType.': invalid field type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }
         $this->clsUFD->updateUserField($varUserVal);
         redirect('admin/uf_user_edit/userAddEdit/'.$lTableID.'/'.$lForeignID.'/0');
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
   function ufFieldVerifyDateValid($strDate){
      if ($strDate==''){
         return(true);
      }else {
         return(bValidVerifyDate($strDate));
      }
   }

   function stripCommas(&$strAmount){
      $strAmount = str_replace (',', '', $strAmount);
      return(true);
   }


}