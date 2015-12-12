<?php
   $attributes =
       array(
            'name'     => 'frmCalEvents',
            'id'       => 'calEvents'
            );

   echoT(form_open('volunteers/events_cal/viewEventsCalendar',  $attributes));

   openBlock('Calendar Events', '');

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;
   echoT('<table width="500" border="0">');

      //------------------------
      // starting month
      //------------------------
   $clsForm->strExtraFieldText = form_error('txtMonth');
   echoT($clsForm->strLabelRow('Starting month', 
                      '<input type="text" value="'.$txtMonth.'" name="txtMonth" size="8" id="month1">', 1, ''));

      //------------------------
      // # of months
      //------------------------
   $strDDL = '';
   for ($idx = 1; $idx <= 12; ++$idx){
      $strDDL .= '<option value="'.$idx.'" '.($idx==$lDuration ? 'SELECTED' : '').'>'.$idx.'</option>'."\n";
   }
   echoT($clsForm->strGenericDDLEntry('# Months to Display', 'ddlDuration', false, $strDDL));
                      
   $clsForm->strStyleExtraLabel = 'text-align: left; width: 100pt;';
   echoT($clsForm->strSubmitEntry('View Events', 1, 'cmdSubmit', ''));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">skillsRpt.addEditEntry.focus();</script>');
   
   
   closeblock();
   
