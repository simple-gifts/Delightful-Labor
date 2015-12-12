<?php
   $clsDateTime = new dl_date_time;
   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '130pt';
   

   showGiftInfo           ($clsRpt, $lGiftID, $lFID, $bBiz, $gifts, $people, $biz, $bSponPayment);
   showGiftContactInfo    ($clsRpt, $lGiftID, $lFID, $bBiz, $people, $biz, $strDonor);
   showGiftHonMemInfo     ($clsRpt, $lGiftID, $lFID, $bBiz, $lNumHonMem, $honMemTable);
   if ($bSponPayment){
      showGiftSponsorInfo($clsRpt, $lGiftID, $lFID, $gifts, $spon);
   }
   showCustomGiftTableInfo($strPT, $lNumPTablesAvail);
//   showReminderBlock      ($clsRem, $lGiftID, CENUM_CONTEXT_GIFT);
   showENPGiftStats       ($clsRpt, $gifts);   
   
function showGiftSponsorInfo(&$clsRpt, $lGiftID, $lFID, $gifts, &$spon){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
   $bMismo = $lFID==$spon->lForeignID;
   $lSponID = $spon->lKeyID;
   $lClientID = $spon->lClientID;
   if (is_null($lClientID)){
      $strClientID = '<i>not set</i>';
   }else {
      $strClientID = str_pad($lClientID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                    .strLinkView_ClientRecord($lClientID, 'View client record', true).'&nbsp;'
                    .$spon->strClientSafeNameFL;
   }
   openBlock('Sponsorship'."\n",
               strLinkView_Sponsorship($lSponID, 'View sponsorship', true).'&nbsp;'
              .strLinkView_Sponsorship($lSponID, 'View sponsorship', false)."\n"
              );
   echoT(
       $clsRpt->openReport());
       
   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Sponsor ID:')
      .$clsRpt->writeCell (str_pad($spon->lKeyID, 5, '0', STR_PAD_LEFT))
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Sponsor:')
      .$clsRpt->writeCell ($spon->strSponSafeNameLF.($bMismo ? ' (same as donor)' : ''))
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Sponsorship program:')
      .$clsRpt->writeCell (htmlspecialchars($spon->strSponProgram))
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Client:')
      .$clsRpt->writeCell ($strClientID)
      .$clsRpt->closeRow  ());


   echoT(
      $clsRpt->closeReport(''));
   closeBlock();
   
}
     
