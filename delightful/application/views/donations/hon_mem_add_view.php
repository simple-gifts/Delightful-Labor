<?php
   if (bAllowAccess('adminOnly')){
      $strLinkWorkWith = '
             <i>Click '.strLinkView_ListHonMem($bHon, 'here', false)
             .' to work with your '.($bHon ? 'honorariums' : 'memorials').'.</i>';      
   }else {
      $strLinkWorkWith = '<i>To add names to the honorarium/memorial list, please contact your system administrator.</i>';
   }

   if ($lNumHonMem==0){
      echoT('<i>There are no '.$strButton.'s defined for your organization. <br>'.$strLinkWorkWith.'<br><br>');   
   }else {
      echoT(form_open('donations/hon_mem/addNew/'.$lGiftID.'/'.$strHonMem));

      echoT('
         <table>
            <tr>
               <td valign="top">
                   <input type="submit" name="cmdSubmit" value="Add '.$strButton.'"
                      onclick="this.disabled=1; this.form.submit();"
                      class="btn"
                         onmouseover="this.className=\'btn btnhov\'"
                         onmouseout="this.className=\'btn\'">
                </td>
                <td>
                   <select multiple name="ddlHM[]" ID="hmDDL" size="4">');


      echoT($ddlHonMem);
      echoT('      </select>'.form_error('ddlHM[]').'<br>
                   <i>Ctrl-click to select more than one name</i>
                </td>
                </tr>
             </table>'.form_close('<br><br>'));
      echoT($strLinkWorkWith.'<br>');
   }             

