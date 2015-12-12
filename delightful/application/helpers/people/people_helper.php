<?php
/*
      $this->load->helper('people/people');
*/

   function initPeopleReportDisplay(&$displayData){
      $displayData['showFields'] = new stdClass;
      $displayData['showFields']->bPeopleID        = true;
      $displayData['showFields']->bRemPeople       = bAllowAccess('editPeopleBizVol');
      $displayData['showFields']->bName            = true;
      $displayData['showFields']->bAddress         = true;
      $displayData['showFields']->bPhoneEmail      = true;
      $displayData['showFields']->bGiftSummary     = bAllowAccess('showFinancials');
      $displayData['showFields']->bSponsor         = bAllowAccess('showSponsors');
      $displayData['showFields']->bImportID        = false;
      $displayData['showFields']->deleteReturnPath = null;
      $displayData['showFields']->lReturnPathID    = null;
   }
   
   function initHouseholdReportDisplay(&$displayData){
      $displayData['showFields'] = new stdClass;
      $displayData['showFields']->bHouseholdID      = true;
//      $displayData['showFields']->bRemPeople        = true;
      $displayData['showFields']->bHouseholdName    = true;
      $displayData['showFields']->bHouseholdMembers = true;
      $displayData['showFields']->bAddress          = true;
      $displayData['showFields']->bRelationships    = true;
      $displayData['showFields']->bPhoneEmail       = true;
      $displayData['showFields']->bImportID         = false;
      $displayData['showFields']->deleteReturnPath  = null;
      $displayData['showFields']->lReturnPathID     = null;
   }   
   
   
   