function showGiftInfo(&$clsRpt, $lGID, $lFID, $bBiz, &$gifts, $people, $biz, $bSponPayment){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;
   
      // deposit info
   $lDepositID = $gifts->lDepositLogID;
   if (is_null($lDepositID)){
      $strDeposit = 'n/a';
   }else {
      $strDeposit =
          strLinkView_DepositEntry($lDepositID, 'View deposit', true)
         .str_pad($lDepositID, 5, '0', STR_PAD_LEFT).'&nbsp;&nbsp;';
      if ($gifts->strDepositBank.'' != ''){
         $strDeposit .= $gifts->strDepositBank;
      }
      if ($gifts->strDepositAccount.'' != ''){
         if ($gifts->strDepositBank.'' != ''){
            $strDeposit .= ' / ';
         }      
         $strDeposit .= $gifts->strDepositAccount;
      }
      $strDeposit .= ' (created '.date($genumDateFormat, $gifts->dteDeposit).')';
   }
   
      // pledge info
   if (is_null($gifts->lPledgeID)){
      $strPledge = 'n/a';
   }else {
      $strPledge = 'pledgeID '.str_pad($gifts->lPledgeID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkView_Pledge($gifts->lPledgeID, 'View pledge record', true);
   }

   openBlock(($bSponPayment ? 'Sponsorship Payment' : 'Gift')."\n",
               strLinkEdit_GiftRecord($lGID, 'Edit gift record', true)."\n"
              .'&nbsp;&nbsp;&nbsp;'."\n"
              .strLinkAdd_Gift($lFID, 'Add new gift', true)."\n"
              .'&nbsp;&nbsp;&nbsp;'."\n"
              .strLinkView_GiftsHistory($lFID, 'View donor\'s gift history', true).'&nbsp;'
              .strLinkView_GiftsHistory($lFID, 'Donor\'s gift history', false)."\n"
              .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'."\n"
              .strLinkRem_Gift($lGID, 'Remove this gift record', true, true)."\n\n");

   echoT(
       $clsRpt->openReport());
   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Gift ID:')
      .$clsRpt->writeCell (str_pad($lGID, 5, '0', STR_PAD_LEFT), '', '', 1, 1, ' id="gift_id" ')
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Amount:')
      .$clsRpt->writeCell ($gifts->strFormattedAmnt, '', '', 1, 1, ' id="gift_amount" ')
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Date:')
      .$clsRpt->writeCell (date($genumDateFormat, $gifts->gi_dteDonation), '', '', 1, 1, ' id="gift_date" ')
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Account:')
      .$clsRpt->writeCell (htmlspecialchars($gifts->ga_strAccount), '', '', 1, 1, ' id="gift_acct" ')
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Campaign:')
      .$clsRpt->writeCell (htmlspecialchars($gifts->gc_strCampaign), '', '', 1, 1, ' id="gift_camp" ')
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Check #:')
      .$clsRpt->writeCell (htmlspecialchars($gifts->gi_strCheckNum), '', '', 1, 1, ' id="gift_cn" ')
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Pledge:')
      .$clsRpt->writeCell ($strPledge, '', '', 1, 1, ' id="gift_pledge" ')
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Payment Type:')
      .$clsRpt->writeCell (htmlspecialchars($gifts->strPaymentType), '', '', 1, 1, ' id="gift_pt" ')
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Deposit:')
      .$clsRpt->writeCell ($strDeposit, '', '', 1, 1, ' id="gift_dep" ')
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Gift Category:')
      .$clsRpt->writeCell (htmlspecialchars($gifts->strMajorGiftCat), '', '', 1, 1, ' id="gift_cat" ')
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Attributed To:')
      .$clsRpt->writeCell (htmlspecialchars($gifts->strAttribTo), '', '', 1, 1, ' id="gift_attrib" ')
      .$clsRpt->closeRow  ());
      
   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Notes:')
      .$clsRpt->writeCell (nl2br(htmlspecialchars($gifts->strNotes)), '', '', 1, 1, ' id="gift_notes" ')
      .$clsRpt->closeRow  ());

      //--------------------
      // in-kind
      //--------------------
   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('In-Kind?:'));
   if ($gifts->gi_bGIK){
      echoT($clsRpt->writeCell ('Yes - '.htmlspecialchars($gifts->strInKind), '', '', 1, 1, ' id="gift_ik" '));
   }else {
      echoT($clsRpt->writeCell ('No', '', '', 1, 1, ' id="gift_ik" '));
   }
   echoT(
      $clsRpt->closeRow  ());
      
      //--------------------
      // Acknowledged?
      //--------------------
   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Acknowledged?:', '', 'padding-top: 8px;'));
   if ($gifts->bAck){
      echoT($clsRpt->writeCell ('Yes - '.date($genumDateFormat, $gifts->dteAck)
                        .' '.strLinkSpecial_GiftChangeAck($lGID, false, 'Set to unacknowledged', 
                        true), '', 'padding-top: 8px;', 1, 1, ' id="gift_ack" '));
   }else {
      echoT($clsRpt->writeCell ('No '
                         .strLinkSpecial_GiftChangeAck($lGID, true, 'Set to acknowledged', 
                         true), '', 'padding-top: 8px;', 1, 1, ' id="gift_ack" '));
   }
   echoT(
      $clsRpt->closeRow  ());

   echoT(
      $clsRpt->closeReport(''));
   closeBlock();
}   

