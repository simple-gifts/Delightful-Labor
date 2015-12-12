<?php
   global $gbDev;

   if (!(bAllowAccess('showFinancials') || bAllowAccess('showGrants'))) return('');

   echoT(
      '<br>');


   if (bAllowAccess('dataEntryGifts')){
      echoT('
          <b>Donations:</b>
          <ul style="margin-top: 4pt;">
             <li>'
               .anchor('donations/add_edit/new_gift', 'Add new donation') .'
             </li>
          </ul>');
   }


   if (bAllowAccess('showFinancials')){
      echoT('
          <b>Deposits:</b>
          <ul style="margin-top: 4pt;">
             <li>'
                .anchor('financials/deposits_add_edit/addDeposit', 'Add Deposit').'
             </li>
             <li>'
                .anchor('financials/deposit_log/view',             'Deposit Log').'
             </li>
        </ul>');
   }

   if (bAllowAccess('showGrants') && $gbDev){
      echoT('
          <b>Grants:</b>
          <ul style="margin-top: 4pt;">
                    <li>'.anchor('grants/provider_directory/viewProDirectory/true', 'Provider Directory').'</li>
                    <li>'.anchor('grants/grants_calendar/viewCal',             'Calendar').'</li>
        </ul>');
   }

