<?php
   global $glLabelWidth, $gstrFormName;
   $gstrFormName = 'frmAddEdit';
   $glLabelWidth = 150;

   echoT($strHTMLSummary);

   $clsForm = new generic_form;
   openForm($clsForm, $lParentID, $lCFID, $cForm, $bCusVerification);

   if ($lNumTables > 0){
      userTables($clsForm, $lNumTables, $utables, $errMessages);
   }

   buttonAndClose($clsForm);


   function openForm(&$clsForm, $lParentID, $lCFID, &$cForm, $bCusVerification){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glLabelWidth, $gstrFormName, $gbShowHiddenVerifyError;

      $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
      $clsForm->strTitleClass = 'enpViewTitle';
      $clsForm->strEntryClass = 'enpView';
      $clsForm->bValueEscapeHTML = false;

      $attributes = array('name' => $gstrFormName, 'id' => $gstrFormName);
      $hidden = array('forceSubmit' => '1');
      echoT(form_open('custom_forms/data_entry/addFromCForm/'.$lParentID.'/'.$lCFID, $attributes, $hidden));
      if ($bCusVerification){
         echoT(form_hidden('hVerify', 'true')."\n");
         if ($gbShowHiddenVerifyError) echoT(form_error('hVerify'));
      }

         // top banner
      echoT('<div class="cFormBanner">'.htmlspecialchars($cForm->strBannerTitle).'</div>'."\n");
         // top banner
      echoT('<div class="cFormIntro">'.htmlspecialchars($cForm->strIntro).'</div>'."\n");
   }

   function buttonAndClose(&$clsForm){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glLabelWidth, $gstrFormName;

      echoT($clsForm->strSubmitEntry('Save',
                    2, 'cmdSubmit', 'text-align: center; width: 150px;'));
      echoT(form_close('<br>'));
      echoT('<script type="text/javascript">'. $gstrFormName.'.addEditEntry.focus();</script>');
   }

   function userTables(&$clsForm, $lNumTables, &$utables, &$errMessages){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glLabelWidth;

      if ($lNumTables == 0) return;

      foreach ($utables as $utable){
         $strBLabel = '<font style="font-variant: normal; font-size: 9pt;">';
         if ($utable->bMultiEntry){
            $strBLabel .= ' <i>(adds new record)</i>';
         }else {
            $strBLabel .= ' <i>(updates existing record)</i>';
         }
         $strBLabel .= '</font>';
         openBlock(htmlspecialchars($utable->strUserTableName).$strBLabel, '');  echoT('<table class="enpView" >');
         $clsForm->strStyleExtraLabel = 'width: '.$glLabelWidth.'pt; padding-top: 6px;';

         foreach ($utable->ufields as $ufield){
            showUserField($clsForm, $ufield, $errMessages);
         }
         echoT($clsForm->strLabelRowOneCol('<i>* Required fields</i>', 1));
         echoT('</table>'); closeBlock();
      }
   }

   function showUserField(&$clsForm, &$ufield, &$errMessages){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glLabelWidth, $gstrFormName;

      $enumType = $ufield->enumFieldType;
      $strFN    = $ufield->strFieldNameInternal;
      $clsForm->strExtraFieldText = '';
      $strFieldLabel = htmlspecialchars($ufield->strFieldNameUser);
      $bRequired     = $ufield->bRequired;
      $strValue      = @$ufield->txtValue;

      $clsForm->strExtraFieldText = '';
      if ($ufield->strFieldNotes != ''){
         $clsForm->strExtraFieldText .= '<br><i>'.nl2br(htmlspecialchars($ufield->strFieldNotes)).'</i>';
      }

      $bText = ($enumType==CS_FT_TEXT255) || ($enumType==CS_FT_TEXT80)
            || ($enumType==CS_FT_TEXT20)  || ($enumType==CS_FT_TEXTLONG);
      if ($bText){
         $clsForm->strExtraFieldText = form_error($strFN);
      }

      if (isset($errMessages[$strFN])) $clsForm->strExtraFieldText .= $errMessages[$strFN];
      switch ($enumType){
         case CS_FT_CHECKBOX:
            echoT($clsForm->strGenericCheckEntry($strFieldLabel,
                            $strFN, 'true', false, $ufield->bChecked));
            break;
         case CS_FT_DATE:
            echoT($clsForm->strGenericDatePicker(
                               $strFieldLabel, $strFN, $bRequired,
                               $strValue,    $gstrFormName, $strFN));
            break;

         case CS_FT_TEXT255:
            echoT($clsForm->strGenericTextEntry(
                      $strFieldLabel, $strFN, $bRequired, $strValue,  60, 255));
            break;

         case CS_FT_TEXT80:
            echoT($clsForm->strGenericTextEntry(
                      $strFieldLabel, $strFN, $bRequired, $strValue,  60, 80));
            break;

         case CS_FT_TEXT20:
            echoT($clsForm->strGenericTextEntry(
                      $strFieldLabel, $strFN, $bRequired, $strValue,  20, 20));
            break;

         case CS_FT_HEADING:
            echoT($clsForm->strLabelRowOneCol('<br><br><b>'.$strFieldLabel.'</b>', 1, '', 350));
            break;

         case CS_FT_TEXTLONG:
            echoT($clsForm->strNotesEntry($strFieldLabel, $strFN, $bRequired, $strValue, 3, 56));
            break;

         case CS_FT_INTEGER:
            echoT($clsForm->strGenericTextEntry(
                      $strFieldLabel, $strFN, $bRequired, $strValue,  3, 10));
            break;

         case CS_FT_CURRENCY:
            $clsForm->strExtraFieldText = '';
            $clsForm->strTextBoxPrefix  = $ufield->strCurrencySymbol;
            $clsForm->strExtraFieldText = $ufield->strFlagImg;
            if (isset($errMessages[$strFN])){
               $clsForm->strExtraFieldText .= $errMessages[$strFN];
            }
            echoT($clsForm->strGenericTextEntry(
                      $strFieldLabel, $strFN, $bRequired, $strValue,  8, 14));
            $clsForm->strTextBoxPrefix  =  $clsForm->strExtraFieldText = '';
            break;

         case CS_FT_DDL:
            if ($bRequired) $strFieldLabel .= '*';
            echoT($clsForm->strLabelRow($strFieldLabel, $ufield->strDDL, 1));
            break;

         case CS_FT_DDLMULTI:
            if ($bRequired) $strFieldLabel .= '*';
            echoT($clsForm->strLabelRow($strFieldLabel, $ufield->strDDL, 1));
            break;

         default:
            screamForHelp($enumType.': unexpected field type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }


