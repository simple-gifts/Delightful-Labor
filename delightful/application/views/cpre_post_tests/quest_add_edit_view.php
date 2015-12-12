<?php

   echoT($strTestSummary);


   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;
   
   if ($bNew){
      $strQuestID = '<i>new</i>';
   }else {
      $strQuestID = str_pad($lQuestID, 5, '0', STR_PAD_LEFT);
   }
   
   $attributes = array('name' => 'frmEditCProg', 'id' => 'frmAddEdit');
   echoT(form_open('cpre_post_tests/ppquest_add_edit/addEditQuest/'.$lQuestID.'/'.$lPPTestID, $attributes));
      
   openBlock(($bNew ? 'Add New' : 'Update').' Test Question', '');
   echoT('<table>');
   
      // question ID
   $clsForm->strStyleExtraLabel = 'width: 90pt;';
   echoT($clsForm->strLabelRow('Question ID',  $strQuestID, 1));
   
      //----------------------
      // Question
      //----------------------
   $clsForm->strExtraFieldText = form_error('txtQuestion');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strNotesEntry('Question', 'txtQuestion', false, $formData->txtQuestion, 3, 50));
   
      //----------------------
      // Answer
      //----------------------
   $clsForm->strExtraFieldText = form_error('txtAnswer');
   echoT($clsForm->strNotesEntry('Answer', 'txtAnswer', false, $formData->txtAnswer, 3, 50));
   
   
   $clsForm->strStyleExtraLabel = 'text-align: left;';
   echoT($clsForm->strSubmitEntry(($bNew ? 'Add ' : 'Update ').'Pre/Post Test Question', 2, 'cmdSubmit', 'text-align: center;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');  
   closeBlock();      
   
   