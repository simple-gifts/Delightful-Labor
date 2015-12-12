<?php
   global $genumDateFormat;

   
   $params = array('enumStyle' => 'entrySummary');
   $clsRpt = new generic_rpt($params);
   $clsRpt->setEntrySummary();
  
   echoT(
      $clsRpt->openReport()

      .$clsRpt->openRow()
      .$clsRpt->writeLabel('Group:', 70, '', 1, 1, '')
      .$clsRpt->writeCell(htmlspecialchars($strGroupName), 120, '', 1, 1, '')
      .$clsRpt->closeRow()

      .$clsRpt->openRow()
      .$clsRpt->writeLabel('Group type:')
      .$clsRpt->writeCell(htmlspecialchars($enumGroupType))
      .$clsRpt->closeRow()

      .$clsRpt->openRow()
      .$clsRpt->writeLabel('Notes:')
      .$clsRpt->writeCell(nl2br(htmlspecialchars($strGroupNotes)))
      .$clsRpt->closeRow()

      .$clsRpt->openRow()
      .$clsRpt->writeLabel('Group expiration:')
      .$clsRpt->writeCell(date($genumDateFormat, $dteExpire))
      .$clsRpt->closeRow()

      .$clsRpt->closeReport());   