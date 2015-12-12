<?php
   global $gdteNow;

   openBlock('Data Entry Log', '');

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->strStyleExtraLabel = 'width: 90pt;';
   $clsForm->bValueEscapeHTML = false;
   echoT('<table  border="0">');

      //--------------------------------
      // Time Frame
      //--------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 2px;';
   $clsForm->strStyleExtraValue = 'vertical-align: top;';
   echoT($clsForm->strLabelRow('Time Frame', $dateRange, 1));

      //--------------------------------
      // Report Type
      //--------------------------------
   echoT($clsForm->strLabelRow('Report Type', $strRptType, 1));

      //--------------------------------
      // Report Created
      //--------------------------------
   echoT($clsForm->strLabelRow('Report Created', date('F j, Y H:i:s', $gdteNow), 1));

      //--------------------------------
      // Grouping
      //--------------------------------
   echoT($clsForm->strLabelRow('Grouping', $strRptGroup, 1));

      //--------------------------------
      // Total Count
      //--------------------------------
   echoT($clsForm->strLabelRow($strTotCnt, number_format($lTotRecCnt), 1));

   if ($lTotRecCnt > 0){
      switch ($enumSource){
         case 'client':
            switch ($enumGroup){
               case 'individual':
                  $strOutTable = strClientRecsTable($lNumEntries, $entries);
                  break;
               case 'staffGroup':
                  $strOutTable = strClientRecsViaGroupTable($lNumStaffGroups, $staffGroups);
                  break;
               default:
                  screamForHelp($enumGroup.': invalid report group<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }
            break;
         case 'attend':
         case 'enroll':
            $bEnroll = $enumSource=='enroll';
            switch ($enumGroup){
               case 'individual':
                  $strOutTable = strEnrollAttendRecsTable($bEnroll, $lNumEntries, $cprogs);
                  break;
//               case 'staffGroup':
//                  $strOutTable = strEnrollRecsViaGroupTable($lNumStaffGroups, $staffGroups);
//                  break;
               default:
                  screamForHelp($enumGroup.': invalid report group<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }
            break;
         default:
            screamForHelp($enumSource.': invalid report source<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      echoT($clsForm->strLabelRow('Data Entry Log', $strOutTable, 1));

   }

   echoT('</table>');
   closeblock();


   function strClientRecsTable($lNumEntries, &$entries){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($lNumEntries == 0){
         return('<i>No entries</i>');
      }

      $strOut = '
          <table class="enpRpt" style="width: 500px;">
             <tr>
                <td class="enpRptLabel">
                   User ID
                </td>
                <td class="enpRptLabel">
                   User Name
                </td>
                <td class="enpRptLabel">
                   Data Entry Cnt
                </td>
                <td class="enpRptLabel">
                   Staff Group
                </td>
             </tr>';
      foreach ($entries as $en){
         if ($en->lNumGroups == 0){
            $strGroups = '<i>(none)</i>';
         }else {
            $strGroups = '<ul style="list-style-type: none; margin-bottom: 0px; margin-top: 0px; margin-left: -30px;">'."\n";
            foreach ($en->staffGroups as $sg){
               $strGroups .= '<li>'.htmlspecialchars($sg->strGroupName).'</li>'."\n";
            }
            $strGroups .= '</ul>'."\n";
         }
         $strOut .= '
             <tr class="makeStripe">
                <td class="enpRpt" style="text-align: center;">'
                   .str_pad($en->lUserID, 5, '0', STR_PAD_LEFT).'
                </td>
                <td class="enpRpt">'
                   .htmlspecialchars($en->strLastName.', '.$en->strFirstName).'
                </td>
                <td class="enpRpt" style="text-align: center;">'
                   .number_format($en->lNumRecs).'
                </td>
                <td class="enpRpt">'
                   .$strGroups.'
                </td>
             </tr>';
      }

      $strOut .= '</table><br><br>';
      return($strOut);
   }

   function strClientRecsViaGroupTable($lNumStaffGroups, &$staffGroups){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($lNumStaffGroups == 0) return('<i>No staff groups</i>');

      $strOut = '
          <table class="enpRpt" style="width: 470px;">
             <tr>
                <td class="enpRptLabel">
                   Group ID
                </td>
                <td class="enpRptLabel">
                   Data Entry Cnt
                </td>
                <td class="enpRptLabel">
                   Staff Group
                </td>
                <td class="enpRptLabel">
                   Group Members
                </td>
             </tr>';
      foreach ($staffGroups as $sg){
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$sg   <pre>');
echo(htmlspecialchars( print_r($sg, true))); echo('</pre></font><br>');
die;
// ------------------------------------- */
         if ($sg->lNumMembers == 0){
            $strMembers = '<i>(no members)</i>';
         }else {
            $strMembers = '<ul style="list-style-type: none; margin-bottom: 0px; margin-top: 0px; margin-left: -30px;">'."\n";
            foreach ($sg->staffMembers as $sm){
               $strMembers .= '<li>'.htmlspecialchars($sm->strName).'</li>'."\n";
            }
            $strMembers .= '</ul>'."\n";
         }
         $strOut .= '
             <tr class="makeStripe">
                <td class="enpRpt" style="text-align: center;">'
                   .str_pad($sg->lKeyID, 5, '0', STR_PAD_LEFT).'
                </td>
                <td class="enpRpt" style="text-align: center;">'
                   .number_format($sg->lNumEntries).'
                </td>
                <td class="enpRpt">'
                   .htmlspecialchars($sg->strGroupName).'
                </td>
                <td class="enpRpt">'
                   .$strMembers.'
                </td>
             </tr>';
      }

      $strOut .= '</table><br><i><b>Note:</b> this table\'s total may not equal the grand total<br>
                         since staff members can belong to multiple groups (or no groups).<br>';
      return($strOut);
   }

   function strEnrollAttendRecsTable($bEnroll, $lNumEntries, $cprogs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strLabel  = ($bEnroll ? 'enrollment' : 'attendance');
      $strLabelC = ($bEnroll ? 'Enrollment' : 'Attendance');
      if ($lNumEntries == 0) return('<i>No '.$strLabel.' records added during this time frame</i>');

      $strOut = '';
      foreach ($cprogs as $cp){
         $strOut .= '
              <table class="enpRpt" style="width: 420pt;">
                 <tr>
                    <td class="enpRptTitle" colspan="7">'
                       .htmlspecialchars($cp->strProgramName).':
                       Client Program '.$strLabelC.' Records<br>
                       <font style="font-weight: normal; font-size: 9pt;">
                       '.$strLabelC.' records entered for this program during timeframe: '
                       .number_format($cp->lNumAERecs).'</font>
                    </td>
                 </tr>';

         $strOut .= '
                 <tr>
                    <td class="enpRptLabel" style="width: 50px;">
                       User ID
                    </td>
                    <td class="enpRptLabel" style="width: 300px;">
                       Name
                    </td>
                    <td class="enpRptLabel">
                       # Records Entered
                    </td>
                 </tr>';
                 
         if ($cp->lNumAERecs == 0){
            $strOut .= '
                 <tr>
                    <td class="enpRpt" colspan="3"><i>
                       No '.$strLabel.' records entered during this timeframe</i>
                    </td>
                 </tr>';
         }else {
            foreach ($cp->users as $user){
               $strOut .= '
                       <tr class="makeStripe">
                          <td class="enpRpt" style="text-align: center;">'
                             .str_pad($user->lUserID, 5, '0', STR_PAD_LEFT).'
                          </td>
                          <td class="enpRpt">'
                            .htmlspecialchars($user->strLastName.', '.$user->strFirstName).'
                          </td>
                          <td class="enpRpt" style="text-align: center;">'
                             .number_format($user->lNumAERecs).'
                          </td>
                       </tr>';
            }
         }

         $strOut .= '</table><br><br>';

      }
      return($strOut);


   }








