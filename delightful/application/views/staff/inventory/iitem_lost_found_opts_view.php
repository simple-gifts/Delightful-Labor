<?php

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   $strItemID = str_pad($lICatID, 5, '0', STR_PAD_LEFT);

   $attributes = array('name' => 'frmIItem', 'id' => 'frmAddEdit');
   echoT(form_open('staff/inventory/inventory_items/lostFound/'.$strLostLabel.'/'.$lIItemID, $attributes));

   openBlock('Mark Inventory Item as '.$strLostLabel, '');
   echoT('<table class="enpView">');

   echoT($clsForm->strLabelRow('itemID',  $strItemID, 1));
   
   echoT($clsForm->strLabelRow('Item',      htmlspecialchars($item->strItemName), 1));
   echoT($clsForm->strLabelRow('Category',  htmlspecialchars($item->strCatBreadCrumb), 1));
   echoT($clsForm->strLabelRow('Serial / Item Tag (a)',  htmlspecialchars($item->strItemSNa), 1));
   echoT($clsForm->strLabelRow('Serial / Item Tag (b)',  htmlspecialchars($item->strItemSNb), 1));
   
      //----------------------
      // Lost notes
      //----------------------
   $clsForm->strStyleExtraLabel = 'padding-top: 4px;';
   echoT($clsForm->strNotesEntry($strLostLabel.' Item Notes', 'txtNotes', false, $item->strLostNotes, 3, 50));
   
   
   echoT($clsForm->strSubmitEntry('Mark as '.$strLostLabel, 1, 'cmdSubmit', 'text-align: center; width: 80pt;'));
   echoT('</table>'.form_close('<br>'));

   closeBlock();

   
