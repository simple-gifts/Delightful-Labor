<?php

   $params = array('enumStyle' => 'terse');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '110pt';

   showVolStatus         ($clsRpt, $volRec, $lVolID);
   showVolSchedule       ($clsRpt, $volRec, $lVolID, $lPastShifts, $lCurrentFutureShifts);
   showVolHours          ($clsRpt, $volRec, $dTotHours, $dTotUnHours, $lVolID);
   showVolSkills         ($clsRpt, $volRec, $lVolID, $lNumSingleVolSkills, $singleVolSkills);

   if (bAllowAccess('showClients')) showVolClientAssoc    ($clsRpt, $volRec, $lVolID);

   showGroupInfo         ($lVolID, $volRec->strSafeName, $lNumGroups, $groupList,
                             $inGroups, $lCntGroupMembership,
                             CENUM_CONTEXT_VOLUNTEER, 'vRecView');
   showCustomVolTableInfo($strPT, $lNumPTablesAvail);
   showImageInfo         (CENUM_CONTEXT_VOLUNTEER, $lVolID, 'Volunteer Images',
                          $images, $lNumImages, $lNumImagesTot);
   showDocumentInfo      (CENUM_CONTEXT_VOLUNTEER, $lVolID, 'Volunteer Documents',
                          $docs, $lNumDocs, $lNumDocsTot);

   showVolENPStats       ($clsRpt, $volRec);

