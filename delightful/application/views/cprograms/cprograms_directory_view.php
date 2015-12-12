<?php
   global $genumDateFormat;

   if ($lNumCProgs == 0){
      echoT('<br/><i>There are no client programs available for review.</i><br/>');
      return;
   }

   $params = array('enumStyle' => 'terse', 'crpt');
   $crpt = new generic_rpt($params);
   $crpt->strWidthLabel = '130pt';

   $attributes = new stdClass;
   foreach ($cprogs as $cprog){
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$cprog   <pre>');
echo(htmlspecialchars( print_r($cprog, true))); echo('</pre></font><br>');
die;
// ------------------------------------- */
   
      if ($cprog->bShowCProgLink){
         $lCProgID = $cprog->lKeyID;
         $attributes->divID        = 'cprog'.$lCProgID.'Div';
         $attributes->divImageID   = 'cprog'.$lCProgID.'DivImg';
         $attributes->bCloseDiv    = false;
         $attributes->bStartOpen   = false;
         openBlock(htmlspecialchars($cprog->strProgramName), '', $attributes);
         
         if ($cprog->lNumEnrolledCurrent > 0){
            $strLinkViewCurrentEnrolled = '&nbsp;'.strLinkView_CProgEnrollDir($lCProgID, true, 'View currently active', true);
         }else {
            $strLinkViewCurrentEnrolled = '';
         }
         if ($cprog->lNumEnrolledTot > 0){
            $strLinkViewTotEnrolled = '&nbsp;'.strLinkView_CProgEnrollDir($lCProgID, false, 'View all enrolled clients', true);
         }else {
            $strLinkViewTotEnrolled = '';
         }

         echoT(
             $crpt->openReport()

            .$crpt->openRow   ()
            .$crpt->writeLabel('Description:')
            .$crpt->writeCell (nl2br(htmlspecialchars($cprog->strDescription)))
            .$crpt->closeRow  ());

         echoT(
             $crpt->openRow   ()
            .$crpt->writeLabel('Time Frame:')
            .$crpt->writeCell (date($genumDateFormat, $cprog->dteStart).' - '.date($genumDateFormat, $cprog->dteEnd))
            .$crpt->closeRow  ());

         echoT(
             $crpt->openRow   ()
            .$crpt->writeLabel('# Active '.$cprog->strSafeEnrollLabel.':')
            .$crpt->writeCell (number_format($cprog->lNumEnrolledCurrent).$strLinkViewCurrentEnrolled)
            .$crpt->closeRow  ());

         echoT(
             $crpt->openRow   ()
            .$crpt->writeLabel('Total '.$cprog->strSafeEnrollLabel.':')
            .$crpt->writeCell (number_format($cprog->lNumEnrolledTot)
                              .$strLinkViewTotEnrolled)
            .$crpt->closeRow  ());

         echoT(
             $crpt->openRow   ()
            .$crpt->writeLabel('# Client '.$cprog->strSafeEnrollLabel.':')
            .$crpt->writeCell (number_format($cprog->lNumClientsCurrent).$strLinkViewCurrentEnrolled)
            .$crpt->closeRow  ());
            
         echoT(
             $crpt->openRow   ()
            .$crpt->writeLabel('# Total Clients:')
            .$crpt->writeCell (number_format($cprog->lNumClientsTot).$strLinkViewTotEnrolled)
            .$crpt->closeRow  ());
            
         echoT(
             $crpt->openRow   ()
            .$crpt->writeLabel('Total Hours:')
            .$crpt->writeCell (number_format($cprog->hourInfo->dTot, 2))
            .$crpt->closeRow  ());

         echoT(
             $crpt->openRow   ()
            .$crpt->writeLabel('Monthly View:')
            .$crpt->writeCell (
                   strLinkView_CProgDirAttendance($lCProgID, true, $cprog->strSafeAttendLabel, true).'&nbsp;'
                  .strLinkView_CProgDirAttendance($lCProgID, true, $cprog->strSafeAttendLabel, false)
                   )
            .$crpt->closeRow  ());
            
         if ($cprog->hourInfo->dTot > 0){
            $strEnumActivity = '
                  <table class="enpRpt">
                     <tr>
                        <td class="enpRptLabel">
                           Activity
                        </td>
                        <td class="enpRptLabel">
                           Duration
                        </td>
                     </tr>';
            foreach ($cprog->hourInfo->activities as $hInfo){
               $strEnumActivity .= '
                        <tr>
                           <td class="enpRpt">'
                              .htmlspecialchars($hInfo->strActivity).'
                           </td>
                           <td class="enpRpt" style="text-align: right;">'
                              .number_format($hInfo->dDuration, 2).'
                           </td>
                        </tr>';
            }
            $strEnumActivity .= '</table>';
            echoT(
                $crpt->openRow   ()
               .$crpt->writeLabel('Activities:')
               .$crpt->writeCell ($strEnumActivity)
               .$crpt->closeRow  ());
         }

         echoT($crpt->closeReport());
         $attributes->bCloseDiv    = true;
         closeBlock($attributes);
      }
   }


