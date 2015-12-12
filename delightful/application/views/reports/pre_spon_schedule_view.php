<?php

global $genumDateFormat;

if ($lNumEvents == 0){
   echoT('<br><i>There are no <b>'.$strLabel.'</b> events to report!</i><br><br>');
}else {

   $attributes =
       array(
            'name'     => 'frmGroupRpt'//,
//            'onSubmit' => 'return(verifyBoxChecked(\'frmGroupRpt\', \'chkEvent[]\', \' (Event selection)\'))'
            );

   echoT(form_open('reports/pre_vol_schedule/run',
                   $attributes));


   echoT('<br><br>'
         .strHTMLSetClearAllButton(true,  'frmGroupRpt', 'chkEvent[]', 'Select All ').'&nbsp;&nbsp;'
         .strHTMLSetClearAllButton(false, 'frmGroupRpt', 'chkEvent[]', 'Clear All').'<br>'
      );
   
   echoT('<div style="vertical-align: middle;">
             Please select the events you wish to view:<br><br></div>');

   $lHeight = 30*$lNumEvents;
   if ($lHeight > 160) $lHeight = 160;
   if ($lHeight < 45)  $lHeight = 45;
   echoT('
      <div id="scrollCB" style="height:'.$lHeight.'px;   width:445px; overflow:auto;  border: 1px solid black;">'."\n\n");
     
     
   echoT('<table class="enpRpt" style="width: 100%">');
   foreach ($events as $event){
      $lEventID = $event->lKeyID;
         echoT('
            <tr>
               <td class="enpRpt" style="width: 10px; vertical-align:top;">
                  <input type="checkbox" name="chkEvent[]" value="'.$lEventID.'">
               </td>
               <td class="enpRpt" style="vertical-align:top; width: 220px;">'
                  .htmlspecialchars($event->strEventName).'
               </td>
               <td class="enpRpt" style="vertical-align:top; width: 220px;">'
                  .date($genumDateFormat, $event->dteEventStart).' - '
                  .date($genumDateFormat, $event->dteEventEnd).'
               </td>
            </tr>');
   }
   echoT('</table></div><br>

          <input type="submit" name="cmdSubmit" value="Run Report"
              onclick="return(verifyBoxChecked(\'frmGroupRpt\', \'chkEvent[]\', \' (Event selection)\'));"
              style=""
              class="btn"
                 onmouseover="this.className=\'btn btnhov\'"
                 onmouseout="this.className=\'btn\'">');

   echoT(form_close('<br>'));
}
