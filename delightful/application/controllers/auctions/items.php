<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class items extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewItemsViaPID($lPackageID){
   //---------------------------------------------------------------------
   //
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

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         //-----------------------------------------
         // load package and associated auction
         //-----------------------------------------
      setPackageContext($lPackageID, $lAuctionID, $displayData);
      $displayData['lNumItems'] = $lNumItems = $this->cItems->lCountItemsViaPID($lPackageID);

      if ($lNumItems > 0){
         $this->cItems->loadItemsViaPackageID($lPackageID);
         $displayData['items'] = &$this->cItems->items;
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = GSTR_AUCTIONTOPLEVEL
                                .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                                .' | '.anchor('auctions/auctions/viewAuctionRecord/'.$lAuctionID, 'Auction', 'class="breadcrumb"')
                                .' | '.anchor('auctions/packages/viewPackageRecord/'.$lPackageID, 'Auction Package', 'class="breadcrumb"')
                                .' | Items';

      $displayData['title']          = CS_PROGNAME.' | Silent Auction';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'auctions/items_via_package_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addAuctionItem($lPackageID){
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
      $this->load->model('admin/madmin_aco',   'clsACO');
      $this->load->model('img_docs/mimage_doc',    'clsImgDoc');
      $this->load->helper('dl_util/link_auction');
      $this->load->helper('auctions/auction');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('js_build/ajax_support');
      $this->load->helper ('dl_util/web_layout');

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('ddlNames',  'Item Donor', 'trim|callback_checkDDLSel');

      setPackageContext($lPackageID, $lAuctionID, $displayData);

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;

            //-------------------------------
            // people/biz ajax interface
            //-------------------------------
         $clsAjax = new ajax_support;
         $displayData['js'] .= $clsAjax->showCreateXmlHTTPObject();
         $displayData['js'] .= $clsAjax->peopleBizNames('showResult', 'selNames');

         $displayData['js'] .= $clsAjax->strPopulateTextFromDDL('selNames', 'donorName');

         $this->load->library('generic_form');

         if (validation_errors()==''){
         }else {
            setOnFormError($displayData);
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = GSTR_AUCTIONTOPLEVEL
                                   .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                                   .' | '.anchor('auctions/auctions/viewAuctionRecord/'.$lAuctionID, 'Auction', 'class="breadcrumb"')
                                   .' | '.anchor('auctions/packages/viewPackageRecord/'.$lPackageID, 'Auction Package', 'class="breadcrumb"')
                                   .' | Add Item';

         $displayData['title']          = CS_PROGNAME.' | Silent Auction';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'auctions/add_item_sel_donor_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $lDonorID = (integer)$_POST['ddlNames'];
         redirect('auctions/items/addEditAuctionItem/'.$lPackageID.'/0/'.$lDonorID);
      }
   }

   function checkDDLSel($strValue){
      if (($strValue.'' == '' ) || ((int)$strValue <= 0)){
         $this->form_validation->set_message('checkDDLSel',
                   'Please enter the first few letters of the auction item provider\'s last name (or business name), '
                  .'then select the entry from the drop-down list.<br><br>
                    <b>Note:</b> the donor must have a business or people record.');
         return(false);
      }else {
         return(true);
      }
   }

   function addEditAuctionItem($lPackageID, $lItemID, $lDonorID=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      if (!bTestForURLHack('showAuctions')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPackageID, 'package ID');
      if ($lItemID.'' != '0')  verifyID($this, $lItemID, 'auction item ID');
      if (!is_null($lDonorID)) verifyID($this, $lDonorID, 'people/business ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['formData'] = new stdClass;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper ('dl_util/time_date');
      $this->load->model  ('auctions/mauctions',     'cAuction');
      $this->load->model  ('auctions/mpackages',     'cPackages');
      $this->load->model  ('auctions/mitems',        'cItems');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $this->load->model  ('img_docs/mimage_doc',        'clsImgDoc');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('dl_util/link_auction');
      $this->load->helper ('auctions/auction');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('generic_form');

         //-----------------------------------------
         // load package and associated auction
         //-----------------------------------------
      setPackageContext($lPackageID, $lAuctionID, $displayData);

      $displayData['lAuctionID'] = $lAuctionID = (integer)$lAuctionID;
      $displayData['lPackageID'] = $lPackageID = (integer)$lPackageID;
      $displayData['lItemID']    = $lItemID    = (integer)$lItemID;
      $displayData['lDonorID']   = $lDonorID;
      $displayData['bNew']       = $bNew       = $lItemID <= 0;

      $this->cItems->loadItemViaItemID($lItemID);
      $displayData['item'] = $item = &$this->cItems->items[0];

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtItemName',     'Item Name',              'trim|required');
		$this->form_validation->set_rules('txtDonorAck',     'Donor Acknowledgement',  'trim|required');
		$this->form_validation->set_rules('txtPublicNotes',  'Public Notes',           'trim');
		$this->form_validation->set_rules('txtPrivateNotes', 'Private Notes',          'trim');
      $this->form_validation->set_rules('txtODate',        'Date Item Obtained',     'trim|required|callback_obtainedDateValid');
		$this->form_validation->set_rules('txtEstValue',     'Estimated Value',        'trim|required|callback_stripCommas|numeric|callback_minPackageAmnt[0.01]');
		$this->form_validation->set_rules('txtOutOfPocket',  'Out of Pocket Expenses', 'trim|required|callback_stripCommas|numeric|callback_minPackageAmnt[0.00]');

		if ($this->form_validation->run() == FALSE){
         $displayData['js'] .= strDatePicker('datepickerFuture', true);

         if (is_null($lDonorID)){
            $lHoldDonorID = $item->lItemDonorID;
            $bBiz = $item->itemDonor_bBiz;
            $displayData['formData']->txtItemDonor  = $item->itemDonor_safeName;
            $strTempAck = 'Anonymous';
         }else {
            $lHoldDonorID = $lDonorID;
            $this->load->model('people/mpeople', 'clsPeople');
            $this->clsPeople->peopleBizInfoViaPID($lDonorID, $pbInfo);
            $bBiz = $pbInfo->bBiz;
            $displayData['formData']->txtItemDonor = $strTempAck = $pbInfo->strSafeNameFL;
         }
         if ($bBiz){
            $displayData['formData']->txtItemDonor .= ' <i>(business)</i>'
                      .strLinkView_BizRecord($lHoldDonorID, 'View business record', true);
         }else {
            $displayData['formData']->txtItemDonor .= ' '
                      .strLinkView_PeopleRecord($lHoldDonorID, 'View people record', true);
         }

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($bNew){
               $displayData['formData']->txtODate = '';
            }else {
               $displayData['formData']->txtODate = strNumericDateViaMysqlDate($item->mdteItemObtained, $gbDateFormatUS);
            }

            $displayData['formData']->txtItemName     = htmlspecialchars($item->strItemName);
            if ($item->strDonorAck.'' == ''){
               $displayData['formData']->txtDonorAck  = $strTempAck;
            }else {
               $displayData['formData']->txtDonorAck  = htmlspecialchars($item->strDonorAck);
            }
            $displayData['formData']->txtPublicNotes  = htmlspecialchars($item->strDescription);
            $displayData['formData']->txtPrivateNotes = htmlspecialchars($item->strInternalNotes);
            $displayData['formData']->txtEstValue     = number_format($item->curEstAmnt,  2);
            $displayData['formData']->txtOutOfPocket  = number_format($item->curOutOfPocket,  2);
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtItemName     = set_value('txtItemName');
            $displayData['formData']->txtDonorAck     = set_value('txtDonorAck');
            $displayData['formData']->txtPublicNotes  = set_value('txtPublicNotes');
            $displayData['formData']->txtPrivateNotes = set_value('txtPrivateNotes');
            $displayData['formData']->txtODate        = set_value('txtODate');
            $displayData['formData']->txtEstValue     = set_value('txtEstValue');
            $displayData['formData']->txtOutOfPocket  = set_value('txtOutOfPocket');
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = GSTR_AUCTIONTOPLEVEL
                                   .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                                   .' | '.anchor('auctions/auctions/viewAuctionRecord/'.$lAuctionID, 'Auction', 'class="breadcrumb"')
                                   .' | '.anchor('auctions/packages/viewPackageRecord/'.$lPackageID, 'Auction Package', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').'  Item';

         $displayData['title']          = CS_PROGNAME.' | Silent Auctions';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'auctions/add_edit_item_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $strDate = trim($_POST['txtODate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $item->mdteItemObtained = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);

         $item->lPackageID           = $lPackageID;
         $item->strItemName          = trim($_POST['txtItemName']);
         $item->strDonorAck          = trim($_POST['txtDonorAck']);
         $item->strDescription       = trim($_POST['txtPublicNotes']);
         $item->strInternalNotes     = trim($_POST['txtPrivateNotes']);
         $item->curEstAmnt           = (float)(trim($_POST['txtEstValue']));
         $item->curOutOfPocket       = (float)(trim($_POST['txtOutOfPocket']));

            //------------------------------------
            // update db tables and return
            //------------------------------------
         if ($bNew){
            $lItemID = $this->cItems->addNewItem($lDonorID, $lPackageID);
            $this->session->set_flashdata('msg', 'Auction item record added');
         }else {
            $this->cItems->updateItem($lItemID);
            $this->session->set_flashdata('msg', 'Auction item record updated');
         }
         redirect('auctions/items/viewItemRecord/'.$lItemID);
      }
   }

   function obtainedDateValid($strDate){
      return(bValidVerifyDate($strDate));
   }

   function stripCommas(&$strAmount){
      $strAmount = str_replace (',', '', $strAmount);
      return(true);
   }

   function minPackageAmnt($strMinBid, $curMin){
      return((float)($strMinBid) >= $curMin);
   }

   function viewItemRecord($lItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      $displayData = array();
      $displayData['js'] = '';

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lItemID, 'auction item ID');

         //-------------------------
         // models & helpers
         //-------------------------
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
      $this->load->model  ('img_docs/mimage_doc',    'clsImgDoc');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('img_docs/link_img_docs');

      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

         //-------------------------------
         // images and documents
         //-------------------------------
      loadImgDocRecView($displayData, CENUM_CONTEXT_AUCTIONITEM, $lItemID);

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

      $displayData['lItemID']    = $lItemID    = (int)$lItemID;
      $this->cItems->loadItemViaItemID($lItemID);
      $displayData['item'] = $item = &$this->cItems->items[0];

      $displayData['lAuctionID'] = $lAuctionID = $item->lAuctionID;
      $displayData['lPackageID'] = $lPackageID = $item->lPackageID;

      setPackageContext($lPackageID, $lAuctionID, $displayData);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = GSTR_AUCTIONTOPLEVEL
                                .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                                .' | '.anchor('auctions/auctions/viewAuctionRecord/'.$lAuctionID, 'Auction', 'class="breadcrumb"')
                                .' | '.anchor('auctions/packages/viewPackageRecord/'.$lPackageID, 'Auction Package', 'class="breadcrumb"')
                                .' | Item Record';

      $displayData['title']          = CS_PROGNAME.' | Silent Auctions';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'auctions/item_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function remove($lPackageID, $lItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPackageID, 'package ID');
      verifyID($this, $lItemID, 'auction item ID');

      $lPackageID = (integer)$lPackageID;
      $lItemID    = (integer)$lItemID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model('auctions/mitems', 'cItems');
      $this->load->helper ('dl_util/link_auction');

      $this->cItems->loadItemViaItemID($lItemID);
      $item = &$this->cItems->items[0];
      $this->cItems->removeItemViaItemID($lItemID);

      $this->session->set_flashdata('msg', 'Auction item <b>'.str_pad($lItemID, 6, '0', STR_PAD_LEFT).': '
                          .$item->strSafeItemName.'</b> was removed.');
      redirect('auctions/items/viewItemsViaPID/'.$lPackageID);
   }

}