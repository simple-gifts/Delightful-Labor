<?php
   global $genumDateFormat;

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '160pt';

   $bSubmitted = !is_null($logRec->dteSubmitted);

   echoT(ts_util\strTSTLogOverviewBlock($clsRpt, $tst, $logRec, true, true));
   $b24Hr = $logRec->b24HrTime;

   $attributes = new stdClass;
   $attributes->lTableWidth      = 1000;
   openBlock('Entries', '', $attributes);

   echoT('
      <table border="0" style="">');

   $lTotMinutes = 0.0;
   foreach ($tsCalendar as $tsDay){
      echoT('
         <tr>
            <td style="border-bottom: 2px solid black;"><b>'
               .date($genumDateFormat.' (D)', $tsDay->calDate).'</b>
            </td>');
         echoT('
            <td style="padding-top: 7pt; padding-bottom: 2pt;
                       text-align: center; border-bottom: 2px solid black;">'
               .strLinkAdd_TimeSheetDailyEntry($lUserID, $lTSLogID, $tsDay->calDate, 'Add time sheet entry for this date', true).'
            </td>');
      if (isset($tsDay->dailyEntries)){
         if (!$bSubmitted){
            echoT('               
               <td style="width: 20pt;"><b>
                  &nbsp;
               </td>
               <td style="width: 20pt;"><b>
                  &nbsp;
               </td>
               <td style="width: 20pt;"><b>
                  &nbsp;
               </td>');
         }
         echoT('            
            <td style="width: 50pt; text-align: right;"><b>
               Time In
            </td>
            <td style="width: 50pt; text-align: right;"><b>
               Time Out
            </td>
            <td style="width: 50pt; text-align: center;"><b>
               Duration
            </td>
            <td style="width: 100pt;"><b>
               Location
            </td>
            <td style="width: 120pt;"><b>
               Notes
            </td>
         </tr>');
      }else {
         echoT('<td colspan="6">&nbsp;</td></tr>');
      }

      if (isset($tsDay->dailyEntries)){
         $lDateTot = 0;
         foreach ($tsDay->dailyEntries as $de){         
            $lDateTot    += $de->lTimeOut - $de->lTimeIn;
            $lTotMinutes += $de->lTimeOut - $de->lTimeIn;
            echoT('
               <td>&nbsp;</td>
               <td>&nbsp;</td>');
            if (!$bSubmitted){
               echoT('            
                  <td>&nbsp;</td>
                  <td style="text-align: center; vertical-align: top;">'
                     .strLinkEdit_TSLogEntries($de->lEntryID, 'Edit entry', true).'
                  </td>
                  <td style="text-align: center; vertical-align: top;">'
                     .strLinkRem_TSLogEntry($de->lEntryID, 'Remove entry', true, true).'
                  </td>');
            }
            echoT('
               <td style="vertical-align: top; text-align: right;">'
                  .strFormattedTimeViaMinutes($de->lTimeIn, $b24Hr).'
               </td>
               <td style="vertical-align: top; text-align: right;">'
                  .strFormattedTimeViaMinutes($de->lTimeOut, $b24Hr).'
               </td>
               <td style="vertical-align: top; text-align: center;">'
                  .strDurationViaMinutes($de->lTimeOut - $de->lTimeIn).'
               </td>
               <td style="vertical-align: top; width: 100pt;">'
                  .htmlspecialchars($de->location).'
               </td>
               <td style="vertical-align: top; width: 220pt;">'
                  .nl2br(htmlspecialchars($de->notes)).'
               </td>
            </tr>');
         }
         echoT('
            <tr>
               <td colspan="3" style="margin: 0px; height: 2px;border-spacing: 0px; padding: 0px;">&nbsp;</td>
               <td colspan="8" style="margin: 0px; height: 2px;border-spacing: 0px; padding: 0px;">'.strImgTagBlackLine(600, 2).'</td>
            </tr>');
         echoT('
            <tr>
               <td colspan="3">&nbsp;</td>
               <td colspan="4"><b>Total:</td>
               <td style="text-align: center;"><b>'
                  .strDurationViaMinutes($lDateTot).'
               </td>
            </tr>
            <tr><td colspan="8">&nbsp;</td></tr>');
      }else {
         echoT('
            <tr>
               <td colspan="7"><i>No entries for this date.</i></td>
            </tr>
            <tr>
               <td colspan="7">&nbsp;</td>
            </tr>');
      }
   }
   echoT('
       <tr>
          <td style="background-color: #b4cdd2;" colspan="7">
             <b>Total:</b>
          </td>
          <td style="background-color: #b4cdd2; text-align: right;">
             <b>'.strDurationViaMinutes($lTotMinutes).'</b>
          </td>
          <td style="background-color: #b4cdd2;">
             &nbsp;
          </td>
          <td style="background-color: #b4cdd2;">
             &nbsp;
          </td>
       </tr>');

   echoT('</table><br>');

   closeBlock();
