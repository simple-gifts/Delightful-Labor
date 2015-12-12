<?php

//   echoT($strHTMLSummary);

   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 110pt;';

   $attributes = array('name' => 'frmAddEdit', 'id' => 'addEdit');
   echo form_open('admin/uf_log/addEditLogEntry/'.$lTableID.'/'.$lLogFieldID.'/'.$lForeignID.'/'.$lLogEntryID, $attributes);

   echoT('
      <fieldset class="enpFS" style="width: 500px; align: left;">
           <legend class="enpLegend">
              <b>'.$strTTypeLabel.' Table: '.htmlspecialchars($strUserTableName).': <i>'
              .htmlspecialchars($pff_strFieldNameUser)
              .'</b></i>
           </legend>');

   echoT('
      <table class="enpView" >');

   $clsForm->strExtraFieldText = form_error('txtTitle');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Title', 'txtTitle', false,  $strTitle, 60, 255));
   
   $clsForm->strExtraFieldText = form_error('txtLog');
   echoT($clsForm->strNotesEntry('Log Entry', 'txtLog', false, $strLog, 8, 50));
   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSave', ''));
   echoT('
      </table></form></fieldset><br><br>');
   echoT('<script type="text/javascript">addEdit.addEditEntry.focus();</script>');      


