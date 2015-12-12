<?php
   global $gbDateFormatUS, $gstrFormatDatePicker, $gdteNow;

   $attributes =
       array(
            'name'     => $viewOpts->strFormName,
            'id'       => $viewOpts->strID
            );
   $strOnSubmit = '';
   if ($viewOpts->bShowAcct){
//      $attributes['onSubmit'] = 'return(verifyBoxChecked(\''.$viewOpts->strFormName.'\', \'chkAccts[]\', \' (Accounts)\'))';
      $strOnSubmit = 'return(verifyBoxChecked(\''.$viewOpts->strFormName.'\', \'chkAccts[]\', \' (Accounts)\'))';

   }elseif ($viewOpts->bShowCamp){
//      $attributes['onSubmit'] = 'return(verifyBoxChecked(\''.$viewOpts->strFormName.'\', \'chkCamps[]\', \' (Campaigns)\'))';
      $strOnSubmit = 'return(verifyBoxChecked(\''.$viewOpts->strFormName.'\', \'chkCamps[]\', \' (Campaigns)\'))';
   }

   echoT(form_open($frmLink,  $attributes));

   openBlock($viewOpts->blockLabel, '');

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->strStyleExtraLabel = 'width: 90pt;';
   $clsForm->bValueEscapeHTML = false;
   echoT('<table  border="0">');

      //-------------------------------
      // Accounting country of Origin
      //-------------------------------
   if ($viewOpts->bShowACO){
      $clsForm->strStyleExtraLabel = 'vertical-align: middle; width: 90pt; ';
      echoT($clsForm->strLabelRow('Accounting Country', $formData->strACORadio, 1));
      
   }

      //----------------------
      // Year
      //----------------------
   if ($viewOpts->bShowYear){
      $lCurrentYear = (integer)date('Y', $gdteNow);
      $strOut = '<select name="ddlYear">'."\n";
      for ($idx=($lCurrentYear-20); $idx<=($lCurrentYear+1); ++$idx){
         $strOut .= '<option value="'.$idx.'" '.($idx==$formData->lYearSel ? 'SELECTED' : '').'>'
                 .$idx.'</option>'."\n";
      }
      $strOut .= '</select>'."\n";
      echoT($clsForm->strLabelRow('Year', $strOut, 1));
   }
   
      //--------------------------------
      // Time Frame
      //--------------------------------
   if ($viewOpts->bShowTimeFrame){
      $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6px;';
      $clsForm->strStyleExtraValue = 'vertical-align: top;';
      echoT($clsForm->strLabelRow('Time Frame', $dateRanges, 1));
   }   

      //--------------------------------
      // Include
      //--------------------------------
   if ($viewOpts->bShowIncludes){
      $clsForm->strStyleExtraLabel = '';
      echoT($clsForm->strLabelRow('Include',
                         '<input type="radio" name="rdoInc" value="all"  '.($formData->enumInc=='all'   ? 'checked' : '').'>All&nbsp;'
                        .'<input type="radio" name="rdoInc" value="gift" '.($formData->enumInc=='gift'  ? 'checked' : '').'>Exclude Sponsor Payments&nbsp;'
                        .'<input type="radio" name="rdoInc" value="spon" '.($formData->enumInc=='spon'  ? 'checked' : '').'>Only Sponsor Payments&nbsp;'
                        , 1));
   }
