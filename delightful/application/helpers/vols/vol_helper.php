<?php
/*
      $this->load->helper('vols/vol');
*/

   function initVolReportDisplay(&$displayData){
      $displayData['showFields'] = new stdClass;
      $displayData['showFields']->bVolID          = true;
      $displayData['showFields']->bPeopleID       = true;
      $displayData['showFields']->bActiveInactive = true;
      $displayData['showFields']->bName           = true;
      $displayData['showFields']->bAddress        = true;
      $displayData['showFields']->bPhoneEmail     = true;
      $displayData['showFields']->bSchedule       = true;
      $displayData['showFields']->bSkills         = false;
   }


