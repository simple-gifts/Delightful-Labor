<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class vol_record extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function volRecordView($lVolID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $glUserID;

      if (!bTestForURLHack('showPeople')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lVolID, 'volunteer ID');

      $displayData = array();
      $displayData['lVolID'] = $lVolID = (integer)$lVolID;
      $displayData['js'] = '';

         //------------------------------------------------
         // libraries / models / utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');

      $this->load->model  ('vols/mvol',                    'clsVol');
      $this->load->model  ('vols/mvol_skills',             'clsVolSkills');
      $this->load->model  ('vols/mvol_event_hours',        'clsVolHours');
      $this->load->model  ('vols/mvol_event_dates_shifts', 'clsShifts');
      $this->load->model  ('people/mpeople',               'clsPeople');
      $this->load->model  ('groups/mgroups',               'groups');
      $this->load->model  ('img_docs/mimage_doc',          'clsImgDoc');
      $this->load->model  ('img_docs/mimg_doc_tags',       'cidTags');
      $this->load->model  ('admin/madmin_aco');
      $this->load->model  ('admin/muser_accts');
      $this->load->model  ('sponsorship/msponsorship');
      $this->load->model  ('donations/mdonations');

      $this->load->helper ('personalization/ptable');
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper ('groups/groups');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('img_docs/img_doc_tags');
      $this->load->helper ('vols/vol_links');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();
      
         //-------------------------------------
         // stripes
         //-------------------------------------
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;
      

         //-------------------------------
         // volunteer record
         //-------------------------------
      $this->clsVol->loadVolRecsViaVolID($lVolID, true);
      $displayData['volRec'] = $volRec = &$this->clsVol->volRecs[0];
      $displayData['lPID'] = $lPID = $volRec->lPeopleID;

      $this->load->model('reminders/mreminders', 'clsReminders');
      $displayData['clsRem'] = $this->clsReminders;

      $this->clsPeople->loadPeopleViaPIDs($lPID, true, true);
      $displayData['contextSummary'] = $this->clsPeople->peopleHTMLSummary(0);

         //-------------------------------
         // volunteer-client associations
         //-------------------------------
      if (bAllowAccess('showClients')){
         $this->clsVol->loadVolClientAssociations($lVolID, $displayData['volRec']->vca);
         $displayData['volRec']->lNumVolClientAssoc = count($displayData['volRec']->vca);
      }

         //-------------------------------
         // volunteer skills
         //-------------------------------
      $this->clsVolSkills->lVolID = $lVolID;
      $this->clsVolSkills->loadSingleVolSkills();
      $displayData['lNumSingleVolSkills'] = $this->clsVolSkills->lNumSingleVolSkills;
      $displayData['singleVolSkills']     = &$this->clsVolSkills->singleVolSkills;

         //-------------------------------
         // personalized tables
         //-------------------------------
      $this->load->model('personalization/muser_fields',         'clsUF');
      $this->load->model('personalization/muser_fields_display', 'clsUFD');
      $this->load->model('admin/mpermissions',                   'perms');

      $this->perms->loadUserAcctInfo($glUserID, $acctAccess);
      $this->load->model('personalization/muser_fields',         'clsUF');
      $this->load->model('personalization/muser_fields_display', 'clsUFD');
      $displayData['strPT'] = strPTableDisplay(CENUM_CONTEXT_VOLUNTEER, $lVolID,
                                  $this->clsUFD, $this->perms, $acctAccess,
                                  $displayData['strFormDataEntryAlert'],
                                  $displayData['lNumPTablesAvail']);

         //-------------------------------
         // volunteer hours
         //-------------------------------
      $displayData['dTotHours']   = $this->clsVolHours->volEventHoursViaVolID($lVolID);
      $displayData['dTotUnHours'] = $this->clsVolHours->volUnscheduledEventHoursViaVolID($lVolID);

         //-------------------------------
         // volunteer schedule
         //-------------------------------
      $displayData['lPastShifts']          = $this->clsShifts->lNumShiftsViaVolID($lVolID, true);
      $displayData['lCurrentFutureShifts'] = $this->clsShifts->lNumShiftsViaVolID($lVolID, false);

         //-------------------------------
         // groups
         //-------------------------------
      $this->groups->groupMembershipViaFID(CENUM_CONTEXT_VOLUNTEER, $lVolID);
      $displayData['inGroups']            = $this->groups->arrMemberInGroups;
      $displayData['lCntGroupMembership'] = $this->groups->lNumMemInGroups;
      $displayData['lNumGroups']          = $this->groups->lCntActiveGroupsViaType(CENUM_CONTEXT_VOLUNTEER);
      $this->groups->loadActiveGroupsViaType(CENUM_CONTEXT_VOLUNTEER, 'groupName', $this->groups->strMemListIDs, false, null);
      $displayData['groupList']           = $this->groups->arrGroupList;

         //-------------------------------
         // images and documents
         //-------------------------------
      loadImgDocRecView($displayData, CENUM_CONTEXT_VOLUNTEER, $lVolID);
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['title']        = CS_PROGNAME.' | View volunteer record';
      $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                              .' | Record';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'vols/vol_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

}
