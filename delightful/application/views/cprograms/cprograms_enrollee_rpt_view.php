<?php
   global $gbDateFormatUS, $gstrFormatDatePicker, $gdteNow;

   if ($lNumCProgs == 0){
      echoT('<br/><i>There are no client programs available for review.</i><br/>');
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
      // Time Frame
      //--------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6px;';
   $clsForm->strStyleExtraValue = 'vertical-align: top;';
   echoT($clsForm->strLabelRow('Time Frame', $dateRanges, 1)); 
   
      //--------------------------------
      // Client Programs
      //--------------------------------
   $clsForm->strExtraFieldText = form_error('chkCProgs');
   echoT($clsForm->strLabelRowOneCol('<b>View enrollees from the following programs:</b>', 2, '', 260));
   $clsForm->bAddLabelColon = false;
   
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$cprogs   <pre>');
echo(htmlspecialchars( print_r($cprogs, true))); echo('</pre></font><br>');
// ------------------------------------- */
   
   foreach ($cprogs as $cprog){
      if ($cprog->bShowCProgLink){
         $lCProgID = $cprog->lKeyID;
         $strChecked = (in_array($lCProgID, $formData->cProgSelectedIDs) ? 'checked' : '');
         $strProg =
            '<input type="checkbox" name="chkCProgs[]" value="'.$lCProgID.'" '.$strChecked.'> '
            .htmlspecialchars($cprog->strProgramName);
         echoT($clsForm->strLabelRow('', $strProg, 1)); 
      }
   }
   
   
   $clsForm->strStyleExtraLabel = 'text-align: left;';
   echoT($clsForm->strSubmitEntry('View Enrollees', 2, 'cmdSubmit', 'text-align: center;'));
   echoT('</table>'.form_close('<br>'));

   closeblock();
   
   
