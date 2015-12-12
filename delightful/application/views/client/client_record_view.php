<?php
   $clsDateTime = new dl_date_time;
   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '130pt';
   $client = $clsClient->clients[0];
   showClientInfo           ($clsRpt, $lCID, $clsClient, $clsDateTime, true, null, false);

   openClientServices();

      // custom forms
   if ($lNumCustomForms > 0){
      showCustomForms($lCID, $cForms);
   }

      // client programs
   if ($lNumCProgs > 0){
      showClientPrograms($lCID, $lNumCProgs, $cProgs);
   }

      // pre/post tests
   if ($lTotTests > 0){
      showPrePostTests($lCID, $ppcats);
   }

   closeClientServices();

      // personalized tables
   showCustomClientTableInfo($strPT, $lNumPTablesAvail);

      // sponsorship
   if (bAllowAccess('showSponsors')){
      showClientSponsorInfo($clsRpt, $lCID, $clsClient);
   }
   showClientStatusInfo     ($clsRpt, $lCID, $clsClient, $clientStatus, $lNumClientStatus);
   showGroupInfo            ($lCID, $client->strSafeName, $lNumGroups, $groupList,
                             $inGroups, $lCntGroupMembership,
                             CENUM_CONTEXT_CLIENT, 'cRecView');

   if ($bShowEMR){
      showClientMedicalInfo($clsRpt, $lCID, $clsClient, $emr);
   }

   showImageInfo            (CENUM_CONTEXT_CLIENT, $lCID, $client->cv_strVocClientS.' Images',
                             $images, $lNumImages, $lNumImagesTot);
   showDocumentInfo         (CENUM_CONTEXT_CLIENT, $lCID, $client->cv_strVocClientS.' Documents',
                             $docs, $lNumDocs, $lNumDocsTot);
//   showReminderBlock        ($clsRem, $lCID, CENUM_CONTEXT_CLIENT);
   showClientXfers          ($clsRpt, $lCID, $clientXfers, $lNumClientXfers);
   showClientENPStats       ($clsRpt, $client);

function openClientServices(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 1200;
   $attributes->divID        = 'clientServices';
   $attributes->divImageID   = 'clientServicesDivImg';
   $attributes->bStartOpen   = true;
   openBlock('Client Services', '', $attributes);
}

