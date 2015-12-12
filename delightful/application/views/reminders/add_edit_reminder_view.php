<?php
global $gbDateFormatUS;

$attributes = array('name'     => $strForm, 'id' => 'frmAddEdit',
                    'onSubmit' => 'return verifyReminderForm('
                                      .$strForm.', '.($gbDateFormatUS ? 'true' : 'false').', true);');
echoT(form_open('reminders/rem_add_edit/addEditUpdate/'.$enumRemType.'/'.$lFID.'/'.$lRemID, $attributes));

if ($bNew){
   $strRemID = '<i>new</i>';
}else {
   $strRemID = str_pad($lRemID, 5, '0', STR_PAD_LEFT);
}

//$clsForm = new generic_form;
$clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = 
$clsForm->strLabelClassRequired = 'enpViewLabel';
$clsForm->strEntryClass = 'enpView';

echoT('<table>');
$clsForm->strStyleExtraLabel = 'width: 100pt;';
$clsForm->bValueEscapeHTML = false;

echoT($clsForm->strLabelRow('Reminder ID', $strRemID, 1));

$clsForm->strExtraFieldText = form_error('txtSubject');
$clsForm->strID = 'addEditEntry';
echoT($clsForm->strGenericTextEntry('Subject', 'txtSubject', true,  $strTitle, 53, 255));
echoT($clsForm->strNotesEntry      ('Note',    'txtNote',    false, $strReminderNote, 4, 50));

//showInformList($clsRem, $clsForm, $bNew);
echoT($clsForm->strLabelRow('Users to Inform', $strInformDDL, 1));

if ($bNew){
   echoT($recurringOpts);
//   $clsRecurring = new mrecurring();
//   showRemRecurOpts($clsRecurring, $clsRem, $clsForm, 'frmEditReminder');
}else {
   showRemDisplayDates($clsRem);
}

echoT($clsForm->strSubmitEntry('Save Reminder', 2, 'cmdSubmit', ''));
echoT('</table></form>');
echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');



