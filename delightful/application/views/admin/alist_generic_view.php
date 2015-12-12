<?php
//   echoT(strImageLink('admin/alists_generic/addEdit/'.$strListType.'/0', '', true,
//                      true, IMGLINK_ADDNEW, 'Add New '.$xlateListType).'<br><br>');
   echoT(strLinkAdd_GenericListItem($strListType, 'Add New '.$xlateListType, true).' '
        .strLinkAdd_GenericListItem($strListType, 'Add New '.$xlateListType, false).'<br><br>');

   if ($lNumInList == 0){
      echoT($listEmptyLabel);
   }else {
?>
<table class="enpRptC">
   <tr>
      <td colspan="3" class="enpRptTitle" style="text-align: center;">
         <?php echo($xlateListType); ?>
      </td>
   </tr>
   <tr>
      <td class="enpRptLabel" colspan="2">
         &nbsp;
      </td>
      <td class="enpRptLabel">
         List Item
      </td>
   </tr>
<?php   
      foreach ($listItems as $clsItem){        
         $lListID      = $clsItem->lKeyID;
         $strListItem  = $clsItem->strListItem;

         if ($strListItem == $strBlockEdit){
            $strLinkEdit = '&nbsp;';
            $strLinkRem  = strCantDelete('Default entry can\'t be removed');
         }else {
            $strLinkEdit = strLinkEdit_GenericListItem($lListID, $strListType, 'edit', true);
            $strLinkRem  = strLinkRem_GenericListItem($lListID, $strListType, 'Remove list item', true, true);
         }
         echoT(
               '<tr>
                    <td class="enpRpt" align="center">'
                       .$strLinkEdit.'
                    </td>
                    <td class="enpRpt" align="center">'
                       .$strLinkRem.'
                    </td>
                    <td class="enpRpt" align="left" width="300">'
                       .htmlspecialchars($clsItem->strListItem).'
                    </td>
                </tr>');         
         
      }
      echoT('</table><br>');
   }
?>   

          

          