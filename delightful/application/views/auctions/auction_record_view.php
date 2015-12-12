<?php

   global $genumDateFormat;

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '130pt';
   $clsRpt->bValueEscapeHTML = false;

   $lAuctionID = $auction->lKeyID;
   showAuctionInfo ($clsRpt, $auction, $lAuctionID);
   showAuctionItems($clsRpt, $auction, $lAuctionID);

   showImageInfo      (CENUM_CONTEXT_AUCTION, $lAuctionID, ' Images',
                       $images, $lNumImages, $lNumImagesTot);
   showDocumentInfo   (CENUM_CONTEXT_AUCTION, $lAuctionID, ' Documents',
                       $docs, $lNumDocs, $lNumDocsTot);

   showAuctionENPStats($clsRpt, $auction);


   function showAuctionInfo(&$clsRpt, &$auction, $lAuctionID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      openBlock('Silent Auction: <b>'.htmlspecialchars($auction->strAuctionName).'</b>',
                   strLinkView_AuctionOverview($lAuctionID, 'Overview', false).'&nbsp;&nbsp;'
                  .strLinkEdit_Auction($lAuctionID, 'Edit auction information', true)
                  .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                  .strLinkRem_Auction($lAuctionID, 'Remove this auction record', true, true));

      echoT(
          $clsRpt->openReport());
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Auction ID:')
         .$clsRpt->writeCell (str_pad($lAuctionID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Name:')
         .$clsRpt->writeCell (htmlspecialchars($auction->strAuctionName))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Date of Auction:')
         .$clsRpt->writeCell (date($genumDateFormat, $auction->dteAuction))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Contact:')
         .$clsRpt->writeCell (htmlspecialchars($auction->strContact))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Email:')
         .$clsRpt->writeCell (htmlspecialchars($auction->strEmail))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Phone:')
         .$clsRpt->writeCell (htmlspecialchars($auction->strPhone))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Location:')
         .$clsRpt->writeCell (htmlspecialchars($auction->strLocation))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Account/Campaign:')
         .$clsRpt->writeCell (htmlspecialchars($auction->strAccount.' / '.$auction->strCampaign))
         .$clsRpt->closeRow  ());

         // Accounting Country
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Accounting Country:')
         .$clsRpt->writeCell ($auction->strFlagImg.' '.$auction->strCurrencySymbol)
         .$clsRpt->closeRow  ());

         // description
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Description:')
         .$clsRpt->writeCell (nl2br(htmlspecialchars($auction->strDescription)))
         .$clsRpt->closeRow  ());
         
         // default bidsheet
      if (is_null($auction->lBidsheetID)){
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Default Bid Sheet:')
            .$clsRpt->writeCell ('<i>Not set!</i>&nbsp;&nbsp; Click '.strLinkView_BidSheets('here', false).' to work with bid sheet templates.')
            .$clsRpt->closeRow  ());
      }else {
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Default Bid Sheet:')
            .$clsRpt->writeCell (htmlspecialchars($auction->strSheetName).' '
                                   .strLinkView_BidSheetRecord($auction->lDefaultBidSheet, 'View bid sheet', true)
                                   .' (based on template "'.$auction->tInfo->title.'")')
            .$clsRpt->closeRow  ());
      }

      echoT($clsRpt->closeReport());
      closeBlock();
   }

   function showAuctionItems(&$clsRpt, &$auction, $lAuctionID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock('Auction Packages, Items, and Bids', '');
      echoT(
          $clsRpt->openReport());
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('# Packages:')
         .$clsRpt->writeCell (number_format($auction->lNumPackages).'&nbsp;'
                     .strLinkView_AuctionPackages($lAuctionID, 'View auction packages', true)
                     .'&nbsp;&nbsp;'
                     .strLinkAdd_AuctionPackage($lAuctionID, 'Add new package', true))
         .$clsRpt->closeRow  ());

         // # Auction Items
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('# Auction Items:')
         .$clsRpt->writeCell (number_format($auction->lNumItems))
         .$clsRpt->closeRow  ());

         // Estimated Value
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Est. Value:')
         .$clsRpt->writeCell ($auction->strCurrencySymbol.' '.number_format($auction->curEstValue, 2))
         .$clsRpt->closeRow  ());

         // Out-of-Pocket Expenses
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Out-of-Pocket Exp.:')
         .$clsRpt->writeCell ($auction->strCurrencySymbol.' '.number_format($auction->curOutOfPocket, 2))
         .$clsRpt->closeRow  ());

         // # Winning Bids
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('# Winning Bids:')
         .$clsRpt->writeCell (number_format($auction->lNumWinningBids).'&nbsp;')
         .$clsRpt->closeRow  ());
//                     .strLinkView_AuctionBids($lAuctionID, 'View winning auction bids', true))

         // Winning Bid Total
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Winning Bid Total:')
         .$clsRpt->writeCell ($auction->strCurrencySymbol.' '
                            .number_format($auction->curAmntWinBids, 2))
         .$clsRpt->closeRow  ());

         // Winning Bids Fulfilled
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Winning Bids Fulfilled:')
         .$clsRpt->writeCell ($auction->strCurrencySymbol.' '
                            .number_format($auction->curIncome, 2))
         .$clsRpt->closeRow  ());

         // Unfulfilled Bids
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Unfulfilled Bids:')
         .$clsRpt->writeCell (number_format($auction->lNumUnfulfilled)).'&nbsp;'
//                     .strLinkView_AuctionUnfulfilled($lAuctionID, 'View winning bids that have not been fulfilled', true))
         .$clsRpt->closeRow  ());

      echoT($clsRpt->closeReport());
      closeBlock();
   }

   function showAuctionENPStats(&$clsRpt, &$auction){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->divID        = 'aucENPStats';
      $attributes->divImageID   = 'aucENPStatsDivImg';
      openBlock('Record Information', '', $attributes);
      echoT(
         $clsRpt->showRecordStats($auction->dteOrigin,
                               $auction->strCFName.' '.$auction->strCLName,
                               $auction->dteLastUpdate,
                               $auction->strLFName.' '.$auction->strLLName,
                               $clsRpt->strWidthLabel));
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

