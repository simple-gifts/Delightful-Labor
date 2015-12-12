<?php

   $idx = 0;
   foreach ($access as $uperm){
      openUserBlock($uperm->strLabel, $idx);
      writeUserBlock($uperm);
      closeUserBlock();
      ++$idx;
   }

   function writeUserBlock($uperm){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($uperm->lNumAccts == 0){
         echoT('<i>There are no users in this category.</i>');
      }else {
         echoT('<table style="width: 270pt;">');
         foreach ($uperm->accounts as $acct){
            $lUserID = $acct->lUserID;
            if ($acct->bInactive){
               $strColor = 'color: #aaa;';
            }else {
               $strColor = '';
            }
            echoT('
               <tr class="makeStripe">
                  <td style="width: 80pt; text-align: left; '.$strColor.'">'
                     .strLinkView_User($lUserID, 'View user account', true).'&nbsp;&nbsp;'
                     .strLinkEdit_User($lUserID, 'Edit user account', true).'&nbsp;'
                     .str_pad($lUserID, 5, '0', STR_PAD_LEFT).'
                  </td>
                  <td style="'.$strColor.'">'
                     .htmlspecialchars($acct->strLastName.', '.$acct->strFirstName)
                        .' <i>('.htmlspecialchars($acct->strUserName).')</i>
                  </td>
               </tr>');
         }
         
         echoT('</table>');
      }
   }


   function openUserBlock($strLabel, $idx){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 1200;
      $attributes->divID        = 'userBlockDiv'.$idx;
      $attributes->divImageID   = 'userBlockDivImg'.$idx;
      $attributes->bStartOpen   = true;
      openBlock($strLabel, '', $attributes);
   }

   function closeUserBlock(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   
   
   