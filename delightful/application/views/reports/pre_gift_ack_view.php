<?php
   global $gbDateFormatUS, $gstrFormatDatePicker;

   $attributes =
       array(
            'name'     => $viewOpts->strFormName,
            'id'       => $viewOpts->strID
            );

   echoT(form_open('reports/pre_gift_ack/showOpts/'.$enumAckType,  $attributes));

   openBlock('Gift Acknowledgment / '.$strLabel, '');

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->strStyleExtraLabel = 'width: 100pt;';
   $clsForm->bValueEscapeHTML = false;
   echoT('<table width="900" border="0">');


      //-------------------------------
      // Accounting country of Origin
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: middle; width: 120pt; ';
   echoT($clsForm->strLabelRow('Accounting Country', $formData->strACORadio, 1));
   
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6px;';
   $clsForm->strStyleExtraValue = 'vertical-align: top;';
   echoT($clsForm->strLabelRow('Time Frame', $dateRanges, 1));
   
/*   
   
      //----------------------
      // report start
      //----------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   echoT(strDatePicker('datepicker1', false));
   $clsForm->strExtraFieldText = form_error('txtSDate');
   echoT($clsForm->strGenericDatePicker(
                      'Start',   'txtSDate',      true,
                      $txtSDate, 'frmGiftAckRpt', 'datepicker1'));

      //----------------------
      // report end
      //----------------------
   echoT(strDatePicker('datepicker2', true));
   $clsForm->strExtraFieldText = form_error('txtEDate');
   echoT($clsForm->strGenericDatePicker(
                      'End', 'txtEDate', true,
                      $txtEDate,  'frmGiftAckRpt', 'datepicker2'));
*/
      //--------------------------------
      // include only not acknowledged
      //--------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: middle; width: 120pt; ';
   echoT($clsForm->strLabelRow('Include donations',
                         '<input type="radio" name="rdoAck" value="notAck" '.($bNotAck ? 'checked' : '').'>Not acknowledged&nbsp;'
                        .'<input type="radio" name="rdoAck" value="all"    '.($bNotAck ? '' : 'checked').'>All donations'
                        , 1));

      //---------------------------------
      // include sponsorship payments
      //---------------------------------
   if ($enumAckType=='gifts'){
      $clsForm->strExtraFieldText = ' (check to include)';
      echoT($clsForm->strGenericCheckEntry('Include sponsorship payments', 'chkSpon', 'true', false, $bIncludeSpon));
   }
   
      //---------------------------------
      // mark donations as acknowledged
      //---------------------------------
   $clsForm->strExtraFieldText = ' Check to mark donations<br><i>Note: donations are marked as acknowledged when the report is exported</i>';
   echoT($clsForm->strGenericCheckEntry('Mark donations as acknowledged', 'chkMarkAck', 'true', false, $bMarkAck));
   
      //--------------------------------
      // Sort by
      //--------------------------------
   echoT($clsForm->strLabelRow('Sort by',
                         '<input type="radio" name="rdoSort" value="date"  '.($enumSort=='date'  ? 'checked' : '').'>Date&nbsp;'
                        .'<input type="radio" name="rdoSort" value="amnt"  '.($enumSort=='amnt'  ? 'checked' : '').'>Amount&nbsp;'
                        .'<input type="radio" name="rdoSort" value="donor" '.($enumSort=='donor' ? 'checked' : '').'>Donor&nbsp;'
                        , 1));
   
   
   echoT($clsForm->strSubmitEntry('Run Report', 2, 'cmdSubmit', 'text-align: center;'));
   echoT('</table>'.form_close('<br>'));
//   echoT('<script type="text/javascript">skillsRpt.addEditEntry.focus();</script>');


   closeblock();
