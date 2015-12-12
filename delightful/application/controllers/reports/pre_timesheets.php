<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_timesheets extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function showOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;
      $displayData = array();
      $displayData['js'] = '';

      
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Time Sheets';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'staff/timesheets/pre_timesheet_opts_view';
      $this->load->vars($displayData);
      $this->load->view('template');
      
      
   }
   
   
 }
   
   