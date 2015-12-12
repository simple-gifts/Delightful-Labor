<?php
echoT(strLinkAdd_GiftAcct('Add new donation account', true).' '
     .strLinkAdd_GiftAcct('Add new account', false).'<br><br>');

if ($lNumAccts == 0){
   echoT('<i>There are currently no accounts defined in your database!</i><br><br>');
}else {
   $params = array('enumStyle' => 'enpRptC');
   $clsRpt = new generic_rpt($params);
   echoT(
       $clsRpt->openReport(400)

      .$clsRpt->writeTitle('Donation Accounts', 500, '', 4)
      .$clsRpt->openRow(false)
      .$clsRpt->writeLabel('Acct ID',         '65',  'text-align: center; vertical-align: bottom;')
      .$clsRpt->writeLabel('Account Name',    '',    'text-align: center; vertical-align: bottom;')
      .$clsRpt->writeLabel('# Campaigns',     '30',  'text-align: center; vertical-align: bottom;')
      .$clsRpt->writeLabel('Total Donations', '130', 'text-align: center; vertical-align: bottom;')
      .$clsRpt->closeRow());

   $idx = 0;
   foreach($accts as $clsAcct){
      $lAID = $clsAcct->lKeyID;

      $clsRpt->openRow(true);
      if($clsAcct->bProtected){
         $strLinkEdit = '&nbsp;&nbsp;';
      }else {
         $strLinkEdit = strLinkEdit_GiftAcct($lAID, 'Edit account', true);
      }
      echoT(
         $clsRpt->writeCell(
                 $strLinkEdit.' '
                .str_pad($lAID, 5, '0', STR_PAD_LEFT),
                 '', $strStyleExtra='text-align: center;')
         .$clsRpt->writeCell(htmlspecialchars($clsAcct->strAccount))

         .$clsRpt->writeCell($clsAC->lNumCampsViaAcctID($lAID, false).' '
                        .strLinkView_GiftCampaigns($lAID, 'View campaigns', true).'&nbsp;&nbsp;',
                        '', 'text-align: right;')
         .$clsRpt->writeCell($strCumGiftsNonSoft[$idx].$strLinkGifts[$idx], '', 'text-align: left;')
         .$clsRpt->closeRow());
      ++$idx;
   }
   echoT($clsRpt->closeReport());
}

?>