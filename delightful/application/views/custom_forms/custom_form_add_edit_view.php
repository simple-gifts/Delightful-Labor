<?php

   echoT('This feature allows you to create custom forms from personalized tables<br><br>');
   if ($lNumTables == 0){
      echoT('<i><span style="color: red;">Please add one or more '.$contextLabel->strTypeC.' personalized tables
                   (with at least one field) before creating a custom form.<br><br>');
      return;
   }

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   $attributes = array('name' => 'frmVolReg', 'id' => 'frmAddEdit');
   echoT(form_open('custom_forms/custom_form_add_edit/addEditCForm/'.$lCFID.'/'.$enumType, $attributes));


   if ($bNew){
      $strFormID = '<i>new</i>';
   }else {
      $strFormID = str_pad($lCFID, 5, '0', STR_PAD_LEFT);
   }

   customFormInternal ($strFormID, $formData, $clsForm, $contextLabel);
   customFormTopBanner($formData, $clsForm);
   customFormPTables  ($formData, $clsForm, $lNumTables, $userTables);

   //echoT('</table>');
   //closeBlock();

   echoT($clsForm->strSubmitEntry(($bNew ? ' Add new ' : 'Update ').'custom '.$enumType.' form',
                 2, 'cmdSubmit', 'text-align: center;'));
   echoT(form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');


   function customFormInternal($strFormID, &$formData, &$clsForm, &$contextLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock('Form ', '');
      echoT('<table class="enpView" border="0">');

      echoT($clsForm->strLabelRow('form ID',  $strFormID, 1));

         /*------------------------
            Form Name
         ------------------------*/
      $clsForm->strExtraFieldText = form_error('txtCustomFormName');
      $clsForm->strID = 'addEditEntry';
      $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 8px;';
      echoT($clsForm->strGenericTextEntry('Form Name',   'txtCustomFormName',   true,  $formData->txtCustomFormName, 60, 255));

         /*----------------------
            Description
         ----------------------*/
      echoT($clsForm->strNotesEntry('Description', 'txtDescription', false, $formData->txtDescription, 2, 50));

         /*------------------------
            Parent Group
         ------------------------*/
      if ($formData->lNumParentGroups > 0){
         $clsForm->strStyleExtraLabel = 'padding-top: 6px;';
         echoT($clsForm->strLabelRow($contextLabel->strTypeC.' Group', $formData->ddlParentGroup.'<br>'
                               .'<i>New '.$contextLabel->strTypeP.' who are entered through this form will be added to the selected group.</i>', 1));
      }

         /*----------------------
            Validation file
         ----------------------*/
      $clsForm->strExtraFieldText = '<br>'
                     .'<i>You can optionally provide your own software to manage complex custom form validation.<br>'
                     .'The validation software is in the form of a codeIgniter helper file. Save your file<br>'
                     .'in directory </i><font style="font-family: courier;">
                     application/helpers/custom_verification</font><i>
                     and your <br> file name must end with
                         </i><font style="font-family: courier;">"_helper.php"</font><i>. Please see the user\'s guide for more details.<br>'
                     .form_error('txtVerificationModule');
      echoT($clsForm->strGenericTextEntry('Validation File', 'txtVerificationModule', false, $formData->txtVerificationModule, 60, 255));

      $clsForm->strExtraFieldText = '<br>'
                     .'<i>If you specify a validation file, this is where you specify the name of your function <br>'
                     .'to call. Your routine returns false if the form data is not valid.<br>'
                     .form_error('txtVModEntryPoint');
      echoT($clsForm->strGenericTextEntry('Entry Point', 'txtVModEntryPoint', false, $formData->txtVModEntryPoint, 60, 255));


      echoT('</table> <!-- customFormInternal -->'."\n");
      closeBlock();
   }

   function customFormTopBanner(&$formData, &$clsForm){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock('Form Top Banner', '');
      echoT('<table class="enpView" border="0">');

         /*------------------------------------
            Banner Text: Organization Name
      $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 8px;';
      $clsForm->strExtraFieldText = form_error('txtBannerOrg');
      echoT($clsForm->strGenericTextEntry('Banner Text: Org. Name',   'txtBannerOrg',   false,  $formData->txtBannerOrg, 60, 255));
         --------------------------------------*/

         /*------------------------------------
            Banner Text: Title
         --------------------------------------*/
      $clsForm->strExtraFieldText = form_error('txtBannerTitle');
      echoT($clsForm->strGenericTextEntry('Banner / Title',   'txtBannerTitle',   true,  $formData->txtBannerTitle, 60, 255));

         /*----------------------
            Top Intro Text
         ----------------------*/
      $clsForm->strExtraFieldText = form_error('txtIntro');
      echoT($clsForm->strNotesEntry('Top Intro Text', 'txtIntro', true, $formData->txtIntro, 2, 50));

         /*----------------------
            Submission Text
         ----------------------*/
      $clsForm->strExtraFieldText = form_error('txtSubmissionText');
      echoT($clsForm->strNotesEntry('Text Shown After Submission', 'txtSubmissionText', true, $formData->txtSubmissionText, 2, 50));

      echoT('</table> <!-- customFormTopBanner -->'."\n");
      closeBlock();
   }

   function strCheckBoxHeaders($bShowType){
      $strOut = '
         <tr>
            <td class="enpViewLabel" style="width: 120pt; padding-top: 2px;">&nbsp;
            </td>
            <td class="enpView" style="">
               <table cellpadding="0" cellspacing="0">
                  <tr>
                     <td style="width: 60px; text-align: center;">
                        <u>Show?</u>
                     </td>
                     <td style="width: 80px; text-align: center;">
                        <u>Required?</u>
                     </td>';
      if ($bShowType){
         $strOut .= '
                     <td style="text-align: left;">
                        <u>Field Type</u>
                     </td>';

      }
      $strOut .= '
                  </tr>
               </table>
            </td>
         </tr>';
      return($strOut);
   }

   function strFieldForReg($strLabel, $strFNShow, $strFNReq, $bShow, $bRequired,
                            $bEnabled, $strExtraText, $strExtraShow='',
                            $strExtraReq='', $bHeading = false ){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strBG = $bHeading ? 'background-color: #eee;' : '';
      $strOut = '
         <tr>
            <td class="enpViewLabel" style="width: 200pt; '.$strBG.' padding-top: 3px;">'.$strLabel.':
            </td>
            <td class="enpView" style=" '.$strBG.' ">
               <table cellpadding="0" cellspacing="0">
                  <tr>
                     <td style="width: 60px; text-align: center;">
                        <input type="checkbox" value="true" name="'.$strFNShow.'" '.$strExtraShow.' '
                           .($bShow ? 'checked' : '').' '
                           .($bEnabled ? '' : ' disabled="disabled" ').'
                        >
                     </td>';

      if (is_null($strFNReq)){
         $strOut .= '
                     <td style="width: 80px;  '.$strBG.' text-align: center;">
                        n/a
                     </td>';
      }else {
         $strOut .= '
                     <td style="width: 80px;  '.$strBG.' text-align: center;">
                        <input type="checkbox" value="true" name="'.$strFNReq.'" '.$strExtraReq.' '
                           .($bRequired ? 'checked' : '').' '
                           .($bEnabled ? '' : ' disabled="disabled" ').'
                        >
                     </td>';
      }
      $strOut .= '
                     <td style="width: 260px;  '.$strBG.' text-align: left;">'.$strExtraText.'
                     </td>
                  </tr>
               </table>
            </td>
         </tr>';
      return($strOut);
   }

   function customFormPTables($formData, $clsForm, $lNumTables, &$userTables){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($lNumTables==0) return;

      $attributes = new stdClass;
      $attributes->lTableWidth  = null;
      $attributes->lMarginLeft  = 0;

      $attributesClose = new stdClass;
      $attributesClose->bCloseDiv = true;

      foreach ($userTables as $utable){
         $lTableID = $utable->lKeyID;
         $attributes->divID        = $strDivID = 'ut_'.$lTableID.'Div';
         $attributes->divImageID   = 'ut_'.$lTableID.'ImgDiv';
         openBlock('Personalized Table: <b>'.htmlspecialchars($utable->strUserTableName).'</b>', '', $attributes);
         echoT('<table class="enpView" border="0">');

         if ($utable->lNumFields==0){
            echoT('<i>No fields defined for this table.</i>');
         }else {
            echoT('
               <i>To exclude this table from your form, uncheck all the boxes.</i><br>
               <input type="button" value="Check All"   onclick="checkByParent(\''.$strDivID.'\', true);">
               <input type="button" value="Uncheck All" onclick="checkByParent(\''.$strDivID.'\', false);"><br><br>');

            echoT(strCheckBoxHeaders(true));

            foreach ($utable->fields as $field){
               if ($field->enumFieldType!=CS_FT_LOG){
                  $lFieldID = $field->pff_lKeyID;
                  $enumFieldType = $field->enumFieldType;
                  if ($enumFieldType==CS_FT_CHECKBOX || $enumFieldType==CS_FT_HEADING){
                     echoT(strFieldForReg(htmlspecialchars($field->pff_strFieldNameUser),
                                $field->strFNShow, null,
                                $field->bShow, null, true,
                                '&nbsp;'.$field->strFieldTypeLabel,
                                '', '', $enumFieldType==CS_FT_HEADING));
                  }else {
                     echoT(strFieldForReg(htmlspecialchars($field->pff_strFieldNameUser),
                                $field->strFNShow, $field->strFNRequired,
                                $field->bShow, $field->bRequired, true,
                                '&nbsp;'.$field->strFieldTypeLabel,
                                'onChange="clearCheckOnUnCheck(document.frmVolReg.'.$field->strFNShow.    ', document.frmVolReg.'.$field->strFNRequired.');" ',
                                'onChange="setCheckOnCheck    (document.frmVolReg.'.$field->strFNRequired.', document.frmVolReg.'.$field->strFNShow.');" '
                                ));
                  }
               }
            }
         }

         echoT('</table>');
         closeBlock($attributesClose);
         echoT('<br> <!-- personalized table -->'."\n");
      }
   }




