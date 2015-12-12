<?php
   global $genumDateFormat;

   echoT(strLinkAdd_Auction('Add new auction', true).'&nbsp;'
        .strLinkAdd_Auction('Add new auction', false).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
        .strLinkView_BidSheets('View bid sheet templates', true).'&nbsp;'
        .strLinkView_BidSheets('View bid sheet templates', false)
        .'<br><br>');

   if ($lNumAuctions==0){
      echoT('<i>There are currently no auctions in your database.<br><br></i>');
   }else {
      echoT('
         <table class="enpRptC">
            <tr>
               <td class="enpRptTitle" colspan="10">
                  Silent Auctions
               </td>
            </tr>');

      echoT('
            <tr>
               <td class="enpRptLabel">
                  Auction ID
               </td>
               <td class="enpRptLabel">
                  &nbsp;
               </td>
               <td class="enpRptLabel">
                  Name
               </td>
               <td class="enpRptLabel">
                  Date
               </td>
               <td class="enpRptLabel">
                  # Packages
               </td>
               <td class="enpRptLabel">
                  # Items
               </td>
               <td class="enpRptLabel">
                  Estimated Value
               </td>
               <td class="enpRptLabel">
                  Out of Pocket
               </td>
               <td class="enpRptLabel">
                  Income
               </td>
               <td class="enpRptLabel">
                  Net
               </td>               
            </tr>');
            
      foreach ($auctions as $auction){
         $lAuctionID = $auction->lKeyID;
         echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align:left;">'
                  .str_pad($lAuctionID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkView_AuctionRecord($lAuctionID, 'View Auction Record', true).'<br>'
                  .strLinkView_AuctionOverview($lAuctionID, 'Overview', false).'
               </td>
               <td class="enpRpt">'
                  .strLinkRem_Auction($lAuctionID, 'Remove Auction', true, true).'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($auction->strAuctionName).'
               </td>
               <td class="enpRpt" style="text-align:center;">'
                  .date($genumDateFormat, $auction->dteAuction).'
               </td>
               <td class="enpRpt" style="text-align:center; padding-left: 7pt;">'
                  .number_format($auction->lNumPackages).'&nbsp;&nbsp;&nbsp;'
                  .strLinkView_AuctionPackages($lAuctionID, 'View auction packages', true)
                  .'&nbsp;&nbsp;'
                  .strLinkAdd_AuctionPackage($lAuctionID, 'Add new package', true).'                  
               </td>
               <td class="enpRpt" style="text-align:center;">'
                  .number_format($auction->lNumItems).'
               </td>
               <td class="enpRpt" style="text-align:right;">'
                  .number_format($auction->curEstValue, 2).'&nbsp;'.$auction->strFlagImg.'
               </td>
               <td class="enpRpt" style="text-align:right;">'
                  .number_format($auction->curOOP, 2).'
               </td>
               <td class="enpRpt" style="text-align:right;">'
                  .number_format($auction->curIncome, 2).'
               </td>
               <td class="enpRpt" style="text-align:right;">'
                  .number_format(($auction->curIncome-$auction->curOOP), 2).'
               </td>
            </tr>');
      }      

      echoT('
         </table><br><br>');
   }
