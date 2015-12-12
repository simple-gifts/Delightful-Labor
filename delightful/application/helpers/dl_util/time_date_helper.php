<?php
/*---------------------------------------------------------------------
// copyright (c) 2012-2015
// Austin, Texas 78759
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('dl_util/time_date');
---------------------------------------------------------------------
   Notes:
     Most of these utilities use the UNIX timestamp. Unfortunately
     the UNIX timestamp has some limitations, and has
     behaviors that differ from operating system to operating system.

     On Windows systems, the UNIX timestamp does not work for dates
     prior to 1/1/1970, and the 32-bit implementation peters out in
     a few decades.

     One solution is to use the PEAR date class. Another is to
     use mySQL functions to manipulate dates as part of the underlying
     sql lookup. We also have an open source time/date class that
     handles any date/time
---------------------------------------------------------------------
   strXlateMonth              ($lMonth) - return month name for month index 1-12

   year_UTS                   ($UTS)
   month_UTS                  ($UTS)
   day_of_month_UTS           ($UTS)

   add_days_UTS               ($UTS_start, $lNumDays)
   dteLoadDateDDL
   setDateDDLs

   displayMonthDDL            ($strDDL_MonthName, $lMonth, $bShowBlank)
   displayDayDDL              ($strDDL_DayName, $lDay, $bShowBlank)
   displayYearDDL             ($strDDL_YearName, $lYear, $lStartYear, $lEndYear, $bShowBlank)

   displayMonthDayYearDDLs    ($strDDL_MonthName, $lMonth, ...

   lDaysInMonth               ($lMonth, $lYear)
   lLastDayMon                ($lMonth, $lYear)
   loadDateFromDDL            ($strDDL_MonthName, $strDDL_DayName, $strDDL_YearName, &$lMonth, &$lDay, &$lYear, &$bNoEntry)
   strLoadOptional_MDY_mySQL  ($strDDL_Mo, $strDDL_Day, $strDDL_Yr)
   dteUnixDateTimeViaDDL      ($bLoadDate, $bLoadTime, $strDDL_Month, $strDDL_Day, $strDDL_Year, $strDDL_Hour,  $strDDL_Minute, $strDDL_AM_PM)
   displayHourDDL             ($strDDL_HourName, $lHour, $b24HourFormat, $bShowBlank) {
   displayMinuteDDL           ($strDDL_MinuteName, $lMinute, $bShowBlank)
   displayAM_PM_DDL           ($strDDL_AM_PM_Name, $bAM)

   strMonthBetween            ($lMonth, $lYear)

   dteMonthStart              ($lMonth, $lYear)
   dteMonthEnd                ($lMonth, $lYear)

   bVerifyValidMonthDayYear   ($lMonth, $lDay, $lYear)
   convertTo24Hour            (&$lHour, $bAM)
   convertTo12Hour            (&$lHour, &$bAM)

   strdisplayTimeDDL          ($dteTime, $bShowBlank)
   strDurationDDL             ($bShowBlank, $bShowAllDay, $lDurationSelect)
   lDurationHrsToQuarters     ($decDuration)
   lTimeToQuarters            ($dteTime)
   quartersToHrsMin           ($lQuarters, &$lHrs, &$lMins)
   strXlateDuration           ($lDuration, $bTerse=true)

   dteMySQLTime2Unix          ($strMySQLTime) 
   MySQL_TimeExtract          ($strMySQLTime, &$lHour, &$lMinute, &$lSecond)
   strMinutesToMySQLTime      ($lMinutes);
   strDurationViaMinutes      ($lMinutes)
   strFormattedTimeViaMinutes ($lMinutes, $b24Hr)
   lMySQLTimeToMinutes        ($strMySqlTime);

   dteMySQLDate2Unix          ($strMySQLTime)  // moved to dl_library_helper.php
   lMySQLDate2MY              ($strDate)
   lUTS_2_MY                  ($dteDate)
   dte_MY_2_UTS               ($lMY)
   dteAdd_UT_Months           ($dteBase, $lNumMonths)

   numsMoDaYr                 ($dteTest, &$lMonth, &$lDay, &$lYear)
   numsHrsMin                 ($dteTest, &$lHours, &$lMinutes)
   numsMoDaYrMySQL            ($strMySQLDate, &$lMonth, &$lDay, &$lYear)
   strMoDaYr2MySQLDate        ($lMonth, $lDay, $lYear)
   strHrMinSec2MySQLTime      ($lHour, $lMin, $lSec)
   strStrDate2MySQLDate       ($strDate, $bUSFormat)
   strLoadOptional_MDY_mySQL  ($strDDL_Month, $strDDL_Day, $strDDL_Year)
   strLoadOptional_MY_mySQL   ($strDDL_Month, $strDDL_Year)
   strNumericDateViaMysqlDate ($dteMysqlDate, $bDateFormatUS)
   strNumericDateViaUTS       ($dteUTS, $bDateFormatUS)
   MDY_ViaUserForm            ($strDate, &$lMon, &$lDay, &$lYear, $bDateFormatUS){

   dteAddTimeToDate           ($dteBase, $lHour24, $lMinute, $lSec)
   dteRemoveTimeFromDate      ($dteBase)

   lDateDiff_Days             ($dteFirst, $dteSecondDate)

   dateRangeYears             ($mysqlDteBase, $lAgeYrsMin, $lAgeYrsMax, &$mysqlStartDate, &$mysqlEndDate)

   ageCalculations(
                 $lPresentMonth, $lPresentDay,         $lPresentYear,
                 $lBirthMonth,   $lBirthDay,           $lBirthYear,
                 &$lAgeYears,    &$lAgeYearsModMonths, &$lAgeYearsModDays,
                 &$lAgeMonths,   &$lAgeMonthsModDays,
                 &$lAgeDays) {
   lDateDiff_DaysViaMDY(
                 $lEndMonth,   $lEndDay,   $lEndYear,
                 $lStartMonth, $lStartDay, $lStartYear)

   dteDatePicker2Date        ($strDate)
   strDateForDatePicker      ($dteDateOfExam)

   bDateInFuture             ($lDay, $lMonth, $lYear)
   bValidVerifyDate          ($strDate)
   bValidVerifyNotFuture     ($strDate)

   bVerifyCartBeforeHorse    ($strStartDate, $strEndDate)
---------------------------------------------------------------------*/

