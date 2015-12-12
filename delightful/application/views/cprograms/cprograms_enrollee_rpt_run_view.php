<?php

   if ($lNumEnrollees == 0){
      echoT('<br><br>
           <font style="font-size: 11pt;"><i>There are no clients that meet your search criteria.</i><br><br></font>');
      return;           
   }
   
   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '130pt';
   $clsRpt->bValueEscapeHTML = false;
   
   
   openBlock('Enrollees', '');
   echoT(
          $clsRpt->openReport());;

   echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Time Frame:')
         .$clsRpt->writeCell ($dateRange)
         .$clsRpt->closeRow  ());
    
   $strCProgs = '<ul style="margin-top: 0px;">';    
   foreach ($cprogs as $cprog){
      $strCProgs .= '<li style="margin-left: -20px;">'.htmlspecialchars($cprog->strProgramName).'</li>'."\n";
   }
   $strCProgs .= '</ul>';
   echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Clients Enrolled In:')
         .$clsRpt->writeCell ($strCProgs)
         .$clsRpt->closeRow  ());
   
   
   echoT($clsRpt->closeReport());
   closeBlock();
   
   
   
   echoT('<br>
      <table class="enpRptC" style="width: 550pt;">
         <tr>
            <td class="enpRptTitle" colspan="7">
               Client Enrollment Report
            </td>
         </tr>');
         
   echoT('
         <tr>
            <td class="enpRptLabel" style="width: 50pt;">
               Client ID
            </td>
            <td class="enpRptLabel">
               Client
            </td>
            <td class="enpRptLabel">
               Program/Enrollment
            </td>
         </tr>');
         
   foreach ($enrollees as $erec){
      $lClientID = $erec->lClientID;
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="width: 50pt; text-align: center;">'
               .strLinkView_ClientRecord($lClientID, 'View Client Record', true)
               .str_pad($lClientID, 5, '0', STR_PAD_LEFT).'
            </td>
            <td class="enpRpt" style="width: 160pt;">'
               .htmlspecialchars($erec->strCLName.', '.$erec->strCFName).'
            </td>
            <td class="enpRpt">'
               .strProgramTable($lClientID, $erec->programs).'
            </td>
         </tr>');
   }            
         
   echoT('</table><br><br>');
   
   function strProgramTable($lClientID, $programs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      
      $strOut = '<table class="enpRpt" width="100%">';
      
      $strOut .=
         '<tr>
             <td class="enpRptLabel" style="font-size: 8pt;">
                Program
             </td>
             <td class="enpRptLabel" style="font-size: 8pt;">
                Enrollment ID
             </td>
             <td class="enpRptLabel" style="font-size: 8pt;">
                Dates
             </td>
          </tr>';
          
      foreach ($programs as $prog){
         $lCProgID = $prog->lCProgID;
         $lERecID  = $prog->lERecID;
         $dteEnd   = $prog->dteEnd;
         if (is_null($dteEnd)){
            $strDateEnd = '<i>ongoing</i>';
         }else {
            $strDateEnd = date($genumDateFormat, $dteEnd);
         }
         $strOut .=
            '<tr>
                <td class="enpRpt" style="font-size: 8pt; width: 140pt;">'
                   .htmlspecialchars($prog->strProgName).'
                </td>
                <td class="enpRpt" style="font-size: 8pt; width: 60pt; text-align: center;">'
                   .strLinkView_CProgEnrollRec($prog->lETableID, $lClientID, $lERecID, 'View enrollment record', true)
                   .str_pad($lERecID, 5, '0', STR_PAD_LEFT).'                   
                </td>
                <td class="enpRpt" style="font-size: 8pt;">'
                   .date($genumDateFormat, $prog->dteStart).' - '.$strDateEnd.'
                </td>
             </tr>';
      }
      
      $strOut .= '</table>';
      return($strOut);
   }




