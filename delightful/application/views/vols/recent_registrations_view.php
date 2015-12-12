<?php
   global $genumDateFormat;
   
   if ($lNumVols == 0){
      echoT('<br>
          <span style="font-size: 12pt;"><i>There are no volunteers who have registered under <b>'
         .htmlspecialchars($rRec->strFormName).'</b>.</i></span><br><br>');
      return;
   }

   echoT('<br>
      <table class="enpRptC">
         <tr>
            <td class="enpRptTitle" colspan="10">
               Recent Registrations:<br>'
               .htmlspecialchars($rRec->strFormName).'
            </td>
         </tr>');

   echoT('
      <tr>
         <td class="enpRptLabel">
            &nbsp;
         </td>
         <td class="enpRptLabel">
            peopleID
         </td>
         <td class="enpRptLabel">
            volID
         </td>
         <td class="enpRptLabel">
            Name
         </td>
         <td class="enpRptLabel">
            Address
         </td>
         <td class="enpRptLabel">
            Registration Date
         </td>
         <td class="enpRptLabel">
            Vol. Registration Records
         </td>
      </tr>');

   $idx = 1;
//   $lNumUTables = count($utables);
   foreach ($vols as $vol){
      $lPeopleID = $vol->lPeopleID;
      $lVolID = $vol->lKeyID;

      if ($lNumUTables == 0){
         $strUTabLinks = '<i>(no personalized tables)</i>';
      }else {
         $strUTabLinks =
             '<ul style="margin-top: 0px; margin-bottom: 0px;">';
         foreach ($utables as $ut){   // hook 'em
            $strUTabLinks .=
                 '<li style="margin-left: -14pt;">'
                   .strLinkView_UFMFRecordsViaFID(CENUM_CONTEXT_VOLUNTEER, $ut->lTableID, $lVolID, 'View record', true).'&nbsp;'
                   .htmlspecialchars($ut->strUserTableName)
                .'</li>';
         }
         $strUTabLinks .= '</ul>';
      }

      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="text-align: center;">'
               .number_format($idx).'
            </td>
            <td class="enpRpt" style="text-align: center;">'
               .str_pad($lPeopleID, 6, '0', STR_PAD_LEFT).'&nbsp;'
               .strLinkView_PeopleRecord($lPeopleID, 'View people record', true).'
            </td>
            <td class="enpRpt" style="text-align: center;">'
               .str_pad($lVolID, 5, '0', STR_PAD_LEFT).'&nbsp;'
               .strLinkView_Volunteer($lVolID, 'View volunteer record', true).'
            </td>
            <td class="enpRpt">'
               .$vol->strSafeNameLF.'
            </td>
            <td class="enpRpt">'
               .$vol->strAddress.'<br>'
               .$vol->strEmailFormatted.'<br>
               phone: '.htmlspecialchars($vol->strPhone).'<br>
               cell: '.htmlspecialchars($vol->strCell).'
            </td>
            <td class="enpRpt">'
               .date($genumDateFormat.' H:i:s', $vol->dteOrigin).'
            </td>
            <td class="enpRpt">'
               .$strUTabLinks.'
            </td>
         </tr>');
      ++$idx;
   }

   echoT('</table>');

