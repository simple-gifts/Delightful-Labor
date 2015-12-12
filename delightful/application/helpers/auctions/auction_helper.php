<?php
/*
      $this->load->helper('auctions/auction');
*/

   function setPackageContext($lPackageID, &$lAuctionID, &$displayData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $CI =& get_instance();

      $CI->cPackages->loadPackageByPacID($lPackageID);
      $displayData['package'] = $package = $CI->cPackages->packages[0];

      $displayData['lAuctionID'] = $lAuctionID = $package->lAuctionID;
      $CI->cAuction->loadAuctionByAucID($lAuctionID);
      $displayData['auction'] = $auction = &$CI->cAuction->auctions[0];
      $displayData['contextSummary'] = $CI->cPackages->packageHTMLSummary();

   }

   function strXlateTemplate($lTemplateID, &$tInfo){ // &$strThumbImgLink, &$strLinkImage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $tInfo = new stdClass;;
      $tInfo->title = '';
      switch ($lTemplateID){
         case CENUM_BSTEMPLATE_SIMPLEPACK:    // simple template - package
            $tInfo->strThumbImgLink = '<img src="'.base_url().'images/auctions/bidSheetSample01_tn.png" border="0">';
            $tInfo->strLinkImage    = '<a href="'.base_url().'images/auctions/bidSheetSample01.png" target="_blank">';
            $tInfo->title           = 'Package Info';
            $tInfo->htmlInfo        = '<b>'.$tInfo->title.'</b><br>'
                                       .'<i>Bid sheet with<br>'
                                       .'organization logo and<br>'
                                       .'package description</i>';
            break;
         case CENUM_BSTEMPLATE_PACKAGEPIC:    // simple template - package and item
            $tInfo->strThumbImgLink = '<img src="'.base_url().'images/auctions/bidSheetSample02_tn.png" border="0">';
            $tInfo->strLinkImage    = '<a href="'.base_url().'images/auctions/bidSheetSample02.png" target="_blank">';
            $tInfo->title           = 'Package Info w/Photo';
            $tInfo->htmlInfo        = '<b>'.$tInfo->title.'</b><br>'
                                       .'<i>Bid sheet with<br>'
                                       .'organization logo,<br>'
                                       .'package image, and<br>' 
                                       .'package description</i>';
            break;
            
         case CENUM_BSTEMPLATE_MIN:           // minimalist
            $tInfo->strThumbImgLink = '<img src="'.base_url().'images/auctions/bidSheetSample03_tn.png" border="0">';
            $tInfo->strLinkImage    = '<a href="'.base_url().'images/auctions/bidSheetSample03.png" target="_blank">';
            $tInfo->title           = 'Simple Bid Sheet';
            $tInfo->htmlInfo        = '<b>'.$tInfo->title.'</b><br>'
                                       .'<i>Minimalist sheet with<br>'
                                       .'organization and<br>'
                                       .'package description</i>';
            break;

         case CENUM_BSTEMPLATE_ITEMS:           // package and items
            $tInfo->strThumbImgLink = '<img src="'.base_url().'images/auctions/bidSheetSample04_tn.png" border="0">';
            $tInfo->strLinkImage    = '<a href="'.base_url().'images/auctions/bidSheetSample04.png" target="_blank">';
            $tInfo->title           = 'Package/Items Bid Sheet';
            $tInfo->htmlInfo        = '<b>'.$tInfo->title.'</b><br>'
                                       .'<i>Extended sheet with<br>'
                                       .'organization, <br>'
                                       .'package description,<br>'
                                       .'and itemized items list</i>';
            break;

         default:
            $strThumbImgLink =
            $strLinkImage    =
            $tInfo->title    = '# error #';
            break;
      }
      return($tInfo->title);
   }

   function loadDefaultTemplateVals($enumTemplateType, $bNew, &$formData, &$bs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      setShowBidFields($enumTemplateType, $formData);
      
      if ($bNew){
         $formData->bIncludeSignup              = true;
         switch ($enumTemplateType){
            case CENUM_BSTEMPLATE_SIMPLEPACK:
               $formData->bIncludeOrgLogo             = true;
               $formData->bIncludeOrgName             = true;
               $formData->bIncludeMinBid              = true;
               $formData->bIncludeMinBidInc           = true;
               $formData->bIncludeBuyItNow            = true;
               $formData->bIncludeReserve             = false;
               $formData->bIncludeDate                = true;
               $formData->bIncludeFooter              = true;
                                                      
               $formData->bIncludePackageName         = true;
               $formData->bIncludePackageID           = true;
               $formData->bIncludePackageDesc         = true;
               $formData->bIncludePackageImage        = false;
               $formData->bIncludePackageEstValue     = true;
                                                      
               $formData->bIncludeItemName            = false;
               $formData->bIncludeItemID              = false;
               $formData->bIncludeItemDesc            = false;
               $formData->bIncludeItemImage           = false;
               $formData->bIncludeItemDonor           = false;
               $formData->bIncludeItemEstValue        = false;
               break;

            case CENUM_BSTEMPLATE_PACKAGEPIC:
               $formData->bIncludeOrgLogo             = true;
               $formData->bIncludeOrgName             = true;
               $formData->bIncludeMinBid              = true;
               $formData->bIncludeMinBidInc           = true;
               $formData->bIncludeBuyItNow            = true;
               $formData->bIncludeReserve             = false;
               $formData->bIncludeDate                = true;
               $formData->bIncludeFooter              = true;
                                                      
               $formData->bIncludePackageName         = true;
               $formData->bIncludePackageID           = true;
               $formData->bIncludePackageDesc         = true;
               $formData->bIncludePackageImage        = true;
               $formData->bIncludePackageEstValue     = true;
                                                      
               $formData->bIncludeItemName            = false;
               $formData->bIncludeItemID              = false;
               $formData->bIncludeItemDesc            = false;
               $formData->bIncludeItemImage           = false;
               $formData->bIncludeItemDonor           = false;
               $formData->bIncludeItemEstValue        = false;
               break;

            case CENUM_BSTEMPLATE_MIN:
               $formData->bIncludeOrgLogo             = false;
               $formData->bIncludeOrgName             = true;
               $formData->bIncludeMinBid              = true;
               $formData->bIncludeMinBidInc           = true;
               $formData->bIncludeBuyItNow            = true;
               $formData->bIncludeReserve             = false;
               $formData->bIncludeDate                = true;
               $formData->bIncludeFooter              = true;
                                                      
               $formData->bIncludePackageName         = true;
               $formData->bIncludePackageID           = true;
               $formData->bIncludePackageDesc         = true;
               $formData->bIncludePackageImage        = false;
               $formData->bIncludePackageEstValue     = true;
                                                      
               $formData->bIncludeItemName            = false;
               $formData->bIncludeItemID              = false;
               $formData->bIncludeItemDesc            = false;
               $formData->bIncludeItemImage           = false;
               $formData->bIncludeItemDonor           = false;
               $formData->bIncludeItemEstValue        = false;
               break;

            case CENUM_BSTEMPLATE_ITEMS:
               $formData->bIncludeOrgLogo             = false;
               $formData->bIncludeOrgName             = true;
               $formData->bIncludeMinBid              = true;
               $formData->bIncludeMinBidInc           = true;
               $formData->bIncludeBuyItNow            = true;
               $formData->bIncludeReserve             = false;
               $formData->bIncludeDate                = true;
               $formData->bIncludeFooter              = true;
                                                      
               $formData->bIncludePackageName         = true;
               $formData->bIncludePackageID           = true;
               $formData->bIncludePackageDesc         = true;
               $formData->bIncludePackageImage        = true;
               $formData->bIncludePackageEstValue     = true;
                                                      
               $formData->bIncludeItemName            = true;
               $formData->bIncludeItemID              = true;
               $formData->bIncludeItemDesc            = true;
               $formData->bIncludeItemImage           = true;
               $formData->bIncludeItemDonor           = true;
               $formData->bIncludeItemEstValue        = true;
               break;

            default:
               screamForHelp($enumTemplateType.': invalid template type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }
      }else {
         $formData->bIncludeOrgName         = $bs->bIncludeOrgName;
         $formData->bIncludeOrgLogo         = $bs->bIncludeOrgLogo;
         $formData->bIncludeMinBid          = $bs->bIncludeMinBid;
         $formData->bIncludeMinBidInc       = $bs->bIncludeMinBidInc;
         $formData->bIncludeBuyItNow        = $bs->bIncludeBuyItNow;
         $formData->bIncludeReserve         = $bs->bIncludeReserve;
         $formData->bIncludeDate            = $bs->bIncludeDate;
         $formData->bIncludeFooter          = $bs->bIncludeFooter;

         $formData->bIncludePackageName     = $bs->bIncludePackageName;
         $formData->bIncludePackageID       = $bs->bIncludePackageID;
         $formData->bIncludePackageDesc     = $bs->bIncludePackageDesc;
         $formData->bIncludePackageImage    = $bs->bIncludePackageImage;
         $formData->bIncludePackageEstValue = $bs->bIncludePackageEstValue;

         $formData->bIncludeItemName        = $bs->bIncludeItemName;
         $formData->bIncludeItemID          = $bs->bIncludeItemID;
         $formData->bIncludeItemDesc        = $bs->bIncludeItemDesc;
         $formData->bIncludeItemImage       = $bs->bIncludeItemImage;
         $formData->bIncludeItemDonor       = $bs->bIncludeItemDonor;
         $formData->bIncludeItemEstValue    = $bs->bIncludeItemEstValue;
         $formData->bIncludeSignup          = $bs->bIncludeSignup;
      }
   }
   
   function setShowBidFields($enumTemplateType, &$formData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      $formData->bShowIncludeSignup = true;
      
      switch ($enumTemplateType){
         case CENUM_BSTEMPLATE_SIMPLEPACK:   
            $formData->bShowIncludeOrgName         = true;
            $formData->bShowIncludeOrgLogo         = true;
            $formData->bShowIncludeMinBid          = true;
            $formData->bShowIncludeMinBidInc       = true;
            $formData->bShowIncludeBuyItNow        = true;
            $formData->bShowIncludeReserve         = true;
            $formData->bShowIncludeDate            = true;
            $formData->bShowIncludeFooter          = true;

            $formData->bShowIncludePackageName     = true;
            $formData->bShowIncludePackageID       = true;
            $formData->bShowIncludePackageDesc     = true;
            $formData->bShowIncludePackageImage    = false;
            $formData->bShowIncludePackageEstValue = true;

            $formData->bShowIncludeItemName        = false;
            $formData->bShowIncludeItemID          = false;
            $formData->bShowIncludeItemDesc        = false;
            $formData->bShowIncludeItemImage       = false;
            $formData->bShowIncludeItemDonor       = false;
            $formData->bShowIncludeItemEstValue    = false;
            break;
            
         case CENUM_BSTEMPLATE_PACKAGEPIC:   
            $formData->bShowIncludeOrgName         = true;
            $formData->bShowIncludeOrgLogo         = true;
            $formData->bShowIncludeMinBid          = true;
            $formData->bShowIncludeMinBidInc       = true;
            $formData->bShowIncludeBuyItNow        = true;
            $formData->bShowIncludeReserve         = true;
            $formData->bShowIncludeDate            = true;
            $formData->bShowIncludeFooter          = true;

            $formData->bShowIncludePackageName     = true;
            $formData->bShowIncludePackageID       = true;
            $formData->bShowIncludePackageDesc     = true;
            $formData->bShowIncludePackageImage    = true;
            $formData->bShowIncludePackageEstValue = true;

            $formData->bShowIncludeItemName        = false;
            $formData->bShowIncludeItemID          = false;
            $formData->bShowIncludeItemDesc        = false;
            $formData->bShowIncludeItemImage       = false;
            $formData->bShowIncludeItemDonor       = false;
            $formData->bShowIncludeItemEstValue    = false;
            break;
            
         case CENUM_BSTEMPLATE_MIN:   
            $formData->bShowIncludeOrgName         = true;
            $formData->bShowIncludeOrgLogo         = false;
            $formData->bShowIncludeMinBid          = true;
            $formData->bShowIncludeMinBidInc       = true;
            $formData->bShowIncludeBuyItNow        = true;
            $formData->bShowIncludeReserve         = true;
            $formData->bShowIncludeDate            = true;
            $formData->bShowIncludeFooter          = true;

            $formData->bShowIncludePackageName     = true;
            $formData->bShowIncludePackageID       = true;
            $formData->bShowIncludePackageDesc     = true;
            $formData->bShowIncludePackageImage    = false;
            $formData->bShowIncludePackageEstValue = true;

            $formData->bShowIncludeItemName        = false;
            $formData->bShowIncludeItemID          = false;
            $formData->bShowIncludeItemDesc        = false;
            $formData->bShowIncludeItemImage       = false;
            $formData->bShowIncludeItemDonor       = false;
            $formData->bShowIncludeItemEstValue    = false;
            break;
            
         case CENUM_BSTEMPLATE_ITEMS:   
            $formData->bShowIncludeOrgName         = true;
            $formData->bShowIncludeOrgLogo         = false;
            $formData->bShowIncludeMinBid          = true;
            $formData->bShowIncludeMinBidInc       = true;
            $formData->bShowIncludeBuyItNow        = true;
            $formData->bShowIncludeReserve         = true;
            $formData->bShowIncludeDate            = true;
            $formData->bShowIncludeFooter          = true;

            $formData->bShowIncludePackageName     = true;
            $formData->bShowIncludePackageID       = true;
            $formData->bShowIncludePackageDesc     = true;
            $formData->bShowIncludePackageImage    = true;
            $formData->bShowIncludePackageEstValue = true;

            $formData->bShowIncludeItemName        = true;
            $formData->bShowIncludeItemID          = true;
            $formData->bShowIncludeItemDesc        = true;
            $formData->bShowIncludeItemImage       = true;
            $formData->bShowIncludeItemDonor       = true;
            $formData->bShowIncludeItemEstValue    = true;
            break;
            
         default:
             screamForHelp($enumTemplateType.': invalid template type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }
   
   function writeAuctionPackageTable($lAuctionID, &$auction, &$packages, $bDescriptions){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      echoT('
         <table class="enpRpt">');
      echoT('
         <tr>
            <td class="enpRptLabel">
               package ID
            </td>
            <td class="enpRptLabel">
               &nbsp;
            </td>
            <td class="enpRptLabel">
               &nbsp;
            </td>
            <td class="enpRptLabel">
               Bid<br>Sheet
            </td>
            <td class="enpRptLabel">
               Name
            </td>
            <td class="enpRptLabel" nowrap>
               # Items
            </td>
            <td class="enpRptLabel">
               Est. Value
            </td>
            <td class="enpRptLabel">
               Winner
            </td>
            <td class="enpRptLabel">
               Winning Bid
            </td>
            <td class="enpRptLabel">
               Fulfilled?
            </td>
         </tr>');

      $curEstValueTot = $curWinBidTot = $curFullfillTot = 0.0;
      $lTotItems = 0;
      foreach ($packages as $package){
         $lPackageID = $package->lKeyID;
         $lWinnerID  = $package->lBidWinnerID;
         $lGiftID    = $package->lGiftID;
         
         $curEstValueTot += $package->curEstValue;
         $lTotItems += $package->lNumItems;

         if (is_null($lWinnerID)){
            $strWinner = 'n/a '.strLink_SetPackageWinner($lPackageID, 'Set Winner', true);
            $strWinnerStyle = 'text-align: center;';
            $strWinningBid = 'n/a';
            $strWinningBidStyle = 'text-align: center;';
            $strFulfilled = 'No';
            $strFulfilledStyle = 'text-align: center;';
            $strPeopleLink = '';
         }else {
            $curWinBidTot += $package->curWinBidAmnt;
            $strWinner = $package->bidWinner->strLink.'&nbsp;'.$package->bidWinner->strSafeNameLF;
            $strWinnerStyle = '';
            $strWinningBid = number_format($package->curWinBidAmnt, 2);
            $strWinningBidStyle = 'text-align: right;';
            if (is_null($lGiftID)){
               $strFulfilled = 'No'.strLink_SetPackageFulfill($lPackageID, 'Fulfill/receive payment', true);
               $strFulfilledStyle = 'text-align: center;';
            }else {
               $strFulfilled = number_format($package->curActualGiftAmnt, 2).'&nbsp;'
                                .strLinkView_GiftsRecord($lGiftID, 'View gift record', true);
               $strFulfilledStyle = '';
               $curFullfillTot += $package->curActualGiftAmnt;
            }
         }

         if (is_null($package->lBidSheetID)){
            $strLinkPDF = 'n/a';
         }else {
            $strLinkPDF = strLink_PDF_PackageBidSheet($package->lBidSheetID, $lPackageID, 'Create PDF Bid Sheet', true, ' target="_blank" ');
         }

         echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align: center;">'
                  .str_pad($lPackageID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkView_AuctionPackageRecord($lPackageID, 'View auction package', true).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .strLinkEdit_AuctionPackage($lAuctionID, $lPackageID, 'Edit package', true).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .strLinkRem_AuctionPackage($lAuctionID, $lPackageID, 'Remove Package', true, true).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .$strLinkPDF.'
               </td>
               <td class="enpRpt">'
                  .$package->strPackageSafeName.'
               </td>
               <td class="enpRpt" style="text-align: center; padding-left: 7pt;" nowrap>'
                  .number_format($package->lNumItems).'&nbsp;'
                  .strLinkView_AuctionItemsViaPID($lPackageID, 'View package items', true).'&nbsp;'
                  .strLinkAdd_AuctionItem($lPackageID, 'Add new item', true).'
               </td>
               <td class="enpRpt" style="text-align: right;">'
                  .$auction->strCurrencySymbol.' '.number_format($package->curEstValue, 2).'
               </td>
               <td class="enpRpt" style="'.$strWinnerStyle.'">'
                  .$strWinner.'
               </td>
               <td class="enpRpt" style="'.$strWinningBidStyle.'">'
                  .$strWinningBid.'
               </td>
               <td class="enpRpt"style="'.$strFulfilledStyle.'">'
                  .$strFulfilled.'
               </td>
            </tr>');

      }
      
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" colspan="5">
               <b>Total:</b>
            </td>
            <td class="enpRpt" style="width: 40pt; text-align: center;"><b>'
               .number_format($lTotItems).'</b>
            </td>
            <td class="enpRpt" style="width: 40pt; text-align: right;"><b>'
               .number_format($curEstValueTot, 2).'</b>
            </td>
            <td class="enpRpt" >
               &nbsp;
            </td>
            <td class="enpRpt" style="width: 40pt; text-align: right;"><b>'
               .number_format($curWinBidTot, 2).'</b>
            </td>
            <td class="enpRpt" style="width: 40pt; text-align: right;"><b>'
               .number_format($curFullfillTot, 2).'</b>
            </td>
         </tr>');      
      

      echoT('</table><br><br>');
   }   

   function writeAuctionItemsTable(&$package, &$items, $bDescriptions){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lPackageID = $package->lKeyID;
      echoT('
         <table class="enpRpt">
            <tr>
               <td class="enpRptTitle" colspan="8">
                  Items in the package <b>"'.$package->strPackageSafeName.'"</b>
               </td>
            </tr>');

      echoT('
         <tr>
            <td class="enpRptLabel">
               item ID
            </td>
            <td class="enpRptLabel">
               &nbsp;
            </td>
            <td class="enpRptLabel">
               &nbsp;
            </td>
            <td class="enpRptLabel">
               Name
            </td>
            <td class="enpRptLabel">
               Est. Value
            </td>
            <td class="enpRptLabel">
               Out of Pocket
            </td>
            <td class="enpRptLabel">
               From
            </td>
         </tr>');

      $curTotVal = $curTotOOP = 0.0;
      foreach ($items as $item){
         $lItemID = $item->lKeyID;
         if ($item->itemDonor_bBiz){
            $strNameLink = ' <i>(business)</i> '.strLinkView_BizRecord($item->lItemDonorID, 'View business record', true);
         }else {
            $strNameLink = ' '.strLinkView_PeopleRecord($item->lItemDonorID, 'View people record', true);
         }

         echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align: center; width: 20pt;">'
                  .str_pad($lItemID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkView_AuctionItem($lItemID, 'View auction item', true).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .strLinkEdit_AuctionItem($lPackageID, $lItemID, 'Edit Item', true).'
               </td>
               <td class="enpRpt" style="text-align: center; width: 20pt;">'
                  .strLinkRem_AuctionItem($lPackageID, $lItemID, 'Remove auction item', true, true).'
               </td>
               <td class="enpRpt" style="width: 140pt;">'
                  .$item->strSafeItemName.'
               </td>
               <td class="enpRpt" style="width: 40pt; text-align: right;">'
                  .number_format($item->curEstAmnt, 2).'
               </td>
               <td class="enpRpt" style="width: 40pt; text-align: right;">'
                  .number_format($item->curOutOfPocket, 2).'
               </td>
               <td class="enpRpt" style="width: 110pt;">'
                  .$item->itemDonor_safeName.$strNameLink.'
               </td>
            </tr>');
         $curTotVal += $item->curEstAmnt;
         $curTotOOP += $item->curOutOfPocket;
      }
      
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" colspan="4">
               <b>Total:</b>
            </td>
            <td class="enpRpt" style="width: 40pt; text-align: right;"><b>'
               .number_format($curTotVal, 2).'</b>
            </td>
            <td class="enpRpt" style="width: 40pt; text-align: right;"><b>'
               .number_format($curTotOOP, 2).'</b>
            </td>
            <td colspan="2">
               &nbsp;
            </td>
         </tr>');      

      echoT('
         </table><br>');
   }
   


