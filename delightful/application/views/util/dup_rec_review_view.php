<?php
   $clsDateTime = new dl_date_time;

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '130pt';
   $clsRpt->openReport();


   openBlock('Good '.$strLabel.' Record', '');
   echoT('<table class="enpView" border="0">');

      // the good ID
   switch ($enumContext){
      case CENUM_CONTEXT_CLIENT:
         showClientInfo($clsDateTime, $clsRpt, $goodClient[0]);
         break;

      case CENUM_CONTEXT_PEOPLE:
         showPeopleInfo($clsDateTime, $clsRpt, $goodPeople[0]);
         break;

      case CENUM_CONTEXT_BIZ:
         showBizInfo($clsDateTime, $clsRpt, $goodBiz[0]);
         break;

      default:
         screamForHelp($enumContext.': invalid context for duplicate record consolidation<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         break;
   }


   echoT('</table>');

   closeBlock();

   openBlock('Duplicate '.$strLabel.' Records', '');
   switch ($enumContext){
      case CENUM_CONTEXT_CLIENT:
         reportDupClientInfo($clsDateTime, $clsRpt, $dupClients);
         break;

      case CENUM_CONTEXT_PEOPLE:
         reportDupPeopleInfo($clsDateTime, $clsRpt, $dupPeople);
         break;

      case CENUM_CONTEXT_BIZ:
         reportDupBizInfo($clsDateTime, $clsRpt, $dupBiz);
         break;

      default:
         screamForHelp($enumContext.': invalid context for duplicate record consolidation<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         break;
   }
   closeBlock();

   openBlock('Action', '');
   echoT(anchor('util/dup_records/consolidate/'.$reportID, 'CONSOLIDATE DUPLICATE RECORDS').'<br><br>');

   echoT(anchor('util/dup_records/opts/'.$enumContext, 'CANCEL').' - Don\'t consolidate.<br><br>');
   closeBlock();

   echoT($clsRpt->closeReport());




   function reportDupBizInfo($clsDateTime, $clsRpt, $dupBiz){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      foreach ($dupBiz as $biz){
         echoT('<table class="enpView" style="margin-bottom: 16pt;">');
         showBizInfo($clsDateTime, $clsRpt, $biz);
         echoT('</table>');
      }
   }
   
   function showBizInfo($clsDateTime, $clsRpt, $biz){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lBID = $biz->lKeyID;
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Business ID:')
         .$clsRpt->writeCell(strLinkView_BizRecord($lBID, 'View business record', true).'&nbsp;'
                     .str_pad($lBID, 5, '0', STR_PAD_LEFT))
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

/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$biz   <pre>');
echo(htmlspecialchars( print_r($biz, true))); echo('</pre></font><br>');
// ------------------------------------- */   
   }

   

   function reportDupPeopleInfo($clsDateTime, $clsRpt, $dupPeople){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      foreach ($dupPeople as $person){
         echoT('<table class="enpView" style="margin-bottom: 16pt;">');
         showPeopleInfo($clsDateTime, $clsRpt, $person);
         echoT('</table>');
      }
   }

   function showPeopleInfo($clsDateTime, $clsRpt, $person){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lPID = $person->lKeyID;
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('People ID:')
         .$clsRpt->writeCell(strLinkView_PeopleRecord($lPID, 'View people record', true).'&nbsp;'
                     .str_pad($lPID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow());

         // Name
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Name:')
         .$clsRpt->writeCell($person->strSafeNameLF)
         .$clsRpt->closeRow());

         // Address
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Address:')
         .$clsRpt->writeCell($person->strAddress)
         .$clsRpt->closeRow());
         
         // Phone
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Phone:')
         .$clsRpt->writeCell(htmlspecialchars(strPhoneCell($person->strPhone, $person->strCell)))
         .$clsRpt->closeRow  ());        
   }


   function reportDupClientInfo($clsDateTime, $clsRpt, $dupClients){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      foreach ($dupClients as $client){
         echoT('<table class="enpView" style="border-bottom: 1px solid black; margin-bottom: 8pt;">');
         showClientInfo($clsDateTime, $clsRpt, $client);
         echoT('</table>');
      }
   }

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



