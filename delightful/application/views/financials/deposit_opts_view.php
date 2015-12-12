<?php

   global $genumDateFormat, $gbDateFormatUS, $gstrFormatDatePicker;

   $attributes =
       array(
            'name'     => 'frmNewDeposit',
            'id'       => 'newDeposit'
            );

   echoT(form_open('financials/deposits_add_edit/addDeposit',  $attributes));

   openBlock('Add a New Deposit', '');

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->strStyleExtraLabel = 'width: 100pt;';
   $clsForm->bValueEscapeHTML = false;
   echoT('<table width="900" border="0">');


      //-------------------------------
      // Notes
      //-------------------------------
   echoT($clsForm->strLabelRow('Notes', 
                    'You will be able to select donations to be included in this deposit.<br>
                     To qualify, a donation must:
                     <ul style="list-style-type: square; display:inline; margin-left: 0; padding: 0pt;">
                       <li style="margin-left: 20px;">be within your timeframe</li>
                       <li style="margin-left: 20px;">not an in-kind donation</li>
                       <li style="margin-left: 20px;">not included in another deposit</li>
                     </ul>', 1));

      //-------------------------------
      // Recent Deposits
      //-------------------------------
   if ($lNumDeposits == 0){
      echoT($clsForm->strLabelRow('Recent Deposits', 
                    '<i>No previous deposits</i>', 1));
   }else {
      $strOut = '';
      foreach ($deposits as $deposit){
         $strOut .= $deposit->strCountryName.'&nbsp;'.$deposit->strFlagImg.'&nbsp;&nbsp;'
                   .date($genumDateFormat, $deposit->dteStart).' - '
                   .date($genumDateFormat, $deposit->dteEnd)
                   .' by '.htmlspecialchars($deposit->strCFName.' '.$deposit->strCLName).'&nbsp;'
                   .'('.number_format($deposit->lNumGifts).' entries)&nbsp;'
                   .strLinkView_DepositEntry($deposit->lKeyID, 'View deposit record', true)
                   .'<br>';
      }
      echoT($clsForm->strLabelRow('Recent Deposits', $strOut, 1));
   }
                     
      //-------------------------------
      // Accounting country of Origin
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: middle; width: 100pt; ';
   echoT($clsForm->strLabelRow('Accounting Country', $formData->strACORadio, 1));
   
      //----------------------
      // report start
      //----------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   echoT(strDatePicker('datepicker1', false));
   $clsForm->strExtraFieldText = form_error('txtSDate');
   echoT($clsForm->strGenericDatePicker(
                      'Start',   'txtSDate',      true,
                      $txtSDate, 'frmNewDeposit', 'datepicker1'));

      //----------------------
      // report end
      //----------------------
   echoT(strDatePicker('datepicker2', true));
   $clsForm->strExtraFieldText = form_error('txtEDate');
   echoT($clsForm->strGenericDatePicker(
                      'End', 'txtEDate', true,
                      $txtEDate,  'frmNewDeposit', 'datepicker2'));
                      
      //------------------------
      // Bank
      //------------------------
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
   
   
   echoT($clsForm->strSubmitEntry('Create Deposit', 2, 'cmdSubmit', 'text-align: center;'));
   echoT('</table>'.form_close('<br>'));
   closeblock();

