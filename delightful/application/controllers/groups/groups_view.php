<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class groups_view extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function view($enumGroupType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      $displayData = array();

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('groups/mgroups', 'clsGroups');
      $this->load->helper('groups/groups');
      
      groupExtensionProperties($enumGroupType, $gProps);
      $displayData['gProps'] = &$gProps;
      
      $displayData['enumGroupType'] = $enumGroupType = htmlspecialchars($enumGroupType);
      $displayData['strGroupType']  = $strGroupType = strXlateContext($enumGroupType);
      $this->clsGroups->gp_enumGroupType = $enumGroupType;
      $this->clsGroups->loadActiveGroupsViaType($this->clsGroups->gp_enumGroupType, 'groupName', '', false, null);
      $displayData['lNumGroups']   = $lNumGroups = $this->clsGroups->lNumGroupList;
      $displayData['arrGroupList'] = $this->clsGroups->arrGroupList;

      if ($lNumGroups > 0){
         $idx = 0;
         foreach ($this->clsGroups->arrGroupList as $clsList){
            $this->clsGroups->lGroupID = $lGID = $clsList->lKeyID;
            $displayData['lMembersInGroup'][$idx] = $this->clsGroups->lCountMembersInGroup($lGID, $enumGroupType);
            ++$idx;
         }
      }
      
         //----------------------
         // set breadcrumbs
         //----------------------
      $displayData['title']        = CS_PROGNAME.' | Groups';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                              .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                              .' | Groups: '.$strGroupType;
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate'] = 'groups/group_view';

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   public function viewMembers($lGroupID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lGroupID, 'group ID');
      
      $displayData = array();

         //-------------------------
         // models & helpers
         //-------------------------
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);
      $this->load->model  ('groups/mgroups', 'clsGroups');
      $this->load->helper ('groups/groups');
      $this->load->helper ('staff/link_staff');
      
      $displayData['lGroupID'] = $this->clsGroups->lGroupID = $lGroupID = (integer)$lGroupID;
      $this->clsGroups->loadGroupInfo($lGroupID);
      $grp = $this->clsGroups->groupTable[0];
      $displayData['enumGroupType']  = $enumGroupType = $grp->gp_enumGroupType;
      $displayData['strGroupName']   = $grp->gp_strGroupName;
      $displayData['strGroupNotes']  = $grp->gp_strNotes;
      $displayData['dteExpire']      = $grp->gp_dteExpire;
      $displayData['groupMemLabels'] = &$this->clsGroups->groupMemLabels;
      $displayData['groupMembers']   = &$this->clsGroups->groupMembers;
      
      if ($enumGroupType==CENUM_CONTEXT_USER){
         if (!bTestForURLHack('adminOnly')) return;
      }

      $this->clsGroups->loadGroupMembership($enumGroupType, $lGroupID);
      $displayData['groupMembers'] = &$this->clsGroups->groupMembers;
      $displayData['lMembersInGroup'] = $this->clsGroups->lCntMembersInGroup;

      $this->load->helper('js/set_check_boxes');
      $displayData['js'] = insertCheckSet();
      $this->load->helper('js/verify_check_set');
      $displayData['js'] .= verifyCheckSet();

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         //----------------------
         // set breadcrumbs
         //----------------------
      $displayData['title']        = CS_PROGNAME.' | Groups';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                              .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                              .' | '.anchor('groups/groups_view/view/'.$enumGroupType, 'Groups: '.$enumGroupType, 'class="breadcrumb"')
                              .' | Membership';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'][0] = 'groups/group_summary_view';
      switch ($enumGroupType){
         case CENUM_CONTEXT_BIZ:
            $displayData['mainTemplate'][1] = 'groups/group_biz_members_view';
            break;
         case CENUM_CONTEXT_PEOPLE:
         case CENUM_CONTEXT_VOLUNTEER:
         case CENUM_CONTEXT_SPONSORSHIP:
         case CENUM_CONTEXT_STAFF:
         case CENUM_CONTEXT_USER:
            $displayData['mainTemplate'][1] = 'groups/group_people_members_view';
            break;
         case CENUM_CONTEXT_CLIENT:
            $displayData['mainTemplate'][1] = 'groups/group_people_members_view';
            break;
         case CENUM_CONTEXT_STAFF_TS_LOCATIONS:
         case CENUM_CONTEXT_STAFF_TS_PROJECTS:
            $displayData['mainTemplate'][1] = 'groups/group_timesheet_proj_members_view';
            break;
         default:
            screamForHelp($enumGroupType.': group type not yet available<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
            break;
      }
      $this->load->vars($displayData);
      $this->load->view('template');
   }


}