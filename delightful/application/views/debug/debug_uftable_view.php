<?php

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '130pt';
   $clsRpt->bValueEscapeHTML = false;

   showUFTableInfo($clsRpt, $schema, $lTableID);
   showUFFieldInfo($clsRpt, $schema, $lTableID);

   function showUFFieldInfo(&$clsRpt, &$schema, $lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $table = &$schema[$lTableID];
//      $lTableID = $table->lTableID;
      openBlock('Fields for table <b>'.htmlspecialchars($table->strUserTableName).'</b>', '');

      echoT('
         <table class="enpRpt">
            <tr>
               <td class="enpRptLabel">
                  Field ID
               </td>
               <td class="enpRptLabel">
                  User Name
               </td>
               <td class="enpRptLabel">
                  Internal Name
               </td>
               <td class="enpRptLabel">
                  Sort IDX
               </td>
               <td class="enpRptLabel">
                  Field Type
               </td>
            </tr>
         ');

      foreach ($table->fields as $ufield){
         echoT('
               <tr class="makeStripe">
                  <td class="enpRpt" style="text-align: center;">'
                     .str_pad($ufield->lFieldID, 5, '0', STR_PAD_LEFT).'
                  </td>
                  <td class="enpRpt">'
                     .htmlspecialchars($ufield->strFieldNameUser).'
                  </td>
                  <td class="enpRpt">'
                     .htmlspecialchars($ufield->strFieldNameInternal).'
                  </td>
                  <td class="enpRpt" style="text-align: center;">'
                     .$ufield->lSortIDX.'
                  </td>
                  <td class="enpRpt">'
                     .$ufield->enumFieldType.'
                  </td>
               </tr>
            ');
      }

      echoT('
         </table>');
      closeBlock();
   }

   function showUFTableInfo(&$clsRpt, &$schema, $lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $table = &$schema[$lTableID];
/*
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$schema   <pre>');
echo(htmlspecialchars( print_r($schema, true))); echo('</pre></font><br>');
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$table   <pre>');
echo(htmlspecialchars( print_r($table, true))); echo('</pre></font><br>');
*/

//      $lTableID = $table->lTableID;
      openBlock('User Table <b>'.htmlspecialchars($table->strUserTableName).'</b>', '');

      echoT(
          $clsRpt->openReport());
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Table ID:')
         .$clsRpt->writeCell (str_pad($lTableID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('User Name:')
         .$clsRpt->writeCell (htmlspecialchars($table->strUserTableName))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Internal Name:')
         .$clsRpt->writeCell (htmlspecialchars($table->strDataTableName))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Parent Table:')
         .$clsRpt->writeCell (htmlspecialchars($table->enumAttachType))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Multi-Entry?:')
         .$clsRpt->writeCell (($table->bMultiEntry ? 'Yes' : 'No'))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Field Prefix:')
         .$clsRpt->writeCell (htmlspecialchars($table->strFieldPrefix))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Data Table Key FN:')
         .$clsRpt->writeCell (htmlspecialchars($table->strDataTableKeyID))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Data Table Foreign FN:')
         .$clsRpt->writeCell (htmlspecialchars($table->strDataTableFID))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('# Fields:')
         .$clsRpt->writeCell ($table->lNumFields)
         .$clsRpt->closeRow  ());

      echoT($clsRpt->closeReport());
      closeBlock();
   }

