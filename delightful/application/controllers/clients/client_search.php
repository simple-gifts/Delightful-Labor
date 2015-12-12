<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class client_search extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function searchOpts($strViaAvailRpt = 'false'){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      
      $displayData = array();
      
      $displayData['bViaAvailRpt'] = $bViaAvailRpt = $strViaAvailRpt=='true';

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
      if (!$bViaAvailRpt){
         $this->form_validation->set_rules('txtSearch1',      'Client ID',  'trim|required|greater_than[0]');
         $this->form_validation->set_rules('txtSearch2',      'Search',  'trim|required');

         for ($idx=1; $idx<=2; ++$idx){
            $displayData['formErr'][$idx] = '';
         }
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
         if ($bViaAvailRpt){
            $displayData['pageTitle']    = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                    .' | Client Available for Sponsorship';
         }else {
            $displayData['pageTitle']    = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                    .' | Client Search';
         }

         $displayData['title']        = CS_PROGNAME.' | Clients';
         $displayData['nav']          = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate'] = 'client/search_opts';

         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->runSearchSetup((integer)$_POST['searchIdx']);
      }
   }

   function searchViaAvail($strViaAvailRpt = 'false'){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      $displayData = array();
      $lSponProgID = (integer)$_POST['ddlSponProg'];
      $bViaAvailRpt = $strViaAvailRpt=='true';

         //--------------------------
         // models
         //--------------------------
      $this->load->model('clients/mclients', 'clsClients');
      $this->load->library('clients/client_search_util', '',       'clsACSearch');
      $clsACSearch = new client_search_util;

      $this->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');

      $clsACSearch->lSponProgID = $lSponProgID;
      $clsACSearch->clientsAvailableForSponsorship();

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;      

      $bAllSponProg = $lSponProgID <= 0;
      if ($bAllSponProg){
         $displayData['strSponProg'] = $strSponProg = 'All Sponsorship Programs';
      }else {
         $displayData['strSponProg'] = $strSponProg = htmlspecialchars($this->clsSponProg->strSponProgsViaID($lSponProgID));
      }

      $displayData['lNumAvail'] = $lNumAvail = $clsACSearch->lNumAvail;
      if ($lNumAvail > 0){
         $clsACSearch->strExtraClientWhere = ' AND (cr_lKeyID IN ('.implode(',', $clsACSearch->lAvailList).')) ';
         $clsACSearch->loadClientsGeneric();

         $cDir = $clsACSearch->dir;
         $cDir->strTableClassStyle  = ' class="enpRpt" ';
         $cDir->strTitle            = 'Clients available for sponsorship: '.$strSponProg;
         $cDir->strTitleClassStyle  = ' class="enpRptTitle" ';
         $cDir->strHeaderClass      = ' class="enpRptLabel" ';
         $cDir->strRowTRClass       = ' class="makeStripe" ';

         $cDir->cols = array();
         for ($idx=0; $idx<=4; ++$idx) $cDir->cols[$idx] = new stdClass;
         $cDir->cols[0]->label = 'Client ID';     $cDir->cols[0]->width = '50pt';  $cDir->cols[0]->tdClass=' class="enpRpt" ';
         $cDir->cols[1]->label = 'Name';          $cDir->cols[1]->width = '150pt'; $cDir->cols[1]->tdClass=' class="enpRpt" ';
         $cDir->cols[2]->label = 'Location';      $cDir->cols[2]->width = '150pt'; $cDir->cols[2]->tdClass=' class="enpRpt" ';
         $cDir->cols[3]->label = 'Birthday/Age';  $cDir->cols[3]->width = '';      $cDir->cols[3]->tdClass=' class="enpRpt" ';
         $cDir->cols[4]->label = 'Gender';        $cDir->cols[4]->width = '';      $cDir->cols[4]->tdClass=' class="enpRpt" ';
         if ($bAllSponProg){
            $cDir->cols[5] = new stdClass;
            $cDir->cols[5]->label = 'Sponsorship Program';  $cDir->cols[5]->width = '';  $cDir->cols[5]->tdClass=' class="enpRpt" ';
         }
         $displayData['strDirectory'] = $clsACSearch->clientDirectory();
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      if ($bViaAvailRpt){
         $displayData['pageTitle']    = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                 .' | '.anchor('clients/client_search/searchOpts/true', 'Client Available for Sponsorship', 'class="breadcrumb"')
                                 .' | Results';
      }else {
         $displayData['pageTitle']    = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                 .' | '.anchor('clients/client_search/searchOpts', 'Client Search', 'class="breadcrumb"')
                                 .' | Search Results';
      }

      $displayData['title']        = CS_PROGNAME.' | Clients';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate'] = 'client/search_results';

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function runSearchSetup($idx){
      $idx = (integer)$idx;
      redirect('clients/client_search/run/'.$idx.'/'.bin2hex(trim($_POST['txtSearch'.$idx])));
   }

   function run($idx, $strSearch){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      $idx = (integer)$idx;
      $strSearch = hex2bin($strSearch);
      switch ($idx){
         case 1:            // search client ID
            $lCID = (integer)$strSearch;
            $this->load->model('clients/mclients', 'clsClient');
            if ($this->clsClient->bValidClientID($lCID)){
               redirect('clients/client_record/view/'.$lCID);
            }else {
               $this->session->set_flashdata('error', 'No client records match clientID <b>'.$lCID.'</b>');
               redirect('clients/client_search/searchOpts');
            }
            break;

         case 2:            // search first/last name
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
      if (!bTestForURLHack('showClients')) return;
      $this->load->model('util/msearch_single_generic', 'clsSearch');
      $this->load->model('clients/mclients', 'clsClient');
      $this->load->helper('reports/report_util');

      $params = array('enumStyle' => 'terse');
      $this->load->library('Generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');

      $this->clsSearch->strSearchTerm = $strSearch;

         //-----------------------------
         // search display setup
         //-----------------------------
      $this->clsSearch->enumSearchType      = CENUM_CONTEXT_CLIENT;
      $this->clsSearch->strSearchLabel      = 'Client';
      $this->clsSearch->bShowKeyID          = true;
      $this->clsSearch->bShowSelect         = true;
      $this->clsSearch->bShowEnumSearchType = false;
      $this->clsSearch->strDisplayTitle     =
                '<br>Please select the client</b><br>';

         // landing page for selection
      $this->clsSearch->strPathSelection  = 'clients/client_record/view/';
      $this->clsSearch->strTitleSelection = 'Select client:';

         // landing page for "back"
      $this->clsSearch->strPathSearchAgain  = 'clients/client_search/searchOpts';
      $this->clsSearch->strTitleSearchAgain = 'Search again...';

      $lLeftCnt = strlen($strSearch);
      $this->clsSearch->strWhereExtra = " AND (   (LEFT(cr_strFName, $lLeftCnt)=".strPrepStr($strSearch).")
                                               OR (LEFT(cr_strLName, $lLeftCnt)=".strPrepStr($strSearch).')) ';
                                               
      $this->clsSearch->strIDLabel = 'clientID: ';
      $this->clsSearch->bShowLink  = false;

         // run search
      $displayData['strSearchLabel'] =
                           strLinkAdd_Client('Add new client', true).'&nbsp;'
                          .strLinkAdd_Client('Add new client', false).'<br><br>
                           Searching for clients whose first or last name begins with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
      $this->clsSearch->searchClients($this);
      $displayData['strHTMLSearchResults'] = $this->clsSearch->strHTML_SearchResults();

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Client Search';
      $displayData['pageTitle']    = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                              .' | '.anchor('clients/client_search/searchOpts', 'Search Options', 'class="breadcrumb"')
                              .' | Results';

      $displayData['mainTemplate'] = 'reports/search_select_view';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }







}