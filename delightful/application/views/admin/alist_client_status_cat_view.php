<?php
   echoT(strLinkAdd_ClientStatCat('Add new client status category', true).' '
        .strLinkAdd_ClientStatCat('Add new client status category', false).'<br><br>');
        
   if ($numStatCat <= 0){
      echoT('<i>There are currently no client status records defined for this sponsorship category.</i><br><br>');
   }else {
      echoT(strClientStatusCatTable($statCats, $statCatsEntries, true, true));
   }
       



       
?>