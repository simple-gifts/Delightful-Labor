<?php
   global $genumDateFormat;

   $strImportType = $logEntry->enumImportType;
   if ($strImportType==CENUM_CONTEXT_SPONSORPAY) $strImportType = 'Sponsor Payment';
   $strImportType = strtoupper(substr($strImportType, 0, 1)).substr($strImportType, 1);
   
   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   
   $clsRpt->strWidthLabel = '100pt';

   openBlock('Import Log', '');
   echoT($clsRpt->openReport());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Import ID:')
      .$clsRpt->writeCell (str_pad($logEntry->lKeyID, 5, '0', STR_PAD_LEFT))
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Import Type:')
      .$clsRpt->writeCell ($strImportType)
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Date of Import:')
      .$clsRpt->writeCell (date($genumDateFormat.' H:i:s', $logEntry->dteOrigin))
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Imported By:')
      .$clsRpt->writeCell ($logEntry->strUserCSafeName)
      .$clsRpt->closeRow  ());

   echoT($clsRpt->closeReport());

   closeBlock();

