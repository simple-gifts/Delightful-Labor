<?php
   global $genumDateFormat;

   $attributes = array('name' => 'frmEventShift', 'id' => 'frmAddEdit');
   echoT(form_open('volunteers/event_date_shifts_add_edit/addEditShift/'.$lEventDateID.'/'.$lShiftID, $attributes));

   $clsForm  = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->strStyleExtraLabel = 'width: 100pt;';
   $clsForm->bValueEscapeHTML   = false;

   if ($bNew){
      $strShiftID = '<i>new</i>';
   }else {
      $strShiftID = str_pad($lShiftID, 5, '0', STR_PAD_LEFT);
   }   
   echoT('<table>');

   $clsForm->strStyleExtraLabel = 'width: 100pt;';
   $clsForm->bValueEscapeHTML = false;
   echoT($clsForm->strLabelRow('Shift ID', $strShiftID, 1));
   echoT($clsForm->strLabelRow('Shift Date', date($genumDateFormat.' (D)', $dteEvent), 1));

   $clsForm->strExtraFieldText = form_error('txtShiftName');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Shift Name',    'txtShiftName',    true,  $formData->txtShiftName,  53, 255));

   echoT($clsForm->strNotesEntry      ('Description',   'txtShiftNotes',   false, $formData->txtShiftNotes, 4,  50));

   $clsForm->strExtraFieldText = form_error('txtNumVols');
   echoT($clsForm->strGenericTextEntry('# Vols Needed', 'txtNumVols',      true,  $formData->txtNumVols,  5, 5));

      // job code for shift
   echoT($clsForm->strLabelRow('Job Code', $strDDLJobCode, 1));
   
      // shift date
   $clsForm->strExtraFieldText = form_error('ddlShiftStart');
   echoT($clsForm->strGenericDDLEntry('Start Time', 'ddlShiftStart',       true,  $formData->lEventStartTime));           
           
      // shift duration
   $clsForm->strExtraFieldText = form_error('ddlShiftDuration');
   echoT($clsForm->strGenericDDLEntry('Duration', 'ddlShiftDuration',      true,  $formData->enumDuration));

   echoT($clsForm->strSubmitEntry('Save Event Shift', 2, 'cmdSubmit', ''));
   echoT('</table></form>');
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');




