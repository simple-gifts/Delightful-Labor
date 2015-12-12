<?php

   echoT(form_open('reports/pre_clients/bDay_run'));
   
   if ($lNumLocations == 0){
      echoT('<br><i>There are no client locations defined in your database.</i><br><br>');
      return;
   }

   openBlock('Client Birthdays', '');
   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->strStyleExtraLabel = 'width: 90pt;';
   $clsForm->bValueEscapeHTML = false;
   echoT('<table width="800" border="0">');

      // location selection
   $clsForm->strStyleExtraLabel = 'width: 80pt; padding-top: 8px;';
   $strOut =
      '<select name="ddlLoc">
         <option value="-1" selected>All locations</option>'
         .$ddlLocs
         .'</select>';
   echoT($clsForm->strLabelRow('Location', $strOut, 1));
   
      // month selection
   $strOut =
      '<select name="ddlMonth">'."\n";
   for ($idx=1; $idx<=12; ++$idx){
      $strOut .= '<option value="'.$idx.'">'.strXlateMonth($idx).'</option>'."\n";
   }
   $strOut .= '</select>'."\n";
      
   echoT($clsForm->strLabelRow('Birthdays in', $strOut, 1));

   echoT($clsForm->strSubmitEntry('Run Report', 2, 'cmdSubmit', 'text-align: center;'));
   echoT('</table>'.form_close('<br>'));

   closeblock();


