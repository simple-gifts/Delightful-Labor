<?php
   global $gbAdmin, $glUserID, $gbDev;

   echoT('<br>');

   echoT('<b><u>Custom Reports</b></u>');
   echoT(
        '<ul style="margin-top: 4pt;">
            <li>'.anchor('creports/add_edit_crpt/add_edit/0',         'Add New').'</li>
            <li>'.anchor('creports/custom_directory/view/'.$glUserID, 'Report Directory').'</li>
          </ul><br>');


   echoT('<b><u>Predefined Reports</u></b>');

         //---------------------
         // Admin
         //---------------------
   if ($gbAdmin){
      echoT(
           '<ul style="margin-top: 4pt;">
                    <li><b>Admin</b></li>
                    <ul>
                        <li>'.anchor('reports/pre_admin/usageOpts',  'Usage Log').'</li>
                        <li>'.anchor('reports/pre_admin/loginLog/0', 'User Login History').'</li>
                    </ul>
             </ul>');
   }

         //---------------------
         // Businesses/Organizations
         //---------------------
   if (bAllowAccess('showReports')){
      if (bAllowAccess('showPeople')){
         echoT(
           '<ul style="margin-top: 4pt;">
              <li><b>Businesses/Organizations</b></li>
              <ul>
                  <li>'.anchor('reports/pre_groups/showOpts/'.CENUM_CONTEXT_BIZ, 'Groups').'</li>
              </ul>
            </ul>');
      }

         //---------------------
         // Clients
         //---------------------
      if (bAllowAccess('showClients')){
         echoT(
           '<ul style="margin-top: 4pt;">
              <li><b>Clients</b></li>
              <ul>
                  <li>'.anchor('reports/pre_groups/showOpts/'.CENUM_CONTEXT_CLIENT, 'Groups')               .'</li>
                  <li>'.anchor('clients/client_search/searchOpts/true', 'Clients Available for Sponsorship').'</li>
                  <li>'.anchor('reports/pre_clients/bDay',              'Birthday Report')                  .'</li>
                  <li style="margin-top: 4px;">
                     <b>Client Programs</b>
                     <ul>
                        <li>'.anchor('cprograms/cprog_enrollees/opts', 'Enrollees')   .'</li>
                     </ul>
                  </li>
                  <li style="margin-top: 4px;">
                     <b>Pre/Post Test Reports</b>
                     <ul>
                        <li>'.anchor('reports/pre_client_pre_post/opts',       'Answer Breakdown')             .'</li>
                        <li>'.anchor('reports/pre_client_pre_post/dirViaTest', 'Directory Via Test')   .'</li>
                     </ul>
                  </li>
                  <li style="margin-top: 4px;">
                     <b>Aggregate Reports</b>
                     <ul>
                        <li>'.anchor('reports/pre_clients/aggRptsLocAge', 'Location / Age')   .'</li>
                        <li>'.anchor('reports/pre_clients/aggRptsStatus/true',  'Status (active clients)').'</li>
                        <li>'.anchor('reports/pre_clients/aggRptsStatus/false', 'Status (inactive clients)').'</li>
                     </ul>
                  </li>
              </ul>
           </ul>');
      }

         //---------------------
         // Donations
         //---------------------
      if (bAllowAccess('showFinancials') || bAllowAccess('showSponsorFinancials')){
         echoT(
           '<ul style="margin-top: 4pt;">
              <li><b>Donations</b></li>
               <ul>');
         if (bAllowAccess('showFinancials')){
            echoT('
                  <li>'.anchor('reports/pre_gifts/recentOpts',    'Recent Donations')          .'</li>
                  <li>'.anchor('reports/pre_gifts/yearEndOpts',   'Year-end Report')           .'</li>
                  <li>'.anchor('reports/pre_gifts/timeframeOpts', 'Donations by Time Frame')   .'</li>
                  <li>'.anchor('reports/pre_gifts/accountOpts',   'Donations by Account')      .'</li>
                  <li>'.anchor('reports/pre_gifts/campOpts',      'Donations by Campaign')     .'</li>');
         }
         echoT('
                  <li style="margin-top: 4px;">
                     <b>Aggregate Reports</b>
                     <ul>');
         if (bAllowAccess('showFinancials')){
            echoT('
                        <li>'.anchor('reports/pre_gifts/agOpts',     'Donations Aggregate Report').'</li>');
         }
         echoT('
                        <li>'.anchor('reports/pre_gifts/agSponOpts', 'Sponsorship Payments')      .'</li>
                     </ul>
                  </li>');

         if (bAllowAccess('showFinancials')){
            echoT('
                  <li style="margin-top: 4px;">
                     <b>Acknowledgments</b>
                     <ul>
                        <li>'.anchor('reports/pre_gift_ack/showOpts/gifts', 'Gifts')      .'</li>
                        <li>'.anchor('reports/pre_gift_ack/showOpts/hon',   'Honorariums').'</li>
                        <li>'.anchor('reports/pre_gift_ack/showOpts/mem',   'Memorials')  .'</li>
                     </ul>
                  </li>');
         }
         echoT('
              </ul>
           </ul>');
      }

         //-----------------
         // Images/Documents
         //-----------------
      if (bAllowAccess('showReports')){
            echoT(
           '<ul style="margin-top: 4pt;">
              <li><b>Images/Documents</b>
                  <ul>
                     <li>'.anchor('reports/image_doc/id_overview/overview', 'Overview').'</li>
                  </ul>
               </li>
            </ul>');
      }


         //---------------------
         // People
         //---------------------
      if (bAllowAccess('showPeople')){
         echoT(
           '<ul style="margin-top: 4pt;">
              <li><b>People</b></li>
              <ul>
                  <li>'.anchor('reports/pre_groups/showOpts/'.CENUM_CONTEXT_PEOPLE, 'Groups').'</li>
              </ul>
           </ul>');
      }

         //---------------------
         // Sponsors
         //---------------------
      if (bAllowAccess('showSponsors')){
         echoT(
           '<ul style="margin-top: 4pt;">
                 <li><b>Sponsors</b></li>
                 <ul>
                     <li>'.anchor('reports/pre_groups/showOpts/'.CENUM_CONTEXT_SPONSORSHIP, 'Groups')  .'</li>');
         if (bAllowAccess('showFinancials')){
            echoT('
                     <li>'.anchor('reports/pre_spon_income/showOpts',      'Income')                      .'</li>
                     <li>'.anchor('reports/pre_spon_past_due/showOpts',    'Sponsor Past Due')            .'</li>');
         }
      }
      if (bAllowAccess('showClients')){
         echoT('
                  <li>'.anchor('reports/pre_spon_via_prog/showOptsLoc', 'Sponsors Via Client Location').'</li>');
      }
      echoT('
                  <li>'.anchor('reports/pre_spon_via_prog/showOpts',    'Sponsors Via Program')        .'</li>');

      if (bAllowAccess('showClients')){
         echoT('
                  <li>'.anchor('reports/pre_spon_wo_clients/rpt',       'Sponsors Without Clients')    .'</li>
              </ul>');
      }
      if (bAllowAccess('showSponsors')){
         echoT('</ul>');
      }

         //-----------------
         // Staff
         //-----------------
      if (bAllowAccess('showReports') && bAllowAccess('timeSheetAdmin')){
         echoT(
           '<ul style="margin-top: 4pt;">
                 <li><b>Staff</b></li>
                  <ul>
                     <li>'.anchor('reports/pre_timesheets/showOpts', 'Time Sheet Reports').'</li>
                  </ul>
               </li>
            </ul>');
      }


         //---------------------
         // Volunteers
         //---------------------
      if (bAllowAccess('showPeople')){
         echoT(
           '<ul style="margin-top: 4pt;">
                 <li><b>Volunteers</b></li>
                 <ul>
                     <li>'.anchor('reports/pre_groups/showOpts/'.CENUM_CONTEXT_VOLUNTEER, 'Groups')     .'</li>
                     <li>'.anchor('reports/pre_vol_job_skills/showOpts', 'Job Skills')                  .'</li>
                     <li>'.anchor('reports/pre_vol_hours/showOptsYear',  'Volunteer Hours by Year')     .'</li>
                     <li>'.anchor('reports/pre_vol_hours/showOptsTFSum', 'Volunteer Hours (Summary / By Time Frame)','id="rpts_vol_sum"').'</li>
                     <li>'.anchor('reports/pre_vol_hours/showOpts',      'Volunteer Hours (Scheduled)') .'</li>
                     <li>'.anchor('reports/pre_vol_hours/showOptsPVA',   'Volunteer Hours - Scheduled vs. Actual') .'</li>
                     <li>'.anchor('reports/pre_vol_schedule/past',       'Past Events')                 .'</li>
                     <li>'.anchor('reports/pre_vol_schedule/current',    'Current and Future Events')   .'</li>
                     <li>'.anchor('reports/pre_vol_jobcodes/showOpts',   'Job Codes',    'id="viewrpts_vol_jcode"').'</li>
                 </ul>
             </ul>');
      }

         //---------------------
         // Miscellaneous
         //---------------------
      echoT(
           '<ul style="margin-top: 4pt;">
                 <li><b>Miscellaneous</b></li>
                 <ul>
                     <li>'.anchor('reports/pre_attrib/attrib',         'Attributed To').'</li>
                     <li>'.anchor('reports/pre_log_search/searchOpts', 'Log Search').'</li>
                     <li>'.anchor('reports/pre_data_entry/daOpts',     'Data Entry Log').'</li>
                 </ul>
             </ul>');
      echoT('</ul>');

         //-----------------
         // exports
         //-----------------
      if (bAllowAccess('allowExports')){
         echoT(
            '<b><u>Exports</u></b>
                <ul style="margin-top: 4pt;">
                   <li>'.anchor('reports/exports/showTableOpts', 'Exports').'
                   </li>
                </ul>');
      }
   }

