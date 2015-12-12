<?php

   if ($lNumThisPage == 0){
      echoT('<br><i>No records meet your search criteria.</i>');
      return;
   }

   echoT(strLinkView_CReportExport($lReportID, 'Export this report', true).'&nbsp;'
        .strLinkView_CReportExport($lReportID, 'Export this report', false)
                        .'<br><br>');

   $lTableWidth = lSetColDisplayWidths($report);

      // set up the magic window
   echoT('
          <div id="attendTable" style="width: 100%; overflow: auto; ">
          <table class="enpRpt" style="width: '.$lTableWidth.'pt;">'."\n");

   displayCReportHeader($report);
   displayCReportData($report, $lNumCRecs, $crecs);


      // close the magic window
   echoT('</table></div>'."\n");

   
   function displayCReportData($report, $lNumCRecs, &$crecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($lNumCRecs == 0) return;
      foreach ($crecs as $cr){
         echoT('<tr class="makeStripe">'."\n");
         foreach ($report->fields as $field){
            echoT(fdh\strDisplayValueViaType(
                        $cr[$field->strSelectAsName], $field->enumType, 
                        $field->lTableID, true, $field->displayWidth)."\n");
//echoT('<td class="enpRpt">Hello</td>');            
         }
         
         echoT('</tr>'."\n");
      }
   }
   
   
   
   function displayCReportHeader($report){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('<tr>'."\n");
      foreach ($report->fields as $field){
         echoT('
            <td class="enpRptLabel" style="width: '.$field->displayWidth.'pt;">'
               .htmlspecialchars($field->strSelectAsName).'
            </td>'."\n");
      }
      echoT('</tr>'."\n");
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$report   <pre>');
echo(htmlspecialchars( print_r($report, true))); echo('</pre></font><br>');
// ------------------------------------- */

   }

   function lSetColDisplayWidths(&$report){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lTableWidth = 0;
      foreach ($report->fields as $field){
         switch ($field->enumType){

            case CS_FT_CHECKBOX:    $lWidth =  50; break;
            case CS_FT_DATE:        $lWidth =  70; break;
            case CS_FT_DECIMAL:     $lWidth =  50; break;
            case CS_FT_TEXT255:     $lWidth = 100; break;
            case CS_FT_TEXT80:      $lWidth = 100; break;
            case CS_FT_TEXT20:      $lWidth =  80; break;
            case CS_FT_TEXTLONG:    $lWidth = 220; break;
            case CS_FT_TEXT:        $lWidth = 120; break;
            case CS_FT_INTEGER:     $lWidth =  50; break;
            case CS_FT_ID:          $lWidth =  50; break;
            case CS_FT_CLIENTID:    $lWidth =  50; break;
            case CS_FT_CURRENCY:    $lWidth =  70; break;
            case CS_FT_DDL:         $lWidth =  80; break;
            case CS_FT_DDLMULTI:    $lWidth =  80; break;
            case CS_FT_LOG:         $lWidth = 120; break;
            case CS_FT_ENUM:        $lWidth =  80; break;
            case CS_FT_DDL_SPECIAL: $lWidth =  80; break;

            default:
               screamForHelp($field->enumType.': unknown field type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }
         $field->displayWidth = $lWidth;
         $lTableWidth += $lWidth;
      }
      return($lTableWidth);
   }



