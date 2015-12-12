<?php
   if ($showFields->bSubmitOnClick){
      $strSubmit = ' onchange="this.form.submit();" ';
   }else {
      $strSubmit = '';
   }

   $attributes = array('name' => $showFields->formName);
   echoT(form_open($showFields->locDDLDest, $attributes));  
   echoT('<select name="ddlLocation" '.$strSubmit.'>'.$ddlLocations.'</select>');
   echoT('
          <input type="checkbox" 
                 name="chkShowAll" '.$strSubmit.' '
                 .($showFields->bShowInactive ? 'checked' : '').'
                 value="TRUE">Include inactive clients');    
   echoT(form_close('<br>'));
  

   
   