<?php
$idx = 0;
foreach ($uf as $clsUF){

   $strAddLabel = 'Add a new '.$clsUF->strTTypeLabel.' personalized table.';

   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'perBlock'.$idx;
   $attributes->divImageID   = 'perBlockDivImg'.$idx;
   $attributes->bStartOpen   = $clsUF->enumTType == $enumOpenBlock;
   openBlock('Personalized '.$clsUF->strTTypeLabel.' Tables', '', $attributes);
   
   echoT(strLinkAdd_UFTable($clsUF->enumTType, $strAddLabel, true, '').' '
        .strLinkAdd_UFTable($clsUF->enumTType, $strAddLabel, false, '').'<br>');

   if ($clsUF->lNumTables==0) {
         echoT(
             '<br>
              There are currently no personalized tables for <b>'.$clsUF->strTTypeLabel.'</b>.<br><br>');
   }else {
      openUF_TableInfo();
      foreach ($clsUF->userTables as $clsUTable){
         writeUF_TableRow($clsUF, $clsUTable);
      }
      closeUF_TableInfo();
   }
   
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
   
   ++$idx;
}

function writeUF_TableRow(&$clsUF, &$clsUTable){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gbDev;
   
   if ($gbDev){
      $strLinkDebug = '&nbsp;&nbsp;'.strLinkDebug_Fields($clsUTable->lKeyID, 'Internal field details', true);
   }else {
      $strLinkDebug = '';
   }
   
   $bHidden = $clsUTable->bHidden;
   if ($bHidden){
      $strHideStyle = 'color: #999; font-style: italic; ';
      $strHideLabel = ' (hidden)';
      $strFieldLink = '';
   }else {
      $strHideStyle = 
      $strHideLabel = '';
      $strFieldLink = strLinkView_UFFields($clsUTable->lKeyID, 'View fields', true, '').' ';
   }
   
   $lTableID = $clsUTable->lKeyID;
   
   if ($clsUTable->bMultiEntry){
      $strMLabel = 'Multiple-entry';
      if ($clsUTable->bReadOnly) $strMLabel .= ' <i>(read only)</i>';
   }else {
     $strMLabel = 'Single-entry';
   }

   echoT(
       '<tr>
           <td class="enpRpt" style="'.$strHideStyle.'" nowrap>'
              .str_pad($lTableID, 5, '0', STR_PAD_LEFT).'&nbsp;'
              .strLinkView_UFTable($lTableID, 'View table record', true, '').'&nbsp;&nbsp;'
              .strLinkClone_PTable($lTableID, 'Clone', true).'
           </td>
           <td class="enpRpt" style="'.$strHideStyle.'">'
              .strLinkEdit_UFTable($lTableID, $clsUF->enumTType, 'Edit table', true, '').'
           </td>
           <td class="enpRpt" width="30%" style="'.$strHideStyle.'">
              <b>'.nl2br(htmlspecialchars($clsUTable->strUserTableName)).$strHideLabel.'</b>
           </td>
           <td class="enpRpt" style="text-align: left; '.$strHideStyle.'" width="25%">'
              .$strMLabel.'
           </td>
           <td class="enpRpt" style="text-align: right; '.$strHideStyle.'" width="10%">'
              .$strFieldLink
              .$clsUTable->lNumFields.$strLinkDebug.'
           </td>
           <td class="enpRpt" width="40%" style="'.$strHideStyle.'">'
              .nl2br(htmlspecialchars($clsUTable->strDescription)).'
           </td>
        </tr>');
}

function openUF_TableInfo(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT(
       '<br>
        <table class="enpRpt" style="width: 630pt;">
           <tr>
              <td align="center" class="enpRptLabel">
                 tableID
              </td>
              <td align="center" class="enpRptLabel">
                 &nbsp;
              </td>
              <td align="center" class="enpRptLabel">
                 Table Name
              </td>
              <td align="center" class="enpRptLabel">
                 Type
              </td>
              <td align="center" class="enpRptLabel">
                 # Fields
              </td>
              <td align="center" class="enpRptLabel">
                 Description
              </td>
           </tr>');
}

function closeUF_TableInfo(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echo(
       '</table><br>'."\n");
}

?>