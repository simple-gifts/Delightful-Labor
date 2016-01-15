<?php
   global $genumDateFormat, $glUserID;
   
   openUserDirectory(
                 $strLinkBase,          $strImgBase,        $lNumRecsTot,
                 $lNumDisplayRows);


   foreach ($userList as $clsDir){
      $lUserID = $clsDir->lUserID;
      $bInactive = $clsDir->bInactive;
      if (is_null($clsDir->dteLastLogin)){
         $strDateLastLogin = '<small>(none)</small>';
      }else {
         $strDateLastLogin = date($genumDateFormat.' H:i:s', $clsDir->dteLastLogin); 
      }
       
      $strAcctType = '';
      if ($clsDir->bAdmin){
         $strAcctType = 'Admin';
      }elseif ($clsDir->bVolAccount){
         $strAcctType = 'Volunteer';
      }else {
         $strAcctType = 'User';
      }
      if ($clsDir->us_bDebugger) $strAcctType .= ' / Debugger';

      
      $strColor = '';
      if ($glUserID==$lUserID){
         $strLinkRem = '&nbsp;';
      }else {
         if ($bInactive){
            $strLinkRem = strLinkSpecial_UserActivate($lUserID, 'Activate user', true);
            $strColor = 'color: #999;font-style:italic;';
         }else {
            $strLinkRem = strLinkSpecial_UserDeactivate($lUserID, 'Deactivate user', true);
         }
      }
      
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="text-align: center; '.$strColor.'" nowrap>'
               .strLinkView_User($lUserID, 'View this user\'s record', true)
               .str_pad($lUserID, 5, 0, STR_PAD_LEFT).'
            </td>
            <td class="enpRpt" style="text-align: center; '.$strColor.'">'
               .$strLinkRem.'
            </td>
            <td class="enpRpt" style="width: 150px; '.$strColor.'"><b>'
               .htmlspecialchars($clsDir->us_strLastName).'</b>, '
               .htmlspecialchars($clsDir->us_strFirstName).'
            </td>
            <td class="enpRpt" style="width: 80px; '.$strColor.'">'
               .htmlspecialchars($clsDir->us_strUserName).'
            </td>
            <td class="enpRpt"  style="width: 120px; '.$strColor.'">'
               .htmlspecialchars($clsDir->us_strEmail).'<br>'
               .htmlspecialchars($clsDir->us_strPhone).'
            </td>

            <td class="enpRpt" style="text-align: left; width: 160px; '.$strColor.'">'
               .$strDateLastLogin.'
            </td>
            <td class="enpRpt" style="text-align: left; width: 130px; '.$strColor.'">'
               .strUserGroupsBlock($lUserID, $clsDir).'
            </td>
            <td class="enpRpt" style="text-align: left; width: 110px; '.$strColor.'">'
               .$strAcctType.'
            </td>
         </tr>');      
   }
   echoT('</table><br>');


   function openUserDirectory(
                       &$strLinkBase,  &$strImgBase, &$lTotRecs,
                       $lNumThisPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

      echoT('
          <table class="enpRptC">
              <tr>
                 <td class="enpRptLabel">
                    UserID
                 </td>
                 <td class="enpRptLabel">
                    &nbsp;
                 </td>
                 <td class="enpRptLabel" style="text-width: 200px;">
                    Name
                 </td>
                 <td class="enpRptLabel">
                    User Name
                 </td>
                 <td class="enpRptLabel">
                    Email/Phone
                 </td>
                 <td class="enpRptLabel">
                    Last Login
                 </td>
                 <td class="enpRptLabel">
                    User Groups
                 </td>
                 <td class="enpRptLabel">
                    Flags
                 </td>
              </tr>');
   }

   function strUserGroupsBlock($lUserID, $cdir){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($cdir->bAdmin || $cdir->bVolAccount) return('n/a');
      $strOut = '';

      $attributes = new stdClass;
      $attributes->lTableWidth  = 200;
      $attributes->divID        = 'ug_'.$lUserID;
      $attributes->divImageID   = 'ug_'.$lUserID.'DivImg';
      $attributes->lUnderscoreWidth = 200;
      $attributes->lTitleFontSize   = 10;
      $attributes->bAddTopBreak     = false;
      $strOut .= strOpenBlock('User Groups ('.$cdir->userGroups->lNumUserGroups.')', '', $attributes);
      
      if ($cdir->userGroups->lNumUserGroups > 0){
         foreach ($cdir->userGroups->groups as $ugroup){
            $strOut .= htmlspecialchars($ugroup->strGroupName).'<br>'."\n";
         }
      }

      $attributes->bCloseDiv = true;
      $strOut .= strCloseBlock($attributes).'<br>';
      
      return($strOut);         
   }
