<?php
   global $glLabelWidth;

   echoT($strHTMLSummary);
   $glLabelWidth = 150;

   echoT('<br>');

   if ($bMultiEntry && !($bCProg && $bEnrollment)){
      $strALabel = ($bCProg && !$bEnrollment ? $utable->cprog->strSafeAttendLabel : '');
      echoT(strLinkAdd_UFMultiRecEntry($utable->enumTType, $lTableID, $lFID, true,
               'Add '.$strALabel.' record', '', $lEnrollRecID).'&nbsp;'
           .strLinkAdd_UFMultiRecEntry($utable->enumTType, $lTableID, $lFID, false,
               'Add '.$strALabel.' record', '', $lEnrollRecID).'<br><br>');
   }

   if ($utable->lNumFields == 0  && !$bCProg){
      echoT('<br><i>There are no fields defined in table <b>'.htmlspecialchars($utable->strUserTableName).'</b></i><br><br>');
      return;
   }

   openMRecTable($strTableLabel, $utable);

   if ($lNumMRRecs > 0){
      writeMRecRows($lTableID, $lFID, $utable, $mRecs, $lEnrollRecID);
   }

   closeMRecTable($utable);

   function writeMRecRows($lTableID, $lFID, &$utable, &$mRecs, $lEnrollRecID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $strFNPrefix = $utable->strFieldPrefix;
      $strFNKeyID  = $strFNPrefix.'_lKeyID';
      if ($utable->bCProg) $lCProgID = $utable->cprog->lKeyID;
      $bReadOnly = $utable->bReadOnly;

      foreach ($mRecs as $mrec){
         echoT('<tr class="makeStripe">');

         $lRecID = $mrec->$strFNKeyID;

         if ($utable->bCProg && !$utable->bEnrollment){
            $strClone = '<br>'.strLinkClone_CPAttendance($lCProgID, $lRecID, 'Clone attendance record', true);
         }else {
            $strClone = '';
         }

         if ($bReadOnly){
            $strEditLink = '';
         }else {
            $strEditLink = strLinkEdit_UFMultiRecEntry($utable->enumTType, $lTableID, $lFID, $lRecID, true,  'Edit record', '', $lEnrollRecID);
         }

         echoT('
            <td class="enpRpt" style="text-align: center;">'
               .str_pad($lRecID, 5, '0', STR_PAD_LEFT).'&nbsp;'
               .strLinkView_UFMFRecordViaRecID($lTableID, $lFID, $lRecID, 'View', true).'
            </td>');

         echoT('
            <td class="enpRpt" style="text-align: center;">'
               .$strEditLink
               .$strClone.'
            </td>');

         if (!$bReadOnly){
            echoT('
               <td class="enpRpt" style="text-align: center;">'
                 .strLinkRem_UFMultiRecEntry($utable->enumTType, $lTableID, $lFID, $lRecID, 'Remove', true,  true).'
               </td>');
         }

         if ($utable->bCProg){
                  //-----------------------------
                  // enrollment records
                  //-----------------------------
            if ($utable->bEnrollment){
               echoT('
                  <td class="enpRpt">'
                     .date($genumDateFormat, $mrec->dteStart).'&nbsp;
                  </td>');
               if (is_null($mrec->dteMysqlEnd)){
                  $strEnd = '<i>Ongoing</i>';
               }else {
                  $strEnd = date($genumDateFormat, $mrec->dteEnd);
               }
               echoT('
                     <td class="enpRpt" style="text-align: center;">'
                        .$strEnd.'
                     </td>
                     <td class="enpRpt" style="text-align: center;">'
                        .($mrec->bCurrentlyEnrolled ? 'Yes' : 'No').'
                     </td>
                     <td class="enpRpt" style="text-align: center;">'
                        .strLinkAdd_CProgAttendance(false, $lFID, $utable->cprog->lKeyID, $lRecID, 'Add attendance record', true).'
                     </td>
                     ');
            }else {
                  //-----------------------------
                  // attendance records
                  //-----------------------------
               echoT('
                  <td class="enpRpt" style="text-align: center;">'
                     .str_pad($mrec->lEnrollRecordID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                     .strLinkView_UFMFRecordViaRecID($utable->cprog->lEnrollmentTableID, $lFID, $mrec->lEnrollRecordID, 'View enrollment record', true).'
                  </td>
                  <td class="enpRpt">'
                     .date($genumDateFormat, $mrec->dteAttendance).'&nbsp;
                  </td>
                  <td class="enpRpt" style="text-align: right;">'
                     .number_format($mrec->dDuration, 2).'&nbsp;
                  </td>
                  <td class="enpRpt" style="width: 220pt;">'
                     .nl2br(htmlspecialchars($mrec->strCaseNotes)).'&nbsp;
                  </td>
                  ');
               $utable->dTotDuration += $mrec->dDuration;
            }
         }

         if ($utable->lNumFields > 0){
            foreach ($utable->ufields as $ufield){
               displayField($mrec, $ufield);
            }
         }
         echoT('</tr>');
      }
   }

   function displayField(&$mrec, &$ufield){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;
      $strOut = $vVal = '';

      $enumType = $ufield->enumFieldType;
      $strFN = $ufield->strFieldNameInternal;
      if (!($enumType==CS_FT_HEADING || $enumType==CS_FT_LOG)) $vVal = $mrec->$strFN;

      switch ($enumType){
         case CS_FT_CHECKBOX:
            $strOut = '
               <td class="enpRpt" style="text-align: center; width: 60pt;">'
                  .($vVal ? 'Yes' : 'No').'
               </td>';
            break;
         case CS_FT_DATE:
            if ($vVal.''==''){
               $strOut = '
                   <td class="enpRpt" style="text-align: center; width: 100pt;">&nbsp;</td>';
            }else {
               $strOut = '
                  <td class="enpRpt" style="text-align: center; width: 100pt;">'
                     .strNumericDateViaMysqlDate($vVal, $gbDateFormatUS).'
                  </td>';
            }
            break;

         case CS_FT_TEXT255:
            $strOut = '
               <td class="enpRpt"  style="width: 140pt;">'
                  .htmlspecialchars($vVal).'
               </td>';
            break;
         case CS_FT_TEXT80:
            $strOut = '
               <td class="enpRpt"  style="width: 100pt;">'
                  .htmlspecialchars($vVal).'
               </td>';
            break;
         case CS_FT_TEXT20:
            $strOut = '
               <td class="enpRpt"  style="width: 80pt;">'
                  .htmlspecialchars($vVal).'
               </td>';
            break;

         case CS_FT_HEADING:
         case CS_FT_LOG:
            break;

         case CS_FT_TEXTLONG:
            $strOut = '
               <td class="enpRpt"  style="width: 220pt;">'
                  .nl2br(htmlspecialchars($vVal)).'
               </td>';
            break;

         case CS_FT_CLIENTID:
         case CS_FT_INTEGER:
            if ($vVal.''==''){
               $strOut = '
                   <td class="enpRpt" style="text-align: center; width: 80pt;">&nbsp;</td>';
            }else {
               $strOut = '
                  <td class="enpRpt" style="text-align: right; width: 80pt;">'
                     .number_format($vVal, 0).'
                  </td>';
            }
            break;

         case CS_FT_CURRENCY:
            if ($vVal.''==''){
               $strOut = '
                   <td class="enpRpt" style="text-align: center; width: 80pt;">&nbsp;</td>';
            }else {
               $strOut = '
                  <td class="enpRpt" style="text-align: right; width: 80pt;">'
                     .number_format($vVal, 2).'
                  </td>';
            }
            break;

         case CS_FT_DDL:
            $strDDLTextFN = $strFN.'_ddlText';
            $strDDLVal = $mrec->$strDDLTextFN;
            if ($strDDLVal == ''){
               $strDDLVal = '&nbsp;';
            }else {
               $strDDLVal = htmlspecialchars($strDDLVal);
            }
            $strOut = '
               <td class="enpRpt" style="width: 140pt;">'
                  .$strDDLVal.'
               </td>';
            break;

         case CS_FT_DDLMULTI:
            $strDDLTextFN = $strFN.'_ddlMulti';
            $md = $mrec->$strDDLTextFN;
            $strOut = '
               <td class="enpRpt" style="width: 140pt;">'
                  .$md->strUL.'
               </td>';
            break;

         default:
            screamForHelp($enumType.': unexpected field type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      echoT($strOut);
   }

   function openMRecTable($strTableLabel, &$utable){
   //---------------------------------------------------------------------
   // http://stackoverflow.com/questions/5533636/add-horizontal-scrollbar-to-html-table
   //---------------------------------------------------------------------
      $lTableWidth = 200;
      $bReadOnly = $utable->bReadOnly;

         // table width
      if ($utable->lNumFields > 0){
         foreach ($utable->ufields as $ufield){
            $enumType = $ufield->enumFieldType;
            switch ($enumType){
               case CS_FT_CHECKBOX:    $lTableWidth +=  60;    break;
               case CS_FT_DATE:        $lTableWidth += 100;    break;
               case CS_FT_TEXT255:     $lTableWidth += 140;    break;
               case CS_FT_TEXT80:      $lTableWidth += 100;    break;
               case CS_FT_TEXT20:      $lTableWidth +=  80;    break;

               case CS_FT_TEXTLONG:    $lTableWidth += 220;    break;

               case CS_FT_CLIENTID:    $lTableWidth +=  80;    break;
               case CS_FT_INTEGER:     $lTableWidth +=  80;    break;
               case CS_FT_CURRENCY:    $lTableWidth +=  80;    break;
               case CS_FT_DDL:         $lTableWidth += 140;    break;
               case CS_FT_DDLMULTI:    $lTableWidth += 140;    break;

               case CS_FT_HEADING:
               case CS_FT_LOG:
                  break;

               default:
                  screamForHelp($enumType.': unexpected field type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }
         }
      }

      if ($utable->bCProg){
         if ($utable->bEnrollment){
            $utable->lExtraFields = 8;
            $lTableWidth += 500;
         }else {
            $utable->lExtraFields = 8;
            $utable->dTotDuration = 0.0;
            $lTableWidth += 600;
         }
      }else {
         $utable->lExtraFields = 4;
      }
      if (!$bReadOnly) --$utable->lExtraFields;

      echoT('
            <div id="mrecTable" style="width: 100%; overflow: auto; ">
            <table class="enpRpt" width="'.$lTableWidth.'">
               <tr>
                  <td colspan="'.($utable->lNumFields+$utable->lExtraFields).'" class="enpRptTitle">'
                     .$strTableLabel.'
                  </td>
               </tr>
               <tr>
                  <td class="enpRptLabel">Record ID</td>
                  <td class="enpRptLabel">&nbsp;</td>');
      if (!$bReadOnly){
         echoT(  '<td class="enpRptLabel">&nbsp;</td>');
      }

         // client program default fields
      if ($utable->bCProg){
         if ($utable->bEnrollment){
            echoT('
               <td class="enpRptLabel">
                  Start Date
               </td>
               <td class="enpRptLabel">
                  End Date
               </td>
               <td class="enpRptLabel">
                  Active?
               </td>
               <td class="enpRptLabel">
                  Add<br>Attendance
               </td>
               ');
         }else {
            echoT('
               <td class="enpRptLabel">
                  EnrollmentID
               </td>
               <td class="enpRptLabel">
                  Date
               </td>
               <td class="enpRptLabel">
                  Duration
               </td>
               <td class="enpRptLabel">
                  Case Notes
               </td>
               ');
         }
      }

         // fields
      if ($utable->lNumFields > 0){
         foreach ($utable->ufields as $ufield){
            $enumType = $ufield->enumFieldType;
            if (!($enumType==CS_FT_HEADING || $enumType==CS_FT_LOG)){
               if ($enumType==CS_FT_CURRENCY){
                  $strLabelExtra = ' ('.$ufield->ACO->strName.')';
               }else {
                  $strLabelExtra = '';
               }
               echoT('
                  <td class="enpRptLabel">'
                     .htmlspecialchars($ufield->pff_strFieldNameUser).$strLabelExtra.'
                  </td>');
            }
         }
      }
      echoT('</tr>');
   }

   function closeMRecTable(&$utable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

      if ($utable->bCProg && !$utable->bEnrollment){
         echoT('
            <tr>
               <td colspan="5" class="enpRptLabel">Total:</td>
               <td class="enpRptLabel" style="text-align: right;">'.number_format($utable->dTotDuration, 2).'</td>
               <td class="enpRptLabel" colspan="'.($utable->lNumFields + $utable->lExtraFields - 4).'">&nbsp;</td>
            </tr>');
      }
      echoT('</table></div><br><br>');
   }

