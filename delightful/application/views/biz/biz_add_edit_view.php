<?php
   global $gclsChapterVoc;

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;
   

   if ($bNew){
      $strTitle  = "Add New Business/Organization";
      $strButton = "Add Business/Organization";
   }else {
      $strTitle  = "Update Business/Organization Record";
      $strButton = "Update";
   }

   if ($bNew){
      $strBizID = '<i>new</i>';
   }else {
      $strBizID = str_pad($lBID, 5, '0', STR_PAD_LEFT);
   }

   $attributes = array('name' => 'frmAddEdit');
   echoT(form_open('biz/biz_add_edit/addEditBiz/'.$lBID, $attributes));

      // signals for duplicate biz test
   if (isset($bHiddenNewTestDup)){
      echoT(form_hidden('hiddenTestDup', ($bHiddenNewTestDup ? 'true' : 'false')));
   }
   echoT($strDuplicateWarning);   
   
   openBlock('Name', ''); echoT('<table class="enpView" >');

      //------------------------
      // Biz ID
      //------------------------
   $clsForm->bValueEscapeHTML = false;
   echoT($clsForm->strLabelRow('Biz ID', $strBizID, 1));

      //------------------------
      // Name
      //------------------------
   $clsForm->strID = 'addEditEntry';
   $clsForm->strStyleExtraLabel = 'width: 90pt; padding-top: 8px;';
   $clsForm->strExtraFieldText = form_error('txtBizName');
   echoT($clsForm->strGenericTextEntry('Name','txtBizName', true, $formData->txtBizName, 40, 120));

   echoT('</table>');  closeBlock();

   openBlock('Address', '');  echoT('<table class="enpView" >');

      //------------------------
      // Address 1
      //------------------------
   echoT($clsForm->strGenericTextEntry('Address 1',   'txtAddr1',   false,  $formData->txtAddr1,   40, 80));
   echoT($clsForm->strGenericTextEntry('Address 2',   'txtAddr2',   false,  $formData->txtAddr2,   40, 80));
   echoT($clsForm->strGenericTextEntry('City',        'txtCity',    false,  $formData->txtCity,    40, 80));
   echoT($clsForm->strGenericTextEntry($gclsChapterVoc->vocState, 'txtState',   false,  $formData->txtState,   40, 80));
   echoT($clsForm->strGenericTextEntry($gclsChapterVoc->vocZip,   'txtZip',     false,  $formData->txtZip,     20, 40));
   echoT($clsForm->strGenericTextEntry('Country',     'txtCountry', false,  $formData->txtCountry, 20, 40));

   echoT('</table>'); closeBlock();

   openBlock('Other Contact Info', ''); echoT('<table class="enpView" >');

      //------------------------
      // Email / Phone
      //------------------------
   $clsForm->strExtraFieldText = form_error('txtEmail');
   echoT($clsForm->strGenericTextEntry('Email',     'txtEmail',   false, $formData->txtEmail,   30, 80));
   echoT($clsForm->strGenericTextEntry('Phone',     'txtPhone',   false, $formData->txtPhone,   30, 40));
   echoT($clsForm->strGenericTextEntry('Cell',      'txtCell',    false, $formData->txtCell,    30, 40));
   echoT($clsForm->strGenericTextEntry('Fax',       'txtFax',     false, $formData->txtFax,     30, 40));
   echoT($clsForm->strGenericTextEntry('Web Site',  'txtWebSite', false, $formData->txtWebSite, 30, 255));

   echoT('</table>'); closeBlock();

   openBlock('Miscellaneous', ''); echoT('<table class="enpView" >');

      //-------------------------------
      // Business Category
      //-------------------------------
   if ($bizCatCnt <= 0){
      $strBizList = '<i>No business categories defined. See <b>Admin/Lists</b> to add categories.</i>';
   }else {
      $strBizList = $formData->strBizList;  
   }
   $clsForm->strExtraFieldText = form_error('ddlBizCat');
   echoT($clsForm->strLabelRow('Business Category', $strBizList, 1));

      //-------------------------------
      // Accounting country of Origin
      //-------------------------------
   echoT($clsForm->strLabelRow('Accounting Country', $formData->rdoACO, 1));

      //-------------------------------
      // Notes
      //-------------------------------
   echoT($clsForm->strNotesEntry('Notes', 'txtNotes', false, $formData->txtNotes, 3, 40));
   
   
      //--------------------------
      // Attributed to
      //--------------------------
   echoT($clsForm->strLabelRow('Attributed To', $strAttribDDL, false));

   echoT('</table>'); closeBlock();

   openBlock('Record Info', '');  echoT('<table class="enpView" >');

   $clsForm->strStyleExtraLabel = 'width: 90pt;';

   $clsForm->bNewRec = $bNew;
   if (!$bNew){
      $clsForm->strStaffCFName = $biz->strStaffCFName;
      $clsForm->strStaffCLName = $biz->strStaffCLName;
      $clsForm->dteOrigin      = $biz->dteOrigin;
      $clsForm->strStaffLFName = $biz->strStaffLFName;
      $clsForm->strStaffLLName = $biz->strStaffLLName;
      $clsForm->dteLastUpdate  = $biz->dteLastUpdate;
   }
   echoT($clsForm->strRecCreateModInfo());

   echoT('</table>'); closeBlock();

   echoT($clsForm->strSubmitEntry($strButton, 1, 'cmdSubmit', 'text-align: center; width: 150pt;'));
   echoT(form_close('<br>'));

   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');  



