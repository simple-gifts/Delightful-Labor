<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class menu extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }
   
   function home(){
      $this->index();
   }

   function index(){
      $displayData = array();

      $displayData['title']        = CS_PROGNAME;
      $displayData['pageTitle']    = '&nbsp;'; //anchor('main/menu/admin', 'Admin', 'class="breadcrumb"');
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = null;
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function admin(){
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();

      $displayData['title']        = CS_PROGNAME.' | Admin';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"');
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = 'main/menu_admin_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function biz(){
      if (!bTestForURLHack('notVolunteer')) return;
      $displayData = array();

      $displayData['title']        = CS_PROGNAME.' | Businesses';
      $displayData['pageTitle']    = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"');
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = 'main/menu_biz_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function client(){
      if (!bTestForURLHack('notVolunteer')) return;
      $displayData = array();

      $displayData['title']        = CS_PROGNAME.' | Clients';
      $displayData['pageTitle']    = anchor('main/menu/client', 'Clients', 'class="breadcrumb"');
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = 'main/menu_client_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function financials(){
      global $gbDev;
      
      if (!bTestForURLHack('notVolunteer')) return;
      $displayData = array();

      $displayData['title']        = CS_PROGNAME.' | Financials/Grants';
      $displayData['pageTitle']    = anchor('main/menu/financials', 'Financials'.($gbDev ? '/Grants' : ''), 'class="breadcrumb"');
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = 'main/menu_financials_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function more(){
      if (!bTestForURLHack('notVolunteer')) return;
      $displayData = array();

      $displayData['title']        = CS_PROGNAME.' | More...';
      $displayData['pageTitle']    = anchor('main/menu/more', 'More...', 'class="breadcrumb"');
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = 'main/menu_more_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function people(){
      if (!bTestForURLHack('notVolunteer')) return;
      $displayData = array();

      $displayData['title']        = CS_PROGNAME.' | People';
      $displayData['pageTitle']    = anchor('main/menu/people', 'People', 'class="breadcrumb"');
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = 'main/menu_people_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   function reports(){
      if (!bTestForURLHack('notVolunteer')) return;
      $displayData = array();

      $displayData['title']        = CS_PROGNAME.' | Reports';
      $displayData['pageTitle']    = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"');
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = 'main/menu_reports_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }
      
   function sponsorship(){
      if (!bTestForURLHack('notVolunteer')) return;
      $displayData = array();

      $displayData['title']        = CS_PROGNAME.' | Sponsorship';
      $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"');
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = 'main/menu_sponsorship_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   function vols(){
      if (!bTestForURLHack('notVolunteer')) return;
      $displayData = array();

      $displayData['title']        = CS_PROGNAME.' | Volunteers';
      $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"');
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = 'main/menu_volunteer_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

}