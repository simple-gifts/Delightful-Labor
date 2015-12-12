<?php
//---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
/*---------------------------------------------------------------------
      $this->load->helper('dl_util/reminder');
---------------------------------------------------------------------*/

function configRemRecViewViaType($enumRemType, $lFID, &$displayData){
   $lFIDs = array($lFID);

   $CI =& get_instance();

   $CI->load->model ('img_docs/mimage_doc',   'clsImgDoc');
   $CI->load->helper('img_docs/image_doc');
   $CI->load->helper('img_docs/link_img_docs');
   $CI->load->model('admin/mpermissions', 'perms');

   switch ($enumRemType){
      case CENUM_CONTEXT_USER:
         $clsUser = new muser_accts;
         $clsUser->loadSingleUserRecord($lFID);
         $displayData['contextSummary'] = $clsUser->userHTMLSummary(0);
         $displayData['pageTitle']      = strPageTitle('reminderRecordUser', $lFIDs);
         break;

      case CENUM_CONTEXT_PEOPLE:
         $clsPeople = new mpeople;
         $clsPeople->loadPeopleViaPIDs($lFID, false, false);
         $displayData['contextSummary'] = $clsPeople->peopleHTMLSummary(0);
         $displayData['pageTitle']      = strPageTitle('reminderRecordPeople', $lFIDs);
         break;

      case CENUM_CONTEXT_GIFT:
         $clsGifts = new mdonations;
         $clsGifts->loadGiftViaGID($lFID);
         $displayData['contextSummary'] = $clsGifts->giftHTMLSummary();
         $lPeopleBizID = $clsGifts->gifts[0]->gi_lForeignID;
         $lFIDs[1] = $lPeopleBizID;
         if ($clsGifts->gifts[0]->pe_bBiz){
            $displayData['pageTitle'] = strPageTitle('reminderRecordBizGift', $lFIDs);
         }else {
            $displayData['pageTitle'] = strPageTitle('reminderRecordPeopleGift', $lFIDs);
         }
         break;

      case CENUM_CONTEXT_SPONSORSHIP:
         $clsSpon = new msponsorship;
         $clsSpon->sponsorInfoViaID($lFID);
         $displayData['contextSummary'] = $clsSpon->sponsorshipHTMLSummary();
         $displayData['pageTitle']      = strPageTitle('reminderSponsor', $lFIDs);
         break;

      case CENUM_CONTEXT_CLIENT:
         $clsClients = new mclients;
         $clsClients->loadClientsViaClientID($lFID);
         $displayData['contextSummary'] = $clsClients->strClientHTMLSummary(0);
         $displayData['pageTitle']      = strPageTitle('reminderClient', $lFIDs);
         break;

      case CENUM_CONTEXT_BIZ:
         $clsBiz = new mbiz;
         $clsBiz->loadBizRecsViaBID($lFID);
         $displayData['contextSummary'] = $clsBiz->strBizHTMLSummary();
         $displayData['pageTitle']      = strPageTitle('reminderBiz', $lFIDs);
         break;

      case CENUM_CONTEXT_LOCATION:
      case CENUM_CONTEXT_VOLUNTEER:
      case CENUM_CONTEXT_GENERIC:
      default:
         screamForHelp($enumRemType.': Switch type not implemented</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
         break;
   }
}

