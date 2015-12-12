<?php
   $clsGF = new generic_form();
   $clsGF->bValueEscapeHTML = false;
   
   $attributes = array('name' => 'frmLoc', 'id' => 'frmAddEdit');
   echoT(form_open('clients/client_voc/addEdit/'.$lVocID, $attributes));

   $strSingular = '<font style="font-weight: normal; font-size: 8pt;">(singular)</font>';
   $strPlural   = '<font style="font-weight: normal; font-size: 8pt;">(plural)</font>';
   echoT('<br><br><table class="enpRptC">');
   echoT($clsGF->strTitleRow( (($bNew ? 'Add New ' : 'Update ').'Client Vocabulary'), 2, ''));
   
   $clsGF->strExtraFieldText = form_error('txtVocName');   
   $clsGF->strID = 'addEditEntry';
   echoT($clsGF->strGenericTextEntry('Vocabulary Name',          'txtVocName', true, $formD->txtVocName, 40, 255));
   
   $clsGF->strExtraFieldText = form_error('txtVClS');   
   echoT($clsGF->strGenericTextEntry('Vocabulary: Client '.$strSingular,   'txtVClS',    true, $formD->txtVClS, 40, 40));

   $clsGF->strExtraFieldText = form_error('txtVClP');   
   echoT($clsGF->strGenericTextEntry('Vocabulary: Client '.$strPlural,     'txtVClP',    true, $formD->txtVClP, 40, 40));

   $clsGF->strExtraFieldText = form_error('txtVSpS');   
   echoT($clsGF->strGenericTextEntry('Vocabulary: Sponsor '.$strSingular,  'txtVSpS',    true, $formD->txtVSpS, 40, 40));

   $clsGF->strExtraFieldText = form_error('txtVSpP');   
   echoT($clsGF->strGenericTextEntry('Vocabulary: Sponsor '.$strPlural,    'txtVSpP',    true, $formD->txtVSpP, 40, 40));

   $clsGF->strExtraFieldText = form_error('txtLocS');   
   echoT($clsGF->strGenericTextEntry('Vocabulary: Location '.$strSingular, 'txtLocS',    true, $formD->txtLocS, 40, 40));

   $clsGF->strExtraFieldText = form_error('txtLocP');   
   echoT($clsGF->strGenericTextEntry('Vocabulary: Location '.$strPlural,   'txtLocP',    true, $formD->txtLocP, 40, 40));

   echoT($clsGF->strSubmitEntry('Save', 2, 'cmdSubmit', ''));
   echoT('</table>'.form_close('<br><br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');
   
?>