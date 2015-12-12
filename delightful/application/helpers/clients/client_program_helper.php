<?php
/*
      $this->load->helper('clients/client_program');
*/

   function bTypeIsClientProg($enumTType){
      return($enumTType==CENUM_CONTEXT_CPROGENROLL ||
             $enumTType==CENUM_CONTEXT_CPROGATTEND ||
             $enumTType==CENUM_CONTEXT_CPROGRAM);
   }

   function setClientProgFields(&$displayData, &$bClientProg, &$lCProgID, &$cprog, $enumTType, $lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $CI =& get_instance();

      $displayData['bClientProg'] = $bClientProg = bTypeIsClientProg($enumTType); // ==CENUM_CONTEXT_CPROGENROLL || $enumTType==CENUM_CONTEXT_CPROGATTEND;
      if ($bClientProg){
         $CI->load->model('client_features/mcprograms',         'cprograms');
         $CI->load->helper('clients/link_client_features');

         $displayData['bEnrollment'] = $bEnrollment = $enumTType==CENUM_CONTEXT_CPROGENROLL;
         if ($bEnrollment){
            $CI->cprograms->loadClientProgramsViaETableID($lTableID);
         }else {
            $CI->cprograms->loadClientProgramsViaATableID($lTableID);
         }
         $cprog = $CI->cprograms->cprogs[0];
         $lCProgID = $cprog->lKeyID;
         $displayData['strTableLabel'] = ($bEnrollment ? 'Enrollment ' : 'Attendance ').' table: '
                                               .htmlspecialchars($cprog->strProgramName);
         $displayData['entrySummary'] = $CI->cprograms->strHTMLProgramSummaryDisplay($enumTType);
      }else {
         $cprog = null;
         $displayData['strTableLabel'] = htmlspecialchars($CI->clsUF->userTables[0]->strUserTableName); // htmlspecialchars($userTable->strUserTableName);
         $displayData['entrySummary']  = $CI->clsUF->strUFTableSummaryDisplay(true);
      }
   }

   function strDDLDuration($strDDLName, $bAddBlank, $dMatchVal){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '<select name="'.$strDDLName.'">'."\n";
      if ($bAddBlank){
         $strOut .= '<option value="-1" >&nbsp;</option>'."\n";
      }

      $strOut .= '
         <option value=" 0.25" '.($dMatchVal== 0.25 ? 'SELECTED' : '').'> 15 minutes</option>
         <option value=" 0.50" '.($dMatchVal== 0.50 ? 'SELECTED' : '').'> 30 minutes</option>
         <option value=" 0.75" '.($dMatchVal== 0.75 ? 'SELECTED' : '').'> 45 minutes</option>
         <option value=" 1.00" '.($dMatchVal== 1.00 ? 'SELECTED' : '').'>1 hour</option>
         <option value=" 1.25" '.($dMatchVal== 1.25 ? 'SELECTED' : '').'>1 hour 15 minutes</option>
         <option value=" 1.50" '.($dMatchVal== 1.50 ? 'SELECTED' : '').'>1 hour 30 minutes</option>
         <option value=" 1.75" '.($dMatchVal== 1.75 ? 'SELECTED' : '').'>1 hour 45 minutes</option>
         <option value=" 2.00" '.($dMatchVal== 2.00 ? 'SELECTED' : '').'>2 hours</option>
         <option value=" 2.25" '.($dMatchVal== 2.25 ? 'SELECTED' : '').'>2 hours 15 minutes</option>
         <option value=" 2.50" '.($dMatchVal== 2.50 ? 'SELECTED' : '').'>2 hours 30 minutes</option>
         <option value=" 2.75" '.($dMatchVal== 2.75 ? 'SELECTED' : '').'>2 hours 45 minutes</option>
         <option value=" 3.00" '.($dMatchVal== 3.00 ? 'SELECTED' : '').'>3 hours</option>
         <option value=" 3.25" '.($dMatchVal== 3.25 ? 'SELECTED' : '').'>3 hours 15 minutes</option>
         <option value=" 3.50" '.($dMatchVal== 3.50 ? 'SELECTED' : '').'>3 hours 30 minutes</option>
         <option value=" 3.75" '.($dMatchVal== 3.75 ? 'SELECTED' : '').'>3 hours 45 minutes</option>
         <option value=" 4.00" '.($dMatchVal== 4.00 ? 'SELECTED' : '').'>4 hours</option>
         <option value=" 4.25" '.($dMatchVal== 4.25 ? 'SELECTED' : '').'>4 hours 15 minutes</option>
         <option value=" 4.50" '.($dMatchVal== 4.50 ? 'SELECTED' : '').'>4 hours 30 minutes</option>
         <option value=" 4.75" '.($dMatchVal== 4.75 ? 'SELECTED' : '').'>4 hours 45 minutes</option>
         <option value=" 5.00" '.($dMatchVal== 5.00 ? 'SELECTED' : '').'>5 hours</option>
         <option value=" 5.25" '.($dMatchVal== 5.25 ? 'SELECTED' : '').'>5 hours 15 minutes</option>
         <option value=" 5.50" '.($dMatchVal== 5.50 ? 'SELECTED' : '').'>5 hours 30 minutes</option>
         <option value=" 5.75" '.($dMatchVal== 5.75 ? 'SELECTED' : '').'>5 hours 45 minutes</option>
         <option value=" 6.00" '.($dMatchVal== 6.00 ? 'SELECTED' : '').'>6 hours</option>
         <option value=" 6.25" '.($dMatchVal== 6.25 ? 'SELECTED' : '').'>6 hours 15 minutes</option>
         <option value=" 6.50" '.($dMatchVal== 6.50 ? 'SELECTED' : '').'>6 hours 30 minutes</option>
         <option value=" 6.75" '.($dMatchVal== 6.75 ? 'SELECTED' : '').'>6 hours 45 minutes</option>
         <option value=" 7.00" '.($dMatchVal== 7.00 ? 'SELECTED' : '').'>7 hours</option>
         <option value=" 7.25" '.($dMatchVal== 7.25 ? 'SELECTED' : '').'>7 hours 15 minutes</option>
         <option value=" 7.50" '.($dMatchVal== 7.50 ? 'SELECTED' : '').'>7 hours 30 minutes</option>
         <option value=" 7.75" '.($dMatchVal== 7.75 ? 'SELECTED' : '').'>7 hours 45 minutes</option>
         <option value=" 8.00" '.($dMatchVal== 8.00 ? 'SELECTED' : '').'>8 hours</option>
         <option value=" 8.25" '.($dMatchVal== 8.25 ? 'SELECTED' : '').'>8 hours 15 minutes</option>
         <option value=" 8.50" '.($dMatchVal== 8.50 ? 'SELECTED' : '').'>8 hours 30 minutes</option>
         <option value=" 8.75" '.($dMatchVal== 8.75 ? 'SELECTED' : '').'>8 hours 45 minutes</option>
         <option value=" 9.00" '.($dMatchVal== 9.00 ? 'SELECTED' : '').'>9 hours</option>
         <option value=" 9.25" '.($dMatchVal== 9.25 ? 'SELECTED' : '').'>9 hours 15 minutes</option>
         <option value=" 9.50" '.($dMatchVal== 9.50 ? 'SELECTED' : '').'>9 hours 30 minutes</option>
         <option value=" 9.75" '.($dMatchVal== 9.75 ? 'SELECTED' : '').'>9 hours 45 minutes</option>
         <option value="10.00" '.($dMatchVal==10.00 ? 'SELECTED' : '').'>10 hours</option>
         <option value="10.25" '.($dMatchVal==10.25 ? 'SELECTED' : '').'>10 hours 15 minutes</option>
         <option value="10.50" '.($dMatchVal==10.50 ? 'SELECTED' : '').'>10 hours 30 minutes</option>
         <option value="10.75" '.($dMatchVal==10.75 ? 'SELECTED' : '').'>10 hours 45 minutes</option>
         <option value="11.00" '.($dMatchVal==11.00 ? 'SELECTED' : '').'>11 hours</option>
         <option value="11.25" '.($dMatchVal==11.25 ? 'SELECTED' : '').'>11 hours 15 minutes</option>
         <option value="11.50" '.($dMatchVal==11.50 ? 'SELECTED' : '').'>11 hours 30 minutes</option>
         <option value="11.75" '.($dMatchVal==11.75 ? 'SELECTED' : '').'>11 hours 45 minutes</option>
         <option value="12.00" '.($dMatchVal==12.00 ? 'SELECTED' : '').'>12 hours</option>
         <option value="12.25" '.($dMatchVal==12.25 ? 'SELECTED' : '').'>12 hours 15 minutes</option>
         <option value="12.50" '.($dMatchVal==12.50 ? 'SELECTED' : '').'>12 hours 30 minutes</option>
         <option value="12.75" '.($dMatchVal==12.75 ? 'SELECTED' : '').'>12 hours 45 minutes</option>
         <option value="13.00" '.($dMatchVal==13.00 ? 'SELECTED' : '').'>13 hours</option>
         <option value="13.25" '.($dMatchVal==13.25 ? 'SELECTED' : '').'>13 hours 15 minutes</option>
         <option value="13.50" '.($dMatchVal==13.50 ? 'SELECTED' : '').'>13 hours 30 minutes</option>
         <option value="13.75" '.($dMatchVal==13.75 ? 'SELECTED' : '').'>13 hours 45 minutes</option>
         <option value="14.00" '.($dMatchVal==14.00 ? 'SELECTED' : '').'>14 hours</option>
         <option value="14.25" '.($dMatchVal==14.25 ? 'SELECTED' : '').'>14 hours 15 minutes</option>
         <option value="14.50" '.($dMatchVal==14.50 ? 'SELECTED' : '').'>14 hours 30 minutes</option>
         <option value="14.75" '.($dMatchVal==14.75 ? 'SELECTED' : '').'>14 hours 45 minutes</option>
         <option value="15.00" '.($dMatchVal==15.00 ? 'SELECTED' : '').'>15 hours</option>
         <option value="15.25" '.($dMatchVal==15.25 ? 'SELECTED' : '').'>15 hours 15 minutes</option>
         <option value="15.50" '.($dMatchVal==15.50 ? 'SELECTED' : '').'>15 hours 30 minutes</option>
         <option value="15.75" '.($dMatchVal==15.75 ? 'SELECTED' : '').'>15 hours 45 minutes</option>
         <option value="16.00" '.($dMatchVal==16.00 ? 'SELECTED' : '').'>16 hours</option>
         <option value="16.25" '.($dMatchVal==16.25 ? 'SELECTED' : '').'>16 hours 15 minutes</option>
         <option value="16.50" '.($dMatchVal==16.50 ? 'SELECTED' : '').'>16 hours 30 minutes</option>
         <option value="16.75" '.($dMatchVal==16.75 ? 'SELECTED' : '').'>16 hours 45 minutes</option>
         <option value="17.00" '.($dMatchVal==17.00 ? 'SELECTED' : '').'>17 hours</option>
         <option value="17.25" '.($dMatchVal==17.25 ? 'SELECTED' : '').'>17 hours 15 minutes</option>
         <option value="17.50" '.($dMatchVal==17.50 ? 'SELECTED' : '').'>17 hours 30 minutes</option>
         <option value="17.75" '.($dMatchVal==17.75 ? 'SELECTED' : '').'>17 hours 45 minutes</option>
         <option value="18.00" '.($dMatchVal==18.00 ? 'SELECTED' : '').'>18 hours</option>
         <option value="18.25" '.($dMatchVal==18.25 ? 'SELECTED' : '').'>18 hours 15 minutes</option>
         <option value="18.50" '.($dMatchVal==18.50 ? 'SELECTED' : '').'>18 hours 30 minutes</option>
         <option value="18.75" '.($dMatchVal==18.75 ? 'SELECTED' : '').'>18 hours 45 minutes</option>
         <option value="19.00" '.($dMatchVal==19.00 ? 'SELECTED' : '').'>19 hours</option>
         <option value="19.25" '.($dMatchVal==19.25 ? 'SELECTED' : '').'>19 hours 15 minutes</option>
         <option value="19.50" '.($dMatchVal==19.50 ? 'SELECTED' : '').'>19 hours 30 minutes</option>
         <option value="19.75" '.($dMatchVal==19.75 ? 'SELECTED' : '').'>19 hours 45 minutes</option>
         <option value="20.00" '.($dMatchVal==20.00 ? 'SELECTED' : '').'>20 hours</option>
         <option value="20.25" '.($dMatchVal==20.25 ? 'SELECTED' : '').'>20 hours 15 minutes</option>
         <option value="20.50" '.($dMatchVal==20.50 ? 'SELECTED' : '').'>20 hours 30 minutes</option>
         <option value="20.75" '.($dMatchVal==20.75 ? 'SELECTED' : '').'>20 hours 45 minutes</option>
         <option value="21.00" '.($dMatchVal==21.00 ? 'SELECTED' : '').'>21 hours</option>
         <option value="21.25" '.($dMatchVal==21.25 ? 'SELECTED' : '').'>21 hours 15 minutes</option>
         <option value="21.50" '.($dMatchVal==21.50 ? 'SELECTED' : '').'>21 hours 30 minutes</option>
         <option value="21.75" '.($dMatchVal==21.75 ? 'SELECTED' : '').'>21 hours 45 minutes</option>
         <option value="22.00" '.($dMatchVal==22.00 ? 'SELECTED' : '').'>22 hours</option>
         <option value="22.25" '.($dMatchVal==22.25 ? 'SELECTED' : '').'>22 hours 15 minutes</option>
         <option value="22.50" '.($dMatchVal==22.50 ? 'SELECTED' : '').'>22 hours 30 minutes</option>
         <option value="22.75" '.($dMatchVal==22.75 ? 'SELECTED' : '').'>22 hours 45 minutes</option>
         <option value="23.00" '.($dMatchVal==23.00 ? 'SELECTED' : '').'>23 hours</option>
         <option value="23.25" '.($dMatchVal==23.25 ? 'SELECTED' : '').'>23 hours 15 minutes</option>
         <option value="23.50" '.($dMatchVal==23.50 ? 'SELECTED' : '').'>23 hours 30 minutes</option>
         <option value="23.75" '.($dMatchVal==23.75 ? 'SELECTED' : '').'>23 hours 45 minutes</option>
         <option value="24.00" '.($dMatchVal==24.00 ? 'SELECTED' : '').'>24 hours</option>
         </select>';

      return($strOut);
   }

   function strAttendanceTable(&$cprog, $attrib){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lMaxDays = lDaysInMonth($attrib->lMonth, $attrib->lYear);
      $lNumERecs = $cprog->lNumERecs;
      
      if ($lNumERecs > 0){
         $lATableID     = $cprog->lAttendanceTableID;
         $strATable     = $cprog->strAttendanceTable;
         $strAFNPrefix  = $cprog->strATableFNPrefix;

         $lETableID = $cprog->lEnrollmentTableID;
         $attend = array();
         $idx = 0;
         foreach ($cprog->erecs as $erec){
            $lERecID = $erec->lKeyID;
            $lClientID = $erec->lClientID;

               // load the enrollees
            $attend[$idx] = array();
            $attend[$idx][0] =
                       strLinkView_ClientRecord($lClientID, 'View client record', true).'&nbsp;'
                      .str_pad($lClientID, 5, '0', STR_PAD_LEFT).'&nbsp;&nbsp;'
                      .'<b>'.htmlspecialchars($erec->strClientLName).'</b>, '
                            .htmlspecialchars($erec->strClientFName);

               // enrollment link
            $attend[$idx][1] =
                       strLinkView_CProgEnrollRec($lETableID, $lClientID, $lERecID, 'View Enrollment', true).'&nbsp;'
                            .str_pad($lERecID, 5, '0', STR_PAD_LEFT);

               // populate the attendance cells
            $strCheckImg = '<div style="display:inline-block; width: 100%; text-align: center;"><img src="'.DL_IMAGEPATH.'/misc/check06.gif"></div>';
            for ($jidx=2; $jidx<=($lMaxDays+1); ++$jidx){
               if (bEnrolleeInAttendance($erec, $attrib->lMonth, $jidx-1, $attrib->lYear)){
                  $attend[$idx][$jidx] = $strCheckImg;
                  $strEntry = '';
                  arecIDXsViaERecDate($erec, $attrib->lMonth, $jidx-1, $attrib->lYear, $lNumARecs, $arecIDX);
                  $lCnt = 1;
                  foreach ($arecIDX as $aIDX){
                     $arecCurrent = $erec->arecs[$aIDX];
                     $lARecID = $arecCurrent->lKeyID;

                     if ($lNumARecs > 1){
                        $strEntry .= '('.$lCnt.')<br>';
                        ++$lCnt;
                     }
                     if ($attrib->bALink){
                        $strEntry .= strLinkView_CProgAttendRec($lATableID, $lClientID, $lARecID,
                                'View attendance record', true).str_pad($lARecID, 5, '0', STR_PAD_LEFT).'<br>';
                     }
                     if ($attrib->bDuration){
                        $strEntry .= '<b>duration:</b> '.number_format($arecCurrent->dDuration, 2).'hr<br>';
                     }
                     if ($attrib->bActivity){
                        $strEntry .= '<b>activity:</b> '.strShortenString($arecCurrent->strActivity, 25, true).'<br>';
                     }
                     if ($attrib->bCNotes){
                        $strEntry .= '<b>case notes:</b> '.strShortenString($arecCurrent->strCaseNotes, 25, true).'<br>';
                     }
                  }
                  if ($strEntry != '') $attend[$idx][$jidx] .= '<br>'.$strEntry;
               }else {
                  $attend[$idx][$jidx] = '&nbsp;';
               }
            }
            ++$idx;
         }
      }

         // build the html table
      $strOut = '
          <div id="attendTable" style="width: 100%; overflow: auto; ">
          <table class="enpRpt">
             <tr>
                <td class="enpRptTitle" colspan="'.($lMaxDays+2).'">'
                   .htmlspecialchars($cprog->strSafeAttendLabel).': '
                   .htmlspecialchars($cprog->strProgramName).'
                </td>
             </tr>
             <tr>
                <td class="enpRptLabel" rowspan="2" style="vertical-align: bottom;">Enrollee</td>
                <td class="enpRptLabel" rowspan="2" style="vertical-align: bottom;">Enrollment<br>Record</td>
                <td class="enpRptLabel" colspan="'.$lMaxDays.'" style="text-align: center; vertical-align: middle; font-size: 12pt;">'
                        .$attrib->strLinkPrev
                        .strXlateMonth($attrib->lMonth).' '.$attrib->lYear
                        .$attrib->strLinkNext
                        .'</td></tr><tr>'."\n";
      for ($jidx=1; $jidx<=$lMaxDays; ++$jidx){
         $strOut .= '<td class="enpRptLabel" style="text-align: center;">&nbsp;'
                      .$jidx.'&nbsp;</td>'."\n";
      }
      $strOut .= '</tr>'."\n";
      for ($idx=0; $idx < $lNumERecs; ++$idx){
         $strOut .= '<tr class="makeStripe">'."\n";
         $strOut .= '<td class="enpRpt" style="width: 170pt;" nowrap>'.$attend[$idx][0].'</td>'."\n";
         for($jidx=1; $jidx<=$lMaxDays+1; ++$jidx){
            $strOut .= '<td class="enpRpt" style="font-size: 8pt;" nowrap>'.$attend[$idx][$jidx].'</td>'."\n";
         }
         $strOut .= '</tr">'."\n";
      }
      $strOut .= '</table></div>';
      return($strOut);
   }

   function bEnrolleeInAttendance($erec, $lMonth, $lDay, $lYear){
      if ($erec->lNumARecs == 0) return(false);
      $mdteTestDate = strMoDaYr2MySQLDate($lMonth, $lDay, $lYear);
      foreach ($erec->arecs as $arec){
         if ($arec->dteMysqlAttendance == $mdteTestDate) return(true);
      }
      return(false);
   }

   function arecIDXsViaERecDate($erec, $lMonth, $lDay, $lYear, &$lNumARecs, &$arecIDX){
      $lNumARecs = 0;
      $arecIDX = array();
      if ($erec->lNumARecs == 0) return;
      $mdteTestDate = strMoDaYr2MySQLDate($lMonth, $lDay, $lYear);
      $idx = 0;
      foreach ($erec->arecs as $arec){
         if ($arec->dteMysqlAttendance == $mdteTestDate){
            $arecIDX[$lNumARecs] = $idx;
            ++$lNumARecs;
         }
         ++$idx;
      }
   }

   function cprogScalars($cp,
               &$lCProgID,
               &$strProgramName,

               &$lEnrollmentTableID,
               &$strEnrollmentTable,
               &$strETableFNPrefix,
               &$strEFN_FID,
               &$strEFN_Retired,

               &$lAttendanceTableID,
               &$strAttendanceTable,
               &$strATableFNPrefix,
               &$strAFN_FID,
               &$strAFN_Retired,
               &$strAFN_DteAttend,
               &$strAFN_EnrollID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lCProgID                    = $cp->lKeyID;
      $strProgramName              = $cp->strProgramName;

      $lEnrollmentTableID          = $cp->lEnrollmentTableID;
      $strEnrollmentTable          = $cp->strEnrollmentTable;
      $strETableFNPrefix           = $cp->strETableFNPrefix;
      $strEFN_FID                  = $strETableFNPrefix.'_lForeignKey';
      $strEFN_Retired              = $strETableFNPrefix.'_bRetired';

      $lAttendanceTableID          = $cp->lAttendanceTableID;
      $strAttendanceTable          = $cp->strAttendanceTable;
      $strATableFNPrefix           = $cp->strATableFNPrefix;
      $strAFN_FID                  = $strATableFNPrefix.'_lForeignKey';
      $strAFN_Retired              = $strATableFNPrefix.'_bRetired';
      $strAFN_DteAttend            = $strATableFNPrefix.'_dteAttendance';
      $strAFN_EnrollID             = $strATableFNPrefix.'_lEnrollID';
   }




