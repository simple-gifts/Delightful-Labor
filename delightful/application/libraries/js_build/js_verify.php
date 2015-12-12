<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//---------------------------------------------------------------------
// copyright (c) 2011
// Austin, Texas 78759
//
// 
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
/*---------------------------------------------------------------------
   $this->load->library('js_build/js_verify');

   $this->js_verify = new js_verify;
   $this->js_verify->clearEmbedOpts();
   $this->js_verify->bShow_bVerifyString =
   $this->js_verify->bShow_trim          = true;
   $displayData['js'] = $this->js_verify->loadJavaVerify();

   ...
        <form method="GET"
           name="frmMyLittleForm"
           action="../myForms/myFormUpdate.php"
           onSubmit="return verifyMyLittleForm(frmMyLittleForm);" >

---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class js_verify {

   public
      $bShow_bVerifyDDL_Selected,
      $bShow_bVerifyEmail,
      $bShow_bVerifyRadio,
      $bShow_strRadioSelValue,

      $bShow_bVerifyNumber,
      $bShow_bVerifyFloat,
      $bShow_bBasicNumTest,

      $bShow_bRangeTestNumber,
      $bShow_bVerifyString,
      $bShow_trim,

      $bShow_isValidDate,
      $bShow_isValidMDY,
      $bShow_dateToMDY,
      $bShow_dateCartBeforeHorse;


   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
      $this->clearEmbedOpts();
   }


   function clearEmbedOpts() {
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->bShow_bVerifyDDL_Selected =
      $this->bShow_bVerifyRadio        =
      $this->bShow_bVerifyEmail        =
      $this->bShow_strRadioSelValue    =

      $this->bShow_bVerifyNumber       =
      $this->bShow_bVerifyFloat        =
      $this->bShow_bBasicNumTest       =

      $this->bShow_bRangeTestNumber    =
      $this->bShow_bVerifyString       =
      $this->bShow_trim                =

      $this->bShow_isValidDate         =
      $this->bShow_isValidMDY          =
      $this->bShow_dateToMDY           =
      $this->bShow_dateCartBeforeHorse =
                                           false;
   }
   function loadJavaVerify(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $strJSEmbed = "\n".'<script language="JavaScript"><!--'."\n";


         //----------------------------------------------------------------------
         // return true if email is valid
         // thanks to
         //  http://www.white-hat-web-design.co.uk/blog/javascript-validation/
         //----------------------------------------------------------------------
      if ($this->bShow_bVerifyEmail){
         $strJSEmbed .= '
            function bValidEmail(strEmail) {
               var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
               // var address = document.forms[form_id].elements[email].value;
               return(reg.test(strEmail));
            }';
      }

         //---------------------------------------------------
         // return true if an entry in ddl list is selected
         //---------------------------------------------------
      if ($this->bShow_bVerifyDDL_Selected) {
         $strJSEmbed .= '
            function bVerifySelected(ddlObj) {
               return(ddlObj.selectedIndex>=0);
            }';
      }

         //---------------------------------------------------
         // return true if a radio item is selected
         //---------------------------------------------------
      if ($this->bShow_bVerifyRadio) {
         $strJSEmbed .= '
             function bVerifyRadio(rdoObj) {
                  var idx;
                  if (isNaN(rdoObj.length)) {
                     return(rdoObj.checked);
                  }else {
                     for (idx=0; idx<rdoObj.length; ++idx) {
                        if (rdoObj[idx].checked) {
                           return(true);
                        }
                     }
                     return(false);
                  }
               }';
      }

         //---------------------------------------------------
         // return the value of the selected radio button
         //---------------------------------------------------
      if ($this->bShow_strRadioSelValue) {
         $strJSEmbed .= '
               function strRadioSelValue(rdoObj) {
                  var idx;

                  if (isNaN(rdoObj.length)){
                     if (rdoObj.checked){
                        return(rdoObj.value);
                     }else {
                        return(false);
                     }
                 }else {
                     for (idx=0; idx<rdoObj.length; ++idx) {
                        if (rdoObj[idx].checked) {
                           return(rdoObj[idx].value);
                        }
                     }
                     return(false);
                  }
               }';
      }

         //---------------------------------------------------
         // return true if test string is non-blank and a
         // valid number. Optionally perform a range test.
         //---------------------------------------------------
      if ($this->bShow_bVerifyNumber) {
         $strJSEmbed .= '
               function bVerifyNumber(strTestNum, bUseRangeTest, bRangeMin, lMinVal, bRangeMax, lMaxVal) {
                  var strHold;

                  strHold = trim(strTestNum);

                  if (!bBasicNumTest(strHold)) {
                     return(false);
                  }else {
                     if (bUseRangeTest){
                        return(bRangeTestNumber(strTestNum, bRangeMin, lMinVal, bRangeMax, lMaxVal, false));
                     }else {
                        return(true);
                     }
                  }
               }';
         }

         //---------------------------------------------------
         // return true if test string is non-blank and a
         // valid number. Optionally perform a range test.
         //---------------------------------------------------
      if ($this->bShow_bVerifyFloat) {
         $strJSEmbed .= '
               function bVerifyFloat(strTestNum, bUseRangeTest, bRangeMin, sngMinVal, bRangeMax, sngMaxVal) {
                  var strHold;

                  strHold = trim(strTestNum);

                  if (!bBasicNumTest(strHold)) {
                     return(false);
                  }else {
                     if (bUseRangeTest){
                        return(bRangeTestNumber(strTestNum, bRangeMin, sngMinVal, bRangeMax, sngMaxVal, true));
                     }else {
                        return(true);
                     }
                  }
               }';
      }

         //---------------------------------------------------
         // return false if empty or isNaN
         //---------------------------------------------------
      if ($this->bShow_bBasicNumTest) {
         $strJSEmbed .= '
               function bBasicNumTest(strTestNum) {
                  if (strTestNum.length==0) {
                     return(false);
                  }else {
                     if (isNaN(strTestNum)) {
                        return(false);
                     }else {
                        return(true);
                     }
                  }
               }';
      }

         //---------------------------------------------------
         // Perform a numeric range test.
         //---------------------------------------------------
      if ($this->bShow_bRangeTestNumber) {
         $strJSEmbed .= '
               function bRangeTestNumber(strTestNum, bRangeMin, lMinVal, bRangeMax, lMaxVal, bFloat) {
                  var lNumHold;

                  if (bFloat) {
                     lNumHold = parseFloat(strTestNum);
                  }else {
                     lNumHold = parseInt(strTestNum);
                  }
               //alert(\'Your number=\'+lNumHold);

                  if (bRangeMin && (lNumHold<lMinVal)) {
                      return(false);
                  }else if (bRangeMax && (lNumHold>lMaxVal)){
                      return(false);
                  }
                     // we\'ve passed the gauntlet
                  return(true);
               }';
      }

         //---------------------------------------------------
         // return true if trimmed string is non-empty
         //---------------------------------------------------
      if ($this->bShow_bVerifyString) {
         $strJSEmbed .= '
               function bVerifyString(strTest){
                  var strHold;
                  strHold = trim(strTest);
                  return(strHold.length>0);
               }';
      }

         //---------------------------------------------------
         // from http://developer.irt.org/script/1310.htm
         // trim a string
         //---------------------------------------------------
      if ($this->bShow_trim) {
         $strJSEmbed .= '
               function trim(strText) {
                      // leading spaces
                   while (strText.substring(0,1) == \' \')
                       strText = strText.substring(1, strText.length);

                      // trailing spaces
                   while (strText.substring(strText.length-1,strText.length) == \' \')
                       strText = strText.substring(0, strText.length-1);
                  return strText;
               }';
      }

         //-------------------------------------------------------------------------------------
         // from http://www.experts-exchange.com/Web/Web_Languages/JavaScript/Q_20432725.html
         // (originally in european format; changed to accept either US or Indian/European)
         // verify a date
         //-------------------------------------------------------------------------------------
      if ($this->bShow_isValidDate) {
         $strJSEmbed .= '
               function isValidDate(dateStr, bIndiaStyle) {

                     // for those using mm-dd-yy
                  var strRegExp = /-/g;
                  dateStr = dateStr.replace(strRegExp, "/");

                  var dateArr = dateStr.split(\'/\');
                  var dteToday = new Date();
                  if ((dateArr[0]==undefined) || (dateArr[1]==undefined) || (dateArr[2]==undefined)) {
                     return false;
                  }

                  if (dateArr.length == 1){
                     if (dateArr[0].length==4){
                        strHold = dateArr[0];
                        dateArr[0] = strHold.substr(0,2);
                        dateArr[1] = strHold.substr(2,2);
                        dateArr[2] = dteToday.getFullYear();
                     }else {
                        return(false);
                     }
                  }

                  if (dateArr[2].length == 2){
                     dateArr[2]=\'20\'+dateArr[2];
                  }

                  if (bIndiaStyle){
                     var holdMonth = dateArr[0];
                     dateArr[0]    = dateArr[1];
                     dateArr[1]    = holdMonth;
                  }

                  if ((dateArr.length != 3) || isNaN(dateArr[0]) || isNaN(dateArr[1]) || isNaN(dateArr[2])) {
                     return false;
                  }
                  else if ( dateArr[0].length > 2 || dateArr[1].length > 2 || dateArr[2].length > 4 ) {
                       return false;
                  }
                  else if ( (dateArr[1] < 1 || dateArr[1] > 31) || (dateArr[0] < 1 || dateArr[0] > 12) || (dateArr[2] < 1900 || dateArr[2] > 2500)) {
                       return false;
                  }
                  else    // feb test
                     if ( Number(dateArr[0]) == 2 && Number(dateArr[1]) > 29) {
                       return false;
                  }
                  else   // leap day
                     if ( Number(dateArr[0]) == 2 && Number(dateArr[1]) == 29 && Number(dateArr[2]) % 4 != 0 ) {
                       return false;
                  }
                  else  // 30 days have sept,...
                     if ( Number(dateArr[1]) == 31 && ( Number(dateArr[0]) == 4 || Number(dateArr[0]) == 6 || Number(dateArr[0]) ==  9 || Number(dateArr[0]) == 11 )) {
                       return false;
                  }
                  else // weird leap year case
                     if ( Number(dateArr[0]) == 2 && Number(dateArr[1]) == 29 && Number(dateArr[2]) % 4 == 0 && dateArr[2].substring(2) == \'00\' && Number(dateArr[2]) % 400 != 0) {
                       return false;
                  }
                  else return true;
               }';
      }

         //---------------------------------------------------
         // return true if the specified m/d/y is valid
         //---------------------------------------------------
      if ($this->bShow_isValidMDY) {
         $strJSEmbed .= '
               function isValidMDY(lMonth, lDay, lYear) {

                  if ( isNaN(lMonth) || isNaN(lDay) || isNaN(lYear)) {
                     return false;
                  }
                  else if ( (lDay < 1 || lDay > 31) || (lMonth < 1 || lMonth > 12) || (lYear < 1000 || lYear > 6500)) {
                       return false;
                  }
                  else    // feb test
                     if ( Number(lMonth) == 2 && Number(lDay) > 29) {
                       return false;
                  }
                  else   // leap day
                     if ( Number(lMonth) == 2 && Number(lDay) == 29 && Number(lYear) % 4 != 0 ) {
                       return false;
                  }
                  else  // 30 days have sept,...
                     if ( Number(lDay) == 31 && ( Number(lMonth) == 4 || Number(lMonth) == 6 || Number(lMonth) ==  9 || Number(lMonth) == 11 )) {
                       return false;
                  }
                  else // weird leap year case
                     if ( Number(lMonth) == 2 && Number(lDay) == 29 && Number(lYear) % 4 == 0 && lYear.substring(2) == \'00\' && Number(lYear) % 400 != 0) {
                       return false;
                  }
                  else return true;
               }';
      }

         //---------------------------------------------------
         // return true if test date is before "today" date
         //---------------------------------------------------
      if ($this->bShow_dateCartBeforeHorse) {
         $strJSEmbed .= '
            function bCartBeforeHorse(
                         lTestMonth,  lTestDay,  lTestYear,
                         lTodayMonth, lTodayDay, lTodayYear){
               var bCartBeforeHorse = false;

               if (lTestYear < lTodayYear) {
                  bCartBeforeHorse = true;
               }else if (lTestYear==lTodayYear){
                  if (lTestMonth < lTodayMonth) {
                     bCartBeforeHorse = true;
                  }else if ((lTestMonth==lTodayMonth) && (lTestDay<lTodayDay)) {
                     bCartBeforeHorse = true;
                  }
               }
               return(bCartBeforeHorse);
            }';
      }

         //---------------------------------------------------
         // return array
         //    dateArr[0] => month
         //    dateArr[1] => day
         //    dateArr[2] => year
         // assumes the date string has been validated
         //---------------------------------------------------
      if ($this->bShow_dateToMDY) {
         $strJSEmbed .= '
            function dateToMDY(dateStr, bIndiaStyle) {

                  // for those using mm-dd-yy
               var strRegExp = /-/g;
               dateStr = dateStr.replace(strRegExp, "/");

               var dateArr = dateStr.split(\'/\');
               var dteToday = new Date();

               if (dateArr.length == 1){
                  if (dateArr[0].length==4){
                     strHold = dateArr[0];
                     dateArr[0] = strHold.substr(0,2);
                     dateArr[1] = strHold.substr(2,2);
                     dateArr[2] = dteToday.getFullYear();
                  }else {
                     return(false);
                  }
               }

               if (dateArr[2].length == 2){
                  dateArr[2]=\'20\'+dateArr[2];
               }

               if (bIndiaStyle){
                  var holdMonth = dateArr[0];
                  dateArr[0]    = dateArr[1];
                  dateArr[1]    = holdMonth;
               }
               dateArr[0] = dateArr[0] * 1;
               dateArr[1] = dateArr[1] * 1;
               dateArr[2] = dateArr[2] * 1;
               return(dateArr);
            }';

      }
      $strJSEmbed .= ' //--></script>';
      return($strJSEmbed);

   }
}

?>