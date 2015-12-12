<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class user_acct extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($lUserID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $gbVolLogin;
      $lUserID = (integer)$lUserID;
      
      if ($gbVolLogin) return;

         // someone hacking the url?
      if ($lUserID != $glUserID){
          $this->session->set_flashdata('error', '<b>ERROR:</b> User ID not valid!</font>');
          redirect_More();
      }
      $this->load->helper('user/user_acct');
      commonAcctView($lUserID, false, $displayData);
   }

   function edit($lUserID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $lUserID = (integer)$lUserID;

         // someone hacking the url?
      if ($lUserID != $glUserID){
          $this->session->set_flashdata('error', '<b>ERROR:</b> User ID not valid!</font>');
          redirect_More();
      }
      $this->load->helper('user/user_acct');
      commonAcctEdit($lUserID, false);
   }

      //-------------------------------
      // verification callbacks
      //-------------------------------
   function verifyUniqueUserID($strUserName, $id){
      $id = (integer)$id;
      $strUserName = xss_clean(trim($strUserName));
      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                $strUserName, 'us_strUserName',
                $id,   'us_lKeyID',
                true,  'us_bInactive',
                false, null, null,
                false, null, null,
                'admin_users')){
         return(false);
      }else {
         return(true);
      }
   }
   
   function verifyPWordsMatch($strPWord2){
      $strPWord1 =  trim($_POST['txtPWord1']);
      return($strPWord1==$strPWord2);
   }

   function pw(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $gbVolLogin;
      
      $displayData = array();

      $this->load->model('admin/muser_accts', 'clsUser');
      $this->load->helper('dl_util/web_layout');
      $this->load->library('generic_form');

         //--------------------------
         // validation rules
         //--------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtPWord',  'Current Password',   'trim|required|callback_verifyGoodPW');
      $this->form_validation->set_rules('txtPWord1', 'Password',           'trim|required|callback_verifyPWordMatch');
      $this->form_validation->set_rules('txtPWord2', 'Password (again)',   'trim');

      if ($this->form_validation->run() == FALSE){
         if (validation_errors()==''){
         }else {
            setOnFormError($displayData);
         }
         
            //--------------------------
            // breadcrumbs
            //--------------------------
         if ($gbVolLogin){
            $displayData['pageTitle']    = 'Change Password';
         }else {
            $displayData['pageTitle']    = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                                      .' | Change Password';
         }

         $displayData['title']          = CS_PROGNAME.' | More';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'more/change_password_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $strPWord = trim($_POST['txtPWord1']);
         $this->clsUser->changePWord($glUserID, $strPWord);
         $this->session->set_flashdata('msg', 'Your password was changed.');
         if ($gbVolLogin){
            redirect_VolLoginGeneric();
         }else {
            redirect_userAcct();
         }
      }      
   }
   
   function verifyGoodPW($strPW){
      global $glUserID;
      return($this->clsUser->bPWValid($glUserID, $strPW));
   }
   
   function verifyPWordMatch($strPW1){
      return($_POST['txtPWord1'] == $_POST['txtPWord2']);
   }

}
