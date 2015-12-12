<?php
   $attributes = array('name' => 'frmLoc', 'id' => 'frmAddEdit');
   echoT(form_open('accts_camp/accounts/addEdit/'.$lAcctID, $attributes));
   $clsForm->bValueEscapeHTML = false;
   
   echoT('<br><br><table class="enpRptC">');
   echoT($clsForm->strTitleRow( (($bNew ? 'Add New ' : 'Update ').'Account'), 2, ''));
   
   $clsForm->strExtraFieldText = form_error('txtAcct');      
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Account', 'txtAcct', true, $strAccount, 40, 80));
   
   $clsForm->bDisabled = $clsAcct->bProtected || $bAnyGifts;
   if ($clsForm->bDisabled){
      if ($clsAcct->bProtected){
         $clsForm->strExtraFieldText = '<i>This account can not be removed</i>';
      }else {
         $clsForm->strExtraFieldText = '<i>Can\'t be removed until all associated<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;gifts have been removed</i>';
      }
   }else {
      $clsForm->strExtraFieldText = 'Check to retire';
   }
   if (!$bNew && !$clsAcct->bProtected){
      echoT($clsForm->strGenericCheckEntry('Retire account?', 'chkRetire', 'TRUE', false, false));
   }
   $clsForm->bDisabled = false;

   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', ''));
   echoT('</table>'.form_close('<br><br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');
   


?>