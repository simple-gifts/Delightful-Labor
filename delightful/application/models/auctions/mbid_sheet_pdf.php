<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2013 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('auctions/mbid_sheet_pdf', 'cBSPDF');
--------------------------------------------------------------------

---------------------------------------------------------------------*/

require_once('./application/libraries/fpdf/fpdf.php');
//require_once('./application/libraries/tcpdf/tcpdf.php');

class bsPDF extends FPDF {
//class bsPDF extends TCPDF {

   public $bs, $package;

// Page header
   function Header(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gpdf, $gbHeaderSet;

      $gpdf->lTopMargin = $gpdf->lLeftMargin = $gpdf->lRightMargin = 40;
      $gpdf->lBottomMargin = 55;
      paperSizeDimPts($gpdf->bs->enumPaperType, $gpdf->lPageWidth, $gpdf->lPageHeight);

      $this->SetMargins($gpdf->lLeftMargin, $gpdf->lTopMargin);

      $gbHeaderSet = true;
   }

   function createBidSheet(){
      global $gpdf;

      switch ($gpdf->bs->lTemplateID){
         case CENUM_BSTEMPLATE_SIMPLEPACK:
            $this->headerSimplePack();
            break;
         case CENUM_BSTEMPLATE_PACKAGEPIC:
            $this->headerSimplePackagePic();
            break;
         case CENUM_BSTEMPLATE_MIN:
            $this->headerMinimal();
            break;
         case CENUM_BSTEMPLATE_ITEMS:
            $this->headerEverything();
            break;
      }
   }

