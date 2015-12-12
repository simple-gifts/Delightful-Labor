<?php
//   global $gbDateFormatUS, $gstrFormatDatePicker;

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;
   
   $attributes = array('name' => 'frmClientDups', 'id' => 'frmAddEdit');
   echoT(form_open('util/dup_records/opts/'.$enumContext, $attributes));
   
   
   openBlock('Consolidate Duplicate '.$strLabel.' Records', '');
   echoT('<table class="enpView">');
   
      //----------------------
      // Good ID
      //----------------------
   $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 8px;';   
   $clsForm->strExtraFieldText = form_error('txtGoodID');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Correct '.$strLabel.' ID',  'txtGoodID', true,  $formData->txtGoodID, 8, 8));


      //----------------------
      // Bad IDs
      //----------------------
   $clsForm->strExtraFieldText = 
                  '<br><i>Enter one or more comma-seperated duplicate '.$strLabel.' IDs.</i>'
                 .form_error('txtBadIDs');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Duplicate '.$strLabel.' IDs',  'txtBadIDs', true,  $formData->txtBadIDs, 60, 120));
   
   echoT($clsForm->strSubmitEntry('Review '.$strLabel.' Records', 2, 'cmdSubmit', 'text-align: center;'));
   
   
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');  
   
   switch ($enumContext){
         case CENUM_CONTEXT_CLIENT:
            echoT('<i>This utility will transfer client information from the duplicate records<br>
                to the correct record. The transferred information includes:<br>
                <ul>
                   <li>Sponsorships
                   <li>Multi-record personalized client table information
                   <li>Client program enrollment and attendance records
                   <li>Client documents and images
                   <li>EMF measurements
                   <li>Pre/Post Tests
                </ul>');
            break;
         case CENUM_CONTEXT_PEOPLE:
            echoT('<i>This utility will transfer people information from the duplicate records<br>
                to the correct record. The transferred information includes:<br>
                <ul>
                   <li>Multi-record personalized people table information
                   <li>Sponsorships
                   <li>Donations
                   <li>Pledges
                   <li>Auction Bid Winners
                   <li>Relationships
                   <li>Business Contacts
                   <li>People documents and images
                   <li>Volunteer shifts and hours
                </ul>');
            break;
         case CENUM_CONTEXT_BIZ:
            echoT('<i>This utility will transfer business/organization information from the duplicate records<br>
                to the correct record. The transferred information includes:<br>
                <ul>
                   <li>Multi-record personalized business table information
                   <li>Sponsorships
                   <li>Donations
                   <li>Pledges
                   <li>Business Contacts
                   <li>Documents and images
                </ul>');
            break;
         default:
            screamForHelp($enumContext.': invalid context for duplicate record consolidation<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);         
            break;
   }
   
   echoT('<br>You will have an opportunity to review the '.$strLabel.' records before 
             committing to this operation.<br>');

   closeBlock();      
   
   
