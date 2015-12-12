<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class client_test_results extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function addEditTestResults($lTestLogID, $lClientID, $lPPTestID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $gbDateFormatUS;
   
      if (!bTestForURLHack('showClients')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lClientID, 'client ID');
      verifyID($this, $lPPTestID, 'pre/post test ID');

      $displayData = array();
      $displayData['js'] = '';

      $displayData['lTestLogID'] = $lTestLogID = (int)$lTestLogID;
      $displayData['lClientID']  = $lClientID  = (int)$lClientID;
      $displayData['lPPTestID']  = $lPPTestID  = (int)$lPPTestID;
      $bNew = $lTestLogID <= 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('client_features/mcpre_post_tests', 'cpptests');
      $this->load->model  ('admin/mpermissions',               'perms');
      $this->load->helper ('clients/link_client_features');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->helper ('dl_util/time_date');  // for date verification
      $this->load->model  ('clients/mclients', 'clsClients');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('clients/client');
      $this->load->helper ('js/check_boxes_in_div');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

      $displayData['js'] .= checkUncheckInDiv();
/*      
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$displayData['js'] = ".htmlspecialchars($displayData['js'])." <br></font>\n");
*/      
         //-------------------------------------
         // load the client info
         //-------------------------------------
      $this->clsClients->loadClientsViaClientID($lClientID);
      $displayData['client'] = $this->clsClients->clients[0];

         //-------------------------
         // load the pre/post test
         //-------------------------
      $this->cpptests->loadPPTestsViaPPTID($lPPTestID);
      $displayData['pptest']    = $pptest    = &$this->cpptests->pptests[0];
      $displayData['lPPTestID'] = $lPPTestID = $pptest->lKeyID;

         //-------------------------
         // load the test log
         //-------------------------
      $this->cpptests->loadTestLogViaTestLogID($lTestLogID, $lNumTests, $testLogInfo);
      $testInfo = &$testLogInfo[0];
      
      $this->cpptests->loadClientScores($lTestLogID, $lPPTestID, $lNumQuest, $QandA);
      $displayData['lNumQuests'] = $lNumQuest;
      $displayData['QandA']      = &$QandA;
      
         //-------------------------------------
         // validation rules
         //-------------------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtPretestDate',   'Pre-Test Date',  'trim|required'.'|callback_verifyDateValid');
      $this->form_validation->set_rules('txtPosttestDate',  'Post-Test Date', 'trim|required'.'|callback_verifyDateValid|callback_verifyCBH');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($bNew){
               $displayData['txtPretestDate'] = $displayData['txtPosttestDate'] = '';
            }else {
               $displayData['txtPretestDate']  = strNumericDateViaUTS($testInfo->dtePreTest,  $gbDateFormatUS);
               $displayData['txtPosttestDate'] = strNumericDateViaUTS($testInfo->dtePostTest, $gbDateFormatUS);
            }
         }else {
            setOnFormError($displayData);
            $displayData['txtPretestDate']  = set_value('txtPretestDate');
            $displayData['txtPosttestDate'] = set_value('txtPosttestDate');
            $this->loadQAs($QandA);            
         }
                                                                    
            /*-------------------------------------
               load the client summary block
            -------------------------------------*/
         $this->load->model  ('img_docs/mimage_doc',   'clsImgDoc');
         $this->load->helper ('img_docs/image_doc');
         $this->load->helper('img_docs/link_img_docs');
         $params = array('enumStyle' => 'terse');
         $this->load->library('generic_rpt', $params, 'generic_rpt');
         $displayData['clsRpt'] = $this->generic_rpt;
         $displayData['contextSummary'] = $this->clsClients->strClientHTMLSummary(0);
                                                                    
            //-------------------------------------
            // stripes
            //-------------------------------------
         $this->load->model('util/mbuild_on_ready',    'clsOnReady');
         $this->clsOnReady->addOnReadyTableStripes();
         $this->clsOnReady->closeOnReady();
         $displayData['js'] .= $this->clsOnReady->strOnReady;

            //-------------------------------------
            // breadcrumbs and page layout
            //-------------------------------------
         $displayData['pageTitle']  = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                               .' | '.anchor('clients/client_record/view/'.$lClientID, 'Client Record', 'class="breadcrumb"')
                               .' | Pre/Post Test';
         $displayData['title']       = CS_PROGNAME.' | Clients';
         $displayData['nav']         = $this->mnav_brain_jar->navData();


         $displayData['mainTemplate'] = 'cpre_post_tests/client_record_test_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->loadQAs($QandA);            
         
         $strDate   = trim($_POST['txtPretestDate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $testInfo->dtePreTest = strtotime($lMon.'/'.$lDay.'/'.$lYear);
         
         $strDate   = trim($_POST['txtPosttestDate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $testInfo->dtePostTest = strtotime($lMon.'/'.$lDay.'/'.$lYear);

            //------------------------------------
            // update db tables and return
            //------------------------------------
         if ($bNew){
            $testInfo->lClientID  = $lClientID;
            $testInfo->lPrePostID = $lPPTestID;
            $lTestLogID = $this->cpptests->lAddTestLogEntry($testInfo);
            $this->session->set_flashdata('msg', 'New test results added');
         }else {
            $this->cpptests->updateTestLogEntry($lTestLogID, $testInfo);
            $this->session->set_flashdata('msg', 'Test results updated');
         }
         $this->cpptests->saveTestResults($lTestLogID, $QandA);
         redirect_Client($lClientID);
      }
   }
   
   function loadQAs($QandA){
      foreach ($QandA as $QA){
         $lQuestID = $QA->lKeyID;
         $QA->bPreTestRight  = @$_POST['chkPre' .$lQuestID]=='true';
         $QA->bPostTestRight = @$_POST['chkPost'.$lQuestID]=='true';
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
   function verifyDateValid($strDate){
      if (bValidVerifyDate($strDate)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyDateValid', 'The date you entered is not valid.');
         return(false);
      }
   }
   
   function verifyCBH($strPostTestDate){
   // CBH: cart before horse
      if (bVerifyCartBeforeHorse(trim($_POST['txtPretestDate']), $strPostTestDate)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyCBH', 'The post-test date is before the pre-test date!');
         return(false);
      }
   }

   function remove($lTestLogID, $lClientID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lClientID, 'client ID');
      
      $lTestLogID = (int)$lTestLogID;
      $lClientID  = (int)$lClientID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model('client_features/mcpre_post_tests', 'cpptests');
      $this->cpptests->removeClientTestResults($lTestLogID, $lClientID);
      
      $this->session->set_flashdata('msg', 'Test results were removed');
      redirect_Client($lClientID);
   }
   
   
   
   
   
   
   
}
