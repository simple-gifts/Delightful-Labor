<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class bid_templates extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function main(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      
      $displayData = array();
      $displayData['js'] = '';

         //-----------------------------
         // models and helpers
         //-----------------------------
      $this->load->model ('auctions/mbid_sheets', 'cBidSheets');
      $this->load->model ('auctions/mauctions',   'cAuction');
      $this->load->helper('auctions/auction');
      $this->load->helper('dl_util/link_auction');
      $this->load->helper('img_docs/image_doc');
      $this->load->helper('img_docs/link_img_docs');

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $this->cBidSheets->strOrderBidSheet = ' auc_dteAuctionDate DESC, auc_strAuctionName, abs_strSheetName, abs_lKeyID ';
      $this->cBidSheets->loadBidSheets();

      $displayData['lNumBidSheets'] = $lNumBidSheets = $this->cBidSheets->lNumBidSheets;
      $displayData['bidSheets']     = &$this->cBidSheets->bidSheets;
      $displayData['lNumAuctions']  = $lNumAuc = $this->cAuction->lCountAuctions();

      if ($lNumBidSheets > 0 && $lNumAuc > 0){
         foreach ($this->cBidSheets->bidSheets as $bs){
            strXlateTemplate($bs->lTemplateID, $bs->tInfo);
         }
      }

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']       = CS_PROGNAME.' | Silent Auctions';
      $displayData['pageTitle']   = GSTR_AUCTIONTOPLEVEL
                              .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                              .' | Silent Auction Bid Sheets';

      $displayData['mainTemplate'] = 'auctions/bidsheet_list_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addSheetTempSelect(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      
      $displayData = array();
      $displayData['formData'] = new stdClass;
      $displayData['js']      = '';

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('auctions/mbid_sheets', 'cBidSheets');
      $this->load->model ('auctions/mauctions',   'cAuction');
      $this->load->helper('auctions/auction');
      $this->load->helper('dl_util/link_auction');
      $this->load->helper('dl_util/web_layout');

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('rdoTemplate', 'Default Bid Sheet',  'trim');
		$this->form_validation->set_rules('ddlAuction',  'Auction', 'trim|required|callback_verifyDDLAuction');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

         if (validation_errors()==''){
            $displayData['strRadioTemplates'] = $this->cBidSheets->strBidSheetTemplateRadio('rdoTemplate', CENUM_BSTEMPLATE_SIMPLEPACK);
            $displayData['strAuctionDDL'] = $this->cAuction->strDDLAuctions('ddlAuction', -1, true);
         }else {
            setOnFormError($displayData);
            $displayData['strRadioTemplates'] = $this->cBidSheets->strBidSheetTemplateRadio('rdoTemplate', 
                                                     (int)$_POST['rdoTemplate']);
            $displayData['strAuctionDDL'] = $this->cAuction->strDDLAuctions('ddlAuction', (int)$_POST['ddlAuction'], true);
         }
         
            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = GSTR_AUCTIONTOPLEVEL
                                   .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                                   .' | '.anchor('auctions/bid_templates/main', 'Auction Bid Templates', 'class="breadcrumb"')
                                   .' | Add New Bid Sheet Template';

         $displayData['title']          = CS_PROGNAME.' | Silent Auctions';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'auctions/add_new_template_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $lTemplateID = (int)trim($_POST['rdoTemplate']);
         $lAuctionID  = (int)trim($_POST['ddlAuction']);
         redirect('auctions/bid_templates/addEditTemplate/0/'.$lTemplateID.'/'.$lAuctionID);
      }
   }
   
   function verifyDDLAuction($strAuctionID){
      $lAuctionID = (int)trim($strAuctionID);
      if ($lAuctionID <= 0){
         $this->form_validation->set_message('verifyDDLAuction',
                   'Please specify an auction.');
         return(false);
      }else {
         return(true);
      }
   }   

   function addEditTemplate($lBSID, $lTemplateID=null, $lAuctionID=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glChapterID, $genumMeasurePref;
      
      if (!bTestForURLHack('showAuctions')) return;
      
      $bMetric = $genumMeasurePref=='metric';

      $displayData = array();
      $displayData['formData'] = new stdClass;
      $displayData['js']    = '';
      $displayData['lBSID'] = $lBSID = (int)$lBSID;
      $displayData['bNew']  = $bNew = $lBSID <= 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('auctions/auction');
      $this->load->model ('auctions/mbid_sheets', 'cBidSheets');
      $this->load->model ('auctions/mauctions',   'cAuction');
      $this->load->model ('img_docs/mimage_doc',      'cImgDoc');
      $this->load->model ('admin/morganization',  'clsChapter');
      $this->load->model ('admin/madmin_aco',     'clsACO');
      $this->load->helper('dl_util/link_auction');
      $this->load->helper('dl_util/pdf');
      $this->load->helper('img_docs/image_doc');
      $this->load->helper('img_docs/link_img_docs');
      $this->load->helper('dl_util/web_layout');

      $this->cBidSheets->loadSheetByBSID($lBSID);
      $displayData['bs'] = $bs = &$this->cBidSheets->bidSheets[0];

      if ($bNew){
         $displayData['lTemplateID'] = $lTemplateID = (int)$lTemplateID;
         $bs->lAuctionID = $lAuctionID = (int)$lAuctionID;
         $this->cAuction->loadAuctionByAucID($lAuctionID);
         $auction = &$this->cAuction->auctions[0];
         $bs->strAuctionName = $auction->strAuctionName;
         $bs->dteAuction     = $auction->dteAuction;         
      }else {
         $displayData['lTemplateID'] = $lTemplateID = $bs->lTemplateID;
      }
      strXlateTemplate($lTemplateID, $displayData['tInfo']);
      $displayData['lAuctionID'] = $lAuctionID = $bs->lAuctionID;

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtBSName',  'Bid Sheet Name',  'trim|required');
		$this->form_validation->set_rules('txtBSName',  'Bid Sheet Name', 'trim|required|'
                                                       .'callback_verifyUniqueBidSheet['.$lAuctionID.','.$lBSID.']');
		$this->form_validation->set_rules('txtDesc',   'Bid Sheet Description', 'trim');

		$this->form_validation->set_rules('txtSUSCol1', 'Signup Column', 'callback_verifySUColumns');
		$this->form_validation->set_rules('txtSUSCol2', 'Signup Column', 'trim');
		$this->form_validation->set_rules('txtSUSCol3', 'Signup Column', 'trim');
		$this->form_validation->set_rules('txtSUSCol4', 'Signup Column', 'trim');

		$this->form_validation->set_rules('txtSUSColWidth1', 'Signup Column Width', 'trim|required');
		$this->form_validation->set_rules('txtSUSColWidth2', 'Signup Column Width', 'trim');
		$this->form_validation->set_rules('txtSUSColWidth3', 'Signup Column Width', 'trim');
		$this->form_validation->set_rules('txtSUSColWidth4', 'Signup Column Width', 'trim');

		$this->form_validation->set_rules('rdoLogo',   'Logo', 'trim');

		$this->form_validation->set_rules('ddlPaperSize',   'Paper Size', 'trim|required|callback_verifyDDLPaperSize');
		$this->form_validation->set_rules('ddlExtraSheets', 'Extra Sheets', 'trim|required|callback_verifyDDLExtraSheets');

      $this->form_validation->set_rules('chkIncludeMinBid',       'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludeOrgName',      'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludeMinBidInc',    'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludeBuyItNow',     'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludeReserve',      'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludeDate',         'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludeFooter',       'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludePkgName',      'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludePkgID',        'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludePkgDesc',      'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludePkgImage',     'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludePkgEstValue',  'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludeItemName',     'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludeItemID',       'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludeItemDesc',     'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludeItemImage',    'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludeItemDonor',    'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludeItemEstValue', 'Show field on bid sheet', 'trim');
      $this->form_validation->set_rules('chkIncludeSignup',       'Show field on bid sheet', 'trim');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData']   = new stdClass;
         $displayData['signUpCols'] = array();
         $this->load->library('generic_form');

         loadDefaultTemplateVals($lTemplateID, $bNew, $displayData['formData'], $bs);
         $bShowOrgLogo = $displayData['formData']->bShowIncludeOrgLogo;

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['formData']->txtBSName = $bs->strSheetName;
            $displayData['formData']->txtDesc   = $bs->strDescription;
            if ($bNew){
               $displayData['strPaperSizeDDL']   = strPaperSizeDDL('ddlPaperSize', true, -1, $bMetric);
               $displayData['strExtraSheetsDDL'] = $this->cBidSheets->strExtraSheetsDDL('ddlExtraSheets', 0, true);
//               $this->cImgDoc->loadProfileImage(CENUM_CONTEXT_ORGANIZATION, $glChapterID);
               $this->cImgDoc->loadProfileImage(CENUM_CONTEXT_AUCTION, $lAuctionID);
               if ($this->cImgDoc->lNumImageDocs == 0){
                  $lLogoID = null;
               }else {
                  $lLogoID = $this->cImgDoc->imageDocs[0]->lKeyID;
               }
            }else {
               $displayData['strPaperSizeDDL']   = strPaperSizeDDL('ddlPaperSize', true, $bs->enumPaperType, $bMetric);
               $displayData['strExtraSheetsDDL'] = $this->cBidSheets->strExtraSheetsDDL('ddlExtraSheets', $bs->lNumSignupPages, true);
               $lLogoID = $bs->lLogoImgID;
            }
            for ($idx=1; $idx<=4; ++$idx){
               $displayData['signUpCols'][$idx] = new stdClass;
               $dsuC = &$displayData['signUpCols'][$idx];
               $bsuC = &$bs->signUpCols[$idx];

               if ($bsuC->bShow){
                  $dsuC->heading = $bsuC->heading;
                  $dsuC->width   = $bsuC->width;
               }else {
                  $dsuC->heading = '';
                  $dsuC->width   = '';
               }
            }
            if ($bNew){
               $displayData['signUpCols'][1]->heading = 'Bid Amount';
               $displayData['signUpCols'][1]->width   = '20';
               $displayData['signUpCols'][2]->heading = 'Name/Address';
               $displayData['signUpCols'][2]->width   = '50';
               $displayData['signUpCols'][3]->heading = 'Phone';
               $displayData['signUpCols'][3]->width   = '30';
            }
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtBSName    = set_value('txtBSName');
            $displayData['formData']->txtDesc      = set_value('txtDesc');
            $lLogoID = set_value('rdoLogo');

            $displayData['strPaperSizeDDL']   = strPaperSizeDDL('ddlPaperSize', true, set_value('ddlPaperSize'), $bMetric);
            $displayData['strExtraSheetsDDL'] = $this->cBidSheets->strExtraSheetsDDL('ddlExtraSheets', set_value('ddlExtraSheets'), true);

            $displayData['formData']->bIncludeOrgName         = set_value('chkIncludeOrgName');
            $displayData['formData']->bIncludeMinBid          = set_value('chkIncludeMinBid');
            $displayData['formData']->bIncludeMinBidInc       = set_value('chkIncludeMinBidInc');
            $displayData['formData']->bIncludeBuyItNow        = set_value('chkIncludeBuyItNow');
            $displayData['formData']->bIncludeReserve         = set_value('chkIncludeReserve');
            $displayData['formData']->bIncludeDate            = set_value('chkIncludeDate');
            $displayData['formData']->bIncludeFooter          = set_value('chkIncludeFooter');
            $displayData['formData']->bIncludePackageName     = set_value('chkIncludePkgName');
            $displayData['formData']->bIncludePackageID       = set_value('chkIncludePkgID');
            $displayData['formData']->bIncludePackageDesc     = set_value('chkIncludePkgDesc');
            $displayData['formData']->bIncludePackageImage    = set_value('chkIncludePkgImage');
            $displayData['formData']->bIncludePackageEstValue = set_value('chkIncludePkgEstValue');
            $displayData['formData']->bIncludeItemName        = set_value('chkIncludeItemName');
            $displayData['formData']->bIncludeItemID          = set_value('chkIncludeItemID');
            $displayData['formData']->bIncludeItemDesc        = set_value('chkIncludeItemDesc');
            $displayData['formData']->bIncludeItemImage       = set_value('chkIncludeItemImage');
            $displayData['formData']->bIncludeItemDonor       = set_value('chkIncludeItemDonor');
            $displayData['formData']->bIncludeItemEstValue    = set_value('chkIncludeItemEstValue');
            $displayData['formData']->bIncludeSignup          = set_value('chkIncludeSignup');

            for ($idx=1; $idx<=4; ++$idx){
               $displayData['signUpCols'][$idx] = new stdClass;
               $dsuC = &$displayData['signUpCols'][$idx];
               $dsuC->heading = set_value('txtSUSCol'.$idx);
               $dsuC->width   = set_value('txtSUSColWidth'.$idx);
            }
         }

         if ($bShowOrgLogo){
               // logo selection table
            $logoOpts = new stdClass;
            $logoOpts->enumEntryType     = CENUM_IMGDOC_ENTRY_IMAGE;
            $logoOpts->enumContextType   = CENUM_CONTEXT_AUCTION; // CENUM_CONTEXT_ORGANIZATION;
            $logoOpts->lFID              = $lAuctionID; // $glChapterID;
            $logoOpts->lCellWidth        = 70;
            $logoOpts->lCellHeight       = 80;
            $logoOpts->lBorderWidth      = 1;
            $logoOpts->lCellsPerRow      = 4;
            $logoOpts->bShowCaption      = true;
            $logoOpts->bShowDescription  = false;
            $logoOpts->bShowDate         = false;
            $logoOpts->bShowOriginalFN   = false;
            $logoOpts->bAddRadioSelect   = true;
            $logoOpts->strRadioFieldName = 'rdoLogo';
            $logoOpts->lMatchID          = $lLogoID;
            $logoOpts->bShowNone         = true;
            $logoOpts->strShowNoneLabel  = 'No logo image';

            $displayData['formData']->rdoLogo = $this->cImgDoc->strImageDocTerseTable($logoOpts, $displayData['lNumLogoImages']);
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = GSTR_AUCTIONTOPLEVEL
                                   .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                                   .' | '.anchor('auctions/bid_templates/main', 'Auction Bid Templates', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').'  Bid Template';

         $displayData['title']          = CS_PROGNAME.' | Bid Sheets';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'auctions/add_edit_bidsheet_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $bs->bIncludeOrgName         = @$_POST['chkIncludeOrgName']      == 'true';
         $bs->bIncludeItemEstValue    = @$_POST['chkIncludeItemEstValue'] == 'true';
         $bs->bIncludeSignup          = @$_POST['chkIncludeSignup']       == 'true';
         $bs->bIncludeItemDonor       = @$_POST['chkIncludeItemDonor']    == 'true';
         $bs->bIncludeItemImage       = @$_POST['chkIncludeItemImage']    == 'true';
         $bs->bIncludeItemDesc        = @$_POST['chkIncludeItemDesc']     == 'true';
         $bs->bIncludeItemID          = @$_POST['chkIncludeItemID']       == 'true';
         $bs->bIncludeItemName        = @$_POST['chkIncludeItemName']     == 'true';
         $bs->bIncludePackageEstValue = @$_POST['chkIncludePkgEstValue']  == 'true';
         $bs->bIncludePackageImage    = @$_POST['chkIncludePkgImage']     == 'true';
         $bs->bIncludePackageDesc     = @$_POST['chkIncludePkgDesc']      == 'true';
         $bs->bIncludePackageID       = @$_POST['chkIncludePkgID']        == 'true';
         $bs->bIncludePackageName     = @$_POST['chkIncludePkgName']      == 'true';
         $bs->bIncludeFooter          = @$_POST['chkIncludeFooter']       == 'true';
         $bs->bIncludeDate            = @$_POST['chkIncludeDate']         == 'true';
         $bs->bIncludeReserve         = @$_POST['chkIncludeReserve']      == 'true';
         $bs->bIncludeMinBidInc       = @$_POST['chkIncludeMinBidInc']    == 'true';
         $bs->bIncludeBuyItNow        = @$_POST['chkIncludeBuyItNow']     == 'true';
         $bs->bIncludeMinBid          = @$_POST['chkIncludeMinBid']       == 'true';
         $bs->strDescription          = trim($_POST['txtDesc']);
         $bs->strSheetName            = trim($_POST['txtBSName']);

         $bs->enumPaperType           = trim($_POST['ddlPaperSize']);
         $bs->lNumSignupPages         = (int)trim($_POST['ddlExtraSheets']);

         $bs->lTemplateID             = $lTemplateID;

         $bs->bIncludeOrgLogo         = isset($_POST['rdoLogo']);
         if ($bs->bIncludeOrgLogo){
            $bs->lLogoImgID = (int)$_POST['rdoLogo'];
            if ($bs->lLogoImgID <= 0) $bs->lLogoImgID = null;
         }else {
            $bs->lLogoImgID = null;
         }
         
            // column headings
         for ($idx=1; $idx<=4; ++$idx){
               $suCol = &$bs->signUpCols[$idx];
               $suCol->heading = null;
               $suCol->width   = null;
               $suCol->bShow   = false;         
         }
         $idx = 1;
         for ($jidx=1; $jidx<=4; ++$jidx){
            $strHeading = trim(@$_POST['txtSUSCol'.$jidx]);
            $lWidth     = (int)trim(@$_POST['txtSUSColWidth'.$jidx]);
            if ($strHeading != ''){         
               $bs->signUpCols[$idx]->heading = $strHeading;
               $bs->signUpCols[$idx]->width   = $lWidth;
               $bs->signUpCols[$idx]->bShow   = true;
               ++$idx;
            }
         }

            //------------------------------------
            // update db tables and return
            //------------------------------------
         if ($bNew){
            $lBSID = $this->cBidSheets->addNewBidSheet();
            $this->session->set_flashdata('msg', 'Bid sheet added!');
         }else {
            $this->cBidSheets->updateBidSheet($lBSID);
            $this->session->set_flashdata('msg', 'Bid sheet updated!');
         }
         redirect('auctions/bid_templates/viewBidSheet/'.$lBSID);
      }
   }

   function verifySUColumns(){
      $signUpCols = array();
      $strErr = ''; $lTotWidth = 0;
      for ($idx=1; $idx<=4; ++$idx){
         $signUpCols[$idx] = new stdClass;
         $vsuC = &$signUpCols[$idx];
         $vsuC->heading = trim(@$_POST['txtSUSCol'.$idx]);
         $vsuC->width   = trim(@$_POST['txtSUSColWidth'.$idx]);
         if ($idx==1){
            if ($vsuC->heading==''){
               $strErr .= 'At least one column heading is required!';
               break;
            }
         }
         if ($vsuC->heading != ''){
            if (!is_numeric($vsuC->width)){
               $strErr .= 'The column widths must be numeric!';
               break;
            }
            $vsuC->width = (int)$vsuC->width;
            if ($vsuC->width < 10){
               $strErr .= 'Each column widths must be at least 10%%!';
               break;
            }
            $lTotWidth += $vsuC->width;
         }
      }
      
      if ($strErr == '' && $lTotWidth != 100){
         $strErr .= 'The column widths must total 100%% - your total is '.$lTotWidth.'!';
      }
      if ($strErr != ''){
         $this->form_validation->set_message('verifySUColumns', $strErr);
         return(false);
      }else {
         return(true);
      }
   }

   function verifyDDLPaperSize($strPaperSize){
      $strPaperSize = trim($strPaperSize);
      if ($strPaperSize=='-1'){
         $this->form_validation->set_message('verifyDDLPaperSize',
                   'Please specify a paper size.');
         return(false);
      }else {
         return(true);
      }
   }

   function verifyDDLExtraSheets($strExtraSheets){
      $lExtra = (int)trim($strExtraSheets);
      if ($lExtra < 0){
         $this->form_validation->set_message('verifyDDLExtraSheets',
                   'Please specify the number of extra sign-up sheets.');
         return(false);
      }else {
         return(true);
      }
   }

   function verifyUniqueBidSheet($strBidSheetName, $strIDs){
      $IDs = explode(',', $strIDs);
      $lAuctionID = (int)$IDs[0];
      $lBSID      = (int)$IDs[1];

      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                trim($strBidSheetName), 'abs_strSheetName',
                $lBSID,            'abs_lKeyID',
                true,              'abs_bRetired',
                true, $lAuctionID, 'abs_lAuctionID', 
                false, null, null,
                'gifts_auctions_bidsheets')){
         $this->form_validation->set_message('verifyUniqueBidSheet',
                   'The Bid Sheet Name is already being used.');

         return(false);
      }else {
         return(true);
      }
   }

   function viewBidSheet($lBSID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lBSID, 'bidsheet ID');

      $displayData = array();
      $displayData['js']    = '';
      $displayData['lBSID'] = $lBSID = (int)$lBSID;

         //-------------------------
         // models & helpers
         //-------------------------
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      $this->load->model  ('auctions/mbid_sheets', 'cBidSheets');
      $this->load->helper ('auctions/auction');
      $this->load->helper ('dl_util/pdf');
      $this->load->helper ('dl_util/link_auction');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('dl_util/web_layout');

      $this->cBidSheets->loadSheetByBSID($lBSID);
      
      $displayData['bs'] = $bs = &$this->cBidSheets->bidSheets[0];
      strXlateTemplate($bs->lTemplateID, $bs->tInfo);
      $lTemplateID = $bs->lTemplateID;
      $displayData['template'] = new stdClass;
      loadDefaultTemplateVals($lTemplateID, false, $displayData['template'], $bs);

          // hide/show
      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //--------------------------
         // breadcrumbs
         //--------------------------
         $displayData['pageTitle'] = GSTR_AUCTIONTOPLEVEL
                                .' | '.anchor('auctions/auctions/auctionEvents', 'Silent Auctions', 'class="breadcrumb"')
                                .' | '.anchor('auctions/bid_templates/main', 'Auction Bid Templates', 'class="breadcrumb"')
                                .' | View Bid Sheet';

      $displayData['title']          = CS_PROGNAME.' | Bid Sheets';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'auctions/bidsheet_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function removeBidSheet($lBSID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showAuctions')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lBSID, 'bidsheet ID');
      $lBSID = (int)$lBSID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('auctions/mbid_sheets', 'cBidSheets');
      $this->cBidSheets->removeBidSheet($lBSID);

      $this->session->set_flashdata('msg', 'Bid sheet '.str_pad($lBSID, 5, '0', STR_PAD_LEFT).' was removed!');
      redirect('auctions/bid_templates/main');
   }


}