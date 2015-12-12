<?php

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;
   
   if ($bNew){
      $strPPTestID = '<i>new</i>';
   }else {
      $strPPTestID = str_pad($lPPTestID, 5, '0', STR_PAD_LEFT);
   }
   
   $attributes = array('name' => 'frmEditCProg', 'id' => 'frmAddEdit');
   echoT(form_open('cpre_post_tests/pptest_add_edit/addEditPPTest/'.$lPPTestID, $attributes));
      
   openBlock(($bNew ? 'Add New' : 'Update').' Pre/Post Test', '');
   echoT('<table class="enpView">');
   
      // program ID
   echoT($clsForm->strLabelRow('Pre/Post Test ID',  $strPPTestID, 1));

      //----------------------
      // Category
      //----------------------
   $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 8px;';
   
   $clsForm->strExtraFieldText = form_error('ddlPPCat');
   echoT($clsForm->strLabelRow('Test Category', $formData->strCatDDL, 1));
   
      //----------------------
      // Program Name
      //----------------------
   $clsForm->strExtraFieldText = form_error('txtTestName');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Test Name',  'txtTestName', true,  $formData->txtTestName, 50, 255));
   
      //----------------------
      // Description
      //----------------------
   echoT($clsForm->strNotesEntry('Description', 'txtDescription', false, $formData->txtDescription, 3, 50));
   
      // Hidden?
   if (!$bNew){
      $clsForm->strStyleExtraLabel = 'padding-top: 2px;';
      $clsForm->strExtraFieldText = 'Check this box to hide the test. You can always restore it later.';
      echoT($clsForm->strGenericCheckEntry('Hidden?', 'chkHidden', 'true', false, $formData->bHidden));
   }
      // Published?
   if (!$bNew){
      $clsForm->strStyleExtraLabel = 'padding-top: 2px;';
      $clsForm->strExtraFieldText = 'Check this box to publish the test. Once published, you will
                    not be able to add questions or change answers.';
      echoT($clsForm->strGenericCheckEntry('Published?', 'chkPublished', 'true', false, $formData->bPublished));
   }
   
   echoT($clsForm->strSubmitEntry(($bNew ? 'Add ' : 'Update ').'Pre/Post Test', 1, 'cmdSubmit', 'text-align: center;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');  
   closeBlock();      
