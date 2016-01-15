<?php
/*---------------------------------------------------------------------
// copyright (c) 2012-2016 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
--------------------------------------------------------------------- */
   global $gclsChapterVoc;

   if ($bNew){
      $strLabel = 'Add a New User';
   }else {
      $strLabel = 'Edit User: <b>'.$userRec->strSafeName.'</b>';
   }
   $attributes = array('name' => 'formMUser', 'id' => 'frmAddEdit');
   if ($bAsAdmin){
      echoT(form_open('admin/accts/addEdit/'.$lUserID, $attributes));
   }else {
      echoT(form_open('more/user_acct/edit/'.$lUserID, $attributes));
   }

   $lTableWidth = 500;
   $lLabelWidth = 105;
   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   showUserInfo        ($clsForm, $lTableWidth, $lLabelWidth, $strLabel, $userRec, $bNew, $bAsAdmin);
   showAccountType     ($clsForm, $lTableWidth, $lLabelWidth, $strLabel, $userRec, $bNew, $bAsAdmin);
   showUserContactInfo ($clsForm, $lTableWidth, $lLabelWidth,            $userRec, $bNew);

   echoT(form_close('<br><br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');

function showAccountType(&$clsForm, $lTableWidth, $lLabelWidth, $strLabel, &$userRec, $bNew, $bAsAdmin){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   openBlock('Account', '');  echoT('<table class="enpView" >');

   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 4pt; width: '.$lLabelWidth.'pt;';

      //------------------------
      // Admin user?
      //------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 4pt; width: '.$lLabelWidth.'pt;';
   if ($bAsAdmin){
      $strRadioDebug =
         '<input type="radio" name="rdoDebug" value="true" ' .($userRec->bDebugger  ? 'checked' : '').'
                  >Yes&nbsp;<i>(access to debug info)</i>&nbsp;'
        .'<input type="radio" name="rdoDebug" value="false" ' .($userRec->bDebugger ? '' : 'checked').'
                  >No&nbsp;<i>(recommended)</i>';
      echoT($clsForm->strLabelRow('Developer', $strRadioDebug, 1));

      $strRadio =
         '<input type="radio" name="rdoAcctType" value="admin" '.($userRec->bAdmin        ? 'checked' : '').'
                  onClick="hideShowDiv(\'volPerms\', true); hideShowDiv(\'userPerms\', true);">Administrator&nbsp;&nbsp;'
        .'<input type="radio" name="rdoAcctType" value="user" ' .($userRec->bStandardUser ? 'checked' : '').'
                  onClick="hideShowDiv(\'volPerms\', true); hideShowDiv(\'userPerms\', false);">User&nbsp;&nbsp;'
        .'<input type="radio" name="rdoAcctType" value="vol" '  .($userRec->bVolAccount   ? 'checked' : '').'
                  onClick="hideShowDiv(\'volPerms\', false); hideShowDiv(\'userPerms\', true);">Volunteer';
      echoT($clsForm->strLabelRow('Account Type', $strRadio, 1));
      showUserPerms($userRec, $clsForm);
      showVolPerms($userRec, $clsForm);
   }else {
      if ($userRec->bAdmin){
         $strAcctType = 'Administrator';
      }elseif ($userRec->bStandardUser){
         $strAcctType = 'User';
      }else {
         $strAcctType = 'Volunteer';
      }

      $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 2pt;';
      echoT($clsForm->strLabelRow('Account Type', $strAcctType, 1));
   }

   echoT('</table>'); closeBlock();
}

function showVolPerms(&$userRec, &$clsForm){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gclsChapterVoc;

   $strVolPerm =
      '<i>Volunteer accounts have limited access to <b>Delightful Labor</b>. You can set<br>
          what information is accessible. Registered volunteers can only access their own records.</i><br>'."\n";


   $strVolPerm .=
      '<input type="checkbox" name="chkVolEditContactInfo" value="true" '
                     .($userRec->bVolEditContact ? 'checked' : '').'>Edit contact info<br>'."\n"
     .'<input type="checkbox" name="chkVolVolPassReset" value="true" '
                     .($userRec->bVolPassReset ? 'checked' : '').'>Reset password<br>'."\n";

   $strVolPerm .=
      '<input type="checkbox" name="chkVolViewGiftHistory" value="true" '
                     .($userRec->bVolViewGiftHistory ? 'checked' : '').'>View donation history<br>'."\n";
   $strVolPerm .=
      '<input type="checkbox" name="chkVolEditJobSkills" value="true" '
                     .($userRec->bVolEditJobSkills ? 'checked' : '').'>Edit '.htmlspecialchars($gclsChapterVoc->vocJobSkills).'<br>'."\n"
     .'<input type="checkbox" name="chkVolViewHrsHistory" value="true" '
                     .($userRec->bVolViewHrsHistory ? 'checked' : '').'>View volunteer hours<br>'."\n"
     .'<input type="checkbox" name="chkVolAddVolHours" value="true" '
                     .($userRec->bVolAddVolHours ? 'checked' : '').'>Add / Edit volunteer hours<br>'."\n"
     .'<input type="checkbox" name="chkVolShiftSignup" value="true" '
                     .($userRec->bVolShiftSignup ? 'checked' : '').'>View upcoming events / register for shifts<br>'."\n";

      // optional peopleID
   $strVolPerm .= form_error('txtPID');

   $strVolPerm .=
      '<span style="vertical-align: center;">'
         .'<input type="text" name="txtPID" style="width: 30pt; text-align:right;" value="'.$userRec->txtPID.'"> People ID (optional)'
     .'</span><br>'."\n";
   echoT($clsForm->strLabelRow('Vol. Account Access', $strVolPerm, 1,
           ' ID="volPerms" style="display: '.($userRec->bVolAccount ? '' : 'none').';" '));
}

function showUserPerms(&$userRec, &$clsForm){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $strUserPerm =
      '<i>User accounts can optionally access various features of <b>Delightful Labor</b>. You can set<br>
          what information is accessible.</i><br>'."\n";
   $strUserPerm .=
      '<input type="checkbox" name="chkUserDataEntryPeople" value="true" '
                     .($userRec->bUserDataEntryPeople ? 'checked' : '').'>Data Entry (people/businesses/volunteers)<br>'."\n"

     .'<input type="checkbox" name="chkUserDataEntryGifts" value="true" '
                     .($userRec->bUserDataEntryGifts ? 'checked' : '').'>Data Entry (donations)<br>'."\n"

     .'<input type="checkbox" name="chkUserEditPeople" value="true" '
                     .($userRec->bUserEditPeople ? 'checked' : '').'>Edit (people/businesses/volunteers)<br>'."\n"

     .'<input type="checkbox" name="chkUserEditGifts" value="true" '
                     .($userRec->bUserEditGifts ? 'checked' : '').'>Edit (donations)<br>'."\n"

     .'<input type="checkbox" name="chkUserViewPeople" value="true" '
                     .($userRec->bUserViewPeople ? 'checked' : '').'>View (people/businesses/volunteers)<br>'."\n"

     .'<input type="checkbox" name="chkUserViewGiftHistory" value="true" '
                     .($userRec->bUserViewGiftHistory ? 'checked' : '').'>View (gift histories)<br>'."\n"

     .'<input type="checkbox" name="chkUserViewReports" value="true" '
                     .($userRec->bUserViewReports ? 'checked' : '').'>View (reports)<br>'."\n"

     .'<input type="checkbox" name="chkUserAllowExports" value="true" '
                     .($userRec->bUserAllowExports ? 'checked' : '').'>Allow exports<br>'."\n"

     .'<input type="checkbox" name="chkUserAllowAuctions" value="true" '
                     .($userRec->bUserAllowAuctions ? 'checked' : '').'>Allow access to silent auctions<br>'."\n"

     .'<input type="checkbox" name="chkUserAllowInventory" value="true" '
                     .($userRec->bUserAllowInventory ? 'checked' : '').'>Allow access to inventory management<br>'."\n"

     .'<input type="checkbox" name="chkUserAllowGrants" value="true" '
                     .($userRec->bUserAllowGrants ? 'checked' : '').'>Allow access to grants<br>'."\n"

     .'<input type="checkbox" name="chkUserVolManager" value="true" '
                     .($userRec->bUserVolManager ? 'checked' : '').'>Volunteer Manager<br>'."\n"

     .'<br><u>Client/Sponsor</u><br>'."\n"
     .'<input type="checkbox" name="chkUserAllowSponsorship" value="true" '
                     .($userRec->bUserAllowSponsorship ? 'checked' : '').'>Access to sponsorships<br>'."\n"

     .'<input type="checkbox" name="chkUserAllowSponFinancial" value="true" '
                     .($userRec->bUserAllowSponFinancial ? 'checked' : '').'>Access to sponsorships financials<br>'."\n"

     .'<input type="checkbox" name="chkUserAllowClient" value="true" '
                     .($userRec->bUserAllowClient ? 'checked' : '').'>Access to client records<br>'."\n"
     ;

   echoT($clsForm->strLabelRow('User Account Access', $strUserPerm, 1,
           ' ID="userPerms" style="display: '.($userRec->bStandardUser ? '' : 'none').';" '));
}

function showUserContactInfo(&$clsForm, $lTableWidth, $lLabelWidth, &$userRec, $bNew){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gclsChapterVoc;

   $bReadOnly = false;
   openBlock('User Contact Info', '');  echoT('<table class="enpView" >');

   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 7pt; width: '.$lLabelWidth.'pt;';

   echoT($clsForm->strGenericTextEntry('Address 1',   'txtAddr1',    false, $userRec->strAddr1,   40, 80));
   echoT($clsForm->strGenericTextEntry('Address 2',   'txtAddr2',    false, $userRec->strAddr2,   40, 80));
   echoT($clsForm->strGenericTextEntry('City',        'txtCity',     false, $userRec->strCity,    40, 40));
   echoT($clsForm->strGenericTextEntry($gclsChapterVoc->vocState, 'txtState',    false, $userRec->strState,   40, 40));
   echoT($clsForm->strGenericTextEntry('Country',     'txtCountry',  false, $userRec->strCountry, 40, 40));
   echoT($clsForm->strGenericTextEntry($gclsChapterVoc->vocZip, 'txtZip',      false, $userRec->strZip,     15, 15));
   $clsForm->strExtraFieldText = form_error('txtEmail');
   echoT($clsForm->strGenericTextEntry('Email',       'txtEmail',    true,  $userRec->strEmail,   40, 80));
   echoT($clsForm->strGenericTextEntry('Phone',       'txtPhone',    false, $userRec->strPhone,   20, 40));
   echoT($clsForm->strGenericTextEntry('Cell',        'txtCell',     false, $userRec->strCell,    20, 40));

   echoT($clsForm->strSubmitEntry('Submit', 1, 'cmdSubmit', 'text-align: center; width: 100pt;'));

   echoT('</table>'); closeBlock();
}

function showUserInfo(&$clsForm, $lTableWidth, $lLabelWidth, $strLabel, $userRec, $bNew, $bAsAdmin){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gdteNow, $glUserID;
   $bReadOnly = false;

   openBlock($strLabel, '');  echoT('<table class="enpView" >');

   if ($userRec->lKeyID <= 0){
      echoT($clsForm->strLabelRow('userID',  '<i>new</i>', 1));
   }else {
      echoT($clsForm->strLabelRow('userID',  str_pad($userRec->lKeyID, 5, '0', STR_PAD_LEFT), 1));
   }

      //------------------------
      // First Name
      //------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 7pt; width: '.$lLabelWidth.'pt;';
   $clsForm->strExtraFieldText = form_error('txtFName');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('First Name', 'txtFName', true, $userRec->strFName, 40, 80));

      //------------------------
      // Last Name
      //------------------------
   $clsForm->strExtraFieldText = form_error('txtLName');
   echoT($clsForm->strGenericTextEntry('Last Name', 'txtLName', true, $userRec->strLName, 40, 80));

      //------------------------
      // User Name
      //------------------------
   $clsForm->strExtraFieldText = form_error('txtUN');
   echoT($clsForm->strGenericTextEntry('User Name', 'txtUN', true, $userRec->strUserName, 40, 50));


      //------------------------
      // Password
      //------------------------
   if (!$bReadOnly && $bAsAdmin){
      $clsForm->strExtraFieldText = form_error('txtPWord1');
      $clsForm->bAddLabelColon = false;
      $strLabel = 'Password:'.($bNew ? '' : '<br><small><i>Leave blank to keep current password</i></small>');

      $clsForm->bPassword = true;
      echoT($clsForm->strGenericTextEntry('Password:'.($bNew ? '' : '<br><small><i>Leave blank to keep current password</i></small>'),
                                          'txtPWord1', $bNew, '', 20, 20));

      $clsForm->strExtraFieldText = form_error('txtPWord2');
      $clsForm->bAddLabelColon = true;
      $clsForm->bPassword = true;
      echoT($clsForm->strGenericTextEntry('Password <small>(again)</small>',
                                          'txtPWord2', $bNew, '', 20, 20));
   }

      //-----------------------------
      // Date format
      //-----------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 3pt;;';
   $clsForm->strExtraFieldText = form_error('rdoDateFormat');
   $strDateFormat = '
              <input type="radio" name="rdoDateFormat" value="M j Y" '
                 .($userRec->enumDateFormat=='M j Y' ? 'checked' : '').'>
                 US (e.g. '.date('M j Y', $gdteNow).')
                 <br>
              <input type="radio" name="rdoDateFormat" value="F j Y" '
                 .($userRec->enumDateFormat=='F j Y' ? 'checked' : '').'>
                 US (e.g. '.date('F j Y', $gdteNow).')
                 <br>
              <input type="radio" name="rdoDateFormat" value="m/d/Y" '
                 .($userRec->enumDateFormat=='m/d/Y' ? 'checked' : '').'>
                 US (e.g. '.date('m/d/Y', $gdteNow).')
                 <br>
              <input type="radio" name="rdoDateFormat" value="j M Y" '
                 .($userRec->enumDateFormat=='j M Y' ? 'checked' : '').'>
                 India/Europe (e.g. '.date('j M Y', $gdteNow).')
                 <br>
              <input type="radio" name="rdoDateFormat" value="j F Y" '
                 .($userRec->enumDateFormat=='j F Y' ? 'checked' : '').'>
                 India/Europe (e.g. '.date('j F Y', $gdteNow).')
                 <br>
              <input type="radio" name="rdoDateFormat" value="d/m/Y" '
                 .($userRec->enumDateFormat=='d/m/Y' ? 'checked' : '').'>
                 India/Europe (e.g. '.date('d/m/Y', $gdteNow).')';
   echoT($clsForm->strLabelRow('Preferred Date Format', $strDateFormat, 1));

      //-----------------------------
      // Measurement Preference
      //-----------------------------
   $clsForm->strExtraFieldText = form_error('rdoMeasureFormat');
   $strMeasure = '
              <input type="radio" name="rdoMeasureFormat" value="English" '
                 .($userRec->enumMeasurePref=='English' ? 'checked' : '').'>
                 US (pounds, inches, etc)
                 <br>
              <input type="radio" name="rdoMeasureFormat" value="metric" '
                 .($userRec->enumMeasurePref=='metric' ? 'checked' : '').'>
                 Metric (kilograms, meters, etc)';
   echoT($clsForm->strLabelRow('Measurement Format', $strMeasure, 1));

   echoT('</table>'); closeBlock();

}

