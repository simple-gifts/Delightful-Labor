<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class household extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($lHouseholdID, $lPID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lHouseholdID, 'household ID');
      verifyIDsViaType($this, CENUM_CONTEXT_PEOPLE, $lPID, true);

      $displayData = array();
      $displayData['lHouseholdID'] = $lHouseholdID = (integer)$lHouseholdID;
      $displayData['lPID']         = $lPID         = (integer)$lPID;
         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model  ('people/mpeople',   'clsPeople');
      $this->load->model  ('admin/madmin_aco', 'clsACO');
      $this->load->model  ('sponsorship/msponsorship');
      $this->load->model  ('donations/mdonations');
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper('img_docs/link_img_docs');

      $this->clsPeople->lHouseholdID = $lHouseholdID;
      $displayData['strHouseholdName'] = $this->clsPeople->strHouseholdNameViaHID($lHouseholdID);
      $this->clsPeople->loadPIDsViaHouseholdHID();

      $displayData['lNumInHousehold'] = $lNumInHousehold = $this->clsPeople->lNumInHousehold;
      $displayData['households']      = $this->clsPeople->arrHouseholds;
      if ($lPID <= 0) $displayData['lPID'] = $lPID = $this->clsPeople->arrHouseholds[0]->PID;

      $this->clsPeople->loadPeopleViaPIDs($lPID, true, true);
      $displayData['contextSummary'] = $this->clsPeople->peopleHTMLSummary(0);

         //------------------------------------------------
         // donation summaries
         //------------------------------------------------
      $idx = 0;
      $this->mdonations->bUseDateRange = false;
      $this->mdonations->cumulativeOpts = new stdClass;
      $this->mdonations->cumulativeOpts->enumCumulativeSource = 'people';
      if ($lNumInHousehold > 0){
         foreach($displayData['households'] as $contact){
            $lPID = $contact->PID;

            $this->mdonations->lPeopleID = $lPID;
            $this->mdonations->cumulativeOpts->enumMoneySet = 'all';

            $this->mdonations->cumulativeOpts->bSoft        = false;
            $this->mdonations->cumulativeDonation($this->clsACO, $lNumHard);
            $displayData['households'][$idx]->lNumACODonationGroups_hard = $this->mdonations->lNumCumulative;
            $displayData['households'][$idx]->donationsViaACO_hard       = $this->mdonations->cumulative;

            $this->mdonations->cumulativeOpts->bSoft        = true;
            $this->mdonations->cumulativeDonation($this->clsACO, $lNumSoft);
            $displayData['households'][$idx]->lNumACODonationGroups_soft = $this->mdonations->lNumCumulative;
            $displayData['households'][$idx]->donationsViaACO_soft       = $this->mdonations->cumulative;
            ++$idx;
         }
      }

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['mainTemplate'] = array('people/household');
      $displayData['pageTitle']    = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                              .' | Household';

      $displayData['title']        = CS_PROGNAME.' | People';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function makeHOH($lPID, $lHID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lHID, 'household ID');
      verifyID($this, $lPID, 'people ID');

      $lPID = (integer)$lPID;
      $lHID = (integer)$lHID;

      $this->load->model('people/mpeople',  'clsPeople');
      $this->clsPeople->lHouseholdID = $lHID;
      $this->clsPeople->lPeopleID    = $lPID;

      $this->clsPeople->peopleInfoLight();
      $this->clsPeople->resetHeadOfHousehold($lPID, $lHID);

      $this->session->set_flashdata('msg', '<b>'.$this->clsPeople->strSafeName.'</b> was made Head of Household');
      redirect('people/household/view/'.$lPID.'/'.$lPID);
   }

   function removePfromH($lPID, $lHID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lHID, 'household ID');
      verifyID($this, $lPID, 'people ID');

      $lPID = (integer)$lPID;
      $lHID = (integer)$lHID;

      $this->load->model('people/mpeople',  'clsPeople');
      $this->clsPeople->lHouseholdID = $lHID;
      $this->clsPeople->lPeopleID    = $lPID;

      $this->clsPeople->peopleInfoLight();
      $this->clsPeople->removeFromHousehold($lPID);

      $this->session->set_flashdata('msg', '<b>'.$this->clsPeople->strSafeName.'</b> was made removed from this Household');
      redirect('people/household/view/'.$lHID.'/0');
   }

   function addNewS1($lHouseholdID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lHouseholdID, 'household ID');

      $displayData = array();
      $displayData['lHouseholdID'] = $lHouseholdID = (integer)$lHouseholdID;

      $this->load->library('js_build/js_verify');
      $this->load->model('util/msearch_single_generic', 'clsSearch');
      $this->load->model('people/mpeople',  'clsPeople');
      $this->load->helper('js/simple_search');

      $this->js_verify->clearEmbedOpts();
      $this->js_verify->bShow_bVerifyString    =
      $this->js_verify->bShow_trim             = true;
      $displayData['js']  = $this->js_verify->loadJavaVerify();
      $displayData['js'] .= simpleSearch();

      $this->clsPeople->lHouseholdID = $lHouseholdID;
      $displayData['strHouseholdName'] = $this->clsPeople->strHouseholdNameViaHID($lHouseholdID);

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Add to household';
      $displayData['pageTitle']    = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                              .' | '.anchor('people/household/view/'.$lHouseholdID.'/0', 'Household', 'class="breadcrumb"')
                              .' | Add To Household';

      $displayData['mainTemplate'] = array('people/household_summary_view',
                                           'people/add_to_household_sel_house_view');
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addNewS2($lHouseholdID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lHouseholdID, 'household ID');

      $displayData = array();
      $displayData['lHouseholdID'] = $lHouseholdID = (integer)$lHouseholdID;

      $this->load->helper('js/simple_search');

      $this->load->model('util/msearch_single_generic', 'clsSearch');
      $this->load->model('people/mpeople',              'clsPeople');
      $this->clsPeople->lHouseholdID = $lHouseholdID;
      $displayData['strHouseholdName'] = $this->clsPeople->strHouseholdNameViaHID($lHouseholdID);

      $strSearch = $this->clsSearch->strSearchTerm = trim($_POST['txtSearch']);

      $displayData['strHouseholdName'] = $strHName = $this->clsPeople->strHouseholdNameViaHID($lHouseholdID);


         //-----------------------------
         // search display setup
         //-----------------------------
      $this->clsSearch->enumSearchType      = CENUM_CONTEXT_PEOPLE;
      $this->clsSearch->strSearchLabel      = 'People';
      $this->clsSearch->bShowKeyID          = true;
      $this->clsSearch->bShowSelect         = true;
      $this->clsSearch->bShowEnumSearchType = false;
      $this->clsSearch->strDisplayTitle     =
                '<br>Please select the new person for <b>'.htmlspecialchars($strHName).'</b><br>';

         // landing page for selection
      $this->clsSearch->strPathSelection  = 'people/household/addNewS3/'.$lHouseholdID.'/';
      $this->clsSearch->strTitleSelection = 'Select person';

         // landing page for "back"
      $this->clsSearch->strPathSearchAgain  = 'people/household/addNewS1/'.$lHouseholdID;
      $this->clsSearch->strTitleSearchAgain = 'Search again...';

      $lLeftCnt = strlen($strSearch);
      $this->clsSearch->strWhereExtra = " AND LEFT(pe_strLName, $lLeftCnt)=".strPrepStr($strSearch)
                                 ." AND pe_lHouseholdID!=$lHouseholdID ";

      $this->clsSearch->strIDLabel = 'peopleID: ';
      $this->clsSearch->bShowLink  = false;

         // run search
      $displayData['strSearchLabel'] =
                          'Searching for '.$this->clsSearch->enumSearchType.' that begin with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';

      $this->clsSearch->searchPeople();
      $displayData['strHTMLSearchResults'] = $this->clsSearch->strHTML_SearchResults();

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Add to household';
      $displayData['pageTitle']    = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                              .' | '.anchor('people/household/view/'.$lHouseholdID.'/0', 'Household', 'class="breadcrumb"')
                              .' | Add To Household';

      $displayData['mainTemplate'] = array('people/household_summary_view',
                                           'people/search_sel_person_view');
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addNewS3($lHouseholdID, $lPeopleID){
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lHouseholdID, 'household ID');
      verifyID($this, $lPeopleID, 'people ID');

      $lHouseholdID = (integer)$lHouseholdID;
      $lPeopleID    = (integer)$lPeopleID;

      $this->load->model('people/mpeople', 'clsPeople');
      $this->clsPeople->lPeopleID = $lPeopleID;
      $this->clsPeople->peopleInfoLight();
      $strHousehold = htmlspecialchars($this->clsPeople->strHouseholdNameViaHID($lHouseholdID));

      $this->clsPeople->addToHousehold($lPeopleID, $lHouseholdID);

      $this->session->set_flashdata('msg', '<b>'.$this->clsPeople->strSafeName.'</b> was made added to '
                   .'<b>'.htmlspecialchars($strHousehold).'</b>');
      redirect('people/household/view/'.$lHouseholdID.'/0');
   }





}