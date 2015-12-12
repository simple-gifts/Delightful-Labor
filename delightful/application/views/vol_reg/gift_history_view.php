<?php

   if ($lTotGifts == 0){
      echoT('<b><i>No donations were found for '.$pRec->strSafeName.'</i></b><br><br>');
      return;
   }

   openVolGiftHistory($pRec);
   
   writeGiftHistory($lNumGiftsGH, $giftHistory);
   
   closeVolGiftHistory();

   function writeGiftHistory(&$lNumGiftsGH, &$giftHistory){
   //---------------------------------------------------------------------
   //
   //--------------------------------------------------------------------- 
      global $genumDateFormat;

   
      $idx = 0;
      foreach ($lNumGiftsGH as $lNumGifts){
         if ($lNumGifts > 0){         
            foreach ($giftHistory[$idx] as $gh){

               $strNotes = '';
               if ($gh->bHon)    $strNotes .= '/ Honorarium ';
               if ($gh->bMem)    $strNotes .= '/ Memorial ';
               if ($gh->gi_bGIK) $strNotes .= '/ In Kind ';
               if (!is_null($gh->gi_lSponsorID)) $strNotes .= '/ Sponsorship Payment ';
               if ($strNotes == ''){
                  $strNotes = '&nbsp;';
               }else {
                  $strNotes = substr($strNotes, 2);
               }
            
               echoT('
                     <tr class="makeStripe">
                        <td class="enpRpt" style="text-align: center;">'
                           .str_pad($gh->gi_lKeyID, 6, '0', STR_PAD_LEFT).'
                        </td>
                        <td class="enpRpt" style="text-align: right;">'
                           .$gh->strACOCurSymbol.'&nbsp;'.number_format($gh->gi_curAmnt, 2).'&nbsp;'.$gh->strACOFlag.'
                        </td>
                        <td class="enpRpt" style="text-align: center;">'
                           .date($genumDateFormat, $gh->gi_dteDonation).'
                        </td>
                        <td class="enpRpt">'
                           .$strNotes.'
                        </td>
                     </tr>');
            }
         }
         ++$idx;
      }
   }
   
   
   function openVolGiftHistory(&$pRec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      echoT('<br>
         <table class="enpRptC">
            <tr>
               <td colspan="10" class="enpRptTitle">
                  Donation History for '.$pRec->strSafeName.'
               </td>
            </tr>');
            
      echoT('
            <tr>
               <td class="enpRptLabel">
                  Donation ID
               </td>
               <td class="enpRptLabel">
                  Amount
               </td>
               <td class="enpRptLabel">
                  Date
               </td>
               <td class="enpRptLabel">
                  Notes
               </td>
            </tr>');
   }


   function closeVolGiftHistory(){
   //---------------------------------------------------------------------
   //
   //--------------------------------------------------------------------- 
      echoT('</table><br><br>');   
   }





