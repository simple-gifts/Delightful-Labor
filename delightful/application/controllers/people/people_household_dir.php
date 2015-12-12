<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class people_household_dir extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($strLookupLetter='A', $lStartRec=0, $lRecsPerPage=50){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      if (!bTestForURLHack('showPeople')) return;

      $strLookupLetter = urldecode($strLookupLetter);
      $displayData = array();

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper('people/people');
      $this->load->helper('people/people_display');
      $this->load->helper('dl_util/directory');
      $this->load->helper('dl_util/rs_navigate');
//      $this->load->helper('dl_util/email_web');
      $this->load->helper('dl_util/record_view');
      $this->load->helper('img_docs/link_img_docs');

      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('people/mpeople', 'clsPeople');
      $this->load->model('admin/madmin_aco', 'clsACO');
      $this->load->model('sponsorship/msponsorship');
      $this->load->model('donations/mdonations');
      $this->load->model('people/mrelationships', 'clsRel');

         //------------------------------------------------
         // sanitize the lookup letter
         //------------------------------------------------
/*
      $strLookupLetter = strtoupper(substr($strLookupLetter, 0, 1));
      if ($strLookupLetter < 'A' || $strLookupLetter > 'Z') $strLookupLetter = '#';
      $displayData['strDirLetter'] = $strLookupLetter;
*/
      $displayData['strDirLetter'] = $strLookupLetter = strSanitizeLetter($strLookupLetter);

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

      initHouseholdReportDisplay($displayData);

      $displayData['strRptTitle']  = 'Household Directory';

      $displayData['strDirLetter'] = $strLookupLetter;
      $displayData['strLinkBase']  = $strLinkBase = 'people/people_household_dir/view/';
      $displayData['strDirTitle']  = strDisplayDirectory(
                                         $strLinkBase, ' class="directoryLetters" ', $strLookupLetter,
                                         true, $lStartRec, $lRecsPerPage);

         //------------------------------------------------
         // total # households for this letter
         //------------------------------------------------
      $displayData['lNumRecsTot']   = $lNumRecsTot = lNumPeopleRecsViaLetter($strLookupLetter, CENUM_CONTEXT_HOUSEHOLD);
      $displayData['lNumPeople']    = $lNumRecsTot;
      $displayData['strPeopleType'] = CENUM_CONTEXT_HOUSEHOLD;


         //------------------------------------------------
         // load household directory page
         //------------------------------------------------
      $strWhereExtra = $this->clsPeople->strWhereByLetter($strLookupLetter, CENUM_CONTEXT_HOUSEHOLD, false);
      $this->clsPeople->loadHouseholdDirectoryPage($strWhereExtra, $lStartRec, $lRecsPerPage);
      $displayData['lNumDisplayRows']      = $this->clsPeople->lNumPeople;
      $displayData['directoryRecsPerPage'] = $lRecsPerPage;
      $displayData['directoryStartRec']    = $lStartRec;
      $displayData['people']               = $this->clsPeople->people;

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['mainTemplate'] = array('people/household_directory_view', 'people/rpt_generic_household_list_view');
      $displayData['pageTitle']    = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                              .' | Household Directory';

      $displayData['title']        = CS_PROGNAME.' | People';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');


















   }







}