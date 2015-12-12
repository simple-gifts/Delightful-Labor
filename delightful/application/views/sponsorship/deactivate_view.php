<?php

   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 90pt;';

   $attributes = array('name' => 'frmSponDeactivate');
   echoT(form_open('sponsors/add_edit_sponsorship/deactivate/'.$lSponID, $attributes));

   openBlock('Terminate Sponsorship', '');

   $clsForm->strLabelClass    = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass    = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   echoT('
      <table class="enpView">');      

      //------------------------
      // Inactive date
      //------------------------
   $clsForm->strExtraFieldText = form_error('txtDate');
   echoT(strDatePicker('datepicker1', true, 1970));
   echoT($clsForm->strGenericDatePicker(
                      'Termination Date', 'txtDate', true,
                      $txtDate,  'frmSponDeactivate', 'datepicker1'));

//   $clsList->enumListType = CENUM_LISTTYPE_SPONTERMCAT;
   $clsForm->strExtraFieldText = form_error('ddlTermReason');
   echoT($clsForm->strLabelRow('Reason', $strTermList, 1));
   echoT($clsForm->strSubmitEntry('Terminate', 1, 'cmdSubmit', ''));

   echoT('</table></form>');
   closeBlock();   


