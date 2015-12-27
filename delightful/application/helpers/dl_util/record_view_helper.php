<?php
//---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012-2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
/*---------------------------------------------------------------------
      $this->load->helper('dl_util/record_view');
---------------------------------------------------------------------*/

function showSponsorshipInfo($clsRpt, $sponInfo, $lNumSponsors, $lPID, $clsSpon){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;

   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'allSponSumDiv';
   $attributes->divImageID   = 'allSponSumDivImg';
   openBlock('Sponsorship <span style="font-size: 9pt;"> ('.$lNumSponsors.')</span>',
               strLinkAdd_Sponsorship($lPID, 'Add new sponsorship', true,  'id="addNewSponI"').' '
              .strLinkAdd_Sponsorship($lPID, 'Add new sponsorship', false, 'id="addNewSponL"'), $attributes);

   if ($lNumSponsors == 0){
      echoT('<i>(no sponsorships)</i><br>');
   }else {
      echoT('<table>');
      foreach ($sponInfo as $idx=>$spon){
         $bInactive = $spon->bInactive;
         if ($bInactive){
            $strActiveStyle = 'color: #999;';
            $strInactive = ' Inactive as of '.date($genumDateFormat, $spon->dteInactive);
         }else {
            $strActiveStyle = '';
            $strInactive = '';
         }
         $lSponsorID = $spon->lKeyID;
         $lClientID  = $spon->lClientID;
         echoT('
                <tr>
                   <td style="'.$strActiveStyle.'">'
                      .strLinkView_Sponsorship($lSponsorID, 'view sponsorship', true, 'id="viewSponRec_'.$lSponsorID.'"').' '
                      .str_pad($spon->lKeyID, 5, '0', STR_PAD_LEFT).'
                   </td>
                   <td style="'.$strActiveStyle.'">'
                      .htmlspecialchars($spon->strSponProgram).'
                   </td>
                   <td style="'.$strActiveStyle.'">');

         if (is_null($lClientID)){
            echoT('<i>Client not set</i>');
         }else {
            echoT(
               strLinkView_ClientRecord($lClientID, 'View client record', true, 'id="viewClientRec_'.$lClientID.'"').' '
              .$spon->strClientSafeNameFL.' ('.htmlspecialchars($spon->strLocation).')');
         }
         echoT($strInactive.'
                   </td>
                </tr>');
      }
      echoT('</table>');
   }
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showSingleSponsorshipInfo($clsRpt, $strTitle, $clsSpon){
//---------------------------------------------------------------------
// use for single sponsorship
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'ssponDiv';
   $attributes->divImageID   = 'ssponDivImg';
   openBlock($strTitle, '', $attributes);

   $clsRpt->openReport();
   if ($clsSpon->lNumSponsors == 0){
      echoT('<i>(no sponsorships)</i><br>');
   }else {
      foreach ($clsSpon->sponInfo as $idx=>$spon){
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Sponsor ID:')
            .$clsRpt->writeCell(strLinkView_Sponsorship($spon->lKeyID, 'view sponsorship', true).' '
                         .str_pad($spon->lKeyID, 5, '0', STR_PAD_LEFT))
            .$clsRpt->closeRow());

         $lFID = $spon->lForeignID;
         if ($spon->bSponBiz){
            $strLinkFID = strLinkView_Biz($lFID, 'View business record', true);
         }else {
            $strLinkFID = strLinkView_PeopleRecord($lFID, 'View people record', true);
         }
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Sponsor:')
            .$clsRpt->writeCell($strLinkFID.' '.str_pad($lFID, 5, '0', STR_PAD_LEFT)
                          .' '.$spon->strSponSafeNameFL)
            .$clsRpt->closeRow()

            .$clsRpt->openRow   ()
            .$clsRpt->writeLabel('Sponsorship Program:')
            .$clsRpt->writeCell(htmlspecialchars($spon->strSponProgram))
            .$clsRpt->closeRow()

            .$clsRpt->openRow   ()
            .$clsRpt->writeLabel('Client:'));

         if (is_null($spon->lClientID)){
            echoT($clsRpt->writeCell('<i>Client not set</i>'));
         }else {
            echoT(
               $clsRpt->writeCell(
                  strLinkView_Client($spon->lClientID, 'View client record', true).' '
                    .$spon->strClientSafeNameFL.' ('.htmlspecialchars($spon->strLocation).')'));
         }
         echoT($clsRpt->closeRow());
      }
   }
   echoT($clsRpt->closeReport());
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}




function showGroupInfo($lFID, $strSafeName, $lNumGroups, $groupList,
                       $inGroups, $lCntGroupMembership,
                       $enumGroupType, $strFrom, $enumSubGroup=null,
                       $bStartOpen = false){
//---------------------------------------------------------------------
// depricated; use helpers/groups/groups_helper
//   see views/personalization/uf_table_rec_view.php for usage
//---------------------------------------------------------------------
   global $gstrSeed, $gdteNow, $gbAdmin;
   
   $bUserPermissionsGroup = $enumGroupType==CENUM_CONTEXT_USER;
   $bGroupFullAccess = ($bUserPermissionsGroup && $gbAdmin) || (!$bUserPermissionsGroup);
   
   switch ($enumGroupType){
      case CENUM_CONTEXT_STAFF:  $strBlockLabel = 'Staff Groups';  break;        
      case CENUM_CONTEXT_USER:   $strBlockLabel = 'User Permissions Groups';   break;
      case CENUM_CONTEXT_STAFF_TS_PROJECTS:  $strBlockLabel = 'Billable Projects'; break;
      case CENUM_CONTEXT_STAFF_TS_LOCATIONS: $strBlockLabel = 'Locations';         break;
        
      default: 
         $strBlockLabel = 'Group Membership';
         break;
   }
   $strBlockLabel .= '<span style="font-size: 9pt;"> ('.$lCntGroupMembership.')</span>';

   $attributes = new stdClass;
   if ($bStartOpen) $attributes->bStartOpen   = true;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'groupDiv'.$enumGroupType.$lFID;
   $attributes->divImageID   = 'groupDivImg'.$enumGroupType.$lFID;
   openBlock($strBlockLabel, '', $attributes);
   echoT('<b>'.$strSafeName.'</b> is a member of '.$lCntGroupMembership.' group'.($lCntGroupMembership==1 ? '' : 's').'<br>'."\n");

   echoT('<table width="100%" border="0">');
   if ($lCntGroupMembership > 0){
      foreach ($inGroups as $clsGroup){
         if ($bGroupFullAccess){
            $strLinks = 
              '<td width="50" valign="top">'
                  .strLinkView_GroupMembership($clsGroup->lGroupID, 'view group membership', true).'&nbsp;&nbsp;'
                  .strLinkRem_GroupMembership($enumGroupType, $enumSubGroup, $clsGroup->lGroupID, 
                                              $lFID, $strFrom,
                                              'remove from this group', true, true).'
               </td>'."\n";
         }else {
            $strLinks = '<td style="width: 5px;">&nbsp;</td>'."\n";
         }
         echoT('
            <tr>'
               .$strLinks.'
               <td width="300" valign="top">'
                  .htmlspecialchars($clsGroup->strGroupName));

         if ($clsGroup->strGroupNote != ''){
            echoT('<br><i>'.nl2br(htmlspecialchars($clsGroup->strGroupNote)).'</i>'."\n");
         }

         echoT('
               </td>
               <td>
                  &nbsp;
               </td>
            </tr>'."\n");
      }
   }
   echoT('</table>'."\n");
   
   if ( bAllowAccess('editGroupMembership', $enumGroupType) ){
      if ($bGroupFullAccess){
         if ($lNumGroups <= $lCntGroupMembership){
            echoT('<br><i>No additional groups are available for membership</i>');
         }else {
            echoT('<br>Add <b>'.$strSafeName.'</b> to the following groups:<br>
                    <font style="font-size: 8pt;"><i>Ctrl-click to select more than one group</i></font><br>');

            $strAnchorBase = 'groups/group_util/addMemToGroup/'.$lFID.'/'.$strFrom.'/';
            switch ($enumGroupType){
               case CENUM_CONTEXT_PEOPLE:             $strAnchorBase .= 'person';       break;
               case CENUM_CONTEXT_BIZ:                $strAnchorBase .= 'biz';          break;
               case CENUM_CONTEXT_CLIENT:             $strAnchorBase .= 'client';       break;
               case CENUM_CONTEXT_VOLUNTEER:          $strAnchorBase .= 'volunteers';   break;
               case CENUM_CONTEXT_SPONSORSHIP:        $strAnchorBase .= 'sponsors';     break;
               case CENUM_CONTEXT_STAFF:              $strAnchorBase .= 'staff';        break;
               case CENUM_CONTEXT_STAFF_TS_PROJECTS:  $strAnchorBase .= 'tsprojects';   break;
               case CENUM_CONTEXT_STAFF_TS_LOCATIONS: $strAnchorBase .= 'tslocations';  break;
               case CENUM_CONTEXT_USER:               $strAnchorBase .= 'user/'.$enumSubGroup;   break;

               default:
                  screamForHelp($enumGroupType.': invalid group type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }
            echoT(form_open($strAnchorBase)."\n");
            echoT('
               <select name="ddlGroups[]" multiple size="5">'."\n");

            foreach ($groupList as $clsGroup){
               if ($clsGroup->dteExpire > $gdteNow){
                  echoT('
                     <option value="'.$clsGroup->lKeyID.'">'
                        .htmlspecialchars($clsGroup->strGroupName).'
                     </option>'."\n");
               }
            }

            echoT('
               </select><br>'."\n");
            echoT(
                '
                 <input type="submit"
                    name="cmdAdd"
                    value="Add"
                    onclick="this.disabled=1; this.form.submit();"
                    class="btn"
                       onmouseover="this.className=\'btn btnhov\'"
                       onmouseout="this.className=\'btn\'">
                 </form>'."\n");
         }
      }
   }
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showGifts($lFID, $bBiz,
                   $strCumGiftsNonSoftMon, $strCumGiftsNonSoftInKind,
                   $strCumGiftsSoft,       $strCumSpon, $lNumPledges,
                   $lTotHard, $lTotSoft, $lTotInKind, $lNumSponPay){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gstrCurrency;

   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'giftSumDiv';
   $attributes->divImageID   = 'giftSumDivImg';
   $attributes->bStartOpen   = false;

   openBlock('Donations <span style="font-size: 9pt;">('
                 .$lTotHard.' hard / '.$lTotSoft.' soft / '.$lTotInKind.' in-kind / '.$lNumSponPay.' spon pay)</span><br>',
              strLinkView_GiftsHistory($lFID, 'Gift history', true).'&nbsp;'
             .strLinkView_GiftsHistory($lFID, 'Gift history', false)
             .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
             .strLinkAdd_Gift($lFID, 'New donation', true).'&nbsp;'
             .strLinkAdd_Gift($lFID, 'New donation', false)
             .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
             .strLinkAdd_Pledge($lFID, 'New pledge', true).'&nbsp;'
             .strLinkAdd_Pledge($lFID, 'New pledge', false), $attributes             
             );

   if (bAllowAccess('showGiftHistory')){
      echoT('
         <table border="0">
            <tr>
               <td class="enpView" style="width: 120pt;">
                  Total Monetary Gifts:
               </td>
               <td class="enpView" style="text-align: right; 
                  vertical-align: top; width: 70pt;" nowrap>'
                  .$gstrCurrency
                  .$strCumGiftsNonSoftMon.'
               </td>
               <td class="enpView">&nbsp;</td>
            </tr>');

      echoT('
            <tr>
               <td class="enpView">
                  Total In-Kind Gifts:
               </td>
               <td class="enpView" style="text-align: right;">'.$gstrCurrency.' '
                  .$strCumGiftsNonSoftInKind.'
               </td>
               <td class="enpView">&nbsp;</td>
            </tr>');

      echoT('
            <tr>
               <td class="enpView">
                  Sponsorship Payments:<br>
                  <i>included in monetary gifts</i>
               </td>
               <td class="enpView" style="text-align: right;">'.$gstrCurrency.' '
                  .$strCumSpon.'
               </td>
               <td class="enpView">&nbsp;</td>
            </tr>');

      echoT('
            <tr>
               <td class="enpView">
                  Total Soft Donations:
               </td>
               <td class="enpView" style="text-align: right;">'.$gstrCurrency.' '
                  .$strCumGiftsSoft.'
               </td>
               <td class="enpView">&nbsp;</td>
            </tr>');
            
      if ($lNumPledges > 0){
         $strLinkPledges = strLinkView_PledgeViaFID($lFID, 'View pledges', true);
      }else {
         $strLinkPledges = '';
      }
      echoT('
            <tr>
               <td class="enpView">
                  Pledges:
               </td>
               <td class="enpView" style="text-align: center;">'
                  .$lNumPledges.'&nbsp;'.$strLinkPledges.'
               </td>
               <td class="enpView">&nbsp;</td>
            </tr>');

      echoT('
         </table>');
      
   }

   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function strBuildCumlativeTable($lNumCumulative, $cumulative, $bSetWidth){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if ($lNumCumulative==0){
      $strCumulative = '<table><tr><td style="text-align:center; width:165pt; color: #b0b0b0;">n/a</td></tr></table>';
   }else {
      $strCumulative = '<table border="0" cellpadding="0" cellspacing="0" style="vertical-align: top; margin-top: 0pt;">';
      foreach($cumulative as $cum){
         $strCumulative .=
                    '<tr>
                        <td '.($bSetWidth ? ' style="width: 15pt;" ' : '').'>'
                           .$cum->strCurSymbol.'
                        </td>
                        <td style="text-align: right; '.($bSetWidth ? ' width: 120pt; ' : '').'";">'
                           .number_format($cum->curCumulative, 2, '.', ',').'
                        </td>
                        <td style="text-align: right; '.($bSetWidth ? ' width: 30pt; ' : '').'";">'
                           .$cum->strFlagImg.'
                        </td>
                     </tr>';

      }
      $strCumulative .= '</table>';
   }
   return($strCumulative);
}
/*
function showReminderBlock($clsRem, $lFID, $enumRemType, $lViewerUserID=null){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat, $gdteNow, $glUserID;

   if (is_null($lViewerUserID)) $lViewerUserID = $glUserID;

   $bDisplayAllTypes = $enumRemType==CENUM_CONTEXT_USER;

   $lNumRem = $clsRem->lNumReminders($enumRemType, $lFID, false, $lViewerUserID);

   $strBlockLabel = strLinkAdd_Reminder($enumRemType,  $lFID, 'Add reminder', true).'&nbsp;'
                   .strLinkAdd_Reminder($enumRemType,  $lFID, 'Add reminder', false)
                   .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
   if ($lNumRem > 0) {
      $strBlockLabel .=
                  strLinkView_ReminderViaObject($enumRemType, $lFID, 'View all '.$lNumRem.' reminders', true)
                 .strLinkView_ReminderViaObject($enumRemType, $lFID, 'View all '.$lNumRem.' reminders', false);
   }
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'reminderDiv';
   $attributes->divImageID   = 'reminderDivImg';
   openBlock('Reminders', $strBlockLabel, $attributes);

   $clsRem->lFID           = $lFID;
   $clsRem->enumLookupType = $bDisplayAllTypes ? 'CurrentViaEnumRemTypeUserID' : 'CurrentViaEnumRemTypeFID';
   $clsRem->enumSort       = 'currentFirst';
   $bNoReminders           = true;

   foreach ($clsRem->enumRemTypeList as $enumRT){
      $clsRem->enumRemType    = $enumRT;
      $clsRem->loadReminders();

      if ($clsRem->lNumReminders > 0){
         foreach ($clsRem->reminders as $clsSingleRem){
            $lRID = $clsSingleRem->lKeyID;
            $lCurrentIdx = $clsRem->lCurrentRemIdx($clsSingleRem, $gdteNow);

            if (!is_null($lCurrentIdx)){
               $bNoReminders = false;
               if ($bDisplayAllTypes){
                  $strRemLink = '<i>'.$clsRem->strHTMLOneLineLink($clsSingleRem).'</i>';
               }else {
                  $strRemLink = '';
               }

               $strRem = '
                        <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                               <td style="vertical-align: top;"><b>'
                                 .date($genumDateFormat, $clsSingleRem->dates[$lCurrentIdx]->dteDisplayStart)
                                .':</b>&nbsp;&nbsp;
                               </td>
                               <td style="vertical-align: top;"> '
                                  .htmlspecialchars($clsSingleRem->strTitle).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                             .'</td>
                               <td style="vertical-align: top;">'
                                  .strLinkRem_Reminder($lRID, $lFID, $enumRemType, 'Remove reminder', true, true,
                                             ' style="vertical-align: top;" ')
                             .'</td>
                               <td style="width: 300pt;vertical-align: top;">'
                                 .$strRemLink
                             .'</td>
                           </tr>
                        </table>';

               if ($clsSingleRem->strReminderNote != ''){
                  $strRem .= ''.nl2br(htmlspecialchars($clsSingleRem->strReminderNote));
               }
               echoT($strRem.'<br><br>'."\n\n");
            }
         }
      }
   }
   if ($bNoReminders) echoT('<i>No current reminders</i>');
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}
*/
function showClientInfo(
                  $clsRpt,      $lCID,        $clsClient,
                  $clsDateTime, $bShowAddNew, $lSponID,
                  $bViewOnly,   $strClientPreLabel=''){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gbDateFormatUS, $genumDateFormat, $glclsDTDateFormat;

   if (is_null($lCID)){
      if ($bViewOnly){
         $attributes = new stdClass;
         $attributes->lTableWidth  = 900;
         $attributes->divID        = 'clientA';
         $attributes->divImageID   = 'clientADivImg';
         openBlock('Client Information', '', $attributes);
      }else {
         openBlock('Client Information', '');
      }
      echoT('<i>No client has been linked to this sponsorship.</i>');
      if ($bShowAddNew){
         echoT(
           '&nbsp;&nbsp;&nbsp;'
             .strLinkAdd_ClientToSpon($lSponID, 'Add client to this sponsorship', true).'&nbsp'
             .strLinkAdd_ClientToSpon($lSponID, 'Add client to this sponsorship', false).'&nbsp'
             );
      }
      if ($bViewOnly){
         $attributes = new stdClass;
         $attributes->bCloseDiv = true;
         closeBlock($attributes);
      }else {
         closeBlock();
      }
   }else {

      $clsC = $clsClient->clients[0];
      if ($bShowAddNew){
         echoT(strLinkAdd_Client('Add new client', true).' '
              .strLinkAdd_Client('Add new client', false).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
              .strLinkSpecial_SearchClient('Search clients', true).'&nbsp;'
              .strLinkSpecial_SearchClient('Search clients', false)
              
              .'<br>');
      }

      if (isset($clsC->strFlagsTable)){
         $strCFlags = '&nbsp;'.$clsC->strFlagsTable;
      }else {
         $strCFlags = '';
      }
      
      if ($bViewOnly){
         $strBlockLink    = strLinkView_ClientRecord($lCID, 'View client record', true).'&nbsp;'
                           .strLinkView_ClientRecord($lCID, 'View client record', false);
         $strVocLink      = '';
         $strLinkLocation = '';
         $strLinkStatCat  = '';
         $strLinkStatHist = '';
         $strLinkRem      = '';
         $attributes = new stdClass;
         $attributes->lTableWidth  = 900;
         $attributes->divID        = 'clientB';
         $attributes->divImageID   = 'clientBDivImg';
         openBlock($strClientPreLabel.$clsClient->clients[0]->cv_strVocClientS,
                   $strBlockLink.$strCFlags, $attributes);
      }else {
/*
         $strBlockLink    = strLinkEdit_Client       ($lCID, 'Edit client record', true ).'&nbsp;'
                           .strLinkEdit_Client       ($lCID, 'Edit client record', false).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                           .strLinkView_ClientAssessmentViaCID($lCID, 'Client Assessments', true ).'&nbsp;'
                           .strLinkView_ClientAssessmentViaCID($lCID, 'Client Assessments', false);
*/
         $strBlockLink    = strLinkEdit_Client       ($lCID, 'Edit client record', true ).'&nbsp;'
                           .strLinkEdit_Client       ($lCID, 'Edit client record', false);
         $strVocLink      = strLinkEdit_ClientRecVoc        ($lCID, 'Edit this client\'s vocabulary', true).'&nbsp;';
         $strLinkLocation = strLinkView_ClientsViaLocation  ($clsC->lLocationID, 'View this location\'s directory', true);
         $strLinkStatCat  = strLinkEdit_ClientRecStatCat    ($lCID, 'Edit this client\'s status category', true).'&nbsp;';
         
         $strLinkStatHist = strLinkView_ClientStatusHistory ($lCID, 'status history', true);
         $strLinkRem      = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                   .strLinkRem_Client($lCID, 'Remove client record', true, true);
         openBlock($strClientPreLabel.$clsClient->clients[0]->cv_strVocClientS,
                   $strBlockLink.$strCFlags.$strLinkRem);
      }

      echoT(
          $clsRpt->openReport()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Client ID:')
         .$clsRpt->writeCell(str_pad($lCID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Name:')
         .$clsRpt->writeCell (htmlspecialchars($clsC->strFName.' '
                                .$clsC->strMName.' '.$clsC->strLName.' ('.$clsC->enumGender.')'))
         .$clsRpt->closeRow  ()

         //------------------
         // enrollment date
         //------------------
         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Enrollment Date:')
         .$clsRpt->writeCell (date($genumDateFormat, $clsC->dteEnrollment))
         .$clsRpt->closeRow  ());

         //---------------
         // birthday
         //---------------
      $mdteBirth = $clsC->dteBirth;
      $clsDateTime->setDateViaMySQL(0, $mdteBirth);
      $strAgeBDay = $clsDateTime->strPeopleAge(0, $mdteBirth, $lAgeYears, $glclsDTDateFormat);
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Birth Date:')
         .$clsRpt->writeCell ($strAgeBDay)
         .$clsRpt->closeRow  ());

         //---------------
         // location
         //---------------
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Location:')
         .$clsRpt->writeCell ($strLinkLocation.htmlspecialchars($clsC->strLocation))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Max # of Sponsors:')
         .$clsRpt->writeCell ($clsC->lMaxSponsors)
         .$clsRpt->closeRow  ());

         //------------------------
         // Address
         //------------------------
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Address:')
         .$clsRpt->writeCell($clsC->strAddress)
         .$clsRpt->closeRow  ());

         //------------------------
         // Email
         //------------------------
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Email:')
         .$clsRpt->writeCell($clsC->strEmailFormatted)
         .$clsRpt->closeRow  ());

         //------------------------
         // Phone
         //------------------------
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Phone:')
         .$clsRpt->writeCell(htmlspecialchars(strPhoneCell($clsC->strPhone, $clsC->strCell)))
         .$clsRpt->closeRow  ());

         //---------------
         // Vocabulary
         //---------------
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Vocabulary:')
         .$clsRpt->writeCell ($strVocLink.$clsC->cv_strVocTitle)
         .$clsRpt->closeRow  ());

         //------------------
         // Status Category
         //------------------
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Status Category:')
         .$clsRpt->writeCell ($strLinkStatCat.$clsC->strStatusCatName)
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Current Status:')
         .$clsRpt->writeCell ($strLinkStatHist
                             .$clsC->curStat_strStatus.' <small>(set on '
                             .date($genumDateFormat, $clsC->curStat_dteStatus).')</small>')
         .$clsRpt->closeRow  ());

         //------------------
         // Attributed To
         //------------------
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Attributed To:')
         .$clsRpt->writeCell (htmlspecialchars($clsC->strAttrib))
         .$clsRpt->closeRow  ());

         //------------------
         // Bio
         //------------------
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Bio:')
         .$clsRpt->writeCell (nl2br(htmlspecialchars($clsC->strBio)))
         .$clsRpt->closeRow  ());

      echoT($clsRpt->closeReport());

      if ($bViewOnly){
         $attributes = new stdClass;
         $attributes->bCloseDiv = true;
         closeBlock($attributes);
      }else {
         closeBlock();
      }
   }
}

function strSponsorFinancialSummary(&$clsRpt, &$clsSCP, $lSponID, $strWidthLabel){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $clsACO = new madmin_aco;

   $clsRpt->strWidthLabel = $strWidthLabel;

      //---------------
      // Charges
      //---------------
   $clsSCP->chargeHistoryTotals($lSponID);
   $strCharge =
           strLinkAdd_SponsorCharges($lSponID, 'Add new charge', true).' '
          .strLinkAdd_SponsorCharges($lSponID, 'Add new charge', false).'<br>';

   if ($clsSCP->lNumChargesTotal==0){
      $strCharge .= '<i>No charges have been applied.</i>';
   }else {
      $strCharge .=
            '<table>';
      foreach($clsSCP->chargesTotal as $clsACOCharge){
         $strCharge .=
                '<tr>
                    <td>'.$clsACOCharge->strACOFlagImg.'</td>
                    <td>'.$clsACOCharge->strACOCurSym .'</td>
                    <td style="text-align:right;">'.number_format($clsACOCharge->curTotal, 2).'</td>
                 </tr>';
      }
      $strCharge .= '</table>';
   }

      //---------------
      // Payments
      //---------------
   $clsSCP->cumulativeSponsorshipViaSponID($clsACO, $lSponID);
   $strPay =
           strLinkAdd_SponsorPayment($lSponID, 'Add new payment', true).' '."\n"
          .strLinkAdd_SponsorPayment($lSponID, 'Add new payment', false).'<br>'."\n";

   if ($clsSCP->lNumSponPayCumulative==0){
      $strPay .= '<i>No payments have been made.</i><br><br>';
   }else {
      $strPay .=
            '<table>';
      foreach($clsSCP->sponPayCumulative as $clsACOPay){
         $strPay .=
                '<tr>
                    <td>'.$clsACOPay->strFlagImg.'</td>
                    <td>'.$clsACOPay->strCurSymbol.'</td>
                    <td style="text-align:right;">'.number_format($clsACOPay->curCumulative, 2).'</td>
                 </tr>';
      }
      $strPay .= '</table><br>'."\n";
   }
   $clsSCP->balanceSummary();

   $strOut =
       $clsRpt->openReport()."\n"
      .$clsRpt->openRow   ()."\n"
      .$clsRpt->writeLabel('Charges:')."\n"
      .$clsRpt->writeCell ($strCharge)."\n"
      .$clsRpt->closeRow  ()."\n"

      .$clsRpt->openRow   ()."\n"
      .$clsRpt->writeLabel('Payments:')."\n"
      .$clsRpt->writeCell ($strPay)."\n"
      .$clsRpt->closeRow  ()."\n"

      .$clsRpt->openRow   ()."\n"
      .$clsRpt->writeLabel('Balance:')."\n"
      .$clsRpt->writeCell ($clsSCP->strBalanceSummaryHTMLTable())."\n"
      .$clsRpt->closeRow  ()."\n"
      .$clsRpt->closeReport("\n");

   return($strOut);
}

function showImageInfo($enumContext, $lFID, $strLabel, &$imageDoc, $lNumImages, $lNumImagesTot){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   showImageDocumentInfo(CENUM_IMGDOC_ENTRY_IMAGE, $enumContext, $lFID,
                         $strLabel, $imageDoc, $lNumImages, $lNumImagesTot);
}

function showDocumentInfo($enumContext, $lFID, $strLabel, &$imageDoc, $lNumDocs, $lNumDocsTot){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   showImageDocumentInfo(CENUM_IMGDOC_ENTRY_PDF, $enumContext, $lFID,
                         $strLabel, $imageDoc, $lNumDocs, $lNumDocsTot);
}

function showImageDocumentInfo($enumEntryType,    $enumContext, $lFID,
                               $strLabel,         &$imageDocs,  $lNumImageDocs,
                               $lNumImageDocsTot, $lTableWidth=700, $bShowViewAll=true,
                               $bCollapsedDiv=true){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------

   $bImage = $enumEntryType == CENUM_IMGDOC_ENTRY_IMAGE;
   $bPDF   = $enumEntryType == CENUM_IMGDOC_ENTRY_PDF;
   if ($lNumImageDocsTot > 0 && $bShowViewAll){
      $strView =
                '&nbsp;&nbsp;&nbsp;&nbsp;'
               .strLinkView_ImageDocs($enumContext, $enumEntryType, $lFID, 'View all '.$enumEntryType.'s', true).'&nbsp;'
               .strLinkView_ImageDocs($enumContext, $enumEntryType, $lFID, 'View all '.$enumEntryType.'s', false);
   }else {
      $strView = '';
   }

   if ($bCollapsedDiv){
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = ($bImage ? 'image' : 'doc').'LibraryDiv';
      $attributes->divImageID   = ($bImage ? 'image' : 'doc').'LibraryDivImg';
   }else {
      $attributes = null;
   }
   
   $strLabel .='<span style="font-size: 9pt;"> ('.$lNumImageDocsTot.')</span>';

   openBlock($strLabel,
                strLinkAdd_ImageDoc  ($enumContext, $enumEntryType, $lFID, 'Add new '.$enumEntryType, true).'&nbsp;'
               .strLinkAdd_ImageDoc  ($enumContext, $enumEntryType, $lFID, 'Add new '.$enumEntryType, false)
               .$strView, $attributes);

   echoT('Showing '.$lNumImageDocs.' of '.$lNumImageDocsTot.' '.$enumEntryType.'s<br>');


   echoT(strImageDocTable($enumEntryType, $enumContext,   $lFID,
                          $imageDocs,     $lNumImageDocs, $lTableWidth));

   if ($bCollapsedDiv){
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }else {
      closeBlock();
   }
}

