<?php
/*---------------------------------------------------------------------
 copyright (c) 2012 by Database Austin

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.

      $this->load->helper('js/hh_search_form');

---------------------------------------------------------------------*/

function simpleSearch(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   return('
         <script language="JavaScript"><!--

         function verifySimpleSearch(frmTest){
         //---------------------------------------------------
         //
         //---------------------------------------------------
            var bFailed, strHold, strErrMsg, strFront, bRangeFailed;
            bFailed = false; strErrMsg = \'\';
            strFront = \'\\n   * \';

               //-----------------------
               // first/last name
               //-----------------------
            if (!bVerifyString(frmTest.txtSearch.value)) {
               bFailed = true;
               strErrMsg += strFront + \'Please enter your SEARCH CRITERIA\';
            }


            if (bFailed) {
               alert(\'Problems were detected in the SEARCH FORM. Please review and correct.\\n\'
                   +strErrMsg);
            }
            return(!bFailed);
         }
         //--></script>');
}
