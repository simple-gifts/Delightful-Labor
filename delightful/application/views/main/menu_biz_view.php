<?php

   echoT('<br>');
      
   if (bAllowAccess('dataEntryPeopleBizVol')){
      echoT(
          '<b>New Business/Organization:</b>
          <ul style="margin-top: 4pt;">
             <li>'
               .anchor('biz/biz_add_edit/addEditBiz/0', 'Add New Business/Organization')  .'
             </li>
          </ul>');
   }

   if (bAllowAccess('viewPeopleBizVol')){
      echoT('
           <b>Directory:</b>'
         .'<ul style="margin-top: 4pt;">
             <li>'.anchor('biz/biz_directory/view/A',         'Business').'
             <li>'.anchor('biz/biz_directory/viewCName/A',    'Contacts by Name').'
             <li>'.anchor('biz/biz_directory/viewCBizName/A', 'Contacts by Business').'
           </ul>');

      echoT(
         '<b>Utilities:</b>
          <ul style="margin-top: 4pt;">');
          
      if (bAllowAccess('editPeopleBizVol')){
         echoT('<li>'.anchor('util/dup_records/opts/'.CENUM_CONTEXT_BIZ, 'Consolidate Duplicates').'</li>');
      }  
          
      echoT('
          <li>'
             .anchor('biz/biz_search/searchOpts',     'Search').'
          </li>
       </ul>');
   }

