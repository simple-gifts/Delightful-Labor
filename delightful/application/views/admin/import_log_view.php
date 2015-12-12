<?php  
   global $genumDateFormat;

   if ($lNumLogEntries > 0){
      echoT('<br>
         <table class="enpRptC">
            <tr>
               <td class="enpRptLabel" >
                  Log ID
               </td>
               <td class="enpRptLabel" >
                  Import Type
               </td>
               <td class="enpRptLabel" >
                  Date
               </td>
               <td class="enpRptLabel" >
                  # Records
               </td>
               <td class="enpRptLabel" >
                  By
               </td>
            </tr>
            ');

      foreach ($logEntries as $entry){            
         $lLogID = $entry->lKeyID;
         $strType = $entry->enumImportType;
         if ($strType == CENUM_CONTEXT_SPONSORPAY) $strType = 'Sponsor Payment';
         $strType = strtoupper(substr($strType, 0, 1)).substr($strType, 1);
         echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align: center;">'
                  .strLinkView_ImportEntries($lLogID, 'View import records', true).' '
                  .str_pad($lLogID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td class="enpRpt"  style="text-align: left;">'
                  .$strType.' 
               </td>
               <td class="enpRpt">'
                  .date($genumDateFormat.' H:i:s', $entry->dteOrigin).'               
               </td>
               <td class="enpRpt" style="text-align: right;">'
                  .number_format($entry->lNumRecs).'
               </td>
               <td class="enpRpt" style="text-align: left;">'
                  .$entry->strUserCSafeName.'
               </td>
            </tr>');
      }  
       echoT('</table>');      
   }else {
      echoT('<i>No imports have been logged in your database.</i>');
   }
         
?>         