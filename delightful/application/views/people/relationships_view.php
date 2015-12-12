<?php

   echoT(strLinkAdd_PeopleRelItem('Add new relationship list item', true).' '
        .strLinkAdd_PeopleRelItem('Add new relationship list item', false).'<br><br>');


   if ($lNumRelListItems <= 0){
      echoT('<i>There are no relationship list items defined in your database.</i><br><br>');
   }else {
      echoT($strHTMLRelItemList);
   }





?>