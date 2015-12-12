<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class uf_log extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }


   function addEditLogEntry($lTableID, $lLogFieldID, $lForeignID, $lLogEntryID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $displayData['lTableID']    = $lTableID    = (integer)$lTableID;
      $displayData['lLogFieldID'] = $lLogFieldID = (integer)$lLogFieldID;
      $displayData['lForeignID']  = $lForeignID  = (integer)$lForeignID;
      $displayData['lLogEntryID'] = $lLogEntryID = (integer)$lLogEntryID;
      $displayData['bNew']        = $bNew        = $lLogEntryID <= 0;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('personalization/muser_fields',         'clsUF');
      $this->load->model('personalization/muser_fields_display', 'clsUFD');
      $this->load->model ('admin/mpermissions',                  'perms');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);
      $this->load->model('admin/madmin_aco');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/context');
      
      $this->clsUFD->lTableID   = $lTableID;
      $this->clsUFD->lForeignID = $lForeignID;
      $this->clsUFD->loadTableViaTableID();
      $userTable = &$this->clsUFD->userTables[0];
      $enumTType = $userTable->enumTType;
      $this->clsUFD->setTType($enumTType);
      
      loadSupportModels($enumTType, $lForeignID);

         //---------------------------------
         // validation rules
         //---------------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtTitle', 'Log Title', 'trim|required');
      $this->form_validation->set_rules('txtLog', 'Log Entry', 'trim|required');

      if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');
         $this->load->helper('dl_util/web_layout');
         $displayData['clsForm']          = &$this->generic_form;
         $displayData['strTTypeLabel']    = $this->clsUFD->strTTypeLabel;
         $displayData['strUserTableName'] =  $userTable->strUserTableName;
         $this->clsUFD->loadFieldsGeneric(false, $lTableID, $lLogFieldID);
         $displayData['pff_strFieldNameUser'] = $this->clsUFD->fields[0]->pff_strFieldNameUser;

         if (validation_errors()==''){
            $this->clsUF->loadSingleLogEntry($lLogEntryID);
            $displayData['strTitle'] = htmlspecialchars($this->clsUF->logEntries[0]->strTitle);
            $displayData['strLog']   = htmlspecialchars($this->clsUF->logEntries[0]->strLogEntry);
         }else {
            setOnFormError($displayData);
            $displayData['strTitle'] = set_value('txtTitle');
            $displayData['strLog']   = set_value('txtLog');
         }
         $this->clsUFD->tableContext(0);
         $this->clsUFD->tableContextRecView(0);

            //------------------------------------------------
            // breadcrumbs / page setup
            //------------------------------------------------
         $displayData['title']        = CS_PROGNAME.' | Personalized Fields';
         $displayData['pageTitle']    = $this->clsUFD->strBreadcrumbsTableDisplay(0);
         $displayData['nav']          = $this->mnav_brain_jar->navData();

         $displayData['contextSummary']  = $this->clsUFD->strHTMLSummary;
         $displayData['mainTemplate'] = 'admin/user_table_log_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->clsUF->logEntries = array();
         $this->clsUF->logEntries[0] = new stdClass;
         $this->clsUF->logEntries[0]->lKeyID      = $lLogEntryID;
         $this->clsUF->logEntries[0]->lFieldID    = $lLogFieldID;
         $this->clsUF->logEntries[0]->lForeignID  = $lForeignID;
         $this->clsUF->logEntries[0]->strTitle    = trim($_POST['txtTitle']);
         $this->clsUF->logEntries[0]->strLogEntry = trim($_POST['txtLog']);

         if ($bNew){
            $this->clsUF->addNewLogEntry();
            $this->session->set_flashdata('msg', 'Log entry added');
         }else {
            $this->clsUF->updateLogEntry();
            $this->session->set_flashdata('msg', 'Log entry updated');
         }
         redirect('admin/uf_log/viewLogEntries/'.$lTableID.'/'.$lLogFieldID.'/'.$lForeignID);
      }
   }
   
   function viewLogEntries($lTableID, $lLogFieldID, $lForeignID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $displayData['lTableID']    = $lTableID    = (integer)$lTableID;
      $displayData['lLogFieldID'] = $lLogFieldID = (integer)$lLogFieldID;
      $displayData['lForeignID']  = $lForeignID  = (integer)$lForeignID;
      
         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('personalization/muser_fields',         'clsUF');
      $this->load->model('personalization/muser_fields_display', 'clsUFD');
      $this->load->model('admin/mpermissions',                   'perms');
      $this->load->model('admin/madmin_aco');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);
      $this->load->model('admin/muser_accts', 'clsUser');
      $this->load->helper('dl_util/context');

      $this->clsUFD->lTableID = $lTableID;
      $this->clsUFD->lForeignID = $lForeignID;
      $this->clsUFD->loadTableViaTableID();
      $userTable = &$this->clsUFD->userTables[0];
      $enumTType = $userTable->enumTType;      
      $this->clsUFD->loadFieldsGeneric(false, $lTableID, $lLogFieldID);
//      $this->clsUF->loadSupportModels($enumTType, $lForeignID);
      loadSupportModels($enumTType, $lForeignID);

      $this->clsUFD->tableContext(0);
      $this->clsUFD->tableContextRecView(0);      
      
   
      $displayData['strTTypeLabel']    = $this->clsUFD->strTTypeLabel;
      $displayData['strUserTableName'] =  $userTable->strUserTableName;
      $displayData['pff_strFieldNameUser'] = $this->clsUFD->fields[0]->pff_strFieldNameUser;
      
      $this->clsUFD->bShowViewAllLog   = false;
      $this->clsUFD->bShowLogEditLinks = true;
      $this->clsUFD->lMaxLogDisplayLen = 999999;
      $displayData['strLogEntries'] = $this->clsUFD->logDisplay(
                           $this->clsUFD->enumTType, $lLogFieldID, $lTableID, $lForeignID, 999999);
   
         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['title']        = CS_PROGNAME.' | Personalized Fields';
      $displayData['pageTitle']    = $this->clsUFD->strBreadcrumbsTableDisplay(0);
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['contextSummary']  = $this->clsUFD->strHTMLSummary;
      $displayData['mainTemplate'] = 'admin/user_table_log_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function removeLogEntry($lTableID, $lLogFieldID, $lForeignID, $lLogEntryID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lTableID    = (integer)$lTableID;
      $lLogFieldID = (integer)$lLogFieldID;
      $lForeignID  = (integer)$lForeignID;
      $lLogEntryID = (integer)$lLogEntryID;
      
      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->model('admin/mpermissions',           'perms');
      
      $this->clsUF->logEntries = array();
      $this->clsUF->logEntries[0] = new stdClass;
      $this->clsUF->logEntries[0]->lKeyID = $lLogEntryID;
      $this->clsUF->removeLogEntry();
      
      $this->session->set_flashdata('msg', 'Log entry removed');
      redirect('admin/uf_log/viewLogEntries/'.$lTableID.'/'.$lLogFieldID.'/'.$lForeignID);
   }
      
      
}