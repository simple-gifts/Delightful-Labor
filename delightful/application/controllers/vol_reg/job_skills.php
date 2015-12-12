<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class job_skills extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function update(){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      $displayData['pageTitle']    = 'Volunteer Skills';
      $displayData['mainTemplate'] = 'vols/empty_view';
      $displayData['title']        = CS_PROGNAME.' | Volunteer Skills';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   
}