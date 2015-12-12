<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class mgr_performance extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function review(){
   //------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------
      global $glUserID;

      $this->load->helper('dl_util/permissions');    // in autoload
      if (!bAllowAccess('management')) return('');

      $displayData = array();
      $displayData['js'] = '';

         //-------------------------------------
         // models, libraries, and helpers
         //-------------------------------------
      $this->load->model('staff/mstaff_status',  'cstat');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('staff/link_staff');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //-------------------------------------
         // stripes
         //-------------------------------------
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $displayData['lMgrGroupID'] = $lMgrGroupID = $this->cstat->lStaffManagementGroupID();

      if (!is_null($lMgrGroupID)){
         $strTmpTable = 'tmp_mgr_status';
         $this->cstat->loadUsersAndStaffGroups($strTmpTable);
         $this->cstat->buildStaffGroups($strTmpTable, $displayData['lNumStaffGroups'], $displayData['staffGroups']);
         $lNumStaffGroups = $displayData['lNumStaffGroups'];
         $staffGroups = &$displayData['staffGroups'];
         if ($lNumStaffGroups > 0){
            foreach ($staffGroups as $sg){
               if ($sg->lNumUsers > 0){
                  foreach ($sg->users as $sguser){
                     $lStaffUID = $sguser->lUserID;
                     $this->cstat->reviewCountsForStaffMember(
                               $lStaffUID, $glUserID,
                               $sguser->lNumReviewed, $sguser->lNumReviewedDraft, $sguser->lNumNotReviewed,
                               $sguser->lTotPublished);
                  }
               }
            }
         }
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      =
                                    anchor('aayhf/main/aayhfMenu', 'AAYHF', 'class="breadcrumb"')
                               .' | Status Report Review';

      $displayData['title']          = CS_PROGNAME.' | Status Report Review';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'aayhf/aayhf_staff/mgr_status_review_overview_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function mgrViewLog($lUserID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lUserID, 'user ID');
      $lUserID = (int)$lUserID;

      $displayData = array();
      $displayData['js'] = '';

      $this->load->helper('dl_util/permissions');    // in autoload
      if (!bAllowAccess('management')) return('');

      $displayData['lUserID'] = $lUserID = (int)$lUserID;

         //-------------------------------------
         // models, libraries, and helpers
         //-------------------------------------
      $this->load->model ('staff/mstaff_status', 'cstat');
      $this->load->model ('admin/muser_accts',   'clsUser');
      $this->load->model ('admin/mpermissions',  'perms');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('staff/link_staff');
      $this->load->helper('staff/status_report');

         // load the current user's record
      $this->clsUser->loadSingleUserRecord($lUserID);
      $displayData['uRec'] = $uRec = &$this->clsUser->userRec[0];

      $this->cstat->loadStatusReportViaUserID($lUserID);
      $displayData['lNumSReports'] = $lNumSReports = $this->cstat->lNumSReports;
      $displayData['sreports']     = $sreports = &$this->cstat->sreports;

//      $displayData['bReviewedByTheMan'] = false;

         // load the reviews for this status report
      if ($lNumSReports > 0){
         foreach ($sreports as $srpt){
            $lRptID = $srpt->lKeyID;
            $srpt->bReviewedByTheMan = false;
            $this->cstat->loadReviewsViaRptID($lRptID, $srpt->lNumReviews, $srpt->reviewLog);
            if ($srpt->lNumReviews > 0){
               foreach ($srpt->reviewLog as $rlog){
                  if ($rlog->lReviewerID == $lUserID){
                     $srpt->bReviewedByTheMan = true;
                     break;
                  }
               }
            }
         }
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      =
                                      anchor('aayhf/main/aayhfMenu', 'AAYHF', 'class="breadcrumb"')
                               .' | '.anchor('staff/mgr_performance/review', 'Status Report Review', 'class="breadcrumb"')
                               .' | Status Log for '.$uRec->strSafeName;

      $displayData['title']          = CS_PROGNAME.' | Status Report Review';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'aayhf/aayhf_staff/mgr_status_staff_log_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function maagRpt($lMonth, $lYear){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lMonth    = (int)$lMonth;
      $lYear     = (int)$lYear;

      if ($lMonth <= 0){
         $lMonth = 12;
         --$lYear;
      }
      if ($lMonth > 12){
         $lMonth = 1;
         ++$lYear;
      }

      $displayData = array();
      $displayData['js'] = '';

      $displayData['lMonth'] = $lMonth;
      $displayData['lYear']  = $lYear;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('aayhf/aayhf_links');
      $this->load->helper('staff/link_staff');
      $this->load->model ('staff/mstaff_status',   'cstat');
      $this->load->model ('admin/mpermissions',    'perms');
      $this->load->model ('admin/muser_accts',     'cusers');
      $this->load->helper('dl_util/string');
      $this->load->helper('reports/month_at_a_glance');

         //--------------------------
         // Stripes
         //--------------------------
      $this->load->model ('util/mbuild_on_ready',  'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $displayData['lNumSRpts'] = $lNumSRpts = $this->cstat->lNumStatRptsInMonth($lMonth, $lYear);

         // load users
      $this->cusers->sqlWhere = '';   // need to load inactive users also
      $this->cusers->loadUserRecords();
      $userCal = array(); $userIDs = array();
      foreach ($this->cusers->userRec as $uRec){
         $userCal[$uRec->us_lKeyID] = new stdClass;
         $userIDs[] = $uRec->us_lKeyID;
         $uC = &$userCal[$uRec->us_lKeyID];
         $uC->strSafeName = htmlspecialchars($uRec->us_strLastName.', '.$uRec->us_strFirstName);
         $uC->bActive = !$uRec->us_bInactive;
      }

         // load status reports for these users
      if ($lNumSRpts > 0){
         $this->cstat->loadStatRptViaUsersMonthYear($userIDs, $lMonth, $lYear, $lNumStat, $statRecs);
         foreach ($statRecs as $sr){
            $lUserID = $sr->lUserID;
            if (!isset($userCal[$lUserID]->calendar)){
               $userCal[$lUserID]->calendar = array();
            }
            $userCal[$lUserID]->calendar[$sr->lDayOfMonthSubmit] = $sr->lKeyID;
         }
      }
      $displayData['userCal'] = &$userCal;


         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['mainTemplate'] = 'aayhf/aayhf_staff/status_maag_view';
      $displayData['pageTitle']    =
                                        anchor('aayhf/main/aayhfMenu',        'AAYHF',         'class="breadcrumb"')
                                 .' | '.anchor('staff/mgr_perf_rpt/maagOpts', 'Status Reports: Month-at-a-Glance Options', 'class="breadcrumb"')
                                 .' | Month-at-a-Glance';

      $displayData['title']        = CS_PROGNAME.' | Status Reports';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }


}




