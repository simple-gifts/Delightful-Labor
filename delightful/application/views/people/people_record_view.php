<?php
   global $gbVolLogin;

   $clsDateTime = new dl_date_time;
   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '120pt';

   $lPID = $people->lKeyID;


      //------------------------------------------------------------------------
      // is this a temporary people record? If so, give the user some options
      // Expiration dates are used for imported mailing lists
      //------------------------------------------------------------------------

   if (!is_null($people->dteExpire)){
      displayExpirationInfo($lPID, $people);
   }

   $lIndentWidth = 20;

   showPeopleInfo           ($clsRpt, $lPID, $people, $vol, $clsDateTime);
   if (!$gbVolLogin){

      if (bAllowAccess('showSponsors')){
         showSponsorshipInfo($clsRpt, $sponInfo, $lNumSponsors, $lPID, $people);
      }
      showHouseholdInfo        ($people, $lPID, $arrHouseholds);
      showGroupInfo            ($lPID, $people->strSafeName, $lNumGroups, $groupList,
                                $inGroups, $lCntGroupMembership,
                                CENUM_CONTEXT_PEOPLE, 'pRecView');
      showRelationships        ($lPID, $people, $arrRelAB, $arrRelBA,
                                $lNumRelAB, $lNumRelBA);
      showCustomPeopleTableInfo($strPT, $lNumPTablesAvail);

      if (bAllowAccess('showFinancials')){
         showGifts             ($lPID, false,
                                $strCumGiftsNonSoftMon, $strCumGiftsNonSoftInKind,
                                $strCumGiftsSoft,       $strCumSpon, $lNumPledges,
                                $lTotHard, $lTotSoft, $lTotInKind, $lNumSponPay);
      }
      showImageInfo            (CENUM_CONTEXT_PEOPLE, $lPID, ' Images',
                                $images, $lNumImages, $lNumImagesTot);
      showDocumentInfo         (CENUM_CONTEXT_PEOPLE, $lPID, ' Documents',
                                $docs, $lNumDocs, $lNumDocsTot);

      showPeopleBizContacts    ($lPID, $people, $lNumContacts, $contacts, $clsRpt);
//      showReminderBlock        ($clsRem, $lPID, CENUM_CONTEXT_PEOPLE);
      showPeopleENPStats       ($clsRpt, $people);
   }

   function showPeopleInfo(&$clsRpt, $lPID, &$people, $vol, &$clsDateTime){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS, $glclsDTDateFormat, $genumDateFormat, $gbVolLogin;

      if (!$gbVolLogin){
         if ($vol->bVol){
            $strVol = strLinkView_Volunteer($vol->lVolID, 'View volunteer record', true).' ';
            if ($vol->bInactive){
               $strVol .= '<i>Inactive since '.date($genumDateFormat, $vol->dteInactive);
            }else {
               $strVol .= 'Active '.strLinkView_Volunteer($vol->lVolID, '(view)', false);
            }
         }else {
               $strVol = 'Not a volunteer&nbsp;&nbsp;&nbsp;&nbsp;'
                        .strLinkAdd_VolFromPeople($lPID, 'Add as volunteer', true).'&nbsp;'
                        .strLinkAdd_VolFromPeople($lPID, 'Add as volunteer', false);
         }
      }

      if (($people->strPreferredName != $people->strFName) && ($people->strPreferredName!='')){
         $strPreferred = ' ('.$people->strPreferredName.')';
      }else {
         $strPreferred = '';
      }
      $strName = strBuildName(false, $people->strTitle, $people->strPreferredName,
                              $people->strFName, $people->strLName, $people->strMName);

      if ($gbVolLogin){
         openBlock('Contact Info',
                   strLinkEdit_PeopleContact('', 'Edit your contact information', true).'&nbsp;'
                  .strLinkEdit_PeopleContact('', 'Edit your contact information', false)
                   );
      }else {
         openBlock('Contact Info',
                   strLinkEdit_PeopleContact($lPID, 'Edit this person\'s contact information', true)
                  .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                  .strLinkRem_People($lPID, 'Remove this person\'s record', true, true));
      }

      echoT(
          $clsRpt->openReport(600));

         //------------------------
         // People ID
         //------------------------
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('People ID:')
         .$clsRpt->writeCell(str_pad($lPID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ());

         //------------------------
         // Name
         //------------------------
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Name:')
         .$clsRpt->writeCell(htmlspecialchars($strName))
         .$clsRpt->closeRow  ());

         //------------------------
         // Household
         //------------------------
      if (!$gbVolLogin){
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Household:')
            .$clsRpt->writeCell(
                      strLinkView_Household($people->lHouseholdID, $lPID, 'View Household', true).' '
                     .htmlspecialchars($people->strHouseholdName))
            .$clsRpt->closeRow  ());
      }

         //------------------------
         // Birthdate
         //------------------------
      if (is_null($people->dteMysqlBirthDate)){
         $strBDay = '&nbsp;';
      }else {
         $strBDay = $clsDateTime->strPeopleAge(0, $people->dteMysqlBirthDate, $lAgeYears, $glclsDTDateFormat);
      }
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Birthdate:')
         .$clsRpt->writeCell($strBDay)
         .$clsRpt->closeRow  ());

         //------------------------
         // Gender
         //------------------------
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Gender:')
         .$clsRpt->writeCell($people->enumGender)
         .$clsRpt->closeRow  ());

         //------------------------
         // Address
         //------------------------
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Address:')
         .$clsRpt->writeCell($people->strAddress)
         .$clsRpt->closeRow  ());

         //------------------------
         // Phone
         //------------------------
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Phone:')
         .$clsRpt->writeCell(htmlspecialchars(strPhoneCell($people->strPhone, $people->strCell)))
         .$clsRpt->closeRow  ());

         //------------------------
         // Accounting Country
         //------------------------
      if (!$gbVolLogin){
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Accounting Country:')
            .$clsRpt->writeCell($people->strFlagImage.' '.$people->strCurSymbol)
            .$clsRpt->closeRow  ());
      }

         //------------------------
         // Email
         //------------------------
      if ($gbVolLogin){
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Email:')
            .$clsRpt->writeCell(htmlspecialchars($people->strEmail))
            .$clsRpt->closeRow  ());
      }else {
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Email:')
            .$clsRpt->writeCell($people->strEmailFormatted)
            .$clsRpt->closeRow  ());
      }
         //------------------------
         // Volunteer Status
         //------------------------
      if (!$gbVolLogin){
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Volunteer Status:')
            .$clsRpt->writeCell($strVol)
            .$clsRpt->closeRow  ());
      }

         //------------------
         // Attributed To
         //------------------
      if (!$gbVolLogin){
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Attributed To:')
            .$clsRpt->writeCell (htmlspecialchars($people->strAttrib))
            .$clsRpt->closeRow  ());
      }

         //------------------
         // Notes
         //------------------
      if (!$gbVolLogin){
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Notes:')
            .$clsRpt->writeCell (nl2br(htmlspecialchars($people->strNotes)))
            .$clsRpt->closeRow  ());
      }

      echoT(
         $clsRpt->closeReport());

      closeBlock();
   }

   function showHouseholdInfo(&$people, $lPID, $arrHouseholds){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'peopleHousehold';
      $attributes->divImageID   = 'phdivImg';

      openBlock('Household',
                  strLinkView_Household($people->lHouseholdID, $lPID, 'View Household', true).' '
                 .strLinkView_Household($people->lHouseholdID, $lPID, 'View Household', false),
                 $attributes);


      echoT('<table>');
      foreach ($arrHouseholds as $idx=>$hhMember){
         $strHOH = ($idx==0 ? ' (head of household)' : '');
         echoT('
                <tr>
                   <td>');
         if ($hhMember->PID==$lPID){
            echoT('&nbsp;');
         }else {
            echoT(strLinkView_PeopleRecord($hhMember->PID, 'View People Record', true));
         }
         echoT('
                   </td>
                   <td>'
                      .htmlspecialchars($hhMember->FName.' '.$hhMember->LName).$strHOH.'
                   </td>
                </tr>');
      }
      echoT('</table>');
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function showRelationships($lPID, &$clsPeopleRec, &$arrRelAB, &$arrRelBA,
                              $lNumRelAB, $lNumRelBA){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'peopleRel';
      $attributes->divImageID   = 'preldivImg';

      openBlock('Relationships <span style="font-size: 9pt;">('.($lNumRelAB + $lNumRelBA).')</span>',
                     strLinkAdd_Relationship($lPID, 'Add new relationship', true, '').' '
                    .strLinkAdd_Relationship($lPID, 'Add new relationship', false, ''),
                    $attributes);

         // div required to keep bolded text from dropping when an image appears on the line
      echoT('<div style="vertical-align: middle;">'."\n\n");
      if ($lNumRelAB==0 && $lNumRelBA==0){
         echoT('<i>No relationships</i>');
      }else {
         if ($lNumRelAB > 0){
            echoT('<table>');
            foreach ($arrRelAB as $clsRel){
               showRelEntry($lPID, $clsRel, true);
            }
            echoT('</table>');
            if ($lNumRelAB > 0) echoT('<br>');
         }

         if ($lNumRelBA > 0){
            echoT('<table>');
            foreach ($arrRelBA as $clsRel){
               showRelEntry($lPID, $clsRel, false);
            }
            echoT('</table>');
         }
      }
      echoT('</div>'."\n\n");
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function showRelEntry($lPID, &$clsRel, $bA2B){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bA2B){
         $strLinkPeopleA = '';
         $strLinkPeopleB = strLinkView_PeopleRecord($clsRel->lPerson_B_ID, 'View people record', true).'&nbsp;';
      }else {
         $strLinkPeopleA = strLinkView_PeopleRecord($clsRel->lPerson_A_ID, 'View people record', true).'&nbsp;';
         $strLinkPeopleB = '';
      }
      echoT('
         <tr>');

      echoT('
            <td>'
               .strLinkEdit_Relationship($clsRel->lRelID, $bA2B, 'Edit relationship', true).'
            </td>
            <td>'
               .strLinkRem_Relationship($clsRel->lRelID, $lPID, 'Remove relationship', true, true).'
            </td>');

      if ($clsRel->bSoftDonations){
         echoT('<td><img src="'.IMGLINK_DOLLAR.'" border="0" title="Soft cash relationship"></td>');
      }else {
         echoT('<td>&nbsp;</td>');
      }

      echoT('
            <td>'
               .$strLinkPeopleA
               .htmlspecialchars($clsRel->strAFName.' '.$clsRel->strALName)
               .' is <b>'.htmlspecialchars($clsRel->strRelationship).'</b> to '
               .$strLinkPeopleB
               .htmlspecialchars($clsRel->strBFName.' '.$clsRel->strBLName).'
             </td>'
         );

      echoT('</tr>');
   }

   function showCustomPeopleTableInfo($strPT, $lNumPTablesAvail){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'peopleCus';
      $attributes->divImageID   = 'precusdivImg';
      $attributes->bStartOpen   = false;
      openBlock('Personalized Tables <span style="font-size: 9pt;">('.$lNumPTablesAvail.')</span>', '', $attributes);

      echoT($strPT);

      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function showPeopleBizContacts($lPID, $people, $lNumContacts, $contacts, &$clsRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'peopleBizCon';
      $attributes->divImageID   = 'preBizConDivImg';
      openBlock('Business Contacts <span style="font-size: 9pt;">('.$lNumContacts.')</span>', '', $attributes);

      if ($lNumContacts == 0){
         echoT('<i>There are no business contacts for this person.</i><br>');
      }else {
         echoT($clsRpt->openReport());
         foreach ($contacts as $clsCon){
            echoT(
                $clsRpt->openRow()
               .$clsRpt->writeCell(strLinkView_BizRecord($clsCon->lBizID, 'View business record', true))
               .$clsRpt->writeCell(htmlspecialchars($clsCon->strBizName)));
            if ($clsCon->strRelationship.'' == ''){
               echoT($clsRpt->writeCell('&nbsp;'));
            }else {
               echoT($clsRpt->writeCell('('.htmlspecialchars($clsCon->strRelationship).')'));
            }
            if ($clsCon->bSoftCash){
               echoT($clsRpt->writeCell('<img src="'.DL_IMAGEPATH.'/misc/dollar.gif" border="0" title="soft cash relationship">'));
            }else {
               echoT($clsRpt->writeCell('&nbsp;'));
            }
            echoT($clsRpt->closeRow());
         }
         echoT($clsRpt->closeReport());
      }
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function showPeopleENPStats($clsRpt, $people){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'peopleENPStats';
      $attributes->divImageID   = 'peopleENPStatsDivImg';
      openBlock('Record Information', '', $attributes);
      echoT(
         $clsRpt->showRecordStats($people->dteOrigin,
                               $people->strStaffCFName.' '.$people->strStaffCLName,
                               $people->dteLastUpdate,
                               $people->strStaffLFName.' '.$people->strStaffLLName,
                               $clsRpt->strWidthLabel));
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }
