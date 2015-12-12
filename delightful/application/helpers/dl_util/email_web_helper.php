<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2013 by Database Austin
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('dl_util/email_web');   
---------------------------------------------------------------------
//  strFormatWebSite      ($strTestWeb, $strLabel, ....
//  lCountPersonEmails    ($dbConn, $lPersonID){
//  strPrimaryPeopleEmail ($lPersonID, $bFormatted, &$lEmailID){
//  strPrimaryPeopleWeb   ($lPersonID, &$lWebID)
//  bReasonableEmail      ($strTestEmail)
//  strChapterEmailAddr   ($lChapterID, $bFormatted)
//  lInsertPeopleEmail    ($lPeopleID, $strEmail, ...
//---------------------------------------------------------------------*/


// function strFormatWebSite($strTestWeb, $strLabel, $bIncludeLabIfBlank,
//                 $strClassTag='', $strTarget=''){
// //------------------------------------------------------------------
// //
// //------------------------------------------------------------------
//    if (strlen($strTarget)>0) {
//       $strTarget = " target=\"$strTarget\" ";
//    }else {
//       $strTarget = '';
//    }
// 
//    if (strlen($strTestWeb)>0) {
//       if (strtoupper(substr($strTestWeb,0,4))=='HTTP') {
//          $strFormatWebSite =
//                  $strLabel
//                  .'<a '.$strClassTag.' '.$strTarget.' href="'.$strTestWeb.'">'.$strTestWeb.'</a>';
//       }else {
//          $strFormatWebSite =
//                  $strLabel
//                 .'<a '.$strClassTag.' '.$strTarget.' href="http://'.$strTestWeb.'">'.$strTestWeb.'</a>';
//       }
//    }else {
//       if ($bIncludeLabIfBlank) {
//          $strFormatWebSite = $strLabel;
//       }else {
//          $strFormatWebSite = '';
//       }
//    }
//    return($strFormatWebSite);
// }
//
// function lCountPersonEmails($lPersonID){
// //---------------------------------------------------------------------
// // return the total number of phones associated with the person
// //---------------------------------------------------------------------
//    $sqlStr =
//        "SELECT count(em_lKeyID) AS lNumEmails
//         FROM tbl_people_email '
//         WHERE (em_lPersonID=$lPersonID)
//            AND (NOT em_bRetired);";
// 
//    $result = mysql_query($sqlStr);
//    if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//       screamForHelp('Unexpected SQL error');
//    }else{
//       $numRows = mysql_num_rows($result);
//       if ($numRows==0) {
//          return(0);
//       }else {
//          $row = mysql_fetch_array($result);
//          $vHold = $row['lNumEmails'];
//          if (is_null($vHold)){
//             return(0);
//          }else {
//             return($vHold);
//          }
//       }
//    }
// }
// 
// function strPrimaryPeopleEmail($lPersonID, $bFormatted, &$lEmailID){
// //---------------------------------------------------------------------
// // return the primary email for a given person
// //
// // if $bFormatted, return as a hyperlink
// //---------------------------------------------------------------------
//    $sqlStr =
//        "SELECT em_strEmail, em_lKeyID
//         FROM tbl_people_email
//         WHERE (em_lPersonID=$lPersonID)
//            AND em_bPrimary AND (NOT em_bRetired);";
// 
//    $result = mysql_query($sqlStr);
//    if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//       screamForHelp('Unexpected SQL error');
//    }else{
//       $numRows = mysql_num_rows($result);
//       if ($numRows==0) {
//          $lEmailID = -1;
//          return('');
//       }else {
//          $row = mysql_fetch_array($result);
//          $strEmail = $row['em_strEmail'];
//          $lEmailID = $row['em_lKeyID'];
//          if ($bFormatted) {
//             return(strBuildEmailLink($strEmail,'',false));
//          }else {
//             return($strEmail);
//          }
//       }
//    }
// }
// 
// function strPrimaryPeopleWeb($lPersonID, &$lWebID){
// //---------------------------------------------------------------------
// // return the primary email for a given person
// //
// // if $bFormatted, return as a hyperlink
// //---------------------------------------------------------------------
//    $sqlStr =
//         "SELECT wp_lKeyID, wp_strWebAddress
//          FROM tbl_people_web_page
//          WHERE (wp_lPersonID=$lPersonID) AND (NOT wp_bRetired)
//          ORDER BY wp_lKeyID
//          LIMIT 0,1;";
// 
//    $result = mysql_query($sqlStr);
//    if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//       screamForHelp('Unexpected SQL error');
//    }else{
//       $numRows = mysql_num_rows($result);
//       if ($numRows==0) {
//          $lWebID = -1;
//          return('');
//       }else {
//          $row    = mysql_fetch_array($result);
//          $lWebID = $row['wp_lKeyID'];
//          return($row['wp_strWebAddress']);
//       }
//    }
// }
// 
// function bReasonableEmail($strTestEmail) {
// //---------------------------------------------------------------------
// // return true if the email address seems passibly reasonable
// // i.e. x@y.z
// //---------------------------------------------------------------------
//    $lELen = strlen($strTestEmail);
//    if ($lELen<=4) return(false);
// 
//    if (($lAtPos = strpos($strTestEmail,'@'))===false) return(false);
//    if (($lAtPos<1) || ($lAtPos>($lELen-3))) return(false);
// 
//       // find the dot, starting from the right
//    if (($lDotPos = strrpos($strTestEmail,'.'))===false) return(false);
//    if (($lDotPos>=$lELen-1)||($lDotPos<($lAtPos+1))) return(false);
// 
//    return(true);
// }
// 
// function strChapterEmailAddr($lChapterID, $bFormatted){
// //---------------------------------------------------------------------
// //
// //---------------------------------------------------------------------
//    $sqlStr =
//         "SELECT ch_strEmail
//          FROM tbl_chapters
//          WHERE ch_lKeyID=$lChapterID;";
//    $result = mysql_query($sqlStr);
//    if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//       screamForHelp('Unexpected SQL error');
//    }else{
//       $numRows = mysql_num_rows($result);
//       if ($numRows==0) {
//          return('');
//       }else {
//          $row = mysql_fetch_array($result);
//          $strEmail = $row['ch_strEmail'];
//          if ($bFormatted) {
//             return(strBuildEmailLink($strEmail,'',false));
//          }else {
//             return($strEmail);
//          }
//       }
//    }
// }
// 
// function lInsertPeopleEmail(
//                    $lPeopleID, $strEmail, $strNotes,
//                    $bPrimary,  &$clsBase){
// //---------------------------------------------------------------------
// //
// //---------------------------------------------------------------------
//    $sqlStr =
//        'INSERT INTO tbl_people_email
//         SET
//            em_bPrimary  ='.($bPrimary ? '1' : '0').',
//            em_strEmail  ='.strPrepStr($strEmail).',
//            em_strNotes  ='.strPrepStr($strNotes).",
//            em_lPersonID =$lPeopleID,
//            em_bRetired  =0,
//            em_lOriginID     = $clsBase->lUserID,
//            em_lLastUpdateID = $clsBase->lUserID,
//            em_dteOrigin     = NOW(),
//            em_dteLastUpdate = NOW();";
//    $result = mysql_query($sqlStr);
//    if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//       screamForHelp('Unexpected SQL error');
//    }
//    return(mysql_insert_id());
// }

?>