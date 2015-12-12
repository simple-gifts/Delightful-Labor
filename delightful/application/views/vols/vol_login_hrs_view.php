<?php

   showCumulativeStats($lVolID, $dTotScheduledHrs, $dTotUnscheduledHrs);

   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $closeAttributes = new stdClass;
   $closeAttributes->bCloseDiv = true;
   $idx = 0;
   foreach ($eventsMonths as $eMonth){
      $strDate = $eMonth->strMonth.' '.$eMonth->lYear;
      $attributes->divID        = 'month'.$idx;
      $attributes->divImageID   = 'divImg'.$idx;
      openBlock($strDate, '', $attributes);

      showUnscheduledMonthlyEvents($lVolID, $idx, $eMonth, $strDate);
      showMonthlyEvents($lVolID, $idx, $eMonth, $strDate);

      closeBlock($closeAttributes);
      ++$idx;
   }

   function showUnscheduledMonthlyEvents($lVolID, $lMonthIDX, &$eMonth, $strDate){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      if ($eMonth->lNumUnscheduled == 0){
         echoT('<i>No unscheduled activities were logged for '.$strDate.'.</i>&nbsp;&nbsp;&nbsp;&nbsp;'
                .strLinkAdd_VolUnschedHrsAsVol($lVolID, 'Add an activity', true).'&nbsp;'
                .strLinkAdd_VolUnschedHrsAsVol($lVolID, 'Add an activity', false)
                 .'<br>');
         return;
      }

      $attributes = new stdClass;
      $attributes->lTableWidth  = 800;
      $attributes->lUnderscoreWidth = 300;

      $closeAttributes = new stdClass;
      $closeAttributes->bCloseDiv = true;
      $attributes->divID        = 'eUnAct_'.$lMonthIDX;
      $attributes->divImageID   = 'eUnActDivImg_'.$lMonthIDX;
      openBlock('Unscheduled Activities',
                 strLinkAdd_VolUnschedHrsAsVol($lVolID, 'Add an activity', true).'&nbsp;'
                .strLinkAdd_VolUnschedHrsAsVol($lVolID, 'Add an activity', false), $attributes);

      echoT('
         <table>
            <tr>
               <td>&nbsp;</td>
               <td style="width: 100pt;"><b>Date</b></td>
               <td style="width: 140pt;"><b>Activity</b></td>
               <td style="width: 40pt;text-align: right; padding-right: 10px;"><b># Hrs</b></td>
               <td style="width: 280pt;"><b>Notes</b></td>
            </tr>');

      foreach ($eMonth->unscheduled as $act){
         echoT('
               <tr>
                  <td style="vertical-align: top;">'
                      .strLinkEdit_VolUnschedHrsAsVol($lVolID, $act->lKeyID, 'Edit Volunteer Hours', true).'&nbsp;
                  </td>
                  <td style="vertical-align: top;">'
                      .date($genumDateFormat.' (D)', $act->dteActivityDate).'
                  </td>
                  <td style="vertical-align: top;">'
                     .htmlspecialchars($act->strActivity).'
                  </td>
                  <td style="text-align: right; padding-right: 10px; vertical-align: top;">'
                     .number_format($act->dHoursWorked, 2).'
                  </td>
                  <td style="vertical-align: top;">'
                     .nl2br(htmlspecialchars($act->strNotes)).'
                  </td>
               </tr>');
      }

      echoT('</table>');

      closeBlock($closeAttributes);
   }

   function showCumulativeStats($lVolID, $dTotScheduledHrs, $dTotUnscheduledHrs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock('Cumulative Hours', '');

      echoT('
         <table>
            <tr>
               <td>
                  <b>Scheduled hours:</b>
               </td>
               <td style="text-align: right;">'
                  .number_format($dTotScheduledHrs, 2).'
               </td>
               <td>
                  &nbsp;
               </td>
            </tr>
            <tr>
               <td>
                  <b>Unscheduled hours:</b>
               </td>
               <td style="text-align: right;">'
                  .number_format($dTotUnscheduledHrs, 2).'
               </td>
               <td>&nbsp;'
                     .strLinkAdd_VolUnschedHrsAsVol($lVolID, 'Add an activity', true).'&nbsp;'
                     .strLinkAdd_VolUnschedHrsAsVol($lVolID, 'Add an activity', false).'
                  &nbsp;
               </td>
            </tr>
         </table>');

      closeBlock();
   }

   function showMonthlyEvents($lVolID, $lMonthIDX, &$eMonth, $strDate){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($eMonth->lNumEvents == 0){
         echoT('<br><i>No events were scheduled for '.$strDate.'.</i><br>');
         return;
      }

      $attributes = new stdClass;
      $attributes->lTableWidth  = 800;
      $attributes->lUnderscoreWidth = 300;

      $closeAttributes = new stdClass;
      $closeAttributes->bCloseDiv = true;

      $idx = 0;
      foreach ($eMonth->events as $eDetails){
         $attributes->divID        = 'eDetail_'.$lMonthIDX.'_'.$idx;
         $attributes->divImageID   = 'eDetailDivImg_'.$lMonthIDX.'_'.$idx;
         openBlock(htmlspecialchars($eDetails->strEvent), '', $attributes);

         showDatesShifts($lVolID, $eDetails->dates, $eDetails->lNumDates);

         closeBlock($closeAttributes);
         ++$idx;
      }
   }

   function showDatesShifts($lVolID, &$eDates, $lNumDates){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      if ($lNumDates==0){
         echoT('<i>No dates have been scheduled for this event</i><br>');
         return;
      }

      echoT('
         <table width="100%">
            <tr>
               <td style="width: 120pt; font-weight: bold;">Date</td>
               <td style="width: 180pt; font-weight: bold;">Shift</td>
               <td style="width: 80pt; font-weight: bold;">Scheduled</td>
               <td style="width: 50pt; font-weight: bold;">Logged</td>
               <td>&nbsp;</td>
            </tr>');

      foreach ($eDates as $eDate){
         $lVolEventID = $eDate->lVolEventID;
         $strDate = date($genumDateFormat.' (D)', $eDate->dteEvent);
         if ($eDate->lNumShifts == 0){
            echoT('
                   <tr>
                      <td>'
                         .$strDate.'
                      </td>
                      <td colspan="3">
                         <i>No shifts were scheduled for this date</i>
                      </td>
                   </tr>');
         }else {
            foreach ($eDate->shifts as $shift){
               $lShiftID      = $shift->lShiftID;
               $dHrsScheduled = $shift->dHrsScheduled;
               $dHrsWorked    = $shift->dHrsWorked;
               if ($dHrsScheduled < 0.001){
                  $strScheduled = '<i>not scheduled</i>';
                  $strLogged    = '-';
                  $strHrsStyle  = 'text-align: center;';
               }else {
                  $strScheduled = $shift->enumHrsScheduled;
                  $strLogged    = number_format($dHrsWorked, 2);
                  $strHrsStyle  = 'text-align: right;';
                  if (bAllowAccess('volEditHours')){
                     $strLogged .= '&nbsp;'.strLinkEdit_VolEventHrsAsVol(
                            $lVolID, $lVolEventID, $lShiftID, 'Edit Volunteer Hours', true);

                  }
               }
               echoT('
                   <tr class="makeStripe">
                      <td>'
                         .$strDate.'
                      </td>
                      <td>'
                         .htmlspecialchars($shift->strShiftName).'
                      </td>
                      <td>'
                         .$strScheduled.'
                      </td>
                      <td style="width: 50pt;'.$strHrsStyle.'">'
                         .$strLogged.'
                      </td>
                      <td>&nbsp;</td>
                   </tr>');
               $strDate = '&nbsp;';
            }
         }
      }
      echoT('</table>');
   }



