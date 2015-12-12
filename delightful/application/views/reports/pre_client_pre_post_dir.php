<?php
   global $gbDateFormatUS, $gstrFormatDatePicker, $gdteNow;
   
   if ($lNumPPTests==0){
      echoT('<br><i>There are no pre/post tests available for this report.<br><br></i>');
      return;
   }

   $attributes =
       array(
            'name'     => 'frmPrePostDir',
            'id'       => 'prePostDirID'
            );

   echoT(form_open('reports/pre_client_pre_post/dirViaTest',  $attributes));

   openBlock('Client List via Pre/Post Test', '');

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
   
   $clsForm->strStyleExtraLabel = 'text-align: left;';
   echoT($clsForm->strSubmitEntry('Run Report', 2, 'cmdSubmit', 'text-align: center;'));
   echoT('</table>'.form_close('<br>'));

   closeblock();


