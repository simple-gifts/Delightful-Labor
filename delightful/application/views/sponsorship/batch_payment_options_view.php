<?php

   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 110pt;';

   $attributes = array('name' => 'frmBatchPaymentOpts');
   echoT(form_open('sponsors/batch_payments/batchSelectOpts', $attributes));

   openBlock('Sponsorship Batch Payment Options', '');
   
   echoT('
      <table class="enpView">');      

   echoT('
      <tr><td colspan="3"><i>This utility allows you to record multiple sponsorship payments at one time.</i></td></tr>'); 
   
      //-------------------------------
      // Sponsorship Program
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtAmount');
   $clsForm->strStyleExtraLabel = 'width: 110pt; padding-top: 6px;';
   echoT($clsForm->strLabelRow('Sponsorship Program', $strSponProgs, 1));

      //-------------------------------
      // Viewing Options
      //-------------------------------
   $strSort = 
      '<input type="radio" name="rdoSort" value="name"     '.($strSort=='name'     ? 'checked' : '').'>Sponsor\'s name<br>'."\n"
     .'<input type="radio" name="rdoSort" value="acoName"  '.($strSort=='acoName'  ? 'checked' : '').'>Accounting Country / Sponsor\'s name<br>'."\n"
     .'<input type="radio" name="rdoSort" value="id"       '.($strSort=='id'       ? 'checked' : '').'>Sponsorship ID<br>'."\n"
     .'<input type="radio" name="rdoSort" value="client"   '.($strSort=='client'   ? 'checked' : '').'>Client\'s name<br>'."\n"
      ;
   $clsForm->strExtraFieldText = form_error('rdoSort');
   $clsForm->strStyleExtraLabel = 'padding-top: 2px;';
   echoT($clsForm->strLabelRow('Sorting Options', $strSort, 1));

   echoT($clsForm->strSubmitEntry('Continue', 2, 'cmdSubmit', 'width: 100pt;'));      
   
   echoT('</table></form><br><br>');
   closeBlock();
   
