<?php
global $genumDateFormat;

if ($lNumGifts == 0){
   echoT('<br><i>There are no quailifying donations for this deposit.</i><br><br>');
}else {
   echoT(form_open('financials/deposits_add_edit/addC2Deposit/'.$lDepositID));
   echoT('Select donations to add to this deposit report:<br>');
   echoT('
      <div id="scrollCB" style="height:170px;   width:600px; overflow:auto;  border: 1px solid black;">');

   echoT('
      <table class="enpRpt" style="width: 100%">');
      
   foreach ($gifts as $gift){
         $lGiftID = $gift->gi_lKeyID;
         echoT('
            <tr>
               <td class="enpRpt" style="width: 10px; vertical-align:top;">
                  <input type="checkbox" name="chkGift[]" value="'.$lGiftID.'" checked>
               </td>
               <td class="enpRpt" style="vertical-align:top; text-align: center; width: 50px;">'
                  .str_pad($lGiftID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkView_GiftsRecord($lGiftID, 'View gift record', true).'
               </td>
               <td class="enpRpt" style="vertical-align:top; text-align: right; width: 50px;">'
                  .number_format($gift->gi_curAmnt, 2).'
               </td>
               <td class="enpRpt" style="vertical-align:top; text-align: center; ">'
                  .date($genumDateFormat, $gift->gi_dteDonation).'
               </td>
               <td class="enpRpt" style="vertical-align:top; ">'
                  .$gift->strSafeNameLF.'
               </td>
               <td class="enpRpt" style="vertical-align:top; width: 140px;">'
                  .htmlspecialchars($gift->strPaymentType).'
               </td>
            </tr>');
   }

   echoT('
      </table></div><br>
         <input type="submit" name="cmdSubmit" value="Create Deposit"
            onclick="this.disabled=1; this.form.submit();"
            style=""
            class="btn"
            onmouseover="this.className=\'btn btnhov\'"
            onmouseout="this.className=\'btn\'">
         </form><br>');

}
         
         
         
         
