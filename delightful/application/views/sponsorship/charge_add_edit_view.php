<?php

   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 110pt;';

   $attributes = array('name' => 'frmEditSponCharge');
   echoT(form_open('sponsors/charges/addEditCharge/'.$lSponID.'/'.$lChargeID, $attributes));

   openBlock(($bNew ? 'Add new ' : 'Update '). 'Charge', '');
   echoT('
      <table class="enpView">');      


      //-------------------------------
      // Charge Amount
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtAmount');
   echoT($clsForm->strGenericTextEntry('Charge Amount', 'txtAmount', true,
                           $strAmount, 14, 20));
      //-------------------------------
      // Accounting country of Origin
      //-------------------------------
   echoT($clsForm->strLabelRow('Accounting Country', 
                      $clsACO->strACO_Radios($lChargeACO, 'rdoACO'), 1));


      //------------------------
      // Date of charge
      //------------------------
   $clsForm->strExtraFieldText = form_error('txtChargeDate');
   echoT(strDatePicker('datepicker1', true, 1970));
   echoT($clsForm->strGenericDatePicker(
                      'Date of Charge', 'txtChargeDate',           true,
                      $strChargeDate,   'frmEditSponCharge', 'datepicker1'));

   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'width: 100pt;'));
   echoT('</table></form><br><br>');

   closeBlock();


