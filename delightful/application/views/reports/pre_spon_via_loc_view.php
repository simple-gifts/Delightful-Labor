<?php
   global $gbDateFormatUS, $gstrFormatDatePicker, $gdteNow;

   $attributes =
       array(
            'name'     => 'frmSponLocRpt',
            'id'       => 'sponProgRpt'
            );

   echoT(form_open('reports/pre_spon_via_prog/runViaLoc',  $attributes));

   openBlock('Sponsorships Via Client Location', '');
   
   if ($lNumLocs<=0){
      echoT('<br><br><i>There are no client locations defined in your database.</i>');
   }else {
   
      $clsForm = new generic_form;
      $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
      $clsForm->strTitleClass = 'enpViewTitle';
      $clsForm->strEntryClass = 'enpView';
      $clsForm->strStyleExtraLabel = 'width: 120pt;';
      $clsForm->bValueEscapeHTML = false;
      echoT('<table width="800" border="0">');

         //----------------------------
         // client locations
         //----------------------------      
      $strDDLOptions = '';
      $bFirst = true;
      foreach ($clientLocations as $clientLoc){
         $strDDLOptions .= '<option value="'.$clientLoc->lKeyID.'" >'
            .htmlspecialchars($clientLoc->strLocation).'</option>'."\n";
      }
      echoT($clsForm->strGenericDDLEntry('Client Location', 'ddlClientLoc', false, $strDDLOptions));
      
      $clsForm->strExtraFieldText = '(check to include inactive sponsorships)';
      echoT($clsForm->strGenericCheckEntry('Include inactive', 'chkInactive', 'true', false, false));
      
      
      $clsForm->strStyleExtraLabel = 'text-align: left;';
      echoT($clsForm->strSubmitEntry('Run Report', 2, 'cmdSubmit', 'text-align: left;'));
      
      echoT('</table>'.form_close('<br>'));
   }
   closeblock();
