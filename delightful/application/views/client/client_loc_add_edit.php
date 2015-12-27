<?php
   global $gclsChapterVoc;

   $attributes = array('name' => 'frmLoc', 'id' => 'frmAddEdit');
   echoT(form_open('clients/locations/addEdit/'.$id, $attributes));
   
   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   echoT('<br><table class="enpView">'
         .$clsForm->strTitleRow(($bNew ? 'Add new ' : 'Update ').' Client Location', 2, ''));
   
   $clsForm->strStyleExtraLabel = 'width: 120pt; padding-top: 8px;';
   

   $clsForm->strExtraFieldText = form_error('txtLoc');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Location',    'txtLoc',      true,  $formD->txtLoc,     40, 80));
   echoT($clsForm->strGenericTextEntry('Address 1',   'txtAddr1',    false, $formD->txtAddr1,   40, 100));
   echoT($clsForm->strGenericTextEntry('Address 2',   'txtAddr2',    false, $formD->txtAddr2,   40, 100));
   echoT($clsForm->strGenericTextEntry('City',        'txtCity',     false, $formD->txtCity,    40, 50));
   echoT($clsForm->strGenericTextEntry($gclsChapterVoc->vocState, 'txtState',    false, $formD->txtState,   40, 50));
   echoT($clsForm->strGenericTextEntry($gclsChapterVoc->vocZip,   'txtZip',      false, $formD->txtZip,     20, 25));
   echoT($clsForm->strGenericTextEntry('Country',     'txtCountry',  false, $formD->txtCountry, 40, 200));
   
   $clsForm->strStyleExtraLabel = 'padding-top: 3px;';
   $clsForm->strExtraFieldText = '<i>If checked, show Delightful Labor medical features for clients at this location.</i>';
   echoT($clsForm->strGenericCheckEntry('Allow med. features?', 'chkAllowEMR', 'true', false, $formD->chkAllowEMR));
   echoT($clsForm->strNotesEntry('Description', 'txtDescription', false, $formD->txtDescription, 3, 35));

      // allow user to select which sponsorship programs the location participates in
   echoT('
      <tr>
         <td class="enpViewLabel">
            Participates in<br>
            these sponsorship<br>
            programs:
         </td>
         <td class="enpView">');

   foreach ($sponProgs as $clsProg){
      $lSPID = $clsProg->lKeyID;      

      echoT('
         <input type="checkbox" name="chkSponProgs[]" value="'.$lSPID.'" id="chkSP_'.$lSPID.'" '
                .$progInUse[$lSPID].' '
                .'value="TRUE">'.htmlspecialchars($clsProg->strProg).'<br>');               
   }

   echoT('
         </td>
      </tr>');   
   
   echoT($clsForm->strSubmitEntry('Submit', 1, 'cmdSubmit', 'text-align: center; width: 70pt;'));
   echoT('</table>'.form_close('<br><br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');
   
?>
   