<?php

   global $genumDateFormat, $gbAdmin;

   $params = array('enumStyle' => 'enpRptC');
   $clsRpt = new generic_rpt($params);

   $lNumCols = 0;

   if ($showFields->bHouseholdID         ) ++$lNumCols;
   if ($showFields->bHouseholdName       ) ++$lNumCols;
   if ($showFields->bHouseholdMembers    ) ++$lNumCols;

   if ($lNumCols==0) {
      echoT('There are no people fields to report.');
   }else {
      echoT('<br>'.$clsRpt->openReport());
      echoT($clsRpt->writeTitle($strRptTitle, '', '', $lNumCols));
      echoT($clsRpt->openRow(true));
      if ($showFields->bHouseholdID      ) echoT($clsRpt->writeLabel('householdID',  60));
      if ($showFields->bHouseholdName    ) echoT($clsRpt->writeLabel('Household Name',     250));
      if ($showFields->bHouseholdMembers ) echoT($clsRpt->writeLabel('Members', 440));

      echoT($clsRpt->closeRow());

      if ($lNumPeople == 0){
         echoT($clsRpt->openRow(true));
         echoT($clsRpt->writeCell('<i>No '.$strPeopleType.' match your search criteria</i>', '', '', $lNumCols));
      }else {
         foreach ($people as $hoh){
//echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
//   .': '.__LINE__.'<pre>'); print_r($hoh); echo('</pre></font><br>');
            $lHID    = $hoh->lKeyID;
            echoT($clsRpt->openRow(true));

//            $strHONSafeNameFL = $hoh->strSafeName;

               //--------------------
               // Household ID
               //--------------------
            if ($showFields->bHouseholdID){
               echoT($clsRpt->writeCell(
                                  strLinkView_Household($lHID, $lHID, 'View household', true)
                                 .'&nbsp;'.str_pad($lHID, 5, '0', STR_PAD_LEFT)
                                 , 60)
                                 );
            }

               //--------------------
               // Household Name
               //--------------------
            if ($showFields->bHouseholdName){
               echoT($clsRpt->writeCell('<i>'.htmlspecialchars($hoh->strHouseholdName).'</i>', 240));
            }

               //--------------------
               // Household Members
               //--------------------
            if ($showFields->bHouseholdMembers){
               $strCell = '<table width="100%" border="0">';
               foreach ($hoh->household as $member){
//echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
//   .': '.__LINE__.'<pre>'); print_r($member); echo('</pre></font><br>');
//die;
                  $lPID = $member->PID;
                  $bHOH = $lPID==$lHID;
//                  $strMemSafeNameFL = htmlspecialchars($member->FName.' '.$member->LName);
                  $strName =
                           strLinkView_PeopleRecord($lPID, 'View people record', true).'&nbsp;'
                        .'<b>'.$member->strSafeName.'</b>';
                  if ($bHOH){
                     $strName .= ' (head of household)';
                  }
                  $strCell .= '<tr><td colspan="2">'.$strName.'</td></tr>';

                     //------------------
                     // relationships
                     //------------------
                  if ($showFields->bRelationships){
                     if (($member->lNumRel_A2B > 0) || ($member->lNumRel_B2A)){
                        $strCell .= '<tr><td style="width: 20pt;">&nbsp;</td><td style="color:#56608c;">';
                        if ($member->lNumRel_A2B > 0){
                           foreach ($member->relA2B as $a2b){
                              $strCell .= '<i>'.$hoh->strSafeName.' is <b>'.$a2b->strRelationship.'</b> to '
                                  .$member->strSafeName.'</i><br>'."\n";
                           }
                        }
                        if ($member->lNumRel_B2A > 0){
                           foreach ($member->relB2A as $b2a){
                              $strCell .= '<i>'.$member->strSafeName.' is <b>'.$b2a->strRelationship.'</b> to '
                                     .$hoh->strSafeName.'</i><br>'."\n";
                           }
                        }
                        $strCell .= '</td></tr>';
                     }
                  }

                     //------------------
                     // address
                     //------------------
                  if ($showFields->bAddress){
                     if (trim($member->strAddress)!=''){
                        $strCell .= '<tr><td style="width: 20pt;">&nbsp;</td><td>'.$member->strAddress.'</td></tr>';
                     }
                  }
               }
               $strCell .= '</table>';
               echoT($clsRpt->writeCell($strCell, 440));
            }
         }
      }
      echoT($clsRpt->closeReport());
   }





