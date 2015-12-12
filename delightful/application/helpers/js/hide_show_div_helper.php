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
// hide/set div
--------------------------------------------------------------
   Sample Usage
   ============
   $this->load->helper('js/hide_show_div');
   $displayData['js'] .= insertHideSetDiv();

      //-----------------
      // using links
      //-----------------

      $strRadio =
         '<input type="radio" name="rdoAcctType" value="admin" '.($userRec->bAdmin        ? 'checked' : '').'
                  onClick="hideShowDiv(\'volPerms\', true);">Administrator&nbsp;&nbsp;');

--------------------------------------------------------------*/


function insertHideSetDiv(){
//--------------------------------------------------------------
//
//--------------------------------------------------------------
   return('
         <script language="JavaScript" type="text/JavaScript">
         <!---
         function hideShowDiv(divID, bHide){
            if (bHide){
               document.getElementById(divID).style.display = "none";
            }else {
               document.getElementById(divID).style.display = "";
            }
         }
         // end ->
         </script>');
}
