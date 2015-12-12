<?php
   global $gbVolLogin;

   if ($gbVolLogin){
      openBlock('Update your skills', '');
   }else {
      openBlock('Assign skills', '');
   }
   echoT(form_open('volunteers/vol_add_edit/editVolSkillsUpdate/'.$lVolID));

   foreach ($singleVolSkills as $skill){
      echoT('
         <input type="checkbox" name="chkSkill[]"
                value="'.$skill->lSkillID.'" '
                .($skill->bVolHasSkill ? 'checked' : '').'> '
                .htmlspecialchars($skill->strSkill).'<br>');
   }
   echoT('<br>
         <input type="submit" 
                name="cmdSubmit" 
                onclick="this.disabled=1; this.form.submit();"
                value="Update Skills"
                style="text-align: center; width: 130pt;"
                class="btn"
                onmouseover="this.className=\'btn btnhov\'"
                onmouseout="this.className=\'btn\'">');
   echoT(form_close());

   closeBlock();