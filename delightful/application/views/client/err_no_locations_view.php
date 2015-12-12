<?php
   global $gbAdmin;

   echoT('There are no client locations. Please define locations before adding clients.');
   If ($gbAdmin){
      echoT('<br><br>You can add client locations '
                 .strLinkAdd_ClientLocation('here', false).'.');      
   }else {
      echoT('<br><br>Please contact your '.CS_PROGNAME.' administrator to configure the client locations.');
   }

