<?php

echoT(strLinkAdd_Pledge($lFID, 'Add new pledge', true).'&nbsp;'
     .strLinkAdd_Pledge($lFID, 'Add new pledge', false).'<br><br>');

if ($lNumPledges == 0){
   echoT('<i>There are no pledges for <b>'.$strSafeName.'</b>.</i><br><br>');
   return;
}

   echoT('
      <table class="enpRptC"  >
         <tr>
            <td class="enpRptTitle" colspan="7">
               Pledges for '.$strSafeName.'
            </td>
         </tr>');
         
   echoT('
      <tr>
         <td class="enpRptLabel">
            pledgeID
         </td>
         <td class="enpRptLabel">
            &nbsp;
         </td>
         <td class="enpRptLabel">
            Date
         </td>
         <td class="enpRptLabel">
            Commitment
         </td>
         <td class="enpRptLabel">
            Total<br>Commitment
         </td>
         <td class="enpRptLabel">
            Frequency
         </td>
         <td class="enpRptLabel">
            Fulfillment
         </td>
      </tr>');
      
   foreach ($pledges as $pledge){
      $lPledgeID = $pledge->lKeyID;
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="text-align: center; width: 40pt" nowrap>'
               .str_pad($lPledgeID, 5, '0', STR_PAD_LEFT).'&nbsp;'
               .strLinkView_Pledge($lPledgeID, 'View pledge', true).'
            </td>
            <td class="enpRpt" style="text-align: center; width: 20pt" nowrap>'
               .strLinkEdit_Pledge($lPledgeID, 'Edit pledge', true).'
            </td>
            <td class="enpRpt">
               Date
            </td>
            <td class="enpRpt" style="text-align: right; width: 100pt;">'
               .$pledge->lNumCommit.' x '.$pledge->strFormattedAmnt.'
            </td>
            <td class="enpRpt" style="text-align: right; width: 80pt;">'
               .$pledge->strACOCurSymbol.'&nbsp;'
               .number_format(($pledge->lNumCommit * $pledge->curCommitment), 2).'
            </td>
            <td class="enpRpt" style="text-align: center; width: 80pt;">'
               .$pledge->enumFreq.'
            </td>
            <td class="enpRpt" style="text-align: right; width: 80pt;">'
               .$pledge->strACOCurSymbol.'&nbsp;'
               .number_format($pledge->curTotFulfill, 2).'&nbsp;'
               .$pledge->strFlagImg.'
            </td>
         </tr>');
   }
         
   echoT('
      </table><br><br>');


/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$pledges   <pre>');
echo(htmlspecialchars( print_r($pledges, true))); echo('</pre></font><br>');
// ------------------------------------- */



