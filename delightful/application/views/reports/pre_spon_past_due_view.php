<?php

   $attributes =
       array(
            'name'     => 'frmPastDueRpt',
            'id'       => 'pastDueRpt'
            );

   echoT(form_open('reports/pre_spon_past_due/run',  $attributes));

   openBlock('Sponsor Past Due', '');
   echoT('<table border="0">');

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;
   $clsForm->strStyleExtraLabel = 'width: 100pt;';   
   
      //-------------------------------
      // Accounting country of Origin
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: middle; ';
   echoT($clsForm->strLabelRow('Accounting Country', $formData->strACORadio, 1));
   
      //-------------------------------
      // Past Due Duration
      //-------------------------------
   $clsForm->strStyleExtraLabel = '';
   echoT($clsForm->strLabelRow('Past Due Duration', 
                     'At least 
                      <select name="ddlPastDue">
                        <option value="1">1</option> 
                        <option value="2" selected>2</option> 
                        <option value="3">3</option> 
                        <option value="4">4</option> 
                        <option value="5">5</option> 
                        <option value="6">6</option> 
                      </select> months past due'
                       , 1));
   
      //-------------------------------
      // Include inactive?
      //-------------------------------
   echoT($clsForm->strGenericCheckEntry('Include inactive?', 'chkInactive', 'true', false, false) );  
   
      //----------------------
      // Sponsorship Program
      //----------------------
//   $clsForm->strExtraFieldText = form_error('rdoGender');
//   echoT($clsForm->strGenderRadioEntry('Gender', 'rdoGender', true, $formData->rdoGender));
   
   $clsForm->strStyleExtraLabel = ' text-align: left; ';   
   echoT($clsForm->strSubmitEntry('Run Report', 2, 'cmdSubmit', 'width: 150pt;'));
   
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">skillsRpt.addEditEntry.focus();</script>');

   closeblock();
   
   
   
