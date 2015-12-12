<?php

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   openBlock(($bUpdate ? 'Update ' : 'Add new ')
            .strLabelViaContextType($enumContextType, true, false).' '.($bImage ? 'Image' : 'PDF document'), '');

   $attributes = array('name' => 'frmUpload');
   if ($bUpdate){
      echoT(form_open('img_docs/upload_image_doc/edit/'.$lImageDocID, $attributes));
   }else {
      echoT(form_open_multipart('img_docs/upload_image_doc/add/'.$enumContextType.'/'.$enumEntryType.'/'.$lFID, $attributes));
   }

   $clsForm->strStyleExtraLabel = 'padding-top: 9px;';

      //----------------------
      // file selection
      //----------------------
   echoT('<table class="enpView">');
   if ($bUpdate){
      if ($bImage){
         echoT($clsForm->strLabelRow('Image', $strImageTag, 1));
      }
   }else {
      $clsForm->strExtraFieldText = form_error('userfile');
      echoT($clsForm->strLabelRow(($bImage ? 'Image file (jpg/gif/png)' : 'Document (pdf)'),
                  '<input type="file" name="userfile" size="80" />', 1));
   }
      //----------------------
      // date of image/doc
      //----------------------
   echoT(strDatePicker('datepicker1', true));
   $clsForm->strExtraFieldText = form_error('txtDate');
   echoT($clsForm->strGenericDatePicker(
                      ($bImage ? 'Image' : 'Document').' Date', 'txtDate',      true,
                      $formData->txtDate,    'frmUpload', 'datepicker1'));

      //----------------------
      // tags
      //----------------------
   if ($lNumTags == 0){
      $clsForm->strStyleExtraValue = 'padding-top: 8pt;';
      echoT($clsForm->strLabelRow('Tags',
                  '<i>There are no <b>'.$strTagLabel.'</b> tags defined. Contact your system administrator to add tags.', 1));
      $clsForm->strStyleExtraValue = '';
   }else {
      echoT($clsForm->strLabelRow('Tags',
         imgDocTags\strImgDocTagsDDL('ddlTags', 5, true, $lNumTags, $tags), 1));
   }
   
      //----------------------
      // caption
      //----------------------
   $clsForm->strStyleExtraLabel = 'padding-top: 6px;';
   $clsForm->strExtraFieldText = form_error('txtCaption');
   echoT($clsForm->strGenericTextEntry(($bImage ? 'Caption' : 'Document Title'),  'txtCaption', false,  $formData->txtCaption, 60, 255));


      //----------------------
      // description
      //----------------------
   echoT($clsForm->strNotesEntry('Description', 'txtDescription', false, $formData->txtDescription, 5, 60));

      //----------------------
      // profile image?
      //----------------------
   if ($bImage){
      echoT($clsForm->strGenericCheckEntry('Profile image?', 'chkProfile', 'TRUE', false, $formData->bProfile));
   }

      //----------------------
      // submit
      //----------------------
//   echoT($clsForm->strSubmitEntry(($bUpdate ? 'Update' : 'Upload file'), 1, 'cmdSubmit', 'text-align: left;'));
   echoT('
         <tr>
            <td class="enpView" style=" padding-top: 10px;" colspan="1">
            <input type="submit" name="cmdSubmit" value="'.($bUpdate ? 'Update' : 'Upload file').'" style="text-align: left;"
                  class="btn"
                  onmouseover="this.className=\'btn btnhov\'"
                  onmouseout="this.className=\'btn\'">
            </td>
         </tr>');


   echoT('</table>'.form_close('<br>'));

   echoT('</table>'); closeBlock();
