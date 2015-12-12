<?php
   $attributes = array('name' => 'frmNewItem', 'id' => 'newItem');
   echoT(form_open('auctions/packages/setWinner1/'.$lPackageID, $attributes));

   $clsForm = new generic_form;
   $clsForm->strLabelClass      = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strEntryClass      = 'enpView';
   $clsForm->bValueEscapeHTML   = false;
   $clsForm->strStyleExtraLabel = 'width: 100pt;';

   openBlock('Auction Package Winner: <b>'.$package->strPackageSafeName.'</b>', ''); echoT('<table>');
   
      //-------------------------------
      // Auction Package Winner Name
      //-------------------------------
   $clsForm->strStyleExtraLabel = 'vertical-align: top; padding-top: 6pt; ';
   $clsForm->strExtraFieldText = form_error('ddlNames');
   $clsForm->strID = 'winnerName';
   
   $clsForm->strTxtControlExtra = ' onkeyup="showResult(this.value)" autocomplete="off" ';
   $clsForm->strExtraFieldText .= '
                <br><i>(last name [must be in <b>people</b> table] or business name [must be in <b>business/org</b> table])</i><br>
                <span id="notFound" style="visibility: hidden;">(no matches)</span> 
                <br><select name="ddlNames" id="selNames" 
                        size="4" 
                        style="visibility: hidden;"
                        onchange="populateSearch(); this.form.submit();"></select>
                        
                        ';
   
echoT('<!--  ------------------------------------------------------------- -->');
   echoT($clsForm->strGenericTextEntry('Winner Name', 'txtDonor', true, '', 40, 120));
echoT('<!--  ------------------------------------------------------------- -->');
   
   echoT(form_close());
   echoT('<script type="text/javascript">frmNewItem.winnerName.focus();</script>');
   
   echoT('</table>'); closeBlock(); 
   
