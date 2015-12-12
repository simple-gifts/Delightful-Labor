<?php

if (isset($enumContext)){
   switch ($enumContext){
      case CENUM_CONTEXT_BIZ:
         echoT(strLinkAdd_Biz('Add new person', true).'&nbsp;'
              .strLinkAdd_Biz('Add new business/organization', false).'<br><br>');
         break;
         
      case CENUM_CONTEXT_VOLUNTEER:
      case CENUM_CONTEXT_PEOPLE:
         echoT(strLinkAdd_PeopleRec('Add new person', true).'&nbsp;'
              .strLinkAdd_PeopleRec('Add new person', false).'<br><br>');
         break;
      default:
         break;
   }
}

echoT($strSearchLabel);
echoT($strHTMLSearchResults);

