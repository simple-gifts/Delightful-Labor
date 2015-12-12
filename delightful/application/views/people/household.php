<?php
   if ($lNumInHousehold == 0){
      echoT('<i>The requested household was not found.</i>');
   }else {
      $params  = array('enumStyle' => 'enpRptC');
      $clsRpt  = new generic_rpt($params);
      $clsSpon = new msponsorship;

      echoT(strLinkAdd_PersonToHousehold($lHouseholdID, 'Add person to this household', true).'&nbsp;'
           .strLinkAdd_PersonToHousehold($lHouseholdID, 'Add person to this household', false).'<br>');

      echoT('<br>'.$clsRpt->openReport());
      echoT($clsRpt->writeTitle(htmlspecialchars($strHouseholdName), '', '', 7));

      echoT($clsRpt->openRow(true));
      echoT($clsRpt->writeLabel('&nbsp;',              ''));
      echoT($clsRpt->writeLabel('&nbsp;',              ''));
      echoT($clsRpt->writeLabel('Name',                160));
      echoT($clsRpt->writeLabel('Contact Info',        160));
      
      if (bAllowAccess('showGiftHistory')){
         echoT($clsRpt->writeLabel('Gift Totals (hard)',  160));
         echoT($clsRpt->writeLabel('Gift Totals (soft)',  160));
      }
      if (bAllowAccess('showSponsors')){
         echoT($clsRpt->writeLabel('Sponsorship',         160));
      }

      $bHOH = true;
      foreach($households as $contact){
         $strLinkGiftHistory = '';
         $lPID = $contact->PID;
         if ($bHOH){
            $strHOH    = '<b>';
            $strEndHOH = '</b> <span class="smaller">(head)</span>';
            $bHOH = false;
         }else {
            $strHOH    = strLinkSpecial_MakeHOH($lPID, $lHouseholdID, 'Make head-of-household', true).' '
                        .strLinkRem_PersonFromHousehold($lPID, $lHouseholdID,
                                                  'Remove from household', true, true, '');
            $strEndHOH = '';
         }

         $strAddr = strBuildAddress(
                  $contact->strAddr1, $contact->strAddr2,   $contact->strCity,
                  $contact->strState, $contact->strCountry, $contact->strZip,
                  true);

         if ($strAddr != '') $strAddr .= '<br><br>';
         $strEmail = $contact->strEmail;
         if ($strEmail != '') $strAddr .= strBuildEmailLink($strEmail, '', false, '').'<br>';
         $strPhone = $contact->strPhone;
         if ($strPhone != '') $strAddr .= $strPhone;

         if (bAllowAccess('showGiftHistory')){
            if ($contact->lNumACODonationGroups_hard + $contact->lNumACODonationGroups_soft > 0){
               $strLinkGiftHistory = '<br>'.strLinkView_GiftsHistory($lPID, 'Gift history', true).' '
                                           .strLinkView_GiftsHistory($lPID, 'Gift history', false);
            }
         }


         echoT($clsRpt->openRow(true));
         echoT($clsRpt->writeCell(
                          strLinkView_PeopleRecord($lPID, 'View people record', true)
                        .'&nbsp;'.str_pad($lPID, 5, '0', STR_PAD_LEFT), 60));

         echoT($clsRpt->writeCell($strHOH, 60));

         echoT($clsRpt->writeCell($contact->strSafeNameLF, 60));

         echoT($clsRpt->writeCell($strAddr, 60));

         if (bAllowAccess('showGiftHistory')){         
            echoT($clsRpt->writeCell(
                          strBuildCumlativeTable($contact->lNumACODonationGroups_hard,
                                                 $contact->donationsViaACO_hard,
                                                 140).$strLinkGiftHistory));
            echoT($clsRpt->writeCell(
                          strBuildCumlativeTable($contact->lNumACODonationGroups_soft,
                                                 $contact->donationsViaACO_soft,
                                                 140)));
         }

         if (bAllowAccess('showSponsors')){
            echoT($clsRpt->writeCell($clsSpon->strSponsorSumLiteViaPID($lPID)));
         }

         echoT($clsRpt->closeRow());
      }

      echoT($clsRpt->closeReport());
   }
