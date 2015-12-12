<?php

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '100pt';
   showChargeRec($clsRpt, $cRec, $lSponID, $lChargeID);
   showChargeRecENPStats($clsRpt, $cRec);
   
function showChargeRec($clsRpt, $cRec, $lSponID, $lChargeID){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;
   openBlock('Charge Record',
                strLinkEdit_SponChargeRec($lSponID, $lChargeID, 'Edit Charge Record', true)
                .'&nbsp;&nbsp;&nbsp;'
                .strLinkAdd_SponsorCharges($lSponID, 'Add new charge', true)
                .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                .strLinkRem_SponsorCharge($lSponID, $lChargeID, 'Remove charge', true, true)                
                );

   echoT(
       $clsRpt->openReport()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Charge ID:')
      .$clsRpt->writeCell (str_pad($lChargeID, 5, '0', STR_PAD_LEFT))
      .$clsRpt->closeRow  ());

   $lAutoChargeID = $cRec->lAutoGenID;
   if (is_null($lAutoChargeID)){
      $strAutoCell = 'n/a';
   }else {
      $strAutoCell = strLinkView_SponsorAutoCharge($lAutoChargeID, 'View auto-charge entry', true).'&nbsp;'
                    .str_pad($lAutoChargeID, 5, '0', STR_PAD_LEFT);
   }
   
   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('AutoGen ID:')
      .$clsRpt->writeCell ($strAutoCell)
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Amount:')
      .$clsRpt->writeCell ($cRec->strCurSymbol.' '.number_format($cRec->curChargeAmnt, 2).' '.$cRec->strFlagImage)
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Date of charge:')
      .$clsRpt->writeCell (date($genumDateFormat, $cRec->dteCharge))
      .$clsRpt->closeRow  ()

      .$clsRpt->closeReport());
   closeBlock();
}
   
function showChargeRecENPStats($clsRpt, $cRec){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   openBlock('Record Information', '');
   echoT(
      $clsRpt->showRecordStats($cRec->dteOrigin, 
                            $cRec->strStaffCFName.' '.$cRec->strStaffCLName, 
                            $cRec->dteLastUpdate, 
                            $cRec->strStaffLFName.' '.$cRec->strStaffLLName, 
                            $clsRpt->strWidthLabel));
   closeBlock();
}
   

