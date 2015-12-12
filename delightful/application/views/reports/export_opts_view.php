<?php

// '<ul style="list-style-type: square; display: inline; margin-left: 0; padding-left: 0;">';

   $strLH = '18pt';
   standardExports      ($strLH);
   exportUserTables     ($strLH, $lNumTabsTot, $pTabs);
   exportVolRegistration($strLH, $lNumVolRegForms, $volRegForms);
   exportImageDocs      (true, $strLH);
   exportImageDocs      (false, $strLH);


   function standardExports($strLH){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('
         Click on the <img src="'.base_url().'images/misc/exportSmall.png"> icon to export the table:<br>
         <ul style="list-style-type: square;">');

      if (bAllowAccess('showPeople')){
         echoT('
            <li style="line-height:'.$strLH.'">
               '.strLinkExport_Table(CENUM_CONTEXT_PEOPLE, 'Export people table', true ).'
               <b>People</b> table
            </li>

            <li style="line-height:'.$strLH.'">
               '.strLinkExport_Table(CENUM_CONTEXT_BIZ, 'Export business table', true ).'
               <b>Businesses/Organizations</b> table
            </li>

            <li style="line-height:'.$strLH.'">
               '.strLinkExport_Table(CENUM_CONTEXT_BIZCONTACT, 'Export business contacts', true ).'
               <b>Businesses Contacts</b> table
            </li>');
      }

      if (bAllowAccess('showFinancials')){
         echoT('
            <li style="line-height:'.$strLH.'"><b>Donations</b><br>
               <ul>
                  <li style="line-height:'.$strLH.'">
                     '.strLinkExport_Table(CENUM_CONTEXT_GIFT, 'Export donations', true ).'
                     <b>Donations</b> table
                  </li>
                  <li style="line-height:'.$strLH.'">
                     '.strLinkExport_Table(CENUM_CONTEXT_GIFTHON, 'Export Honorariums', true ).'
                     <b>Honorariums</b> table
                  </li>
                  <li style="line-height:'.$strLH.'">
                     '.strLinkExport_Table(CENUM_CONTEXT_GIFTMEM, 'Export memorials', true ).'
                     <b>Memorials</b> table
                  </li>
               </ul>
            </li>');
      }

      if (bAllowAccess('showSponsors')){
         echoT('
            <li style="line-height:'.$strLH.'"><b>Sponsorship</b><br>
               <ul>
                  <li style="line-height:'.$strLH.'">
                     '.strLinkExport_Table(CENUM_CONTEXT_SPONSORSHIP, 'Export sponsorships', true ).'
                     <b>Sponsorships</b> table
                  </li>');
         if (bAllowAccess('showClients')){
            echoT('
                  <li style="line-height:'.$strLH.'">
                     '.strLinkExport_Table(CENUM_CONTEXT_SPONSORPACKET, 'Export sponsorship packets', true ).'
                     <b>Sponsorship Packets</b>
                  </li>');
         }
         echoT('
               </ul>
            </li>');
      }

      if (bAllowAccess('showClients')){
         echoT('
            <li style="line-height:'.$strLH.'">
               '.strLinkExport_Table(CENUM_CONTEXT_CLIENT, 'Export clients', true ).'
               <b>Clients</b> table
            </li>');
      }
      echoT('
         </ul>');
   }

   function exportUserTables($strLH, $lNumTabsTot, &$pTabs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('
         <ul style="list-style-type: square;">

            <li style="line-height:'.$strLH.'">
               <b>Personalized Tables</b>
               <ul>');

      if ($lNumTabsTot == 0){
         echoT('<li><i>There are no personalized tables defined in your database</i></li>');
      }else {
         $enumGroupTType = 'zzz';
         foreach ($pTabs as $pTab){
            $enumTType = $pTab->enumTType;

            if ($enumGroupTType != $enumTType){
               if (bAllowAccess('showUTable', $enumTType)){
                  if ($enumGroupTType != 'zzz'){
                     echoT('</ul>'."\n");
                  }
                  $enumGroupType = $enumTType;

                  echoT('<li style="line-height:'.$strLH.'"><b>'.$pTab->strTTypeLabel.'</b></li><ul>'."\n");
                  if ($pTab->lNumTables==0){
                     echoT('<li><i>No personalized tables</i></li>');
                  }else {
                     foreach ($pTab->userTables as $ut){
                        echoT('
                           <li style="line-height:'.$strLH.'">
                              '.strLinkExport_UTable($ut->lKeyID, 'Export personalized table', true ).'&nbsp;'
                              .htmlspecialchars($ut->strUserTableName).'
                           </li>');
                     }
                  }
                  echoT('</ul>');
               }
            }
         }
      }
      echoT('</li></ul></li></ul>');
   }

   function exportImageDocs($bImage, $strLH){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strLabel = ($bImage ? 'images' : 'documents');

      echoT('
         <ul style="list-style-type: square;">

            <li style="line-height:'.$strLH.'">
               <b>'.($bImage ? 'Images' : 'Documents').'</b>
               <ul>');
      if (bAllowAccess('showImagesDocs', CENUM_CONTEXT_BIZ)){
         echoT('
                  <li style="line-height:'.$strLH.'">
                     '.strLinkExport_ImgDoc(CENUM_CONTEXT_BIZ, $bImage, 'Business '.$strLabel, true ).'
                     <b>Business</b> '.$strLabel.'
                  </li>');
      }
      if (bAllowAccess('showImagesDocs', CENUM_CONTEXT_CLIENT)){
         echoT('
                  <li style="line-height:'.$strLH.'">
                     '.strLinkExport_ImgDoc(CENUM_CONTEXT_CLIENT, $bImage, 'Client '.$strLabel, true ).'
                     <b>Client</b> '.$strLabel.'
                  </li>');
      }
      if (bAllowAccess('showImagesDocs', CENUM_CONTEXT_LOCATION)){
         echoT('
                  <li style="line-height:'.$strLH.'">
                     '.strLinkExport_ImgDoc(CENUM_CONTEXT_LOCATION, $bImage, 'Client location '.$strLabel, true ).'
                     <b>Client Location</b> '.$strLabel.'
                  </li>');
      }
      if (bAllowAccess('showImagesDocs', CENUM_CONTEXT_PEOPLE)){
         echoT('
                  <li style="line-height:'.$strLH.'">
                     '.strLinkExport_ImgDoc(CENUM_CONTEXT_PEOPLE, $bImage, 'People '.$strLabel, true ).'
                     <b>People</b> '.$strLabel.'
                  </li>');
      }
      if (bAllowAccess('showImagesDocs', CENUM_CONTEXT_SPONSORSHIP)){
         echoT('

                  <li style="line-height:'.$strLH.'">
                     '.strLinkExport_ImgDoc(CENUM_CONTEXT_SPONSORSHIP, $bImage, 'Sponsorship '.$strLabel, true ).'
                     <b>Sponsor</b> '.$strLabel.'
                  </li>');
      }
      if (bAllowAccess('showImagesDocs', CENUM_CONTEXT_VOLUNTEER)){
         echoT('

                  <li style="line-height:'.$strLH.'">
                     '.strLinkExport_ImgDoc(CENUM_CONTEXT_VOLUNTEER, $bImage, 'Volunteer '.$strLabel, true ).'
                     <b>Volunteer</b> '.$strLabel.'
                  </li>');
      }
      echoT('
               </ul>
            </li>
         </ul>');
   }

   function exportVolRegistration($strLH, $lNumVolRegForms, $volRegForms){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$volRegForms   <pre>');
echo(htmlspecialchars( print_r($volRegForms, true))); echo('</pre></font><br>');
// ------------------------------------- */
   
      if (!bAllowAccess('editPeopleBizVol')) return;

      echoT('
         <ul style="list-style-type: square;">

            <li style="line-height:'.$strLH.'">
               <b>Volunteer Registrations</b>
               <ul>');

      if ($lNumVolRegForms == 0){
         echoT('<li><i>There are volunteer registration forms defined in your database</i></li>');
      }else {
         foreach ($volRegForms as $vrForm){
            echoT('<li style="line-height:'.$strLH.'">'
               .strLinkExport_VolReg($vrForm->lKeyID, 'Export volunteer registration', true ).'&nbsp;'
                        .htmlspecialchars($vrForm->strFormName)
               .'</li>'."\n");
         }
      }

      echoT('
               </ul>
            </li>
         </ul>');
   }



