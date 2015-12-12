<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2014-2015 Database Austin

   author: John Zimmerman

   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.

---------------------------------------------------------------------
      $this->load->helper('creports/link_creports');
---------------------------------------------------------------------*/

//===============================
//    A D D   N E W
//===============================
function strLinkAdd_CReport($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('creports/add_edit_crpt/add_edit/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_CReportSearchTerm($lReportID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('creports/search_terms/add_edit/'.$lReportID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_CReportSortTerm($lReportID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('creports/sort_terms/add_edit/'.$lReportID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}



//===============================
//    E D I T
//===============================
function strLinkEdit_CReport($lReportID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('creports/add_edit_crpt/add_edit/'.$lReportID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_CReportSearchTerm($lReportID, $strFNID, $lTermID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('creports/search_terms/term_selected/'.$lReportID.'/'.$strFNID.'/'.$lTermID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_CRptSearchFieldOrder($lReportID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('creports/search_order/add_edit/'.$lReportID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_CRptSearchFieldParens($lReportID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('creports/search_parens/add_edit/'.$lReportID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}



//===============================
//    E X P O R T
//===============================


//===============================
//    R E M O V E
//===============================
function strLinkRem_CReport($lRptID, $strTitle,  $bShowIcon,  $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this custom report?\');" ';
   }
   return(strImageLink('creports/custom_directory/remove/'.$lRptID,
                   $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_CReportSearchTerm($lReportID, $lTermID, $strTitle,  $bShowIcon,  $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this search term?\');" ';
   }
   return(strImageLink('creports/search_terms/remove/'.$lReportID.'/'.$lTermID.'/',
                   $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_CReportSortTerm($lReportID, $lSortTermID, $strTitle,  $bShowIcon,  $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this sort term?\');" ';
   }
   return(strImageLink('creports/sort_terms/remove/'.$lReportID.'/'.$lSortTermID.'/',
                   $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}



//===============================
//    V I E W
//===============================
function strLinkView_CReportFields($lRptID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('creports/custom_fields/viewRecFields/'.$lRptID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_CReportRec($lRptID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('creports/custom_directory/viewRec/'.$lRptID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}



//===============================
//    S P E C I A L
//===============================
function strLinkView_CReportDebugGenSQL($lRptID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('creports/run_creport/debugSQLGen/'.$lRptID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DEBUG, $strTitle));
}

function strLinkView_CReportExport($lRptID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reports/reports/crunExport/'.$lRptID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EXPORT, $strTitle));
}

function strLinkView_CReportRun($lRptID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('creports/run_creport/reviewReport/'.$lRptID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_RUNREPORT, $strTitle));
}

function strLinkEdit_CRptSortingTermsOrder($lReportID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('creports/search_order/addEditSortOrder/'.$lReportID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_CRptDisplayTermsOrder($lReportID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('creports/display_order/addEditDisplayTermOrder/'.$lReportID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
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


