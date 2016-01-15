<?php
   echoT('<br>'.form_open('admin/import/pTableImportPrep/'.CENUM_CONTEXT_PTABLE.'/'.$lTableID));
   $cform = new generic_form;
   echoT($cform->strSubmitButton('Continue to Import', 'subNext', ''));
   echoT(form_close('<br>'));

   echoT('
      <table class="dlView">
         <tr>
            <td colspan="3" class="dlViewTitle">
               Personalized Table Import<br>
               ['.strXlateContext($userTable->enumTType, true, false).'] '.htmlspecialchars($userTable->strUserTableName).'
            </td>
         </tr>');
   echoT('
         <tr>
            <td class="dlViewLabel">
               Field / Column Title
            </td>
            <td class="dlViewLabel">
               Type
            </td>
            <td class="dlViewLabel">
               Required?
            </td>
         </tr>');

   echoT('
      <tr>
         <td class="dlView">'
            .$userTable->enumTType.' ID
         </td>
         <td class="dlView">
            Number
         </td>
         <td class="dlView" style="text-align: center;">
            <b>Yes</b>
         </td>
      </tr>');
         
         
   foreach ($fields as $field){
      if ($field->enumFieldType != CS_FT_HEADING){
         echoT('
            <tr>
               <td class="dlView">'
                  .htmlspecialchars($field->pff_strFieldNameUser).'
               </td>
               <td class="dlView">'
                  .$field->strFieldTypeLabel.$field->strLabelExtra.'
               </td>
               <td class="dlView" style="text-align: center;">'
                  .($field->pff_bRequired ? '<b>Yes</b>' : 'No').'
               </td>
            </tr>');
      }
   }

   echoT('</table>');

