<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class group_util extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function addMemToGroup($lFID, $enumOrigin, $enumGroupType, $enumSubGroup=null){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      $lFID = (integer)$lFID;
      $this->load->model('groups/mgroups', 'groups');
      $this->load->helper('groups/groups');

      if (!isset($_POST['ddlGroups'])){
         $this->session->set_flashdata('error', 'To add to a group, please select one or more groups from the list.');
         $this->returnViaOrigin($enumOrigin, $lFID);
      }

      $this->groups->lForeignID   = $lFID;
      $this->groups->enumSubGroup = $enumSubGroup;
      foreach ($_POST['ddlGroups'] as $lGroup){
         $this->groups->lGroupID = (integer)$lGroup;
         $this->groups->addGroupMembership();
      }
      $this->returnViaOrigin($enumOrigin, $lFID, 'The group membership was updated');
   }

   function returnViaOrigin($enumOrigin, $lFID, $strMsg){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->session->set_flashdata('msg', $strMsg);
      switch ($enumOrigin){
         case 'pRecView':
            redirect_People($lFID);
            break;
         case 'bRecView':
            redirect_Biz($lFID);
            break;
         case 'cRecView':
            redirect_Client($lFID);
            break;
         case 'vRecView':
            redirect_Vol($lFID);
            break;
         case 'spRecView':
            redirect_SponsorshipRecord($lFID);
            break;
         case 'pTableRecView':
            redirect_personalizedTable($lFID);
            break;
         case 'staffRecView':
         case 'uRecView':
            redirect_User($lFID);
            break;
         case 'clientProgramRecView':
            redirect('cprograms/cprog_record/view/'.$lFID);
            break;
         case 'clientPrePostRecView':
            redirect('cpre_post_tests/pptest_record/view/'.$lFID);
            break;
         case 'timesheetRecView':
            redirect('admin/timesheets/view_tst_record/viewTSTRecord/'.$lFID);
            break;
         default:
            $this->session->set_flashdata('error', $enumOrigin.': Unrecognized record view type');
            redirect('main/menu');
            break;
      }
   }

   function removeMemberFromGroup($lGroupID, $lFID, $enumGroupType, $enumSubGroup=null){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
/*----------------------------
echo(__FILE__.' '.__LINE__.'<br>'."\n");
echo(__FILE__.' '.__LINE__.'<br>'."\n"); $this->output->enable_profiler(TRUE);
//----------------------------- */
      $this->load->model('groups/mgroups', 'groups');
      $this->load->helper('groups/groups');

      $this->groups->lGroupID     = (integer)$lGroupID;
      $this->groups->lForeignID   = (integer)$lFID;
      $this->groups->enumSubGroup = $enumSubGroup;
      $this->groups->removeMemberFromGroup();
//return;
      $this->returnViaOrigin($enumGroupType, $lFID, 'Removed from the selected group');
   }

   function remMem($lGroupID, $enumSubGroup=null){
   //-------------------------------------------------------------------------
   // block removal
   //-------------------------------------------------------------------------
      $this->load->model('groups/mgroups', 'groups');
      $this->load->helper('groups/groups');

      $this->groups->lGroupID = $lGroupID = (integer)$lGroupID;
      $this->groups->enumSubGroup = $enumSubGroup;

      if (isset($_POST['chkGroupMem'])){
         $strBlock = implode(',', $_POST['chkGroupMem']);
         $this->groups->strForeignIDs = $strBlock;
         $this->groups->removeBlockMembersFromGroup();
      }
      $this->session->set_flashdata('msg', 'The selected members were removed from this group');
      redirect('groups/groups_view/viewMembers/'.$lGroupID.'/'.$enumSubGroup);
   }


}