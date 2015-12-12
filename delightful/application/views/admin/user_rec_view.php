<?php
global $gbVolLogin;

if ($bAsAdmin){
   echoT(strLinkAdd_User('Add new user', true).' '
        .strLinkAdd_User('Add new user', false) .'<br>');
}

$lLabelWidth = 140;
showUserRecBlock         ($clsRpt, $lUserID, $userRec, $lLabelWidth, $bAsAdmin);

if (!$gbVolLogin){
   if (!$userRec->us_bVolAccount){
      showGroupInfo($lUserID, $userRec->strSafeName, $lNumGroupsStaff, $groupListStaff,
                             $inGroupsStaff, $lCntGroupMembershipStaff,
                             CENUM_CONTEXT_STAFF, 'staffRecView', null);
   }
   if ($userRec->bStandardUser){
      showGroupInfo($lUserID, $userRec->strSafeName, $lNumGroups, $groupList,
                                $inGroups, $lCntGroupMembership,
                                CENUM_CONTEXT_USER, 'uRecView', CENUM_CONTEXT_USER);
   }
}

if ($bAsAdmin){
   showImageInfo            (CENUM_CONTEXT_STAFF, $lUserID, 'Staff Images',
                             $images, $lNumImages, $lNumImagesTot);
   showDocumentInfo         (CENUM_CONTEXT_STAFF, $lUserID, 'Staff Documents',
                             $docs, $lNumDocs, $lNumDocsTot);

      // personalized tables
   showCustomUserTableInfo($strPT, $lNumPTablesAvail);
}

if (!$gbVolLogin){
   showUserENPStats($clsRpt, $userRec, $lLabelWidth);
}

