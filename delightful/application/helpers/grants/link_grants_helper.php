<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2015 Database Austin
  
   author: John Zimmerman 
  
   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
   
---------------------------------------------------------------------   
      $this->load->helper('grants/link_grants');
---------------------------------------------------------------------*/

//===============================
//    A D D   N E W
//===============================
function strLinkAdd_Grant($lProviderID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showGrants')) return('');
   return(strImageLink("grants/grant_add_edit/addEdit/$lProviderID/0", $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_GrantProvider($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showGrants')) return('');
   return(strImageLink('grants/provider_add_edit/addEdit/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}


//===============================
//    E D I T
//===============================
function strLinkEdit_Grant($lGrantID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showGrants')) return('');
   return(strImageLink('grants/grant_add_edit/addEdit/'.$lGrantID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_GrantProvider($lProviderID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showGrants')) return('');
   return(strImageLink('grants/provider_add_edit/addEdit/'.$lProviderID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}



//===============================
//    E X P O R T
//===============================
/*
function strLinkExport_ImgDoc($enumContext, $bImage, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reports/export_tables/'.($bImage ? 'img' : 'doc').'/'.$enumContext, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EXPORTSMALL, $strTitle));
}
*/


//===============================
//    R E M O V E
//===============================
function strLinkRem_Provider($lProviderID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showGrants')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this Provider/Funder? This will remove all associated grants.\');" ';
   }
   return(strImageLink('grants/provider_add_edit/remove/'.$lProviderID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_Grant($lGrantID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showGrants')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this grant?\');" ';
   }
   return(strImageLink('grants/grant_add_edit/remove/'.$lGrantID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}




//===============================
//    V I E W
//===============================
function strLinkView_Grant($lGrantID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showGrants')) return('');
   return(strImageLink('grants/grant_record/viewGrant/'.$lGrantID,
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_GrantProvider($lProviderID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showGrants')) return('');
   return(strImageLink('grants/provider_record/viewProvider/'.$lProviderID,
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}




//===============================
//    S P E C I A L
//===============================
/*
function strSpecial_CPPHideShow($lPPTestID, $bCurrentVisibility, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('cpre_post_tests/pptest_add_edit/hideUnhide/'.$lPPTestID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, 
                  ($bCurrentVisibility ? IMGLINK_DEACTIVATE : IMGLINK_ACTIVATE), 
                  ($bCurrentVisibility ? 'Hide' : 'Show')));
}
*/


//===============================
//   R E D I R E C T S
//===============================



function redirect_GrantProvider($lProviderID){
   redirect('grants/provider_record/viewProvider/'.$lProviderID);
}

/*
function redirect_AuctionItem($lItemID){
   redirect('auctions/items/viewItemRecord/'.$lItemID);
}


*/