   private function headerEverything(){
   //---------------------------------------------------------------------
   // show package and individual item descriptions
   //---------------------------------------------------------------------
      global $gpdf, $gclsChapter;

      $lTop = $gpdf->lTopMargin;
      $lTitleLeft  = $gpdf->lLeftMargin;
      $lAvailSpace = $gpdf->lPageWidth - ($gpdf->lRightMargin + $gpdf->lLeftMargin);

         // thanks to http://stackoverflow.com/questions/3514076/special-characters-in-fpdf-with-php
      $strCur = html_entity_decode($gpdf->package->strCurrencySymbol);
      $strCur = iconv('UTF-8', 'windows-1252', $strCur);

      $lLineX = array();  // x coordinate of separator line
      $lLineX[] = $gpdf->lTopMargin;

//      if ($this->PageNo()==1){
         // organization name
      $this->SetXY($lTitleLeft, $lTop);
      if ($gpdf->bs->bIncludeOrgName){
         $strPName = $gclsChapter->strChapterName;
         $lFS = lFontSizeThatFits($this, 'Arial', 'B', 16, 10, $lAvailSpace, $strPName, $lWidth);
         $this->Cell(0, $lFS+1, $strPName, 0, 2, 'C');
         $lTop = $lLineX[] = $this->GetY();
      }

         // auction name
      $strAName = $gpdf->package->strAuctionName;
      $lFS = lFontSizeThatFits($this, 'Arial', 'B', 18, 10, $lAvailSpace, $strAName, $lWidth);
      $this->Cell(0, $lFS+1, $strAName, 0, 2, 'C');
      $lTop = $lLineX[] = $this->GetY();

         // auction date
      if ($gpdf->bs->bIncludeDate){
         $strPName = date('F jS, Y', $gpdf->package->dteAuction);
         $lFS = lFontSizeThatFits($this, 'Arial', '', 13, 10, $lAvailSpace, $strPName, $lWidth);
         $this->Cell(0, $lFS+5, $strPName, 0, 2, 'C');
         $lTop = $lLineX[] = $this->GetY();
      }

      $lPackInfoLeft   = $gpdf->lLeftMargin;
      $lPackInfoBottom = $imageBottom = $gpdf->lTopMargin;

          // Package image?
      $bShowPackImg = !is_null($gpdf->package->profileImage) && $gpdf->bs->bIncludePackageImage;
      if ($bShowPackImg){
         $lPackInfoWidth = $lAvailSpace * 0.55;
         $lImgLeft = $lPackInfoLeft + $lPackInfoWidth + 20;
         $maxWidth = ($lAvailSpace - $lImgLeft) + 20;
         $lPackImgTop = $lTop + 20;
      }else {
         $lPackInfoWidth = $lAvailSpace;
      }

         // package name
      if ($gpdf->bs->bIncludePackageName){
         $this->SetXY($lPackInfoLeft, $lTop+10);
         $strPName = $gpdf->package->strPackageName;
         $lFS = lFontSizeThatFits($this, 'Arial', 'B', 15, 10, $lPackInfoWidth, $strPName, $lWidth);
         $this->Cell($lPackInfoWidth, $lFS+1, $strPName, 0, 2, 'C');
         $lTop = $lLineX[] = $this->GetY();
      }

         // package ID
      if ($gpdf->bs->bIncludePackageID){
         $strPName = 'package ID: '.str_pad($gpdf->package->lKeyID, 5, '0', STR_PAD_LEFT);
         $lFS = lFontSizeThatFits($this, 'Courier', '', 10, 8, $lPackInfoWidth, $strPName, $lWidth);
         $this->Cell($lPackInfoWidth, $lFS, $strPName, 0, 2, 'C');
         $lTop = $lLineX[] = $this->incTopPos(0, $this->GetY()+5);
      }

         // package description
      $lFS = 11;
      $this->SetFont('Arial', '', $lFS);
      if ($gpdf->bs->bIncludePackageDesc){
         $this->MultiCell($lPackInfoWidth, $lFS+1, $gpdf->package->strDescription, 0, 'L');
         
         $lTop = $lLineX[] = $this->incTopPos(0, $this->GetY()+10);
      }

          // Package image
      if ($bShowPackImg){
         $strFN = $gpdf->package->profileImage->strPath.'/'.$gpdf->package->profileImage->strSystemFN;
         if (file_exists($strFN)){
            $maxHeight = 3.0*72;
            $imgSize = getimagesize($strFN, $imageinfo);
            $width = optimumImageWidth($imgSize, $maxWidth, $maxHeight, $sngAspect, $sngOptY);
            $lImgLeft = $lImgLeft +(($maxWidth - $width)/2);
            $this->Image($strFN, $lImgLeft, $lPackImgTop, $width);
            $lTop = $lLineX[] = $imageBottom = $lPackImgTop + $sngOptY + 20;
         }
      }

      $lTop = max($lLineX); // max value in array     // max($lPackInfoBottom, $imageBottom, $lTopAuctionName, $lPackNameBottom, $lPackIDBottom);
      $lFS = 10;
      $this->SetFont('Arial', '', $lFS);

         // starting bid
      if ($gpdf->bs->bIncludeMinBid){
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Starting Bid:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curMinBidAmnt, 2), '0', '1', 'R');
      }

         // bid increment
      if ($gpdf->bs->bIncludeMinBidInc){
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Minimum Bid Increment:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curMinBidInc, 2), '0', '1', 'R');
      }

         // buy it now
      if ($gpdf->bs->bIncludeBuyItNow && !is_null($gpdf->package->curBuyItNowAmnt)){
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Buy it now for:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curBuyItNowAmnt, 2), '0', '1', 'R');
      }

         // estimated value
      if ($gpdf->bs->bIncludePackageEstValue){
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Estimated Value:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curEstValue, 2), '0', '1', 'R');
      }

         // reserve amount
      if ($gpdf->bs->bIncludeReserve){
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Reserve Amount:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curReserveAmnt, 2), '0', '1', 'R');
      }

      $lTop = $this->incTopPos($lTop, 12);

         // line separator
      $this->SetLineWidth(1);
      $this->Line($gpdf->lLeftMargin, $lTop, $gpdf->lPageWidth - $gpdf->lLeftMargin, $lTop);


      $cOpts = new stdClass;
      $cOpts->fontFamily   = 'Arial';
      $cOpts->fontStyle    = '';
      $cOpts->fontSize     = 10;
      $cOpts->cellWidth    = $lAvailSpace;
      $cOpts->lineHeight   = $cOpts->fontSize + 1;
      $cOpts->strCur       = $strCur;
