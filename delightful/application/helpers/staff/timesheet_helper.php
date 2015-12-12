<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2014 Database Austin

   author: John Zimmerman

   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
-----------------------------------------------------------------------------
      $this->load->helper('staff/timesheet');
-----------------------------------------------------------------------------*/
   namespace ts_util;

   function strDaysOfWeekDDL($strDDLName, $bIncludeBlank, $lMatchID){
   //---------------------------------------------------------------------
   // uses the php "w" format for day of week; 0=Sunday, 6=Saturday
   //---------------------------------------------------------------------
      $strOut = '<select name="'.$strDDLName.'">'."\n";
      if ($bIncludeBlank) $strOut .= '<option value="-1">&nbsp;</option>'."\n";

      $strOut .=
            '<option value="0" '.($lMatchID==0 ? 'SELECTED' : '').'>Sunday   </option>'."\n"
           .'<option value="1" '.($lMatchID==1 ? 'SELECTED' : '').'>Monday   </option>'."\n"
           .'<option value="2" '.($lMatchID==2 ? 'SELECTED' : '').'>Tuesday  </option>'."\n"
           .'<option value="3" '.($lMatchID==3 ? 'SELECTED' : '').'>Wednesday</option>'."\n"
           .'<option value="4" '.($lMatchID==4 ? 'SELECTED' : '').'>Thursday </option>'."\n"
           .'<option value="5" '.($lMatchID==5 ? 'SELECTED' : '').'>Friday   </option>'."\n"
           .'<option value="6" '.($lMatchID==6 ? 'SELECTED' : '').'>Saturday </option>'."\n"
         .'</select>'."\n";
      return($strOut);
   }

   function strTimeGrainularityDDL($strDDLName, $bIncludeBlank, $lMatchID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '<select name="'.$strDDLName.'">'."\n";
      if ($bIncludeBlank) $strOut .= '<option value="-1">&nbsp;</option>'."\n";

      $strOut .=
            '<option value= "5" '.($lMatchID== 5 ? 'SELECTED' : '').'>5 minutes</option>'."\n"
           .'<option value="10" '.($lMatchID==10 ? 'SELECTED' : '').'>10 minutes</option>'."\n"
           .'<option value="15" '.($lMatchID==15 ? 'SELECTED' : '').'>15 minutes</option>'."\n"
           .'<option value="30" '.($lMatchID==30 ? 'SELECTED' : '').'>30 minutes</option>'."\n"
           .'<option value="60" '.($lMatchID==60 ? 'SELECTED' : '').'>60 minutes</option>'."\n"
         .'</select>'."\n";
      return($strOut);
   }

   function strTimePeriodDDL($strDDLName, $bIncludeBlank, $enumMatchID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '<select name="'.$strDDLName.'">'."\n";
      if ($bIncludeBlank) $strOut .= '<option value="-1">&nbsp;</option>'."\n";

      $strOut .=
            '<option value="Weekly" '      .($enumMatchID=='Weekly'       ? 'SELECTED' : '').'>Weekly</option>'."\n"
           .'<option value="Semi-monthly" '.($enumMatchID=='Semi-monthly' ? 'SELECTED' : '').'>Semi-monthly</option>'."\n"
           .'<option value="Monthly" '     .($enumMatchID=='Monthly'      ? 'SELECTED' : '').'>Monthly</option>'."\n"
         .'</select>'."\n";
      return($strOut);
   }
   
   function strTimeInTimeOut($strDDLName, $bIncludeBlank, $lMatchTime, $b24Hr, $lGranularity){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '<select name="'.$strDDLName.'">'."\n";
      if ($bIncludeBlank) $strOut .= '<option value="-1">&nbsp;</option>'."\n";
      if ($lGranularity <= 0) return('#error#');
      
      $lMinutes = 0;
      $lMinPerDay = 60 * 24;
      $strAMPM = '';
      while ($lMinutes < $lMinPerDay){
         $lHour = floor($lMinutes / 60);
         $lMin  = $lMinutes % 60;
         
         if (!$b24Hr){
            $strAMPM = $lHour < 12 ? ' AM' : ' PM';
            if ($lHour == 0){
               $lHour = 12;
            }elseif ($lHour > 12){
               $lHour = $lHour - 12;
            }
         }
         
         $strSelect = ($lMatchTime == $lMinutes ? 'selected' : '');
         $strOut .= 
            '<option value="'.$lMinutes.'" '.$strSelect.'>'
               .$lHour.':'.str_pad($lMin, 2, '0', STR_PAD_LEFT).$strAMPM.'</option>'."\n";
         $lMinutes += $lGranularity;
      }
      
      $strOut .= '</select>'."\n";
      return($strOut);
   }

   function strXlateDayofWeek($lDOW){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($lDOW){
         case 0;  $strOut = 'Sunday';    break;
         case 1;  $strOut = 'Monday';    break;
         case 2;  $strOut = 'Tuesday';   break;
         case 3;  $strOut = 'Wednesday'; break;
         case 4;  $strOut = 'Thursday';  break;
         case 5;  $strOut = 'Friday';    break;
         case 6;  $strOut = 'Saturday';  break;
         default: $strOut = '';          break;
      }
      return($strOut);
   }

   function dtePastStartOfWeekFromBaseDate($lDayOfWeek, $dteBase){
   //---------------------------------------------------------------------
   // return the most recent date in the past that is the specified
   // day of the week (Sun=0, Sat=6)
   //
   // For example, if the base date is a Tuesday and the day of week is
   // Sunday, return the base date minus two days. If the base date is
   // the same as the day of week, return the base date.
   //---------------------------------------------------------------------
      $aBase = getdate($dteBase);
      $lBDOW = $aBase['wday'];

      if ($lBDOW == $lDayOfWeek) return($dteBase);

      if ($lBDOW > $lDayOfWeek) return(mktime(0, 0, 0, $aBase['mon'],
                     $aBase['mday']-($lBDOW-$lDayOfWeek), $aBase['year']));
      return(mktime(0, 0, 0, $aBase['mon'],
                     $aBase['mday']-(7-($lDayOfWeek-$lBDOW)), $aBase['year']));
   }

   function dteFutureStartOfWeekFromBaseDate($lDayOfWeek, $dteBase){
   //---------------------------------------------------------------------
   // return the most recent date in the future that is the specified
   // day of the week (Sun=0, Sat=6)
   //
   // For example, if the base date is a Tuesday and the day of week is
   // Sunday, return the base date plus five days. If the base date is
   // the same as the day of week, return the base date plus 7 days.
   //---------------------------------------------------------------------
      $aBase = getdate($dteBase);
      $lBDOW = $aBase['wday'];

      if ($lBDOW == $lDayOfWeek) return((mktime(0, 0, 0, $aBase['mon'],
                     $aBase['mday']+7, $aBase['year'])));

      if ($lBDOW < $lDayOfWeek) return(mktime(0, 0, 0, $aBase['mon'],
                     $aBase['mday']+($lDayOfWeek-$lBDOW), $aBase['year']));
      return(mktime(0, 0, 0, $aBase['mon'],
                     $aBase['mday']+(7-($lBDOW-$lDayOfWeek)), $aBase['year']));
   }

   function strTSTLogOverviewBlock(&$clsRpt, &$tst, &$logRec, $bAllowSubmit, $bAllProjHrsEdit){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $lTSLogID = $logRec->lKeyID;

      $lMinutesTot     = $logRec->lMinutesTot;
      $lMinutesTotProj = $logRec->lMinutesTotProj;
      $bMinutesMatch   = abs($lMinutesTot - $lMinutesTotProj) < 0.1;

      $bSubmitted = !is_null($logRec->dteSubmitted);
      $strLinkProjects = '';
      if ($bSubmitted){
         $strSubmitted = 'Submitted on '.date($genumDateFormat.' H:i:s', $logRec->dteSubmitted);
      }else {
         $strSubmitted = 'Not submitted';
         if ($bAllProjHrsEdit){
            $strLinkProjects = '&nbsp;'.strLinkEdit_TSLogToProjects($lTSLogID, 'Assign hours to projects', true);
         }
         if ($bAllowSubmit){
            if ($bMinutesMatch && $lMinutesTot > 0){
               $strSubmitted .= 
                  '<br>'
                  .form_open('staff/timesheets/ts_log_edit/ts_submit/'.$lTSLogID)."\n"
                  .'<div style="width: 300pt; border: 1px solid black; padding: 3px; margin-top: 3px;">'.form_hidden('forceSubmit', 'makeItSo')."\n"
                  .'<input type="checkbox" name="chkIAgree" value="true">'."\n"
                  .nl2br(htmlspecialchars($tst->strAckText))."<br><br>\n"
                  .'<input type="submit"
                     class="btn" onmouseover="this.className=\'btn btnhov\'" onmouseout="this.className=\'btn\'"
                     value="Submit Time Sheet"><br>
                     <i>Once submitted, you can only make changes through
                     your system administrator.</i></div>'."\n"
                  .form_close();
            }elseif (!$bMinutesMatch) {
               $strSubmitted .= '<br>
                  <div style="color: red; width: 300pt; border: 1px solid red; padding: 3px; margin-top: 3px;">
                     Your <b>Total Hours</b> for this time period does not match the <b>Hours Assigned to Projects</b>.
                     These hours must be equal before you can submit your time sheet.
                  </div>';
            }
         }
      }

      $strOut = strOpenBlock(date('F jS, Y', $logRec->dteTSEntry).': Time Sheet for '.htmlspecialchars($logRec->strUserFName.' '.$logRec->strUserLName), '');

      $strOut .= $clsRpt->openReport();

      $strOut .=
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Time Sheet Template:', '', 'vertical-align: bottom;')
         .$clsRpt->writeCell(htmlspecialchars($logRec->strTSName))
         .$clsRpt->closeRow  ();

      $strOut .=
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Reporting Frequency:', '')
         .$clsRpt->writeCell ($logRec->enumRptPeriod)
         .$clsRpt->closeRow  ();

      $strOut .=
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Total Hours:', '')
         .$clsRpt->writeCell (strDurationViaMinutes($lMinutesTot))
         .$clsRpt->closeRow  ();

      $strOut .=
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Project Hours Assigned:', '')
         .$clsRpt->writeCell (strDurationViaMinutes($lMinutesTotProj).$strLinkProjects)
         .$clsRpt->closeRow  ();

      $strOut .=
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Status:', '')
         .$clsRpt->writeCell ($strSubmitted)
         .$clsRpt->closeRow  ();

      $strOut .= $clsRpt->closeReport();
      $strOut .= strCloseBlock();

      return($strOut);
   }
   
   function tsEntryCalendar($logRec, &$tsCalendar){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      $tsCalendar = array();
      $baseDate = getdate($logRec->dteTSEntry);
      $lMon  = (int)$baseDate['mon'];
      $lDay  = (int)$baseDate['mday'];
      $lYear = (int)$baseDate['year'];
      $lDaysInMonth = (int)date('t', $logRec->dteTSEntry);

      switch ($logRec->enumRptPeriod){
         case 'Weekly':
            $lNumDays = 7;
            break;
         case 'Semi-monthly':
            if ($baseDate['mday']==1){
               $lNumDays = 15;
            }else {
               $lNumDays = $lDaysInMonth - 15;
            }
            break;
         case 'Monthly':
            $lNumDays = $lDaysInMonth;
            break;
         default:
            screamForHelp($tst->enumRptPeriod.': reporting time period not found<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      
      for ($idx=0; $idx<$lNumDays; ++$idx){
         $tsCalendar[$idx] = new \stdClass;  // use "\" to reference ourside this namespace
         $tc = &$tsCalendar[$idx];
         $tc->calDate = mktime(0, 0, 0, $lMon, $lDay+$idx, $lYear);
         $tc->strDate = date('M j, Y (D)', $tc->calDate);         
      }
      
   }











