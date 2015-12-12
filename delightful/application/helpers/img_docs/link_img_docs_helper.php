<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2014 Database Austin
  
   author: John Zimmerman 
  
   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
   
---------------------------------------------------------------------   
      $this->load->helper('img_docs/link_img_docs');
---------------------------------------------------------------------*/

//===============================
//    A D D   N E W
//===============================
function strLinkAdd_ID_DDLEntry($enumContext, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('admin')) return('');
   return(strImageLink('admin/admin_imgdoc_tags/addEdit/0/'.$enumContext, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_ImageDoc($enumContext, $enumEntryType, $lFID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editImagesDocs', $enumContext)) return('');
   return(strImageLink('img_docs/upload_image_doc/add/'.$enumContext.'/'.$enumEntryType.'/'.$lFID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

/*
function strLinkAdd_CProgram($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('cprograms/cprog_add_edit/addEdit/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}
*/




//===============================
//    E D I T
//===============================
function strLinkEdit_ImageDoc($enumTType, $lImageDocID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editImagesDocs', $enumTType)) return('');
   return(strImageLink('img_docs/upload_image_doc/edit/'.$lImageDocID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_ImgDocTag($enumContext, $lEntryID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('admin')) return('');
   return(strImageLink('admin/admin_imgdoc_tags/addEdit/'.$lEntryID.'/'.$enumContext, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}




//===============================
//    E X P O R T
//===============================
function strLinkExport_ImgDoc($enumContext, $bImage, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reports/export_tables/'.($bImage ? 'img' : 'doc').'/'.$enumContext, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EXPORTSMALL, $strTitle));
}



//===============================
//    R E M O V E
//===============================
function strLinkRem_ImageDoc($enumTType, $lImageDocID, $bImage, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editImagesDocs', $enumTType)) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this '.($bImage ? 'image' : 'document').'?\');" ';
   }
   return(strImageLink('img_docs/upload_image_doc/remove/'.$lImageDocID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_ImgDocTag($enumContext, $lEntryID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('admin')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this tag?\');" ';
   }
   return(strImageLink('admin/admin_imgdoc_tags/remove/'.$enumContext.'/'.$lEntryID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}




//===============================
//    V I E W
//===============================
function strLinkView_ImageDocs($enumContext, $enumEntryType, $lFID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('img_docs/image_doc_view/view/'.$enumContext.'/'.$enumEntryType.'/'.$lFID,
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_ImageDocsViaTag($lTagID, $enumContext, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bPermitViaContext($enumContext)) return('');
   return(strImageLink('img_docs/image_doc_view_via_tag/view/'.$lTagID,
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

/*


function redirect_Auction($lAuctionID){
   redirect('auctions/auctions/viewAuctionRecord/'.$lAuctionID);
}

function redirect_AuctionItem($lItemID){
   redirect('auctions/items/viewItemRecord/'.$lItemID);
}


*/


