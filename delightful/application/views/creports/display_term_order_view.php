<?php


   if ($report->lNumFields == 0){
      echoT('<i>There are no fields associated with this report.</i>');
      return;
   }

      // It's the little things that make a house a home.
   $bSingular = $report->lNumFields==1;
   echoT(
       'There '.($bSingular ? 'is' : 'are').' '
       .$report->lNumFields.' field'.($bSingular ? '' : 's')
       .' displayed in this report.<br><br>');

   $clsUpDown = new up_down_top_bottom;
   $clsUpDown->lMax = $report->lNumFields;

   echoT(
      '<table border="0" cellspacing="5">'."\n");
   $iFldCnt = 1; $idx = 0;
   foreach ($report->fields as $field){
      $lFieldID  = $field->lKeyID;

      $clsUpDown->strLinkBase =
         '<a href="'.base_url()."index.php/creports/display_order/moveSort/$lReportID/$lFieldID/";
      $clsUpDown->upDownLinks($idx);
      echoT('<tr>');

      if ($field->lTableID <= 0){
         $strTable = $field->strUserTableName;
      }else {
         $strTable = '<b>['.$field->strAttachLabel.']</b> ['.htmlspecialchars($field->strUserTableName).']';
      }

      echoT(
             '<td style="width: 20pt;">&nbsp;</td>
              <td class="enpRpt" style="font-size: 7pt;" nowrap>'
                 .$clsUpDown->strUp.$clsUpDown->strDown
                 .'&nbsp;&nbsp;&nbsp;&nbsp;'
                 .$clsUpDown->strTop.$clsUpDown->strBottom.'
              </td>
              <td class="enpRpt" style="vertical-align: middle;">'
                 .$strTable.'
              </td>
              <td class="enpRpt" style="vertical-align: middle;">'
                 .htmlspecialchars($field->strUserFN).'
              </td>
              ');
      echoT('</tr>');
      ++$iFldCnt; ++$idx;
   }
   echoT('</table>');