function closeClientServices(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showClientMedicalInfo(&$clsRpt, $lClientID, &$clsClient, &$emr){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'clientMed';
   $attributes->divImageID   = 'clientMedDivImg';
   openBlock('Health/Medical', '', $attributes);

   echoT($clsRpt->openReport());

   $strLinks = strLinkAdd_EMR_Measurement($lClientID, 'Add Measurements', true).'&nbsp;'
              .strLinkAdd_EMR_Measurement($lClientID, 'Add Measurements', false);
   if ($emr->lNumMeasure == 0){
      $strCount = '<i>No measurements recorded</i>';
   }else {
      $strCount = $emr->lNumMeasure.' measurement'.($emr->lNumMeasure==1 ? '' : 's');
      $strLinks = strLinkView_EMRMeasurements($lClientID, 'View Measurements', true).'&nbsp;'
                 .strLinkView_EMRMeasurements($lClientID, 'View Measurements', false).'&nbsp;&nbsp;&nbsp;'
                 .$strLinks;
   }

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Client Measurements:', '', 'vertical-align: bottom;')
      .$clsRpt->writeCell($strCount.'&nbsp;&nbsp;&nbsp;'.$strLinks)
      .$clsRpt->closeRow  ());

   echoT($clsRpt->closeReport());

   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showClientXfers(&$clsRpt, $lCID, &$clientXfers, $lNumClientXfers){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;

   $attributes = new stdClass;
   $attributes->lTableWidth  = 1200;
   $attributes->divID        = 'clientXfer';
   $attributes->divImageID   = 'clientXferDivImg';
   openBlock('Client Transfers', strLinkSpecial_XferClient($lCID, 'Transfer client', true).'&nbsp'
                                .strLinkSpecial_XferClient($lCID, 'Transfer client', false),
                                $attributes);
   if ($lNumClientXfers==0){
      echoT('<i>There are no transfers associated with this client.</i>');
   }else {
      echoT('<table>
                <tr>
                   <td>
                      <b>Date</b>
                   </td>
                   <td colspan="2">
                      <b>Action</b>
                   </td>
                </tr>');
      foreach ($clientXfers as $xfer){
         echoT('<tr>
                   <td style="padding-right: 10pt; vertical-align: top;">'
                      .date($genumDateFormat, $xfer->dteEffective).'
                   </td>');
         $strChanges = '';
         if ($xfer->locOld != $xfer->locNew){
            $strChanges .= '<b>Location:</b> '.htmlspecialchars($xfer->locOld.' => '.$xfer->locNew).'<br>';
         }
         if ($xfer->statCatOld != $xfer->statCatNew){
            $strChanges .= '<b>Status category:</b> '.htmlspecialchars($xfer->statCatOld.' => '.$xfer->statCatNew).'<br>';
         }
         if ($xfer->vocOld != $xfer->vocNew){
            $strChanges .= '<b>Vocabulary:</b> '.htmlspecialchars($xfer->vocOld.' => '.$xfer->vocNew).'<br>';
         }
         echoT('
                   <td style="padding-right: 10pt; vertical-align: top;">'
                      .$strChanges.'
                   </td>
                </tr>');
      }
      echoT('</table>');
   }
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showClientENPStats(&$clsRpt, &$client){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'clientENP';
   $attributes->divImageID   = 'clientENPDivImg';
   openBlock('Record Information', '', $attributes);
   echoT(
      $clsRpt->showRecordStats($client->dteOrigin,
                            $client->ucstrFName.' '.$client->ucstrLName,
                            $client->dteLastUpdate,
                            $client->ulstrFName.' '.$client->ulstrLName,
                            $clsRpt->strWidthLabel));
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showPrePostTests($lCID, &$ppcats){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $strOut = '';

   $attributes = new stdClass;
   $attributes->divID        = 'clientPPTests';
   $attributes->divImageID   = 'clientPPTestsDivImg';
   $attributes->bStartOpen   = false;
   $attributes->lTableWidth  = 1100;
   $attributes->lUnderscoreWidth = 350;

   openBlock('Pre/Post Tests', '', $attributes);
   foreach ($ppcats as $ppcat){

      $pcatAttributes = new stdClass;
      $pcatAttributes->bStartOpen   = false;
      $pcatAttributes->lTableWidth  = 700;
      $pcatAttributes->lUnderscoreWidth = 320;
      if ($ppcat->lNumPPTests > 0){
         $lCatID = $ppcat->lKeyID;
         $pcatAttributes->divID        = 'pCatDiv'.$lCatID;
         $pcatAttributes->divImageID   = 'pCatDivImg'.$lCatID;
         $pcatAttributes->bCloseDiv    = false;
         openBlock(htmlspecialchars($ppcat->strListItem), '', $pcatAttributes);
         foreach ($ppcat->pptests as $pptest){
            if ($pptest->bShowTest){
               if ($pptest->lNumQuest > 0){
                  $strOut .=
                     '<table border="0">
                        <tr>
                           <td colspan="5"><b>'
                              .htmlspecialchars($pptest->strTestName).'</b>
                           </td>
                        <tr>';
                  $strOut .= strTestsTaken($lCID, $pptest);
                  $strOut .= '</table>';
               }
               echoT($strOut); $strOut = '';
            }
         }
         $pcatAttributes->bCloseDiv = true;
         closeBlock($pcatAttributes);
      }
   }

   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function strTestsTaken($lClientID, &$pptest){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;

   $strOut = '';
   $lPPTestID = $pptest->lKeyID;
   $lNumQuest = $pptest->lNumQuest;

   $strRowStart =
         '<tr>
             <td style="width: 15pt;">&nbsp;</td>';

   if ($pptest->lNumTests == 0){
      $strOut .=
          $strRowStart.'
             <td colspan="4">Test not recorded for this client.</td>
          </tr>';
   }else {
      foreach ($pptest->testInfo as $ti){
         $strOut .=
            $strRowStart.'
             <td>'.strLinkEdit_CPPTestResults($ti->lKeyID, $lClientID, $lPPTestID, 'Edit', true).'
             <td>'.strLinkRem_CPPTestResults($ti->lKeyID, $lClientID, 'Remove test scores', true, true).'
             <td>Pretest: '.date($genumDateFormat, $ti->dtePreTest)
                .':
             </td>
             <td style="text-align: right;">
                <b>'.number_format(($ti->lNumRightPre/$lNumQuest)*100, 1).'%</b>
             </td></tr>
          <tr>'.$strRowStart.'
             <td colspan="2">&nbsp;</td>
             <td>Posttest: '.date($genumDateFormat, $ti->dtePostTest)
                .':
             </td>
             <td style="text-align: right;">
                <b>'.number_format(($ti->lNumRightPost/$lNumQuest)*100, 1).'%</b>
             </td>
          </tr>'
             ;

      }
   }

   $strOut .=
          $strRowStart.'
             <td colspan="4" nowrap>'
                 .strLinkAdd_CPPTestResults($lClientID, $lPPTestID, 'Add new test results', true).'&nbsp;'
                 .strLinkAdd_CPPTestResults($lClientID, $lPPTestID, 'Add new test results', false).'
             </td>
          </tr>';

   return($strOut);
}

function showClientPrograms($lClientID, $lNumCProgs, $cProgs){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;

   $attributes = new stdClass;
   $attributes->divID        = 'clientProgs';
   $attributes->divImageID   = 'clientProgsDivImg';
   $attributes->bStartOpen   = false;
   $attributes->lTableWidth  = 1100;
   $attributes->lUnderscoreWidth = 350;

   openBlock('Programs <span style="font-size: 9pt;">('.$lNumCProgs.')</span>', '', $attributes);

   $attributes->lTableWidth      = 900;
   $attributes->lUnderscoreWidth = 373;
   $attributes->lTitleFontSize   = 10;

   foreach ($cProgs as $cprog){
      if ($cprog->bShowCProgLink){
         $lCProgID = $cprog->lKeyID;
      
            // set up link to utility that allows the user to
            // reassign an attendance to a different enrollment
         $strLinkMoveAttend = '';
         $lNumEnroll = $cprog->lNumEnrollments;
         if ($lNumEnroll > 1){
            if ($cprog->lTotAttend > 0){
               $strLinkMoveAttend =
                    strLinkUtil_CProgMoveAttend($lClientID, $lCProgID, 'Transfer attendance between enrollments', true).'&nbsp;'
                   .strLinkUtil_CProgMoveAttend($lClientID, $lCProgID, 'Transfer attendance between enrollments', false);
            }
         }
      
         $strOut = '';
         $strProgSafeName = htmlspecialchars($cprog->strProgramName);
         $attributes->divID       = 'clientProg'.$lCProgID;
         $attributes->divImageID  = 'clientProg'.$lCProgID.'DivImg';
         $attributes->bCloseDiv   = false;

            // it's the little things that make a house a home
         openBlock($strProgSafeName.' <span style="font-size: 9pt;">('
                  .$lNumEnroll.' enrollment'.($lNumEnroll==1 ? '' : 's').')</span>', '', $attributes);

         $strOut .= '<b><span style="vertical-align: bottom;"> '.$strProgSafeName.'</b>'."\n";
         if ($cprog->bEnrolled){
            $strOut .= '<br>Currently enrolled:<br>
                         <ul style="margin-top: 0px;">';
            $lETableID = $cprog->lEnrollmentTableID;
            $lATableID = $cprog->lAttendanceTableID;
            $bAnyActiveEnrollment = false;
            foreach ($cprog->erecs as $erec){
               $lERecID = $erec->lKeyID;
               $bCurrentlyEnrolled = $erec->bCurrentlyEnrolled;
               if ($bCurrentlyEnrolled) $bAnyActiveEnrollment = true;
               $strOut .= '<li style="margin-bottom: 6px;">'
                            .($bCurrentlyEnrolled ? '<font>' : '<font style="color: #999;">')
                            .strLinkView_UFMFRecordViaRecID($lETableID, $lClientID, $lERecID, 'View enrollment record', true).'&nbsp;'
                            .date($genumDateFormat, $erec->dteStart).' - ';
               if (is_null($erec->dteMysqlEnd)){
                  $strOut .= '<i>ongoing</i>'."\n";
               }else {
                  $strOut .= date($genumDateFormat, $erec->dteEnd);
               }
               if (!$bCurrentlyEnrolled) $strOut .= ' <i>(inactive)</i>';

               if ($erec->lNumAttend > 0){
                  $strViewA =
                      '&nbsp;'
                     .strLinkView_UFMFRecordsViaFID(CENUM_CONTEXT_CLIENT, $lATableID, $lClientID,
                                 'View attendance records', true, '', $lERecID)
                     .'&nbsp;&nbsp;&nbsp;';
               }else {
                  $strViewA = '';
               }
               $strOut .= '<br>Attendance records: '.$erec->lNumAttend.$strViewA.'&nbsp;&nbsp;&nbsp;&nbsp;';
               if ($bCurrentlyEnrolled){
                  $strOut .=
                          strLinkAdd_CProgAttendance(false, $lClientID, $lCProgID, $lERecID, 'Add attendance record', true).'&nbsp;'
                         .strLinkAdd_CProgAttendance(false, $lClientID, $lCProgID, $lERecID, 'Add attendance record', false);
               }
               $strOut .= '</li>'."\n";
               $strOut .= '</font>';
            }
            if (!$bAnyActiveEnrollment){
               $strOut .= '<li style="list-style-type: none;"><br>'
                      .strLinkAdd_CProgEnrollment($lClientID, $lCProgID, 'Add additional enrollment', true).'&nbsp;'
                      .strLinkAdd_CProgEnrollment($lClientID, $lCProgID, 'Add additional enrollment', false).'
                       </li>
                         </ul></span>';
            }
         }else {
            $strOut .= '<span style="text-align: bottom;">: Not enrolled. '
                      .strLinkAdd_CProgEnrollment($lClientID, $lCProgID, 'Enroll this client', true).'&nbsp;'
                      .strLinkAdd_CProgEnrollment($lClientID, $lCProgID, 'Enroll this client', false).'</span><br>';
         }
         $strOut .= $strLinkMoveAttend;
         echoT($strOut);
         $attributes->bCloseDiv = true;
         closeBlock($attributes);
      }
   }

   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showCustomForms($lClientID, &$cForms){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->divID        = 'clientCForms';
   $attributes->divImageID   = 'clientCFormsDivImg';
   $attributes->bStartOpen   = true;
   $attributes->lUnderscoreWidth = 350;
   openBlock('Custom Client Forms', '', $attributes);

   $strOut = '';
   foreach ($cForms as $cform){
      $lCFID = $cform->lKeyID;
      $strLabel = ($cform->bAnyTablesMulti ? 'Add new' : 'Updated');
      $strOut .= strLinkAdd_CustomFormDataEntry($lCFID, $lClientID, $strLabel.' form', true).'&nbsp;'
                .strLinkAdd_CustomFormDataEntry($lCFID, $lClientID, $strLabel.' form', false).'&nbsp;'
                .'<b>'.htmlspecialchars($cform->strFormName).'</b><br>';
      if ($cform->lNumLogEntries > 0){
         $strOut .= strCFormLog($cform);
      }else {
         $strOut .= '<i><span style="color: #999;">No forms have been submitted.</i></span><br>';
      }

   }
   echoT($strOut);

   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function strCFormLog($cform){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;
   $strOut =
   '<table style="border: 1px solid black; margin-top: 6px;">
      <tr>
         <td style="background-color: #d2cfdc;" colspan="2">
            <b>Form Log
         </td>
      </tr>
      <tr>
         <td class="" style="font-size: 8pt; width: 100pt; background-color: #e2e1e7;">'
            .($cform->bAnyTablesMulti ? 'Added By' : 'Updated By').'
         </td>
         <td class="" style="font-size: 8pt;background-color: #e2e1e7;">
            Date
         </td>
      </tr>';
      
   foreach ($cform->formLog as $fl){
      $strOut .= '
      <tr>
         <td class="" style="font-size: 8pt; width: 100pt; background-color: #f6f8e9;">'
            .htmlspecialchars($fl->strUCFName.' '.$fl->strUCLName).'
         </td>
         <td class="" style="font-size: 8pt; background-color: #f6f8e9;">'
            .date($genumDateFormat.' H:i:s', $fl->dteOrigin).'
         </td>
      </tr>';
   }


   $strOut .= '</table>';
   return($strOut);
}


function showCustomClientTableInfo($strPT, $lNumPTablesAvail){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 1000;
   $attributes->divID        = 'clientCTable';
   $attributes->divImageID   = 'clientCTableDivImg';
   $attributes->bStartOpen   = false;
   openBlock('Personalized Tables <span style="font-size: 9pt;">('.$lNumPTablesAvail.')</span>', '', $attributes);

   echoT($strPT);

   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showClientSponsorInfo($clsRpt, $lCID, $clsClient){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;

   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'clientSpon';
   $attributes->divImageID   = 'clientSponDivImg';
   openBlock('Sponsorships', '', $attributes);

   if ($clsClient->clients[0]->lNumSponsors==0){
      echoT('<i>There are currently no sponsors for this '.$clsClient->clients[0]->cv_strVocClientS.'</i><br>');
   }else {
      $clsRpt->openReport();
      foreach($clsClient->clients[0]->sponsors as $clsCSpon){
         $bInactive = $clsCSpon->bInactive;
         if ($bInactive){
            $strSpanStart = '<span style="color: #999;"><i>';
            $strSpanStop  = '</i></span>';
            $strInactive  = ' (inactive since '.date($genumDateFormat, $clsCSpon->dteInactive).')';
         }else {
            $strSpanStart =
            $strSpanStop  =
            $strInactive  = '';
         }
         echoT(strLinkView_Sponsorship($clsCSpon->lKeyID, 'View sponsorship', true)
             .$strSpanStart
             .str_pad($clsCSpon->lKeyID, 5, '0', STR_PAD_LEFT).' '
             .$clsCSpon->strSafeNameFL
             .$strInactive
             .$strSpanStop
             .'<br>');
      }
      $clsRpt->closeReport();
   }
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showClientStatusInfo($clsRpt, $lCID, $clsClient, &$clientStatus, $lNumClientStatus){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;

   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'clientStat';
   $attributes->divImageID   = 'clientStatDivImg';
   openBlock($clsClient->clients[0]->cv_strVocClientS.' Status History',
             strLinkView_ClientStatusHistory($lCID, 'View status history', true),
             $attributes);

   if ($lNumClientStatus==0){
      echoT('<i>There are no status records for this '.$clsClient->clients[0]->cv_strVocClientS.'</i><br>');
      closeBlock();
      return;
   }
   showStatusHistory(false, false, $clientStatus, $lNumClientStatus, $lCID, "80%");

   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}


