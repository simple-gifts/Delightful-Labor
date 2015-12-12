<?php
   echoT($entrySummary.'<br>');
   
   echoT(form_open('admin/uf_tables_add_edit/addField2/'.$lTableID.'/0'));

   echoT('
      Select the field type:
      <select name="ddlFieldType">');

   echoT($ddlFields.'</select>');

   echoT('&nbsp;
      <input type="submit"
            onclick="this.disabled=1; this.form.submit();"
            name="cmdAdd"
            value="Next"
            class="btn"
               onmouseover="this.className=\'btn btnhov\'"
               onmouseout="this.className=\'btn\'"></form><br>');

   echoT('</table>'.form_close('<br><br>'));



?>