<?php

echoT(strLinkAdd_VolEvent('Add Event', true).'&nbsp;'
     .strLinkAdd_VolEvent('Add Event', false).'<br><br>');


echoT($strAltView);


if ($lNumEvents == 0){
   echoT('<i>There are no events to display</i><br><br>');
}else {
   openVolEventTable();
   foreach($events as $event){
      writeVolEventRow($event);
   }
   closeVolEventTable();
}


function writeVolEventRow(&$event){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;

   $lEventID = $event->lKeyID;
   
   echoT('
      <tr>
         <td class="enpRpt" style="text-align: center;">'
            .strLinkView_VolEvent($lEventID, 'View event', true).'&nbsp;'
            .str_pad($lEventID, 5, '0', STR_PAD_LEFT).'
         </td>
         <td class="enpRpt" style="width: 150pt;">'
            .htmlspecialchars($event->strEventName).'
         </td>
         <td class="enpRpt">'
            .date($genumDateFormat.' (D)', $event->dteEventStart).'
         </td>
         <td class="enpRpt">'
            .date($genumDateFormat.' (D)', $event->dteEventEnd).'
         </td>
         <td class="enpRpt" style="text-align: center;">'
            .$event->lNumShifts.'
         </td>
         <td class="enpRpt" style="text-align: center;">'
            .$event->lTotVolsNeeded .'
         </td>
         <td class="enpRpt" style="text-align: center;">'
            .$event->lTotVolsAssigned.'&nbsp;'
            .strLinkView_VolEventAssignments($lEventID, 'View volunteer assignments for this event', true).'
         </td>
      </tr>');


}

function openVolEventTable(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT('
      <table class="enpRptC">
         <tr>
            <td class="enpRptLabel">
               event ID
            </td>
            <td class="enpRptLabel">
               Event
            </td>
            <td class="enpRptLabel">
               Start Date
            </td>
            <td class="enpRptLabel">
               End Date
            </td>
            <td class="enpRptLabel">
               Shifts
            </td>
            <td class="enpRptLabel">
               Vols Needed
            </td>
            <td class="enpRptLabel">
               Scheduled Vols.
            </td>
         </tr>');
}

function closeVolEventTable(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT('</table>');
}

