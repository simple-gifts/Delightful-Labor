<?php

   global $genumDateFormat;

   if ($lMembersInGroup <= 0){
      echoT('<br><i>This group has no members!</i><br>');
   }else {
      $bAllowEdit = bAllowAccess('editPeopleBizVol');
      if ($bAllowEdit){
         $attributes = array('name' => 'frmBlockCheck');
         echoT(form_open('groups/group_util/remMem/'.$lGroupID, $attributes));
      }

      echoT('<br>
         <table class="enpRptC">

        <tr>
           <td class="enpRptTitle" colspan="6">'
              .htmlspecialchars($strGroupName).'<br>');

      if ($bAllowEdit){
         echoT(
              '<a href=""
                    onclick="setCheckBoxes(\'frmBlockCheck\', \'chkGroupMem[]\', true); return false;"
                    style="font-size: 7pt; font-weight: normal;">(check all)</a>&nbsp;&nbsp;
               <a href=""
                    onclick="setCheckBoxes(\'frmBlockCheck\', \'chkGroupMem[]\', false); return false;"
                    style="font-size: 7pt; font-weight: normal;">(clear all)</a>');
         echoT('&nbsp;&nbsp;');
         echoT('<font style="font-weight: normal; font-size: 7pt;">with checked: '
             .strLinkRem_GroupBlockRemove ($lGroupID,              true,            'frmBlockCheck',
                                         'Remove from group', 'chkGroupMem[]', ' (Group members) ',
                                         'BOPT',              'remBlock').'&nbsp;'
             .strLinkRem_GroupBlockRemove  ($lGroupID,              false,           'frmBlockCheck',
                                         'Remove from group', 'chkGroupMem[]', ' (Group members) ',
                                         'BOPT',              'remBlock')
             );
      }

      echoT('
              </td>
           </tr>
           <tr>');
      if ($bAllowEdit){
         echoT('
              <td class="enpRptLabel">
                 &nbsp;
              </td>');
      }
      echoT('
              <td class="enpRptLabel">'
                 .$groupMemLabels->strKey.'
              </td>
              <td class="enpRptLabel">'
                 .$groupMemLabels->strName.'
              </td>
              <td class="enpRptLabel">'
                 .$groupMemLabels->strAddress.'
              </td>
              <td class="enpRptLabel">
                 Date Added
              </td>
           </tr>');

      foreach ($groupMembers as $clsMember){
         echoT('
            <tr class="makeStripe">');
            
         if ($bAllowEdit){
            echoT('
               <td class="enpRpt">
                  <input type="checkbox" value="'.$clsMember->lKeyID.'" name="chkGroupMem[]">
               </td>');
         }
               
         echoT('         
               <td class="enpRpt">'
                  .$clsMember->strLinkView.' '.str_pad($clsMember->lKeyID, 5, '0', STR_PAD_LEFT).'
               </td>');
        echoT('
               <td class="enpRpt"><b>'
                  .htmlspecialchars($clsMember->strName).'
               </td>
               <td class="enpRpt">'
                  .nl2br(htmlspecialchars($clsMember->strAddress)).'
               </td>
               <td class="enpRpt">'
                  .date($genumDateFormat, $clsMember->dteAdded).'
               </td>
            </tr>');
      }

      echoT('</table>'.form_close('<br>'));
   }

