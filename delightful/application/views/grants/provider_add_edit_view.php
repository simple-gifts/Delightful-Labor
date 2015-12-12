<?php
   global $gbDateFormatUS, $gstrFormatDatePicker;
   global $gclsChapterVoc;

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   if ($bNew){
      $strProviderID = '<i>new</i>';
   }else {
      $strProviderID = str_pad($lProviderID, 5, '0', STR_PAD_LEFT);
   }

   $attributes = array('name' => 'frmEditGrant', 'id' => 'frmAddEdit');
   echoT(form_open('grants/provider_add_edit/addEdit/'.$lProviderID, $attributes));

   openBlock(($bNew ? 'Add new ' : 'Edit Existing ').'Funding Provider', '');
   echoT('<table class="enpView">');

      // program ID
   echoT($clsForm->strLabelRow('Provider ID',  $strProviderID, 1));

      //----------------------
      // Granting Organization
      //----------------------
   $clsForm->strStyleExtraLabel = 'width: 100pt; padding-top: 8px;';

   $clsForm->strExtraFieldText = form_error('txtGrantOrg');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Provider/Grantor',  'txtGrantOrg', true,  $formData->txtGrantOrg, 50, 255));

      //----------------------
      // Grant Name
      //----------------------
//   $clsForm->strExtraFieldText = form_error('txtGrantName');
//   echoT($clsForm->strGenericTextEntry('Grant Name',  'txtGrantName', true,  $formData->txtGrantName, 50, 255));

/*
      //-------------------------------
      // Accounting country of Origin
      //-------------------------------
   echoT('
         <tr>
            <td class="enpViewLabel" width="100" style="padding-top: 8px;">
               Accounting Country:
            </td>
            <td class="enpView">'
               .$formData->rdoACO.'
            </td>
         </tr>');
*/
      //-------------------------------
      // Notes
      //-------------------------------
   echoT($clsForm->strNotesEntry('Notes', 'txtNotes', false, $formData->txtNotes, 3, 40));

      //--------------------------
      // Attributed to
      //--------------------------
   $clsForm->strStyleExtraLabel = 'padding-top: 8px;';
   echoT($clsForm->strLabelRow('Attributed To', $strAttribDDL, false));

   echoT('</table>'); closeBlock();

   openBlock('Contact Information', '');
   echoT('<table class="enpView" >');
   $clsForm->strStyleExtraLabel = 'width: 100pt; padding-top: 8px;';

      //------------------------
      // Address
      //------------------------
   echoT($clsForm->strGenericTextEntry('Address 1',   'txtAddr1',   false,  $formData->txtAddr1,    40, 80));
   echoT($clsForm->strGenericTextEntry('Address 2',   'txtAddr2',   false,  $formData->txtAddr2,    40, 80));
   echoT($clsForm->strGenericTextEntry('City',        'txtCity',    false,  $formData->txtCity,     40, 80));
   echoT($clsForm->strGenericTextEntry($gclsChapterVoc->vocState,       'txtState',   false,  $formData->txtState,    40, 80));
   echoT($clsForm->strGenericTextEntry($gclsChapterVoc->vocZip,         'txtZip',     false,  $formData->txtZip,      20, 40));
   echoT($clsForm->strGenericTextEntry('Country',     'txtCountry', false,  $formData->txtCountry,  20, 80));

   $clsForm->strExtraFieldText = form_error('txtEmail');
   echoT($clsForm->strGenericTextEntry('Email',     'txtEmail',   false,  $formData->txtEmail,   40, 120));
   echoT($clsForm->strGenericTextEntry('Web',       'txtWebSite', false,  $formData->txtWebSite, 40, 255));
   echoT($clsForm->strGenericTextEntry('Phone',     'txtPhone',   false,  $formData->txtPhone,   20,  40));
   echoT($clsForm->strGenericTextEntry('Cell',      'txtCell',    false,  $formData->txtCell,    20,  40));

   echoT('</table>'); closeBlock();

      //---------------------------
      // record information
      //---------------------------
   openBlock('Record Info', '');  echoT('<table class="enpView" >');
   $clsForm->strStyleExtraLabel = 'width: 100pt; padding-top: 3px;';
   $clsForm->bNewRec        = $bNew;
   if (!$bNew){
      $clsForm->strStaffCFName = $provider->ucstrFName;
      $clsForm->strStaffCLName = $provider->ucstrLName;
      $clsForm->dteOrigin      = $provider->dteOrigin;
      $clsForm->strStaffLFName = $provider->ulstrFName;
      $clsForm->strStaffLLName = $provider->ulstrLName;
      $clsForm->dteLastUpdate  = $provider->dteLastUpdate;
   }
   echoT($clsForm->strRecCreateModInfo());

   echoT('</table>');
   closeBlock();

   echoT($clsForm->strSubmitEntry('Save', 1, 'cmdSubmit', 'text-align: center; width: 80pt;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');
//   closeBlock();

