<?php

echoT(strLinkAdd_Client('Add new client', true).'&nbsp;'
     .strLinkAdd_Client('Add new client', false).'<br><br>');

if ($lNumAvail <= 0){
   echoT('<br><i>There are no clients that match your search criteria.</i><br><br><br>');
}else {
   openWrapperTable();
   echoT('<br>'.$strDirectory);
   echoT('<br>');
   closeWrapperTable();
}

