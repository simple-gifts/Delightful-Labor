<?php
   global $genumDateFormat;

   $params = array('enumStyle' => 'enpRptC');
   $clsRpt = new generic_rpt($params);

   $lNumCols = 0;
   if ($showFields->bSponsorID    ) ++$lNumCols;
   if ($showFields->bName         ) ++$lNumCols;
   if ($showFields->bSponsorInfo  ) ++$lNumCols;
   if ($showFields->bClient       ) ++$lNumCols;

   if ($lNumCols==0) {
      echoT('There are no sponsorship fields to report.');
   }else {
      echoT('<br>'.$clsRpt->openReport(800));
      echoT($clsRpt->writeTitle($strRptTitle, '', '', $lNumCols));
      echoT($clsRpt->openRow(true));
      if ($showFields->bSponsorID    ) echoT($clsRpt->writeLabel('SponsorID', 40));
      if ($showFields->bName         ){
         echoT($clsRpt->writeLabel('Name'));
      }
      if ($showFields->bSponsorInfo  ) echoT($clsRpt->writeLabel('Sponsorship Info'));
      if ($showFields->bClient       ) echoT($clsRpt->writeLabel('Client'));
      echoT($clsRpt->closeRow());

      foreach ($sponInfo as $sponsor){
         $lSponID     = $sponsor->lKeyID;
         $bBiz        = $sponsor->bSponBiz;
         $lForeignID  = $sponsor->lForeignID;
         $lClientID   = $sponsor->lClientID;
         $bInactive   = $sponsor->bInactive;
         $dteInactive = $sponsor->dteInactive;
         if ($bInactive){
            $strSpanInactive = '<span style="color: #999;">';
            $strStopSpan     = '</span>';
         }else {
            $strSpanInactive = 
            $strStopSpan     = '';
         }

         echoT($clsRpt->openRow(true));
         if ($showFields->bSponsorID){
            echoT($clsRpt->writeCell(
                              strLinkView_Sponsorship($lSponID, 'View sponsorship record', true).'&nbsp;'
                             .$strSpanInactive
                             .str_pad($lSponID, 5, '0', STR_PAD_LEFT).$strStopSpan,
                              '40', $strStyleExtra='text-align: center;'));
         }
         if ($showFields->bName){
            if ($bBiz){
               $strLink = strLinkView_BizRecord($lForeignID, 'View Business Record', true);                              
               $strName = htmlspecialchars($sponsor->strSponsorLName).' <i>(business)</i>';
            }else {
               $strLink = strLinkView_PeopleRecord($lForeignID, 'View People Record', true);
               $strName = htmlspecialchars($sponsor->strSponsorLName.', '.$sponsor->strSponsorFName);
            }
            $strName .= '<br>'.$strLink.'&nbsp;'.str_pad($lForeignID, 5, '0', STR_PAD_LEFT);
            if ($showFields->bSponAddr){
               $strName .= '<br>'.$sponsor->strAddr;
            }
            echoT($clsRpt->writeCell($strSpanInactive.$strName.$strStopSpan, 175));
         }


         if ($showFields->bSponsorInfo){
            if ($sponsor->bInactive){
               $strDates = date($genumDateFormat, $sponsor->dteStart).' - '
                          .date($genumDateFormat, $sponsor->dteInactive).'<br>Inactive';
            }else {
               $strDates = date($genumDateFormat, $sponsor->dteStart).' - now ';
            }
            echoT($clsRpt->writeCell($strSpanInactive
                                    .$strDates.'<br>program: '
                                    .htmlspecialchars($sponsor->strSponProgram)
                                    .$strStopSpan));
         }

         if ($showFields->bClient){
            if (is_null($lClientID)){
               $strLinkClient = '';
               $strClientName = '&nbsp;';
               $strLocation   = '&nbsp;';
            }else {
               $strLinkClient = '<br>'
                              .strLinkView_ClientRecord($lClientID, 'View client record', true)
                              .'&nbsp;'.str_pad($lClientID, 5, '0', STR_PAD_LEFT);
               $strClientName = $sponsor->strClientSafeNameFL;
               if ($showFields->bLocation     ){
                  $strLocation   = '<br>'
                                  .htmlspecialchars($sponsor->strLocation).'<br>'
                                  .htmlspecialchars($sponsor->strLocationCountry);
               }else {
                  $strLocation = '';
               }
            }
            echoT($clsRpt->writeCell($strSpanInactive.$strClientName
                                    .$strLinkClient.$strLocation.$strStopSpan));
         }
         echoT($clsRpt->closeRow());
      }
      echoT($clsRpt->closeReport());
   }


