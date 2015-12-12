<?php
   global $gbDateFormatUS, $gstrFormatDatePicker;

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   if ($bNew){
      $strGrantID = '<i>new</i>';
   }else {
      $strGrantID = str_pad($lGrantID, 5, '0', STR_PAD_LEFT);
   }

   $attributes = array('name' => 'frmEditGrant', 'id' => 'frmAddEdit');
   echoT(form_open('grants/grant_add_edit/addEdit/'.$lProviderID.'/'.$lGrantID, $attributes));

   openBlock(($bNew ? 'Add new ' : 'Edit Existing ').'Grant', '');
   echoT('<table class="enpView">');

      //----------------------
      // Grant Name
      //----------------------
   $clsForm->strExtraFieldText = form_error('txtGrantName');
   echoT($clsForm->strGenericTextEntry('Grant Name',  'txtGrantName', true,  $formData->txtGrantName, 50, 255));

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
   
   
      //---------------------------
      // record information
      //---------------------------
   openBlock('Record Info', '');  echoT('<table class="enpView" >');
   $clsForm->strStyleExtraLabel = 'width: 100pt; padding-top: 3px;';
   $clsForm->bNewRec        = $bNew;
   if (!$bNew){
      $clsForm->strStaffCFName = $grant->ucstrFName;
      $clsForm->strStaffCLName = $grant->ucstrLName;
      $clsForm->dteOrigin      = $grant->dteOrigin;
      $clsForm->strStaffLFName = $grant->ulstrFName;
      $clsForm->strStaffLLName = $grant->ulstrLName;
      $clsForm->dteLastUpdate  = $grant->dteLastUpdate;
   }
   echoT($clsForm->strRecCreateModInfo());

   echoT('</table>');
   closeBlock();

   echoT($clsForm->strSubmitEntry('Save', 1, 'cmdSubmit', 'text-align: center; width: 80pt;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
