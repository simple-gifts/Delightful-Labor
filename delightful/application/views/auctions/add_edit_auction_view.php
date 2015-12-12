<?php
   global $genumDateFormat;

   $attributes = array('name' => 'frmEditAuction', 'id' => 'frmAddEdit');
   echoT(form_open('auctions/auction_add_edit/addEditAuction/'.$lAuctionID, $attributes));

   $clsForm = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 100pt;';

   openBlock('Silent Auction', ''); echoT('<table>');


      //-------------------------------
      // Record ID
      //-------------------------------
   echoT($clsForm->strLabelRow('Auction ID',
               ($bNew ? '<i>new</i>' : str_pad($lAuctionID, 5, '0', STR_PAD_LEFT)), 1));

      //-------------------------------
      // Auction Name
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 4pt; ';
   $clsForm->strExtraFieldText = form_error('txtAuctionName');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Auction Name', 'txtAuctionName', true, $formData->txtAuctionName, 40, 255));

      //-------------------------------
      // Date of auction
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtADate');
   echoT($clsForm->strGenericDatePicker(
                      'Date of Auction',    'txtADate',      true,
                      $formData->txtADate,
                      'frmEditAuction', 'datepickerFuture'));

      //-------------------------------
      // Contact
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtContact');
   echoT($clsForm->strGenericTextEntry('Contact', 'txtContact', false, $formData->txtContact, 40, 255));

      //-------------------------------
      // Email
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtEmail');
   echoT($clsForm->strGenericTextEntry('Email', 'txtEmail', false, $formData->txtEmail, 40, 200));

      //-------------------------------
      // Phone
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtPhone');
   echoT($clsForm->strGenericTextEntry('Phone', 'txtPhone', false, $formData->txtPhone, 40, 80));

      //-------------------------------
      // Location
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtLocation');
   echoT($clsForm->strNotesEntry('Location', 'txtLocation', false, $formData->txtLocation, 2, 40));

      //-------------------------------
      // Description
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 2pt; ';
   $clsForm->strExtraFieldText = form_error('txtDescription');
   echoT($clsForm->strNotesEntry('Description', 'txtDescription', false, $formData->txtDescription, 2, 40));

      //-------------------------------
      // Accounting country of Origin
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 8pt; ';
   echoT($clsForm->strLabelRow('Accounting Country', $formData->strACORadio, 1));

      //------------------------------------------
      // Accounts / Campaigns for winning bidders
      //------------------------------------------
   $clsForm->strExtraFieldText = ' (for gift records from winning bids)'.form_error('ddlAccount');
   $clsForm->strExtraSelect = ' onChange="showCampaigns(this.value, \'ddlCamps\')" ';
   $clsForm->strStyleExtraValue = 'vertical-align: middle; padding-top: 4pt;';
   echoT($clsForm->strGenericDDLEntry('Account', 'ddlAccount', true,  $formData->strDDLAccts));

   $clsForm->strExtraFieldText = ' (for gift records from winning bids)'.form_error('ddlCamps');
   $clsForm->strExtraSelect = ' ID="ddlCamps" ';
   $clsForm->strStyleExtraValue = 'vertical-align: middle;';
   echoT($clsForm->strGenericDDLEntry('Campaign', 'ddlCamps', true, $formData->strDDLCamps));
   $clsForm->strExtraSelect = '';

      //-------------------------------
      // Default Bid Sheet
      //-------------------------------
   if ($lNumBidSheets <= 0){
      $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 2pt; ';
      if ($bNew){
         echoT($clsForm->strLabelRow('Default Bid Sheet',
                  '<i>You will be able to create bid sheets after creating your new auction.</i>', 1));
      }else {
         echoT($clsForm->strLabelRow('Default Bid Sheet',
                  '<i>Not set!</i>&nbsp;&nbsp; Click '.strLinkView_BidSheets('here', false)
                  .' to work with bid sheet templates.</i>', 1));
      }
   }else {
      echoT($clsForm->strLabelRow('Default Bid Sheet', $ddlDefBidSheet, 1));
   }



      //------------------------------------------
      // Save / Close form
      //------------------------------------------
   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'width: 90pt;'));
   echoT(form_close());
   echoT('<script type="text/javascript">frmEditAuction.addEditEntry.focus();</script>');

   echoT('</table>'); closeBlock();

