<?php

   echoT('<br>'
       .strLinkAdd_TimeSheetTemplate('Add new time sheet template', true).'&nbsp;'
       .strLinkAdd_TimeSheetTemplate('Add new time sheet template', false).'<br>'
       );

   if ($lNumTST== 0){
      echoT('<br><br><i>There are currently no time sheet templates defined in your database.<br><br><br>');
      return;
   }
   
   $lLabelWidth = 150;
   showTSAdminsBlock($userAssignments, $lLabelWidth);

   openTSTTable();
   foreach ($timeSheetTemplates as $tst){
      writeTSTTable($tst);
   }
   closeTSTTable();

   function writeTSTTable($tst){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lTSTID = $tst->lKeyID;
      if ($tst->strNotes != ''){
         $tst->strNotes = '<br>'.nl2br(htmlspecialchars($tst->strNotes));
      }
      if ($tst->bHidden){
         $strHideStyle = 'color: #aaa;';
      }else {
         $strHideStyle = '';
      }
      
      $strProperties =
         '<table cellpadding="0" style="width: 100%;">
            <tr>
               <td style="width: 80pt;">
                  Reporting Period: 
               </td>
               <td style="">'
                  .$tst->enumRptPeriod.'
               </td>
            </tr>
               <td style="width: 80pt;">
                  First day of Week: 
               </td>
               <td style="">'
                  .ts_util\strXlateDayofWeek($tst->lFirstDayOfWeek).'
               </td>
            </tr>
            <tr>
               <td >
                  Time Granularity: 
               </td>
               <td>'
                  .$tst->enumGranularity.' minutes
               </td>
            </tr>
            <tr>
               <td >
                  Time Format: 
               </td>
               <td>'
                  .($tst->b24HrTime ? '24' : '12').' Hr. 
               </td>
            </tr>
            <tr>
               <td >
                  Visibility: 
               </td>
               <td>'
                  .($tst->bHidden ? 'Hidden' : 'Normal').'
               </td>
            </tr>
         </table>';
         
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="width: 50pt; text-align: center; '.$strHideStyle.'">'
               .strLinkView_TimeSheetTemplateRecord($lTSTID, 'View time sheet template record', true).'&nbsp;'
               .str_pad($lTSTID, 5, '0', STR_PAD_LEFT).'
            </td>
            <td class="enpRpt" style="width: 20pt; '.$strHideStyle.'">
               &nbsp;
            </td>
            <td class="enpRpt" style="width: 20pt; '.$strHideStyle.'">
               &nbsp;
            </td>
            <td class="enpRpt" style="width: 200pt; '.$strHideStyle.'"><b>'
               .htmlspecialchars($tst->strTSName).'</b>'.$tst->strNotes.'
            </td>
            <td class="enpRpt" style="width: 150pt; '.$strHideStyle.'">'
               .$strProperties.'
            </td>
         </tr>');   
   }

   function openTSTTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock('Time Sheet Templates', '');
      echoT('
        <table class="enpRpt" style="width: 600pt;">
           <tr>
              <td class="enpRptTitle" colspan="6">
                 Time Sheet Templates
              </td>
           </tr>');
           
      echoT('
         <tr>
            <td class="enpRptLabel" style="width: 50pt;">
               templateID
            </td>
            <td class="enpRptLabel" style="width: 20pt;">
               &nbsp;
            </td>
            <td class="enpRptLabel" style="width: 20pt;">
               &nbsp;
            </td>
            <td class="enpRptLabel" style="width: 200pt;">
               Name
            </td>
            <td class="enpRptLabel" style="width: 150pt;">
               Properties
            </td>
         </tr>');
   }

   function closeTSTTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('
         </table><br><br>');
      closeBlock();
   }
   
   function showTSAdminsBlock($userAssignments, $lLabelWidth){
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

      echoT(form_open('admin/timesheets/add_edit_tst_record/setTSTAdmins', $frmAtt));

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
               <td class="enpRpt" style="width: 10px; vertical-align:top;">');
         if ($uA->bAdmin){
            echoT('<img src="'.base_url().'images/misc/checkBox.gif" title="Admin" border="0">');
         }else {
            echoT('
                  <input type="checkbox" 
                         name="chkGroup[]" 
                         value="'.$lUserID.'" '.($uA->bTSAdmin ? 'checked' : '').'>');
         }
         echoT('
               </td>
               <td class="enpRpt" style="vertical-align:top; ">'
                  .htmlspecialchars($uA->strLName.', '.$uA->strFName)
                  .($uA->bAdmin ? ' <i>(system admin)</i>' : '')
                  .'
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
   

/*   
   function showTSUsersBlock($userAssignments, $lLabelWidth){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'userDiv';
      $attributes->divImageID   = 'userDivImg';
      $attributes->bStartOpen   = true;
      openBlock('Time Sheet Administrators', '', $attributes);

      $frmAtt = array('name' => 'frmAssignToTST');

      echoT(form_open('admin/timesheets/add_edit_tst_record/setTSTUsers', $frmAtt));

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
         if ($uA->bAdmin){
            echoT('<img src="'.base_url().'images/misc/checkBox.gif" title="Admin" border="0">');
            $strStyle = 'color: #999;';
            $strAssigned = ' <i>(admin)</i>';
         }else {
            $strStyle = '';
            echoT('
                  <input type="checkbox" 
                         name="chkGroup[]" 
                         value="'.$lUserID.'" '.($uA->bTSAdmin ? 'checked' : '').'>');
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
*/



