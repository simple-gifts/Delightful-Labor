<?php

echoT('You have requested to transfer the client to the new location <b>'.$strLocName.'</b>.<br><br>
       The client is currently sponsored by '.($bOneProblemo ? 'a' : $lNumProblemos).' sponsorship program'
       .($bOneProblemo ? '' : 's').'<br>
       that '.($bOneProblemo ? 'is' : 'are').' not supported by the new location:<br><ul>');
       
foreach ($sponProgs as $prog){
   if ($prog->bOldLoc && !$prog->bNewLoc){
      echoT('<li style="margin-left: 10pt;">'.htmlspecialchars($prog->strProg).'</li>');
   }
}       
echoT('</ul><br>');

echoT('Please select one of the following options:
      <ul>
         <li style="margin-left: 10pt;">To add '.($bOneProblemo ? 'this' : 'these').' sponsorship program'.($bOneProblemo ? '' : 's')
            .' to location <b>'.$strLocName.'</b> '
            .anchor('clients/client_record/xfer3/'.$lClientID.'/'.$lNewLocID.'/'.$lStatCatID.'/'.$lVocID.'/'.$dteEDate, 'click here').'.
         </li>
         <li style="margin-left: 10pt;">To <b>cancel</b> the transfer, '.strLinkView_ClientRecord($lClientID, 'click here', false).'.
         </li>
      </ul>');
      


       