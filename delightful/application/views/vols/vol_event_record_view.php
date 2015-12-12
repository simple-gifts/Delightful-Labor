<?php

$params = array('enumStyle' => 'terse');
$clsRpt = new generic_rpt($params);
$clsRpt->strWidthLabel = 80;

echoT(strLinkAdd_VolEvent('Add new volunteer event', true)
            .'&nbsp;'
            .strLinkAdd_VolEvent('Add new volunteer event', false).'<br>');

showEventInfo           ($clsRpt, $lEventID, $event);
showEventVolHours       ($clsRpt, $lEventID, $event, $dNumHrs);
showEventDates          ($lEventID, $event, $eventDates, $lNumDates);

showEventENPStats       ($clsRpt, $event);

function showEventVolHours($clsRpt, $lEventID, &$event, $dNumHrs){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'volHrs';
   $attributes->divImageID   = 'volHrsDivImg';

   openBlock('Volunteer Hours',
                strLinkView_VolEventHrs($lEventID, 'View volunteer hours for this event', true).'&nbsp;'
               .strLinkView_VolEventHrs($lEventID, 'View volunteer hours for this event', false),
               $attributes);
   echoT('Total volunteer hours worked: '.number_format($dNumHrs, 2).'<br />');

   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showEventInfo($clsRpt, $lEventID, &$event){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;

   openBlock('Event',
                strLinkEdit_VolEvent($lEventID, 'Edit this event', true)
               .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
               .strLinkRem_VolEvent($lEventID, 'Remove this event', true, true));

   echoT(
       $clsRpt->openReport()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Event ID:')
      .$clsRpt->writeCell (str_pad($lEventID, 5, '0', STR_PAD_LEFT))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Name:')
      .$clsRpt->writeCell (htmlspecialchars($event->strEventName))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Start Date:')
      .$clsRpt->writeCell (date($genumDateFormat.' (D)', $event->dteEventStart))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('End Date:')
      .$clsRpt->writeCell (date($genumDateFormat.' (D)', $event->dteEventEnd))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Details:')
      .$clsRpt->writeCell (nl2br(htmlspecialchars($event->strDescription)))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Location:')
      .$clsRpt->writeCell (nl2br(htmlspecialchars($event->strLocation)))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Contact:')
      .$clsRpt->writeCell (htmlspecialchars($event->strContact))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Phone:')
      .$clsRpt->writeCell (htmlspecialchars($event->strPhone))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Email:')
      .$clsRpt->writeCell (htmlspecialchars($event->strEmail))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Web Site:')
      .$clsRpt->writeCell (htmlspecialchars($event->strWebSite))
      .$clsRpt->closeRow  ()

      .$clsRpt->closeReport());
   closeBlock();
}

function showEventDates($lEventID, $event, &$eventDates, $lNumDates){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;

   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'volEDates';
   $attributes->divImageID   = 'volEDatesDivImg';
   openBlock('Event Dates',
                strLinkAdd_VolEventDate($lEventID, 'Add date to this event', true).'&nbsp;'
               .strLinkAdd_VolEventDate($lEventID, 'Add date', false).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
               .strLinkView_VolEventAssignments($lEventID, 'View volunteer assignements', true).'&nbsp;'
               .strLinkView_VolEventAssignments($lEventID, 'View volunteer assignements', false),
               $attributes);

   if ($lNumDates==0){
      echoT('<i>No dates scheduled for this event</i>');
   }else {
      echoT('<table border="0">
         <tr>
            <td style="font-weight: bold; width: 40pt;">
               &nbsp;
            </td>
            <td style="font-weight: bold; width: 50pt; text-align: center;">
               Date ID
            </td>
            <td style="font-weight: bold; width: 90pt;">
               Date
            </td>
            <td style="font-weight: bold;">
               Shifts
            </td>
            <td style="font-weight: bold;">
               &nbsp;
            </td>
         </tr>');
      foreach($eventDates as $eDate){
         $lEdateID = $eDate->lKeyID;
         echoT('
             <tr>
                <td style="text-align: center; vertical-align: top;">'
                   .strLinkView_VolEventDate($lEdateID, 'View date', true).'&nbsp;&nbsp;&nbsp;&nbsp;'
                   .strLinkRem_VolEventDate($lEventID, $lEdateID, 'Remove this event date', true, true,
                          '', true).'
                </td>
                <td style="vertical-align: top; text-align: center;">'
                   .str_pad($lEdateID, 5, '0', STR_PAD_LEFT).'
                </td>
                <td style="text-align: left; vertical-align: top;">'
                   .date($genumDateFormat.' (D)', $eDate->dteEvent).'
                </td>
                <td style="text-align: center; vertical-align: top; padding-right: 10px;">'
                   .$eDate->lNumShifts.'
                </td>
                <td style="text-align: left; vertical-align: top;">'
                   .showShiftInfo($eDate, $lEventID).'
                </td>
             </tr>
             ');
      }
      echoT('</table>');
   }
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showShiftInfo(&$eDate, $lEventID){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if ($eDate->lNumShifts==0){
      return('n/a');
   }else {
      $strOut =
          '<table width="100%" border="0">
             <tr>
               <td style="font-weight: bold; vertical-align: top;">
                  Shift Name
               </td>
               <td style="font-weight: bold; vertical-align: top;">
                  Volunteers
               </td>
               <td style="font-weight: bold; vertical-align: top;">
                  Starts
               </td>
               <td style="font-weight: bold; vertical-align: top;">
                  Duration
               </td>
               <td style="font-weight: bold; vertical-align: top;" nowrap>
                  Hours Logged
               </td>
            </tr>
          ';
      foreach ($eDate->shiftInfo as $shift){
         $lVolsNeeded   = $shift->lNumVolsNeeded;
         $lVolsAssigned = $shift->lNumVolsAssigned;
         if ($lVolsNeeded > $lVolsAssigned){
            $strFont = '<font color="red">';
         }else {
            $strFont = '<font>';
         }
         $strVols = $lVolsAssigned.' of '.$lVolsNeeded;
         $lShiftID = $shift->shiftID;

         $strJobCode = $shift->strJobCode.'';
         if ($strJobCode == ''){
            $strJobCode = '<i>(not set)</i>';
         }else {
            $strJobCode = htmlspecialchars($strJobCode);
         }

         $strOut .= '
               <tr>
                  <td style="align: left; width: 150pt; padding-right: 5px;">'
                     .htmlspecialchars($shift->shiftName).'<br>
                     <b>Job code:</b> '.$strJobCode.'
                  </td>
                  <td style="align: left; width: 90px; padding-right: 5px;">'
                     .$strFont.$strVols.'</font>
                  </td>
                  <td style="align: left; width: 90px; padding-right: 5px;">'
                     .date('g:i:s A', $shift->dteEventStartTime).'
                  </td>
                  <td style="align: left; width: 60pt;">'
                     .$shift->enumDuration.'
                  </td>
                  <td style="vertical-align: top; text-align: right; padding-right: 10px;" nowrap>'
                     .number_format($shift->hoursLogged, 2)
                     .strLinkEdit_VolEventHrsViaShift($lEventID, $lShiftID, 'event',
                                  'Edit volunteer hours', true, '', '13px').'
                  </td>
               </tr>';
      }
      $strOut .=
          '</table>';
      return($strOut);
   }
}

function showEventENPStats($clsRpt, $event){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'volEENP';
   $attributes->divImageID   = 'volEENPDivImg';
   openBlock('Record Information', '', $attributes);

   echoT(
      $clsRpt->showRecordStats($event->dteOrigin,
                            $event->strUCFName.' '.$event->strUCLName,
                            $event->dteLastUpdate,
                            $event->strULFName.' '.$event->strULLName,
                            $clsRpt->strWidthLabel));

   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}
















