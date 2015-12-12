<?php

   echoT('<span style="color: red; font-size: 12pt;">'
      .'<b>A problem was detected in your report!</b><br><br>
        One or more personalized fields are no longer available for reporting:</span><br><br>');
        
   echoT(anchor('creports/run_creport/removeBadFields/'.$lReportID, 'Click here')
         .' to remove these fields from your report.<br><br>');   
        
   if ($lNumBad_Display > 0) displayBadFields('Display Fields', $badFields_Display);
   if ($lNumBad_Search  > 0) displayBadFields('Search Fields',  $badFields_Search);
   if ($lNumBad_Sort    > 0) displayBadFields('Sorting Fields', $badFields_Sort);
   
   function displayBadFields($strLabel, $badFields){
   //---------------------------------------------------------------------
   // 
   //---------------------------------------------------------------------      
      echoT('
        <table class="enpRpt">
           <tr>
              <td class="enpRptTitle" colspan="5">'
                 .$strLabel.' No Longer Available for Reporting
              </td>
           </tr>');
           
      echoT('
         <tr>
            <td class="enpRptLabel">
               Parent Table
            </td>
            <td class="enpRptLabel">
               Table
            </td>
            <td class="enpRptLabel">
               Field ID
            </td>
            <td class="enpRptLabel">
               Field
            </td>
         </tr>');
         
      foreach ($badFields as $bf){
         echoT('
            <tr>
               <td class="enpRpt">'
                  .$bf->enumParentTable.'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($bf->strUserTableName).'
               </td>
               <td class="enpRpt" style="text-align: center; width: 40pt;">'
                  .str_pad($bf->lFieldID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td class="enpRpt">'
                  .(is_null($bf->strUserFN) ? '&lt;removed&gt;' : htmlspecialchars($bf->strUserFN)).'
               </td>
            </tr>');
      }           
      echoT('</table><br>');      
   }
        