function strXlateMonth($lMonth){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   switch($lMonth) {
      case 1:
         return('January');
         break;

      case 2:
         return('February');
         break;

      case 3:
         return('March');
         break;

      case 4:
         return('April');
         break;

      case 5:
         return('May');
         break;

      case 6:
         return('June');
         break;

      case 7:
         return('July');
         break;

      case 8:
         return('August');
         break;

      case 9:
         return('September');
         break;

      case 10:
         return('October');
         break;

      case 11:
         return('November');
         break;

      case 12:
         return('December');
         break;

      default:
         screamForHelp('Invalid Month; error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__);
         break;
   }
}

function lLastDayMon($lMonth, $lYear){
//---------------------------------------------------------------------
//  return an integer representing the last day of the specified month
//---------------------------------------------------------------------
   return(date('t', mkTime(0, 0, 0, $lMonth, 1, $lYear)));
}

function year_UTS($UTS) {
//----------------------------------------------------------------
//  return the 4-digit year (integer) from a unix timestamp
//----------------------------------------------------------------
   if ($UTS<=0) {
      return(-1);
   }else {
      return( (integer)date('Y',$UTS) );
   }
}

function month_UTS($UTS) {
//----------------------------------------------------------------
//  return the 2-digit month (integer, 1..12) from a unix timestamp
//----------------------------------------------------------------
   if ($UTS<=0) {
      return(-1);
   }else {
      return((integer)date('n',$UTS));
   }
}

function day_of_month_UTS($UTS) {
//----------------------------------------------------------------
//  return the day of month (integer, 1..31) from a unix timestamp
//----------------------------------------------------------------
   if ($UTS<=0) {
      return(-1);
   }else {
      return((integer)date('j',$UTS));
   }
}

function add_days_UTS($UTS_start, $lNumDays) {
//----------------------------------------------------------------
// Add the specified number of days to a given start date.
// The value of $lNumDays can be negative
//----------------------------------------------------------------
   $UTS_destination =
           mktime (0,0,0,
                month_UTS($UTS_start),
                day_of_month_UTS($UTS_start) + $lNumDays,
                year_UTS($UTS_start) );

   return($UTS_destination);
}


function lDateDiff_Days($dteFirst, $dteSecondDate) {
//----------------------------------------------------------------
// find the number of days between the two unix timestamps
//
// i.e. lDateDiff_Days(today, tomorrow) == 1
// i.e. lDateDiff_Days(today, yesterday) == -1
//
// note that these unix timestamps represent the number of
// seconds since the current Unix epoc
//
// dteSecond - dteFirst
//----------------------------------------------------------------
   $lNumSeconds = $dteSecondDate - $dteFirst;

//echo("$dteSecondDate-$dteFirst".CBR);
//echo("\$dteSecondDate-\$dteFirst=".(integer)($lNumSeconds/(60*60*24)).CBR);

   return((int)($lNumSeconds/(60*60*24)));
}

function dateRangeYears($mysqlDteBase, $lAgeYrsMin, $lAgeYrsMax, &$mysqlStartDate, &$mysqlEndDate){
/*--------------------------------------------------------------------------------
   Let's say you want to find all the clients who were between 1 and 2 years
   of age on 2012-12-25:

      born           age on 2012-12-25
      ------------   -----------------
      2011-12-24             1
      2011-12-25             1
      2011-12-26             0

      2010-12-24             2
      2010-12-25             2
      2010-12-26             1

      2009-12-24             3
      2009-12-25             3
      2009-12-26             2

        > 2009-12-25  && <= 2011-12-25


   dateRangeYears('2010-12-25', 1, 2, &$mysqlStartDate, &$mysqlEndDate);

   returns $mysqlStartDate='2009-12-25', $mysqlEndDate='2011-12-25'
   ...
   the sql statement....
      WHERE NOT cr_bRetired
         AND cr_dteBirth > '.strPrepStr($mysqlStartDate).'
         AND cr_dteBirth <='.strPrepStr($mysqlEndDate).'
--------------------------------------------------------------------------------*/
   numsMoDaYrMySQL($mysqlDteBase, $lMonth, $lDay, $lYear);

   $lMonthStart = $lMonth;
   $lDayStart   = $lDay;
   $lYearStart  = $lYear - ($lAgeYrsMax+1);

      // leap year normalization
   normalizeDate($lMonthStart, $lDayStart, $lYearStart);

   $lMonthEnd = $lMonth;
   $lDayEnd   = $lDay;
   $lYearEnd  = $lYear - $lAgeYrsMin;

      // leap year normalization
   normalizeDate($lMonthEnd, $lDayEnd, $lYearEnd);

   $mysqlStartDate = strMoDaYr2MySQLDate($lMonthStart, $lDayStart, $lYearStart);
   $mysqlEndDate   = strMoDaYr2MySQLDate($lMonthEnd,   $lDayEnd,   $lYearEnd);
}

function dteLoadDateDDL(
                    $strDDL_MonthName, $strDDL_DayName, $strDDL_YearName,
                    &$lMonth, &$lDay, &$lYear, &$bError, &$bFuture) {
//-------------------------------------------------------------------------
//  return the unix timestamp associated with the ddl's for month/day/year;
//  also return the values of the ddls, and an error flag if an illegal
//  date was specified (like feb 30)
//-------------------------------------------------------------------------
   $bError = false;

   $lMonth = $_REQUEST[$strDDL_MonthName];
   $lDay   = $_REQUEST[$strDDL_DayName];
   $lYear  = $_REQUEST[$strDDL_YearName];

   if (($lMonth<=0) || ($lDay<=0) || ($lYear<=0) ) {
      $bError = true;
      return(-1);
   }

   $lNumDaysInMonth = (integer)date('t',strtotime("$lMonth/1/$lYear") );
   if ($lDay>$lNumDaysInMonth) {
      $bError = true;
      return(-1);
   }else {
      $dteUTS = strtotime("$lMonth/$lDay/$lYear");
      $bFuture = $dteUTS>time();
      return($dteUTS);
   }
}

function setDateDDLs($dteShowDate, $strDDL_MonthName,
                     $strDDL_DayName, $strDDL_YearName, $bShowBlank=true) {
//-------------------------------------------------------------------------
//
//-------------------------------------------------------------------------

   $dteNow = time();
   $lCurYear =  Year_UTS($dteNow);

   $lMonth = Month_UTS($dteShowDate);
   $lDay   = day_of_month_UTS($dteShowDate);
   $lYear  = Year_UTS($dteShowDate);

   if ($lYear<=0) {
      $lYear = (integer)date('Y');
   }

   if ($lYear<$lCurYear) {
      $lStartYear = $lYear - 20;
   } else {
      $lStartYear = $lCurYear - 20;
   }

   if ($lYear>$lCurYear) {
      $lEndYear = $lYear + 3;
   }else {
      $lEndYear = $lCurYear + 3;
   }

   displayMonthDDL($strDDL_MonthName, $lMonth, $bShowBlank);
   displayDayDDL($strDDL_DayName, $lDay, $bShowBlank);
   displayYearDDL($strDDL_YearName, $lYear, $lStartYear, $lEndYear, $bShowBlank);
}

function displayMonthDayYearDDLs(
                    $strDDL_MonthName, $lMonth,
                    $strDDL_DayName,   $lDay,
                    $strDDL_YearName,  $lYear,        $lStartYear, $lEndYear,
                    $bShowBlank,       $bIndianStyle, $bClearAllBGOnClick,
                    $strFormName,      $strExtraSel){
//-------------------------------------------------------------------------
//  sample calling sequence:
//   displayMonthDayYearDDLs(
//                    'ddlMonth', $lMonth,
//                    'ddlDay',   $lDay,
//                    'ddlYear',  $lYear, (min($lYear, $lYearNow)-5), (max($lYear, $lYearNow)+5),
//                    false,      true,   'onClick="frmShots.&&.style.background=\'#fff\';" ');
// Note that the object name is substitued for the "&&"
//-------------------------------------------------------------------------

   $strOnChangeStr =
         'onClick="'.$strFormName.'.'.$strDDL_DayName  .'.style.background=\'#fff\'; '
                    .$strFormName.'.'.$strDDL_MonthName.'.style.background=\'#fff\'; '
                    .$strFormName.'.'.$strDDL_YearName .'.style.background=\'#fff\';" '.$strExtraSel;

   if ($bIndianStyle) {
      displayDayDDL  ($strDDL_DayName,   $lDay,   $bShowBlank, $strOnChangeStr);
      displayMonthDDL($strDDL_MonthName, $lMonth, $bShowBlank, $strOnChangeStr);
   }else {
      displayMonthDDL($strDDL_MonthName, $lMonth, $bShowBlank, $strOnChangeStr);
      displayDayDDL  ($strDDL_DayName,   $lDay,   $bShowBlank, $strOnChangeStr);
   }
   displayYearDDL($strDDL_YearName, $lYear, $lStartYear, $lEndYear, $bShowBlank, $strOnChangeStr);
}

function displayMonthDDL($strDDL_MonthName, $lMonth, $bShowBlank, $strOnChange='') {
//-------------------------------------------------------------------------
// sample calling sequence:
//      displayMonthDDL('ddlMonth', $lMonth, false, 'onChange="frmLog.chkRemind.checked=true;"');
//-------------------------------------------------------------------------
   echo('<select size="1" name="'.$strDDL_MonthName.'" '.$strOnChange.'>'."\n");

   if ($bShowBlank) {
      echo('<option value="-1">&nbsp;</option>');
   }

   echo(
          '<option value="1"  '.($lMonth==1  ? 'SELECTED' : '').'>January  </option>'."\n"
         .'<option value="2"  '.($lMonth==2  ? 'SELECTED' : '').'>February </option>'."\n"
         .'<option value="3"  '.($lMonth==3  ? 'SELECTED' : '').'>March    </option>'."\n"
         .'<option value="4"  '.($lMonth==4  ? 'SELECTED' : '').'>April    </option>'."\n"
         .'<option value="5"  '.($lMonth==5  ? 'SELECTED' : '').'>May      </option>'."\n"
         .'<option value="6"  '.($lMonth==6  ? 'SELECTED' : '').'>June     </option>'."\n"
         .'<option value="7"  '.($lMonth==7  ? 'SELECTED' : '').'>July     </option>'."\n"
         .'<option value="8"  '.($lMonth==8  ? 'SELECTED' : '').'>August   </option>'."\n"
         .'<option value="9"  '.($lMonth==9  ? 'SELECTED' : '').'>September</option>'."\n"
         .'<option value="10" '.($lMonth==10 ? 'SELECTED' : '').'>October  </option>'."\n"
         .'<option value="11" '.($lMonth==11 ? 'SELECTED' : '').'>November </option>'."\n"
         .'<option value="12" '.($lMonth==12 ? 'SELECTED' : '').'>December </option>'."\n"
      .'</select>'."\n");

}

function displayDayDDL($strDDL_DayName, $lDay, $bShowBlank, $strOnChange='') {
//-------------------------------------------------------------------------
//
//-------------------------------------------------------------------------
   echo(
      '<select size="1" name="'.$strDDL_DayName.'" '.$strOnChange.'>'."\n");
   if ($bShowBlank) echo('<option value="-1">&nbsp;</option>');

   for ($idx=1; $idx<=31; ++$idx) {
      echo('<option value="'.$idx.'" '.($lDay==$idx?'SELECTED':'').'>'.$idx.'</option>'."\n");
   }
   echo("</select>\n");
}


function displayYearDDL($strDDL_YearName, $lYear, $lStartYear, $lEndYear, $bShowBlank, $strOnChange='') {
//-------------------------------------------------------------------------
//
//-------------------------------------------------------------------------
   echo('<select size="1" name="'.$strDDL_YearName.'" '.$strOnChange.'>'."\n");
   if ($bShowBlank)
       echo('<option value="-1" '.($lYear<=0 ? 'selected':'').'>&nbsp;</option>'."\n");

   for ($idx=$lStartYear; $idx<=$lEndYear; ++$idx) {
      echo('<option value="'.$idx.'" '.($lYear==$idx?'SELECTED':'').' >'
         .$idx."</option>\n");
   }
   echo("</select>\n");
}

function loadDateFromDDL($strDDL_MonthName, $strDDL_DayName, $strDDL_YearName,
                         &$lMonth,          &$lDay,          &$lYear,
                         &$bNoEntry){
//-------------------------------------------------------------------------
//
//-------------------------------------------------------------------------
   $lMonth = (integer)$_REQUEST[$strDDL_MonthName];
   $lDay   = (integer)$_REQUEST[$strDDL_DayName];
   $lYear  = (integer)$_REQUEST[$strDDL_YearName];

   $bNoEntry = ($lMonth<=0) && ($lDay<=0) && ($lYear<=0);
}

function dteUnixDateTimeViaDDL(
                  $bLoadDate,    $bLoadTime,
                  $strDDL_Month, $strDDL_Day,    $strDDL_Year,
                  $strDDL_Hour,  $strDDL_Minute, $strDDL_AM_PM){
//-------------------------------------------------------------------------
//
//-------------------------------------------------------------------------
   if ($bLoadTime){
      $lHour   = (integer)$_REQUEST[$strDDL_Hour];
      $lMinute = (integer)$_REQUEST[$strDDL_Minute];

      $bPM = $_REQUEST[$strDDL_AM_PM]=='PM';
      if ($bPM) {
         if ($lHour!=12) $lHour += 12;
      }else {
         if ($lHour==12) $lHour = 0;
      }
   }else {
      $lHour   = 0;
      $lMinute = 0;
   }

   if ($bLoadDate){
      $lMonth  = (integer)$_REQUEST[$strDDL_Month];
      $lDay    = (integer)$_REQUEST[$strDDL_Day];
      $lYear   = (integer)$_REQUEST[$strDDL_Year];
   }else {
      $lMonth  = 0;
      $lDay    = 0;
      $lYear   = 0;
   }

   if (
         $bLoadTime && (($lHour<0) || ($lMinute<0))
      ){
      $lHour = $lMinute = 0;
   }

   if (
         $bLoadDate && (($lMonth<0) || ($lDay<0) || ($lYear<0))
      ) {
      return(null);
   }else {
      return(
         mktime ( $lHour,
                  $lMinute,
                  0,
                  $lMonth,
                  $lDay,
                  $lYear));
   }
}

function displayHourDDL($strDDL_HourName, $lHour, $b24HourFormat, $bShowBlank) {
//-------------------------------------------------------------------------
//
//-------------------------------------------------------------------------
   echo('<select size="1" name="'.$strDDL_HourName.'">'."\n");

   if ($bShowBlank) {
      echo('<option value="-1">&nbsp;</option>'."\n");
   }

   if ($b24HourFormat) {
      echo(
         '<option value="0" '.($lHour==0? 'SELECTED' : '').'>0</option>'."\n");
      }
   for ($idx=1; $idx<=12; ++$idx) {
      echo(
         '<option value="'.$idx.'" '.($lHour==$idx ? 'SELECTED' : '').'>'.$idx.'</option>'."\n");
   }

   if ($b24HourFormat) {
      for ($idx=13; $idx<=23; ++$idx) {
         echo(
            '<option value="'.$idx.'" '.($lHour==$idx ? 'SELECTED' : '').'>'.$idx.'</option>'."\n");
      }
   }
   echo('</select>'."\n");
}

function displayMinuteDDL($strDDL_MinuteName, $lMinute, $bShowBlank) {
//-------------------------------------------------------------------------
//
//-------------------------------------------------------------------------
   echo(
      '<select size="1" name="'.$strDDL_MinuteName.'">'."\n");

   if ($bShowBlank) {
      echo('<option value="-1">&nbsp;</option>'."\n");
   }

   for ($idx=0; $idx<60; ++$idx) {
      echo(
         '<option value="'.$idx.'" '.($lMinute==$idx ?  'SELECTED' : '').'>'
            .str_pad($idx, 2, '0', STR_PAD_LEFT)
        .'</option>'."\n");
   }
   echo('</select>'."\n");
}

function displayAM_PM_DDL($strDDL_AM_PM_Name, $bAM){
//-------------------------------------------------------------------------
//
//-------------------------------------------------------------------------
   echo(
      '<select name="'.$strDDL_AM_PM_Name.'">'."\n"
         .'<option value="AM" '.($bAM ? 'SELECTED' : '').'>AM</option>'."\n"
         .'<option value="PM" '.($bAM ? '' : 'SELECTED').'>PM</option>'."\n"
     .'</select>'."\n");

}

function strMonthBetween($lMonth, $lYear) {
//-------------------------------------------------------------------------
// return a string that represents the timespan encompased by a month
// in mySQL date format
//-------------------------------------------------------------------------
   $strStartDate = strPrepDateTime(dteMonthStart($lMonth, $lYear));
   $strEndDate   = strPrepDateTime(dteMonthEnd(  $lMonth, $lYear));
   return($strStartDate.' AND '.$strEndDate);
}

function dteMonthStart($lMonth, $lYear) {
//-------------------------------------------------------------------------
// return the unix timestamp for the start of the month
//-------------------------------------------------------------------------
   return(mktime( 0, 0, 0, $lMonth, 1, $lYear));
}

function dteMonthEnd($lMonth, $lYear) {
//-------------------------------------------------------------------------
// return the unix timestamp for the end of the month
//-------------------------------------------------------------------------
   return(mktime(23,59,59,$lMonth+1,0,$lYear));
}

function strMonthNameFromOrd($lMonth) {
//-------------------------------------------------------------------------
//  for a given ordinal, return the month string
//-------------------------------------------------------------------------
   switch ($lMonth) {
      case  1: return ("January");    break;
      case  2: return ("February");   break;
      case  3: return ("March");      break;
      case  4: return ("April");      break;
      case  5: return ("May");        break;
      case  6: return ("June");       break;
      case  7: return ("July");       break;
      case  8: return ("August");     break;
      case  9: return ("September");  break;
      case 10: return ("October");    break;
      case 11: return ("November");   break;
      case 12: return ("December");   break;
      default: return ("#error#");    break;
   }
}

function dteNextBusinessDay($dteBase){
//------------------------------------------------------------------
//
//------------------------------------------------------------------
   $dteHold = $dteBase;

   $iMonth = month_UTS($dteHold);
   $iDay   = day_of_month_UTS($dteHold);
   $iDOW   = date('w',$dteHold);   //0=Sunday, 1=Monday,... 6=Saturday

   if ($iDOW==0) {
      $dteHold = add_days_UTS($dteHold, 1);
   }elseif ($iDOW==6) {
      $dteHold = add_days_UTS($dteHold, 2);
   }

      // move past major holidays
   if ((($iMonth==12) && ($iDay==25)) ||
       (($iMonth==1)  && ($iDay==1))  ||
       (($iMonth==7)  && ($iDay==4))) {
      $dteHold = add_days_UTS($dteHold, 1);

         // check again in case holiday
         // pushed us into weekend
      if ($iDOW==0) {
         $dteHold = add_days_UTS($dteHold, 1);
      }elseif ($iDOW==6) {
         $dteHold = add_days_UTS(dteHold, 2);
      }
   }
   return($dteHold);
}

function dteFirstDayPreviousMonth($dteBasis) {
//------------------------------------------------------------------
// return the first day of the month, one month before the month
// in the calling argument.
//------------------------------------------------------------------
//echo('$dteBasis='.' '.date('m/d/Y H:i:s',$dteBasis).'<br>');
   $lPrevMonth = date("m",$dteBasis)-1;
//echo("<b>\$lPrevMonth=$lPrevMonth</b><br>");
   $dtePrevMonth = mktime( 0, 0, 0, $lPrevMonth, 1, date('Y',$dteBasis) );
   return($dtePrevMonth);
}

function determineDateRange($lDateRangeConst, $dteReferenceDate,
                            &$dteStart, &$dteEnd, &$strDateRange){
//----------------------------------------------------------------------------------
// sample calling sequence:
//
//      dim dteStart, dteEnd, sDateRange
//      call determineDateRange(CI_DATERANGE_THIS_WEEK, now(), dteStart, dteEnd, sDateRange, bUseWIP)
//
//  Some date range constants are 6 digits, used for finding quarters
//----------------------------------------------------------------------------------
   $dteNow = $dteReferenceDate;
   $bUseWIP = false;
   $bIncludeDateRange = true;

   switch ($lDateRangeConst) {
      case CI_DATERANGE_TODAY:
         $dteStart   = strtotime(date('m/d/Y', $dteNow));
         $dteEnd     = strtotime(date('m/d/Y 23:59:59', $dteNow));
         $strDateRange = 'Today';
         break;

      case CI_DATERANGE_YESTERDAY:
         $dteNow     = add_days_UTS($dteNow, -1);
         $dteStart   = strtotime(date('m/d/Y', $dteNow));
         $dteEnd     = strtotime(date('m/d/Y 23:59:59', $dteNow));
         $strDateRange = "Yesterday";
         break;

      case CI_DATERANGE_THIS_WEEK:
         $lWeekDay = (integer)date('w', $dteNow);   // 0 - 6 for sun..sat
         $dteStart = strtotime(date('m/d/Y', add_days_UTS($dteNow, -($lWeekDay))) );
         $dteEnd   = strtotime(date('m/d/Y 23:59:59', $dteNow));
         $strDateRange = "This Week";
         break;

      case CI_DATERANGE_THIS_MONTH:
         $dteStart  = strtotime(date('m/1/Y', $dteNow));
         $dteEnd    = strtotime(date('m/d/Y 23:59:59', $dteNow));
         $strDateRange = "This Month";
         break;

      case CI_DATERANGE_LAST_MONTH:
         $dteStart   = mktime(0, 0, 0,
                            (integer)date('n', $dteNow)-1, 1, (integer)date('Y',$dteNow) );

         $dteEnd     = mktime(23, 59, 59,
                          (integer) date('n', $dteStart),
                          (integer) date('t', $dteStart),
                          (integer) date('Y', $dteStart) );

         $strDateRange = "Last Month";
         break;

      case CI_DATERANGE_PAST_30:
         $dteEnd   = strtotime(date('m/d/Y 23:59:59', $dteNow));
         $dteStart = strtotime(date('m/d/Y', add_days_UTS($dteEnd, -30)));
         $strDateRange = "Past 30 Days";
         break;

      case CI_DATERANGE_PAST_60:
         $dteEnd   = strtotime(date('m/d/Y 23:59:59', $dteNow));
         $dteStart = strtotime(date('m/d/Y', add_days_UTS($dteEnd, -60)));
         $strDateRange = "Past 60 Days";
         break;

      case CI_DATERANGE_PAST_90:
         $dteEnd   = strtotime(date('m/d/Y 23:59:59', $dteNow));
         $dteStart = strtotime(date('m/d/Y', add_days_UTS($dteEnd, -90)));
         $strDateRange = "Past 90 Days";
         break;

      case CI_DATERANGE_PAST_YEAR:
         $dteEnd       = strtotime(date('m/d/Y 23:59:59', $dteNow));
         $dteStart     = add_days_UTS($dteEnd, -365);
         $strDateRange = "Past Year";
         break;

      case CI_DATERANGE_THIS_YEAR:
         $dteEnd       = strtotime(date('m/d/Y 23:59:59', $dteNow));
         $dteStart     = strtotime(date('1/1/Y', $dteNow));
         $strDateRange = 'Year to Date';
         break;

      case CI_DATERANGE_LAST_YEAR:
         $lYear        = (integer)(date('Y', $dteNow))-1;
         $dteEnd       = strtotime('12/31/'.$lYear.' 23:59:59');
         $dteStart     = strtotime('1/1/'.$lYear);
         $strDateRange = 'Last Year';
         break;

      case CI_DATERANGE_NONE:
         $bIncludeDateRange = false;
         $dteStart  = strtotime('1/1/1975');
         $dteEnd    = strtotime('1/18/2038');
         $strDateRange = "No Date Range";

      case CI_DATERANGE_WIP:
         $bUseWIP = true;
         break;

      default:

            //----------------------------------------------------
            // check for a range that is defined by a quarter
            //----------------------------------------------------
         $strDDL = (string)($lDateRangeConst);
         if (strlen($strDDL)==6 ){
            $iQYear = (integer)(substr($strDDL, -4));
            $iQCode = (integer)(substr($strDDL,0,2));

            switch ($iQCode) {
               case CI_DATERANGE_1ST_Q:
                  $strDateRange = "1st Quarter $iQYear";
                  $dteStart = strtotime('1/1/'.$iQYear);
                  $dteEnd   = strtotime('3/31/'.$iQYear.' 23:59:59');
                  break;

               case CI_DATERANGE_2ND_Q:
                  $strDateRange = "2nd Quarter $iQYear";
                  $dteStart = strtotime('4/1/'.$iQYear);
                  $dteEnd   = strtotime('6/30/'.$iQYear.' 23:59:59');
                  break;

               case CI_DATERANGE_3RD_Q:
                  $strDateRange = "3rd Quarter $iQYear";
                  $dteStart = strtotime('7/1/'.$iQYear);
                  $dteEnd   = strtotime('9/30/'.$iQYear.' 23:59:59');
                  break;

               case CI_DATERANGE_4TH_Q:
                  $strDateRange = "4th Quarter $iQYear";
                  $dteStart = strtotime('10/1/'.$iQYear);
                  $dteEnd   = strtotime('12/31/'.$iQYear.' 23:59:59');
                  break;

               default:
                  screamForHelp('Invalid case index in routine determineDateRange!');
                  break;
             }
         }else {
            screamForHelp($lDateRangeConst.' - Unrecognized DATE RANGE detected, error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__);
         }
         break;
   }

   if ($bIncludeDateRange) $strDateRange .= ' (' . date('m/d/Y', $dteStart).' - '.date('m/d/Y', $dteEnd).')';
}

function bVerifyValidMonthDayYear($lMonth, $lDay, $lYear){
//----------------------------------------------------------------------------------
//  This routine is designed to verify a valid date was selected from drop-down
//  lists. For example, return false if the m/d/Y = 2/31/2003
//
//  jpz 1/23/08 - removed dependance on UNIX timestamp
//----------------------------------------------------------------------------------
   if (! ((is_numeric($lYear) && is_numeric($lMonth) && is_numeric($lDay))) ) return(false);

   if (   ($lDay   < 1    || $lDay   > 31)
       || ($lMonth < 1    || $lMonth > 12)
       || ($lYear  < 1000 || $lYear  > 6500)) return(false);

   if (($lMonth == 2) && ($lDay > 29)) return(false);

      //------------------------
      // non-leap year test
      //------------------------
   if ( ($lMonth == 2) && ($lDay == 29) && (($lYear) % 4 != 0 )) return(false);

     //-----------------------------
     // 30 days have sept,...
     //-----------------------------
   if ( ($lDay == 31) &&
          ( ($lMonth == 4) || ($lMonth == 6) || ($lMonth ==  9) || ($lMonth == 11)) ) return(false);

      //-------------------------------
      // weird leap year case
      //-------------------------------
   if ( ($lMonth == 2) && ($lDay == 29) && (($lYear % 4) == 0)
            && (($lYear % 100)==0)
            && (($lYear % 400)!= 0)) return(false);

   return(true);
}

function convertTo24Hour(&$lHour, $bAM){
//----------------------------------------------------------------------------------
// convert a 12-hour formatted hour to 24 hour format
//----------------------------------------------------------------------------------
   if ($bAM) {
      if ($lHour==12) $lHour = 0;
   }else {
      if ($lHour<12)  {
         $lHour += 12;
      }
   }
}

function convertTo12Hour(&$lHour, &$bAM){
//----------------------------------------------------------------------------------
// convert a 24-hour formatted hour to 12 hour format
//----------------------------------------------------------------------------------
   if ($lHour >= 12) {
      $bAM = false;
      $lHour -= 12;
   }else {
      $bAM = true;
   }
   if ($lHour==0) $lHour = 12;
}

function strDisplayTimeDDL($dteTime, $bShowBlank, $bValAsQuarter=false){
//-------------------------------------------------------------------------
//  display time in 15 minute intervals
//-------------------------------------------------------------------------
   if (is_null($dteTime) || ($dteTime < 0)){
      $strEventTime = '';
      $lMatch = -1;
   }else {
      if ($bValAsQuarter){
         $lMatch = lTimeToQuarters($dteTime);
      }else {
         $strEventTime = date('g:i A', $dteTime);
      }
   }

   $strDDL = '';
   if ($bShowBlank) {
      $strDDL .= '<option value="-1">&nbsp;</option>'."\n";
   }
   $dteBaseTime = strtotime('12:00:00 AM');
   for ($idx=0; $idx<96; ++$idx) {
      $strDisplayTime = date('g:i A',$dteBaseTime);

      if ($bValAsQuarter){
         $strOption = $idx;
         $strSelected = ($idx==$lMatch ? 'selected' : '');
      }else {
         $strOption = $dteBaseTime;
         $strSelected = ($strEventTime==$strDisplayTime ? 'selected' : '');
      }

      $strDDL .= '<option value="'.$strOption.'" '.$strSelected.' >'
            .$strDisplayTime
            ."</option> \n";
      $dteBaseTime += 900;  // 15 min *60 sec
   }
   return($strDDL);
}

function strDurationDDL($bShowBlank, $bShowAllDay, $strDurationSelect, $bOptValAsQuarters=false){
//-------------------------------------------------------------------------
//  display duratin in 15 minute intervals
//  note: 96 = # of 15 minute intervals in 24 hours
//-------------------------------------------------------------------------
   $strDDL = '';
   if ($bShowBlank)  $strDDL .= '<option value="" >&nbsp;</option>'."\n";
   if ($bShowAllDay) $strDDL .= '<option value="(all day)" '.($strDurationSelect=='(all day)' ? 'selected' : '').'>(all day)</option>'."\n";
   for ($idx=1; $idx<96; ++$idx){
      $lHours = (integer)($idx / 4);
      $lQuarter = $idx % 4;
      $strOption = strXlateDuration($idx, false);
      if ($bOptValAsQuarters){
         $strValue = $idx;
      }else {
         $strValue = trim($strOption);
      }
      $strSelect = ($strDurationSelect==$strValue ? 'selected' : '');

      $strDDL .= '<option value="'.$strValue.'" '.$strSelect.'>'.$strOption.'</option>'."\n";
   }
   return($strDDL);
}

function lDurationHrsToQuarters($decDuration){
//-------------------------------------------------------------------------
// convert decimal hours to # of quarters
//-------------------------------------------------------------------------
   $lDuration = (integer)$decDuration;
   $sngFrac   = $decDuration - $lDuration;
   return( ($lDuration*4) + (integer)($sngFrac/0.25));
}

function lTimeToQuarters($dteTime){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $lHrs = (int)(date('G', $dteTime));
   $lMin = (int)(date('i', $dteTime));
   return( ($lHrs*4) + ((integer)($lMin/15)));
}

function quartersToHrsMin($lQuarters, &$lHrs, &$lMins){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $lHrs = (int)($lQuarters / 4);
   $lMins = ($lQuarters % 4) * 15;
}

function strXlateDuration($lDuration, $bTerse=true){
//-------------------------------------------------------------------------
//
//-------------------------------------------------------------------------
   $lHours = (integer)($lDuration / 4);
   $lQuarter = $lDuration % 4;
   $strDuration = '';

   if ($lHours==1){
      $strDuration .= $lHours.($bTerse ? 'hr' : ' hour');
   }elseif ($lHours > 1) {
      $strDuration .= $lHours.($bTerse ? 'hr' : ' hours');
   }
   if ($lQuarter==1){
      $strDuration .= ' 15'.($bTerse ? 'min' : ' minutes');
   }elseif ($lQuarter==2){
      $strDuration .= ' 30'.($bTerse ? 'min' : ' minutes');
   }elseif ($lQuarter==3){
      $strDuration .= ' 45'.($bTerse ? 'min' : ' minutes');
   }
   return($strDuration);
}

function dteMySQLTime2Unix($strMySQLTime){
//-------------------------------------------------------------------------
// convert a mySQL timestring to a unix timestamp
// mySQL timestring format: hh:mm:ss
//
// if the time string is null, return 0; date portion is the current date
//-------------------------------------------------------------------------
   if (is_null($strMySQLTime)) {
      return(0);
   }

   return(strtotime (
               substr($strMySQLTime, 0, 2).':'
              .substr($strMySQLTime, 3, 2).':'
              .substr($strMySQLTime, 6, 2))
          );
}

function MySQL_TimeExtract($strMySQLTime, &$lHour, &$lMinute, &$lSecond){
//-------------------------------------------------------------------------
// convert a mySQL timestring to integer hours, minutes, seconds
//-------------------------------------------------------------------------
   $lHour   = (integer)substr($strMySQLTime, 0, 2);
   $lMinute = (integer)substr($strMySQLTime, 3, 2);
   $lSecond = (integer)substr($strMySQLTime, 6, 2);
}

function strMinutesToMySQLTime($lMinutes){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $lHours = floor($lMinutes/60);
   $lMinutes = $lMinutes % 60;
   return(str_pad($lHours,   2, '0', STR_PAD_LEFT).':'
         .str_pad($lMinutes, 2, '0', STR_PAD_LEFT).':00');
}

function strDurationViaMinutes($lMinutes){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $lHours = floor($lMinutes/60);
   $lMinutes = $lMinutes % 60;
   return($lHours.':'.str_pad($lMinutes, 2, '0', STR_PAD_LEFT));
}

function lMySQLTimeToMinutes($strMySqlTime){
   MySQL_TimeExtract($strMySqlTime, $lHour, $lMinute, $lSecond);
   return($lHour*60+$lMinute);
}

function strFormattedTimeViaMinutes($lMinutes, $b24Hr){
   $lHour = floor($lMinutes/60);
   $lMin  = $lMinutes % 60;
   if ($b24Hr){
      $strOut = str_pad($lHour, 2, '0', STR_PAD_LEFT).':'.str_pad($lMin, 2, '0', STR_PAD_LEFT);
   }else {
      $strAMPM = $lHour < 12 ? ' AM' : ' PM';
      if ($lHour > 12) $lHour = $lHour - 12;
      if ($lHour == 0 ) $lHour = 12;
      $strOut = $lHour.':'.str_pad($lMin, 2, '0', STR_PAD_LEFT).$strAMPM;
   }
   return($strOut);
}

function lMySQLDate2MY($strDate) {
//---------------------------------------------------------------------
// return the month/year integer for a given mysql date string
//---------------------------------------------------------------------
   return(
          ((integer)substr($strDate, 0, 4))*12
        + ((integer)substr($strDate, 5, 2))-1);
}

function lUTS_2_MY($dteDate){
//---------------------------------------------------------------------
// convert a unix timestamp to a monthYear
//---------------------------------------------------------------------
   numsMoDaYr($dteDate, $lMonth, $lDay, $lYear);
   return( ($lMonth-1) + ($lYear*12));
}

function dte_MY_2_UTS($lMY) {
//---------------------------------------------------------------------
// return the unix datestamp for a given monthYear integer
// (returns the first day of the month)
//---------------------------------------------------------------------
   $lHoldYear = (integer)($lMY/12);
   return(mktime(0, 0, 0, ($lMY-($lHoldYear*12)+1), 1, $lHoldYear));
}

function dteAdd_UT_Months($dteBase, $lNumMonths){
//------------------------------------------------------------------
// return a date representing the first day of the month, offset
// from the base date by lNumMonths
//------------------------------------------------------------------
   $objDate = getdate($dteBase);
   return(mktime ( 0, 0, 0, $objDate['mon']+$lNumMonths, 1, $objDate['year']));
}

function numsMoDaYr($dteTest, &$lMonth, &$lDay, &$lYear){
//------------------------------------------------------------------
// return the numbers for specified month/day/year
//------------------------------------------------------------------
   $myDate = getdate($dteTest);
   $lYear  = $myDate['year'];
   $lMonth = $myDate['mon'];
   $lDay   = $myDate['mday'];
}

function numsHrsMin($dteTest, &$lHours, &$lMinutes){
//------------------------------------------------------------------
// return the numbers for specified hours/minutes (24-hour format)
//------------------------------------------------------------------
   $myDate   = getdate($dteTest);
   $lHours   = $myDate['hours'];
   $lMinutes = $myDate['minutes'];
}

function numsMoDaYrMySQL($strMySQLDate, &$lMonth, &$lDay, &$lYear){
//------------------------------------------------------------------
//   DATETIME    '0000-00-00 00:00:00'
//   DATE        '0000-00-00'
//------------------------------------------------------------------
   $lMonth = (integer)substr($strMySQLDate, 5, 2);
   $lDay   = (integer)substr($strMySQLDate, 8, 2);
   $lYear  = (integer)substr($strMySQLDate, 0, 4);
}

function strMoDaYr2MySQLDate($lMonth, $lDay, $lYear){
//------------------------------------------------------------------
//
//------------------------------------------------------------------
    return(
       str_pad($lYear,  4, '0', STR_PAD_LEFT).'-'
      .str_pad($lMonth, 2, '0', STR_PAD_LEFT).'-'
      .str_pad($lDay,   2, '0', STR_PAD_LEFT));
}

function strHrMinSec2MySQLTime($lHour, $lMin, $lSec){
//------------------------------------------------------------------
//
//------------------------------------------------------------------
    return(
       str_pad($lHour,  2, '0', STR_PAD_LEFT).':'
      .str_pad($lMin,   2, '0', STR_PAD_LEFT).':'
      .str_pad($lSec,   2, '0', STR_PAD_LEFT));
}

function strStrDate2MySQLDate($strDate, $bDateFormatUS){
//------------------------------------------------------------------
// convert a string (for example 12/24/1942) to mysql string
//   DATETIME    '0000-00-00 00:00:00'
//   DATE        '0000-00-00'
//------------------------------------------------------------------
   $strDate = str_replace('-', '/', $strDate);
   $strDate = str_replace('.', '/', $strDate);

   $strDateFields = explode('/', $strDate);
   if ($bDateFormatUS){
      $strMSqlDate =
               str_pad($strDateFields[2], 4, '0', STR_PAD_LEFT).'-'
              .str_pad($strDateFields[0], 2, '0', STR_PAD_LEFT).'-'
              .str_pad($strDateFields[1], 2, '0', STR_PAD_LEFT);
   }else {
      $strMSqlDate =
               str_pad($strDateFields[2], 4, '0', STR_PAD_LEFT).'-'
              .str_pad($strDateFields[1], 2, '0', STR_PAD_LEFT).'-'
              .str_pad($strDateFields[0], 2, '0', STR_PAD_LEFT);
   }
   return($strMSqlDate);
}

function strLoadOptional_MDY_mySQL($strDDL_Month, $strDDL_Day, $strDDL_Year){
//------------------------------------------------------------------
//
//------------------------------------------------------------------
   $lYear  = (integer)@$_REQUEST[$strDDL_Year];
   $lMonth = (integer)@$_REQUEST[$strDDL_Month];
   $lDay   = (integer)@$_REQUEST[$strDDL_Day];

   if (($lYear<=0)||($lMonth<=0)||($lDay<=0)) return('NULL');
   if (!bVerifyValidMonthDayYear($lMonth, $lDay, $lYear)) return('NULL');

   return(strPrepStr(
                     str_pad($lYear,  4, '0', STR_PAD_LEFT)
                .'-'.str_pad($lMonth, 2, '0', STR_PAD_LEFT)
                .'-'.str_pad($lDay,   2, '0', STR_PAD_LEFT)));

}

function strLoadOptional_MY_mySQL($strDDL_Month, $strDDL_Year){
//------------------------------------------------------------------
//
//------------------------------------------------------------------
   $lYear  = (integer)@$_REQUEST[$strDDL_Year];
   $lMonth = (integer)@$_REQUEST[$strDDL_Month];

   if (($lYear<=0)||($lMonth<=0)) return('NULL');
   if (!bVerifyValidMonthDayYear($lMonth, 1, $lYear)) return('NULL');

   return(strPrepStr(
                     str_pad($lYear,  4, '0', STR_PAD_LEFT)
                .'-'.str_pad($lMonth, 2, '0', STR_PAD_LEFT)
                .'-01'));
}

function strNumericDateViaUTS($dteUTS, $bDateFormatUS){
//------------------------------------------------------------------
//
//------------------------------------------------------------------
   if ($bDateFormatUS){
      return(date('m/d/Y', $dteUTS));
   }else {
      return(date('d/m/Y', $dteUTS));
   }
}

function strNumericDateViaMysqlDate($dteMysqlDate, $bDateFormatUS){
//------------------------------------------------------------------
//
//------------------------------------------------------------------
   numsMoDaYrMySQL($dteMysqlDate, $lMonth, $lDay, $lYear);
   if ($bDateFormatUS){
      return($lMonth.'/'.$lDay.'/'.$lYear);
   }else {
      return($lDay.'/'.$lMonth.'/'.$lYear);
   }
}

function MDY_ViaUserForm($strDate, &$lMon, &$lDay, &$lYear, $bDateFormatUS){
//------------------------------------------------------------------
// return false on invalid date format
//------------------------------------------------------------------
   $strDateFields = explode('/', $strDate);
   if (count($strDateFields)!=3) return(false);
   for ($idx=0; $idx<3; ++$idx){
      if (!is_numeric($strDateFields[$idx])) return(false);
   }
   $lYear = (integer)$strDateFields[2];
   if ($bDateFormatUS){
      $lMon = (integer)$strDateFields[0];
      $lDay = (integer)$strDateFields[1];
   }else {
      $lMon = (integer)$strDateFields[1];
      $lDay = (integer)$strDateFields[0];
   }
   return(true);
}

function dteAddTimeToDate($dteBase, $lHour24, $lMinute, $lSecond){
//------------------------------------------------------------------
//
//------------------------------------------------------------------
   numsMoDaYr($dteBase, $lMonth, $lDay, $lYear);
   return(mktime($lHour24, $lMinute, $lSecond, $lMonth, $lDay, $lYear));
}

function dteRemoveTimeFromDate($dteBase){
//------------------------------------------------------------------
// return date only
//------------------------------------------------------------------
   numsMoDaYr($dteBase, $lMonth, $lDay, $lYear);
   return(mktime(0, 0, 0, $lMonth, $lDay, $lYear));
}

function ageCalculations(
                 $lPresentMonth, $lPresentDay,         $lPresentYear,
                 $lBirthMonth,   $lBirthDay,           $lBirthYear,
                 &$lAgeYears,    &$lAgeYearsModMonths, &$lAgeYearsModDays,
                 &$lAgeMonths,   &$lAgeMonthsModDays,
                 &$lAgeDays) {
//---------------------------------------------------------------------
// created 2009-06-08 jpz
//
//  example of ageYear output:
//      5 years, 3 months,  8 days
//  example of ageMonth output:
//      22 months,  14 days
//  example of ageDay output:
//      722 days
//---------------------------------------------------------------------
   if (   ($lBirthYear >  $lPresentYear)
       ||(($lBirthYear == $lPresentYear) && ($lBirthMonth >  $lPresentMonth))
       ||(($lBirthYear == $lPresentYear) && ($lBirthMonth == $lPresentMonth)
                                         && ($lBirthDay   >  $lPresentDay))) {
      echo('Present: '.$lPresentMonth.'/'.$lPresentDay.'/'.$lPresentYear.'<br>');
      echo('Birth: '  .$lBirthMonth   .'/'.$lBirthDay  .'/'.$lBirthYear.'<br>');
      screamForHelp('Birth date must be on or before current date!<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
   }

   $lNumDaysInPresentMonth = lDaysInMonth($lPresentMonth, $lPresentYear);
   $lNumDaysInBirthMonth   = lDaysInMonth($lBirthMonth,   $lBirthYear);

   $lAgeYears = $lPresentYear-$lBirthYear;

//echo('Line: '.__LINE__.": \$lPresentMonth=$lPresentMonth \$lBirthMonth=$lBirthMonth <br>\n");

      //------------------------
      // Year calculations
      //------------------------
   if ($lPresentMonth < $lBirthMonth){
      --$lAgeYears;
      $lAgeYearsModMonths = ($lPresentMonth+12)-$lBirthMonth;
      if ($lPresentDay < $lBirthDay) {
         --$lAgeYearsModMonths;
         $lAgeYearsModDays = $lPresentDay + ($lNumDaysInBirthMonth-$lBirthDay);
      }elseif ($lPresentDay == $lBirthDay)  {
         $lAgeYearsModDays = 0;
      }else {       // $lPresentDay > $lBirthDay
         $lAgeYearsModDays = $lPresentDay;
      }
   }elseif ($lPresentMonth > $lBirthMonth) {
      $lAgeYearsModMonths = ($lPresentMonth-$lBirthMonth);
      if ($lPresentDay < $lBirthDay) {
         --$lAgeYearsModMonths;
         $lAgeYearsModDays = ($lNumDaysInBirthMonth-$lBirthDay) + $lPresentDay ;
      }elseif ($lPresentDay == $lBirthDay) {
         $lAgeYearsModDays = 0;
      }else {     // $lPresentDay > $lBirthDay
         $lAgeYearsModDays = $lPresentDay - $lBirthDay;
      }
   }else {         // $lPresentMonth == $lBirthMonth
      if ($lPresentDay < $lBirthDay) {
         --$lAgeYears;
         $lAgeYearsModMonths = 11;
         $lAgeYearsModDays   = ($lNumDaysInBirthMonth-$lBirthDay) + $lPresentDay ;
      }elseif ($lPresentDay == $lBirthDay) {
         $lAgeYearsModMonths = 0;
         $lAgeYearsModDays   = 0;
      }else {     // $lPresentDay > $lBirthDay
         $lAgeYearsModMonths = 0;
         $lAgeYearsModDays   = $lPresentDay - $lBirthDay;
      }
   }

      //------------------------
      // Month calculations
      //------------------------
   $lAgeMonths        = $lAgeYears*12 + $lAgeYearsModMonths;
   $lAgeMonthsModDays = $lAgeYearsModDays;

      //------------------------
      // Day calculations
      //------------------------
   $lAgeDays = lDateDiff_DaysViaMDY(
                   $lPresentMonth, $lPresentDay, $lPresentYear,
                   $lBirthMonth,   $lBirthDay,   $lBirthYear);
}

function lDaysInMonth($lMonth, $lYear){
//---------------------------------------------------------------------
// created 2009-06-08 jpz
//  1 - Jan     2 - Feb    3 - Mar    4 - Apr*
//  5 - May     6 - Jun*   7 - Jul    8 - Aug
//  9 - Sep*   10 - Oct   11 - Nov*  12 - Dec
//---------------------------------------------------------------------

   if (   ($lMonth < 1)   || ($lMonth > 12)
       || ($lYear < 1000) || ($lYear > 6500)) {
      screamForHelp('Invalid Date<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
   }

   if (($lMonth==4)||($lMonth==6)||($lMonth==9)||($lMonth==11)){
      return(30);
   }elseif ($lMonth != 2){
      return(31);
   }else {
      if (
         (($lYear %   4)==0) &&
         (($lYear % 100)==0) &&
         (($lYear % 400)!=0)){
         return(28);
      }elseif (($lYear % 4)==0) {
         return(29);
      }else {
         return(28);
      }
   }
}

function lDateDiff_DaysViaMDY(
                   $lEndMonth,   $lEndDay,   $lEndYear,
                   $lStartMonth, $lStartDay, $lStartYear){
//---------------------------------------------------------------------
// created 2009-06-08 jpz
//---------------------------------------------------------------------
   if (   ( $lStartYear > $lEndYear)
       ||(($lStartYear == $lEndYear) && ($lStartMonth >  $lEndMonth))
       ||(($lStartYear == $lEndYear) && ($lStartMonth == $lEndMonth)
                                     && ($lStartDay   >  $lEndDay))) {
      screamForHelp('Start date must be on or before End date!<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
   }

      //----------------------------------------
      // special case - within the same month
      //----------------------------------------
   if (($lEndMonth==$lStartMonth) && ($lEndYear==$lStartYear)){
      return($lEndDay-$lStartDay);
   }

      //----------------------------------------
      // starting month days
      //----------------------------------------
   $lHoldDayCount = lDaysInMonth($lStartMonth, $lStartYear) - $lStartDay;

   $lLoopMonth = $lStartMonth + 1;
   $lLoopYear  = $lStartYear;
   if ($lLoopMonth>12) {
      $lLoopMonth = 1;
      ++$lLoopYear;
   }

   $lAbsLoopMonth = $lLoopYear*12 + $lLoopMonth;
   $lAbsMonthEnd   = $lEndYear*12 + $lEndMonth;

   for ($idx=$lAbsLoopMonth; $idx<$lAbsMonthEnd; ++$idx) {
      $lHoldDayCount += lDaysInMonth($lLoopMonth, $lLoopYear);
      ++$lLoopMonth;
      if ($lLoopMonth>12) {
         $lLoopMonth = 1;
         ++$lLoopYear;
      }
   }
   $lHoldDayCount += $lEndDay;
   return($lHoldDayCount);
}

function dteDatePicker2Date($strDate, $bDateFormatUS){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $strDate = str_replace('-', '/', $strDate);
   $strDate = str_replace('.', '/', $strDate);
   $strDateFields = explode('/', $strDate);
   if ($bDateFormatUS){
      $dteExamDate = strtotime($strDateFields[0].'/'.$strDateFields[1].'/'.$strDateFields[2]);
   }else {
      $dteExamDate = strtotime($strDateFields[1].'/'.$strDateFields[0].'/'.$strDateFields[2]);
   }
   return($dteExamDate);
}

function strDateForDatePicker($dteDateOfExam){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gbDateFormatUS;

   if ($dteDateOfExam <= 1) {
      $strDateExam = '';
   }else {
      if ($gbDateFormatUS){
         $strDateExam = date('m/d/Y', $dteDateOfExam);
      }else {
         $strDateExam = date('d/m/Y', $dteDateOfExam);
      }
   }
   return($strDateExam);
}

function strMDateForDatePicker($dteMDate){
//---------------------------------------------------------------------
// format a mysql date field for date picker
//---------------------------------------------------------------------
   global $gbDateFormatUS;

   if ($dteMDate.'' == '') {
      $strPickerDate = '';
   }else {
      numsMoDaYrMySQL($dteMDate, $lMonth, $lDay, $lYear);
      if ($gbDateFormatUS){
         $strPickerDate =
                         str_pad($lMonth, 2, '0', STR_PAD_LEFT).'/'
                        .str_pad($lDay,   2, '0', STR_PAD_LEFT).'/'
                        .str_pad($lYear,  4, '0', STR_PAD_LEFT);
      }else {
         $strPickerDate =
                         str_pad($lDay,   2, '0', STR_PAD_LEFT).'/'
                        .str_pad($lMonth, 2, '0', STR_PAD_LEFT).'/'
                        .str_pad($lYear,  4, '0', STR_PAD_LEFT);
      }
   }
   return($strPickerDate);
}

function bDateInFuture($lDay, $lMonth, $lYear){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gdteNow;
   numsMoDaYr($gdteNow, $lMonthNow, $lDayNow, $lYearNow);
   if ($lYear < $lYearNow) return(false);
   if ($lYear > $lYearNow) return(true);

   if ($lMonth < $lMonthNow) return(false);
   if ($lMonth > $lMonthNow) return(true);

   if ($lDay <= $lDayNow) return(false);
   return(true);
}

function bDateInPast($lDay, $lMonth, $lYear){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gdteNow;
   numsMoDaYr($gdteNow, $lMonthNow, $lDayNow, $lYearNow);
   if ($lYear > $lYearNow) return(false);
   if ($lYear < $lYearNow) return(true);

   if ($lMonth > $lMonthNow) return(false);
   if ($lMonth < $lMonthNow) return(true);

   if ($lDay >= $lDayNow) return(false);
   return(true);
}

function bValidVerifyDate($strDate){
//---------------------------------------------------------------------
// useful in form verification
//---------------------------------------------------------------------
   global $gbDateFormatUS;
   if (!MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS)) return(false);
   return(bVerifyValidMonthDayYear($lMon, $lDay, $lYear));
}

function bValidMySQLDate($strDate){
//---------------------------------------------------------------------
//   yyyy-mm-dd
//---------------------------------------------------------------------
   $dateFields = explode('-',$strDate);
   if (count($dateFields) != 3) return(false);

   if (strlen($dateFields[0]) != 4) return(false);
   if (strlen($dateFields[1]) != 2) return(false);
   if (strlen($dateFields[2]) != 2) return(false);

   if (!is_numeric($dateFields[0])) return(false);
   if (!is_numeric($dateFields[1])) return(false);
   if (!is_numeric($dateFields[2])) return(false);
   return(bVerifyValidMonthDayYear((integer)$dateFields[1], (integer)$dateFields[2], (integer)$dateFields[0]));
}

function bValidVerifyUnixRange($strDate){
//---------------------------------------------------------------------
// useful in form verification
//---------------------------------------------------------------------
   global $gbDateFormatUS;
   MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
   return(!($lYear < 1970 || $lYear > 2037));
}

function bValidVerifyNotFuture($strDate){
//---------------------------------------------------------------------
// useful in form verification
//---------------------------------------------------------------------
   global $gbDateFormatUS;
   MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
   return(!bDateInFuture($lDay, $lMon, $lYear));
}

function bValidVerifyNotPast($strDate){
//---------------------------------------------------------------------
// useful in form verification
//---------------------------------------------------------------------
   global $gbDateFormatUS;
   MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
   return(!bDateInPast($lDay, $lMon, $lYear));
}

function bVerifyCartBeforeHorse($strStartDate, $strEndDate){
//---------------------------------------------------------------------
// return false if end date before start date
//---------------------------------------------------------------------
   global $gbDateFormatUS;

   MDY_ViaUserForm($strStartDate, $lMonS, $lDayS, $lYearS, $gbDateFormatUS);
   MDY_ViaUserForm($strEndDate,   $lMonE, $lDayE, $lYearE, $gbDateFormatUS);

   if ($lYearS != $lYearE)  return($lYearS < $lYearE);

   if ($lMonS != $lMonE)  return($lMonS < $lMonE);

   return($lDayS <= $lDayE);
}

?>