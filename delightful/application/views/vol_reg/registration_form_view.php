<?php


   echoT(strLinkAdd_VolReg_RegForm('Add new volunteer registration form', true).'&nbsp;'
        .strLinkAdd_VolReg_RegForm('Add new volunteer registration form', false).'<br><br>');

   if ($lNumRegRecs==0){
      echoT('<i>No volunteer registration forms have been created.</i>');
   }else {
      openVolRegTable();
      
      writeVolRegTable($regRecs);
      
      closeVolRegTable();
   }

   function writeVolRegTable(&$regRecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      foreach ($regRecs as $rRec){
         $lVolRegFormID = $rRec->lKeyID;
         $strSpacer = ($rRec->strDescription=='' ? '' : '<br><br>');
         
         $lNumVolsViaForm = $rRec->lNumVols;
         if ($lNumVolsViaForm == 0){
            $strVRegCnt = '<i>No volunteers have registered with this form.</i>';
         }else {
            $strVRegCnt = 
                $lNumVolsViaForm.' volunteer'.($lNumVolsViaForm==1 ? '' : 's')
               .' have registered with this form.<br>'
               .strLinkView_VolsViaRegFormID($lVolRegFormID, 'View Volunteers', true).'&nbsp;'
               .strLinkView_VolsViaRegFormID($lVolRegFormID, 'View Volunteers', false).'<br>'
               .strLinkView_VolsRecentViaRegFormID($lVolRegFormID, 'Recent Registrations', true).'&nbsp;'
               .strLinkView_VolsRecentViaRegFormID($lVolRegFormID, 'Recent Registrations', false);
         }
         echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align: center;">'
                  .str_pad($lVolRegFormID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkEdit_VolRegForm($lVolRegFormID, 'Edit volunteer registration form', true).'
               </td>
               <td class="enpRpt">'
                  .strLinkRem_VolRegForm($lVolRegFormID, 'Remove volunteer registration form.', true, true).'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($rRec->strFormName).'<br><br>'
                  .$strVRegCnt.'
               </td>
               <td class="enpRpt" style="width: 200pt;">'
                  .nl2br(htmlspecialchars($rRec->strDescription)).$strSpacer              
                  .'form URL: <a href="'.$rRec->strVolRegFormURL.'" target="_blank">'.$rRec->strVolRegFormURL.'</a><br>
                  <i>Note: for security reasons, clicking this link will log you out of your current Delightful Labor session.</i>
               </td>
            </tr>');
      }
   }

   function openVolRegTable(){
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
                  Description
               </td>
            </tr>');
   }

   function closeVolRegTable(){
      echoT('
         </table><br><br>');
   }



