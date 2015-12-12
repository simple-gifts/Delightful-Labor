<?php
class vol_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEditS1(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('editPeopleBizVol')) return;
      $displayData = array();

      $this->load->library('js_build/js_verify');
      $this->load->model('util/msearch_single_generic', 'clsSearch');
      $this->load->model('people/mpeople',  'clsPeople');
      $this->load->helper('js/simple_search');

      $this->js_verify->clearEmbedOpts();
      $this->js_verify->bShow_bVerifyString    =
      $this->js_verify->bShow_trim             = true;
      $displayData['js']  = $this->js_verify->loadJavaVerify();
      $displayData['js'] .= simpleSearch();

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Add New Volunteer';
      $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                              .' | Add New Volunteers';

      $displayData['mainTemplate'] = 'vols/add_new_search_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addEditS2(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('editPeopleBizVol')) return;
      $displayData = array();

      $this->load->helper('js/simple_search');

      $this->load->model('util/msearch_single_generic', 'clsSearch');
      $this->load->model('people/mpeople', 'clsPeople');

      $this->clsSearch->strSearchTerm = $strSearch = trim($_POST['txtSearch']);


         //-----------------------------
         // search display setup
         //-----------------------------
      $this->clsSearch->enumSearchType      = CENUM_CONTEXT_PEOPLE;
      $this->clsSearch->strSearchLabel      = 'People';
      $this->clsSearch->bShowKeyID          = true;
      $this->clsSearch->strIDLabel          = 'peopleID: ';
      $this->clsSearch->bShowLink           = false;
      $this->clsSearch->bShowSelect         = true;
      $this->clsSearch->bShowEnumSearchType = false;
      $this->clsSearch->strDisplayTitle     =
                '<br>Please select the person to add as a volunteer</b><br>';

         // landing page for selection
      $this->clsSearch->strPathSelection  = 'volunteers/vol_add_edit/addNewS3/';
      $this->clsSearch->strTitleSelection = 'Select person';

         // landing page for "back"
      $this->clsSearch->strPathSearchAgain  = 'volunteers/vol_add_edit/addEditS1';
      $this->clsSearch->strTitleSearchAgain = 'Search again...';

      $lLeftCnt = strlen($strSearch);
      $this->clsSearch->strWhereExtra = " AND LEFT(pe_strLName, $lLeftCnt)=".strPrepStr($strSearch).' ';

         // run search
      $displayData['strSearchLabel'] =
                          'Searching for '.$this->clsSearch->enumSearchType.' that begin with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
      $this->clsSearch->searchNewVolunteer();
      $displayData['strHTMLSearchResults'] = $this->clsSearch->strHTML_SearchResults();

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Add new volunteer';
      $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                              .' | Add New Volunteers';

      $displayData['mainTemplate'] = 'vols/search_sel_person_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addNewS3($lPID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('editPeopleBizVol')) return;
      $displayData = array();
      $displayData['lPID'] = $lPID = (integer)$lPID;

      $this->load->model ('vols/mvol',                           'clsVol');
      $this->load->model ('people/mpeople',                      'clsPeople');
      $this->load->model ('personalization/muser_fields',        'clsUF');
      $this->load->model ('personalization/muser_fields_create', 'clsUFC');
      $this->load->model ('admin/mpermissions',                  'perms');
