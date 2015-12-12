<?php
   $attributes = array('name' => 'frmEventDate');
   echoT(form_open('volunteers/event_dates_add_edit/addEditDate/'.$lEventID.'/'.$lDateID, $attributes));

   $clsForm  = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->strStyleExtraLabel = 'width: 100pt;';
   $clsForm->bValueEscapeHTML   = false;

   echoT('<table>');

   if ($bNew){
      $strEventDateID = '<i>new</i>';
   }else {
      $strEventDateID = str_pad($lDateID, 5, '0', STR_PAD_LEFT);
   }

      //----------------------
      // event date
      //----------------------
   echoT(strDatePicker('datepicker1', true, 2010));
   $clsForm->strExtraFieldText = form_error('txtEDate');
   echoT($clsForm->strGenericDatePicker(
                      'Event date', 'txtEDate',      true,
                      $formData->txtEDate,    'frmEventDate', 'datepicker1'));                      
                      
   echoT($clsForm->strSubmitEntry('Save Date', 2, 'cmdSubmit', ''));
   echoT('</table></form>');




