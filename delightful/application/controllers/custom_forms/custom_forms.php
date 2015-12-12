<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class client_forms extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }
   
   public function viewViaCID($lCID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      
      $displayData = array();
      $displayData['js'] = '';

         /*------------------------------------------------
             models/libraries/helpers
         ------------------------------------------------*/
      $this->load->model('clients/mclient_intake', 'cIntake');
      
      
         /*--------------------------
           breadcrumbs
         --------------------------*/
      $displayData['title']        = CS_PROGNAME.' | Client Ongoing Assessments';
      $displayData['pageTitle']    = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                              .' | Ongoing Assessments Forms';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'client_intake/assessments_via_cid_view';
      $this->load->vars($displayData);
      $this->load->view('template');
      
   }   
   
   
   
}
