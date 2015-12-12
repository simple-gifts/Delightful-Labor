<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2015 Database Austin

   author: John Zimmerman

   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
-----------------------------------------------------------------------------
      $this->load->helper('staff/link_inventory');
-----------------------------------------------------------------------------*/

//===============================
//    A D D   N E W
//===============================
function strLinkAdd_InventoryCat($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/inventory/icat/addEditICat/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_InventoryItem($lICatID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/inventory/inventory_items/addEditItem/'.$lICatID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

//===============================
//    E D I T
//===============================
function strLinkEdit_InventoryCat($lICatID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/inventory/icat/addEditICat/'.$lICatID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_InventoryItem($lICatID, $lIItemID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/inventory/inventory_items/addEditItem/'.$lICatID.'/'.$lIItemID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

/*
function strLinkEdit_StaffStatEntry($lEntryID, $lStatRptID, $enumEntryType, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/performance_entry/addEditEntry/'.$lStatRptID.'/'.$lEntryID.'/'.$enumEntryType, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}
*/

//===============================
//    R E M O V E
//===============================
function strLinkRem_ICat($lICatID, 
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
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this inventory category?.'
                 .'\');" ';
   }
   return(strImageLink('staff/inventory/icat/removeCat/'.$lICatID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_IItem($lIItemID, 
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
             ' onClick="javascript:return confirm(\'Are you sure you want to delete this inventory item record?\n\n' 
             .'You can removed the item from the inventory while preserving the history by selecting:\n'
             .'         Actions / Remove item from inventory.'.'\');" ';
   }
   return(strImageLink('staff/inventory/inventory_items/removeIItem/'.$lIItemID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

/*
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
*/

//===============================
//    V I E W
//===============================
function strLinkView_InventoryItem($lIItemID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/inventory/inventory_items/iitemRec/'.$lIItemID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_InventoryItemsByCat($lICatID, $enumType, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/inventory/inventory_items/viewViaCatID/'.$enumType.'/'.$lICatID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

/*
function strLinkView_StaffStatRpt($lRptID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/performance_rec/viewRec/'.$lRptID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}
*/

//===============================
//    S P E C I A L
//===============================
function strLinkXfer_IItemCat($lIItemID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/inventory/inventory_items/xferItemCatOpts/'.$lIItemID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_XFER, $strTitle));
}

function strLinkCO_IItem($lIItemID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/inventory/inventory_cico/checkOutItem/'.$lIItemID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_CICO_CHECKOUT, $strTitle));
}

function strLinkCI_IItem($lIItemID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/inventory/inventory_cico/checkInItem/'.$lIItemID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_CICO_CHECKIN, $strTitle));
}

function strLink_IItemLost($lIItemID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/inventory/inventory_items/itemLost/'.$lIItemID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_LOSTFOUND, $strTitle));
}

function strLink_IItemFound($lIItemID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/inventory/inventory_items/itemFound/'.$lIItemID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_LOSTFOUND, $strTitle));
}

function strLink_IItemRemoveFromInventory($lIItemID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   $strAnchorExtra .=
          ' onClick="javascript:return confirm(\'Are you sure you want to remove this item from the inventory?\n\n' 
          .'The item information and loan history will be retained, and you\n'
          .'can return the item to the inventory at a later time if necessary.'.'\');" ';

   return(strImageLink('staff/inventory/inventory_items/itemRemoveFromInv/'.$lIItemID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DEACTIVATE, $strTitle));
}

function strLink_IItemUnRemoveFromInventory($lIItemID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('staff/inventory/inventory_items/itemRestoreToInv/'.$lIItemID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ACTIVATE, $strTitle));
}

//===============================
//    R E D I R E C T S
//===============================
function redirect_InventoryItem($lIItemID){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   redirect('staff/inventory/inventory_items/iitemRec/'.$lIItemID);
}



