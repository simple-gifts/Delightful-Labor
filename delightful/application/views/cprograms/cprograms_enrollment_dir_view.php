<?php
   echoT($strHTMLSummary);

   if ($lNumClients == 0){
      echoT('
         <br><i>There are no '.($bActiveOnly ? ' currently enrolled ' : '').' clients in program <b>'
             .htmlspecialchars($cprog->strProgramName).'</b>.</i><br><br>');
      return;
   }

   echoT('<br>
      <table class="enpRptC">
         <tr>
            <td class="enpRptTitle" colspan="6">
               Client '.$cprog->strSafeEnrollLabel.($bActiveOnly ? ' (Current) ' : '').': '
              .htmlspecialchars($cprog->strProgramName).'
            </td>
         </tr>');

   echoT('
         <tr>
            <td class="enpRptLabel">
               client ID
            </td>
            <td class="enpRptLabel">
               Name
            </td>
            <td class="enpRptLabel">
               '.$cprog->strSafeEnrollLabel.'
            </td>
         </tr>');

   foreach ($clients as $client){
      $lCID = $client->lClientID;
      
      if ($client->lNumSponsors == 0){
         $strSponsors = '<br><i>No sponsors</i>';
      }else {
         $strSponsors =
            '<br><b><i>Sponsors</i></b><br><ul style="margin-top: 0px; margin-left:-10px;">';
         foreach ($client->sponsors as $spon){
            if ($spon->bInactive){
               $strSStart = '<font style="color: #999;">';
               $strSEnd   = ' (inactive)</font>';
            }else {
               $strSStart = $strSEnd = '';
            }
         
            $strSponsors .= '<li>'.$strSStart.strLinkView_Sponsorship($spon->lKeyID, 'View sponsorship', true)
               .'&nbsp;'.htmlspecialchars($spon->strFName.' '.$spon->strLName).$strSEnd.'</li>'."\n";
         }
         $strSponsors .= '</ul>';
      }
      
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="text-align: center; width: 40pt" nowrap>'
               .str_pad($lCID, 6, '0', STR_PAD_LEFT).'&nbsp;'
               .strLinkView_ClientRecord($lCID, 'View client record', true).'
            </td>
            <td class="enpRpt">'
               .htmlspecialchars($client->strClientLName.', '.$client->strClientFName)
               .$strSponsors.'
            </td>
            <td class="enpRpt" style="width: 270pt;">'
               .strEnrollmentTable($cprog, $lCID, $client->lNumEnrollments, $client->erecs).'
            </td>
         </tr>');
   }

   echoT('
      </table>');

   function strEnrollmentTable(&$cprog, $lCID, $lNumEnrollments, $erecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      
      $lATableID = $cprog->lAttendanceTableID;
      
      if ($lNumEnrollments == 0) return('<i>No enrollments</i>');

      $strOut =
         '<table class="enpRpt" width="100%">
            <tr>
               <td class="enpRptLabel" style="background-color: #e6e6e6; 
                        vertical-align: middle; text-align: center; width: 120pt;" rowspan="2">
                  '.$cprog->strSafeEnrollLabel.' Dates
               </td>
               <td class="enpRptLabel" style="background-color: #e6e6e6; 
                         vertical-align: middle; text-align: center; width: 40pt;" rowspan="2">
                  Active?
               </td>
               <td class="enpRptLabel" style="background-color: #e6e6e6; text-align: center;" colspan="2">
                  '.$cprog->strSafeAttendLabel.'
               </td>
            </tr>
            <tr>
               <td class="enpRptLabel" style="background-color: #e6e6e6; text-align: center;"">
                  # Days
               </td>
               <td class="enpRptLabel" style="background-color: #e6e6e6; text-align: center;"">
                  # Hours
               </td>
            </tr>';
            
      foreach ($erecs as $erec){      
         $eRecID = $erec->lKeyID;
         $strLinkERec = strLinkView_CProgEnrollRec($cprog->lEnrollmentTableID, $lCID, $eRecID, 'View enrollment record', true);
         if (is_null($erec->dteEnd) || $erec->dteEnd == 0){
            $strDateEnd = 'ongoing';
         }else {
            $strDateEnd = date($genumDateFormat, $erec->dteEnd);
         }
         
         if (isset($erec->strFlagsTable)){
            $strCFlags = '&nbsp;'.$erec->strFlagsTable;
         }else {
            $strCFlags = '';
         }
         
         $strOut .= '
               <tr>
                  <td class="enpRpt">'
                     .$strLinkERec.'&nbsp;'
                     .date($genumDateFormat, $erec->dteStart).' - '.$strDateEnd.$strCFlags.'
                  </td>
                  <td class="enpRpt" style="text-align: center;">'
                     .($erec->bCurrentlyEnrolled ? 'Yes' : 'No').'
                  </td>
                  <td class="enpRpt" style="text-align: center;">'
                     .$erec->lNumDaysAttended.'
                  </td>
                  <td class="enpRpt" style="text-align: center;">'
                     .number_format($erec->sngNumHours, 2).'&nbsp;'
                     . strLinkView_CProgAttendanceViaCID($lATableID, $eRecID, $lCID, 'View attendance', true).'
                  </td>
               </tr>';
      }

      $strOut .=
         '</table>';
      return($strOut);
   }




