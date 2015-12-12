<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class auctions extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function auctionEvents(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      
      $displayData = array();
      $displayData['js'] = '';

         //-----------------------------
         // models and helpers
         //-----------------------------
      $this->load->model('auctions/mauctions', 'cAuction');
      $this->load->model('auctions/mpackages', 'cPackages');
      $this->load->model('auctions/mitems',    'cItems');
      $this->load->model('img_docs/mimage_doc',    'clsImgDoc');
      $this->load->model('admin/madmin_aco',   'clsACO');
      $this->load->helper('dl_util/link_auction');
      $this->load->helper('auctions/auction');

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $this->cAuction->loadAuctions();

      $displayData['lNumAuctions'] = $lNumAuctions = $this->cAuction->lNumAuctions;

      if ($this->cAuction->lNumAuctions > 0){
         foreach ($this->cAuction->auctions as $auction){
            $lAuctionID = $auction->lKeyID;
            $auction->lNumPackages = $this->cPackages->lCountPackagesViaAID($lAuctionID);
            $auction->lNumItems    = $this->cItems->lCountItemsViaAID($lAuctionID);
            $auction->curEstValue  = $this->cItems->curEstValueViaAID($lAuctionID);
            $auction->curOOP       = $this->cItems->curOutOfPocketViaAID($lAuctionID);
            $auction->curIncome    = $this->cItems->curIncomeViaAID($lAuctionID);
         }
      }
      $displayData['auctions']     = &$this->cAuction->auctions;

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Silent Auctions';
      $displayData['pageTitle']    = GSTR_AUCTIONTOPLEVEL.' | Silent Auctions';

      $displayData['mainTemplate'] = 'auctions/auction_list_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function viewAuctionRecord($lAuctionID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lAuctionID, 'auction ID');

      $displayData = array();
      $displayData['lAuctionID'] = $lAuctionID = (integer)$lAuctionID;
      $displayData['js'] = '';

      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

         //-----------------------------
         // models and helpers
         //-----------------------------
      $this->load->helper ('img_docs/img_doc_tags');
      $this->load->model  ('auctions/mauctions',     'cAuction');
      $this->load->model  ('auctions/mpackages',     'cPackages');
      $this->load->model  ('auctions/mitems',        'cItems');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $this->load->model  ('img_docs/mimage_doc',    'clsImgDoc');
      $this->load->model  ('img_docs/mimg_doc_tags', 'cidTags');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('dl_util/link_auction');
      $this->load->helper ('auctions/auction');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('img_docs/link_img_docs');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //-------------------------------
         // images and documents
         //-------------------------------
      loadImgDocRecView($displayData, CENUM_CONTEXT_AUCTION, $lAuctionID);

      $this->cAuction->loadAuctionByAucID($lAuctionID);
      $auction    = &$this->cAuction->auctions[0];;
      $lAuctionID = $auction->lKeyID;
      $auction->lNumPackages    = $this->cPackages->lCountPackagesViaAID($lAuctionID);
      $auction->lNumItems       = $this->cItems->lCountItemsViaAID($lAuctionID);
      $auction->curEstValue     = $this->cItems->curEstValueViaAID($lAuctionID);
      $auction->curOutOfPocket  = $this->cItems->curOutOfPocketViaAID($lAuctionID);
      $auction->curIncome       = $this->cItems->curIncomeViaAID($lAuctionID);
      $auction->lNumWinningBids = $this->cItems->lNumWinningBidsViaAID($lAuctionID);
      $auction->lNumUnfulfilled = $this->cItems->lNumUnfulfilledViaAID($lAuctionID);
      $auction->curAmntWinBids  = $this->cItems->curWinningBidAmntViaAID($lAuctionID);

      $displayData['auction']   = &$this->cAuction->auctions[0];

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']    = GSTR_AUCTIONTOPLEVEL
                                  .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                                  .' | Auction Record';

      $displayData['title']          = CS_PROGNAME.' | Silent Auctions';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'auctions/auction_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }   
   
   function viewAuctionOverview($lAuctionID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lAuctionID, 'auction ID');

      $displayData = array();
      $displayData['lAuctionID'] = $lAuctionID = (integer)$lAuctionID;
      $displayData['js'] = '';
      

         //-----------------------------
         // models and helpers
         //-----------------------------
      $this->load->model  ('auctions/mauctions', 'cAuction');
      $this->load->model  ('auctions/mpackages', 'cPackages');
      $this->load->model  ('auctions/mitems',    'cItems');
      $this->load->model  ('admin/madmin_aco',   'clsACO');
      $this->load->model  ('people/mpeople',     'clsPeople');
      $this->load->model  ('img_docs/mimage_doc',    'clsImgDoc');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('dl_util/link_auction');
      $this->load->helper ('auctions/auction');
      $this->load->helper ('dl_util/web_layout');
      
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      
      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();
      
         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;
      

         // load auction
      $this->cAuction->loadAuctionByAucID($lAuctionID);
      $displayData['auction'] = &$this->cAuction->auctions[0];
      
         // load packages
      $this->cPackages->loadPackageByAID($lAuctionID);
      $displayData['packages']     = $packages = &$this->cPackages->packages;
      $displayData['lNumPackages'] = $lNumPackages = $this->cPackages->lNumPackages;
      
         // load items
      if ($lNumPackages > 0){
         foreach ($packages as $package){
            $lPackageID = $package->lKeyID;
            $this->cItems->loadItemsViaPackageID($lPackageID);
            $package->lNumItems = $lNumItems = $this->cItems->lNumItems;
            
            $package->curEstValue  = $this->cItems->curEstValueViaPID($lPackageID);
            if (!is_null($package->lBidWinnerID)){
               $this->clsPeople->peopleBizInfoViaPID($package->lBidWinnerID, $pbInfo);
               $package->bidWinner = clone($pbInfo);
            }            
            
            if ($lNumItems > 0){
               $package->items = array();
               
               foreach ($this->cItems->items as $item){
                  $package->items[] = clone($item);
               }
            }
         }
      }
      
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$displayData   <pre>');
echo(htmlspecialchars( print_r($displayData, true))); echo('</pre></font><br>');
// -------------------------------------*/

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']    = GSTR_AUCTIONTOPLEVEL
                                  .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                                  .' | Auction Overview';

      $displayData['title']          = CS_PROGNAME.' | Silent Auctions';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'auctions/auction_overview_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }   

}
