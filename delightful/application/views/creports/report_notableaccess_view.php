<?php
   echoT(
       '<div class="error">
          <b>You do not have access to one or more personallized tables referenced in this report.</b><br><br>
          Please contact your system administrator.<br><br>
          Based on your account settings, the following tables are not available:
          <ul>');
   foreach ($failTables as $ft){
      echoT('<li>'.htmlspecialchars($ft).'</li>'."\n");
   }
          
   echoT('
        </ul>
        </div>');