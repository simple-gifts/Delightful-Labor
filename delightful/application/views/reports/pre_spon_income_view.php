<?php
   global $gdteNow;

   $attributes =
       array(
            'name'     => 'frmPastDueRpt',
            'id'       => 'pastDueRpt'
            );

   echoT(form_open('reports/pre_spon_income/run',  $attributes));



   openBlock('Sponsorship Income', '');
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
   $lYear = (integer)date('Y', $gdteNow);
   $strYearDDL = '<select name="ddlYear">';
   for ($idx=$lYear; $idx >= 2000; --$idx){
      $strYearDDL .= '<option value="'.$idx.'" '.($idx==$lYear ? 'selected' : '').' >'.$idx.'</option>'."\n";
   }
   $strYearDDL .= '</select>';
   echoT($clsForm->strLabelRow('Income for the year', $strYearDDL, 1));
   
  
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

   
   
   
   
   
