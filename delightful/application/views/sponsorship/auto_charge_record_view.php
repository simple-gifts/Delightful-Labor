<?php

   $dteAutoCharge = strtotime($lMonth.'/1/'.$lYear);
   
   $attributes = new stdClass;
   $attributes->lTableWidth      = '100%';
   $attributes->lUnderscoreWidth = 500;
   
   openBlock('Autocharges for '.date('F Y', $dteAutoCharge)
                  .' were applied to the following sponsorships:', 
                '', $attributes);

   
   if ($lNumAutoCharges > 0){
      openAutoChargeInfo();
      foreach ($autoChargeInfo as $clsACInfo){
         writeAutoChargeRow($clsACInfo);
      }
      closeAutoChargeInfo();
   }else {
      echoT('There were no autocharges made for this month.<br><br>');
   }
   
   closeBlock();
   
function writeAutoChargeRow($clsACInfo){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $lCID      = $clsACInfo->lKeyID;
   $lSponID   = $clsACInfo->lSponsorshipID;
   $bBiz      = $clsACInfo->bBiz;
   $lFID      = $clsACInfo->lForeignID;
   $lClientID = $clsACInfo->lClientID;
   
   if (is_null($lClientID)){
      $strClientRow = 'n/a';
   }else {
      $strClientRow = strLinkView_ClientRecord($lClientID, 'View Client', true).' '
         .str_pad($lClientID, 5, '0', STR_PAD_LEFT).' '
         .$clsACInfo->strClientSafeNameFL.' / '
         .htmlspecialchars($clsACInfo->strLocation);
   }
   
   if ($bBiz){
      $strFLink = strLinkView_BizRecord($lFID, 'View business record', true);
   }else {
      $strFLink = strLinkView_PeopleRecord($lFID, 'View people record', true);
   }
   
   echoT('
      <tr>
         <td class="enpRpt" style="text-align:center">'
            .strLinkView_SponsorCharge($lCID, 'View charge record', true).'&nbsp;'
            .str_pad($lCID, 5, '0', STR_PAD_LEFT).'
         </td>');

   echoT('
         <td class="enpRpt" style="text-align:center">'
            .strLinkView_Sponsorship($lSponID, 'View sponsorship record', true).'&nbsp;'
            .str_pad($lSponID, 5, '0', STR_PAD_LEFT).'
         </td>');

      //------------------------------
      // sponsor
      //------------------------------
   echoT('
         <td class="enpRpt">'
            .$strFLink.'&nbsp;'
            .$clsACInfo->strSponSafeNameFL.'
         </td>');

      //------------------------------
      // charge
      //------------------------------
   echoT('
         <td class="enpRpt" style="text-align: right;">'
            .$clsACInfo->strCurSymbol.'&nbsp;'
            .number_format($clsACInfo->curChargeAmnt, 2).'&nbsp;'
            .$clsACInfo->strFlagImage.'
         </td>');

      //------------------------------
      // client
      //------------------------------
   echoT('
         <td class="enpRpt">'
            .$strClientRow.'
         </td>');

   echoT('
      </tr>');
         
}            
   
function openAutoChargeInfo(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT('<br>
      <table class="enpRptC">
         <tr>
            <td class="enpRptLabel">
               ChargeID
            </td>
            <td class="enpRptLabel">
               SponsorID
            </td>
            <td class="enpRptLabel">
               Sponsor
            </td>
            <td class="enpRptLabel">
               Amount
            </td>
            <td class="enpRptLabel">
               Client
            </td>
         </tr>
   ');
}

function closeAutoChargeInfo(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT('</table>');
}   








