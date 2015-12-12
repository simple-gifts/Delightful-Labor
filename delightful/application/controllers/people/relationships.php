<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class relationships extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEditS1($lPID){
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPID, 'people ID');

      $displayData = array();
      $lPID   = (integer)$lPID;

      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->model('people/mpeople',  'clsPeople');
      $this->load->model('admin/madmin_aco', 'clsACO');
//      $this->load->helper('dl_util/email_web');
      $this->clsPeople->loadPeopleViaPIDs($lPID, false, false);
      $displayData['contextSummary'] = $this->clsPeople->peopleHTMLSummary(0);


         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtSearch', 'SEARCH', 'trim|required');
      $bFormValidated = $this->form_validation->run();

      $displayData['title']        = CS_PROGNAME.' | Relationships';
      $displayData['pageTitle']  = anchor('main/menu/people',                'People', 'class="breadcrumb"')
                            .' | '.anchor('people/people_record/view/'.$lPID, 'Record', 'class="breadcrumb"')
                            .' | Relationships';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
		if (!$bFormValidated){

         $displayData['search'] = new stdClass;
         $displayData['search']->strButtonLabel = 'Search';
         $displayData['search']->strLegendLabel = 'Create a relationship between '.$this->clsPeople->people[0]->strSafeName.' and another person';
         $displayData['search']->formLink = 'people/relationships/addEditS1/'.$lPID;

         $displayData['search']->lSearchTableWidth = 240;
         $displayData['search']->bBiz = false;

         $displayData['mainTemplate'] = 'util/search_people_biz_view';
         $this->load->vars($displayData);
         $this->load->view('template');

      }else {
         $this->searchSelected(
                       $displayData,  $lPID, 'People Search',
                       xss_clean(trim($_POST['txtSearch'])));

      }
   }

   private function searchSelected(
                           &$displayData, $lPID, $strTitle,
                           $strSearch){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->model('util/msearch_single_generic', 'clsSearch');

         //-----------------------------
         // search display setup
         //-----------------------------
      $this->clsSearch->enumSearchType      = CENUM_CONTEXT_PEOPLE;
      $this->clsSearch->strSearchLabel      = 'People';
      $this->clsSearch->bShowKeyID          = true;
      $this->clsSearch->bShowSelect         = true;
      $this->clsSearch->strIDLabel          = 'peopleID: ';
      $this->clsSearch->bShowLink           = false;
      $this->clsSearch->bShowEnumSearchType = false;
      $this->clsSearch->strDisplayTitle     =
                '<br>Please select the person for this new relationship</b><br>';

         // landing page for selection
      $this->clsSearch->strPathSelection  = 'people/relationships/setRelType/'.$lPID.'/';
      $this->clsSearch->strTitleSelection = 'Select person';

         // landing page for "back"
      $this->clsSearch->strPathSearchAgain  = 'people/relationships/addEditS1/'.$lPID;
      $this->clsSearch->strTitleSearchAgain = 'Search again...';

      $lLeftCnt = strlen($strSearch);
      $this->clsSearch->strSearchTerm = $strSearch;
      $this->clsSearch->strWhereExtra =
                               " AND (pe_lKeyID != $lPID)
                                 AND LEFT(pe_strLName, $lLeftCnt)=".strPrepStr($strSearch)." ";

         // run search
      $displayData['strSearchLabel'] =
                          'Searching for '.$this->clsSearch->enumSearchType.' that begin with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
      $this->clsSearch->searchPeople();
      $displayData['strHTMLSearchResults'] = $this->clsSearch->strHTML_SearchResults();

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Relationships';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = 'people/search_sel_person_view';

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function edit($lRelID, $strA2B){
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lRelID, 'relationship ID');
      $bA2B = $strA2B=='true';
      $this->load->model('people/mrelationships', 'clsRel');
      $this->clsRel->relationshipInfoViaRelID($lRelID);

      if ($bA2B){
         $lPID_A     = $this->clsRel->lPersonID_A;
         $lPID_B     = $this->clsRel->lPersonID_B;
         $lRelID_A2B = $this->clsRel->lRelID;
         $lRelID_B2A = null;
         $bShowA = true;
         $bShowB = false;
      }else {
         $lPID_A     = $this->clsRel->lPersonID_B;
         $lPID_B     = $this->clsRel->lPersonID_A;
         $lRelID_A2B = null;
         $lRelID_B2A = $this->clsRel->lRelID;
         $bShowA = false;
         $bShowB = true;
      }
      $this->setRelType($lPID_A, $lPID_B, $lRelID_A2B, $lRelID_B2A, $bShowA, $bShowB);
   }

   function setRelType($lPeople_A_ID,    $lPeople_B_ID,
                       $lRelID_A2B=0,    $lRelID_B2A=0,
                       $bShowA=true,     $bShowB=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPeople_A_ID, 'people ID');
      verifyID($this, $lPeople_B_ID, 'people ID');

      $bNew = $lRelID_A2B==0 && $lRelID_B2A==0;

      $bShowA = (boolean)$bShowA;
      $bShowB = (boolean)$bShowB;

      $displayData = array();
      $displayData['relInfo'] = new stdClass;
      $displayData['lPeople_A_ID'] = $lPeople_A_ID = (integer)$lPeople_A_ID;
      $displayData['lPeople_B_ID'] = $lPeople_B_ID = (integer)$lPeople_B_ID;

         //-------------------------
         // load models
         //-------------------------
      $this->load->model('people/mpeople');
      $this->load->model('admin/madmin_aco', 'clsACO');
      $this->load->model('people/mrelationships', 'clsRel');
