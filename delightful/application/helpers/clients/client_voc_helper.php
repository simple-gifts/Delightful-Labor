<?php

   function htmlVocList($bShowEditLink, $clsVocs, $bCenterTable=true){
   //---------------------------------------------------------------------
   // user must call $this->loadClientVocabulary prior to this call
   //---------------------------------------------------------------------
      $strVocTable = '
         <table class="enpRpt'.($bCenterTable ? 'C' : '').'">
            <tr>
               <td class="enpRptLabel" rowspan="2" style="vertical-align:bottom;">
                  Vocabulary ID
               </td>';
      if ($bShowEditLink){
         $strVocTable .= '
               <td class="enpRptLabel" rowspan="2" style="vertical-align:bottom;">
                  &nbsp;&nbsp;&nbsp;&nbsp;
               </td>';
      }

      $strVocTable .= '
               <td class="enpRptLabel" rowspan="2" style="vertical-align:bottom;">
                  Name
               </td>
               <td class="enpRptLabel" colspan="6" style="text-align: center">
                  Vocabulary
               </td>
            </tr>
            <tr>
               <td class="enpRptLabel">
                  Sponsor <font style="font-weight: normal;">(s)</font>
               </td>
               <td class="enpRptLabel">
                  Sponsor <font style="font-weight: normal;">(p)</font>
               </td>
               <td class="enpRptLabel">
                  Client <font style="font-weight: normal;">(s)</font>
               </td>
               <td class="enpRptLabel">
                  Client <font style="font-weight: normal;">(p)</font>
               </td>
               <td class="enpRptLabel">
                  Location <font style="font-weight: normal;">(s)</font>
               </td>
               <td class="enpRptLabel">
                  Location <font style="font-weight: normal;">(p)</font>
               </td>
            </tr>';

      foreach ($clsVocs as $clsVoc){
         if (!$clsVoc->bRetired){
            $lKeyID = $clsVoc->lKeyID;
            if ($clsVoc->bProtected  || !$bShowEditLink){
               $strLinkEdit = '';
               if ($bShowEditLink){
                  $strRemRow   = '<td class="enpRpt">'.strCantDelete('Default vocabulary can\'t be removed').'</td>';
               }else {
                  $strRemRow   = '';
               }
            }else {
               $strLinkEdit = strLinkEdit_ClientVoc($lKeyID, 'Edit client vocabulary', true);
               $strRemRow   = '<td class="enpRpt">'
                        .strLinkRem_ClientVoc($lKeyID, 'Remove client vocabulary', true, true)
                        .'</td>';
            }
            $strVocTable .= '
               <tr class="makeStripe">
                  <td class="enpRpt" style="text-align: center;">'
                     .$strLinkEdit.' '
                     .str_pad($lKeyID, 5, '0', STR_PAD_LEFT).'
                  </td>'
                 .$strRemRow
                 .'<td class="enpRpt" style="text-align: left;">'
                     .htmlspecialchars($clsVoc->strVocTitle).'
                  </td>

                  <td class="enpRpt">'
                     .htmlspecialchars($clsVoc->strSponsorS).'
                  </td>
                  <td class="enpRpt">'
                     .htmlspecialchars($clsVoc->strSponsorP).'
                  </td>
                  <td class="enpRpt">'
                     .htmlspecialchars($clsVoc->strClientS).'
                  </td>
                  <td class="enpRpt">'
                     .htmlspecialchars($clsVoc->strClientP).'
                  </td>
                  <td class="enpRpt">'
                     .htmlspecialchars($clsVoc->strLocS).'
                  </td>
                  <td class="enpRpt">'
                     .htmlspecialchars($clsVoc->strLocP).'
                  </td>
               </tr>';
         }
      }
      $strVocTable .= '</table>';
      return($strVocTable);
   }
?>