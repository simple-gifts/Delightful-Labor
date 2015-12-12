<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2013 Database Austin
  
   author: John Zimmerman 
  
   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
   
---------------------------------------------------------------------   
         $this->load->helper('dl_util/link_auction');
---------------------------------------------------------------------*/

define('GSTR_AUCTIONTOPLEVEL', anchor('main/menu/more', 'More', 'class="breadcrumb"'));

//===============================
//    A D D   N E W
//===============================
function strLinkAdd_Auction($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/auction_add_edit/addEditAuction/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_AuctionPackage($lAuctionID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/packages/addEditAuctionPackage/'.$lAuctionID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_AuctionItem($lPackageID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/items/addAuctionItem/'.$lPackageID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_BidSheet($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/bid_templates/addSheetTempSelect', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

/*
function strLinkAdd_BidWinner($lPackageID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('auctions/bids/addBidWinner/'.$lPackageID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}
*/


//===============================
//    E D I T
//===============================
function strLinkEdit_Auction($lAuctionID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/auction_add_edit/addEditAuction/'.$lAuctionID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_AuctionPackage($lAuctionID, $lPackageID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/packages/addEditAuctionPackage/'.$lAuctionID.'/'.$lPackageID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_AuctionItem($lPackageID, $lItemID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/items/addEditAuctionItem/'.$lPackageID.'/'.$lItemID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_AuctionWinnngBid($lPackageID, $lDonorID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/packages/addEditPackageWinner/'.$lPackageID.'/'.$lDonorID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_BidSheet($lBSID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/bid_templates/addEditTemplate/'.$lBSID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}




//===============================
//    E X P O R T
//===============================
/*
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

function strLinkRem_Auction($lAuctionID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='', 
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this auction record?\n\n'
                 .'This will also remove the auction\\\'s associated packages, gift items, and donation records.\n\n'
                 .'This can not be undone.'
                 .'\');" ';
   }

   return(strImageLink('auctions/auction_add_edit/remove/'.$lAuctionID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_AuctionPackage($lAuctionID, $lPackageID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='', 
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this auction package?\n\n'
                 .'This will also remove the package\\\'s associated gift items and donation records.\n\n'
                 .'This can not be undone.'
                 .'\');" ';
   }

   return(strImageLink('auctions/packages/remove/'.$lAuctionID.'/'.$lPackageID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_AuctionItem($lPackageID, $lItemID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='', 
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this auction item?\n\n'
                 .'This can not be undone.'
                 .'\');" ';
   }

   return(strImageLink('auctions/items/remove/'.$lPackageID.'/'.$lItemID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_AuctionWinningBid($lPackageID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='', 
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this winning bid?\n\n'
                 .'It will not effect the package or package items.\n\n'
                 .'\');" ';
   }

   return(strImageLink('auctions/packages/removeBid/'.$lPackageID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_AuctionFulfillment($lPackageID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='', 
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this fulfillment?\n\n'
                 .'It will not effect the package or package items.\n\n'
                 .'\');" ';
   }
   return(strImageLink('auctions/packages/removeFulfillment/'.$lPackageID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_BidSheet($lBSID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='', 
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this bid sheet?\n\n'
                 .'\');" ';
   }
   return(strImageLink('auctions/bid_templates/removeBidSheet/'.$lBSID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}




//===============================
//    V I E W
//===============================

function strLinkView_AuctionOverview($lAuctionID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/auctions/viewAuctionOverview/'.$lAuctionID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_AuctionRecord($lAuctionID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/auctions/viewAuctionRecord/'.$lAuctionID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_AuctionPackageRecord($lPackageID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/packages/viewPackageRecord/'.$lPackageID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_AuctionBids($lAuctionID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/details/viewBidsViaAID/'.$lAuctionID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_AuctionUnfulfilled($lAuctionID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/details/viewUnfulfilledViaAID/'.$lAuctionID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_AuctionPackages($lAuctionID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/packages/viewPackagesViaAID/'.$lAuctionID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_AuctionItemsViaPID($lPackageID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/items/viewItemsViaPID/'.$lPackageID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_AuctionItem($lItemID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/items/viewItemRecord/'.$lItemID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_BidSheetRecord($lBSID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/bid_templates/viewBidSheet/'.$lBSID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_BidSheets($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/bid_templates/main', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}


//===============================
//    S P E C I A L
//===============================
function strLink_SetPackageWinner($lPackageID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/packages/setWinner1/'.$lPackageID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_WINNER, $strTitle));
}

function strLink_SetPackageFulfill($lPackageID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/packages/setFulfill/'.$lPackageID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_FULFILL, $strTitle));
}

function strLink_PDF_PackageBidSheet($lBidSheetID, $lPackageID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showAuctions')) return('');
   return(strImageLink('auctions/bid_sheets/bidsheetViaPID/'.$lBidSheetID.'/'.$lPackageID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_PDFSMALL, $strTitle));
}



//===============================
//   R E D I R E C T S
//===============================
function redirect_Auction($lAuctionID){
   redirect('auctions/auctions/viewAuctionRecord/'.$lAuctionID);
}

function redirect_AuctionItem($lItemID){
   redirect('auctions/items/viewItemRecord/'.$lItemID);
}

function redirect_AuctionOverview($lAuctionID){
  redirect('auctions/auctions/viewAuctionOverview/'.$lAuctionID);
}

function redirect_AuctionPackageList($lAuctionID){
  redirect('auctions/packages/viewPackagesViaAID/'.$lAuctionID);
}

function redirect_AuctionPackage($lPackageID){
  redirect('auctions/packages/viewPackageRecord/'.$lPackageID);
}



