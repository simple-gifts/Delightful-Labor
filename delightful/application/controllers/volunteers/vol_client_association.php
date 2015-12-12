<?php
class vol_client_association extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addS1($lVolID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lVolID, 'volunteer ID');

      if (!bTestForURLHack('dataEntryPeopleBizVol')) return;
      if (!bTestForURLHack('showClients')) return;

      $displayData = array();
      $displayData['lVolID'] = $lVolID = (integer)$lVolID;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
      $this->load->library('js_build/js_verify');
      $this->load->model  ('vols/mvol', 'clsVol');
      $this->load->model  ('people/mpeople');
      $this->load->model  ('util/msearch_single_generic', 'clsSearch');
      $this->load->helper('js/simple_search');

      $this->js_verify->clearEmbedOpts();
      $this->js_verify->bShow_bVerifyString    =
      $this->js_verify->bShow_trim             = true;
      $displayData['js']  = $this->js_verify->loadJavaVerify();
      $displayData['js'] .= simpleSearch();

      $this->clsVol->loadVolRecsViaVolID($lVolID, false);
      $displayData['contextSummary'] = $this->clsVol->volHTMLSummary(0);

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Volunteers';
      $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                              .' | '.anchor('volunteers/vol_record/volRecordView/'.$lVolID, 'Volunteer Record', 'class="breadcrumb"')
                              .' | Add Volunteer/Client Association';

      $displayData['mainTemplate'] = 'vols/vol_client_assoc_s1_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addS2($lVolID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lVolID, 'volunteer ID');

      $displayData = array();
      $displayData['lVolID'] = $lVolID = (integer)$lVolID;
      if (!bTestForURLHack('dataEntryPeopleBizVol')) return;
      if (!bTestForURLHack('showClients')) return;

      $this->load->helper('js/simple_search');

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('util/msearch_single_generic', 'clsSearch');
      $this->load->model('people/mpeople', 'clsPeople');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',  'clsDateTime');
      $this->load->model  ('admin/madmin_aco', 'clsACO');
      $this->load->model  ('vols/mvol', 'clsVol');
      $this->load->helper ('reports/report_util');

      $this->clsVol->loadVolRecsViaVolID($lVolID, false);
      $displayData['contextSummary'] = $this->clsVol->volHTMLSummary(0);

      $this->clsSearch->strSearchTerm = $strSearch = trim($_POST['txtSearch']);

         //-----------------------------
         // search display setup
         //-----------------------------
      $this->clsSearch->enumSearchType      = CENUM_CONTEXT_CLIENT;
      $this->clsSearch->strSearchLabel      = 'Client';
      $this->clsSearch->bShowKeyID          = true;
      $this->clsSearch->bShowSelect         = true;
      $this->clsSearch->bShowEnumSearchType = false;
      $this->clsSearch->strDisplayTitle     =
                '<br>Please select the client associated with this volunteer</b><br>';

         // landing page for selection
      $this->clsSearch->strPathSelection  = 'volunteers/vol_client_association/clientAddEdit/'.$lVolID.'/';
      $this->clsSearch->strTitleSelection = 'Select client';

         // landing page for "back"
      $this->clsSearch->strPathSearchAgain  = 'volunteers/vol_client_association/addS1/'.$lVolID;
      $this->clsSearch->strTitleSearchAgain = 'Search again...';

         // exclude existing associations
      $this->clsVol->loadVolClientAssociations($lVolID, $volClient);

      $lLeftCnt = strlen($strSearch);
      $this->clsSearch->strWhereExtra = " AND LEFT(cr_strLName, $lLeftCnt)=".strPrepStr($strSearch).' ';
      if (count($volClient) > 0) {
         $cIDs = array();
         foreach ($volClient as $vca) $cIDs[] = $vca->lClientID;
         $this->clsSearch->strWhereExtra .= ' AND NOT (cr_lKeyID IN ('.implode(',', $cIDs).')) ';
      }

      $this->clsSearch->strIDLabel = 'clientID: ';
      $this->clsSearch->bShowLink  = false;

         // run search
      $displayData['strSearchLabel'] =
                          'Searching for '.$this->clsSearch->enumSearchType.' that begin with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
      $this->clsSearch->searchClients($this);
      $displayData['strHTMLSearchResults'] = $this->clsSearch->strHTML_SearchResults();

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Volunteers';
      $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                              .' | '.anchor('volunteers/vol_record/volRecordView/'.$lVolID, 'Volunteer Record', 'class="breadcrumb"')
                              .' | Add Volunteer/Client Association';

      $displayData['mainTemplate'] = 'vols/vol_client_assoc_s2_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function clientAddEdit($lVolID, $lClientID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lVolID, 'volunteer ID');
      verifyID($this, $lClientID, 'client ID');

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model  ('vols/mvol', 'clsVol');

      $lClientID = (integer)$lClientID;
      $lVolID    = (integer)$lVolID;

      $va = new stdClass;
      $va->lVolID    = $lVolID;
      $va->lClientID = $lClientID;
      $va->strNotes  = '';
      $this->clsVol->addVolAssociation($va);
      $this->session->set_flashdata('msg', 'The volunteer/client association was added.');
      redirect_Vol($lVolID);
   }

   function remove($lVAID, $lVolID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lVAID = (integer)$lVAID;
      if (!bTestForURLHack('dataEntryPeopleBizVol')) return;
      if (!bTestForURLHack('showClients')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lVolID, 'volunteer ID');

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model  ('vols/mvol', 'clsVol');

      $this->clsVol->removeVolClientAssoc($lVAID);

      $this->session->set_flashdata('msg', 'The volunteer/client association was removed.');
      redirect_Vol($lVolID);
   }

}
