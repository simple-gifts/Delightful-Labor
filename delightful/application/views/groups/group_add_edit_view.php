<?php

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;


   $attributes = array('name' => 'frmAddEdit', 'id' => 'frmGroupAddEdit');
   echoT(form_open('groups/add_edit_group/addEdit/'.$enumGroupType.'/'.$lGID, $attributes));
//   $clsGF = new generic_form();

   openBlock(($bNew ? 'Add New' : 'Edit').' '.$strGroupType.' Group Name', '');
   echoT('<table class="enpView">');
   
      //------------------------
      // Group 
      //------------------------
   $clsForm->strStyleExtraLabel = 'width: 80pt; padding-top: 8px;';
   $clsForm->strExtraFieldText = form_error('txtGroupName');
   $clsForm->strID = 'addEditEntry';
   echoT($clsForm->strGenericTextEntry('Group Name', 'txtGroupName', true,  $strGroupName, 40, 80));
   
      // does the group have extended fields?
   if ($gProps->extended){
      if ($gProps->lNumBool > 0){
         $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
         foreach ($gProps->bools as $bField){
            $clsForm->strExtraFieldText = $bField->strLabelExtra;
            echoT($clsForm->strGenericCheckEntry(
                           $bField->strLabel, $bField->strFormFN, 'true', false, 
                           $bField->bValue));
         }
      }
      
      if ($gProps->lNumInt > 0){
echo(__FILE__.' '.__LINE__.'<br>'."\n"); die;      
      }
   }
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$gProps   <pre>');
echo(htmlspecialchars( print_r($gProps, true))); echo('</pre></font><br>');
// ------------------------------------- */
      
   
   
   
/*   
   echoT('
      <br>
      <table class="enpRptC">
           <tr>
              <td class="enpRptTitle" colspan="2" style="text-align: center;">'
                 .($bNew ? 'Add new ' : 'Update ').' '.$strGroupType.' Group Name
              </td>
           </tr>');

   $clsGF->strExtraFieldText = form_error('txtGroupName');
   $clsGF->strID = 'groupEntry';
   echoT($clsGF->strGenericTextEntry('Group Name',    'txtGroupName', 
                           true,  $strGroupName,     40, 80));

      // thanks to http://www.seobywebmechanix.com/auto-focus-text-field.html
      // for the focus technique
   echoT($clsGF->strSubmitEntry('Submit', 2, 'cmdSubmit', ''));
   echoT('</table></form>');
   echoT(
      '<script type="text/javascript">frmGroupAddEdit.groupEntry.focus();</script>');
      
*/      
      
   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'text-align: center; width: 80pt;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');  

   closeBlock();      
      
