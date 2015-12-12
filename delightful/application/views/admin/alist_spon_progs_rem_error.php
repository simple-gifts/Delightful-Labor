<?php

echoT('
   <font color="red">You have requested to remove sponsorship program
   <b>'.htmlspecialchars($clsProgram->strProg).'</b>, but this sponsorship program<br>is associated with other
   records.<br><br>Please correct these associations before removing this sponsorship program</font><br><br>');
   
if ($lNumSponsors > 0){
   echoT('* Number of <b>Sponsors</b> associated with this program: '
       .number_format($lNumSponsors).' '
       .strLinkView_SponsorsViaSponProg($lProgID, 'View sponsors', true).' '
       .strLinkView_SponsorsViaSponProg($lProgID, 'View sponsors', false).'<br><br>');
}

if ($lNumClients > 0){
   echoT('* Number of <b>Clients</b> associated with this program: '
       .number_format($lNumClients).' '
       .strLinkView_ClientsViaSponProg($lProgID, 'View clients', true).' '
       .strLinkView_ClientsViaSponProg($lProgID, 'View clients', false).'<br><br>');
}

