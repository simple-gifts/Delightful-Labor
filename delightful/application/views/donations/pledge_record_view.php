<?php
//   $clsDateTime = new dl_date_time;
   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '130pt';

   showPledgeInfo($clsRpt, $lPledgeID, $pledge);
   showPledgeSchedule($clsRpt, $lPledgeID, $pledge, $schedule);
//   showPledgeFulfillment($clsRpt, $lPledgeID, $pledge);
   showENPPledgeStats($clsRpt, $pledge);


   function showPledgeInfo(&$clsRpt, $lPledgeID, &$pledge){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      openBlock('Pledge'."\n",
                  strLinkEdit_Pledge($lPledgeID, 'Edit Pledge', true).'&nbsp;'
                 .strLinkEdit_Pledge($lPledgeID, 'Edit Pledge', false).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'."\n"
                 .strLinkRem_Pledge ($lPledgeID, 'Delete Pledge', true, true)."\n");
      echoT(
          $clsRpt->openReport());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Pledge ID:')
         .$clsRpt->writeCell (str_pad($lPledgeID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Commitment:')
         .$clsRpt->writeCell ($pledge->strACOCurSymbol.' '
                     .number_format($pledge->curCommitment, 2).'&nbsp;'.$pledge->strFlagImg.' <i>per payment</i>')
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('# Projected Payments:')
         .$clsRpt->writeCell ($pledge->lNumCommit)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Expected Total:')
         .$clsRpt->writeCell ($pledge->strACOCurSymbol.' '
                     .number_format($pledge->curCommitment*$pledge->lNumCommit, 2).'&nbsp;'.$pledge->strFlagImg)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Current Fulfillment:')
         .$clsRpt->writeCell ($pledge->strACOCurSymbol.' '
                     .number_format($pledge->curTotFulfill, 2).'&nbsp;'.$pledge->strFlagImg)
         .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Starting Date:')
      .$clsRpt->writeCell (date($genumDateFormat, $pledge->dteStart))
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Frequency:')
      .$clsRpt->writeCell ($pledge->enumFreq)
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Account:')
      .$clsRpt->writeCell (htmlspecialchars($pledge->strAccount))
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Campaign:')
      .$clsRpt->writeCell (htmlspecialchars($pledge->strCampaign))
      .$clsRpt->closeRow  ());

      echoT(
         $clsRpt->closeReport(''));
      closeBlock();

   }

   function showPledgeSchedule(&$clsRpt, $lPledgeID, &$pledge, &$schedule){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'schDiv';
      $attributes->bStartOpen   = true;
      $attributes->divImageID   = 'schDivImg';

      openBlock('Schedule'."\n", '', $attributes);
      echoT('
         <table class="enpRpt">
            <tr>
               <td class="enpRptLabel">
                  &nbsp;
               </td>
               <td class="enpRptLabel" style="vertical-align: bottom;">
                  Date
               </td>
               <td class="enpRptLabel" style="vertical-align: bottom;">
                  Payments
               </td>
               <td class="enpRptLabel" style="vertical-align: bottom;">
                  Cumulative<br>Obligation
               </td>
               <td class="enpRptLabel" style="vertical-align: bottom;">
                  Cumulative<br>Fulfillment
               </td>
               <td class="enpRptLabel" style="vertical-align: bottom;">
                  Balance
               </td>
            </tr>');

      $idx = 1; $curCum = 0.0; $curCumActual = 0.0;
      foreach ($schedule as $sch){
         $curCum += $pledge->curCommitment;
         $strFTable = strFulfillmentTable($lPledgeID, $sch, $curCumActual);
         $curBalance = $curCumActual-$curCum;
         if ($curBalance < 0.001){
            $strBalColor = ' color: red; ';
         }else {
            $strBalColor = '';
         }
         echoT('
               <tr class="makeStripe">
                  <td class="enpRpt" style="text-align: center;">'
                     .$idx.'
                  </td>
                  <td class="enpRpt">'
                     .date($genumDateFormat, $sch->pDate).'
                  </td>
                  <td class="enpRpt">'
                     .$strFTable.'
                  </td>
                  <td class="enpRpt" style="text-align: right;">'
                     .number_format($curCum, 2).'
                  </td>
                  <td class="enpRpt" style="text-align: right;">'
                     .number_format($curCumActual, 2).'
                  </td>
                  <td class="enpRpt" style="text-align: right; '.$strBalColor.'">'
                     .number_format($curBalance, 2).'
                  </td>
               </tr>');
         ++$idx;
      }

      echoT('
         </table>');
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function showPledgeFulfillment(&$clsRpt, $lPledgeID, &$pledge){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'fDiv';
      $attributes->divImageID   = 'fDivImg';

      openBlock('Fulfillment'."\n", '', $attributes);
      echoT(
          $clsRpt->openReport());

      echoT(
         $clsRpt->closeReport(''));
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function showENPPledgeStats(&$clsRpt, &$pledge){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'grecDiv';
      $attributes->divImageID   = 'grecDivImg';
      openBlock('Record Information', '', $attributes);
      echoT(
         $clsRpt->showRecordStats($pledge->dteOrigin,
                               $pledge->strStaffCFName.' '.$pledge->strStaffCLName,
                               $pledge->dteLastUpdate,
                               $pledge->strStaffLFName.' '.$pledge->strStaffLLName,
                               $clsRpt->strWidthLabel));
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }





