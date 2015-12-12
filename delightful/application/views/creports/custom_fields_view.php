<?php
   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   if ($bReadOnly){
      echoT('<i><span style="font-size: 12pt;">Fields selected for reporting:</span></i><br>');
   }else {
      echoT(form_open('creports/custom_fields/saveFields/'.$lReportID));
      echoT('<i><span style="font-size: 12pt;">Select the fields for your report, then click "Save".</span></i><br>');
   }

   $idx = 0;
   $attributes = new stdClass;
   $attributes->lTableWidth      = 900;
   $attributes->lUnderscoreWidth = 600;
   $attributes->bStartOpen       = true;
   $attributes->bAddTopBreak     = true;

   foreach ($tables as $table){
      $attributes->bCloseDiv  = false;
      $attributes->divID      = $strDiv = 'groupDiv'.$idx;
      $attributes->divImageID = 'groupDivImg'.$idx;

      if ($table->lTableID <= 0){
         $strTable = $table->name;
      }else {
         $strTable = '<b>['.$table->strAttachLabel.']</b> '.htmlspecialchars($table->name);
      }

      if ($table->tType == ''){
         $strTType = '';
      }else {
         $strTType = ' <span style="font-size: 9pt;"><i>('.$table->tType.')</i></span>';
      }

      if ($bReadOnly){
         $strCheckAllNone = '';
      }else {
         $strCheckAllNone = '&nbsp;&nbsp;'
              .'<input type="button" value="Check All" onclick="checkByParent(\''.$strDiv.'\', true);">&nbsp;
                <input type="button" value="Clear All" onclick="checkByParent(\''.$strDiv.'\', false);">&nbsp;';
      }

      openBlock($strTable.$strTType.$strCheckAllNone, '', $attributes);
      if (!$bReadOnly) $attributes->bStartOpen = false;

      showCFields($table, $idx, $bReadOnly);

      $attributes->bCloseDiv = true;
      closeBlock($attributes);
      ++$idx;
   }

   if (!$bReadOnly){
      echoT('<br><br>'.$clsForm->strSubmitEntry('Save', 1, 'cmdSubmit', 'text-align: center; width: 70pt;'));
      echoT(form_close('<br><br>'));
   }


   function showCFields(&$table, $idx, $bReadOnly){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echo('<table>');
      $lTableID = $table->lTableID;
      foreach ($table->fields as $field){
         if (!( ($field->enumType == 'Heading')||($field->enumType== 'Log')) ){
            if ($field->fTypeLabel == '#error#'){
               $strFType = '';
            }else {
               $strFType = '&nbsp;<i>('.$field->fTypeLabel.')</i>';
            }

            if ($bReadOnly){
               if ($field->bChecked){
                  echoT(
                     '<tr>
                        <td>&nbsp;</td>
                        <td>'
                           .htmlspecialchars($field->publicName).$strFType.'
                        </td>
                     </tr>');
               }
            }else {
               $lFieldID = $field->lFieldID;
                  // exclude unsupported field types
               if (bSupportedCReportFieldType($field->enumType)){
                  echoT(
                     '<tr>
                        <td>
                           <input type="checkbox" name="chkFields[]"
                              '.($field->bChecked ? 'checked' : '').'
                              value="'.$lTableID.'|'.$lFieldID.'|'.$field->internalName.'">
                        </td>
                        <td>'
                           .htmlspecialchars($field->publicName).$strFType.'
                        </td>
                     </tr>');
               }
            }
         }
      }

      echoT('
         </table>
         </div><br>');

   }
