<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class vol_search extends CI_Controller {

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
      $this->form_validation->set_rules('txtSearch1',      'Volunteer ID', 'trim|required|greater_than[0]');
      $this->form_validation->set_rules('txtSearch2',      'People ID',    'trim|required|greater_than[0]');
      $this->form_validation->set_rules('txtSearch3',      'Search',       'trim|required');

      for ($idx=1; $idx<=3; ++$idx){
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
         $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                                 .' | Volunteer Search';

         $displayData['title']        = CS_PROGNAME.' | Volunteers';
         $displayData['nav']          = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate'] = 'vols/search_opts_view';

         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->runSearchSetup((integer)$_POST['searchIdx']);
      }
   }

   function runSearchSetup($idx){
      $idx = (integer)$idx;
      redirect('volunteers/vol_search/run/'.$idx.'/'.$_POST['txtSearch'.$idx]);
   }

   function run($idx, $strSearch){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $idx = (integer)$idx;
      $strSearch = trim(urldecode($strSearch));
      switch ($idx){
         case 1:            // search vol ID
            $this->load->helper('dl_util/verify_id');
            if (vid_bVolRecExists ($this, $strSearch)){
               $lVID = (integer)$strSearch;
               redirect('volunteers/vol_record/volRecordView/'.$lVID);
            }else {
               $this->session->set_flashdata('error', 'No records match volunteerID <b>'.htmlspecialchars($strSearch).'</b>');
               redirect('volunteers/vol_search/searchOpts');
            }
            break;

         case 2:            // search vol via PID
            $this->load->helper('dl_util/verify_id');
            if (vid_bVolViaPIDExists ($this, $strSearch, $lVolID)){
               redirect('volunteers/vol_record/volRecordView/'.$lVolID);
            }else {
               $this->session->set_flashdata('error', 'No volunteer records match peopleID <b>'.htmlspecialchars($strSearch).'</b>');
               redirect('volunteers/vol_search/searchOpts');
            }
            break;

         case 3:            // search first/last name
            $this->firstLastNameSearch($strSearch);
            break;

         default:
            screamForHelp($idx.': invalid search type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function firstLastNameSearch($strSearch){
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
      $this->clsSearch->enumSearchType      = CENUM_CONTEXT_VOLUNTEER;
      $this->clsSearch->strSearchLabel      = 'Volunteers';
      $this->clsSearch->bShowKeyID          = true;
      $this->clsSearch->bShowSelect         = true;
      $this->clsSearch->bShowEnumSearchType = false;
      $this->clsSearch->strDisplayTitle     =
                '<br>Please select the volunteer</b><br>';

         // landing page for selection
      $this->clsSearch->strPathSelection  = 'volunteers/vol_record/volRecordView/';
      $this->clsSearch->strTitleSelection = 'Select volunteer:';

         // landing page for "back"
      $this->clsSearch->strPathSearchAgain  = 'volunteers/vol_search/searchOpts';
      $this->clsSearch->strTitleSearchAgain = 'Search again...';

      $lLeftCnt = strlen($strSearch);
      $this->clsSearch->strWhereExtra = " AND (   (LEFT(pe_strFName, $lLeftCnt)=".strPrepStr($strSearch).")
                                               OR (LEFT(pe_strLName, $lLeftCnt)=".strPrepStr($strSearch).')) ';

      $this->clsSearch->strIDLabel = 'volunteerID: ';
      $this->clsSearch->bShowLink  = false;

         // run search
      $displayData['strSearchLabel'] =
                          'Searching for volunteers whose first or last name begins with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
      $this->clsSearch->searchExistingVolunteers();
      $displayData['strHTMLSearchResults'] = $this->clsSearch->strHTML_SearchResults();
      $displayData['enumContext'] = CENUM_CONTEXT_VOLUNTEER;

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Volunteer Search';
      $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                              .' | '.anchor('volunteers/vol_search/searchOpts', 'Search Options', 'class="breadcrumb"')
                              .' | Results';

      $displayData['mainTemplate'] = 'reports/search_select_view';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }







}