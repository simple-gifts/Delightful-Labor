<br>

<?php

/*
   echoT(form_open($search->formLink));
   
   if (isset($search->formHidden)){
      foreach ($search->formHidden as $key=>$value){
         echoT(form_hidden($key, $value));   
      }
   }
*/

   echoT('Searching for '.$search->enumSearchType.' that begin with <b><i>"'
           .htmlspecialchars($search->strSearchTerm).'"</b></i><br>');
           
   if ($search->lNumSearchResults==0) {
      echoT('<font color="red">There are no '.$search->strSearchLabel.' records that
             match your search criteria  <b><i>"'
            .htmlspecialchars($search->strSearchTerm).'"</b></i></font><br><br>');
   }else {
      echoT(
         $search->strDisplayTitle.'<br>
         <table class="enpRpt">');
              
           
      foreach ($search->searchResults as $clsFound){
         $lKeyID = $clsFound->lKeyID;
         if ($search->bShowKeyID){
            if ($search->bShowLink){
               $strRecLink = $search->strIDLabel.strLinkView_ViaRecType($search->enumSearchType, $lKeyID)
                            .' '.str_pad($lKeyID, 5, '0', STR_PAD_LEFT).'<br>';
            }else {
               $strRecLink = $search->strIDLabel.' '.str_pad($lKeyID, 5, '0', STR_PAD_LEFT).'<br>';
            }
         }else {
            $strRecLink = '';
         }
         echoT('
            <tr>');

         if ($search->bShowSelect){
            $strLinkSel = strLinkSearchSelect($search->formLink, $lKeyID, $search->strSelectLabel, true);
            echoT('
               <td class="enpRpt" style="text-align: center;">'
                  .$strLinkSel."\n
               </td>");
         }
         
         if ($search->bShowEnumSearchType){
            echoT('
               <td class="enpRpt">'
                  .$search->enumSearchType.'
               </td>');
         }
         
         echoT('
               <td class="enpRpt">'
                  .$strRecLink
                  .$clsFound->strResult.'
               </td>

            </tr>');
      }
      echoT('</table>');
   }
   
   



?>   