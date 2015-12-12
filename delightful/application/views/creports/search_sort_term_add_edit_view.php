   <?php

   echoT(form_open($strFormLink));

   echoT($strLabel);

   if ($bShowAscending){
      echoT('Sort by term '
         .'<input type="radio" name="rdoAscend" value="true" id="rdoAscendTrue" checked>'
         .'Ascending <span style="font-size: 8pt;">(A-Z)</span> '
         .'<input type="radio" name="rdoAscend" value="false" id="rdoAscendFalse">'
         .'Descending <span style="font-size: 8pt;">(Z-A)</span><br>');
   }

   echoT('
      <div id="scrollCB" style="height:230px;   width:400px; overflow:auto;
                border: 1px solid black; margin-bottom: 4px;">'."\n\n");

   echoT('<table>');
   $idx = 0;
   $strPaddingTop = '';

   foreach ($tables as $table){
      if ($table->lTableID <= 0){
         $strTable = $table->name;
      }else {
         $strTable = '<b>['.$table->strAttachLabel.']</b> '.htmlspecialchars($table->name);
      }

      echoT('
         <tr style="margin-top: 20pt;">
            <td colspan="3" style="font-size: 11pt; padding-left: 10px; '.$strPaddingTop.'">
               <b><u>'.$strTable.'</u></b>
            </td>
         </tr>');
      fieldSelect($idx, $table);
      $strPaddingTop =  'padding-top: 14pt;';
   }

   echoT('</table><br></div>');
   echoT('
             <input type="submit" name="cmdSubmit" value="Select Field"
                 onclick="this.disabled=1; this.form.submit();"
                 style=""
                 class="btn"
                    onmouseover="this.className=\'btn btnhov\'"
                    onmouseout="this.className=\'btn\'">');

   echoT(form_close('<br>'));

   function fieldSelect(&$idx, &$table){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      foreach ($table->fields as $field){
         if (!bSupportedCReportFieldType($field->enumType)){
            if ($field->enumType == CS_FT_HEADING){
               echoT('
                  <tr>
                     <td style="width: 20px;">&nbsp;</td>
                     <td colspan="2"><b>'
                        .$field->publicName.'</b>
                     </td>
                  </tr>');
            }else {
               echoT('
                  <tr>
                     <td style="width: 20px;">&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>'
                        .$field->publicName.' <i><span style="font-size: 8.5pt;">(not available for searching/sorting)</span></i>
                     </td>
                  </tr>');
            }
         }else {
            if ($field->enumType == CS_FT_CURRENCY){
               $strExtra = '&nbsp;'.$field->ACO->strFlagImg;
            }else {
               $strExtra = '';
            }
            echoT('
               <tr>
                  <td style="width: 20px;">&nbsp;</td>
                  <td >
                     <input type="radio" name="rdoField" '
                           .($idx==0 ? 'checked' : '').'
                            value="'.$field->internalName.'">
                  </td>
                  <td>'
                     .$field->publicName.' <i>('.$field->fTypeLabel.$strExtra.')</i>
                  </td>
               </tr>');
            ++$idx;
         }
      }
   }
