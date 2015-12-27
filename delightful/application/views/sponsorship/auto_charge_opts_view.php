<?php
   global $genumDateFormat;


   echoT('<br>You can automatically apply charges for all active sponsorship on a monthly basis.<br><br>');
   echoT('<table>');
   foreach ($autoCharges12Mo as $clsMoCharge){
      if (is_null($clsMoCharge->lKeyID)){
         $strLabel = '<i>Charges not applied</i>';
         $strLink = strLinkAdd_SponsorAutoCharges($clsMoCharge->lMonth, $clsMoCharge->lYear, 
                     'Apply Charges', true, 'id="charge_'.$clsMoCharge->lYear.'_'.$clsMoCharge->lMonth.'"');
      }else {
         $strLabel = 'Charges applied on '.date($genumDateFormat, $clsMoCharge->dteOrigin)
                    .' by '.$clsMoCharge->strUserSafe;
         $strLink = strLinkView_SponsorAutoCharge($clsMoCharge->lKeyID, 'View autocharge entry', true);
      }

      echoT('<tr>');
      echoT('<td>'.$strLink.'</td>');
      echoT('<td>'.str_pad($clsMoCharge->lMonth, 2, '0', STR_PAD_LEFT).'-'.$clsMoCharge->lYear.'</td>');
      echoT('<td>'.$strLabel.'</td>');

      echoT('</tr>');
   }
   echoT('</table>');