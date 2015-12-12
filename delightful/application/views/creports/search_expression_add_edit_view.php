<?php
   $hidden = array('internalFieldID' => $strField,
                   'tableName'       => $tableName,
                   'lFieldID'        => $lFieldID,
                   'lFieldIDX'       => $lFieldIDX,
                   'lTableID'        => $lTableID,
                   'lTableIDX'       => $lTableIDX);

   $clsForm = new generic_form;

   $attributes = array('name' => 'fmrSearchTerm', 'id' => 'searchTerm');
   echoT(form_open('creports/search_terms/term_selected/'.$lReportID.'/'
                     .$strField.'/'.$lTermID, $attributes, $hidden));

   openBlock(($bNew ? 'Add new ' : 'Edit ').'Search Term', '');

      // comparison drop-down (or label for checkboxes)
   if ($field->enumType==CS_FT_CHECKBOX){
      $strCompatorDDL = ' <b><i>IS SET TO</i></b> ';
   }else {
      $strCompatorDDL  = '<select name="ddlCompare">';
      foreach ($term->ddlCompare as $dcomp){
         $strCompatorDDL .=
            '<option value="'.$dcomp->optVal.'" '.($dcomp->bSel ? 'SELECTED' : '').'>'
                .$dcomp->name.'</option>'."\n";
      }
      $strCompatorDDL .= '</select>';
   }

   $strComparisionVal = '';
   if (isset($term->ddlSelectValue_1)){
      $strComparisionVal .= '
           <select name="ddlCompValue1" style="">';

      foreach ($term->ddlSelectValue_1 as $selVal){
         $strComparisionVal .=
                    '<option value="'.$selVal->optVal.'" '.($selVal->bSel ? 'SELECTED' : '').'>'
                    .$selVal->name.'</option>'."\n";
      }

      $strComparisionVal .= '</select>';

   }else {
      $strComparisionVal = 'TBD';
      switch ($term->enumFieldType) {

         case CS_FT_CHECKBOX:
            $strComparisionVal = '';
            break;

         case CS_FT_ID:
         case CS_FT_INTEGER:
            $strComparisionVal =
               '<input type="text" name="txtCompValue" value="'.$term->lCompVal.'"
                     style="width: 50px; text-align: right;">'
                     .form_error('txtCompValue');
            break;

         case CS_FT_CURRENCY:
            $strComparisionVal =
               '<input type="text" name="txtCompValue" value="'.number_format($term->curCompVal, 2).'"
                     style="width: 70px; text-align: right;">&nbsp;'.$term->ACO->strFlagImg
                     .form_error('txtCompValue');
            break;

         case CS_FT_DATE:
         //----------------------
         // Date
         //----------------------
            $strComparisionVal = strDatePicker('datepicker1', true)
               .$clsForm->strGenericDatePicker(
                         'Date', 'txtDate',      true,
                         $term->strDteCompVal,
                         'fmrSearchTerm', 'datepicker1',
                         '', true)
               .form_error('txtDate');
            break;

         case CS_FT_TEXTLONG:
         case CS_FT_TEXT255:
         case CS_FT_TEXT80:
         case CS_FT_TEXT20:
         case CS_FT_TEXT:
            $strComparisionVal =
               '<input type="text" name="txtCompValue" value="'.$term->strCompVal.'"
                     style="width: 150px;">'
                     .form_error('txtCompValue');
            break;

         case CS_FT_DDLMULTI:
         case CS_FT_DDL:
            $strComparisionVal = strLoadDDLCompare($term).form_error('ddlCompareTo');
            break;

         case CS_FT_DDL_SPECIAL:
            $strComparisionVal = strLoadDDLSpecialCompare($term).form_error('ddlCompareTo');
            break;

         case CS_FT_DATETIME:
         case CS_FT_HEADING:
         case CS_FT_LOG:
//         case CS_FT_EMAIL:
//         case CS_FT_HLINK:
         default:
            screamForHelp('INVALID FIELD TYPE '.$term->enumFieldType.', error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__);
            break;
      }

   }

   echoT('<div style="vertical-align: middle;">'
       .'<b>'.$tableName.'&nbsp;&nbsp;</b>'
       .htmlspecialchars($field->publicName).'&nbsp;&nbsp;'.$strCompatorDDL.'&nbsp;&nbsp;'.$strComparisionVal
       .'</div><br>');

   echoT('
             <input type="submit" name="cmdSubmit" value="Save search term"
                 style=""
                 onclick="this.disabled=1; this.form.submit();"
                 class="btn"
                    onmouseover="this.className=\'btn btnhov\'"
                    onmouseout="this.className=\'btn\'">');

   echoT(form_close('<br>'));



   closeBlock();

   function strLoadDDLSpecialCompare($term){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($term->lNumDDLEntries > 0){
         $strOut =
            '<select name="ddlCompareTo">
               <option value="-1">&nbsp;</option>'."\n";
         foreach ($term->ddlEntries as $entry){
            $strOut .= '<option value="'.$entry->key.'" '
                      .($term->strCompVal == $entry->key ? 'SELECTED' : '').'>'
                      .htmlspecialchars($entry->value).'</option>'."\n";
         }
         $strOut .= '</select>';
      }else {
         $strOut =
            '<select name="ddlCompareTo">
               <option value="-1">(no selection available)</option>
             </select>';
      }
      return($strOut);
   }

   function strLoadDDLCompare($term){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumOpts = 0;
      if ($term->lNumDDLEntries > 0){
         $strOut =
            '<select name="ddlCompareTo">
               <option value="-1">&nbsp;</option>'."\n";
         foreach ($term->ddlEntries as $entry){
            if (!$entry->bRetired){
               $strOut .= '<option value="'.$entry->lKeyID.'" '
                         .($term->lCompVal == $entry->lKeyID ? 'SELECTED' : '').'>'
                         .htmlspecialchars($entry->strEntry).'</option>'."\n";
               ++$lNumOpts;
            }
         }
         $strOut .= '</select>';
      }

      if ($lNumOpts == 0){
         $strOut =
            '<select name="ddlCompareTo">
               <option value="-1">(no selection available)</option>
             </select>';
      }
      return($strOut);
   }










