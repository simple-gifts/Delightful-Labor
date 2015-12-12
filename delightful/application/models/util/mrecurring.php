<?php
/*---------------------------------------------------------------------
// Delightful Labor
// copyright (c) 2012 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
   __construct           ()
   strRecurringTable     ($strForm)
   generateDateList      ()
   looseMonthlyRecurring ()
   fixedMonthlyRecurring ()
   weeklyRecurring       ()
   loadFormRecur         ()
---------------------------------------------------------------------
      $this->load->model('util/mrecurring', 'clsRecurring');
---------------------------------------------------------------------*/


class mrecurring extends CI_Model{

   public
      $strRecurLabel, $lDaysExtend;

   public function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
		parent::__construct();
      $this->strRecurLabel = '';
      $this->lDaysExtend = 1;
      $this->lStartMon = $this->lStartDay = $this->lStartYear = null;
   }

   public function strRecurringTable($strForm, $selects=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_null($selects)){
         $selects = new stdClass;
         $selects->lNumRecur = 1;
         $selects->rdoRecur   = 'daily';
         $selects->rdoFixedLoose = '';
         $selects->chkWkSun   = false;
         $selects->chkWkMon   = false;
         $selects->chkWkTue   = false;
         $selects->chkWkWed   = false;
         $selects->chkWkThu   = false;
         $selects->chkWkFri   = false;
         $selects->chkWkSat   = false;
         $selects->chkWeekDaysOnly = false;

         $selects->chkFixed = array();
         for ($idx=1; $idx<=31; ++$idx) $selects->chkFixed[$idx] = false;

         $selects->chkRel_1st  =
         $selects->chkRel_2nd  =
         $selects->chkRel_3rd  =
         $selects->chkRel_4th  =
         $selects->chkRel_5th  =
         $selects->chkRel_last = false;

         $selects->chkRelSun   =
         $selects->chkRelMon   =
         $selects->chkRelTue   =
         $selects->chkRelWed   =
         $selects->chkRelThu   =
         $selects->chkRelFri   =
         $selects->chkRelSat   = false;
      }

      $strRecurTable =
         '<table class="enpRpt" id="recurringOpts">';