//      $cOpts->pageBottom   = $gpdf->lPageHeight - ($gpdf->lTopMargin+$gpdf->lBottomMargin+20);
      $cOpts->pageBottom   = $gpdf->lPageHeight - ($gpdf->lBottomMargin+20);

      $itemImage = new stdClass;
      if ($gpdf->bs->bIncludeItemImage){
//         $lTop += 15;
         $lTop = $this->incTopPos($lTop, 15);
         $itemImage->maxWidth  = 100;
         $itemImage->maxHeight = 75;
         $cOpts->cellX = $gpdf->lLeftMargin + $itemImage->maxWidth + 20;
      }else {
         $itemImage->maxWidth  = 0;
         $itemImage->maxHeight = 0;
         $cOpts->cellX = $gpdf->lLeftMargin;
      }

      $lBottom = $gpdf->lPageHeight - $gpdf->lBottomMargin;
      $this->SetFont($cOpts->fontFamily, '', $cOpts->fontSize);

      if ($gpdf->lNumItems > 0){
         foreach ($gpdf->items as $item){
            $this->addItemToBS($item, $cOpts, $itemImage, $lTop, $lBottom);
         }
      }

      $lTop += 35;
      $this->addSignUpColumns($lTop);
   }

   function addItemToBS(&$item, &$cOpts, &$itemImage, &$lTop, $lBottom){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gpdf, $gbHeaderSet;

      $lCellHeight = 0;
      $this->SetXY($cOpts->cellX, $lTop);
      $lImageBottom = $lTop;

          // item image
      if (!is_null($item->profileImage) && $gpdf->bs->bIncludeItemImage){
         $strFN = $item->profileImage->strPath.'/'.$item->profileImage->strSystemThumbFN;
         if (file_exists($strFN)){
            $lImageX  = $gpdf->lLeftMargin;
            $maxWidth = $itemImage->maxWidth;

            $maxHeight = $itemImage->maxHeight;
            $imgSize = getimagesize($strFN, $imageinfo);
            $width = optimumImageWidth($imgSize, $maxWidth, $maxHeight, $sngAspect, $sngOptY);

            $lImageBottom = $sngOptY+$lTop+13;
/*            
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$lImageBottom = $lImageBottom, \$cOpts->pageBottom=".$cOpts->pageBottom." <br></font>\n");
*/  
  
            if ($lImageBottom > $cOpts->pageBottom){
               $this->AddPage();
               $lTop = $gpdf->lTopMargin;
               $lImageBottom = $sngOptY+$lTop+3;
               $this->SetXY($cOpts->cellX, $lTop);
            }

            $lImageLeft = $lImageX +(($maxWidth - $width)/2);
            $this->Image($strFN, $lImageLeft, $lTop+3, $width);
         }
      }

      $gbHeaderSet = false;
      if ($gpdf->bs->bIncludeItemName){
         $this->SetFont($cOpts->fontFamily, 'B', $cOpts->fontSize);
         $this->MultiCell($cOpts->cellWidth, $cOpts->lineHeight, $item->strItemName);
         $this->SetFont($cOpts->fontFamily, '', $cOpts->fontSize);
         $this->SetX($cOpts->cellX);
      }
      if ($gpdf->bs->bIncludeItemDesc && $item->strDescription != ''){
         $this->MultiCell(0, $cOpts->lineHeight, $item->strDescription);
         $this->SetX($cOpts->cellX);
      }
      if ($gpdf->bs->bIncludeItemID){
         $strItemID = 'Item ID '.str_pad($item->lKeyID, 5, '0', STR_PAD_LEFT);
         $this->SetFont($cOpts->fontFamily, 'I', $cOpts->fontSize-1);
         $this->MultiCell($cOpts->cellWidth, $cOpts->lineHeight, $strItemID);
         $this->SetFont($cOpts->fontFamily, '', $cOpts->fontSize);
         $this->SetX($cOpts->cellX);
      }

      if ($gpdf->bs->bIncludeItemDonor){
         $this->SetFont($cOpts->fontFamily, 'I', $cOpts->fontSize-1);
         $this->MultiCell($cOpts->cellWidth, $cOpts->lineHeight, 'Donated by '.$item->strDonorAck);  //$item->itemDonor_strFName.' '.$item->itemDonor_strLName );
         $this->SetFont($cOpts->fontFamily, '', $cOpts->fontSize);
         $this->SetX($cOpts->cellX);
      }

      if ($gpdf->bs->bIncludeItemEstValue){
         $this->SetFont($cOpts->fontFamily, 'I', $cOpts->fontSize-1);
         $this->MultiCell($cOpts->cellWidth, $cOpts->lineHeight,
                       'Estimated Value: '.$cOpts->strCur.' '.number_format($item->curEstAmnt, 2));
         $this->SetFont($cOpts->fontFamily, '', $cOpts->fontSize);
         $this->SetX($cOpts->cellX);
      }

      if ($gbHeaderSet){
         $lTop = $this->GetY()+12;
      }else {
         $lTop = max($this->GetY()+12, $lImageBottom);
      }
   }


   private function headerMinimal(){
   //---------------------------------------------------------------------
   // show package image
   //---------------------------------------------------------------------
      global $gpdf, $gclsChapter;

         // thanks to http://stackoverflow.com/questions/3514076/special-characters-in-fpdf-with-php
      $strCur = html_entity_decode($gpdf->package->strCurrencySymbol);
      $strCur = iconv('UTF-8', 'windows-1252', $strCur);

         // organization name
      $lTop = $gpdf->lTopMargin;
      $lAvailSpace = $gpdf->lPageWidth-($gpdf->lRightMargin + $gpdf->lLeftMargin);
      $this->SetXY($gpdf->lRightMargin, $lTop);

      if ($gpdf->bs->bIncludeOrgName){
         $strPName = $gclsChapter->strChapterName;
         $lFS = lFontSizeThatFits($this, 'Arial', 'B', 16, 10, $lAvailSpace, $strPName, $lWidth);
         $this->Cell(0, $lFS+1, $strPName, 0, 2, 'C');
         $lTop = $this->GetY();
      }
         // auction name
      $strAName = $gpdf->package->strAuctionName;
      $lFS = lFontSizeThatFits($this, 'Arial', 'B', 24, 10, $lAvailSpace, $strAName, $lWidth);
      $this->Cell(0, $lFS+12, $strAName, 0, 2, 'C');
      $lTop = $this->GetY();

         // package name
      if ($gpdf->bs->bIncludePackageName){
         $strPName = $gpdf->package->strPackageName;
         $lFS = lFontSizeThatFits($this, 'Arial', 'B', 18, 10, $lAvailSpace, $strPName, $lWidth);
         $this->Cell(0, $lFS+3, $strPName, 0, 2, 'C');
         $lTop = $this->GetY();
      }

         // package ID
      if ($gpdf->bs->bIncludePackageID){
         $strPName = 'package ID: '.str_pad($gpdf->package->lKeyID, 5, '0', STR_PAD_LEFT);
         $lFS = lFontSizeThatFits($this, 'Courier', '', 10, 8, $lAvailSpace, $strPName, $lWidth);
         $this->Cell(0, $lFS+15, $strPName, 0, 2, 'C');
      }

      $lFS = 11;
      $this->SetFont('Arial', '', $lFS);

         // package description
      if ($gpdf->bs->bIncludePackageDesc){
         $this->MultiCell(0, $lFS+1, $gpdf->package->strDescription, 0, 'L');
      }

      $lTop = $this->GetY()+8;

         // starting bid
      if ($gpdf->bs->bIncludeMinBid){
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Starting Bid:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curMinBidAmnt, 2), '0', '1', 'R');
      }
         // bid increment
      if ($gpdf->bs->bIncludeMinBidInc){
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Minimum Bid Increment:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curMinBidInc, 2), '0', '1', 'R');
      }

         // buy it now
      if ($gpdf->bs->bIncludeBuyItNow && !is_null($gpdf->package->curBuyItNowAmnt)){
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Buy it now for:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curBuyItNowAmnt, 2), '0', '1', 'R');
      }

         // estimated value
      if ($gpdf->bs->bIncludePackageEstValue){
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Estimated Value:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curEstValue, 2), '0', '1', 'R');
      }

         // reserve amount
      if ($gpdf->bs->bIncludeReserve){
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Reserve Amount:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curReserveAmnt, 2), '0', '1', 'R');
      }

      $lTop = $this->GetY()+25;
      $this->addSignUpColumns($lTop);
   }

   private function headerSimplePackagePic(){
   //---------------------------------------------------------------------
   // show package image
   //---------------------------------------------------------------------
      global $gpdf, $gclsChapter;

         // max logo dimensions in points
      $maxWidth = 1.5*72; $maxHeight = 1.5*72;
      $lTitleLeft = $gpdf->lLeftMargin;
      $imageBottom = $lRightColBot = $gpdf->lTopMargin;

         // thanks to http://stackoverflow.com/questions/3514076/special-characters-in-fpdf-with-php
      $strCur = html_entity_decode($gpdf->package->strCurrencySymbol);
      $strCur = iconv('UTF-8', 'windows-1252', $strCur);
      $lLogoBot = $gpdf->lTopMargin;

      if ($gpdf->bs->bIncludeOrgLogo && !is_null($gpdf->bs->lLogoImgID)){
             // Logo
         $strFN = $gpdf->bs->strPath.'/'.$gpdf->bs->strSystemFN;
         if (file_exists($strFN)){
            $imgSize = getimagesize($strFN, $imageinfo);
            $width = optimumImageWidth($imgSize, $maxWidth, $maxHeight, $sngAspect, $sngOptY);

            $this->Image($strFN, $gpdf->lLeftMargin, $gpdf->lTopMargin, $width);
            $lTitleLeft = $gpdf->lLeftMargin + $width + 5;
            $lLogoBot = $gpdf->lTopMargin + $sngOptY + 20;
         }
      }

         // organization name
      $lTop = $gpdf->lTopMargin;
      $lAvailSpace = ($gpdf->lPageWidth-$gpdf -> lRightMargin) - $lTitleLeft;
      $this->SetXY($lTitleLeft, $lTop);
      if ($gpdf->bs->bIncludeOrgName){
         $strPName = $gclsChapter->strChapterName;
         $lFS = lFontSizeThatFits($this, 'Arial', 'B', 16, 10, $lAvailSpace, $strPName, $lWidth);
         $this->Cell(0, $lFS+1, $strPName, 0, 2, 'C');
         $lTop = $this->GetY();
      }

         // auction name
      $strAName = $gpdf->package->strAuctionName;
      $lFS = lFontSizeThatFits($this, 'Arial', 'B', 24, 10, $lAvailSpace, $strAName, $lWidth);
      $this->Cell(0, $lFS+1, $strAName, 0, 2, 'C');
      $lTop = $this->GetY();

         // package name
      if ($gpdf->bs->bIncludePackageName){
         $this->SetXY($lTitleLeft, $lTop+10);
         $strPName = $gpdf->package->strPackageName;
         $lFS = lFontSizeThatFits($this, 'Arial', 'B', 18, 10, $lAvailSpace, $strPName, $lWidth);
         $this->Cell(0, $lFS+1, $strPName, 0, 2, 'C');
         $lTop = $this->GetY();
      }

         // package ID
      if ($gpdf->bs->bIncludePackageID){
         $strPName = 'package ID: '.str_pad($gpdf->package->lKeyID, 5, '0', STR_PAD_LEFT);
         $lFS = lFontSizeThatFits($this, 'Courier', '', 10, 8, $lAvailSpace, $strPName, $lWidth);
         $this->Cell(0, $lFS, $strPName, 0, 2, 'C');
         $lTop = $this->GetY()+5;
      }

         // line separator
      $this->SetLineWidth(1);
      $this->Line($lTitleLeft, $lTop, $gpdf->lPageWidth - $gpdf->lLeftMargin, $lTop);
      $lTop = max($lTop, $lLogoBot);

      $lTop += 5;

          // Package image
      $maxWidth = $gpdf->lPageWidth - ($gpdf->lLeftMargin+$gpdf->lRightMargin); // for no package image
      if (!is_null($gpdf->package->profileImage) && $gpdf->bs->bIncludePackageImage){
         $strFN = $gpdf->package->profileImage->strPath.'/'.$gpdf->package->profileImage->strSystemFN;
         if (file_exists($strFN)){

               // image nominally starts 20 pts right of midline
            $lImageX = $gpdf->lPageWidth/2 + 20;
            $maxWidth = $gpdf->lPageWidth/2 - ($gpdf->lLeftMargin+20);

            $maxHeight = 3.0*72;
            $imgSize = getimagesize($strFN, $imageinfo);
            $width = optimumImageWidth($imgSize, $maxWidth, $maxHeight, $sngAspect, $sngOptY);
            $lImageLeft = $lImageX +(($maxWidth - $width)/2);
            $this->Image($strFN, $lImageLeft, $lTop, $width);
            $imageBottom = $lTop + $sngOptY + 20;
         }
      }
      $lFS = 11;
      $this->SetFont('Arial', '', $lFS);

         // package description
      if ($gpdf->bs->bIncludePackageDesc){
//         $lTop += 2;
         $lTop = $this->incTopPos($lTop, 2);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->MultiCell($maxWidth+15, $lFS+1, $gpdf->package->strDescription, 0, 'L');
         $lTop = $this->GetY()+10;
      }
         // starting bid
      if ($gpdf->bs->bIncludeMinBid){
//         $lTop += 12;
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Starting Bid:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curMinBidAmnt, 2), '0', '1', 'R');
      }
         // bid increment
      if ($gpdf->bs->bIncludeMinBidInc){
//         $lTop += 12;
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Minimum Bid Increment:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curMinBidInc, 2), '0', '1', 'R');
      }

         // buy it now
      if ($gpdf->bs->bIncludeBuyItNow && !is_null($gpdf->package->curBuyItNowAmnt)){
//         $lTop += 12;
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Buy it now for:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curBuyItNowAmnt, 2), '0', '1', 'R');
      }

         // estimated value
      if ($gpdf->bs->bIncludePackageEstValue){
//         $lTop += 12;
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Estimated Value:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curEstValue, 2), '0', '1', 'R');
      }

         // reserve amount
      if ($gpdf->bs->bIncludeReserve){
//         $lTop += 12;
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Reserve Amount:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curReserveAmnt, 2), '0', '1', 'R');
      }
      $lTop += 25;

      if ($imageBottom > $lTop) $lTop = $imageBottom;   // case where the image extends lower than the other details
      if ($lLogoBot > $lTop) $lTop = $lLogoBot + 25;
      $this->addSignUpColumns($lTop);
   }

   private function incTopPos($lTop, $lPts){
      global $gpdf;

      $lTop += $lPts;
      if ($lTop >= ($gpdf->lPageHeight - $gpdf->lBottomMargin)){
         $this->addPage();
         $lTop = $gpdf->lTopMargin;
      }
      return($lTop);
   }

