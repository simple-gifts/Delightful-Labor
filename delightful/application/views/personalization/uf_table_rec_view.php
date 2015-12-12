<?php

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '130pt';
   $clsRpt->bValueEscapeHTML = false;

   showUFTableInfo($clsRpt, $schema, $lTableID);
   showUFFieldInfo($clsRpt, $schema, $lTableID);

      // show group membership
   $attributes = new stdClass;
//   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'groupDiv';
   $attributes->divImageID   = 'groupDivImg';
   $attributes->bStartOpen   = true;
   openBlock('Permissions Groups', '', $attributes);

   $opts = new stdClass;
   $opts->strSafeName = htmlspecialchars($schema[$lTableID]->strUserTableName);
   $opts->lCntGroupMembership = $lCntGroupMembership;
   $opts->inGroups     = &$inGroups;
   $opts->enumGType    = CENUM_CONTEXT_USER;
   $opts->enumSubGroup = CENUM_CONTEXT_PTABLE;
   $opts->lFID         = $lTableID;
   $opts->strFrom      = 'pTableRecView';
   $opts->lNumGroups   = $lNumGroups;
   $opts->groupList    = &$groupList;
   $opts->postText     = '<b>Note:</b> personalized tables that belong to no user groups are<br>
                               accessible to admins and all users.<br>';
   if ($lCntGroupMembership==0){
      echoT('<i>Admins and all users can access this table.</i><br>'."\n");
   }else {
      echoT('<i>Admins and users who are members of <b>ALL</b> the following groups can access this table:</i><br>'."\n");
      echoT(strGroupMembership($opts));
   }
   echoT(strDDLAvailableGroups($opts));

   $attributes->bCloseDiv      = true;
   closeBlock($attributes);


   function showUFFieldInfo(&$clsRpt, &$schema, $lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $table = &$schema[$lTableID];

      $attributes = new stdClass;
      $attributes->lTableWidth  = '';
      $attributes->divID        = 'fieldsDev';
      $attributes->divImageID   = 'fieldsDivImg';

      openBlock('Fields for table <b>'.htmlspecialchars($table->strUserTableName).'</b>',
                        strLinkView_UFFields($lTableID, 'View fields', true, '').'&nbsp;'
                       .strLinkView_UFFields($lTableID, 'View fields', false, ''), $attributes);

      echoT('
         <table class="enpRpt">
            <tr>
               <td class="enpRptLabel">
                  Field ID
               </td>
               <td class="enpRptLabel">
                  User Name
               </td>
               <td class="enpRptLabel">
                  Internal Name
               </td>
               <td class="enpRptLabel">
                  Sort IDX
               </td>
               <td class="enpRptLabel">
                  Field Type
               </td>
            </tr>
         ');

      foreach ($table->fields as $ufield){
         $bHidden = $ufield->bHidden;
         if ($bHidden){
            $strStyle = 'color: #999; font-style: italic;';
            $strHidden = ' (hidden)';
         }else {
            $strStyle = '';
            $strHidden = '';
         }
         echoT('
               <tr class="makeStripe" style="'.$strStyle.'">
                  <td class="enpRpt" style="text-align: center;">'
                     .str_pad($ufield->lFieldID, 5, '0', STR_PAD_LEFT).'
                  </td>
                  <td class="enpRpt" style="'.$strStyle.'">'
                     .htmlspecialchars($ufield->strFieldNameUser).'&nbsp;'.$strHidden.'
                  </td>
                  <td class="enpRpt" style="'.$strStyle.'">'
                     .htmlspecialchars($ufield->strFieldNameInternal).'
                  </td>
                  <td class="enpRpt" style="text-align: center; '.$strStyle.'">'
                     .$ufield->lSortIDX.'
                  </td>
                  <td class="enpRpt" style="'.$strStyle.'">'
                     .$ufield->enumFieldType.'
                  </td>
               </tr>
            ');
      }

      echoT('
         </table>');
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function showUFTableInfo(&$clsRpt, &$schema, $lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $table = &$schema[$lTableID];

      openBlock('User Table <b>'.htmlspecialchars($table->strUserTableName).'</b>',
             strLinkEdit_UFTable($lTableID, $table->enumAttachType, 'Edit table', true).'&nbsp;'
            .strLinkEdit_UFTable($lTableID, $table->enumAttachType, 'Edit table', false)
           );

      echoT(
          $clsRpt->openReport());
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Table ID:')
         .$clsRpt->writeCell (str_pad($lTableID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('User Name:')
         .$clsRpt->writeCell (htmlspecialchars($table->strUserTableName))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Internal Name:')
         .$clsRpt->writeCell (htmlspecialchars($table->strDataTableName))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Parent Table:')
         .$clsRpt->writeCell (htmlspecialchars($table->enumAttachType))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Description:')
         .$clsRpt->writeCell (nl2br(htmlspecialchars($table->strDescription)))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Multi-Entry?:')
         .$clsRpt->writeCell (($table->bMultiEntry ? 'Yes' : 'No'))
         .$clsRpt->closeRow  ());
         
      if ($table->bMultiEntry){
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Read Only?:')
            .$clsRpt->writeCell (($table->bReadOnly ? 'Yes' : 'No'))
            .$clsRpt->closeRow  ());
      }

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Hidden?:')
         .$clsRpt->writeCell (($table->bHidden ? 'Yes' : 'No'))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Alert if No Entry?:')
         .$clsRpt->writeCell (($table->bAlertIfNoEntry ? 'Yes' : 'No'))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Alert Text:')
         .$clsRpt->writeCell (strip_tags($table->strAlertMsg, '<b><i><br><font>'))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Verification Module:')
         .$clsRpt->writeCell (htmlspecialchars($table->strVerificationModule))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Verification Entry Point')
         .$clsRpt->writeCell (htmlspecialchars($table->strVModEntryPoint))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('# Fields:')
         .$clsRpt->writeCell ($table->lNumFields)
         .$clsRpt->closeRow  ());

      echoT($clsRpt->closeReport());
      closeBlock();
   }

