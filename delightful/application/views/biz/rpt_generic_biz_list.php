<?php

   global $genumDateFormat;

   $params = array('enumStyle' => 'enpRptC');
   $clsRpt = new generic_rpt($params);

   $lNumCols = 0;

   if ($showFields->bBizID        ) ++$lNumCols;
   if ($showFields->bRemBiz       ) ++$lNumCols;
   if ($showFields->bImportID     ) ++$lNumCols;
   if ($showFields->bName         ) ++$lNumCols;
   if ($showFields->bAddress      ) ++$lNumCols;
   if ($showFields->bPhoneEmail   ) ++$lNumCols;
   if ($showFields->bContacts     ) ++$lNumCols;
   if ($showFields->bContactNames ) ++$lNumCols;
   if ($showFields->bGiftSummary  ) $lNumCols += 2;
   if ($showFields->bSponsor    ){
      ++$lNumCols;
      $clsSpon = new msponsorship;
   }

   if ($lNumCols==0) {
      echoT('There are no business fields to report.');
   }else {
      ++$lNumCols;   // row counter
      echoT('<br>'.$clsRpt->openReport());
      echoT($clsRpt->writeTitle($strRptTitle, '', '', $lNumCols));
      echoT($clsRpt->openRow(true));
      echoT($clsRpt->writeLabel('&nbsp;',  10));
      if ($showFields->bBizID        ) echoT($clsRpt->writeLabel('businessID',  10));
      if ($showFields->bRemBiz       ) echoT($clsRpt->writeLabel('&nbsp;',      17));
      if ($showFields->bImportID     ) echoT($clsRpt->writeLabel('Import ID',   60));
      if ($showFields->bName         ) echoT($clsRpt->writeLabel('Name',        60));
      if ($showFields->bAddress      ) echoT($clsRpt->writeLabel('Address',     60));
      if ($showFields->bPhoneEmail   ) echoT($clsRpt->writeLabel('Phone/Email', 60));
      if ($showFields->bContacts     ) echoT($clsRpt->writeLabel('Contacts',    60));
      if ($showFields->bContactNames ) echoT($clsRpt->writeLabel('Contacts',    300));
      if ($showFields->bGiftSummary  ){
         echoT($clsRpt->writeLabel('Donations (hard)', 130));
         echoT($clsRpt->writeLabel('Donations (soft)', 130));
      }
      if ($showFields->bSponsor     ) echoT($clsRpt->writeLabel('Sponsorships'));

      echoT($clsRpt->closeRow());

      if ($lNumDisplayRows == 0){
         echoT($clsRpt->openRow(true));
         echoT($clsRpt->writeCell('<i>No businesses/organizations match your search criteria</i>', '', '', $lNumCols));
      }else {
         $idx = 1;
         foreach ($bizRecs as $biz){
            $lBID    = $biz->lKeyID;
            echoT($clsRpt->openRow(true));
            echoT('<td class="enpRpt" style="text-align: center;">'.$idx.'</td>'); 
            ++$idx;
            if ($showFields->bBizID){
               echoT($clsRpt->writeCell(
                                  strLinkView_BizRecord($lBID, 'View business record', true)
                                 .'&nbsp;'.str_pad($lBID, 5, '0', STR_PAD_LEFT)
                                 , 10)
                                 );
            }

               //--------------------
               // Remove link
               //--------------------
            if ($showFields->bRemBiz){
               echoT($clsRpt->writeCell(strLinkRem_Biz($lBID, 'Remove this business record', true, true, '',
                                        $showFields->deleteReturnPath, $showFields->lReturnPathID)));
            }
            
               //--------------------
               // Import ID
               //--------------------
            if ($showFields->bImportID){
               echoT($clsRpt->writeCell(htmlspecialchars($biz->strImportRecID).'&nbsp;'));
            }

               //--------------------
               // Biz Name
               //--------------------
            if ($showFields->bName){
               echoT($clsRpt->writeCell($biz->strSafeName, 160));
            }
            if ($showFields->bAddress){
               echoT($clsRpt->writeCell($biz->strAddress, 140));
            }
            if ($showFields->bPhoneEmail){
               echoT($clsRpt->writeCell(strPhoneCell($biz->strPhone, $biz->strCell, true, true)
                     .'<br>'.$biz->strEmailFormatted, 100));
            }

               //--------------------
               // Contacts
               //--------------------
            if ($showFields->bContacts){
               echoT($clsRpt->writeCell(
                    strLinkView_BizContacts($lBID, 'View/add business contacts', true, '').' '
                   .$biz->lNumContacts
                   ));
            }
            
               //--------------------
               // Contact Names
               //--------------------
            if ($showFields->bContactNames){
               if ($biz->lNumContacts==0){
                  $strOut = '<i>none</i>';
               }else {
                  $strOut = '<table width="100%">'."\n";
                  foreach ($biz->contacts as $con){
                     $lPID = $con->lPeopleID;
                     if ($con->strRelationship.'' == ''){
                        $strRel = '&nbsp;';
                     }else {
                        $strRel = '&nbsp;<i>('.htmlspecialchars($con->strRelationship).')</i>';
                     }
                     $strOut .= '
                        <tr>
                           <td style="width: 230pt;">'
                              .strLinkView_PeopleRecord($lPID, 'View people record', true).'&nbsp;&nbsp;'
                              .$con->strSafeNameLF.$strRel.'
                           </td>';
                     if ($con->bSoftCash){
                        $strOut .= '<td><img src="'.IMGLINK_DOLLAR.'" border="0" title="Soft cash relationship"></td>';
                     }else {
                        $strOut .= '<td>&nbsp;</td>';
                     }
                     $strOut .= '                          
                        </tr>'."\n";
                  }
                  $strOut .= '</table>'."\n";
               }
               echoT($clsRpt->writeCell($strOut, 300));
            }

               //--------------------
               // Gift Summary
               //--------------------
            if ($showFields->bGiftSummary){
               $strLinkAddNew = strLinkAdd_Gift($lBID, 'New&nbsp;gift', true).'&nbsp;'
                               .strLinkAdd_Gift($lBID, 'New&nbsp;gift', false);
               if ($biz->lNumACODonationGroups_hard + $biz->lNumACODonationGroups_soft > 0){
                  $strLinkGiftHistory = '<br>'.strLinkView_GiftsHistory($lBID, 'Gift history', true).'&nbsp;'
                                              .strLinkView_GiftsHistory($lBID, 'Gift history', false).'<br>'
                                              .$strLinkAddNew;
               }else {
                  $strLinkGiftHistory = $strLinkAddNew;
               }

               echoT($clsRpt->writeCell(
                             strBuildCumlativeTable($biz->lNumACODonationGroups_hard,
                                                    $biz->donationsViaACO_hard,
                                                    130).$strLinkGiftHistory));
               echoT($clsRpt->writeCell(
                             strBuildCumlativeTable($biz->lNumACODonationGroups_soft,
                                                    $biz->donationsViaACO_soft,
                                                    130)));
            }

               //--------------------
               // Sponsorship
               //--------------------
            if ($showFields->bSponsor){
               $strLinkAddNew = strLinkAdd_Sponsorship($lBID, 'Add new sponsorship', true).'&nbsp;'
                               .strLinkAdd_Sponsorship($lBID, 'Add new', false);
               $strSponSum = $clsSpon->strSponsorSumLiteViaPID($lBID);
               if ($strSponSum=='n/a'){
                  $strStyle = ' text-align: center; color: #b0b0b0; ';
               }else {
                  $strStyle = '';
               }
               echoT($clsRpt->writeCell($strSponSum.'<br>'.$strLinkAddNew, '', $strStyle));
            }
         }
         echoT($clsRpt->closeRow(''));
      }
      echoT($clsRpt->closeReport());



   }
