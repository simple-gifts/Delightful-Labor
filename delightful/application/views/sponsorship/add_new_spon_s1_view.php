<?php
   global $gbDateFormatUS, $gstrFormatDatePicker, $gdteNow;

   $attributes = array('name' => 'frmEditSpon', 'id' => 'frmAddEdit',
                       'onSubmit' =>
                              'return verifySponForm(frmEditSpon, '
                                          .($gbDateFormatUS ? 'true' : 'false').','
                                          .($bNew ? 'true' : 'false'));
   if ($bNew){
      $strSponID = '<i>new</i>';
      echoT(form_open('sponsors/add_edit_sponsorship/addNewS1/'.$lFID.'/'.$lSponID, $attributes));
   }else {
      $strSponID = str_pad($lSponID, 5, '0', STR_PAD_LEFT);
      echoT(form_open('sponsors/add_edit_sponsorship/editRec/'.$lSponID, $attributes));
   }



   openBlock(($bNew ? 'Add new' : 'Update').' sponsorship for '.$strSafeName, '');
   echoT('
      <table class="enpView">');

   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

      //------------------------
      // Sponsor ID
      //------------------------
   $clsForm->strStyleExtraLabel = 'width: 110pt;';
   echoT($clsForm->strLabelRow('Sponsor ID', $strSponID, 1));

      //--------------------------
      // Sponsorship Program DDL
      //--------------------------
   if ($bNew){
      $clsForm->strExtraFieldText = form_error('ddlSponProg');
      $clsForm->strID = 'addEditEntry';
      echoT($clsForm->strGenericDDLEntry('Sponsorship Program', 'ddlSponProg', true,
             '<option value="-1">&nbsp;</option>'
             .$strSponProgDDL));
   }else {
      echoT($clsForm->strLabelRow('Sponsorship Program', $sponRec->strSponProgram, 1));
      $clsForm->strID = 'addEditEntry';
      $clsForm->strExtraFieldText = form_error('txtAmount');
      echoT($clsForm->strGenericTextEntry('Amount', 'txtAmount', true, $strAmount, 14, 20));
      
         //-------------------------------
         // Accounting country of Origin
         //-------------------------------
      echoT($clsForm->strLabelRow('Accounting Country', $clsACO->strACO_Radios($lCommitACO, 'rdoACO'), 1));
   }

      //------------------------
      // Sponsorship start date
      //------------------------
   echoT(strDatePicker('datepicker1', true, 1970));
   $clsForm->strExtraFieldText = form_error('txtStartDate');
   echoT($clsForm->strGenericDatePicker(
                      'Sponsorship Start Date', 'txtStartDate', true,
                      $strStartDate,            'frmEditSpon', 'datepicker1'));

      //--------------------------
      // Attributed to
      //--------------------------
   echoT($clsForm->strLabelRow('Attributed To', $strAttribDDL, false));

   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'width: 100pt;'));
   echoT('</table></form><br><br>');

   echoT('</table>'.form_close('<br><br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');

   closeBlock();


