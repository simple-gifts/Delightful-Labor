<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class demo extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function ajax_test(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $displayData['js']      = '';
   
      $this->load->library('js_build/ajax_support');
      $this->load->helper ('dl_util/web_layout');
      
         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('ddlNames',  'Donor',     'trim|required');
      
      
		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
      
            //-------------------------------
            // Acct/Camp and ajax interface
            //-------------------------------
         $clsAjax = new ajax_support;
         $displayData['js'] .= $clsAjax->showCreateXmlHTTPObject();
         $displayData['js'] .= $clsAjax->peopleBizNames('showResult', 'selNames');
         
         $displayData['js'] .= '
                 <script>
                    function populateSearch(){
                       var objSearchTxt = document.getElementById("donorName");
                       var objDDL    = document.getElementById("selNames");
                       objSearchTxt.value = objDDL.options[objDDL.selectedIndex].text;
                       
//document.getElementById(\'test\').options[document.getElementById(\'test\').selectedIndex].text;                       
                       
//                       alert("populate search: "+objDDL.value);
                    }
                 </script>';

         $this->load->library('generic_form');
         
         if (validation_errors()==''){
         }else {
            setOnFormError($displayData);
         
         }
         
         $displayData['pageTitle']   = 'Ajax Demo';
         $displayData['mainTemplate']   = array('auctions/ajax_demo_view');
         
         $displayData['title']          = CS_PROGNAME.' | Auctions';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $this->load->vars($displayData);
         $this->load->view('template');
         
         
      }else {
      }
   
   }
   
}
