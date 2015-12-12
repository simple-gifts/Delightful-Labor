<?php
   $attributes = array('name' => 'frmWinningBid', 'id' => 'winningBid');
   echoT(form_open('auctions/packages/addEditPackageWinner/'.$lPackageID.'/'.$lDonorID, $attributes));

   $clsForm = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 100pt;';

   openBlock('Auction Package Winner: <b>'.$package->strPackageSafeName.'</b>', ''); echoT('<table>');

   
      // Winner
   if ($pbInfo->bBiz){
      $strNameLink = ' <i>(business)</i> ';
   }else {
      $strNameLink = ' ';
   }
   echoT($clsForm->strLabelRow('Winning Bidder', 
         $pbInfo->strSafeNameFL.$strNameLink.$pbInfo->strLink, 1));
   
   
      //-------------------------------
      // Winning Amount
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strExtraFieldText = form_error('txtPackageName');
   $clsForm->strID = 'winnerAmnt';
   $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
   $clsForm->strTextBoxPrefix   = $auction->strCurrencySymbol;
   $clsForm->strStyleExtraValue = 'vertical-align: middle; ';
   $clsForm->strExtraFieldText = form_error('txtAmount');
   echoT($clsForm->strGenericTextEntry('Winning Amount', 'txtAmount', true, $formData->txtAmount, 8, 20));
   
/*   
      //-------------------------------
      // Payment Received?
      //-------------------------------
   $clsForm->strExtraFieldText = ' (if checked, a gift record will be created)';
   echoT($clsForm->strGenericCheckEntry('Payment Received?', 
                    'chkReceived', 'true', false, $formData->bReceived));
   
*/   
   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'width: 90pt;'));
   echoT(form_close());
   echoT('<script type="text/javascript">frmWinningBid.winnerAmnt.focus();</script>');
   
   echoT('</table>'); closeBlock(); 



   