<?php

global $genumDateFormat;

echoT(strLinkAdd_ClientStat($lClientID, 'Add new status for this client', true).' '
     .strLinkAdd_ClientStat($lClientID, 'Add new status for this client', false).'<br><br>');

if ($lNumClientStatus==0){
   echoT('<i>There are no status records for this '.$client->cv_strVocClientS.'</i><br>');
}else {
   showStatusHistory(true, true, $clientStatus, $lNumClientStatus, $lClientID, '95%');
}



