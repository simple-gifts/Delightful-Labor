<?php
/*
      $this->load->helper('sponsors/sponsorship');
*/

   function initSponReportDisplay(&$displayData){
      $displayData['showFields'] = new stdClass;
      $displayData['showFields']->bSponID         = true;
      $displayData['showFields']->bForeignID      = true;
      $displayData['showFields']->bActiveInactive = true;
      $displayData['showFields']->bName           = true;
      $displayData['showFields']->bHonoree        = true;
      $displayData['showFields']->bAddress        = true;
      $displayData['showFields']->bPhoneEmail     = true;
      $displayData['showFields']->bProgram        = true;
      $displayData['showFields']->bClient         = true;
      $displayData['showFields']->bCommitment     = true;
   }
   
   
   
   