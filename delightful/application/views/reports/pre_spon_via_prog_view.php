<?php
   global $gbDateFormatUS, $gstrFormatDatePicker, $gdteNow;

   $attributes =
       array(
            'name'     => 'frmSponProgRpt',
            'id'       => 'sponProgRpt'
            );

   echoT(form_open('reports/pre_spon_via_prog/run',  $attributes));

   openBlock('Sponsorships Via Program', '');
   
   if ($lNumProgs<=0){
      echoT('<br><br><i>There are no sponsorship programs defined in your database</i>');
   }else {
   
      $clsForm = new generic_form;
      $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
      $clsForm->strTitleClass = 'enpViewTitle';
      $clsForm->strEntryClass = 'enpView';
      $clsForm->strStyleExtraLabel = 'width: 120pt;';
      $clsForm->bValueEscapeHTML = false;
      echoT('<table width="800" border="0">');

         //----------------------------
         // sponsorship program
         //----------------------------      
      $strDDLOptions = '';
      $bFirst = true;
      foreach ($sponProgs as $sponProg){
         $strDDLOptions .= '<option value="'.$sponProg->lKeyID.'" '.($sponProg->bDefault ? 'SELECTED' : '').'>'
            .htmlspecialchars($sponProg->strProg).'</option>'."\n";
      }
      echoT($clsForm->strGenericDDLEntry('Sponsorship Program', 'ddlSponProg', false, $strDDLOptions));
      
      $clsForm->strExtraFieldText = '(check to include inactive sponsorships)';
      echoT($clsForm->strGenericCheckEntry('Include inactive', 'chkInactive', 'true', false, false));
      
      
      $clsForm->strStyleExtraLabel = 'text-align: left;';
      echoT($clsForm->strSubmitEntry('Run Report', 2, 'cmdSubmit', 'text-align: left;'));
      
      echoT('</table>'.form_close('<br>'));
   }
   closeblock();
   
   
