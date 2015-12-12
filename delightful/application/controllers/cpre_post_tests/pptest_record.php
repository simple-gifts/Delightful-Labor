<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pptest_record extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function view($lPPTestID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPPTestID, 'pre/post test ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lPPTestID'] = $lPPTestID = (integer)$lPPTestID;
      
         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model('client_features/mcpre_post_tests', 'cpptests');
      $this->load->model ('admin/mpermissions',              'perms');
      $this->load->model ('groups/mgroups',                  'groups');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('clients/link_client_features');
      $this->load->helper('dl_util/record_view');
      $this->load->helper('img_docs/link_img_docs');
      $this->load->helper('personalization/ptable');
      $this->load->helper('groups/groups');
      
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

      $this->load->helper('js/div_hide_show');
      $displayData['js'] .= showHideDiv();
      
         // load the Pre/Post test
      $this->cpptests->loadPPTestsViaPPTID($lPPTestID);
      $displayData['pptest']     = $pptest = &$this->cpptests->pptests[0];
      $displayData['lNumQuests'] = $this->cpptests->lNumQuestsViaPPTID($lPPTestID);
      
         //-------------------------------
         // permission groups
         //-------------------------------
      $this->groups->groupMembershipViaFID(CENUM_CONTEXT_CPREPOST, $lPPTestID);
      $displayData['pdgroup'] = new stdClass;
      $pdgroup = &$displayData['pdgroup'];
      $pdgroup->inGroups            = &$this->groups->arrMemberInGroups;
      $pdgroup->lCntGroupMembership = $this->groups->lNumMemInGroups;
      $pdgroup->lNumGroups          = $this->groups->lCntActiveGroupsViaType(CENUM_CONTEXT_CPREPOST);
      $this->groups->loadActiveGroupsViaType(CENUM_CONTEXT_CPREPOST, 'groupName', $this->groups->strMemListIDs, false, null);
      $pdgroup->groupList           = $this->groups->arrGroupList;
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                .' | '.anchor('cpre_post_tests/pptests/overview', 'Client Pre/Post Tests', 'class="breadcrumb"')
                                .' | '.htmlspecialchars($pptest->strTestName);

      $displayData['title']          = CS_PROGNAME.' | Clients';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'cpre_post_tests/pp_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');     
   }
   
   
   
}
   
   
