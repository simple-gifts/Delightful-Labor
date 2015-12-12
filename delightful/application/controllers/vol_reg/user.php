<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class user extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }
   
   function landing(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
   
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']    = 'Your volunteer account';

      $displayData['title']          = CS_PROGNAME.' | Volunteer Account';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'vol_reg/vol_landing_view';
      $this->load->vars($displayData);
      $this->load->view('template');   
   }   
   
}
