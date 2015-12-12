<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class client_verify extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function status(){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      
      $displayData = array();      
      $displayData['js'] = '';
      $displayData['bCorrected'] = false;
      
         //-------------------------------
         // models / helpers / libraries
         //-------------------------------
      $this->load->model('clients/mclient_status', 'cStat');
      
      $this->cStat->verifyClientStatus($displayData['lNumGood'], 
                      $displayData['lNumBad'], $displayData['badStatus']);
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']    = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                              .' | Verify Client Status';

      $displayData['title']        = CS_PROGNAME.' | Clients';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate'] = 'client/status_verify_view';

      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   function statusCorrect(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------      
      if (!bTestForURLHack('showClients')) return;
      
      $displayData = array();      
      $displayData['js'] = '';
      $displayData['bCorrected'] = true;
      
         //-------------------------------
         // models / helpers / libraries
         //-------------------------------
      $this->load->model('clients/mclient_status', 'cStat');
      
      $this->cStat->verifyClientStatus($displayData['lNumGood'], 
                      $displayData['lNumBad'], $displayData['badStatus']);
      if ($displayData['lNumBad'] > 0){
         $this->cStat->correctClientStatus($displayData['badStatus']);
      }         
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']    = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                              .' | '.anchor('clients/client_verify/status', 'Verify Client Status', 'class="breadcrumb"')
                              .' | Correct Client Status';

      $displayData['title']        = CS_PROGNAME.' | Clients';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate'] = 'client/status_verify_view';

      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   
}
