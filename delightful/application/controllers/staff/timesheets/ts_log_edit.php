<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ts_log_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function add_edit_ts($lTSLogID, $lUserID=null, $dteTSLogDate=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lTSLogID'] = $lTSLogID = (int)$lTSLogID;
      $displayData['bNew']     = $bNew = $lTSLogID <= 0;
      $displayData['dteTSLogDate'] = $dteTSLogDate = (int)$dteTSLogDate;

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
      $this->load->helper('staff/link_staff');
      $this->load->helper('dl_util/time_date');
      $this->load->model ('util/mbuild_on_ready',  'clsOnReady');

      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);

         //--------------------------
         // Stripes
         //--------------------------
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         // load the user's current time sheet assignment
      $displayData['lTSTID'] = $lTSTID = $this->cts->lStaffTSAssignment($glUserID, $displayData['strTemplateName']);
      if (is_null($lTSTID)){
         redirect('staff/timesheets/ts_log_edit/error_tstid');
      }

         // load the time sheet template associated with this staff member
      $this->cts->loadTimeSheetTemplateViaTSTID($lTSTID);
      $displayData['tst'] = $tst = &$this->cts->timeSheetTemplates[0];

         // if this is a new log entry, build the base log record; first make
         // sure there isn't an entry by checking the date
      if ($bNew){
         $this->cts->loadUserTSLogUserDateTST($lTSTID, $lUserID, $dteTSLogDate, $lNumLogRecs, $logRecs);
         if ($lNumLogRecs == 0){
            $displayData['lTSLogID'] = $lTSLogID = (int)$this->cts->lAddNewTSLog($lUserID, $lTSTID, $dteTSLogDate);
         }else {
            $displayData['lTSLogID'] = $lTSLogID = (int)$logRecs[0]->lKeyID;
         }
      }

         // load the log entry and any daily entries
      $this->cts->loadUserTSLogByLogID($lTSLogID, $lNumLogRecs, $logRecs, true);
      
      $displayData['lUserID'] = $lUserID = $logRecs[0]->lStaffID;
         // if time sheet is being edited by a 3rd party, make sure they are authorized
      if ($glUserID != $lUserID){
         if (!bAllowAccess('timeSheetAdmin')){
            redirect('staff/timesheets/ts_log_edit/error_tst_access');
         }
      }

         // if this time sheet has already been submitted, error off (possibly a URL hack)
      if (!$bNew){
         if (!is_null($logRecs[0]->dteSubmitted)){
            redirect('staff/timesheets/ts_log_edit/error_edit_submitted');
         }
      }

      $displayData['logRec'] = $logRec = &$logRecs[0];
      $dteTSEntry = $logRec->dteTSEntry;
      $lTSYear = (int)date('Y', $dteTSEntry);

         // set up the display calendar
      ts_util\tsEntryCalendar($logRec, $displayData['tsCalendar']);
      $tsCal = &$displayData['tsCalendar'];
      $lNumDays = count($tsCal);

         // add the ts entries to the appropriate calendar day
      if ($logRec->lNumEntries > 0){
         foreach ($logRec->logEntries as $entry){
            $dteE = $entry->dteLogEntry;

            for ($idx=0; $idx < $lNumDays; ++$idx){
               if ($tsCal[$idx]->calDate == $dteE){
                  if (!isset($tsCal[$idx]->dailyEntries)){
                     $tsCal[$idx]->dailyEntries = array();
                     $tsCal[$idx]->lNumDailyEntries = 0;
                  }
                  $lNumD = $tsCal[$idx]->lNumDailyEntries;
                  $de = &$tsCal[$idx]->dailyEntries;
                  $de[$lNumD] = new stdClass;
                  $de[$lNumD]->lEntryID = $entry->lKeyID;
                  $de[$lNumD]->lTimeIn  = $entry->lTimeInMin;
                  $de[$lNumD]->lTimeOut = $entry->lTimeOutMin;
                  $de[$lNumD]->location = $entry->strLocation;
                  $de[$lNumD]->notes    = $entry->strNotes;

                  ++$tsCal[$idx]->lNumDailyEntries;
                  break;
               }
            }
         }
      }

      $displayData['formData'] = new stdClass;
      $this->load->library('generic_form');

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                                .' | '.anchor('staff/timesheets/ts_log/viewLog/'.$lTSYear.'/'.$lUserID, 'Time Sheet Log', 'class="breadcrumb"')
                                .' | '.($bNew ? 'Add New' : 'Edit').' Time Sheet';

      $displayData['title']          = CS_PROGNAME.' | Staff';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'staff/timesheets/add_edit_tslog_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function error_tstid(){
      $this->ts_error_generic('staff/timesheets/error_no_tstid');
   }
   function error_tst_access(){
      $this->ts_error_generic('staff/timesheets/error_no_access');
   }
   function error_tst_noprojects(){
      $this->ts_error_generic('staff/timesheets/error_no_projects');
   }
   function error_edit_submitted(){
      $this->ts_error_generic('staff/timesheets/error_edit_submitted');
   }
   function ts_error_generic($strViewFile){
      $displayData['pageTitle']      = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                                .' | Time Sheet - Error';

      $displayData['title']          = CS_PROGNAME.' | More';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = $strViewFile;
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function unsubmit($lTSLogID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // must be a time sheet admin to unsubmit
      if (!bAllowAccess('timeSheetAdmin')){
         redirect('staff/timesheets/ts_log_edit/error_tst_access');
      }

      $lTSLogID = (int)$lTSLogID;
      
         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('staff/mtime_sheets', 'cts');
      $this->load->helper('dl_util/time_date');
      
      $this->cts->loadUserTSLogByLogID($lTSLogID, $lNumLogRecs, $logRecs, false);
      $lr = &$logRecs[0];
      $lUserID = $lr->lStaffID;
      $lYear   = (int)date('Y', $lr->dteTSEntry);
      
      $this->cts->unsubmitTimeSheet($lTSLogID);
      $this->session->set_flashdata('msg', 'Time sheet unsubmitted');
      redirect('staff/timesheets/ts_log/viewLog/'.$lYear.'/'.$lUserID);
   }

   function add_edit_ts_entry($lEntryID, $lTSLogID=null, $lUserID=null, $dteEntry=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lEntryID'] = $lEntryID = (int)$lEntryID;
      $displayData['bNew']     = $bNew = $lEntryID <= 0;

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
      $this->load->model ('groups/mgroups', 'cgroups');
      $this->load->helper('groups/groups');
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);

         // if time sheet is being edited by a 3rd party, make sure they are authorized
      if ($glUserID != $lUserID){
         if (!bAllowAccess('timeSheetAdmin')){
            redirect('staff/timesheets/ts_log_edit/error_tst_access');
         }
      }
      $displayData['lUserID'] = $lUserID;

         // if editing an existing entry, load the entry
      if ($bNew){
         $lNumEntries = 5;
      }else {
         $this->cts->loadTSLogEntriesViaEntryID($lEntryID, $lNumEntries, $logEntries);
         $le = &$logEntries[0];

         $lTSLogID = $le->lTSLogID;
         $dteEntry = $le->dteLogEntry;
         $lNumEntries = 1;
      }
      $displayData['lTSLogID']    = $lTSLogID = (int)$lTSLogID;
      $displayData['dteEntry']    = $dteEntry = (int)$dteEntry;
      $displayData['lNumEntries'] = $lNumEntries;

         // load the log record
      $this->cts->loadUserTSLogByLogID($lTSLogID, $lNumLogRecs, $logRecs, false);

         // if this time sheet has already been submitted, error off (possibly a URL hack)
      if (!is_null($logRecs[0]->dteSubmitted)){
         redirect('staff/timesheets/ts_log_edit/error_edit_submitted');
      }

      $displayData['logRec'] = $logRec = &$logRecs[0];
      $dteTSEntry   = $logRec->dteTSEntry;
      $lTimeSheetID = $logRec->lTimeSheetID;
      $lTSYear = (int)date('Y', $dteTSEntry);

         // load locations associated with this template
      $this->cgroups->groupMembershipViaFID(CENUM_CONTEXT_STAFF_TS_LOCATIONS, $lTimeSheetID);

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('lNumEntries', 'Entries', 'callback_verifyTSEntries');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $displayData['formData']->timeIn   = array();
         $displayData['formData']->timeOut  = array();
         $displayData['formData']->location = array();

         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($bNew){
               for ($idx=0; $idx<$lNumEntries; ++$idx){
                  $displayData['formData']->timeIn[$idx] =
                      ts_util\strTimeInTimeOut('ddlTimeIn'.$idx, true, -1, $logRec->b24HrTime, $logRec->enumGranularity);
                  $displayData['formData']->timeOut[$idx] =
                      ts_util\strTimeInTimeOut('ddlTimeOut'.$idx, true, -1, $logRec->b24HrTime, $logRec->enumGranularity);
                  $displayData['formData']->location[$idx] =
                      $this->cgroups->strMembersInGroupDDL('ddlLocation'.$idx, true, -1);
                  $displayData['formData']->notes[$idx] = '';
               }
            }else {
               $displayData['formData']->timeIn[0] =
                   ts_util\strTimeInTimeOut('ddlTimeIn0',  true, $le->lTimeInMin,  $logRec->b24HrTime, $logRec->enumGranularity);
               $displayData['formData']->timeOut[0] =
                   ts_util\strTimeInTimeOut('ddlTimeOut0', true, $le->lTimeOutMin, $logRec->b24HrTime, $logRec->enumGranularity);
               $displayData['formData']->location[0] =
                   $this->cgroups->strMembersInGroupDDL('ddlLocation0', true, $le->lLocationID);
               $displayData['formData']->notes[0] = htmlspecialchars($le->strNotes);
            }
         }else {
            setOnFormError($displayData);
            for ($idx=0; $idx<$lNumEntries; ++$idx){
               $displayData['formData']->timeIn[$idx] =
                   ts_util\strTimeInTimeOut('ddlTimeIn'.$idx, true, (int)$_POST['ddlTimeIn'.$idx], $logRec->b24HrTime, $logRec->enumGranularity);
               $displayData['formData']->timeOut[$idx] =
                   ts_util\strTimeInTimeOut('ddlTimeOut'.$idx, true, (int)$_POST['ddlTimeOut'.$idx], $logRec->b24HrTime, $logRec->enumGranularity);
               $displayData['formData']->location[$idx] =
                   $this->cgroups->strMembersInGroupDDL('ddlLocation'.$idx, true, (int)$_POST['ddlLocation'.$idx]);
               $displayData['formData']->notes[$idx] =  $_POST['txtNotes'.$idx];
            }
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                                   .' | '.anchor('staff/timesheets/ts_log/viewLog/'.$lTSYear.'/'.$lUserID, 'Time Sheet Log', 'class="breadcrumb"')
                                   .' | '.anchor('staff/timesheets/ts_log_edit/add_edit_ts/'.$lTSLogID, 'Time Sheet', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').'  Time Sheet Entries';

         $displayData['title']          = CS_PROGNAME.' | Staff';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'staff/timesheets/add_tsentries_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->loadPostTSEntries($lNumEntries, $entries);

         if ($bNew){
            foreach ($entries as $ei){
               if (($ei->timeIn >= 0) && ($ei->timeOut > 0) && ($ei->location > 0)){
                  $lEID = $this->cts->addTSEntry($lTSLogID, $dteEntry, $ei);
               }
            }
            $this->session->set_flashdata('msg', 'Time sheet entries were added.');
         }else {
            $this->cts->updateTSEntry($lEntryID, $entries[0]);
            $this->session->set_flashdata('msg', 'Time sheet entry was updated.');
         }
         redirect('staff/timesheets/ts_log_edit/add_edit_ts/'.$lTSLogID);
      }
   }

   function loadPostTSEntries(&$lNumEntries, &$entries){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumEntries = (int)$_POST['lNumEntries'];
      $entries = array();
      for ($idx=0; $idx<$lNumEntries; ++$idx){
         $entries[$idx] = new stdClass;
         $ei = &$entries[$idx];
         $ei->timeIn   = (int)$_POST['ddlTimeIn'.$idx];
         $ei->timeOut  = (int)$_POST['ddlTimeOut'.$idx];
         $ei->location = (int)$_POST['ddlLocation'.$idx];
         $ei->notes    = trim($_POST['txtNotes'.$idx]);
      }
   }

   function verifyTSEntries($dummy){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->loadPostTSEntries($lNumEntries, $entries);
      $bFailed = false;
      $bAnyEntry = false;
      $strError = '';
      for ($idx=0; $idx<$lNumEntries; ++$idx){
         $lLineNum = $idx + 1;
         $ei = &$entries[$idx];
         $ei->bValidEntry = false;
         $bAnyDDLSel = ($ei->timeIn >= 0) || ($ei->timeOut > 0) || ($ei->location > 0) || ($ei->notes != '');
         $bAllDDLSel = ($ei->timeIn >= 0) && ($ei->timeOut > 0) && ($ei->location > 0);

         if ($bAnyDDLSel && !$bAllDDLSel){
            $bFailed = true;
            $strError .= ' * Line '.$lLineNum.': Please make a selection for time in, time out, and location (or leave all fields blank).<br>';
         }elseif ($bAllDDLSel){
            if ($ei->timeIn > $ei->timeOut){
               $bFailed = true;
               $strError .= ' * Line '.$lLineNum.': Time In is <b><i>after</b></i> Time Out.<br>';
            }elseif ($ei->timeIn == $ei->timeOut){
               $bFailed = true;
               $strError .= ' * Line '.$lLineNum.': Time In and Time Out are the same.<br>';
            }else {
               $ei->bValidEntry = true;
            }
         }
         $bAnyEntry = $bAnyEntry || $bAnyDDLSel;
      }

      if (!$bAnyEntry){
         $bFailed = true;
         $strError .= ' * Please make one or more time sheet entries.<br>';
      }

         // check for overlapping time periods among the new entries
      if (!$bFailed){
         for ($idx=0; $idx<$lNumEntries; ++$idx){
            $ei = &$entries[$idx];
            if ($ei->bValidEntry){
               if ($this->lCntOverlapping($lNumEntries, $idx, $ei->timeIn, $ei->timeOut, $entries) != 0){
                  $bFailed = true;
                  $strError .= ' * There are overlapping time periods.<br>';
                  break;
               }
            }
         }
      }

         // check for overlapping time periods among existing entries
      if (!$bFailed){
         $lEntryID = (int)$_POST['lEntryID'];
         $lTSLogID = (int)$_POST['lTSLogID'];
         $dteEntry = (int)$_POST['dteEntry'];
         $this->cts->loadTSEntriesForOverlapTest($lEntryID, $dteEntry, $lTSLogID, $lNumDBEntries, $dbEntries);

         for ($idx=0; $idx<$lNumEntries; ++$idx){
            $ei = &$entries[$idx];
            if ($ei->bValidEntry){
               if ($this->lCntOverlapping($lNumDBEntries, -1, $ei->timeIn, $ei->timeOut, $dbEntries) != 0){
                  $bFailed = true;
                  $strError .= ' * There are overlapping time periods with previously recorded entries for this date.<br>';
                  break;
               }
            }
         }
      }

      if ($bFailed){
         $this->form_validation->set_message('verifyTSEntries',
            'The following problems were detected in your time sheet entries:<br>'.$strError);
         return(false);
      }else {
         return(true);
      }
   }

   function lCntOverlapping($lNumEntries, $lExcludeIDX, $timeIn, $timeOut, $entries){
      $lCnt = 0;
      for ($idx=0; $idx<$lNumEntries; ++$idx){
         if ($idx != $lExcludeIDX){
            $ei = &$entries[$idx];
            if ($ei->bValidEntry){
               if (($ei->timeIn >= $timeIn) && ($ei->timeIn < $timeOut)){
                  ++$lCnt;
               }elseif (($ei->timeOut > $timeIn) && ($ei->timeOut <= $timeOut)){
                  ++$lCnt;
               }elseif (($ei->timeIn <= $timeIn) && ($ei->timeOut > $timeIn)){
                  ++$lCnt;
               }
            }
         }
      }
      return($lCnt);
   }


   function remove_tsentry($lEntryID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $lEntryID = (int)$lEntryID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('staff/mtime_sheets', 'cts');
      $this->load->helper('staff/timesheet');
      $this->load->helper('dl_util/time_date');

         // load the time sheet entry so that we can verify the record's
         // existence, and the ability of the user to modify it
      $this->cts->loadTSLogEntriesViaEntryID($lEntryID, $lNumEntries, $logEntries);
      if ($lNumEntries==0){
         redirect('staff/timesheets/ts_log_edit/error_tst_access');
      }
      $logEntry = &$logEntries[0];
      $lTSLogID = $logEntry->lTSLogID;
      $lTSTID   = $logEntry->lTimeSheetID;
      $lUserID  = $logEntry->lStaffID;

         // if time sheet is being edited by a 3rd party, make sure they are authorized;
         // also, don't allow edit of submitted time sheets
      if ($glUserID != $lUserID || $logEntry->bSubmitted){
         if (!bAllowAccess('timeSheetAdmin')){
            redirect('staff/timesheets/ts_log_edit/error_tst_access');
         }
      }

      $this->cts->removeTSEntry($lEntryID);
      $this->session->set_flashdata('msg', 'Time sheet entry was removed.');
      redirect('staff/timesheets/ts_log_edit/add_edit_ts/'.$lTSLogID);
   }

   function ts_submit($lTSLogID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $lTSLogID = (int)$lTSLogID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('staff/mtime_sheets', 'cts');
      $this->load->helper('staff/timesheet');
      $this->load->helper('dl_util/time_date');

      $this->cts->loadUserTSLogByLogID($lTSLogID, $lNumLogRecs, $logRecs, false);
      if ($lNumLogRecs==0){
         redirect('staff/timesheets/ts_log_edit/error_tst_access');
      }
      $logRec     = &$logRecs[0];
      $lTSTID     = $logRec->lTimeSheetID;
      $lUserID    = $logRec->lStaffID;
      $bSubmitted = !is_null($logRec->dteSubmitted);
      $lYear      = (int)date('Y', $logRec->dteTSEntry);

      if ($bSubmitted){
         redirect('staff/timesheets/ts_log_edit/error_tst_access');
      }

         // if time sheet is being edited by a 3rd party, make sure they are authorized;
         // also, don't allow edit of submitted time sheets
      if ($glUserID != $lUserID ){
         if (!bAllowAccess('timeSheetAdmin')){
            redirect('staff/timesheets/ts_log_edit/error_tst_access');
         }
      }

      $bAgree = @$_POST['chkIAgree']=='true';

      if ($bAgree){
         $this->cts->submitTSLog($lTSLogID);
         $this->session->set_flashdata('msg', 'The '.$logRec->enumRptPeriod.' time sheet '
              .'beginning '.date('l, F jS, Y', $logRec->dteTSEntry).' was submitted.');
         redirect('staff/timesheets/ts_log/viewLog/'.$lYear.'/'.$lUserID);
      }else {
         $this->session->set_flashdata('error', 'You must check the box associated with the submission agreement. Your time sheet was <b>NOT</b> submitted.');
         redirect('staff/timesheets/ts_log_edit/add_edit_ts/'.$lTSLogID);
      }
   }

}



