<?php

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '100pt';
   showPayRec($clsRpt, $pRec, $lSponID, $lFPayerID, $lPayID);
   showPayRecENPStats($clsRpt, $pRec);



function showPayRec($clsRpt, &$pRec, $lSponID, $lFPayerID, $lPayID){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;

   openBlock('Sponsor payment record ',
              strLinkEdit_SponsorPayment($lSponID, $lFPayerID, $lPayID, 'Edit payment record', true)
             .'&nbsp;&nbsp;'
             .strLinkAdd_SponsorPayment($lSponID, 'Add new payment', true)
             .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
             .strLinkRem_SponsorPayment($lSponID, $lPayID, 'Remove payment', true, true)
             );

//   $pRec = $clsSCP->paymentRec;
   echoT(
       $clsRpt->openReport()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Payment ID:')
      .$clsRpt->writeCell (str_pad($lPayID, 5, '0', STR_PAD_LEFT))
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Amount:')
      .$clsRpt->writeCell ($pRec->strCurSymbol.' '.number_format($pRec->curPaymentAmnt, 2).' '.$pRec->strFlagImage)
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Date of payment:')
      .$clsRpt->writeCell (date($genumDateFormat, $pRec->dtePayment))
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Payment type:')
      .$clsRpt->writeCell (htmlspecialchars($pRec->strPaymentType))
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Check #:')
      .$clsRpt->writeCell (htmlspecialchars($pRec->strCheckNum))
      .$clsRpt->closeRow  ());

   if ($pRec->lSponPeopleID == $pRec->lDonorID){
      $strPayer = $pRec->strSponSafeNameFL.' (sponsor)';
   }else {
      if ($pRec->bDonorBiz){
         $strPayer = strLinkView_BizRecord($pRec->lDonorID, 'View business record', true).' ';
      }else {
         $strPayer = strLinkView_PeopleRecord($pRec->lDonorID, 'View people record', true).' ';
      }
      $strPayer .= $pRec->strDonorSafeNameFL.' <b>(THIRD PARTY PAYER)</b>';
   }

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Payer:')
      .$clsRpt->writeCell ($strPayer)
      .$clsRpt->closeRow  ()
      .$clsRpt->closeReport());
}


function showPayRecENPStats($clsRpt, $pRec){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   openBlock('Record Information', '');
   echoT(
      $clsRpt->showRecordStats($pRec->dteOrigin, 
                            $pRec->strStaffCFName.' '.$pRec->strStaffCLName, 
                            $pRec->dteLastUpdate, 
                            $pRec->strStaffLFName.' '.$pRec->strStaffLLName, 
                            $clsRpt->strWidthLabel));
   closeBlock();
}
