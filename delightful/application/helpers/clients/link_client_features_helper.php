<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2014 Database Austin
  
   author: John Zimmerman 
  
   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
   
---------------------------------------------------------------------   
      $this->load->helper('clients/link_client_features');
---------------------------------------------------------------------*/

//===============================
//    A D D   N E W
//===============================
function strLinkAdd_CProgram($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('cprograms/cprog_add_edit/addEdit/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_CProgAttendance($bUseReturnPath, $lClientID, $lCProgID, $lERecID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('cprograms/enroll_attend_add_edit/addEditA/0/'
                  .$lClientID.'/'.$lCProgID.'/'.$lERecID.'/'.($bUseReturnPath ? 'true' : 'false'), 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_CProgEnrollment($lClientID, $lCProgID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('cprograms/enroll_attend_add_edit/addEditE/0/'.$lClientID.'/'.$lCProgID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_CPPTest($lCatID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('cpre_post_tests/pptest_add_edit/addEditPPTest/0/'.$lCatID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_CPPTestResults($lClientID, $lPPTestID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('cpre_post_tests/client_test_results/addEditTestResults/0/'.$lClientID.'/'.$lPPTestID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_CPPQuestion($lPPTestID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('cpre_post_tests/ppquest_add_edit/addEditQuest/0/'.$lPPTestID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}



//===============================
//    E D I T
//===============================
function strLinkEdit_CProgram($lCProgID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('cprograms/cprog_add_edit/addEdit/'.$lCProgID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_CPPTest($lPPTestID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('cpre_post_tests/pptest_add_edit/addEditPPTest/'.$lPPTestID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_CPPQuestion($lQuestionID, $lPPTestID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('cpre_post_tests/ppquest_add_edit/addEditQuest/'.$lQuestionID.'/'.$lPPTestID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_CPPTestResults($lTestLogID, $lClientID, $lPPTestID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('cpre_post_tests/client_test_results/addEditTestResults/'.$lTestLogID.'/'.$lClientID.'/'.$lPPTestID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}



/*

//===============================
//    E X P O R T
//===============================
function strLinkExport_Table($enumContext, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reports/export_tables/run/'.$enumContext, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EXPORTSMALL, $strTitle));
}
*/


//===============================
//    R E M O V E
//===============================

function strLinkRem_ClientProgram($lCProgID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='', 
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to completely remove this client program?\n\n'
                 .'This will also remove the associated enrollment and attendance records.\n\n'
                 .'This can not be undone.'
                 .'\');" ';
   }

   return(strImageLink('cprograms/cprog_add_edit/remove/'.$lCProgID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_CPPTest($lPPTestID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='', 
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to completely remove this pre/post test?\n\n'
                 .'This will also remove the associated questions and client response records.\n\n'
                 .'This can not be undone.'
                 .'\');" ';
   }

   return(strImageLink('cpre_post_tests/pptest_add_edit/remove/'.$lPPTestID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_CPPQuestion($lQuestionID, $lPPTestID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='', 
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this question?\n\n'
                 .'\');" ';
   }

   return(strImageLink('cpre_post_tests/ppquest_add_edit/remove/'.$lQuestionID.'/'.$lPPTestID.$strLinkExtra, 
                  $strAnchorExtra, $bShowIcon, !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_CPPTestResults($lTestLogID, $lClientID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='', 
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this test score?\n\n'
                 .'\');" ';
   }

   return(strImageLink('cpre_post_tests/client_test_results/remove/'.$lTestLogID.'/'.$lClientID.$strLinkExtra, 
                  $strAnchorExtra, $bShowIcon, !$bShowIcon, IMGLINK_DELETE, $strTitle));
}


//===============================
//    V I E W
//===============================
function strLinkView_CProgram($lCProgID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('cprograms/cprog_record/view/'.$lCProgID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_CProgAttendanceViaCID($lATableID, $lEnrollID, $lClientID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strLinkView_UFMFRecordsViaFID(CENUM_CONTEXT_CPROGATTEND, $lATableID, $lClientID, $strTitle, $bShowIcon, $strAnchorExtra, $lEnrollID));
//   return(strImageLink('cprograms/attendance/view/'.$lCProgID.'/'.$lEnrollID.'/'.$lClientID, $strAnchorExtra, $bShowIcon,
//                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_CProgDirAttendance($lCProgID, $bActive, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('cprograms/cprog_client_dir/optsA/'.$lCProgID.'/'.($bActive ? 'true' : 'false'), 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_CProgDirEnrollment($lCProgID, $bActive, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('cprograms/cprog_client_dir/optsE/'.$lCProgID.'/'.($bActive ? 'true' : 'false'), 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_CProgEnrollRec($lTableID, $lFID, $lEnrollRecID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strLinkView_UFMFRecordViaRecID($lTableID, $lFID, $lEnrollRecID, $strTitle, $bShowIcon, $strAnchorExtra));
}

function strLinkView_CProgAttendRec($lTableID, $lFID, $lARecID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strLinkView_UFMFRecordViaRecID($lTableID, $lFID, $lARecID, $strTitle, $bShowIcon, $strAnchorExtra));
}


function strLinkView_CProgEnrollDir($lCProgID, $bActiveOnly, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('cprograms/cprog_dir/viewEnroll/'.$lCProgID.'/'.($bActiveOnly ? 'true' : 'false'), $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_CPPQuestions($lPPTestID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('cpre_post_tests/ppquest_view/viewQuestions/'.$lPPTestID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_CPPTestView($lPPTestID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('cpre_post_tests/pptest_record/view/'.$lPPTestID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}



/*
depricated; use strLinkView_UFMFRecordViaRecID($lTableID, $lFID, $lRecID, $strTitle, $bShowIcon, $strAnchorExtra='')
function strLinkView_CProgEnrollRec($lERecID, $lCProgID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('cprograms/enroll_rec/view/'.$lERecID.'/'.$lCProgID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

*/



//===============================
//    S P E C I A L
//===============================
function strSpecial_CPPHideShow($lPPTestID, $bCurrentVisibility, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('cpre_post_tests/pptest_add_edit/hideUnhide/'.$lPPTestID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, 
                  ($bCurrentVisibility ? IMGLINK_DEACTIVATE : IMGLINK_ACTIVATE), 
                  ($bCurrentVisibility ? 'Hide' : 'Show')));
}

function strLinkClone_CProgram($lSourceCProgID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('cprograms/cprog_add_edit/clone01/'.$lSourceCProgID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_CLONE, $strTitle));
}

function strLinkClone_CPAttendance($lCProgID, $lSourceAttendanceID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('cprograms/attendance/cloneAttOpts/'.$lCProgID.'/'.$lSourceAttendanceID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_CLONE, $strTitle));
}

function strLinkDebug_CProgram($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('cprograms/cprog_debug/showDebugDetails', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DEBUG, $strTitle));
}

function strLinkUtil_CProgMoveAttend($lClientID, $lCProgID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('cprograms/cprog_util/moveAttendRecs/'.$lClientID.'/'.$lCProgID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_XFER, $strTitle));
}


//===============================
//   R E D I R E C T S
//===============================

/*


function redirect_Auction($lAuctionID){
   redirect('auctions/auctions/viewAuctionRecord/'.$lAuctionID);
}

function redirect_AuctionItem($lItemID){
   redirect('auctions/items/viewItemRecord/'.$lItemID);
}


*/


