<?php


   echoT(form_open('volunteers/event_date_shift_vols_add_edit/updateVolsForShift/'.$lDateID.'/'.$lShiftID));

   if ($lNumVolsAvail <= 0){
      echoT('<i>There are no volunteers available for this shift</i><br>');
   }else {

      echoT("\n\n".'<b>Available Volunteers:</b><br>'."\n"
         .'<div id="scrollCB" style="height:200px; width:640px; overflow: auto; border: 1px solid black;">'."\n\n");
      echoT('<table class="enpRpt" style="width: 100%">
                <tr>
                   <td class="enpRptLabel">
                      &nbsp;
                   </td>
                   <td class="enpRptLabel">
                      Name
                   </td>
                   <td class="enpRptLabel">
                      Address
                   </td>
                   <td class="enpRptLabel">
                      Skills
                   </td>
                </tr>
            ');
      foreach ($volsA as $clsVA){
         if ($clsVA->bEligible){
            $lVolID = $clsVA->lVolID;
            echoT('
               <tr>
                  <td class="enpRpt" style="width: 10px; vertical-align:top;">
                     <input type="checkbox" name="chkVol[]" value="'.$clsVA->lVolID.'">
                  </td>
                  <td class="enpRpt" style="vertical-align:top; width: 180px;">'
                     .strLinkView_Volunteer($lVolID, 'View volunteer record', true).'&nbsp;'
                     .$clsVA->strSafeNameFL.'
                  </td>
                  <td class="enpRpt" style="vertical-align:top;">'
                     .$clsVA->strAddr.'
                  </td>
                  <td class="enpRpt" style="vertical-align:top;">');

            if ($clsVA->lNumVolSkills > 0 ){
               foreach ($clsVA->volSkills as $skill){
                  echoT(htmlspecialchars($skill->strSkill)."<br>\n");
               }
            }else {
               echoT('<i>No skill assigned</i>');
            }

            echoT('
                  </td>
               </tr>');
         }
      }
      echoT('</table></div>
             <input type="submit" name="cmdSubmit" value="Add Volunteers to Shift"
                 style=""
                 onclick="this.disabled=1; this.form.submit();"
                 class="btn"
                    onmouseover="this.className=\'btn btnhov\'"
                    onmouseout="this.className=\'btn\'">

      </form>');

   echoT(form_open('volunteers/event_date_shift_vols_add_edit/addVolsViaSkills/'.$lDateID.'/'.$lShiftID));
   echoT('<br><i>Refresh with volunteers having <b>all</b> the following skills:</i><br>');
   echoT('<select name="ddlSkills[]" multiple size="4">
            <option value="-1" selected>(all skills)</option>');
   foreach ($skills as $skill){
      echoT('<option value="'.$skill->lKeyID.'">'.htmlspecialchars($skill->strListItem).'</option>'."\n");
   }
   echoT('</select>');
   echoT('<br>
             <input type="submit" name="cmdSubmit" value="Refresh"
                 style=""
                 onclick="this.disabled=1; this.form.submit();"
                 class="btn"
                    onmouseover="this.className=\'btn btnhov\'"
                    onmouseout="this.className=\'btn\'">

      </form>');
/*
*/

   }










