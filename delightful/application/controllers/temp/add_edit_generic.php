<?php
/*
   Bare-bones controller for editing a simple form, using
   CodeIgniter's form verification features and custom callback verification
*/
class add_edit_generic extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEditGeneric($lID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      global $gbDateFormatUS;
      
      if (!bTestForURLHack('test')) return;
      
      $this->load->helper('dl_util/verify_id');
      if ($lID.'' != '0') verifyID($this, $ID, 'test ID');      
   
      $displayData = array();
      $displayData['id'] = $lID = (integer)$lID;
      $displayData['js'] = '';

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('dl_util/time_date');  // for date verification      
      $this->load->library('generic_form');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper('dl_util/web_layout');
      
         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtField1', 'My Field', 'trim|required');
      $this->form_validation->set_rules('txtDate',   'My date',  'trim|required'
                                                                    .'|callback_verifyDateValid'
                                                                    .'|callback_verifyDatePast');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');
      
            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['formData']->txtField1 = htmlspecialchars($myModel->strField1);
            $displayData['formData']->txtDate   = $myModel->strDate;
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtField1    = set_value('txtField1');
            $displayData['formData']->txtDate      = set_value('txtDate');
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/home', 'Home', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').'  Record';

         $displayData['title']          = CS_PROGNAME.' | My Record';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'my_record/my_record_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $strField1 = trim($_POST['txtField1']);
         $strDate   = trim($_POST['txtDate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $dteMyDate = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);
      
            //------------------------------------
            // update db tables and return
            //------------------------------------
         if ($bNew){
            $lID = $myModel->addNewRecord($strField1, $dteMyDate);
            $this->session->set_flashdata('msg', 'Record added');
         }else {
            $myModel->updateRecord($lID, $strField1, $dteMyDate);
            $this->session->set_flashdata('msg', 'Record updated');
         }         
         redirect('myRecords/myRecord/view/'.$lID);         
      }
   }
 
      //-----------------------------
      // verification routines
      //-----------------------------
   function verifyDateValid($strDate){
      if (bValidVerifyDate($strDate)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyDateValid', 'Bad date.... been there');
         return(false);
      }
   }

   function verifyDatePast($strDate){
      return(bValidVerifyNotFuture($strDate));
   } 
}  