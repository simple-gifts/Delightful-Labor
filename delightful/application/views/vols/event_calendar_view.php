<?php

   echoT('<br>'
        .strLinkAdd_VolEvent('Add event', true).'&nbsp;'
        .strLinkAdd_VolEvent('Add event', false).'<br>');


   foreach ($events as $event){
      openBlock('Calendar of Events for <b>'.strXlateMonth($event->lMonth).' '.$event->lYear.'</b>', '');
      
      if ($event->lNumDates==0){
         echoT('No events scheduled for this month');
      }else {
         $dteGroup = -1;
         echoT('<ul style="list-style-type: none; padding:0; margin:0;">');
         foreach ($event->dates as $edate){
            $dteEvent = $edate->dteEvent;
            $lDateID  = $edate->lDateKeyID;
            if ($dteEvent != $dteGroup){
               if ($dteGroup > 0) echoT('</ul>');
               $dteGroup = $dteEvent;
               echoT('
                         <li><b>'
                            .date('l \t\h\e jS \o\f F, Y', $edate->dteEvent).'</b>
                         <ul style="
                                 margin-left:-20px; 
                                 margin-right:0px; 
                                 list-style:none; 
                             ">');
            }             
            echoT('<li>'
                      .htmlspecialchars($edate->strEventName)
                      .'&nbsp;'.strLinkView_VolEvent($edate->lVolEventID, 'View event', true)
                      .'<ul style="list-style-type: none; margin-left:-20px; ">');
                      
            if ($edate->lNumShifts==0){
               echoT('<li><i>No shifts associated with this event for this date</i><br><br></li></ul>');
            }else {
               foreach ($edate->shifts as $shift){
                  echoT('<li>'.htmlspecialchars($shift->strShiftName)
                     .' ('.$shift->dteStartTime.' / ' .$shift->enumDuration
                     .')'
                     .strLinkView_VolEventDate($lDateID, 'View shifts', true)
                     .'<br>Volunteers:<ul>');
                     
                  if ($shift->lNumVols==0){
                     echoT('<li><i>No volunteers assigned</i><br><br></li>');
                  }else {
                     foreach ($shift->vols as $vol){
                        $lVolID = $vol->lVolID;
                        echoT('<li>'.htmlspecialchars($vol->strFName.' '.$vol->strLName)
                             .strLinkView_Volunteer($lVolID, 'View volunteer record', true)
                             .'</li>');
                     }
                  }
                     
                  echoT(
                      '</ul><br></li>');
               }
               echoT('</ul><br>');
            }
            
         }
         echoT('</ul>');
      
      }
      
      closeblock();
      
   }