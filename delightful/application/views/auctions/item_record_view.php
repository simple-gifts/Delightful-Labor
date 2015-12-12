<?php

   global $genumDateFormat;

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '120pt';
   $clsRpt->bValueEscapeHTML = false;

   showItemInfo ($clsRpt, $item, $auction, $lPackageID, $lItemID);
   
   showImageInfo      (CENUM_CONTEXT_AUCTIONITEM, $lItemID, ' Images',
                       $images, $lNumImages, $lNumImagesTot);
   showDocumentInfo   (CENUM_CONTEXT_AUCTIONITEM, $lItemID, ' Documents',
                       $docs, $lNumDocs, $lNumDocsTot);
   
   showItemENPStats($clsRpt, $item);
   
   function showItemInfo (&$clsRpt, &$item, &$auction, $lPackageID, $lItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------  
      global $genumDateFormat;
      openBlock('Silent Auction Item',
                   strLinkEdit_AuctionItem($lPackageID, $lItemID, 'Edit item information', true)
                  .'&nbsp;&nbsp;'
                  .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                  .strLinkRem_AuctionItem($lPackageID, $lItemID, 'Remove this item record', true, true));

      echoT(
          $clsRpt->openReport());
          
         // Item ID
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Item ID:')
         .$clsRpt->writeCell (str_pad($lItemID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ());
          
         // Item Name
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Name:')
         .$clsRpt->writeCell ($item->strSafeItemName)
         .$clsRpt->closeRow  ());

         // Date Obtained
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Date Obtained:')
         .$clsRpt->writeCell (date($genumDateFormat, $item->dteObtained))
         .$clsRpt->closeRow  ());

         // Item Donor
      if ($item->itemDonor_bBiz){
         $strNameLink = ' <i>(business)</i> '.strLinkView_BizRecord($item->lItemDonorID, 'View business record', true);
      }else {
         $strNameLink = ' '.strLinkView_PeopleRecord($item->lItemDonorID, 'View people record', true);
      }
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Item Donor:')
         .$clsRpt->writeCell ($item->itemDonor_safeName.$strNameLink)
         .$clsRpt->closeRow  ());

         // Donor Acknowledgement
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Donor Ack.:')
         .$clsRpt->writeCell (htmlspecialchars($item->strDonorAck))
         .$clsRpt->closeRow  ());
         
         // Est. Value
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Est. Value:')
         .$clsRpt->writeCell ($auction->strCurrencySymbol.' '.number_format($item->curEstAmnt, 2))
         .$clsRpt->closeRow  ());
         
         // Out-of-Pocket
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Out-of-Pocket:')
         .$clsRpt->writeCell ($auction->strCurrencySymbol.' '.number_format($item->curOutOfPocket, 2))
         .$clsRpt->closeRow  ());
         
         // Public Notes
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Public Notes:')
         .$clsRpt->writeCell (nl2br(htmlspecialchars($item->strDescription)))
         .$clsRpt->closeRow  ());

         // Private Notes
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Private Notes:')
         .$clsRpt->writeCell (nl2br(htmlspecialchars($item->strInternalNotes)))
         .$clsRpt->closeRow  ());

      echoT($clsRpt->closeReport());
      closeBlock();
          
   }
   
   function showitemENPStats(&$clsRpt, &$item){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->divID        = 'aucENPStats';
      $attributes->divImageID   = 'aucENPStatsDivImg';
      openBlock('Record Information', '', $attributes);
      echoT(
         $clsRpt->showRecordStats($item->dteOrigin,
                               $item->strCFName.' '.$item->strCLName,
                               $item->dteLastUpdate,
                               $item->strLFName.' '.$item->strLLName,
                               $clsRpt->strWidthLabel));
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   