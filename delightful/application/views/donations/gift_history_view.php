<?php

   echoT(strLinkAdd_Gift($lFID, 'Add new gift', true).' '
        .strLinkAdd_Gift($lFID, 'Add new gift', false).'<br>');

      // Donations
   $attributes = new stdClass;
   $attributes->lTableWidth      = 1400;
   $attributes->lUnderscoreWidth = 500;
   $attributes->divID            = 'groupGiftDiv';
   $attributes->divImageID       = 'groupGiftDivImg';
   $attributes->bStartOpen       = true;
   $attributes->bAddTopBreak     = true;

   echoT(openBlock('Gifts', '', $attributes));

   if (!$bAnyGifts){
      echoT('<i>There are no donations recorded for '.$strName.'</i><br><br>');
   }else {
      $idx = 0;
      foreach ($giftHistory as $gh){

         if (count($gh) > 0){
            echoT('
               <table class="enpRpt">
                  <tr>
                     <td colspan="9" class="enpRptTitle">'
                        .$ghACO[$idx]->strFlag.' '.$ghACO[$idx]->strCountry.' Gift History for '.$strName.'
                     </td>
                  </tr>
                  <tr>
                     <td class="enpRptLabel" style="width: 15pt;">
                        &nbsp;
                     </td>
                     <td class="enpRptLabel" style="width: 50pt;">
                        Gift ID
                     </td>
                     <td class="enpRptLabel" style="width: 60pt;">
                        Amount
                     </td>
                     <td class="enpRptLabel" style="width: 60pt;">
                        Date
                     </td>
                     <td class="enpRptLabel" style="width: 140pt;">
                        Donor
                     </td>
                     <td class="enpRptLabel" style="width: 70pt;">
                        Account
                     </td>
                     <td class="enpRptLabel" style="width: 130pt;">
                        Campaign
                     </td>');
            if (bAllowAccess('showSponsors')){
               echoT('
                     <td class="enpRptLabel" style="width: 50pt;">
                        SponsorID
                     </td>');
            }
            echoT('
                     <td class="enpRptLabel" style="width: 30pt;">
                        Flags
                     </td>
                  </tr>');

            $curTotal = 0.0; $jidx = 0;
            foreach ($gh as $ghViaACO){
               ++$jidx;
               writeGiftHistoryRow($jidx, $lFID, $ghViaACO, $curTotal);
            }
            echoT('
               <tr class="makeStripe">
                  <td class="enpRpt" colspan="2"><b>
                     Total:
                  </td>
                  <td class="enpRpt" style="text-align: right;"><b>'
                     .number_format($curTotal, 2).'
                  </td>
                  <td class="enpRpt" colspan="6">
                     &nbsp;
                  </td>
               </tr>');
            echoT('</table></br>');
         }
         ++$idx;
      }
      echoT('<b>Flags:</b> <br>
                      &nbsp;&nbsp;<b>H</b> - honorarium<br>
                      &nbsp;&nbsp;<b>M</b> - memorial<br>
                      &nbsp;&nbsp;<b><i>italics</i></b> - soft donation<br>
                      ');
   }
   $attributes->bCloseDiv = true;
   echoT(closeBlock($attributes));

      // Pledges
   $attributes->divID            = 'groupPledgeDiv';
   $attributes->divImageID       = 'groupPledgeDivImg';
   echoT(openBlock('Pledges', 
                    strLinkView_PledgeViaFID($lFID, 'View Pledge Report', true).'&nbsp;'
                   .strLinkView_PledgeViaFID($lFID, 'View Pledge Report', false), $attributes));

   if ($lNumPledges == 0){
      echoT('<i>No pledges associated with this donor.</i>');
   }else {
      echoT('
         <table class="enpRpt">
            <tr>
               <td class="enpRptLabel">
                  &nbsp;
               </td>
               <td class="enpRptLabel">
                  Amount
               </td>
               <td class="enpRptLabel">
                  Account
               </td>
               <td class="enpRptLabel">
                  Campaign
               </td>
               <td class="enpRptLabel">
                  # Commitments
               </td>
               <td class="enpRptLabel">
                  Total Commit.
               </td>
            </tr>');

      foreach ($pledges as $pl){
         $lPledgeID = $pl->lKeyID;
         echoT('
            <tr>
               <td class="enpRpt">'
                  .str_pad($lPledgeID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkView_Pledge($lPledgeID, $strTitle, true).'
               </td>
               <td class="enpRpt" style="text-align: right;">'
                  .$pl->strFormattedAmnt.'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($pl->strAccount).'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($pl->strCampaign).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .number_format($pl->lNumCommit).'
               </td>
               <td class="enpRpt" style="text-align: right;">'
                  .$pl->strACOCurSymbol.'&nbsp;'
                  .number_format($pl->lNumCommit * $pl->curCommitment, 2).'&nbsp;'
                  .$pl->strFlagImg.'
               </td>
            </tr>');
      }

      echoT('</table>');
   }
   echoT(closeBlock());



function writeGiftHistoryRow($idx, $lPID, $clsGH, &$curTotal){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;

   $lGiftID    = $clsGH->gi_lKeyID;
   $lDonorID   = $clsGH->gi_lForeignID;
   $lSponsorID = $clsGH->gi_lSponsorID;
   $bSoft      = $lDonorID != $lPID;
   $bBiz       = $clsGH->pe_bBiz;

   if ($bSoft){
      if ($bBiz){
         $strLink  = strLinkView_BizRecord($lDonorID, 'View business record '.str_pad($lDonorID, 5, '0', STR_PAD_LEFT), true).' ';
      }else {
         $strLink  = strLinkView_PeopleRecord($lDonorID, 'View people record '.str_pad($lDonorID, 5, '0', STR_PAD_LEFT), true).' ';
      }
      $strFontStyle = 'font-style:italic; color: #666;';
   }else {
      $strLink      = '';
      $strFontStyle = '';
   }

   if ($bBiz){
      $strDonor = htmlspecialchars($clsGH->pe_strLName);
   }else {
      $strDonor = htmlspecialchars($clsGH->pe_strLName.', '.$clsGH->pe_strFName);
   }

   if ($clsGH->gi_bGIK){
      $strGH = '<br><small>in-kind: '.$clsGH->strGIK.'</small>';
   }else {
      $strGH = '';
   }

   if (is_null($lSponsorID)){
      $strSponID = '&nbsp;';
      $strGiftLink = strLinkView_GiftsRecord($lGiftID, 'View gift record', true);
   }else {
       if (bAllowAccess('showSponsors')){
          $strSponID = strLinkView_Sponsorship($lSponsorID, 'view sponsorship', true).' '
                      .str_pad($lSponsorID, 5, '0', STR_PAD_LEFT);
      }else {
         $strSponID = '&nbsp;';
      }
      $strGiftLink = strLinkView_SponsorPayment($lGiftID, 'View sponsorship payment record', true);
   }

   $strFlags = '';
   if ($clsGH->bHon) $strFlags .= ' H';
   if ($clsGH->bMem) $strFlags .= ' M';
   $strFlags = trim($strFlags);
   if ($strFlags=='') $strFlags = '&nbsp;';

   $curTotal += $clsGH->gi_curAmnt;

   echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="'.$strFontStyle.' text-align:center; width: 15pt;">'
               .$idx.'
            </td>
            <td class="enpRpt" style="'.$strFontStyle.' width: 50pt;">'
               .$strGiftLink.' '
               .str_pad($lGiftID, 5, '0', STR_PAD_LEFT).'
            </td>
            <td class="enpRpt" style="text-align: right;'.$strFontStyle.' width: 60pt;">'
               .number_format($clsGH->gi_curAmnt, 2).'
            </td>
            <td class="enpRpt" style="text-align: center;'.$strFontStyle.' width: 60pt;">'
               .date($genumDateFormat, $clsGH->gi_dteDonation).'
            </td>
            <td class="enpRpt" style="'.$strFontStyle.' width: 140pt;">'
               .$strLink.$strDonor.'
            </td>
            <td class="enpRpt" style="'.$strFontStyle.' width: 70pt;">'
               .htmlspecialchars($clsGH->ga_strAccount).'
            </td>
            <td class="enpRpt" style="'.$strFontStyle.' width: 130pt;">'
               .htmlspecialchars($clsGH->gc_strCampaign).$strGH.'
            </td>');

   if (bAllowAccess('showSponsors')){
      echoT('
            <td class="enpRpt" style="text-align: center;'.$strFontStyle.' width: 50pt;">'
               .$strSponID.'
            </td>');
   }
   echoT('
            <td class="enpRpt" style="text-align: center;'.$strFontStyle.' width: 30pt;">'
               .$strFlags.'
            </td>
         </tr>');
}



