<?php

   global $genumDateFormat, $glUserID;

//echoT('<div class="directoryLetters">'.$strDirTitle.'</div><br>');

   if ($lNumRecsTot > 0 && $lNumRecsThisPage > 0){
      openUserDirectory(
                    $strTitle,
                    $strLinkBase,          $strImgBase,        $lNumRecsTot,
                    $directoryRecsPerPage, $directoryStartRec, $lNumDisplayRows);

      showSponsorsPayments($sponInfo, $clsForm, $linkOpts, $validation);
   }else {
      echoT('There are no sponsors that meet your search criteria.<br>');
   }


function showSponsorsPayments(&$sponInfo, &$clsForm, &$linkOpts, &$validation){
//                 $strSort, $lSponProgID, $lStartRec, $lRecsPerPage){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   openSponsorPaymentTable($clsForm, $linkOpts);

   foreach ($sponInfo as $spon){

      $lSponID = $spon->lKeyID;
      $lFID = $spon->lForeignID;
      echoT('<input type="hidden" name="hdnAOCID['.$lSponID.']" value="'.$spon->lCommitACO.'">'."\n");
      echoT('<input type="hidden" name="hdnFID['.$lSponID.']"   value="'.$spon->lForeignID.'">'."\n");
      if ($spon->bSponBiz){
         $strFLink = 'bizID: '.str_pad($lFID, 6, '0', STR_PAD_LEFT)
                    .'&nbsp;'.strLinkView_BizRecord($lFID, 'View business record (new window)',
                                   true, 'target="_blank"');
      }else {
         $strFLink = 'peopleID: '.str_pad($lFID, 6, '0', STR_PAD_LEFT)
                    .'&nbsp;'.strLinkView_PeopleRecord($lFID, 'View people record (new window)',
                                   true, 'target="_blank"');
      }
      $strRadioName = 'rdoPayType'.$lSponID;

      $lClientID = $spon->lClientID;
      if (is_null($lClientID)){
         $strClient = 'client: n/a';
      }else {
         $strClient = 'client: '.str_pad($lClientID, 6, '0', STR_PAD_LEFT)
                 .strLinkView_ClientRecord($lClientID, 'View client record (new window)',
                           true, 'target="_blank"').'&nbsp;'
                 .$spon->strClientSafeNameFL;
      }
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="text-align: center;">'
               .str_pad($lSponID, 6, '0', STR_PAD_LEFT).'&nbsp;'
               .strLinkView_Sponsorship($lSponID, 'View sponsorship (new window)', true, 'target="_blank"').'
            </td>');

      echoT('
            <td class="enpRpt" style="width: 230pt;"><b>'
               .$spon->strSponSafeNameLF.'</b><br>'.$strFLink.'<br>'
               .$strClient.'<br>
               program: '.htmlspecialchars($spon->strSponProgram).'
            </td>');


      echoT('
            <td class="enpRpt" style="text-align: right;">'
               .$spon->strCommitACOCurSym.'&nbsp;'
               .number_format($spon->curCommitment, 2).'&nbsp;'
               .$spon->strCommitACOFlagImg
               .'
            </td>');

      echoT('
            <td class="enpRpt" style="vertical-align: top;">'
               .$spon->strCommitACOCurSym.'&nbsp;'
               .'<input type="text"
                     name="'.$spon->strAmountFN.'" style="width: 60pt; text-align: right;"
                     onChange="bUserDataEntered = true;"
                     value="'.$spon->txtAmount.'">'.$validation->amount[$lSponID]
                  .'<br><span style="font-size: 8pt;">'.$spon->strLastPay.'</span>
            </td>');


      echoT('
            <td class="enpRpt">
               <table cellpadding="0" cellspacing="0">
                  <tr>
                     <td style="text-align: right; font-size: 8pt;">
                        check #:
                     </td>
                     <td>
                        <input type="text" style="width: 60pt;"
                           onChange="bUserDataEntered = true;"
                           name="'.$spon->strCheckFN.'"
                           value="'.$spon->txtCheckNum.'">
                     </tr>');

      echoT('     <tr>
                     <td style="text-align: right; vertical-align: top; font-size: 8pt; padding-top: 4px;">
                        payment type:
                     </td>
                     <td>'
                        .$spon->strDDLPayType.$validation->paymentType[$lSponID].'
                     </td>
                  </tr>');
         //----------------------
         // Payment Date
         //----------------------
      echoT('     <tr>
                     <td style="text-align: right; vertical-align: top; font-size: 8pt; padding-top: 6px;">
                        payment date:
                     </td>
                     <td>'
                      .$clsForm->strGenericDatePicker(
                         '', 'txtPayDate'.$lSponID,      true,
                         $spon->txtPayDate,    'frmBatchPayments', 'datepicker'.$lSponID,
                         '', true)
                         .$validation->paymentDate[$lSponID].'
                     </td>
                  </tr>');

      echoT('
               </table>
            </td>');

      echoT('
         </tr>');

      echoT(strDatePicker('datepicker'.$lSponID, true));

   }

   closeSponsorPaymentTable();

}

function strUpdateButton($strName, $strLabel){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   return(
        '<input type="button" name="'.$strName.'"
              onclick="this.disabled=1; this.form.submit();"
              class="btn"
                 onmouseover="this.className=\'btn btnhov\'"
                 onmouseout="this.className=\'btn\'"
              value="'.$strLabel.'" style="margin-top: 4px; margin-bottom: 4px;">');
}

function openSponsorPaymentTable(&$clsForm, &$linkOpts){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 110pt;';

   $attributes = array('name' => 'frmBatchPayments');
   echoT(form_open('sponsors/batch_payments/showSponsors/'
                   .$linkOpts->strSort.'/'.$linkOpts->lSponProgID.'/'
                   .$linkOpts->lStartRec.'/'.$linkOpts->lRecsPerPage, $attributes));
   echoT('<input type="hidden" name="hdnFormVerify" value="1">'."\n");

   echoT(strUpdateButton('btnTop', 'Record payments').'<br>');

   echoT('
      <table class="enpRpt">
         <tr>
            <td class="enpRptLabel">
               sponID
            </td>
            <td class="enpRptLabel">
               Sponsor
            </td>
            <td class="enpRptLabel">
               Commitment
            </td>
            <td class="enpRptLabel">
               Amount
            </td>
            <td class="enpRptLabel">
               Payment Info
            </td>
         </tr>');
}

function closeSponsorPaymentTable(){
   echoT('<table>');
   echoT(strUpdateButton('btnBottom', 'Record payments').'<br></form>');
}


function openUserDirectory(
                    $strTitle,
                    &$strLinkBase, &$strImgBase, &$lTotRecs,
                    $lRecsPerPage, $lStartRec,   $lNumThisPage){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT(
      '<br>
       <table class="enpRpt" id="myLittleTable">
           <tr>
              <td class="enpRptLabel">
                 Batch Sponsorship Payments: <b>'.htmlspecialchars($strTitle).'</b>
              </td>
           </tr>
           <tr>
              <td class="recSel">');

   $strLinkBase = $strLinkBase;
   $strImgBase = base_url().'images/dbNavigate/rs_page_';

   $opts = new stdClass;
   $opts->strLinkBase         = $strLinkBase;
   $opts->strImgBase          = $strImgBase;
   $opts->lTotRecs            = $lTotRecs;
   $opts->lRecsPerPage        = $lRecsPerPage;
   $opts->lStartRec           = $lStartRec;
   $opts->strLinkExtra        = $strLinkExtra = '';

   $strURL = site_url($strLinkBase.$lStartRec).'/';

   $opts->strCustomOnChangeDDL =
        "    if (bUserDataEntered){
                  if (confirm('The changes you made to this page have not been saved yet. \\n\\n"
                     ."Are you sure you want to leave this page and lose your changes?')){
                      location.href='#link#' + this.options[this.selectedIndex].value + ''
                 }
             }else {
                location.href='#link#' + this.options[this.selectedIndex].value + ''
             }
          ";

   $opts->strCustomOnClick =
        "    if (bUserDataEntered){
                  if (confirm('The changes you made to this page have not been saved yet. \\n\\n"
                     ."Are you sure you want to leave this page and lose your changes?')){ "
                     ."location.href='#link#'  "
                 ."} "
             ."}else { "
                ."location.href='#link#'  "
             ."} "
          ;


   echoT(set_RS_Navigation($opts));

   echoT(
      '<i>Showing records '.($lStartRec+1).' to '.($lStartRec+$lNumThisPage)
         ." ($lTotRecs total)</i>");

   echoT('</td></tr></table><br>');

}
