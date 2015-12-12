<?php
//---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
/*---------------------------------------------------------------------
      $this->load->helper('groups/groups');
---------------------------------------------------------------------*/

function bUserPermGroupType($enumType){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   return($enumType == CENUM_CONTEXT_PTABLE
       || $enumType == CENUM_CONTEXT_CUSTOMFORM
       || $enumType == CENUM_CONTEXT_CPROGRAM
       || $enumType == CENUM_CONTEXT_CPREPOST
       || $enumType == CENUM_CONTEXT_USER);
}

function bGroupFullAccess($enumGType){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gbAdmin;
   $bUserPermissionsGroup = bUserPermGroupType($enumGType);
   return(($bUserPermissionsGroup && $gbAdmin) || (!$bUserPermissionsGroup));
}

function strGroupMembership($opts){
/*---------------------------------------------------------------
   $opts
        ->lCntGroupMembership
        ->inGroups
        ->enumGType
        ->enumSubGroup
        ->lFID
        ->strFrom

---------------------------------------------------------------*/

   $strOut = '';
   $bGroupFullAccess = bGroupFullAccess($opts->enumGType);
//   $bUserPermissionsGroup = bUserPermGroupType($opts->enumGType);
//   $bGroupFullAccess = ($bUserPermissionsGroup && $gbAdmin) || (!$bUserPermissionsGroup);

   if ($opts->lCntGroupMembership > 0){
      $strOut .= '<table border="0">';
      foreach ($opts->inGroups as $cGroup){
         if ($bGroupFullAccess){
            $strLinks =
              '<td width="50" valign="top">'
                  .strLinkView_GroupMembership($cGroup->lGroupID, 'view group membership', true).'&nbsp;&nbsp;'
                  .strLinkRem_GroupMembership($opts->enumGType, $opts->enumSubGroup, $cGroup->lGroupID,
                                              $opts->lFID, $opts->strFrom,
                                              'remove from this group', true, true).'
               </td>';
         }else {
            $strLinks = '<td style="width: 5px;">&nbsp;</td>';
         }
         $strOut .= '
            <tr>'.$strLinks.'<td valign="top">'.htmlspecialchars($cGroup->strGroupName);

         if ($cGroup->strGroupNote != ''){
            $strOut .= '<br><i>'.nl2br(htmlspecialchars($cGroup->strGroupNote)).'</i>';
         }

         $strOut .= '</td></tr>';
      }
      $strOut .= '</table>';
   }
   return($strOut);
}


function strDDLAvailableGroups(&$opts){
/*---------------------------------------------------------------------
   $opts
        ->lCntGroupMembership
        ->inGroups
        ->enumGType
        ->enumSubGroup
        ->lFID
        ->strFrom
        ->strSafeName

     optional
        ->ddlGroupName = 'ddlGroups'
        ->buttonLabel  = 'Add'
        ->ddlPreText   = '<br>Add <b>'.$opts->strSafeName.'</b> to the following groups:<br>
                          <font style="font-size: 8pt;"><i>Ctrl-click to select more than one group</i></font><br>
        ->postText     = ''
---------------------------------------------------------------------*/
   global $gdteNow;
   
   $strOut = '';

      // optional settings
   if (!isset($opts->ddlGroupName)) $opts->ddlGroupName = 'ddlGroups';
   if (!isset($opts->buttonLabel))  $opts->buttonLabel = 'Add';
   if (!isset($opts->postText))     $opts->postText = '';
   if (!isset($opts->ddlPreText))   $opts->ddlPreText =
                    '<br>Add <b>'.$opts->strSafeName.'</b> to the following groups:<br>
                     <font style="font-size: 8pt;"><i>Ctrl-click to select more than one group</i></font><br>';

   $bGroupFullAccess = bGroupFullAccess($opts->enumGType);
   if ( bAllowAccess('editGroupMembership', $opts->enumGType) ){
//echo(__FILE__.' '.__LINE__.'<br>'."\n");   
      if ($bGroupFullAccess){
         if ($opts->lNumGroups <= $opts->lCntGroupMembership){
            $strOut .= '<br><i>No additional groups are available for membership</i>';
         }else {
            $strOut .= $opts->ddlPreText;

            $strAnchorBase = strGroupAnchor($opts->lFID, $opts->strFrom, $opts->enumGType, $opts->enumSubGroup);
            $strOut .= "\n".form_open($strAnchorBase);
            $strOut .= '
               <select name="'.$opts->ddlGroupName.'[]" multiple size="5">';

            foreach ($opts->groupList as $cGroup){
               if ($cGroup->dteExpire > $gdteNow){
                  $strOut .= '
                     <option value="'.$cGroup->lKeyID.'">'
                        .htmlspecialchars($cGroup->strGroupName).'
                     </option>';
               }
            }

            $strOut .= '
               </select><br>';
            $strOut .= '
                 <input type="submit"
                    name="cmdAdd"
                    value="'.$opts->buttonLabel.'"
                    onclick="this.disabled=1; this.form.submit();"
                    class="btn"
                       onmouseover="this.className=\'btn btnhov\'"
                       onmouseout="this.className=\'btn\'">
                 </form>'.$opts->postText;
         }
      }
   }
   return($strOut);
}


function strGroupAnchor($lFID, $strFrom, $enumGroupType, $enumSubGroup){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $strAnchorBase = 'groups/group_util/addMemToGroup/'.$lFID.'/'.$strFrom.'/';
   switch ($enumGroupType){
      case CENUM_CONTEXT_PEOPLE:
         $strAnchorBase .= 'person';
         break;

      case CENUM_CONTEXT_BIZ:
         $strAnchorBase .= 'biz';
         break;

      case CENUM_CONTEXT_CLIENT:
         $strAnchorBase .= 'client';
         break;

      case CENUM_CONTEXT_VOLUNTEER:
         $strAnchorBase .= 'volunteers';
         break;

      case CENUM_CONTEXT_SPONSORSHIP:
         $strAnchorBase .= 'sponsors';
         break;

      case CENUM_CONTEXT_USER:
         $strAnchorBase .= 'user/'.$enumSubGroup;
/*      
         if ($enumSubGroup==CENUM_CONTEXT_CPROGRAM){
            $strAnchorBase .= 'cprograms/cprog_record';
         }else {
            $strAnchorBase .= 'user/'.$enumSubGroup;
         }
*/         
         break;

      default:
         screamForHelp($enumGroupType.': invalid group type</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
         break;
   }
   return($strAnchorBase);
}

function groupExtensionProperties($enumType, &$properties){
//---------------------------------------------------------------------
// Allows groups to be extended with additional fields
//---------------------------------------------------------------------
   $properties = new stdClass;
   $properties->extended = false;
   
   switch ($enumType){
      case CENUM_CONTEXT_STAFF_TS_PROJECTS:
         $properties->extended = true;
         $properties->lNumInt  = 0;
         $properties->lNumBool = 1;
         $properties->bools = array();
         
         $properties->bools[0] = new stdClass;
         $pb = &$properties->bools[0];
         $pb->strLabel      = 'Internal Project';
         $pb->strLabelExtra = '<br><i>Internal projects can be things like "overhead", "sick time", "vacation", etc.</i>';
         $pb->strFormFN     = 'chkInternal';
         $pb->strDBFN       = 'gp_bGeneric1';
         $pb->bValue        = false;
         break;
         
      default:
         $properties->extended = false;
         break;
   }
}





