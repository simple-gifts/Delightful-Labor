<?php

if ($lNumShifts <= 0){
   echoT('There are no shifts assigned to this event. Please create one or more shifts, then<br>
          add volunteers to the shift.<br><br>');
}elseif ($lNumVols <= 0) {
   echoT('There are no volunteers assigned to this event. Please create one or more shifts, then<br>
          add volunteers to the shift.<br><br>');
}else {
   foreach ($shifts as $shift){
      $lShiftID = $shift->lKeyID;
      if ($shift->lNumVolsInShift > 0){ 
         $strBlockLink = strLinkEdit_VolEventHrs($lEventID, $lShiftID, 'Edit hours for this shift', true);
      }else {
         $strBlockLink = '';
      }      
      openBlock(
             date('F j Y', $shift->dteEvent).'&nbsp&nbsp;'.date('g:i a', $shift->dteEventStartTime).': '
            .'<b>'.htmlspecialchars($shift->strShiftName)
            .'</b> ('.$shift->enumDuration.')',
            $strBlockLink);
            
      echoT('<table>');
      if ($shift->lNumVolsInShift > 0){
         $dTotHrs = 0.0;
         foreach ($shift->vols as $vol){
            $lVolID = $vol->lVolID;
            $dTotHrs += $vol->dHoursWorked;
            echoT('
               <tr>
                  <td style="width: 50px;">'
                     .strLinkView_Volunteer($lVolID, 'View volunteer record', true)
                     .str_pad($lVolID, 5, '0', STR_PAD_LEFT).'
                  </td>
                  <td style="width: 250px;">'
                     .htmlspecialchars($vol->strLName.', '.$vol->strFName).'
                  </td>
                  <td style="width: 90px;">&nbsp;hours logged:</td>
                  <td style="width: 50px; text-align: right;">'
                     .number_format($vol->dHoursWorked, 2).'
                  </td>
               </tr>');
         }
         echoT('
            <tr>
               <td style="width: 50px;" >&nbsp;</td>
               <td style="width: 250px;">&nbsp;</td>
               <td style="width: 90px;"><b>Total:</b</td>
               <td style="width: 50px; text-align: right;"><b>'
                  .number_format($dTotHrs, 2).'
               </td>
            </tr>');
         
      }else {
         echoT('<tr><td><i>There are no volunteers assigned to this shift</i></td></tr>');
      }
      echoT('</table>');
            
      
      closeblock();
   }

}









