<?php
   $attributes = array('name' => 'frmSearch', 'id' => 'frmSearch');
   echoT(form_open('creports/search_parens/update/'.$lReportID, $attributes));

   echoT('<h3>Click in the parenthesis box to add/remove</h3>');
   echoT($strSearchExpression);
   echoT('
      <input type="submit" name="cmdSubmit" value="Update"
      style=" text-align: center; width: 110pt;"
      onclick=" this.disabled=1; this.form.submit(); "
      class="btn"
      onmouseover="this.className=\'btn btnhov\'"
      onmouseout="this.className=\'btn\'">');
   
   echoT(form_close('<br>'));


