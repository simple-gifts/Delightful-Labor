<?php
   global $gbDateFormatUS, $gstrFormatDatePicker, $gbVolLogin;
   global $gclsChapterVoc;

   echoT(strDatePicker('datepicker1', false));

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   if ($bNew){
      $strPID = '<i>new</i>';
   }else {
      $strPID = str_pad($lPID, 5, '0', STR_PAD_LEFT);
   }

   $attributes = array('name' => 'frmEditPerson', 'id' => 'frmAddEdit');
   echoT(form_open('people/people_add_edit/contact/'.$lPID.'/'.$people->lHouseholdID, $attributes));

      // signals for duplicate people test
   if (isset($bHiddenNewTestDup)){
      echoT(form_hidden('hiddenTestDup', ($bHiddenNewTestDup ? 'true' : 'false')));
   }
   echoT($strDuplicateWarning);

   if ($bNew){
      $strTitle  = "Add New Person";
      $strButton = "Add Person";
   }else {
      $strTitle  = "Update People Record";
      $strButton = "Update";
   }

//   echoT('<br><table class="enpView">');
   $clsForm->strStyleExtraLabel = 'width: 90pt;';

   openBlock('Name', '');  echoT('<table class="enpView" >');
   echoT($clsForm->strLabelRow('peopleID',  $strPID, 1));

      //------------------------
      // Household
      //------------------------
   if (!$gbVolLogin){
      echoT($clsForm->strLabelRow('Household', $people->strHouseholdName, 1));
   }

      //------------------------
      // Title
      //------------------------
   $clsForm->strStyleExtraLabel = 'width: 90pt; padding-top: 8px;';
   $clsForm->strExtraFieldText = form_error('txtTitle');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Title', 'txtTitle', false, $formData->txtTitle, 40, 50));

      //------------------------
      // Name
      //------------------------
   $clsForm->strExtraFieldText = form_error('txtFName');
   echoT($clsForm->strGenericTextEntry('First Name',     'txtFName', true,  $formData->txtFName, 40, 80));
   echoT($clsForm->strGenericTextEntry('Middle Name',    'txtMName', false, $formData->txtMName, 40, 80));
   $clsForm->strExtraFieldText = form_error('txtLName');
   echoT($clsForm->strGenericTextEntry('Last Name',      'txtLName', true,  $formData->txtLName, 40, 80));
   echoT($clsForm->strGenericTextEntry('Preferred Name', 'txtPName', false, $formData->txtPName, 40, 80));
   echoT($clsForm->strGenericTextEntry('Salutation',     'txtSal',   false, $formData->txtSal,   40, 80));

   echoT('</table>'); closeBlock();

   openBlock('Address', '');  echoT('<table class="enpView" >');

      //------------------------
      // Address
      //------------------------
   echoT($clsForm->strGenericTextEntry('Address 1',   'txtAddr1',   false,  $formData->txtAddr1,    40, 80));
   echoT($clsForm->strGenericTextEntry('Address 2',   'txtAddr2',   false,  $formData->txtAddr2,    40, 80));
   echoT($clsForm->strGenericTextEntry('City',        'txtCity',    false,  $formData->txtCity,     40, 80));
   echoT($clsForm->strGenericTextEntry($gclsChapterVoc->vocState,    'txtState',   false,  $formData->txtState,    40, 80));
   echoT($clsForm->strGenericTextEntry($gclsChapterVoc->vocZip,      'txtZip',     false,  $formData->txtZip,      20, 40));
   echoT($clsForm->strGenericTextEntry('Country',     'txtCountry', false,  $formData->txtCountry,  20, 80));

   echoT('</table>'); closeBlock();

   openBlock('Other Contact Info', '');  echoT('<table class="enpView" >');

      //------------------------
      // Email / Phone
      //------------------------
   if ($gbVolLogin){
      $strExtraEmail = '<br><i>Note: changing your email will also change your log-in user ID.</i>';
   }else {
      $strExtraEmail = '';
   }

   $clsForm->strExtraFieldText = $strExtraEmail.form_error('txtEmail');
   echoT($clsForm->strGenericTextEntry('Email',     'txtEmail', false,  $formData->txtEmail, 40, 120));
   echoT($clsForm->strGenericTextEntry('Phone',     'txtPhone', false,  $formData->txtPhone, 20,  40));
   echoT($clsForm->strGenericTextEntry('Cell',      'txtCell',  false,  $formData->txtCell,  20,  40));

   echoT('</table>'); closeBlock();

   openBlock('Miscellaneous', '');  echoT('<table class="enpView" >');
      //------------------------
      // Gender
      //------------------------
   $clsForm->strStyleExtraLabel = 'width: 90pt; padding-top: 2px;';
   echoT($clsForm->strGenderRadioEntry('Gender', 'rdoGender', true, $formData->enumGender));

      //------------------------
      // Birthdate
      //------------------------
   $clsForm->strStyleExtraLabel = 'padding-top: 8px;';
   $clsForm->strExtraFieldText = form_error('txtBDate');
   echoT($clsForm->strGenericDatePicker(
                      'Birthdate', 'txtBDate',      false,
                      $formData->strBDay,    'frmEditPerson', 'datepicker1'));

      //-------------------------------
      // Accounting country of Origin
      //-------------------------------
   if (!$gbVolLogin){
      echoT('
            <tr>
               <td class="enpViewLabel" width="100" style="padding-top: 8px;">
                  Accounting Country:
               </td>
               <td class="enpView">'
                  .$formData->rdoACO.'
               </td>
            </tr>');
   }

      //-------------------------------
      // Notes
      //-------------------------------
   if (!$gbVolLogin){
      echoT($clsForm->strNotesEntry('Notes', 'txtNotes', false, $formData->txtNotes, 3, 40));
   }

      //--------------------------
      // Attributed to
      //--------------------------
   if (!$gbVolLogin){
      $clsForm->strStyleExtraLabel = 'padding-top: 8px;';
      echoT($clsForm->strLabelRow('Attributed To', $strAttribDDL, false));

      echoT('</table>'); closeBlock();

      openBlock('Record Info', '');  echoT('<table class="enpView" >');

      $clsForm->strStyleExtraLabel = 'padding-top: 3px;';
      $clsForm->bNewRec        = $bNew;
      if (!$bNew){
         $clsForm->strStaffCFName = $people->strStaffCFName;
         $clsForm->strStaffCLName = $people->strStaffCLName;
         $clsForm->dteOrigin      = $people->dteOrigin;
         $clsForm->strStaffLFName = $people->strStaffLFName;
         $clsForm->strStaffLLName = $people->strStaffLLName;
         $clsForm->dteLastUpdate  = $people->dteLastUpdate;
      }
      echoT($clsForm->strRecCreateModInfo());
   }

   echoT('</table>'); closeBlock();

   echoT($clsForm->strSubmitEntry($strButton, 1, 'cmdSubmit', 'text-align: center; width: 100pt;'));
   echoT(form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');

