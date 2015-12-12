<?php
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->bStartOpen   = true;
   $closeAttributes = new stdClass;
   $closeAttributes->bCloseDiv = true;
   $idx = 0;
   foreach ($eventsMonths as $eMonth){
      $strDate = $eMonth->strMonth.' '.$eMonth->lYear;
      $attributes->divID        = 'month'.$idx;
      $attributes->divImageID   = 'divImg'.$idx;
      openBlock($strDate, '', $attributes);

      showMonthlyCalendar($eMonth);

      closeBlock($closeAttributes);
      ++$idx;
   }
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$eventsMonths   <pre>');
echo(htmlspecialchars( print_r($eventsMonths[2], true))); echo('</pre></font><br>');
// ------------------------------------- */


   function showMonthlyCalendar($eMonth){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      
      if ($eMonth->lNumEvents == 0){
         echoT('<i>There are no events scheduled for this month.<br></i>');
         return;
      }
      echoT('
        <table>
           <tr>
              <td style="width: 120pt;"><b>Event</b></td>
              <td style="width: 80pt;"><b>Date</b></td>
              <td style="width: 100pt;"><b>Shift</b></td>
              <td style="width: 80pt;"><b>Start Time</b></td>
              <td style="width: 80pt;"><b>Duration</b></td>
              <td ><b>Notes</b></td></tr>');

      if ($eMonth->lNumShifts==0){
         echoT('
            <tr>
               </td>
               <td colspan="6">
                   <i>You are not scheduled for any shifts</i>
               </td>
            </tr>');
      }else {
         foreach ($eMonth->shifts as $shift){
            if ($shift->bFuture){
               $strStyle = '';
            }elseif ($shift->bPast){
               $strStyle = 'color: #888888; font-style: italic;';
            }else {
               $strStyle = 'color: #8a580a; font-weight: bold;';
            }
            echoT('
                 <tr class="makeStripe">
                    <td style="vertical-align: top; '.$strStyle.'">'.htmlspecialchars($shift->strEventName).'</td>
                    <td style="vertical-align: top; '.$strStyle.'">'.date($genumDateFormat.' (D)', $shift->dteEvent).'</td>
                    <td style="vertical-align: top; '.$strStyle.'">'.htmlspecialchars($shift->strShiftName).'</td>
                    <td style="vertical-align: top; '.$strStyle.'">'.$shift->dteShiftStartTime.'</td>
                    <td style="vertical-align: top; '.$strStyle.'">'.$shift->enumDuration.'</td>
                    <td style="vertical-align: top; '.$strStyle.'">'.nl2br(htmlspecialchars($shift->strShiftDescription)).'</td></tr>');
         }
      }

      echoT('
         </table>');
   }


