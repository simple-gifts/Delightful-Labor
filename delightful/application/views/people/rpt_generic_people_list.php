<?php
   global $genumDateFormat, $gbAdmin;

   $params = array('enumStyle' => 'enpRptC');
   $clsRpt = new generic_rpt($params);

   $lNumCols = 0;

   if ($showFields->bPeopleID   ) ++$lNumCols;
   if ($showFields->bRemPeople  ) ++$lNumCols;
   if ($showFields->bImportID   ) ++$lNumCols;
   if ($showFields->bName       ) ++$lNumCols;
   if ($showFields->bAddress    ) ++$lNumCols;
   if ($showFields->bPhoneEmail ) ++$lNumCols;
   if ($showFields->bGiftSummary) $lNumCols += 2;
   if ($showFields->bSponsor    ){
      ++$lNumCols;
      $clsSpon = new msponsorship;
   }

   if ($lNumCols==0) {
      echoT('There are no people fields to report.');
   }else {
      echoT('<br>'.$clsRpt->openReport());
      echoT($clsRpt->writeTitle($strRptTitle, '', '', $lNumCols));
      echoT($clsRpt->openRow(true));
      if ($showFields->bPeopleID    ) echoT($clsRpt->writeLabel('PeopleID',   60));
      if ($showFields->bRemPeople   ) echoT($clsRpt->writeLabel('&nbsp;',     20));
      if ($showFields->bImportID    ) echoT($clsRpt->writeLabel('Import ID',  60));
      if ($showFields->bName        ) echoT($clsRpt->writeLabel('Name',      150));
      if ($showFields->bAddress     ) echoT($clsRpt->writeLabel('Address'));
      if ($showFields->bPhoneEmail  ) echoT($clsRpt->writeLabel('Phone/Email'));
      if ($showFields->bGiftSummary ){
         echoT($clsRpt->writeLabel('Donations (hard)'));
         echoT($clsRpt->writeLabel('Donations (soft)'));
      }
      if ($showFields->bSponsor     ) echoT($clsRpt->writeLabel('Sponsorships'));

      echoT($clsRpt->closeRow());

      if ($lNumPeople == 0){
         echoT($clsRpt->openRow(true));
         echoT($clsRpt->writeCell('<i>No '.$strPeopleType.' match your search criteria</i>', '', '', $lNumCols));
      }else {
         foreach ($people as $person){
            $lPID    = $person->lKeyID;
            echoT($clsRpt->openRow(true));
            
               //--------------------
               // People ID
               //--------------------            
            if ($showFields->bPeopleID){
               echoT($clsRpt->writeCell(
                                  strLinkView_PeopleRecord($lPID, 'View people record', true)
                                 .'&nbsp;'.str_pad($lPID, 5, '0', STR_PAD_LEFT)
                                 , 60)
                                 );
            }

               //--------------------
               // Remove Link
               //--------------------
            if ($showFields->bRemPeople){
               echoT($clsRpt->writeCell(strLinkRem_People($lPID, 'Remove this person\'s record', true, true, '',
                                        $showFields->deleteReturnPath, $showFields->lReturnPathID)));
            }
               //--------------------
               // Import ID
               //--------------------
            if ($showFields->bImportID){
               echoT($clsRpt->writeCell(htmlspecialchars($person->strImportRecID).'&nbsp;'));
            }
            
               //--------------------
               // Name
               //--------------------
            if ($showFields->bName){
               echoT($clsRpt->writeCell($person->strSafeNameLF
                                 .'<br>'
                                 .strLinkView_Household($person->lHouseholdID, $lPID, 'View household', true)
                                 .'<i>'.htmlspecialchars($person->strHouseholdName)
                                 .'</i><br>'.$person->strFlagImage, 
                                 240));
            }
            
               //--------------------
               // Address
               //--------------------
            if ($showFields->bAddress){
               echoT($clsRpt->writeCell($person->strAddress, 160));
            }
            
               //--------------------
               // Phone/Email
               //--------------------
            if ($showFields->bPhoneEmail){
               echoT($clsRpt->writeCell(strPhoneCell($person->strPhone, $person->strCell, true, true)
                     .'<br>'.$person->strEmailFormatted));
            }
            
               //--------------------
               // Gift Summary
               //--------------------
            if ($showFields->bGiftSummary){
               $strLinkAddNew = strLinkAdd_Gift  ($lPID, 'New&nbsp;gift',   true).'&nbsp;'
                               .strLinkAdd_Gift  ($lPID, 'New&nbsp;gift',   false).'<br>'
                               .strLinkAdd_Pledge($lPID, 'New&nbsp;pledge', true).'&nbsp;'
                               .strLinkAdd_Pledge($lPID, 'New&nbsp;pledge', false);
               if ($person->lNumACODonationGroups_hard + $person->lNumACODonationGroups_soft > 0){
                  $strLinkGiftHistory = '<br>'.strLinkView_GiftsHistory($lPID, 'History', true).'&nbsp;'
                                              .strLinkView_GiftsHistory($lPID, 'History', false).'<br>'
                                              .$strLinkAddNew;
               }else {
                  $strLinkGiftHistory = $strLinkAddNew;
               }
            
               echoT($clsRpt->writeCell(
                             strBuildCumlativeTable($person->lNumACODonationGroups_hard, 
                                                    $person->donationsViaACO_hard,                                                    
                                                    140).$strLinkGiftHistory));
               echoT($clsRpt->writeCell(
                             strBuildCumlativeTable($person->lNumACODonationGroups_soft, 
                                                    $person->donationsViaACO_soft, 
                                                    140)));
            }
            
               //--------------------
               // Sponsorship
               //--------------------
            if ($showFields->bSponsor){
               $strLinkAddNew = strLinkAdd_Sponsorship($lPID, 'Add new sponsorship', true).'&nbsp;'
                               .strLinkAdd_Sponsorship($lPID, 'Add new', false);
               $strSponSum = $clsSpon->strSponsorSumLiteViaPID($lPID);
               if ($strSponSum=='n/a'){
                  $strStyle = ' text-align: center; color: #b0b0b0;';
               }else {
                  $strStyle = '';
               }
               echoT($clsRpt->writeCell($strSponSum.'<br>'.$strLinkAddNew, '', $strStyle));
            }     
            echoT($clsRpt->closeRow());            
         }
      }
      echoT($clsRpt->closeReport());
   }





