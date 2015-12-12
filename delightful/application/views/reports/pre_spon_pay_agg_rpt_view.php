<?php

   echoT($strRptTitle);

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '120pt';

   $strLabelWidth = 'width: 120pt; ';
   openBlock('Sponsorship Payment Totals', '');
   openPaymentTable($strLabelWidth);
   writePaymentTable('Sponsorship Payments',
                   $strLabelWidth,
                   $lNumSponPay, $curTotSpon);
   closePaymentTable();
   closeBlock();
   
   openBlock('Sponsorship Payment By Program', '');
   if ($lNumSponProg > 0){
      openPaymentTable($strLabelWidth);
      foreach ($sp as $sProg){
         writePaymentTable($sProg->strSafeName, $strLabelWidth,
                           $sProg->lNumSponPay, $sProg->curTotSpon);
      }
      closePaymentTable();
   }else {
      echoT('<i>No sponsorship programs are defined</i>');
   }
   closeBlock();

   openBlock('Sponsorship Payment By Location', '');
   openPaymentTable($strLabelWidth);
   foreach ($locs as $loc){
      writePaymentTable($loc->strLocation, $strLabelWidth,
                        $loc->lNumSponPay, $loc->curTotSpon);
   }
   closePaymentTable();
   closeBlock();



function openPaymentTable($strLabelWidth){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT('<table border="0">
             <tr>
                <td style="'.$strLabelWidth.'">&nbsp;</td>
                <td style="font-weight: bold; text-align: right; width: 40pt;">
                   #
                </td>
                <td style="font-weight: bold; text-align: right; width: 70pt;">
                   Amount
                </td>
             </tr>');
}


function writePaymentTable(
                    $strLabel,
                    $strLabelWidth,
                    $lNumPayments, $curTotPayments){
//---------------------------------------------------------------------
//  sponsorship payments
//---------------------------------------------------------------------
   echoT('
            <tr>
                <td style="'.$strLabelWidth.'">'.$strLabel.'</td>
                <td style="text-weight: bold; text-align: right;">'
                   .number_format($lNumPayments).'
                </td>
                <td style="text-weight: bold; text-align: right;">'
                   .number_format($curTotPayments, 2).'
                </td>
            </tr>');
}

function closePaymentTable(){
   echoT('</table><br>');
}



