<?php

   echoT('
      <fieldset class="enpFS" style="width: 600px; align: left;">
           <legend class="enpLegend">
              <b>'.$strTTypeLabel.' Table: '.htmlspecialchars($strUserTableName).': <i>'
              .htmlspecialchars($pff_strFieldNameUser)
              .'</b></i>
           </legend>');
           
   echoT($strLogEntries);           
           
   echoT('</fieldset>');



