<?php

   if ($lNumUsers==0){
      echoT('<br>
         <i>There are no users mapped to any time sheets!</i><br><br>');
      return;
   }

   echoT('<br>
      <table class="enpRptC">
         <tr>
            <td class="enpRptTitle" colspan="8">
               Time Sheet Administration
            </td>
         </tr>');

   echoT('
         <tr>
            <td class="enpRptLabel">
               Year Log
            </td>
            <td class="enpRptLabel">
               Submitted
            </td>
            <td class="enpRptLabel">
               Not Submitted
            </td>
            <td class="enpRptLabel">
               Staff Member
            </td>
         </tr>');

   $lYear = (int)date('Y');
   foreach ($users as $user){
      echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align: center">'
                  .strLinkView_TSLog($lYear, $user->lStaffID, 'View yearly log', true).'&nbsp;'
                  .strLinkView_TSLog($lYear, $user->lStaffID, 'View yearly log', false).'
               </td>
               <td class="enpRpt" style="text-align: center">'
                  .number_format($user->lNumSubmitted).'
               </td>
               <td class="enpRpt" style="text-align: center">'
                  .number_format($user->lNumNotSubmitted).'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($user->strLastName.', '.$user->strFirstName).'
               </td>
            </tr>');
   }

   echoT('</table>');

