<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class view_tst_record extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewTSTList(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bAllowAccess('adminOnly')) return;

      $displayData = array();
      $displayData['js'] = '';

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('staff/link_staff');
      $this->load->helper('dl_util/web_layout');
      $this->load->model ('staff/mtime_sheets', 'cts');
      $this->load->helper('staff/timesheet');
      $this->load->model ('admin/mpermissions', 'perms');
      $this->load->model ('admin/muser_accts',  'cusers');
      
         //------------------------------------
         // load the check/uncheck support
         //------------------------------------
      $this->load->helper('js/set_check_boxes');
      $displayData['js'] .= insertCheckSet();
      $this->load->helper('js/verify_check_set');
      $displayData['js'] .= verifyCheckSet();      

      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);
      

      $this->cts->loadTimeSheetTemplates();
      $displayData['lNumTST'] = $this->cts->lNumTST;
      $displayData['timeSheetTemplates'] = &$this->cts->timeSheetTemplates;
      
         //-------------------------------
         // users
         //-------------------------------
      $this->cusers->sqlWhere = 
              ' AND (us_bAdmin OR NOT us_bVolAccount)
                AND NOT us_bInactive ';
      $this->cusers->loadUserRecords();
      
      $userAssignments = array();
      $idx = 0;
      foreach ($this->cusers->userRec as $uRec){
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$uRec   <pre>');
echo(htmlspecialchars( print_r($uRec, true))); echo('</pre></font><br>');
// ------------------------------------- */
      
         $userAssignments[$idx] = new stdClass;
         $uA = &$userAssignments[$idx];
         $uA->lUserID     = $lUserID = $uRec->us_lKeyID;
         $uA->strFName    = $uRec->us_strFirstName;
         $uA->strLName    = $uRec->us_strLastName;
         $uA->bAdmin      = $bAdmin = $uRec->us_bAdmin;
         $uA->bTSAdmin    = $this->cts->bIsUserTSAdmin($lUserID);
         $uA->strUserName = $uRec->us_strUserName;
//         $uA->lTemplateAssignment = $lTA = $this->cts->lStaffTSAssignment($lUserID, $uA->strAssignedTemplateName);
         
         $uA->bGrayed        = $bAdmin; //!is_null($lTA) && $lTA != $lTSTID;
         $uA->bCheckedAssign = !$uA->bGrayed;//  && $lTA == $lTSTID;
//         $uA->bTSAdmin       = $this->cts->lStaffTSAdmin($lUserID, $lTSTID);
         ++$idx;
      }
      $displayData['userAssignments'] = &$userAssignments;
      

         //--------------------------
         //  breadcrumbs
         //--------------------------
      $displayData['title']        = CS_PROGNAME.' | Admin';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                              .' | Staff Time Sheet Templates';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'admin/staff_tst_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function viewTSTRecord($lTSTID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bAllowAccess('adminOnly')) return;

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lTSTID'] = $lTSTID = (int)$lTSTID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('staff/link_staff');
      $this->load->helper('groups/groups');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('staff/timesheet');
      $this->load->helper('dl_util/record_view');
      $this->load->helper('img_docs/link_img_docs');
      $this->load->model ('groups/mgroups',     'cgroups');
      $this->load->model ('staff/mtime_sheets', 'cts');
      $this->load->model ('admin/mpermissions', 'perms');
      $this->load->model ('admin/muser_accts',  'cusers');
      
         //------------------------------------
         // load the check/uncheck support
         //------------------------------------
      $this->load->helper('js/set_check_boxes');
      $displayData['js'] .= insertCheckSet();
      $this->load->helper('js/verify_check_set');
      $displayData['js'] .= verifyCheckSet();      

      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);

      $this->cts->loadTimeSheetTemplateViaTSTID($lTSTID);
      $displayData['tst'] = $tst = &$this->cts->timeSheetTemplates[0];

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //-------------------------------
         // projects group
         //-------------------------------
      $displayData['groupsProj'] = new stdClass;
      $this->cgroups->groupMembershipViaFID(CENUM_CONTEXT_STAFF_TS_PROJECTS, $lTSTID);
      $displayData['groupsProj']->inGroups = $this->cgroups->arrMemberInGroups;
      $displayData['groupsProj']->lCntGroupMembership = $this->cgroups->lNumMemInGroups;
      $displayData['groupsProj']->lNumGroups          = $this->cgroups->lCntActiveGroupsViaType(CENUM_CONTEXT_STAFF_TS_PROJECTS);
      $this->cgroups->loadActiveGroupsViaType(CENUM_CONTEXT_STAFF_TS_PROJECTS, 'groupName', $this->cgroups->strMemListIDs, false, null);
      $displayData['groupsProj']->groupList           = $this->cgroups->arrGroupList;
      
         //-------------------------------
         // locations group
         //-------------------------------
      $displayData['groupsLoc'] = new stdClass;
      $this->cgroups->groupMembershipViaFID(CENUM_CONTEXT_STAFF_TS_LOCATIONS, $lTSTID);
      $displayData['groupsLoc']->inGroups = $this->cgroups->arrMemberInGroups;
      $displayData['groupsLoc']->lCntGroupMembership = $this->cgroups->lNumMemInGroups;
      $displayData['groupsLoc']->lNumGroups          = $this->cgroups->lCntActiveGroupsViaType(CENUM_CONTEXT_STAFF_TS_LOCATIONS);
      $this->cgroups->loadActiveGroupsViaType(CENUM_CONTEXT_STAFF_TS_LOCATIONS, 'groupName', $this->cgroups->strMemListIDs, false, null);
      $displayData['groupsLoc']->groupList           = $this->cgroups->arrGroupList;
      
         //-------------------------------
         // users
         //-------------------------------
      $this->cusers->sqlWhere = 
              ' AND (us_bAdmin OR NOT us_bVolAccount)
                AND NOT us_bInactive ';
      $this->cusers->loadUserRecords();
      
      $userAssignments = array();
      $idx = 0;
      foreach ($this->cusers->userRec as $uRec){
         $userAssignments[$idx] = new stdClass;
         $uA = &$userAssignments[$idx];
         $uA->lUserID  = $lUserID = $uRec->us_lKeyID;
         $uA->strFName = $uRec->us_strFirstName;
         $uA->strLName = $uRec->us_strLastName;
         $uA->strUserName = $uRec->us_strUserName;
         $uA->lTemplateAssignment = $lTA = $this->cts->lStaffTSAssignment($lUserID, $uA->strAssignedTemplateName);
         
         $uA->bGrayed        = !is_null($lTA) && $lTA != $lTSTID;
         $uA->bCheckedAssign = !$uA->bGrayed  && $lTA == $lTSTID;
//         $uA->bTSAdmin       = $this->cts->lStaffTSAdmin($lUserID, $lTSTID);
         ++$idx;
      }
      $displayData['userAssignments'] = &$userAssignments;
/*      
*/      
         //--------------------------
         //  breadcrumbs
         //--------------------------
      $displayData['title']        = CS_PROGNAME.' | Admin';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                              .' | '.anchor('admin/timesheets/view_tst_record/viewTSTList', 'Staff Time Sheet Templates', 'class="breadcrumb"')
                              .' | View Time Sheet Template';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'admin/staff_tst_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }



}