private function testImageSize($x, $y, $maxX, $maxY){
   $imgSize = array($x, $y);
   $optX = optimumImageWidth($imgSize, $maxX, $maxY, $sngAspect, $optY);
   echoT('<table border="1">
       <tr>
          <td><b>X</b></td>
          <td><b>Y</b></td>
          <td><b>aspect</b></td>
          <td><b>maxX</b></td>
          <td><b>maxY</b></td>
          <td><b>optX</b></td>
          <td><b>opyY</b></td>
       </tr>
       <tr>
          <td>'.$x.'</td>
          <td>'.$y.'</td>
          <td>'.number_format($sngAspect, 3).'</td>
          <td>'.$maxX.'</td>
          <td>'.$maxY.'</td>
          <td>'.number_format($optX, 3).'</td>
          <td>'.number_format($optY, 3).'</td>
       </tr>
    </table><br>');

}

   private function headerSimplePack(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gpdf, $gclsChapter;

         // max logo dimensions in points
      $maxWidth = 3*72; $maxHeight = 2*72;
      $lTitleLeft = $gpdf->lLeftMargin;
      $imageBottom = $lRightColBot = $gpdf->lTopMargin;

         // thanks to http://stackoverflow.com/questions/3514076/special-characters-in-fpdf-with-php
      $strCur = html_entity_decode($gpdf->package->strCurrencySymbol);
      $strCur = iconv('UTF-8', 'windows-1252', $strCur);
/*
$this->testImageSize(50, 25, 200, 200);
$this->testImageSize(25, 50, 200, 200);
die;
*/
      $bImageSet = false;
      if ($gpdf->bs->bIncludeOrgLogo && !is_null($gpdf->bs->lLogoImgID)){
             // Logo
         $strFN = $gpdf->bs->strPath.'/'.$gpdf->bs->strSystemFN;
         if (file_exists($strFN)){
            $imgSize = getimagesize($strFN, $imageinfo);
            $width   = optimumImageWidth($imgSize, $maxWidth, $maxHeight, $sngAspect, $sngOptY);

            $this->Image($strFN, $gpdf->lLeftMargin + (($maxWidth-$width)/2), $gpdf->lTopMargin, $width);
            $lTitleLeft = $gpdf->lLeftMargin + $maxWidth + 5;
            $imageBottom = $gpdf->lTopMargin + $sngOptY + 10;
            $bImageSet = true;
         }
      }

         // auction name
      $strAName = $gpdf->package->strAuctionName;
      $lAvailSpace = ($gpdf->lPageWidth-$gpdf -> lRightMargin) - $lTitleLeft;
      $lFS = lFontSizeThatFits($this, 'Arial', 'B', 28, 10, $lAvailSpace, $strAName, $lWidth);
      $lTop = $gpdf->lTopMargin;
      $this->SetXY($lTitleLeft, $lTop);
      $this->Cell(0, $lFS+1, $strAName, 0, 2, 'C');
      $lTop = $this->GetY();

         // auction date
      if ($gpdf->bs->bIncludeDate){
         $strPName = date('F jS, Y', $gpdf->package->dteAuction);
         $lFS = lFontSizeThatFits($this, 'Arial', '', 13, 10, $lAvailSpace, $strPName, $lWidth);
         $this->Cell(0, $lFS+5, $strPName, 0, 2, 'C');
         $lTop = $this->GetY();
      }

         // organization name
      if ($gpdf->bs->bIncludeOrgName){
         $strPName = $gclsChapter->strChapterName;
         $lFS = lFontSizeThatFits($this, 'Arial', 'B', 15, 10, $lAvailSpace, $strPName, $lWidth);
         $this->Cell(0, $lFS+1, $strPName, 0, 2, 'C');
         $lTop = $this->GetY();
      }

         // package name
      if ($gpdf->bs->bIncludePackageName){
         $this->SetXY($lTitleLeft, $lTop+10);
         $strPName = $gpdf->package->strPackageName;
         $lFS = lFontSizeThatFits($this, 'Arial', 'B', 18, 10, $lAvailSpace, $strPName, $lWidth);
         $this->Cell(0, $lFS+1, $strPName, 0, 2, 'C');
         $lTop = $this->GetY();
      }

         // package ID
      if ($gpdf->bs->bIncludePackageID){
         $strPName = 'package ID: '.str_pad($gpdf->package->lKeyID, 5, '0', STR_PAD_LEFT);
         $lFS = lFontSizeThatFits($this, 'Courier', '', 10, 8, $lAvailSpace, $strPName, $lWidth);
         $this->Cell(0, $lFS, $strPName, 0, 2, 'C');
         $lTop = $this->GetY()+5;
      }

      $lFS = 11;
      $this->SetFont('Arial', '', $lFS);

         // package description
      if ($gpdf->bs->bIncludePackageDesc){
//         $lTop += 2;
         $lTop = $this->incTopPos($lTop, 2);
         $this->SetXY($lTitleLeft, $lTop);
         $this->MultiCell(0, $lFS+1, $gpdf->package->strDescription, 0, 'L');
         $lTop = $this->GetY();
      }
      $lRightColBot = $lTop;
      if ($bImageSet && ($imageBottom < $lRightColBot)) $lTop = $imageBottom;

         // starting bid
      if ($gpdf->bs->bIncludeMinBid){
//         $lTop += 12;
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Starting Bid:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curMinBidAmnt, 2), '0', '1', 'R');
      }

         // bid increment
      if ($gpdf->bs->bIncludeMinBidInc){
//         $lTop += 12;
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Minimum Bid Increment:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curMinBidInc, 2), '0', '1', 'R');
      }

         // buy it now
      if ($gpdf->bs->bIncludeBuyItNow && !is_null($gpdf->package->curBuyItNowAmnt)){
//         $lTop += 12;
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Buy it now for:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curBuyItNowAmnt, 2), '0', '1', 'R');
      }

         // estimated value
      if ($gpdf->bs->bIncludePackageEstValue){
//         $lTop += 12;
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Estimated Value:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curEstValue, 2), '0', '1', 'R');
      }

         // reserve amount
      if ($gpdf->bs->bIncludeReserve){
//         $lTop += 12;
         $lTop = $this->incTopPos($lTop, 12);
         $this->SetXY($gpdf->lLeftMargin, $lTop);
         $this->Cell(0, 0, 'Reserve Amount:');
         $this->SetXY($gpdf->lLeftMargin+140, $lTop);
         $this->Cell(50, 0, $strCur.' '.number_format($gpdf->package->curReserveAmnt, 2), '0', '1', 'R');
      }
      $lTop += 20;
 //     }else {
 //        $lTop = $gpdf->lTopMargin;
 //     }
      $lTop += 20;
      if ($imageBottom > $lTop) $lTop = $imageBottom;   // case where the image extends lower than the other details
      if ($lRightColBot > $lTop) $lTop = $lRightColBot + 25;
      $this->addSignUpColumns($lTop);
   }

   function addSignUpColumns($lTop){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gpdf;

         // user can optionally not include signup entries
      if (!$gpdf->bs->bIncludeSignup) return;

      $signupLeft  = array($gpdf->lLeftMargin, $gpdf->lLeftMargin+150, $gpdf->lLeftMargin+400);
      $signupWidth = array(130,
                           200,
                           $gpdf->lPageWidth - ($gpdf->lLeftMargin+$gpdf->lRightMargin+300));
      $lBottom = $gpdf->lPageHeight - $gpdf->lBottomMargin;

      if ($lTop > ($lBottom-30)){
         $this->AddPage();
         $lTop = $gpdf->lTopMargin;
      }

         // sign-up sheet headings
      $lPageWidth = $gpdf->lPageWidth - ($gpdf->lLeftMargin + $gpdf->lRightMargin);
      $lLeft = $gpdf->lLeftMargin;
      $this->SetFont('Arial', '', 11);
      if ($lTop < $lBottom){
         foreach ($gpdf->bs->signUpCols as $suCol){
            if ($suCol->bShow){
               $this->SetXY($lLeft, $lTop);
               $this->Cell(0, 0, $suCol->heading);
               $lZoneWidth = $lPageWidth * ($suCol->width/100);
               $suCol->left = $lLeft + 4;
               $lLeft = $lLeft + $lZoneWidth;
               $suCol->lineWidth = $lZoneWidth - 15;
            }
         }
         $lTop += 25;
      }

      $this->SetLineWidth(1);
      while ($lTop < $lBottom){
         foreach ($gpdf->bs->signUpCols as $suCol){
            if ($suCol->bShow){
               $this->Line($suCol->left, $lTop, $suCol->left+$suCol->lineWidth, $lTop);
            }
         }
         $lTop += 25;
      }
   }

      // Page footer
   function Footer(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gpdf;
      if ($gpdf->bs->bIncludeFooter)
            // Position at 40pt from bottom
         $this->SetXY($gpdf->lLeftMargin, -40);
         $lWidth = $gpdf->lPageWidth - ($gpdf->lLeftMargin + $gpdf->lRightMargin);

            // Arial italic 9
         $this->SetFont('Arial','I',9);

         $strPName = 'package ID: '.str_pad($gpdf->package->lKeyID, 5, '0', STR_PAD_LEFT)
                       ."\n".'Page '.$this->PageNo();

            // Page number
         $this->MultiCell($lWidth, 12, $strPName.'/{nb}', 0, 'C');
//         $this->Cell(0, 10, $strPName.'/{nb}',0,0,'C');
   }


}

class mbid_sheet_pdf extends CI_Model{

   public
       $strWhereExtra;

   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->lNumBidSheets = $this->bidSheets = null;
      $this->strWhereExtra = '';
   }

}
