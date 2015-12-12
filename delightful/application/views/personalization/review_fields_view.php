<?php
   $clsUpDown = new up_down_top_bottom;
   $clsUpDown->lMax = $lNumFields;

   $clsACO = new madmin_aco;

   echoT($entrySummary.'<br>');

   echoT(
         strLinkAdd_UFField($lTableID,  'Add new field', true, '').'&nbsp;&nbsp;'
        .strLinkAdd_UFField($lTableID,  'Add new field', false, '').'<br><br>');

   $params = array('enumStyle' => 'enpRptC');
   $clsRpt = new generic_rpt($params);

   if ($bClientProg){
      if ($bEnrollment){
         echoT('The enrollment table has the following pre-defined fields:<br>
            <ul style="margin-top: 0px;">
               <li>Client Program</li>
               <li>Client ID</li>
               <li>Start/Stop Dates</li>
               <li>Actively Enrolled Flag</li>
            </ul><br>');
      }else {
         echoT('The attendance table has the following pre-defined fields:<br>
            <ul style="margin-top: 0px;">
               <li>Enrollment ID</li>
               <li>Attendance Date</li>
               <li>Duration</li>
               <li>Activity (you can add/change the list entries)</li>
               <li>Case Notes</li>
            </ul><br>');
      }
   }

   if ($lNumFields<=0){
      echoT('<i>There are no fields currently defined for this table</i>');
   }else {
      openViewFieldsTable($lTableID, $userTable->bMultiEntry, $fields, $strTableLabel, $strTTypeLabel, $clsRpt);
      $idx = 0;
      foreach ($fields as $clsField){
         $lFieldID = $clsField->pff_lKeyID;

            // the activity field in the attendance table (client programs) is required
            // and can not be removed or edited (although the ddl entries can be personalized)
         $bReadOnly = $bClientProg && !$bEnrollment && ($lActivityFieldID==$lFieldID);

         writeFieldRow($bReadOnly, $userTable->bMultiEntry, $clsACO, $clsUpDown,
                       $clsRpt, $clsUF, $clsField, $idx);
         ++$idx;
      }
      closeViewFieldsTable($clsRpt);
   }

function writeFieldRow($bReadOnly, $bMultiRec, &$clsACO, &$clsUpDown, &$clsRpt, &$clsUF, &$clsField, $idx){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gbDev;

   $strFieldNotes = $clsField->strFieldNotes;
   if ($strFieldNotes != ''){
      $strFieldNotes = '<br><i>'.nl2br(htmlspecialchars($strFieldNotes)).'</i>';
   }

   $lFieldID   = $clsField->pff_lKeyID;
   $lTableID   = $clsUF->lTableID;

   $enumFieldType = $clsField->enumFieldType;

   $bHidden = $clsField->pff_bHidden;
   if ($bHidden){
      $strStyle = 'color: #999; font-style:italic;';
      $strHideLabel = ' (hidden)';
   }else {
      $strStyle = '';
      $strHideLabel = '';
   }

   if ($enumFieldType == CS_FT_HEADING){
      $strStyle .= ' border: 2px solid black; ';
   }

   if ($enumFieldType==CS_FT_CURRENCY){
      $clsACO->loadCountries(false, false, true, $clsField->pff_lCurrencyACO);
      $strLabelExtra = ' '.$clsACO->countries[0]->strFlagImg;
   }else {
      $strLabelExtra = '';
   }

   $clsUpDown->strLinkBase =
            '<a href="'.base_url()."index.php/admin/uf_fields/moveFields/$lTableID/$lFieldID/";

   $clsUpDown->upDownLinks($idx);

   $strXfer = '';
   if (!$bMultiRec){
      if ($enumFieldType == CS_FT_DDLMULTI || $enumFieldType == CS_FT_HEADING || $bReadOnly){
         $strXfer = '
              <td class="enpRpt">&nbsp;</td>';
      }else {
         $strXfer = '
              <td class="enpRpt" style="text-align: center;">'
                 .strLinkSpecial_XferUField($lTableID, $lFieldID, 'Transfer field to different table', true).'
              </td>';
      }
   }

   if ($bReadOnly){
      $strLinkEdit = '';
      $strLinkRem  = '&nbsp;';
   }else {
      $strLinkEdit = strLinkEdit_UFField($lTableID, $lFieldID, $enumFieldType, 'edit field', true);
      $strLinkRem  = strLinkRem_UFField($lTableID, $lFieldID, $enumFieldType, 'remove field', true, true);
   }

   echoT(
       '<tr class="makeStripe">
           <td class="enpRpt" nowrap style="'.$strStyle.' text-align: center;">'
              .($idx+1).'
            </td>
           <td class="enpRpt" nowrap style="'.$strStyle.' text-align: center;">'
              .$strLinkEdit.'&nbsp;'
              .str_pad($lFieldID, 5, '0', STR_PAD_LEFT).'
            </td>'
           .$strXfer.'
           <td class="enpRpt" style="text-align: center;'.$strStyle.'">'
              .$strLinkRem.'
           </td>
           <td class="enpRpt" style="'.$strStyle.'">
              <b>'.htmlspecialchars($clsField->pff_strFieldNameUser).$strHideLabel.'</b>'
              .$strFieldNotes.'
           </td>
           <td class="enpRpt" style="'.$strStyle.'">'
             .$clsField->strFieldTypeLabel.$strLabelExtra);

   if (($enumFieldType == CS_FT_DDL) || ($enumFieldType == CS_FT_DDLMULTI)){
      display_DDL_ProcOpts($clsUF, $clsField);
   }

   echoT(
          '</td>');
   if ($gbDev){
      echoT('
           <td class="enpRpt" style="'.$strStyle.' font-family: courier; font-size: 8pt;">
              '.htmlspecialchars($clsField->strFieldNameInternal).'
           </td>');
   }

   if ($bMultiRec){
      echoT('
           <td class="enpRpt" style="text-align: center;'.$strStyle.'">'
              .($clsField->pff_bRequired ? 'Yes' : 'No').'
           </td>');
      echoT('
           <td class="enpRpt" style="text-align: center;'.$strStyle.'">'
              .($clsField->bPrefilled ? 'Yes' : 'No').'
           </td>');
   }

   echoT('
           <td class="enpRpt" style="'.$strStyle.'">'
              .$clsUF->strXlateDefFieldValue($clsField).'
           </td>
           <td class="enpRpt" style="'.$strStyle.' font-size: 7pt;" nowrap>'
            .$clsUpDown->strUp
            .$clsUpDown->strDown
            .'&nbsp;&nbsp;&nbsp;&nbsp;'
            .$clsUpDown->strTop
            .$clsUpDown->strBottom.'
           </td>');

      // reorder text input
   echoT('
           <td class="enpRpt" style="'.$strStyle.' text-align: center;">
              <input type="text"  style="text-align: right; width: 20pt;"
                 name="txtOrder_'.$lFieldID.'"
                 value="'.($idx+1).'">
           </td>');

   echoT('
        </tr>');
}

function openViewFieldsTable($lTableID, $bMultiRec, &$clsUF, $strTableLabel, $strTTypeLabel, &$clsRpt){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gbDev;

   echoT(form_open('admin/uf_fields/reorder/'.$lTableID));

   echoT($clsRpt->openReport());

   echoT(
       $clsRpt->openRow()
      .$clsRpt->writeTitle(
                 'Fields in <b><i>"'
                 .$strTableLabel.'"</b></i> '
                 .'<font style="font-size: 9pt; font-weight: normal;">('
                 .$strTTypeLabel.' table)</font>',
                 '', ' vertical-align: middle; ', 11, 1, '')

      .$clsRpt->closeRow());

   if ($bMultiRec){
      $strXfer = '';
   }else {
      $strXfer = $clsRpt->writeLabel('&nbsp;', '');
   }

   echoT(
       $clsRpt->openRow()
      .$clsRpt->writeLabel('&nbsp;',     '12pt')
      .$clsRpt->writeLabel('Field ID',   '55pt')
      .$clsRpt->writeLabel('&nbsp;',     '')
      .$strXfer
      .$clsRpt->writeLabel('Field Name', '255pt')
      .$clsRpt->writeLabel('Type',       '145pt'));
   if ($gbDev){
      echoT(
         $clsRpt->writeLabel('Internal<br>Name', '15pt'));
   }

   if ($bMultiRec){
      echoT($clsRpt->writeLabel('Required?',    ''));
      echoT($clsRpt->writeLabel('Prefill?',    ''));
   }
   echoT(
       $clsRpt->writeLabel('Default',    '145pt')
      .$clsRpt->writeLabel('Order',      '90pt;'));

      // bringing order from chaos
   $strOrderButt =
         '<input type="submit" name="cmdSubmit" value="Re-order"
             style="text-align: center; font-size: 8pt;"
             onclick="this.disabled=1; this.form.submit();"
             class="btn"
             onmouseover="this.className=\'btn btnhov\'"
             onmouseout="this.className=\'btn\'">';
   echoT(
       $clsRpt->writeLabel($strOrderButt, '20pt', 'text-align: center;'));

   echoT(
       $clsRpt->closeRow());
}

function closeViewFieldsTable($clsRpt){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT($clsRpt->closeReport());
   echoT(form_close('<br>'));
}

function display_DDL_ProcOpts($clsUF, $clsField){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------

   $clsUF->uf_ddl_info($clsField->pff_lKeyID);

   if ($clsUF->clsDDL_Info->pff_bConfigured) {
         echoT(
                '<br>'.$clsUF->clsDDL_Info->lNumEntries.' items '
          .strLinkConf_UFDDL($clsUF->lTableID, $clsField->pff_lKeyID,
                             'view/add/edit', false, ''));
   }else {
      echoT(
          '<br><br><font color="red"><b>NOT CONFIGURED!</b></font><br>'
          .strLinkConf_UFDDL($clsUF->lTableID, $clsField->pff_lKeyID,
                             'Click here to configure', false, ''));
   }
}


?>