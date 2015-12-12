<?php
   global $genumDateFormat;

   $attributes = array('name' => 'frmTemplate', 'id' => 'frmTemplate');
   echoT(form_open('auctions/bid_templates/addSheetTempSelect', $attributes));

   $clsForm = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 100pt;';

   openBlock('New Bid Sheet', ''); echoT('<table>');   
   
      //-------------------------------
      // Auction
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('ddlAuction');
   echoT($clsForm->strLabelRow('Auction', $strAuctionDDL, 1));
      
      //-------------------------------
      // Default Bid Sheet
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 4pt; ';
   echoT($clsForm->strLabelRow('Base bid sheet on', $strRadioTemplates, 1));      
   
      //------------------------------------------
      // Save / Close form
      //------------------------------------------
   echoT($clsForm->strSubmitEntry('Select and Configure', 2, 'cmdSubmit', 'width: 140pt;'));
   echoT(form_close());

   echoT('</table>'); closeBlock(); 
   
   
   