//      $this->load->helper('dl_util/email_web');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

         //-----------------------------
         // validation rules
         //-----------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
//		$this->form_validation->set_rules('ddlRel_A',       'Relationship A to B', 'trim|required');
		$this->form_validation->set_rules('ddlRel_A',       'Relationship A to B', 'trim'.($lRelID_A2B || $bNew ? '|required' : ''));
		$this->form_validation->set_rules('ddlRel_B',       'Relationship B to A', 'trim'.($lRelID_B2A          ? '|required' : ''));
		$this->form_validation->set_rules('chkSoftCash_A',  'Soft Cash (A)', 'trim');
		$this->form_validation->set_rules('chkSoftCash_B',  'Soft Cash (B)', 'trim|callback_relBSoftCash');
      $this->form_validation->set_rules('txtNotes_A',     'Notes', 'trim');
      $this->form_validation->set_rules('txtNotes_B',     'Notes', 'trim');

		if ($this->form_validation->run() == FALSE){
         $displayData['relInfo']->lRelID_A2B = $lRelID_A2B;
         $displayData['relInfo']->lRelID_B2A = $lRelID_B2A;

         $displayData['bShowA'] = $bShowA;
         $displayData['bShowB'] = $bShowB;

         $clsPersonA = new mpeople;
         $clsPersonB = new mpeople;
         $clsPersonA->loadPeopleViaPIDs($lPeople_A_ID, false, false);
         $displayData['contextSummary'] = $clsPersonA->peopleHTMLSummary(0);

         $clsPersonA->loadPeopleViaPIDs($lPeople_A_ID, false, false);
         $clsPersonB->loadPeopleViaPIDs($lPeople_B_ID, false, false);
         $displayData['strSafeName_A'] = $clsPersonA->people[0]->strSafeName;
         $displayData['strSafeName_B'] = $clsPersonB->people[0]->strSafeName;

         $displayData['relInfo']->lRelNameID_B2A      = null;
         $displayData['relInfo']->lRelNameID_A2B      = null;

         if (validation_errors()==''){
            if ($bNew){

               $displayData['relInfo']->strRelationship_A2B = '';
               $displayData['relInfo']->bSoftMoneyShare_A2B = false;
               $displayData['relInfo']->strNotes_A2B        = '';

               $displayData['relInfo']->strRelationship_B2A = '';
               $displayData['relInfo']->bSoftMoneyShare_B2A = false;
               $displayData['relInfo']->strNotes_B2A        = '';

               $displayData['bShowA'] = $displayData['bShowB'] = true;
               $displayData['strRelDDL_A'] = $this->clsRel->strPeopleRelationshipsDDL(true, $displayData['relInfo']->lRelNameID_A2B);
               $displayData['strRelDDL_B'] = $this->clsRel->strPeopleRelationshipsDDL(true, $displayData['relInfo']->lRelNameID_B2A);
            }else {
               $displayData['relInfo']->bSoftMoneyShare_A2B =
               $displayData['relInfo']->bSoftMoneyShare_B2A = false;
               if ($bShowA){
                  $this->clsRel->relationshipInfoViaRelID($lRelID_A2B);
                  $displayData['relInfo']->bSoftMoneyShare_A2B = $this->clsRel->bSoftCash;
                  $displayData['relInfo']->strNotes_A2B        = $this->clsRel->strNotes;
                  $displayData['strRelDDL_A'] = $this->clsRel->strPeopleRelationshipsDDL(
                                                          true, $this->clsRel->lRelNameID);
               }
               if ($bShowB){
                  $this->clsRel->relationshipInfoViaRelID($lRelID_B2A);
                  $displayData['relInfo']->bSoftMoneyShare_B2A = $this->clsRel->bSoftCash;
                  $displayData['relInfo']->strNotes_B2A        = $this->clsRel->strNotes;
                  $displayData['strRelDDL_B'] = $this->clsRel->strPeopleRelationshipsDDL(
                                                          true, $this->clsRel->lRelNameID);
               }
            }
         }else {
            setOnFormError($displayData);
            $displayData['relInfo']->bSoftMoneyShare_A2B = set_value('chkSoftCash_A')=='TRUE';
            $displayData['relInfo']->strNotes_A2B        = set_value('txtNotes_A');
            $displayData['strRelDDL_A'] = $this->clsRel->strPeopleRelationshipsDDL(true, set_value('ddlRel_A'));


            $displayData['relInfo']->bSoftMoneyShare_B2A = set_value('chkSoftCash_B')=='TRUE';
            $displayData['relInfo']->strNotes_B2A        = set_value('txtNotes_B');
            $displayData['strRelDDL_B'] = $this->clsRel->strPeopleRelationshipsDDL(true, set_value('ddlRel_B'));
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']  = anchor('main/menu/people',                        'People', 'class="breadcrumb"')
                               .' | '.anchor('people/people_record/view/'.$lPeople_A_ID, 'Record', 'class="breadcrumb"')
                               .' | Relationships';

         $displayData['title']      = CS_PROGNAME.' | People';
         $displayData['nav']        = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'people/relationship_add_edit';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
//echo(__FILE__.' '.__LINE__.'<br>'."\n"); die;
         $this->addUpdate($lPeople_A_ID, $lPeople_B_ID, $lRelID_A2B, $lRelID_B2A, $bShowA, $bShowB);
      }
   }

   function relBSoftCash($strSoftCashB){
      if ($strSoftCashB.'' != 'TRUE') return(true);
      return($_POST['ddlRel_B'].'' != '');
   }


   function addUpdate($lPID_A, $lPID_B, $lRelID_A2B, $lRelID_B2A, $bShowA, $bShowB){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $bUpdateA2B = $bShowA;
      $bUpdateB2A = $bShowB;

      $lPID_A   = (integer)$lPID_A;
      $lPID_B   = (integer)$lPID_B;

      $lRelID_A2B   = (integer)$lRelID_A2B;
      $lRelID_B2A   = (integer)$lRelID_B2A;

      $this->load->model('people/mrelationships', 'clsRel');

         //-----------------------------
         // relationship A -> B
         //-----------------------------
      $bAnyUpdate = false;
      if ($bUpdateA2B) {
         $bNewA2B      = $lRelID_A2B <= 0;

         $lRelTypeID_A2B  = (integer)$_POST['ddlRel_A'];
         $bAnyUpdate = true;
         $bSoftCashA2B   = @$_POST['chkSoftCash_A']=='TRUE';
         $strNotesA2B    = trim($_POST['txtNotes_A']);
         $this->clsRel->lSavePeopleRelationship(
                      $lRelID_A2B,
                      $lPID_A,  $lPID_B, $lRelTypeID_A2B,
                      $bSoftCashA2B, $strNotesA2B);
      }

         //-----------------------------
         // relationship B -> A
         //-----------------------------
      $bAnyUpdate = false;
//echo('$bUpdateB2A='.($bUpdateB2A ? 'true' : 'false').'<br>'); die;
      if ($bUpdateB2A) {
         $bNewB2A      = $lRelID_B2A <= 0;

         $lRelTypeID_B2A  = (integer)$_POST['ddlRel_B'];
         $bAnyUpdate = true;
         $bSoftCashB2A   = @$_POST['chkSoftCash_B']=='TRUE';
         $strNotesB2A    = trim($_POST['txtNotes_B']);
         $this->clsRel->lSavePeopleRelationship(
                      $lRelID_B2A,
                      $lPID_B,  $lPID_A, $lRelTypeID_B2A,
                      $bSoftCashB2A, $strNotesB2A);
      }
      $this->session->set_flashdata('msg', 'The relationships were updated');
      redirect('people/people_record/view/'.$lPID_A);
   }

   function remove($lRelationshipID, $lBasePID){
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lBasePID, 'people ID');
      verifyID($this, $lRelationshipID, 'relationship ID');

      $lBasePID = (integer)$lBasePID;
      $this->load->model('people/mrelationships', 'clsRel');
      $this->clsRel->lRelID = (integer)$lRelationshipID;
      $this->clsRel->relationshipInfoViaRelID($lRelationshipID);
      $this->clsRel->removeRelViaRelID();

      $this->session->set_flashdata('msg',
               'The selected relationship between <b>'
                 .htmlspecialchars($this->clsRel->strFName_A.' '.$this->clsRel->strLName_A)
                 .' </b>and <b>'
                 .htmlspecialchars($this->clsRel->strFName_B.' '.$this->clsRel->strLName_B)
              .'</b> was removed.');
      redirect('people/people_record/view/'.$lBasePID);
   }


}