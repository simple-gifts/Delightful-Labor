<?php


   openBlock('Select Sponsorship Payer', '');
   echoT('<i>Sponsorship payments are generally made by the sponsor; however, any individual or<br>
          business can make a payment towards a sponsorship.</i><br><br>');
   
   $attributes = array('name'     => 'frmSelSponPay', 
                       'onSubmit' => 'return verifySelSponPayForm(frmSelSponPay)');

   echo form_open('sponsors/payments/payerList/'.$lSponID, $attributes);
/*   
   echoT(
        '<form method="GET"
               action="../main/mainOpts.php"
               name="frmSelSponPay"
               onSubmit="return verifySelSponPayForm(frmSelSponPay)");">'
         .$clsNav->strHidden());
*/
   echoT('<font style="font-size:11pt;">
       Sponsorship payment made by:</font><br>
       <table>
          <tr>
             <td>
                <input type="radio" name="rdoSP" value="sponsor" checked>
             </td>
             <td colspan="2">
                The Sponsor ('
                      .$sponRec->strSponSafeNameFL.')
             </td>
          </tr>
          <tr>
             <td>
                <input type="radio" name="rdoSP" value="person">
             </td>
             <td>
                Individual
             </td>
             <td>
                <input type="text" name="txtSPP" value="" size="4" maxlength="50"
                   onfocus="setRadioOther(frmSelSponPay.rdoSP,\'person\');"
                   > (first few letters of the individual\'s last name i.e. "smith")
             </td>
          </tr>
          <tr>
             <td>
                <input type="radio" name="rdoSP" value="biz">
             </td>
             <td>
                Business/Organization
             </td>
             <td>
                <input type="text" name="txtSPB" value="" size="4" maxlength="50"
                    onfocus="setRadioOther(frmSelSponPay.rdoSP,\'biz\');"
                > (first few letters of the business/organization name i.e. "acme")
             </td>
          </tr>
       </table>
       ');

   echoT('
      <input type="submit"
            name="cmdAdd"
            value="Next"
            onclick="this.disabled=1; this.form.submit();"
            class="btn"
               onmouseover="this.className=\'btn btnhov\'"
               onmouseout="this.className=\'btn\'"><br>');


   closeBlock();          