<?php

   global $genumDateFormat;

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '120pt';
   $clsRpt->bValueEscapeHTML = false;

   $lPackageID = $package->lKeyID;
//   $lAuctionID = $auction->lKeyID;


   showPackageInfo  ($clsRpt, $package, $auction, $lPackageID, $lAuctionID);
   showBidAssignment($clsRpt, $package, $auction, $pbInfo, $lPackageID);
   showItemInfo     ($clsRpt, $lNumItems, $items, $lPackageID);
   showImageInfo              (CENUM_CONTEXT_AUCTIONPACKAGE, $lPackageID, ' Images',
                               $images, $lNumImages, $lNumImagesTot);
   showDocumentInfo           (CENUM_CONTEXT_AUCTIONPACKAGE, $lPackageID, ' Documents',
                               $docs, $lNumDocs, $lNumDocsTot);

   showPackageENPStats($clsRpt, $package);

   function showItemInfo(&$clsRpt, $lNumItems, &$items, $lPackageID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->divID        = 'aucENPItems';
      $attributes->divImageID   = 'aucENPItemsDivImg';
      openBlock('Items',
                strLinkView_AuctionItemsViaPID($lPackageID, 'View package items', true).'&nbsp;'
               .strLinkView_AuctionItemsViaPID($lPackageID, 'View package items', false)
               .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
               .strLinkAdd_AuctionItem($lPackageID, 'Add new item', true).'&nbsp;'
               .strLinkAdd_AuctionItem($lPackageID, 'Add new item', false),
               $attributes);

      if ($lNumItems==0){
         echoT('<i>There are no items assigned to this package.</i>');
      }else {
         echoT('
            <table class="enpRpt">
               <tr>
                  <td class="enpRptLabel">
                     Item ID
                  </td>
                  <td class="enpRptLabel">
                     Est. Value
                  </td>
                  <td class="enpRptLabel">
                     Out-of-Pocket
                  </td>
                  <td class="enpRptLabel">
                     Name
                  </td>
                  <td class="enpRptLabel">
                     Provided By
                  </td>
               </tr>');

         $curTotOOP = $curTotEst = 0.0;
         foreach ($items as $item){
            $lItemID = $item->lKeyID;
            $bBiz = $item->itemDonor_bBiz;
            $lDonorID = $item->lItemDonorID;
            
            $curTotOOP += $item->curOutOfPocket;
            $curTotEst += $item->curEstAmnt;
            if ($bBiz){
               $strName = strLinkView_BizRecord($lDonorID, 'View business record', true).'&nbsp;'
                         .$item->itemDonor_safeName.' (business)';
            }else {
               $strName = strLinkView_PeopleRecord($lDonorID, 'View people record', true).'&nbsp;'
                         .$item->itemDonor_safeName;
            }
            echoT('
                  <tr>
                     <td class="enpRpt" style="text-align: center;">'
                        .str_pad($lItemID, 6, '0', STR_PAD_LEFT).'&nbsp'
                        .strLinkView_AuctionItem($lItemID, 'View auction item', true).'
                     </td>
                     <td class="enpRpt" style="text-align: right;">'
                        .number_format($item->curEstAmnt, 2).'
                     </td>
                     <td class="enpRpt" style="text-align: right;">'
                        .number_format($item->curOutOfPocket, 2).'
                     </td>
                     <td class="enpRpt">'
                        .$item->strSafeItemName.'
                     </td>
                     <td class="enpRpt">'
                        .$strName.'
                     </td>
                  </tr>');
         }
            echoT('
                  <tr>
                     <td class="enpRpt"><b>
                        Total:</b>
                     </td>
                     <td class="enpRpt" style="text-align: right;"><b>'
                        .number_format($curTotEst, 2).'</b>
                     </td>
                     <td class="enpRpt" style="text-align: right;"><b>'
                        .number_format($curTotOOP, 2).'</b>
                     </td>
                     <td class="enpRpt">
                        &nbsp;
                     </td>
                     <td class="enpRpt">
                        &nbsp;
                     </td>
                  </tr>');
         echoT('</table>');
      }


      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function showBidAssignment(&$clsRpt, &$package, &$auction, &$pbInfo, $lPackageID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      openBlock('Bid Assignment', '');

      echoT(
          $clsRpt->openReport());

      $lBidWinnerID = $package->lBidWinnerID;
      $lGiftID      = $package->lGiftID;
      $bAssigned    = !is_null($lBidWinnerID);
      $bFulfilled   = !is_null($lGiftID);
      if ($bAssigned){
            // winning bidder
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Package Winner:', '', 'vertical-align: bottom;')
            .$clsRpt->writeCell ($pbInfo->strSafeNameFL.' '.$pbInfo->strLink)
            .$clsRpt->closeRow  ());

            // winning bid amount
         if ($bFulfilled){
            $strEditWin = '';
         }else {
            $strEditWin = strLinkEdit_AuctionWinnngBid($lPackageID, $lBidWinnerID, 'Edit winning bid', true);
         }
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Winning Bid:', '', 'vertical-align: bottom;')
            .$clsRpt->writeCell (
                     $auction->strCurrencySymbol
                    .' '.number_format($package->curWinBidAmnt, 2)
                    .'&nbsp;'.$strEditWin
                    .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                    .strLinkRem_AuctionWinningBid($lPackageID, 'Remove this winning bid (and fulfillment)', true, true),
                    '', ' vertical-align: bottom; '
                    )
            .$clsRpt->closeRow  ());

            // fulfillment
         if ($bFulfilled){
            echoT(
                $clsRpt->openRow   ()
               .$clsRpt->writeLabel('Fulfilled?:', '', 'vertical-align: bottom;')
               .$clsRpt->writeCell (
                        'Yes: giftID '.str_pad($lGiftID, 5, '0', STR_PAD_LEFT).' '
                        .strLinkView_GiftsRecord($lGiftID, 'View gift record', true)
                        .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                        .strLinkRem_AuctionFulfillment($lPackageID, 'Remove fulfillment', true, true),
                       '', ' vertical-align: bottom; '
                       )
               .$clsRpt->closeRow  ());
         }else {
            echoT(
                $clsRpt->openRow   ()
               .$clsRpt->writeLabel('Fulfilled?:', '', 'vertical-align: bottom;')
               .$clsRpt->writeCell (
                        'No '
                        .strLink_SetPackageFulfill($lPackageID, 'Fulfill/complete this bid', true),
                       '', ' vertical-align: bottom; '
                       )
               .$clsRpt->closeRow  ());
         }
      }else {
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Package Winner:', '', 'vertical-align: bottom;')
            .$clsRpt->writeCell ('<i>Not assigned</i>&nbsp;&nbsp;&nbsp;'
                         .strLink_SetPackageWinner($lPackageID, 'Add a bid winner', true).'&nbsp;'
                         .strLink_SetPackageWinner($lPackageID, 'Add a bid winner', false).'&nbsp;'
                         )
            .$clsRpt->closeRow  ());
      }

      echoT($clsRpt->closeReport());
      closeBlock();

   }

   function showPackageInfo(&$clsRpt, &$package, &$auction, $lPackageID, $lAuctionID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      openBlock('Silent Auction Package',
                   strLinkEdit_AuctionPackage($lAuctionID, $lPackageID, 'Edit package information', true)
                  .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                  .strLinkRem_AuctionPackage($lAuctionID, $lPackageID, 'Remove this package record', true, true));

      echoT(
          $clsRpt->openReport());
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Package ID:')
         .$clsRpt->writeCell (str_pad($lPackageID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ());

         // Package Name
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Name:')
         .$clsRpt->writeCell ($package->strPackageSafeName)
         .$clsRpt->closeRow  ());

         // Min bid amount
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Min. Bid Amount:')
         .$clsRpt->writeCell ($auction->strCurrencySymbol.' '.number_format($package->curMinBidAmnt, 2))
         .$clsRpt->closeRow  ());

         // Min bid increment
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Min. Bid Increment:')
         .$clsRpt->writeCell ($auction->strCurrencySymbol.' '.number_format($package->curMinBidInc, 2))
         .$clsRpt->closeRow  ());
         
         // Buy it now amount
      if (is_null($package->curBuyItNowAmnt)){
         $strBuyItNow = '<i>Not set for this package</i>';
      }else {
         $strBuyItNow = $auction->strCurrencySymbol.' '.number_format($package->curBuyItNowAmnt, 2);
      }
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('"Buy it now" amount:')
         .$clsRpt->writeCell ($strBuyItNow)
         .$clsRpt->closeRow  ());

         // Reserve
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Reserve:')
         .$clsRpt->writeCell ($auction->strCurrencySymbol.' '.number_format($package->curReserveAmnt, 2))
         .$clsRpt->closeRow  ());

         // default bidsheet         
      if (is_null($package->lBidSheetID) || is_null($package->tInfo) || is_null($package->lTemplateID)){
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Bid Sheet:')
            .$clsRpt->writeCell ('<i>Not set!</i>')
            .$clsRpt->closeRow  ());
      }else {
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Bid Sheet:')
            .$clsRpt->writeCell (htmlspecialchars($package->strSheetName).'&nbsp;'
                                   .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                                   .strLinkView_BidSheetRecord($package->lBidSheetID, 'View bid sheet template', true)
                                   .'&nbsp;(based on template "'.$package->tInfo->title.'")')
            .$clsRpt->closeRow  ());
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Bid Sheet PDF:')
            .$clsRpt->writeCell (strLink_PDF_PackageBidSheet(
                                            $package->lBidSheetID, $lPackageID, 'Create bid sheet for this package', true,
                                            ' target="_blank" ')
                                            .'&nbsp;'
                                .strLink_PDF_PackageBidSheet(                                            
                                            $package->lBidSheetID, $lPackageID, 'Create bid sheet for this package', false,
                                            ' target="_blank" ')
                                            )
            .$clsRpt->closeRow  ());
      }
         
         
         // # items
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('# Auction Items:')
         .$clsRpt->writeCell (number_format($package->lNumItems).'&nbsp;'
                    .strLinkView_AuctionItemsViaPID($lPackageID, 'View package items', true)
                    .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                    .strLinkAdd_AuctionItem($lPackageID, 'Add new item', true)
                    )
         .$clsRpt->closeRow  ());

         // Est. Value of Items
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Est. Value:')
         .$clsRpt->writeCell ($auction->strCurrencySymbol.' '.number_format($package->curEstValue, 2))
         .$clsRpt->closeRow  ());

         // Out of pocket Expenses
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Out-of-Pocket:')
         .$clsRpt->writeCell ($auction->strCurrencySymbol.' '.number_format($package->curOutOfPocket, 2))
         .$clsRpt->closeRow  ());

         // Public Notes
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Public Notes:')
         .$clsRpt->writeCell (nl2br(htmlspecialchars($package->strDescription)))
         .$clsRpt->closeRow  ());

         // Private Notes
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Private Notes:')
         .$clsRpt->writeCell (nl2br(htmlspecialchars($package->strInternalNotes)))
         .$clsRpt->closeRow  ());

      echoT($clsRpt->closeReport());
      closeBlock();
   }

   function showPackageENPStats(&$clsRpt, &$package){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->divID        = 'aucENPStats';
      $attributes->divImageID   = 'aucENPStatsDivImg';
      openBlock('Record Information', '', $attributes);
      echoT(
         $clsRpt->showRecordStats($package->dteOrigin,
                               $package->strCFName.' '.$package->strCLName,
                               $package->dteLastUpdate,
                               $package->strLFName.' '.$package->strLLName,
                               $clsRpt->strWidthLabel));
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }


