<?php
   global $genumDateFormat;

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '160pt';

   $attributes = array('name' => 'frmTSProjects', 'id' => 'frmAddEdit');
   echoT(ts_util\strTSTLogOverviewBlock($clsRpt, $tst, $logRec, false, false));

   if ($logRec->lNumEntries == 0){
      echoT('<br><i>There are no entries in this time sheet!</i><br><br>');
      return;
   }
   
   openBlock('Time Sheet', '');
   echoT('
      <table class="enpRpt" style="width: 600pt;">
         <tr>
            <td class="enpRptLabel" style="width: 70pt;">
               Date
            </td>
            <td class="enpRptLabel">
               Time In
            </td>
            <td class="enpRptLabel">
               Time Out
            </td>
            <td class="enpRptLabel">
               Duration
            </td>
            <td class="enpRptLabel">
               Location
            </td>
            <td class="enpRptLabel">
               Notes
            </td>
         </tr>');
   $b24Hr = $logRec->b24HrTime;
   $dteGroup = -1;
   $lTotMin = 0;
   foreach ($logRec->logEntries as $le){
      $dteLogEntry = $le->dteLogEntry;
      if ($dteGroup != $dteLogEntry){
         $strDate = '<b>'.date($genumDateFormat, $le->dteLogEntry).'</b>';
         $dteGroup = $dteLogEntry;
      }else {
         $strDate = '&nbsp;';
      }
      $lDuration = $le->lTimeOutMin - $le->lTimeInMin;
      $lTotMin += $lDuration;
      echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align: center;">'
                  .$strDate.'
               </td>
               <td class="enpRpt" style="text-align: right;" nowrap>'
                  .strFormattedTimeViaMinutes ($le->lTimeInMin, $b24Hr).'
               </td>
               <td class="enpRpt" style="text-align: right;" nowrap>'
                  .strFormattedTimeViaMinutes ($le->lTimeOutMin, $b24Hr).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .strDurationViaMinutes($lDuration).'
               </td>
               <td class="enpRpt" style="width: 120pt;">'
                  .htmlspecialchars($le->strLocation).'
               </td>
               <td class="enpRpt" style="width: 375pt;">'
                  .nl2br(htmlspecialchars($le->strNotes)).'
               </td>
            </tr>');
   }
   
   echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="text-align: left;" colspan="3">
               <b>TOTAL
            </td>
            <td class="enpRpt" style="text-align: center;"><b>'
               .strDurationViaMinutes($lTotMin).'</b>
            </td>
            <td class="enpRpt" colspan="2">
               &nbsp;
            </td>
         </tr>');
   echoT('</table>'); closeBlock();
   
   
   openBlock('Project Assignments', '');
   echoT('
      <table class="enpRpt" style="width: 600pt;">
         <tr>
            <td class="enpRptLabel">
               Project
            </td>
            <td class="enpRptLabel">
               Duration
            </td>
            <td class="enpRptLabel">
               Notes
            </td>
         </tr>');
         
   $lTotMin = 0;
   foreach ($projects as $proj){
      $lTotMin += $proj->lMinutesToProject;
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt">'
               .htmlspecialchars($proj->strProjectName).'
            </td>
            <td class="enpRpt" style="text-align: center;">'
               .strDurationViaMinutes($proj->lMinutesToProject).'
            </td>
            <td class="enpRpt" style="width: 375pt;">'
               .nl2br(htmlspecialchars($proj->strNotes)).'
            </td>
         </tr>');
   }   
   echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="text-align: left;" >
               <b>TOTAL
            </td>
            <td class="enpRpt" style="text-align: center;"><b>'
               .strDurationViaMinutes($lTotMin).'</b>
            </td>
            <td class="enpRpt" colspan="1">
               &nbsp;
            </td>
         </tr>');
   
   echoT('</table>'); closeBlock();




