<?php
   $clsDateTime = new dl_date_time;

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '130pt';
   $clsRpt->openReport();


   openBlock('Consolidated Business Record', '');
   echoT('<table class="enpView" border="0">');
   showBizInfo($clsDateTime, $clsRpt, $biz, $dupIDs);
   echoT('</table>');

   closeBlock();         


   function showBizInfo($clsDateTime, $clsRpt, $biz, $dupIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      global $gbDateFormatUS, $genumDateFormat, $glclsDTDateFormat;
      
         // Biz ID
      $lBizID = $biz->lKeyID;
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Business ID:')
         .$clsRpt->writeCell(strLinkView_BizRecord($lBizID, 'View business record', true).'&nbsp;'
                     .str_pad($lBizID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow());
   
         // Name
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Name:')
         .$clsRpt->writeCell($biz->strSafeName)
         .$clsRpt->closeRow());
   
         // Address
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Address:')
         .$clsRpt->writeCell($biz->strAddress)
         .$clsRpt->closeRow());
                  
         // Phone
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Phone:')
         .$clsRpt->writeCell(htmlspecialchars(strPhoneCell($biz->strPhone, $biz->strCell)))
         .$clsRpt->closeRow  ());
         
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Consolidated With:')
         .$clsRpt->writeCell('Business IDs: '.implode(', ', $dupIDs))
         .$clsRpt->closeRow  ());
   }













