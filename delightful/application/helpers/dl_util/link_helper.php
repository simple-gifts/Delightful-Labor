<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2013-2014 Database Austin
  
   author: John Zimmerman 
  
   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------*/

define ('IMGLINK_ACTIVATE',        'activate.png');
define ('IMGLINK_ADDNEW',          'recordAdd.gif');
define ('IMGLINK_ADDNEWPERSON',    'newPerson.png');
define ('IMGLINK_APPLYCHARGES',    'applyCharges01.png');
define ('IMGLINK_ASSESSMENTS',     'assessment01.png');
define ('IMGLINK_CICO_CHECKIN',    'cico_check_in.png');
define ('IMGLINK_CICO_CHECKOUT',   'cico_check_out.png');
define ('IMGLINK_CLONE',           'clone01.gif');
define ('IMGLINK_CONFIG',          'configIcon.png');
define ('IMGLINK_DEBUG',           'debug.gif');
define ('IMGLINK_DEACTIVATE',      'inactive03.gif');
define ('IMGLINK_NODELETE',        'cantDelete.gif');   
define ('IMGLINK_DELETE',          'delete02.gif');      
define ('IMGLINK_DELETESMALL',     'deleteSmall01.gif'); 
define ('IMGLINK_EDIT',            'edit.png');
define ('IMGLINK_EXPORT',          'export1.png');
define ('IMGLINK_EXPORTSMALL',     'exportSmall.png');
define ('IMGLINK_MAKE_HOH',        'makeHOH.gif');
define ('IMGLINK_LOSTFOUND',       'lostFound01.png');
define ('IMGLINK_LOCKSET',         'lock.png');
define ('IMGLINK_LOCKUNSET',       'lock_open.png');
define ('IMGLINK_REPORTLARGE',     'report02a.gif');
define ('IMGLINK_REPORTMED',       'reportMR02a.gif');
define ('IMGLINK_RETURN',          'return.gif');
define ('IMGLINK_RUNREPORT',       'runReport.png');
define ('IMGLINK_SELECT',          'select.png');
define ('IMGLINK_SEARCH',          'search04.png');
define ('IMGLINK_TIMESTAMP',       'timeStampSmall.png');
define ('IMGLINK_XFER',            'transfer01.gif');
define ('IMGLINK_VIEW',            'viewIcon.gif');
define ('IMGLINK_VIEWGIFTHISTORY', 'iconDonationHistory.png');
define ('IMGLINK_VIEWALLUSERS',    'viewAllUsers.png');

define ('IMGLINK_GIFTACK',         'giftAck03.png');

define ('IMGLINK_UP',              'arrowUpGreen01.png');
define ('IMGLINK_DOWN',            'arrowDownGreen01.png');
define ('IMGLINK_TOP',             'arrowTopGreen01.png');
define ('IMGLINK_BOTTOM',          'arrowBottomGreen01.png');
define ('IMGLINK_ARROWBLANK',      'arrowBlank.png');

define ('IMGLINK_HIDE',            'zoomHide.png');
define ('IMGLINK_UNHIDE',          'zoomShow.png');

define ('IMGLINK_JOOMLA',          'joomla_small.png');

define ('IMGLINK_WINNER',          'winner_icon.gif');
define ('IMGLINK_FULFILL',         'fulfill.gif');
define ('IMGLINK_PDFSMALL',        'pdfIiconSmall.gif');
define ('IMGLINK_DOLLAR',          base_url().'images/misc/dollar.gif');

define ('IMGLINK_CHECKON',         base_url().'images/misc/checkBoxOn.gif');
define ('IMGLINK_CHECKOFF',        base_url().'images/misc/checkBoxOff.gif');

function strImageLink($strAnchor, $strAnchorExtra, $bShowImage,
                      $bShowText, $enumImage,      $strLinkText,
                      $strHeight=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   $strLink = '';

   if ($bShowImage){
      if (is_null($strHeight)){
         $strHeight = '';
      }else {
         $strHeight = ' height="'.$strHeight.'" ';
      }
      $strLink .= anchor($strAnchor, '<img src="'.base_url().'images/misc/'.$enumImage.'" '.$strHeight
                                   .' title="'.$strLinkText.'" border="0" alt="link" />', $strAnchorExtra);
   }
   if ($bShowImage && $bShowText) $strLink .= '&nbsp;';
   if ($bShowText){
      $strLink .= anchor($strAnchor, $strLinkText, $strAnchorExtra);
   }
   return($strLink);
}



