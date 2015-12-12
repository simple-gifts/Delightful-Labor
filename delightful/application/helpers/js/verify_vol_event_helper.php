<?php
/*---------------------------------------------------------------------
   Delightful Labor
  
   copyright (c) 2012-2013 by Database Austin
   Austin, Texas
  
   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
   Ad maiorem Dei gloriam
---------------------------------------------------------------------
      $this->load->helper('js/verify_vol_event');
---------------------------------------------------------------------*/
function volEventVerify(){
   return('

      <script language="JavaScript"><!--

      function verifyVolEventForm(frmTest,bUSDateFormat,bNew){
      //---------------------------------------------------
      //
      //---------------------------------------------------
         var bFailed, strHold, strErrMsg, strFront, strErrMsgRecur;
         bFailed = false; strErrMsg = \'\';
         strFront = \'\n   * \';

            // event name
         if (!bVerifyString(trim(frmTest.txtEvent.value))) {
            bFailed = true;
            strErrMsg += strFront + \'Please enter an EVENT NAME\';
         }

         if (bNew){
            var strStartDate = trim(frmTest.txtStart.value);
            if (!isValidDate(strStartDate, !bUSDateFormat)){
               bFailed = true;
               strErrMsg += strFront + \'Please enter a valid EVENT DATE\';
            }
         
            bFailed = verifyRecurEvent(frmTest, false) || bFailed;
         }

         if (bFailed) {
            alert(\'Problems were detected in this VOLUNTEER EVENT FORM. Please review and correct.\n\'
                +strErrMsg+strRecurErrMsg);
         }
         return(!bFailed);
      }
      //--></script>');
}
