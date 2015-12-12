<?php
   global $genumDateFormat;

   $attributes = array('name' => 'frmEditItem', 'id' => 'frmAddEdit');
   echoT(form_open('auctions/items/addEditAuctionItem/'.$lPackageID
               .'/'.$lItemID.(is_null($lDonorID) ? '' : '/'.$lDonorID), $attributes));

   $clsForm = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 90pt;';

   openBlock('Silent Auction Item', ''); echoT('<table>');


      //-------------------------------
      // Record ID
      //-------------------------------
   echoT($clsForm->strLabelRow('Item ID',
               ($bNew ? '<i>new</i>' : str_pad($lItemID, 5, '0', STR_PAD_LEFT)), 1));

      //-------------------------------
      // Item Donor
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strStyleExtraValue = 'vertical-align: top; padding-top: 6pt; ';
   echoT($clsForm->strLabelRow('Item Donor', $formData->txtItemDonor, 1));
   $clsForm->strStyleExtraValue = '';

      //-------------------------------
      // Bid Sheet Acknowledgement
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 10pt; ';
   $clsForm->strStyleExtraValue = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strExtraFieldText  = '<br><i>If blank, the donor\'s name will be used</i>';
   echoT($clsForm->strGenericTextEntry('Bid Sheet Ack.', 'txtDonorAck', true, 
               $formData->txtDonorAck, 40, 255));
   $clsForm->strStyleExtraValue = '';

      //-------------------------------
      // Item Name
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strExtraFieldText = form_error('txtItemName');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Item Name', 'txtItemName', true, $formData->txtItemName, 40, 255));

      //-------------------------------
      // Date obtained
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtODate');
   echoT($clsForm->strGenericDatePicker(
                      'Date Obtained',    'txtODate',      true,
                      $formData->txtODate,
                      'frmEditItem', 'datepickerFuture'));


      //-------------------------------
      // Estimated Value
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
   $clsForm->strTextBoxPrefix   = $auction->strCurrencySymbol;
   $clsForm->strStyleExtraValue = 'vertical-align: middle; ';
   $clsForm->strExtraFieldText  = form_error('txtEstValue');
   echoT($clsForm->strGenericTextEntry('Estimated Value', 'txtEstValue', true, $formData->txtEstValue, 8, 20));

      //-------------------------------
      // Out of Pocket
      //-------------------------------
   $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
   $clsForm->strTextBoxPrefix   = $auction->strCurrencySymbol;
   $clsForm->strExtraFieldText = form_error('txtOutOfPocket');
   echoT($clsForm->strGenericTextEntry('Out of Pocket Exp.', 'txtOutOfPocket', true, $formData->txtOutOfPocket, 8, 20));

      //-------------------------------
      // Public Notes
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtPublicNotes');
   echoT($clsForm->strNotesEntry('Public Notes', 'txtPublicNotes', false, $formData->txtPublicNotes, 3, 40));

      //-------------------------------
      // Private Notes
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtPrivateNotes');
   echoT($clsForm->strNotesEntry('Private Notes', 'txtPrivateNotes', false, $formData->txtPrivateNotes, 3, 40));

      //------------------------------------------
      // Save / Close form
      //------------------------------------------
   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'width: 90pt;'));
   echoT(form_close());
   echoT('<script type="text/javascript">frmEditPackage.addEditEntry.focus();</script>');

   echoT('</table>'); closeBlock();


