<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class biz_search extends CI_Controller {

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
      $displayData['formData'] = new stdClass;

         //--------------------------------------------
         // models/helpers
         //--------------------------------------------
      $this->load->library('Generic_form');
      $this->load->helper ('dl_util/web_layout');
      $this->load->model  ('util/mlist_generic', 'clsList');

      $this->clsList->enumListType = 'bizCat';

         //--------------------------
         // validation rules
         //--------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtSearch1',      'Business ID',       'trim|required|greater_than[0]');
      $this->form_validation->set_rules('txtSearch2',      'Search',            'trim|required');
      $this->form_validation->set_rules('ddlBizCat',       'Business Category', 'trim|required');

      for ($idx=1; $idx<=3; ++$idx){
         $displayData['formErr'][$idx] = '';
      }

      $displayData['formData']->strBizList = $this->clsList->strLoadListDDL('ddlBizCat', true, -1);
      if ($this->form_validation->run() == FALSE){
         if (validation_errors()==''){
         }else {
            setOnFormError($displayData);
            $lSearchIDX = (integer)$_POST['searchIdx'];

               // test for special searches
            if ($lSearchIDX == 3){

            }else {
               if (form_error('txtSearch'.$lSearchIDX)==''){
                  $this->runSearchSetup($lSearchIDX);
               }else {
                  $displayData['formErr'][$lSearchIDX] = form_error('txtSearch'.$lSearchIDX);
               }
            }
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']    = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                                 .' | Business Search';

         $displayData['title']        = CS_PROGNAME.' | People';
         $displayData['nav']          = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate'] = 'biz/search_opts_view';

         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->runSearchSetup((integer)$_POST['searchIdx']);
      }
   }

   function runSearchSetup($idx){
      $idx = (integer)$idx;
      redirect('biz/biz_search/run/'.$idx.'/'.$_POST['txtSearch'.$idx]);
   }

   function run($idx, $strSearch){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $idx = (integer)$idx;
      $strSearch = trim(urldecode($strSearch));
      switch ($idx){
         case 1:            // search people ID
            $this->load->helper('dl_util/verify_id');
            if (vid_bBizRecExists ($this, $strSearch)){
               $lPID = (integer)$strSearch;
               redirect('biz/biz_record/view/'.$lPID);
            }else {
               $this->session->set_flashdata('error', 'No records match businessID <b>'.htmlspecialchars($strSearch).'</b>');
               redirect('biz/biz_search/searchOpts');
            }
            break;

         case 2:            // search first/last name
            $this->firstBizNameSearch($strSearch);
            break;

         default:
            screamForHelp($idx.': invalid search type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function firstBizNameSearch($strSearch){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->model('util/msearch_single_generic', 'clsSearch');
      $this->load->helper('reports/report_util');

      $params = array('enumStyle' => 'terse');
      $this->load->library('Generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
//      $this->load->helper('dl_util/email_web');
      $this->load->model('biz/mbiz', 'clsBiz');
      $this->load->model('people/mpeople', 'clsPeople');

      $this->clsSearch->strSearchTerm = $strSearch;

         //-----------------------------
         // search display setup
         //-----------------------------
      $this->clsSearch->enumSearchType      = CENUM_CONTEXT_BIZ;
      $this->clsSearch->strSearchLabel      = 'Businesses/Organizations';
      $this->clsSearch->bShowKeyID          = true;
      $this->clsSearch->bShowSelect         = true;
      $this->clsSearch->bShowEnumSearchType = false;
      $this->clsSearch->strDisplayTitle     =
                '<br>Please select the person</b><br>';

         // landing page for selection
      $this->clsSearch->strPathSelection  = 'biz/biz_record/view/';
      $this->clsSearch->strTitleSelection = 'Select business/organization:';

         // landing page for "back"
      $this->clsSearch->strPathSearchAgain  = 'biz/biz_search/searchOpts';
      $this->clsSearch->strTitleSearchAgain = 'Search again...';

      $lLeftCnt = strlen($strSearch);
      $this->clsSearch->strWhereExtra = " AND (   (LEFT(pe_strLName, $lLeftCnt)=".strPrepStr($strSearch).')) AND pe_bBiz ';
      $this->clsSearch->strIDLabel = 'businessID: ';
      $this->clsSearch->bShowLink  = false;

         // run search
      $displayData['strSearchLabel'] =
                          'Searching for businesses whose name begins with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
      $this->clsSearch->searchBiz();
      $displayData['strHTMLSearchResults'] = $this->clsSearch->strHTML_SearchResults();
      $displayData['enumContext']          = CENUM_CONTEXT_BIZ;

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Business Search';
      $displayData['pageTitle']    = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                              .' | '.anchor('biz/biz_search/searchOpts', 'Search Options', 'class="breadcrumb"')
                              .' | Results';

      $displayData['mainTemplate'] = 'reports/search_select_view';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }







}