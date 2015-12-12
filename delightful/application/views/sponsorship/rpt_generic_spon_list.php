<?php
   global $genumDateFormat, $gbAdmin;

   if ($lNumSponProgs <= 0) return;
   if ($lNumDisplayRows <= 0) return;

   $params = array('enumStyle' => 'enpRptC');
   $clsRpt = new generic_rpt($params);

   $lNumCols = 0;

   if ($showFields->bSponID          ) ++$lNumCols;
//   if ($showFields->bForeignID       ) ++$lNumCols;
   if ($showFields->bActiveInactive  ) ++$lNumCols;
   if ($showFields->bName            ) ++$lNumCols;
   if ($showFields->bAddress         ) ++$lNumCols;
//   if ($showFields->bPhoneEmail      ) ++$lNumCols;
//   if ($showFields->bProgram         ) ++$lNumCols;
   if ($showFields->bClient          ) ++$lNumCols;
   if ($showFields->bCommitment      ) ++$lNumCols;

   if ($lNumCols==0) {
      echoT('There are no sponsorship fields to report.');
   }else {
      echoT('<br>'.$clsRpt->openReport());
      echoT($clsRpt->writeTitle($strRptTitle, '', '', $lNumCols));
      echoT($clsRpt->openRow(true));
      if ($showFields->bSponID          ) echoT($clsRpt->writeLabel('SponsorID',  60));
//      if ($showFields->bForeignID       ) echoT($clsRpt->writeLabel('&nbsp;',     40));
      if ($showFields->bActiveInactive  ) echoT($clsRpt->writeLabel('Active',    90));
      if ($showFields->bName            ) echoT($clsRpt->writeLabel('Sponsor'));
      if ($showFields->bAddress         ) echoT($clsRpt->writeLabel('Address'));
//      if ($showFields->bPhoneEmail      ) echoT($clsRpt->writeLabel('Phone/Email'));
//      if ($showFields->bProgram         ) echoT($clsRpt->writeLabel('Sponsorship Prog.'));
      if ($showFields->bClient          ) echoT($clsRpt->writeLabel('Client', 210));
      if ($showFields->bCommitment      ) echoT($clsRpt->writeLabel('Commitment'));
      echoT($clsRpt->closeRow());
   }

   foreach ($sponInfo as $sponRec){
      $lSponsorID = $sponRec->lKeyID;
      $lFID       = $sponRec->lForeignID;

         //---------------------------
         // active state setup
         //---------------------------
      if ($showFields->bActiveInactive){
         if ($sponRec->bInactive){
            $strIStyle = 'color: #aaaaaa;';
            $strActiveDates = 'sponsor dates: '.date($genumDateFormat, $sponRec->dteStart).' - '
                                               .date($genumDateFormat, $sponRec->dteInactive);
         }else {
            $strIStyle = '';
            $strActiveDates = 'sponsor since: '.date($genumDateFormat, $sponRec->dteStart);
         }
      }else {
         $strIStyle = '';
      }

      echoT('
         <tr class="makeStripe">');

         //----------------------
         // sponsor ID
         //----------------------
      if ($showFields->bSponID){
         echoT('
             <td class="enpRpt" style="'.$strIStyle.'">'
                .strLinkView_Sponsorship($lSponsorID, 'View sponsorship record', true).'&nbsp;'
                .str_pad($lSponsorID, 5, '0', STR_PAD_LEFT).'
             </td>');
      }

         //---------------------------
         // active state
         //---------------------------
      if ($showFields->bActiveInactive){
         echoT('
             <td class="enpRpt" style="'.$strIStyle.'">'
                .$strActiveDates.'
             </td>');
      }

         //---------------------------
         // sponsor name
         //---------------------------
      if ($showFields->bName){
         if ($sponRec->bSponBiz){
            $strLinkFID = 'business ID: '.strLinkView_BizRecord($lFID, 'View business record', true);
         }else {
            $strLinkFID = 'people ID: '.strLinkView_PeopleRecord($lFID, 'View people record', true);
         }
         $strLinkFID .= str_pad($lFID, 5, '0', STR_PAD_LEFT);
         
         $strHonoree = '';
         if ($showFields->bHonoree){
            if ($sponRec->bHonoree){
               $lHFID = $sponRec->lHonoreeID;
               if ($sponRec->bHonBiz){
                  $strLinkHFID = 'business ID: '.strLinkView_BizRecord($lHFID, 'View business record', true);
               }else {
                  $strLinkHFID = 'people ID: '.strLinkView_PeopleRecord($lHFID, 'View people record', true);
               }
               $strLinkHFID .= str_pad($lHFID, 5, '0', STR_PAD_LEFT);
            
               $strHonoree = '<br><br>Honoree: '.$sponRec->strHonSafeNameLF.'<br>'.$strLinkHFID;
            }
         }
         echoT('
              <td class="enpRpt" style="width: 130pt;'.$strIStyle.'">'
                .'<b>'.$sponRec->strSponSafeNameLF.'</b>'
                .'<br>'
                .$strLinkFID.$strHonoree.'<br>
                program: '.htmlspecialchars($sponRec->strSponProgram).'
             </td>');
      }

         //---------------------------
         // address / contact
         //---------------------------
      if ($showFields->bAddress){
         echoT('
             <td class="enpRpt" style="width: 130pt;'.$strIStyle.'">'
                .strBuildAddress(
                        $sponRec->strSponAddr1, $sponRec->strSponAddr2,   $sponRec->strSponCity,
                        $sponRec->strSponState, $sponRec->strSponCountry, $sponRec->strSponZip,
                        true));
         if ($showFields->bPhoneEmail){
            if ($sponRec->strSponEmail.'' != ''){
               echoT(strBuildEmailLink($sponRec->strSponEmail, '<br>', false, 
                               ' style="'.$strIStyle.'"'));
            
            }
            $strPhone = strPhoneCell($sponRec->strSponPhone, $sponRec->strSponPhone, true, true);
            if ($strPhone!=''){
               echoT('<br>'.$strPhone);
            }
         }
         echoT('
             </td>');
      }

         //---------------------------
         // client
         //---------------------------
      if ($showFields->bClient) {
         $lClientID = $sponRec->lClientID;
         if (is_null($lClientID)){
            if ($sponRec->bInactive){
               $strClientInfo = '<i>Client not set</i>';
            }else {
               $strClientInfo = '<i>Client not set</i><br>'
                            .strLinkAdd_ClientToSpon($lSponsorID, 'Add client to sponsorship', false);
            }
         }else {
            $strClientInfo = '<b>'.$sponRec->strClientSafeNameLF.'</b><br>'
                            .'client ID: '.strLinkView_ClientRecord($lClientID, 'View client record', true)
                            .str_pad($lClientID, 5, '0', STR_PAD_LEFT).'<br>'
                            .htmlspecialchars($sponRec->strLocation).'<br>'
                            .'birth/age: '.$sponRec->strClientAgeBDay;
            if ($showFields->bProgram){
               $strClientInfo .= '<br>'.htmlspecialchars($sponRec->strLocation);
            }
         }
         echoT('
             <td class="enpRpt" style="width: 130pt;'.$strIStyle.'">'
                .$strClientInfo.'
             </td>');
      }
      
         //---------------------------
         // financial commitment
         //---------------------------
      if ($showFields->bCommitment){
         echoT('
             <td class="enpRpt" style="width: 80pt; text-align: right;'.$strIStyle.'">'
                .$sponRec->strCommitACOCurSym.' '.number_format($sponRec->curCommitment, 2).' '
                .$sponRec->strCommitACOFlagImg.'<br>'
                .strLinkView_SponsorFinancials($lSponsorID, 'View sponsorship financials', true)
                .strLinkView_SponsorFinancials($lSponsorID, 'View financials', false).'
             </td>');
      
      }

   }

   echoT('</table><br>');











