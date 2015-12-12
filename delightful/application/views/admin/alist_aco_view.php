<?php
   echoT(form_open('admin/admin_special_lists/aco/update'));
//whereAmI(); die;   
?>  

      <table class="enpRptC" border="1">
         <tr>
            <td class="enpRptLabel">
               In Use
            </td>
            <td class="enpRptLabel">
               Default
            </td>
            <td class="enpRptLabel">
               Flag
            </td>
            <td class="enpRptLabel">
               Currency
            </td>
            <td class="enpRptLabel">
               Country
            </td>
         </tr>
<?php         
   foreach ($countries as $clsACOCountry){
      $lKeyID = $clsACOCountry->lKeyID;
  
      echoT('      
         <tr>
            <td class="enpRpt" style="text-align: center;">
               <input type="checkbox" name="chkACO[]" value="'.$lKeyID.'" '
                    .($clsACOCountry->bInUse ? 'checked' : '').'>
            </td>
            <td class="enpRpt" style="text-align: center;">
               <input type="radio" name="rdoDefault" value="'.$lKeyID.'" '
                    .($clsACOCountry->bDefault ? 'checked' : '').'>
            </td>
            <td class="enpRpt" style="text-align: center;">'
               .$clsACOCountry->strFlagImg.'
            </td>
            <td class="enpRpt" style="text-align: center;">'
               .$clsACOCountry->strCurrencySymbol.'
            </td>
            <td class="enpRpt">'
               .$clsACOCountry->strName.'
            </td>
         </tr>');

   }

   echoT('   
       <tr>
           <td class="enpRpt" style="text-align: center;" colspan="5">
              <input style="width: 160pt;" type="submit"
                    onclick="this.disabled=1; this.form.submit();"
                    name="cmdSubmit"
                    value="Update Accounting Countries"
                    class="btn"
                       onmouseover="this.className=\'btn btnhov\'"
                       onmouseout="this.className=\'btn\'">
           </td>
        </tr>  
   
   </table>');
   echoT(form_close());
   
   