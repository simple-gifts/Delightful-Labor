<?php

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   $attributes = array('name' => 'frmVolReg', 'id' => 'frmAddEdit');
   echoT(form_open('volunteers/registration/addEditRegForm/'.$lRegFormID, $attributes));


   if ($bNew){
      $strFormID = '<i>new</i>';
   }else {
      $strFormID = str_pad($lRegFormID, 5, '0', STR_PAD_LEFT);
   }

   regFormInternal($strFormID, $formData, $clsForm, $lNumLogoImages);
   regFormRegistration($formData, $clsForm);
   regFormTopBanner($formData, $clsForm);
   regFormBasicInfo($formData, $clsForm);
   regFormDisclaimer($formData, $clsForm);
   regFormJobSkills($formData, $clsForm, $lNumSkills, $jobSkills);
   regFormPTables($formData, $clsForm, $lNumTables, $userTables);

   echoT('</table>');
   closeBlock();

   echoT($clsForm->strSubmitEntry(($bNew ? ' Add new ' : 'Update ').'Volunteer Registration Form',
                 2, 'cmdSubmit', 'text-align: center;'));
   echoT(form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');

//   closeBlock();

   function regFormInternal($strFormID, &$formData, &$clsForm, $lNumLogoImages){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock('Volunteer Registration Form', '');
      echoT('<table class="enpView" border="0">');

      echoT($clsForm->strLabelRow('form ID',  $strFormID, 1));

         /*------------------------
            Form Name
         ------------------------*/
      $clsForm->strExtraFieldText = form_error('txtRegFormName');
      $clsForm->strID = 'addEditEntry';
      $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 8px;';
      echoT($clsForm->strGenericTextEntry('Form Name',   'txtRegFormName',   true,  $formData->txtRegFormName, 60, 255));

         //--------------------------
         //   Vol. Coordinator Email
         //--------------------------
      $clsForm->strExtraFieldText = form_error('txtVolCoordinatorEmail');
      echoT($clsForm->strGenericTextEntry('Vol. Coordinator Email',   'txtVolCoordinatorEmail',   true,
                   $formData->txtVolCoordinatorEmail, 60, 255));

         /*----------------------
            Description
         ----------------------*/
      echoT($clsForm->strNotesEntry('Description', 'txtDescription', false, $formData->txtDescription, 2, 50));

         /*------------------------
            Use captcha?
         ------------------------*/
      $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 4px;';
      echoT($clsForm->strGenericCheckEntry('Use Captcha?', 'chkCaptchaRequired', 'true', false, $formData->bCaptchaRequired));

         /*------------------------
            Volunteer Group
         ------------------------*/
      $clsForm->strStyleExtraLabel = 'padding-top: 6px;';
      echoT($clsForm->strLabelRow('Volunteer Group', $formData->ddlVolGroup.'<br>'
                               .'<i>Vols who register through this form will be added to the selected group.</i>', 1));
      
         /*------------------------
            Style Sheet
         ------------------------*/
      echoT($clsForm->strLabelRow('Style Sheet', $formData->strDDLCSS.'<br>'
                               .'<i>You can upload custom css files into directory</i> 
                                 <font style="font-family: courier;">./css/vol_reg</font>', 1));
      
      
         /*------------------------
            Logo
         ------------------------*/
      if ($lNumLogoImages == 0){
         $clsForm->strStyleExtraLabel = 'padding-top: 2px;';
         echoT($clsForm->strLabelRow('Logo Image', 'You have no logo images defined for <br>'
                                  .'your organization. You can add organization logos from "Admin/Your Organization" ', 1));
      }else {
         echoT($clsForm->strLabelRow('Logo Image', $formData->rdoLogo
                           .'<br><i>If selected, the logo will appear full size, centered at the top of the registration form.</i>', 1));
      }

      echoT('</table>');
      closeBlock();
   }

   function regFormRegistration(&$formData, &$clsForm){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock('Volunteer Permissions', '');
      echoT('<table class="enpView" border="0">');

      echoT('<tr><td colspan="2"><i>Volunteers who register with this form will have access to:</i></td></tr>');

      $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 4px;';
      echoT($clsForm->strGenericCheckEntry('Contact Information',          'chkPermContactInfo',    'true', false, $formData->bPermContactInfo));
      echoT($clsForm->strGenericCheckEntry('Password Reset',               'chkPermPassReset',      'true', false, $formData->bPermPassReset));
      echoT($clsForm->strGenericCheckEntry('Personal Donation History',    'chkPermGiftHistory',    'true', false, $formData->bPermGiftHistory));
      echoT($clsForm->strGenericCheckEntry('Job Skills',                   'chkPermJobSkills',      'true', false, $formData->bPermJobSkills));
      echoT($clsForm->strGenericCheckEntry('View Volunteer Log',           'chkPermViewVolLog',     'true', false, $formData->bPermViewVolLog));
      echoT($clsForm->strGenericCheckEntry('Ability of Update Vol. Hours', 'chkPermAddVolHrs',      'true', false, $formData->bPermAddVolHrs));
      echoT($clsForm->strGenericCheckEntry('Vol. Shift Signup',            'chkPermVolShiftSignup', 'true', false, $formData->bVolShiftSignup));

      echoT('<tr><td colspan="2"><i>Uncheck all boxes to disable volunteer login.</i></td></tr>');
      
      echoT('</table>');
      closeBlock();
   }

   function regFormTopBanner(&$formData, &$clsForm){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock('Registration Form Top Banner', '');
      echoT('<table class="enpView" border="0">');

         /*------------------------------------
            Banner Text: Organization Name
         --------------------------------------*/
      $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 8px;';
      $clsForm->strExtraFieldText = form_error('txtBannerOrg');
      echoT($clsForm->strGenericTextEntry('Banner Text: Org. Name',   'txtBannerOrg',   false,  $formData->txtBannerOrg, 60, 255));

         /*------------------------------------
            Banner Text: Title
         --------------------------------------*/
      $clsForm->strExtraFieldText = form_error('txtBannerTitle');
      echoT($clsForm->strGenericTextEntry('Banner Text: Title',   'txtBannerTitle',   false,  $formData->txtBannerTitle, 60, 255));

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

      echoT('</table>');
      closeBlock();
   }

   function regFormBasicInfo(&$formData, &$clsForm){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock('Basic Info', '');
      echoT('<table class="enpView" border="0">');

      echoT(strCheckBoxHeaders(false));

         /*------------------------------------
            Standard Fields
         --------------------------------------*/
      echoT(strFieldForReg('First Name',    'chkFNameShow', 'chkFNReq',    true, true, false, '&nbsp;'));
      echoT(strFieldForReg('Last Name',     'chkLNameShow', 'chkLNReq',    true, true, false, '&nbsp;'));
      echoT(strFieldForReg('Address',       'chkAddrShow',  'chkAddrReq',  $formData->bShowAddr,  $formData->bAddrRequired,  true, '&nbsp;',
                                'onChange="clearCheckOnUnCheck(document.frmVolReg.chkAddrShow, document.frmVolReg.chkAddrReq);" ',
                                'onChange="setCheckOnCheck    (document.frmVolReg.chkAddrReq,  document.frmVolReg.chkAddrShow);" '
      ));
      echoT(strFieldForReg('Email',         'chkEmailShow', 'chkEmailReq', $formData->bShowEmail, $formData->bEmailRequired, true, 
                                '&nbsp;',
                                'onChange="clearCheckOnUnCheck(document.frmVolReg.chkEmailShow, document.frmVolReg.chkEmailReq);" ',
                                'onChange="setCheckOnCheck    (document.frmVolReg.chkEmailReq,  document.frmVolReg.chkEmailShow);" '
      ));
      $strEError = form_error('chkEmailShow');
      if ($strEError != ''){
         $clsForm->bAddLabelColon = false;
         echoT($clsForm->strLabelRow('&nbsp;', $strEError, 1));
         $clsForm->bAddLabelColon = true;
      }
      
      echoT(strFieldForReg('Phone',         'chkPhoneShow', 'chkPhoneReq', $formData->bShowPhone, $formData->bPhoneRequired, true, '&nbsp;',
                                'onChange="clearCheckOnUnCheck(document.frmVolReg.chkPhoneShow, document.frmVolReg.chkPhoneReq);" ',
                                'onChange="setCheckOnCheck    (document.frmVolReg.chkPhoneReq,  document.frmVolReg.chkPhoneShow);" '
      ));
      echoT(strFieldForReg('Cell',          'chkCellShow',  'chkCellReq',  $formData->bShowCell,  $formData->bCellRequired,  true, '&nbsp;',
                                'onChange="clearCheckOnUnCheck(document.frmVolReg.chkCellShow, document.frmVolReg.chkCellReq);" ',
                                'onChange="setCheckOnCheck    (document.frmVolReg.chkCellReq,  document.frmVolReg.chkCellShow);" '
      ));
      echoT(strFieldForReg('Date of Birth', 'chkBDayShow',  'chkBDayReq',  $formData->bShowBDay,  $formData->bBDayRequired,  true, '&nbsp;',
                                'onChange="clearCheckOnUnCheck(document.frmVolReg.chkBDayShow, document.frmVolReg.chkBDayReq);" ',
                                'onChange="setCheckOnCheck    (document.frmVolReg.chkBDayReq,  document.frmVolReg.chkBDayShow);" '
      ));


      echoT('</table>');
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
                            $bEnabled, $strExtraText, $strExtraShow='', $strExtraReq='' ){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '
         <tr>
            <td class="enpViewLabel" style="width: 120pt; padding-top: 3px;">'.$strLabel.':
            </td>
            <td class="enpView" style="">
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
                     <td style="width: 80px; text-align: center;">
                        n/a                        
                     </td>';
      }else {
         $strOut .= '
                     <td style="width: 80px; text-align: center;">
                        <input type="checkbox" value="true" name="'.$strFNReq.'" '.$strExtraReq.' '
                           .($bRequired ? 'checked' : '').' '
                           .($bEnabled ? '' : ' disabled="disabled" ').'
                        >
                     </td>';
      }
      $strOut .= '
                     <td style="width: 260px; text-align: left;">'.$strExtraText.'
                     </td>
                  </tr>
               </table>
            </td>
         </tr>';
      return($strOut);
   }

   function regFormDisclaimer(&$formData, &$clsForm){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock('Disclaimer', '');
      echoT('<table class="enpView" border="0">');


         /*------------------------
            Show dispclaimer
         ------------------------*/
      $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 2px;';
      $clsForm->strID = 'chkDisclaimer';
      echoT($clsForm->strGenericCheckEntry('Show disclaimer?', 'chkShowDisclaimer', 'true', false, $formData->bShowDisclaimer));

         /*------------------------------------
            Disclaimer Acknowledgement
         --------------------------------------*/
      $clsForm->strStyleExtraLabel = 'padding-top: 6px;';
      $clsForm->strTxtControlExtra = ' onKeypress="document.getElementById(\'chkDisclaimer\').checked = true;" ';
      $clsForm->strExtraFieldText = form_error('txtDisclaimerAck');
      echoT($clsForm->strGenericTextEntry('Acknowledgement Text',   'txtDisclaimerAck',   false,  $formData->txtDisclaimerAck, 60, 255));
      
      
         /*----------------------
            Disclaimer text
         ----------------------*/
      $clsForm->strExtraFieldText  = form_error('txtDisclaimer');
      $clsForm->strTxtControlExtra = ' onKeypress="document.getElementById(\'chkDisclaimer\').checked = true;" ';
      echoT($clsForm->strNotesEntry('Disclaimer Text', 'txtDisclaimer', false, $formData->txtDisclaimer, 2, 50));

      echoT('</table>');
      closeBlock();
   }

   function regFormJobSkills($formData, $clsForm, $lNumSkills, $jobSkills){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock('Job Skills', '');
      echoT('<table class="enpView" border="0">');

      if ($lNumSkills == 0){
         echoT('<tr><td colspan="3"><i>There are no job skills defined in your database.</i></td></tr></table>');
         closeBlock();
         return;
      }
      echoT('<tr><td colspan="2"><i>Show the following job skills:</i></td></tr>');
      $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 4px;';
      foreach ($jobSkills as $skill){
         echoT($clsForm->strGenericCheckEntry(htmlspecialchars($skill->strSkill),
                         $skill->chkShow, 'true', false, $skill->bShow));
      }

/*
      $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 2px;';
      $clsForm->strID = 'chkDisclaimer';
      echoT($clsForm->strGenericCheckEntry('Show disclaimer?', 'chkShowDisclaimer', 'true', false, $formData->bShowDisclaimer));
*/

      echoT('</table>');
      closeBlock();

   }

   function regFormPTables($formData, $clsForm, $lNumTables, &$userTables){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($lNumTables==0) return;

      foreach ($userTables as $utable){
         $lTableID = $utable->lKeyID;
         openBlock('Personalized Vol Table: <b>'.htmlspecialchars($utable->strUserTableName).'</b>', '');
         echoT('<table class="enpView" border="0">');

         if ($utable->lNumFields==0){
            echoT('<i>No fields defined for this table.</i>');
         }else {
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
                                '', '' ));
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
         closeBlock();
      }
   }




