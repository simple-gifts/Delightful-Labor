<?php
   global $gbDateFormatUS;
   
/*   
   $attributes = array('name'     => $strForm, 'id' => 'frmAddEdit',
                       'onSubmit' => 'return verifyVolEventForm(frmEditVolEvent, '
                              .($gbDateFormatUS ? 'true' : 'false')
                              .', '
                              .($bNew ? 'true' : 'false').');');
   echoT(form_open('volunteers/events_add_edit/eventUpdate/'.$lEventID, $attributes));
*/   
   $attributes = array('name'     => $strForm, 'id' => 'frmAddEdit');
   echoT(form_open('volunteers/events_add_edit/addEditEvent/'.$lEventID, $attributes));
   
   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = 
   $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass = 'enpView';
   
   if ($bNew){
      $strEventID = '<i>new</i>';
   }else {
      $strEventID = str_pad($lEventID, 5, '0', STR_PAD_LEFT);
   }

   echoT('<table>');
   $clsForm->strStyleExtraLabel = 'width: 100pt; padding-top: 2px;';
   $clsForm->bValueEscapeHTML = false;
   echoT($clsForm->strLabelRow('Event ID', $strEventID, 1));

   $clsForm->strStyleExtraLabel = 'width: 100pt; padding-top: 8px;';
   $clsForm->strID = 'addEditEntry';
   $clsForm->strExtraFieldText = form_error('txtEvent');
   echoT($clsForm->strGenericTextEntry('Event Name', 'txtEvent',    true,  $formData->strEventName,  53, 255));
   echoT($clsForm->strNotesEntry      ('Note',       'txtNote',     false, $formData->strDescription, 4,  50));
   echoT($clsForm->strNotesEntry      ('Location',   'txtLocation', false, $formData->strLocation,    4,  50));
   echoT($clsForm->strGenericTextEntry('Contact',    'txtContact',  false, $formData->strContact,    53, 255));
   echoT($clsForm->strGenericTextEntry('Phone',      'txtPhone',    false, $formData->strPhone,      53,  80));

   $clsForm->strExtraFieldText = form_error('txtEmail');
   echoT($clsForm->strGenericTextEntry('Email',      'txtEmail',    false, $formData->strEmail,      53, 200));
   echoT($clsForm->strGenericTextEntry('Web Link',   'txtWebLink',  false, $formData->strWebSite,    53, 200));

   if ($bNew){
      echoT($recurringOpts);
   }

   echoT($clsForm->strSubmitEntry('Save Event', 2, 'cmdSubmit', ''));
   echoT('</table></form>');
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');

