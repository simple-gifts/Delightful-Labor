<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pptest_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function addEditPPTest($lPPTestID='', $lCatID=-1){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;

      $this->load->helper('dl_util/verify_id');
      if ($lPPTestID.'' != '0') verifyID($this, $lPPTestID, 'pre/post test ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lPPTestID'] = $lPPTestID = (integer)$lPPTestID;
      $displayData['bNew']      = $bNew = $lPPTestID <= 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model('client_features/mcpre_post_tests', 'cpptests');
      $this->load->model('util/mlist_generic',               'clsList');
      $this->load->model('admin/mpermissions',               'perms');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper ('dl_util/web_layout');
      
         // make sure there are pre/post test categories in the system
      $this->clsList->enumListType = CENUM_LISTTYPE_CPREPOSTCAT;
      if ($this->clsList->lListCnt()==0){
         $this->noCatError($displayData);
         return;      
      }

         // load the pre/post test
      $this->cpptests->loadPPTestsViaPPTID($lPPTestID);
      $pptest = &$this->cpptests->pptests[0];

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtTestName',    'Test Name',  'trim|required|callback_verifyUniquePPTestName['.$lPPTestID.']');
		$this->form_validation->set_rules('txtDescription', 'Description',   'trim');
		$this->form_validation->set_rules('chkHidden',      'Hidden',        'trim');
		$this->form_validation->set_rules('chkPublished',   'Published',     'trim');
		$this->form_validation->set_rules('ddlPPCat',       'Test Category', 'trim|callback_verifyDDLSel');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($bNew){
               $lCatID = (int)$lCatID;
            }else {
               $lCatID = $pptest->lTestCatID;
            }
            
            $displayData['formData']->txtTestName    = htmlspecialchars($pptest->strTestName.'');
            $displayData['formData']->txtDescription = htmlspecialchars($pptest->strDescription.'');
            $displayData['formData']->bHidden        = $pptest->bHidden;
            $displayData['formData']->bPublished     = $pptest->bPublished;
            
               // load the pre/post category ddl
            $displayData['formData']->strCatDDL     = $this->clsList->strLoadListDDL('ddlPPCat', true, $lCatID);
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtTestName    = set_value('txtTestName');
            $displayData['formData']->txtDescription = set_value('txtDescription');
            $displayData['formData']->bHidden        = set_value('chkHidden') =='true';
            $displayData['formData']->bPublished     = set_value('bPublished')=='true';
            
               // load the pre/post category ddl
            $displayData['formData']->strCatDDL     = 
                  $this->clsList->strLoadListDDL('ddlPPCat', true, set_value('ddlPPCat'));
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      =
                                       anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                .' | '.anchor('cpre_post_tests/pptests/overview', ' Client Pre/Post Tests', 'class="breadcrumb"')
                                .' | '.($bNew ? 'Add New' : 'Edit').'  Pre/Post Test';

         $displayData['title']          = CS_PROGNAME.' | Client Pre/Post Tests';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'cpre_post_tests/pp_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $pptest->strTestName    = trim($_POST['txtTestName']);
         $pptest->strDescription = trim($_POST['txtDescription']);
         $pptest->bHidden        = trim(@$_POST['chkHidden'])   =='true';
         $pptest->bPublished     = trim(@$_POST['chkPublished'])=='true';
         $pptest->lTestCatID     = (int)trim($_POST['ddlPPCat']);

            //------------------------------------
            // update db tables and return
            //------------------------------------
         if ($bNew){
            $lPPTestID = $this->cpptests->addNewPrePostTest();
            $this->session->set_flashdata('msg', 'Pre/Post Test added');
         }else {
            $this->cpptests->updatePrePost();
            $this->session->set_flashdata('msg', 'Pre/Post Test Record updated');
         }
         redirect('cpre_post_tests/pptest_record/view/'.$lPPTestID);
      }
   }
   
   function noCatError($displayData){
      $displayData['pageTitle']      =
                                    anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                             .' | '.anchor('cpre_post_tests/pptests/overview', ' Client Pre/Post Tests', 'class="breadcrumb"')
                             .' | Error';

      $displayData['title']          = CS_PROGNAME.' | Client Pre/Post Tests';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'cpre_post_tests/pp_err_nocats_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function verifyDDLSel($strDDLVal){
      $lDDLVal = (int)$strDDLVal;
      if ($lDDLVal <= 0){
         $this->form_validation->set_message('verifyDDLSel', 'Please select a Pre/Post Test Category');
         return(false);
      }else {
         return(true);
      }
   }

   function verifyUniquePPTestName($strTestName, $id){
      $id = (integer)$id;
      $strTestName = xss_clean(trim($strTestName));
      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                $strTestName, 'cpp_strTestName',
                $id,   'cpp_lKeyID',
                true,  'cpp_bRetired',
                false, null, null,
                false, null, null,
                'cpp_tests')){
         $this->form_validation->set_message('verifyUniquePPTestName', 'The <b>Pre/Post Test name</b> is already being used.');
         return(false);
      }else {
         return(true);
      }
   }

   function hideUnhide($lPPTestID){
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
      $bNewVisState = !$pptest->bHidden;
      $this->cpptests->changeVisibility($lPPTestID, $bNewVisState);
      
      $this->session->set_flashdata('msg', 'Test <b>'.htmlspecialchars($pptest->strTestName)
                    .'</b> is now <b>'.($bNewVisState ? 'hidden' : 'visible').'</b>.');
      redirect('cpre_post_tests/pptest_record/view/'.$lPPTestID);
   
   }

}


