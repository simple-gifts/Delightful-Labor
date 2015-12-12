<?php
   echoT(strLinkAdd_ClientVoc('Add new client vocabulary', true).' '
        .strLinkAdd_ClientVoc('Add new client vocabulary', false).'<br><br>');

   if ($lNumVocs <= 0){
      echoT('<i>There are no client vocabularies defined in your database.</i><br><br>');
   }else {
      echoT(htmlVocList(true, $clsVocs));
   }

?>