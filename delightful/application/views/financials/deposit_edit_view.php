<?php

   $attributes =
       array(
            'name'     => 'frmEditDeposit',
            'id'       => 'newDeposit'
            );

   echoT(form_open('financials/deposits_add_edit/editDepositUpdate/'.$lDepositID,  $attributes));

   openBlock('Edit Deposit', '');
   
   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->strStyleExtraLabel = 'width: 100pt;';
   $clsForm->bValueEscapeHTML = false;
   echoT('<table width="900" border="0">');

      //------------------------
      // Bank
      //------------------------
   $clsForm->strID = 'addEditEntry';
   $clsForm->strExtraFieldText = form_error('txtBank');
   echoT($clsForm->strGenericTextEntry('Bank', 'txtBank', false, $formData->txtBank, 30, 80));

      //------------------------
      // Account
      //------------------------
   $clsForm->strExtraFieldText = form_error('txtAccount');
   echoT($clsForm->strGenericTextEntry('Account', 'txtAccount', false, $formData->txtAccount, 30, 80));
   
      //----------------------
      // Notes
      //----------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 3pt; ';
   echoT($clsForm->strNotesEntry('Notes', 'txtNotes', false, $formData->txtNotes, 4, 40));
   
   
   echoT($clsForm->strSubmitEntry('Update Deposit', 2, 'cmdSubmit', 'text-align: center;'));
   echoT('</table>'.form_close('<br>'));
   closeblock();

   echoT('<script type="text/javascript">frmEditDeposit.addEditEntry.focus();</script>');
   

