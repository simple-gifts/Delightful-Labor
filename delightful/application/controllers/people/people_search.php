<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class people_search extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function searchOpts(){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      $displayData = array();

         //--------------------------------------------
         // models/helpers
         //--------------------------------------------
      $this->load->library('Generic_form');
      $this->load->helper('dl_util/web_layout');

         //--------------------------
         // validation rules
         //--------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtSearch1',      'People ID',  'trim|required|greater_than[0]');
      $this->form_validation->set_rules('txtSearch2',      'Search',  'trim|required');
      $this->form_validation->set_rules('txtSearch3',      'Search',  'trim|required');
      $this->form_validation->set_rules('txtSearch4',      'Search',  'trim|required');
      $this->form_validation->set_rules('txtSearch5',      'Search',  'trim|required');
      $this->form_validation->set_rules('txtSearch6',      'Search',  'trim|required');
      $this->form_validation->set_rules('txtSearch7',      'Search',  'trim|required');

      for ($idx=1; $idx<=7; ++$idx){
         $displayData['formErr'][$idx] = '';
      }

      if ($this->form_validation->run() == FALSE){
         if (validation_errors()==''){
         }else {
            setOnFormError($displayData);
            $lSearchIDX = (integer)$_POST['searchIdx'];
            if (form_error('txtSearch'.$lSearchIDX)==''){
               $this->runSearchSetup($lSearchIDX);
            }else {
               $displayData['formErr'][$lSearchIDX] = form_error('txtSearch'.$lSearchIDX);
            }
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']    = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                                 .' | People Search';

         $displayData['title']        = CS_PROGNAME.' | People';
         $displayData['nav']          = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate'] = 'people/search_opts_view';

         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->runSearchSetup((integer)$_POST['searchIdx']);
      }
   }

   function runSearchSetup($idx){
      $idx = (integer)$idx;
      redirect('people/people_search/run/'.$idx.'/'.$_POST['txtSearch'.$idx]);
   }

   function run($idx, $strSearch){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $idx = (integer)$idx;
      $strSearch = trim(urldecode($strSearch));
      $strSafeSearch = strPrepStr($strSearch);
      $lLeftCnt  = strlen($strSearch);
      switch ($idx){
         case 1:            // search people ID
            $this->load->helper('dl_util/verify_id');
            if (vid_bPeopleRecExists ($this, $strSearch)){
               $lPID = (integer)$strSearch;
               redirect('people/people_record/view/'.$lPID);
            }else {
               $this->session->set_flashdata('error', 'No people records match peopleID <b>'.htmlspecialchars($strSearch).'</b>');
               redirect('people/people_search/searchOpts');
            }
            break;

         case 2:            // search first/last name
            $strWhere = " AND (   (LEFT(pe_strFName, $lLeftCnt)=$strSafeSearch)
                               OR (LEFT(pe_strLName, $lLeftCnt)=$strSafeSearch)) ";
            $strLabel =
                          'Searching for people whose <b>first or last name</b> begins with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
            $this->peopleNameAddrSearch($strSearch, $strWhere, $strLabel);
            break;

         case 3:            // search city
            $strWhere = " AND ( LEFT(pe_strCity, $lLeftCnt)=$strSafeSearch) ";
            $strLabel =
                          'Searching for people whose <b>city</b> begins with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
            $this->peopleNameAddrSearch($strSearch, $strWhere, $strLabel);
            break;

         case 4:            // search state
            $strWhere = " AND ( LEFT(pe_strState, $lLeftCnt)=$strSafeSearch) ";
            $strLabel =
                          'Searching for people whose <b>state</b> begins with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
            $this->peopleNameAddrSearch($strSearch, $strWhere, $strLabel);
            break;

         case 5:            // search country
            $strWhere = " AND ( LEFT(pe_strCountry, $lLeftCnt)=$strSafeSearch) ";
            $strLabel =
                          'Searching for people whose <b>country</b> begins with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
            $this->peopleNameAddrSearch($strSearch, $strWhere, $strLabel);
            break;

         case 6:            // search zip
            $strWhere = " AND ( LEFT(pe_strZip, $lLeftCnt)=$strSafeSearch) ";
            $strLabel =
                          'Searching for people whose <b>zip</b> begins with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
            $this->peopleNameAddrSearch($strSearch, $strWhere, $strLabel);
            break;

         case 7:            // search everything
            $strWhere = " AND (   (INSTR(pe_strFName,   $strSafeSearch)>0)
                               OR (INSTR(pe_strLName,   $strSafeSearch)>0)
                               OR (INSTR(pe_strMName,   $strSafeSearch)>0)
                               OR (INSTR(pe_strAddr1,   $strSafeSearch)>0)
                               OR (INSTR(pe_strAddr2,   $strSafeSearch)>0)
                               OR (INSTR(pe_strCity,    $strSafeSearch)>0)
                               OR (INSTR(pe_strState,   $strSafeSearch)>0)
                               OR (INSTR(pe_strCountry, $strSafeSearch)>0)
                               OR (INSTR(pe_strZip,     $strSafeSearch)>0)
                              ) ";
            $strLabel =
                          'Searching for people whose <b>name/address</b> contains <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
            $this->peopleNameAddrSearch($strSearch, $strWhere, $strLabel);
            break;

         default:
            screamForHelp($idx.': invalid search type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function peopleNameAddrSearch($strSearch, $strWhere, $strLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->model('util/msearch_single_generic', 'clsSearch');
      $this->load->helper('reports/report_util');

      $params = array('enumStyle' => 'terse');
      $this->load->library('Generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
//      $this->load->helper('dl_util/email_web');
      $this->load->model('people/mpeople', 'clsPeople');

      $this->clsSearch->strSearchTerm = $strSearch;

         //-----------------------------
         // search display setup
         //-----------------------------
      $this->clsSearch->enumSearchType      = CENUM_CONTEXT_PEOPLE;
      $this->clsSearch->strSearchLabel      = 'People';
      $this->clsSearch->bShowKeyID          = true;
      $this->clsSearch->bShowSelect         = true;
      $this->clsSearch->bShowEnumSearchType = false;
      $this->clsSearch->strDisplayTitle     =
                '<br>Please select the person</b><br>';

         // landing page for selection
      $this->clsSearch->strPathSelection  = 'people/people_record/view/';
      $this->clsSearch->strTitleSelection = 'Select person:';

         // landing page for "back"
      $this->clsSearch->strPathSearchAgain  = 'people/people_search/searchOpts';
      $this->clsSearch->strTitleSearchAgain = 'Search again...';

      $this->clsSearch->strWhereExtra = $strWhere;

      $this->clsSearch->strIDLabel = 'peopleID: ';
      $this->clsSearch->bShowLink  = false;

         // run search
      $displayData['strSearchLabel'] = $strLabel;
      $displayData['enumContext']    = CENUM_CONTEXT_PEOPLE;
      $this->clsSearch->searchPeople($this);
      $displayData['strHTMLSearchResults'] = $this->clsSearch->strHTML_SearchResults();

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | People Search';
      $displayData['pageTitle']    = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                              .' | '.anchor('people/people_search/searchOpts', 'Search Options', 'class="breadcrumb"')
                              .' | Results';

      $displayData['mainTemplate'] = 'reports/search_select_view';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }



}