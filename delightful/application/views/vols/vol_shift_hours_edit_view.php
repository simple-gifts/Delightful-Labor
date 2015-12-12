<?php

   openBlock($strShiftLabel, '');
   if ($bAsVol){
      $strLink = 'volunteers/vol_event_hours/editShiftHrsAsVol/'.$lVolID.'/'.$lEventID.'/'.$lShiftID;
   }else {
      $strLink = 'volunteers/vol_event_hours/editShiftHrs/'.$lEventID.'/'.$lShiftID;
      if ($strReturn!='') $strLink .= '/'.$strReturn;
   }
   echoT(form_open($strLink));

   echoT('<table>');
   if ($lNumVols == 0){
      echoT('<br><br><i>There are no volunteers scheduled for this shift.</i>');
   }else {
      foreach ($vols as $vol){
         $lVolID = $vol->lVolID;
         $lVolAssignID = $vol->lVolShiftAssignID;
         $strField = 'txtHR_'.$lVolAssignID;
         if (form_error($strField)==''){
            $strVA = 'middle';
         }else {
            $strVA = 'top';
         }
    
         echoT('
            <tr>
               <td style="width: 50px; vertical-align: '.$strVA.';">'
                  .strLinkView_Volunteer($lVolID, 'View volunteer record', true)
                  .str_pad($lVolID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td style="width: 170px; vertical-align: '.$strVA.';">'
                  .htmlspecialchars($vol->strLName.', '.$vol->strFName).'
               </td>
               <td style="width: 90px; vertical-align: '.$strVA.';">&nbsp;hours logged:</td>
               <td style="text-align: left; vertical-align: '.$strVA.';">
                  <input type="text" name="'.$strField.'" 
                     size="5" maxlength="10"
                     value="'.$vol->strHours.'" />'
                     .form_error($strField).'
               </td>
            </tr>');

         
      }
      echoT('</table>      
                <input type="submit" name="cmdSubmit" value="Update Volunteer Hours"
                    onclick="this.disabled=1; this.form.submit();"
                    style=""
                    class="btn"
                       onmouseover="this.className=\'btn btnhov\'"
                       onmouseout="this.className=\'btn\'">
            </form><br /><br />
            <i>Volunteer hours are expressed as decimals. For example, 2 hours 45 minutes is expressed as 2.75</i>
          ');
   }
   
   closeblock();
   
