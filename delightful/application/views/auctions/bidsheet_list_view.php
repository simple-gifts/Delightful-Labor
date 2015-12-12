<?php
   global $genumDateFormat;
   
   if ($lNumAuctions == 0){
      echoT('<br><i>Bid sheet templates are attached to your silent auction events. You currently<br>
            do not have any silent auctions defined. Please create one or more auction events <br>
            before creating bit sheet templates.</i><br><br>');
            
      echoT(strLinkAdd_Auction('Add auction event', true,  'id="imgaddAuction" ').'&nbsp;'
           .strLinkAdd_Auction('Add auction event', false, 'id="lnkaddAuction" ').'<br>');
      return;
   }

   echoT(strLinkAdd_BidSheet('Add new bid sheet template', true).'&nbsp;'
        .strLinkAdd_BidSheet('Add new bid sheet template', false).'<br><br>');

   if ($lNumBidSheets==0){
      echoT('<i>There are currently no bid sheet templates in your database.<br><br></i>');
   }else {
      echoT('
         <table class="enpRptC">
            <tr>
               <td class="enpRptTitle" colspan="10">
                  Bid Sheet Templates
               </td>
            </tr>');

      echoT('
            <tr>
               <td class="enpRptLabel">
                  Bid Sheet ID
               </td>
               <td class="enpRptLabel">
                  Auction
               </td>
               <td class="enpRptLabel">
                  Name
               </td>
               <td class="enpRptLabel">
                  Description
               </td>
               <td class="enpRptLabel">
                  Base Template
               </td>
            </tr>');
            
      foreach ($bidSheets as $bs){
         $lBSID = $bs->lKeyID;
         echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align:center;">'
                  .str_pad($lBSID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkView_BidSheetRecord($lBSID, 'View Bid Sheet', true).'
               </td>
               <td class="enpRpt" style="width: 180pt;">'
                  .htmlspecialchars($bs->strAuctionName).'&nbsp;('
                        .date($genumDateFormat, $bs->dteAuction).')
               </td>
               <td class="enpRpt" style="width: 180pt;">'
                  .htmlspecialchars($bs->strSheetName).'
               </td>
               <td class="enpRpt" style="width: 200pt;">'
                  .nl2br(htmlspecialchars($bs->strDescription)).'
               </td>
               <td class="enpRpt" >'
                  .$bs->tInfo->title
//                  .'<br>'.$bs->tInfo->strThumbImgLink.'
                  .'
               </td>
            </tr>');
      }      

      echoT('
         </table><br><br>');
   }
