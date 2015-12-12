<?php
   global $gbDateFormatUS, $gstrFormatDatePicker;

   if ($lNumEvents <= 0){
      echoT('<br><i>There are no volunteer events
             defined in your system. </i><br><br>');
   }elseif ($lNumVols==0){
      echoT('<br><i>There are no volunteers
             defined in your system. </i><br><br>');
   }else {
      $attributes =
          array(
               'name'     => $viewOpts->strFormName,
               'id'       => $viewOpts->strID
               );

      echoT(form_open('reports/pre_vol_hours/'.($enumOptsType=='hours' ? 'showOpts' : 'showOptsTFSum'), $attributes));

      openBlock($viewOpts->strTitle, '');

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

         /*--------------------------------
           Sort
         --------------------------------*/
      if ($enumOptsType=='hours'){
         echoT($clsForm->strLabelRow('Sort by', 
                               '<input type="radio" name="rdoSort" value="event" '.($bSortEvent ? 'checked' : '').'>Event&nbsp;'
                              .'<input type="radio" name="rdoSort" value="vol"   '.($bSortVol   ? 'checked' : '').'>Volunteer'
                              , 1));
      }

      $clsForm->strStyleExtraLabel = 'text-align: left;';
      echoT($clsForm->strSubmitEntry('Run Report', 2, 'cmdSubmit', 'text-align: left;'));
      echoT('</table>'.form_close('<br>'));
      echoT('<script type="text/javascript">vhrsRpt.addEditEntry.focus();</script>');

      closeblock();
   }
