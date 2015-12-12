<?php

   if ($lNumGroupList <= 0){
      echoT('<br><i>There are no '.strLabelViaContextType($enumContext, false, false).' groups
             defined in your system. </i><br><br>');
   }else {
      $attributes =
          array(
               'name'     => 'frmGroupRpt'//,
//               'onSubmit' => 'return(verifyBoxChecked(\'frmGroupRpt\', \'chkGroup[]\', \' (Groups)\'))'
               );

      echoT(form_open('reports/pre_groups/saveOpts/'.$enumContext,
                      $attributes));


      echoT('<br><br>'
            .strHTMLSetClearAllButton(true,  'frmGroupRpt', 'chkGroup[]', 'Select All ').'&nbsp;&nbsp;'
            .strHTMLSetClearAllButton(false, 'frmGroupRpt', 'chkGroup[]', 'Clear All').'<br>'
         );
      echoT('<div style="vertical-align: middle;">
                Find all the '.strLabelViaContextType($enumContext, false, true)
              .' who are members of <input type="radio" name="rdoAnyAll" value="any" checked><b>any</b>
                                    <input type="radio" name="rdoAnyAll" value="all" ><b>all</b>
                 of the selected groups:<br><br></div>');
                 
      echoT('
         <div id="scrollCB" style="height:130px;   width:300px; overflow:auto;  border: 1px solid black;">'."\n\n");
        
      echoT('<table class="enpRpt" style="width: 100%">');
      foreach ($groupList as $group){
         $lGroupID = $group->lKeyID;
         echoT('
            <tr>
               <td class="enpRpt" style="width: 10px; vertical-align:top;">
                  <input type="checkbox" name="chkGroup[]" value="'.$lGroupID.'">
               </td>
               <td class="enpRpt" style="vertical-align:top; width: 180px;">'
                  .htmlspecialchars($group->strGroupName).'
               </td>
            </tr>');
      }
      echoT('</table></div><br>

             <input type="submit" name="cmdSubmit" value="Run Report"
                 style=""
                 onClick="return(verifyBoxChecked(\'frmGroupRpt\', \'chkGroup[]\', \' (Groups)\'));"
                 class="btn"
                    onmouseover="this.className=\'btn btnhov\'"
                    onmouseout="this.className=\'btn\'">');

      echoT(form_close('<br>'));
//                 onclick="this.disabled=1; this.form.submit();"
   }
