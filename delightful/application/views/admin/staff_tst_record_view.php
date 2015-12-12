<?php

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '130pt';

   $lLabelWidth = 120;

   showTimeSheetBlock($lTSTID, $clsRpt, $tst, $lLabelWidth);

   showTSProjectsBlock ($lTSTID, $groupsProj, $tst);
   showTSLocationsBlock($lTSTID, $groupsLoc,  $tst);
   showTSUsersBlock    ($lTSTID, $userAssignments, $clsRpt, $tst, $lLabelWidth);
//   showTSAdminsBlock   ($lTSTID, $userAssignments, $clsRpt, $tst, $lLabelWidth);

   showUserENPStats  ($clsRpt, $tst, $lLabelWidth);

   function showTSProjectsBlock($lTSTID, $groupsProj, $tst){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      showGroupInfo($lTSTID, $tst->strTSName, $groupsProj->lNumGroups, $groupsProj->groupList,
                             $groupsProj->inGroups, $groupsProj->lCntGroupMembership,
                             CENUM_CONTEXT_STAFF_TS_PROJECTS, 'timesheetRecView', null,
                             true);

   }

   function showTSLocationsBlock($lTSTID, $groupsLoc, $tst){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      showGroupInfo($lTSTID, $tst->strTSName, $groupsLoc->lNumGroups, $groupsLoc->groupList,
                             $groupsLoc->inGroups, $groupsLoc->lCntGroupMembership,
                             CENUM_CONTEXT_STAFF_TS_LOCATIONS, 'timesheetRecView', null,
                             true);
   }

   function showTSUsersBlock($lTSTID, $userAssignments, $clsRpt, $tst, $lLabelWidth){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'userDiv';
      $attributes->divImageID   = 'userDivImg';
      $attributes->bStartOpen   = true;
      openBlock('Staff Members Assigned to this Template', '', $attributes);

      $frmAtt = array('name' => 'frmAssignToTST');

      echoT(form_open('admin/timesheets/add_edit_tst_record/setTSTUsers/'.$lTSTID, $frmAtt));

      echoT('<br>'
            .strHTMLSetClearAllButton(true,  'frmAssignToTST', 'chkGroup[]', 'Select All ').'&nbsp;&nbsp;'
            .strHTMLSetClearAllButton(false, 'frmAssignToTST', 'chkGroup[]', 'Clear All').'<br>'
         );
      echoT('<div style="vertical-align: middle;">
                Assign Staff Members to this Time Sheet Template<br>
                <i>You must click "Update Assignments" for your changes to be recorded.</i><br></div>');

      echoT('
         <div id="scrollCB" style="height:150px;   width:440px; overflow:auto;  border: 1px solid black;">'."\n\n");

      echoT('<table class="enpRpt" style="width: 100%">');
      foreach ($userAssignments as $uA){
         $lUserID = $uA->lUserID;
         $strAssigned = '';
         echoT('
            <tr>
               <td class="enpRpt" style="width: 10px; vertical-align:top;">');
         if ($uA->bGrayed){
            echoT('&nbsp;');
            $strStyle = 'color: #999;';
            $strAssigned = ' <i>(assigned to <b>'.htmlspecialchars($uA->strAssignedTemplateName).'</b>)</i>';
         }else {
            $strStyle = '';
            echoT('
                  <input type="checkbox" 
                         name="chkGroup[]" 
                         value="'.$lUserID.'" '.($uA->bCheckedAssign ? 'checked' : '').'>');
         }
         echoT('
               </td>
               <td class="enpRpt" style="vertical-align:top;  '.$strStyle.'">'
                  .htmlspecialchars($uA->strLName.', '.$uA->strFName).$strAssigned.'
               </td>
            </tr>');
      }
      echoT('</table></div>

             <input type="submit" name="cmdSubmit" value="Update Assignments"
                 style=""
                 class="btn"
                    onmouseover="this.className=\'btn btnhov\'"
                    onmouseout="this.className=\'btn\'">');

      echoT(form_close('<br>'));

      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

/*
   function showTSAdminsBlock($lTSTID, $userAssignments, $clsRpt, $tst, $lLabelWidth){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'userADiv';
      $attributes->divImageID   = 'userADivImg';
      $attributes->bStartOpen   = true;
      openBlock('Staff Members Authorized To Edit Time Sheets', '', $attributes);
      
      $frmAtt = array('name' => 'frmAdminToTST');

      echoT(form_open('admin/timesheets/add_edit_tst_record/setTSTAdmins/'.$lTSTID, $frmAtt));

      echoT('<br>'
            .strHTMLSetClearAllButton(true,  'frmAdminToTST', 'chkGroup[]', 'Select All ').'&nbsp;&nbsp;'
            .strHTMLSetClearAllButton(false, 'frmAdminToTST', 'chkGroup[]', 'Clear All').'<br>'
         );
      echoT('<div style="vertical-align: middle;">
                Assign Staff Members to View/Edit Submitted Time Sheets<br>
                <i>You must click "Update Assignments" for your changes to be recorded.</i><br></div>');

      echoT('
         <div id="scrollCBAdmin" style="height:150px; width:310px; overflow:auto;  border: 1px solid black;">'."\n\n");

      echoT('<table class="enpRpt" style="width: 100%">');
      foreach ($userAssignments as $uA){
         $lUserID = $uA->lUserID;
         echoT('
            <tr>
               <td class="enpRpt" style="width: 10px; vertical-align:top;">
                  <input type="checkbox" 
                         name="chkGroup[]" 
                         value="'.$lUserID.'" '.($uA->bTSAdmin ? 'checked' : '').'>');
         echoT('
               </td>
               <td class="enpRpt" style="vertical-align:top; ">'
                  .htmlspecialchars($uA->strLName.', '.$uA->strFName).'
               </td>
            </tr>');
      }
      echoT('</table></div>

             <input type="submit" name="cmdSubmit" value="Update Assignments"
                 style=""
                 onClick="return(verifyBoxChecked(\'frmAdminToTST\', \'chkGroup[]\', \' (Staff members authorized to edit time sheets)\'));"
                 class="btn"
                    onmouseover="this.className=\'btn btnhov\'"
                    onmouseout="this.className=\'btn\'">');

      echoT(form_close('<br>'));

      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }
*/
   function showTimeSheetBlock($lTSTID, $clsRpt, $tst, $lLabelWidth){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock('Time Sheet Template',
                strLinkEdit_TimeSheetTemplate($lTSTID, 'Edit time sheet template', true));

      echoT(
          $clsRpt->openReport());

         // template ID
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Template ID:', $lLabelWidth)
         .$clsRpt->writeCell(str_pad($lTSTID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ());

         // Name
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Name:', $lLabelWidth)
         .$clsRpt->writeCell(htmlspecialchars($tst->strTSName))
         .$clsRpt->closeRow  ());

         // Reporting Period
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Reporting Period:', $lLabelWidth)
         .$clsRpt->writeCell($tst->enumRptPeriod)
         .$clsRpt->closeRow  ());

         // First day of week
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('First Day of Week:', $lLabelWidth)
         .$clsRpt->writeCell(ts_util\strXlateDayofWeek($tst->lFirstDayOfWeek))
         .$clsRpt->closeRow  ());

         // Time Granularity
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Time Granularity:', $lLabelWidth)
         .$clsRpt->writeCell($tst->enumGranularity.' minutes')
         .$clsRpt->closeRow  ());

         // Time Format
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Time Format:', $lLabelWidth)
         .$clsRpt->writeCell(($tst->b24HrTime ? '24' : '12').' Hr. ')
         .$clsRpt->closeRow  ());

         // Visibility
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Visibility:', $lLabelWidth)
         .$clsRpt->writeCell(($tst->bHidden ? 'Hidden' : 'Normal'))
         .$clsRpt->closeRow  ());

         // Notes
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Notes:', $lLabelWidth)
         .$clsRpt->writeCell(nl2br(htmlspecialchars($tst->strNotes)))
         .$clsRpt->closeRow  ());


      echoT($clsRpt->closeReport());

      closeBlock();
   }

   function showUserENPStats($clsRpt, $tst, $lLabelWidth){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'userENP';
      $attributes->divImageID   = 'userENPDivImg';
      openBlock('Record Information', '', $attributes);
      echoT(
         $clsRpt->showRecordStats($tst->dteOrigin,
                               $tst->strUCFName.' '.$tst->strUCLName,
                               $tst->dteLastUpdate,
                               $tst->strULFName.' '.$tst->strULLName,
                               $lLabelWidth));
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }


