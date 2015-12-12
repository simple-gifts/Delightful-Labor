<?php


   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '120pt';

   openBlock('Sponsor Past Due Report', '');

   $clsRpt->openReport();

   echoT($clsRpt->openRow(false));
   echoT($clsRpt->writeLabel('Accounting Country:'));
   echoT($clsRpt->writeCell($cACO->strName.' '.$cACO->strFlagImg.' '.$cACO->strCurrencySymbol, 
                            '', $strStyleExtra='text-align: left;'));
   echoT($clsRpt->closeRow());

   echoT($clsRpt->openRow(false));
   echoT($clsRpt->writeLabel('Months Past Due:'));
   echoT($clsRpt->writeCell('At least '.$lMonthsPastDue.' month'.(($lMonthsPastDue==1) ? '':'s'), '', $strStyleExtra='text-align: left;'));
   echoT($clsRpt->closeRow());

   echoT($clsRpt->openRow(false));
   echoT($clsRpt->writeLabel('Include Inactive?:'));
   echoT($clsRpt->writeCell(($bIncludeInactive ? 'Yes' : 'No'), $strStyleExtra='text-align: left;'));
   echoT($clsRpt->closeRow());

   $clsRpt->closeReport();
   closeBlock();

   if ($lNumPastDue == 0){
      echoT('<br /><br /><i>There are no sponsors that meet your search criteria.</i><br>');
   }else {
      echoT('<br /><br />
         <table class="enpRptC">
            <tr>
               <td class="enpRptLabel">
                  Sponsor ID
               </td>
               <td class="enpRptLabel">
                  Sponsor
               </td>
               <td class="enpRptLabel">
                  Client
               </td>
               <td class="enpRptLabel">
                  Monthly Commitment
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
               <td class="enpRptLabel">
                  Months Past Due
               </td>
            </tr>');
      $curCommitTot = $curChargeTot = $curPayTot = $curBalTot = 0.0;
      foreach ($pastDue as $pd){
         $lSponID      = $pd->lSponID;
         
         $curCommit    = $pd->curCommit;
         $curCharges   = $pd->curCharges;
         $curPayments  = $pd->curPayments;
         $curBal       = $curCharges - $curPayments;
         $lMonthsPast  = (integer)($curBal/$curCommit);
         
         $curCommitTot += $curCommit;
         $curChargeTot += $curCharges;
         $curPayTot    += $curPayments;
         $curBalTot    += $curBal;
         
         $bInactive    = $pd->bInactive;
         $strInactive  = $bInactive ? 'color: #777;' : '';
         echoT('
               <tr class="makeStripe">
                  <td class="enpRpt" style="width: 50pt; text-align: center;'.$strInactive.'">'
                     .str_pad($lSponID, 5, '0', STR_PAD_LEFT).' '
                     .strLinkView_Sponsorship($lSponID, 'View sponsorship record', true).'
                  </td>
                  <td class="enpRpt" style="width: 150pt;'.$strInactive.'">'
                     .$pd->strSponSafeNameLF.($bInactive ? ' (inactive)' : '').'
                  </td>
                  <td class="enpRpt" style="width: 100pt;'.$strInactive.'">'
                     .$pd->strClientSafeNameLF.'
                  </td>
                  <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 5pt;'.$strInactive.'">'
                     .number_format($curCommit, 2).'
                  </td>
                  <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 5pt;'.$strInactive.'">'
                     .number_format($curCharges, 2).'
                  </td>
                  <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 5pt;'.$strInactive.'">'
                     .number_format($curPayments, 2).'
                  </td>
                  <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 5pt;'.$strInactive.'"><b>'
                     .number_format($curBal, 2).'</b>
                  </td>
                  <td class="enpRpt" style="width: 30pt; text-align: center;'.$strInactive.'"><b>'
                     .number_format($lMonthsPast).'</b>
                  </td>
               </tr>');
      }
      
      echoT('
            <tr class="makeStripe">
               <td class="enpRpt">
                  <b>Total</b>
               </td>
               <td class="enpRpt" colspan="2">
                  &nbsp;
               </td>
               <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 5pt;"><b>'
                  .number_format($curCommitTot, 2).'</b>
               </td>
               <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 5pt;"><b>'
                  .number_format($curChargeTot, 2).'</b>
               </td>
               <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 5pt;"><b>'
                  .number_format($curPayTot, 2).'</b>
               </td>
               <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 5pt;"><b>'
                  .number_format($curBalTot, 2).'</b>
               </td>
               <td class="enpRpt" style="width: 30pt; text-align: center;">
                  &nbsp;
               </td>
            </tr>');     
      
      echoT('</table><br><br>');
   }