function showVolClientAssoc($clsRpt, $volRec, $lVolID){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'volClient';
   $attributes->divImageID   = 'volClientImg';
   openBlock('Volunteer-Client Associations  <span style="font-size: 9pt;">('.$volRec->lNumVolClientAssoc.')</span>',
               strLinkAdd_VolClientAssoc($lVolID, 'Add new volunteer/client association', true, 'id="vcaImg"').'&nbsp;'
              .strLinkAdd_VolClientAssoc($lVolID, 'Add new association', false, 'id="vcaImgLnk"'),
               $attributes);

   if ($volRec->lNumVolClientAssoc == 0){
      echoT('<i>There are no volunteer/client associations for this volunteer.</i>');
   }else {
      echoT('
         <table class="enpRpt">
            <tr>
               <td class="enpRptLabel">
                  clientID
               </td>
               <td class="enpRptLabel">
                  &nbsp;
               </td>
               <td class="enpRptLabel">
                  Name
               </td>
               <td class="enpRptLabel">
                  Address
               </td>
               <td class="enpRptLabel">
                  Phone
               </td>
            </tr>');

      foreach ($volRec->vca as $va){

         $lClientID = $va->lClientID;
         echoT('
               <tr class="makeStripe">
                  <td class="enpRpt" style="text-align: center;">'
                     .str_pad($lClientID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                     .strLinkView_ClientRecord($lClientID, 'View client record', true, 'id="cr'.$lClientID.'"').'
                  </td>
                  <td class="enpRpt" style="text-align: center;">'
                     .strLinkRem_VolClientAssoc($va->lKeyID, $lVolID, 'Remove association', true, true).'
                  </td>
                  <td class="enpRpt">'
                     .htmlspecialchars($va->strLName.', '.$va->strFName).'
                  </td>
                  <td class="enpRpt">'
                     .strBuildAddress(
                           $va->strAddr1, $va->strAddr2,   $va->strCity,
                           $va->strState, $va->strCountry, $va->strZip,
                           true).'
                  </td>
                  <td class="enpRpt">'
                     .strPhoneCell($va->strPhone, $va->strCell, true).'
                  </td>
               </tr>');
      }
      echoT('
         </table>');
   }

   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showVolStatus($clsRpt, $volRec, $lVolID){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;

   openBlock('Volunteer Status', '');
   $bActive = !$volRec->bInactive;

   echoT($clsRpt->openReport());

   echoT(
        $clsRpt->openRow()
       .$clsRpt->writeLabel('Volunteer ID:')
       .$clsRpt->writeCell(str_pad($lVolID, 5, '0', STR_PAD_LEFT))
       .$clsRpt->closeRow());

   echoT(
        $clsRpt->openRow()
       .$clsRpt->writeLabel('Status:')
       .$clsRpt->writeCell('<b><span style="font-size: 10pt;">'.($bActive ? 'ACTIVE' : 'INACTIVE').'</span></b>')
       .$clsRpt->closeRow());

   echoT(
       $clsRpt->openRow()
       .$clsRpt->writeLabel('Volunteer Since:')
       .$clsRpt->writeCell(date($genumDateFormat, $volRec->dteOrigin))
       .$clsRpt->closeRow());

   if (!$bActive){
      echoT(
          $clsRpt->openRow()
          .$clsRpt->writeLabel('Inactive Since:')
          .$clsRpt->writeCell(date($genumDateFormat, $volRec->dteInactive))
          .$clsRpt->closeRow());
   }

   echoT(
       $clsRpt->openRow()
       .$clsRpt->writeLabel('')
       .$clsRpt->writeCell(
             strLinkSpecial_VolActiveInactive($lVolID, !$bActive,
                                             ($bActive ? 'Inactivate' : 'Activate').' this volunteer', true, true).'&nbsp;'
            .strLinkSpecial_VolActiveInactive($lVolID, !$bActive,
                                             ($bActive ? 'Inactivate' : 'Activate').' this volunteer', false, true))
       .$clsRpt->closeRow());


   echoT($clsRpt->closeReport());

   closeBlock();
}

function showVolENPStats($clsRpt, $volRec){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'volENPStat';
   $attributes->divImageID   = 'volENPStatDivImg';
   openBlock('Record Information', '', $attributes);
   echoT(
      $clsRpt->showRecordStats($volRec->dteOrigin,
                            $volRec->strUCFName.' '.$volRec->strUCLName,
                            $volRec->dteLastUpdate,
                            $volRec->strULFName.' '.$volRec->strULLName,
                            $clsRpt->strWidthLabel));
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showVolHours($clsRpt, $volRec, $dTotHours, $dTotUnHours, $lVolID){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'volHrs';
   $attributes->divImageID   = 'volHrsDivImg';
   openBlock('Volunteer Hours <span style="font-size: 9pt;">('
                .(number_format($dTotHours+$dTotUnHours, 2)).' hrs.)</span>', '', $attributes);
   $clsRpt->openReport();

   echoT('
      <table>
         <tr>
            <td style="padding-right: 20pt;">
               <b>Scheduled:</b>
            </td>
            <td style="text-align: right; ">'
               .number_format($dTotHours, 2).' hours
            </td>
            <td>'
               .strLinkView_VolHrsViaVolID($lVolID, true, 'View details', true).'
            </td>
         </tr>');

   echoT('
         <tr>
            <td style="vertical-align: bottom;">
               <b>Unscheduled:</b>
            </td>
            <td style="vertical-align: bottom; text-align: right;">'
               .number_format($dTotUnHours, 2).' hours
            </td>
            <td>'
               .strLinkView_VolHrsViaVolID($lVolID, false, 'View details', true).'&nbsp;&nbsp;&nbsp;'
               .strLinkAdd_VolUnschedHrs($lVolID, 'Log hours for an unscheduled activity', true).'&nbsp;'
               .strLinkAdd_VolUnschedHrs($lVolID, 'Log volunteer activity', false).'
            </td>
         </tr>
         <tr>
            <td style="vertical-align: bottom;">
               <b>Total:</b>
            </td>
            <td style="vertical-align: bottom; text-align: right;">'
               .number_format($dTotHours+$dTotUnHours, 2).' hours '.'
            </td>
            <td>'
               .strLinkView_VolDetailHrsViaYrMon(null, null, $lVolID, 'View All', true).'
            </td>
         </tr>
      </table>');

   $clsRpt->closeReport();
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showVolSchedule($clsRpt, $volRec, $lVolID, $lPastShifts, $lCurrentFutureShifts){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'volSchedule';
   $attributes->divImageID   = 'volScheduleDivImg';
   openBlock('Volunteer Schedule',
              strLinkView_VolScheduleViaVolID($lVolID, true, false, 'View volunteer\'s schedule', true).'&nbsp;'
             .strLinkView_VolScheduleViaVolID($lVolID, true, false, 'View volunteer\'s schedule', false),
             $attributes);
   echoT($clsRpt->openReport());

   echoT(
        $clsRpt->openRow()
       .$clsRpt->writeLabel('# Past Shifts:')
       .$clsRpt->writeCell(number_format($lPastShifts).'&nbsp;'
                          .strLinkView_VolScheduleViaVolID($lVolID, false, true, 'View volunteer\'s past shifts', true))
       .$clsRpt->closeRow());

   echoT(
        $clsRpt->openRow()
       .$clsRpt->writeLabel('# Upcoming Shifts:')
       .$clsRpt->writeCell(number_format($lCurrentFutureShifts).'&nbsp;'
                          .strLinkView_VolScheduleViaVolID($lVolID, false, false, 'View volunteer\'s current/future shifts', true))
       .$clsRpt->closeRow());


   echoT($clsRpt->closeReport());
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showVolSkills($clsRpt, $volRec, $lVolID, $lNumSingleVolSkills, $singleVolSkills){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'volSkills';
   $attributes->divImageID   = 'volSkillsDivImg';
   openBlock('Volunteer Skills',
                 strLinkEdit_VolSkills($lVolID, 'Edit Volunteer Skills', true).' '
                .strLinkEdit_VolSkills($lVolID, 'Edit Volunteer Skills', false), $attributes);
   $clsRpt->openReport();

   if ($lNumSingleVolSkills == 0){
      echoT('<i>No skills registered for this volunteer</i>');
   }else {
      echoT('<ul style="list-style-type: square; display:inline; margin-left: 0px; padding-left: 0px;">');
      foreach ($singleVolSkills as $skill){
         echoT('<li style="margin-left: 20px; padding-left: 3px;">'.htmlspecialchars($skill->strSkill).'</li>');
      }
      echoT('</ul>');
   }

   $clsRpt->closeReport();
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showCustomVolTableInfo($strPT, $lNumPTablesAvail){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'volPTables';
   $attributes->divImageID   = 'volPTablesDivImg';
   $attributes->bStartOpen   = false;
   openBlock('Personalized Tables <span style="font-size: 9pt;">('.$lNumPTablesAvail.')</span>', '', $attributes);

   echoT($strPT);

   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}