//echo(__FILE__.' '.__LINE__.'<br>'."\n"); die;

      //--------------------------------
      // Aggregate
      //--------------------------------
   if ($viewOpts->bShowAggregateDonor){
      echoT($clsForm->strLabelRow('Grouping',
                         '<input type="radio" name="rdoAggDonor" value="all"   '.($formData->enumAgg=='all'   ? 'checked' : '').'>Individual Donors&nbsp;'
                        .'<input type="radio" name="rdoAggDonor" value="group" '
                                  .($formData->enumAgg=='group' ? 'checked' : '').'>Group by Donor (cumulative)&nbsp;'
                        , 1));
   }

      //-------------------------------
      // Min Amount
      //-------------------------------
   if ($viewOpts->bShowMinAmnt){
      $clsForm->strStyleExtraLabel = 'padding-top: 8px;';
      $clsForm->strExtraFieldText = form_error('txtMinAmount');
      $clsForm->strID = 'addEditEntry';
      echoT($clsForm->strGenericTextEntry('Minimum Amount', 'txtMinAmount', false, $formData->strMinAmount, 14, 20));
   }

      //-------------------------------
      // Max Amount
      //-------------------------------
   if ($viewOpts->bShowMaxAmnt){
      $clsForm->strStyleExtraLabel = 'padding-top: 8px;';
      $clsForm->strExtraFieldText = form_error('txtMaxAmount');
      $clsForm->strID = 'addEditEntry';
      echoT($clsForm->strGenericTextEntry('Maximum Amount', 'txtMaxAmount', false, $formData->strMaxAmount, 14, 20));
   }

      //-------------------------------
      // Account
      //-------------------------------
   if ($viewOpts->bShowAcct){
      $clsForm->strStyleExtraLabel = '';
      $strAccts =
         '<div id="scrollCB" style="height:90px; width:340px; overflow: auto; border: 1px solid black;">'."\n\n";
      foreach ($accts as $acct){
         $strAccts .= '<input type="checkbox" name="chkAccts[]" 
                           value="'.$acct->lKeyID.'"  '.($acct->bSel ? 'checked' : '').'>'."\n"
                     .htmlspecialchars($acct->strAccount).'<br>'."\n";
      }
      $strAccts .= '</div>';
      $clsForm->strExtraFieldText = form_error('txtAccts');
      echoT($clsForm->strLabelRow('Accounts', $strAccts, 1));
   }

      //-------------------------------
      // Campaigns
      //-------------------------------
   if ($viewOpts->bShowCamp){
      $strCamps =
         '<div id="scrollCB" style="height:120px; width:340px; overflow: auto; border: 1px solid black;">'."\n\n";
      $lAcctGroup = -999;   
      foreach ($camps as $camp){
         $lAcctID = $camp->lAcctID;
         if ($lAcctID != $lAcctGroup){
            if ($lAcctGroup > 0) $strCamps .= '<br>'."\n";
            $lAcctGroup = $lAcctID;
            $strCamps .= '<b><i>'.$camp->strAcctSafeName.'</i></b><br>'."\n";
         }
         $strCamps .= '&nbsp;&nbsp;&nbsp;&nbsp;'
                     .'<input type="checkbox" name="chkCamps[]" 
                           value="'.$camp->lKeyID.'"  '.($camp->bSel ? 'checked' : '').'>'."\n"
                     .$camp->strSafeName.'<br>'."\n";
      }
      $strCamps .= '</div>';
      $clsForm->strExtraFieldText = form_error('txtCamps');
      echoT($clsForm->strLabelRow('Campaigns', $strCamps, 1));
   }
   
      //--------------------------------
      // Sort by
      //--------------------------------
   if ($viewOpts->bShowSortBy){
      $clsForm->strStyleExtraLabel = 'padding-top: 2px;';
      echoT($clsForm->strLabelRow('Sort by',
                         '<input type="radio" name="rdoSort" value="date"  '.($formData->enumSort=='date'  ? 'checked' : '').'>Date&nbsp;'
                        .'<input type="radio" name="rdoSort" value="amnt"  '.($formData->enumSort=='amnt'  ? 'checked' : '').'>Amount&nbsp;'
                        .'<input type="radio" name="rdoSort" value="donor" '.($formData->enumSort=='donor' ? 'checked' : '').'>Donor&nbsp;'
                        , 1));
   }


   $clsForm->strStyleExtraLabel = 'text-align: left;';
   echoT($clsForm->strSubmitEntry('Run Report', 2, 'cmdSubmit', 'text-align: center;',
             $strOnSubmit));
   echoT('</table>'.form_close('<br>'));

   closeblock();


