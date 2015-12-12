<?php
//--------------------------------------------------------------
// The Empowered Non-Profit
//
// copyright (c) 2013 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//--------------------------------------------------------------
// return true if at least one check box is set, else alert
// user
/*--------------------------------------------------------------
   $this->load->helper('js/verify_check_set');
   $displayData['js'] .= verifyCheckSet();
   
   Sample calling sequence:
      return="verifyBoxChecked(\'frmComInventory\', \'chkInv[]\', \' (Inventory Contents)\'))" 
      
   From a form:
      (submit button)
       onSubmit=" return(verifyBoxChecked(\'frmCloneVolEvent\', \'chkDate[]\', \' (Event Dates)\'))" 
   
      
--------------------------------------------------------------*/
   function verifyCheckSet(){
      return('
         <script language="JavaScript" type="text/JavaScript">
         <!---
         function verifyBoxChecked(objForm, strCheckArray, strExtraMessage) {
            var objElements = document.forms[objForm].elements[strCheckArray];
            var lElCnt = objElements.length;
            if (isNaN(lElCnt)){
               if (!objElements.checked) {
                  alert(\'Please check one or more boxes\'+strExtraMessage);
                  return(false);
               }else {
                  return(true);
               }
            }else {
               for (var idx=0; idx<lElCnt; ++idx) {
                  if (objElements[idx].checked){
                     return(true);
                  }
               }
               alert(\'Please check one or more boxes\'+strExtraMessage);
               return(false);
            }
         }
         // end --->
         </script>');
   }         
   
   
   