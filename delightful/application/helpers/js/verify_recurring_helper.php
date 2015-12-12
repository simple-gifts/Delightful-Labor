<?php
/*---------------------------------------------------------------------
// copyright (c) 2012 by Database Austin
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('js/verify_recurring');
      $displayData['js'] .= verifyRecurring();
---------------------------------------------------------------------*/

function verifyRecurring(){
   return('
   <script language="JavaScript"><!--

   var strRecurErrMsg;
   function verifyRecurEvent(frmTest, bDisplayAlert){
      var bFailed, strHold, strFront, bRangeFailed;
      bFailed = false; strRecurErrMsg=\'\';
      strFront = \'\n   * \';

      if (!bVerifyRadio(frmTest.rdoRecur)){
         bFailed = true;
         strRecurErrMsg += strFront + \'Please enter a RECURRENCE TYPE (Daily/Weekly/Monthly)\';
      }

         // ensure a recurrence count was selected
      if (frmTest.ddlRecur.value==\'\'){
         bFailed = true;
         strRecurErrMsg += strFront + \'Please enter a NUMBER OF TIMES YOU WANT THIS EVENT TO RECUR\';
      }

         // if week is selected, make sure at least one day is checked
      var strSelRecur = strRadioSelValue(frmTest.rdoRecur);
      if (strSelRecur==\'weekly\') {
         if (!
               (
                  (frmTest.chkWkSun.checked) ||
                  (frmTest.chkWkMon.checked) ||
                  (frmTest.chkWkTue.checked) ||
                  (frmTest.chkWkWed.checked) ||
                  (frmTest.chkWkThu.checked) ||
                  (frmTest.chkWkFri.checked) ||
                  (frmTest.chkWkSat.checked)
               )
            ){
            bFailed = true;
            strRecurErrMsg += strFront + \'For the weekly recurring option, please select at least one DAY OF THE WEEK\';
         }
      }

         //--------------------------------------------------------------
         // for the monthly option, ensure that a fixed date or relative
         // date is set
         //--------------------------------------------------------------
      if (strSelRecur==\'monthly\') {
         if (!bVerifyRadio(frmTest.rdoFixedLoose)){
            bFailed = true;
            strRecurErrMsg += strFront + \'For the MONTHLY option, please select either FIXED or RELATIVE dates\';
         }else {
            var strSelFixedLoose = strRadioSelValue(frmTest.rdoFixedLoose);
            if (strSelFixedLoose==\'FL\') {
               if (!
                     (
                        frmTest.chkFixed1.checked  ||
                        frmTest.chkFixed2.checked  ||
                        frmTest.chkFixed3.checked  ||
                        frmTest.chkFixed4.checked  ||
                        frmTest.chkFixed5.checked  ||
                        frmTest.chkFixed6.checked  ||
                        frmTest.chkFixed7.checked  ||
                        frmTest.chkFixed8.checked  ||
                        frmTest.chkFixed9.checked  ||
                        frmTest.chkFixed10.checked ||
                        frmTest.chkFixed11.checked ||
                        frmTest.chkFixed12.checked ||
                        frmTest.chkFixed13.checked ||
                        frmTest.chkFixed14.checked ||
                        frmTest.chkFixed15.checked ||
                        frmTest.chkFixed16.checked ||
                        frmTest.chkFixed17.checked ||
                        frmTest.chkFixed18.checked ||
                        frmTest.chkFixed19.checked ||
                        frmTest.chkFixed20.checked ||
                        frmTest.chkFixed21.checked ||
                        frmTest.chkFixed22.checked ||
                        frmTest.chkFixed23.checked ||
                        frmTest.chkFixed24.checked ||
                        frmTest.chkFixed25.checked ||
                        frmTest.chkFixed26.checked ||
                        frmTest.chkFixed27.checked ||
                        frmTest.chkFixed28.checked ||
                        frmTest.chkFixed29.checked ||
                        frmTest.chkFixed30.checked ||
                        frmTest.chkFixed31.checked
                     )
                  ){
                  bFailed = true;
                  strRecurErrMsg += strFront + \'For the FIXED DATE MONTHLY OPTION, please check one or more dates\';
               }
            }else if (strSelFixedLoose==\'RM\') {
               if (!
                     (
                        frmTest.chkRel_1st.checked  ||
                        frmTest.chkRel_2nd.checked  ||
                        frmTest.chkRel_3rd.checked  ||
                        frmTest.chkRel_4th.checked  ||
                        frmTest.chkRel_5th.checked  ||
                        frmTest.chkRel_last.checked
                      )
                   ){
                  bFailed = true;
                  strRecurErrMsg += strFront + \'For the RELATIVE DATE MONTHLY OPTION, please check one or more relative dates (1st, 2nd, etc)\';
               }

               if (!
                     (
                        frmTest.chkRelSun.checked  ||
                        frmTest.chkRelMon.checked  ||
                        frmTest.chkRelTue.checked  ||
                        frmTest.chkRelWed.checked  ||
                        frmTest.chkRelThu.checked  ||
                        frmTest.chkRelFri.checked  ||
                        frmTest.chkRelSat.checked
                      )
                   ){
                  bFailed = true;
                  strRecurErrMsg += strFront + \'For the RELATIVE DATE MONTHLY OPTION, please check one or more days of the week\';
               }
            }
         }
      }

      if (bFailed && bDisplayAlert) {
         alert(\'Problems were detected with the RECURRING DATES. Please review and correct.\n\'
             +strRecurErrMsg);
      }
      return(bFailed);
   }
   //--></script>');
}
