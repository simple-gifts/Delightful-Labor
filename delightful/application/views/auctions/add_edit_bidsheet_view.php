<?php
   global $genumDateFormat;

   $attributes = array('name' => 'frmEditAuction', 'id' => 'frmAddEdit');
   echoT(form_open('auctions/bid_templates/addEditTemplate/'.$lBSID
                               .'/'.$lTemplateID.'/'.$lAuctionID, $attributes));

   $clsForm = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 160px;';
   $attributes = new stdClass;
   $attributes->lTableWidth      = 900;

   openBlock('Auction Bid Sheet', '', $attributes); echoT('<table border="0">');   
   
      // Record ID
   echoT($clsForm->strLabelRow('Bid Sheet ID',
               ($bNew ? '<i>new</i>' : str_pad($lBSID, 5, '0', STR_PAD_LEFT)), 1));
               
      // Auction
   echoT($clsForm->strLabelRow('Auction',
               htmlspecialchars($bs->strAuctionName).'&nbsp;('
              .date($genumDateFormat, $bs->dteAuction).')', 1));

      // Template Style
   $strTempStyleTable =
       '<table border="0">'
          .'<tr>'
             .'<td style="vertical-align: top;">'.$tInfo->strThumbImgLink.'</td>'
             .'<td style="vertical-align: top;">'.$tInfo->htmlInfo.'</td></tr></table>';
   echoT($clsForm->strLabelRow('Template Style', $strTempStyleTable, 1));
               
               
      // Bidsheet Name
   $clsForm->strStyleExtraLabel = 'padding-top: 8px;';
   $clsForm->strExtraFieldText = form_error('txtBSName');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Bid Sheet Name', 'txtBSName', true, $formData->txtBSName, 40, 255));

      // Paper Size
   $clsForm->strExtraFieldText = form_error('ddlPaperSize');
   echoT($clsForm->strLabelRow('Paper Size*', $strPaperSizeDDL, 1));

      // Extra Sign-up Sheets
   $clsForm->strExtraFieldText = form_error('ddlExtraSheets');
   echoT($clsForm->strLabelRow('Extra Sign-up Sheets', $strExtraSheetsDDL, 1));

      // Signup Sheet Columns
   $clsForm->strExtraFieldText = form_error('txtSUSCol1');
   $clsForm->strStyleExtraLabel = 'padding-top: 2px;';
   $strColTable =
       'You can specify up to four columns for your sign-up sheets.<br>
        Leave the column heading blank to remove the column.<br><br>
        The widths need to add up to 100%<br>
        <table cellpadding="0" cellspacing="0">
          <tr>
             <td>
                <b>Column Heading</b>
             </td>
             <td>
                <b>&nbsp;&nbsp;Width (%)</b>
             </td>
          </tr>';
   $idx = 1;
   foreach ($bs->signUpCols as $suCol){
      $strColTable .= 
          '<tr>
              <td>
                  <input type="text" name="txtSUSCol'.$idx.'"   value="'.$signUpCols[$idx]->heading.'" size="20" maxlength="20">
              </td>
              <td>
                  <input type="text" style="text-align: right;" 
                       name="txtSUSColWidth'.$idx.'" value="'.$signUpCols[$idx]->width.'" 
                       size="3"  maxlength="4">%
               </td>
            </tr>';
      ++$idx;
   }
   $strColTable .= '
       </table>';
   echoT($clsForm->strLabelRow('Signup Sheet Columns', $strColTable, 1));
      
   
   
      // Notes
   $clsForm->strStyleExtraLabel = 'padding-top: 8px;';
   echoT($clsForm->strNotesEntry('Notes', 'txtDesc', false, $formData->txtDesc, 3, 50));

      // Logo image
   if ($formData->bShowIncludeOrgLogo){
      if ($lNumLogoImages == 0){
         $clsForm->strStyleExtraLabel = 'padding-top: 2px;';
         echoT($clsForm->strLabelRow('Logo Image', 'You have no logo images defined for <br>'
                                  .'your auction. You can add logos from the '
                                  .strLinkView_AuctionRecord($lAuctionID, 'auction record', false).'.', 1));
      }else {
         echoT($clsForm->strLabelRow('Logo Image', $formData->rdoLogo, 1));
      }
   }
   
   showIncludeCheck($clsForm, $formData->bShowIncludeOrgName,         $formData->bIncludeOrgName,         'Show Organization Name?',   'chkIncludeOrgName');
   showIncludeCheck($clsForm, $formData->bShowIncludeMinBid,          $formData->bIncludeMinBid,          'Show Min Bid Amount?',      'chkIncludeMinBid');
   showIncludeCheck($clsForm, $formData->bShowIncludeMinBidInc,       $formData->bIncludeMinBidInc,       'Show Min Bid Increment?',   'chkIncludeMinBidInc');
   showIncludeCheck($clsForm, $formData->bShowIncludeBuyItNow,        $formData->bIncludeBuyItNow,        'Show "Buy It Now"?',        'chkIncludeBuyItNow');
   showIncludeCheck($clsForm, $formData->bShowIncludeReserve,         $formData->bIncludeReserve,         'Show Reserve Amount?',      'chkIncludeReserve');
   showIncludeCheck($clsForm, $formData->bShowIncludeDate,            $formData->bIncludeDate,            'Show Auction Date?',        'chkIncludeDate');
   showIncludeCheck($clsForm, $formData->bShowIncludeFooter,          $formData->bIncludeFooter,          'Show Footer?',              'chkIncludeFooter');
   
   showIncludeCheck($clsForm, $formData->bShowIncludePackageName,     $formData->bIncludePackageName,     'Show Package Name?',        'chkIncludePkgName');
   showIncludeCheck($clsForm, $formData->bShowIncludePackageID,       $formData->bIncludePackageID,       'Show Package ID?',          'chkIncludePkgID');
   showIncludeCheck($clsForm, $formData->bShowIncludePackageDesc,     $formData->bIncludePackageDesc,     'Show Package Description?', 'chkIncludePkgDesc');
   showIncludeCheck($clsForm, $formData->bShowIncludePackageImage,    $formData->bIncludePackageImage,    'Show Package Image?',       'chkIncludePkgImage');
   showIncludeCheck($clsForm, $formData->bShowIncludePackageEstValue, $formData->bIncludePackageEstValue, 'Show Package Est. Value?',  'chkIncludePkgEstValue');

   showIncludeCheck($clsForm, $formData->bShowIncludeItemName,        $formData->bIncludeItemName,        'Show Item Name?',           'chkIncludeItemName');
   showIncludeCheck($clsForm, $formData->bShowIncludeItemID,          $formData->bIncludeItemID,          'Show Item ID?',             'chkIncludeItemID');
   showIncludeCheck($clsForm, $formData->bShowIncludeItemDesc,        $formData->bIncludeItemDesc,        'Show Item Description?',    'chkIncludeItemDesc');
   showIncludeCheck($clsForm, $formData->bShowIncludeItemImage,       $formData->bIncludeItemImage,       'Show Item Image?',          'chkIncludeItemImage');
   showIncludeCheck($clsForm, $formData->bShowIncludeItemDonor,       $formData->bIncludeItemDonor,       'Show Item Donor?',          'chkIncludeItemDonor');
   showIncludeCheck($clsForm, $formData->bShowIncludeItemEstValue,    $formData->bIncludeItemEstValue,    'Show Item Est. Value?',     'chkIncludeItemEstValue');
   showIncludeCheck($clsForm, $formData->bShowIncludeSignup,          $formData->bIncludeSignup,          'Include signup table?',     'chkIncludeSignup');
  
      //------------------------------------------
      // Save / Close form
      //------------------------------------------
   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'width: 90pt;'));
   echoT(form_close());
   echoT('<script type="text/javascript">frmEditAuction.addEditEntry.focus();</script>');

   echoT('</table>'); closeBlock(); 

   function  showIncludeCheck(&$clsForm, $bShow, $bChecked, $strLabel, $strCheckFN){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!$bShow) return;
      $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
      echoT($clsForm->strGenericCheckEntry($strLabel, $strCheckFN, 'true', false, $bChecked));
   }

   
   
   
   