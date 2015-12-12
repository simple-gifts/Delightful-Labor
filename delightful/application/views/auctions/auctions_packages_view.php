<?php

   echoT(strLinkAdd_AuctionPackage($lAuctionID, 'Add new auction package', true).'&nbsp;'
        .strLinkAdd_AuctionPackage($lAuctionID, 'Add new auction package', false).'<br><br>');
   if ($lNumPackages == 0){
      echoT('<i>There are currently no packages in the <b>"'.$auction->strSafeName.'"</b> auction<br><br>');
      return;
   }else {
         writeAuctionPackageTable($lAuctionID, $auction, $packages, false);
   }


