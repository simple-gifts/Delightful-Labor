<?php

   function strSponsorSummaryViaCID($bTerse, $clsClient){
   //-----------------------------------------------------------------
   // assumes $clsClient in the form of $this->clients[$idx]
   //-----------------------------------------------------------------
      $strOut = '';
      if ($clsClient->lNumSponsors <= 0) return('&nbsp;');
      
      if ($bTerse){
         $strOut = '<table width="100%" class="enpRpt">';
         foreach ($clsClient->sponsors as $clsSpon){
            $lSponID = $clsSpon->lKeyID;
            $bInactive = $clsSpon->bInactive;
            if ($bInactive){
               $strStyle = 'color: #999; font-style:italic;';
            }else {
               $strStyle = '';
            }
            $strOut .=
               '<tr>
                  <td  class="enpRpt" style="'.$strStyle.'">'
                     .strLinkView_Sponsorship($lSponID, 'View sponsorship', true).'&nbsp;'
                     .str_pad($lSponID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                     .$clsSpon->strSafeNameFL.'
                  </td>
                </tr>';
         }
         $strOut .= '</table>';
      }else {
echo(__FILE__.' '.__LINE__.'<br>'."\n"); die;      
      }
    return($strOut);
  }


