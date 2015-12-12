<?php
   global $gbDateFormatUS, $genumDateFormat, $gstrFormatDatePicker;

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   $attributes = array('name' => 'frmCloneAttendance', 'id' => 'frmAddEdit');
   echoT(form_open('cprograms/attendance/cloneAttOpts/'.$lCProgID.'/'.$lARecID, $attributes));


   openBlock('Clone Attendance Record', '');

   echoT('<table class="enpView" style="width: 500pt;">');

      // source attendance record
   echoT($clsForm->strLabelRowOneCol('Source Attendance Record', 2, 'style="font-weight: bold;"', 221));
   echoT($clsForm->strLabelRow('attendanceID', str_pad($arec->lKeyID, 5, '0', STR_PAD_LEFT)
        .'&nbsp'.strLinkView_UFMFRecordViaRecID($cprog->lAttendanceTableID, $arec->lClientID, $arec->lKeyID, 'View attendance record', true), 1));
   echoT($clsForm->strLabelRow('enrollmentID', str_pad($arec->lEnrollID, 5, '0', STR_PAD_LEFT)
        .'&nbsp'.strLinkView_UFMFRecordViaRecID($cprog->lEnrollmentTableID, $arec->lClientID, $arec->lEnrollID, 'View enrollment record', true), 1));
   echoT($clsForm->strLabelRow('Client',
         htmlspecialchars($arec->strClientFName.' '.$arec->strClientLName)
         .'&nbsp;'.strLinkView_ClientRecord($arec->lClientID, 'View client record', true), 1));
   echoT($clsForm->strLabelRow('Date', date($genumDateFormat, $arec->dteAttendance), 1));

   echoT($clsForm->strLabelRow('Duration', number_format($arec->dDuration, 2), 1));
   echoT($clsForm->strLabelRow('Case Notes', nl2br(htmlspecialchars($arec->strCaseNotes)), 1));

      // target attendance record
   echoT($clsForm->strLabelRowOneCol('<br>Target Attendance Records', 2, 'style="font-weight: bold;"', 221));

      //----------------------
      // attendance date
      //----------------------
   $clsForm->strStyleExtraLabel = 'width: 100pt; padding-top: 8px;';
   echoT(strDatePicker('datepicker1', true));
   $clsForm->strExtraFieldText = form_error('txtADate');
   echoT($clsForm->strGenericDatePicker(
                      'Attendance Date', 'txtADate',      true,
                      $formData->txtADate,    'frmCloneAttendance', 'datepicker1'));
                      
      //----------------------
      // skip duplicates
      //----------------------
   $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
   $clsForm->strExtraFieldText = 
        '<br><i>If checked, skip clients who already
                have attendance records<br>for the specified date.</i>';
   echoT($clsForm->strGenericCheckEntry('Skip duplicates', 'chkSkipDups', 'true', false, $bSkipDups));
                      
                      
      // the client list
   $clsForm->strStyleExtraLabel = 'padding-top: 8px;';
   $strClientChk =
          strHTMLSetClearAllButton(true,  'frmCloneAttendance', 'chkClient[]', 'Select All').'&nbsp;&nbsp;'
         .strHTMLSetClearAllButton(false, 'frmCloneAttendance', 'chkClient[]', 'Clear All').'<br>'."\n";

   foreach ($clients as $client){
      if ($client->lNumERecs > 0){
         $lClientID = $client->lClientID;
         foreach ($client->erecs as $erec){
            $strEDates = ' <i>(enrollment: '.date('m/d/Y', $erec->dteStart).' - ';
            if (is_null($erec->dteEnd) || $erec->dteEnd==0){
               $strEDates .= 'ongoing)</i>';
            }else {
               $strEDates .= date('m/d/Y', $erec->dteEnd).')</i>';
            }
            $strClientChk .=
                  '<div><input type="checkbox" name="chkClient[]" '
                        .($client->bSelected ? 'checked' : '')
                        .' value="'.$client->lClientID.'_'.$erec->lKeyID.'">'
                  .strLinkView_ClientRecord($lClientID, 'View client record', true).'&nbsp;'
                  .htmlspecialchars($client->strClientLName.', '.$client->strClientFName)
                  .' '.$strEDates
                  .'</div>'."\n";
         }
      }
   }
   echoT($clsForm->strLabelRow('Clone to', $strClientChk, 1));


   echoT($clsForm->strSubmitEntry('Clone', 2, 'cmdSubmit', 'text-align: center; width: 150px;',
             'return(verifyBoxChecked(\'frmCloneAttendance\', \'chkClient[]\', \' (Clients)\'))'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');

   closeBlock();



