<?php
   echoT('<br>');

   if (bAllowAccess('showClients')){

      echoT('
          <b>New Clients:</b>
          <ul style="margin-top: 4pt;">
             <li>'.anchor('clients/client_rec_add_edit/addNewS1',     'Add New Client').'
          </ul>');


      echoT('
          <b>Directories</b>
          <ul style="margin-top: 4pt;">
             <li>'.anchor('clients/client_dir/name/N/A', 'By Client Last Name').'
             <li>'.anchor('clients/client_dir/view/0',   'By Location').'
             <li>'.anchor('clients/client_dir/sProg/0',  'By Sponsorship Program').'
           </ul>

          <b>Client Programs</b>
          <ul style="margin-top: 4pt;">
             <li>'.anchor('cprograms/cprog_dir/cprogList', 'Client Programs List').'
          </ul>
             </li>

          <b>Pre/Post Tests</b>
          <ul style="margin-top: 4pt;">
             <li>'.anchor('cpre_post_tests/pptests/overview', 'Pre/Post Tests').'</li>
         </ul>

         <b>Utilities</b>
         <ul style="margin-top: 4pt;">
            <li>'.anchor('util/dup_records/opts/'.CENUM_CONTEXT_CLIENT, 'Consolidate Duplicate Client Records').'</li>
            <li>'.anchor('clients/client_search/searchOpts', 'Client Search').'</li>
            <li>'.anchor('clients/client_verify/status',     'Verify Status').'</li>
         </ul>');




   }
?>