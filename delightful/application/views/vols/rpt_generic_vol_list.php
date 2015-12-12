<?php

   global $genumDateFormat, $gbAdmin;

   $params = array('enumStyle' => 'enpRptC');
   $clsRpt = new generic_rpt($params);

   $lNumCols = 0;

   if ($showFields->bVolID         ) ++$lNumCols;
   if ($showFields->bPeopleID      ) ++$lNumCols;
   if ($showFields->bActiveInactive) ++$lNumCols;
   if ($showFields->bName          ) ++$lNumCols;
   if ($showFields->bAddress       ) ++$lNumCols;
   if ($showFields->bPhoneEmail    ) ++$lNumCols;
   if ($showFields->bSchedule      ) ++$lNumCols;
   if ($showFields->bSkills        ) ++$lNumCols;



   if ($lNumCols==0) {
      echoT('There are no volunteer fields to report.');
   }else {
      echoT('<br>'.$clsRpt->openReport());
      echoT($clsRpt->writeTitle($strRptTitle, '', '', $lNumCols));

     echoT($clsRpt->openRow(true));
      if ($showFields->bVolID         ) echoT($clsRpt->writeLabel('VolunteerID',  60));
      if ($showFields->bPeopleID      ) echoT($clsRpt->writeLabel('PeopleID',     60));
      if ($showFields->bActiveInactive) echoT($clsRpt->writeLabel('Active?',      60));
      if ($showFields->bName          ) echoT($clsRpt->writeLabel('Name',         60));
      if ($showFields->bAddress       ) echoT($clsRpt->writeLabel('Address',      60));
      if ($showFields->bPhoneEmail    ) echoT($clsRpt->writeLabel('Phone/Email',  60));
/*      
      if ($showFields->bSchedule      ) echoT($clsRpt->writeLabel('Schedule',     60));
*/
      if ($showFields->bSkills        ) echoT($clsRpt->writeLabel('Skills',       210));
      echoT($clsRpt->closeRow());

      
      
      if ($lNumVols == 0){
         echoT($clsRpt->openRow(true));
         echoT($clsRpt->writeCell('<i>No volunteers match your search criteria</i>', '', '', $lNumCols));
      }else {
         foreach ($vols as $vol){
            $bInactive = $vol->bInactive;
            $lVolID    = $vol->lKeyID;
            $lPID      = $vol->lPeopleID;
            if ($bInactive){
               $strColor = ' color: #888;';
            }else {
               $strColor = '';
            }
            echoT($clsRpt->openRow(true));
            if ($showFields->bVolID){
               echoT($clsRpt->writeCell(
                                  strLinkView_Volunteer($lVolID, 'View volunteer record', true)
                                 .'&nbsp;'.str_pad($lVolID, 5, '0', STR_PAD_LEFT)
                                 , 60, 'text-align: center;'.$strColor)
                                 );
            }
            if ($showFields->bPeopleID){
               echoT($clsRpt->writeCell(
                                  strLinkView_PeopleRecord($lPID, 'View people record', true)
                                 .'&nbsp;'.str_pad($lPID, 5, '0', STR_PAD_LEFT)
                                 , 60, 'text-align: center;'.$strColor)
                                 );
            }
            if ($showFields->bActiveInactive){
               echoT($clsRpt->writeCell(
                               ($vol->bInactive ? 'No' : 'Yes'), 60, 'text-align: center;'.$strColor));
            }
            
            if ($showFields->bName){
               echoT($clsRpt->writeCell($vol->strSafeNameLF
                                 .'<br>'
                                 .strLinkView_Household($vol->lHouseholdID, $lPID, 'View household', true)
                                 .'<i>'.htmlspecialchars($vol->strHouseholdName).'</i>', 240, $strColor));
            }
            if ($showFields->bAddress){
               echoT($clsRpt->writeCell($vol->strAddress, 160, $strColor));
            }
            if ($showFields->bPhoneEmail){
               echoT($clsRpt->writeCell(strPhoneCell($vol->strPhone, $vol->strCell, true, true)
                     .'<br>'.$vol->strEmailFormatted, 100, $strColor));
            }
            
            if ($showFields->bSkills){
               if ($vol->lNumJobSkills == 0){
                  echoT($clsRpt->writeCell('- na -', 210, 'text-align: center;'.$strColor));
               }else {
                  $strSkills = '<ul style="list-style-position: inside; list-style-type: square; display:inline; 
                                       margin-left: 0pt; padding-left: 0pt;">';
                  foreach ($vol->volSkills as $skill){
                     $strSkills .= '<li>'.htmlspecialchars($skill->strSkill).'</li>';
                  }
                  echoT($clsRpt->writeCell($strSkills.'</ul>', 210, $strColor));
               }
            }
/*            
            if ($showFields->bSchedule){
               echoT($clsRpt->writeCell('TBD', 40, $strColor));
            }
*/            
            
         }
      }
      echoT($clsRpt->closeReport());
   }









