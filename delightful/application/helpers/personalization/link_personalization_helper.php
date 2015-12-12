<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('personalization/link_personalization');
---------------------------------------------------------------------*/

//===============================
//    A D D   N E W
//===============================

function strLinkAdd_UFDDLEntry($lTableID, $lFieldID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('admin/uf_ddl/addEditDDLEntry/'.$lTableID.'/'.$lFieldID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_UFField($lTableID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('admin/uf_tables_add_edit/addField/'.$lTableID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}





//===============================
//    E D I T
//===============================

function strLinkEdit_UFDDLEntry($lTableID, $lFieldID, $lEntryID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('admin/uf_ddl/addEditDDLEntry/'.$lTableID.'/'.$lFieldID.'/'.$lEntryID,
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_UFField($lTableID, $lFieldID, $enumFieldType, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('admin/uf_tables_add_edit/addField2a/'.$lTableID.'/'.$lFieldID.'/'.$enumFieldType,
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}



//===============================
//    R E M O V E
//===============================

function strLinkRem_UFDDLEntry($lTableID, $lFieldID, $lEntryID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this drop-down list entry? \');" ';
   }
   return(strImageLink('admin/uf_ddl/removeUF_DDLEntry/'.$lTableID.'/'.$lFieldID.'/'.$lEntryID,
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_UFField($lTableID, $lFieldID, $enumFieldType, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this field? '
                 .'The action can not be undone.\');" ';
   }
   return(strImageLink('admin/uf_tables_add_edit/remField/'.$lTableID.'/'.$lFieldID.'/'.$enumFieldType,
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}



//===============================
//    S P E C I A L S
//===============================

function strLinkSpecial_CloneDDLList($lTableID, $lFieldID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('admin/uf_ddl_clone/clone01/'.$lTableID.'/'.$lFieldID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_CLONE, $strTitle));
}

function strLinkSpecial_CloneDDLListRun($lTableID, $lFieldID, $lSourceDDLFID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('admin/uf_ddl_clone/clone02/'.$lTableID.'/'.$lFieldID.'/'.$lSourceDDLFID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_CLONE, $strTitle));
}


function strLinkConf_UFDDL($lTableID, $lFieldID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('admin/uf_ddl/configDDL/'.$lTableID.'/'.$lFieldID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkSpecial_XferUField($lTableID, $lFieldID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('admin/uf_fields/xfer1/'.$lTableID.'/'.$lFieldID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_XFER, $strTitle));
}


