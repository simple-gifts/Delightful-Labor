<?php



   showSponsorChargeList           ($lNumCharges,  $charges,    $lSponID);
   showSponsorPaymentList          ($lNumPayments, $payHistory, $lSponID, $lSponFID);
   showSponsorFinancialSummaryBlock($strFinancialSum);

   
function showSponsorChargeList($lNumCharges, &$charges, $lSponID){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
//   $clsSCP->cumulativeChargeHistory($lSponID);
   openBlock('Sponsor Charges',
             strLinkAdd_SponsorCharges($lSponID, 'Add new charge', true).'&nbsp;'
            .strLinkAdd_SponsorCharges($lSponID, 'Add new charge', false)
             );
   if ($lNumCharges > 0){

      openSponChargeList();   
      writeSponChargeList($lNumCharges, $charges);
      closeSponChargeList();
   }else {
      echoT('<i>There are no charges associated with this sponsorship.</i><br><br>');
   }
   closeBlock();
}   

function writeSponChargeList($lNumCharges, &$charges){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;
   if ($lNumCharges > 0){
      foreach ($charges as $clsC){
         $lAutoGenID = $clsC->lAutoGenID;
         if (is_null($lAutoGenID)){
            $strAutoGen  = 'n/a';
            $strAGTAlign = 'center';
         }else {
            $strAutoGen = strLinkView_SponsorAutoCharge($lAutoGenID, 'View autogen report', true).' '
                         .str_pad($lAutoGenID, 5, '0', STR_PAD_LEFT).' for '
                         .date('F Y', $clsC->dteAutoCharge);
            $strAGTAlign = 'left';
         }
      
         echoT('
             <tr>
                <td class="enpRpt" style="text-align: center;">'
                   .strLinkView_SponsorCharge($clsC->lKeyID, 'View charge record', true).' '
                   .str_pad($clsC->lKeyID, 5, '0', STR_PAD_LEFT).'
                </td>

                <td class="enpRpt">'
                   .date($genumDateFormat, $clsC->dteCharge).'
                </td>

                <td class="enpRpt" style="text-align: right;">'
                   .$clsC->strACOCurSym.' '.number_format($clsC->curChargeAmnt, 2).' '.$clsC->strACOFlagImg.'
                </td>

                <td class="enpRpt" style="text-align: '.$strAGTAlign.';">'
                   .$strAutoGen.'
                </td>
             </tr>
         ');
      }
   }
}
   
function openSponChargeList(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT('
       <table class="enpRpt">
          <tr>
             <td class="enpRptLabel" style="width: 80px;">
                Charge ID
             </td>

             <td class="enpRptLabel" >
                Date
             </td>

             <td class="enpRptLabel" style="width: 80px;">
                Amount
             </td>

             <td class="enpRptLabel" style="width: 170px;">
                AutoGen ID
             </td>
          </tr>
   ');
}

function closeSponChargeList(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT('</table>');
}

function showSponsorPaymentList($lNumPayments, &$payHistory, $lSponID, $lSponFID){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   openBlock('Sponsor Payments',
             strLinkAdd_SponsorPayment($lSponID, 'Add new payment', true).'&nbsp;'
            .strLinkAdd_SponsorPayment($lSponID, 'Add new payment', false)
             );

   if ($lNumPayments > 0){
      openSponPaymentList();   
      writeSponPaymentList($lNumPayments, $payHistory, $lSponFID);
      closeSponPaymentList();
   }else {
      echoT('<i>There are no payments associated with this sponsorship.</i><br><br>');
   }
   
   closeBlock();
}

function writeSponPaymentList($lNumPayments, &$payHistory, $lSponForeignID){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;
   
   foreach ($payHistory as $clsP){
   
      if ($lSponForeignID == $clsP->lPayerID){
         $strPayer = $clsP->strPayerSafeNameFL.' (sponsor)';
      }else {
         if ($clsP->bDonorBiz){
            $strPayer = strLinkView_BizRecord($clsP->lPayerID, 'View business record', true).' ';
         }else {
            $strPayer = strLinkView_PeopleRecord($clsP->lPayerID, 'View people record', true).' ';
         }
         $strPayer .= $clsP->strPayerSafeNameFL.' (THIRD PARTY PAYER)';      
      }
   
      echoT('
          <tr>
             <td class="enpRpt" style="text-align: center;">'
                .strLinkView_SponsorPayment($clsP->lKeyID, 'View payment record', true).' '
                .str_pad($clsP->lKeyID, 5, '0', STR_PAD_LEFT).'
             </td>

             <td class="enpRpt">'
                .date($genumDateFormat, $clsP->dtePayment).'
             </td>

             <td class="enpRpt" style="text-align: right;">'
                .$clsP->strCurSymbol.' '.number_format($clsP->curPayment, 2).' '.$clsP->strFlagImage.'
             </td>

             <td class="enpRpt" style="text-align: left;">'
                .$strPayer.'
             </td>
          </tr>
      ');
   }
}

function openSponPaymentList(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT('
       <table class="enpRpt">
          <tr>
             <td class="enpRptLabel" style="width: 80px;">
                Payment ID
             </td>

             <td class="enpRptLabel" >
                Date
             </td>

             <td class="enpRptLabel" style="width: 80px;">
                Amount
             </td>

             <td class="enpRptLabel" style="width: 260px;">
                Payer
             </td>
          </tr>
   ');
}

function closeSponPaymentList(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT('</table>');
}



function showSponsorFinancialSummaryBlock($strFinancialSum){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------

   openBlock('Financial Summary', '');
   echoT($strFinancialSum);
   closeBlock();

}










   