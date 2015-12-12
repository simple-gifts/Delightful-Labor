<?php

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   $attributes = array('name' => 'frmTSProject', 'id' => 'frmAddEdit');
   echoT(form_open('admin/timesheets/ts_projects/addEditTSProject/'.$lTSProjID, $attributes));
   
   openBlock('Time Sheets: '.($bNew ? 'Add New' : 'Edit').' Billable Project', '');
   echoT('<table class="enpView">');
   
   
      //------------------------
      // Project Name
      //------------------------
   $clsForm->strStyleExtraLabel = 'width: 80pt; padding-top: 8px;';
   $clsForm->strExtraFieldText = form_error('txtProject');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Project Name', 'txtProject', true,  $formData->txtProject, 30, 80));
   
      //------------------------
      // Internal Project
      //------------------------
   $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
   $clsForm->strExtraFieldText = '<br><i>Internal projects can be things like "overhead", "sick time", "vacation", etc.</i>';
   echoT($clsForm->strGenericCheckEntry('Internal Project', 'chkInternal', 'true', false, 
                           $formData->bInternalProject));
   
   
   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'text-align: center; width: 80pt;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');  

   closeBlock();      
   
