<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2014-2015 Database Austin

   author: John Zimmerman

   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
-----------------------------------------------------------------------------
      $this->load->helper('staff/link_staff');
-----------------------------------------------------------------------------*/

//===============================
//    A D D   N E W
//===============================
function strLinkAdd_StaffStatEntry($lStatRptID, $enumEntryType, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/performance_entry/addEditEntry/'.$lStatRptID.'/0/'.$enumEntryType, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_StaffStatRpt($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/performance/addEditPR/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_StaffStatReview($lSRptID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/mgr_preview/addEditReview/0/'.$lSRptID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_TimeSheetTemplate($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/timesheets/add_edit_tst_record/addEditTST/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_TimeSheetProject($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/timesheets/ts_projects/addEditTSProject/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_TimeSheetDailyEntry($lUserID, $lTSLogID, $dteEntry, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/timesheets/ts_log_edit/add_edit_ts_entry/0/'
                 .$lTSLogID.'/'.$lUserID.'/'.$dteEntry,
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}


//===============================
//    E D I T
//===============================
function strLinkEdit_StaffStatEntry($lEntryID, $lStatRptID, $enumEntryType, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/performance_entry/addEditEntry/'.$lStatRptID.'/'.$lEntryID.'/'.$enumEntryType, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_StaffRpt($lRptID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/performance/addEditPR/'.$lRptID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_StaffStatReview($lSRptID, $lReviewID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/mgr_preview/addEditReview/'.$lReviewID.'/'.$lSRptID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_TimeSheetTemplate($lTSTID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/timesheets/add_edit_tst_record/addEditTST/'.$lTSTID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_TimeSheetProject($lProjectID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/timesheets/ts_projects/addEditTSProject/'.$lProjectID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_TSLog($lTSLogID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/timesheets/ts_log_edit/add_edit_ts/'.$lTSLogID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_TSLogEntries($lTSLogEntryID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/timesheets/ts_log_edit/add_edit_ts_entry/'.$lTSLogEntryID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_TSLogToProjects($lTSLogID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/timesheets/ts_projects/assignHrsToProject/'.$lTSLogID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}




//===============================
//    R E M O V E
//===============================

function strLinkRem_StaffStatEntry($lEntryID, $lStatRptID, $enumEntryType,
             $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='',
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this progress report entry?.'
                 .'\');" ';
   }
   return(strImageLink('staff/performance_entry/removeEntry/'.$lStatRptID.'/'.$lEntryID.'/'.$enumEntryType, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}


function strLinkRem_StaffRpt($lRptID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='',
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this progress report?.'
                 .'\');" ';
   }
   return(strImageLink('staff/performance/remove/'.$lRptID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_TimeSheetProject($lProjectID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='',
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this time sheet project?.'
                 .'\');" ';
   }
   return(strImageLink('admin/timesheets/ts_projects/removeProject/'.$lProjectID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_TSLogEntry($lTSLogEntryID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='',
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this time sheet entry?.'
                 .'\');" ';
   }
   return(strImageLink('staff/timesheets/ts_log_edit/remove_tsentry/'.$lTSLogEntryID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}




//===============================
//    V I E W
//===============================
function strLinkView_StaffStatRpt($lRptID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/performance_rec/viewRec/'.$lRptID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_StaffReviewedRpt($lRptID, $lReviewID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/performance_rec/viewRec/'.$lRptID.'/'.$lReviewID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_StaffStatRptLog($lUserID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/mgr_performance/mgrViewLog/'.$lUserID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_TimeSheetTemplateRecord($lTSTID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/timesheets/view_tst_record/viewTSTRecord/'.$lTSTID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_TSLog($lYear, $lStaffID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/timesheets/ts_log/viewLog/'.$lYear.'/'.$lStaffID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_TSLogEntries($lTSLogID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/timesheets/view_ts_log/viewTS/'.$lTSLogID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}


//===============================
//    S P E C I A L
//===============================
function strLink_PDF_StaffReports($enumType, $strReportID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/mgr_perf_rpt/pdf/'.$strReportID.'/'.$enumType, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_PDFSMALL, $strTitle));
}

function strLink_TSLogEntryUnsubmit($lTSLogID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/timesheets/ts_log_edit/unsubmit/'.$lTSLogID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ACTIVATE, $strTitle));
}


