<?php
   $clsDateTime = new dl_date_time;

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '130pt';
   $clsRpt->openReport();


   openBlock('Consolidated People Record', '');
   echoT('<table class="enpView" border="0">');
   showPeopleInfo($clsDateTime, $clsRpt, $people, $dupIDs);
   echoT('</table>');

   closeBlock();         


   function showPeopleInfo($clsDateTime, $clsRpt, $people, $dupIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      global $gbDateFormatUS, $genumDateFormat, $glclsDTDateFormat;
      
         // People ID
      $lPeopleID = $people->lKeyID;
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('People ID:')
         .$clsRpt->writeCell(strLinkView_PeopleRecord($lPeopleID, 'View people record', true).'&nbsp;'
                     .str_pad($lPeopleID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow());
   
         // Name
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Name:')
         .$clsRpt->writeCell($people->strSafeNameLF)
         .$clsRpt->closeRow());
   
         // Address
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Address:')
         .$clsRpt->writeCell($people->strAddress)
         .$clsRpt->closeRow());
                  
         // Phone
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Phone:')
         .$clsRpt->writeCell(htmlspecialchars(strPhoneCell($people->strPhone, $people->strCell)))
         .$clsRpt->closeRow  ());
         
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Consolidated With:')
         .$clsRpt->writeCell('People IDs: '.implode(', ', $dupIDs))
         .$clsRpt->closeRow  ());
   }













