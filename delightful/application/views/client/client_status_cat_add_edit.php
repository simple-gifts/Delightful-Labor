<?php

   $attributes = array('name' => 'frmLoc', 'id' => 'frmAddEdit');
   echo form_open('clients/status_cat/addEdit/'.$id, $attributes);
   $clsGF = new generic_form();
   $clsGF->bValueEscapeHTML = false;

   echoT('
      <br>
      <table class="enpRptC">
           <tr>
              <td class="enpRptTitle" colspan="2" style="text-align: center;">'
                 .($bNew ? 'Add new ' : 'Update ').' Client Status Category
              </td>
           </tr>');
   $clsGF->strExtraFieldText = form_error('txtStatCatName');
   $clsGF->strID = 'addEditEntry';
   echoT($clsGF->strGenericTextEntry  ('Status',      'txtStatCatName', true,  $formD->txtStatCatName, 40, 200));
   echoT($clsGF->strNotesEntry        ('Description', 'txtNotes',       false, $formD->txtNotes,        4, 40));
   echoT($clsGF->strSubmitEntry('Submit', 2, 'cmdSubmit', ''));

   echoT('</table></form>');
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');
