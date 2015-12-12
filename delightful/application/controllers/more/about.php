<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class about extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }
   
   function aboutDL(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('notVolunteer')) return;
      $displayData = array();

      $this->load->model('admin/muser_accts', 'clsUser');
      $this->clsUser->versionLevel();
      $displayData['versionInfo'] = &$this->clsUser->versionInfo;
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']    = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                                .' | About';

      $displayData['title']          = CS_PROGNAME.' | More';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'more/about_info';
      $this->load->vars($displayData);
      $this->load->view('template');   
   }
   
   function php_info(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDev;
      if (!$gbDev) return;
      
      $this->load->view('more/about_phpinfo_view');   
   }

}