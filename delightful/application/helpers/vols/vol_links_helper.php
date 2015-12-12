<?php
/*---------------------------------------------------------------------
   Delightful Labor!
   copyright (c) 2013-2015 Database Austin

   author: John Zimmerman

   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.

---------------------------------------------------------------------
         $this->load->helper('vols/vol_links');
---------------------------------------------------------------------*/


//===============================
//    A D D   N E W
//===============================
function strLinkAdd_VolReg_RegForm($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('volunteers/registration/addEditRegForm/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_VolClientAssoc($lVolID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('volunteers/vol_client_association/addS1/'.$lVolID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}


//===============================
//    E D I T
//===============================
function strLinkEdit_VolRegForm($lVolRegFormID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('volunteers/registration/addEditRegForm/'.$lVolRegFormID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}



//===============================
//    E X P O R T
//===============================
function strLinkExport_VolReg($lVolRegID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reports/exports/exportVolReg/'.$lVolRegID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EXPORTSMALL, $strTitle));
}

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
function strLinkRem_VolRegForm($lVolRegFormID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='',
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
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this volunteer registration form?\n\n'
                 .'This can not be undone.'
                 .'\');" ';
   }
   return(strImageLink('volunteers/registration/remove/'.$lVolRegFormID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_VolClientAssoc($lVAID, $lVolID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='',
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
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this volunteer/client association?'
                 .'\');" ';
   }
   return(strImageLink('volunteers/vol_client_association/remove/'.$lVAID.'/'.$lVolID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}



//===============================
//    V I E W
//===============================
function strLinkView_VolsViaRegFormID($lRegFormID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('volunteers/vol_directory/viewViaRegFormID/'.$lRegFormID.'/A', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_VolsRecentViaRegFormID($lRegFormID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('volunteers/vol_directory/viewRecentViaRegFormID/'.$lRegFormID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_VolsJobCodeViaMonth($lYear, $lMonth, $lJobCodeID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reports/pre_vol_jobcodes/jcMonthDetailRun/'.$lYear.'/'.$lMonth.'/'.$lJobCodeID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}



//===============================
//   R E D I R E C T S
//===============================
function redirect_VolRegForms(){
   redirect('volunteers/registration/view');
}
/*
*/



