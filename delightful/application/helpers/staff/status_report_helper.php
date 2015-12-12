<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2014 Database Austin
  
   author: John Zimmerman 
  
   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
-----------------------------------------------------------------------------
      $this->load->helper('staff/status_report');
-----------------------------------------------------------------------------*/

   function strStatReviewTable($lSRptID, &$srpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $genumDateFormat;
      
      if (!$srpt->bPublished) return('<i>n/a</i>');
      
      $bReviewedByUser = false;
      
      $strOut = '';
      if ($srpt->lNumReviews == 0){
         $strOut .= '<i>No reviews for this weekly report.</i><br>';
      }else {
      
         foreach ($srpt->reviewLog as $review){
            $lReviewID   = $review->lKeyID;
            $lReviewerID = $review->lReviewerID;
            $bIAmTheMan = $glUserID==$lReviewerID;
            if ($bIAmTheMan) $bReviewedByUser = true;
            if ($review->bReviewed){
               $strOut .= 
                  strLinkView_StaffReviewedRpt($lSRptID, $lReviewID, 'View review', true).'&nbsp;'
                 .'reviewed by '.$review->strReviewerSafeName.' on '.date('l, '.$genumDateFormat, $review->dteReviewed)
                 .'<br>';
            }else {
               if ($bIAmTheMan){
                  $strOut .=
                     strLinkEdit_StaffStatReview($lSRptID, $lReviewID, 'Edit the review draft', true).'&nbsp;';
               }
               $strOut .= 
                  'Draft saved by '.$review->strReviewerSafeName.' on '.date('l, '.$genumDateFormat, $review->dteLastUpdate)
                 .'<br>';
            }
         }
         $strOut .= 
                  strLinkView_StaffReviewedRpt($lSRptID, -1, 'View all reviews', true).'&nbsp;'
                 .strLinkView_StaffReviewedRpt($lSRptID, -1, 'View all reviews', false)
                 .'<br>';
         
      }
      
      if (!$bReviewedByUser && bAllowAccess('management')){
         $strOut .= '<span style="background-color: #e1def6; color: red;">';
         $strOut .= strLinkAdd_StaffStatReview($lSRptID, 'Add a review to weekly report', true).'&nbsp;'
                   .strLinkAdd_StaffStatReview($lSRptID, 'Add a review to weekly report', false, ' style="font-weight: bold; color: #5c48f4;" ')
                   .'</span><br>'."\n";
                   
      }
      return($strOut);
   }

   function strStaffRptUpcomingFundRequest($srpt, $opts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      if ($srpt->lNumEntries > 0){
         foreach ($srpt->entries as $entry){
            $lEntryID = $entry->lKeyID;
            if ($opts->bShowLinks){
               $strLinks = strLinkEdit_StaffStatEntry($lEntryID, $opts->lStatRptID, $opts->enumSRType, 'Edit entry', true)
                   .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                   .strLinkRem_StaffStatEntry($lEntryID, $opts->lStatRptID, $opts->enumSRType, 'Remove entry', true, true);
            }else {
               $strLinks = '';
            }
            $strOut .= $opts->strDivEntryID.'entryID: '.str_pad($lEntryID, 5, '0', STR_PAD_LEFT)
                 .$strLinks.'</div>'
                 .$opts->strDivEntry
                 .'<u><b>Fund Request</b></u><br>'
                 .nl2br(htmlspecialchars($entry->strText01)).'<br><br>
                 <u><b>Est. Amount:</b></u>&nbsp;&nbsp;'
                 .number_format($entry->curEstAmnt, 2).'</div><br>';
         }
      }else {
         $strOut .= '<i>You currently have no entries under <b>"'.$srpt->strLabel1.'"</b>.</i><br><br>';
      }
      return($strOut);
   }
   
   function strStaffRptConcernsIssues($srpt, $opts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      if ($srpt->lNumEntries > 0){
         foreach ($srpt->entries as $entry){
            $lEntryID = $entry->lKeyID;
            if ($opts->bShowLinks){
               $strLinks = strLinkEdit_StaffStatEntry($lEntryID, $opts->lStatRptID, $opts->enumSRType, 'Edit entry', true)
                   .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                   .strLinkRem_StaffStatEntry($lEntryID, $opts->lStatRptID, $opts->enumSRType, 'Remove entry', true, true);
            }else {
               $strLinks = '';
            }
            $strOut .= $opts->strDivEntryID.'entryID: '.str_pad($lEntryID, 5, '0', STR_PAD_LEFT)
                 .$strLinks.'</div>'
                 .$opts->strDivEntry
                 .'<u><b>Concern / Issue</b></u><br>'
                 .nl2br(htmlspecialchars($entry->strText01)).'<br><br>
                 <u><b>Urgency</b></u><br>'
                 .nl2br(htmlspecialchars($entry->strUrgency)).'</div><br>';
         }
      }else {
         $strOut .= '<i>You currently have no entries under <b>"'.$srpt->strLabel1.'"</b>.</i><br><br>';
      }
      return($strOut);
   }
   
   function strStaffRptProjects($srpt, $opts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      if ($srpt->lNumEntries > 0){
         foreach ($srpt->entries as $entry){
            $lEntryID = $entry->lKeyID;
            if ($opts->bShowLinks){
               $strLinks = strLinkEdit_StaffStatEntry(
                           $lEntryID, $opts->lStatRptID, $opts->enumSRType, 'Edit entry', true)
                   .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                   .strLinkRem_StaffStatEntry($lEntryID, $opts->lStatRptID, $opts->enumSRType, 'Remove entry', true, true);
            }else {
               $strLinks = '';
            }
            $lEntryID = $entry->lKeyID;
            $strOut .= $opts->strDivEntryID.'entryID: '.str_pad($lEntryID, 5, '0', STR_PAD_LEFT)
                 .$strLinks.'</div>'
                 .$opts->strDivEntry
                 .'<u><b>Project</b></u><br>'
                 .nl2br(htmlspecialchars($entry->strText01)).'<br><br>
                 <u><b>Status</b></u><br>'
                 .nl2br(htmlspecialchars($entry->strText02)).'</div><br>';
         }
      }else {
         $strOut .= '<i>You currently have no entries under <b>"'.$srpt->strLabel1.'"</b>.</i><br><br>';
      }
      return($strOut);
   }
   
   function strStaffRptSingleEntries($srpt, $opts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      if ($srpt->lNumEntries > 0){
         foreach ($srpt->entries as $entry){
            $lEntryID = $entry->lKeyID;
            if ($opts->bShowLinks){
               $strLinks = strLinkEdit_StaffStatEntry($lEntryID, $opts->lStatRptID, $opts->enumSRType, 'Edit entry', true)
                   .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                   .strLinkRem_StaffStatEntry($lEntryID, $opts->lStatRptID, $opts->enumSRType, 'Remove entry', true, true);
            }else {
               $strLinks = '';
            }
            $strOut .= $opts->strDivEntryID.'entryID: '.str_pad($lEntryID, 5, '0', STR_PAD_LEFT)
                 .$strLinks.'</div>'
                 .$opts->strDivEntry
                 .'<u><b>'.$opts->strLabel.'</b></u><br>'
                 .nl2br(htmlspecialchars($entry->strText01)).'</div><br>';
         }
      }else {
         $strOut .= '<i>You currently have no entries under <b>"'.$srpt->strLabel1.'"</b>.</i><br><br>';
      }
      return($strOut);
   }   

   function displayStatusReport(&$crpt, &$sreport, $strDivEntry='', $strDivEntryID='', $bShowLinks=false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $bPublished = $sreport->bPublished;
      $lRptID     = $sreport->lKeyID;
      
      if ($strDivEntry.''   == '') $strDivEntry   = '<div>';
      if ($strDivEntryID.'' == '') $strDivEntryID = '<div>';

      if ($bPublished){
         $strLinkEdit = '';
         $strStatus = 'Published on '.date($genumDateFormat.' H:i:s', $sreport->dteSubmitDate);
      }else {
         $strStatus = 'Draft <i>(last updated on '
                 .date($genumDateFormat.' H:i:s', $sreport->dteLastUpdate).')</i>';
         if ($bShowLinks) {
            $strLinkEdit =
                strLinkEdit_StaffRpt($lRptID, 'Edit status report', true)
               .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
               .strLinkRem_StaffRpt($lRptID, 'Delete report', true, true);

         }else {
            $strLinkEdit = '';
         }
      }
      openBlock('Weekly Report', $strLinkEdit);

      echoT(
          $crpt->openReport('650pt'));

      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Weekly Report ID:')
         .$crpt->writeCell (str_pad($lRptID, 5, '0', STR_PAD_LEFT))
         .$crpt->closeRow  ());

      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('For:')
         .$crpt->writeCell (htmlspecialchars($sreport->strRptFName.' '.$sreport->strRptLName))
         .$crpt->closeRow  ());

      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Status:')
         .$crpt->writeCell ($strStatus)
         .$crpt->closeRow  ());


         //---------------------------
         // current projects
         //---------------------------
      $opts = new stdClass;
      $opts->lStatRptID    = $lRptID;
      $opts->bShowLinks    = false;
      $opts->strDivEntry   = $strDivEntry;
      $opts->strDivEntryID = $strDivEntryID;
      $opts->enumSRType    = CENUM_STATCAT_CURRENTPROJECTS;
      $srpt = &$sreport->sections[CENUM_STATCAT_CURRENTPROJECTS];
      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Current Projects:', '', 'width: 110pt; padding-top: 7px', 1, 1, 'nowrap')
         .$crpt->writeCell (strStaffRptProjects($srpt, $opts))
         .$crpt->closeRow  ());

         //---------------------------
         // current activities
         //---------------------------
      $opts->enumSRType    = CENUM_STATCAT_CURRENTACTIVITIES;
      $opts->strLabel      = 'Current Activity';
      $srpt = &$sreport->sections[CENUM_STATCAT_CURRENTACTIVITIES];
      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Current Activities:', '', 'padding-top: 7px;')
         .$crpt->writeCell (strStaffRptSingleEntries($srpt, $opts))
         .$crpt->closeRow  ());

         //---------------------------
         // Upcoming Events
         //---------------------------
      $opts->enumSRType    = CENUM_STATCAT_UPCOMINGEVENTS;
      $opts->strLabel      = 'Upcoming Event';
      $srpt = &$sreport->sections[CENUM_STATCAT_UPCOMINGEVENTS];
      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Upcoming Events:', '', 'padding-top: 7px;')
         .$crpt->writeCell (strStaffRptSingleEntries($srpt, $opts))
         .$crpt->closeRow  ());

         //---------------------------
         // Upcoming Funding Requests
         //---------------------------
      $opts->enumSRType    = CENUM_STATCAT_UPCOMINGFUNDRQST;
      $opts->strLabel      = 'Upcoming Funding Request';
      $srpt = &$sreport->sections[CENUM_STATCAT_UPCOMINGFUNDRQST];
      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Upcoming Funding Requests:')
         .$crpt->writeCell (strStaffRptUpcomingFundRequest($srpt, $opts))
         .$crpt->closeRow  ());

         //---------------------------
         // Concerns / Issues
         //---------------------------
      $opts->enumSRType    = CENUM_STATCAT_CONCERNSISSUES;
      $opts->strLabel      = 'Concern / Issue';
      $srpt = &$sreport->sections[CENUM_STATCAT_CONCERNSISSUES];
      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Concerns / Issues:', '', 'padding-top: 7px;')
         .$crpt->writeCell (strStaffRptConcernsIssues($srpt, $opts))
         .$crpt->closeRow  ());

      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Last Updated:')
         .$crpt->writeCell (date($genumDateFormat.' H:i:s', $sreport->dteLastUpdate)
               .' by '.htmlspecialchars($sreport->strULFName.' '.$sreport->strULLName))
         .$crpt->closeRow  ());

      echoT($crpt->closeReport());

      closeBlock();
   }

