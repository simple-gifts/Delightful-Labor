<?php
   echoT($entrySummary.'<br>');
   $clsUpDown = new up_down_top_bottom;
   $clsUpDown->lMax = $lNumDDLEntries;

   echoT(
         strLinkAdd_UFDDLEntry($lTableID, $lFieldID, 'Add new list entry', true, '').'&nbsp;&nbsp;'
        .strLinkAdd_UFDDLEntry($lTableID, $lFieldID, 'Add new list entry', false, '').'<br><br>');

   if ($lNumDDLEntries <= 0){
      echoT('<i>There are currently no entries in drop-down list <b>'
                .htmlspecialchars($strTableLabel.': '.$strUserFieldName).'</b></i>.<br>'
                .strLinkSpecial_CloneDDLList($lTableID, $lFieldID, 'Clone existing Drop-Down List', true).'&nbsp;'
                .strLinkSpecial_CloneDDLList($lTableID, $lFieldID, 'Clone existing Drop-Down List', false).'<br>');
   }else {
      $params = array('enumStyle' => 'enpRptC');
      $clsRpt = new generic_rpt($params);

      $strLinkBase =
               '<a href="'.base_url()."index.php/admin/uf_ddl/moveEntries/$lTableID/$lFieldID/";
      $strLinkSort = anchor("admin/uf_ddl/sortEntries/$lTableID/$lFieldID", '(sort)');
      openDDL_EntryTable($clsEntries, $clsRpt, $strTableLabel, $strUserFieldName, $strLinkSort);
      $idx = 0;
      foreach ($clsEntries as $clsEntry){
         writeEntryRow($clsRpt, $clsUpDown, $clsEntry, $lTableID, $lFieldID, $idx, $strLinkBase);
         ++$idx;
      }

      echoT($clsRpt->closeReport());
   }

function writeEntryRow($clsRpt, $clsUpDown, $clsEntry, $lTableID, $lFieldID, $idx, $strLinkBase){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $lEntryID = $clsEntry->lKeyID;
   $clsUpDown->strLinkBase = $strLinkBase.$lEntryID.'/';

   $clsUpDown->upDownLinks($idx);

   echoT(
       $clsRpt->openRow()
      .$clsRpt->writeCell(
                   strLinkEdit_UFDDLEntry($lTableID, $lFieldID, $lEntryID, 'Edit entry', true).' '
                  .str_pad($lEntryID, 5, '0', STR_PAD_LEFT),
                   30, 'text-align: center;')
      .$clsRpt->writeCell(
                   strLinkRem_UFDDLEntry($lTableID, $lFieldID, $lEntryID, 'Remove entry', true, true),
                   0, 'text-align: center;')
      .$clsRpt->writeCell(
                   htmlspecialchars($clsEntry->strEntry),
                   225, '')

      .$clsRpt->writeCell(
             $clsUpDown->strUp
            .$clsUpDown->strDown.'&nbsp;&nbsp;&nbsp;&nbsp;'
            .$clsUpDown->strTop
            .$clsUpDown->strBottom, '', '')
      .$clsRpt->closeRow());
}

function openDDL_EntryTable($clsEntries, $clsRpt, $strUserTableName, $strUserFieldName, $strLinkSort){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT($clsRpt->openReport());

   echoT(
       $clsRpt->openRow()
      .$clsRpt->writeTitle(
                 'Entries in Drop-down List:<br><b><i>'.htmlspecialchars($strUserTableName.': '.$strUserFieldName)
                 .'</i></b></font>',
                 '', ' vertical-align: middle; ', 5, 1, '')

      .$clsRpt->closeRow()

      .$clsRpt->openRow()
      .$clsRpt->writeLabel('Entry ID',            '55pt')
      .$clsRpt->writeLabel('&nbsp;',              '')
      .$clsRpt->writeLabel('Entry',               '175pt')
      .$clsRpt->writeLabel('Order <span style="font-weight: normal;">'.$strLinkSort.'</span>', '')
      .$clsRpt->closeRow());
}

