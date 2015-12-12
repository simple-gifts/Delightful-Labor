<?php
   global $genumDateFormat;

   $attributes = array('name' => 'frmEditGift', 'id' => 'frmAddEdit');
   echoT(form_open('donations/add_edit/addEditGift/'.$lGiftID.'/'.$lFID, $attributes));

   $clsForm = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 100pt;';

   openBlock(($bSponPayment ? 'Sponsorship Payment' : 'Donation'), ''); echoT('<table>');

      //-------------------------------
      // Record ID
      //-------------------------------
   echoT($clsForm->strLabelRow('Donation ID',
               ($bNew ? '<i>new</i>' : str_pad($lGiftID, 5, '0', STR_PAD_LEFT)), 1));

      //-------------------------------
      // Amount
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strExtraFieldText  = form_error('txtAmount');
   $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Amount', 'txtAmount', true, $formData->txtAmount, 10, 20));

      //-------------------------------
      // Accounting country of Origin
      //-------------------------------
   $clsForm->strExtraFieldText  = form_error('rdoACO');
   echoT($clsForm->strLabelRow('Accounting Country', $formData->strACORadio, 1));

      //-------------------------------
      // Accounts / Campaigns
      //-------------------------------
   if ($bSponPayment){
      echoT($clsForm->strLabelRow('sponsorID', str_pad($spon->lKeyID, 5, '0', STR_PAD_LEFT), 1));
      echoT($clsForm->strLabelRow('Sponsor',   $spon->strSponSafeNameFL.($bMismo ? ' (same as donor)' : ' (third-party payment)'), 1));
      echoT($clsForm->strLabelRow('Program',   htmlspecialchars($spon->strSponProgram), 1));
      echoT($clsForm->strLabelRow('Account',   $gift->ga_strAccount, 1));
      echoT($clsForm->strLabelRow('Campaign',  $gift->gc_strCampaign, 1));
      echoT('
         <input type="hidden" name="ddlAccount" value="'.$gift->ga_lKeyID.'">
         <input type="hidden" name="ddlCamps"   value="'.$gift->gc_lKeyID.'">
         <input type="hidden" name="ddlInKind"  value="">
         <input type="hidden" name="ddlGiftCat" value="">
         <input type="hidden" name="ddlAttrib"  value="">');
   }else {
      $clsForm->strExtraFieldText = form_error('ddlAccount');
      $clsForm->strExtraSelect = ' onChange="showCampaigns(this.value, \'ddlCamps\')" ';
      echoT($clsForm->strGenericDDLEntry('Account', 'ddlAccount', true,
                 $formData->strDDLAccts));

      $clsForm->strExtraFieldText = form_error('ddlCamps');
      $clsForm->strExtraSelect = ' ID="ddlCamps" ';
      echoT($clsForm->strGenericDDLEntry('Campaign', 'ddlCamps', true, $formData->strDDLCamps));
      $clsForm->strExtraSelect = '';
      
         //-------------------------------
         // In-kind
         //-------------------------------
      echoT($clsForm->strLabelRow('In-Kind', $formData->strDDLGIK, 1));
   }

      //-------------------------------
      // Check number
      //-------------------------------
   echoT($clsForm->strGenericTextEntry('Check number', 'txtCheck', false,
                              $formData->strCheckNum, 30, 255));

      //-------------------------------
      // Payment type
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('ddlPayType');
   echoT($clsForm->strLabelRow('Payment type*', $formData->strDDLPayType, 1));

   if (!$bSponPayment){      
         //-------------------------------
         // Major gift category
         //-------------------------------
      echoT($clsForm->strLabelRow('Major gift category', $formData->strDDLMajGiftType, 1));

         //-------------------------------
         // Attributed to
         //-------------------------------
      echoT($clsForm->strLabelRow('Attributed to', $formData->strDDLAttrib, 1));
   }

      //-------------------------------
      // Date of donation
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtDDate');
   echoT($clsForm->strGenericDatePicker(
                      'Date',    'txtDDate',      true,
                      $formData->txtDDate,
                      'frmEditGift', 'datepickerFuture'));
                      
      //-------------------------------
      // Notes
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 2pt; ';
   $clsForm->strExtraFieldText = form_error('txtNotes');
   echoT($clsForm->strNotesEntry('Notes', 'txtNotes', false, $formData->strNotes, 2, 40));                      

   closeBlock();

      //-------------------------------
      // Record info
      //-------------------------------
   openBlock('Record Info', '');
   $clsForm->strStyleExtraLabel = 'width: 100pt;';

   $clsForm->bNewRec        = $bNew;
   $clsForm->strStaffCFName = $formData->strStaffCFName;
   $clsForm->strStaffCLName = $formData->strStaffCLName;
   $clsForm->dteOrigin      = $formData->dteOrigin;
   $clsForm->strStaffLFName = $formData->strStaffLFName;
   $clsForm->strStaffLLName = $formData->strStaffLLName;
   $clsForm->dteLastUpdate  = $formData->dteLastUpdate;
   echoT($clsForm->strRecCreateModInfo());

   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'width: 100pt;'));
   echoT(form_close());
   echoT('<script type="text/javascript">frmEditGift.addEditEntry.focus();</script>');

   echoT('</table>'); closeBlock(); 

