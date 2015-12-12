<?php

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   $attributes = array('name' => 'frmUpload');
   echoT(form_open_multipart('admin/import/importPrep/'.$enumImportType, $attributes));


   openBlock('Import '.strUpFirst($enumImportType).' Records', '');
   echoT('<table class="enpView">');

   $clsForm->strExtraFieldText = form_error('userfile');
   $clsForm->strStyleExtraLabel = 'padding-top: 10px;';
   echoT($clsForm->strLabelRow('Import file (.csv)',
                       '<input type="file" name="userfile" size="80" />', 1));

      // add imported records into one or more groups
   if ($ddlGroups != ''){
      echoT($clsForm->strLabelRow('Add imported records<br>to group(s)',
                       $ddlGroups, 1));   
   }

      //----------------------
      // submit
      // for some reason, using the technique to disable the submit button
      // causes the file name not to be passed back to the controller,
      // and the error "no file selected" is presented.
      //----------------------
//   echoT($clsForm->strSubmitEntry('Import File', 1, 'cmdSubmit', 'text-align: left;'));
//                <!--  onclick=" this.disabled=1;  this.form.submit();" -->
   echoT('
         <tr>
            <td class="enpView" style=" padding-top: 10px;" colspan="1">
            <input type="submit" name="cmdSubmit" value="Import File" style="text-align: left;"
                  class="btn"
                  onmouseover="this.className=\'btn btnhov\'"
                  onmouseout="this.className=\'btn\'">
            </td>
         </tr>');


   echoT('</table>'.form_close('<br>'));

   echoT('</table>'); closeBlock();
