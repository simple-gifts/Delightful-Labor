<?php
/*
      $this->load->helper('biz/biz');
*/

   function initBizReportDisplay(&$displayData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      $displayData['showFields'] = new stdClass;
      $displayData['showFields']->bBizID           = true;
      $displayData['showFields']->bRemBiz          = true;
      $displayData['showFields']->bName            = true;
      $displayData['showFields']->bAddress         = true;
      $displayData['showFields']->bPhoneEmail      = true;
      $displayData['showFields']->bGiftSummary     = bAllowAccess('showGiftHistory');
      $displayData['showFields']->bSponsor         = bAllowAccess('showSponsors');
      $displayData['showFields']->bContacts        = true;
      $displayData['showFields']->bContactNames    = false;
      $displayData['showFields']->bImportID        = false;
      $displayData['showFields']->deleteReturnPath = null;
      $displayData['showFields']->lReturnPathID    = null;
   }