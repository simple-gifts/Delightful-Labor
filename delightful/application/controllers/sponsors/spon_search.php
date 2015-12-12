<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class spon_search extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function opts(){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      if (!bTestForURLHack('showSponsors')) return;
      $displayData = array();

         //--------------------------------------------
         // models/helpers
         //--------------------------------------------
      $this->load->library('Generic_form');
      $this->load->helper('dl_util/web_layout');
      $this->load->model('sponsorship/msponsorship_programs', 'clsSponProg');


      $displayData['strSponProgDDL'] = $this->clsSponProg->strSponProgramDDL(-1);

         //--------------------------
         // validation rules
         //--------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtSearch1',      'Sponsor ID',  'trim|required|greater_than[0]');
      $this->form_validation->set_rules('txtSearch2',      'People ID',   'trim|required|greater_than[0]');
      $this->form_validation->set_rules('txtSearch3',      'Search',      'trim|required');

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
         $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                                 .' | Sponsor Search';

         $displayData['title']        = CS_PROGNAME.' | Sponsors';
         $displayData['nav']          = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate'] = 'sponsorship/search_opts_view';

         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->runSearchSetup((integer)$_POST['searchIdx']);
      }
   }

   function runSearchSetup($idx){
      $idx = (integer)$idx;
      redirect('sponsors/spon_search/run/'.$idx.'/'.$_POST['txtSearch'.$idx]);
   }

   function run($idx, $strSearch){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showSponsors')) return;
      $this->load->helper('dl_util/verify_id');

      $idx = (integer)$idx;
      $strSearch = trim(urldecode($strSearch));
      switch ($idx){
         case 1:            // search sponsor ID
            $lSID = (integer)$strSearch;
            if (vid_bSponsorIDExists($this, $lSID)){
               redirect('sponsors/view_spon_rec/viewViaSponID/'.$lSID);
            }else {
               $this->session->set_flashdata('error', 'No sponsorship records match sponsorID <b>'.$lSID.'</b>');
               redirect('sponsors/spon_search/opts');
            }
            break;

         case 2:            // search people/biz ID
            $lPID = (integer)$strSearch;
            if (vid_bPeopleRecExists($this, $strSearch)){
               redirect('sponsors/view_via_people_id/view/'.$lPID);
            }elseif (vid_bBizRecExists($this, $strSearch)){
               redirect('sponsors/view_via_people_id/view/'.$lPID);
            }else {
               $this->session->set_flashdata('error', 'No people/business records match ID <b>'.$lPID.'</b>');
               redirect('sponsors/spon_search/opts');
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
      if (!bTestForURLHack('showSponsors')) return;
      $this->load->model('util/msearch_single_generic', 'clsSearch');
      $this->load->model('sponsorship/msponsorship', 'clsSpon');
      $this->load->helper('reports/report_util');

      $params = array('enumStyle' => 'terse');
      $this->load->library('Generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');

      $this->clsSearch->strSearchTerm = $strSearch;

         //-----------------------------
         // search display setup
         //-----------------------------
      $this->clsSearch->enumSearchType      = CENUM_CONTEXT_SPONSORSHIP;
      $this->clsSearch->strSearchLabel      = 'Sponsor';
      $this->clsSearch->bShowKeyID          = true;
      $this->clsSearch->bShowSelect         = true;
      $this->clsSearch->bShowEnumSearchType = false;
      $this->clsSearch->strDisplayTitle     =
                '<br>Please select the sponsor</b><br>';

         // landing page for selection
      $this->clsSearch->strPathSelection  = 'sponsors/view_spon_rec/viewViaSponID/';
      $this->clsSearch->strTitleSelection = 'Select sponsor:';

         // landing page for "back"
      $this->clsSearch->strPathSearchAgain  = 'sponsors/spon_search/opts';
      $this->clsSearch->strTitleSearchAgain = 'Search again...';

      $lLeftCnt = strlen($strSearch);
      $this->clsSearch->strWhereExtra = " AND (   (LEFT(pe_strFName, $lLeftCnt)=".strPrepStr($strSearch).")
                                               OR (LEFT(pe_strLName, $lLeftCnt)=".strPrepStr($strSearch).')) ';

      $this->clsSearch->strIDLabel = 'sponsorID: ';
      $this->clsSearch->bShowLink  = false;

         // run search
      $displayData['strSearchLabel'] =
                          'Searching for sponsors whose first or last name begins with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
      $this->clsSearch->searchSponsors($this);
      $displayData['strHTMLSearchResults'] = $this->clsSearch->strHTML_SearchResults();
         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Sponsor Search';
      $displayData['pageTitle']    = anchor('main/menu/sponsorship',     'Sponsorship', 'class="breadcrumb"')
                              .' | '.anchor('sponsors/spon_search/opts', 'Search Options', 'class="breadcrumb"')
                              .' | Results';

      $displayData['mainTemplate'] = 'reports/search_select_view';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function viaProgram(){
      if (!bTestForURLHack('showSponsors')) return;
      $lProgID = @$_POST['ddlSponProg'];
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lProgID, 'sponsorship program ID');
      redirect('reports/pre_spon_via_prog/viaList/'.$lProgID);
   }
}