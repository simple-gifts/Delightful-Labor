<?php


   if ($lNumSortTerms == 0){
      echoT('<i>There are no sorting fields associated with this report.</i>');
      return;
   }

      // It's the little things that make a house a home.
   $bSingular = $lNumSortTerms==1;
   echoT(
       'There '.($bSingular ? 'is' : 'are').' '
       .$lNumSortTerms.' sorting term'.($bSingular ? '' : 's')
       .' associated with this report.<br><br>');

   $clsUpDown = new up_down_top_bottom;
   $clsUpDown->lMax = $lNumSortTerms;

   echoT(
      '<table border="0" cellspacing="5">'."\n");
   $iFldCnt = 1; $idx = 0;
   foreach ($sortTerms as $term){
      $lTermID  = $term->lKeyID;

      $clsUpDown->strLinkBase =
         '<a href="'.base_url()."index.php/creports/search_order/moveSort/$lReportID/$lTermID/";
      $clsUpDown->upDownLinks($idx);
      echoT('<tr>');

      if ($term->lTableID <= 0){
         $strTable = $term->strUserTableName;
      }else {
         $strTable = '<b>['.$term->strAttachLabel.']</b> '.htmlspecialchars($term->strUserTableName);
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
                 .htmlspecialchars($term->strFieldNameUser).'
              </td>
               <td class="enpRpt" style="vertical-align: middle;">'
                  .($term->bLarkAscending ? 'Ascending <span style="font-size: 8pt;">(A-Z)</span>' :
                                            'Descending <span style="font-size: 8pt;">(Z-A)</span>').'
               </td>
              ');
      echoT('</tr>');
      ++$iFldCnt; ++$idx;
   }
   echoT('</table>');

