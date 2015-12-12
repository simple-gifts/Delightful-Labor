<?php
//---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012-2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
/*---------------------------------------------------------------------
      $this->load->helper('dl_util/permissions');    // in autoload

      if (!bAllowAccess('')) return('');
      if (bAllowAccess('')){

      $enumRequest:
         adminOnly
         devOnly

         allowExports

         showAuctions
         showClients
         showFinancials
         showGrants
         showGiftHistory
         showImagesDocs
         showPeople
         showReports
         showSponsors
         showSponsorFinancials
         showUTable
         inventoryMgr

         dataEntryPeopleBizVol
         dataEntryGifts

         editPeopleBizVol
         editGifts
         editImagesDocs
         editUTable
         editGroupMembership

         timeSheetAdmin

         viewPeopleBizVol
         viewUTable

         volEditContact
         volResetPassword
         volViewGiftHistory
         volJobSkills
         volViewHours
         volEditHours
         volShiftSignup
         volFeatures
         
         userVolunteerMgr

         forceFail
         notVolunteer
         management
---------------------------------------------------------------------*/

   function bAllowAccess($enumRequest, $enumType=''){
   /*---------------------------------------------------------------------

   --------------------------------------------------------------------- */
      global $glUserID, $gbDev, $gbAdmin, $gbStandardUser, $gUserPerms,
         $gVolPerms, $gbVolLogin, $gbUserInMgrGroup, $gbDev;

         // special case - management trumps admin
      if ($enumRequest=='management'){
         if ($gbVolLogin){
            return(false);
         }else {
            return($gbUserInMgrGroup);
         }
      }

      if ($enumRequest=='devOnly') return($gbDev);
      if ($gbAdmin) return(true);
      if ($enumRequest=='notVolunteer'){
         return(!$gbVolLogin);
      }

      $bPeopleBizVol = $enumType==CENUM_CONTEXT_BIZ         || $enumType==CENUM_CONTEXT_PEOPLE      || $enumType==CENUM_CONTEXT_VOLUNTEER;
      $bAuction      = $enumType==CENUM_CONTEXT_AUCTION     || $enumType==CENUM_CONTEXT_AUCTIONITEM || $enumType==CENUM_CONTEXT_AUCTIONPACKAGE;
      $bClient       = $enumType==CENUM_CONTEXT_CLIENT      || $enumType==CENUM_CONTEXT_CPROGRAM    ||
                       $enumType==CENUM_CONTEXT_CPROGENROLL || $enumType==CENUM_CONTEXT_CPROGATTEND;
      $bGrants       = $enumType==CENUM_CONTEXT_GRANTS      || $enumType==CENUM_CONTEXT_GRANTPROVIDER;
      $bAllow = false;
      switch ($enumRequest){
         case 'allowExports':
            $bAllow = $gbStandardUser && $gUserPerms->bUserAllowExports;
            break;

         case 'dataEntryGifts':
            $bAllow =
               ($gbStandardUser && ($gUserPerms->bUserDataEntryGifts ||
                                    $gUserPerms->bUserEditGifts
                                    ));
            break;

         case 'editGifts':
            $bAllow = $gbStandardUser && $gUserPerms->bUserEditGifts;
            break;

         case 'editUTable':
         case 'editGroupMembership':
            $bAllow = ($gbStandardUser && (
                             ($bPeopleBizVol                       && ($gUserPerms->bUserEditPeople)) ||
                             ($enumType==CENUM_CONTEXT_SPONSORSHIP && ($gUserPerms->bUserAllowSponsorship || $gUserPerms->bUserAllowSponFinancial)) ||
                             ($bClient                             && ($gUserPerms->bUserAllowClient ))   ||
                             ($enumType==CENUM_CONTEXT_LOCATION    && ($gUserPerms->bUserAllowClient ))   ||
                             ($enumType==CENUM_CONTEXT_GIFT        && ($gUserPerms->bUserDataEntryGifts ))||
                             ($bGrants                             && ($gUserPerms->bUserAllowGrants    ))||
                             ($bAuction                            && ($gUserPerms->bUserAllowAuctions ))
                            )
                          );
            break;

         case 'showUTable':
            $bAllow = ($gbStandardUser && (
                             ($bPeopleBizVol                       && ($gUserPerms->bUserViewPeople))     ||
                             ($bClient                             && ($gUserPerms->bUserAllowClient ))   ||
                             ($enumType==CENUM_CONTEXT_SPONSORSHIP && ($gUserPerms->bUserAllowSponsorship || $gUserPerms->bUserAllowSponFinancial)) ||
                             ($enumType==CENUM_CONTEXT_CLIENT      && ($gUserPerms->bUserAllowClient ))   ||
                             ($enumType==CENUM_CONTEXT_LOCATION    && ($gUserPerms->bUserAllowClient ))   ||
                             ($enumType==CENUM_CONTEXT_GIFT        && ($gUserPerms->bUserViewGiftHistory )) ||
                             ($bGrants                             && ($gUserPerms->bUserAllowGrants     )) ||
                             ($bAuction                            && ($gUserPerms->bUserAllowAuctions ))
                            )
                          );
            break;

         case 'viewUTable':
            $bAllow = ($gbStandardUser && (
                             ($bPeopleBizVol                       && ($gUserPerms->bUserViewPeople       || $gUserPerms->bUserDataEntryPeople  || $gUserPerms->bUserEditPeople)) ||
                             ($enumType==CENUM_CONTEXT_SPONSORSHIP && ($gUserPerms->bUserAllowSponsorship || $gUserPerms->bUserAllowSponFinancial)) ||
                             ($bClient                             && ($gUserPerms->bUserAllowClient ))   ||
                             ($enumType==CENUM_CONTEXT_CLIENT      && ($gUserPerms->bUserAllowClient ))   ||
                             ($enumType==CENUM_CONTEXT_LOCATION    && ($gUserPerms->bUserAllowClient ))   ||
                             ($enumType==CENUM_CONTEXT_GIFT        && ($gUserPerms->bUserDataEntryGifts   || $gUserPerms->bUserViewGiftHistory)) ||
                             ($bGrants                             && ($gUserPerms->bUserAllowGrants ))   ||
                             ($bAuction                            && ($gUserPerms->bUserAllowAuctions ))
                            )
                          );
            break;

         case 'editImagesDocs':
            $bAllow = ($gbStandardUser && (
                             ($bPeopleBizVol                       && ($gUserPerms->bUserDataEntryPeople  || $gUserPerms->bUserEditPeople)) ||
                             ($enumType==CENUM_CONTEXT_SPONSORSHIP && ($gUserPerms->bUserAllowSponsorship || $gUserPerms->bUserAllowSponFinancial)) ||
                             ($bClient                             && ($gUserPerms->bUserAllowClient ))   ||
                             ($enumType==CENUM_CONTEXT_CLIENT      && ($gUserPerms->bUserAllowClient ))   ||
                             ($enumType==CENUM_CONTEXT_LOCATION    && ($gUserPerms->bUserAllowClient ))   ||
                             ($enumType==CENUM_CONTEXT_GIFT        && ($gUserPerms->bUserViewGiftHistory  || $gUserPerms->bUserDataEntryGifts )) ||
                             ($bGrants                             && ($gUserPerms->bUserAllowGrants ))   ||
                             ($bAuction                            && ($gUserPerms->bUserAllowAuctions ))
                            )
                          );
            break;

         case 'showImagesDocs':
            $bAllow = ($gbStandardUser && (
                             ($bPeopleBizVol                       && ($gUserPerms->bUserViewPeople))      ||
                             ($enumType==CENUM_CONTEXT_SPONSORSHIP && ($gUserPerms->bUserAllowSponsorship  || $gUserPerms->bUserAllowSponFinancial)) ||
                             ($bClient                             && ($gUserPerms->bUserAllowClient ))    ||
                             ($enumType==CENUM_CONTEXT_CLIENT      && ($gUserPerms->bUserAllowClient ))    ||
                             ($enumType==CENUM_CONTEXT_LOCATION    && ($gUserPerms->bUserAllowClient ))    ||
                             ($enumType==CENUM_CONTEXT_GIFT        && ($gUserPerms->bUserViewGiftHistory)) ||
                             ($bGrants                             && ($gUserPerms->bUserAllowGrants ))    ||
                             ($bAuction                            && ($gUserPerms->bUserAllowAuctions ))
                            )
                          );
            break;

         case 'showAuctions':
            $bAllow = ($gbStandardUser && $gUserPerms->bUserAllowAuctions);
            break;

         case 'showReports':
            $bAllow =
               ($gbStandardUser && ($gUserPerms->bUserViewReports        ||
                                    $gUserPerms->bUserAllowExports       ||
                                    $gUserPerms->bUserAllowSponsorship   ||
                                    $gUserPerms->bUserAllowSponFinancial ||
                                    $gUserPerms->bUserAllowClient
                                    ));
            break;

         case 'showPeople':
            $bAllow =
               ($gbStandardUser && ($gUserPerms->bUserViewPeople ||
                                    $gUserPerms->bUserDataEntryPeople ||
                                    $gUserPerms->bUserEditPeople));
            break;

         case 'showFinancials':
            $bAllow =
               ($gbStandardUser && ($gUserPerms->bUserDataEntryGifts || $gUserPerms->bUserViewGiftHistory || $gUserPerms->bUserEditGifts));
            break;

         case 'showSponsorFinancials':
            $bAllow =
               ($gbStandardUser && $gUserPerms->bUserAllowSponFinancial);
         break;

         case 'showGiftHistory':
            $bAllow =
               ($gbStandardUser && $gUserPerms->bUserViewGiftHistory);
         break;

         case 'showGrants':
            $bAllow = ($gbStandardUser && $gUserPerms->bUserAllowGrants);
            break;
            
         case 'inventoryMgr':
            $bAllow = ($gbStandardUser && $gUserPerms->bUserAllowInventory);
            break;
         
         case 'showClients':
            $bAllow =
               ($gbStandardUser && $gUserPerms->bUserAllowClient);
            break;

         case 'showSponsors':
            $bAllow =
               ($gbStandardUser && ($gUserPerms->bUserAllowSponsorship || $gUserPerms->bUserAllowSponFinancial));
            break;

         case 'userVolunteerMgr':
            $bAllow =
               ($gbStandardUser && $gUserPerms->bUserVolManager);
            break;

         case 'dataEntryPeopleBizVol':
            $bAllow =
               ($gbStandardUser && ($gUserPerms->bUserDataEntryPeople || $gUserPerms->bUserEditPeople));
            break;

         case 'viewPeopleBizVol':
            $bAllow =
               ($gbStandardUser && ($gUserPerms->bUserDataEntryPeople ||
                                                $gUserPerms->bUserEditPeople      ||
                                                $gUserPerms->bUserViewPeople));
            break;

         case 'editPeopleBizVol':
            $bAllow = ($gbStandardUser && $gUserPerms->bUserEditPeople);
            break;

         case 'volJobSkills':
            if ($gbVolLogin){
               $bAllow = $gVolPerms->bVolEditJobSkills;
            }else {
               $bAllow = false;
            }
            break;
            
         case 'timeSheetAdmin':
            $bAllow = ($gbStandardUser && $gUserPerms->bTimeSheetAdmin);
            break;

         case 'volViewHours':
            if ($gbVolLogin){
               $bAllow = $gVolPerms->bVolViewHrsHistory;
            }else {
               $bAllow = false;
            }
            break;

         case 'volEditHours':
            if ($gbVolLogin){
               $bAllow = $gVolPerms->bVolAddVolHours;
            }else {
               $bAllow = false;
            }
            break;

         case 'volShiftSignup':
            if ($gbVolLogin){
               $bAllow = $gVolPerms->bVolShiftSignup;
            }else {
               $bAllow = false;
            }
            break;

         case 'volEditContact':
            if ($gbVolLogin){
               $bAllow = $gVolPerms->bVolEditContact;
            }else {
               $bAllow = false;
            }
            break;

         case 'volResetPassword':
            if ($gbVolLogin){
               $bAllow = $gVolPerms->bVolPassReset;
            }else {
               $bAllow = false;
            }
            break;

         case 'volViewGiftHistory':
            if ($gbVolLogin){
               $bAllow = $gVolPerms->bVolViewGiftHistory;
            }else {
               $bAllow = false;
            }
            break;

         case 'volFeatures':
            if ($gbVolLogin){
               $bAllow = $gVolPerms->bVolViewHrsHistory || $gVolPerms->bVolAddVolHours || $gVolPerms->bVolShiftSignup;
            }else {
               $bAllow = false;
            }
            break;

         case 'adminOnly':
         case 'forceFail':
            $bAllow = false;
            break;


         default:
            screamForHelp($enumRequest.': invalid access request<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($bAllow);
   }

   function bTestForURLHack($enumRequest, $enumType=''){
   /*---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      if (!bTestForURLHack('devOnly')) return;
      if (!bTestForURLHack('editPeopleBizVol')) return;
      if (!bTestForURLHack('showGrants')) return;
      if (!bTestForURLHack('showFinancials')) return;
      if (!bTestForURLHack('dataEntryPeopleBizVol')) return;
      if (!bTestForURLHack('notVolunteer')) return;
      if (!bTestForURLHack('forceFail')) return;
   --------------------------------------------------------------------- */
      global $gbDebug;
      if (!bAllowAccess($enumRequest, $enumType)){
         badBoyRedirect('Your account settings do not allow you to access this feature.');
         return(false);
      }else {
         return(true);
      }
   }

   function badBoyRedirect($strErrMsg){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
            //--------------------------
            // breadcrumbs
            //--------------------------
         $CI =& get_instance();
         $displayData = array();
         $displayData['strErr']    = $strErrMsg;
         $displayData['pageTitle'] = 'Permissions Error';

         $displayData['title']          = CS_PROGNAME.' | Error';
         $displayData['nav']            = $CI->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'admin/permissions_error_view';
         $CI->load->vars($displayData);
         $CI->load->view('template');
   }

   function bInUserGroup($strUserGroup){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gUserPerms, $gbAdmin, $gbStandardUser;
      
      if ($gbAdmin) return(true);
      if (!$gbStandardUser) return(false);
      if ($gUserPerms->lNumUserGroups == 0) return(false);
      return(in_array($strUserGroup, $gUserPerms->strUGroups));
   }
   
   function bPermitViaContext($enumContext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------  
      switch ($enumContext){
         case CENUM_CONTEXT_AUCTION:
         case CENUM_CONTEXT_AUCTIONITEM:
         case CENUM_CONTEXT_AUCTIONPACKAGE:
            $bPermit = bAllowAccess('showAuctions');  break;

         case CENUM_CONTEXT_BIZ:
         case CENUM_CONTEXT_PEOPLE:
         case CENUM_CONTEXT_VOLUNTEER:
            $bPermit = bAllowAccess('viewPeopleBizVol');  break;
            
         case CENUM_CONTEXT_GRANTS:
         case CENUM_CONTEXT_GRANTPROVIDER:
            $bPermit = bAllowAccess('showGrants');  break;

         case CENUM_CONTEXT_CLIENT:
         case CENUM_CONTEXT_LOCATION:
            $bPermit = bAllowAccess('showClients');  break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $bPermit = bAllowAccess('showSponsors');  break;

         case CENUM_CONTEXT_ORGANIZATION:
         case CENUM_CONTEXT_STAFF:
            $bPermit = bAllowAccess('adminOnly');  break;
            
         case CENUM_CONTEXT_INVITEM:
            $bPermit = bAllowAccess('inventoryMgr');  break;
            
         default:
            screamForHelp($enumContext.': invalid permissions context<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($bPermit);   
   }
   
   

