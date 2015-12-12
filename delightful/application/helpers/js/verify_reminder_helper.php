<?php
/*---------------------------------------------------------------------
// The Empowered Non-Profit
//
// copyright (c) 2011 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
// Ad maiorem Dei gloriam
---------------------------------------------------------------------
      $this->load->helper('js/verify_reminder');
---------------------------------------------------------------------*/

function reminderVerify(){
   return('
      <script language="JavaScript"><!--

      function verifyReminderForm(frmTest,bUSDateFormat,bNew){
      //---------------------------------------------------
      //
      //---------------------------------------------------
         var bFailed, strHold, strErrMsg, strFront, strErrMsgRecur;
         bFailed = false; strErrMsg = \'\';
         strFront = \'\n   * \';

            // first/last name
         if (!bVerifyString(trim(frmTest.txtSubject.value))) {
            bFailed = true;
            strErrMsg += strFront + \'Please enter a REMINDER SUBJECT\';
         }

         var strStartDate = trim(frmTest.txtStart.value);
         if (!isValidDate(strStartDate, !bUSDateFormat)){
            bFailed = true;
            strErrMsg += strFront + \'Please enter a valid REMINDER DISPLAY DATE\';
         }
         
         bFailed = verifyRecurEvent(frmTest, false) || bFailed;


         if (bFailed) {
            alert(\'Problems were detected in this REMINDER FORM. Please review and correct.\n\'
                +strErrMsg+strRecurErrMsg);
         }
         return(!bFailed);
      }
      //--></script>');
}
