<?php

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '120pt';

   showProvider($clsRpt, $provider, $lProviderID);
   showGrants($clsRpt, $provider, $lProviderID);

   showImageInfo            (CENUM_CONTEXT_GRANTPROVIDER, $lProviderID, 'Provider Images',
                             $images, $lNumImages, $lNumImagesTot);
   showDocumentInfo         (CENUM_CONTEXT_GRANTPROVIDER, $lProviderID, 'Provider Documents',
                             $docs, $lNumDocs, $lNumDocsTot);

   function showProvider($clsRpt, $provider, $lProviderID){
   //--------------------------------------------------
   // provider section
   //--------------------------------------------------
      openBlock('Funding Provider',
                 strLinkEdit_GrantProvider($lProviderID, 'Edit Provider Record', true).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                .strLinkRem_Provider($lProviderID, 'Remove Provider', true, true)
                 );

      echoT(
          $clsRpt->openReport()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Funder/Provider ID:')
         .$clsRpt->writeCell(str_pad($lProviderID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Name:')
         .$clsRpt->writeCell (htmlspecialchars($provider->strGrantOrg))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Address:')
         .$clsRpt->writeCell ($provider->strAddress)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Phone:')
         .$clsRpt->writeCell (htmlspecialchars($provider->strPhone))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Cell:')
         .$clsRpt->writeCell (htmlspecialchars($provider->strCell))
         .$clsRpt->closeRow  ());

      if ($provider->strEmail == ''){
         $strOut = '&nbsp;';
      }else {
         $strOut = mailto($provider->strEmail, htmlspecialchars($provider->strEmail));
      }

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Email:')
         .$clsRpt->writeCell ($strOut)
         .$clsRpt->closeRow  ());

      if ($provider->strWebSite == ''){
         $strOut = '&nbsp;';
      }else {
         if (!strtoupper(substr($provider->strWebSite, 0, 4))=='HTTP'){
            $provider->strWebSite = 'http://'.$provider->strWebSite;
         }
         $strOut = '<a target="_blank" href="'.prep_url($provider->strWebSite).'">'.htmlspecialchars($provider->strWebSite).'</a>';
      }
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Web:')
         .$clsRpt->writeCell ($strOut)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Notes:')
         .$clsRpt->writeCell (nl2br(htmlspecialchars($provider->strNotes)))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Attributed to:')
         .$clsRpt->writeCell (htmlspecialchars($provider->strAttributedTo))
         .$clsRpt->closeRow  ());

      echoT($clsRpt->closeReport());
      closeBlock();
   }

   function showGrants($clsRpt, $provider, $lProviderID){
   //--------------------------------------------------
   // grants section
   //--------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth      = 1200;
      $attributes->lUnderscoreWidth = 400;
      $attributes->divID            = 'grantDiv';
      $attributes->divImageID       = 'grantDivImg';
      $attributes->bStartOpen       = true;
      $attributes->bAddTopBreak     = true;

      openBlock('Grants <span style="font-size: 9pt;">('.$provider->lNumGrants.')</span>',
                 strLinkAdd_Grant($lProviderID, 'Add new grant', true).'&nbsp;'
                .strLinkAdd_Grant($lProviderID, 'Add new grant', false),
                $attributes);
      if ($provider->lNumGrants > 0){
         echoT('<table class="enpView" >');
            echoT(
              '<tr>
                  <td class="enpRptLabel">
                     grantID
                  </td>
                  <td class="enpRptLabel">
                     Name
                  </td>
                  <td class="enpRptLabel">
                     ACO
                  </td>
                  <td class="enpRptLabel">
                     Notes
                  </td>
               </tr>');
         foreach ($provider->grants as $grant){
            $lGrantID = $grant->lGrantID;
            echoT(
              '<tr class="makeStripe">
                  <td class="enpRpt" style="text-align: center;">'
                     .str_pad($lGrantID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                     .strLinkView_Grant($lGrantID, 'View grant record', true).'
                  </td>
                  <td class="enpRpt" style="width: 180pt;">'
                     .htmlspecialchars($grant->strGrantName).'
                  </td>
                  <td class="enpRpt" style="text-align: center;">'
                     .$grant->strFlagImg.'
                  </td>
                  <td class="enpRpt" style="width: 260pt;">'
                     .nl2br(htmlspecialchars($grant->strNotes)).'
                  </td>
               </tr>');
         }


         echoT('</table>');
      }
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

