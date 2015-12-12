<?php
/*--------------------------------------------------------------
 Delightful Labor

 copyright (c) 2014 by Database Austin
 Austin, Texas

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.
--------------------------------------------------------------
 utility routine to toggle the parenthesis status of a
 paren text box in the search modules
--------------------------------------------------------------

      $this->load->helper ('js/toggle_paren');

-------------------------------------------------------------- */

   function strToggleParen(){
      return('
         <script language="JavaScript" type="text/JavaScript">
         function toggleParen(objText, strSetValue) {
            if (objText.value==\'\') {
               objText.value=strSetValue;
            }else {
               objText.value=\'\';
            }
         }
         // end --->
         </script>
         ');
   }