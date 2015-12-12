<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
/*---------------------------------------------------------------------
// date/time class
//
// copyright (c) 2011 by Database Austin, Austin Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
---------------------------------------------------------------------
/* Notes:
     I wanted a fairly simple date/time class that was not constrained
     by the UNIX timestamp boundaries. Also, I wanted something that
     interfaced easily with the mySQL date format.
  ---------------------------------------------------------------------
  
    class dl_date_time{
        function setDateTime         ($idx, $lHour, $lMinute, $lSecond, $lMonth, $lDay, $lYear) {
        function getDateTime         ($idx, &$lHour, &$lMinute, &$lSecond, &$lMonth, &$lDay, &$lYear) {
        function setDateTimeViaMySQL ($idx, $strMySQLDate) {
        function setDateViaMySQL     ($idx, $strMySQLDate) {
        function strMySQL_DateTime   ($idx, $bQuotes) {
        function strMySQL_Date       ($idx, $bQuotes) {
        function setNow              ($idx){
        function lAgeYears           ($idx) {
        function strPeopleAge        ($idx, $strMysqlBDate){
        function strAge              ($idx, $strMysqlBDate) {
  
   comparison
   -------------------
        function bDateTimeBeforeEqual($idx1, $idx2) {
        function bDateTimeBefore     ($idx1, $idx2) {
        function bDateTimeAfterEqual ($idx1, $idx2) {
        function bDateTimeEqual      ($idx1, $idx2) {
        function bDateTimeAfter      ($idx1, $idx2) {
  
   formatting
   -------------------
        function strDateTimeFormat($lFormatType) {
        function bDateTimeOkay    ($lHour, $lMinute, $lSecond, $lMonth, $lDay, $lYear){
  
   non-class functions
   -------------------
        function dba_parseDateTime     ($strDateTime,    $bCorrect2000,
        function dba_strMySQL_DateTime ($bQuotes,
        function dba_strMySQL_Date     ($bQuotes,
        function dba_strTextDate2MySQL ($strTextDate, $bQuotes, &$bError){
        function dba_strXlateShortMonth($lMonth) {
        function dba_strXlateLongMonth ($lMonth) {
        function normalizeDateTime     ($lHour, $lMinute, $lSecond, $lMonth, $lDay, $lYear);
        normalizeDate                  (&$lMonth, &$lDay, &$lYear)
  
  --------------------------------------------------------------------- */

/*---------------------------------------------------------------------
   for reference:
  
   from the mySQL manual (http://dev.mysql.com/doc/mysql/en/Date_and_time_types.html):
     Column Type `Zero' Value
     ----------- ---------------
     DATETIME    '0000-00-00 00:00:00'
     DATE        '0000-00-00'
     TIMESTAMP   00000000000000
     TIME        '00:00:00'
     YEAR        0000
  ---------------------------------------------------------------------*/

class dl_date_time{
   var $timeTable; // $lHour, $lMinute, $lSecond, $lMonth, $lDay, $lYear;

   function __construct(){
   //---------------------------------------------------------------------
   // constructor; null year is our indicator that the date/time is
   // uninitialized.
   //---------------------------------------------------------------------
      $this->timeTable = array();
   }

   function setDateTime($idx, $lHour, $lMinute, $lSecond, $lMonth, $lDay, $lYear) {
   //---------------------------------------------------------------------
   // for the caller's date/time information, set the class variables
   // this routine automatically normalizes out-of-range values.
   // for example, if the user supplies a date of 2/29/2001, it will
   // be converted to 3/1/2001
   //---------------------------------------------------------------------
      normalizeDateTime($lHour, $lMinute, $lSecond, $lMonth, $lDay, $lYear);
      $this->timeTable[$idx] = new stdClass;
      $this->timeTable[$idx]->lHour   = $lHour;
      $this->timeTable[$idx]->lMinute = $lMinute;
      $this->timeTable[$idx]->lSecond = $lSecond;
      $this->timeTable[$idx]->lMonth  = $lMonth;
      $this->timeTable[$idx]->lDay    = $lDay;
      $this->timeTable[$idx]->lYear   = $lYear;
   }

   function getDateTime($idx, &$lHour, &$lMinute, &$lSecond, &$lMonth, &$lDay, &$lYear) {
   //---------------------------------------------------------------------
   // return date/time information, set the class variables
   //---------------------------------------------------------------------
      if (!isset($this->timeTable[$idx])) {
         $lHour   =   null;
         $lMinute =   null;
         $lSecond =   null;
         $lMonth  =   null;
         $lDay    =   null;
         $lYear   =   null;
      }else {
         $lHour   =   $this->timeTable[$idx]->lHour;
         $lMinute =   $this->timeTable[$idx]->lMinute;
         $lSecond =   $this->timeTable[$idx]->lSecond;
         $lMonth  =   $this->timeTable[$idx]->lMonth;
         $lDay    =   $this->timeTable[$idx]->lDay;
         $lYear   =   $this->timeTable[$idx]->lYear;
      }
   }

