<?php

   echoT(strLinkAdd_GiftCamp($lAcctID, 'Add new donation campaign', true).' '
        .strLinkAdd_GiftCamp($lAcctID, 'Add new campaign under "'.$strAcctName.'"', false).'<br><br>');

   echoT(
      $clsRpt->openReport(520)

      .$clsRpt->writeTitle('Donation Campaigns for acount "'.$strAcctName.'"', 520, '', 4)
      .$clsRpt->openRow(false)
      .$clsRpt->writeLabel('Campaign ID',     '65',  'text-align: center;')
      .$clsRpt->writeLabel('Name',            '200', 'text-align: center;')
      .$clsRpt->writeLabel('Total Donations', '110', 'text-align: center;')
      .$clsRpt->closeRow());

   $idx = 0;
   foreach($campaigns as $clsCamp){
      echoT($clsRpt->openRow(true));
      if($clsCamp->bProtected){
         $strLinkEdit = '&nbsp;&nbsp;';
      }else {
         $strLinkEdit = strLinkEdit_GiftCamp($lAcctID, $lCampID[$idx], 'Edit campaign', true).'&nbsp;&nbsp;'
                       .strLinkView_GiftCampaignsXfer($lCampID[$idx], 'Transfer to different account', true);
      }
      echoT(
          $clsRpt->writeCell(
                 $strLinkEdit.' '
                .str_pad($lCampID[$idx], 5, '0', STR_PAD_LEFT),
                 '95', $strStyleExtra='text-align: center;')
         .$clsRpt->writeCell($clsCamp->strSafeName)
         .$clsRpt->writeCell($strCumGiftsNonSoft[$idx], '',   'text-align: left;')
//         .$clsRpt->writeCell($strLinkGifts[$idx],       '90', 'text-align: left;')
         .$clsRpt->closeRow());
      ++$idx;
   }
   echoT($clsRpt->closeReport());


?>