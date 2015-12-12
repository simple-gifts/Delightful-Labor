<?php

echoT(strLinkAdd_HonMem($bHon, false, null, "Add new $strLabel", true).' '
     .strLinkAdd_HonMem($bHon, false, null, "Add new $strLabel", false).'<br><br>');

if ($lNumHonMem <= 0){
   echoT('<i>There are no '.$strLabel.'s defined in your database.</i><br><br>');
}else {
   showHonMemList($honMemTable, $bHon, $strLabel, $numGifts);
}

function showHonMemList($honMemTable, $bHon, $strLabel, $numGifts){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------

   echoT(
      '<table class="enpRptC">');

   if ($bHon){
      echoT('
         <tr>
            <td class="enpRptLabel">
               HonMemID
            </td>
            <td class="enpRptLabel">
               &nbsp;
            </td>
            <td class="enpRptLabel">
               &nbsp;
            </td>
            <td class="enpRptLabel">
               Person being honored
            </td>
            <td class="enpRptLabel">
               Gifts
            </td>
         </tr>');
   }else {
      echoT('
         <tr>
            <td class="enpRptLabel">
               HonMemID
            </td>
            <td class="enpRptLabel">
               &nbsp;
            </td>
            <td class="enpRptLabel">
               &nbsp;
            </td>
            <td class="enpRptLabel">
               Person being memorialized
            </td>
            <td class="enpRptLabel">
               Contact for memorials
            </td>
            <td class="enpRptLabel">
               Gifts
            </td>
         </tr>');
   }

   $idx = 0;
   foreach($honMemTable as $clsHM){
      $bHidden = $clsHM->ghm_bHidden;
      $lHMID   = $clsHM->ghm_lKeyID;

      if ($bHidden){
         $strStyle = 'color: #aaa; font-style:italic;';
      }else {
         $strStyle = '';
      }
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="text-align: center; '.$strStyle.'">'
               .str_pad($clsHM->ghm_lKeyID, 5, '0', STR_PAD_LEFT).'
            </td>
            <td class="enpRpt" style="'.$strStyle.'">'
               .strLinkHideUnhide_HonMem($lHMID, !$bHidden, $bHon, ($bHidden ? 'Un-hide entry' : 'Hide entry'), true).'
            </td>
            <td class="enpRpt" style="'.$strStyle.'">'
               .strLinkRem_HonMem($lHMID, $bHon, 'Remove this '.$strLabel, true, true).'
            </td>
            <td class="enpRpt" style="'.$strStyle.'">'
               .strLinkView_PeopleRecord($clsHM->ghm_lFID, 'View people record', true)
               .$clsHM->honorMem->strSafeNameLF.'<br>'
               .$clsHM->honorMem->strAddress.'
            </td>');

      if (!$bHon){
         $lMCID = $clsHM->ghm_lMailContactID;
         if (is_null($lMCID)){
            $strMCEntry = '<font color="red">NOT SET</font> '
                        .strLinkAdd_HonMem(false, true, $lHMID, 'Add mail contact for this memorial', true);
         }else {
            $strMCEntry =
                $clsHM->mailContact->strSafeNameLF.' '
               .strLinkView_PeopleRecord($lMCID, 'View mail contact\'s people record', true)
               .strLinkRem_HonMemMailCon($lHMID, 'Remove this mail contact', true, true).'<br>'
               .$clsHM->mailContact->strAddress;
         }
         echoT('
            <td class="enpRpt" style="'.$strStyle.'">'
               .$strMCEntry.'
            </td>');
      }
      
      $lNumGifts = $numGifts[$idx];
      if ($lNumGifts > 0){
         $strLinkGifts = strLinkView_GiftsViaHM($lHMID, 'View gifts', true);
      }else {
         $strLinkGifts = '';
      }
      echoT('
            <td class="enpRpt" style="text-align: center; '.$strStyle.'">'
               .number_format($lNumGifts).' '
               .$strLinkGifts.'
            </td>
         </tr>');
      ++$idx;
   }

   echoT('</table>');
}
   
?>