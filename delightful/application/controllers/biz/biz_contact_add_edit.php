<?php
class biz_contact_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addNewS1($lBizID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lBizID, 'business ID');

      if (!bTestForURLHack('dataEntryPeopleBizVol')) return;

      $displayData = array();
      $displayData['lBizID'] = $lBizID = (integer)$lBizID;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
      $this->load->model('admin/madmin_aco', 'clsACO');
      $this->load->model('biz/mbiz', 'clsBiz');
      $this->load->library('js_build/js_verify');
      $this->load->model('util/msearch_single_generic', 'clsSearch');
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper('js/simple_search');

      $this->js_verify->clearEmbedOpts();
      $this->js_verify->bShow_bVerifyString    =
      $this->js_verify->bShow_trim             = true;
      $displayData['js']  = $this->js_verify->loadJavaVerify();
      $displayData['js'] .= simpleSearch();

      $this->clsBiz->loadBizRecsViaBID($lBizID);
      $displayData['contextSummary'] = $this->clsBiz->strBizHTMLSummary();


         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Business';
      $displayData['pageTitle']    = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                              .' | '.anchor('biz/biz_record/view/'.$lBizID, 'Business Record', 'class="breadcrumb"')
                              .' | Add Business Contact';

      $displayData['mainTemplate'] = 'biz/contact_s1_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addNewS2($lBizID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lBizID, 'business ID');

      $displayData = array();
      $displayData['lBizID'] = $lBizID = (integer)$lBizID;
      if (!bTestForURLHack('dataEntryPeopleBizVol')) return;

      $this->load->helper('js/simple_search');

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('util/msearch_single_generic', 'clsSearch');
      $this->load->model('people/mpeople', 'clsPeople');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
      $this->load->model('admin/madmin_aco', 'clsACO');
      $this->load->model('biz/mbiz', 'clsBiz');
