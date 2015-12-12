<?php
global $genumDateFormat;

if ($lNumGifts == 0){
   echoT('<br><i>There are no gift records are associated with this import!</i></b><br><br>');
   return;
}

echoT('
   <table class="enpRptC">
      <tr>
         <td class="enpRptLabel">
            Gift ID
         </td>
         <td class="enpRptLabel">
            Donor ID
         </td>
         <td class="enpRptLabel">
            Import ID
         </td>
         <td class="enpRptLabel">
            Donor
         </td>
         <td class="enpRptLabel">
            Amount
         </td>
         <td class="enpRptLabel">
            Date of Donation
         </td>
         <td class="enpRptLabel">
            Account
         </td>
         <td class="enpRptLabel">
            Campaign
         </td>
      </tr>');
      
      
      
   foreach ($gifts as $gift){
      $lGiftID  = $gift->gi_lKeyID;
      $lDonorID = $gift->gi_lForeignID;
      $bBizGift = $gift->pe_bBiz;
      if ($bBizGift){
         $strLinkDonor = strLinkView_BizRecord($lDonorID, 'View business record', true);
      }else {
         $strLinkDonor = strLinkView_PeopleRecord($lDonorID, 'View people record', true);
      }
      echoT('
            <tr class="makeStripe">
               <td class="enpRpt">'
                  .strLinkView_GiftsRecord($lGiftID, 'View gift record', true).'&nbsp;'
                  .str_pad($lGiftID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td class="enpRpt">'
                  .$strLinkDonor.'&nbsp;'
                  .str_pad($lDonorID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($gift->strImportID).'
               </td>
               <td class="enpRpt">'
                  .$gift->strSafeNameLF.'
               </td>
               <td class="enpRpt" style="text-align: right;">'
                  .$gift->strFormattedAmnt.'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .date($genumDateFormat, $gift->gi_dteDonation).'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($gift->ga_strAccount).'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($gift->gc_strCampaign).'
               </td>
            </tr>');      
   }
   
   echoT('
      </table>');



