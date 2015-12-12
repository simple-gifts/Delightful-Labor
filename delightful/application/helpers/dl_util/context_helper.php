<?php
/*-----------------------------------------------------------------------------
// copyright (c) 2011-2015 by Database Austin.
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
-----------------------------------------------------------------------------
      $this->load->helper('dl_util/context');
-----------------------------------------------------------------------------*/

   function loadSupportModels($enumType, $lForeignID){
   //---------------------------------------------------------------------
   //    $CI =& get_instance();
   //---------------------------------------------------------------------
      $CI =& get_instance();

      $CI->load->model ('img_docs/mimage_doc',    'clsImgDoc');
      $CI->load->model ('img_docs/mimg_doc_tags', 'cidTags');
      $CI->load->helper('img_docs/image_doc');
      $CI->load->helper('img_docs/link_img_docs');
      $CI->load->helper('img_docs/img_doc_tags');

      switch ($enumType){
         case CENUM_CONTEXT_AUCTION:
            $CI->load->model  ('auctions/mauctions',  'cAuction');
            $CI->load->model  ('admin/madmin_aco',    'clsACO');
            $CI->load->helper ('dl_util/link_auction');
            $CI->load->helper('auctions/auction');
            break;

         case CENUM_CONTEXT_AUCTIONITEM:
            $CI->load->model  ('auctions/mauctions',   'cAuction');
            $CI->load->model  ('auctions/mitems',      'cItems');
            $CI->load->helper ('dl_util/link_auction');
            $CI->load->helper('auctions/auction');
            break;

         case CENUM_CONTEXT_AUCTIONPACKAGE:
            $CI->load->model  ('auctions/mauctions',     'cAuction');
            $CI->load->model  ('auctions/mpackages',     'cPackages');
//            $CI->load->model  ('img_docs/mimage_doc',    'clsImgDoc');
            $CI->load->helper ('dl_util/link_auction');
            $CI->load->helper('auctions/auction');
            break;

         case CENUM_CONTEXT_PEOPLE:
//            $CI->load->helper('dl_util/email_web');
            $CI->load->model('admin/madmin_aco', 'clsACO');
            $CI->load->model('people/mpeople',   'clsPeople');
            break;

         case CENUM_CONTEXT_CLIENT:
         case CENUM_CONTEXT_CPROGRAM:
         case CENUM_CONTEXT_CPROGENROLL:
         case CENUM_CONTEXT_CPROGATTEND:
            $CI->load->model('clients/mclients', 'clsClients');
            break;

         case CENUM_CONTEXT_LOCATION:
            $CI->load->model('clients/mclient_locations', 'clsLoc');
            break;

         case CENUM_CONTEXT_GRANTS:
         case CENUM_CONTEXT_GRANTPROVIDER:
            $CI->load->model('admin/madmin_aco', 'cACO');
            $CI->load->model('grants/mgrants',   'cgrants');
            $CI->load->helper('grants/link_grants');
            break;

         case CENUM_CONTEXT_BIZ:
//            $CI->load->helper('dl_util/email_web');
            $CI->load->model('admin/madmin_aco', 'clsACO');
            $CI->load->model('biz/mbiz',         'clsBiz');
            break;

         case CENUM_CONTEXT_GIFT:
            $CI->load->model('donations/mdonations', 'clsGifts');
            $CI->clsUFD->bGiftPerson = $CI->clsGifts->bGiftViaPerson($lForeignID);
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $CI->load->model('sponsorship/msponsorship', 'clsSpon');
            break;

         case CENUM_CONTEXT_USER:
         case CENUM_CONTEXT_STAFF:
            $CI->load->model('admin/muser_accts', 'clsUsers');
            break;

         case CENUM_CONTEXT_ORGANIZATION:
            $CI->load->model('admin/morganization', 'clsChapter');
            $CI->load->model('admin/madmin_aco',    'clsACO');
            break;

         case CENUM_CONTEXT_VOLUNTEER:
//            $CI->load->helper('dl_util/email_web');
            $CI->load->model('people/mpeople');
            $CI->load->model('vols/mvol', 'clsVol');
            break;

         case CENUM_CONTEXT_INVITEM:
            $CI->load->model  ('staff/inventory/minventory',   'cinv');
            $CI->load->model('admin/madmin_aco', 'clsACO');
            $CI->load->helper ('staff/link_inventory');
            break;

//         case CENUM_CONTEXT_LOCATION:
         default:
            screamForHelp($enumType.': feature not available yet<br>error on line <b>'.__LINE__.'</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function strContextHTML($enumType, $lForeignID, &$strContextName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $CI =& get_instance();
      switch ($enumType){
         case CENUM_CONTEXT_AUCTION:
            $CI->cAuction->loadAuctionByAucID($lForeignID);
            $strHTMLSummary = $CI->cAuction->strAuctionHTMLSummary();
            $strContextName = $CI->cAuction->auctions[0]->strAuctionName;
            break;

         case CENUM_CONTEXT_AUCTIONITEM:
            $CI->cItems->loadItemViaItemID($lForeignID);
            $strHTMLSummary = $CI->cItems->strItemHTMLSummary();
            $strContextName = $CI->cItems->items[0]->strItemName;
            $CI->lContextPackageID = $CI->cItems->items[0]->lPackageID;
            $CI->lContextAuctionID = $CI->cItems->items[0]->lAuctionID;
            break;

         case CENUM_CONTEXT_AUCTIONPACKAGE:
            $CI->cPackages->loadPackageByPacID($lForeignID);
            $strHTMLSummary = $CI->cPackages->packageHTMLSummary();
            $strContextName = $CI->cPackages->packages[0]->strPackageName;
            $CI->lContextAuctionID = $CI->cPackages->packages[0]->lAuctionID;
            break;

         case CENUM_CONTEXT_BIZ:
            $CI->clsBiz->loadBizRecsViaBID($lForeignID);
            $strHTMLSummary = $CI->clsBiz->strBizHTMLSummary();
            $strContextName = $CI->clsBiz->bizRecs[0]->strSafeName;
            break;

         case CENUM_CONTEXT_CLIENT:
         case CENUM_CONTEXT_CPROGRAM:
         case CENUM_CONTEXT_CPROGENROLL:
         case CENUM_CONTEXT_CPROGATTEND:
            $CI->clsClients->loadClientsViaClientID($lForeignID);
            $strHTMLSummary = $CI->clsClients->strClientHTMLSummary(0);
            $strContextName = $CI->clsClients->clients[0]->strFName.' '.$CI->clsClients->clients[0]->strLName;
            break;

         case CENUM_CONTEXT_GIFT:
            $CI->clsGifts->loadGiftViaGID($lForeignID);
            $strHTMLSummary = $CI->clsGifts->giftHTMLSummary();
            $strContextName = 'Donation by '.$CI->clsGifts->gifts[0]->strSafeName;
            break;

         case CENUM_CONTEXT_LOCATION:
            $CI->clsLoc->loadLocationRec($lForeignID);
            $strHTMLSummary = $CI->clsLoc->strClientLocationHTMLSummary(0);
            $strContextName = $CI->clsLoc->strLocation;
            break;

         case CENUM_CONTEXT_ORGANIZATION:
            $CI->clsChapter->lChapterID  = $lForeignID;
            $CI->clsChapter->loadChapterInfo();
            $strHTMLSummary = $CI->clsChapter->strChapterHTMLSummary();
            $strContextName = $CI->clsChapter->chapterRec->strSafeChapterName;
            break;

         case CENUM_CONTEXT_PEOPLE:
            $CI->clsPeople->loadPeopleViaPIDs($lForeignID, false, false);
            $strHTMLSummary = $CI->clsPeople->peopleHTMLSummary(0);
            $strContextName = $CI->clsPeople->people[0]->strFName.' '.$CI->clsPeople->people[0]->strLName;
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $CI->clsSpon->sponsorInfoViaID($lForeignID);
            $strHTMLSummary = $CI->clsSpon->sponsorshipHTMLSummary();
            $strContextName = 'Sponsor '.$CI->clsSpon->sponInfo[0]->strSponSafeNameFL;
            break;

         case CENUM_CONTEXT_USER:
         case CENUM_CONTEXT_STAFF:
            $CI->clsUsers->loadSingleUserRecord($lForeignID);
            $strHTMLSummary = $CI->clsUsers->userHTMLSummary(0);
            $strContextName = 'Staff Member '.$CI->clsUsers->userRec[0]->strSafeName;
            break;

         case CENUM_CONTEXT_VOLUNTEER:
            $CI->clsVol->loadVolRecsViaVolID($lForeignID, true);
            $strHTMLSummary = $CI->clsVol->volHTMLSummary(0);
            $strContextName = $CI->clsVol->volRecs[0]->strSafeNameFL;
            break;

         case CENUM_CONTEXT_GRANTPROVIDER:
            $CI->cgrants->loadGrantProviderViaGPID($lForeignID, $lNumProviders, $providers);
            $strHTMLSummary = $CI->cgrants->providerHTMLSummary($providers[0]);
            $strContextName = htmlspecialchars($providers[0]->strGrantOrg);
            break;

         case CENUM_CONTEXT_INVITEM:
            $CI->cinv->loadSingleInventoryItem($lForeignID, $lNumItems, $items);
            $lICatID = $items[0]->lCategoryID;

               // load the inventory category breadcrumbs
            $items[0]->strCatBreadCrumb = '';
            $CI->cinv->icatBreadCrumbs($items[0]->strCatBreadCrumb, $lICatID);
            $strHTMLSummary = $CI->cinv->strIItemHTMLSummary($items[0]);
            $strContextName = htmlspecialchars($items[0]->strItemName);
            break;

         default:
            screamForHelp($enumType.': feature not available yet<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($strHTMLSummary);
   }

   function contextNameLink($enumType, $lForeignID,
                            &$strContextName, &$strContextLink){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $CI =& get_instance();
      $strContextLink = str_pad($lForeignID, 5, '0', STR_PAD_LEFT);
      switch ($enumType){
         case CENUM_CONTEXT_PEOPLE:
            $CI->clsPeople->loadPeopleViaPIDs($lForeignID, false, false);
            $strContextName = $CI->clsPeople->people[0]->strFName.' '.$CI->clsPeople->people[0]->strLName;
            $strContextLink .= strLinkView_PeopleRecord($lForeignID, 'View people record', true);
            break;

         case CENUM_CONTEXT_CLIENT:
            $CI->clsClients->loadClientsViaClientID($lForeignID);
            $strContextName = $CI->clsClients->clients[0]->strFName.' '.$CI->clsClients->clients[0]->strLName;
            $strContextLink .= strLinkView_ClientRecord($lForeignID, 'View client record', true);
            break;

         case CENUM_CONTEXT_GIFT:
            $CI->clsGifts->loadGiftViaGID($lForeignID);
            $strContextName = 'Donation by '.$CI->clsGifts->gifts[0]->strSafeName;
            $strContextLink .= strLinkView_GiftsRecord($lForeignID, 'View gift record', true);
            break;

         case CENUM_CONTEXT_BIZ:
            $CI->clsBiz->loadBizRecsViaBID($lForeignID);
            $strContextName = $CI->clsBiz->bizRecs[0]->strSafeName;
            $strContextLink .= strLinkView_BizRecord($lForeignID, 'View business record', true);
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $CI->clsSpon->sponsorInfoViaID($lForeignID);
            $strContextName = 'Sponsor '.$CI->clsSpon->sponInfo[0]->strSponSafeNameFL;
            $strContextLink .= strLinkView_Sponsorship($lForeignID, 'View sponsorship record', true);
            break;

         case CENUM_CONTEXT_VOLUNTEER:
            $CI->clsVol->loadVolRecsViaVolID($lForeignID, true);
            $strContextName = $CI->clsVol->volRecs[0]->strSafeNameFL;
            $strContextLink .= strLinkView_Volunteer($lForeignID, 'View volunteer record', true);
            break;

         case CENUM_CONTEXT_LOCATION:
            $CI->clsLoc->loadLocationRec($lForeignID);
            $strContextName = $CI->clsLoc->strLocation;
            $strContextLink .= strLinkView_ClientLocation($lForeignID, 'View client location record', true);
            break;

         case CENUM_CONTEXT_ORGANIZATION:
            $CI->clsLoc->loadLocationRec($lForeignID);
            $strContextName = $CI->clsChapter->chapterRec->strSafeChapterName;
            $strContextLink .= strLinkView_OrganizationRecord($lForeignID, 'View your organization record', true);
            break;

         default:
            screamForHelp($enumType.': feature not available yet<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function redirectViaContextType($enumType, $lForeignID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumType){

         case CENUM_CONTEXT_AUCTION:         redirect_Auction          ($lForeignID);    break;
         case CENUM_CONTEXT_AUCTIONITEM:     redirect_AuctionItem      ($lForeignID);    break;
         case CENUM_CONTEXT_AUCTIONPACKAGE:  redirect_AuctionPackage   ($lForeignID);    break;
         case CENUM_CONTEXT_BIZ:             redirect_Biz              ($lForeignID);    break;
         case CENUM_CONTEXT_CLIENT:          redirect_Client           ($lForeignID);    break;
         case CENUM_CONTEXT_GIFT:            redirect_Gift             ($lForeignID);    break;
         case CENUM_CONTEXT_GRANTPROVIDER:   redirect_GrantProvider    ($lForeignID);    break;
         case CENUM_CONTEXT_PEOPLE:          redirect_People           ($lForeignID);    break;
         case CENUM_CONTEXT_SPONSORSHIP:     redirect_SponsorshipRecord($lForeignID);    break;
         case CENUM_CONTEXT_USER:            redirect_User             ($lForeignID);    break;
         case CENUM_CONTEXT_VOLUNTEER:       redirect_VolRec           ($lForeignID);    break;
         case CENUM_CONTEXT_LOCATION:        redirect_ClientLocRec     ($lForeignID);    break;
         case CENUM_CONTEXT_ORGANIZATION:    redirect_Organization     ($lForeignID);    break;
         case CENUM_CONTEXT_STAFF:           redirect_User             ($lForeignID);    break;
         case CENUM_CONTEXT_INVITEM:         redirect_InventoryItem    ($lForeignID);    break;

         case CENUM_CONTEXT_GENERIC:
         default:
            screamForHelp($enumType.': Switch type not implemented</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
            break;
      }
   }

   function breadCrumbsToRecViewViaContextType($enumType, $lForeignID, $strLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $CI =& get_instance();
      switch ($enumType){
         case CENUM_CONTEXT_AUCTION:
            $strOut    = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                          .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                          .' | '.anchor('auctions/auctions/viewAuctionRecord/'.$lForeignID, 'Auction', 'class="breadcrumb"')
                          .' | '.$strLabel;

            break;

         case CENUM_CONTEXT_AUCTIONITEM:
            $strOut    = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                          .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                          .' | '.anchor('auctions/auctions/viewAuctionRecord/'.$CI->lContextAuctionID, 'Auction', 'class="breadcrumb"')
                          .' | '.anchor('auctions/packages/viewPackageRecord/'.$CI->lContextPackageID, 'Auction Package', 'class="breadcrumb"')
                          .' | '.anchor('auctions/items/viewItemRecord/'.$lForeignID, 'Auction Item', 'class="breadcrumb"')
                          .' | '.$strLabel;

            break;

         case CENUM_CONTEXT_AUCTIONPACKAGE:
            $strOut    = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                          .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                          .' | '.anchor('auctions/auctions/viewAuctionRecord/'.$CI->lContextAuctionID, 'Auction', 'class="breadcrumb"')
                          .' | '.anchor('auctions/packages/viewPackageRecord/'.$lForeignID, 'Auction Package', 'class="breadcrumb"')
                          .' | '.$strLabel;

            break;

         case CENUM_CONTEXT_BIZ:
            $strOut = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
               .' | '.anchor('biz/biz_record/view/'.$lForeignID, 'Record', 'class="breadcrumb"')
               .' | '.$strLabel;
            break;

         case CENUM_CONTEXT_CLIENT:
            $strOut = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
               .' | '.anchor('clients/client_record/view/'.$lForeignID, 'Client Record', 'class="breadcrumb"')
               .' | '.$strLabel;
            break;

         case CENUM_CONTEXT_LOCATION:
            $strOut =
                      anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
               .' | '.anchor('admin/alists/showLists',                    'Lists',            'class="breadcrumb"')
               .' | '.anchor('admin/admin_special_lists/clients/locationView',    'Client Locations', 'class="breadcrumb"')
               .' | '.anchor('clients/locations/view/'.$lForeignID, 'Location Record',  'class="breadcrumb"')
               .' | '.$strLabel;
            break;

         case CENUM_CONTEXT_ORGANIZATION:
            $strOut =
                      anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
               .' | '.anchor('admin/org/orgView/'.$lForeignID, 'Your Organization',  'class="breadcrumb"')
               .' | '.$strLabel;
            break;

         case CENUM_CONTEXT_PEOPLE:
            $strOut = anchor('main/menu/people', 'People', 'class="breadcrumb"')
               .' | '.anchor('people/people_record/view/'.$lForeignID, 'Record', 'class="breadcrumb"')
               .' | '.$strLabel;
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $strOut = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
               .' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$lForeignID, 'Record', 'class="breadcrumb"')
               .' | '.$strLabel;
            break;

         case CENUM_CONTEXT_STAFF:
            $strOut = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
               .' | '.anchor('admin/accts/view/'.$lForeignID, 'View Account', 'class="breadcrumb"')
               .' | '.$strLabel;
            break;

         case CENUM_CONTEXT_VOLUNTEER:
            $strOut = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
               .' | '.anchor('volunteers/vol_record/volRecordView/'.$lForeignID, 'Record', 'class="breadcrumb"')
               .' | '.$strLabel;
            break;

         case CENUM_CONTEXT_GRANTPROVIDER:
            $strOut = anchor('main/menu/financials', 'Financials/Grants', 'class="breadcrumb"')
               .' | '.anchor('grants/provider_record/viewProvider/'.$lForeignID, 'Grant Provider Record', 'class="breadcrumb"')
               .' | '.$strLabel;
            break;

         case CENUM_CONTEXT_INVITEM:
            $strOut = anchor('main/menu/more', 'More', 'class="breadcrumb"')
               .' | '.anchor('staff/inventory/icat/viewICats', 'Inventory Categories', 'class="breadcrumb"')
               .' | '.anchor('staff/inventory/inventory_items/iitemRec/'.$lForeignID, 'Inventory Item', 'class="breadcrumb"')
               .' | '.$strLabel;
            break;


         case CENUM_CONTEXT_GIFT:
         case CENUM_CONTEXT_USER:
         case CENUM_CONTEXT_LOCATION:
         case CENUM_CONTEXT_GENERIC:
         default:
            screamForHelp($enumType.': Switch type not implemented</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
            break;
      }
      return($strOut);
   }

   function strLabelViaContextType($enumType, $bCapLetter, $bPlural){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumType){

         case CENUM_CONTEXT_AUCTION:
            $strOut = ($bPlural ? ($bCapLetter ? 'Auctions' : 'auctions') : ($bCapLetter ? 'Auction' : 'auction'));
            break;

         case CENUM_CONTEXT_AUCTIONITEM:
            $strOut = ($bPlural ? ($bCapLetter ? 'Auction Items' : 'auction items') : ($bCapLetter ? 'Auction Item' : 'auction item'));
            break;

         case CENUM_CONTEXT_AUCTIONPACKAGE:
            $strOut = ($bPlural ? ($bCapLetter ? 'Auction Packages' : 'auction packages') : ($bCapLetter ? 'Auction Package' : 'auction package'));
            break;

         case CENUM_CONTEXT_BIZ:
            $strOut = ($bPlural ? ($bCapLetter ? 'Businesses/Organizations' : 'businesses/organizations') : ($bCapLetter ? 'Business/Organization' : 'business/organization'));
            break;

         case CENUM_CONTEXT_CLIENT:
            $strOut = ($bPlural ? ($bCapLetter ? 'Clients' : 'clients') : ($bCapLetter ? 'Client' : 'client'));
            break;

         case CENUM_CONTEXT_GIFT:
            $strOut = ($bPlural ? ($bCapLetter ? 'Gifts' : 'gifts') : ($bCapLetter ? 'Gift' : 'gift'));
            break;

         case CENUM_CONTEXT_GRANTPROVIDER:
            $strOut = ($bPlural ? ($bCapLetter ? 'Grant Providers' : 'grant providers') : ($bCapLetter ? 'Grant Provider' : 'grant provider'));
            break;

         case CENUM_CONTEXT_ORGANIZATION:
            $strOut = ($bPlural ? ($bCapLetter ? 'Organizations' : 'organizations') : ($bCapLetter ? 'Organization' : 'organization'));
            break;

         case CENUM_CONTEXT_PEOPLE:
            $strOut = ($bPlural ? ($bCapLetter ? 'People' : 'people') : ($bCapLetter ? 'Person' : 'person'));
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $strOut = ($bPlural ? ($bCapLetter ? 'Sponsorships' : 'sponsorships') : ($bCapLetter ? 'Sponsorship' : 'sponsorship'));
            break;

         case CENUM_CONTEXT_STAFF:
            $strOut = ($bPlural ? ($bCapLetter ? 'Staff' : 'staff') : ($bCapLetter ? 'Staff' : 'staff'));
            break;

         case CENUM_CONTEXT_USER:
            $strOut = ($bPlural ? ($bCapLetter ? 'Users' : 'users') : ($bCapLetter ? 'User' : 'user'));
            break;

         case CENUM_CONTEXT_VOLUNTEER:
            $strOut = ($bPlural ? ($bCapLetter ? 'Volunteers' : 'volunteers') : ($bCapLetter ? 'Volunteer' : 'volunteer'));
            break;

         case CENUM_CONTEXT_LOCATION:
            $strOut = ($bPlural ? ($bCapLetter ? 'Locations' : 'locations') : ($bCapLetter ? 'Location' : 'location'));
            break;

         case CENUM_CONTEXT_INVITEM:
            $strOut = ($bPlural ? ($bCapLetter ? 'Inventory Items' : 'inventory items') : ($bCapLetter ? 'Inventory Item' : 'inventory item'));
            break;

         case CENUM_CONTEXT_GENERIC:
            $strOut = ($bPlural ? ($bCapLetter ? '(Other)' : '(other)') : ($bCapLetter ? '(Other)' : '(other)'));
            break;

         default:
            screamForHelp($enumType.': Switch type not implemented</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
            break;
      }
      return($strOut);
   }

   function contextLabels($enumType, &$labels){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $labels = new stdClass;
      $labels->strType    = $enumType;
      $labels->strTypeP   = strLabelViaContextType($enumType, false, true);
      $labels->strTypeC   = strLabelViaContextType($enumType, true, false);
      $labels->strTypePC  = strLabelViaContextType($enumType, true, true);
      $labels->strTypeUC  = strtoupper($enumType);
      $labels->strTypeUCP = strtoupper($labels->strTypeP);
   }





