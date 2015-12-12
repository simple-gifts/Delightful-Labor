<?php

   echoT(strLinkAdd_AuctionItem($lPackageID, 'Add new item to this package', true).'&nbsp;'
        .strLinkAdd_AuctionItem($lPackageID, 'Add new item to this package', false).'<br><br>');
   if ($lNumItems == 0){
      echoT('<i>There are currently no items in the package <b>"'.$package->strPackageSafeName.'"</b><br><br>');
      return;
   }else {
      writeAuctionItemsTable($package, $items, false);
   }

