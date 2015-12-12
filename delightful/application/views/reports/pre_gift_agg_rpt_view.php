<?php

   echoT($strRptTitle);

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '120pt';

   $strLabelWidth = 'width: 100pt; ';
   openBlock('Gift Totals', '');
   echoT('<div id="chart_01_div" style="text-align: left; width:500; height:300"></div>');
   
   writeGiftsTable(false, null,
                   $strLabelWidth,
                   $lNumGiftMonetary, $curTotGiftMonetary,
                   $lNumGIK, $curTotGIK,
                   $lNumSpon, $curTotSpon);
   closeBlock();

   openBlock('Accounts', '');
   echoT('<div id="chart_02_div" style="text-align: left; width:500; height:300"></div>');
   foreach ($accts as $acct){
      writeGiftsTable(true, $acct->strSafeAcct,
                      $strLabelWidth,
                      $acct->lNumGiftsM, $acct->curTotalM,
                      $acct->lNumGiftsG, $acct->curTotalG,
                      $acct->lNumGiftsS, $acct->curTotalS);
   }
   closeBlock();

   $lDivIDX = 3;
   $lAcctGrp = -999;
   foreach ($camps as $camp){
      $lAcctID = $camp->lAcctID;
      if ($lAcctID != $lAcctGrp){
         if ($lAcctGrp > 0){
            closeBlock();            
         }
         $lAcctGrp = $lAcctID;
         openBlock('Campaigns for account '.$camp->strSafeAcct, '');
         echoT('
           <div id="chart_'.str_pad($lDivIDX, 2, '0', STR_PAD_LEFT).'_div" style="text-align: left; width:500; height:300"></div>
         ');
         ++$lDivIDX;         
      }
   
      writeGiftsTable(true, $camp->strSafeCamp,
                      $strLabelWidth,
                      $camp->lNumGiftsM, $camp->curTotalM,
                      $camp->lNumGiftsG, $camp->curTotalG,
                      $camp->lNumGiftsS, $camp->curTotalS);
   }
   closeBlock();


function writeGiftsTable(
                    $bUseHeading, $strHeading,
                    $strLabelWidth,
                    $lNumGiftMonetary, $curTotGiftMonetary,
                    $lNumGIK, $curTotGIK,
                    $lNumSpon, $curTotSpon){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT('<table border="0">
             <tr>');
   if ($bUseHeading){
      echoT('<td colspan="5"><b>'.$strHeading.'</b></td></tr>
             <tr>
                <td style="width: 15pt; ">&nbsp;</td>'."\n");
   }
   echoT('
                <td style="'.$strLabelWidth.'">&nbsp;</td>
                <td style="font-weight: bold; text-align: right; width: 40pt;">
                   #
                </td>
                <td style="font-weight: bold; text-align: right; width: 70pt;">
                   Amount
                </td>
             </tr>');

      //----------------------------
      // monetary
      //----------------------------
   echoT('
            <tr>');

   if ($bUseHeading){
      echoT('<td style="width: 15pt;">&nbsp;</td>'."\n");
   }
   echoT('
                <td style="'.$strLabelWidth.'">Monetary Gifts</td>
                <td style="text-weight: bold; text-align: right;">'
                   .number_format($lNumGiftMonetary).'
                </td>
                <td style="text-weight: bold; text-align: right;">'
                   .number_format($curTotGiftMonetary, 2).'
                </td>
            </tr>');

      //----------------------------
      // sponsorship payments
      //----------------------------
   if ($lNumSpon > 0){
      echoT('
               <tr>');
      if ($bUseHeading){
         echoT('<td style="width: 5pt;">&nbsp;</td>'."\n");
      }
      echoT('
                   <td style="'.$strLabelWidth.'">Sponsorship Payments</td>
                   <td style="text-weight: bold; text-align: right;">'
                      .number_format($lNumSpon).'
                   </td>
                   <td style="text-weight: bold; text-align: right;">'
                      .number_format($curTotSpon, 2).'
                   </td>
               </tr>');
   }
      //----------------------------
      // in-kind
      //----------------------------
   echoT('
            <tr>');
   if ($bUseHeading){
      echoT('<td style="width: 5pt;">&nbsp;</td>'."\n");
   }
   echoT('
                <td style="'.$strLabelWidth.'">In-Kind Gifts</td>
                <td style="text-weight: bold; text-align: right;">'
                   .number_format($lNumGIK).'
                </td>
                <td style="text-weight: bold; text-align: right;">'
                   .number_format($curTotGIK, 2).'
                </td>
            </tr>');

      //----------------------------
      // total
      //----------------------------
   echoT('
            <tr style="background-color: #ddd;">');
   if ($bUseHeading){
      echoT('<td style="width: 5pt; background-color: #fff;">&nbsp;</td>'."\n");
   }
   echoT('
                <td style="'.$strLabelWidth.'"><b>Total</b></td>
                <td style="text-weight: bold; text-align: right;">'
                   .number_format($lNumGiftMonetary+$lNumGIK+$lNumSpon).'
                </td>
                <td style="text-weight: bold; text-align: right;">'
                   .number_format($curTotGiftMonetary+$curTotGIK+$curTotSpon, 2).'
                </td>
            </tr>');

   echoT('</table><br>');

}



