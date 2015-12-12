<?php
   echoT(strLinkAdd_SponProg('Add new sponsorship program', true).' '
        .strLinkAdd_SponProg('Add new sponsorship program', false).'<br>');
   
         
   if ($lNumSponProg > 0){
      echoT('<br>
         <table class="enpRptC">
            <tr>
               <td class="enpRptLabel" >
                  ID
               </td>
               <td class="enpRptLabel" >
                  &nbsp;
               </td>
               <td class="enpRptLabel" >
                  Program Name
               </td>
               <td class="enpRptLabel" >
                  Commitment
               </td>
               <td class="enpRptLabel" >
                  ACO
               </td>
               <td class="enpRptLabel" >
                  # Sponsors
               </td>
            </tr>
            ');

      foreach ($sponProg as $clsSingleCat){            
         $lSPID = $clsSingleCat->lSPID;
            // don't remove basic sponsorship
         if ($lSPID==1){
            $strRem = strCantDelete('Basic sponsorship can\'t be removed.');
         }else {
            $strRem = strLinkRem_SponProg($lSPID, 'Retire sponsorship program', true, true);
         }
         echoT('
            <tr>
               <td class="enpRpt" style="text-align: center;">'
                  .strLinkEdit_SponProg($lSPID, 'Edit sponsorship program', true).' '
                  .str_pad($lSPID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td class="enpRpt"  style="text-align: center;">'
                  .$strRem.' 
               </td>
               
               <td class="enpRpt">'
                  .htmlspecialchars($clsSingleCat->strProg).'
               </td>
               <td class="enpRpt" style="text-align: right;">'
                  .$clsSingleCat->strCurrencySymbol.' '.number_format($clsSingleCat->curDefMonthlyCommit, 2).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .$clsSingleCat->strFlagImg.'
               </td>
               <td class="enpRpt" style="text-align: center;">'               
                  .$clsSingleCat->lNumSponsors.'&nbsp;'
                  .strLinkView_SponsorsViaSponProg($lSPID, 'View sponsors', true).'
               </td>
            </tr>');
      }  
       echoT('</table>');      
   }else {
      echoT('<i>There are currently no sponsorship programs defined</i>');
   }
         
?>         