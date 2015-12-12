<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cprog_record extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function view($lCProgID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $genumDateFormat, $glUserID;

      if (!bTestForURLHack('adminOnly')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lCProgID, 'client program ID');

      $displayData = array();
      $displayData['lCProgID'] = $lCProgID = (integer)$lCProgID;
      $displayData['js'] = '';

      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('client_features/mcprograms',           'cprograms');
      $this->load->model ('personalization/muser_fields',         'clsUF');
      $this->load->model ('personalization/muser_fields_display', 'clsUFD');
      $this->load->model ('admin/mpermissions',                   'perms');
      $this->load->model ('groups/mgroups',                       'groups');
      
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('clients/link_client_features');
      $this->load->helper('dl_util/record_view');
      $this->load->helper('img_docs/link_img_docs');
      $this->load->helper('personalization/ptable');
      $this->load->helper('groups/groups');

      $this->load->helper('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //-------------------------
         // load the client program
         //-------------------------
      $this->cprograms->loadClientProgramsViaCPID($lCProgID);
      $displayData['cprog'] = $cprog = &$this->cprograms->cprogs[0];
      
      $cprog->lNumEFields = $this->clsUF->lNumUF_TableFields($cprog->lEnrollmentTableID);
      $cprog->lNumAFields = $this->clsUF->lNumUF_TableFields($cprog->lAttendanceTableID);      

         //-------------------------------
         // personalized tables
         //-------------------------------
      $this->perms->loadUserAcctInfo($glUserID, $acctAccess);
      $displayData['strPTEnroll'] = strPTableDisplay(CENUM_CONTEXT_CPROGENROLL, $lCProgID, 
                         $this->clsUFD, $this->perms, $acctAccess,
                                  $displayData['strFormDataEntryAlert'], $lDummy);
      $displayData['strPTAttend'] = strPTableDisplay(CENUM_CONTEXT_CPROGATTEND, $lCProgID, 
                         $this->clsUFD, $this->perms, $acctAccess,
                                  $displayData['strFormDataEntryAlert'], $lDummy);

         //-------------------------------
         // permission groups
         //-------------------------------
      $this->groups->groupMembershipViaFID(CENUM_CONTEXT_CPROGRAM, $lCProgID);
      $displayData['pdgroup'] = new stdClass;
      $pdgroup = &$displayData['pdgroup'];
      $pdgroup->inGroups            = &$this->groups->arrMemberInGroups;
      $pdgroup->lCntGroupMembership = $this->groups->lNumMemInGroups;
      $pdgroup->lNumGroups          = $this->groups->lCntActiveGroupsViaType(CENUM_CONTEXT_CPROGRAM);
      $this->groups->loadActiveGroupsViaType(CENUM_CONTEXT_CPROGRAM, 'groupName', $this->groups->strMemListIDs, false, null);
      $pdgroup->groupList           = $this->groups->arrGroupList;
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/admin',              'Admin',           'class="breadcrumb"')
                                .' | '.anchor('cprograms/cprograms/overview', 'Client Programs', 'class="breadcrumb"')
                                .' | Client Program Record';

      $displayData['title']          = CS_PROGNAME.' | Admin';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'cprograms/cprograms_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }


}


