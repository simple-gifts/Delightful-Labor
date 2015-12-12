<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class packages extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEditAuctionPackage($lAuctionID, $lPackageID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;
      if (!bTestForURLHack('showAuctions')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lAuctionID, 'auction ID');
      if ($lPackageID.'' != '0') verifyID($this, $lPackageID, 'package ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['formData'] = new stdClass;
      $displayData['lAuctionID'] = $lAuctionID = (integer)$lAuctionID;
      $displayData['lPackageID'] = $lPackageID = (integer)$lPackageID;
      $displayData['bNew'] = $bNew = $lPackageID <= 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper ('dl_util/time_date');
      $this->load->model  ('auctions/mauctions',     'cAuction');
      $this->load->model  ('auctions/mpackages',     'cPackages');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $this->load->model  ('img_docs/mimage_doc',        'clsImgDoc');
      $this->load->model  ('donations/maccts_camps', 'clsAC');
      $this->load->model  ('auctions/mbid_sheets',   'cBidSheets');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('dl_util/link_auction');
      $this->load->helper ('auctions/auction');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('generic_form');

         //-----------------------------------------
         // load package and associated auction
         //-----------------------------------------
      $this->cAuction->loadAuctionByAucID($lAuctionID);
      $displayData['auction'] = $auction = &$this->cAuction->auctions[0];
      $displayData['contextSummary'] = $this->cAuction->strAuctionHTMLSummary();

      $this->cPackages->loadPackageByPacID($lPackageID);
      $package = $this->cPackages->packages[0];

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtPackageName',  'Package Name',       'trim|required|'
                                   .'callback_verifyUniquePackage['.$lAuctionID.','.$lPackageID.']');
		$this->form_validation->set_rules('txtPublicNotes',  'Public Notes',       'trim');
		$this->form_validation->set_rules('txtPrivateNotes', 'Private Notes',      'trim');

		$this->form_validation->set_rules('txtMinBid',       'Minimum Bid',           'trim|required|callback_stripCommas|numeric|callback_minPackageAmnt[0.01]');
		$this->form_validation->set_rules('txtMinBidInc',    'Minimum Bid Increment', 'trim|required|callback_stripCommas|numeric|callback_minPackageAmnt[0.01]');
		$this->form_validation->set_rules('txtBuyItNowAmnt', '"Buy It Now" amount',   'trim|callback_stripCommas|numeric|callback_minPackageAmnt[0.00]');
		$this->form_validation->set_rules('txtReserve',      'Reserve Amount',        'trim|required|callback_stripCommas|numeric|callback_minPackageAmnt[0.00]');
		$this->form_validation->set_rules('ddlBS',           'Bid Sheet',  'trim');


		if ($this->form_validation->run() == FALSE){

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($bNew){
               $displayData['ddlBidSheet'] = $this->cBidSheets->strBidSheetListDDL($lAuctionID, $auction->lBidsheetID,
                                                   'ddlBS', true, $displayData['lNumBidSheets']);
            }else {
               $displayData['ddlBidSheet'] = $this->cBidSheets->strBidSheetListDDL($lAuctionID, $package->lBidSheetID,
                                                   'ddlBS', true, $displayData['lNumBidSheets']);
            }
            $displayData['formData']->txtPackageName  = htmlspecialchars($package->strPackageName);
            $displayData['formData']->txtPublicNotes  = htmlspecialchars($package->strDescription);
            $displayData['formData']->txtPrivateNotes = htmlspecialchars($package->strInternalNotes);
            $displayData['formData']->txtMinBid       = number_format($package->curMinBidAmnt,   2);
            $displayData['formData']->txtMinBidInc    = number_format($package->curMinBidInc,    2);
            $displayData['formData']->txtBuyItNowAmnt = number_format($package->curBuyItNowAmnt, 2);
            $displayData['formData']->txtReserve      = number_format($package->curReserveAmnt,  2);
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtPackageName  = set_value('txtPackageName');
            $displayData['formData']->txtPublicNotes  = set_value('txtPublicNotes');
            $displayData['formData']->txtPrivateNotes = set_value('txtPrivateNotes');
            $displayData['formData']->txtMinBid       = set_value('txtMinBid');
            $displayData['formData']->txtMinBidInc    = set_value('txtMinBidInc');
            $displayData['formData']->txtBuyItNowAmnt = set_value('txtBuyItNowAmnt');
            $displayData['formData']->txtReserve      = set_value('txtReserve');
            $displayData['ddlBidSheet']  = $this->cBidSheets->strBidSheetListDDL($lAuctionID, set_value('ddlBS'),
                                                   'ddlBS', true, $displayData['lNumBidSheets']);
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = GSTR_AUCTIONTOPLEVEL
                                   .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                                   .' | '.anchor('auctions/auctions/viewAuctionRecord/'.$lAuctionID, 'Auction', 'class="breadcrumb"')
                                   .' | '.anchor('auctions/packages/viewPackagesViaAID/'.$lAuctionID, 'Auction Packages', 'class="breadcrumb"');
         if (!$bNew) $displayData['pageTitle'] .=
                                    ' | '.anchor('auctions/packages/viewPackageRecord/'.$lPackageID, 'Package Record', 'class="breadcrumb"');
         $displayData['pageTitle'] .=
                                    ' | '.($bNew ? 'Add New' : 'Edit').'  Package';

         $displayData['title']          = CS_PROGNAME.' | Silent Auctions';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'auctions/add_edit_package_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $package->lAuctionID        = $lAuctionID;
         $package->strPackageName    = trim($_POST['txtPackageName']);
         $package->strDescription    = trim($_POST['txtPublicNotes']);
         $package->strInternalNotes  = trim($_POST['txtPrivateNotes']);
         $package->curMinBidAmnt     = (float)(trim($_POST['txtMinBid']));
         $package->curMinBidInc      = (float)(trim($_POST['txtMinBidInc']));
         $package->curBuyItNowAmnt   = (float)(trim($_POST['txtBuyItNowAmnt']));
         $package->curReserveAmnt    = (float)(trim($_POST['txtReserve']));
         $package->lBidSheetID       = (int) trim(@$_POST['ddlBS']);
         if ($package->lBidSheetID <= 0) $package->lBidSheetID = null;

            //------------------------------------
            // update db tables and return
            //------------------------------------
         if ($bNew){
            $lPackageID = $this->cPackages->addNewPackage();
            $this->session->set_flashdata('msg', 'Package record added');
         }else {
            $this->cPackages->updatePackage($lPackageID);
            $this->session->set_flashdata('msg', 'Package record updated');
         }
         redirect('auctions/packages/viewPackageRecord/'.$lPackageID);
      }
   }

   function stripCommas(&$strAmount){
      $strAmount = str_replace (',', '', $strAmount);
      return(true);
   }

   function minPackageAmnt($strMinBid, $curMin){
      return((float)($strMinBid) >= $curMin);
   }

   function verifyUniquePackage($strPackageName, $strIDs){
      $IDs = explode(',', $strIDs);
      $lAuctionID = (int)$IDs[0];
      $lPackageID = (int)$IDs[1];
      $this->load->model('util/mverify_unique', 'clsUnique');
      
      if (!$this->clsUnique->bVerifyUniqueText(
                trim($strPackageName), 'ap_strPackageName',
                $lPackageID,           'ap_lKeyID',
                true,                  'ap_bRetired',
                true, $lAuctionID,     'ap_lAuctionID',
                false, null, null,
                'gifts_auctions_packages')){
         $this->form_validation->set_message('verifyUniquePackage',
                   'The Package Name is already used in this auction.');

         return(false);
      }else {
         return(true);
      }
   }

   function viewPackageRecord($lPackageID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPackageID, 'package ID');

      $displayData = array();
      $displayData['js'] = '';

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('img_docs/img_doc_tags');
      $this->load->model  ('auctions/mauctions',     'cAuction');
      $this->load->model  ('auctions/mpackages',     'cPackages');
      $this->load->model  ('auctions/mitems',        'cItems');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $this->load->model  ('img_docs/mimage_doc',    'clsImgDoc');
      $this->load->model  ('img_docs/mimg_doc_tags', 'cidTags');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('dl_util/link_auction');
      $this->load->helper ('auctions/auction');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('img_docs/link_img_docs');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //-----------------------------------------
         // load package and associated auction
         //-----------------------------------------
      $this->cPackages->loadPackageByPacID($lPackageID);
      $displayData['package'] = $package = $this->cPackages->packages[0];
      $this->cPackages->loadPackageProfileImage();

      $displayData['lAuctionID'] = $lAuctionID = $package->lAuctionID;
      $this->cAuction->loadAuctionByAucID($lAuctionID);
      $displayData['auction'] = $auction = &$this->cAuction->auctions[0];
      $displayData['contextSummary'] = $this->cAuction->strAuctionHTMLSummary();

      $package->lNumItems      = $this->cItems->lCountItemsViaPID   ($lPackageID);
      $package->curEstValue    = $this->cItems->curEstValueViaPID   ($lPackageID);
      $package->curOutOfPocket = $this->cItems->curOutOfPocketViaPID($lPackageID);

      $this->cItems->loadItemsViaPackageID($lPackageID);
      $displayData['lNumItems'] = $this->cItems->lNumItems;
      $displayData['items']     = &$this->cItems->items;

      if (!is_null($package->lBidWinnerID)){
         $this->load->model('people/mpeople', 'clsPeople');
         $this->clsPeople->peopleBizInfoViaPID($package->lBidWinnerID, $displayData['pbInfo']);
      }

         //-------------------------------
         // images and documents
         //-------------------------------
      loadImgDocRecView($displayData, CENUM_CONTEXT_AUCTIONPACKAGE, $lPackageID);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']  = GSTR_AUCTIONTOPLEVEL
                             .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                             .' | '.anchor('auctions/auctions/viewAuctionRecord/'.$lAuctionID, 'Auction', 'class="breadcrumb"')
                             .' | '.anchor('auctions/packages/viewPackagesViaAID/'.$lAuctionID, 'Auction Packages', 'class="breadcrumb"')
                             .' | Package Record';

      $displayData['title']          = CS_PROGNAME.' | Silent Auctions';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'auctions/package_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function viewPackagesViaAID($lAuctionID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lAuctionID, 'auction ID');

      $displayData = array();
      $displayData['js'] = '';
         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('auctions/mauctions',     'cAuction');
      $this->load->model  ('auctions/mpackages',     'cPackages');
      $this->load->model  ('auctions/mitems',        'cItems');
      $this->load->model  ('img_docs/mimage_doc',        'clsImgDoc');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $this->load->helper ('auctions/auction');
      $this->load->helper ('dl_util/link_auction');
      $this->load->model('people/mpeople', 'clsPeople');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $this->cAuction->loadAuctionByAucID($lAuctionID);
      $displayData['auction'] = $auction = &$this->cAuction->auctions[0];
      $displayData['contextSummary'] = $this->cAuction->strAuctionHTMLSummary();
      $displayData['lAuctionID']     = $lAuctionID;

      $this->cPackages->loadPackageByAID($lAuctionID);
      $displayData['packages'] = &$this->cPackages->packages;
      $displayData['lNumPackages'] = $lNumPackages = $this->cPackages->lNumPackages;

      if ($lNumPackages > 0){
         foreach ($this->cPackages->packages as $package){
            $lPackageID = $package->lKeyID;
            $package->lNumItems    = $this->cItems->lCountItemsViaPID($lPackageID);
            $package->curEstValue  = $this->cItems->curEstValueViaPID($lPackageID);
            if (!is_null($package->lBidWinnerID)){
               $this->clsPeople->peopleBizInfoViaPID($package->lBidWinnerID, $pbInfo);
//               $package->strBidWinner = $pbInfo->strSafeNameFL;
               $package->bidWinner = clone($pbInfo);
            }
         }
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
         $displayData['pageTitle'] = GSTR_AUCTIONTOPLEVEL
                                .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                                .' | '.anchor('auctions/auctions/viewAuctionRecord/'.$lAuctionID, 'Auction', 'class="breadcrumb"')
                                .' | Auction Packages';

      $displayData['title']          = CS_PROGNAME.' | Silent Auctions';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'auctions/auctions_packages_view';
      $this->load->vars($displayData);
      $this->load->view('template');

   }

   function setWinner1($lPackageID){
   //---------------------------------------------------------------------
   // select item donor
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPackageID, 'package ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lPackageID'] = (int)$lPackageID;

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
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('js_build/ajax_support');
      $this->load->helper ('dl_util/web_layout');

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('ddlNames',  'Package Winner', 'trim|callback_checkDDLSel');

      setPackageContext($lPackageID, $lAuctionID, $displayData);

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;

            //-------------------------------
            // people/biz ajax interface
            //-------------------------------
         $clsAjax = new ajax_support;
         $displayData['js'] .= $clsAjax->showCreateXmlHTTPObject();
         $displayData['js'] .= $clsAjax->peopleBizNames('showResult', 'selNames');

         $displayData['js'] .= $clsAjax->strPopulateTextFromDDL('selNames', 'winnerName');

         $this->load->library('generic_form');

         if (validation_errors()==''){
         }else {
            setOnFormError($displayData);
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle'] = GSTR_AUCTIONTOPLEVEL
                                   .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                                   .' | '.anchor('auctions/auctions/viewAuctionRecord/'.$lAuctionID, 'Auction', 'class="breadcrumb"')
                                   .' | '.anchor('auctions/packages/viewPackageRecord/'.$lPackageID, 'Auction Package', 'class="breadcrumb"')
                                   .' | Winning Bidder';

         $displayData['title']          = CS_PROGNAME.' | Silent Auction';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'auctions/add_item_sel_winner_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $lDonorID = (integer)$_POST['ddlNames'];
         redirect('auctions/packages/addEditPackageWinner/'.$lPackageID.'/'.$lDonorID);
      }
   }

   function checkDDLSel($strValue){
      if (($strValue.'' == '' ) || ((int)$strValue <= 0)){
         $this->form_validation->set_message('checkDDLSel',
                   'Please enter the first few letters of the auction item provider\'s last name (or business name), '
                  .'then select the entry from the drop-down list.');
         return(false);
      }else {
         return(true);
      }
   }

   function addEditPackageWinner($lPackageID, $lDonorID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPackageID, 'package ID');
      verifyID($this, $lDonorID,   'people/business ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lPackageID'] = (int)$lPackageID;
      $displayData['lDonorID']   = (int)$lDonorID;

         //-----------------------------
         // models and helpers
         //-----------------------------
      $this->load->model  ('auctions/mauctions', 'cAuction');
      $this->load->model  ('auctions/mpackages', 'cPackages');
      $this->load->model  ('auctions/mitems',    'cItems');
      $this->load->model  ('img_docs/mimage_doc',    'clsImgDoc');
      $this->load->model  ('admin/madmin_aco',   'clsACO');
      $this->load->helper ('dl_util/link_auction');
      $this->load->helper ('auctions/auction');
      $this->load->helper ('auctions/auction');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('js_build/ajax_support');
      $this->load->helper ('dl_util/web_layout');
      $this->load->model('people/mpeople', 'clsPeople');

      $this->clsPeople->peopleBizInfoViaPID($lDonorID, $displayData['pbInfo']);

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtAmount',  'Winning Bid Amount', 'trim|required|callback_stripCommas|numeric|callback_minPackageAmnt[0.01]');

      setPackageContext($lPackageID, $lAuctionID, $displayData);
      $package = &$displayData['package'];
      $bNew = is_null($package->lBidWinnerID);

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;

            //-------------------------------
            // people/biz ajax interface
            //-------------------------------
         $clsAjax = new ajax_support;

         $this->load->library('generic_form');

         if (validation_errors()==''){
            if ($bNew){
               $displayData['formData']->txtAmount = '0.00';
            }else {
               $displayData['formData']->txtAmount = number_format($package->curWinBidAmnt, 2);
            }
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtAmount = set_value('txtAmount');
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle'] = GSTR_AUCTIONTOPLEVEL
                                   .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                                   .' | '.anchor('auctions/auctions/viewAuctionRecord/'.$lAuctionID, 'Auction', 'class="breadcrumb"')
                                   .' | '.anchor('auctions/packages/viewPackageRecord/'.$lPackageID, 'Auction Package', 'class="breadcrumb"')
                                   .' | Winning Bidder';

         $displayData['title']          = CS_PROGNAME.' | Silent Auctions';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'auctions/add_item_winner_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $curWinBid = (float)$_POST['txtAmount'];
         $this->cPackages->updateWinBidAmount($lPackageID, $lDonorID, $curWinBid);
         redirect_AuctionPackageList($lAuctionID);
      }
   }

   function removeBid($lPackageID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      $this->removeBidFulfillment($lPackageID, false);
   }

   function removeFulfillment($lPackageID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      $this->removeBidFulfillment($lPackageID, true);
   }

   function removeBidFulfillment($lPackageID, $bFulfillmentOnly){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPackageID, 'package ID');

      $this->load->model ('auctions/mpackages', 'cPackages');
      $this->load->model ('img_docs/mimage_doc',    'clsImgDoc');
      $this->load->helper('dl_util/link_auction');
      $this->load->helper('auctions/auction');

      $this->cPackages->loadPackageByPacID($lPackageID);
      $package = &$this->cPackages->packages[0];

         // if associated with a donation, remove gift record
      if (!is_null($package->lGiftID)){
         $this->load->model('donations/mdonations', 'clsGifts');
         $this->load->model('util/mrecycle_bin',    'clsRecycle');
         $this->load->model('personalization/muser_fields', 'clsUF');
         $this->clsGifts->retireSingleGift($package->lGiftID, null);
      }
      if (!$bFulfillmentOnly){
         $this->cPackages->removeBid($lPackageID);
      }
      redirect_AuctionPackage($lPackageID);
   }

   function setFulfill($lPackageID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      if (!bTestForURLHack('showAuctions')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPackageID, 'package ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['formData'] = new stdClass;
      $displayData['lPackageID'] = $lPackageID = (integer)$lPackageID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper ('dl_util/time_date');
      $this->load->model  ('auctions/mauctions',     'cAuction');
      $this->load->model  ('auctions/mpackages',     'cPackages');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $this->load->model  ('img_docs/mimage_doc',        'clsImgDoc');
      $this->load->model  ('donations/maccts_camps', 'clsAC');
      $this->load->model  ('util/mlist_generic',     'clsList');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('dl_util/link_auction');
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('auctions/auction');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('generic_form');

      setPackageContext($lPackageID, $lAuctionID, $displayData);
      $package = &$displayData['package'];

      $displayData['js'] .= strDatePicker('datepickerFuture', true);

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtAmount',   'Amount',              'trim|required|callback_stripCommas|numeric|callback_minPackageAmnt[0.00]');
		$this->form_validation->set_rules('txtCheckNum', 'Check Number',        'trim');
		$this->form_validation->set_rules('ddlPayType',  'Payment Type',        'trim|callback_bidVerifyPayType');

		$this->form_validation->set_rules('ddlGiftCat',  'Gift Category',       'trim');
		$this->form_validation->set_rules('ddlAttrib',   'Attributed To',       'trim');
      $this->form_validation->set_rules('txtDDate',    'Fulfillment date',    'trim|required|callback_bidVerifyFDateValid');
      $this->form_validation->set_rules('txtNotes',    'Notes',               'trim');

		if ($this->form_validation->run() == FALSE){

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['formData']->txtAmount   = number_format($package->curWinBidAmnt, 2);
            $displayData['formData']->txtCheckNum = '';
            $displayData['formData']->txtDDate    = '';
            $displayData['formData']->txtNotes    = '';

            $this->clsList->strBlankDDLName = '&nbsp;';
            $this->clsList->enumListType = CENUM_LISTTYPE_GIFTPAYTYPE;
            $displayData['formData']->strDDLPayType     = $this->clsList->strLoadListDDL('ddlPayType', true, -1);
            $this->clsList->enumListType = CENUM_LISTTYPE_MAJORGIFTCAT;
            $displayData['formData']->strDDLMajGiftType = $this->clsList->strLoadListDDL('ddlGiftCat', true, -1);
            $this->clsList->enumListType = CENUM_LISTTYPE_ATTRIB;
            $displayData['formData']->strDDLAttrib      = $this->clsList->strLoadListDDL('ddlAttrib',  true, -1);

         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtAmount   = set_value('txtAmount');
            $displayData['formData']->txtCheckNum = set_value('txtCheckNum');
            $displayData['formData']->txtDDate    = set_value('txtDDate');
            $displayData['formData']->txtNotes    = set_value('txtNotes');

            $this->clsList->strBlankDDLName = '&nbsp;';
            $this->clsList->enumListType = CENUM_LISTTYPE_GIFTPAYTYPE;
            $displayData['formData']->strDDLPayType     = $this->clsList->strLoadListDDL('ddlPayType', true, set_value('ddlPayType'));
            $this->clsList->enumListType = CENUM_LISTTYPE_MAJORGIFTCAT;
            $displayData['formData']->strDDLMajGiftType = $this->clsList->strLoadListDDL('ddlGiftCat', true, set_value('ddlGiftCat'));
            $this->clsList->enumListType = CENUM_LISTTYPE_ATTRIB;
            $displayData['formData']->strDDLAttrib      = $this->clsList->strLoadListDDL('ddlAttrib',  true, set_value('ddlAttrib'));
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle'] = GSTR_AUCTIONTOPLEVEL
                                   .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                                   .' | '.anchor('auctions/auctions/viewAuctionRecord/'.$lAuctionID, 'Auction', 'class="breadcrumb"')
                                   .' | '.anchor('auctions/packages/viewPackageRecord/'.$lPackageID, 'Auction Package', 'class="breadcrumb"')
                                   .' | Fulfillment';

         $displayData['title']          = CS_PROGNAME.' | Silent Auctions';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'auctions/package_fulfillment_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $auction = &$displayData['auction'];
         $this->load->model ('personalization/muser_fields',        'clsUF');
         $this->load->model ('personalization/muser_fields_create', 'clsUFC');
         $this->load->model ('admin/mpermissions',                  'perms');
         $this->load->model ('donations/mdonations',                'clsGift');
         $this->load->helper('dl_util/util_db');

         $this->clsGift->loadGiftViaGID(-1);
         $gifts = &$this->clsGift->gifts[0];

         $gifts->gi_curAmnt       = trim((float)$_POST['txtAmount']);
         $gifts->strNotes         = trim($_POST['txtNotes']);
         $gifts->gi_strCheckNum   = trim($_POST['txtCheckNum']);
         $gifts->gi_lAttributedTo = trim((integer)$_POST['ddlAttrib']);
         $gifts->gi_lPaymentType  = trim((integer)$_POST['ddlPayType']);
         $gifts->gi_lMajorGiftCat = trim((integer)$_POST['ddlGiftCat']);

         $gifts->lACOID           = $auction->lACOID;
         $gifts->gi_lForeignID    = $package->lBidWinnerID;
         $gifts->gc_lKeyID        = $auction->lCampaignID;
         $gifts->gi_lSponsorID    = null;
         $gifts->gi_lGIK_ID       = null;
         $gifts->gi_bGIK          = false;

         if ($gifts->gi_lAttributedTo <= 0) $gifts->gi_lAttributedTo = null;

         $strDate   = trim($_POST['txtDDate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $gifts->mdteDonation = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);

            //------------------------------------
            // update db tables and return
            //------------------------------------
         $lGiftID = $this->clsGift->lAddNewGiftRecord();
         $this->cPackages->setBidGiftID($lPackageID, $lGiftID);
         $this->session->set_flashdata('msg', 'Bid fulfilled / gift record <b>'.str_pad($lGiftID, 5, '0', STR_PAD_LEFT).'</b> added');
         redirect_AuctionPackageList($lAuctionID);
      }
   }

   function bidVerifyPayType($lPayType){
      return(((integer)$lPayType) > 0);
   }
   function bidVerifyFDateValid($strDate){
      return(bValidVerifyDate($strDate));
   }

   function remove($lAuctionID, $lPackageID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPackageID, 'package ID');

      $this->load->model ('auctions/mpackages', 'cPackages');
      $this->load->model ('img_docs/mimage_doc',    'clsImgDoc');
      $this->load->model ('auctions/mitems',    'cItems');
      $this->load->helper('dl_util/link_auction');
      $this->load->model ('personalization/muser_fields',    'clsUF');
      $this->load->helper('auctions/auction');

      $this->cPackages->loadPackageByPacID($lPackageID);
      $package = &$this->cPackages->packages[0];

         // if associated with a donation, remove gift record
      if (!is_null($package->lGiftID)){
         $this->load->model('donations/mdonations', 'clsGifts');
         $this->load->model('util/mrecycle_bin',    'clsRecycle');
         $this->clsGifts->retireSingleGift($package->lGiftID, null);
      }

         // remove items
      $this->cItems->removeItemsViaPackageID($lPackageID);

         // finally, remove package
      $this->cPackages->removePackage($lPackageID);

      $this->session->set_flashdata('msg', 'Auction package <b>'.str_pad($lPackageID, 6, '0', STR_PAD_LEFT).': '
                          .$package->strPackageSafeName.'</b> was removed.');
      redirect_AuctionOverview($lAuctionID);
   }



}

