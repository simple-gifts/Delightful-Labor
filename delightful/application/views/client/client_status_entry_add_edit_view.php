<?php
   $clsGF = new generic_form();
   $attributes = array('name' => 'frmStat', 'id' => 'frmAddEdit');
   echo form_open('clients/status_cat_entry/addEdit/'.$lCatID.'/'.$lCatEntryID, $attributes);

   echoT('<br><br><table class="enpRptC">');
   echoT($clsGF->strTitleRow( (($bNew ? 'Add new ' : 'Edit ').'Client Status Entry ('
                         .$strCatName.')'), 2, ''));
                         
                         
   $clsGF->strExtraFieldText = form_error('txtStatEntry');
   $clsGF->strID = 'addEditEntry';
   echoT($clsGF->strGenericTextEntry  ('Status Entry',      'txtStatEntry',         true,  $formD->txtStatEntry, 40, 80));
   echoT($clsGF->strGenericCheckEntry('Allow sponsorship?', 'chkAllowSpon', 'TRUE', false, $formD->bAllowSponsorship));
   echoT($clsGF->strGenericCheckEntry('Show in directory?', 'chkShowInDir', 'TRUE', false, $formD->bShowInDir));
   echoT($clsGF->strGenericCheckEntry('Default status?',    'chkDefault',   'TRUE', false, $formD->bDefault));

//   if (!$bNew){
//      echoT($clsGF->strGenericCheckEntry('Retire entry?', 'chkRetire', 'TRUE', false, false));
//   }
   echoT($clsGF->strSubmitEntry('Save', 2, 'cmdSubmit', ''));
   echoT('</table>'.form_close('<br><br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');
   
   
