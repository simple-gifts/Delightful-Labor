<?php
   global $genumDateFormat, $gbAdmin;

      // location support
   if (!isset($bViaLocation)) $bViaLocation = false;
   if ($bViaLocation){   
      $attributes = array('name' => $locChange->frmName);
      echoT(form_open($locChange->frmDest, $attributes));  
      echoT('<select name="ddlLocation" >'.$ddlLocations.'</select>');
      echoT('
         <input type="submit" name="cmdSubmit" value="Refresh"
         style=" text-align: center; width: 70pt;"
         onclick="this.disabled=1; this.form.submit();"
         class="btn"
         onmouseover="this.className=\'btn btnhov\'"
         onmouseout="this.className=\'btn\'">'); 
      
      echoT(form_close('<br>'));  
   }
   
      // program support
   if (!isset($bViaSponProg)) $bViaSponProg = false;
   if ($bViaSponProg){   
      $attributes = array('name' => $progChange->frmName);
      echoT(form_open($progChange->frmDest, $attributes));  
      echoT($ddlSProgs);
      echoT('
         <input type="submit" name="cmdSubmit" value="Refresh"
         style=" text-align: center; width: 70pt;"
         onclick="this.disabled=1; this.form.submit();"
         class="btn"
         onmouseover="this.className=\'btn btnhov\'"
         onmouseout="this.className=\'btn\'">'); 
      
      echoT(form_close('<br>'));  
   }
   
   
   $params = array('enumStyle' => 'enpRptC');
   $clsRpt = new generic_rpt($params);

   $lNumCols = 1;

   if ($showFields->bClientID     ) ++$lNumCols;
   if ($showFields->bRemClient    ) ++$lNumCols;
   if ($showFields->bName         ) ++$lNumCols;
   if ($showFields->bAgeGender    ) ++$lNumCols;
   if ($showFields->bLocation     ) ++$lNumCols;
   if ($showFields->bStatus       ) ++$lNumCols;
   if ($showFields->bSponsors     ) ++$lNumCols;

   if ($lNumCols==0) {
      echoT('There are no client fields to report.');
   }else {
      echoT('<br>'.$clsRpt->openReport());
      echoT($clsRpt->writeTitle($strRptTitle, '', '', $lNumCols));
      echoT($clsRpt->openRow(true));
      echoT($clsRpt->writeLabel('&nbsp;',  20));
      if ($showFields->bClientID    ) echoT($clsRpt->writeLabel('ClientID',  60));
      if ($showFields->bRemClient   ) echoT($clsRpt->writeLabel('&nbsp;',    20));
      if ($showFields->bName        ) echoT($clsRpt->writeLabel('Name',     150));
      if ($showFields->bAgeGender   ) echoT($clsRpt->writeLabel('Birthday/Gender'));
      if ($showFields->bLocation    ) echoT($clsRpt->writeLabel('Location'));
      if ($showFields->bStatus      ) echoT($clsRpt->writeLabel('Status'));
      if ($showFields->bSponsors    ) echoT($clsRpt->writeLabel('Sponsors'));
      echoT($clsRpt->closeRow());

      if ($lNumClients == 0){
         echoT($clsRpt->openRow(true));
         echoT($clsRpt->writeCell('<i>No clients match your search criteria</i>', '', '', $lNumCols));
      }else {
         $lRowIDX = 1;
         foreach ($clientInfo as $client){
            
            $bActive = $client->curStat_bShowInDir;
            if ($bActive){
               $strStyleActive = '';
            }else {
               $strStyleActive = 'color: #999;';
            }
            
            $lClientID    = $client->lKeyID;
            echoT($clsRpt->openRow(true));
            echoT($clsRpt->writeCell($lRowIDX, '', 'text-align: center;'));
            if ($showFields->bClientID){
               echoT($clsRpt->writeCell(
                                  strLinkView_ClientRecord($lClientID, 'View client record', true, ' id="dirCID_'.$lClientID.'" ')
                                 .'&nbsp;'.str_pad($lClientID, 5, '0', STR_PAD_LEFT), 60, 
                                 $strStyleActive));
            }

            if ($showFields->bRemClient){
               echoT($clsRpt->writeCell(strLinkRem_Client($lClientID, 'Remove this client\'s record', true, true)));
            }
            if ($showFields->bName){
               echoT($clsRpt->writeCell($client->strSafeNameLF, 150, $strStyleActive));
            }
            if ($showFields->bAgeGender){
               echoT($clsRpt->writeCell($client->strClientAgeBDay.'<br>'.$client->enumGender, 200, $strStyleActive));
            }
            if ($showFields->bLocation){
               echoT($clsRpt->writeCell(htmlspecialchars($client->strLocation)
//                                       .'<br>'.htmlspecialchars($client->strLocCountry)
                                       .'<br>'.$client->strAddress, 170, $strStyleActive));
            }
            if ($showFields->bStatus){
               echoT($clsRpt->writeCell(htmlspecialchars($client->curStat_strStatus), '', $strStyleActive));
            }

            if ($showFields->bSponsors){
               echoT($clsRpt->writeCell(strSponsorSummaryViaCID(true, $client), '', $strStyleActive));
            }
            echoT($clsRpt->closeRow());
            ++$lRowIDX;            
         }
      }
      echoT($clsRpt->closeReport());
   }

