<?php
/*
   
*/
function verifySelSponPayForm(){
   return('
      <script language="JavaScript"><!--

      function verifySelSponPayForm(frmTest){
      //---------------------------------------------------
      //
      //---------------------------------------------------
         var bFailed, strHold, strErrMsg, strFront, bRangeFailed;
         bFailed = false; strErrMsg = \'\';
         strFront = \'\n   * \';

         var strRadio = strRadioSelValue(frmTest.rdoSP);

            //-----------------------
            // person search
            //-----------------------
         if (strRadio==\'person\'){
            if (!bVerifyString(trim(frmTest.txtSPP.value))) {
               bFailed = true;
               strErrMsg += strFront + \'Please enter your SEARCH TERM for Individuals\';
            }
         }
            //-----------------------
            // biz / org search
            //-----------------------
         if (strRadio==\'biz\'){
            if (!bVerifyString(trim(frmTest.txtSPB.value))) {
               bFailed = true;
               strErrMsg += strFront + \'Please enter your SEARCH TERM for Business/Organization\';
            }
         }

         if (bFailed) {
            alert(\'Problems were detected in this SEARCH FORM. Please review and correct.\n\'
                +strErrMsg);
         }
         return(!bFailed);
      }
      //--></script>');
}      
