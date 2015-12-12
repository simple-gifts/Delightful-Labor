
This client's record can not be removed because it is associated with
the following sponsorships:

   <table>
<?php
   foreach ($sponsors as $sponsor){
      $lSponID = $sponsor->lKeyID;
      echoT('
          <tr>
             <td>'.strLinkView_Sponsorship($lSponID, 'View sponsorship record', true).' '
                  .str_pad($lSponID, 5, '0', STR_PAD_LEFT).'
             </td>
             <td>'.$sponsor->strSafeNameFL.'
             </td>
          </tr>');
             
   }
?>
   </table>