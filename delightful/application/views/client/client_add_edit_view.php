<?php
   global $gbDateFormatUS, $gstrFormatDatePicker;
   global $gclsChapterVoc;

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   if ($bNoStatEntries){
      echoT($strNoStatErrMsg);
   }else {

      if ($bNew){
         $strClientID = '<i>new</i>';
      }else {
         $strClientID = str_pad($lClientID, 5, '0', STR_PAD_LEFT);
      }

      $strClientVName = $clsClientV->vocs[0]->strClientS;
      $attributes = array('name' => 'frmEditClient', 'id' => 'frmAddEdit');
      echoT(form_open('clients/client_rec_add_edit/addEdit/'.$lClientID, $attributes));
      
         // signals for duplicate client test
      if (isset($bHiddenNewTestDup)){
         echoT(form_hidden('hiddenTestDup', ($bHiddenNewTestDup ? 'true' : 'false')));
      }
      echoT($strDuplicateWarning);

      if ($bNew){
         echoT(form_hidden('lLocationID',  $client->lLocationID));
         echoT(form_hidden('lStatusCatID', $client->lStatusCatID));
         echoT(form_hidden('lVocID',       $client->lVocID));
      }

      if ($bNew){
         $strTitle  = "Add New $strClientVName";
         $strButton = "Add $strClientVName";
      }else {
         $strTitle  = "Update $strClientVName";
         $strButton = "Update $strClientVName";
      }      

      openBlock($strTitle, '');
      echoT('<table class="enpView">'
         .$clsForm->strLabelRow($clsClientV->vocs[0]->strLocS, $clsLocation->strLocation, 1));

      echoT($clsForm->strLabelRow('clientID',  $strClientID, 1));

         //----------------------
         // name
         //----------------------
      $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 8px;';
      
      $clsForm->strExtraFieldText = form_error('txtFName');
      $clsForm->strID = 'addEditEntry';
      echoT($clsForm->strGenericTextEntry("$strClientVName First Name",  'txtFName', true,  $formData->txtFName, 20, 30));
      echoT($clsForm->strGenericTextEntry("$strClientVName Middle Name", 'txtMName', false, $formData->txtMName, 20, 30));
      echoT($clsForm->strGenericTextEntry("$strClientVName Last Name",   'txtLName', false, $formData->txtLName, 20, 30));

         //----------------------
         // birthdate
         //----------------------
      echoT(strDatePicker('datepicker1', false));
      $clsForm->strExtraFieldText = form_error('txtBDate');
      echoT($clsForm->strGenericDatePicker(
                         'Birthdate', 'txtBDate',      true,
                         $formData->txtBDate,    'frmEditClient', 'datepicker1'));

         //----------------------
         // Date enrolled
         //----------------------
      echoT(strDatePicker('datepicker2', false));
      $clsForm->strExtraFieldText = form_error('txtPEntryDate');
      echoT($clsForm->strGenericDatePicker(
                         'Date entered in program', 'txtPEntryDate', true,
                         $formData->txtPEntryDate,  'frmEditClient', 'datepicker2'));

         //-----------------------------------------------------
         // sponsorship categories supported by this location
         //-----------------------------------------------------
      echoT('
         <td class="enpViewLabel" style="width: 120pt;">
            Participates in these<br>Sponsorship Programs:
         </td>
         <td class="enpView" style="">');
      if ($clsSponProg->lNumSponPrograms == 0){
         echoT('<i>No sponsorship categories defined for this location</i>');
      }else {
         foreach ($clsSponProg->sponProgs as $clsLocSP){
            $lSCID = $clsLocSP->lKeyID;
            echoT('
               <input type="checkbox" name="chkSP[]" value="'.$lSCID.'" '
                     .(@$clsLocSP->bSupported ? 'checked' : '')
                     .' >'
                     .htmlspecialchars($clsLocSP->strProg).'<br>');
         }
      }
      echoT('
            </td>
         </tr>');

      $clsForm->strExtraFieldText = form_error('txtMaxSpon');
      echoT($clsForm->strGenericTextEntry('Max # of Sponsors', 'txtMaxSpon', true, $formData->txtMaxSpon, 4, 10));

         //----------------------
         // Status
         //----------------------
      if ($bNew){
         $clsForm->strExtraFieldText = form_error('ddlStatus');
         echoT($clsForm->strGenericDDLEntry(
                    'Initial Status',
                    'ddlStatus',
                    true,
                    $clsClientStat->strClientStatEntriesDDL($client->lStatusCatID, true, $formData->lInitStatID)
                  )
         );
      }

         //----------------------
         // Gender
         //----------------------
      $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 3px;';
      $clsForm->strExtraFieldText = form_error('rdoGender');
      echoT($clsForm->strGenderRadioEntry('Gender', 'rdoGender', true, $formData->rdoGender));

         //----------------------
         // Bio
         //----------------------
      echoT($clsForm->strNotesEntry("$strClientVName Bio", 'txtBio', false, $formData->txtBio, 5, 50));

         //------------------------
         // Address 
         //------------------------
      $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 8px;';
      echoT($clsForm->strGenericTextEntry('Address 1',   'txtAddr1',   false,  $formData->txtAddr1,    40, 80));
      echoT($clsForm->strGenericTextEntry('Address 2',   'txtAddr2',   false,  $formData->txtAddr2,    40, 80));
      echoT($clsForm->strGenericTextEntry('City',        'txtCity',    false,  $formData->txtCity,     40, 80));
      echoT($clsForm->strGenericTextEntry($gclsChapterVoc->vocState,       'txtState',   false,  $formData->txtState,    40, 80));
      echoT($clsForm->strGenericTextEntry($gclsChapterVoc->vocZip,         'txtZip',     false,  $formData->txtZip,      20, 40));
      echoT($clsForm->strGenericTextEntry('Country',     'txtCountry', false,  $formData->txtCountry,  20, 80));

         //------------------------
         // Email / Phone
         //------------------------
      $clsForm->strExtraFieldText = form_error('txtEmail');
      echoT($clsForm->strGenericTextEntry('Email',     'txtEmail', false,  $formData->txtEmail, 40, 120));
      echoT($clsForm->strGenericTextEntry('Phone',     'txtPhone', false,  $formData->txtPhone, 20,  40));
      echoT($clsForm->strGenericTextEntry('Cell',      'txtCell',  false,  $formData->txtCell,  20,  40));
      
      
         //--------------------------
         // Attributed to
         //--------------------------
      $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 8px;';
      echoT($clsForm->strLabelRow('Attributed To', $strAttribDDL, false));      
      
      echoT($clsForm->strSubmitEntry($strButton, 1, 'cmdSubmit', 'text-align: center;'));
      echoT('</table>'.form_close('<br>'));
      echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');  

      closeBlock();      
   }