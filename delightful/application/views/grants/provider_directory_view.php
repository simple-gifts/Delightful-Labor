<?php

   echoT('<br>'.strLinkAdd_GrantProvider('Add new grant provider', true).'&nbsp;'
               .strLinkAdd_GrantProvider('Add new grant provider', false).'<br><br>');

   if ($lNumProviders == 0){
      echoT('<i>There are currently no grant providers in your database.</i><br><br>');
      return;
   }

   openGrantProviderTable();

   foreach ($providers as $provider){
      showGrantProviderRow($provider);
   }

   closeGrantProviderTable();


   function showGrantProviderRow($provider){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lProviderID = $provider->lKeyID;
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$provider   <pre>');
echo(htmlspecialchars( print_r($provider, true))); echo('</pre></font><br>');
// ------------------------------------- */

      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="text-align: center;">'
               .str_pad($lProviderID, 5, '0', STR_PAD_LEFT).'&nbsp;'
               .strLinkView_GrantProvider($lProviderID, 'View provider record', true).'
            </td>
            <td class="enpRpt" style="width: 200pt;"><b>'
               .htmlspecialchars($provider->strGrantOrg).'</b><br>'
               .$provider->strAddress.'<br>'
               .strPhoneWebEmail($provider).'
            </td>
            <td class="enpRpt" style="width: 200pt;">'
                .strLinkAdd_Grant($lProviderID, 'Add new grant', true).'&nbsp;'
                .strLinkAdd_Grant($lProviderID, 'Add new grant', false).'<br>');
      if ($provider->lNumGrants == 0){
         echoT('<i>(none)</i>');
      }else {
         echoT('<ul style="margin-top: 6px;">');
         foreach ($provider->grants as $grant){
            $lGrantID = $grant->lGrantID;
            echoT('<li style="margin-left: -8pt;">'
                     .str_pad($lGrantID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                     .strLinkView_Grant($lGrantID, 'View grant record', true).'&nbsp;'
                     .$grant->strFlagImg.'&nbsp;'
                     .htmlspecialchars($grant->strGrantName).'</li>'."\n");
         }
         echoT('</ul>');
      }

      echoT('
            </td>
            <td class="enpRpt" style="width: 240pt;">'
               .nl2br(htmlspecialchars($provider->strNotes)).'
            </td>
         </tr>');

   }

   function strPhoneWebEmail($provider){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut =
         '<table cellpadding="0" cellspacing="0" border="0" style="margin: 0px; padding: 0px; width: 100%">';

         // phone
      $strOut .=
         '<tr>
             <td style="padding-left: 0px; margin-left: 0px; width: 50pt;">
                phone:
             </td>
             <td>'
                .htmlspecialchars($provider->strPhone).'
             </td>
          </tr>';

         // cell
      $strOut .=
         '<tr>
             <td style="padding-left: 0px; margin-left: 0px;">
                cell:
             </td>
             <td>'
                .htmlspecialchars($provider->strCell).'
             </td>
          </tr>';

         // email
      $strOut .=
         '<tr>
             <td style="padding-left: 0px; margin-left: 0px;">
                email:
             </td>
             <td>';
      if ($provider->strEmail == ''){
         $strOut .= '&nbsp;';
      }else {
         $strOut .= mailto($provider->strEmail, htmlspecialchars($provider->strEmail));
      }
      $strOut .= '
                </td>
             </tr>';

         // web
      $strOut .=
         '<tr>
             <td style="padding-left: 0px; margin-left: 0px;">
                web:
             </td>
             <td>';
      if ($provider->strWebSite == ''){
         $strOut .= '&nbsp;';
      }else {
         if (!strtoupper(substr($provider->strWebSite, 0, 4))=='HTTP'){
            $provider->strWebSite = 'http://'.$provider->strWebSite;
         }
         $strOut .= '<a target="_blank" href="'.prep_url($provider->strWebSite).'">'.htmlspecialchars($provider->strWebSite).'</a>';
      }
      $strOut .= '
                </td>
             </tr>';

      $strOut .= '</table>';
      return($strOut);
   }

   function openGrantProviderTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('
         <table class="enpRptC">
            <tr>
               <td class="enpRptTitle" colspan=5>
                  Grant Providers
               </td>
            </tr>');
      echoT('
         <tr>
            <td class="enpRptLabel">
               providerID
            </td>
            <td class="enpRptLabel">
               Name
            </td>
            <td class="enpRptLabel">
               Grants
            </td>
            <td class="enpRptLabel">
               Notes
            </td>
         </tr>');
   }

   function closeGrantProviderTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('</table><br>');
   }

