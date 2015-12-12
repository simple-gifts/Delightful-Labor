<?php

/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$   <pre>');
echo(htmlspecialchars( print_r($targetDDLInfo, true))); echo('</pre></font><br>');
// ------------------------------------- */

   echoT($entrySummary);
   
   $params = array('enumStyle' => 'terse', 'crpt');
   $crpt = new generic_rpt($params);
   
   $attributes = new stdClass;
   $attributes->lTableWidth = 900;

   openBlock('Clone Drop-Down List', '', $attributes);

   echoT(
       $crpt->openReport()

      .$crpt->openRow   ()
      .$crpt->writeLabel('Target List:', 120)
      .$crpt->writeCell (htmlspecialchars($targetDDLInfo->pff_strFieldNameUser))
      .$crpt->closeRow  ()

      .$crpt->openRow   ()
      .$crpt->writeLabel('Source List:')
      .$crpt->writeCell (strSourceDDLTable($lTableID, $lFieldID, $lNumDDLs, $ddls))
      .$crpt->closeRow  ());

   echoT($crpt->closeReport());

   closeBlock();
   
   function strSourceDDLTable($lTableID, $lFieldID, $lNumDDLs, &$ddls){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($lNumDDLs == 0){
         return('<i>There are no drop-down lists configured in your database!</i>');
      }      
      
      $strOut = '
           <table class="enpRpt">
              <tr>
                 <td class="enpRptLabel">
                    &nbsp;
                 </td>
                 <td class="enpRptLabel">
                    &nbsp;
                 </td>
                 <td class="enpRptLabel">
                    Parent Table
                 </td>
                 <td class="enpRptLabel">
                    List Name
                 </td>
                 <td class="enpRptLabel">
                    Entries
                 </td>
              </tr>';
              
      $idx = 1;
      foreach ($ddls as $ddl){
         $lSourceDDLFID = $ddl->lFieldID;
         $strEntryList = '<ul style="margin-top: 0px; margin-bottom: 0px;">'."\n";
         foreach ($ddl->entries as $entry){
            $strEntryList .= '<li style="margin-left: -10pt;">'.htmlspecialchars($entry->strEntry).'</li>'."\n";
         }
         $strEntryList .= '</ul>';
      
      
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$ddl   <pre>');
echo(htmlspecialchars( print_r($ddl, true))); echo('</pre></font><br>');
die;
// ------------------------------------- */
      $strOut .= '
              <tr class="makeStripe">
                 <td class="enpRpt" style="text-align: center;">'
                    .number_format($idx).'
                 </td>
                 <td class="enpRpt" style="text-align: center;">'
                    .strLinkSpecial_CloneDDLListRun($lTableID, $lFieldID, $lSourceDDLFID, 'Clone this list', true).'
                 </td>
                 <td class="enpRpt" style="width: 160pt;">'
                    .htmlspecialchars($ddl->strUserTableName).'<br>('
                    .$ddl->enumAttachType.')
                 </td>
                 <td class="enpRpt" style="width: 160pt;">'
                    .htmlspecialchars($ddl->strFieldNameUser).'
                 </td>                                 
                 <td class="enpRpt" style="width: 180pt;">'
                    .$strEntryList.'
                 </td>
              </tr>';
         ++$idx;
      }      
      
      $strOut .= '</table>';
      return($strOut);
   }
   