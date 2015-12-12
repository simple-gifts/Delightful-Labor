<?php
   global $gbDateFormatUS, $gstrFormatDatePicker;

   $attributes =
       array(
            'name'     => $viewOpts->strFormName,
            'id'       => $viewOpts->strID
            );

   echoT(form_open('reports/pre_vol_hours/showOptsPVA', $attributes));

   openBlock('Volunteer Hours - Scheduled vs. Actual', '');

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;
   echoT('<table width="500" border="0">');

         /*--------------------------------
           Time Frame
         --------------------------------*/
      $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6px;';
      $clsForm->strStyleExtraValue = 'vertical-align: top;';
      echoT($clsForm->strLabelRow('Time Frame', $dateRanges, 1));
   
/*
      //----------------------
      // report start
      //----------------------
   $clsForm->strStyleExtraLabel = 'padding-top: 8px;';
   echoT(strDatePicker('datepicker1', false));
   $clsForm->strExtraFieldText = form_error('txtSDate');
   echoT($clsForm->strGenericDatePicker(
                      'Start',   'txtSDate',      true,
                      $txtSDate, 'frmVHrsPVARpt', 'datepicker1'));

      //----------------------
      // report end
      //----------------------
   echoT(strDatePicker('datepicker2', true));
   $clsForm->strExtraFieldText = form_error('txtEDate');
   echoT($clsForm->strGenericDatePicker(
                      'End', 'txtEDate', true,
                      $txtEDate,  'frmVHrsPVARpt', 'datepicker2'));
*/

   $clsForm->strStyleExtraLabel = 'padding-top: 3px;';
   echoT($clsForm->strLabelRow('Sort by', 
                         '<input type="radio" name="rdoSort" value="vol"   '.($bSortVol  ? 'checked' : '').'>Volunteer&nbsp;&nbsp;'
                        .'<input type="radio" name="rdoSort" value="phrs"  '.($bSortPHrs ? 'checked' : '').'>Projected Hours&nbsp;&nbsp;'
                        .'<input type="radio" name="rdoSort" value="lhrs"  '.($bSortLHrs ? 'checked' : '').'>Logged Hours'
                        , 1));

   $clsForm->strStyleExtraLabel = 'text-align: left;';
   echoT($clsForm->strSubmitEntry('Run Report', 2, 'cmdSubmit', 'text-align: left;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">vhrsPVARpt.addEditEntry.focus();</script>');


   closeblock();
