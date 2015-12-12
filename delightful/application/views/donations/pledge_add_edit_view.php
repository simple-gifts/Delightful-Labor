<?php
   global $genumDateFormat;

   $attributes = array('name' => 'frmEditPledge', 'id' => 'frmAddEdit');
   echoT(form_open('donations/pledge_add_edit/addEdit/'.$lPledgeID.'/'.$lFID, $attributes));

   $clsForm = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 100pt;';

   openBlock('Pledge', ''); echoT('<table>');

      //-------------------------------
      // Record ID
      //-------------------------------
   echoT($clsForm->strLabelRow('Pledge ID',
               ($bNew ? '<i>new</i>' : str_pad($lPledgeID, 5, '0', STR_PAD_LEFT)), 1));

      //-------------------------------
      // Commitment Amount
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strExtraFieldText  = ' <i>Amount pledged for each pledge payment</i>'.form_error('txtCommit');
   $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Pledge Amount', 'txtCommit', true, $formData->txtCommit, 10, 20));

      //-------------------------------
      // Payment Frequency
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('ddlFreq');
   echoT($clsForm->strLabelRow('Payment Frequency', $formData->strDDLFreq, 1));

      //-------------------------------
      // # of payments
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strExtraFieldText  = ' <i>Total # of pledge payment</i>'.form_error('txtNumPay');
   $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
   echoT($clsForm->strGenericTextEntry('# of payments', 'txtNumPay', true, $formData->txtNumPay, 5, 10));
   
   
      //-------------------------------
      // Accounting country of Origin
      //-------------------------------
   $clsForm->strExtraFieldText  = form_error('rdoACO');
   echoT($clsForm->strLabelRow('Accounting Country', $formData->strACORadio, 1));

      //-------------------------------
      // Accounts / Campaigns
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('ddlAccount');
   $clsForm->strExtraSelect = ' onChange="showCampaigns(this.value, \'ddlCamps\')" ';
   echoT($clsForm->strGenericDDLEntry('Account', 'ddlAccount', true,
              $formData->strDDLAccts));

   $clsForm->strExtraFieldText = form_error('ddlCamps');
   $clsForm->strExtraSelect = ' ID="ddlCamps" ';
   echoT($clsForm->strGenericDDLEntry('Campaign', 'ddlCamps', true, $formData->strDDLCamps));
   $clsForm->strExtraSelect = '';

      //-------------------------------
      // Attributed to
      //-------------------------------
   echoT($clsForm->strLabelRow('Attributed to', $formData->strDDLAttrib, 1));

      //-------------------------------
      // Date of pledge
      //-------------------------------
   $clsForm->strExtraFieldText = '&nbsp;&nbsp;<i>Date of first expected pledge payment</i>'.form_error('txtStartDate');
   echoT($clsForm->strGenericDatePicker(
                      'Start Date',    'txtStartDate',      true,
                      $formData->txtStartDate,
                      'frmEditPledge', 'datepickerFuture'));
                      
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
   echoT('<script type="text/javascript">frmEditPledge.addEditEntry.focus();</script>');

   echoT('</table>'); closeBlock(); 

