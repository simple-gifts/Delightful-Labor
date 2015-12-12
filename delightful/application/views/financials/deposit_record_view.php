<?php
global $genumDateFormat;

if ($lNumDepositSummary > 0){
   echoT(strLinkSpecial_ExportDeposit($reportID, 'Export this report').'<br><br>');
}

   //-----------------------------------
   // deposit log summary
   //-----------------------------------
echoT(
        '<table class="enpView" style="width: 700px;">
            <tr>
               <td class="enpViewLabel" style="width: 75pt;">
                  Deposit Period:
               </td>
               <td class="enpView">'
                  .date($genumDateFormat, $deposit->dteStart).' - '.date($genumDateFormat, $deposit->dteEnd).'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel" style="vertical-align: bottom">
                  Deposit ID:
               </td>
               <td class="enpView">'
                  .str_pad($deposit->lKeyID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkEdit_Deposit($lDepositID, 'Edit deposit', true)
                  .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                  .strLinkRem_Deposit($deposit->lKeyID, 'Remove this deposit', true, true).'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel" nowrap>
                  Accounting Country:
               </td>
               <td class="enpView">'
                  .$deposit->strCountryName.'&nbsp;'.$deposit->strFlagImg.'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Bank:
               </td>
               <td class="enpView">'
                  .htmlspecialchars($deposit->strBank).'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Account:
               </td>
               <td class="enpView">'
                  .htmlspecialchars($deposit->strAccount).'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Notes:
               </td>
               <td class="enpView">'
                  .nl2br(htmlspecialchars($deposit->strNotes)).'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  # Entries:
               </td>
               <td class="enpView">'
                  .$lNumGifts.'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Total Deposit:
               </td>
               <td class="enpView">'
                  .$deposit->strCurrencySymbol.' '.number_format($curTot, 2).'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel">
                  Created By:
               </td>
               <td class="enpView">'
                  .htmlspecialchars($deposit->strCFName.' '.$deposit->strCLName).' on '
                  .date($genumDateFormat.' H:i:s', $deposit->dteOrigin).'
               </td>
            </tr>
         </table><br><br>');

if ($lNumDepositSummary == 0){
   echoT('<br><br><i>There are no items in this deposit report.</i><br><br>');
}else {
   echoT('
       <table class="enpRpt">
          <tr>
             <td class="enpRptTitle" colspan="3">
                Deposit Summary
             </td>
          </tr>
          </tr>
          <tr>
             <td class="enpRptLabel">
                Type
             </td>
             <td class="enpRptLabel">
                # of Entries
             </td>
             <td class="enpRptLabel">
                Total
             </td>
          </tr>');
   foreach ($depositSummary as $depSum){
      echoT('
          <tr class="makeStripe">
             <td class="enpRpt">'
                .$depSum->strSafePaymentType.'
             </td>
             <td class="enpRpt" style="text-align: center;">'
                .number_format($depSum->lNumEntries).'
             </td>
             <td class="enpRpt" style="text-align: right;">'
                .number_format($depSum->curTotal, 2).'
             </td>
          </tr>');
   }

   echoT('</table><br><br>');

      // now for the gift details
   foreach ($gifts as $giftViaPay){
      echoT('
         <table class="enpRpt">
            <tr>
               <td class="enpRptTitle" colspan="8">'
                  .$giftViaPay->safePayType.'
               </td>
            </tr>
            <tr>
               <td class="enpRptLabel">
                  gift ID
               </td>
               <td class="enpRptLabel">
                  &nbsp;
               </td>
               <td class="enpRptLabel">
                  Amount
               </td>
               <td class="enpRptLabel">
                  Date
               </td>
               <td class="enpRptLabel">
                  Donor
               </td>
            </tr>
            ');

      $curTot  = 0.0;
      foreach ($giftViaPay->gifts as $gift){
         $lGiftID = $gift->gi_lKeyID;

         echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align: center;">'
                  .str_pad($lGiftID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkView_GiftsRecord($lGiftID, 'View gift record', true).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .strLinkRem_DepositEntry($lGiftID, $lDepositID, 'Remove gift from deposit', true, true).'
               </td>
               <td class="enpRpt" style="text-align: right; width: 50pt;">'
                  .number_format($gift->gi_curAmnt, 2).'
               </td>
               <td class="enpRpt" style="width: 80pt; text-align: center;">'
                  .date($genumDateFormat, $gift->gi_dteDonation).'
               </td>
               <td class="enpRpt" style="width: 200pt;">'
                  .$gift->strSafeNameLF.'
               </td>
            </tr>
         ');
         $curTot += $gift->gi_curAmnt;
      }

      echoT('
            <tr>
               <td class="enpRptLabel" colspan="2">
                  Total
               </td>
               <td class="enpRpt" style="text-align: right;"><b>'
                  .number_format($curTot, 2).'
               </td>
               <td class="enpRpt" colspan="2">
                  &nbsp;
               </td>
            </tr>
         </table><br><br>');
   }
}

