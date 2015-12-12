<?php

for ($idx=0; $idx<$lNumCharts; ++$idx){
   $chart = &$charts[$idx];

   $strDivIDX = str_pad(($idx+1), 2, '0', STR_PAD_LEFT);
   openBlock($chart->sectionLabel, '');
   if ($chart->bError){
      echoT($chart->strError);
   }else {
//      echoT('<div id="chart_'.$strDivIDX.'_div" style="text-align: left; width:500; height:300"></div>');
      echoT('<div id="'.$chart->strDivID.'" style="text-align: left; width:500; height:300"></div>');
   }
   if ($idx==2) showClientAgeTable($ageRanges);
   closeBlock();

}


function showClientAgeTable(&$ageRanges){
   echoT(
      '<table class="enpRpt">
         <tr>
            <td class="enpRptTitle" colspan="3">
               Active Clients By Age
            </td>
         </tr>');
         
   echoT('         
         <tr>
            <td class="enpRptLabel">
               Age Range
            </td>
            <td class="enpRptLabel">
               Count
            </td>
         </tr>');
         
   $idx = 0;
   foreach ($ageRanges as $ageGroup){      
      echoT('         
         <tr>
            <td class="enpRpt">'
               .$ageGroup['label'].'
            </td>
            <td class="enpRpt" style="text-align: right;">'
               .$ageGroup['numClients'].'&nbsp;'.strLinkView_RptClientAge($idx, 'View details', true).'
            </td>
         </tr>');
   
         ++$idx;
      }
         
         
         
   echoT('</table>');         
}

