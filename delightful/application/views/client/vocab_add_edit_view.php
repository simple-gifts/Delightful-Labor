<?php

   $clsForm = new generic_form;
   
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   echoT(form_open('clients/client_rec_voc/addEdit/'.$lClientID));
   echoT('<table class="enpView">');
   echoT($clsForm->strTitleRow('Vocabulary Association', 2, ''));
   echoT($clsForm->strGenericDDLEntry('Vocabulary', 'ddlVoc', true, $ddlVoc));
   echoT($clsForm->strSubmitEntry('Update Client\'s Vocabulary', 2, 'cmdSubmit', ''));
   echoT('</table></form><br><br><br>');

   echoT('The following vocabularies are defined for your organization:<br>');
   echoT(htmlVocList(false, $vocs, false));
   
