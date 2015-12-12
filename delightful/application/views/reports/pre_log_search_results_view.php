<?php

echoT('Searching for <b>"'.htmlspecialchars($strSearch).'"</b> <i>('.$strSearchTypeLabel.')</i><br>');

$strPadding = ' padding: 1px 3px 1px 3px; ';
foreach ($searchResults as $sr){
   openBlock('Search Results: '.$sr->strLabel, '');
   if ($sr->lNumResults==0){
      echoT('<i><span style="font-size: 11pt;">No matches</i></font><br>');
   }else {
      foreach ($sr->matches as $match){
         echoT('
         <table class="enpRptView" style="width:500pt;">
            <tr>
               <td class="enpViewLabel" style="width: 60pt; '.$strPadding.'">
                  Context:
               </td>
               <td class="enpView" style="'.$strPadding.'">'
                  .$match->searchInfo.'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel" style="'.$strPadding.'">
                  Links:
               </td>
               <td class="enpView" style="'.$strPadding.'">'
                  .$match->links.'
               </td>
            </tr>
            <tr>
               <td class="enpViewLabel" style="'.$strPadding.'">
                  Matching Text:
               </td>
               <td class="enpView" style="'.$strPadding.'">'
                  .$match->textHighlighted.'
               </td>
            </tr>
         
         ');         
         echoT('</table><br>');
      }
   }
/*   
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<pre>'); print_r($sr); echo('</pre></font><br>');   
*/   
   
   closeBlock();

}
