<?php

   $lTableWidth = 500;
   $lLabelWidth = 105;
   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   openBlock('Add New Custom Report', '');  echoT('<table class="enpView" >');

      //-------------------
      // Open form
      //-------------------
   $attributes = array('name' => 'frmAddCRpt', 'id' => 'frmAddEdit');
   echoT(form_open('creports/add_edit_crpt/add_edit/'.$lReportID, $attributes));
   
   
      //-------------------
      // Name
      //-------------------
   $clsForm->strExtraFieldText = form_error('txtName');
   $clsForm->strID = 'addEditEntry';
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 7pt; width: 90pt;';
   echoT($clsForm->strGenericTextEntry('Report Name',   'txtName',    true, $formData->strName,   40, 255));
   
      //-------------------
      // Notes
      //-------------------
   $clsForm->strStyleExtraLabel = 'padding-top: 3pt;';
   echoT($clsForm->strNotesEntry('Notes', 'txtNotes', false, $formData->strNotes, 3, 40));

      //-------------------
      // Report Type
      //-------------------
   if ($bNew){
      echoT($clsForm->strGenericDDLEntry('Report Type', 'ddlCRpt', true, $formData->strCRptTypeDDL));
   }else {
      echoT($clsForm->strLabelRow('Report Type', $formData->strRptType, 1));
   }
   
      //-------------------
      // Private?
      //-------------------
   $clsForm->strExtraFieldText = '<i>If checked, only you (and admins) have access to this report.<br>'
                             .'If public, all users can run the report, but only you and admins can modify it.</i>';
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 3pt;';
   echoT($clsForm->strGenericCheckEntry('Private?', 'chkPrivate', 'TRUE', false, $formData->bPrivate));   

   echoT($clsForm->strSubmitEntry('Add Report', 1, 'cmdSubmit', 'text-align: center;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');  

   
   
   closeBlock();

