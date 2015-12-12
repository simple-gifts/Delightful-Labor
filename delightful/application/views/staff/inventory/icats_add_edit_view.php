<?php

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   if ($bNew){
      $strCatID = '<i>new</i>';
   }else {
      $strCatID = str_pad($lICatID, 5, '0', STR_PAD_LEFT);
   }

   $attributes = array('name' => 'frmEditIClient', 'id' => 'frmAddEdit');
   echoT(form_open('staff/inventory/icat/addEditICat/'.$lICatID, $attributes));

   openBlock(($bNew ? 'Add' : 'Update').' Inventory Category', '');
   echoT('<table class="enpView">');

   echoT($clsForm->strLabelRow('catID',  $strCatID, 1));

      //----------------------
      // category
      //----------------------
   $clsForm->strStyleExtraLabel = 'width: 100pt; padding-top: 6px;';
   $clsForm->strExtraFieldText = form_error('txtCat');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Category',  'txtCat', true,  $formData->txtCat, 30, 255));

      //----------------------
      // Parent Category
      //----------------------
   $clsForm->strExtraFieldText = form_error('ddlParent');
   $strDDL =
       '<select name="ddlParent">
          <option value="-1">(root)</option>'."\n".$ddlParent.'</select>';
   echoT($clsForm->strLabelRow('Parent Category', $strDDL, 1));

      //----------------------
      // Notes
      //----------------------
   $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
   echoT($clsForm->strNotesEntry('Notes', 'txtNotes', false, $formData->txtNotes, 5, 50));

   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'text-align: center; width: 80pt;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');

   closeBlock();


