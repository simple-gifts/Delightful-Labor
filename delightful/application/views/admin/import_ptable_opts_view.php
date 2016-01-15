<?php

   $strContext = strXlateContext($enumAttachType, true, false);
   
   if ($bNoTables){
      echoT('<br>There are no <b>'.$strContext.'</b> personalized tables. Unable to continue.<br><br>');
      return;
   }
   
echoT('Hello');