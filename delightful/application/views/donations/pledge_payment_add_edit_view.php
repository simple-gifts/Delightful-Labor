<?php
   global $genumDateFormat;

   $attributes = array('name' => 'frmEditPledge', 'id' => 'frmAddEdit');
   echoT(form_open('donations/pledge_add_edit/addEditPayment/'.$lPledgeID.'/'.$lGiftID.'/'.$dtePledge, $attributes));

   $clsForm = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 100pt;';

   openBlock('Pledge Payment', ''); echoT('<table>');
   
      //-------------------------------
      // Pledge ID
      //-------------------------------
   echoT($clsForm->strLabelRow('Pledge ID', str_pad($lPledgeID, 5, '0', STR_PAD_LEFT)
                    .'&nbsp;'.strLinkView_Pledge($lPledgeID, 'View pledge record', true), 1));
                    
      //-------------------------------
      // Gift ID
      //-------------------------------
   if (is_null($lGiftID)){
      $strGiftID = 'n/a';
   }else {
      $strGiftID = str_pad($lGiftID, 5, '0', STR_PAD_LEFT);
   }
   echoT($clsForm->strLabelRow('Gift ID', str_pad($lGiftID, 5, '0', STR_PAD_LEFT), 1));
                    
   
      //-------------------------------
      // Commitment
      //-------------------------------
   echoT($clsForm->strLabelRow('Commitment', $pledge->strACOCurSymbol.' '.number_format($pledge->curCommitment, 2)
                     .' ('.$pledge->enumFreq.') '.$pledge->strFlagImg, 1));
   
      //-------------------------------
      // Campaign/Account
      //-------------------------------
   echoT($clsForm->strLabelRow('Campaign/Account', htmlspecialchars($pledge->strCampaign.' / '.$pledge->strAccount), 1));
   
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$pledge   <pre>');
echo(htmlspecialchars( print_r($pledge, true))); echo('</pre></font><br>');
// ------------------------------------- */
   
      //-------------------------------
      // Amount
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strExtraFieldText  = $pledge->strFlagImg.form_error('txtAmount');
   $clsForm->strTxtControlExtra = ' style="text-align: right;" ';
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Amount', 'txtAmount', true, $formData->txtAmount, 10, 20));
   
   
      //-------------------------------
      // Date of donation
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('txtDDate');
   echoT($clsForm->strGenericDatePicker(
                      'Date',    'txtDDate',      true,
                      $formData->txtDDate,
                      'frmEditPledge', 'datepickerFuture'));

      //-------------------------------
      // Check number
      //-------------------------------
   echoT($clsForm->strGenericTextEntry('Check number', 'txtCheckNum', false,
                              $formData->strCheckNum, 30, 255));

      //-------------------------------
      // Payment type
      //-------------------------------
   $clsForm->strExtraFieldText = form_error('ddlPayType');
   echoT($clsForm->strLabelRow('Payment type*', $formData->strDDLPayType, 1));

                      
      //-------------------------------
      // Major gift category
      //-------------------------------
   echoT($clsForm->strLabelRow('Major gift category', $formData->strDDLMajGiftType, 1));
   
   
   
      //-------------------------------
      // Notes
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 2pt; ';
   $clsForm->strExtraFieldText = form_error('txtNotes');
   echoT($clsForm->strNotesEntry('Notes', 'txtNotes', false, $formData->strNotes, 2, 40));                      
   
   
   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'width: 100pt;'));
   echoT(form_close());
   echoT('<script type="text/javascript">frmEditPledge.addEditEntry.focus();</script>');

   echoT('</table>'); closeBlock(); 
   
