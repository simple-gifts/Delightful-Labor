<?php
   $clsForm = new generic_form;
   
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   echoT(form_open('clients/client_rec_stat/addEditStatCat/'.$lClientID));
   echoT('<table class="enpView">');
   echoT($clsForm->strTitleRow('Status Category', 2, ''));
   echoT($clsForm->strGenericDDLEntry('Category', 'ddlStatCats', true, $ddlStatCats));
   echoT($clsForm->strSubmitEntry('Update Client\'s Status Category', 2, 'cmdSubmit', ''));
   echoT('</table></form><br><br><br>');

   echoT('The following status categories are defined for your organization:<br>');
   echoT(strClientStatusCatTable($statCats, $statCatsEntries, false, false));
   
