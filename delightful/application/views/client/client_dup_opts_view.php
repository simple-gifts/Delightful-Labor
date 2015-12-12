<?php
//   global $gbDateFormatUS, $gstrFormatDatePicker;

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;
   
   $attributes = array('name' => 'frmClientDups', 'id' => 'frmAddEdit');
   echoT(form_open('clients/client_dups/opts', $attributes));
   
   
   openBlock('Consolidate Duplicate Client Records', '');
   echoT('<table class="enpView">');
   
      //----------------------
      // Good client ID
      //----------------------
   $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 8px;';   
   $clsForm->strExtraFieldText = form_error('txtGoodCID');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Correct client ID',  'txtGoodCID', true,  $formData->txtGoodCID, 8, 8));


      //----------------------
      // Bad client ID
      //----------------------
   $clsForm->strExtraFieldText = 
                  '<br><i>Enter one or more comma-seperated duplicate client IDs.</i>'
                 .form_error('txtBadCIDs');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Duplicate client IDs',  'txtBadCIDs', true,  $formData->txtBadCIDs, 60, 120));




   
   echoT($clsForm->strSubmitEntry('Review Client Records', 2, 'cmdSubmit', 'text-align: center;'));
   
   
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');  
   echoT('<i>This utility will transfer client information from the duplicate records<br>
             to the correct record. The transferred information includes:<br>
             <ul>
                <li>Sponsorships
                <li>Multi-record personalized client table information
                <li>Client program enrollment and attendance records
             </ul>
          You will have an opportunity to review the client records before 
          committing to this operation.<br>');

   closeBlock();      
   
   