function showGiftContactInfo($clsRpt, $lGID, $lFID, $bBiz, $clsPeopleRec, $clsBiz, $strDonor){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if ($bBiz){
      $strLink  = strLinkView_BizRecord($lFID, 'View business/organization record', true);
      $strAddr  = $clsBiz->strAddress;
      $strPhone = $clsBiz->strPhone;
   }else {
      $strLink  = strLinkView_PeopleRecord($lFID, 'View people record', true);
      $strAddr  = $clsPeopleRec->strAddress;
      $strPhone = $clsPeopleRec->strPhone;
   }

   openBlock('Donor', '');
   echoT(
       $clsRpt->openReport()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Donor:')
      .$clsRpt->writeCell ($strLink.' '.$strDonor)
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Donor Type:')
      .$clsRpt->writeCell ($bBiz ? 'Business/Organization' : 'Individual')
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Address:')
      .$clsRpt->writeCell ($strAddr)
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Phone:')
      .$clsRpt->writeCell ($strPhone)
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->closeReport(''));
   closeBlock();
}

function showGiftHonMemInfo($clsRpt, $lGID, $lFID, $bBiz, $lNumHonMem, &$honMemTable){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'hmDiv';
   $attributes->divImageID   = 'hmDivImg';

   openBlock('Honorariums and Memorials <span style="font-size: 9pt;">('.$lNumHonMem.')</span>',
                      strLinkAdd_GiftHonMem($lGID, true,  'Add Honorarium', true).'&nbsp;'
                     .strLinkAdd_GiftHonMem($lGID, true,  'Add Honorarium', false)
                     .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                     .strLinkAdd_GiftHonMem($lGID, false, 'Add Memorial', true).'&nbsp;'
                     .strLinkAdd_GiftHonMem($lGID, false, 'Add Memorial', false),
             $attributes);

   if ($lNumHonMem==0){
      echoT('<i>This gift is not associated with any honorariums or memorials</i><br>');
   }else {
      writeHonMemGiftsTable($lGID, $clsRpt, $honMemTable);
   }
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function writeHonMemGiftsTable($lGiftID, $clsRpt, $honMemTable){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gbAdmin;

   echoT($clsRpt->openReport());

   foreach ($honMemTable as $clsHM){
      echoT($clsRpt->openRow   ());
      $bHon    = $clsHM->ghm_bHon;
      $strCell = strLinkView_PeopleRecord($clsHM->ghm_lFID, 'View people record', true).' '
                .strLinkRem_HonMemLink($clsHM->lHMLinkID, $lGiftID, $bHon, 'Remove '.($bHon ? 'honorarium' : 'memorial'), true, true).' '
                .$clsHM->honorMem->strSafeNameFL;

      if ($bHon){
         echoT($clsRpt->writeLabel('Honorarium:'));
         echoT($clsRpt->writeCell ($strCell));
      }else {
         $lMCID = $clsHM->ghm_lMailContactID;
         if (is_null($lMCID)){
            $strCell .= ' <font color="red"><i>(Mail contact not set!';
            if ($gbAdmin){
               $strCell .= ' Click '
                          .strLinkView_ListHonMem(false, 'here', false)
                          .' to work with memorials.';
            }
            $strCell .= ')</i></font>';
         }else {
            $strCell .= ' (mail contact: '
                       .$clsHM->mailContact->strSafeNameFL
                       .strLinkView_PeopleRecord($lMCID, 'View people record for the mail contact', true).' '
                       .')';
         }
         echoT($clsRpt->writeLabel('Memorial:'));
         echoT($clsRpt->writeCell($strCell, '', 'vertical-align: text-top;'));
      }
      echoT($clsRpt->closeRow  ());
   }
   echoT($clsRpt->closeReport('<br>'));
}

function showCustomGiftTableInfo($strPT, $lNumPTablesAvail){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'gptDiv';
   $attributes->divImageID   = 'gptDivImg';
   $attributes->bStartOpen   = false;
   openBlock('Personalized Tables <span style="font-size: 9pt;">('.$lNumPTablesAvail.')</span>', '', $attributes);

   echoT($strPT);
   
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showENPGiftStats(&$clsRpt, &$gifts){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'grecDiv';
   $attributes->divImageID   = 'grecDivImg';
   openBlock('Record Information', '', $attributes);
   echoT(
      $clsRpt->showRecordStats($gifts->dteOrigin, 
                            $gifts->strStaffCFName.' '.$gifts->strStaffCLName, 
                            $gifts->dteLastUpdate, 
                            $gifts->strStaffLFName.' '.$gifts->strStaffLLName, 
                            $clsRpt->strWidthLabel));
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}
   

