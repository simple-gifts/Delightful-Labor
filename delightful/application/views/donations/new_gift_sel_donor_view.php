<?php
   $attributes = array('name' => 'frmNewGift', 'id' => 'newGift');
   echoT(form_open('donations/add_edit/new_gift', $attributes));

   $clsForm = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 100pt;';

   openBlock('Select Donor', ''); echoT('<table>');
   
      //-------------------------------
      // Donor Name
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strExtraFieldText = form_error('ddlNames');
   $clsForm->strID = 'donorName';
   
   $clsForm->strTxtControlExtra = ' onkeyup="showResult(this.value)" autocomplete="off" ';
   $clsForm->strExtraFieldText .= '
                <span id="notFound" style="visibility: hidden;">(no matches)</span> 
                <br><select name="ddlNames" id="selNames" 
                        size="4" 
                        style="visibility: hidden;"
                        onchange="populateSearch(); this.form.submit();"></select>
                        
                        ';
   
echoT('<!--  ------------------------------------------------------------- -->');
   echoT($clsForm->strGenericTextEntry('Donor Name', 'txtDonor', true, '', 40, 120));
echoT('<!--  ------------------------------------------------------------- -->');
   
   echoT(form_close());
   echoT('<script type="text/javascript">frmNewGift.donorName.focus();</script>');
   
   echoT('</table>'); closeBlock(); 
   
