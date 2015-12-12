<?php

      $attributes =
          array(
               'name'     => 'frmCPAttend',
               'id'       => 'cpAttend'
               );

      echoT(form_open('cprograms/cprog_client_dir/optsA/'.$lCProgID,  $attributes));

      openBlock($cprog->strSafeAttendLabel.': '.htmlspecialchars($cprog->strProgramName), '');

      $clsForm = new generic_form;
      $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
      $clsForm->strTitleClass = 'enpViewTitle';
      $clsForm->strEntryClass = 'enpView';
      $clsForm->bValueEscapeHTML = false;
      echoT('<table width="600" border="0">');

      $clsForm->strStyleExtraLabel = 'text-align: right; width: 130pt; padding-top: 8px;';

         //------------------------
         // Reporting month
         //------------------------
      $clsForm->strExtraFieldText = form_error('txtMonth');
      echoT($clsForm->strLabelRow('Month',
                         '<input type="text" value="'.$txtMonth.'" name="txtMonth" size="8" id="month1">', 1, ''));

         //------------------------
         // Show case notes summary
         //------------------------
      $clsForm->strStyleExtraLabel = 'padding-top: 4px;'; 
      echoT($clsForm->strGenericCheckEntry('Show case notes summary', 
                             'chkCNotes', 'true', false, $formData->bShowCNotes));
                             
         //------------------------
         // Show duration
         //------------------------
      echoT($clsForm->strGenericCheckEntry('Show duration', 
                             'chkDuration', 'true', false, $formData->bShowDuration));
         
         //------------------------
         // Show activity
         //------------------------
      echoT($clsForm->strGenericCheckEntry('Show activity', 
                             'chkActivity', 'true', false, $formData->bShowActivity));
                             
         //------------------------
         // Show attendance link
         //------------------------
      echoT($clsForm->strGenericCheckEntry('Show '.$cprog->strSafeAttendLabel.' link', 
                             'chkALink', 'true', false, $formData->bShowALink));
         
         
      $clsForm->strStyleExtraLabel = 'text-align: left;';
      echoT($clsForm->strSubmitEntry('View Attendance', 2, 'cmdSubmit', ''));
      echoT('</table>'.form_close('<br>'));
      echoT('<script type="text/javascript">frmCPAttend.addEditEntry.focus();</script>');

      closeblock();

