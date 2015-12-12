<?php
   $clsDateTime = new dl_date_time;

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '130pt';
   $clsRpt->openReport();


   openBlock('Good Client Record', '');
   echoT('<table class="enpView" border="0">');
   showClientInfo($clsDateTime, $clsRpt, $goodClient[0]);
   echoT('</table>');

   closeBlock();         

   openBlock('Duplicate Client Records', '');
   foreach ($dupClients as $client){
      echoT('<table class="enpView">');
      showClientInfo($clsDateTime, $clsRpt, $client);   
      echoT('</table>');
   }
   closeBlock();      
   
   openBlock('Action', '');
   echoT(anchor('clients/client_dups/consolidate/'.$reportID, 'CONSOLIDATE DUPLICATE RECORDS').'<br><br>');
      
   echoT(anchor('clients/client_dups/opts', 'CANCEL').' - Don\'t consolidate.<br><br>');
   closeBlock();      
   
   
   
   echoT($clsRpt->closeReport());
   

   function showClientInfo($clsDateTime, $clsRpt, $client){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      global $gbDateFormatUS, $genumDateFormat, $glclsDTDateFormat;
      
         // Client ID
      $lClientID = $client->lKeyID;
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Client ID:')
         .$clsRpt->writeCell(strLinkView_ClientRecord($lClientID, 'View client record', true).'&nbsp;'
                     .str_pad($lClientID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow());
   
         // Name
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Name:')
         .$clsRpt->writeCell($client->strSafeName)
         .$clsRpt->closeRow());
   
         // Address
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Address:')
         .$clsRpt->writeCell($client->strAddress)
         .$clsRpt->closeRow());
         
         // birthday
      $mdteBirth = $client->dteBirth;
      $clsDateTime->setDateViaMySQL(0, $mdteBirth);
      $strAgeBDay = $clsDateTime->strPeopleAge(0, $mdteBirth, $lAgeYears, $glclsDTDateFormat);
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Birth Date:')
         .$clsRpt->writeCell ($strAgeBDay)
         .$clsRpt->closeRow  ());
         
         // Phone
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Phone:')
         .$clsRpt->writeCell(htmlspecialchars(strPhoneCell($client->strPhone, $client->strCell)))
         .$clsRpt->closeRow  ());
   }



