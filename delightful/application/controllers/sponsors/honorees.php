<?php
class honorees extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addNewS1($lSponID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showSponsors')) return;
      $displayData = array();
      $displayData['lSponID'] = $lSponID = (integer)$lSponID;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
      $this->load->model  ('sponsorship/msponsorship',      'clsSpon');
      $this->load->library('js_build/js_verify');
      $this->load->model('util/msearch_single_generic', 'clsSearch');
      $this->load->model  ('people/mpeople',  'clsPeople');
      $this->load->helper ('js/simple_search');

      $this->js_verify->clearEmbedOpts();
      $this->js_verify->bShow_bVerifyString    =
      $this->js_verify->bShow_trim             = true;
      $displayData['js']  = $this->js_verify->loadJavaVerify();
      $displayData['js'] .= simpleSearch();

      $this->clsSpon->sponsorInfoViaID($lSponID);
      $displayData['sponRec']        = &$this->clsSpon->sponInfo[0];
      $displayData['contextSummary'] = $this->clsSpon->sponsorshipHTMLSummary();


         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Sponsors';
      $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                              .' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$lSponID, 'Sponsorship Record', 'class="breadcrumb"')
                              .' | Add Sponsorship Honoree';

      $displayData['mainTemplate'] = 'sponsorship/honoree_s1_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addNewS2($lSponID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showSponsors')) return;
      $displayData = array();
      $displayData['lSponID'] = $lSponID = (integer)$lSponID;

      $this->load->helper('js/simple_search');

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('util/msearch_single_generic', 'clsSearch');
      $this->load->model('people/mpeople', 'clsPeople');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
      $this->load->model('sponsorship/msponsorship',      'clsSpon');

      $this->clsSpon->sponsorInfoViaID($lSponID);
      $displayData['sponRec']        = &$this->clsSpon->sponInfo[0];
      $displayData['contextSummary'] = $this->clsSpon->sponsorshipHTMLSummary();
      $lSponFID                      = $this->clsSpon->sponInfo[0]->lForeignID;


      $this->clsSearch->strSearchTerm = $strSearch = trim($_POST['txtSearch']);

         //-----------------------------
         // search display setup
         //-----------------------------
      $this->clsSearch->enumSearchType      = CENUM_CONTEXT_PEOPLE;
      $this->clsSearch->strSearchLabel      = 'People';
      $this->clsSearch->bShowKeyID          = true;
      $this->clsSearch->bShowSelect         = true;
      $this->clsSearch->strIDLabel          = 'peopleID: ';
      $this->clsSearch->bShowLink           = false;
      $this->clsSearch->bShowEnumSearchType = false;
      $this->clsSearch->strDisplayTitle     =
                '<br>Please select the person to act as the honoree for this sponsorship</b><br>';

         // landing page for selection
      $this->clsSearch->strPathSelection  = 'sponsors/honorees/addNewS3/'.$lSponID.'/';
      $this->clsSearch->strTitleSelection = 'Select person';

         // landing page for "back"
      $this->clsSearch->strPathSearchAgain  = 'sponsors/honorees/addNewS1/'.$lSponID;
      $this->clsSearch->strTitleSearchAgain = 'Search again...';

      $lLeftCnt = strlen($strSearch);
      $this->clsSearch->strWhereExtra = " AND LEFT(pe_strLName, $lLeftCnt)=".strPrepStr($strSearch).' '
                                       ." AND pe_lKeyID != $lSponFID ";


         // run search
      $displayData['strSearchLabel'] =
                          'Searching for '.$this->clsSearch->enumSearchType.' that begin with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
      $this->clsSearch->searchPeople();
      $displayData['strHTMLSearchResults'] = $this->clsSearch->strHTML_SearchResults();

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Sponsors';
      $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                              .' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$lSponID, 'Sponsorship Record', 'class="breadcrumb"')
                              .' | Add Sponsorship Honoree';

      $displayData['mainTemplate'] = 'sponsorship/honoree_select_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }


   function addNewS3($lSponID, $lPID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showSponsors')) return;
      $lSponID = (integer)$lSponID;
      $lPID    = (integer)$lPID;
      if ($lPID <= 0) $lPID = null;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('sponsorship/msponsorship',      'clsSpon');
      $this->load->helper('dl_util/util_db');

      if (is_null($lPID)){
         $this->session->set_flashdata('msg', 'Honoree removed from this sponsorship.');
      }else {
         $this->load->model('people/mpeople', 'clsPeople');
         $this->clsPeople->lPeopleID = $lPID;
         $this->clsPeople->peopleInfoLight();
         $this->session->set_flashdata('msg', 'Honoree '.$this->clsPeople->strSafeName.' was added to this sponsorship.');
      }

      $this->clsSpon->addRemoveHonoreeToSponsorship($lSponID, $lPID);

      redirect_SponsorshipRecord($lSponID);
   }












}