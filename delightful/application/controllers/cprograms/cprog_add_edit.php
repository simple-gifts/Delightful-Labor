<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cprog_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function addEdit($lCProgID='', $lCloneSourceID=0){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $gstrFormatDatePicker, $gbDateFormatUS;

      if (!bTestForURLHack('adminOnly')) return;

      $this->load->helper('dl_util/verify_id');
      if ($lCProgID.'' != '0') verifyID($this, $lCProgID, 'client program ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lCProgID']       = $lCProgID       = (integer)$lCProgID;
      $displayData['bNew']           = $bNew           = $lCProgID <= 0;
      $displayData['lCloneSourceID'] = $lCloneSourceID = (int)$lCloneSourceID;
      $displayData['bClone']         = $bClone         = $lCloneSourceID > 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('client_features/mcprograms',   'cprograms');
      $this->load->model ('personalization/muser_fields', 'clsUF');
      $this->load->model ('personalization/muser_schema', 'cUFSchema');
      $this->load->model ('admin/mpermissions',           'perms');
      $this->load->helper('dl_util/time_date');  // for date verification
      $this->load->helper('dl_util/util_db');
      $this->load->helper('personalization/validate_custom_verification');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper ('dl_util/web_layout');

         // load the client program
      if ($bClone){
         $this->cprograms->loadClientProgramsViaCPID($lCloneSourceID);
         $displayData['strSourceProgName'] = $this->cprograms->cprogs[0]->strProgramName;
         $strBlockLabel = 'Clone Client Program';
      }else {
         $this->cprograms->loadClientProgramsViaCPID($lCProgID);
         $strBlockLabel = ($bNew ? 'Add New' : 'Update').' Client Program';
      }
      $displayData['strBlockLabel'] = $strBlockLabel;
      $cprog = &$this->cprograms->cprogs[0];

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtProgramName',   'Program Name',  'trim|required|callback_verifyUniqueClientProg['.$lCProgID.']');
		$this->form_validation->set_rules('txtEnrollLabel',   'Enrollment Label',  'trim|required');
		$this->form_validation->set_rules('txtAttendLabel',   'Attendance Label',  'trim|required');
		$this->form_validation->set_rules('txtDescription',   'Description',   'trim');
		$this->form_validation->set_rules('chkHidden',        'Hidden',        'trim');
		$this->form_validation->set_rules('chkETableReadOnly','ETable Read Only', 'trim');
		$this->form_validation->set_rules('chkATableReadOnly','ATable Read Only', 'trim');
      $this->form_validation->set_rules('txtStartDate',     'Starting Date', 'trim|required'
                                                                    .'|callback_verifyDateValid');
      $this->form_validation->set_rules('txtEndDate',       'Ending Date',   'trim|required'
                                                                    .'|callback_verifyDateValid|callback_verifyCBH');

		$this->form_validation->set_rules('chkMentorMentee',  'Mentor/Mentee',           'trim');

		$this->form_validation->set_rules('txtE_VerificationModule', 'Enrollment Validation File',         'callback_verifyEVerMod');
		$this->form_validation->set_rules('txtE_VModEntryPoint',     'Enrollment Validation Entry Point',  'callback_verifyEVModEntry');
		$this->form_validation->set_rules('txtA_VerificationModule', 'Attendance Validation File',         'callback_verifyAVerMod');
		$this->form_validation->set_rules('txtA_VModEntryPoint',     'Attendance Validation Entry Point',  'callback_verifyAVModEntry');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($bClone){
               $cprog->strProgramName .= ' (clone)';
            }
            $displayData['formData']->txtProgramName  = htmlspecialchars($cprog->strProgramName.'');
            $displayData['formData']->txtEnrollLabel  = htmlspecialchars($cprog->strEnrollmentLabel.'');
            $displayData['formData']->txtAttendLabel  = htmlspecialchars($cprog->strAttendanceLabel.'');
            $displayData['formData']->txtDescription  = htmlspecialchars($cprog->strDescription.'');
            $displayData['formData']->bHidden         = $cprog->bHidden;
            $displayData['formData']->bETableReadOnly = $cprog->bETableReadOnly;
            $displayData['formData']->bATableReadOnly = $cprog->bATableReadOnly;

            $displayData['formData']->txtE_VerificationModule = htmlspecialchars($cprog->strE_VerificationModule.'');
            $displayData['formData']->txtE_VModEntryPoint     = htmlspecialchars($cprog->strE_VModEntryPoint.'');
            $displayData['formData']->txtA_VerificationModule = htmlspecialchars($cprog->strA_VerificationModule.'');
            $displayData['formData']->txtA_VModEntryPoint     = htmlspecialchars($cprog->strA_VModEntryPoint.'');

               // mentor support
            $displayData['formData']->bMentorMentee  = $cprog->bMentorMentee;

            if (is_null($cprog->dteMysqlStart)){
               $displayData['formData']->txtStartDate = '';
            }else {
               $displayData['formData']->txtStartDate = strNumericDateViaMysqlDate($cprog->dteMysqlStart, $gbDateFormatUS);
            }
            if (is_null($cprog->dteMysqlEnd)){
               $displayData['formData']->txtEndDate = '';
            }else {
               $displayData['formData']->txtEndDate = strNumericDateViaMysqlDate($cprog->dteMysqlEnd, $gbDateFormatUS);
            }
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtProgramName  = set_value('txtProgramName');
            $displayData['formData']->txtDescription  = set_value('txtDescription');
            $displayData['formData']->txtEnrollLabel  = set_value('txtEnrollLabel');
            $displayData['formData']->txtAttendLabel  = set_value('txtAttendLabel');
            $displayData['formData']->txtStartDate    = set_value('txtStartDate');
            $displayData['formData']->txtEndDate      = set_value('txtEndDate');
            $displayData['formData']->bHidden         = set_value('chkHidden')=='true';
            $displayData['formData']->bETableReadOnly = set_value('chkETableReadOnly')=='true';
            $displayData['formData']->bATableReadOnly = set_value('chkATableReadOnly')=='true';

            $displayData['formData']->txtE_VerificationModule = set_value('txtE_VerificationModule');
            $displayData['formData']->txtE_VModEntryPoint     = set_value('txtE_VModEntryPoint');
            $displayData['formData']->txtA_VerificationModule = set_value('txtA_VerificationModule');
            $displayData['formData']->txtA_VModEntryPoint     = set_value('txtA_VModEntryPoint');

            $displayData['formData']->bMentorMentee  = set_value('chkMentorMentee')=='true';
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']   = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                .' | '.anchor('cprograms/cprograms/overview', 'Client Programs', 'class="breadcrumb"')
                                .' | '.$strBlockLabel;

         $displayData['title']          = CS_PROGNAME.' | Client Program';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'cprograms/cprog_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $cprog->strProgramName     = trim($_POST['txtProgramName']);
         $cprog->strEnrollmentLabel = trim($_POST['txtEnrollLabel']);
         $cprog->strAttendanceLabel = trim($_POST['txtAttendLabel']);
         $cprog->strDescription     = trim($_POST['txtDescription']);
         $cprog->bHidden            = trim(@$_POST['chkHidden'])=='true';
         $cprog->bETableReadOnly    = trim(@$_POST['chkETableReadOnly'])=='true';
         $cprog->bATableReadOnly    = trim(@$_POST['chkATableReadOnly'])=='true';

         $cprog->bMentorMentee  = trim(@$_POST['chkMentorMentee'])=='true';
         
         $cprog->strE_VerificationModule = trim($_POST['txtE_VerificationModule']);
         $cprog->strE_VModEntryPoint     = trim($_POST['txtE_VModEntryPoint']);
         $cprog->strA_VerificationModule = trim($_POST['txtA_VerificationModule']);
         $cprog->strA_VModEntryPoint     = trim($_POST['txtA_VModEntryPoint']);         

         $strDate   = trim($_POST['txtStartDate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $cprog->dteMysqlStart = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);

         $strDate   = trim($_POST['txtEndDate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $cprog->dteMysqlEnd = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);

            //------------------------------------
            // update db tables and return
            //------------------------------------
         if ($bNew){
            $this->load->model('personalization/muser_fields', 'clsUF');
            $this->load->model('personalization/muser_fields_create', 'clsUFC');
            $lCProgID = $this->cprograms->addNewCProgram();
            if ($bClone){
               $this->load->model('personalization/muser_fields',        'clsUF');
               $this->load->model('personalization/muser_fields_create', 'clsUFC');
               $this->load->model('personalization/muser_clone',         'cUFClone');

               $this->cUFClone->cloneCProgram($lCloneSourceID, $lCProgID);
               $this->session->set_flashdata('msg', 'Client program cloned');
            }else {
               $this->session->set_flashdata('msg', 'New client program added');
            }
         }else {
            $this->cprograms->updateCProgram($lCProgID);
            $this->session->set_flashdata('msg', 'Client program record updated');
         }
         redirect('cprograms/cprog_record/view/'.$lCProgID);
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
	function verifyEVerMod($strMod){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (customVal\verifyVerMod($strMod, $strErr)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyEVerMod', $strErr);
         return(false);
      }
   }

	function verifyEVModEntry($strEntry){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strMod    = trim(@$_POST['txtE_VerificationModule']);
      if (customVal\verifyVModEntry($strEntry, $strMod, $strErr)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyEVModEntry', $strErr);
         return(false);
      }
   }

	function verifyAVerMod($strMod){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (customVal\verifyVerMod($strMod, $strErr)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyAVerMod', $strErr);
         return(false);
      }
   }
	function verifyAVModEntry($strEntry){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strMod    = trim(@$_POST['txtA_VerificationModule']);
      if (customVal\verifyVModEntry($strEntry, $strMod, $strErr)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyAVModEntry', $strErr);
         return(false);
      }
   }

   function verifyVolTableSel($strDDL){
      $bMentorProg = @$_POST['chkMentorMentee']=='true';
      if ($bMentorProg){
         if ((int)$strDDL <= 0){
            $this->form_validation->set_message('verifyVolTableSel', 'Please select a table.');
            return(false);
         }else {
            return(true);
         }
      }else {
         return(true);
      }
   }

   function verifyDateValid($strDate){
      if (bValidVerifyDate($strDate)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyDateValid', 'The date you entered is not valid.');
         return(false);
      }
   }

   function verifyCBH($strEDate){
   // CBH: cart before horse
      if (bVerifyCartBeforeHorse(trim($_POST['txtStartDate']), $strEDate)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyCBH', 'The end date is before the start date!');
         return(false);
      }
   }

   function verifyUniqueClientProg($strProgName, $id){
      $id = (integer)$id;
      $strProgName = xss_clean(trim($strProgName));
      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                $strProgName, 'cp_strProgramName',
                $id,   'cp_lKeyID',
                true,  'cp_bRetired',
                false, null, null,
                false, null, null,
                'cprograms')){
         $this->form_validation->set_message('verifyUniqueClientProg', 'The <b>Client Program Name</b> is already being used.');
         return(false);
      }else {
         return(true);
      }
   }

   function remove($lCProgID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lCProgID, 'client program ID');

      $lCProgID = (int)$lCProgID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model('client_features/mcprograms',                'cprograms');
      $this->load->model('admin/mpermissions',                  'perms');
      $this->load->model('personalization/muser_fields',        'clsUF');
      $this->load->model('personalization/muser_fields_create', 'clsUFC');

      $this->cprograms->loadClientProgramsViaCPID($lCProgID);
      $cprog = &$this->cprograms->cprogs[0];
      $this->cprograms->removeCProgram();

      $this->session->set_flashdata('msg', 'Client program <b>'.htmlspecialchars($cprog->strProgramName).'</b> was removed.');
      redirect('cprograms/cprograms/overview');
   }

   function clone01($lClientProg){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      if (! verifyID($this, $lClientProg, 'client program ID')) return;

      $lClientProg = (int)$lClientProg;

      $this->addEdit(0, $lClientProg);
   }

   function mentorFields($lCProgID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;

      $this->load->helper('dl_util/verify_id');
      if ($lCProgID.'' != '0') verifyID($this, $lCProgID, 'client program ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lCProgID']       = $lCProgID       = (integer)$lCProgID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('client_features/mcprograms',   'cprograms');
      $this->load->model ('personalization/muser_fields', 'clsUF');
      $this->load->model ('personalization/muser_schema', 'cUFSchema');
      $this->load->model ('admin/mpermissions',           'perms');
      $this->load->helper('dl_util/util_db');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper ('dl_util/web_layout');

      $this->cprograms->loadClientProgramsViaCPID($lCProgID);
      $cprog = &$this->cprograms->cprogs[0];

         // mentor/mentee support
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$cprog   <pre>');
echo(htmlspecialchars( print_r($cprog, true))); echo('</pre></font><br>');
// ------------------------------------- */

      $this->cprograms->initMentorMentee($cprog->lMMVolMRTableID);
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$this->cprograms->UFVschema   <pre>');
echo(htmlspecialchars( print_r($this->cprograms->UFVschema, true))); echo('</pre></font><br>');
// ------------------------------------- */

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtProgramName',  'Program Name',  'trim|required|callback_verifyUniqueClientProg['.$lCProgID.']');
		$this->form_validation->set_rules('txtDescription',  'Description',   'trim');
		$this->form_validation->set_rules('chkHidden',       'Hidden',        'trim');
      $this->form_validation->set_rules('txtStartDate',    'Starting Date', 'trim|required'
                                                                    .'|callback_verifyDateValid');
      $this->form_validation->set_rules('txtEndDate',      'Ending Date',   'trim|required'
                                                                    .'|callback_verifyDateValid|callback_verifyCBH');

		$this->form_validation->set_rules('chkMentorMentee', 'Mentor/Mentee',           'trim');
      $this->form_validation->set_rules('ddlUFVSingle',    'Volunteer Single Table',  'callback_verifyVolTableSel');
      $this->form_validation->set_rules('ddlUFVMulti',     'Volunteer Multi-Table',   'callback_verifyVolTableSel');


   }


}
