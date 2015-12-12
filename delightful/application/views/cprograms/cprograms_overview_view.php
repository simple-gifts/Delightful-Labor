<?php
   global $gbDev;
   
   if ($gbDev){
      echoT('<br>'.strLinkDebug_CProgram('View internal details', true));
   }

   echoT('<br>'
        .strLinkAdd_CProgram('Add new client program', true).'&nbsp;'
        .strLinkAdd_CProgram('Add new client program', false).'<br><br>');

   if ($lNumCProgs == 0){
      echoT('<i>There are no client programs defined in your database.<br><br></i>');
      return;
   }

   openClientProgramTable();

   foreach ($cprogs as $cprog){
      writeClientProgRow($cprog);
   }

   closeClientProgramTable();

   function writeClientProgRow(&$cprog){
   //---------------------------------------------------------------------
   //
   //--------------------------------------------------------------------- 
      global $genumDateFormat, $gbDev;
      
      $lCProgID = $cprog->lKeyID;
      if ($cprog->bHidden){
         $strStyle = 'color: #999; font-style: italic;';
         $strEnroll = '&nbsp;';
         $strAttend = '&nbsp;';
         $strClone  = '&nbsp;';
      }else {
         $strStyle = '';
         $strEnroll = strLinkView_UFFields($cprog->lEnrollmentTableID, 'View enrollment fields', true)
                               .'&nbsp;'.$cprog->lNumEFields;
         $strAttend = strLinkView_UFFields($cprog->lAttendanceTableID, 'View attendance fields', true)
                               .'&nbsp;'.$cprog->lNumAFields;
         if ($gbDev){
            $strEnroll .= '&nbsp;'.strLinkDebug_Fields($cprog->lEnrollmentTableID, 'Field debug info', true);
            $strAttend .= '&nbsp;'.strLinkDebug_Fields($cprog->lAttendanceTableID, 'Field debug info', true);
         }
         $strClone  = strLinkClone_CProgram($lCProgID, 'Clone this client program', true);
      }
      
      if ($cprog->bMentorMentee){
         $strProgType = '<br><i>Mentor Program</i>';
//         &nbsp;'
//                .strLinkEdit_CPMentorFields($lCProgID, 'Edit field association', true);
      }else {
         $strProgType = '';
      }
      echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align: center; '.$strStyle.'" nowrap>'
                  .str_pad($lCProgID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkView_CProgram($lCProgID, 'View program record', true).'
               </td>
               <td class="enpRpt" style="text-align: center; vertical-align: top;'.$strStyle.'" nowrap>'
                  .$strClone.'
               </td>
               <td class="enpRpt" style="'.$strStyle.'"><b>'
                  .htmlspecialchars($cprog->strProgramName).'</b>'
                  .$strProgType
                  .'<br>'.date($genumDateFormat, $cprog->dteStart).' - '.date($genumDateFormat, $cprog->dteEnd).'
               </td>
               <td class="enpRpt" style="width: 160pt; '.$strStyle.'">'
                  .nl2br(htmlspecialchars($cprog->strDescription)).'
               </td>
               <td class="enpRpt" style="'.$strStyle.' text-align: center;">'
                  .$strEnroll.'
               </td>
               <td class="enpRpt" style="'.$strStyle.' text-align: center;">'
                  .$strAttend.'
               </td>
            </tr>');
   
   }
   
   function openClientProgramTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('
         <table class="enpRptC" style="width: 600pt;">
            <tr>
               <td class="enpRptTitle" colspan="6">
                  Client Programs
               </td>
            </tr>');
            
      echoT('
            <tr>
               <td class="enpRptLabel" style="width: 40pt;">
                  programID
               </td>
               <td class="enpRptLabel" style="width: 20pt;">
                  &nbsp;
               </td>
               <td class="enpRptLabel" style="width: 140pt;">
                  Program
               </td>
               <td class="enpRptLabel" style="width: 180pt;">
                  Description
               </td>
               <td class="enpRptLabel">
                  Enrollment Fields
               </td>
               <td class="enpRptLabel">
                  Attendance Fields
               </td>
            </tr>');
   }
   
   function closeClientProgramTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('
         </table><br>');
   }   
   
   