<?php
   $clsGF = new generic_form();
   $clsGF->bValueEscapeHTML = false;
   
   $attributes = array('name' => 'frmLoc', 'id' => 'frmAddEdit');
   echo form_open('admin/admin_special_lists/sponsorship_lists/addEditProg/'.$lSPID, $attributes);

   echoT('<br><br><table class="enpRptC">');
   echoT($clsGF->strTitleRow( (($bNew ? 'Add new ' : 'Update ').'Sponsorship Program'), 2, ''));
   $clsGF->strExtraFieldText = form_error('txtProg');
   $clsGF->strID = 'addEditEntry';
   echoT($clsGF->strGenericTextEntry('Program Name', 'txtProg',    true, $strSponProg, 40, 80));
   
   if (is_numeric($curDefMonthlyCommit)){
      $strCommit = number_format($curDefMonthlyCommit, 2);
   }else {
//      $strCommit = htmlspecialchars($curDefMonthlyCommit);
      $strCommit = $curDefMonthlyCommit;
   }
   $clsGF->strExtraFieldText = form_error('txtCommit');
   echoT($clsGF->strGenericTextEntry('Monthly Commitment', 'txtCommit',  true, $strCommit, 8, 8));
   echoT('
      <tr>
         <td class="enpRptLabel">
            <b>Accounting Country*:</b>
         </td>
         <td class="enpRpt">'
            .$strACORadio.form_error('rdoACO')   //$clsACO->strACO_Radios($clsSC->sponCats[0]->lACO, 'rdoACO').'
       .'</td>
      </tr>');


   echoT($clsGF->strSubmitEntry('Save', 2, 'cmdSubmit', ''));
   echoT('</table></form><br><br>');
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');




?>