function showUserSubmit($strName, $bNew){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT(
      '<tr>
           <td class="enpRpt" style="text-align: center;" colspan="2">
              <input style="width: 130pt;" type="submit"
                    name="'.$strName.'"
                    onclick="this.disabled=1; this.form.submit();"
                    value="Save"
                    class="btn"
                       onmouseover="this.className=\'btn btnhov\'"
                       onmouseout="this.className=\'btn\'">
           </td>
        </tr>');
}

function genericTableTextRow($lLabelWidth, $strLabel, $bReadOnly,
                              $strText,    $strError, $strFormFieldName,
                              $lSize,      $lMaxSize, $bRequired, $strExtra=''){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------

   echoT('
         <tr>
            <td class="enpRptLabel" width="'.$lLabelWidth.'pt">'
               .($bRequired ? '<b>' : '')
               .$strLabel
               .($bRequired ? '*</b>' : '').'
            </td>
            <td class="enpRpt">');

   if ($bReadOnly){
      echoT('<font style="font-size: 11pt;">'.htmlspecialchars($strText).'</font>');
   }else {
      echoT('
               <input type="text" name="'.$strFormFieldName.'" size="'.$lSize.'" maxlength="'.$lMaxSize.'"
                  onFocus="frmMUser.'.$strFormFieldName.'.style.background=\'#fff\';"
                  value="'.htmlspecialchars($strText).'" '.$strExtra.' >'.$strError);
   }
   echoT('
            </td>
         </tr>');
}

?>