<?php

   if ($bShowAddLink){
      echoT('<br>'
        .strLinkAdd_InventoryItem($lICatID, 'Add new inventory item', true).'&nbsp;'
        .strLinkAdd_InventoryItem($lICatID, 'Add new inventory item to this category', false).'<br>');
   }

   echoT('<br>
      <table class="enpRptC">
         <tr>
            <td class="enpRptTitle" colspan="8">
               '.$strTitle.'<span style="color: #666;">Inventory category:</span> '.htmlspecialchars($strBreadCrumb).'
            </td>
         </tr>');

   echoT('
      <tr>
         <td class="enpRptLabel">
            itemID
         </td>
         <td class="enpRptLabel">
            Item
         </td>
         <td class="enpRptLabel">
            SN / Tag (a)
         </td>
         <td class="enpRptLabel">
            SN / Tag (b)
         </td>
         <td class="enpRptLabel">
            Responsible Party
         </td>
         <td class="enpRptLabel">
            Status
         </td>
         <td class="enpRptLabel">
            Location
         </td>
         <td class="enpRptLabel">
            Description
         </td>
      </tr>');

   foreach ($items as $item){
      $lIItemID = $item->lKeyID;
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="width: 50pt; text-align: center;">'
               .str_pad($lIItemID, 5, '0', STR_PAD_LEFT).'&nbsp;'
               .strLinkView_InventoryItem($lIItemID, 'View Item', true).'
            </td>
            <td class="enpRpt" style="width: 120pt;">'
               .htmlspecialchars($item->strItemName).'
            </td>
            <td class="enpRpt" style="width: 60pt;">'
               .htmlspecialchars($item->strItemSNa).'
            </td>
            <td class="enpRpt" style="width: 60pt;">'
               .htmlspecialchars($item->strItemSNb).'
            </td>
            <td class="enpRpt" style="width: 80pt;">'
               .htmlspecialchars($item->strRParty).'
            </td>
            <td class="enpRpt" style="width: 80pt;">'
               .htmlspecialchars($item->strStatus).'
            </td>
            <td class="enpRpt" style="width: 150pt;">'
               .nl2br(htmlspecialchars($item->strLocation)).'
            </td>
            <td class="enpRpt" style="width: 150pt;">'
               .nl2br(htmlspecialchars($item->strDescription)).'
            </td>
         </tr>');
   }

   echoT('</table><br>');

