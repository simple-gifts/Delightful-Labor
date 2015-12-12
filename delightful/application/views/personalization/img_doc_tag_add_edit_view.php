<?php
   
   echoT('<br>');
   
   $attributes = array('name' => 'frmAddEditTags', 'id' => 'addEditTags');
   
   echoT(form_open('admin/admin_imgdoc_tags/addEdit/'.$lDIT_ID.'/'.$enumContext, $attributes));

   $clsGF = new generic_form;
   $clsGF->bValueEscapeHTML = false;
   
   echoT('<table class="enpRptC">');
   echoT($clsGF->strTitleRow(($bNew ? 'Add ' : 'Update ').'an entry in tag list for <i>"'
                  .$strContext.'"</i>', 2, ''));

   $clsGF->strStyleExtraLabel = 'padding-top: 8px;';
   $clsGF->strExtraFieldText = form_error('txtDDLEntry');
   $clsGF->strID = 'addEditEntry';
   echoT($clsGF->strGenericTextEntry('Tag', 'txtDDLEntry', true,
                                       $strDDLEntry, 40, 80));

   echoT($clsGF->strSubmitEntry('Submit', 2, 'cmdSubmit', ''));
   echoT('</table>'.form_close('<br><br>'));
   echoT('<script type="text/javascript">addEditTags.addEditEntry.focus();</script>');      


