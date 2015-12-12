<?php

$lIndent = 15;

foreach ($events as $event){
   echoT('<font style="font-size: 12pt;"><b><u>'.htmlspecialchars($event->strEventName).'</u></b></font><br>');

   echoT('<table>');
   foreach ($event->dates as $oneDate){
      echoT('
               <tr>
                  <td colspan="5" style="font-size: 11pt;">'
                     .date('l, F jS, Y', $oneDate->dteEvent).'&nbsp;'
                     .strLinkView_VolEventDate($oneDate->lKeyID, 'View event date', true).'
                  </td>
               </tr>');
      if ($oneDate->lNumShifts==0){
         echoT('<tr>
                   <td style="width: '.$lIndent.'pt;">&nbsp;</td>
                   <td colspan="4">
                      <i>No shifts</i>
                   </td>
                </tr>');
      }else {
         foreach ($oneDate->shifts as $shift){
            echoT('<tr>
                   <td style="width: '.$lIndent.'pt;">&nbsp;</td>
                   <td colspan="4" style="font-size: 10pt;">
                      Shift: <b>'.htmlspecialchars($shift->strShiftName).'</b> <font style="font-size: 9pt;">('
                      .date('g:i A', $shift->dteEventStartTime).'&nbsp;/&nbsp;'
                      .$shift->enumDuration.')</font>
                   </td>
                </tr>');
            if ($shift->lNumVols==0){
               echoT('<tr>
                         <td style="width: '.$lIndent.'pt;">&nbsp;</td>
                         <td style="width: '.$lIndent.'pt;">&nbsp;</td>
                         <td colspan="3" style="font-size: 9pt;">
                            <i>No volunteers</i>
                         </td>
                      </tr>');
            }else {
               foreach ($shift->vols as $vol){
                  echoT('<tr>
                         <td style="width: '.$lIndent.'pt;">&nbsp;</td>
                         <td style="width: '.$lIndent.'pt;">&nbsp;</td>
                         <td colspan="3" style="font-size: 9pt;">'
                            .$vol->strSafeNameFL.'&nbsp;'
                            .strLinkView_PeopleRecord($vol->lPeopleID, 'View people record', true).'
                         </td>
                      </tr>');
               }
            }
            
         }
      }
      echoT('<tr><td colspan="5">&nbsp;</td></tr>');
   }

   echoT('</table>');
}
   
/*
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<pre>'); print_r($dates); echo('</pre></font><br>');    
*/