function showCustomUserTableInfo($strPT, $lNumPTablesAvail){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'userCus';
   $attributes->divImageID   = 'userDivImg';
   $attributes->bStartOpen   = false;
   openBlock('Personalized Tables <span style="font-size: 9pt;">('.$lNumPTablesAvail.')</span>', '', $attributes);

   echoT($strPT);

   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showUserRecBlock($clsRpt, $lUserID, $clsUsers, $lLabelWidth, $bAsAdmin){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gdteNow, $glUserID, $gbVolLogin;

   if ($lUserID==$glUserID || $clsUsers->us_bInactive){
      $strLinkDeactivate = '';
   }else {
      $strLinkDeactivate = '&nbsp;&nbsp;&nbsp;&nbsp;'
                           .strLinkSpecial_UserDeactivate($lUserID, 'Deactivate user', true);
   }

   if (!$gbVolLogin){
      if ($clsUsers->us_bInactive){
         $strLinkActivate = '&nbsp;&nbsp;&nbsp;&nbsp;'
                              .strLinkSpecial_UserActivate($lUserID, 'Activate user', true);
      }else {
         $strLinkActivate = '';
      }
   }

   if ($bAsAdmin){
      openBlock('User Account', strLinkEdit_User($lUserID, 'Edit user record', true)
               .$strLinkDeactivate.$strLinkActivate);
   }else {
      openBlock('Your Account', strLinkEdit_YourAcct($lUserID, 'Edit your account record', true));
   }
   echoT(
       $clsRpt->openReport());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('ID:', $lLabelWidth)   .$clsRpt->writeCell(str_pad($lUserID, 5, '0', STR_PAD_LEFT))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('User Name:', $lLabelWidth)
      .$clsRpt->writeCell(htmlspecialchars($clsUsers->us_strUserName))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Name:', $lLabelWidth)
      .$clsRpt->writeCell($clsUsers->strSafeName)
      .$clsRpt->closeRow  ());


   if (!$gbVolLogin){
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Status:', $lLabelWidth)
         .$clsRpt->writeCell(($clsUsers->us_bInactive ? '<b>INACTIVE</b>' : 'Active'))
         .$clsRpt->closeRow  ());
   }

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Address:', $lLabelWidth)
      .$clsRpt->writeCell($clsUsers->strAddress)
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Phone:', $lLabelWidth)
      .$clsRpt->writeCell(htmlspecialchars($clsUsers->us_strPhone))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Cell:', $lLabelWidth)
      .$clsRpt->writeCell(htmlspecialchars($clsUsers->us_strCell))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Email:', $lLabelWidth)
      .$clsRpt->writeCell(htmlspecialchars($clsUsers->us_strEmail))
      .$clsRpt->closeRow  ());

   $strAcctPerms = '';
   if ($clsUsers->bStandardUser && $bAsAdmin){
      if ($clsUsers->us_bUserDataEntryPeople     ) $strAcctPerms .= '<br>* Data Entry (people/businesses/volunteers)';
      if ($clsUsers->us_bUserDataEntryGifts      ) $strAcctPerms .= '<br>* Data Entry (donations)';
      if ($clsUsers->us_bUserEditPeople          ) $strAcctPerms .= '<br>* Edit (people/businesses/volunteers)';
      if ($clsUsers->us_bUserEditGifts           ) $strAcctPerms .= '<br>* Edit (donations)';
      if ($clsUsers->us_bUserViewPeople          ) $strAcctPerms .= '<br>* View (people/businesses/volunteers)';
      if ($clsUsers->us_bUserViewGiftHistory     ) $strAcctPerms .= '<br>* View (gift histories)';
      if ($clsUsers->us_bUserViewReports         ) $strAcctPerms .= '<br>* View (reports)';
      if ($clsUsers->us_bUserAllowExports        ) $strAcctPerms .= '<br>* Allow exports';
      if ($clsUsers->us_bUserAllowSponsorship    ) $strAcctPerms .= '<br>* Access to sponsorships';
      if ($clsUsers->us_bUserAllowSponFinancial  ) $strAcctPerms .= '<br>* Access to sponsorships financials';
      if ($clsUsers->us_bUserAllowClient         ) $strAcctPerms .= '<br>* Access to client records';
      if ($clsUsers->us_bUserAllowAuctions       ) $strAcctPerms .= '<br>* Allow access to silent auctions';
      if ($clsUsers->us_bUserAllowInventory      ) $strAcctPerms .= '<br>* Allow access to inventory management';
      if ($clsUsers->us_bUserAllowGrants         ) $strAcctPerms .= '<br>* Allow access to grants';
      if ($clsUsers->us_bUserVolManager          ) $strAcctPerms .= '<br>* Volunteer Manager';
   }

   if (!$gbVolLogin){
      $strAcctType = '';
      if ($clsUsers->us_bAdmin){
         $strAcctType = 'Admin';
      }elseif ($clsUsers->us_bVolAccount){
         $strAcctType = 'Volunteer';
      }else {
         $strAcctType = 'User';
      }
      if ($clsUsers->us_bDebugger){
         $strAcctType .= ' / Debugger';
      }
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Account Type:', $lLabelWidth)
         .$clsRpt->writeCell($strAcctType.$strAcctPerms)
         .$clsRpt->closeRow  ());

      if (!is_null($clsUsers->us_lPeopleID)){
         $lPID = $clsUsers->us_lPeopleID;
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('People ID:', $lLabelWidth)
            .$clsRpt->writeCell(str_pad($lPID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                    .strLinkView_PeopleRecord($lPID, 'View people record', true))
            .$clsRpt->closeRow  ());
      }


      if ($clsUsers->us_bVolAccount){
         $strAccess = '';
         if ($clsUsers->us_bVolEditContact)     $strAccess .= '* Edit contact information<br>';
         if ($clsUsers->us_bVolPassReset)       $strAccess .= '* Reset password<br>';
         if ($clsUsers->us_bVolViewGiftHistory) $strAccess .= '* View donation history<br>';
         if ($clsUsers->us_bVolEditJobSkills)   $strAccess .= '* Update job skills<br>';
         if ($clsUsers->us_bVolViewHrsHistory)  $strAccess .= '* View history of volunteer hours<br>';
         if ($clsUsers->us_bVolAddVolHours)     $strAccess .= '* Add/edit volunteer hours<br>';
         if ($clsUsers->us_bVolShiftSignup)     $strAccess .= '* Sign up for volunteer shifts<br>';
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Volunteer Access:', $lLabelWidth)
            .$clsRpt->writeCell($strAccess)
            .$clsRpt->closeRow  ());
      }
   }

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Date format:', $lLabelWidth)
      .$clsRpt->writeCell(date($clsUsers->us_enumDateFormat, $gdteNow))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Measurement:', $lLabelWidth)
      .$clsRpt->writeCell($clsUsers->us_enumMeasurePref)
      .$clsRpt->closeRow  ()

      .$clsRpt->closeReport());
   closeBlock();
}

function showUserENPStats($clsRpt, $clsUsers, $lLabelWidth){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'userENP';
   $attributes->divImageID   = 'userENPDivImg';
   openBlock('Record Information', '', $attributes);
   echoT(
      $clsRpt->showRecordStats($clsUsers->dteOrigin,
                            $clsUsers->strStaffCFName.' '.$clsUsers->strStaffCLName,
                            $clsUsers->dteLastUpdate,
                            $clsUsers->strStaffLFName.' '.$clsUsers->strStaffLLName,
                            $lLabelWidth));
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

?>