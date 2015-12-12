<?php
   $clsDateTime = new dl_date_time;
   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '170pt';

   showClientLocInfo        ($clsRpt, $lLocID, $cLoc, $clsDateTime, $sponProgs, $lNumSponPrograms);

   showCustomLocationTableInfo($strPT, $lNumPTablesAvail);
   showImageInfo            (CENUM_CONTEXT_LOCATION, $lLocID, 'Client Location Images',
                             $images, $lNumImages, $lNumImagesTot);
   showDocumentInfo         (CENUM_CONTEXT_LOCATION, $lLocID, 'Client Location Documents',
                             $docs, $lNumDocs, $lNumDocsTot);
   showLocationENPStats     ($clsRpt, $cLoc);


function showClientLocInfo(&$clsRpt, $lLocID, &$cLoc, &$clsDateTime, &$sponProgs, $lNumSponPrograms){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   openBlock('Location Information', 
              strLinkEdit_ClientLocation($lLocID, 'Edit Location', true).'&nbsp;'
             .strLinkEdit_ClientLocation($lLocID, 'Edit Location', false)
              );

   echoT(
       $clsRpt->openReport()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Location ID:')
      .$clsRpt->writeCell (str_pad($lLocID, 5, '0', STR_PAD_LEFT))
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Location:')
      .$clsRpt->writeCell (htmlspecialchars($cLoc->strLocation))
      .$clsRpt->closeRow  ()
      
      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Address:')
      .$clsRpt->writeCell ($cLoc->strAddress)
      .$clsRpt->closeRow  ());
      
   echoT(      
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Show medical rec. features?:')
      .$clsRpt->writeCell (($cLoc->bEnableEMR ? 'Yes' : 'No'), '500pt;')
      .$clsRpt->closeRow  ());
      
   echoT(      
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Description:')
      .$clsRpt->writeCell (nl2br(htmlspecialchars($cLoc->strDescription)), '500pt;')
      .$clsRpt->closeRow  ());
      
   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Sponsorship Programs:'));
   $strOut = '';
   if ($lNumSponPrograms==0){
      $strOut = '<i>Not participating in any sponsorship programs</i>';
   }else {
      $strOut = 'Participating in: </ul>';
      foreach ($sponProgs as $prog){
         $strOut .= '<li>'.htmlspecialchars($prog->strProg).'</li>'."\n";
      }
      $strOut .= '</ul>';
   }
   echoT(
       $clsRpt->writeCell ($strOut)
      .$clsRpt->closeRow  ());
      
   echoT(
       $clsRpt->closeReport());

   closeBlock();
}

function showLocationENPStats($clsRpt, $cLoc){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'locENP';
   $attributes->divImageID   = 'locENPDivImg';
   openBlock('Record Information', '', $attributes);
   echoT(
      $clsRpt->showRecordStats($cLoc->dteOrigin,
                            $cLoc->strUCFName.' '.$cLoc->strUCLName,
                            $cLoc->dteLastUpdate,
                            $cLoc->strULFName.' '.$cLoc->strULLName,
                            $clsRpt->strWidthLabel));
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showCustomLocationTableInfo($strPT, $lNumPTablesAvail){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 1000;
   $attributes->divID        = 'clientLTable';
   $attributes->divImageID   = 'clientLTableDivImg';
   $attributes->bStartOpen   = false;
   openBlock('Personalized Tables <span style="font-size: 9pt;">('.$lNumPTablesAvail.')</span>', '', $attributes);

   echoT($strPT);

   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}


/*
function showCustomLocationTableInfo($lLocID, &$cLoc, &$clsUFD){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
// -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$clsUFD   <pre>');
echo(htmlspecialchars( print_r($clsUFD, true))); echo('</pre></font><br>');
// -------------------------------------

   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'clientPENP';
   $attributes->divImageID   = 'clientPDivImg';
   openBlock('Personalized Tables <span style="font-size: 9pt;">('.$clsUFD->lNumTables.')</span>', '', $attributes);

   $clsUFD->setTType(CENUM_CONTEXT_LOCATION);
   $clsUFD->loadTablesViaTType();
   $clsUFD->lForeignID = $lLocID;
   $clsUFD->lFieldSetWidth = 550;
   $clsUFD->lFieldValWidth = 550;
   $clsUFD->strViewAllLogLinkBase = 'admin/uf_log/viewAll/';

   if ($clsUFD->lNumTables > 0){
      foreach ($clsUFD->userTables as $clsUserTable){
         $clsUFD->lDispTableID = $clsUserTable->lKeyID;
         echoT($clsUFD->strDiplayUserTable($clsUserTable));
      }
   }else {
      echoT('<i>No personalized tables defined</i><br>');
   }
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}
*/

