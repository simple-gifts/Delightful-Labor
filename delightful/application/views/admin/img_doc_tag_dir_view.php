<?php
   $clsUpDown = new up_down_top_bottom;
   $clsUpDown->lMax = $lNumTags;

   echoT(
         strLinkAdd_ID_DDLEntry($enumContext, 'Add new list entry', true, '').'&nbsp;&nbsp;'
        .strLinkAdd_ID_DDLEntry($enumContext, 'Add new list entry', false, '').'<br><br>');

   if ($lNumTags <= 0){
      echoT('<i>There are currently no tags for <b>'
                .$strContext.'</b></i>.');
   }else {
      $params = array('enumStyle' => 'enpRptC');
      $clsRpt = new generic_rpt($params);

      $strLinkBase =
               '<a href="'.base_url()."index.php/admin/admin_imgdoc_tags/moveEntries/$enumContext/";

      openDDL_EntryTable($tags, $clsRpt, $strContext);
      $idx = 0;
      
      foreach ($tags as $tag){
         writeEntryRow($clsRpt, $clsUpDown, $tag, $enumContext, $idx, $strLinkBase);
         ++$idx;
      }

      echoT($clsRpt->closeReport());
   }

function writeEntryRow($clsRpt, $clsUpDown, $tag, $enumContext, $idx, $strLinkBase){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$tag   <pre>');
echo(htmlspecialchars( print_r($tag, true))); echo('</pre></font><br>');
die;
// ------------------------------------- */

   $lEntryID = $tag->lTagID;
   $clsUpDown->strLinkBase = $strLinkBase.$lEntryID.'/';

   $clsUpDown->upDownLinks($idx);

   echoT(
       $clsRpt->openRow()
      .$clsRpt->writeCell(
                   strLinkEdit_ImgDocTag($enumContext, $lEntryID, 'Edit entry', true).' '
                  .str_pad($lEntryID, 5, '0', STR_PAD_LEFT),
                   30, 'text-align: center;')
      .$clsRpt->writeCell(
                   strLinkRem_ImgDocTag($enumContext, $lEntryID, 'Remove entry', true, true),
                   0, 'text-align: center;')
      .$clsRpt->writeCell(
                   htmlspecialchars($tag->strDDLEntry),
                   225, '')

      .$clsRpt->writeCell(
             $clsUpDown->strUp
            .$clsUpDown->strDown.'&nbsp;&nbsp;&nbsp;&nbsp;'
            .$clsUpDown->strTop
            .$clsUpDown->strBottom, '', '')
      .$clsRpt->closeRow());
}

function openDDL_EntryTable($clsEntries, $clsRpt, $strContext){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT($clsRpt->openReport());

   echoT(
       $clsRpt->openRow()
      .$clsRpt->writeTitle(
                 'Tags for <b><i>'.htmlspecialchars($strContext)
                 .'</i></b></font>',
                 '', ' vertical-align: middle; ', 5, 1, '')

      .$clsRpt->closeRow()

      .$clsRpt->openRow()
      .$clsRpt->writeLabel('Entry ID',   '55pt')
      .$clsRpt->writeLabel('&nbsp;',     '')
      .$clsRpt->writeLabel('Entry',      '175pt')
      .$clsRpt->writeLabel('Order',      '')
      .$clsRpt->closeRow());
}

