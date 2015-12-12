<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*---------------------------------------------------------------------
// copyright (c) 2012 by Database Austin
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
/*---------------------------------------------------------------------
      $this->load->library('js_build/java_joe_radio');
---------------------------------------------------------------------*/

   //----------------------------------------------------------
   // Java Joe says....
   // Select a radio button in response to some other event
   //----------------------------------------------------------
   // example calling sequence....
   //    $this->load->library('js_build/java_joe_radio');
   //    insertJavaJoeRadio();
   //    ...
   //    <select size="1" name="ddl_TimeFrame" 
   //               onChange=setRadioOther(ReportSetupWithDate.rdo_TimePeriod,"DDL")>
   //
   // example with a span area
   //       <span onClick=setRadioOther(frmIceCream.rdoFavorite,'VAN')>Vanilla</span>
   //
   // note - encountered a javascript syntax error in the Microsoft POS 
   //        internet explorer when there was a space between
   //        the two arguments!
   //    live:  setRadioOther(frmConfig.rdoService,'S')
   //    die:   setRadioOther(frmConfig.rdoService, 'S')
   //----------------------------------------------------------
class java_joe_radio {

   function insertJavaJoeRadio() {
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return('
            <SCRIPT LANGUAGE="JavaScript"><!--
            function setRadioOther(sRDOName, sNewVal) {
               for (idx=0; idx<sRDOName.length; idx++) {
                  if (sRDOName[idx].value == sNewVal) {
                     sRDOName[idx].checked = true
                  }else {
                     sRDOName[idx].checked = false
                  }
               }
            }
            //-->
            </script>');

   }
   
   function insertSetDateRadio() {
   //----------------------------------------------------------
   // Java Joe says....
   // Select a radio button in response to some other event
   //----------------------------------------------------------
   // example calling sequence....
   //    <select size="1" name="ddl_TimeFrame" onChange=setDateRadio(ReportSetupWithDate.rdo_TimePeriod,"DDL")>
   //----------------------------------------------------------
      return('
         <SCRIPT LANGUAGE="JavaScript"><!--
         function setDateRadio(sRDOName, sNewVal) {
            for (idx=0; idx<sRDOName.length; idx++) {
               if (sRDOName[idx].value == sNewVal) {
                  sRDOName[idx].checked = true
               }else {
                  sRDOName[idx].checked = false
               }
            }
         }
         //-->
         </script>
         ');
   }
   

   function insertGetCheckedRadioValue(){
   //---------------------------------------------------------------------
   // Thanks to http://www.somacon.com/p143.php
   //
   // return the value of the radio button that is checked
   // return an empty string if none are checked, or
   // there are no radio buttons
   //---------------------------------------------------------------------
      return('
            <SCRIPT LANGUAGE="JavaScript"><!--
            function strGetCheckedRadioValue(radioObj) {
               if(!radioObj)
                  return "";
               var radioLength = radioObj.length;
               if(radioLength == undefined)
                  if(radioObj.checked)
                     return radioObj.value;
                  else
                     return "";
               for(var i = 0; i < radioLength; i++) {
                  if(radioObj[i].checked) {
                     return radioObj[i].value;
                  }
               }
               return "";
            }
            //-->
            </script>');
   }

   function insertSetCheckedRadioValue(){
   //---------------------------------------------------------------------
   // Thanks to http://www.somacon.com/p143.php
   //
   // set the radio button with the given value as being checked
   // do nothing if there are no radio buttons
   // if the given value does not exist, all the radio buttons
   // are reset to unchecked
   //---------------------------------------------------------------------
      return('
            <SCRIPT LANGUAGE="JavaScript"><!--
            function setCheckedRadioValue(radioObj, newValue) {
               if(!radioObj)
                  return;
               var radioLength = radioObj.length;
               if(radioLength == undefined) {
                  radioObj.checked = (radioObj.value == newValue.toString());
                  return;
               }
               for(var i = 0; i < radioLength; i++) {
                  radioObj[i].checked = false;
                  if(radioObj[i].value == newValue.toString()) {
                     radioObj[i].checked = true;
                  }
               }
            }
            //-->
            </script>');
   }
}
?>