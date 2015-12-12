<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2013 Database Austin
  
   author: John Zimmerman 
  
   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
   
---------------------------------------------------------------------   
         $this->load->helper('emr/link_emr');
---------------------------------------------------------------------*/


//===============================
//    A D D   N E W
//===============================
function strLinkAdd_EMR_Measurement($lClientID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('emr/measurements/addEditMeasurement/'.$lClientID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}


//===============================
//    E D I T
//===============================
function strLinkEdit_EMRMeasurementAuction($lMeasureID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('emr/measurements/viewMeasurement/'.$lMeasureID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}
/*
*/




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
/*
function strLinkRem_Auction($lAuctionID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='', 
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
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this auction record?\n\n'
                 .'This will also remove the auction\\\'s associated packages, gift items, and donation records.\n\n'
                 .'This can not be undone.'
                 .'\');" ';
   }

   return(strImageLink('auctions/auction_add_edit/remove/'.$lAuctionID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}
*/



//===============================
//    V I E W
//===============================
function strLinkView_EMRMeasurements($lClientID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('emr/measurements/viewMeasurement/'.$lClientID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}
/*
function strLinkView_AuctionOverview($lAuctionID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('auctions/auctions/viewAuctionOverview/'.$lAuctionID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}
*/



//===============================
//   R E D I R E C T S
//===============================
function redirect_ClientMeasure($lClientID){
   redirect('emr/measurements/viewMeasurement/'.$lClientID);
}
/*
*/



