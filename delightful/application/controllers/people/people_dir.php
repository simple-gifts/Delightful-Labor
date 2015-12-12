<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class people_dir extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($strLookupLetter='A', $lStartRec=0, $lRecsPerPage=50){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      $this->peopleDirView($strLookupLetter, $lStartRec, $lRecsPerPage, false);
   }

   function relView($strLookupLetter='A', $lStartRec=0, $lRecsPerPage=50){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      $this->peopleDirView($strLookupLetter, $lStartRec, $lRecsPerPage, true);
   }

   function peopleDirView($strLookupLetter, $lStartRec, $lRecsPerPage, $bRelDir){
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
      $displayData['strDirLetter'] = $strLookupLetter = strSanitizeLetter($strLookupLetter);
      $displayData['bRelDir'] = $bRelDir;

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

      initPeopleReportDisplay($displayData);

      $displayData['strRptTitle']  = 'People '.($bRelDir ? 'Relationship ' : '').'Directory';

      $displayData['strDirLetter'] = $strLookupLetter;
      $displayData['strLinkBase']  = $strLinkBase = 'people/people_dir/'.($bRelDir ? 'relView' : 'view').'/';
      $displayData['strDirTitle']  = strDisplayDirectory(
                                         $strLinkBase, ' class="directoryLetters" ', $strLookupLetter,
                                         true, $lStartRec, $lRecsPerPage);

         //------------------------------------------------
         // total # people for this letter
         //------------------------------------------------
      $displayData['lNumRecsTot']   = $lNumRecsTot = lNumPeopleRecsViaLetter($strLookupLetter, CENUM_CONTEXT_PEOPLE);
      $displayData['lNumPeople']    = $lNumRecsTot;
      $displayData['strPeopleType'] = CENUM_CONTEXT_PEOPLE;

         //------------------------------------------------
         // load people directory page
         //------------------------------------------------
      $strWhereExtra = $this->clsPeople->strWhereByLetter($strLookupLetter, CENUM_CONTEXT_PEOPLE, false);
      $this->clsPeople->loadPeopleDirectoryPage($strWhereExtra, $lStartRec, $lRecsPerPage);
      $displayData['lNumDisplayRows']      = $this->clsPeople->lNumPeople;
      $displayData['directoryRecsPerPage'] = $lRecsPerPage;
      $displayData['directoryStartRec']    = $lStartRec;
      $displayData['people']               = &$this->clsPeople->people;

      if ($bRelDir){
         if ($this->clsPeople->lNumPeople > 0){
            foreach ($this->clsPeople->people as $person){
               $this->clsRel->lPID = $person->lKeyID;
               $this->clsRel->loadFromRelViaPID();
               $person->lNumFromRels = $this->clsRel->lNumRelAB;
               if ($person->lNumFromRels > 0) $person->fromRels = arrayCopy($this->clsRel->arrRelAB);
               $this->clsRel->loadToRelViaPID();
               $person->lNumToRels = $this->clsRel->lNumRelAB;
               if ($person->lNumToRels > 0) $person->toRels = arrayCopy($this->clsRel->arrRelAB);
            }
         }
      }

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['mainTemplate'] = array('people/people_directory_view',
                                           'people/'.($bRelDir ? 'rpt_people_rel_dir_view' : 'rpt_generic_people_list'));
      $displayData['pageTitle']    = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                              .' | People'.($bRelDir ? ' Relationship' : '').' Directory';

      $displayData['title']        = CS_PROGNAME.' | People';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

}