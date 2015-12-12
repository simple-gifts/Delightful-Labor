<?php

for ($idx=0; $idx<$lNumCharts; ++$idx){
   $chart = &$charts[$idx];
   $strDivIDX = str_pad(($idx+1), 2, '0', STR_PAD_LEFT);
   openBlock($chart->sectionLabel, '');
   if ($chart->bError){
      echoT($chart->strError);
   }else {
      if ($chart->lNumRecs == 0){
         echoT($chart->nobodyHomeMsg.'<br><br>');
      }else {
         echoT('<div id="chart_'.$idx.'_div" style="text-align: left; width:500; height:300"></div>');
         if ($chart->bShowDetails){
            if ($chart->enumDetails == 'status'){
               showChartStatusDetails($chart, $bActive);
            }else {
               showChartStatusCatDetails($chart, $bActive);
            }
         }
      }
   }
   closeBlock();
}

function showChartStatusCatDetails(&$chart, $bActive){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------   
   echoT('
      <table class="enpRpt">
         <tr>
            <td class="enpRptLabel">
               Status Category
            </td>
            <td class="enpRptLabel">
               # Clients
            </td>
         </tr>');
         
   foreach ($chart->details as $detail){
      $lNumClients = $detail->lNumClients;
      if ($lNumClients > 0){
         $strLink = ' '.strLinkView_ClientViaStatCatID(
                            $detail->lStatusCatID, $bActive, 'View clients in this status category', true);
         echoT('
            <tr class="makeStripe">
               <td class="enpRpt">'
                  .htmlspecialchars($detail->strCatName).'
               </td>
               <td class="enpRpt" style="text-align: right; padding-right: 3px;">'
                  .number_format($lNumClients).$strLink.'
               </td>
            </tr>');
      }
   }            
   echoT('</table><br><br>');   
}

function showChartStatusDetails(&$chart, $bActive){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------   
   echoT('
      <table class="enpRpt">
         <tr>
            <td class="enpRptLabel">
               Status
            </td>
            <td class="enpRptLabel">
               # Clients
            </td>
         </tr>');
         
   foreach ($chart->details as $detail){
      $lNumClients = $detail->lNumClients;
      if ($lNumClients > 0){
         $strLink = ' '.strLinkView_ClientViaStatID($detail->lStatusID, $bActive, 'View clients with this status', true);
         echoT('
            <tr class="makeStripe">
               <td class="enpRpt">'
                  .htmlspecialchars($detail->status).'
               </td>
               <td class="enpRpt" style="text-align: right; padding-right: 3px;">'
                  .number_format($lNumClients).$strLink.'
               </td>
            </tr>');
      }
   }            
   echoT('</table><br><br>');   
}

