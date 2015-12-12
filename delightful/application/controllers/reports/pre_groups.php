<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_groups extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function showOpts($enumContext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showUTable', $enumContext)) return;
      $displayData = array();
      $displayData['enumContext'] = $enumContext;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('groups/mgroups', 'groups');
      $this->load->helper('dl_util/context');
      $this->load->helper('groups/groups');

         //------------------------------------
         // load the check/uncheck support
         //------------------------------------
      $this->load->helper('js/set_check_boxes');
      $displayData['js'] = insertCheckSet();
      $this->load->helper('js/verify_check_set');
      $displayData['js'] .= verifyCheckSet();


      $this->groups->loadActiveGroupsViaType(
                              $enumContext, 'groupName', '',
                              false,        null);
      $displayData['lNumGroupList'] = $lNumGroupList = $this->groups->lNumGroupList;

      if ($lNumGroupList > 0){
         $displayData['groupList'] = &$this->groups->arrGroupList;
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | '.strLabelViaContextType($enumContext, true, false).' Groups';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_group_opts_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function saveOpts($enumContext){
      //---------------------------------------------------------------------
      //
      //---------------------------------------------------------------------
      $bShowAny = $_POST['rdoAnyAll'] == 'any';
      $this->load->model('reports/mreports',    'clsReports');

      $groupList = array();
      $idx = 0;
      foreach ($_POST['chkGroup'] as $groupItem){
         $groupList[$idx] = (integer)$groupItem;
         ++$idx;
      }

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_GROUP,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => true,
                             'viewFile'       => 'pre_generic_rpt_view',
                             'groupIDs'       => arrayCopy($groupList),
                             'enumContext'    => $enumContext,
                             'bShowAny'       => $bShowAny);
   
      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
   
      redirect('reports/reports/run/'.$reportID);
   }
   

}