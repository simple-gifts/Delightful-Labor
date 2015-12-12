<?php
   global $genumDateFormat;
   $attributes =
       array(
            'name'     => 'frmCloneVolEvent'//,
//            'onSubmit' => 'return(verifyBoxChecked(\'frmCloneVolEvent\', \'chkDate[]\', \' (Event Dates)\'))'
            );

   echoT(form_open('volunteers/event_date_shifts_add_edit/cloneShift/'.$lEventID.'/'.$lEventDateID.'/'.$lShiftID,
                   $attributes));


   echoT(
          strHTMLSetClearAllButton(true,  'frmCloneVolEvent', 'chkDate[]', 'Select All Dates').'&nbsp;&nbsp;'
         .strHTMLSetClearAllButton(false, 'frmCloneVolEvent', 'chkDate[]', 'Clear All Dates').'<br>'
      );
   echoT('<b>Clone this shift to other event dates:</b><br>');
   echoT('<input type="checkbox" name="chkVols" value="TRUE" checked>Include shift volunteer assignments<br>');


   echoT('
      <div id="scrollCB" style="height:200px;   width:300px; overflow:auto;  border: 1px solid black;">'."\n\n");
     
   echoT('<table class="enpRpt" style="width: 100%">');
   foreach ($dates as $clsDate){
      $lDateID = $clsDate->lKeyID;
      if ($lEventDateID != $lDateID){
         echoT('
            <tr>
               <td class="enpRpt" style="width: 10px; vertical-align:top;">
                  <input type="checkbox" name="chkDate[]" value="'.$lDateID.'">
               </td>
               <td class="enpRpt" style="vertical-align:top; width: 180px;">'
                  .date($genumDateFormat.' (D)', $clsDate->dteEvent).'
               </td>
            </tr>');
      }
   }
   echoT('</table></div><br>

          <input type="submit" name="cmdSubmit" value="Clone Shift to Selected Dates"
              onclick="return(verifyBoxChecked(\'frmCloneVolEvent\', \'chkDate[]\', \' (Event Dates)\'))"
              style=""
              class="btn"
                 onmouseover="this.className=\'btn btnhov\'"
                 onmouseout="this.className=\'btn\'">');

   echoT(form_close('<br>'));

