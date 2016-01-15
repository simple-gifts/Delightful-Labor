<?php
   if (!bAllowAccess('adminOnly')) return;

         // System Lists
   echoT(
       '<br><b>System Lists:</b>
          <ul style="margin-top: 4pt;">
             <li>'
               .anchor('admin/alists/showLists', 'Lists')  .'
             </li>
          </ul>');

/*
   echoT(
       '<br>'
      .anchor('admin/alists/showLists',         'Lists')            .'<br><br>'
*/

         // Delightful Labor Accounts
   echoT(
       '<b>Delightful Labor Accounts:</b>
          <ul style="margin-top: 4pt;">
             <li>'.anchor('admin/accts/userAcctDir/A', 'User Accounts')    .'</li>
             <li>'.anchor('admin/accts/userAccess',    'Users Via Access')    .'</li>
          </ul>');

         // Your Organization
//             <li>'.anchor('admin/timesheets/ts_projects/viewTSProjects',    'Staff Time Sheet Projects').'</li>
   echoT('
        <b>Your Organization:</b>
           <ul style="margin-top: 4pt;">
             <li>'.anchor('admin/org/orgView',                            'Organization Record').'</li>
             <li>'.anchor('admin/timesheets/view_tst_record/viewTSTList', 'Staff Time Sheet Templates').'</li>
           </ul>');

         // Personalization
   echoT('
      <b>Personalization:</b>
         <ul style="margin-top: 4pt;">
            <li>'.anchor('cprograms/cprograms/overview',     'Client Programs').'</li>
            <li>'.anchor('admin/personalization/overview',   'Tables').'</li>
         </ul>');

         // Custom Forms
   echoT('
      <b>Custom Forms:</b>
         <ul style="margin-top: 4pt;">
            <li>'.anchor('custom_forms/custom_form_add_edit/view/client',    'Client Forms').'
            </li>
         </ul>');

         // Database Utilities
   echoT('
      <b>Database Utilities:</b>
         <ul style="margin-top: 4pt;">
            <li>'.anchor('admin/db_zutil/opt',    'Optimize Your Database').'</li>
            <li>'.anchor('admin/db_zutil/backup', 'Database Backup')       .'</li>
            </li>
         </ul>');

         // Import
   echoT('
      <b>Import:</b>

         <ul style="margin-top: 4pt;">
            <li>'.anchor('admin/import/'.CENUM_CONTEXT_PEOPLE,     'People Records'               ).'</li>
            <li>'.anchor('admin/import/'.CENUM_CONTEXT_CLIENT,     'Client Records'               ).'</li>
            <li>'.anchor('admin/import/'.CENUM_CONTEXT_BIZ,        'Business/Organization Records').'</li>
            <li>'.anchor('admin/import/'.CENUM_CONTEXT_GIFT,       'Gift Records'                 ).'</li>
            <li>'.anchor('admin/import/'.CENUM_CONTEXT_SPONSORPAY, 'Sponsor Payments'             ).'</li>
            <li>'.anchor('admin/import/ptables',                   'Personalized Tables',         'id="v_ad_in_pt"').'</li>
            <li>'.anchor('admin/import/log',         'Import Log'                   ).'</li>
         </ul>

      ');




?>