//         '<table class="enpRpt" style="display: none;" id="recurringOpts">';

      $strRecurTable .=
             '<tr>
                 <td class="enpRptLabel" colspan="5">
                    <b>Total Recurrences:
                    <select name="ddlRecur">';

      for ($idx=1; $idx<=365; ++$idx) {
         $strRecurTable .= '<option value="'.$idx.'" '.($idx==$selects->lNumRecur ? 'SELECTED' : '').' >'.$idx.'</option>';
      }

      $strRecurTable .=
                   '</select>
                 </td>
              </tr>';

      $strRecurTable .=
             '<tr>
                 <td colspan="4"  class="enpRptLabel">
                    <b>'.$this->strRecurLabel.':</b>
                 </td>
              </tr>';

         //------------------------------
         // Daily recurrences
         //------------------------------
      $strRecurTable .=
               '<tr>
                   <td width="20" bgcolor="#ffffff">
                      &nbsp;
                   </td>
                     <td bgcolor="#ffffff">
                        <table border="0" bgcolor="#ffffff" style="border: 1px solid #bbbbbb;" width="100%">';
      $strRecurTable .=
             '<tr>
                 <td colspan="3"  nowrap>
                    <span onClick=setRadioOther('.$strForm.'.rdoRecur,\'daily\')>
                    <input type="radio" name="rdoRecur" value="daily" '.($selects->rdoRecur=='daily' ? 'checked' : '').'>
                    <b><i>Daily</span>&nbsp;</b></i>
                    <input type="checkbox" name="chkWeekDaysOnly" value="TRUE" '.($selects->chkWeekDaysOnly ? 'checked' : '').'
                         onChange=setRadioOther('.$strForm.'.rdoRecur,\'daily\')>
                         Weekdays Only
                 </td>
              </tr>';

         //------------------------------
         // weekly recurrences
         //------------------------------
      $strRecurTable .=
             '<tr>
                 <td colspan="3" >
                    <span onClick=setRadioOther('.$strForm.'.rdoRecur,\'weekly\')>
                    <input type="radio" name="rdoRecur" value="weekly" '.($selects->rdoRecur=='weekly' ? 'checked' : '').' >
                    <b><i>Weekly</span>
                 </td>
              </tr>';

      $strRecurTable .=
             '<tr>
                 <td bgcolor="#ffffff" colspan="2" style="width: 20pt;">&nbsp;</td>
                 <td nowrap style="border: 1px solid black;">
                    <input type="checkbox" name="chkWkSun" '.($selects->chkWkSun ? 'checked' : '').' value="TRUE" onChange=setRadioOther('.$strForm.'.rdoRecur,\'weekly\') >Sun
                    <input type="checkbox" name="chkWkMon" '.($selects->chkWkMon ? 'checked' : '').' value="TRUE" onChange=setRadioOther('.$strForm.'.rdoRecur,\'weekly\') >Mon
                    <input type="checkbox" name="chkWkTue" '.($selects->chkWkTue ? 'checked' : '').' value="TRUE" onChange=setRadioOther('.$strForm.'.rdoRecur,\'weekly\') >Tue
                    <input type="checkbox" name="chkWkWed" '.($selects->chkWkWed ? 'checked' : '').' value="TRUE" onChange=setRadioOther('.$strForm.'.rdoRecur,\'weekly\') >Wed
                    <input type="checkbox" name="chkWkThu" '.($selects->chkWkThu ? 'checked' : '').' value="TRUE" onChange=setRadioOther('.$strForm.'.rdoRecur,\'weekly\') >Thu
                    <input type="checkbox" name="chkWkFri" '.($selects->chkWkFri ? 'checked' : '').' value="TRUE" onChange=setRadioOther('.$strForm.'.rdoRecur,\'weekly\') >Fri
                    <input type="checkbox" name="chkWkSat" '.($selects->chkWkSat ? 'checked' : '').' value="TRUE" onChange=setRadioOther('.$strForm.'.rdoRecur,\'weekly\') >Sat
                 </td>
              </tr>';

         //------------------------------
         // monthly recurrences
         //------------------------------
      $strRecurTable .=
             '<tr>
                 <td colspan="3" >
                    <span onClick=setRadioOther('.$strForm.'.rdoRecur,\'monthly\')>
                    <input type="radio" name="rdoRecur" value="monthly" '.($selects->rdoRecur=='monthly' ? 'checked' : '').'>
                    <b><i>Monthly</span>
                 </td>
              </tr>';

        //-------------------------------
        // fixed days within the month
        //-------------------------------
      $strRecurTable .=
             '<tr>
                 <td bgcolor="#ffffff" colspan="2" style="width: 20pt;">&nbsp;</td>
                 <td  nowrap style="border: 1px solid black;">
                    <span onClick="setRadioOther('.$strForm.'.rdoFixedLoose,\'FL\'); setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">
                    <input type="radio" name="rdoFixedLoose" value="FL" '.($selects->rdoFixedLoose == 'FL' ? 'checked' : '').'>
                    Fixed monthly dates:</span> <br>
                       <table border="0" width="100%" >
                          <tr>
                             <td width="6">&nbsp;';

      for ($idx=1; $idx<=31; ++$idx) {
         if (((($idx-1) % 7)==0) && ($idx>1)) {
            $strRecurTable .= '</tr><tr><td width="6">&nbsp;';
         }
         $strRecurTable .=
            '<td nowrap><input type="checkbox" name="chkFixed'.$idx.'" '.($selects->chkFixed[$idx] ? 'checked' : '').'
                 onchange="setRadioOther('.$strForm.'.rdoFixedLoose,\'FL\');
                 setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">'.$idx.'&nbsp;';

      }
      $strRecurTable .=
                        '</tr>
                       </table>
                 </td>
              </tr>';


        //---------------------------------
        // relative days within the month
        //---------------------------------
      $strRecurTable .=
             '<tr>
                 <td bgcolor="#ffffff" colspan="2" style="width: 30pt;">&nbsp;</td>
                 <td nowrap style="border: 1px solid black;">
                    <span onClick="setRadioOther('.$strForm.'.rdoFixedLoose,\'RM\'); setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">
                    <input type="radio" name="rdoFixedLoose" value="RM" '.($selects->rdoFixedLoose == 'RM' ? 'checked' : '').'>
                    Relative monthly dates:</span> <br>
                       <table border="0" width="100%" >
                          <tr>
                             <td width="6">&nbsp;
                             <td colspan="7" align="left">
                                <input type="checkbox" name="chkRel_1st"  value="TRUE" '.($selects->chkRel_1st  ? 'checked' : '').' onchange="setRadioOther('.$strForm.'.rdoFixedLoose,\'RM\'); setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">1st&nbsp;&nbsp;
                                <input type="checkbox" name="chkRel_2nd"  value="TRUE" '.($selects->chkRel_2nd  ? 'checked' : '').' onchange="setRadioOther('.$strForm.'.rdoFixedLoose,\'RM\'); setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">2nd&nbsp;&nbsp;
                                <input type="checkbox" name="chkRel_3rd"  value="TRUE" '.($selects->chkRel_3rd  ? 'checked' : '').' onchange="setRadioOther('.$strForm.'.rdoFixedLoose,\'RM\'); setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">3rd&nbsp;&nbsp;
                                <input type="checkbox" name="chkRel_4th"  value="TRUE" '.($selects->chkRel_4th  ? 'checked' : '').' onchange="setRadioOther('.$strForm.'.rdoFixedLoose,\'RM\'); setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">4th&nbsp;&nbsp;
                                <input type="checkbox" name="chkRel_5th"  value="TRUE" '.($selects->chkRel_5th  ? 'checked' : '').' onchange="setRadioOther('.$strForm.'.rdoFixedLoose,\'RM\'); setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">5th&nbsp;&nbsp;
                                <input type="checkbox" name="chkRel_last" value="TRUE" '.($selects->chkRel_last ? 'checked' : '').' onchange="setRadioOther('.$strForm.'.rdoFixedLoose,\'RM\'); setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">last&nbsp;&nbsp;
                             </td>
                          </tr>

                          <tr>
                             <td width="6">&nbsp;
                             <td nowrap><input type="checkbox" value="on" name="chkRelSun" '.($selects->chkRelSun ? 'checked' : '').' onchange="setRadioOther('.$strForm.'.rdoFixedLoose,\'RM\'); setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">Sun
                             <td nowrap><input type="checkbox" value="on" name="chkRelMon" '.($selects->chkRelMon ? 'checked' : '').' onchange="setRadioOther('.$strForm.'.rdoFixedLoose,\'RM\'); setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">Mon
                             <td nowrap><input type="checkbox" value="on" name="chkRelTue" '.($selects->chkRelTue ? 'checked' : '').' onchange="setRadioOther('.$strForm.'.rdoFixedLoose,\'RM\'); setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">Tue
                             <td nowrap><input type="checkbox" value="on" name="chkRelWed" '.($selects->chkRelWed ? 'checked' : '').' onchange="setRadioOther('.$strForm.'.rdoFixedLoose,\'RM\'); setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">Wed
                             <td nowrap><input type="checkbox" value="on" name="chkRelThu" '.($selects->chkRelThu ? 'checked' : '').' onchange="setRadioOther('.$strForm.'.rdoFixedLoose,\'RM\'); setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">Thu
                             <td nowrap><input type="checkbox" value="on" name="chkRelFri" '.($selects->chkRelFri ? 'checked' : '').' onchange="setRadioOther('.$strForm.'.rdoFixedLoose,\'RM\'); setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">Fri
                             <td nowrap><input type="checkbox" value="on" name="chkRelSat" '.($selects->chkRelSat ? 'checked' : '').' onchange="setRadioOther('.$strForm.'.rdoFixedLoose,\'RM\'); setRadioOther('.$strForm.'.rdoRecur,\'monthly\')">Sat
                          </tr>
                       </table>
                 </td>
              </tr>';


         //------------------------------
         // Annually recurrences
         //------------------------------
      $strRecurTable .=
             '<tr>
                 <td colspan="3" nowrap>
                    <span onClick=setRadioOther('.$strForm.'.rdoRecur,\'annual\')>
                    <input type="radio" name="rdoRecur" value="annual" '.($selects->rdoRecur=='annual' ? 'checked' : '').'>
                    <b><i>Annually</span>&nbsp;</b></i>
                 </td>
              </tr>';



      $strRecurTable .= '</table></td></tr></table>';
      return($strRecurTable);
   }

   public function generateDateList(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $this->lNumDates = 0;
      $this->dateList = array();

      switch ($this->enumRecurGroup) {
         case 'annual':
            for ($idx=0; $idx < $this->lRecurCnt; ++$idx) {
               $this->dateList[$idx]          = new stdClass;
               $this->dateList[$idx]->uts     = mktime(0, 0, 0, $this->lStartMon, $this->lStartDay, ($this->lStartYear+$idx));
               $this->dateList[$idx]->utsEnd  = mktime(0, 0, 0, $this->lStartMon, $this->lStartDay+$this->lDaysExtend, ($this->lStartYear+$idx));
               $this->dateList[$idx]->strDate = date($genumDateFormat, $this->dateList[$idx]->uts);
            }
            break;

         case 'daily':
            $this->weeklyRecurring();
            break;

         case 'weekly':
            $this->weeklyRecurring();
            break;

         case 'monthly':
            if ($this->enumFixedLoose=='FL'){
               $this->fixedMonthlyRecurring();
            }else {
               $this->looseMonthlyRecurring();
            }
            break;

         default:
            screamForHelp($this->enumRecurGroup.': Invalid RECURRING FREQUENCY<br></b>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
         break;
      }
   }

   private function looseMonthlyRecurring(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $lFoundCnt = 0;

      $dteBaseDate = mktime(0,0,0, $this->lStartMon, $this->lStartDay, $this->lStartYear);

      $lStartDay   = 1;
      $lDOW_Cnt     = array();
      $lDOW_LastCnt = array();

      while ($lFoundCnt < $this->lRecurCnt) {
         $dteTest = mktime(0,0,0, $this->lStartMon, $lStartDay, $this->lStartYear);
         $myDate = getdate($dteTest);
         $lYear  = $myDate['year'];
         $lMonth = $myDate['mon'];
         $lDay   = $myDate['mday'];
         $lDOW   = $myDate['wday'];

            //------------------------------------------------------
            // reset weekday counts at the beginning of each month
            //------------------------------------------------------
         if ($lDay==1) {
            for ($idx=0; $idx < 7; ++$idx) {
               $lDOW_Cnt[$idx] = 0;
            }

               //------------------------------------
               // reset the last day of month count
               //------------------------------------
            if ($this->b_last) {
               $dteLastWeek = mktime(0,0,0, $lMonth+1, 0, $lYear);

               $lastWeekDate = getdate($dteLastWeek);
               $lDayLast   = $lastWeekDate['mday'];
               $lDOWLast   = $lastWeekDate['wday'];
               for ($idx=0; $idx<7; ++$idx) {
                  $lDOW_LastCnt[$lDOWLast] = $lDayLast;
                  --$lDOWLast; if ($lDOWLast<0) $lDOWLast = 6;
                  --$lDayLast;
               }
            }
         }

         ++$lDOW_Cnt[$lDOW];

         if ($dteTest >= $dteBaseDate) {
            if ($this->bSetCnt[$lDOW_Cnt[$lDOW]] && $this->bWeekSet[$lDOW]) {
               $this->dateList[$lFoundCnt]          = new stdClass;
               $this->dateList[$lFoundCnt]->uts     = mktime(0, 0, 0, $this->lStartMon, $lStartDay, $this->lStartYear);
               $this->dateList[$lFoundCnt]->utsEnd  = mktime(0, 0, 0, $this->lStartMon, $lStartDay+$this->lDaysExtend, $this->lStartYear);
               $this->dateList[$lFoundCnt]->strDate = date($genumDateFormat, $this->dateList[$lFoundCnt]->uts);
               ++$lFoundCnt;
            }elseif ($this->b_last) {
               if ($lDOW_LastCnt[$lDOW]==$lDay && $this->bWeekSet[$lDOW]) {
                  $this->dateList[$lFoundCnt]          = new stdClass;
                  $this->dateList[$lFoundCnt]->uts     = mktime(0, 0, 0, $this->lStartMon, $lStartDay, $this->lStartYear);
                  $this->dateList[$lFoundCnt]->utsEnd  = mktime(0, 0, 0, $this->lStartMon, $lStartDay+$this->lDaysExtend, $this->lStartYear);
                  $this->dateList[$lFoundCnt]->strDate = date($genumDateFormat, $this->dateList[$lFoundCnt]->uts);
                  ++$lFoundCnt;
               }
            }
         }
         ++$lStartDay;
      }
   }

   private function fixedMonthlyRecurring(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $lFoundCnt = 0;

      $lStartDay = $this->lStartDay;
      $dteBaseDate = mktime(0,0,0, $this->lStartMon, $lStartDay, $this->lStartYear);

      while ($lFoundCnt < $this->lRecurCnt) {
         $dteTest = mktime(0,0,0, $this->lStartMon, $lStartDay, $this->lStartYear);
         $myDate = getdate($dteTest);
         $lYear  = $myDate['year'];
         $lMonth = $myDate['mon'];
         $lDay   = $myDate['mday'];

         if ($this->bDaySelected[$lDay]) {
            $this->dateList[$lFoundCnt]          = new stdClass;
            $this->dateList[$lFoundCnt]->uts     = mktime(0, 0, 0, $this->lStartMon, $lStartDay, $this->lStartYear);
            $this->dateList[$lFoundCnt]->utsEnd  = mktime(0, 0, 0, $this->lStartMon, $lStartDay+$this->lDaysExtend, $this->lStartYear);
            $this->dateList[$lFoundCnt]->strDate = date($genumDateFormat, $this->dateList[$lFoundCnt]->uts);

            ++$lFoundCnt;
         }
         ++$lStartDay;
      }
   }

   private function weeklyRecurring(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $lFoundCnt = 0;
      $lStartDay = $this->lStartDay;
      while ($lFoundCnt < $this->lRecurCnt) {
         if ($this->bWeekSet[(integer)date('w', mktime(0,0,0, $this->lStartMon, $lStartDay, $this->lStartYear))]){
            $this->dateList[$lFoundCnt]          = new stdClass;
            $this->dateList[$lFoundCnt]->uts     = mktime(0, 0, 0, $this->lStartMon, $lStartDay, $this->lStartYear);
            $this->dateList[$lFoundCnt]->utsEnd  = mktime(0, 0, 0, $this->lStartMon, $lStartDay+$this->lDaysExtend, $this->lStartYear);
            $this->dateList[$lFoundCnt]->strDate = date($genumDateFormat, $this->dateList[$lFoundCnt]->uts);
            ++$lFoundCnt;
         }
         ++$lStartDay;
      }
   }

   public function loadFormRecur(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      $this->lRecurCnt         = (integer)$_REQUEST['ddlRecur'];
      $this->enumRecurGroup    = $_REQUEST['rdoRecur'];

      $strStartDate = trim($_POST['txtStart']);

      MDY_ViaUserForm($strStartDate, $this->lStartMon, $this->lStartDay, $this->lStartYear, $gbDateFormatUS);
/*
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$strStartDate = $strStartDate <br></font>\n");
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$this->lStartMon = $this->lStartMon <br></font>\n");
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$this->lStartDay = $this->lStartDay <br></font>\n");
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$this->lStartYear = $this->lStartYear <br></font>\n");
die;

 */
      $this->dteStart = strtotime($this->lStartMon.'/'.$this->lStartDay.'/'.$this->lStartYear);
      $this->bDaySelected = $this->bWeekSet = $this->bSetCnt = array();

      switch ($this->enumRecurGroup) {
         case 'annual':
            break;

         case 'daily':
            $this->bWeekSet[1] = true;
            $this->bWeekSet[2] = true;
            $this->bWeekSet[3] = true;
            $this->bWeekSet[4] = true;
            $this->bWeekSet[5] = true;

            $this->bWeekdaysOnly = @$_REQUEST['chkWeekDaysOnly']=='TRUE';

            if ($this->bWeekdaysOnly) {
               $this->bWeekSet[0] = false;
               $this->bWeekSet[6] = false;
            }else {
               $this->bWeekSet[0] = true;
               $this->bWeekSet[6] = true;
            }
            break;

         case 'weekly':
            $this->bWeekSet[0] = @$_REQUEST['chkWkSun']=='TRUE';
            $this->bWeekSet[1] = @$_REQUEST['chkWkMon']=='TRUE';
            $this->bWeekSet[2] = @$_REQUEST['chkWkTue']=='TRUE';
            $this->bWeekSet[3] = @$_REQUEST['chkWkWed']=='TRUE';
            $this->bWeekSet[4] = @$_REQUEST['chkWkThu']=='TRUE';
            $this->bWeekSet[5] = @$_REQUEST['chkWkFri']=='TRUE';
            $this->bWeekSet[6] = @$_REQUEST['chkWkSat']=='TRUE';

            break;

         case 'monthly':
            $this->enumFixedLoose = $_REQUEST['rdoFixedLoose'];
            if ($this->enumFixedLoose=='FL'){
               for ($idx=1; $idx<=31; ++$idx) {
                  $this->bDaySelected[$idx] = &$_REQUEST['chkFixed'.$idx] == 'on';
               }
            }else {
               $this->bSetCnt[1]  = @$_REQUEST['chkRel_1st'] =='TRUE';
               $this->bSetCnt[2]  = @$_REQUEST['chkRel_2nd'] =='TRUE';
               $this->bSetCnt[3]  = @$_REQUEST['chkRel_3rd'] =='TRUE';
               $this->bSetCnt[4]  = @$_REQUEST['chkRel_4th'] =='TRUE';
               $this->bSetCnt[5]  = @$_REQUEST['chkRel_5th'] =='TRUE';
               $this->b_last      = @$_REQUEST['chkRel_last']=='TRUE';

               $this->bWeekSet[0] = @$_REQUEST['chkRelSun']=='on';
               $this->bWeekSet[1] = @$_REQUEST['chkRelMon']=='on';
               $this->bWeekSet[2] = @$_REQUEST['chkRelTue']=='on';
               $this->bWeekSet[3] = @$_REQUEST['chkRelWed']=='on';
               $this->bWeekSet[4] = @$_REQUEST['chkRelThu']=='on';
               $this->bWeekSet[5] = @$_REQUEST['chkRelFri']=='on';
               $this->bWeekSet[6] = @$_REQUEST['chkRelSat']=='on';
            }
            break;

         default:
            screamForHelp($this->enumRecurGroup.': Invalid RECURRING FREQUENCY<br></b>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
         break;
      }

   }










}

?>