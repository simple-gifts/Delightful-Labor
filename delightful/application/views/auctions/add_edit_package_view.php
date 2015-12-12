<?php
   global $genumDateFormat;

   $attributes = array('name' => 'frmEditPackage', 'id' => 'frmAddEdit');
   echoT(form_open('auctions/packages/addEditAuctionPackage/'.$lAuctionID.'/'.$lPackageID, $attributes));

   $clsForm = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 130pt;';

   openBlock('Silent Auction Package', ''); echoT('<table>');


      //-------------------------------
      // Record ID
      //-------------------------------
   echoT($clsForm->strLabelRow('Package ID',
               ($bNew ? '<i>new</i>' : str_pad($lPackageID, 5, '0', STR_PAD_LEFT)), 1));

      //-------------------------------
      // Package Name
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strExtraFieldText = form_error('txtPackageName');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Package Name', 'txtPackageName', true, $formData->txtPackageName, 40, 255));

      //-------------------------------
      // Public Notes
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtPublicNotes');
   echoT($clsForm->strNotesEntry('Public Notes', 'txtPublicNotes', false, $formData->txtPublicNotes, 2, 40));

      //-------------------------------
      // Private Notes
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtPrivateNotes');
   echoT($clsForm->strNotesEntry('Private Notes', 'txtPrivateNotes', false, $formData->txtPrivateNotes, 2, 40));

      //-------------------------------
      // Minimum Bid
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
   $clsForm->strTextBoxPrefix   = $auction->strCurrencySymbol;
   $clsForm->strStyleExtraValue = 'vertical-align: middle; ';
   $clsForm->strExtraFieldText = form_error('txtMinBid');
   echoT($clsForm->strGenericTextEntry('Minimum Bid', 'txtMinBid', true, $formData->txtMinBid, 8, 20));

      //-------------------------------
      // Minimum Bid Increment
      //-------------------------------
   $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
   $clsForm->strTextBoxPrefix   = $auction->strCurrencySymbol;
   $clsForm->strExtraFieldText = form_error('txtMinBidInc');
   echoT($clsForm->strGenericTextEntry('Minimum Bid Increment', 'txtMinBidInc', true, $formData->txtMinBidInc, 8, 20));

      //-------------------------------
      // "Buy it now" amount
      //-------------------------------
   $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
   $clsForm->strTextBoxPrefix   = $auction->strCurrencySymbol;
   $clsForm->strExtraFieldText = 
                ' <i>Leave blank or set to zero to exclude this package from "Buy It Now"</i>'.form_error('txtBuyItNowAmnt');
   echoT($clsForm->strGenericTextEntry('"Buy it now" amount', 'txtBuyItNowAmnt', true, $formData->txtBuyItNowAmnt, 8, 20));

      //-------------------------------
      // Reserve Amount
      //-------------------------------
   $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
   $clsForm->strTextBoxPrefix   = $auction->strCurrencySymbol;
   $clsForm->strExtraFieldText = form_error('txtReserve');
   echoT($clsForm->strGenericTextEntry('Reserve Amount', 'txtReserve', true, $formData->txtReserve, 8, 20));

      //-------------------------------
      // Bid Sheet
      //-------------------------------
   if ($lNumBidSheets <= 0){
      $clsForm->strStyleExtraLabel = 'padding-top: 3pt; ';
      echoT($clsForm->strLabelRow('Bid Sheet',
                  '<i>Not set!</i>&nbsp;&nbsp; Click '.strLinkView_BidSheets('here', false)
                  .' to work with bid sheet templates.', 1));
   }else {
      echoT($clsForm->strLabelRow('Bid Sheet', $ddlBidSheet, 1));
   }
   

      //------------------------------------------
      // Save / Close form
      //------------------------------------------
   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'width: 90pt;'));
   echoT(form_close());
   echoT('<script type="text/javascript">frmEditPackage.addEditEntry.focus();</script>');

   echoT('</table>'); closeBlock();