//===============================
//    A D D   N E W
//===============================
function strLinkAdd_Biz($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('dataEntryPeopleBizVol')) return('');
   return(strImageLink('biz/biz_add_edit/addEditBiz/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_BizContact($lBID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('dataEntryPeopleBizVol')) return('');
   return(strImageLink('biz/biz_contact_add_edit/addNewS1/'.$lBID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEWPERSON, $strTitle));
}

function strLinkAdd_Client($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('clients/client_rec_add_edit/addNewS1', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

/*
function strLinkAdd_ClientViaCustomForm($lFormID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('custom_forms/add_edit_parent/addEditParent/0/'.$lFormID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}
*/

function strLinkAdd_ClientToSpon($lSponID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('sponsors/client_to_sponsor/add/'.$lSponID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_ClientLocation($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('clients/locations/addEdit/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_ClientStatCat($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('clients/status_cat/addEdit/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_ClientStatEntry($id, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
// status category entry, not individual client status
//---------------------------------------------------------------
   return(strImageLink('clients/status_cat_entry/addEdit/'.$id.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_ClientStat($lClientID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
// individual client status
//---------------------------------------------------------------
   return(strImageLink('clients/client_rec_stat/addEditStatusEntry/'.$lClientID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_ClientVoc($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('clients/client_voc/addEdit/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_CustomForm($enumType, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('custom_forms/custom_form_add_edit/addEditCForm/0/'.$enumType, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_CustomFormDataEntry($lCustomFormID, $lParentID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('custom_forms/data_entry/addFromCForm/'.$lParentID.'/'.$lCustomFormID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_Deposit($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('dataEntryGifts')) return('');
   return(strImageLink('financials/deposits_add_edit/addDeposit', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}


function strLinkAdd_GenericListItem($strListType, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/alists_generic/addEdit/'.$strListType.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_Gift($lFID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('dataEntryGifts')) return('');
   return(strImageLink('donations/add_edit/addEditGift/0/'.$lFID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_GiftAcct($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showFinancials')) return('');
   return(strImageLink('accts_camp/accounts/addEdit/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_GiftCamp($lAcctID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showFinancials')) return('');
   return(strImageLink('accts_camp/campaigns/addEdit/'.$lAcctID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_GiftHonMem($lGiftID, $bHon, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editGifts')) return('');
   return(strImageLink('donations/hon_mem/addNew/'.$lGiftID.'/'.($bHon ? 'hon' : 'mem'), $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_HonMem($bHon, $bMailContact, $lHMID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editGifts')) return('');
   if ($bMailContact){
      $strExtra = '/'.$lHMID;
   }else {
      $strExtra = '';
   }
   return(strImageLink('donations/hon_mem/addNewSelect/'.($bHon ? 'hon' : ($bMailContact ? 'mc' :'mem')).$strExtra,
                      $strAnchorExtra, $bShowIcon,
                      !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_Group($enumGroupType, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('groups/add_edit_group/addEdit/'.$enumGroupType.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

/*
function strLinkAdd_ImageDoc($enumContext, $enumEntryType, $lFID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editImagesDocs', $enumContext)) return('');
   return(strImageLink('img_docs/upload_image_doc/add/'.$enumContext.'/'.$enumEntryType.'/'.$lFID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}
*/
function strLinkAdd_PeopleRelItem($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/admin_special_lists/people_lists/addEditRel/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_PeopleRec($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('dataEntryPeopleBizVol')) return('');
   return(strImageLink('people/people_add_new/selCon', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_PersonToHousehold($lHID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('dataEntryPeopleBizVol')) return('');
   return(strImageLink('people/household/addNewS1/'.$lHID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_Pledge($lFID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('dataEntryGifts')) return('');
   return(strImageLink('donations/pledge_add_edit/addEdit/0/'.$lFID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_PledgePayment($lPledgeID, $dtePledge, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('dataEntryGifts')) return('');
   return(strImageLink('donations/pledge_add_edit/addEditPayment/'.$lPledgeID.'/0/'.$dtePledge, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_Relationship($lPID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('dataEntryPeopleBizVol')) return('');
   return(strImageLink('people/relationships/addEditS1/'.$lPID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_Reminder($enumRemType,  $lFID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('dataEntryPeopleBizVol')) return('');
   return(strImageLink('reminders/rem_add_edit/addEdit/'.$enumRemType.'/'.$lFID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_SponProg($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/admin_special_lists/sponsorship_lists/addEditProg/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_Sponsorship($lForeignID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showSponsors')) return('');
   return(strImageLink('sponsors/add_edit_sponsorship/addNewS1/'.$lForeignID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_SponsorCharges($lSponID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('sponsors/charges/addEditCharge/'.$lSponID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_SponsorAutoCharges($lMonth, $lYear, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to add sponsorship auto-charges for '
                       .date('F Y', strtotime($lMonth.'/1/'.$lYear)).'? \');" ';

   return(strImageLink('sponsors/auto_charge/addAutoCharge/'.$lMonth.'/'.$lYear, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_APPLYCHARGES, $strTitle));
}

function strLinkAdd_SponsorPayment($lSponID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('sponsors/payments/addNewPayment/'.$lSponID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_SponHonoree($lSponID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('sponsors/honorees/addNewS1/'.$lSponID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_User($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/accts/addEdit/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_UFTable($enumTType, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/uf_tables_add_edit/addEditTable/'.$enumTType.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_UFLogEntry($enumTType, $lLogFieldID, $lTableID,    $lForeignID,
                               $bShowIcon,   $strTitle='', $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editUTable', $enumTType)) return('');
   return(strImageLink('admin/uf_log/addEditLogEntry/'.$lTableID.'/'.$lLogFieldID.'/'.$lForeignID.'/0',
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_UFMultiRecEntry($enumTType, $lTableID, $lForeignID,
                               $bShowIcon,   $strTitle='', $strAnchorExtra='', $lEnrollRecID=0,
                               $bUseReturnPath=false){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editUTable', $enumTType)) return('');
   return(strImageLink('admin/uf_multirecord/addEditMultiRecord/'
             .$lTableID.'/'.$lForeignID.'/0/'.$lEnrollRecID.'/'.($bUseReturnPath ? 'true' : 'false'),
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_VolFromPeople($lPeopleID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('dataEntryPeopleBizVol')) return('');
   return(strImageLink('volunteers/vol_add_edit/addViaPID/'.$lPeopleID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_VolEvent($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   return(strImageLink('volunteers/events_add_edit/addEditEvent/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_VolEventDate($lEventID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   return(strImageLink('volunteers/event_dates_add_edit/addEditDate/'.$lEventID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_VolEventDateShift($lEventDateID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   return(strImageLink('volunteers/event_date_shifts_add_edit/addEditShift/'.$lEventDateID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_VolEventDateShiftVol($lEventDateID, $lShiftID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('volunteers/event_date_shift_vols_add_edit/addEditVolsForShift/'
                  .$lEventDateID.'/'.$lShiftID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_VolUnschedHrs($lVolID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   return(strImageLink('volunteers/unscheduled/addEditActivity/'.$lVolID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}

function strLinkAdd_VolUnschedHrsAsVol($lVolID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('volEditHours')) return('');
   return(strImageLink('volunteers/unscheduled/addEditActivityAsVol/'.$lVolID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ADDNEW, $strTitle));
}




//===============================
//    E D I T
//===============================

function strLinkEdit_Biz($lBID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   return(strImageLink('biz/biz_add_edit/addEditBiz/'.$lBID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_BizContact($lBID, $lContactID, $lPID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   return(strImageLink('biz/biz_contact_add_edit/contactAddEdit/'.$lBID.'/'.$lContactID.'/'.$lPID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_Chapter($lChapterID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('admin/org/addEdit/'.$lChapterID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_Client($lCID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('clients/client_rec_add_edit/addEdit/'.$lCID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_ClientIntakeForm($lFormID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('client_intake/intake_add_edit/addEditIntakeForm/'.$lFormID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_ClientLocation($lCLID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('clients/locations/addEdit/'.$lCLID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_ClientRecVoc($lCID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
// edit individual client's vocabulary preference
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('clients/client_rec_voc/addEdit/'.$lCID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_ClientRecStatCat($lCID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('clients/client_rec_stat/addEditStatCat/'.$lCID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_ClientStatCat($lCSCatID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('clients/status_cat/addEdit/'.$lCSCatID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_ClientStatEntry($lSCID, $lKeyID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('clients/status_cat_entry/addEdit/'.$lSCID.'/'.$lKeyID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_ClientStat($lClientID, $lStatusID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
// individual client status
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('clients/client_rec_stat/addEditStatusEntry/'.$lClientID.'/'.$lStatusID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_ClientVoc($lVocID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
// edit vocabulary setup
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('clients/client_voc/addEdit/'.$lVocID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_CustomForm($lCFormID, $enumType, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('custom_forms/custom_form_add_edit/addEditCForm/'.$lCFormID.'/'.$enumType, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_Deposit($lDepositID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
// edit vocabulary setup
//---------------------------------------------------------------
   if (!bAllowAccess('editGifts')) return('');
   return(strImageLink('financials/deposits_add_edit/editDeposit/'.$lDepositID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_GiftAcct($lAcctID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editGifts')) return('');
   return(strImageLink('accts_camp/accounts/addEdit/'.$lAcctID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_GiftCamp($lAcctID, $lCampID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editGifts')) return('');
   return(strImageLink('accts_camp/campaigns/addEdit/'.$lAcctID.'/'.$lCampID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_GiftRecord($lGiftID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editGifts')) return('');
   return(strImageLink('donations/add_edit/addEditGift/'.$lGiftID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_GroupName($lGID, $enumGroupType, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('groups/add_edit_group/addEdit/'.$enumGroupType.'/'.$lGID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

/*
function strLinkEdit_ImageDoc($enumTType, $lImageDocID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editImagesDocs', $enumTType)) return('');
   return(strImageLink('img_docs/upload_image_doc/edit/'.$lImageDocID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}
*/
function strLinkEdit_PeopleContact($lPeopleID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!(bAllowAccess('editPeopleBizVol') || bAllowAccess('volEditContact'))) return('');
   return(strImageLink('people/people_add_edit/contact/'.$lPeopleID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_PeopleRelItem($lKeyID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/admin_special_lists/people_lists/addEditRel/'.$lKeyID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_Pledge($lPledgeID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editGifts')) return('');
   return(strImageLink('donations/pledge_add_edit/addEdit/'.$lPledgeID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_PledgePayment($lPledgeID, $lGiftID, $dtePledge, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('dataEntryGifts')) return('');
   return(strImageLink('donations/pledge_add_edit/addEditPayment/'.$lPledgeID.'/'.$lGiftID.'/'.$dtePledge, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}


function strLinkEdit_SponProg($lSPID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/admin_special_lists/sponsorship_lists/addEditProg/'.$lSPID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_SponRec($lSponID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('sponsors/add_edit_sponsorship/editRec/'.$lSponID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_SponChargeRec($lSponID, $lChargeID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('sponsors/charges/addEditCharge/'.$lSponID.'/'.$lChargeID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
} 

function strLinkEdit_SponsorPayment($lSponID, $lFPayerID, $lPayID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('sponsors/payments/addEditPayment/'.$lSponID.'/'.$lFPayerID.'/'.$lPayID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
} 

function strLinkEdit_Relationship($lRelID, $bA2B, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   return(strImageLink('people/relationships/edit/'.$lRelID.'/'.($bA2B ? 'true' : 'false'), $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
} 

function strLinkEdit_GenericListItem($lListID, $strListType, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/alists_generic/addEdit/'.$strListType.'/'.$lListID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_UFTable($lTableID, $enumTType, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/uf_tables_add_edit/addEditTable/'.$enumTType.'/'.$lTableID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_UFLogEntry($enumTType,  $lTableID,    $lLogFieldID, 
                                $lForeignID, $lLogEntryID, $strTitle, 
                                $bShowIcon,  $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editUTable', $enumTType)) return('');
   return(strImageLink('admin/uf_log/addEditLogEntry/'.$lTableID.'/'.$lLogFieldID.'/'.$lForeignID.'/'.$lLogEntryID,
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_UFTableEntries($enumTType, $lTableID, $lForeignID, $lEditFieldID, $bShowIcon, $strTitle, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editUTable', $enumTType)) return('');
   return(strImageLink('admin/uf_user_edit/userAddEdit/'.$lTableID.'/'.$lForeignID.'/'.$lEditFieldID,
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_UFMultiRecEntry($enumTType, $lTableID, $lForeignID, $lRecID,
                               $bShowIcon,   $strTitle='', $strAnchorExtra='', 
                               $lEnrollRecID=0, $bUseReturnPath=false){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editUTable', $enumTType)) return('');
   return(strImageLink('admin/uf_multirecord/addEditMultiRecord/'.$lTableID.'/'
                 .$lForeignID.'/'.$lRecID.'/'.$lEnrollRecID.'/'.($bUseReturnPath ? 'true' : 'false'),
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_User($lUserID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/accts/addEdit/'.$lUserID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_YourAcct($lUserID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('more/user_acct/edit/'.$lUserID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_VolEvent($lEventID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   return(strImageLink('volunteers/events_add_edit/addEditEvent/'.$lEventID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_VolSkills($lVolID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   return(strImageLink('volunteers/vol_add_edit/editVolSkills/'.$lVolID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_VolEventDate($lEventID, $lEventDateID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   return(strImageLink('volunteers/event_dates_add_edit/addEditDate/'.$lEventID.'/'.$lEventDateID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_VolEventDateShift($lEventDateID, $lShiftID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   return(strImageLink('volunteers/event_date_shifts_add_edit/addEditShift/'.$lEventDateID.'/'.$lShiftID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle));
}

function strLinkEdit_VolEventHrs($lEventID, $lShiftID, $strTitle, $bShowIcon, $strAnchorExtra='', $strHeight=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   return(strImageLink('volunteers/vol_event_hours/editShiftHrs/'.$lEventID.'/'.$lShiftID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle, $strHeight));
}

function strLinkEdit_VolEventHrsAsVol($lVolID, $lEventID, $lShiftID, $strTitle, $bShowIcon, $strAnchorExtra='', $strHeight=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('volEditHours')) return('');
   return(strImageLink('volunteers/vol_event_hours/editShiftHrsAsVol/'.$lVolID.'/'.$lEventID.'/'.$lShiftID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle, $strHeight));
}

function strLinkEdit_VolEventHrsViaShift($lEventID, $lShiftID, $strReturn, $strTitle, $bShowIcon, $strAnchorExtra='', $strHeight=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   return(strImageLink('volunteers/vol_event_hours/editShiftHrs/'.$lEventID.'/'.$lShiftID.'/'.$strReturn, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle, $strHeight));
}

function strLinkEdit_VolUnschedHrs($lVolID, $lActivityID, $strTitle, $bShowIcon, $strAnchorExtra='', $strHeight=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   return(strImageLink('volunteers/unscheduled/addEditActivity/'.$lVolID.'/'.$lActivityID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle, $strHeight));
}

function strLinkEdit_VolUnschedHrsAsVol($lVolID, $lActivityID, $strTitle, $bShowIcon, $strAnchorExtra='', $strHeight=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('volEditHours')) return('');
   return(strImageLink('volunteers/unscheduled/addEditActivityAsVol/'.$lVolID.'/'.$lActivityID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EDIT, $strTitle, $strHeight));
}

/*
*/



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

/*
function strLinkExport_ImgDoc($enumContext, $bImage, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reports/export_tables/'.($bImage ? 'img' : 'doc').'/'.$enumContext, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EXPORTSMALL, $strTitle));
}
*/
function strLinkExport_UTable($lUTKeyID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reports/export_tables/utab/'.$lUTKeyID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_EXPORTSMALL, $strTitle));
}





//===============================
//    R E M O V E
//===============================
function strLinkRem_Biz($lBizID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='', 
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this business/organization record?\n\n'
                 .'This will also remove the business\\\' associated gifts and sponsorships.\n\n'
                 .'This can not be undone.'
                 .'\');" ';
   }

   return(strImageLink('biz/biz_add_edit/remove/'.$lBizID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_BizContact($lBizID, $lContactID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this business contact?\n\n'
                 .'It will not affect the associated people record.'
                 .'\');" ';
   }

   return(strImageLink('biz/biz_contact_add_edit/removeContact/'.$lBizID.'/'.$lContactID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_ClientLocation($lLocID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this client location?\');" ';
   }

   return(strImageLink('clients/locations/remove/'.$lLocID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_ClientStatEntry($lCatID, $lEntryID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this status entry?\');" ';
   }
   return(strImageLink('clients/status_cat_entry/remove/'.$lCatID.'/'.$lEntryID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_ClientStat($lClientID, $lStatRecID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
// individual client status
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this client status?\');" ';
   }
   return(strImageLink('clients/client_rec_stat/remove/'.$lClientID.'/'.$lStatRecID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_ClientCatEntry($lCatID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this status category?\');" ';
   }
   return(strImageLink('clients/status_cat/removeCat/'.$lCatID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_ClientVoc($lVocID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this vocabulary entry?\');" ';
   }
   return(strImageLink('clients/client_voc/removeVoc/'.$lVocID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_Client($lClientID, $strTitle,  $bShowIcon,  $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
// dirType = 'view' or 'name'
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this client?\n\n'
            .'Only use this feature if the client was added in error. Deleting this client will '
            .'remove associated sponsorships and sponsor payments.\');" ';
   }
   return(strImageLink('clients/client_rec_add_edit/remove/'.$lClientID,
                   $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_ClientIntakeForm($lFormID, $strTitle,  $bShowIcon,  $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
// dirType = 'view' or 'name'
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this client intake form?\n\n\');" ';
   }
   return(strImageLink('client_intake/intake_add_edit/remove/'.$lFormID,
                   $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}


function strLinkRem_CustomForm($lCFormID, $enumType, $strTitle,  $bShowIcon,  $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
// 
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this custom form?\');" ';
   }
   return(strImageLink('custom_forms/custom_form_add_edit/removeCForm/'.$lCFormID.'/'.$enumType,
                   $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_Deposit($lDepositID, $strTitle,  $bShowIcon,  $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
// 
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this deposit?\n\n'
            .'This will not remove any donation records, just the deposit record.\');" ';
   }
   return(strImageLink('financials/deposits_add_edit/removeEntry/'.$lDepositID,
                   $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_DepositEntry($lGiftID, $lDepositID, $strTitle,  $bShowIcon,  $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
// 
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this entry from the deposit?\n\n'
            .'This will not remove any donation records, just the deposit record entry.\');" ';
   }
   return(strImageLink('financials/deposits_add_edit/removeGiftEntry/'.$lGiftID.'/'.$lDepositID,
                   $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_Gift($lGiftID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editGifts')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this donation record?\');" ';
   }
   return(strImageLink('donations/gift_record/remove/'.$lGiftID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_GroupBlockRemove(
                    $lGroupID,     $bUseIcon,         $strFormName,
                    $strTitle,     $strNoCheckText,   $strCheckObjectName,
                    $strHiddenObj, $strHiddenObjValue){
//---------------------------------------------------------------
// sample call:
//     $strLink = strLinkRemBlockFromGroup(
//                            $lGID,               true,            'frmBlockCheck',
//                            'Remove from group', 'chkGroupMem[]', ' (Group members) ',
//                            'BOPT',              'remBlock');
//
//---------------------------------------------------------------

   $strLink =
       '<a title="'.$strTitle.'"
           value="remBlock"
           onClick="'.$strFormName.'.'.$strHiddenObj.'.value=\''.$strHiddenObjValue.'\';"
           href="javascript: if (verifyBoxChecked(\''.$strFormName.'\',
                                                  \''.$strNoCheckText.'\',
                                                  \''.$strCheckObjectName.'\')
                                && confirm(\'Are you sure you want to remove these members from the group?\')
                                                  )
                     document.'.$strFormName.'.submit();">';

   if ($bUseIcon){
      $strLink .=
              '<img src="'.base_url().'images/misc/'.IMGLINK_DELETE.'"
                                 title="'.$strTitle.'"
                                 border="0"></a>';
   }else {
      $strLink .=
              $strTitle.'</a>';
   }
   return($strLink);
}

function strLinkRem_GroupMembership($enumGroupType, $enumSubGroup, $lGroupID, $lFID, $enumFrom, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editGroupMembership', $enumGroupType)) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this group membership?\');" ';
   }
   return(strImageLink('groups/group_util/removeMemberFromGroup/'
                          .$lGroupID.'/'.$lFID.'/'.$enumFrom.'/'.$enumSubGroup,
                   $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_HonMem($lHMID, $bHon, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this contact from the '.($bHon ? 'honorarium' : 'memorial').' list?\n\n'
                     .'It will NOT effect associated gifts or people records.\');" ';
   }
   return(strImageLink('donations/hon_mem/remove/'.$lHMID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_HonMemMailCon($lHMID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this mail contact from the memorial?\n\n'
                     .'It will NOT effect associated gifts or people records.\');" ';
   }
   return(strImageLink('donations/hon_mem/removeMC/'.$lHMID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_HonMemLink($lHMLinkID, $lGiftID, $bHon, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editGifts')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this '.($bHon ? 'honorarium' : 'memorial').' from this gift?.\');" ';
   }
   return(strImageLink('donations/hon_mem/removeHMLink/'.$lHMLinkID.'/'.$lGiftID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_GroupName ($lGID, $enumGroupType, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this group?\');" ';
   }
   return(strImageLink('groups/add_edit_group/remove/'.$enumGroupType.'/'.$lGID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

/*
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
*/
function strLinkRem_PeopleRelItem($lKeyID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this relationship entry?\');" ';
   }
   return(strImageLink('admin/admin_special_lists/people_lists/remove/'.$lKeyID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_People($lPID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='', 
             $strReturnPath=null, $lReturnPathID=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');

   if (is_null($strReturnPath)){
      $strLinkExtra = '';
   }else {
      $strLinkExtra = '/'.$strReturnPath.'/'.$lReturnPathID;
   }
   
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this person\\\'s record?\n\n'
                 .'This will also remove the person\\\'s associated gifts and sponsorships.\n\n'
                 .'This can not be undone.'
                 .'\');" ';
   }
   return(strImageLink('people/people_add_edit/remove/'.$lPID.$strLinkExtra, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_PersonFromHousehold($lPID, $lHouseholdID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this person from the household?\n\n'
                 .'They will become the head of their own household.\');" ';
   }
   return(strImageLink('people/household/removePfromH/'.$lPID.'/'.$lHouseholdID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_Pledge($lPledgeID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editGifts')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this pledge?\n\n'
                 .'Removing the pledge will not remove any associated donation records.\');" ';
   }
   return(strImageLink('donations/pledge_add_edit/remove/'.$lPledgeID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_Relationship($lRelationshipID, $lBasePID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this relationship?\');" ';
   }
   return(strImageLink('people/relationships/remove/'.$lRelationshipID.'/'.$lBasePID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_Reminder($lRID, $lFID, $enumRemType, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(\'Are you sure you want to remove this reminder?\');" ';
   }
   return(strImageLink('reminders/rem_add_edit/remove/'.$enumRemType.'/'.$lFID.'/'.$lRID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_SponProg($lSPID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to retire this sponsorship program?\');" ';
   }
   return(strImageLink('admin/admin_special_lists/sponsorship_lists/retireProg/'.$lSPID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_Sponsorship($lSponID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   echoT("\n");
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to delete this sponsorship?\n\n'
                       .'This should only be used if the sponsorship was added by mistake. '
                       .'If the sponsorship is no longer active, use the TERMINATE button.\n\n'
                       .'Deleting this sponsorship will also delete associated payments.\');" ';
   }
   return(strImageLink('sponsors/add_edit_sponsorship/remove/'.$lSponID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_SponsorCharge($lSponID, $lChargeID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   echoT("\n");
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this sponsorship charge?\');" ';
   }
   return(strImageLink('sponsors/charges/remove/'.$lSponID.'/'.$lChargeID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_SponsorHonoree($lSponID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   echoT("\n");
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove the honoree from this sponsorship?\');" ';
   }
   return(strImageLink('sponsors/honorees/addNewS3/'.$lSponID.'/0', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_SponsorPayment($lSponID, $lPayID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   echoT("\n");
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this sponsorship payment?\');" ';
   }
   return(strImageLink('sponsors/payments/remove/'.$lSponID.'/'.$lPayID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_GenericListItem($lListID, $strListType, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to retire this list item?\');" ';
   }
   return(strImageLink('admin/alists_generic/remove/'.$strListType.'/'.$lListID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_User($lUserID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this user?\');" ';
   }
   return(strImageLink('admin/accts/remove/'.$lUserID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_UFLogEntry($enumTType, $lTableID, $lLogFieldID, $lForeignID, $lLogEntryID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editUTable', $enumTType)) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this log entry? \');" ';
   }
   return(strImageLink('admin/uf_log/removeLogEntry/'.$lTableID.'/'.$lLogFieldID.'/'.$lForeignID.'/'.$lLogEntryID,
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_UFMultiRecEntry($enumTType,      $lTableID, $lFID, 
                                    $lRecID,         $strTitle, $bShowIcon, 
                                    $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editUTable', $enumTType)) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this record? \');" ';
   }
   return(strImageLink('admin/uf_multirecord/removeRecord/'.$lTableID.'/'.$lFID.'/'.$lRecID,
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_VolEvent($lEventID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this event? \');" ';
   }
   return(strImageLink('volunteers/events_add_edit/remove/'.$lEventID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_VolEventDate($lEventID, $lEventDateID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra='', $bSmallIcon=false){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this event date? \');" ';
   }
   return(strImageLink('volunteers/event_dates_add_edit/remove/'.$lEventID.'/'.$lEventDateID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, ($bSmallIcon ? IMGLINK_DELETESMALL : IMGLINK_DELETE), $strTitle));
}

function strLinkRem_VolEventDateShift($lEventDateID, $lShiftID, $strTitle, $bShowIcon, $bJSRemoveCheck, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this event shift? \');" ';
   }
   return(strImageLink('volunteers/event_date_shifts_add_edit/remove/'.$lEventDateID.'/'.$lShiftID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_VolEventDateShiftVol(
                       $lVolAssignID, $lEventDateID, $lShiftID, 
                       $strTitle,     $bShowIcon,    $bJSRemoveCheck, 
                       $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this volunteer from this shift? \');" ';
   }
   return(strImageLink('volunteers/event_date_shift_vols_add_edit/removeVolsFromShift/'
                         .$lVolAssignID.'/'.$lEventDateID.'/'.$lShiftID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}

function strLinkRem_VolUnschedHrs($lVolID, $lActivityID, 
                       $strTitle,     $bShowIcon,    $bJSRemoveCheck, 
                       $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to remove this volunteer activity? \');" ';
   }
   return(strImageLink('volunteers/unscheduled/removeUnscheduled/'.$lVolID.'/'.$lActivityID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DELETE, $strTitle));
}



//===============================
//    V I E W
//===============================
function strLinkView_BizRecord($lBizID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('viewPeopleBizVol')) return('');
   return(strImageLink('biz/biz_record/view/'.$lBizID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_BizContacts($lBizID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('viewPeopleBizVol')) return('');
   return(strImageLink('biz/biz_contacts/viewViaBizID/'.$lBizID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_ClientAssessmentViaCID($lCID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('client_intake/client_forms/viewViaCID/'.$lCID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ASSESSMENTS, $strTitle));
}

function strLinkView_ClientLocation($lCLID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('clients/locations/view/'.$lCLID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_ClientDirByName($bIncludeInactive, $strLetter, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('clients/client_dir/name/'.($bIncludeInactive ? 'Y' : 'N').'/'.$strLetter,
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_ClientRecord($lClientID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('clients/client_record/view/'.$lClientID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_ClientStatEntries($lSCID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('admin/admin_special_lists/clients/statCatEntries/'.$lSCID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_ClientStatusHistory($lClientID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('clients/client_rec_stat/viewStatusHistory/'.$lClientID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_ClientsViaLocation($lLocID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('clients/client_dir/view/'.$lLocID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_ClientsViaSponProg($lSponProgID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('clients/clients_via_prog/view/'.$lSponProgID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_ClientViaStatID($lStatID, $bActive, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('reports/pre_clients/clientViaStatusID/'.$lStatID.'/'.($bActive ? 'true' : 'false'), 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_ClientViaStatCatID($lStatCatID, $bActive, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('reports/pre_clients/clientViaStatCatID/'.$lStatCatID.'/'.($bActive ? 'true' : 'false'), 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_DepositEntry($lDepositID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showFinancials')) return('');
   return(strImageLink('financials/deposit_log/viewEntry/'.$lDepositID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_GiftCampaigns($lAcctID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showFinancials')) return('');
   return(strImageLink('accts_camp/campaigns/viewCampsViaAcctID/'.$lAcctID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_GiftCampaignsXfer($lCampID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showFinancials')) return('');
   return(strImageLink('accts_camp/campaigns/xferCampaign/'.$lCampID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_XFER, $strTitle));
}

function strLinkView_GiftListCamps($lCampID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showFinancials')) return('');
   return(strImageLink('accts_camp/campaigns/viewCampaign/'.$lCampID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_GiftListAccts($lAcctID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showFinancials')) return('');
   return(strImageLink('donations/giftLists/viewGiftsViaAcct/'.$lAcctID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_GiftsRecord($lGiftID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('donations/gift_record/view/'.$lGiftID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_GiftsViaHM($lHMID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showFinancials')) return('');
   return(strImageLink('reports/pre_gifts/giftsViaHMID/'.$lHMID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_GiftsHistory($lForeignID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showGiftHistory')) return('');
   return(strImageLink('donations/gift_history/view/'.$lForeignID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_GroupMembership($lGID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('groups/groups_view/viewMembers/'.$lGID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_Household($lHouseholdID, $lPID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showPeople')) return('');
   return(strImageLink('people/household/view/'.$lHouseholdID.'/'.$lPID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

/*
function strLinkView_ImageDocs($enumContext, $enumEntryType, $lFID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('img_docs/image_doc_view/view/'.$enumContext.'/'.$enumEntryType.'/'.$lFID,
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}
*/
function strLinkView_ImportEntries($lLogID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/import/logDetails/'.$lLogID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_ListHonMem($bHon, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/admin_special_lists/hon_mem/'.($bHon ? 'hon' :'mem').'View', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_OrganizationRecord($lChapterID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('admin/org/orgView/'.$lChapterID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_PeopleRecord($lPeopleID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('viewPeopleBizVol')) return('');
   return(strImageLink('people/people_record/view/'.$lPeopleID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_Pledge($lPledgeID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showFinancials')) return('');
   return(strImageLink('donations/pledge_record/view/'.$lPledgeID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_PledgeViaFID($lFID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showFinancials')) return('');
   return(strImageLink('donations/pledges_via_fid/view/'.$lFID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_ReminderViaObject($enumRemType, $lFID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reminders/reminder_record/viewViaObject/'.$enumRemType.'/'.$lFID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_RptAttrib($enumContextType, $lAttribID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reports/pre_attrib/attribList/'.$enumContextType.'/'.$lAttribID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_RptClientAge($lAgeID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reports/pre_clients/ageRpt/'.$lAgeID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_RptDetailGeneric($reportID, $fIDs, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   $strLink = '/reports/reports/run/'.$reportID.'/0/50';
   if (!is_null($fIDs)){
      foreach ($fIDs as $fID){
         $strLink .= '/'.$fID;
      }
   }
   return(strImageLink($strLink, $strAnchorExtra, $bShowIcon, !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_Sponsorship($lSponID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showSponsors')) return('');
   return(strImageLink('sponsors/view_spon_rec/viewViaSponID/'.$lSponID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_SponDir($bShowInactive, $lSponProgID, $strLookupLetter, 
                             $lStartRec,     $lRecsPerPage,
                             $strTitle,      $bShowIcon,    $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showSponsors')) return('');
   return(strImageLink('sponsors/spon_directory/view'
                       .'/'.($bShowInactive ? 'true' : 'false')
                       .'/'.$lSponProgID
                       .'/'.$strLookupLetter
                       .'/'.$lStartRec
                       .'/'.$lRecsPerPage, 
                    $strAnchorExtra, $bShowIcon,
                    !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_SponsorshipViaPID($lPeopleID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showSponsors')) return('');
   return(strImageLink('sponsors/view_via_people_id/view/'.$lPeopleID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_SponsorFinancials($lSponID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showSponsorFinancials')) return('');
   return(strImageLink('sponsors/financials/viewSponsorFinancials/'.$lSponID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_SponsorCharge($lChargeID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showSponsors')) return('');
   return(strImageLink('sponsors/charges/viewChargeRec/'.$lChargeID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_SponsorPayment($lGiftID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showSponsors')) return('');
   return(strImageLink('sponsors/payments/viewPaymentRec/'.$lGiftID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_SponsorMonthlyCharge($lMonth, $lYear, $lACOID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showSponsors')) return('');
   return(strImageLink('reports/pre_spon_income/monthlyCharge/'.$lMonth.'/'.$lYear.'/'.$lACOID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_SponsorMonthlyPayment($lMonth, $lYear, $lACOID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showSponsors')) return('');
   return(strImageLink('reports/pre_spon_income/monthlyPayment/'.$lMonth.'/'.$lYear.'/'.$lACOID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_SponsorAutoCharge($lAutoChargeID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showSponsors')) return('');
   return(strImageLink('sponsors/auto_charge/autoChargeRecord/'.$lAutoChargeID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_SponsorsViaLocProg($lLocID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showSponsors')) return('');
   return(strImageLink('reports/pre_spon_via_prog/viaLocationID/'.$lLocID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_SponsorsViaSponProg($lSponProgID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showSponsors')) return('');
   return(strImageLink('reports/pre_spon_via_prog/viaList/'.$lSponProgID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_User($lUserID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('admin/accts/view/'.$lUserID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_UsersAll($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('admin/accts/userAcctDir/showAll', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_UserLogins($lUserID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('reports/pre_admin/loginLog/'.$lUserID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_UFFields($lTableID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/uf_fields/view/'.$lTableID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_UFAllLogEntries($lTableID, $lFieldID, $lForeignID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/uf_log/viewLogEntries/'.$lTableID.'/'.$lFieldID.'/'.$lForeignID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_UFMFRecordViaRecID($lTableID, $lFID, $lRecID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/uf_multirecord_view/viewSingleMR/'.$lTableID.'/'.$lFID.'/'.$lRecID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_UFMFRecordsViaFID($enumTType, $lTableID, $lForeignID, $strTitle, $bShowIcon, $strAnchorExtra='', $lEnrollRecID=0){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('viewUTable', $enumTType)) return('');
   return(strImageLink('admin/uf_multirecord_view/viewMRViaFID/'.$lTableID.'/'.$lForeignID.'/'.$lEnrollRecID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_UFTable($lTableID,$strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('adminOnly')) return('');
   return(strImageLink('admin/uf_table_record/viewTable/'.$lTableID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_Volunteer($lVolID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showPeople')) return('');
   return(strImageLink('volunteers/vol_record/volRecordView/'.$lVolID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_VolEventAssignments($lEventID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showPeople')) return('');
   return(strImageLink('volunteers/events_cal/viewVolAssignViaEvent/'.$lEventID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_VolEvent($lEventID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showPeople')) return('');
   return(strImageLink('volunteers/events_record/viewEvent/'.$lEventID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_VolEventDate($lEdateID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showPeople')) return('');
   return(strImageLink('volunteers/event_dates_view/viewDates/'.$lEdateID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_VolEventHrs($lEventID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showPeople')) return('');
   return(strImageLink('volunteers/vol_event_hours/viewHoursViaEvent/true/'.$lEventID, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_VolEventsList($bShowPast, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('volunteers/events_schedule/viewEventsList/'.($bShowPast ? 'true' : 'false'), 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_VolHrsViaVolID($lVolID, $bScheduled, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reports/pre_vol_hours/viaVolID/'.$lVolID.'/'.($bScheduled ? 'true' : 'false'), 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_VolHrsViaYrMon($lYear, $lMon, $strSort, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reports/pre_vol_hours/viaYrMon/'.$lYear.'/'.$lMon.'/'.$strSort, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_VolSumHrsViaYrMon($lYear, $lMon, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reports/pre_vol_hours/volSumYrMon/'.$lYear.(is_null($lMon) ? '' : '/'.$lMon), 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_VolDetailHrsViaYrMon($lYear, $lMon, $lVolID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reports/pre_vol_hours/volDetYrMonVID/'
                    .(is_null($lYear)  ? 'null' : $lYear).'/'
                    .(is_null($lMon)   ? 'null' : $lMon) .'/'
                    .(is_null($lVolID) ? 'null' : $lVolID), 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_VolHrsPVA($lVolID, $dteStart, $dteEnd, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('reports/pre_vol_hours/pvaViaVolID/'.$lVolID.'/'.$dteStart.'/'.$dteEnd, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}

function strLinkView_VolScheduleViaVolID($lVolID, $bShowAll, $bShowPast, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bShowAll){
      $strRptType = 'all';
   }elseif ($bShowPast){
      $strRptType = 'past';
   }else {
      $strRptType = 'cur';
   }
   return(strImageLink('reports/pre_vol_schedule/volSchedule/'.$lVolID.'/'.$strRptType, 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_VIEW, $strTitle));
}




//===============================
//    S P E C I A L S
//===============================

function strLinkDebug_Fields($lTableID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/uf_debug/ufFieldDump/'.$lTableID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DEBUG, $strTitle));
}

function strLinkClone_VolShift($lEventDateID, $lShiftID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('volunteers/event_date_shifts_add_edit/cloneShiftOpts/'.$lEventDateID.'/'.$lShiftID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_CLONE, $strTitle));
}

function strLinkClone_PTable($lTableID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('admin/uf_special/cloneTable/'.$lTableID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_CLONE, $strTitle));
}

function strLinkHideUnhide_HonMem($lHMID, $bHide, $bHon, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('donations/hon_mem/'.($bHide ? 'hide' : 'unhide').'/'.$lHMID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, ($bHide ? IMGLINK_HIDE : IMGLINK_UNHIDE), $strTitle));
}

function strLinkSearchSelect($strAncorBase, $lKeyID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink($strAncorBase.'/'.$lKeyID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_SELECT, $strTitle));
}

function strLinkView_ViaRecType($enumSearchType, $lFKey01=null, $lFKey02=null){
//-----------------------------------------------------------------
//
//-----------------------------------------------------------------
   switch ($enumSearchType){
      case CENUM_CONTEXT_HOUSEHOLD:
         return(strLinkView_Household($lFKey01, $lFKey02, 'View household', true));
         break;
      case CENUM_CONTEXT_PEOPLE:
         return(strLinkView_PeopleRecord($lFKey01, 'View people record', true));
         break;
      case CENUM_CONTEXT_BIZ:
         return(strLinkView_BizRecord($lFKey01, 'View business/prganization record', true));
         break;
      case CENUM_CONTEXT_CLIENT:
         return(strLinkView_ClientRecord($lFKey01, 'View client record', true));
         break;
      case CENUM_CONTEXT_SPONSORSHIP:
         return(strLinkView_Sponsorship($lFKey01, 'View sponsor record', true));
         break;
      default:
         screamForHelp($enumSearchType.': invalid link type<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
         break;
   }
}


function strLinkSpecial_GiftChangeAck($lGiftID, $bSetToAck, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editGifts')) return('');
   return(strImageLink('donations/add_edit/setAck/'.$lGiftID.'/'.($bSetToAck ? 'set' : 'clear'), $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_GIFTACK, $strTitle));
}

function strLinkSpecial_Export($strReportID, $strTitle){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('allowExports')) return('');
   return(anchor('reports/exports/run/'.$strReportID, $strTitle));
}

function strLinkSpecial_ExportDeposit($reportID, $strTitle){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('allowExports')) return('');
   return(anchor('reports/exports/run/'.$reportID, $strTitle));
}

function strLinkSpecial_MakeHOH($lPID, $lHID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   return(strImageLink('people/household/makeHOH/'.$lPID.'/'.$lHID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_MAKE_HOH, $strTitle));
}

function strLinkSpecial_SearchAgain($strPath, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink($strPath, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_RETURN, $strTitle));
}

function strLinkSpecial_SearchClient($strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink('clients/client_search/searchOpts', $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_SEARCH, $strTitle));
}

function strLinkSpecial_SearchSelect($strPath, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   return(strImageLink($strPath, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_SELECT, $strTitle));
}

function strLinkSpecial_SponDeactivate($lSponID, $strTitle, $bShowIcon, $bJSRemoveCheck=true, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to terminate this sponsorship? It will NOT remove any charges or payments. \');" ';
   }
   return(strImageLink('sponsors/add_edit_sponsorship/deactivate/'.$lSponID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DEACTIVATE, $strTitle));
}

function strLinkSpecial_UserDeactivate($lUserID, $strTitle, $bShowIcon, $bJSRemoveCheck=true, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to deactivate this user? \');" ';
   }
   return(strImageLink('admin/accts/deactivate/'.$lUserID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_DEACTIVATE, $strTitle));
}

function strLinkSpecial_UserActivate($lUserID, $strTitle, $bShowIcon, $bJSRemoveCheck=true, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to Activate this user? \');" ';
   }
   return(strImageLink('admin/accts/activate/'.$lUserID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_ACTIVATE, $strTitle));
}

function strLinkSpecial_VolActiveInactive($lVolID, $bSetActive, $strTitle, $bShowIcon, $bJSRemoveCheck=true, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('editPeopleBizVol')) return('');
   $strLabel = ($bSetActive ? 'activate' : 'deactivate');
   if ($bJSRemoveCheck){
      $strAnchorExtra .=
             ' onClick="javascript:return confirm(
                       \'Are you sure you want to '.$strLabel.' this volunteer? '
                       .($bSetActive ? '' : 'It will NOT remove the volunteer\\\'s record.').' \');" ';
   }

   return(strImageLink('volunteers/vol_add_edit/actDeact/'.$lVolID.'/'.($bSetActive ? 'true' : 'false'), 
                  $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, ($bSetActive ? IMGLINK_ACTIVATE : IMGLINK_DEACTIVATE), $strTitle));
}

function strLinkSpecial_XferClient($lClientID, $strTitle, $bShowIcon, $strAnchorExtra=''){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (!bAllowAccess('showClients')) return('');
   return(strImageLink('clients/client_record/xfer1/'.$lClientID, $strAnchorExtra, $bShowIcon,
                  !$bShowIcon, IMGLINK_XFER, $strTitle));
}

function strCantDelete($strTitle, $strHeight=null){
//---------------------------------------------------------------
//
//---------------------------------------------------------------
   if (is_null($strHeight)){
      $strHeight = '';
   }else {
      $strHeight = ' height="'.$strHeight.'" ';
   }
   $strOut = '<img src="'.base_url().'images/misc/'.IMGLINK_NODELETE.'" '.$strHeight
                                .' title="'.$strTitle.'" border="0" />';
   return($strOut);
}



//===============================
//   R E D I R E C T S
//===============================
function redirect_Biz($lBizID){
   redirect('biz/biz_record/view/'.$lBizID);
}

function redirect_BizDirectory($strBizLetter){
   redirect('biz/biz_directory/view/'.$strBizLetter);
}

function redirect_BizContacts($lBizID){
   redirect('biz/biz_contacts/view/'.$lBizID);
}

function redirect_Client($lClientID){
   redirect('clients/client_record/view/'.$lClientID);
}

function redirect_ClientIntakeForms(){
   redirect('client_intake/intake_add_edit/view');
}

function redirect_ClientLocRec($lLocID){
   redirect('clients/locations/view/'.$lLocID);
}

function redirect_Gift($lGiftID){
   redirect('donations/gift_record/view/'.$lGiftID);
}

function redirect_ImportLog($lImportLogID){
   redirect('admin/import/logDetails/'.$lImportLogID);
}

function redirect_More(){
   redirect('main/menu/more');
}

function redirect_Organization($lChapterID){
   redirect('admin/org/orgView/'.$lChapterID);
}

function redirect_People($lPeopleID){
   redirect('people/people_record/view/'.$lPeopleID);
}

function redirect_PeopleDirectory($strLetter){
   redirect('people/people_dir/view/'.$strLetter);
}

function redirect_personalizedTable($lTableID){
   redirect('admin/uf_table_record/viewTable/'.$lTableID);
}

function redirect_ReminderViaObject($lFID, $enumRemType){
   redirect('reminders/reminder_record/viewViaObject/'.$enumRemType.'/'.$lFID);
}

function redirect_Reports(){
   redirect('main/menu/reports');
}

function redirect_SponsorAutoChargeEntry($lAutoChargeID){
   redirect('sponsors/auto_charge/autoChargeRecord/'.$lAutoChargeID);
}

function redirect_SponsorshipRecord($lSID){
   redirect('sponsors/view_spon_rec/viewViaSponID/'.$lSID);
}

function redirect_SponsorshipChargeRec($lChargeID){
   redirect('sponsors/charges/viewChargeRec/'.$lChargeID);
}

function redirect_SponsorshipPaymentRec($lPayID){
   redirect('sponsors/payments/viewPaymentRec/'.$lPayID);
}

function redirect_userAcct(){
   global $glUserID;
  redirect('more/user_acct/view/'.$glUserID);
}

function redirect_Vol($lVolID){
   redirect('volunteers/vol_record/volRecordView/'.$lVolID);
}

function redirect_VolEvent($lEventID){
   redirect('volunteers/events_record/viewEvent/'.$lEventID);
}

function redirect_VolEventDate($lEventDateID){
   redirect('volunteers/event_dates_view/viewDates/'.$lEventDateID);
}

function redirect_VolEventHours($lEventID){
   redirect('volunteers/vol_event_hours/viewHoursViaEvent/true/'.$lEventID);
}

function redirect_VolLoginGeneric(){
   redirect('vol_reg/user/landing');
}

function redirect_VolRec($lVolID){
   redirect('volunteers/vol_record/volRecordView/'.$lVolID);
}

function redirect_VolEventList(){
   redirect('volunteers/events_schedule/viewEventsList');
}

function redirect_User($lUserID){
   redirect('admin/accts/view/'.$lUserID);
}
 



