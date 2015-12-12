<?php
/*--------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2009-2013 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//--------------------------------------------------------------
// set/unset checkboxes
//--------------------------------------------------------------
// if the chkPeople array only has one element, javascript
// treats it like a scalar; that is why the array count is
// tested for NaN
--------------------------------------------------------------
   Sample Usage
   ============
   $this->load->helper('js/set_check_boxes');
   $displayData['js'] .= insertCheckSet();

      //-----------------
      // using links
      //-----------------
   echoT(
        '<a href=""
              onclick="setCheckBoxes(\'myLittleForm\', \'myCheckBoxArray[]\', true); return false;"
              style="font-size: 8pt;">(check all)</a>&nbsp;&nbsp;
         <a href=""
              onclick="setCheckBoxes(\'myLittleForm\', \'myCheckBoxArray[]\', false); return false;"
              style="font-size: 8pt;">(clear all)</a>&nbsp;&nbsp;
         <br>');

      //-----------------
      // using buttons
      //-----------------
   echo(
        '<input type="button"
              onclick="setCheckBoxes(\'myLittleForm\', \'myCheckBoxArray[]\', true); return false;"
              class="btn"
                  onmouseover="this.className=\'btn btnhov\'"
                  onmouseout="this.className=\'btn\'"
              value="Set All"
           >&nbsp;&nbsp;
         <input type="button"
              onclick="setCheckBoxes(\'myLittleForm\', \'myCheckBoxArray[]\', false); return false;"
              class="btn"
                 onmouseover="this.className=\'btn btnhov\'"
                 onmouseout="this.className=\'btn\'"
              value="Clear All"
           >');

--------------------------------------------------------------*/

function strHTMLSetClearAllButton($bSet, $strForm, $strCheckArrayName, $strButtonLabel){
//--------------------------------------------------------------
/* sample call:
   echoT(
          strHTMLSetClearAllButton(true,  'myLittleForm', 'myCheckBoxArray[]', 'Set All').'&nbsp;&nbsp;'
         .strHTMLSetClearAllButton(false, 'myLittleForm', 'myCheckBoxArray[]', 'Clear All')
      );

*/
//--------------------------------------------------------------
   return(
        '<input type="button"
              onclick="setCheckBoxes(\''.$strForm.'\', \''.$strCheckArrayName.'\', '.($bSet ? 'true' : 'false').'); return false;"
              class="btn"
                  onmouseover="this.className=\'btn btnhov\'"
                  onmouseout="this.className=\'btn\'"
              value="'.$strButtonLabel.'">' );
}

function insertCheckSet(){
//--------------------------------------------------------------
//
//--------------------------------------------------------------
   return('
         <script language="JavaScript" type="text/JavaScript">
         <!---
         function setCheckBoxes(strForm, strCheckArray, bSetCheck) {
            var objElements = document.forms[strForm].elements[strCheckArray];
            var lElCnt = objElements.length;
            if (isNaN(lElCnt)) {
               objElements.checked = bSetCheck;
            }else {
               for (var idx=0; idx<lElCnt; ++idx) {
                  objElements[idx].checked = bSetCheck;
               }
            }
            return(true);
         }


         function checkUncheckSingleBox(strForm, strCheckArray, bSetCheck, idx) {
            var objElements = document.forms[strForm].elements[strCheckArray];
            var lElCnt = objElements.length;
            if (isNaN(lElCnt)) {
               objElements.checked = bSetCheck;
            }else {
               objElements[idx].checked = bSetCheck;
            }
            return(true);
         }

         // end --->
         </script>');

}
?>