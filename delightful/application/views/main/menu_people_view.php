<?php

   echoT(
       '<br>');
   if (bAllowAccess('dataEntryPeopleBizVol')){
      echoT(
          '<b>Add New:</b>
           <ul style="margin-top: 4pt;">
              <li>'
                .anchor('people/people_add_new/selCon',     'Add New Person').'
              </li>
           </ul>');
   }

   if (bAllowAccess('showPeople')){
      echoT('
           <b>Directory</b>
           <ul style="margin-top: 4pt;">
             <li>'.anchor('people/people_dir/view/A',           'By Last Name').'
             <li>'.anchor('people/people_dir/relView/A',        'Relationship Directory').'
             <li>'.anchor('people/people_household_dir/view/A', 'Household Directory').'
           </ul>
           <b>Utilities</b>
           <ul style="margin-top: 4pt;">');

      if (bAllowAccess('editPeopleBizVol')){
         echoT('<li>'.anchor('util/dup_records/opts/'.CENUM_CONTEXT_PEOPLE, 'Consolidate Duplicates').'</li>');
      }

      echoT('
              <li>'.anchor('people/people_search/searchOpts',     'Search')    .'</li>');

      echoT('
           </ul>');
   }

