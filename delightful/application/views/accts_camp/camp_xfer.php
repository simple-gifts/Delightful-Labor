<?php

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   echoT(form_open('accts_camp/campaigns/xferCampaignSubmit/'.$lCampID));
   
   $attributes = new stdClass;
   $attributes->lTableWidth      = 800;
   $attributes->lUnderscoreWidth = 300;
   openBlock('Transfer Campaign', '', $attributes);
   
   echoT('<table class="enpView">');
   
   echoT($clsForm->strLabelRow('campaignID',  str_pad($lCampID, 5, '0', STR_PAD_LEFT), 1));
   echoT($clsForm->strLabelRow('Campaign Name',  $strCamp, 1));
   echoT($clsForm->strLabelRow('Current Account',  $strAcct, 1));
   
   $strDDL = '';
   foreach ($accts as $acct){
      $lKeyID = $acct->lKeyID;
      if ($lKeyID != $lAcctID){
         $strDDL .= '<option value="'.$lKeyID.'" >'.$acct->strSafeName.'</option>'."\n";
      }
   }
   $clsForm->strStyleExtraLabel = 'width: 80pt; padding-top: 8px;';
   echoT($clsForm->strGenericDDLEntry(
              'New Account', 'ddlAcct',
              true, $strDDL));

   
   echoT($clsForm->strSubmitEntry('Transfer Campaign', 2, 'cmdSubmit', 'text-align: left;'));
   echoT('</table>'.form_close('<br>'));
   
   closeBlock();      
   
   
