<?php

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   $attributes =
       array(
            'name'     => 'frmPSel',
            'id'       => 'parentSel'
            );
   echoT(form_open('admin/import/parentSel',  $attributes));


   openBlock('Personalized Table Import', '');
   echoT('<table class="enpView">');

   $clsForm->strExtraFieldText  = form_error('ddlParent');
   $clsForm->strStyleExtraLabel = 'padding-top: 6px;';
   $clsForm->strExtraSelect     = ' onChange="showPTables(this.value, \'ddlParent\')" ';
   
   echoT($clsForm->strGenericDDLEntry('Parent Table', 'ddlParent', true,
                 $formData->strDDLParent));
   
/*   
   $strDDL =
      '<select name="ddlParent">
         <option value="-1">&nbsp;</option>
         <option value="'.CENUM_CONTEXT_PEOPLE.'"     >People</option>
         <option value="'.CENUM_CONTEXT_VOLUNTEER.'"  >Volunteer</option>
         <option value="'.CENUM_CONTEXT_BIZ.'"        >Business/Organization</option>
         <option value="'.CENUM_CONTEXT_CLIENT.'"     >Client</option>
         <option value="'.CENUM_CONTEXT_SPONSORSHIP.'">Sponsors</option>
         <option value="'.CENUM_CONTEXT_STAFF.'"      >Users/Staff Members</option>         
       </select>';
   echoT($clsForm->strLabelRow('Parent Table', $strDDL, 1));
*/                       
                       
   echoT($clsForm->strSubmitEntry('Select ', 2, 'cmdSubmit', 'text-align: left;'));


   echoT('</table>'.form_close('<br>'));

   echoT('</table>'); closeBlock();
                       