//      $this->load->helper ('dl_util/email_web');

      $this->clsBiz->loadBizRecsViaBID($lBizID);
      $displayData['contextSummary'] = $this->clsBiz->strBizHTMLSummary();

      $this->clsSearch->strSearchTerm = $strSearch = trim($_POST['txtSearch']);

         //-----------------------------
         // search display setup
         //-----------------------------
      $this->clsSearch->enumSearchType      = CENUM_CONTEXT_PEOPLE;
      $this->clsSearch->strSearchLabel      = 'People';
      $this->clsSearch->bShowKeyID          = true;
      $this->clsSearch->bShowSelect         = true;
      $this->clsSearch->bShowEnumSearchType = false;
      $this->clsSearch->strDisplayTitle     =
                '<br>Please select contact for this business/organization</b><br>';

         // landing page for selection
      $this->clsSearch->strPathSelection  = 'biz/biz_contact_add_edit/contactAddEdit/'.$lBizID.'/0/';
      $this->clsSearch->strTitleSelection = 'Select person';

         // landing page for "back"
      $this->clsSearch->strPathSearchAgain  = 'biz/biz_contact_add_edit/addNewS1/'.$lBizID;
      $this->clsSearch->strTitleSearchAgain = 'Search again...';

      $this->clsBiz->lBID = $lBizID;
      $strContacts = $this->clsBiz->strBizContactPeopleIDList();

      $lLeftCnt = strlen($strSearch);
      $this->clsSearch->strWhereExtra = " AND LEFT(pe_strLName, $lLeftCnt)=".strPrepStr($strSearch).' ';
      if ($strContacts != '') $this->clsSearch->strWhereExtra .= " AND NOT (pe_lKeyID IN ($strContacts)) ";

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
      $displayData['title']        = CS_PROGNAME.' | Business';
      $displayData['pageTitle']    = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                              .' | '.anchor('biz/biz_record/view/'.$lBizID, 'Business Record', 'class="breadcrumb"')
                              .' | Add Business Contact';

      $displayData['mainTemplate'] = 'biz/contact_select_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function contactAddEdit($lBizID, $lContactID, $lPID){
   //---------------------------------------------------------------------
   // note - simple form; no verification required
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lBizID, 'business ID');

      if ($lContactID.'' !='0') verifyID($this, $lContactID, 'business contact ID');

      verifyID($this, $lPID, 'people ID');

      $displayData = array();
      $displayData['formData']   = new stdClass;
      $displayData['lBizID']     = $lBizID = (integer)$lBizID;
      $displayData['lPID']       = $lPID = (integer)$lPID;
      $displayData['lContactID'] = $lContactID = (integer)$lContactID;
      $displayData['bNew']       = $bNew   = $lContactID <= 0;

      if ($bNew){
         if (!bTestForURLHack('dataEntryPeopleBizVol')) return;
      }else {
         if (!bTestForURLHack('editPeopleBizVol')) return;
      }

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
      $this->load->model  ('people/mpeople', 'clsPeople');
      $this->load->model  ('admin/madmin_aco', 'clsACO');
      $this->load->model  ('util/mlist_generic',     'clsList');
      $this->load->model  ('biz/mbiz', 'clsBiz');
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper ('dl_util/web_layout');

      $this->load->library('generic_form');

         // contact info
      $this->clsPeople->loadPeopleViaPIDs($lPID, false, false);
      $displayData['people'] = &$this->clsPeople->people[0];

         // biz info
      $this->clsBiz->loadBizRecsViaBID($lBizID);
      $displayData['bizName'] = $this->clsBiz->bizRecs[0]->strSafeName;
      $displayData['contextSummary'] = $this->clsBiz->strBizHTMLSummary();

      if ($bNew){
         $this->clsBiz->lBID = $lBizID;
         $displayData['lContactID'] = $lContactID = $this->clsBiz->lAddNewBizContact($lPID, -1);
         $bNew = false;
      }

         // contact record info
      $this->clsBiz->lConRecID = $lContactID;
      $this->clsBiz->contactList(false, false, true);
      $contact = &$this->clsBiz->contacts[0];
      $displayData['contactName'] = htmlspecialchars($contact->strFName.' '.$contact->strLName);

      $this->clsList->strBlankDDLName = '';
      $this->clsList->enumListType    = CENUM_LISTTYPE_BIZCONTACTREL;

      $lRelationshipID = $contact->lBizRelID;
      $displayData['formData']->bSoftCash   = $contact->bSoftCash;
      $displayData['formData']->relDDL      = $this->clsList->strLoadListDDL('ddlBizRel', true, $contact->lBizRelID);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['title']        = CS_PROGNAME.' | Business';
      $displayData['pageTitle']    = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                              .' | '.anchor('biz/biz_record/view/'.$lBizID, 'Business Record', 'class="breadcrumb"')
                              .' | Add Business Contact';

      $displayData['mainTemplate'] = 'biz/contact_add_edit_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function contactUpdate($lContactID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('dataEntryPeopleBizVol')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lContactID, 'business contact ID');

      $lContactID = (integer)$lContactID;
      $lBizRelID  = (integer)trim($_POST['ddlBizRel']);
      if ($lBizRelID <= 0) $lBizRelID = null;
      $bSoftCash  = @$_POST['chkSoft']=='TRUE';

      $this->load->model('biz/mbiz', 'clsBiz');
//      $this->load->helper('dl_util/email_web');
      $this->clsBiz->lConRecID = $lContactID;
      $this->clsBiz->contactList(false, false, true);
      $lBizID = $this->clsBiz->contacts[0]->lBizID;

      $this->clsBiz->updateBizContact($lContactID, $lBizRelID, $bSoftCash);
      $this->session->set_flashdata('msg', 'The business contact record was updated');
      redirect_Biz($lBizID);
   }

   function removeContact($lBizID, $lContactID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('editPeopleBizVol')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lBizID, 'business ID');
      verifyID($this, $lContactID, 'business contact ID');

      $lBizID     = (integer)$lBizID;
      $lContactID = (integer)$lContactID;

      $this->load->model('biz/mbiz', 'clsBiz');
      $lGroupID = null;
      $this->clsBiz->retireSingleBizCon($lContactID, $lGroupID);

      $this->session->set_flashdata('msg', 'The business contact record was removed');
      redirect_Biz($lBizID);
   }

}