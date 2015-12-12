<?php

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   if ($bNew){
      $strCICO_ID = '<i>new</i>';
   }else {
      $strCICO_ID = str_pad($lCICO_ID, 5, '0', STR_PAD_LEFT);
   }

   $attributes = array('name' => 'frmCICO', 'id' => 'frmAddEdit');

   if ($bCheckOut){
      echoT(form_open('staff/inventory/inventory_cico/checkOutItem/'.$lIItemID, $attributes));
      showCheckOutForm($clsForm, $bNew, $item, $formData, $strCICO_ID);
   }elseif ($bCheckIn){
      echoT(form_open('staff/inventory/inventory_cico/checkInItem/'.$lIItemID, $attributes));
      showCheckInForm($clsForm, $item, $CICOrec, $formData, $strCICO_ID);
   }elseif ($bLost){
   }

   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'text-align: center; width: 80pt;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');

   closeBlock();


   function showCheckOutForm($clsForm, $bNew, $item, $formData, $strCICO_ID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock('Check Out / Loan Inventory Item', '');
      echoT('<table class="enpView">');

      echoT($clsForm->strLabelRow('historyID',  $strCICO_ID, 1));
      echoT($clsForm->strLabelRow('Item',       htmlspecialchars($item->strItemName), 1));

         //----------------------
         // Checked Out To
         //----------------------
      $clsForm->strStyleExtraLabel = 'width: 100pt; padding-top: 6px;';
      $clsForm->strExtraFieldText = form_error('txtCheckedOutTo');
      $clsForm->strID = 'addEditEntry';
      echoT($clsForm->strGenericTextEntry('Checked Out To',  'txtCheckedOutTo', true,  $formData->txtCheckedOutTo, 50, 255));

         //----------------------
         // Deposit
         //----------------------
      $clsForm->strExtraFieldText = form_error('txtSecurity');
      echoT($clsForm->strGenericTextEntry('Deposit',  'txtSecurity', false,  $formData->txtSecurity, 50, 255));

         //----------------------
         // Date Loaned
         //----------------------
      echoT(strDatePicker('datepicker1', true, 1970));
      $clsForm->strExtraFieldText = form_error('txtDateCICO');
      echoT($clsForm->strGenericDatePicker(
                         'Date Checked Out', 'txtDateCICO',      true,
                         $formData->txtDateCICO,    'frmCICO', 'datepicker1'));

         //----------------------
         // Notes
         //----------------------
      $clsForm->strExtraFieldText = form_error('txtCO_Notes');
      $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
      echoT($clsForm->strNotesEntry('Check-out Notes', 'txtCO_Notes', false, $formData->txtCO_Notes, 3, 50));
   }

   function showCheckInForm($clsForm, $item, $CICOrec, $formData, $strCICO_ID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
   
      openBlock('Check Out / Loan Inventory Item', '');
      echoT('<table class="enpView">');

      echoT($clsForm->strLabelRow('historyID',  $strCICO_ID, 1));
      echoT($clsForm->strLabelRow('Item',       htmlspecialchars($item->strItemName), 1));

         //----------------------
         // Checked Out To
         //----------------------
      echoT($clsForm->strLabelRow('Checked out to',  htmlspecialchars($CICOrec->strCheckedOutTo), 1));

         //----------------------
         // Deposit
         //----------------------
      echoT($clsForm->strLabelRow('Deposit',  htmlspecialchars($CICOrec->strSecurity), 1));

         //----------------------
         // Date checked out
         //----------------------
      echoT($clsForm->strLabelRow('Date checked out',  date($genumDateFormat, $CICOrec->dteCheckedOut), 1));

         //----------------------
         // Date Returned
         //----------------------
      echoT(strDatePicker('datepicker1', true, 1970));
      $clsForm->strExtraFieldText = form_error('txtDateCICO');
      echoT($clsForm->strGenericDatePicker(
                         'Date Checked In', 'txtDateCICO',      true,
                         $formData->txtDateCICO,    'frmCICO', 'datepicker1'));

         //----------------------
         // Notes
         //----------------------
      $clsForm->strExtraFieldText = form_error('txtCI_Notes');
      $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
      echoT($clsForm->strNotesEntry('Check-in Notes', 'txtCI_Notes', false, $formData->txtCI_Notes, 3, 50));
   }




