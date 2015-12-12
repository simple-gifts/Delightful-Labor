<?php
   global $glUserID;

//         <li style="margin-left: 20pt; padding: 0pt;">'.anchor('reminders/reminder_record/viewViaUser/'.$glUserID, 'Your Reminders') .'</li>
   echoT(
       '<br><b><u>More...</u></b>'
      .'<ul style="list-style-type: square; display:inline; ">
         <li style="margin-left: 20pt; padding: 0pt; margin-top: 7px;"><b>Your Account</b>
            <ul style="list-style-type: square; display:inline; ">
               <li style="margin-left: 20pt; padding: 0pt;margin-top: 3px;">'.anchor('more/user_acct/view/'.$glUserID,  'Your Profile and Preferences') .'</li>
               <li style="margin-left: 20pt; padding: 0pt;margin-top: 3px;">'.anchor('more/user_acct/pw',               'Change Password') .'</li>
               <li style="margin-left: 20pt; padding: 0pt;margin-top: 3px;">'.anchor('staff/timesheets/ts_log/viewLog', 'Time Sheets') .'</li>');

   if (bAllowAccess('timeSheetAdmin')){
      echoT('<li style="margin-left: 20pt; padding: 0pt;margin-top: 3px;">'.anchor('staff/timesheets/ts_admin/viewUsers', 'Time Sheet Administration').'</li>');
   }

   echoT('
            </ul>
         </li>');

   if (bAllowAccess('inventoryMgr')){
      echoT('
         <li style="margin-left: 20pt; padding: 0pt;"><b>Inventory Management</b>
            <ul style="list-style-type: square; display:inline; ">
               <li style="margin-left: 20pt; padding: 0pt; margin-top: 3px;">'.anchor('staff/inventory/icat/viewICats', 'Categories').'</li>
               <li style="margin-left: 20pt; padding: 0pt; margin-top: 3px;">'.anchor('staff/inventory/items', 'Items').'</li>
               <li style="margin-left: 20pt; padding: 0pt; margin-top: 3px;">'.anchor('staff/inventory/reports', 'Reports').'</li>
            </ul>
         </li>');
         
   if (bAllowAccess('inventoryMgr')){
      echoT('
         <li style="margin-left: 20pt; padding: 0pt;"><b>Inventory Management</b>
            <ul style="list-style-type: square; display:inline; ">
               <li style="margin-left: 20pt; padding: 0pt; margin-top: 3px;">'.anchor('staff/inventory/icat/viewICats', 'Categories').'</li>
               <li style="margin-left: 20pt; padding: 0pt; margin-top: 3px;">Items
                  <ul>
                     <li>'.anchor('staff/inventory/icat/viewICatsRemOnly',    'Removed Items').'</li>
                     <li>'.anchor('staff/inventory/icat/viewICatsLostOnly',   'Lost Items').'</li>
                     <li>'.anchor('staff/inventory/icat/itemsCheckedOutList', 'Checked-Out Items').'</li>
                     <li>'.anchor('staff/inventory/icat/itemsAllList',        'All Items').'</li>
                  </ul>
           <!--    <li>'.anchor('staff/inventory/reports', 'Reports').'</li> -->
            </ul>
         </li>');
   }
         
         
         
   }

   if (bAllowAccess('showAuctions')){
      echoT('
         <li style="margin-left: 20pt; padding: 0pt;"><b>Silent Auctions</b>
            <ul style="list-style-type: square; display:inline; ">
               <li style="margin-left: 20pt; padding: 0pt; margin-top: 3px;">'.anchor('auctions/auction_add_edit/addEditAuction/0', 'Add New Auction').'</li>
               <li style="margin-left: 20pt; padding: 0pt; margin-top: 3px;">'.anchor('auctions/auctions/auctionEvents', 'Silent Auction Events') .'</li>
               <li style="margin-left: 20pt; padding: 0pt; margin-top: 3px;">'.anchor('auctions/bid_templates/main',     'Bid Sheets') .'</li>
            </ul>
         </li>');
   }

   echoT('
         <li style="margin-left: 20pt; padding: 0pt;">'.anchor('more/about/aboutDL', 'About') .'</li>
         <li style="margin-left: 20pt; padding: 0pt; margin-top: 7px;">'.anchor('login/signout', 'Sign Out') .'</li>

     </ul>');
