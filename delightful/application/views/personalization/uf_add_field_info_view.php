<?php

   echoT($entrySummary.'<br>');
   
   $attributes = array('name' => 'frmLoc', 'id' => 'frmAddEdit');
   echoT(form_open('admin/uf_tables_add_edit/addField2a/'.$lTableID.'/'.$lFieldID.'/'.$enumFieldType,
                  $attributes));

   $clsGF = new generic_form;
   $clsGF->bValueEscapeHTML = false;
   
   echoT('<table class="enpRptC">');
   echoT($clsGF->strTitleRow(($bNew ? 'Add a new ' : 'Edit a').' field in table <i>"'
                  .$strTableLabel.'"</i>', 2, ''));
   echoT($clsGF->strLabelRow('Field type', $strFieldTypeLabel, 1));

   $clsGF->strStyleExtraLabel = 'padding-top: 8px;';
   
      // Field Name
   $clsGF->strExtraFieldText = form_error('txtFieldName');
   $clsGF->strID = 'addEditEntry';
   echoT($clsGF->strGenericTextEntry('Field name', 'txtFieldName', true,
                                       $strFieldNameUser, 40, 80));
                                       
      // Field Notes
   $clsGF->strExtraFieldText = '<br><i>Extra info to accompany field on user input forms</i>';
   echoT($clsGF->strNotesEntry('Field Notes', 'txtFieldNotes', false, $strFieldNotes, 3, 38)); 

      // prefill?
   if ($bMultiEntry && ($enumFieldType!=CS_FT_HEADING) && ($enumFieldType!=CS_FT_LOG)){
      $clsGF->strID = 'addEditEntry';
      $clsGF->strExtraFieldText = '<br><i>If checked, when adding a new record this field<br>
                    will be prefilled with the most recent previous entry</i>';
      echoT($clsGF->strGenericCheckEntry('Pre-fill field?', 'chkPrefill', 'true', false, $opts->bPrefill));
   
   }else {
      echoT(form_hidden('chkPrefill', 'false'));
   }   

   showExtraOpts($clsGF, $enumFieldType, $opts);

      // Hide field
   $clsGF->strStyleExtraLabel = 'padding-top: 4px;';
   $clsGF->strExtraFieldText  = '<i>By hiding a field, it will not be visible to the user.<br>
                                  You can restore it at a later time without loss of data.</i>';
   echoT($clsGF->strGenericCheckEntry('Hide this field?', 'chkHidden', 'true', false, $opts->bHidden));
   
   if ($bShowRequired){
      $clsGF->strStyleExtraLabel = 'padding-top: 4px;';
      echoT($clsGF->strGenericCheckEntry('Required?', 'chkRequired', 'true', false, $opts->bRequired));
   }

   echoT($clsGF->strSubmitEntry('Submit', 2, 'cmdSubmit', ''));
   echoT('</table>'.form_close('<br><br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');

   function showExtraOpts($clsGF, $enumFieldType, $opts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      switch ($enumFieldType){
         case CS_FT_CHECKBOX:
            showCheckboxOpts($opts->bCheckDef);
            break;

         case CS_FT_DATE:
         case CS_FT_DATETIME:
            showDateTimeOpts($opts);
            break;

         case CS_FT_TEXT255:
            showTextOpts($clsGF, $opts->strTxtDef, 255);
            break;
         case CS_FT_TEXT80:
            showTextOpts($clsGF, $opts->strTxtDef, 80);
            break;
         case CS_FT_TEXT20:
            showTextOpts($clsGF, $opts->strTxtDef, 20);
            break;
         case CS_FT_TEXTLONG:
            break;
         case CS_FT_CLIENTID:
            break;

         case CS_FT_HEADING:
            break;

         case CS_FT_INTEGER:
            showIntegerOpts($opts->lDef);
            break;

         case CS_FT_CURRENCY:
            showCurrencyOpts($opts->curDef, $opts->lCurrencyACO);
            break;

         case CS_FT_DDL:
         case CS_FT_DDLMULTI:
            showDDLOpts($opts);
            break;

         case CS_FT_LOG:
            showLogOpts($opts);
            break;

         default:
            screamForHelp($enumFieldType.': Invalid field type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function showTextOpts($clsGF, $strDefText, $lMax){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($lMax > 0) {
         $strMax = ' maxlength="'.$lMax.'" ';
      }else {
         $strMax = '';
      }

      echoT($clsGF->strGenericTextEntry('Default Text', 'txtDefaultText', false,
                                       $strDefText, 40, $lMax));
   }

   function showCheckboxOpts($bDefYes){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('
         <tr>
           <td class="enpRptLabel">
              Default:
           </td>
           <td class="enpRpt">
              <input type="radio" name="rdoDefaultYN" value="YES" '.($bDefYes ? 'checked' : '').'> Checked
              <input type="radio" name="rdoDefaultYN" value="NO"  '.($bDefYes ? '' : 'checked').'> Unchecked'
              .form_error('rdoDefaultYN').'
           </td>
        </tr>');
   }

   function showDateTimeOpts($opts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

   }

   function showDDLOpts($opts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

   }

   function showLogOpts($opts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

   }

   function showIntegerOpts($lDef){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('
         <tr>
           <td class="enpRptLabel">
              Default:
           </td>
           <td class="enpRpt">
              <input type="text" name="txtDefaultInt"
                  value="'.$lDef.'"
                  size="6">'
            .form_error('txtDefaultInt').'

           </td>
        </tr>');
   }

   function showCurrencyOpts($curDef, $lCurrencyACO){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsACO = new madmin_aco();
      if (is_numeric($curDef)){
         $strCur = number_format($curDef, 2);
      }else {
         $strCur = xss_clean(trim($curDef));
      }
      echoT('
         <tr>
           <td class="enpRptLabel">
              Default:
           </td>
           <td class="enpRpt">
              <input type="text" name="txtDefaultCur"
                  value="'.$strCur.'"
                  size="6">'
            .form_error('txtDefaultCur').'
           </td>
        </tr>');
      echoT('
         <tr>
           <td class="enpRptLabel">
              Accounting Country:
           </td>
           <td class="enpRpt">'.$clsACO->strACO_Radios($lCurrencyACO, 'rdoACO')
            .form_error('rdoACO').'
           </td>
        </tr>');
   }


?>