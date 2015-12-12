<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ppquest_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function addEditQuest($lQuestID, $lPPTestID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPPTestID, 'pre/post test ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lPPTestID'] = $lPPTestID = (integer)$lPPTestID;
      $displayData['lQuestID']  = $lQuestID = (integer)$lQuestID;
      $displayData['bNew']      = $bNew = $lQuestID <= 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('client_features/mcpre_post_tests', 'cpptests');
      $this->load->model  ('admin/mpermissions',               'perms');
      $this->load->helper ('clients/link_client_features');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper ('dl_util/web_layout');

         //-------------------------
         // load the pre/post test
         //-------------------------
      $this->cpptests->loadPPTestsViaPPTID($lPPTestID);
      $pptest = &$this->cpptests->pptests[0];

         //-------------------------
         // load the question
         //-------------------------
      $this->cpptests->loadQuestionsViaQuestID($lQuestID);
      $quest = &$this->cpptests->questions[0];

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtQuestion', 'Question', 'trim|required');
		$this->form_validation->set_rules('txtAnswer',   'Answer',   'trim|required');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['formData']->txtQuestion    = htmlspecialchars($quest->strQuestion.'');
            $displayData['formData']->txtAnswer      = htmlspecialchars($quest->strAnswer.'');
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtQuestion    = set_value('txtQuestion');
            $displayData['formData']->txtAnswer      = set_value('txtAnswer');
         }

         $displayData['strTestSummary'] = $this->cpptests->strHTMLPPTestSummaryDisplay();

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      =
                                       anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                .' | '.anchor('cpre_post_tests/pptests/overview', ' Client Pre/Post Tests', 'class="breadcrumb"')
                                .' | '.anchor('cpre_post_tests/pptest_record/view/'.$lPPTestID,
                                                     htmlspecialchars($pptest->strTestName), 'class="breadcrumb"')
                                .' | '.($bNew ? 'Add New' : 'Edit').'  Question';

         $displayData['title']          = CS_PROGNAME.' | Client Pre/Post Tests';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'cpre_post_tests/quest_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {

         $quest->strQuestion = trim($_POST['txtQuestion']);
         $quest->strAnswer   = trim($_POST['txtAnswer']);

            //------------------------------------
            // update db tables and return
            //------------------------------------
         if ($bNew){
            $this->load->library('util/up_down_top_bottom');
            $lQuestID = $this->cpptests->addNewQuestion($lPPTestID);
            $this->session->set_flashdata('msg', 'Pre/Post Question added');
         }else {
            $this->cpptests->updateQuestion();
            $this->session->set_flashdata('msg', 'Pre/Post Question updated');
         }
         redirect('cpre_post_tests/ppquest_view/viewQuestions/'.$lPPTestID);
      }
   }

   function moveQuest($lPPTestID, $lQuestID, $enumMove){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;

      $lPPTestID = (int)$lPPTestID;
      $lQuestID  = (int)$lQuestID;

      $this->load->library('util/up_down_top_bottom', '', 'upDown');

      $this->upDown->enumMove            = $enumMove;
      $this->upDown->enumRecType         = 'pptest question';

      $this->upDown->strUfieldDDL        = 'cpp_questions';
      $this->upDown->strUfieldDDLKey     = 'cpq_lKeyID';
      $this->upDown->strUfieldDDLSort    = 'cpq_lSortIDX';
      $this->upDown->strUfieldDDLQual1   = 'cpq_lPrePostID';
      $this->upDown->strUfieldDDLRetired = 'cpq_bRetired';
      $this->upDown->lUfieldDDLQual1Val  = $lPPTestID;
      $this->upDown->lKeyID              = $lQuestID;

      $this->upDown->moveRecs();

      $this->session->set_flashdata('msg', 'The entries were re-ordered');
      redirect('cpre_post_tests/ppquest_view/viewQuestions/'.$lPPTestID);
   }

   function remove($lQuestID, $lPPTestID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPPTestID, 'pre/post test ID');

          //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('client_features/mcpre_post_tests', 'cpptests');
      $this->load->model  ('admin/mpermissions',               'perms');

         //-------------------------
         // load the pre/post test
         //-------------------------
      $this->cpptests->loadPPTestsViaPPTID($lPPTestID);
      $pptest = &$this->cpptests->pptests[0];

      $this->cpptests->removeQuestion($lQuestID);

      $this->session->set_flashdata('msg', 'The question was removed from test <b>'
                            .htmlspecialchars($pptest->strTestName).'</b>');
      redirect('cpre_post_tests/ppquest_view/viewQuestions/'.$lPPTestID);
   }


}