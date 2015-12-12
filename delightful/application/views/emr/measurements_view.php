   <?php

   global $genumDateFormat, $gbMetric;

   echoT(strLinkAdd_EMR_Measurement($lClientID, 'Add new measurement', true).'&nbsp;'
       .strLinkAdd_EMR_Measurement($lClientID, 'Add new measurement', false).'<br>');

   if ($lNumMeasure <= 0){
      echoT('<br><i>There are no measurement records for client <b>'.$client->strSafeName.'</b>.</i><br><br>');
      return;
   }

   openMeasureTable($client);
   showMeasurements($measurements);
   closeMeasureTable();

   function openMeasureTable(&$client){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('<br>
         <table class="enpRptC">
            <tr>
               <td class="enpRptTitle" colspan="11" style="border: 1px solid black;">
                  Measurements for '.$client->strSafeName.'
               </td>
            </tr>');

      echoT('
            <tr>
               <td class="enpRptLabel" style="text-align: center;">
                  &nbsp;
               </td>
               <td class="enpRptLabel" style="text-align: center; border-right: 1px solid black;">
                  Date
               </td>
               <td class="enpRptLabel" style="text-align: center;">
                  Height
               </td>
               <td class="enpRptLabel" style="text-align: center; border-right: 1px solid black;">
                  %
               </td>
               <td class="enpRptLabel" style="text-align: center;">
                  Weight
               </td>
               <td class="enpRptLabel" style="text-align: center; border-right: 1px solid black;">
                  %
               </td>
               <td class="enpRptLabel" style="text-align: center;">
                  BMI
               </td>
               <td class="enpRptLabel" style="text-align: center; border-right: 1px solid black;">
                  %
               </td>
               <td class="enpRptLabel" style="text-align: center;">
                  OFC
               </td>
               <td class="enpRptLabel" style="text-align: center; border-right: 1px solid black;">
                  %
               </td>
               <td class="enpRptLabel" style="text-align: center; border-right: 1px solid black; width: 200pt;">
                  Notes
               </td>
            </tr>');
   }

   function formatMeasure($sngMeasure, $sngPercentile, $bLength, $bIncludeFootInch, $lFeet, $lInch, &$strMeasureP){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      global $genumDateFormat, $gbMetric;
      
      $strMeasure = $strMeasureP = '';
      if (is_null($sngMeasure)){
         $strMeasure  = '-';
         $strMeasureP = '-';
      }else {      
         if ($gbMetric){
            if ($bLength){
               $strMeasure  = number_format($sngMeasure, 1).' cm';
            }else {
               $strMeasure  = number_format($sngMeasure, 1).' kg';
            }
         }else {
            if ($bLength){
               if ($bIncludeFootInch){
                  $strMeasure = $lFeet.'\' '.$lInch.'" ('.number_format($sngMeasure, 1).'")';
               }else {
                  $strMeasure = number_format($sngMeasure, 1).'"';
               }
            }else {
               $strMeasure  = number_format($sngMeasure, 1).' lb';
            }
         }
      }
      if (is_null($sngPercentile)){
         $strMeasureP = '-';
      }else {
         $strMeasureP = number_format($sngPercentile, 1).'%';
         if (($sngPercentile <= 5) || ($sngPercentile >= 95)){
            $strMeasureP = '<font color="red">'.$strMeasureP.'</font>';
         }
      }
      
      return($strMeasure);
   }

   function showMeasurements(&$measurements){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat, $gbMetric;
      
      $dummy = 0;
      foreach ($measurements as $measure){
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$measure   <pre>');
echo(htmlspecialchars( print_r($measure, true))); echo('</pre></font><br>');
// ------------------------------------- */
      
         $lMeasureID = $measure->lKeyID;
         if ($gbMetric){
            $strHeight = formatMeasure($measure->sngHeightCM, $measure->sngHeightP, true, false, $dummy, $dummy, $strHeightP);
            $strWeight = formatMeasure($measure->sngWeightKilos, $measure->sngWeightP, false, false, $dummy, $dummy, $strWeightP);
            $strOFC    = formatMeasure($measure->sngHeadCircCM, $measure->sngOFCP, true, false, $dummy, $dummy, $strOFCP);
         }else {
            $strHeight = formatMeasure($measure->sngHeightIn, $measure->sngHeightP, true, true, 
                                $measure->lHeightFt, $measure->lHeightFtIn, $strHeightP);
            $strWeight = formatMeasure($measure->sngWeightLBS, $measure->sngWeightP, false, false, $dummy, $dummy, $strWeightP);
            $strOFC    = formatMeasure($measure->sngHeadCircIn, $measure->sngOFCP, true, false, $dummy, $dummy, $strOFCP);
         }
         
         if (is_null($measure->sngBMIP)){
            $strBMIP = '-';
         }else {
            $strBMIP = number_format($measure->sngBMIP, 1);
         }
         
         echoT('
               <tr class="makeStripe">
                  <td class="enpRpt" style="text-align: center;">'
                     .strLinkEdit_EMRMeasurementAuction($lMeasureID, 'Edit measurement record', true).'
                  </td>
                  <td class="enpRpt" style="text-align: center; border-right: 1px solid black;">'
                     .date($genumDateFormat, $measure->dteMeasurement).'
                  </td>');

         echoT('
               <td class="enpRpt" style="text-align: center;">'
                  .$strHeight.'
               </td>
               <td class="enpRpt" style="text-align: center; border-right: 1px solid black;">'
                  .$strHeightP.'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .$strWeight.'
               </td>
               <td class="enpRpt" style="text-align: center; border-right: 1px solid black;">'
                  .$strWeightP.'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .number_format($measure->sngBMI, 1).'
               </td>
               <td class="enpRpt" style="text-align: center; border-right: 1px solid black;">'
                  .$strBMIP.'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .$strOFC.'
               </td>
               <td class="enpRpt" style="text-align: center; border-right: 1px solid black;">'
                  .$strOFCP.'
               </td>');

         echoT('
               <td class="enpRpt" style="border-right: 1px solid black; width: 200pt;">'
                  .nl2br(htmlspecialchars($measure->strNotes)).'
               </td>
            </tr>');

      }
   }




   function closeMeasureTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('</table>');
   }

