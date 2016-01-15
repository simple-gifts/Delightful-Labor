<?php

   if ($lNumTables == 0){
      echoT('<br><i>There are no personalized tables available for import!</i><br>');
      return;
   }

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   $attributes =
       array(
            'name'     => 'frmPSel',
            'id'       => 'parentSel'
            );
   echoT(form_open('admin/import/ptables',  $attributes));

   openBlock('Personalized Table Import', '');
   echoT('<table class="enpView">');

   $clsForm->strExtraFieldText  = form_error('ddlTables');
   $clsForm->strStyleExtraLabel = 'padding-top: 6px;';
//   $clsForm->strExtraSelect     = ' onChange="showPTables(this.value, \'ddlParent\')" ';
   
   echoT($clsForm->strGenericDDLEntry('Personalized Table', 'ddlTables', true,
                 $formData->strDDLParent));
   
                       
   echoT($clsForm->strSubmitEntry(' Select ', 2, 'cmdSubmit', 'text-align: left;'));


   echoT('</table>'.form_close('<br>'));

   echoT('</table>'); closeBlock();
                       
