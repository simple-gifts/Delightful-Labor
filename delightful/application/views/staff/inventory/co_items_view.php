<?php
   global $genumDateFormat;

   if ($lNumItems == 0){
      echoT('<br><i>There are no items currently checked out.</i><br><br>');
      return;
   }
   
   echoT('<br>
      <table class="enpRptC">
         <tr>
            <td class="enpRptTitle" colspan="7">
               Items Checked Out
            </td>
         </tr>');         
         
   $strSortBase = 'staff/inventory/icat/itemsCheckedOutList/';
   $strSortAtt  = ' style="font-weight: normal; font-size: 8pt;" ';
   echoT('
      <tr>
         <td class="enpRptLabel">
            itemID<br>'
               .anchor($strSortBase.'itemid', '(sort)',  $strSortAtt).'
         </td>
         <td class="enpRptLabel">
            cicoID
         </td>
         <td class="enpRptLabel">
            Category<br>'
               .anchor($strSortBase.'cat', '(sort)',  $strSortAtt).'
         </td>
         <td class="enpRptLabel">
            Item<br>'
               .anchor($strSortBase.'item', '(sort)',  $strSortAtt).'
         </td>
         <td class="enpRptLabel">
            Checked-out To<br>'
               .anchor($strSortBase.'name', '(sort)',  $strSortAtt).'
         </td>
         <td class="enpRptLabel">
            Date<br>'
               .anchor($strSortBase.'date', '(sort)',  $strSortAtt).'
         </td>
         <td class="enpRptLabel">
            Notes
         </td>
      </tr>');
      
   foreach ($items as $item){
      $strSN = '';
      if ($item->strItemSNa != '') $strSN .= '<br><b>SN(a): </b>'.$item->strItemSNa;
      if ($item->strItemSNb != '') $strSN .= '<br><b>SN(b): </b>'.$item->strItemSNb;
      if ($strSN != '') $strSN = '<span style="font-size: 8pt;">'.$strSN.'</span>';
      
      $strStatus = '';
      if ($item->strCO_Notes != '') $strStatus = '<br>';
      $strStatus .= '<span style="font-size: 8pt;"><b>Status: </b>'.htmlspecialchars($item->strStatus).'</span>';
      
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="text-align: center; width: 40pt;">'
               .str_pad($item->lItemID, 5, '0', STR_PAD_LEFT).'&nbsp;'
               .strLinkView_InventoryItem($item->lItemID, 'View item record', true).'
            </td>
            <td class="enpRpt" style="text-align: center; width: 35pt;">'
               .str_pad($item->lCICO_ID, 5, '0', STR_PAD_LEFT).'
            </td>
            <td class="enpRpt" style="width: 180pt;">'
               .htmlspecialchars($item->strFullCatName).'
            </td>
            <td class="enpRpt" style="width: 140pt;">'
               .htmlspecialchars($item->strItemName).$strSN.'
            </td>
            <td class="enpRpt" style="width: 120pt;">'
               .htmlspecialchars($item->strCheckedOutTo).'<br>'
               .strLinkCI_IItem($item->lItemID, 'Check in item', true).'&nbsp;'
               .strLinkCI_IItem($item->lItemID, 'Check in item', false).'
            </td>
            <td class="enpRpt" style="width: 60pt;">'
               .date($genumDateFormat, $item->dteCheckedOut).'
            </td>
            <td class="enpRpt" style="width: 140pt;">'
               .nl2br(htmlspecialchars($item->strCO_Notes)).$strStatus.'
            </td>
         </tr>');
         
   }
         
   echoT('</table>');         
         
         