   function setDateTimeViaMySQL($idx, $strMySQLDate) {
   //---------------------------------------------------------------------
   // '0000-00-00 00:00:00'
   //---------------------------------------------------------------------
      if (strlen($strMySQLDate)!=19) {
         echo($strMySQLDate.' <b>invalid mysql datetime string, error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__."</b><br>\n");
      }else {
         $this->timeTable[$idx] = new stdClass;
      
         $this->timeTable[$idx]->lYear   = (integer)substr($strMySQLDate,  0, 4);
         $this->timeTable[$idx]->lMonth  = (integer)substr($strMySQLDate,  5, 2);
         $this->timeTable[$idx]->lDay    = (integer)substr($strMySQLDate,  8, 2);

         $this->timeTable[$idx]->lHour   = (integer)substr($strMySQLDate, 11, 2);
         $this->timeTable[$idx]->lMinute = (integer)substr($strMySQLDate, 14, 2);
         $this->timeTable[$idx]->lSecond = (integer)substr($strMySQLDate, 17, 2);
      }
   }

   function setDateViaMySQL($idx, $strMySQLDate) {
   //---------------------------------------------------------------------
   // '0000-00-00'
   //---------------------------------------------------------------------
      if (is_null($strMySQLDate)){
         $this->timeTable[$idx] = null;
      }elseif (strlen($strMySQLDate)!=10) {
         echo($strMySQLDate.' <b>invalid mysql datetime string, error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__."</b><br>\n");
         $this->timeTable[$idx] = null;
      }else {
         $this->timeTable[$idx] = new stdClass;

         $this->timeTable[$idx]->lYear   = (integer)substr($strMySQLDate,  0, 4);
         $this->timeTable[$idx]->lMonth  = (integer)substr($strMySQLDate,  5, 2);
         $this->timeTable[$idx]->lDay    = (integer)substr($strMySQLDate,  8, 2);

         $this->timeTable[$idx]->lHour   = 0;
         $this->timeTable[$idx]->lMinute = 0;
         $this->timeTable[$idx]->lSecond = 0;
      }
   }

   function strMySQL_DateTime($idx, $bQuotes) {
   //---------------------------------------------------------------------
   // return a string formatted as a mySQL date/time field
   //---------------------------------------------------------------------
      if (!isset($this->timeTable[$idx])){
         return(($bQuotes?"'":'').'0000-00-00 00:00:00'.($bQuotes?"'":''));
      }else {
         return(
             ($bQuotes?"'":'')
            .str_pad($this->timeTable[$idx]->lYear.'',  4, '0', STR_PAD_LEFT).'-'
            .str_pad($this->timeTable[$idx]->lMonth.'', 2, '0', STR_PAD_LEFT).'-'
            .str_pad($this->timeTable[$idx]->lDay.'',   2, '0', STR_PAD_LEFT).' '

            .str_pad($this->timeTable[$idx]->lHour.'',   2, '0', STR_PAD_LEFT).':'
            .str_pad($this->timeTable[$idx]->lMinute.'', 2, '0', STR_PAD_LEFT).':'
            .str_pad($this->timeTable[$idx]->lSecond.'', 2, '0', STR_PAD_LEFT)
            .($bQuotes?"'":'') );
      }
   }

   function strMySQL_Date($idx, $bQuotes) {
   //---------------------------------------------------------------------
   // return a string formatted as a mySQL date field
   //---------------------------------------------------------------------
      if (!isset($this->timeTable[$idx])){
         return(($bQuotes?"'":'').'0000-00-00'.($bQuotes?"'":''));
      }else {
         return(
             ($bQuotes?"'":'')
            .str_pad($this->timeTable[$idx]->lYear.'',  4, '0', STR_PAD_LEFT).'-'
            .str_pad($this->timeTable[$idx]->lMonth.'', 2, '0', STR_PAD_LEFT).'-'
            .str_pad($this->timeTable[$idx]->lDay.'',   2, '0', STR_PAD_LEFT)
            .($bQuotes?"'":'') );
      }
   }

   function setNow($idx){
   //---------------------------------------------------------------------
   //  will work for 30+ more years!!!
   //---------------------------------------------------------------------
      $objNow = getdate();
      $this->timeTable[$idx] = new stdClass;

      $this->timeTable[$idx]->lYear   = $objNow['year'];
      $this->timeTable[$idx]->lMonth  = $objNow['mon'];
      $this->timeTable[$idx]->lDay    = $objNow['mday'];

      $this->timeTable[$idx]->lHour   = $objNow['hours'];
      $this->timeTable[$idx]->lMinute = $objNow['minutes'];
      $this->timeTable[$idx]->lSecond = $objNow['seconds'];
   }

   /*-------------------------------------------------------------------------*/
   /*        Comparison Functions                                             */
   /*-------------------------------------------------------------------------*/

   function bDateTimeBeforeEqual($idx1, $idx2) {
   //---------------------------------------------------------------------
   // return true if $objDateTime1<=$objDateTime2
   //---------------------------------------------------------------------
      if ($this->bDateTimeEqual($idx1, $idx2)) {
         return(true);
      }else {
         return($this->bDateTimeBefore($idx1, $idx2));
      }
   }

   function bDateTimeBefore($idx1, $idx2) {
   //---------------------------------------------------------------------
   // return true if $objDateTime1<$objDateTime2
   //---------------------------------------------------------------------
      return(dl_date_time::bDateTimeAfter($idx2, $idx1));
   }

   function bDateTimeAfterEqual($idx1, $idx2) {
   //---------------------------------------------------------------------
   // return true if $objDateTime1 >= $objDateTime2
   //---------------------------------------------------------------------
      if ($this->bDateTimeEqual($idx1, $idx2)) {
         return(true);
      }else {
         return($this->bDateTimeAfter($idx1, $idx2));
      }
   }

   function bDateTimeEqual($idx1, $idx2) {
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return (
           ($this->timeTable[$idx1]->lYear   == $this->timeTable[$idx2]->lYear)   &&
           ($this->timeTable[$idx1]->lMonth  == $this->timeTable[$idx2]->lMonth)  &&
           ($this->timeTable[$idx1]->lDay    == $this->timeTable[$idx2]->lDay)    &&
           ($this->timeTable[$idx1]->lHour   == $this->timeTable[$idx2]->lHour)   &&
           ($this->timeTable[$idx1]->lMinute == $this->timeTable[$idx2]->lMinute) &&
           ($this->timeTable[$idx1]->lSecond == $this->timeTable[$idx2]->lSecond));
   }

   function bDateTimeAfter($idx1, $idx2) {
   //---------------------------------------------------------------------
   // return true if $$this->timeTable[$idx1] > $this->timeTable[$idx2]
   //---------------------------------------------------------------------
      if ($this->timeTable[$idx1]->lYear > $this->timeTable[$idx2]->lYear) {
         return(true);
      }elseif ($this->timeTable[$idx1]->lYear < $this->timeTable[$idx2]->lYear) {
         return(false);
      }else {  // same year
         if ($this->timeTable[$idx1]->lMonth > $this->timeTable[$idx2]->lMonth) {
            return(true);
         }elseif ($this->timeTable[$idx1]->lMonth < $this->timeTable[$idx2]->lMonth) {
            return(false);
         }else {  // same year and month
            if ($this->timeTable[$idx1]->lDay > $this->timeTable[$idx2]->lDay) {
               return(true);
            }elseif ($this->timeTable[$idx1]->lDay < $this->timeTable[$idx2]->lDay) {
               return(false);
            }else {   // same year, month, and day
               if ($this->timeTable[$idx1]->lHour > $this->timeTable[$idx2]->lHour) {
                  return(true);
               }elseif ($this->timeTable[$idx1]->lHour < $this->timeTable[$idx2]->lHour) {
                  return(false);
               }else {  // same year, month, day and hour
                  if ($this->timeTable[$idx1]->lMinute > $this->timeTable[$idx2]->lMinute) {
                     return(true);
                  }elseif ($this->timeTable[$idx1]->lMinute < $this->timeTable[$idx2]->lMinute) {
                     return(false);
                  }else {  // same year, month, day, hour and minute
                     if ($this->timeTable[$idx1]->lSecond > $this->timeTable[$idx2]->lSecond) {
                        return(true);
                     }else {
                        return(false);
                     }
                  }
               }
            }
         }
      }
   }

   function lAgeYears($idx, $dtePresent=null) {
   //---------------------------------------------------------------------
   // return the age (now()-$this
   //---------------------------------------------------------------------
      global $gdteNow;
      
      if (is_null($dtePresent)) $dtePresent = $gdteNow;
      $objNow = getdate($dtePresent);

      $lYears = $objNow['year']-$this->timeTable[$idx]->lYear;

      if ($objNow['mon'] < $this->timeTable[$idx]->lMonth){
         return($lYears-1);
      }elseif ($objNow['mon'] > $this->timeTable[$idx]->lMonth) {
         return($lYears);
      }elseif ($objNow['mday'] < $this->timeTable[$idx]->lDay) {
         return($lYears-1);
      }else {
         return($lYears);
      }
   }

   function strPeopleAge($idx, $strMysqlBDate, &$lAgeYears, $lFormatID=1) {
   //---------------------------------------------------------------------
   // for a given mySQL-formatted date, return the age with birthday
   //---------------------------------------------------------------------
      if (is_null($strMysqlBDate)){
         $strBDate = '(unknown)';
         $lAgeYears = null;
      }else {
         $this->setDateViaMySQL($idx, $strMysqlBDate);
         $lHoldAge = $lAgeYears = $this->lAgeYears($idx);

         $strBDate = $this->strDateTimeFormat($idx, $lFormatID).' (';

         if ($lHoldAge==0){
            $strBDate .= 'less than 1 year old)';
         }else {
            $strBDate .=  $lHoldAge.' year'.($lHoldAge==1?'':'s').' old)';
         }
      }
      return($strBDate);
   }

   function strAge($idx, $strMysqlBDate) {
   //---------------------------------------------------------------------
   // for a given mySQL-formatted date, return the age w/o birthday
   //---------------------------------------------------------------------
      if (is_null($strMysqlBDate)){
         $strAge = '(unknown)';
      }else {
         $this->setDateViaMySQL($idx, $strMysqlBDate);
         $lHoldAge = $this->lAgeYears($idx);

         if ($lHoldAge==0){
            $strAge = 'less than 1 year old';
         }else {
            $strAge =  $lHoldAge.' year'.($lHoldAge==1?'':'s').' old';
         }
      }
      return($strAge);
   }



   /*-------------------------------------------------------------------------*/
   /*        Format Functions                                                 */
   /*-------------------------------------------------------------------------*/

   function strDateTimeFormat($idx, $lFormatType) {
   //---------------------------------------------------------------------
   // return a string with the formated date/time
   //
   // format type:
   //  US
   //   1:   m/d/yyyy
   //   2:   mm/dd/yyyy
   //   3:   m/d/yyyy hh:mm:ss (24 hr)
   //   4:   m/d/yyyy h:mm AM/PM (12 hr)
   //   5:   mon yyyy
   //   6:   month d, yyyy
   //   7:   mon d, yyyy
   //   8:   m/d/yy
   //
   //  European/Indian
   //  11:   d/m/yyyy
   //  12:   dd/mm/yyyy
   //  13:   d/m/yyyy hh:mm:ss (24 hr)
   //  14:   d/m/yyyy h:mm AM/PM (12 hr)
   //  15:   mon yyyy
   //  16:   d month, yyyy
   //  17:   d mon, yyyy
   //  18:   d/m/yy
   //
   //---------------------------------------------------------------------
      switch ($lFormatType) {
         case 1:
            $strDate = $this->timeTable[$idx]->lMonth.'/'.$this->timeTable[$idx]->lDay.'/'.$this->timeTable[$idx]->lYear;
            break;

         case 2:
            $strDate =
                 str_pad($this->timeTable[$idx]->lMonth.'', 2, '0', STR_PAD_LEFT)
                .'/'
                .str_pad($this->timeTable[$idx]->lDay.'', 2, '0', STR_PAD_LEFT)
                .'/'.$this->timeTable[$idx]->lYear;
            break;

         case 3:
            $strDate = $this->timeTable[$idx]->lMonth.'/'.$this->timeTable[$idx]->lDay.'/'.$this->timeTable[$idx]->lYear
                 .' '.str_pad($this->timeTable[$idx]->lHour.'',   2, '0', STR_PAD_LEFT)
                 .':'.str_pad($this->timeTable[$idx]->lMinute.'', 2, '0', STR_PAD_LEFT)
                 .':'.str_pad($this->timeTable[$idx]->lSecond.'', 2, '0', STR_PAD_LEFT);
            break;

         case 4:
            $strDate = $this->timeTable[$idx]->lMonth.'/'.$this->timeTable[$idx]->lDay.'/'.$this->timeTable[$idx]->lYear;
            $bPM = $this->timeTable[$idx]->lHour >= 12;
            $lLocHr = $this->timeTable[$idx]->lHour;
            if ($lLocHr>12) $lLocHr -= 12;
            if ($lLocHr==0) $lLocHr = 12;
            $strDate .=
                  ' '.$lLocHr
                 .':'.str_pad($this->timeTable[$idx]->lMinute.'', 2, '0', STR_PAD_LEFT)
                 .' '.($bPM ? 'PM':'AM');
            break;

         case 5:
            $strDate = dba_strXlateShortMonth($this->timeTable[$idx]->lMonth).' '.$this->timeTable[$idx]->lYear;
            break;

         case 6:
            $strDate = dba_strXlateLongMonth($this->timeTable[$idx]->lMonth).' '.$this->timeTable[$idx]->lDay.', '.$this->timeTable[$idx]->lYear;
            break;

         case 7:
            $strDate = dba_strXlateShortMonth($this->timeTable[$idx]->lMonth).' '.$this->timeTable[$idx]->lDay.', '.$this->timeTable[$idx]->lYear;
            break;

         case 8:
            $strDate = $this->timeTable[$idx]->lMonth.'/'.$this->timeTable[$idx]->lDay.'/'.substr($this->timeTable[$idx]->lYear.'',2,2);
            break;

            //----------------------------
            // European/Indian formats
            //----------------------------
         case 11:
            $strDate = $this->timeTable[$idx]->lDay.'/'.$this->timeTable[$idx]->lMonth.'/'.$this->timeTable[$idx]->lYear;
            break;

         case 12:
            $strDate =
                 str_pad($this->timeTable[$idx]->lDay.'',   2, '0', STR_PAD_LEFT)
                .'/'
                .str_pad($this->timeTable[$idx]->lMonth.'', 2, '0', STR_PAD_LEFT)
                .'/'.$this->timeTable[$idx]->lYear;
            break;

         case 13:
            $strDate =
                    $this->timeTable[$idx]->lDay.'/'.$this->timeTable[$idx]->lMonth.'/'.$this->timeTable[$idx]->lYear
                 .' '.str_pad($this->timeTable[$idx]->lHour.'',   2, '0', STR_PAD_LEFT)
                 .':'.str_pad($this->timeTable[$idx]->lMinute.'', 2, '0', STR_PAD_LEFT)
                 .':'.str_pad($this->timeTable[$idx]->lSecond.'', 2, '0', STR_PAD_LEFT);
            break;

         case 14:
            $strDate = $this->timeTable[$idx]->lDay.'/'.$this->timeTable[$idx]->lMonth.'/'.$this->timeTable[$idx]->lYear;
            $bPM = $this->timeTable[$idx]->lHour >= 12;
            $lLocHr = $this->timeTable[$idx]->lHour;
            if ($lLocHr>12) $lLocHr -= 12;
            if ($lLocHr==0) $lLocHr = 12;
            $strDate .=
                  ' '.$lLocHr
                 .':'.str_pad($this->timeTable[$idx]->lMinute.'', 2, '0', STR_PAD_LEFT)
                 .' '.($bPM?'PM':'AM');
            break;

         case 15:
            $strDate = dba_strXlateShortMonth($this->timeTable[$idx]->lMonth).' '.$this->timeTable[$idx]->lYear;
            break;

         case 16:
            $strDate = $this->timeTable[$idx]->lDay.' '.dba_strXlateLongMonth($this->timeTable[$idx]->lMonth).', '.$this->timeTable[$idx]->lYear;
            break;

         case 17:
            $strDate = $this->timeTable[$idx]->lDay.' '.dba_strXlateShortMonth($this->timeTable[$idx]->lMonth).', '.$this->timeTable[$idx]->lYear;
            break;

         case 18:
            $strDate = $this->timeTable[$idx]->lDay.'/'.$this->timeTable[$idx]->lMonth.'/'.substr($this->timeTable[$idx]->lYear.'', 2, 2);
            break;

         default:
            $strDate = '#error#';
            break;
      }
      return($strDate);
   }

   function bDateTimeOkay($lHour, $lMinute, $lSecond, $lMonth, $lDay, $lYear){
   //---------------------------------------------------------------------
   // return true if date is valid.
   // Compliant with Zager & Evans
   //---------------------------------------------------------------------
      if (!is_numeric($lHour))   return(false);
      if (!is_numeric($lMinute)) return(false);
      if (!is_numeric($lSecond)) return(false);
      if (!is_numeric($lMonth))  return(false);
      if (!is_numeric($lDay))    return(false);
      if (!is_numeric($lYear))   return(false);

      if (($lHour<0)   || ($lHour>23))   return(false);
      if (($lMinute<0) || ($lMinute>59)) return(false);
      if (($lSecond<0) || ($lSecond>59)) return(false);

      if (($lYear>9999) || ($lYear<-9999)) return(false);
      if (($lMonth<=0)  || ($lMonth>12))   return(false);
      if ($lDay<=0) return(false);

         // that pesky February....
      if ($lMonth==2) {
         if (($lYear%4)==0){
            if ($lDay>29) return(false);
         }else {
            if ($lDay>28) return(false);
         }
      }
         // weird leap year case
      if (($lMonth==2) && ($lDay== 29) && (($lYear%100)==0) && (($lYear%400)!= 0)) return(false);

                        // 30 days have Sept,...
      if (($lDay==31) &&
                (($lMonth==4) || ($lMonth==6) || ($lMonth==9) || ($lMonth==11))) return(false);

      return(true);
   }
}


/*-------------------------------------------------------------------------*/
/*  Parsing Functions / non-class                                          */
/*-------------------------------------------------------------------------*/

function dba_parseDateTime($strDateTime,    $bCorrect2000,
                       &$lMonth, &$lDay,    &$lYear,
                       &$lHour,  &$lMinute, &$lSecond,
                       &$bError, $bDebug=false) {
//---------------------------------------------------------------------
// this is basically a strtotime that overcomes the windows (and to
// a lesser extent Linux/Unix) timestamp limitations.
//
// If $bCorrect2000 is true and the year is 2-digit, add 2000 if the
// year is <25; else add 1900.
//
// Supported formats:
//   m[m]/d[d]/yy[yy]
//   m[m]-d[d]-yy[yy]
//   m[m]/d[d]/yy[yy] h[h]:m[m]:s[s] [[a|A|p|P][m|M]]
//   m[m]-d[d]-yy[yy] h[h]:m[m]:s[s] [[a|A|p|P][m|M]]
//---------------------------------------------------------------------
   $strDateTime = trim($strDateTime);

if ($bDebug) echo($strDateTime.' ');

   $lState = 1;
   $bError = false;
   $strHold1    = '';
   $strHold2    = '';
   $strHold3    = '';
   $strHold10   = '';
   $strHold11   = '';
   $strHold12   = '';
   $strHoldAMPM = '';

   for ($idx=0; $idx<strlen($strDateTime); ++$idx) {
      if ($bError) return;

      $chrHold = substr($strDateTime,$idx,1);

if ($bDebug) echo("\$lState=$lState, \$chrHold=$chrHold, \$bError=".($bError?'Yes':'No')." <br>\n");

      switch ($lState) {
         case 1:
            if (($chrHold>='0')&&($chrHold<='9')){
               $strHold1 .= $chrHold;
            }elseif (($chrHold=='/') || ($chrHold=='-')) {
               $lState = 2;
            }elseif ($chrHold==':'){
               $strHold10 = $strHold1;
               $strHold1 = '';
               $lState = 10;
            }else {
               $bError = true;
            }
            break;

         case 2:
            if (($chrHold>='0')&&($chrHold<='9')){
               $strHold2 .= $chrHold;
               $lState = 3;
            }else {
               $bError = true;
            }
            break;

         case 3:
            if (($chrHold>='0')&&($chrHold<='9')){
               $strHold2 .= $chrHold;
            }elseif (($chrHold=='/') || ($chrHold=='-') ){
               $lState = 4;
            }else {
               $bError = true;
            }
            break;

         case 4:
            if (($chrHold>='0')&&($chrHold<='9')){
               $strHold3 .= $chrHold;
               $lState = 5;
            }else {
               $bError = true;
            }
            break;

         case 5:
            if (($chrHold>='0')&&($chrHold<='9')){
               $strHold3 .= $chrHold;
            }elseif ($chrHold==' '){
               $lState = 6;
            }else {
               $bError = true;
            }
            break;

         case 6:
            if ($chrHold!=' ') {
               if (($chrHold>='0')&&($chrHold<='9')){
                  $strHold10 .= $chrHold;
                  $lState = 7;
               }else {
                  $bError = true;
               }
            }
            break;

         case 7:
            if (($chrHold>='0')&&($chrHold<='9')){
               $strHold10 .= $chrHold;
            }elseif ($chrHold==':'){
               $lState = 10;
            }else {
               $bError = true;
            }
            break;

         case 10:
            if (($chrHold>='0')&&($chrHold<='9')){
               $strHold11 .= $chrHold;
               $lState = 11;
            }else {
               $bError = true;
            }
            break;

         case 11:
            $chrUpper = strtoupper($chrHold);
            if (($chrHold>='0')&&($chrHold<='9')){
               $strHold11 .= $chrHold;
            }elseif ($chrHold==':') {
               $lState = 12;
            }elseif ($chrHold==' ') {
               $lState = 15;
            }elseif (($chrUpper=='A')||($chrUpper=='P')){
               $strHoldAMPM .= $chrUpper;
               $lState = 20;
            }else {
               $bError = true;
            }
            break;

         case 12:
            if (($chrHold>='0')&&($chrHold<='9')){
               $strHold12 .= $chrHold;
               $lState = 13;
            }else {
               $bError = true;
            }
            break;

         case 13:
            $chrUpper = strtoupper($chrHold);
            if (($chrHold>='0')&&($chrHold<='9')){
               $strHold12 .= $chrHold;
            }elseif ($chrHold==' ') {
               $lState = 15;
            }elseif (($chrUpper=='A')||($chrUpper=='P')){
               $strHoldAMPM .= $chrUpper;
               $lState = 20;
            }else {
               $bError = true;
            }
            break;

         case 15:
            $chrUpper = strtoupper($chrHold);
            if (($chrUpper=='A')||($chrUpper=='P')){
               $strHoldAMPM .= $chrUpper;
               $lState = 20;
            }elseif ($chrHold!=' ') {
               $bError = true;
            }
            break;

         case 20:
            $chrUpper = strtoupper($chrHold);
            if ($chrUpper=='M'){
               $strHoldAMPM .= $chrUpper;
               $lState = 21;
            }else {
               $bError = true;
            }
            break;

         case 21:
            $bError = true;
            break;

         default:
if ($bDebug) {
echo("\$strHold1=$strHold1 <br>\n");
echo("\$strHold2=$strHold2 <br>\n");
echo("\$strHold3=$strHold3 <br>\n");
echo("\$strHold10=$strHold10 <br>\n");
echo("\$strHold11=$strHold11 <br>\n");
echo("\$strHold12=$strHold12 <br>\n");
echo("\$strHoldAMPM=$strHoldAMPM <br><br>\n");
}
            screamForHelp('INVALID PARSING STATE '.$lState.': error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__);
            break;
      }
   }

if ($bDebug) {
echo("\$strHold1=$strHold1 <br>\n");
echo("\$strHold2=$strHold2 <br>\n");
echo("\$strHold3=$strHold3 <br>\n");
echo("\$strHold10=$strHold10 <br>\n");
echo("\$strHold11=$strHold11 <br>\n");
echo("\$strHold12=$strHold12 <br>\n");
echo("\$strHoldAMPM=$strHoldAMPM <br>\n");
echo("\$bError=".($bError?'Yes':'No')."<br><br>\n");
}

   $lMonth = (integer)$strHold1;
   $lDay   = (integer)$strHold2;
   $lYear  = (integer)$strHold3;
   $lHour  = (integer)$strHold10;
   $lMinute = (integer)$strHold11;
   $lSecond = (integer)$strHold12;
   $bPM     = $strHoldAMPM=='PM';

      // optionally correct for 2-digit year;
   if ($lYear<=99) {
      if ($bCorrect2000) {
         if ($lYear<25) {
            $lYear += 2000;
         }else {
            $lYear += 1900;
         }
      }
   }

if ($bDebug) echo("\$lMonth=$lMonth, \$lDay=$lDay, \$lYear=$lYear, \$lHour=$lHour, \$lMinute=$lMinute, \$lSecond=$lSecond <br><br>\n");

      //------------------------
      // basic sanity checks
      //------------------------
   if (($lMonth>12)||($lMonth<0)) {
      $bError = true;
if ($bDebug) screamForHelp('<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
   }elseif (($lDay<0) || ($lDay>31)){
      $bError = true;
if ($bDebug) screamForHelp('<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
   }elseif (($lHour>23)||($lHour<0)) {
      $bError = true;
if ($bDebug) screamForHelp('<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
   }elseif (($lMinute>59) || ($lMinute<0)) {
      $bError = true;
if ($bDebug) screamForHelp('<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
   }elseif (($lSecond>59) || ($lSecond<0)) {
      $bError = true;
if ($bDebug) screamForHelp('<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
   }
   if ($bError) return;

   if (($lMonth==0) && (($lDay>0)||($lYear!=0))){
      $bError = true;
if ($bDebug) screamForHelp('<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
   }

   if (($lDay==0) && (($lMonth>0)||($lYear!=0))){
      $bError = true;
if ($bDebug) screamForHelp('<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
   }


      //---------------------------------------------
      // the day of the month test
      // 30 days have sept, april, june, and nov
      //                9    4      6         11
      //---------------------------------------------
   if (($lMonth==9)||($lMonth==4)||($lMonth==6)||($lMonth==11)){
      if ($lDay==31) {
         $bError = true;
if ($bDebug) screamForHelp('<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         return;
      }
   }

      //---------------------------------------------
      // that pesky February test
      //---------------------------------------------
   if ($lMonth==2){
      if ($lDay>29) {
         $bError = true;
if ($bDebug) screamForHelp('<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         return;
      }
      if ($lDay==29) {
         if (($lYear % 4)!=0){
            $bError = true;
if ($bDebug) screamForHelp('<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            return;
         }
      }
   }

      //-------------------------
      // weird leap year case
      //-------------------------
   if (($lMonth==2) && ($lDay== 29) && (($lYear%100)==0) && (($lYear%400)!= 0)) {
      $bError = true;
if ($bDebug) screamForHelp('<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      return;
   }


      //---------------------------------------------
      // don't allow PM designation on hours greater
      // than 12
      //---------------------------------------------
   if ($bPM && ($lHour>12)){
      $bError = true;
if ($bDebug) screamForHelp('<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      return;
   }

      //---------------------------------------------
      // if PM specified, convert post-noon times
      // to 24 hour format
      //---------------------------------------------
   if ($bPM && ($lHour!=12)) {
      $lHour += 12;
   }

      //---------------------------------------------
      // if AM explicity specified and the hour is 12,
      // convert to zero-hour.
      //---------------------------------------------
   if (($strHoldAMPM=='AM') && ($lHour==12)) {
      $lHour = 0;
   }

if ($bDebug) echo("\$lMonth=$lMonth, \$lDay=$lDay, \$lYear=$lYear, \$lHour=$lHour, \$lMinute=$lMinute, \$lSecond=$lSecond <br><br>\n");

}


function dba_strMySQL_DateTime($bQuotes,
                   $lMonth, $lDay,    $lYear,
                   $lHour,  $lMinute, $lSecond) {
//---------------------------------------------------------------------
// return a string formatted as a mySQL date/time field
// classless implementation
//---------------------------------------------------------------------
   return(
       ($bQuotes?"'":'')
      .str_pad($lYear.'',   4, '0', STR_PAD_LEFT).'-'
      .str_pad($lMonth.'',  2, '0', STR_PAD_LEFT).'-'
      .str_pad($lDay.'',    2, '0', STR_PAD_LEFT).' '

      .str_pad($lHour.'',   2, '0', STR_PAD_LEFT).':'
      .str_pad($lMinute.'', 2, '0', STR_PAD_LEFT).':'
      .str_pad($lSecond.'', 2, '0', STR_PAD_LEFT)
      .($bQuotes?"'":'') );
}

function dba_strMySQL_Date($bQuotes,
                   $lMonth, $lDay, $lYear) {
//---------------------------------------------------------------------
// return a string formatted as a mySQL date/time field
// classless implementation
//---------------------------------------------------------------------
   return(
       ($bQuotes?"'":'')
      .str_pad($lYear.'',   4, '0', STR_PAD_LEFT).'-'
      .str_pad($lMonth.'',  2, '0', STR_PAD_LEFT).'-'
      .str_pad($lDay.'',    2, '0', STR_PAD_LEFT)
      .($bQuotes?"'":'') );
}

function dba_strTextDate2MySQL($strTextDate, $bQuotes, &$bError){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   dba_parseDateTime($strTextDate, true,
                     $lMonth, $lDay,    $lYear,
                     $lHour,  $lMinute, $lSecond,
                     $bError, false);

   if (!$bError) {
      return(
             ($bQuotes?"'":'')
            .str_pad($lYear.'',   4, '0', STR_PAD_LEFT).'-'
            .str_pad($lMonth.'',  2, '0', STR_PAD_LEFT).'-'
            .str_pad($lDay.'',    2, '0', STR_PAD_LEFT).' '

            .str_pad($lHour.'',   2, '0', STR_PAD_LEFT).':'
            .str_pad($lMinute.'', 2, '0', STR_PAD_LEFT).':'
            .str_pad($lSecond.'', 2, '0', STR_PAD_LEFT)
            .($bQuotes?"'":'') );
   }else {
      return(null);
   }
}

function dba_strXlateShortMonth($lMonth) {
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   switch ($lMonth) {
      case  1: return('Jan'); break;
      case  2: return('Feb'); break;
      case  3: return('Mar'); break;
      case  4: return('Apr'); break;
      case  5: return('May'); break;
      case  6: return('Jun'); break;
      case  7: return('Jul'); break;
      case  8: return('Aug'); break;
      case  9: return('Sep'); break;
      case 10: return('Oct'); break;
      case 11: return('Nov'); break;
      case 12: return('Dec'); break;

      default:
         return('#error#');
         break;
   }
}

function dba_strXlateLongMonth($lMonth) {
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   switch ($lMonth) {
      case  1: return('January');   break;
      case  2: return('February');  break;
      case  3: return('March');     break;
      case  4: return('April');     break;
      case  5: return('May');       break;
      case  6: return('June');      break;
      case  7: return('July');      break;
      case  8: return('August');    break;
      case  9: return('September'); break;
      case 10: return('October');   break;
      case 11: return('November');  break;
      case 12: return('December');  break;

      default:
         return('#error#');
         break;
   }
}

function normalizeDateTime(
               &$lHour,  &$lMinute, &$lSecond,
               &$lMonth, &$lDay,    &$lYear){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
      //-----------------------------------------------
      // start with the seconds, then work our way up
      //-----------------------------------------------
   if ($lSecond >= 60) {
      $lMinute += (integer)($lSecond/60);
      $lSecond = $lSecond % 60;
   }elseif ($lSecond < 0) {
      $lMinute -= ((integer)(-$lSecond/60)+1);
      if (($lSecond % 60)==0){
         ++$lMinute;
         $lSecond = 0;
      }else {
         $lSecond = 60 - (-$lSecond % 60);
      }
   }

      //-----------------------------------------------
      // adjust minutes
      //-----------------------------------------------
   if ($lMinute >= 60) {
      $lHour += (integer)($lMinute/60);
      $lMinute = $lMinute % 60;
   }elseif ($lMinute < 0) {
      $lHour -= ((integer)(-$lMinute/60)+1);
      if (($lMinute % 60)==0){
         ++$Hour;
         $lMinute = 0;
      }else {
         $lMinute = 60 - (-$lMinute % 60);
      }
   }

      //-----------------------------------------------
      // adjust hours
      //-----------------------------------------------
   if ($lHour >= 24) {
      $lDay += (integer)($lHour/24);
      $lHour = $lHour % 24;
   }elseif ($lHour < 0) {
      $lDay -= ((integer)(-$lHour/24)+1);
      if (($lHour % 24)==0){
         ++$lDay;
         $lHour = 0;
      }else {
         $lHour = 24 - (-$lHour % 24);
      }
   }
   
   normalizeDate($lMonth, $lDay, $lYear);
}

function normalizeDate(&$lMonth, &$lDay, &$lYear){
      //-----------------------------------------------
      // normalize month, then adjust for days
      //-----------------------------------------------
   if ($lMonth > 12) {
      $lYear += (integer)($lMonth/12);
      $lMonth = $lMonth % 12;
   }elseif ($lMonth < 0){
      $lYear -= (integer)((-$lMonth/12)+1);
      if ((-$lMonth%12)==0){
         $lMonth = 1;
         ++$lYear;
      }else {
         $lMonth = 13 - (-$lMonth%12);
      }
   }
   if ($lMonth == 0) {
      $lMonth = 12;
      $lYear -= 1;
   }

      //-----------------------------------------------
      // finally, the days....
      //-----------------------------------------------
   while ( ($lDay <= 0) || ($lDay > $lDaysInMonth=cal_days_in_month(CAL_GREGORIAN, $lMonth, $lYear))){

      if ($lDay<=0) {
         --$lMonth;
         if ($lMonth==0) {
            $lMonth = 12; --$lYear;
         }
         $lDay += cal_days_in_month(CAL_GREGORIAN, $lMonth, $lYear);
      }else {
         $lDay -= $lDaysInMonth;
         ++$lMonth;
         if ($lMonth > 12) {
            ++$lYear; $lMonth = 1;
         }
      }
   }
}


?>