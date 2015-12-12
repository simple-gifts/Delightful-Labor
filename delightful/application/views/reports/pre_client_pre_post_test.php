<?php
   global $gbDateFormatUS, $gstrFormatDatePicker, $gdteNow;
   
   if ($lNumPPTests==0){
      echoT('<br><i>There are no pre/post tests available for this report.<br><br></i>');
      return;
   }

   $attributes =
       array(
            'name'     => $viewOpts->strFormName,
            'id'       => $viewOpts->strID
            );

   echoT(form_open($frmLink,  $attributes));

   openBlock($viewOpts->blockLabel, '');

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->strStyleExtraLabel = 'width: 90pt;';
   $clsForm->bValueEscapeHTML = false;
   echoT('<table  border="0">');
   
      //--------------------------------
      // Test
      //--------------------------------
   $clsForm->strStyleExtraLabel = 'padding-top: 8px;';
   $clsForm->strExtraFieldText = form_error('ddlTests');
   echoT($clsForm->strLabelRow('Test', $formData->strPrePostList, 1));
   
      //--------------------------------
      // Time Frame
      //--------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6px;';
   $clsForm->strStyleExtraValue = 'vertical-align: top;';
   echoT($clsForm->strLabelRow('Time Frame', $dateRanges, 1));

   $clsForm->strStyleExtraLabel = 'text-align: left;';
   echoT($clsForm->strSubmitEntry('Run Report', 2, 'cmdSubmit', 'text-align: center;'));
   echoT('</table>'.form_close('<br>'));

   closeblock();


