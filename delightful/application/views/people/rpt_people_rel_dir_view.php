<?php

   $params = array('enumStyle' => 'enpRptC');
   $clsRpt = new generic_rpt($params);

   echoT('<br>'.$clsRpt->openReport());
   echoT($clsRpt->writeTitle($strRptTitle, '', '', 4));
   echoT($clsRpt->openRow(true));
   echoT($clsRpt->writeLabel('PeopleID',  60));
   echoT($clsRpt->writeLabel('Name',     150));
   echoT($clsRpt->writeLabel('Address'));
   echoT($clsRpt->writeLabel('Relationships'));

   echoT($clsRpt->closeRow());

   if ($lNumPeople == 0){
      echoT($clsRpt->openRow(true));
      echoT($clsRpt->writeCell('<i>No '.$strPeopleType.' match your search criteria</i>', '', '', 5));
      echoT($clsRpt->closeRow(''));
   }else {
   
      foreach ($people as $person){
         $lPID    = $person->lKeyID;
         echoT($clsRpt->openRow(true));
         
            //--------------------
            // People ID
            //--------------------            
         echoT($clsRpt->writeCell(
                               strLinkView_PeopleRecord($lPID, 'View people record', true)
                              .'&nbsp;'.str_pad($lPID, 5, '0', STR_PAD_LEFT)
                              , 60)
                              );
         
            //--------------------
            // Name
            //--------------------
         echoT($clsRpt->writeCell($person->strSafeNameLF
                              .'<br>'
                              .strLinkView_Household($person->lHouseholdID, $lPID, 'View household', true)
                              .'<i>'.htmlspecialchars($person->strHouseholdName).'</i>', 240));
         
            //--------------------
            // Address
            //--------------------
         echoT($clsRpt->writeCell($person->strAddress, 160));

            //------------------
            // relationships
            //------------------         
         $strRels = '';            
         if (($person->lNumFromRels > 0) || ($person->lNumToRels)){
            if ($person->lNumFromRels > 0){
               foreach ($person->fromRels as $a2b){
                  $lPIDLink = $a2b->lPerson_B_ID;
                  if ($a2b->bSoftDonations){
                     $strSoftImg = '&nbsp;<img src="'.IMGLINK_DOLLAR.' " border="0">';
                  }else {
                     $strSoftImg = '';
                  }
                  $strRels .= htmlspecialchars($a2b->strAFName.' '.$a2b->strALName).' is <b>'.$a2b->strRelationship.'</b> to '
                             .strLinkView_PeopleRecord($lPIDLink, 'View record', true).' '
                             .htmlspecialchars($a2b->strBFName.' '.$a2b->strBLName).$strSoftImg.'<br>'."\n";
                  if ($a2b->strNotes != ''){
                     $strRels .= '
                            <table cellpadding="0" cellspacing="0">
                               <tr>
                                  <td style="width: 20pt;">&nbsp;</td>
                                  <td>'.nl2br(htmlspecialchars($a2b->strNotes)).'</td>
                               </tr>
                            </table>';
                  }
               }
            }
            if ($person->lNumFromRels > 0) $strRels .= '<br>';

            if ($person->lNumToRels > 0){
               foreach ($person->toRels as $b2a){
                  if ($b2a->bSoftDonations){
                     $strSoftImg = '&nbsp;<img src="'.IMGLINK_DOLLAR.' " border="0">';
                  }else {
                     $strSoftImg = '';
                  }
               
                  $lPIDLink = $a2b->lPerson_A_ID;
                  $strRels .= 
                              strLinkView_PeopleRecord($lPIDLink, 'View record', true).' '
                             .htmlspecialchars($b2a->strAFName.' '.$b2a->strALName).' is <b>'.$b2a->strRelationship.'</b> to '
                             .htmlspecialchars($b2a->strBFName.' '.$b2a->strBLName).$strSoftImg.'<br>'."\n";
                  if ($b2a->strNotes != ''){
                     $strRels .= '
                            <table>
                               <tr>
                                  <td style="width: 15pt;">&nbsp;</td>
                                  <td>'.nl2br(htmlspecialchars($b2a->strNotes)).'</td>
                               </tr>
                            </table>';
                  }
               }
            }
         }else {
            $strRels = '&nbsp;';
         }
         echoT($clsRpt->writeCell($strRels, 520, ' vertical-align: middle;  '));
         echoT($clsRpt->closeRow(''));         
      }
   }
   echoT($clsRpt->closeReport());









