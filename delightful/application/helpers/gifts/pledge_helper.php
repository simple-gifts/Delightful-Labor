<?php
//---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012-2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
/*---------------------------------------------------------------------
      $this->load->helper('gifts/pledge');
---------------------------------------------------------------------*/

   function strFulfillmentTable($lPledgeID, &$sch, &$curCumulativeFulfill){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
   
      $lNumFulfill = $sch->lNumFulfill;
      $dtePledge = $sch->pDate;
   
      $strOut = '';
      if ($lNumFulfill == 0){
         $strOut .= '<i>No payments</i><br>';
      }else {
         $strOut .= '<table class="enpRpt">';
         foreach ($sch->fulfillment as $ff){
            $curCumulativeFulfill += $ff->curAmnt;
            $lGiftID = $ff->lKeyID;
            $strOut .= 
               '<tr>
                   <td class="enpRpt" style="text-align: right; vertical-align: bottom;">'
                      .$ff->strFormattedAmnt.'&nbsp;'
                      .strLinkEdit_PledgePayment($lPledgeID, $lGiftID,$ff->dteDonation, 'Edit Pledge Payment', true).'
                   </td>
                   <td class="enpRpt" style="text-align: right; vertical-align: bottom;">'
                      .date($genumDateFormat, $ff->dteDonation).'
                   </td>
                </tr>';
         }
         
         $strOut .= '</table><br>';
      }
      $strOut .= strLinkAdd_PledgePayment($lPledgeID, $dtePledge, 'Add pledge payment', true).'&nbsp;'
                .strLinkAdd_PledgePayment($lPledgeID, $dtePledge, 'Add pledge payment', false);
      return($strOut);      
   }
