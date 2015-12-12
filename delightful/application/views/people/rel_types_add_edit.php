<?php

   $attributes = array('name' => 'frmLoc', 'id' => 'frmAddEdit');
   echo form_open('admin/admin_special_lists/people_lists/addEditRel/'.$lKeyID, $attributes);
   $clsGF = new generic_form();
   $clsGF->strLabelClass = 'enpRptLabel';
   $clsGF->bValueEscapeHTML = false;


   echoT('<br><br><table class="enpRptC">');
   echoT($clsGF->strTitleRow( (($bNew ? 'Add New ' : 'Update ').'People Relationship'), 2, ''));
   
   $clsGF->strExtraFieldText = form_error('txtRelName');  
   $clsGF->strID = 'addEditEntry';
   echoT($clsGF->strGenericTextEntry ('Relationship Name',     'txtRelName', true, $formD->txtRelName, 40, 40));
   
   $clsGF->strExtraFieldText = form_error('ddlRC');  
   echoT($clsGF->strGenericDDLEntry  ('Relationship Category', 'ddlRC',      true, $formD->strRelCatDDL));

   echoT($clsGF->strGenericCheckEntry('Spousal relationship?', 'chkSpouse', 'TRUE', false, $formD->bSpouse));

   echoT($clsGF->strSubmitEntry('Save', 2, 'cmdSubmit', ''));
  
   echoT('</table>'.form_close('<br><br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');

