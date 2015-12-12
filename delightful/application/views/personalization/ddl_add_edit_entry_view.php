<?php
   
   echoT($entrySummary);
   echoT('<br>');
   
   $attributes = array('name' => 'frmAddEditDDL', 'id' => 'addEditDDL');
   
   echoT(form_open('admin/uf_ddl/addEditDDLEntry/'.$lTableID.'/'.$lFieldID.'/'.$lDDLEntryID, $attributes));

   $clsGF = new generic_form;

   echoT('<table class="enpRptC">');
   echoT($clsGF->strTitleRow(($bNew ? 'Add ' : 'Update ').'an entry in drop-down list<br><i>"'
                  .$strTableLabel.': '.htmlspecialchars($strUserFieldName).'"</i>', 2, ''));

   $clsGF->strStyleExtraLabel = 'padding-top: 8px;';
   $clsGF->strExtraFieldText = form_error('txtDDLEntry');
   $clsGF->strID = 'addEditEntry';
   echoT($clsGF->strGenericTextEntry('List Entry', 'txtDDLEntry', true,
                                       $strDDLEntry, 40, 80));

   echoT($clsGF->strSubmitEntry('Submit', 2, 'cmdSubmit', ''));
   echoT('</table>'.form_close('<br><br>'));
   echoT('<script type="text/javascript">addEditDDL.addEditEntry.focus();</script>');      


