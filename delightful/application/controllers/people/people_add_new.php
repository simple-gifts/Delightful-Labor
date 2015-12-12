<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class people_add_new extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

	public function selCon()	{
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      $displayData = array();
      if (!bTestForURLHack('dataEntryPeopleBizVol')) return;

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Add a new person';
      $displayData['pageTitle']    = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                                   .' | Add New: Household Options';

      $this->load->library('js_build/java_joe_radio');
      $this->load->library('js_build/js_verify');
      $this->load->helper('js/hh_search_form');

      $this->js_verify->clearEmbedOpts();
      $this->js_verify->bShow_bVerifyString    =
      $this->js_verify->bShow_bVerifyRadio     =
      $this->js_verify->bShow_strRadioSelValue =
      $this->js_verify->bShow_trim             = true;
      $displayData['js']  = $this->js_verify->loadJavaVerify();
      $displayData['js'] .= $this->java_joe_radio->insertJavaJoeRadio();
      $displayData['js'] .= verifyHHForm();

      $displayData['mainTemplate'] = 'people/add_new_sel_con_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');

   }

   public function selConSelect(){
      $strSelOpt = trim($_POST['rdoHH']);
      switch ($strSelOpt){
         case 'new':
            redirect('people/people_add_edit/contact/0/0');
            break;
         case 'existing':
            $this->searchHH(trim($_POST['txtHH']));
            break;
         default:
            $this->session->set_flashdata('error', 'Invalid selection');
            redirect('people/people_add_new/selCon');
            break;
      }
   }

	public function index()	{
	}

   function searchHH($strSearch){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      if (!bTestForURLHack('dataEntryPeopleBizVol')) return;
      
      $this->load->model('util/msearch_single_generic', 'clsSearch');
      $this->load->model('people/mpeople',  'clsPeople');

         //-----------------------------
         // search display setup
         //-----------------------------
      $this->clsSearch->enumSearchType      = CENUM_CONTEXT_HOUSEHOLD;
      $this->clsSearch->strSearchLabel      = 'Household';
      $this->clsSearch->bShowKeyID          = true;
      $this->clsSearch->bShowSelect         = true;
      $this->clsSearch->bShowEnumSearchType = false;
      $this->clsSearch->strDisplayTitle     =
                '<br>Please select the household for the new person</b><br>';

         // landing page for selection
      $this->clsSearch->strPathSelection  = 'people/people_add_new/addNewS3/';
      $this->clsSearch->strTitleSelection = 'Select household';

         // landing page for "back"
      $this->clsSearch->strPathSearchAgain  = 'people/people_add_new/selCon';
      $this->clsSearch->strTitleSearchAgain = 'Search again...';

      $lLeftCnt = strlen($strSearch);
      $this->clsSearch->strSearchTerm = $strSearch;
      $this->clsSearch->strWhereExtra = " AND LEFT(pe_strLName, $lLeftCnt)=".strPrepStr($strSearch)." ";

      $this->clsSearch->strIDLabel = 'household ID: ';
      $this->clsSearch->bShowLink  = false;
      
      
         // run search
      $displayData['strSearchLabel'] =
                          'Searching for '.$this->clsSearch->enumSearchType.' that begin with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
//      $this->clsSearch->searchPeople();
      $this->clsSearch->searchHousehold();
      $displayData['strHTMLSearchResults'] = $this->clsSearch->strHTML_SearchResults();

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Select Household';
      $displayData['pageTitle']    = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                              .' | '.anchor('people/people_add_new/selCon', 'Add New', 'class="breadcrumb"')
                              .' | Select Household';

      $displayData['mainTemplate'] = 'people/search_sel_household_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   function addNewS3($lHouseholdID){
      if (!bTestForURLHack('dataEntryPeopleBizVol')) return;
      
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lHouseholdID, 'household ID');
   
      $lHouseholdID = (integer)$lHouseholdID;

      redirect('people/people_add_edit/contact/0/'.$lHouseholdID);
   }



}
