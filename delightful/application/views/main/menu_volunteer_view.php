<?php

   echoT(
       '<br>');
       
   if (bAllowAccess('dataEntryPeopleBizVol')){
      echoT(
         '<b>New Volunteer:</b>
          <ul style="margin-top: 4pt;">
             <li>'
                .anchor('volunteers/vol_add_edit/addEditS1', 'Add New Volunteer') .'
             </li>
          </ul>');
   }
      
   if (bAllowAccess('showPeople')){
      echoT(
         '<b>Volunteer Directory:</b>
          <ul style="margin-top: 4pt;">
             <li>'
                .anchor('volunteers/vol_directory/view/A',  'Directory').'
             </li>
          </ul>');
          
      echoT('
         <b>Events:</b>');
   }
   
   if (bAllowAccess('dataEntryPeopleBizVol')){
      echoT('
          <ul style="margin-top: 4pt;">
             <li>'.anchor('volunteers/events_add_edit/addEditEvent/0', 'Add Event',  
                            'class="menuItem"'));
   }
   
   
   if (bAllowAccess('showPeople')){
      echoT('
          <li>'.anchor('volunteers/events_cal/viewEventsCalendar', 'Event Calendar',  
                         'class="menuItem"').'
          <li>'.anchor('volunteers/events_schedule/viewEventsList', 'Event Schedule (list)',  
                         'class="menuItem"').'
        </ul>');
   }
        
   if (bAllowAccess('editPeopleBizVol')){
      echoT(
         '<b>Registration:</b>
          <ul style="margin-top: 4pt;">
             <li>'
               .anchor('volunteers/registration/view', 'Registration Forms').'
             </li>
          </ul>');
   }
   
   
   if (bAllowAccess('showPeople')){
            echoT(
         '<b>Volunteer Search:</b>
          <ul style="margin-top: 4pt;">
             <li>'
                .anchor('volunteers/vol_search/searchOpts', 'Search').'
             </li>
          </ul>');
   }

