<?php
   $attributes = array('name' => 'frmAjaxDemo', 'id' => 'ajaxDemo');
   echoT(form_open('auctions/demo/ajax_test', $attributes));

   $clsForm = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 100pt;';


   openBlock('Ajax Demo', ''); echoT('<table>');
   
   
   
   
      //-------------------------------
      // Name
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strExtraFieldText = form_error('ddlNames');
   $clsForm->strID = 'donorName';
   
   $clsForm->strTxtControlExtra = ' onkeyup="showResult(this.value)" autocomplete="off" ';
   $clsForm->strExtraFieldText  = '
                <span id="notFound" style="visibility: hidden;">(no matches)</span> 
                <br><select name="ddlNames" id="selNames" 
                        size="4" 
                        style="visibility: hidden;"
                        onchange="populateSearch()"></select>
                        
                        ';
   
echoT('<!--  ------------------------------------------------------------- -->');
echoT('<!--  ------------------------------------------------------------- -->');
   echoT($clsForm->strGenericTextEntry('Donor Name', 'txtDonor', true, '', 40, 120));
echoT('<!--  ------------------------------------------------------------- -->');
echoT('<!--  ------------------------------------------------------------- -->');
   
   
   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'width: 100pt;'));
   echoT(form_close());
   echoT('<script type="text/javascript">frmAjaxDemo.donorName.focus();</script>');
   
   
   
   
   
   
   echoT('</table>'); closeBlock(); 
   
