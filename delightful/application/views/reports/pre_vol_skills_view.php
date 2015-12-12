<?php

   if ($lNumSkillsList <= 0){
      echoT('<br><i>There are no job skills 
             defined in your system. </i><br><br>');
   }else {
      $attributes =
          array(
               'name'     => 'frmSkillsRpt',
//               'onSubmit' => 'return(verifyBoxChecked(frmSkillsRpt, \'chkSkill[]\', \' (Job Skills)\'))'
               );

      echoT(form_open('reports/pre_vol_job_skills/saveOpts',  $attributes));

      echoT('<br><br>'
            .strHTMLSetClearAllButton(true,  'frmSkillsRpt', 'chkSkill[]', 'Select All ').'&nbsp;&nbsp;'
            .strHTMLSetClearAllButton(false, 'frmSkillsRpt', 'chkSkill[]', 'Clear All').'<br>'
         );
      echoT('<div style="vertical-align: middle;">
                Find all the volunteers '
              .' having <input type="radio" name="rdoAnyAll" value="any" checked><b>any</b>
                                    <input type="radio" name="rdoAnyAll" value="all" ><b>all</b>
                 of the selected job skills:<br><br></div>');
                 

      echoT('
         <div id="scrollCB" style="height:130px;   width:300px; overflow:auto;  border: 1px solid black;">'."\n\n");
        
      echoT('<table class="enpRpt" style="width: 100%">');
      foreach ($skillsList as $skill){
         $lSkillID = $skill->lSkillID;
         echoT('
            <tr>
               <td class="enpRpt" style="width: 10px; vertical-align:top;">
                  <input type="checkbox" name="chkSkill[]" value="'.$lSkillID.'">
               </td>
               <td class="enpRpt" style="vertical-align:top; width: 180px;">'
                  .htmlspecialchars($skill->strSkill).'
               </td>
            </tr>');
      }
      
      echoT('</table></div>
             <input type="checkbox" name="chkInactive" value="TRUE">Include inactive volunteers<br><br>

             <input type="submit" name="cmdSubmit" value="Run Report"
                 onclick = "return(verifyBoxChecked(\'frmSkillsRpt\', \'chkSkill[]\', \' (Job Skills)\'));"
                 style=""
                 class="btn"
                    onmouseover="this.className=\'btn btnhov\'"
                    onmouseout="this.className=\'btn\'">');
                 //onclick="this.disabled=1; /*this.form.submit(); */"

      echoT(form_close('<br>'));
   }
