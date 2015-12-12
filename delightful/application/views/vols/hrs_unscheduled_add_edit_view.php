<?php

   openBlock(($bNew ? 'Add New ' : 'Edit ').' Volunteer Activity', '');

   $attributes = array('name' => 'frmHrsLog');
   if ($bAsVol){
      echoT(form_open('volunteers/unscheduled/addEditActivityAsVol/'.$lVolID.'/'.$lActivityID, $attributes));
   }else {
      echoT(form_open('volunteers/unscheduled/addEditActivity/'.$lVolID.'/'.$lActivityID, $attributes));
   }

   $clsForm  = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->strStyleExtraLabel = 'width: 100pt; padding-top: 4px;';
   $clsForm->bValueEscapeHTML   = false;

   echoT('<table>');

      //----------------------
      // activity date
      //----------------------
   echoT(strDatePicker('datepicker1', true, 2000));
   $clsForm->strExtraFieldText = form_error('txtDate');
   echoT($clsForm->strGenericDatePicker(
                      'Date of vol. activity', 'txtDate',      true,
                      $formData->txtDate,    'frmHrsLog', 'datepicker1'));
                      
      /*-------------------------------
         Start Time
      -------------------------------*/
   $clsForm->strExtraFieldText = form_error('ddlStart');
   echoT($clsForm->strGenericDDLEntry('Start Time', 'ddlStart',       true,  $formData->lStartTime));
           
      /*-------------------------------
         Duration
      -------------------------------*/
   $clsForm->strExtraFieldText = form_error('ddlDuration');
   echoT($clsForm->strGenericDDLEntry('Duration', 'ddlDuration',      true,  $formData->enumDuration));
                      
      /*-------------------------------
         Activity
      -------------------------------*/
   $clsForm->strExtraFieldText = form_error('ddlActivity');
   echoT($clsForm->strLabelRow('Activity', $formData->strVolActivity, 1));                      
                      
      // job code for shift
   echoT($clsForm->strLabelRow('Job Code', $strDDLJobCode, 1));
                      
      /*-------------------------------
         Notes
      -------------------------------*/
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 2pt; ';
   $clsForm->strExtraFieldText = form_error('txtNotes');
   echoT($clsForm->strNotesEntry('Notes', 'txtNotes', false, $formData->strNotes, 3, 34));                      


   echoT($clsForm->strSubmitEntry('Save activity info', 2, 'cmdSubmit', ''));
   echoT('</table></form>');

   closeBlock();
