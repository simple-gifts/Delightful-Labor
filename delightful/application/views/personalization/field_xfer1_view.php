<?php

   if ($bMultiEntry) return;   // shouldn't be here!
   
   if ($lNumTables==0){
      echoT('<br><i>Sorry, there are no tables available for transfer. Please create a new table first.<br><br></i>');
      return;
   }


   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;
   
   $attributes = array('name' => 'frmXferField', 'id' => 'frmXferField');
   echoT(form_open('admin/uf_fields/xfer1/'.$lTableID.'/'.$lFieldID, $attributes));
   
   openBlock('Transfer a field to another personalized table', '');
   echoT('<br><table class="enpView">');
   
   echoT($clsForm->strLabelRow('Field Name', htmlspecialchars($ufield->pff_strFieldNameUser), 1));

      // source table
   echoT($clsForm->strLabelRow('Source Table', htmlspecialchars($strUserTableName), 1));
   
      // destination table
   $clsForm->strExtraFieldText = form_error('ddlTables');      
   echoT($clsForm->strLabelRow('Destination Table',  $strDDL, 1));  
   
   $clsForm->strStyleExtraLabel = 'text-align: left;';
   echoT($clsForm->strSubmitEntry('Transfer', 2, 'cmdSubmit', 'width: 100pt; text-align: center;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');  

   closeBlock();      
   
