<?php

   $attributes = array('name' => 'formMUser', 'id' => 'frmAddEdit');
   echoT(form_open('more/user_acct/pw', $attributes));
   
   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   openBlock('Change Password', '');  echoT('<table class="enpView" >');
   
   $clsForm->strStyleExtraLabel = 'width: 90pt; padding-top: 8px;';

   $clsForm->strExtraFieldText = form_error('txtPWord');
   $bPWErr = form_error('txtPWord').''!='';
//   $clsForm->strStyleExtraLabel = 'vertical-align: '.($bPWErr ? 'top' : 'middle').';';
   $clsForm->strID = 'addEditEntry';
   $clsForm->bPassword = true;
   echoT($clsForm->strGenericTextEntry('Current Password', 'txtPWord',  true, '', 20, 20));
   
   $clsForm->strExtraFieldText = '<i>maximum of 20 characters</i>';
   $clsForm->strExtraFieldText = form_error('txtPWord1');
   $clsForm->bPassword = true;
   echoT($clsForm->strGenericTextEntry('New Password',     'txtPWord1', true, '', 20, 20));

//   $clsForm->strStyleExtraLabel = 'vertical-align: middle;';
   $clsForm->bPassword = true;
   echoT($clsForm->strGenericTextEntry('New Password <small>(again)</small>',
                                       'txtPWord2', true, '', 20, 20));

   echoT($clsForm->strSubmitEntry('Submit', 1, 'cmdSubmit', 'text-align: center; width: 100pt;'));
   echoT(form_close());

   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');
   
   echoT('</table>'); closeBlock();

