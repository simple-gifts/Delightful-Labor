<?php
   echoT(strLinkAdd_ClientStatEntry($id, 'Add new client status entry ('.$strStatCat.')', true).' '
        .strLinkAdd_ClientStatEntry($id, 'Add new client status entry ('.$strStatCat.')', false).'<br><br>');

   if ($lNumCatEntries <= 0){
      echoT('<i>There are currently no client status entries for "'.$strStatCat.'".</i><br><br>');
   }else {
      showClientStatusEntrTable($id, $strStatCat, $statCatsEntries);
   }

function showClientStatusEntrTable($lSCID, $strStatCat, $statCatsEntries){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------

   echoT('
      <table class="enpRptC">
         <tr>
            <td class="enpRptTitle" colspan="6">
               Client Status Entries: '.$strStatCat.'
            </td>
         </tr>');

   echoT('
         <tr>
            <td class="enpRptLabel" style="vertical-align: middle;">
               Status ID
            </td>
            <td class="enpRptLabel" style="vertical-align: middle;">
               Status
            </td>
            <td class="enpRptLabel" style="vertical-align: middle;">
               &nbsp;
            </td>
            <td class="enpRptLabel" style="vertical-align: middle;">
               Sponsorable?
            </td>
            <td class="enpRptLabel" style="vertical-align: middle;">
               Appears in<br>Directory?
            </td>
            <td class="enpRptLabel" style="vertical-align: middle;">
               Default?
            </td>
         </tr>');

   foreach ($statCatsEntries as $clsEntry){
      $lKeyID = $clsEntry->lKeyID;
      echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align: center; vertical-align: middle;">'
                  .strLinkEdit_ClientStatEntry($lSCID, $lKeyID, 'Edit client status entry', true).' '
                  .str_pad($lKeyID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td class="enpRpt" style="text-align: center; vertical-align: middle;">'
                  .strLinkRem_ClientStatEntry($lSCID, $lKeyID, 'Remove client status entry', true, true).'
               </td>
               <td class="enpRpt" style="vertical-align: middle;">'
                  .htmlspecialchars($clsEntry->strStatusEntry).'
               </td>
               <td class="enpRpt" style="text-align: center; vertical-align: middle;">'
                  .($clsEntry->bAllowSponsorship ? CSTR_IMG_CHKYES : '&nbsp;-&nbsp;').'
               </td>
               <td class="enpRpt" style="text-align: center; vertical-align: middle;">'
                  .($clsEntry->bShowInDir ? CSTR_IMG_CHKYES : '&nbsp;-&nbsp;').'
               </td>
               <td class="enpRpt" style="text-align: center; vertical-align: middle;">'
                  .($clsEntry->bDefault ? CSTR_IMG_CHKYES : '&nbsp;-&nbsp;').'
               </td>
            </tr>');
   }

   echoT('
      </table>');
}



?>
