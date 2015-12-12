<?php
   global $genumDateFormat;

   echoT($strHTMLSummary);

   $params = array('enumStyle' => 'terse');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '180pt';

   $attributes = new stdClass;
   $attributes->width = null;
   
   $bReadOnly = $utable->bReadOnly;

   if ($bMultiEntry && !$bReadOnly){
      $strLinkRem =
              '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
             .strLinkRem_UFMultiRecEntry($utable->enumTType, $lTableID, $lFID, $lRID, 'Remove', true,  true).'&nbsp;'
             .strLinkRem_UFMultiRecEntry($utable->enumTType, $lTableID, $lFID, $lRID, 'Remove', false, true);
   }else {
      $strLinkRem = '';      
   }
   if ($bReadOnly){
      $strLinkEdit = '';
   }else {
      $strLinkEdit =
                 strLinkEdit_UFMultiRecEntry($utable->enumTType, $lTableID, $lFID, $lRID, true,  'Edit record', ' id="editURec_' .$lTableID.'_'.$lRID.'" ', $lEnrollRecID).'&nbsp;'
                .strLinkEdit_UFMultiRecEntry($utable->enumTType, $lTableID, $lFID, $lRID, false, 'Edit record', ' id="editURec1_'.$lTableID.'_'.$lRID.'" ', $lEnrollRecID);
   }
   
   if ($bCProg && !$bEnrollment){
      $strCloneAttendance =
              '&nbsp;&nbsp;&nbsp;'
             .strLinkClone_CPAttendance($cprog->lKeyID, $lRID, 'Clone '.$cprog->strSafeAttendLabel, true).'&nbsp;'
             .strLinkClone_CPAttendance($cprog->lKeyID, $lRID, 'Clone '.$cprog->strSafeAttendLabel, false).'&nbsp'
             ;
   }else {
      $strCloneAttendance = '';
   }

   openBlock($strTableLabel, $strLinkEdit.$strCloneAttendance.$strLinkRem);
   echoT($clsRpt->openReport(800));

   if ($utable->lNumFields==0 && !$bCProg){
      echoT('<i>There is no data associated with this record!</i><br>');
   }else {
      echoT(
           $clsRpt->openRow()
          .$clsRpt->writeLabel('tableID:')
          .$clsRpt->writeCell(str_pad($lTableID, 5, '0', STR_PAD_LEFT))
          .$clsRpt->closeRow());
      echoT(
           $clsRpt->openRow()
          .$clsRpt->writeLabel('recordID:')
          .$clsRpt->writeCell(str_pad($lRID, 5, '0', STR_PAD_LEFT))
          .$clsRpt->closeRow());

         // add default fields if client program
      if ($bCProg){
         if ($bEnrollment){
            showCProgERecFields($clsRpt, $cprog, $erecs[0]);
         }else {
            showCProgARecFields($clsRpt, $cprog, $arecs[0]);
         }
      }

      if ($mRec->bRecordEntered){
         echoT(
              $clsRpt->openRow()
             .$clsRpt->writeLabel('Created:')
             .$clsRpt->writeCell(date($genumDateFormat.' H:i:s', $mRec->dteOrigin)
                         .' by '.htmlspecialchars($mRec->strUCFName.' '.$mRec->strUCLName))
             .$clsRpt->closeRow());
         echoT(
              $clsRpt->openRow()
             .$clsRpt->writeLabel('Last Updated:')
             .$clsRpt->writeCell(date($genumDateFormat.' H:i:s', $mRec->dteLastUpdate)
                         .' by '.htmlspecialchars($mRec->strULFName.' '.$mRec->strULLName))
             .$clsRpt->closeRow());
      }else {
         echoT(
              $clsRpt->openRow()
             .$clsRpt->writeLabel('Created:')
             .$clsRpt->writeCell('(has not been written)')
             .$clsRpt->closeRow());
      }

      if ($utable->lNumFields > 0){
         foreach ($utable->ufields as $ufield){
            displayMRField($clsRpt, $ufield, $mRec, $bCollapseHeadings, $bCollapseDefaultHide,
                $clientNames);
         }
      }
   }

   echoT($clsRpt->closeReport());
   closeBlock();


   function showCProgERecFields(&$clsRpt, &$cprog, &$erec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      echoT(
           $clsRpt->openRow()
          .$clsRpt->writeLabel('clientID:')
          .$clsRpt->writeCell(str_pad($erec->lClientID, 6, '0', STR_PAD_LEFT).'&nbsp;'
                       .strLinkView_ClientRecord($erec->lClientID, 'View client record', true))
          .$clsRpt->closeRow());
      echoT(
           $clsRpt->openRow()
          .$clsRpt->writeLabel('Client:')
          .$clsRpt->writeCell(htmlspecialchars($erec->strClientFName.' '.$erec->strClientLName))
          .$clsRpt->closeRow());
      echoT(
           $clsRpt->openRow()
          .$clsRpt->writeLabel('Starting Date:')
          .$clsRpt->writeCell(date($genumDateFormat, $erec->dteStart))
          .$clsRpt->closeRow());
      if (is_null($erec->dteMysqlEnd)){
         echoT(
           $clsRpt->openRow()
          .$clsRpt->writeLabel('Ending Date:')
          .$clsRpt->writeCell('<i>Ongoing</i>')
          .$clsRpt->closeRow());
      }else {
         echoT(
           $clsRpt->openRow()
          .$clsRpt->writeLabel('Ending Date:')
          .$clsRpt->writeCell(date($genumDateFormat, $erec->dteEnd))
          .$clsRpt->closeRow());
      }
      echoT(
           $clsRpt->openRow()
          .$clsRpt->writeLabel($cprog->strSafeAttendLabel.' Log:')
          .$clsRpt->writeCell(
                   strLinkView_CProgAttendanceViaCID(
                      $cprog->lAttendanceTableID, $erec->lKeyID, $erec->lClientID, 'View '.$cprog->strSafeAttendLabel.' records', true).'&nbsp;'
                  .strLinkView_CProgAttendanceViaCID(
                      $cprog->lAttendanceTableID, $erec->lKeyID, $erec->lClientID, 'View '.$cprog->strSafeAttendLabel.' records', false))
          .$clsRpt->closeRow());

      echoT(
           $clsRpt->openRow()
          .$clsRpt->writeLabel('Currently Active?:')
          .$clsRpt->writeCell(($erec->bCurrentlyEnrolled ? 'Yes' : 'No'))
          .$clsRpt->closeRow());
   }

   function showCProgARecFields(&$clsRpt, &$cprog, &$arec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      echoT(
           $clsRpt->openRow()
          .$clsRpt->writeLabel($cprog->strSafeEnrollLabel.' Record:')
          .$clsRpt->writeCell(str_pad($arec->lEnrollID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                    .strLinkView_CProgEnrollRec($cprog->lEnrollmentTableID,
                           $arec->lClientID, $arec->lEnrollID, 'View enrollment record', true)
                    )
          .$clsRpt->closeRow());

      echoT(
           $clsRpt->openRow()
          .$clsRpt->writeLabel('Attendance Log:')
          .$clsRpt->writeCell(
                   strLinkView_CProgAttendanceViaCID(
                      $cprog->lAttendanceTableID, $arec->lEnrollID, $arec->lClientID, 'View attendance records', true).'&nbsp;'
                  .strLinkView_CProgAttendanceViaCID(
                      $cprog->lAttendanceTableID, $arec->lEnrollID, $arec->lClientID, 'View attendance records', false))
          .$clsRpt->closeRow());

      echoT(
           $clsRpt->openRow()
          .$clsRpt->writeLabel('Attendance Date:')
          .$clsRpt->writeCell(date($genumDateFormat, $arec->dteAttendance))
          .$clsRpt->closeRow());
      echoT(
           $clsRpt->openRow()
          .$clsRpt->writeLabel('Duration:')
          .$clsRpt->writeCell(number_format($arec->dDuration, 2))
          .$clsRpt->closeRow());
      echoT(
           $clsRpt->openRow()
          .$clsRpt->writeLabel('Case Notes:')
          .$clsRpt->writeCell(nl2br(htmlspecialchars($arec->strCaseNotes)))
          .$clsRpt->closeRow());
   }

   function displayMRField(&$clsRpt, &$ufield, &$mRec, $bCollapseHeadings,
                 $bCollapseDefaultHide, $clientNames){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      $enumType = $ufield->enumFieldType;
      $strFN    = $ufield->strFieldNameInternal;

      if ($enumType==CS_FT_HEADING){
         echoT($clsRpt->openDivBlock(htmlspecialchars($ufield->pff_strFieldNameUser),
                              $bCollapseHeadings, $bCollapseDefaultHide, $strFN, $strFN.'Img', null));
         return;
      }

      $vValue = @$mRec->$strFN;
      $strCell = '';

      switch ($enumType){
         case CS_FT_CHECKBOX:
            $strCell = '<img src="'.($vValue ? IMGLINK_CHECKON : IMGLINK_CHECKOFF).'">';
            break;

         case CS_FT_DATE:
            if ($vValue == ''){
               $strCell = 'n/a';
            }else {
               $strCell = strNumericDateViaMysqlDate($vValue, $gbDateFormatUS);
            }
            break;

         case CS_FT_TEXTLONG:
            $strCell = nl2br(htmlspecialchars($vValue));
            break;

         case CS_FT_TEXT255:
         case CS_FT_TEXT80:
         case CS_FT_TEXT20:
            $strCell = htmlspecialchars($vValue);
            break;

         case CS_FT_CLIENTID:
            if ($vValue == ''){
               $strCell = '&nbsp;';
            }else {
               $lClientID = (int)$vValue;
               if ($lClientID > 0){
                  $strCell = number_format($vValue).'&nbsp;'
                       .strLinkView_ClientRecord($lClientID, 'View client record', true).'&nbsp;'
                       .htmlspecialchars($clientNames[$lClientID]);
               }else {
                  $strCell = '&nbsp;';
               }
            }
            break;
         case CS_FT_INTEGER:
            if ($vValue == ''){
               $strCell = '&nbsp;';
            }else {
               $strCell = number_format($vValue);
            }
            break;

         case CS_FT_HEADING:
            break;

         case CS_FT_CURRENCY:
            if ($vValue == ''){
               $strCell = '&nbsp;';
            }else {
               $strCell = number_format($vValue, 2);
            }
            break;

         case CS_FT_DDL:
            $strFNDDL = $strFN.'_ddlText';
            $strCell = htmlspecialchars($mRec->$strFNDDL);
            break;

         case CS_FT_DDLMULTI:
            $strDDLTextFN = $strFN.'_ddlMulti';
            $md = $mRec->$strDDLTextFN;
            $strCell = $md->strUL;
            break;

         case CS_FT_LOG:
            $strCell = nl2br(htmlspecialchars($vValue));
            break;

         default:
            screamForHelp($enumType.': invalid field type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

      echoT(
           $clsRpt->openRow()
          .$clsRpt->writeLabel(htmlspecialchars($ufield->pff_strFieldNameUser).':')
          .$clsRpt->writeCell($strCell)
          .$clsRpt->closeRow());

   }


