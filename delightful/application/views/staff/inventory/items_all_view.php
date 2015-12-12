<?php

   if ($lNumRecsTot == 0){
      echoT('<br><i>There are no inventory items in your database.</i><br>');
   }else {
      openUserDirectory(
                 $enumSort,
                 $strLinkBase,          $strImgBase,        $lNumRecsTot,
                 $directoryRecsPerPage, $directoryStartRec, $lNumDisplayRows);
      openIItemTable();
      
      foreach ($items as $item){
         writeIItemRow($item);
      }      
      closeIItemTable();
   }

   function writeIItemRow($item){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strStatus = '<span style="font-size: 8pt;"><b>Status: </b>'.htmlspecialchars($item->strStatus).'</span>';
   
      $strSN = '';
      if ($item->strItemSNa != '') $strSN .= '<br><b>SN(a): </b>'.$item->strItemSNa;
      if ($item->strItemSNb != '') $strSN .= '<br><b>SN(b): </b>'.$item->strItemSNb;
      if ($strSN != '') $strSN = '<span style="font-size: 8pt;">'.$strSN.'</span>';
   
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="text-align: center; width: 40pt;">'
               .str_pad($item->lItemID, 5, '0', STR_PAD_LEFT).'&nbsp;'
               .strLinkView_InventoryItem($item->lItemID, 'View item record', true).'
            </td>
            <td class="enpRpt" style="width: 200pt;">'
               .htmlspecialchars($item->strFullCatName).'
            </td>
            <td class="enpRpt" style="width: 200pt;">'
               .htmlspecialchars($item->strItemName).$strSN.'
            </td>
              <td class="enpRpt">'
                 .$strStatus.'
              </td>
         </tr>');
   }

   function openUserDirectory(
                        $enumSort,
                       &$strLinkBase,  &$strImgBase, &$lTotRecs,
                       $lRecsPerPage, $lStartRec,  $lNumThisPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT(
         '<br>
          <table class="enpRpt" id="myLittleTable">
              <tr>
                 <td class="enpRptLabel">
                    Inventory Items
                 </td>
              </tr>
              <tr>
                 <td class="recSel">');

      $strLinkBase = 'staff/inventory/icat/itemsAllList/'.$enumSort.'/';
      $strImgBase = base_url().'images/dbNavigate/rs_page_';
      $opts = new stdClass;
      $opts->strLinkBase       = $strLinkBase;
      $opts->strImgBase        = $strImgBase;
      $opts->lTotRecs          = $lTotRecs;
      $opts->lRecsPerPage      = $lRecsPerPage;
      $opts->lStartRec         = $lStartRec;

      echoT(set_RS_Navigation($opts));
      echoT(
         '<i>Showing records '.($lStartRec+1).' to '.($lStartRec+$lNumThisPage)
            ." ($lTotRecs total)</i>");

      echoT('</td></tr></table>');
   }

   function openIItemTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strSortBase = 'staff/inventory/icat/itemsAllList/';
      $strSortAtt  = ' style="font-weight: normal; font-size: 8pt;" ';
   
      echoT('<br><br>
       <table class="enpRptC" style="width: 700pt;">
           <tr>
              <td class="enpRptTitle" colspan="4">
                 Inventory Items
              </td>
           </tr>
           <tr>
              <td class="enpRptLabel">
                 itemID<br>'
                    .anchor($strSortBase.'itemid', '(sort)',  $strSortAtt).'
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
                 Status
              </td>
           </tr>');
   }

   function closeIItemTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('</table><br>');   
   }
