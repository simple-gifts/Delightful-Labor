<?php
   $attributes =
       array(
            'name'     => 'frmLogSearchRpt',
            'id'       => 'logSearchRpt'
            );
   echoT(form_open('reports/pre_log_search/searchOpts')); //,  $attributes));

   openBlock('Log search', '');

      //---------------------------------
      // form setup
      //---------------------------------
   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;
   echoT('<table width="800" border="0">');

      //---------------------------------
      // search text
      //---------------------------------
   $clsForm->strStyleExtraLabel = 'width: 50pt; padding-top: 8px;';
   $clsForm->strExtraFieldText = form_error('txtSearch');
   $clsForm->strID = 'searchEntry';
   echoT($clsForm->strGenericTextEntry('Search', 'txtSearch', false, $strSearch, 40, 200));

      //---------------------------------
      // search qualifier
      //---------------------------------
   $clsForm->strStyleExtraLabel = 'padding-top: 3px;';
   $rdoCriteria = '
        <input type="radio" name="rdoCriteria" value="phrase" '.($criteria->bPhrase ? 'checked' : '').'>Exact phrase&nbsp;&nbsp;
        <input type="radio" name="rdoCriteria" value="all"    '.($criteria->bAll    ? 'checked' : '').'>All words&nbsp;&nbsp;
        <input type="radio" name="rdoCriteria" value="any"    '.($criteria->bAny    ? 'checked' : '').'>Any word<br>
        <i><font style="font-size:12px;">
             Note: short or common words (like </i><b>the</b><i> and </i><b>and</b><i>) may 
             be ignored for the "all" and "any" options</font><i>';
        
   echoT($clsForm->strLabelRow('Look for', $rdoCriteria, 1));

      //---------------------------------
      // search in
      //---------------------------------
   $clsForm->strExtraFieldText = form_error('chkLogs[]');
   $strSearchIn = '';
   
   if (bAllowAccess('showClients')){
      $strSearchIn .= '
          <input type="checkbox" name="chkLogs[]" value="clientStatus" '
                        .($bCheckedLog->clientStatus ? 'checked' : '').'>Client status notes<br>
          <input type="checkbox" name="chkLogs[]" value="clientBio" '
                        .($bCheckedLog->clientBio ? 'checked' : '').'>Client bios<br>'."\n";
   }
                        
   $strSearchIn .= '                        
          <input type="checkbox" name="chkLogs[]" value="docsImages" '
                        .($bCheckedLog->docsImages ? 'checked' : '').'>Document and Image descriptions<br>'."\n";
                        
   if (bAllowAccess('showFinancials')){
      $strSearchIn .= '
          <input type="checkbox" name="chkLogs[]" value="giftNotes" '
                        .($bCheckedLog->giftNotes ? 'checked' : '').'>Donation notes<br>
      ';
   }
   
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$pTabs   <pre>');
echo(htmlspecialchars( print_r($pTabs, true))); echo('</pre></font><br>');
// ------------------------------------- */
   
   foreach($pTabs as $pTab){
      $enumTType = $pTab->enumTType;
      if (bAllowAccess('showImagesDocs', $enumTType)){
   
         $strLabel = 'Personalized '.$pTab->strTTypeLabel.' tables';
         $lNumLF = $pTab->lNumLogFields;
         if ($lNumLF == 0){
            $strCount = '(no log fields)';
         }elseif ($lNumLF==1) {
            $strCount = '(one log field)';
         }else {
            $strCount = '('.$lNumLF.' log fields)';
         }
         $strSearchIn .= 
                 ($lNumLF==0 ? '<i><font style="color: #999;">' : '')
                .'<input type="checkbox" name="chkLogs[]" value="'.$enumTType.'" '
                      .($lNumLF == 0 ? ' disabled="disabled" ' : '').' '
                      .($bCheckedLog->$enumTType ? 'checked' : '').'>'.$strLabel.' '.$strCount
                .($lNumLF==0 ? '</i></font>' : '')
                      .'<br>';
         if ($lNumLF == 0){
         }else {
         }
      }
   }

   $clsForm->strStyleExtraValue = 'vertical-align: bottom;';
   echoT($clsForm->strLabelRow('Look in', $strSearchIn, 1));


   $clsForm->strStyleExtraLabel = 'text-align: left;';
   echoT($clsForm->strSubmitEntry('Search', 2, 'cmdSubmit', 'text-align: center; width: 70pt;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmLogSearchRpt.searchEntry.focus();</script>');

   closeblock();


