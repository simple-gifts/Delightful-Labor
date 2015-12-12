<?php

   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 110pt; padding-top: 8px;';

   $attributes = array('name' => 'frmEditSponPayment');
   echoT(form_open('sponsors/payments/addEditPayment/'.$lSponID.'/'.$lFPayerID.'/'.$lPayID, $attributes));

   openBlock(($bNew ? 'Add new ' : 'Update '). 'Payment', '');
   echoT('
      <table class="enpView">');      

   if ($bNew){
      $strPayID = '<i>new</i>';
   }else {
      $strPayID = str_pad($lPayID, 5, '0', STR_PAD_LEFT);
   }
      
      //-------------------------------
      // Payer / Payment ID
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'width: 110pt; padding-top: 2px;';
   echoT($clsForm->strLabelRow('Payer',      $strPayer, 1));
   $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
   echoT($clsForm->strLabelRow('Payment ID', $strPayID, 1));

      //-------------------------------
      // Payment Amount
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'padding-top: 8px;';
   $clsForm->strExtraFieldText = form_error('txtAmount');
   echoT($clsForm->strGenericTextEntry('Payment Amount', 'txtAmount', true, $strAmount, 14, 20));
   
      //-------------------------------
      // Accounting country of Origin
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtPayDate');
   echoT($clsForm->strLabelRow('Accounting Country*',
                      $clsACO->strACO_Radios($lPayACO, 'rdoACO'), 1));

      //------------------------
      // Date of payment
      //------------------------
   $clsForm->strExtraFieldText = form_error('txtPayDate');
   echoT(strDatePicker('datepicker1', true, 1970));
   echoT($clsForm->strGenericDatePicker(
                      'Date of Payment', 'txtPayDate',           true,
                      $strPayDate,   'frmEditSponPayment', 'datepicker1'));

      //-------------------------------
      // Check number
      //-------------------------------
   echoT($clsForm->strGenericTextEntry('Check number', 'txtCheck', false,
                              $strCheckNum, 30, 255));

      //-------------------------------
      // Payment type
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('ddlPayType');
   echoT($clsForm->strLabelRow('Payment type*', $formData->strDDLPayType, 1));
      

   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'width: 100pt;'));
   echoT('</table></form><br><br>');
