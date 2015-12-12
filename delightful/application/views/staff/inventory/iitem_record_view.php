<?php
//   $clsDateTime = new dl_date_time;
   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '130pt';


   showIItemInfo($clsRpt, $lIItemID, $lICatID, $strBreadCrumb, $item);

   showIItemActions($clsRpt, $lIItemID, $item);

   showIItemCICOInfo($clsRpt, $lIItemID, $item);

   showIItemHistory($clsRpt, $lIItemID, $item);

   showImageInfo            (CENUM_CONTEXT_INVITEM, $lIItemID, 'Inventory Item Images',
                             $images, $lNumImages, $lNumImagesTot);
   showDocumentInfo         (CENUM_CONTEXT_INVITEM, $lIItemID, 'Inventory Item Documents',
                             $docs, $lNumDocs, $lNumDocsTot);


   showENPIItemStats($clsRpt, $item);

   function showIItemActions($clsRpt, $lIItemID, $item){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $attributes = new stdClass;
      $attributes->lTableWidth  = 1400;
      $attributes->divID        = 'actDiv';
      $attributes->divImageID   = 'actDivImg';
      openBlock('Actions'."\n", '', $attributes);

      echoT(strLinkXfer_IItemCat($lIItemID, 'Transfer to different category', true).'&nbsp;'
           .strLinkXfer_IItemCat($lIItemID, 'Transfer to different category', false).'<br><br>');

      $properties = $item->statProps;
      if ($properties->bLinkCO){
         echoT(strLinkCO_IItem($lIItemID, 'Check-out / loan item', true).'&nbsp;'
              .strLinkCO_IItem($lIItemID, 'Check-out / loan item', false).'<br><br>');
      }
      if ($properties->bLinkCI){
         echoT(strLinkCI_IItem($lIItemID, 'Check-in / return item', true).'&nbsp;'
              .strLinkCI_IItem($lIItemID, 'Check-in / return item', false).'<br><br>');
      }
      if ($properties->bLinkLost){
         echoT(strLink_IItemLost($lIItemID, 'Item lost', true).'&nbsp;'
              .strLink_IItemLost($lIItemID, 'Item lost', false).'<br><br>');
      }
      if ($properties->bLinkFound){
         echoT(strLink_IItemFound($lIItemID, 'Item found', true).'&nbsp;'
              .strLink_IItemFound($lIItemID, 'Item found', false).'<br><br>');
      }
      if ($properties->bLinkRem){
         echoT(strLink_IItemRemoveFromInventory($lIItemID, 'Remove item from inventory', true).'&nbsp;'
              .strLink_IItemRemoveFromInventory($lIItemID, 'Remove item from inventory', false).'<br><br>');
      }
      if ($properties->bLinkUnRem){
         echoT(strLink_IItemUnRemoveFromInventory($lIItemID, 'Return item to inventory', true).'&nbsp;'
              .strLink_IItemUnRemoveFromInventory($lIItemID, 'Return item to inventory', false).'<br><br>');
      }
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function showIItemInfo($clsRpt, $lIItemID, $lICatID, $strBreadCrumb, $item){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      openBlock('Inventory Item'."\n",
                  strLinkEdit_InventoryItem($lICatID, $lIItemID, 'Edit inventory item', true)."\n"
                 .'&nbsp;&nbsp;&nbsp;'."\n"
                 .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'."\n"
                 .strLinkRem_IItem($lIItemID, 'Delete inventory record', true, true)."\n\n");

      echoT(
          $clsRpt->openReport());
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Inventory Item ID:')
         .$clsRpt->writeCell (str_pad($lIItemID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Item Name:')
         .$clsRpt->writeCell (htmlspecialchars($item->strItemName))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Can be checked-out?:')
         .$clsRpt->writeCell (($item->bAvailForLoan ? 'Yes' : 'No'))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Status:')
         .$clsRpt->writeCell ($item->strStatus)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Category:')
         .$clsRpt->writeCell (htmlspecialchars($strBreadCrumb))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Serial / Item Tag (a):')
         .$clsRpt->writeCell (htmlspecialchars($item->strItemSNa))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Serial / Item Tag (b):')
         .$clsRpt->writeCell (htmlspecialchars($item->strItemSNb))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Responsible Party:')
         .$clsRpt->writeCell (htmlspecialchars($item->strRParty))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Date Obtained:')
         .$clsRpt->writeCell (date($genumDateFormat, $item->dteObtained))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Estimated Value:')
         .$clsRpt->writeCell ($item->strFormattedAmnt)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Location:')
         .$clsRpt->writeCell (nl2br(htmlspecialchars($item->strLocation)))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Description:')
         .$clsRpt->writeCell (nl2br(htmlspecialchars($item->strDescription)))
         .$clsRpt->closeRow  ());
         
      if ($item->bLost){
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Date reported lost:')
            .$clsRpt->writeCell (date($genumDateFormat, $item->dteReportedLost)
                         .' by '.htmlspecialchars($item->strLostFName.' '.$item->strLostLName))
            .$clsRpt->closeRow  ());
      }
      
      if ($item->strLostNotes != ''){
         echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Lost/Found Notes:')
            .$clsRpt->writeCell (nl2br(htmlspecialchars($item->strLostNotes)))
            .$clsRpt->closeRow  ());
      }


      echoT(
         $clsRpt->closeRow  ());

      echoT(
         $clsRpt->closeReport(''));
      closeBlock();
   }

   function showIItemCICOInfo($clsRpt, $lIItemID, $item){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 1400;
      $attributes->divID        = 'cicoDiv';
      $attributes->divImageID   = 'cicoDivImg';
      openBlock('Check-out / Check-in History', '', $attributes);

      $properties = $item->statProps;
      if ($item->lNumCICO == 0){
         echoT('<i>This item has not been checked-out / loaned.</i><br><br>');
      }else {
         writeCICOTable($item->CICOrecs, $properties);
      }

      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function writeCICOTable($CICO, $properties){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('
        <table class="enpRpt">
           <tr>
              <td class="enpRptTitle" colspan="6">
                 Check-Out / Check-In History
              </td>
           </tr>
         ');
      echoT('
         <tr>
            <td class="enpRptLabel">
               cicoID
            </td>
            <td class="enpRptLabel">
               Checked Out To
            </td>
            <td class="enpRptLabel">
               Checked In
            </td>
         </tr>');

      foreach ($CICO as $cicoRec){
         echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align: center;">'
                  .str_pad($cicoRec->lKeyID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td class="enpRpt">'
                  .strCheckOutTable($cicoRec).'
               </td>
               <td class="enpRpt">'
                  .strCheckInTable($cicoRec, $properties).'
               </td>
            </tr>');
      }

      echoT('</table>');
   }

   function strCheckInTable($cicoRec, $properties){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      if (is_null($cicoRec->dteCheckedIn)){
         $strOut =
         '<table cellpadding="0" style="width: 240pt;">
             <tr>
                <td>
                   <i>Not checked-in.</i>';

         if ($properties->bLinkCI){
            $strOut .= '<br>'
                 .strLinkCI_IItem($cicoRec->lItemID, 'Check-in / return item', true).'&nbsp;'
                 .strLinkCI_IItem($cicoRec->lItemID, 'Check-in / return item', false);
         }

         $strOut .= '
                </td>
             </tr>
          </table>';
      }else {
         $strOut =
            '<table cellpadding="0" style="width: 240pt;">
               <tr>
                  <td style="width: 70pt;">
                     Returned On:
                  </td>
                  <td>'
                     .date($genumDateFormat, $cicoRec->dteCheckedIn).'
                  </td>
               </tr>';

         $strOut .=
              '<tr>
                  <td>
                     By:
                  </td>
                  <td>'
                     .htmlspecialchars($cicoRec->strCheckInFName.' '.$cicoRec->strCheckInLName).'
                  </td>
               </tr>';

         $strOut .=
              '<tr>
                  <td>
                     Notes:
                  </td>
                  <td>'
                     .nl2br(htmlspecialchars($cicoRec->strCI_Notes)).'
                  </td>
               </tr>';

         $strOut .= '</table>';
      }
      return($strOut);
   }

   function strCheckOutTable($cicoRec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $strOut =
         '<table cellpadding="0" style="width: 240pt;">
            <tr>
               <td style="width: 50pt;">
                  Loaned to:
               </td>
               <td>'
                  .htmlspecialchars($cicoRec->strCheckedOutTo).'
               </td>
            </tr>';
      $strOut .=
           '<tr>
               <td>
                  On:
               </td>
               <td>'
                  .date($genumDateFormat, $cicoRec->dteCheckedOut).'
               </td>
            </tr>';

      $strOut .=
           '<tr>
               <td>
                  By:
               </td>
               <td>'
                  .htmlspecialchars($cicoRec->strCheckOutFName.' '.$cicoRec->strCheckOutLName).'
               </td>
            </tr>';

      $strOut .=
           '<tr>
               <td>
                  Deposit:
               </td>
               <td>'
                  .htmlspecialchars($cicoRec->strSecurity).'
               </td>
            </tr>';

      $strOut .=
           '<tr>
               <td>
                  Notes:
               </td>
               <td>'
                  .nl2br(htmlspecialchars($cicoRec->strCO_Notes)).'
               </td>
            </tr>';

      $strOut .= '</table>';
      return($strOut);
   }

   function showIItemHistory($clsRpt, $lIItemID, $item){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $attributes = new stdClass;
      $attributes->lTableWidth  = 1400;
      $attributes->divID        = 'histDiv';
      $attributes->divImageID   = 'histDivImg';
      openBlock('Item History', '', $attributes);

      if ($item->lNumHRecs == 0){
         echoT('<i>No history records associated with this item.</i>');
      }else {
         echoT('
            <table class="enpRpt">
               <tr>
                  <td class="enpRptLabel">
                     histID
                  </td>
                  <td class="enpRptLabel">
                     cicoID
                  </td>
                  <td class="enpRptLabel">
                     Action
                  </td>
                  <td class="enpRptLabel">
                     Date
                  </td>
                  <td class="enpRptLabel">
                     By
                  </td>
               </tr>');

         foreach ($item->histRecs as $histRec){
            if (is_null($histRec->lCICOID)){
               $strCICOID = 'n/a';
            }else {
               $strCICOID = str_pad($histRec->lCICOID, 5, '0', STR_PAD_LEFT);
            }
            echoT('
               <tr class="makeStripe">
                  <td class="enpRpt" style="text-align: center;">'
                     .str_pad($histRec->lKeyID, 5, '0', STR_PAD_LEFT).'
                  </td>
                  <td class="enpRpt" style="text-align: center;">'
                     .$strCICOID.'
                  </td>
                  <td class="enpRpt">'
                     .$histRec->enumOperation.'
                  </td>
                  <td class="enpRpt">'
                     .date($genumDateFormat.' H:i:s', $histRec->dteAction).'
                  </td>
                  <td class="enpRpt">'
                     .htmlspecialchars($histRec->strCFName.' '.$histRec->strCLName).'
                  </td>
               </tr>');
         }
         echoT('</table>');
      }

      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function showENPIItemStats(&$clsRpt, &$item){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'irecDiv';
      $attributes->divImageID   = 'irecDivImg';
      openBlock('Record Information', '', $attributes);
      echoT(
         $clsRpt->showRecordStats($item->dteOrigin,
                               $item->ucstrFName.' '.$item->ucstrLName,
                               $item->dteLastUpdate,
                               $item->ulstrFName.' '.$item->ulstrLName,
                               $clsRpt->strWidthLabel));
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }


