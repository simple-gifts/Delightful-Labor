<?php
   global $gbDateFormatUS, $gstrFormatDatePicker, $gdteNow;

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
      // Group By (for client recs)
      //--------------------------------
   $strDEGroup =
            '<input type="radio" name="rdoGroup" value="individual" '.($strRdoGroup=='individual' ? 'checked' : '').'>Individual&nbsp;'
           .'<input type="radio" name="rdoGroup" value="staffGroup" '.($strRdoGroup=='staffGroup' ? 'checked' : '').'>Staff Group <br>';
   $strIndent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';           
           
      //--------------------------------
      // Data Entry
      //--------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 4px;';
   $strDESource =
            '<input type="radio" name="rdoSrc" value="client" '.($strRdoSrc=='client' ? 'checked' : '').'>Client Records<br>'
           .$strIndent.$strIndent.'grouping: '.$strDEGroup 
           .'<input type="radio" name="rdoSrc" value="enroll" '.($strRdoSrc=='enroll' ? 'checked' : '').'>Client Programs: Enrollment Records<br>'
           .'<input type="radio" name="rdoSrc" value="attend" '.($strRdoSrc=='attend' ? 'checked' : '').'>Client Programs: Attendance Records<br>';
   echoT($clsForm->strLabelRow('Data Source', $strDESource, 1)); 

//   echoT($clsForm->strLabelRow('Group By', $strDEGroup, 1)); 

   $clsForm->strStyleExtraLabel = 'text-align: left;';
   echoT($clsForm->strSubmitEntry('Run Report', 2, 'cmdSubmit', 'text-align: center;'));
   echoT('</table>'.form_close('<br>'));

   closeblock();


