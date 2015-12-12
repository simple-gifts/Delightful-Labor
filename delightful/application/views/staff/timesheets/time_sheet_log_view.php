<?php

   echoT('<br>'.strChangeYear($lDisplayYear, $lCurrentYear, $lUserID).'<br>');

   openBlock($lDisplayYear.' Unsubmitted Time Sheets for '.$userRec->strSafeName, '');
   closeBlock();

      // is the user assigned to a time sheet?
   if (is_null($lTSTID)){
      echoT('<i>You are not currently assigned to a time sheet template. You must be<br>
             assigned to a template to log your hours.<br><br>
             Please contact your system administrator for assistance.</i><br><br>');
   }else {
      viewTSEntryOptions($lDisplayYear, $lUserID, $tst, $lNumTSDDL, $potentialTS);
   }


   openBlock($lDisplayYear.' Time Sheet Log', '');

   if ($lNumLogRecs == 0){
      echoT('<br><i>You have no time sheet entries for this time period.</i><br><br>');
   }else {
      $lMonthGroup = -1;
      $divAttributes = new stdClass;
      $divAttributes->lTableWidth      = 900;
      $divAttributes->lUnderscoreWidth = 200;
      $divAttributes->bAddTopBreak     = true;
      $divAttributes->lMarginLeft      = 14;

      foreach ($logRecs as $logRec){
         writeTSEntryRow($divAttributes, $lMonthGroup, $logRec);
      }
      echoT('</table>'."\n");
      $divAttributes->bCloseDiv = true;
      closeBlock($divAttributes);
   }

   closeBlock();

   function strChangeYear($lDisplayYear, $lCurrentYear, $lUserID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lTmpYear = $lCurrentYear;
      $strYearDDL =
         '<select name="ddlYear">'."\n";

      for ($idx=0; $idx <= 4; ++$idx){
         $strYearDDL .= '<option value="'.$lTmpYear.'" '.($lTmpYear==$lDisplayYear ? 'selected' : '').'>'.$lTmpYear.'</option>'."\n";
         --$lTmpYear;
      }
      $strYearDDL .= '</select>&nbsp;';

      $strButton =
                '<input type="submit" name="cmdSubmit" value="Change Year"
                    style="font-size: 8pt; height: 14pt;"
                    class="btn"
                       onmouseover="this.className=\'btn btnhov\'"
                       onmouseout="this.className=\'btn\'">';

      $att = array('style' => 'display: inline');
      $strChangeYear =
         form_open('staff/timesheets/ts_log/changeYear/'.$lUserID, $att)
        .$strYearDDL
        .$strButton
        .form_close();

      return($strChangeYear);
   }

   function writeTSEntryRow(&$attributes, &$lMonthGroup, $logRec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $lMon        = (int)date('n', $logRec->dteTSEntry);
      $lTSLogID    = $logRec->lKeyID;
      $bPublished  = !is_null($logRec->dteSubmitted);
      $dteStarting = $logRec->dteTSEntry;
      if ($lMonthGroup != $lMon){
         if ($lMonthGroup > 0){
            echoT('</table>'."\n");
            $attributes->bCloseDiv = true;
            closeBlock($attributes);
            $attributes->bCloseDiv = false;
         }

         $attributes->divID            = 'month'.$lMon.'Div';
         $attributes->divImageID       = 'month'.$lMon.'DivImg';
         $attributes->bStartOpen       = true;

         openBlock(date('F Y', $logRec->dteTSEntry), '', $attributes);
         echoT('<table style="border: 1px solid black;">
                <tr>
                    <td style="width: 50pt; color: #fff; background-color: #888;">
                       <b>entryID</b>
                    </td>
                    <td style="width: 50pt; color: #fff; background-color: #888;">
                       <b>Submitted?</b>
                    </td>
                    <td style="width: 200pt; color: #fff; background-color: #888;">
                      <b>Time Frame</b>
                    </td>
                    <td style="width: 150pt; color: #fff; background-color: #888;">
                      <b>Template</b>
                    </td>
                    <td style="width: 80pt; color: #fff; background-color: #888;">
                      <b>Reporting Period</b>
                    </td>
                    <td style="width: 50pt; color: #fff; background-color: #888;">
                       <b>Total Hours</b>
                    </td>

                </tr>'."\n");
         $lMonthGroup = $lMon;
      }

      echoT('<tr class="makeStripe">
         <td style="width: 50pt; text-align: center; vertical-align: top;">'
            .strLinkView_TSLogEntries($lTSLogID, 'View Time Sheet', true).'&nbsp;'
            .str_pad($lTSLogID, 5, '0', STR_PAD_LEFT).'
         </td>');
         
      if ($bPublished){
         if (bAllowAccess('timeSheetAdmin')){
            $strUnsubmit = '<br>'
                   .strLink_TSLogEntryUnsubmit($lTSLogID, 'Unsubmit', true).'&nbsp;'
                   .strLink_TSLogEntryUnsubmit($lTSLogID, 'Unsubmit', false);
         }else {
            $strUnsubmit = '';
         }
         
         echoT('
            <td style="width: 80pt; text-align: center; center; vertical-align: top;">Yes: '
               .date($genumDateFormat, $logRec->dteSubmitted).$strUnsubmit.'
            </td>');
      }else {
         echoT('
            <td style="width: 80pt; text-align: center; center; vertical-align: top;">'
               .strLinkEdit_TSLog($lTSLogID, 'Edit Time Sheet', true).' No
            </td>');
      }
      echoT('
         <td style="width: 50pt; text-align: left; center; vertical-align: top;">
            <font style="font-size: 8pt;">Week beginning: </font>'
            .date('l, F jS, Y', $dteStarting).'
         </td>');
      echoT('
         <td style="width: 150pt; center; vertical-align: top;">'
            .htmlspecialchars($logRec->strTSName).'
         </td>');
      echoT('
         <td style="width: 80pt; center; vertical-align: top;">'
            .$logRec->enumRptPeriod.'
         </td>');
      echoT('
         <td style="width: 50pt; text-align: right; padding-right: 3pt; center; vertical-align: top;">'
            .strDurationViaMinutes($logRec->lCumulativeMinutes).'
         </td>
      </tr>');
   }

   function viewTSEntryOptions($lDisplayYear, $lUserID, $tst, $lNumTSDDL, $potentialTS){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($lNumTSDDL == 0){
         echoT('<br><i>You currently have no timesheets available for editing.</i><br><br>');
         return;
      }

      $att = array('style' => 'display: inline');
      $strOut = form_open('staff/timesheets/ts_log/add_edit_ts_prep/'.$lUserID, $att)
              .$lDisplayYear.': ';

      switch ($tst->enumRptPeriod){
         case 'Weekly':
            $strOut .= 'Time sheet for the week beginning: <select name="ddlTS">';
            break;
         case 'Monthly':
            $strOut .= 'Time sheet for the month beginning: <select name="ddlTS">';
            break;
         case 'Semi-monthly':
            $strOut .= 'Time sheet for the semi-monthly period beginning: <select name="ddlTS">';
            break;
         default:
            screamForHelp($tst->enumRptPeriod.': reporting time period not found<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

      foreach ($potentialTS as $pTS){
         $strOut .= '
            <option value="'.$pTS->lTSLKeyID.'_'.$pTS->dteTSEntry.'"
               '.($pTS->bSelected ? 'selected' : '').'>'.$pTS->strDate.'</option>';
      }
      $strOut .= '</select>'."\n";

      $strOut .=
             '<input type="submit" name="cmdSubmit" value="Go"
                 style="font-size: 8pt; height: 14pt;"
                 class="btn"
                    onmouseover="this.className=\'btn btnhov\'"
                    onmouseout="this.className=\'btn\'"><br><br>'
           .form_close();
      echoT($strOut);
   }


