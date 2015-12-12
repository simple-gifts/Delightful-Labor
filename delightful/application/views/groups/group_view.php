<?php
   echoT('<br>'
        .strLinkAdd_Group($enumGroupType, 'Add new '.$strGroupType.' group', true).' '
        .strLinkAdd_Group($enumGroupType, 'Add new '.$strGroupType.' group', false).'<br>');

   if ($lNumGroups == 0){
      echoT('<br><i>There are currently no <b>'.$strGroupType.'</b> groups in your database.</i><br><br>');
      return;
   }
   
   if ($gProps->extended){
      $lNumCols = 4 + $gProps->lNumBool + $gProps->lNumInt;
   }else {
      $lNumCols = 4;
   }
   echoT(
      '<br>
       <table class="enpRptC" style="width: 540px;">
           <tr>
              <td class="enpRptTitle" colspan="'.$lNumCols.'">'
                 .$strGroupType.' Groups
              </td>
           </tr>');
   echoT('
        <tr>
           <td class="enpRptLabel" style="width: 80px;">
              Group ID
           </td>
           <td class="enpRptLabel" style="width: 50px;">
              &nbsp;
           </td>
           <td class="enpRptLabel">
              Name
           </td>');
           
   if ($gProps->extended){
      if ($gProps->lNumBool > 0){
         foreach ($gProps->bools as $bField){
            echoT('
                 <td class="enpRptLabel" style="width: 50pt;">'
                    .$bField->strLabel.'
                 </td>');
         }
      }
      if ($gProps->lNumInt > 0){
      }
   } 
           
   echoT('           
           <td class="enpRptLabel" style="width: 90px;">
              # Members
           </td>
        </tr>');

   $idx = 0;
   foreach ($arrGroupList as $clsList){
      $lGID = $clsList->lKeyID;

      echoT('
           <tr class="makeStripe">
              <td class="enpRpt" style="text-align: center;">'
                 .strLinkEdit_GroupName($lGID, $enumGroupType, 'Edit group name', true, '').'&nbsp;&nbsp;'
                 .str_pad($lGID, 5, '0', STR_PAD_LEFT).'
              </td>
              <td class="enpRpt" style="text-align: center;">'
                 .strLinkRem_GroupName ($lGID, $enumGroupType, 'Remove group', true, true).'
              </td>
              <td class="enpRpt">'
                 .htmlspecialchars($clsList->strGroupName).'
              </td>');
              
      if ($gProps->extended){
         if ($gProps->lNumBool > 0){
            foreach ($gProps->bools as $bField){
               $strFN = $bField->strDBFN;
               echoT('
                    <td class="enpRpt" style="text-align: center;">'
                       .($clsList->$strFN ? 'Yes' : 'No').'
                    </td>');
            }
         }
         if ($gProps->lNumInt > 0){
         }
      }    
              
      echoT('
              <td class="enpRpt" style="text-align: center;">'
                 .strLinkView_GroupMembership($lGID, 'View group membership', true).' '
                 .number_format($lMembersInGroup[$idx]).'
              </td>
           </tr>');
      ++$idx;
   }

   echoT('
      </table><br>');

?>