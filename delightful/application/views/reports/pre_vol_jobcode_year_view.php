<?php

   global $gdteNow;

   echoT(form_open('reports/pre_vol_jobcodes/jcYearRun'));

   openBlock('Volunteer Job Codes by Year', '');

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->strStyleExtraLabel = 'width: 90pt;';
   $clsForm->bValueEscapeHTML = false;
   echoT('<table width="800" border="0">');

   
   $lCurrentYear = (integer)date('Y', $gdteNow);
   $strOut = '<select name="ddlYear">'."\n";
   for ($idx=($lCurrentYear-20); $idx<=($lCurrentYear+1); ++$idx){
      $strOut .= '<option value="'.$idx.'" '.($idx==$lCurrentYear ? 'SELECTED' : '').'>'
              .$idx.'</option>'."\n";
   }
   $strOut .= '</select>'."\n";
   $clsForm->strStyleExtraLabel = 'width: 90pt; padding-top: 6px;';
   echoT($clsForm->strLabelRow('Year', $strOut, 1));

//   $clsForm->strExtraFieldText = '<i>(check to view report by volunteer)</i>';
//   echoT($clsForm->strGenericCheckEntry('Summary by Vol.', 'chkViaVol', 'true', false, false));
   
      // job code for shift
   echoT($clsForm->strLabelRow('Job Code', $strDDLJobCode, 1));
   
   
   $clsForm->strStyleExtraLabel = 'text-align: left;';
   echoT($clsForm->strSubmitEntry('Run Report', 2, 'cmdSubmit', 'text-align: center;'));
   echoT('</table>'.form_close('<br>'));

   closeblock();
   
   