//      $this->load->helper('dl_util/email_web');

      $this->clsVol->loadVolRecsViaPeopleID($lPID, true);

      $lVolID = $this->clsVol->volRecs[0]->lKeyID;
      if (is_null($lVolID)){
         $this->clsVol->volRecs[0]->lPeopleID = $lPID;
         $this->clsVol->volRecs[0]->Notes     = '';
         $lVolID = $this->clsVol->lAddNewVolunteer();
         $this->session->set_flashdata('msg', 'Volunteer added!');
      }else {
         $this->clsVol->activateDeactivateVolunteer($lVolID, true);
         $this->session->set_flashdata('msg', 'Volunteer reactivated!');
      }
      redirect_VolRec($lVolID);
   }

   function addViaPID($lPeopleID){
      if (!bTestForURLHack('editPeopleBizVol')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPeopleID, 'people ID');

      $this->load->model('personalization/muser_fields',        'clsUF');
      $this->load->model('personalization/muser_fields_create', 'clsUFC');
      $this->load->model('admin/mpermissions',                  'perms');
      $this->load->model('vols/mvol', 'clsVol');

      $this->clsVol->volRecs[0]->lPeopleID = $lPeopleID;
      $this->clsVol->volRecs[0]->Notes     = '';
      $lVolID = $this->clsVol->lAddNewVolunteer();
      $this->session->set_flashdata('msg', 'This person added as a volunteer!');

      redirect_People($lPeopleID);
   }

   function editVolSkills($lVolID='0'){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $gbDev, $gbVolLogin, $gVolPerms, $glVolPeopleID;

      if (!(bAllowAccess('dataEntryPeopleBizVol') || bAllowAccess('volJobSkills'))){
         bTestForURLHack('forceFail');
         return;
      }

         //--------------------------------
         // Models & Helpers
         //--------------------------------
      $this->load->model('vols/mvol',        'clsVol');
      $this->load->model('vols/mvol_skills', 'clsVolSkills');
      $this->load->model('people/mpeople',   'clsPeople');

//      $this->load->helper('dl_util/email_web');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/record_view');
      $this->load->helper('img_docs/link_img_docs');
      $this->load->library('generic_form');
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);
      $displayData['clsForm'] = $this->generic_form;

      if ($gbVolLogin){
         $this->clsVol->loadVolRecsViaPeopleID($glVolPeopleID, true);
         $lVolID = $this->clsVol->volRecs[0]->lKeyID;
      }

      $displayData = array();
      $displayData['lVolID'] = $lVolID = (integer)$lVolID;

      $this->clsVol->lVolID = $this->clsVolSkills->lVolID = $lVolID;

      $this->clsVol->loadVolRecsViaVolID($lVolID, true);
      if (!$gbVolLogin){
         $displayData['contextSummary'] = $this->clsVol->volHTMLSummary(0);
      }

      $this->clsVolSkills->loadSkillsPlusVolSkills();
      $displayData['lNumSingleVolSkills'] = $this->clsVolSkills->lNumSingleVolSkills;
      $displayData['singleVolSkills']     = &$this->clsVolSkills->singleVolSkills;

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      if ($gbVolLogin){
         $displayData['pageTitle']    = 'Volunteer Skills';
      }else {
         $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                                 .' | '.anchor('volunteers/vol_record/volRecordView/'.$lVolID, 'Record', 'class="breadcrumb"')
                                 .' | Volunteer Skills';
      }
      $displayData['mainTemplate'] = array('vols/vol_skills_update_view');
      $displayData['title']        = CS_PROGNAME.' | Volunteer Skills';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function editVolSkillsUpdate($lVolID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $gbDev, $gbVolLogin, $gVolPerms, $glVolPeopleID;
      $this->load->model('vols/mvol_skills', 'clsVolSkills');

      if (!(bAllowAccess('dataEntryPeopleBizVol') || bAllowAccess('volJobSkills'))){
         bTestForURLHack('forceFail');
         return;
      }
      if ($gbVolLogin){
         $this->load->model('vols/mvol',        'clsVol');
         $this->load->model('people/mpeople',   'clsPeople');
         $this->clsVol->loadVolRecsViaPeopleID($glVolPeopleID, true);
         $lVolID = $this->clsVol->volRecs[0]->lKeyID;
      }

      $lVolID = (integer)$lVolID;

      $this->clsVolSkills->lVolID = $lVolID;

      $this->clsVolSkills->clearVolSkills();

      if (count($_REQUEST['chkSkill']) > 0){
         foreach($_REQUEST['chkSkill'] as $lSkillID){
            $this->clsVolSkills->setVolSkill((integer)$lSkillID);
         }
      }
      if ($gbVolLogin){
         $this->session->set_flashdata('msg', 'Thank you for updating your volunteer skills!');
         redirect('vol_reg/job_skills/update');
      }else {
         $this->session->set_flashdata('msg', 'Volunteer skills updated!');
         redirect_VolRec($lVolID);
      }
   }

   function actDeact($lVolID, $strSetActive){
      $lVolID = (integer)$lVolID;
      $bSetActive = $strSetActive=='true';
      $this->load->model('vols/mvol',       'clsVol');

      $this->clsVol->activateDeactivateVolunteer($lVolID, $bSetActive);
      redirect_VolRec($lVolID);
   }
















}