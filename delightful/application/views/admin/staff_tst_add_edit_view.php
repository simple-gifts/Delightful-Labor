<?php

   if (!bAllowAccess('adminOnly')) return('');

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   $attributes = array('name' => 'frmTSTemplate', 'id' => 'frmAddEdit');
   echoT(form_open('admin/timesheets/add_edit_tst_record/addEditTST/'.$lTSTID, $attributes));

   openBlock('Time Sheets: '.($bNew ? 'Add New' : 'Edit').' Template', '');
   echoT('<table class="enpView">');

      //------------------------
      // Template Name
      //------------------------
   $clsForm->strStyleExtraLabel = 'width: 130pt; padding-top: 8px;';
   $clsForm->strExtraFieldText = form_error('txtTemplateName');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Template Name', 'txtTemplateName', true,  $formData->txtTemplateName, 30, 80));

   if ($bNew){
         // Time Period
      $clsForm->strExtraFieldText = form_error('ddlTP');
      echoT($clsForm->strLabelRow('Time Period', $formData->strTimePeriodDDL, 1));

         // Start of Week
      $clsForm->strExtraFieldText = 
                          '<i>Applies to weekly time periods</i><br>'
                         .form_error('ddlStart');
      echoT($clsForm->strLabelRow('Start of Week', $formData->strStartSOWDDL, 1));
   }else {
         // Time Period
      $clsForm->strStyleExtraLabel = 'padding-top: 3px;';
      echoT($clsForm->strLabelRow('Time Period', $tst->enumRptPeriod, 1));

         // Start of Week
      $clsForm->strExtraFieldText = '<br><i>(applies to weekly time periods)</i><br>';
      echoT($clsForm->strLabelRow('Start of Week', ts_util\strXlateDayofWeek($tst->lFirstDayOfWeek), 1));
      $clsForm->strStyleExtraLabel = 'padding-top: 6px;';
   }
   
      //------------------------
      // Time Granularity
      //------------------------
   $clsForm->strExtraFieldText = form_error('ddlTimeGrain');
   echoT($clsForm->strLabelRow('Time Granularity', $formData->strTimeGrainDDL, 1));

      //------------------------
      // 24 Hour Timeclock
      //------------------------
   $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
   $clsForm->strExtraFieldText = '&nbsp;<i>If checked, 4:42 PM will be displayed as 16:42.</i>';
   echoT($clsForm->strGenericCheckEntry('24-Hour Time Display', 'chk24Hour', 'true', false,
                           $formData->b24HrTime));

      //------------------------
      // Hidden?
      //------------------------
   if (!$bNew){
      $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
      $clsForm->strExtraFieldText = '<br><i>You can hide a time sheet and it not be available
                    to users.<br>
                    You can unhide it later without loss of data. Even when hidden, <br>
                    the time sheet information will be included in time sheet reports.</i>';
      echoT($clsForm->strGenericCheckEntry('Hidden?', 'chkHidden', 'true', false,
                              $formData->bHidden));
   }
                           
      //------------------------
      // Acknowledgment Text
      //------------------------
   echoT($clsForm->strNotesEntry('Acknowledgment Text', 'txtAck', false, $formData->txtAck, 3, 40));

      //------------------------
      // Notes
      //------------------------
   echoT($clsForm->strNotesEntry('Notes', 'txtNotes', false, $formData->txtNotes, 3, 40));


   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'text-align: center; width: 80pt;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');

   closeBlock();









