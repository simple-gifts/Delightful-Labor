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


   function clearCheckOnUnCheck(){
   /* ------------------------------------------------------------------------------------
      clear checkbox 2 if checkbox 1 is unchecked
      
      sample calling sequence
        $this->load->helper('js/clear_set_check_on_check');
        $displayData['js'] .= clearCheckOnUnCheck();
      
      sample HTML     
          <input type="checkbox" 
                 value="true" 
                 name="chkUFShow_11_75" 
                 onChange="clearCheckOnUnCheck(document.frmVolReg.chkUFShow_11_75, 
                                               document.frmVolReg.chkUFReq_11_75);" >
   ------------------------------------------------------------------------------------*/
      return('   
         <script language="JavaScript" type="text/JavaScript">
         <!---
            function clearCheckOnUnCheck($chk1, $chk2){
               if (!$chk1.checked){
                  $chk2.checked = false;
               }
            }
      
         // end --->
         </script>');
   }

   function setCheckOnCheck(){
   /* ------------------------------------------------------------------------------------
      set checkbox 2 if checkbox 1 is checked
      
      sample calling sequence
        $this->load->helper('js/clear_set_check_on_check');
        $displayData['js'] .= setCheckOnCheck();
      
      sample HTML     
          <input type="checkbox" 
                 value="true" 
                 name="chkUFShow_11_75" 
                 onChange="setCheckOnCheck(document.frmVolReg.chkUFShow_11_75, 
                                               document.frmVolReg.chkUFReq_11_75);" >
   ------------------------------------------------------------------------------------*/
      return('   
         <script language="JavaScript" type="text/JavaScript">
         <!---
            function setCheckOnCheck($chk1, $chk2){
               if ($chk1.checked){
                  $chk2.checked = true;
               }
            }
      
         // end --->
         </script>');
   }




