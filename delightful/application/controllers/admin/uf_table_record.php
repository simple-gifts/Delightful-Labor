<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class uf_table_record extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewTable($lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $displayData['js'] = '';

      $displayData['lTableID'] = $lTableID = (int) $lTableID;

         //-----------------------------
         // models and helpers
         //-----------------------------
      $this->load->model  ('personalization/muser_schema', 'cUFSchema');
      $this->load->model  ('groups/mgroups',               'groups');
      $this->load->helper ('groups/groups');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('img_docs/link_img_docs');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

      $this->cUFSchema->loadUFSchemaSingleTable($lTableID);

      $displayData['schema'] = $schema = &$this->cUFSchema->schema;
      $enumType = $schema[$lTableID]->enumAttachType;

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         //-------------------------------
         // permission groups
         //-------------------------------
      $this->groups->groupMembershipViaFID(CENUM_CONTEXT_PTABLE, $lTableID);
      $displayData['inGroups']            = $this->groups->arrMemberInGroups;
      $displayData['lCntGroupMembership'] = $this->groups->lNumMemInGroups;
      $displayData['lNumGroups']          = $this->groups->lCntActiveGroupsViaType(CENUM_CONTEXT_PTABLE);
      $this->groups->loadActiveGroupsViaType(CENUM_CONTEXT_PTABLE, 'groupName', $this->groups->strMemListIDs, false, null);
      $displayData['groupList']           = $this->groups->arrGroupList;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                .' | '.anchor('admin/personalization/overview/'.$enumType, 'Personalization', 'class="breadcrumb"')
                                .' | Table';

      $displayData['title']          = CS_PROGNAME.' | Admin';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'personalization/uf_table_rec_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }


}