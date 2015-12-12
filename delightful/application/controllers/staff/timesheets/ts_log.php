<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class ts_log extends CI_Controller {



   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewLog($lYear=null, $lUserID=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $gdteNow;

      $displayData = array();
      $displayData['js'] = '';

      $displayData['lCurrentYear'] = $lCurrentYear = (int)date('Y', $gdteNow);
      if (is_null($lYear)){
         $lYear = $lCurrentYear;
      }else {
         $lYear = (int)$lYear;
      }
      $displayData['lDisplayYear'] = $lYear;

      if (is_null($lUserID)){
         $lUserID = $glUserID;
      }else {
         $lUserID = (int)$lUserID;
      }

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('staff/link_staff');
      $this->load->helper('dl_util/web_layout');
      $this->load->model ('staff/mtime_sheets', 'cts');
      $this->load->helper('staff/timesheet');
      $this->load->helper('dl_util/time_date');
      $this->load->model('admin/mpermissions', 'perms');
      $this->load->model('admin/muser_accts',  'cusers');

         // if time sheet is being edited by a 3rd party, make sure they are authorized
      if ($glUserID != $lUserID){
         if (!bAllowAccess('timeSheetAdmin')){
            redirect('staff/timesheets/ts_log_edit/error_tst_access');
         }
      }
      $displayData['lUserID'] = $lUserID;
      $this->cusers->loadSingleUserRecord($lUserID);
      $displayData['userRec'] = &$this->cusers->userRec[0];

         //---------------------------
         // hide / show block
         //---------------------------
      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //---------------------------
         // stripes
         //---------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         // load the user's current time sheet assignment
      $displayData['lTSTID'] = $lTSTID = $this->cts->lStaffTSAssignment($lUserID, $displayData['strTemplateName']);

         // load the user's time sheet log for a given year
      $this->cts->loadUserTSLogByYear($lUserID, $lYear, $displayData['lNumLogRecs'], $displayData['logRecs']);

         // load cumulative hours for each timesheet
      if ($displayData['lNumLogRecs'] > 0){
         foreach ($displayData['logRecs'] as $logRec){
            $logRec->lCumulativeMinutes = $this->cts->lCumulativeMinutesViaLogSheet($logRec->lKeyID);
         }
      }

         // load current time sheets that haven't been submitted.
      if (!is_null($lTSTID)){
         $this->cts->loadTimeSheetTemplateViaTSTID($lTSTID);
         $displayData['tst'] = $tst = &$this->cts->timeSheetTemplates[0];

         $potentialTS = array();
         switch ($tst->enumRptPeriod){
            case 'Weekly':
               $this->loadPotentialTS_Weekly($lUserID, $lYear, $tst, $lNumTSDDL, $potentialTS);
               break;
            case 'Semi-monthly':
               $this->loadPotentialTS_SemiMonthly($lUserID, $lYear, $tst, $lNumTSDDL, $potentialTS);
               break;
            case 'Monthly':
               $this->loadPotentialTS_Monthly($lUserID, $lYear, $tst, $lNumTSDDL, $potentialTS);
               break;
            default:
               screamForHelp($tst->enumRptPeriod.': reporting time period not found<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }
         $displayData['lNumTSDDL']   = $lNumTSDDL;
         $displayData['potentialTS'] = $potentialTS;
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                                .' |  Time Sheet Log';

      $displayData['title']          = CS_PROGNAME.' | Time Sheet';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'staff/timesheets/time_sheet_log_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function changeYear($lUserID){
      $lYear = (int)$_POST['ddlYear'];
      redirect('staff/timesheets/ts_log/viewLog/'.$lYear.'/'.$lUserID);
   }

   function add_edit_ts_prep($lUserID){
      $tsa = explode('_', $_POST['ddlTS']);
      redirect('staff/timesheets/ts_log_edit/add_edit_ts/'.(int)$tsa[0].'/'.$lUserID.'/'.(int)$tsa[1]);
   }

   function loadPotentialTS_Monthly($lUserID, $lDisplayYear, &$tst, &$lNumTSDDL, &$potentialTS){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow; //, $glUserID;

      $lTSTID = $tst->lKeyID;

      $today  = getdate($gdteNow);
      $lMon   = $today['mon'];
      $lYear  = $today['year'];

      $dteCurrent = mktime(0, 0, 0, $lMon, 1, $lYear);

      $lPotentialIDX = 0;
      $potentialTS = array();
      for ($idx=1; $idx<=12; ++$idx){
         $dteLookup = mktime(0, 0, 0, $idx, 1, $lDisplayYear);
         $this->potentialLookUp($lUserID, $dteCurrent, $lTSTID, $dteLookup, $lNumTSDDL, $lPotentialIDX, $potentialTS);
      }
   }

   function loadPotentialTS_SemiMonthly($lUserID, $lDisplayYear, &$tst, &$lNumTSDDL, &$potentialTS){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow; //, $glUserID;

      $lTSTID = $tst->lKeyID;

      $today  = getdate($gdteNow);
      $lMon   = $today['mon'];
      $lMDay  = $today['mday'];
      $lYear  = $today['year'];

      $dteCurrent = mktime(0, 0, 0, $lMon, ($lMDay>=16 ? 16 : 1), $lYear);

      $lPotentialIDX = 0;
      $potentialTS = array();
      for ($idx=1; $idx<=12; ++$idx){
         $dteLookup = mktime(0, 0, 0, $idx, 1, $lDisplayYear);
         $this->potentialLookUp($lUserID, $dteCurrent, $lTSTID, $dteLookup, $lNumTSDDL, $lPotentialIDX, $potentialTS);
         $dteLookup = mktime(0, 0, 0, $idx, 16, $lDisplayYear);
         $this->potentialLookUp($lUserID, $dteCurrent, $lTSTID, $dteLookup, $lNumTSDDL, $lPotentialIDX, $potentialTS);
      }
   }

   function loadPotentialTS_Weekly($lUserID, $lDisplayYear, &$tst, &$lNumTSDDL, &$potentialTS){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow; //, $glUserID;

      $lTSTID = $tst->lKeyID;
      $lMatchDOW = $tst->lFirstDayOfWeek;

      $dteNext    = ts_util\dteFutureStartOfWeekFromBaseDate($tst->lFirstDayOfWeek, $gdteNow);
      $dteCurrent = ts_util\dtePastStartOfWeekFromBaseDate  ($tst->lFirstDayOfWeek, $gdteNow);

      $lStartDay = 1;
      $dteStart = mktime(0, 0, 0, 1, $lStartDay, $lDisplayYear);
      $lDOW = date('w', $dteStart);
      while ($lMatchDOW != $lDOW){
         ++$lStartDay;
         $dteStart = mktime(0, 0, 0, 1, $lStartDay, $lDisplayYear);
         $lDOW = date('w', $dteStart);
      }

      $lNumTSDDL = 0;
      $lDayOffset = 0; $lPotentialIDX = 0;
      $potentialTS = array();
      $lYearViaDate = $lDisplayYear;
      for ($idx=0; $idx<=52; ++$idx){
         $dteLookup = mktime(0, 0, 0, 1, $lStartDay, $lDisplayYear);
         $lYearViaDate = (int)date('Y', $dteLookup);
         if ($lYearViaDate == $lDisplayYear){
            $this->potentialLookUp($lUserID, $dteCurrent, $lTSTID, $dteLookup, $lNumTSDDL, $lPotentialIDX, $potentialTS);
         }
         $lStartDay += 7;
      }
   }

   function potentialLookUp($lUserID, $dteCurrent, $lTSTID, $dteLookup, &$lNumTSDDL, &$lPotentialIDX, &$potentialTS){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->cts->loadUserTSLogUserDateTST($lTSTID, $lUserID, $dteLookup, $lNumLogRecs, $logRecs);
      if ($lNumLogRecs == 0){
         $potentialTS[$lPotentialIDX] = new stdClass;
         $pTS = &$potentialTS[$lPotentialIDX];
         $pTS->lTSLKeyID  = -1;
         $pTS->dteTSEntry = $dteLookup;
         $pTS->bSelected  = $pTS->dteTSEntry == $dteCurrent;
         $pTS->strDate    = date('F jS, Y (l)', $dteLookup);

         ++$lPotentialIDX;
         ++$lNumTSDDL;
      }else {
         $logRec = &$logRecs[0];
         if (is_null($logRec->dteSubmitted)){
            $potentialTS[$lPotentialIDX] = new stdClass;
            $pTS = &$potentialTS[$lPotentialIDX];
            $pTS->lTSLKeyID  = $logRec->lKeyID;
            $pTS->dteTSEntry = $logRec->dteTSEntry;
            $pTS->bSelected  = $pTS->dteTSEntry == $dteCurrent;
            $pTS->strDate    = date('F jS, Y (l)', $logRec->dteTSEntry);

            ++$lNumTSDDL;
            ++$lPotentialIDX;
         }
      }
   }

}









