<?php
    echoT(strLinkAdd_ClientLocation('Add new client location', true).' '
        .strLinkAdd_ClientLocation('Add new client location', false).'<br><br>');
        
   if ($lNumLocs == 0){
      echoT('<br><i>There are no client locations defined in your database.</i>');
      return;
   }   
        
?>
 <table class="enpRptC" id="myLittleTable">
     <tr>
         <td class="enpRptTitle" colspan="8">
            Client Location Directory
         </td>
      </tr>
      <tr>
         <td class="enpRptLabel">
           Location ID
         </td>
         <td class="enpRptLabel">
           &nbsp;
         </td>
         <td class="enpRptLabel">
           Name
         </td>
         <td class="enpRptLabel">
           Address
         </td>
         <td class="enpRptLabel">
           Country
         </td>
         <td class="enpRptLabel">
           # Clients
         </td>
         <td class="enpRptLabel">
           # Sponsors
         </td>
         <td class="enpRptLabel">
           Allow<br>Medical Recs
         </td>
      </tr>
<?php   

   foreach ($locations as $clsLoc){
      $lCLID = $clsLoc->lKeyID;
      
      if ($clsLoc->lNumberClients > 0){
         $strLinkRemove = strCantDelete('Location can\'t be removed until all clients are transfered');
      }else {
         $strLinkRemove = strLinkRem_ClientLocation($lCLID, 'Remove client location', true, true);
      }

      echoT('
              <tr class="makeStripe">
                 <td class="enpRpt" style="text-align: center; width: 60pt;">'
                    .strLinkView_ClientLocation($lCLID, 'View client location', true, 'id="cLocView_'.$lCLID.'"').' '
                    .str_pad($lCLID, 5, '0', STR_PAD_LEFT).'
                 </td>
                 <td class="enpRpt" style="text-align: center; width: 18pt;">'
                    .$strLinkRemove.'
                 </td>
                 <td class="enpRpt" style=" width: 160pt;"><b>'
                    .htmlspecialchars($clsLoc->strLocation).'</b><br>');
      echoT($clsLoc->strSponProg);
      
      $lNumSponsors = $clsLoc->lNumSponsors;
      if ($lNumSponsors > 0){
         $strLinkSpon = strLinkView_SponsorsViaLocProg($lCLID, 'View sponsors for this location', true);
      }else {
         $strLinkSpon = '&nbsp;&nbsp;';
      }

      echoT('
                 </td>
                 <td class="enpRpt" style=" width: 160pt;">'
                    .strBuildAddress(
                        $clsLoc->strAddress1, $clsLoc->strAddress2, $clsLoc->strCity,
                        $clsLoc->strState,    $clsLoc->strCountry,  $clsLoc->strPostalCode,
                        true).'
                 </td>
                 <td class="enpRpt" style=" width: 70pt;">'
                    .htmlspecialchars($clsLoc->strCountry).'
                 </td>
                 <td class="enpRpt" style="text-align: center; width: 70pt;">'
                    .number_format($clsLoc->lNumberClients)
                    .' '.strLinkView_ClientsViaLocation($lCLID, 'View Clients at this Location', true).'
                 </td>
                 <td class="enpRpt" style="text-align: center;">'
                    .number_format($clsLoc->lNumSponsors).'&nbsp;'.$strLinkSpon.'
                 </td>
                 <td class="enpRpt" style="text-align: center;">'
                    .($clsLoc->bEnableEMR ? 'Yes' : 'No').'
                 </td>
              </tr>');
   }


   
     
?>     
</table><br>