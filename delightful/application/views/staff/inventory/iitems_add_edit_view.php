<?php

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   if ($bNew){
      $strItemID = '<i>new</i>';
   }else {
      $strItemID = str_pad($lICatID, 5, '0', STR_PAD_LEFT);
   }

   $attributes = array('name' => 'frmIItem', 'id' => 'frmAddEdit');
   echoT(form_open('staff/inventory/inventory_items/addEditItem/'.$lICatID.'/'.$lIItemID, $attributes));

   openBlock(($bNew ? 'Add' : 'Update').' Inventory Item', '');
   echoT('<table class="enpView">');

   echoT($clsForm->strLabelRow('itemID',  $strItemID, 1));

      //----------------------
      // Category Breadcrumb
      //----------------------
   echoT($clsForm->strLabelRow('Category', htmlspecialchars($strBreadCrumb), 1));

      //----------------------
      // Item name
      //----------------------
   $clsForm->strStyleExtraLabel = 'width: 100pt; padding-top: 6px;';
   $clsForm->strExtraFieldText = form_error('txtItem');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Item',  'txtItem', true,  $formData->txtItem, 30, 255));

      //----------------------
      // Serial # a
      //----------------------
   $clsForm->strExtraFieldText = form_error('txtSNa');
   echoT($clsForm->strGenericTextEntry('Serial/Item Tag (a)',  'txtSNa', false,  $formData->txtSNa, 30, 255));

      //----------------------
      // Serial # b
      //----------------------
   $clsForm->strExtraFieldText = form_error('txtSNb');
   echoT($clsForm->strGenericTextEntry('Serial/Item Tag (b)',  'txtSNb', false,  $formData->txtSNb, 30, 255));

      //----------------------
      // Responsible Party
      //----------------------
   $clsForm->strExtraFieldText = form_error('txtRParty');
   echoT($clsForm->strGenericTextEntry('Responsible Party',  'txtRParty', true,  $formData->txtRParty, 30, 255));

      //----------------------
      // Date Acquired
      //----------------------
   echoT(strDatePicker('datepicker1', true, 1970));
   $clsForm->strExtraFieldText = form_error('txtDateAcquired');
   echoT($clsForm->strGenericDatePicker(
                      'Date Acquired', 'txtDateAcquired',      true,
                      $formData->txtDateAcquired,    'frmIItem', 'datepicker1'));

      //-------------------------------
      // Estimated Value
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strExtraFieldText  = form_error('txtEstValue');
   $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
   echoT($clsForm->strGenericTextEntry('Estimated Value', 'txtEstValue', true, $formData->txtEstValue, 10, 20));

      //-------------------------------
      // Accounting country of Origin
      //-------------------------------
   $clsForm->strExtraFieldText  = form_error('rdoACO');
   echoT($clsForm->strLabelRow('Accounting Country', $formData->strACORadio, 1));

      //-------------------------------
      // Available for loan/checkout
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'padding-top: 4pt; ';
   echoT($clsForm->strGenericCheckEntry('Available for Checkout', 'chkAvailLoan', 'TRUE', 
                      false, $formData->bAvailForLoan));

      //----------------------
      // Location
      //----------------------
   $clsForm->strExtraFieldText = form_error('txtLocation');
   $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
   echoT($clsForm->strNotesEntry('Location', 'txtLocation', true, $formData->txtLocation, 3, 50));
   
      //----------------------
      // Description
      //----------------------
   $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
   echoT($clsForm->strNotesEntry('Description', 'txtNotes', false, $formData->txtNotes, 3, 50));

   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'text-align: center; width: 80pt;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');

   closeBlock();


