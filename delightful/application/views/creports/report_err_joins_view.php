<?php

   echoT('
      <div class="error">
         <b>Unable to run custom report!</b><br><br>
         Your report contains too many drop-down lists. Custom reports can only display 
         a maximum of <b>45</b> drop-down lists.<br><br>
         Your report current has <b>'.$lNumDDLJoins.'</b> drop-down lists.
      </div>');