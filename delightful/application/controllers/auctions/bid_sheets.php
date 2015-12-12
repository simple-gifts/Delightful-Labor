<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class bid_sheets extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function bidsheetViaPID($lBSID, $lPackageID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gpdf;
      if (!bTestForURLHack('showAuctions')) return;
      
      $gpdf = new stdClass;
      $gpdf->bItemLayoutInProcess = false;    // are we spliting the items across multiple pages?

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lBSID, 'bidsheet ID');
      verifyID($this, $lPackageID, 'package ID');

         //-----------------------------
         // models and helpers
         //-----------------------------
      $this->load->helper('dl_util/pdf');
      $this->load->model ('auctions/mbid_sheet_pdf', 'cBSPDF');
      $this->load->model ('auctions/mbid_sheets',    'cBidSheets');
      $this->load->model ('auctions/mpackages',      'cPackages');
      $this->load->model ('auctions/mitems',         'cItems');
      $this->load->model ('img_docs/mimage_doc',         'clsImgDoc');
      $this->load->helper('img_docs/image_doc');
      $this->load->helper('img_docs/link_img_docs');
      $this->load->helper('auctions/auction');

      $this->cBidSheets->loadSheetByBSID($lBSID);
      $bs = $gpdf->bs = &$this->cBidSheets->bidSheets[0];

      $this->cPackages->loadPackageByPacID($lPackageID);
      $this->cPackages->loadPackageProfileImage();
      $package = $gpdf->package = &$this->cPackages->packages[0];
      $package->curEstValue  = $this->cItems->curEstValueViaPID($lPackageID);

      $this->cItems->loadItemsViaPackageID($lPackageID);
      $gpdf->items = &$this->cItems->items;
      $gpdf->lNumItems = $this->cItems->lNumItems;
      $this->cItems->loadItemProfileImage();

      $pdf = new bsPDF('Portrait', 'pt', $bs->enumPaperType);
      $pdf->SetAutoPageBreak(true, 50);
      $pdf->AliasNbPages();
      $pdf->AddPage();
      $pdf->createBidSheet();
      $pdf->SetFont('Arial','',12);
      $lNumExtra = $bs->lNumSignupPages;
      if ($lNumExtra > 0 && $bs->bIncludeSignup){
         for ($idx=0; $idx<$lNumExtra; ++$idx){
            $pdf->AddPage();
            $pdf->addSignUpColumns($gpdf->lTopMargin);
         }
      }
      $pdf->Output('package_'.str_pad($package->lKeyID, 5, '0', STR_PAD_LEFT).'.pdf', 'I');
   }


}
