<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ppquest_view extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function viewQuestions($lPPTestID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $glUserID;
/*----------------------------
echo(__FILE__.' '.__LINE__.'<br>'."\n"); $this->output->enable_profiler(TRUE);
//----------------------------- */

      if (!bTestForURLHack('showClients')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPPTestID, 'pre/post test ID');

      $displayData = array();
      $displayData['js'] = '';

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('client_features/mcpre_post_tests', 'cpptests');
      $this->load->model  ('admin/mpermissions',               'perms');
      $this->load->helper ('clients/link_client_features');
      $this->load->library('util/up_down_top_bottom');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      
         //--------------------------
         // Stripes
         //--------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;      

         //-------------------------
         // load the pre/post test
         //-------------------------
      $this->cpptests->loadPPTestsViaPPTID($lPPTestID);
      $displayData['pptest'] = $pptest = &$this->cpptests->pptests[0];

         //-------------------------
         // load the questions
         //-------------------------
      $this->cpptests->loadQuestionsViaPPTID($lPPTestID);
      $displayData['quests'] = $quests = &$this->cpptests->questions;
      $displayData['lNumQuestions']    =  $this->cpptests->lNumQuestions;

      $displayData['strTestSummary'] = $this->cpptests->strHTMLPPTestSummaryDisplay();

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      =
                                    anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                             .' | '.anchor('cpre_post_tests/pptests/overview', ' Client Pre/Post Tests', 'class="breadcrumb"')
                             .' | '.anchor('cpre_post_tests/pptest_record/view/'.$lPPTestID,
                                                  htmlspecialchars($pptest->strTestName), 'class="breadcrumb"')
                             .' | Questions';

      $displayData['title']          = CS_PROGNAME.' | Client Pre/Post Tests';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'cpre_post_tests/questions_list_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }


}
