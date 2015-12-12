<?php
   $attributes = array('name' => 'frmLoc', 'id' => 'frmAddEdit');
   echo form_open('accts_camp/campaigns/addEdit/'.$lAcctID.'/'.$lCampID, $attributes);

   echoT('<br><br><table class="enpRptC">');
   echoT($clsForm->strTitleRow( (($bNew ? 'Add New ' : 'Update ').'Campaign for account "'.$strAcctName.'"'), 2, ''));
   $clsForm->strExtraFieldText = form_error('txtCamp'); 
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Campaign', 'txtCamp', true, $strCampaign, 40, 80));
   
   $clsForm->bDisabled = $bProtected || $bAnyGifts;
   if ($clsForm->bDisabled){
      if ($bProtected){
         $clsForm->strExtraFieldText = '<i>This campaign can not be removed</i>';
      }else {
         $clsForm->strExtraFieldText = '<i>Can\'t be removed until all associated<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;gifts have been removed</i>';
      }
   }else {
      $clsForm->strExtraFieldText = 'Check to retire';
   }
   if (!$bNew && !$bProtected){
      echoT($clsForm->strGenericCheckEntry('Retire campaign?', 'chkRetire', 'TRUE', false, false));
   }
   $clsForm->bDisabled = false;

   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', ''));
   echoT('</table>'.form_close('<br><br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');
   
   
   