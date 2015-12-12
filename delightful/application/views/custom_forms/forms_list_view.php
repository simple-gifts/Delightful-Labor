<?php

   echoT(strLinkAdd_CustomForm($enumType, 'Add new '.$enumType.' custom form', true).'&nbsp;'
        .strLinkAdd_CustomForm($enumType, 'Add new '.$enumType.' custom form', false).'<br><br>');

   if ($lNumCustomForms==0){
      echoT('<i>No '.$enumType.' forms have been created.</i>');
   }else {
      openCustomFormTable($contextLabel);

      writeCustomFormTable($customForms, $contextLabel);

      closeCustomFormTable();
   }

   function writeCustomFormTable(&$customForms, &$contextLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      foreach ($customForms as $cform){

         $lCFID = $cform->lKeyID;
         $strSpacer = ($cform->strDescription=='' ? '' : '<br><br>');
         $enumType = $cform->enumContextType;

         echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align: center;" nowrap>&nbsp;'
                  .str_pad($lCFID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkEdit_CustomForm($lCFID, $enumType, 'Edit '.$contextLabel->strType.' form', true).'
               </td>
               <td class="enpRpt">'
                  .strLinkRem_CustomForm($lCFID, $enumType, 'Remove '.$contextLabel->strType.' form.', true, true).'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($cform->strFormName).'
               </td>
               <td class="enpRpt">'
                  .strTablesInForm($cform).'
               </td>
               <td class="enpRpt" style="width: 150pt;">'
                  .strPermissionsInForm($cform).'
               </td>
               <td class="enpRpt" style="width: 200pt;">'
                  .nl2br(htmlspecialchars($cform->strDescription)).'
               </td>
            </tr>');
      }
   }

   function strPermissionsInForm(&$cform){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!isset($cform->lNumConsolidated)) $cform->lNumConsolidated = 0;
   
      if ($cform->lNumConsolidated == 0){
         return('<i>No permissions required; available to Admins and all users.</i>');
      }else {
         $strOut = '<i>This form is available to <b>Admins</b> and users who belong to
                    <b>all</b> the following user groups:</i>
                    <ul style="margin-left: -16pt; margin-top: 2pt; margin-bottom: 0pt;">'."\n";
         foreach ($cform->cperms as $cperm){
            $strOut .= '<li>'.$cperm->strSafeGroupName.'</li>'."\n";
         }
         $strOut .= '</ul>'."\n";
         return($strOut);
      }
   }

   function openCustomFormTable(&$contextLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('
         <table class="enpRptC">
            <tr>
               <td class="enpRptLabel">
                  Form ID
               </td>
               <td class="enpRptLabel">
                  &nbsp;
               </td>
               <td class="enpRptLabel">
                  Name
               </td>
               <td class="enpRptLabel">
                  Tables
               </td>
               <td class="enpRptLabel">
                  Permissions
               </td>
               <td class="enpRptLabel">
                  Description
               </td>
            </tr>');
   }

   function closeCustomFormTable(){
      echoT('
         </table><br><br>');
   }

   function strTablesInForm(&$cform){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      if ($cform->lNumTables == 0){
         $strOut = '<i>No tables assigned to this form.</i>'."\n";
      }else {
         $strOut .= '<ul style="margin-left: -16pt; margin-bottom: 0pt; margin-top: 0pt;">'."\n";
         foreach ($cform->utables as $utable){
            $strOut .= '<li>'.htmlspecialchars($utable->strUserTableName)."</li>\n";
         }
         $strOut .= '</ul>'."\n";
      }
      return($strOut);
   }

