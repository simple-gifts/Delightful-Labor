<?php

global $genumDateFormat;

if ($lNumSponPay == 0){
   echoT('<br><i>There are no sponsor payment records are associated with this import!</i></b><br><br>');
   return;
}

echoT('<br>
   <table class="enpRptC">
      <tr>
         <td class="enpRptLabel">
            Payment ID
         </td>
         <td class="enpRptLabel">
            Sponsor ID
         </td>
         <td class="enpRptLabel">
            Donor ID
         </td>
         <td class="enpRptLabel">
            Import ID
         </td>         
         <td class="enpRptLabel">
            Payer
         </td>
         <td class="enpRptLabel">
            Amount
         </td>
         <td class="enpRptLabel">
            Date of Payment
         </td>
         <td class="enpRptLabel">
            Sponsor
         </td>
         <td class="enpRptLabel">
            Client
         </td>
      </tr>');
/*      
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<pre>'); print_r($paymentRecs); echo('</pre></font><br>');      
*/      
   foreach ($paymentRecs as $payrec){
      $lPayID      = $payrec->lKeyID;
      $lSponID     = $payrec->lSponsorshipID;
      $lClientID   = $payrec->lClientID;
      $lDonorID    = $payrec->lDonorID;
      $lSponFID    = $payrec->lSponPeopleID;
      $bBizDonor   = $payrec->bDonorBiz;
      $bBizSponsor = $payrec->bSponBiz;
      
      if ($bBizDonor){
         $strLinkDonor = strLinkView_BizRecord($lDonorID, 'View business record of payer', true);
      }else {
         $strLinkDonor = strLinkView_PeopleRecord($lDonorID, 'View people record of payer', true);
      }
      
      if ($bBizSponsor){
         $strLinkSpon = strLinkView_BizRecord($lSponFID, 'View business record of sponsor', true);
      }else {
         $strLinkSpon = strLinkView_PeopleRecord($lSponFID, 'View people record of sponsor', true);
      }
      
      if (is_null($lClientID)){
         $strClientLink = '&nbsp;';
      }else {
         $strClientLink = strLinkView_ClientRecord($lClientID, 'View client record', true).'&nbsp;';
      }
      
      echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align: center;">'
                  .strLinkView_SponsorPayment($lPayID, 'View payment record', true).'&nbsp;'
                  .str_pad($lPayID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .strLinkView_Sponsorship($lSponID, 'View sponsorship record', true).'&nbsp;'
                  .str_pad($lSponID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .$strLinkDonor.'&nbsp;'
                  .str_pad($lDonorID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($payrec->strImportID).'
               </td>               
               <td class="enpRpt">'
                  .$payrec->strDonorSafeNameLF.'
               </td>
               <td class="enpRpt" style="text-align: right;">'
                  .number_format($payrec->curPaymentAmnt, 2).'&nbsp;'.$payrec->strFlagImage.'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .date($genumDateFormat, $payrec->dtePayment).'
               </td>
               <td class="enpRpt">'
                  .$strLinkSpon.'&nbsp;'.htmlspecialchars($payrec->strSponSafeNameLF).'
               </td>
               <td class="enpRpt">'
                  .$strClientLink.htmlspecialchars($payrec->strClientSafeNameFL).'
               </td>
            </tr>');      
   }
   
   echoT('
      </table>');






