<?php
   global $genumDateFormat;

   $attributes = array('name' => 'frmFulfillment', 'id' => 'fulfillment');
   echoT(form_open('auctions/packages/setFulfill/'.$lPackageID, $attributes));

   $clsForm = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 100pt;';

   openBlock('Winning Bid Fulfillment', ''); echoT('<table>');
   
   echoT($clsForm->strLabelRow('Bid Winner',  $package->bw_strSafeName, 1));
   
   
      /*-------------------------------
         Amount
      -------------------------------*/
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strExtraFieldText  = form_error('txtAmount');
   $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Amount', 'txtAmount', true, $formData->txtAmount, 10, 20));

      //-------------------------------
      // Check number
      //-------------------------------
   echoT($clsForm->strGenericTextEntry('Check number', 'txtCheckNum', false,
                              $formData->txtCheckNum, 30, 255));

      //-------------------------------
      // Payment type
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('ddlPayType');
   echoT($clsForm->strLabelRow('Payment type*', $formData->strDDLPayType, 1));

      //-------------------------------
      // Major gift category
      //-------------------------------
   echoT($clsForm->strLabelRow('Major gift category', $formData->strDDLMajGiftType, 1));

      //-------------------------------
      // Attributed to
      //-------------------------------
   echoT($clsForm->strLabelRow('Attributed to', $formData->strDDLAttrib, 1));

      //-------------------------------
      // Date of donation
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtDDate');
   echoT($clsForm->strGenericDatePicker(
                      'Date',    'txtDDate',      true,
                      $formData->txtDDate,
                      'frmFulfillment', 'datepickerFuture'));
                      
      //-------------------------------
      // Notes
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 2pt; ';
   $clsForm->strExtraFieldText = form_error('txtNotes');
   echoT($clsForm->strNotesEntry('Notes', 'txtNotes', false, $formData->txtNotes, 2, 40));                      
   
      //-------------------------------
      // Save / close
      //-------------------------------
   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'width: 100pt;'));
   echoT(form_close());
   echoT('<script type="text/javascript">frmFulfillment.addEditEntry.focus();</script>');


   echoT('</table>'); closeBlock(); 
   
   