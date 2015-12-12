<?php

echoT(strLinkAdd_BizContact  ($lBizID, 'Add new business contact', true, '').'&nbsp;'
     .strLinkAdd_BizContact  ($lBizID, 'Add new business contact', false, '').'<br><br>');
     
if ($lNumContacts==0){
   echoT('<i>There are no contacts associated with this business</i><br><br>');
   return;
}     

echoT('
   <table class="enpRptC">
      <tr>
         <td class="enpRptTitle" colspan="10">'
            .'Contacts for '.htmlspecialchars($strBizName).'
      </tr>');
      
echoT('
   <tr>
      <td class="enpRptLabel">
         contactID
      </td>
      <td class="enpRptLabel">
         peopleID
      </td>
      <td class="enpRptLabel">
         Name
      </td>
      <td class="enpRptLabel">
         Relationship
      </td>
      <td class="enpRptLabel">
         Address
      </td>
      <td class="enpRptLabel">
         Phone/Email
      </td>      
   </tr>');   
      
foreach ($contacts as $contact){
   $lContactID = $contact->lBizConRecID;
   $lPeopleID  = $contact->lPeopleID;
   $bSoftCash = $contact->bSoftCash;
   if ($bSoftCash){
      $strSoftCash = '&nbsp;<img src="'.DL_IMAGEPATH.'/misc/dollar.gif" border="0" title="soft cash relationship">';
   }else {
      $strSoftCash = '';
   }
   echoT('
      <tr class="makeStripe">
         <td class="enpRpt" style="text-align: center; width: 65pt;">'
            .str_pad($lContactID, 5, '0', STR_PAD_LEFT).'&nbsp;'
            .strLinkEdit_BizContact($lBizID, $lContactID, $lPeopleID, 'Edit contact info', true).'&nbsp;&nbsp;'
            .strLinkRem_BizContact($lBizID, $lContactID, 'Remove business contact', true, true).'
         </td>
         <td class="enpRpt" style="text-align: center; width: 50pt;">'
            .str_pad($lPeopleID, 5, '0', STR_PAD_LEFT).'&nbsp;'
            .strLinkView_PeopleRecord($lPeopleID, 'View people record', true).'
         </td>
         <td class="enpRpt" style="width: 150pt;">'
            .htmlspecialchars($contact->strLName.', '.$contact->strFName).'
         </td>
         <td class="enpRpt" style="width: 100pt;">'
            .htmlspecialchars($contact->strRelationship).$strSoftCash.'
         </td>
         <td class="enpRpt" style="width: 170pt;">'
            .$contact->strAddress.'
         </td>
         <td class="enpRpt" style="width: 150pt;">'
            .strPhoneCell($contact->strPhone, $contact->strCell, true, true)
                     .'<br>'.$contact->strEmailFormatted.'
         </td>      
      </tr>');   

}      
      
echoT('</table><br>');      

