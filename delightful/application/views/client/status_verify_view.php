<?php

   $lTotRecs = $lNumGood + $lNumBad;
   if (($lTotRecs) == 0){
      echoT('<br><br><i><span style="font-size: 11pt;">There are no client records in your database!<br><br></i></span>');
      return;
   }

   if ($bCorrected){
      echoT('<br>
         <i>The following client records were corrected (default status record added).</i><br><br>');
   }else {
      echoT('<br>
         <i>This utility reviews the client records and reports any records that do not<br>
            have at least one associated status record. This is an internal consistency check.<br><br>
            The system can optionally create default status records for any clients found<br>
            without a status record.</i><br><br>');

      echoT('<b>Number of client records reviwed:</b>  '.number_format($lTotRecs).'<br>');
      echoT('<b>Number of records w/one or more status:</b>  '.number_format($lNumGood).'<br>');
      echoT('<b>Number of records w/missing status:</b>  '.number_format($lNumBad).'<br><br>');

      if ($lNumBad == 0) return;

      echoT(anchor('clients/client_verify/statusCorrect', 'Click here')
               .' to correct client records.<br><br>');
   }

   echoT('<table class="enpRpt">
      <tr>
         <td class="enpRptTitle" colspan="5">
            Clients with missing status categories
         </td>
      </tr>');

   echoT('
      <tr>
         <td class="enpRptLabel">
            clientID
         </td>
         <td class="enpRptLabel">
            Name
         </td>
         <td class="enpRptLabel">
            Status Category
         </td>
      </tr>');

   foreach ($badStatus as $bad){
      $lClientID = $bad->lKeyID;
      echoT('
         <tr>
            <td class="enpRpt" style="text-align: center;">'
               .str_pad($lClientID, 6, '0', STR_PAD_LEFT).'&nbsp;'
               .strLinkView_ClientRecord($lClientID, 'View client record', true).'
            </td>
            <td class="enpRpt">'
               .htmlspecialchars($bad->strLName.', '.$bad->strFName).'
            </td>
            <td class="enpRpt">'
               .htmlspecialchars($bad->strCatName).'
            </td>
         </tr>');
   }

   echoT('</table>');
