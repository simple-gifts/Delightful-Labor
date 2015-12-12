<?php
   if ($bShowAddCatLink){
      echoT('<br>'
        .strLinkAdd_InventoryCat('Add new inventory category', true).'&nbsp;'
        .strLinkAdd_InventoryCat('Add new inventory category', false).'<br>');
   }

   if (count($icats)==0){
      echoT($strNoCatMsg);
      return;
   }

   echoT('<br>
       <table class="enpRpt" style="width: 90%;">
         <tr>
            <td class="enpRptTitle" colspan="5">'
               .$strTitle.'
            </td>
         </tr>');
         
   echoT('
      <tr class="makeStripe">
         <td class="enpRptLabel">
            &nbsp;
         </td>
         <td class="enpRptLabel">
            &nbsp;
         </td>
         <td class="enpRptLabel">
            Category
         </td>
         <td class="enpRptLabel">
            # Items
         </td>
         <td class="enpRptLabel">
            Notes
         </td>
      </tr>');
   
   writeICatRows('', $icats, $bHideZeroItems, $bShowAddItemsLink, $enumReportType);      



   echoT('</table><br>');
   
   function writeICatRows($strIndent, &$icats, &$bHideZeroItems, &$bShowAddItemsLink, &$enumReportType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------  
      foreach ($icats as $icat){
         if ($icat->lNumItems > 0 || !$bHideZeroItems){
            writeSingleICatRow($icat, $strIndent, $bShowAddItemsLink, $enumReportType);
         }
         if ($icat->lNumChildren > 0){
            writeICatRows($strIndent.'|'.'&mdash;&nbsp;', $icat->children, $bHideZeroItems, 
                $bShowAddItemsLink, $enumReportType);
         }
      }         
   }

   function writeSingleICatRow($icat, $strIndent, $bShowLinks, $enumReportType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lICatID = $icat->lKeyID;
      $bTop = $strIndent=='';
      
      if ($bShowLinks){
         if ($icat->lNumItems==0 && $icat->lNumChildren==0){
            $strLinkRemCat = strLinkRem_ICat($lICatID, 'Remove inventory category', true, true);
         }else {
            $strLinkRemCat = strCantDelete('A category can only be removed if there are no sub-categories and no associated items');
         }
         $strLinkEditCat = strLinkEdit_InventoryCat($lICatID, 'Edit inventory category', true);
      }else {
         $strLinkRemCat = '';
         $strLinkEditCat = '';
      }
      
      $strItems = '';
      if ($icat->lNumItems > 0){
         $strItems = strLinkView_InventoryItemsByCat($lICatID, $enumReportType, 'View items', true);
      }
      if ($bShowLinks){
         $strItems .= '&nbsp;&nbsp;'.strLinkAdd_InventoryItem($lICatID, 'Add item', true);
      }
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="text-align: center; width: 60px;">'
               .str_pad($lICatID, 5, '0', STR_PAD_LEFT).'&nbsp;'
               .$strLinkEditCat.'
            </td>
            <td class="enpRpt" style="text-align: center; width: 30px;">'
               .$strLinkRemCat.'
            </td>
            <td class="enpRpt" style="width: 180pt; '.($bTop ? 'font-weight: bold;' : '').'">'
              .'<span style="color: gray;">'.$strIndent.'</span>&nbsp;'.htmlspecialchars($icat->strCatName).'
            </td>
            <td class="enpRpt" style="text-align: center; width: 80px;">'
              .$icat->lNumItems.$strItems.'
            </td>
            <td class="enpRpt" style="text-align: left;">'
              .nl2br(htmlspecialchars($icat->strNotes)).'
            </td>
         </tr>');
   }

   
   
   
