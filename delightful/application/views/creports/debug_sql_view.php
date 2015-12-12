<?php

   writeReportFields($report);
   echoT($strSearchExpression);
   
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__);
$zzzstrFile=substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1)));
echoT('<br><span style="font-family: monospace; font-size: 9pt;">'.$zzzstrFile.' Line: <b>'.__LINE__.":</b><br><b>
     \$sqlSelect=</b><br>".nl2br(htmlspecialchars($sqlSelect))."<br><br></span>\n");
echoT('<br><span style="font-family: monospace; font-size: 9pt;">'.$zzzstrFile.' Line: <b>'.__LINE__.":</b><br><b>\$sqlFrom=</b><br>".nl2br(htmlspecialchars($sqlFrom))."<br><br></span>\n");
echoT('<br><span style="font-family: monospace; font-size: 9pt;">'.$zzzstrFile.' Line: <b>'.__LINE__.":</b><br><b>\$sqlWhere=</b><br>".nl2br(htmlspecialchars($sqlWhere))."<br><br></span>\n");
echoT('<br><span style="font-family: monospace; font-size: 9pt;">'.$zzzstrFile.' Line: <b>'.__LINE__.":</b><br><b>\$sql=</b><br>".nl2br(htmlspecialchars($sqlSQL))."<br><br></span>\n");
/*      
*/
   
   
   
   
function writeReportFields(&$report){
   
   echoT('<table class="enpRpt">
      <tr>
         <td class="enpRptTitle" colspan="8">Report Fields</td>
      </tr>');
   echoT('
      <tr>
         <td class="enpRptLabel">&nbsp;</td>
         <td class="enpRptLabel">keyID</td>
         <td class="enpRptLabel">Field Name</td>
         <td class="enpRptLabel">sortIDX</td>
         <td class="enpRptLabel">fieldID</td>
         <td class="enpRptLabel">tableID</td>
         <td class="enpRptLabel">tableName</td>
      </tr>');
   $idx = 0;
   foreach ($report->fields as $field){
      echoT('
         <tr>
            <td class="enpRpt" style="text-align: center;">'.$idx                 .'</td>
            <td class="enpRpt" style="text-align: center;">'.$field->lKeyID       .'</td>
            <td class="enpRpt" style="text-align: left;"  >'.$field->strFieldName .'</td>
            <td class="enpRpt" style="text-align: center;">'.$field->lSortIDX     .'</td>
            <td class="enpRpt" style="text-align: center;">'.$field->lFieldID     .'</td>
            <td class="enpRpt" style="text-align: center;">'.$field->lTableID     .'</td>
            <td class="enpRpt" style="text-align: center;">'.$field->strUserTableName .'</td>
         </tr>');
      ++$idx;
   }
   echoT('</table><br>');
   
   
}


