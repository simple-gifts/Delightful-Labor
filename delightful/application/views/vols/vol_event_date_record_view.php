<?php


   $params = array('enumStyle' => 'terse');
   $clsRpt = new generic_rpt($params);

   $clsRpt->strWidthLabel = '120pt';

   echoT(strLinkAdd_VolEventDate($lEventID, 'Add additional date', true)
               .'&nbsp;'
               .strLinkAdd_VolEventDate($lEventID, 'Add additional date', false).'<br>');

   showEventDateInfo($clsRpt, $lDateID,        $lEventID,
                     $edate,  $lNumEventDates, $shifts,
                     $lNumShifts, $lNumVolsTot);



function showEventDateInfo($clsRpt, $lEventDateID, $lEventID,
                           $clsED, $lNumEventDates, &$shifts,
                           $lNumShifts, $lNumVolsTot){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;

   $bShowClone = $lNumEventDates > 1;

   openBlock('Event Date',
              strLinkEdit_VolEventDate($lEventID, $lEventDateID, 'Edit event date', true)
              .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
              .strLinkRem_VolEventDate($lEventID, $lEventDateID, 'Remove this date from the event', true, true));
   echoT(
       $clsRpt->openReport()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Event Date ID:')
      .$clsRpt->writeCell (str_pad($lEventDateID, 5, '0', STR_PAD_LEFT))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Event Date:')
      .$clsRpt->writeCell (date($genumDateFormat.' (l)', $clsED->dteEvent))
      .$clsRpt->closeRow  ());


   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('# Shifts:')
      .$clsRpt->writeCell ('
                  <table border="0" cellpadding="0" cellspacing="0">
                     <tr>
                        <td style="vertical-align: top;">'
                          .$lNumShifts.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </td>
                        <td>'
                          .strLinkAdd_VolEventDateShift($lEventDateID, 'Add new shift', true).'&nbsp;
                        </td>
                        <td style="vertical-align: top;">'
                          .strLinkAdd_VolEventDateShift($lEventDateID, 'Add new shift', false).'
                        </td>
                     </tr>
                  </table>',
                         '')
      .$clsRpt->closeRow  ()

      .$clsRpt->closeReport());


   if ($lNumShifts > 0){
      echoT('
         <table border="0">
         <tr>
            <td style="font-weight: bold;">
               &nbsp;
            </td>
            <td style="font-weight: bold; width: 60pt; text-decoration:underline; vertical-align: bottom;">
               Shift ID
            </td>
            <td style="font-weight: bold; width: 90pt; text-decoration:underline; vertical-align: bottom;">
               Start Time
            </td>
            <td style="font-weight: bold; width: 110pt; text-decoration:underline; vertical-align: bottom;">
               Duration
            </td>
            <td style="font-weight: bold; width: 50pt; text-decoration:underline; text-align: center;">
               Vols Needed
            </td>
            <td style="font-weight: bold; width: 220pt; text-decoration:underline; vertical-align: bottom;">
               Vols Assigned
            </td>
            <td style="font-weight: bold; width: 220pt; text-decoration:underline; vertical-align: bottom;">
               Shift Info
            </td>
            <td style="font-weight: bold; text-decoration:underline; vertical-align: bottom; padding-right: 20px;">
               Hours Logged
            </td>
         </tr>');

      foreach ($shifts as $shift){
         $lShiftID = $shift->lKeyID;
         if ($bShowClone){
            $strClone = '&nbsp;'.strLinkClone_VolShift($lEventDateID, $lShiftID, 'Clone this shift', true);
         }else {
            $strClone = '';
         }

         $strJobCode = $shift->strJobCode.'';
         if ($strJobCode == ''){
            $strJobCode = '<i>(not set)</i>';
         }else {
            $strJobCode = htmlspecialchars($strJobCode);
         }

         if ($shift->lNumVols == 0){
            $strLinkVolEditHrs = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
         }else {
            $strLinkVolEditHrs = strLinkEdit_VolEventHrsViaShift($lEventID, $lShiftID, 'eventDate', 'Edit volunteer hours', true);
         }
         echoT('
            <tr>
               <td style="vertical-align: top;" nowrap>'
                  .strLinkEdit_VolEventDateShift($lEventDateID, $lShiftID, 'Edit shift', true)
                  .$strClone
                  .'&nbsp;&nbsp;&nbsp;'
                  .strLinkRem_VolEventDateShift($lEventDateID, $lShiftID, 'Remove shift', true, true).'
               </td>
               <td style="vertical-align: top; width: 50pt; text-align: center;">'
                  .str_pad($lShiftID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td style="vertical-align: top;">'
                  .date('g:i:s A', $shift->dteEventStartTime).'
               </td>
               <td style="vertical-align: top;">'
                  .$shift->enumDuration.'
               </td>
               <td style="vertical-align: top; text-align: center;">'
                  .$shift->lNumVolsNeeded.'
               </td>
               <td style="vertical-align: top;">'
                  .strVolsForShift($lShiftID, $shift, $lEventDateID, $lNumVolsTot).'
               </td>
               <td style="vertical-align: top; width: 220pt;"><b>'
                  .htmlspecialchars($shift->strShiftName).'</b><br>'
                  .'<b>Job code:</b> '.$strJobCode.'<br>'
                  .nl2br(htmlspecialchars($shift->strDescription)).'
               </td>
               <td style="vertical-align: top; text-align: right; padding-right: 20px;" nowrap>'
                  .number_format($shift->hoursLogged, 2)
                  .$strLinkVolEditHrs.'
               </td>
            </tr>');
      }

      echoT('
         </table>');
   }
   closeBlock();
}

function strVolsForShift($lShiftID, $shift, $lEventDateID, $lNumVolsTot){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $strTable = strLinkAdd_VolEventDateShiftVol($lEventDateID, $lShiftID, 'Add volunteer to this shift', true).'&nbsp;'
              .strLinkAdd_VolEventDateShiftVol($lEventDateID, $lShiftID, 'Add volunteer to this shift', false);

   if ($shift->lNumVols==0){
      $strTable .= '<br><i>No assigned volunteers</i>';
   }else {
      $strTable .= '<br><table>';
      foreach ($shift->vols as $clsV){
         $lVolAssignID = $clsV->lKeyID;
         $lVolID       = $clsV->lVolID;
         $strTable .= '
            <tr>
               <td>'
                  .strLinkView_Volunteer($lVolID, 'View volunteer record', true).'
               </td>
               <td>'
                  .strLinkRem_VolEventDateShiftVol(
                        $lVolAssignID, $lEventDateID,  $lShiftID,
                        'Remove volunteer from shift',
                        true,          true).'
               </td>
               <td>'
                  .$clsV->strSafeNameFL.'
               </td>
            </tr>';
      }
      $strTable .= '</table>';
   }

   return($strTable);
}



