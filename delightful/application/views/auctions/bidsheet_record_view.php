<?php

   global $genumDateFormat;

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '190pt';
   $clsRpt->bValueEscapeHTML = false;

   $BSID = $bs->lKeyID;
   showBidSheetInfo ($clsRpt, $bs, $template, $BSID);
   showBidSheetENPStats($clsRpt, $bs);

   function showBidSheetInfo (&$clsRpt, &$bs, &$template, $BSID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumMeasurePref;

      openBlock('Bid Sheet for <b>'.htmlspecialchars($bs->strAuctionName).'</b>',
                   strLinkEdit_BidSheet($BSID, 'Edit bid sheet', true)
                  .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                  .strLinkRem_BidSheet($BSID, 'Remove bid sheet', true, true));
                  
      echoT(
          $clsRpt->openReport());

         // bidSheet ID
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Bid Sheet ID:')
         .$clsRpt->writeCell (str_pad($bs->lKeyID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ());

         // name
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Name:')
         .$clsRpt->writeCell (htmlspecialchars($bs->strSheetName))
         .$clsRpt->closeRow  ());

         // auction
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Auction:')
         .$clsRpt->writeCell (htmlspecialchars($bs->strAuctionName).'&nbsp;'
                  .strLinkView_AuctionRecord($bs->lAuctionID, 'View auction record', true))
         .$clsRpt->closeRow  ());

         // description
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Description:')
         .$clsRpt->writeCell (nl2br(htmlspecialchars($bs->strDescription)))
         .$clsRpt->closeRow  ());

         // template
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Template:')
         .$clsRpt->writeCell (
                   $bs->tInfo->title
                  .'<br>'.$bs->tInfo->strThumbImgLink)
         .$clsRpt->closeRow  ());

         // paper type
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Paper Type:')
         .$clsRpt->writeCell (strXlatePaperSize($bs->enumPaperType, $genumMeasurePref=='metric'))
         .$clsRpt->closeRow  ());

         // # Extra Signup Sheets
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('# Extra Signup Sheets:')
         .$clsRpt->writeCell ($bs->lNumSignupPages)
         .$clsRpt->closeRow  ());

         // signup sheet columns
      echoT('
              <tr>
                 <td class="enpViewLabel"  >Signup Sheet Columns:</td>
                 <td class="enpView" >
                    <table cellpadding="0">
                       <tr>
                          <td style="vertical-align: top; width: 100pt;"><b>Column Heading</b></td>
                          <td><b>Width</b></td>
                       </tr>');
      for ($idx=1; $idx<=$bs->lNumSignupCols; ++$idx){
         $suCols = &$bs->signUpCols[$idx];
         echoT('<tr><td>'.htmlspecialchars($suCols->heading).'</td>
                    <td style="text-align: right;">'.$suCols->width.' %</td></tr>');
      }
      echoT('                 
                     </table>
                 </td>
              </tr>');

         // Logo
      if ($template->bShowIncludeOrgLogo){
         if ($bs->bIncludeOrgLogo){
            echoT(
                $clsRpt->openRow   ()
               .$clsRpt->writeLabel('Logo:')
               .$clsRpt->writeCell ($bs->strLogoImgLink.$bs->strLogoImgTN.'</a>')
               .$clsRpt->closeRow  ());
         }else {
            echoT(
                $clsRpt->openRow   ()
               .$clsRpt->writeLabel('Include Logo?:')
               .$clsRpt->writeCell ('No')
               .$clsRpt->closeRow  ());
         }
      }

      yesNoIncludes($clsRpt, $template->bShowIncludeSignup,          $bs->bIncludeSignup,          'Include signup table?:');
      yesNoIncludes($clsRpt, $template->bShowIncludeOrgName,         $bs->bIncludeOrgName,         'Include Organization Name?:');
      yesNoIncludes($clsRpt, $template->bShowIncludeMinBid,          $bs->bIncludeMinBid,          'Include Minimum Bid?:');
      yesNoIncludes($clsRpt, $template->bShowIncludeMinBidInc,       $bs->bIncludeMinBidInc,       'Include Min. Bid Increment?:');
      yesNoIncludes($clsRpt, $template->bShowIncludeBuyItNow,        $bs->bIncludeBuyItNow,        'Include "Buy It Now"?:');
      yesNoIncludes($clsRpt, $template->bShowIncludeReserve,         $bs->bIncludeReserve,         'Include Reserve?:');
      yesNoIncludes($clsRpt, $template->bShowIncludeDate,            $bs->bIncludeDate,            'Include Date?:');
      yesNoIncludes($clsRpt, $template->bShowIncludeFooter,          $bs->bIncludeFooter,          'Include Footer?:');

      yesNoIncludes($clsRpt, $template->bShowIncludePackageName,     $bs->bIncludePackageName,     'Include Package Name?:');
      yesNoIncludes($clsRpt, $template->bShowIncludePackageID,       $bs->bIncludePackageID,       'Include Package ID?:');
      yesNoIncludes($clsRpt, $template->bShowIncludePackageDesc,     $bs->bIncludePackageDesc,     'Include Package Description?:');
      yesNoIncludes($clsRpt, $template->bShowIncludePackageImage,    $bs->bIncludePackageImage,    'Include Package Image?:');
      yesNoIncludes($clsRpt, $template->bShowIncludePackageEstValue, $bs->bIncludePackageEstValue, 'Include Package Est. Value?:');

      yesNoIncludes($clsRpt, $template->bShowIncludeItemName,        $bs->bIncludeItemName,        'Include Item Names?:');
      yesNoIncludes($clsRpt, $template->bShowIncludeItemID,          $bs->bIncludeItemID,          'Include Item IDs?:');
      yesNoIncludes($clsRpt, $template->bShowIncludeItemDesc,        $bs->bIncludeItemDesc,        'Include Item Descriptions?:');
      yesNoIncludes($clsRpt, $template->bShowIncludeItemImage,       $bs->bIncludeItemImage,       'Include Item Images?:');
      yesNoIncludes($clsRpt, $template->bShowIncludeItemDonor,       $bs->bIncludeItemDonor,       'Include Item Donors?:');
      yesNoIncludes($clsRpt, $template->bShowIncludeItemEstValue,    $bs->bIncludeItemEstValue,    'Include Item Est. Values?:');

      echoT($clsRpt->closeReport());
      closeBlock();
   }

   function yesNoIncludes(&$clsRpt, $bShowInclude, $bInclude, $strLabel){
      if ($bShowInclude){
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel($strLabel)
            .$clsRpt->writeCell (($bInclude ? 'Yes' : 'No'))
            .$clsRpt->closeRow  ());
      }
   }

   function showBidSheetENPStats (&$clsRpt, &$bs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->divID        = 'bsENPStats';
      $attributes->divImageID   = 'bsENPStatsDivImg';
      openBlock('Record Information', '', $attributes);
      echoT(
         $clsRpt->showRecordStats($bs->dteOrigin,
                               $bs->strCFName.' '.$bs->strCLName,
                               $bs->dteLastUpdate,
                               $bs->strLFName.' '.$bs->strLLName,
                               $clsRpt->strWidthLabel));
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }




