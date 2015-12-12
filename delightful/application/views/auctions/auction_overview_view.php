<?php

   global $genumDateFormat;

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '130pt';
   $clsRpt->bValueEscapeHTML = false;

   $lAuctionID = $auction->lKeyID;
   showAuctionInfo ($clsRpt, $auction,  $lAuctionID);
   showPackageInfo ($clsRpt, $auction, $packages, $lNumPackages, $lAuctionID);

   if ($lNumPackages > 0){
      foreach ($packages as $package){
         showPackageDetail($clsRpt, $package);
      }
   }

   function showPackageDetail(&$clsRpt, &$package){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lPackageID = $package->lKeyID;
      $attributes = new stdClass;
      $attributes->divID        = 'aucPackage'.$lPackageID;
      $attributes->divImageID   = 'aucDiv'.$lPackageID;
      $attributes->lUnderscoreWidth = 600;
      openBlock('Package '.str_pad($lPackageID, 5, '0', STR_PAD_LEFT).': '.$package->strPackageSafeName
               .strLinkView_AuctionPackageRecord($lPackageID, 'View package record', true),
                strLinkAdd_AuctionItem($lPackageID, 'Add item to this package', true).'&nbsp;'
               .strLinkAdd_AuctionItem($lPackageID, 'Add item to this package', false), $attributes);

      echoT('<br>');

      if ($package->lNumItems == 0){
         echoT('<i>There are currently no items in the package <b>"'.$package->strPackageSafeName.'"</b><br><br>');
      }else {
         writeAuctionItemsTable($package, $package->items, false);
      }
      
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function showPackageInfo(&$clsRpt, &$auction, &$packages, $lNumPackages, $lAuctionID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lUnderscoreWidth = 600;
      openBlock('Packages', strLinkAdd_AuctionPackage($lAuctionID, 'Add New Package', true).'&nbsp;&nbsp;'
                  .strLinkAdd_AuctionPackage($lAuctionID, 'Add New Package', false), $attributes);
      
      echoT('<br>');

      if ($lNumPackages == 0){
         echoT('<i>There are currently no packages in the <b>"'.$auction->strSafeName.'"</b> auction<br><br>');
      }else {
         writeAuctionPackageTable($lAuctionID, $auction, $packages, false);
      }      

      closeBlock();
   }


   function showAuctionInfo(&$clsRpt, &$auction, $lAuctionID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $attributes = new stdClass;
      $attributes->lUnderscoreWidth = 600;
      
      openBlock('Silent Auction Overview: <b>'.htmlspecialchars($auction->strAuctionName).'</b>',
                   strLinkView_AuctionRecord($lAuctionID, 'View auction record', true).'&nbsp;&nbsp;'
                  .strLinkEdit_Auction($lAuctionID, 'Edit auction information', true)
                  .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                  .strLinkRem_Auction($lAuctionID, 'Remove this auction record', true, true), $attributes);

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

