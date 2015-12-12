<?php

   $lACOID = $cACO->lKeyID;
   
   $attributes = new stdClass;
   $attributes->lTableWidth      = '100%';
   $attributes->lUnderscoreWidth = 300;

   openBlock('Sponsorship Income: '.$lYear.' / '
            .$cACO->strName.' '.$cACO->strFlagImg.' '.$cACO->strCurrencySymbol, '', 
            $attributes);
   echoT('<br /><br />
      <table class="enpRptC">
         <tr>
            <td class="enpRptLabel">
               Month
            </td>
            <td class="enpRptLabel">
               Charges
            </td>
            <td class="enpRptLabel">
               Payments
            </td>
            <td class="enpRptLabel">
               Balance Due
            </td>
         </tr>');
   $curChargeTot = $curPayTot = $curBalTot = 0.0;
   for ($idx=1; $idx<=12; ++$idx){
      $curCharge = $income[$idx]->curCharge;
      $curPay    = $income[$idx]->curPay;
      $curBal    = $curCharge - $curPay;
      
      $curChargeTot += $curCharge;
      $curPayTot    += $curPay;
      $curBalTot    += $curBal;
      
      echoT('
            <tr class="makeStripes">
               <td class="enpRpt" style="text-align: left; width: 80pt;" >'
                  .strXlateMonth($idx).'
               </td>
               <td class="enpRpt" style="text-align: right; padding-right: 5pt;">'
                  .number_format($curCharge, 2).'&nbsp;'
                  .strLinkView_SponsorMonthlyCharge($idx, $lYear, $lACOID, 'View sponsor charges for this month', true).'
               </td>
               <td class="enpRpt" style="text-align: right; padding-right: 5pt;">'
                  .number_format($curPay, 2).'&nbsp;'
                  .strLinkView_SponsorMonthlyPayment($idx, $lYear, $lACOID, 'View sponsor payments for this month', true).'
               </td>
               <td class="enpRpt" style="text-align: right; padding-right: 5pt;">'
                  .number_format($curBal, 2).'
               </td>
            </tr>');
      
   }
   
   echoT('
         <tr class="makeStripe">
            <td class="enpRpt">
               <b>Total</b>
            </td>
            <td class="enpRpt" style="width: 60pt; text-align: right; padding-right: 5pt;"><b>'
               .number_format($curChargeTot, 2).'</b>
            </td>
            <td class="enpRpt" style="width: 60pt; text-align: right; padding-right: 5pt;"><b>'
               .number_format($curPayTot, 2).'</b>
            </td>
            <td class="enpRpt" style="width: 80pt; text-align: right; padding-right: 5pt;"><b>'
               .number_format($curBalTot, 2).'</b>
            </td>
         </tr>');     
   
   echoT('</table><br><br>');
   
   closeblock();
