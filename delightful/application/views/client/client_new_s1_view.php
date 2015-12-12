<?php

   if ($bFail){
      echoT($strFailMsg);
   }else {
      $clsForm = new generic_form;
      $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
      $clsForm->strTitleClass = 'enpViewTitle';
      $clsForm->strEntryClass = 'enpView';
      $clsForm->bValueEscapeHTML = false;

      $attributes = array('name' => 'frmLoc', 'id' => 'frmAddEdit');
      switch ($enumSource){
         case 'clientXfer':
            $strLabel = 'Transfer Client';
            echoT(form_open('clients/client_record/xfer1/'.$lClientID, $attributes));
            break;
         case 'addNew':
            $strLabel = 'Add a new client: Step 1';
            echoT(form_open('clients/client_rec_add_edit/addNewS1', $attributes));
            break;
         default:
            screamForHelp($enumSource.': invalid form type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

      openBlock($strLabel, '');

      $strNoOptSel = '<option value="-1">&nbsp;</option>';

      echoT('<table >');

      $clsForm->strExtraFieldText = form_error('ddlLocation');
      $clsForm->strID = 'addEditEntry';
      echoT($clsForm->strGenericDDLEntry('Client Location', 'ddlLocation', true,
                     $strNoOptSel.$strLocationDDL));

      $clsForm->strExtraFieldText = form_error('ddlClientStatCat');
      echoT($clsForm->strGenericDDLEntry('Client Status Category', 'ddlClientStatCat', true,
                         $strClientStatCatDDL));

      $clsForm->strExtraFieldText = form_error('ddlClientVoc');
      echoT($clsForm->strGenericDDLEntry('Vocabulary that will<br>Apply to this Client', 'ddlClientVoc', true,
                         $strClientVocDDL));

      if ($enumSource == 'clientXfer'){
            //----------------------
            // Effective date
            //----------------------
         echoT(strDatePicker('datepicker1', false));
         $clsForm->strExtraFieldText = form_error('txtEDate');
         echoT($clsForm->strGenericDatePicker(
                            'Effective date', 'txtEDate',  true,
                            $strEDate,        'frmLoc',   'datepicker1'));
      
      }

      echoT($clsForm->strSubmitEntry('Submit', 1, 'cmdSubmit', 'width: 110pt;'));
      echoT('</table>'.form_close('<br><br>'));
      echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');
      closeBlock();

   }
