<?php
   global $gbDateFormatUS, $gstrFormatDatePicker, $genumMeasurePref, $gbMetric;   

   echoT(strDatePicker('datepicker1', false));

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;
   
   $attributes = array('name' => 'frmMeasureAddEdit', 'id' => 'IDmeasureAddEdit');
   echoT(form_open('emr/measurements/addEditMeasurement/'.$lClientID.'/'.$lMeasureID, $attributes));

   $clsForm->strStyleExtraLabel = 'width: 90pt;';

   echoT('<table class="enpView" >');
   echoT($clsForm->strLabelRow('Client',  $client->strSafeName, 1));
   echoT($clsForm->strLabelRow('Birthday/Age',  $client->strClientAgeBDay, 1));
   
   
      /*------------------------
         Date of Measurement
      ------------------------*/
   $clsForm->strStyleExtraLabel = 'padding-top: 8px;';
   $clsForm->strExtraFieldText = form_error('txtDate');
   echoT($clsForm->strGenericDatePicker(
                      'Date of Measurement', 'txtDate',      true,
                      $formData->strDate,   'frmMeasureAddEdit', 'datepicker1'));
   
      /*-------------------------------
         Height
      -------------------------------*/
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
   $clsForm->strID = 'addEditEntry';
   $clsForm->strExtraFieldText = ($gbMetric ? 'cm' : 'in').form_error('txtHeight');
   $clsForm->strStyleExtraValue = 'vertical-align: middle;';
   echoT($clsForm->strGenericTextEntry('Height', 'txtHeight', false, $formData->txtHeight, 6, 6));
   
   
      /*-------------------------------
         Weight
      -------------------------------*/
   $clsForm->strExtraFieldText = ($gbMetric ? 'kg' : 'lb').form_error('txtWeight');
   $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
   echoT($clsForm->strGenericTextEntry('Weight', 'txtWeight', false, $formData->txtWeight, 6, 6));
   
      /*-------------------------------
         OFC
      -------------------------------*/
//   if ($bShowOFC){
      $clsForm->strExtraFieldText = ($gbMetric ? 'cm' : 'in').' <i>(generally only recorded for children under 3)</i>'.form_error('txtOFC');
      $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
      echoT($clsForm->strGenericTextEntry('Head Circumference', 'txtOFC', false, $formData->txtOFC, 6, 6));
//   }else {
//      $clsForm->strStyleExtraLabel = 'padding-top: 3px;';
//      echoT($clsForm->strLabelRow('Head Circumference',  '<i>Available for children under 3 years of age</i>', 1));
//   }

      /*----------------------
          Notes
      ----------------------*/
   echoT($clsForm->strNotesEntry('Notes', 'txtNotes', false, $formData->txtNotes, 4, 50));

   
   echoT($clsForm->strSubmitEntry('Save', 1, 'cmdSubmit', 'text-align: center; width: 100pt;'));
   echoT(form_close('</table><br>'));
   echoT('<script type="text/javascript">frmMeasureAddEdit.addEditEntry.focus();</script>');
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
