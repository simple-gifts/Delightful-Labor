<?php

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '100pt';


   creportRecInfo($clsRpt, $report, $cRptTypes, $lReportID, $lNumFields, $bReadOnly, $bBalanced);

   creportSearchInfo($clsRpt, $report, $lReportID, $strSearchExpression, $bReadOnly);

   creportSortOrder($clsRpt, $report, $lReportID, $lNumSortTerms, $sortTerms, $bReadOnly);

   showCReportENPStats($clsRpt, $report);

   function creportSortOrder($clsRpt, $report, $lReportID, $lNumSortTerms, $sortTerms, $bReadOnly){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDev;

      $attributes = new stdClass;
      $attributes->lTableWidth  = 800;
      $attributes->divID        = 'crptSort';
      $attributes->divImageID   = 'crptSortDivImg';
      $attributes->bStartOpen   = true;

      $strLinks = ''; $strEditSortOrder = '';

      if (!$bReadOnly){
         $strLinks .=
                strLinkAdd_CReportSortTerm($lReportID, 'Add sort term', true).'&nbsp;'
               .strLinkAdd_CReportSortTerm($lReportID, 'Add sort term', false);
         if ($lNumSortTerms > 1){
            $strEditSortOrder = strLinkEdit_CRptSortingTermsOrder($lReportID, 'Order sorting terms', false).'<br>';
         }
      }
      if ($gbDev){
         $strLinks .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
               .strLinkView_CReportDebugGenSQL($lReportID, 'View SQL', true).'&nbsp;'
               .strLinkView_CReportDebugGenSQL($lReportID, 'View SQL', false);
      }
      openBlock('Sorting Order', $strLinks, $attributes);

      if ($lNumSortTerms == 0){
         echoT('<i>There is currently no sorting criteria.</i><br>');
      }else {
         echoT(
           $strEditSortOrder
          .'The report will be sorted with the following criteria:<br>
            <table class="enpRpt">
               <tr>
                  <td class="enpRptLabel">&nbsp;</td>
                  <td class="enpRptLabel">Table</td>
                  <td class="enpRptLabel">Field</td>
                  <td class="enpRptLabel">Field Type</td>
                  <td class="enpRptLabel">Sort Order</td>
               </tr>');

         foreach ($sortTerms as $term){
            if ($term->lTableID <= 0){
               $strTable = $term->strUserTableName;
            }else {
               $strTable = '<b>['.$term->strAttachLabel.']</b> '.htmlspecialchars($term->strUserTableName);
            }

            if ($bReadOnly){
               $strLinkRem = '&nbsp;';
            }else {
               $strLinkRem = strLinkRem_CReportSortTerm($lReportID, $term->lKeyID, 'Remove sort term',  true, true);
            }

            echoT('
               <tr class="makeStripe">
                  <td class="enpRpt">'
                     .$strLinkRem.'
                  </td>
                  <td class="enpRpt">'
                     .$strTable.'
                  </td>
                  <td class="enpRpt">'
                     .htmlspecialchars($term->strFieldNameUser).'
                  </td>
                  <td class="enpRpt">'
                     .$term->strUserFTypeLabel.'
                  </td>
                  <td class="enpRpt">'
                     .($term->bLarkAscending ? 'Ascending <span style="font-size: 8pt;">(A-Z)</span>' :
                                               'Descending <span style="font-size: 8pt;">(Z-A)</span>').'
                  </td>
               </tr>');
         }

         echoT('
            </table>');
      }

      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function creportSearchInfo($clsRpt, $report, $lReportID, $strSearchExpression, $bReadOnly){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDev;

      $attributes = new stdClass;
      $attributes->lTableWidth  = 800;
      $attributes->divID        = 'crptSearch';
      $attributes->divImageID   = 'crptSearchDivImg';
      $attributes->bStartOpen   = true;

      $strLinks = '';
      if (!$bReadOnly){
         $strLinks .=
                strLinkAdd_CReportSearchTerm($lReportID, 'Add search term', true).'&nbsp;'
               .strLinkAdd_CReportSearchTerm($lReportID, 'Add search term', false);
      }
      if ($gbDev){
         $strLinks .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
               .strLinkView_CReportDebugGenSQL($lReportID, 'View SQL', true).'&nbsp;'
               .strLinkView_CReportDebugGenSQL($lReportID, 'View SQL', false);
      }
      openBlock('Search Criteria', $strLinks, $attributes);

      echoT($strSearchExpression);

      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function creportRecInfo(&$clsRpt, &$report, $cRptTypes, $lReportID, $lNumFields, $bReadOnly, $bBalanced){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 750;

      if ($lNumFields > 0 && $bBalanced) {
         $strLinkRun = '<span style="margin-left: 50pt;">&nbsp;</span>'
                      .strLinkView_CReportRun($lReportID, 'Run Report', true).'&nbsp;'
                      .strLinkView_CReportRun($lReportID, 'Run Report', false);
      }else {
         $strLinkRun = '';
      }

      $strDisplaySortLink = '';
      if ($bReadOnly){
         $strEdit = '';
      }else {
         $strEdit = strLinkEdit_CReport($lReportID, 'Edit Report', true);

         if ($lNumFields > 1){
            $strDisplaySortLink = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.strLinkEdit_CRptDisplayTermsOrder(
                        $lReportID, 'Order display fields', false, ' id="dispFieldOrder" ');
         }
      }
      openBlock('Custom Report', $strEdit.$strLinkRun, $attributes);

      echoT(
          $clsRpt->openReport()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Report ID:')
         .$clsRpt->writeCell(str_pad($lReportID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Name:')
         .$clsRpt->writeCell(htmlspecialchars($report->strName))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Report Type:')
         .$clsRpt->writeCell($cRptTypes[$report->enumRptType]->strLabel)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Visibility:')
         .$clsRpt->writeCell(($report->bPrivate ? 'Private' : 'Public'))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('# Fields:')
         .$clsRpt->writeCell($lNumFields.' '
                       .strLinkView_CReportFields($lReportID, 'View fields', true).$strDisplaySortLink)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Notes:')
         .$clsRpt->writeCell(nl2br(htmlspecialchars($report->strNotes)))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Owner:')
         .$clsRpt->writeCell(htmlspecialchars($report->strCFName.' '.$report->strCLName))
         .$clsRpt->closeRow  ());

      echoT(
         $clsRpt->closeReport(''));

      closeBlock();
   }

   function showCReportENPStats(&$clsRpt, &$report){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'creportENP';
      $attributes->divImageID   = 'creportENPDivImg';
      openBlock('Record Information', '', $attributes);
      echoT(
         $clsRpt->showRecordStats(
                               $report->dteOrigin,
                               $report->strCFName.' '.$report->strCLName,
                               $report->dteLastUpdate,
                               $report->strLFName.' '.$report->strLLName,
                               $clsRpt->strWidthLabel));
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

