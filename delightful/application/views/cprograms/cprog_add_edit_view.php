<?php
   global $gbDateFormatUS, $gstrFormatDatePicker;

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   if ($bNew){
      $strCProgID = '<i>new</i>';
   }else {
      $strCProgID = str_pad($lCProgID, 5, '0', STR_PAD_LEFT);
   }

   $attributes = array('name' => 'frmEditCProg', 'id' => 'frmAddEdit');
   echoT(form_open('cprograms/cprog_add_edit/addEdit/'
              .$lCProgID.($bClone ? '/'.$lCloneSourceID : ''), $attributes));

   openBlock($strBlockLabel, '');
   echoT('<table class="enpView">');

   if ($bClone){
         // source program ID
      echoT($clsForm->strLabelRow('Original Client Program ID',  str_pad($lCloneSourceID, 5, '0', STR_PAD_LEFT), 1));
         // source program ID
      echoT($clsForm->strLabelRow('Original Program Name',
                  '<b>'.htmlspecialchars($strSourceProgName).'</b><br><br>', 1));
   }

      // program ID
   echoT($clsForm->strLabelRow('Client Program ID',  $strCProgID, 1));

      //----------------------
      // Program Name
      //----------------------
   $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 8px;';

   $clsForm->strExtraFieldText = form_error('txtProgramName');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Program Name',  'txtProgramName', true,  $formData->txtProgramName, 50, 255));

      //----------------------
      // Enrollment Label
      //----------------------
   $clsForm->strExtraFieldText =
               '<br><i><span style="color: #999;">Enrollment, Application, Program Recrutment, etc</i></span>'
              .form_error('txtEnrollLabel');
   echoT($clsForm->strGenericTextEntry('Enrollment Label',  'txtEnrollLabel', true,  $formData->txtEnrollLabel, 20, 80));

      //----------------------
      // Attendance Label
      //----------------------
   $clsForm->strExtraFieldText =
               '<br><i><span style="color: #999;">Attendance, Client Contact, Participation, Case Entry, etc</i></span>'
              .form_error('txtAttendLabel');
   echoT($clsForm->strGenericTextEntry('Attendance Label',  'txtAttendLabel', true,  $formData->txtAttendLabel, 20, 80));

      //----------------------
      // Start Date
      //----------------------
   echoT(strDatePicker('datepicker1', true));
   $clsForm->strExtraFieldText = form_error('txtStartDate');
   echoT($clsForm->strGenericDatePicker(
                      'Program Start Date', 'txtStartDate',      true,
                      $formData->txtStartDate,    'frmEditCProg', 'datepicker1'));

      //----------------------
      // End Date
      //----------------------
   echoT(strDatePicker('datepicker2', true));
   $clsForm->strExtraFieldText = form_error('txtEndDate');
   echoT($clsForm->strGenericDatePicker(
                      'Program End Date', 'txtEndDate', true,
                      $formData->txtEndDate,  'frmEditCProg', 'datepicker2'));

      //----------------------
      // Description
      //----------------------
   echoT($clsForm->strNotesEntry('Description', 'txtDescription', false, $formData->txtDescription, 5, 50));

      // Read Only
   $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
   $clsForm->strExtraFieldText  = 'If checked, enrollment records can\'t be modified after entry.';
   echoT($clsForm->strGenericCheckEntry('Enrollment Records Read Only?', 'chkETableReadOnly', 'true', false, $formData->bETableReadOnly));
   $clsForm->strExtraFieldText  = 'If checked, attendance/contact records can\'t be modified after entry.';
   echoT($clsForm->strGenericCheckEntry('Attendance Records Read Only?', 'chkATableReadOnly', 'true', false, $formData->bATableReadOnly));
   
      // Hidden?
   if (!$bNew){
      $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
      $clsForm->strExtraFieldText = 'Check this box to hide the program. You can always restore it later.';
      echoT($clsForm->strGenericCheckEntry('Hidden?', 'chkHidden', 'true', false, $formData->bHidden));
   }

      // Mentor/Mentee Program?
   $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
//   $clsForm->strExtraFieldText = '<br>For mentor/mentee programs, two volunteer personalized tables are required.<br>See user\'s guide for details.';
   echoT($clsForm->strGenericCheckEntry('Mentor/Mentee Program?',
                                'chkMentorMentee', 'true', false, $formData->bMentorMentee));


   echoT('</table>');
   closeBlock();

      /*----------------------
         Validation file
      ----------------------*/
   openBlock('Validation', '');
   echoT('<table class="enpView">');
   $strNote =
          '<i>You can optionally provide your own software to manage complex enrollment/attendance validation.<br>'
         .'The validation software is in the form of a codeIgniter helper file. Save your file<br>'
         .'in directory </i><font style="font-family: courier;">
         application/helpers/custom_verification</font><i>
         and your <br> file name must end with
         </i><font style="font-family: courier;">"_helper.php"</font><i>.
         Please see the user\'s guide for more details.<br>';
   echoT($clsForm->strLabelRowOneCol($strNote, 2));
   
   $clsForm->strExtraFieldText = form_error('txtE_VerificationModule');
   echoT($clsForm->strGenericTextEntry('Validation File (enrollment)', 'txtE_VerificationModule', false, $formData->txtE_VerificationModule, 60, 255));

   $clsForm->strExtraFieldText = '<br>'
                  .'<i>If you specify a validation file, this is where you specify the name of your function <br>'
                  .'to call. Your routine returns false if the form data is not valid.<br>'
                  .form_error('txtE_VModEntryPoint');
   echoT($clsForm->strGenericTextEntry('Entry Point (enrollment)', 'txtE_VModEntryPoint', false, $formData->txtE_VModEntryPoint, 60, 255));

   $clsForm->strExtraFieldText = form_error('txtA_VerificationModule');
   echoT($clsForm->strGenericTextEntry('Validation File (attendance)', 'txtA_VerificationModule', false, $formData->txtA_VerificationModule, 60, 255));

   $clsForm->strExtraFieldText = '<br>'
                  .'<i>If you specify a validation file, this is where you specify the name of your function <br>'
                  .'to call. Your routine returns false if the form data is not valid.<br>'
                  .form_error('txtA_VModEntryPoint');
   echoT($clsForm->strGenericTextEntry('Entry Point (attendance)', 'txtA_VModEntryPoint', false, $formData->txtA_VModEntryPoint, 60, 255));

   echoT($clsForm->strSubmitEntry($strBlockLabel, 1, 'cmdSubmit', 'text-align: center;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');
   closeBlock();



