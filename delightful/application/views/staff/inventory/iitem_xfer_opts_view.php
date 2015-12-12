<?php

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   $strItemID = str_pad($lICatID, 5, '0', STR_PAD_LEFT);

   $attributes = array('name' => 'frmIItem', 'id' => 'frmAddEdit');
   echoT(form_open('staff/inventory/inventory_items/xferItemCat/'.$lIItemID, $attributes));

   openBlock('Transfer Inventory Item', '');
   echoT('<table class="enpView">');

   echoT($clsForm->strLabelRow('itemID',  $strItemID, 1));
   
   echoT($clsForm->strLabelRow('Current Category',  htmlspecialchars($item->strCatBreadCrumb), 1));
   
      //----------------------
      // Transfer Category
      //----------------------
   $strDDL =
       '<select name="ddlXfer">
          <option value="-1">(root)</option>'."\n".$ddlXfer.'</select>';
   echoT($clsForm->strLabelRow('Transfer to Category', $strDDL, 1));
   
   
   echoT($clsForm->strSubmitEntry('Save', 1, 'cmdSubmit', 'text-align: center; width: 80pt;'));
   echoT('</table>'.form_close('<br>'));

   closeBlock();

   
