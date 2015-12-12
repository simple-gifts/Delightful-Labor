<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class auction_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEditAuction($lAuctionID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gbDateFormatUS, $gclsChapterACO;
      
      if (!bTestForURLHack('showAuctions')) return;
      $this->load->helper('dl_util/verify_id');
      if ($lAuctionID.'' != '0') verifyID($this, $lAuctionID, 'auction ID');

      $displayData = array();
      $displayData['formData'] = new stdClass;
      $displayData['lAuctionID'] = $lAuctionID = (integer)$lAuctionID;
      $displayData['bNew'] = $bNew = $lAuctionID <= 0;
      $displayData['js']      = '';

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('dl_util/link_auction');
      $this->load->helper ('auctions/auction');
      $this->load->model  ('auctions/mauctions',     'cAuction');
      $this->load->model  ('auctions/mbid_sheets',   'cBidSheets');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $this->load->model  ('donations/maccts_camps', 'clsAC');
      $this->load->helper ('dl_util/web_layout');
      $this->load->library('js_build/ajax_support');

      $this->cAuction->loadAuctionByAucID($lAuctionID);
      $auction = $this->cAuction->auctions[0];
      
         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtAuctionName', 'Auction Name', 'trim|required|'
                                                       .'callback_verifyUniqueAuction['.$lAuctionID.']');

		$this->form_validation->set_rules('txtContact',     'Contact',            'trim');
		$this->form_validation->set_rules('txtDescription', 'Description',        'trim');
		$this->form_validation->set_rules('txtLocation',    'Location',           'trim');
		$this->form_validation->set_rules('txtEmail',       'Email',              'trim');
		$this->form_validation->set_rules('txtPhone',       'Phone',              'trim');
		$this->form_validation->set_rules('rdoACO',         'Accounting Country', 'trim');
		$this->form_validation->set_rules('ddlDefBS',       'Default Bid Sheet',  'trim');
      $this->form_validation->set_rules('txtADate',       'Auction Date',       'trim|required|callback_auctionDateValid');
		$this->form_validation->set_rules('ddlAccount',     'Account',            'trim|required|callback_auctionVerifyAccountValid');
		$this->form_validation->set_rules('ddlCamps',       'Campaign',           'trim|callback_auctionVerifyCampValid');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');
         $displayData['js'] .= strDatePicker('datepickerFuture', true);
         $this->clsAC->loadAccounts(false, false, null);

         $clsAjax = new ajax_support;
         $displayData['js'] .= $clsAjax->showCreateXmlHTTPObject();
         $displayData['js'] .= $clsAjax->showCampaignLoadViaAcctID();

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
//echo(__FILE__.' '.__LINE__.'<br>'."\n"); die;
                                                   
            if ($bNew){
               $auction->lACOID = $gclsChapterACO->lKeyID;
               $displayData['formData']->txtADate = '';
               $auction->lAccountID = -1;
               $displayData['lNumBidSheets'] = 0;
            }else {
               $displayData['ddlDefBidSheet'] = $this->cBidSheets->strBidSheetListDDL(
                                                   $auction->lKeyID, $auction->lBidsheetID,
                                                   'ddlDefBS', true, $displayData['lNumBidSheets']);
               $displayData['formData']->txtADate = strNumericDateViaMysqlDate($auction->mdteAuction, $gbDateFormatUS);
            }
            $this->clsAC->loadCampaigns(false, true, $auction->lAccountID, false, null);
            $displayData['formData']->strDDLAccts   = $this->clsAC->strDDLAccts    ($auction->lAccountID, true, true);
            $displayData['formData']->strDDLCamps   = $this->clsAC->strDDLCampaigns($auction->lCampaignID, false);

            $displayData['formData']->txtAuctionName = htmlspecialchars($auction->strAuctionName);
            $displayData['formData']->txtDescription = htmlspecialchars($auction->strDescription);
            $displayData['formData']->txtLocation    = htmlspecialchars($auction->strLocation);
            $displayData['formData']->txtContact     = htmlspecialchars($auction->strContact);
            $displayData['formData']->txtEmail       = htmlspecialchars($auction->strEmail);
            $displayData['formData']->txtPhone       = htmlspecialchars($auction->strPhone);

            $displayData['formData']->strACORadio   = $this->clsACO->strACO_Radios ($auction->lACOID, 'rdoACO');

         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtAuctionName    = set_value('txtAuctionName');
            $displayData['formData']->txtDescription    = set_value('txtDescription');
            $displayData['formData']->txtLocation       = set_value('txtLocation');
            $displayData['formData']->txtContact        = set_value('txtContact');
            $displayData['formData']->txtEmail          = set_value('txtEmail');
            $displayData['formData']->txtPhone          = set_value('txtPhone');
            $displayData['formData']->txtADate          = set_value('txtADate');
            $displayData['formData']->strACORadio       = $this->clsACO->strACO_Radios(set_value('rdoACO'), 'rdoACO');
            if ($bNew){
               $displayData['lNumBidSheets'] = 0;
            }else {
               $displayData['ddlDefBidSheet'] = $this->cBidSheets->strBidSheetListDDL(
                                                               $auction->lKeyID, set_value('ddlDefBS'),
                                                              'ddlDefBS', true, $displayData['lNumBidSheets']);
            }
            $this->clsAC->loadCampaigns(false, true, set_value('ddlAccount'), false, null);
            $displayData['formData']->strDDLAccts   = $this->clsAC->strDDLAccts    (set_value('ddlAccount'), true, true);
            $displayData['formData']->strDDLCamps   = $this->clsAC->strDDLCampaigns(set_value('ddlCamps'), false);
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = GSTR_AUCTIONTOPLEVEL
                                   .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')                                   
                                   .' | '.($bNew ? 'Add New' : 'Edit').'  Auction';

         $displayData['title']          = CS_PROGNAME.' | Silent Auctions';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'auctions/add_edit_auction_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $strDate   = trim($_POST['txtADate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $auction->mdteAuction = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);

         $auction->strAuctionName    = trim($_POST['txtAuctionName']);
         $auction->strDescription    = trim($_POST['txtDescription']);
         $auction->lACOID            = trim($_POST['txtADate']);
         $auction->strLocation       = trim($_POST['txtLocation']);
         $auction->strContact        = trim($_POST['txtContact']);
         $auction->strPhone          = trim($_POST['txtPhone']);
         $auction->strEmail          = trim($_POST['txtEmail']);
         $auction->lACOID            = (int) trim($_POST['rdoACO']);
         $auction->lCampaignID       = (int) trim($_POST['ddlCamps']);
         $auction->lDefaultBidSheet  = (int) trim(@$_POST['ddlDefBS']);
         if ($auction->lDefaultBidSheet <= 0) $auction->lDefaultBidSheet = null;

            //------------------------------------
            // update db tables and return
            //------------------------------------   
         if ($bNew){
            $lAuctionID = $this->cAuction->addNewAuction();
            $this->session->set_flashdata('msg', 'Auction record added');
         }else {
            $this->cAuction->updateAuction($lAuctionID);            
            $this->session->set_flashdata('msg', 'Auction record updated');
         }
         redirect('auctions/auctions/viewAuctionRecord/'.$lAuctionID);
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
   function auctionDateValid($strDate){
      return(bValidVerifyDate($strDate));
   }

   function auctionVerifyAccountValid($lAcctID){
      return(((integer)$lAcctID) > 0);
   }

   function auctionVerifyCampValid($lCampID){
      return(((integer)$lCampID) > 0);
   }

   function verifyUniqueAuction($strAuctionName, $strID){
      $lAuctionID = (int)$strID;

      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                trim($strAuctionName), 'auc_strAuctionName',
                $lAuctionID, 'auc_lKeyID',
                true,        'auc_bRetired',
                false, null, null,
                false, null, null,
                'gifts_auctions')){
         $this->form_validation->set_message('verifyUniqueAuction',
                   'This Auction Name is already being used.');

         return(false);
      }else {
         return(true);
      }
   }

   function remove($lAuctionID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      if (!bTestForURLHack('showAuctions')) return;
      
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lAuctionID, 'auction ID');
      
      $this->load->model ('auctions/mauctions',           'cAuction');
      $this->load->model ('auctions/mpackages',           'cPackages');
      $this->load->model ('img_docs/mimage_doc',          'clsImgDoc');
      $this->load->model ('auctions/mitems',              'cItems');
      $this->load->model ('donations/mdonations',         'clsGifts');
      $this->load->model ('util/mrecycle_bin',            'clsRecycle');
      $this->load->model ('personalization/muser_fields', 'clsUF');
      $this->load->helper('dl_util/link_auction');
      $this->load->helper('auctions/auction');
      
      $this->cPackages->loadPackageByAID($lAuctionID);
      if ($this->cPackages->lNumPackages > 0){
         foreach ($this->cPackages->packages as $package){
            set_time_limit(30);         
            $lPackageID = $package->lKeyID;

               // if associated with a donation, remove gift record
            if (!is_null($package->lGiftID)){
               $this->clsGifts->retireSingleGift($package->lGiftID, null);
            }

               // remove items
            $this->cItems->removeItemsViaPackageID($lPackageID);

               // finally, remove package
            $this->cPackages->removePackage($lPackageID);
         }
      }
      
      $this->cAuction->removeAuction($lAuctionID);
      $this->session->set_flashdata('msg', 'Auction  <b>'.str_pad($lAuctionID, 6, '0', STR_PAD_LEFT)
                                .'</b> was removed.');
      redirect('auctions/auctions/auctionEvents');   
   }
   
   
}
