<?php
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

      showMonthlyEvents($lVolID, $idx, $eMonth, $strDate);

      closeBlock($closeAttributes);
      ++$idx;
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
               <td style="width: 50pt; font-weight: bold;">Start</td>
               <td style="width: 50pt; font-weight: bold;">Duration</td>
               <td style="width: 80pt; font-weight: bold;">Vols Needed</td>
               <td style="width: 80pt; font-weight: bold; padding-right: 8px;">Vols Registered</td>
               <td style="width: 80pt; font-weight: bold;">Scheduled</td>
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
               if ($shift->bCurrentVolRegistered){
                  $strReg = '<font color="#4a0e5e"><b>YES</b>&nbsp;&nbsp;'
                        .anchor('vol_reg/vol_hours/regUnreg/false/'.$lShiftID.'/'.$shift->lCurVolAssignID, '(unregister)');
               }else {
                  $strReg = 'No&nbsp;&nbsp;&nbsp;&nbsp;'
                        .anchor('vol_reg/vol_hours/regUnreg/true/'.$lShiftID.'/0', '(register)');
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
                         .$shift->dteStartTime.'
                      </td>
                      <td>'
                         .$shift->enumDuration.'
                      </td>
                      <td style="text-align: center;">'
                         .$shift->lNumVolsNeeded.'
                      </td>
                      <td style="text-align: center;">'
                         .$shift->lNumVols.'
                      </td>
                      <td>'
                         .$strReg.'
                      </td>
                      <td>&nbsp;</td>
                   </tr>');
               $strDate = '&nbsp;';
            }
         }
      }
      echoT('</table>');

/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$eDates   <pre>');
echo(htmlspecialchars( print_r($eDates, true))); echo('</pre></font><br>');
die;
// ------------------------------------- */

   }

