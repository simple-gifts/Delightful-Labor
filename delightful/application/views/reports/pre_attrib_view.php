<?php

echoT('<br>');
$idx = 0;
foreach ($attribCounts as $aCnt){
   echoT('
      <div id="chart_'.str_pad($idx, 2, '0', STR_PAD_LEFT).'_div" 
                   style="text-align: left; width:320; height:180"></div><br>
   
      <table class="enpRpt" style="width: 300pt;">
         <tr>
            <td class="enpRptTitle" colspan="2">'
               .$aCnt->strEnumType.'
            </td>
         <tr>
            <td class="enpRptLabel">
               Attributed To
            </td>
            <td class="enpRptLabel" style="width: 60pt;">
               Count
            </td>
         </tr>');
         
   foreach ($aCnt->attribList as $aList){
      $strAttrib = $aList->strAttrib;
      $lCnt      = $aList->lCount;
      $lAttribID = $aList->lAttribID;
      if (is_null($strAttrib)){
         $strAttrib = '<i>(not attributed)<i>';
         $strLinkAttrib = strLinkView_RptAttrib($aCnt->enumType, 0, 'View '.$aCnt->strEnumType, true);
      }else {
         $strAttrib = htmlspecialchars($strAttrib);
         $strLinkAttrib = strLinkView_RptAttrib($aCnt->enumType, $lAttribID, 'View '.$aCnt->strEnumType, true);
      }
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt">'
               .$strAttrib.'
            </td>
            <td class="enpRpt" style="text-align: right;">'
               .number_format($lCnt).'&nbsp;'.$strLinkAttrib.'
            </td>
         </tr>');
   }
   
   
   echoT('</table><br><br><br><br>');
   ++$idx; 
      
}


