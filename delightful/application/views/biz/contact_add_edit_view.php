<?php
   global $gbDateFormatUS, $gstrFormatDatePicker;

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;
   
   echo form_open('biz/biz_contact_add_edit/contactUpdate/'.$lContactID);
   
   openBlock(($bNew ? 'Add New ' : 'Update ').'Business Contact', '');
   
   echoT('<table class="">');
//   echoT($clsForm->strTitleRow( (($bNew ? 'Add New ' : 'Update ').'Contact Record'), 2, ''));
   
   
   $strConName =
      strLinkView_PeopleRecord($lPID, 'View people record', true).' '
             .str_pad($lPID, 5, '0', STR_PAD_LEFT).' '
             .htmlspecialchars($contactName);

   echoT($clsForm->strTitleRow('Contact for <i>'.$bizName.'</i>', 2, ''));
   echoT($clsForm->strLabelRow('Contact', $strConName, 1));
   echoT($clsForm->strLabelRow('Relationship', $formData->relDDL, 1));
             
             
   echoT($clsForm->strGenericCheckEntry('Soft Cash?', 'chkSoft', 'TRUE', false, $formData->bSoftCash));
   
   
   
   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'width: 50pt;'));
   echoT(form_close());
   closeBlock(); echoT('</table>');
   
   
