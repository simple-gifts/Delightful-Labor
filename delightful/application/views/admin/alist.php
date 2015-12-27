<?php
   global $gclsChapterVoc;

   if (!bAllowAccess('adminOnly')) return('');

      // Accounting Country of Origin
   echoT('
      <ul>
         <li style="margin-top: 8px;"><b>Admin Lists</b>
            <ul>
               <li style="margin-top: 2px;">'
                  .anchor('admin/admin_special_lists/aco/view', 'Accounting Country of Origin').'
               </li>
            </ul>
         </li>');
         
      // Businesses
   echoT('
         <li style="margin-top: 8px;"><b>Businesses</b>
            <ul>
               <li style="margin-top: 2px;">'
                  .anchor('admin/alists_generic/view/'.CENUM_LISTTYPE_BIZCAT, 'Business Categories').'
               </li>
               <li style="margin-top: 2px;">'
                  .anchor('admin/alists_generic/view/'.CENUM_LISTTYPE_BIZCONTACTREL, 'Business Contact Relationships').'
               </li>
            </ul>
         </li>');

      // clients
   echoT('
         <li style="margin-top: 8px;"><b>Clients</b>
            <ul>
               <li style="margin-top: 2px;">'
                  .anchor('admin/admin_special_lists/clients/locationView', 'Client Locations', 'id="clientLoc"').'
               </li>
               <li style="margin-top: 2px;">'
                  .anchor('admin/admin_special_lists/clients/statCatView', 'Client Status Categories', 'id="clientStatCat"').'
               </li>
               <li style="margin-top: 2px;">'
                  .anchor('admin/alists_generic/view/'.CENUM_LISTTYPE_CPREPOSTCAT, 'Client Pre/Post Test Categories', 'id="clientPrePost"').'
               </li>
               <li style="margin-top: 2px;">'
                  .anchor('admin/admin_special_lists/clients/vocView', 'Client Vocabulary', 'id="clientVoc"').'
               </li>
            </ul>
         </li>');
         
      // Donations
   echoT('
         <li style="margin-top: 8px;"><b>Donations</b>
            <ul>
               <li style="margin-top: 2px;">'
                  .anchor('accts_camp/accounts/view', 'Accounts & Campaigns').'
               </li>
               <li style="margin-top: 2px;">'
                  .anchor('admin/alists_generic/view/'.CENUM_LISTTYPE_CAMPEXPENSE, 'Campaign Expenses').'
               </li>
               <li style="margin-top: 2px;">'
                  .anchor('admin/admin_special_lists/hon_mem/honView', 'Honorariums').' / '
                         .anchor('admin/admin_special_lists/hon_mem/memView', 'Memorials').'
               </li>
               <li style="margin-top: 2px;">'
                  .anchor('admin/alists_generic/view/'.CENUM_LISTTYPE_INKIND, 'In-kind Donation Types').'
               </li>
               <li style="margin-top: 2px;">'
                  .anchor('admin/alists_generic/view/'.CENUM_LISTTYPE_MAJORGIFTCAT, 'Major Gift Categories').'

               </li>
               <li style="margin-top: 2px;">'
                  .anchor('admin/alists_generic/view/'.CENUM_LISTTYPE_GIFTPAYTYPE, 'Payment Types').'
               </li>
            </ul>
         </li>');

      // Groups
   echoT('
         <li style="margin-top: 8px;"><b>Groups</b>
            <ul>
               <li style="margin-top: 2px;">'
                  .anchor('groups/groups_view/view/'.CENUM_CONTEXT_BIZ, 'Business Groups').'
               </li>');
   echoT('
               <li style="margin-top: 2px;">'
                  .anchor('groups/groups_view/view/'.CENUM_CONTEXT_CLIENT, 'Client Groups').'
               </li>');
   echoT('
               <li style="margin-top: 2px;">'
                  .anchor('groups/groups_view/view/'.CENUM_CONTEXT_PEOPLE, 'People Groups').'
               </li>
               <li style="margin-top: 2px;">'
                  .anchor('groups/groups_view/view/'.CENUM_CONTEXT_SPONSORSHIP, 'Sponsor Groups').'
               </li>
               <li style="margin-top: 2px;">'
                  .anchor('groups/groups_view/view/'.CENUM_CONTEXT_STAFF, 'Staff Groups').'
               </li>
               <li style="margin-top: 2px;">'
                  .anchor('groups/groups_view/view/'.CENUM_CONTEXT_STAFF_TS_LOCATIONS, 'Staff Timesheets: Locations').'
               </li>
               <li style="margin-top: 2px;">'
                  .anchor('groups/groups_view/view/'.CENUM_CONTEXT_STAFF_TS_PROJECTS, 'Staff Timesheets: Projects').'
               </li>
               <li style="margin-top: 2px;">'
                  .anchor('groups/groups_view/view/'.CENUM_CONTEXT_USER, 'User Permissions Groups').'
               </li>
               <li style="margin-top: 2px;">'
                  .anchor('groups/groups_view/view/'.CENUM_CONTEXT_VOLUNTEER, 'Volunteer Groups').'
               </li>
            </ul>
         </li>');

      // miscellaneous
   echoT('
         <li style="margin-top: 8px;"><b>Miscellaneous</b>
            <ul>
               <li style="margin-top: 2px;">'
                  .anchor('admin/alists_generic/view/'.CENUM_LISTTYPE_ATTRIB, '"Attributed To" List').'
               </li>
            </ul>
         </li>');

      // People
   echoT('
         <li style="margin-top: 8px;"><b>People</b>
            <ul>
               <li style="margin-top: 2px;">'
                  .anchor('admin/admin_special_lists/people_lists/relTypesView', 'Relationship Types').'
               </li>
            </ul>
         </li>');

      // Sponsorship
   echoT('
         <li style="margin-top: 8px;"><b>Sponsorship</b>
            <ul>
               <li style="margin-top: 2px;">'
                  .anchor('admin/admin_special_lists/sponsorship_lists/sponProgView', 'Sponsorship Programs').'
               </li>
               <li style="margin-top: 2px;">'
                  .anchor('admin/alists_generic/view/'.CENUM_LISTTYPE_SPONTERMCAT, 'Sponsorship Terminations').'
               </li>
            </ul>
         </li>');

      // Volunteers
   echoT('
         <li style="margin-top: 8px;"><b>Volunteers</b>
            <ul>
               <li style="margin-top: 2px;">'
                  .anchor('admin/alists_generic/view/'.CENUM_LISTTYPE_VOLACT, 'Activities').'
               </li>
            </ul>
            <ul>
               <li style="margin-top: 2px;">'
                  .anchor('admin/alists_generic/view/'.CENUM_LISTTYPE_VOLJOBCAT, 'Job Categories').'
               </li>
            </ul>

            <ul>
               <li style="margin-top: 2px;">'
                  .anchor('admin/alists_generic/view/'.CENUM_LISTTYPE_VOLSKILLS, 
                          htmlspecialchars($gclsChapterVoc->vocJobSkills), 'id="listJobSkills"').'
               </li>
            </ul>
            
            <ul>
               <li style="margin-top: 2px;">'
                  .anchor('admin/alists_generic/view/'.CENUM_LISTTYPE_VOLJOBCODES, 'Shift Job Codes').'
               </li>
            </ul>
         </li>
      </ul>');      
      
      // Images and Documents
   $strSpacer = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
   echoT('
         <li style="margin-top: 8px;"><b>Images and Documents</b>
            <ul style="margin-top: 0px;">
               <li style="margin-top: 0px;"><b>Images Tags</b>
                  <ul>
                     <li style="margin-top: 2px;">'
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_IMG_AUCTION,        'Auctions').$strSpacer
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_IMG_AUCTIONPACKAGE, 'Auction Packages').$strSpacer
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_IMG_AUCTIONITEM,    'Auction Items')
                   .'</li>
                     
                     <li style="margin-top: 2px;">'
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_IMG_BIZ,          'Businesses / Organizations')
                   .'</li>
                     
                     <li style="margin-top: 2px;">'
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_IMG_CLIENT,          'Clients').$strSpacer
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_IMG_CLIENTLOCATION,  'Client Locations').$strSpacer
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_IMG_SPONSOR,         'Sponsors').$strSpacer
                   .'</li>
                     <li style="margin-top: 2px;">'
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_IMG_ORGANIZATION, 'Your Organization').$strSpacer
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_IMG_STAFF,        'Staff')
                   .'</li>
                     <li style="margin-top: 2px;">'
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_IMG_GRANTPROVIDER, 'Funders/Providers').$strSpacer
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_IMG_GRANTS,        'Grants')
                   .'</li>
                     <li style="margin-top: 2px;">'
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_IMG_PEOPLE,       'People').$strSpacer
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_IMG_VOLUNTEER,    'Volunteers')
                   .'</li>
                     <li style="margin-top: 2px;">'
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_IMG_INVITEM,      'Inventory Items')
                   .'</li>
                  </ul>
               </li>');
               
   echoT('
               <li style="margin-top: 8px;"><b>Document Tags</b>
                  <ul>
                     <li style="margin-top: 2px;">'
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_DOC_AUCTION,        'Auctions').$strSpacer
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_DOC_AUCTIONPACKAGE, 'Auction Packages').$strSpacer
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_DOC_AUCTIONITEM,    'Auction Items')
                   .'</li>
                     <li style="margin-top: 2px;">'
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_DOC_BIZ,            'Businesses / Organizations')
                   .'</li>
                     <li style="margin-top: 2px;">'
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_DOC_CLIENT,          'Clients').$strSpacer
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_DOC_CLIENTLOCATION,  'Client Locations').$strSpacer
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_DOC_SPONSOR,         'Sponsors').$strSpacer
                   .'</li>
                     <li style="margin-top: 2px;">'
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_DOC_ORGANIZATION, 'Your Organization').$strSpacer
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_DOC_STAFF,        'Staff')
                   .'</li>
                     <li style="margin-top: 2px;">'
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_DOC_GRANTPROVIDER, 'Funders/Providers').$strSpacer
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_DOC_GRANTS,        'Grants')
                   .'</li>
                     <li style="margin-top: 2px;">'
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_DOC_PEOPLE,       'People').$strSpacer
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_DOC_VOLUNTEER,    'Volunteers')
                   .'</li>
                     <li style="margin-top: 2px;">'
                        .anchor('admin/admin_imgdoc_tags/viewTags/'.CENUM_CONTEXT_DOC_INVITEM,      'Inventory Items')
                   .'</li>
                  </ul>
               </li>
            </ul>
         </li>');
      
      