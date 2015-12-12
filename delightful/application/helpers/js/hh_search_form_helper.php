<?php
/*-------------------------------------------------
 copyright (c) 2011 John Zimmerman
      $this->load->helper('js/hh_search_form');
--------------------------------------------------*/

function verifyHHForm(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   return('
         <script language="JavaScript"><!--

         function verifySelHHForm(frmTest){
         //---------------------------------------------------
         //
         //---------------------------------------------------
            var bFailed, strHold, strErrMsg, strFront, bRangeFailed;
            bFailed = false; strErrMsg = \'\';
            strFront = \'\\n   * \';

            var strRadio = strRadioSelValue(frmTest.rdoHH);

               //-----------------------
               // search term
               //-----------------------
            if (strRadio==\'existing\'){
               if (!bVerifyString(trim(frmTest.txtHH.value))) {
                  bFailed = true;
                  strErrMsg += strFront + \'Please enter your SEARCH TERM\';
               }
            }

            if (bFailed) {
               alert(\'Problems were detected in this SEARCH FORM. Please review and correct.\\n\'
                   +strErrMsg);
            }
            return(!bFailed);
         }
         //--></script>');
         
}