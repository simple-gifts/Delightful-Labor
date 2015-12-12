<?php
/*---------------------------------------------------------------------
// copyright (c) 2014 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model('staff/mstaff_status', 'cstat');
      $this->load->model('staff/mstaff_pdf',    'cstpdf');
---------------------------------------------------------------------*/

class mstaff_pdf extends mstaff_status{

   public
      $lNumSReports, $sreports,
      $sqlWhere, $sqlOrder,
      $sqlWhereSections, $sqlOrderSections,
      $sqlWhereReviews, $sqlOrderReviews;

	function __construct(){
		parent::__construct();

      $this->lNumSReports = $this->sreports = null;
      $this->sqlWhere = $this->sqlOrder = '';
      $this->sqlWhereSections = $this->sqlOrderSections = '';

      $this->sqlWhereReviews = $this->sqlOrderReviews = '';
	}

      //----------------------------------------------------
      //       P D F   G E N E R A T I O N
      //----------------------------------------------------
   function strStaffStatViaUserRptOverview($sRpt, $displayData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $clsRpt = new generic_rpt($params);
      $clsRpt->strWidthLabel = '120pt';
      $clsRpt->bValueEscapeHTML = false;


      $strOut .= strOpenBlock('Consolidated Status Reports', '');

      $strOut .=
          $clsRpt->openReport();

      $strOut .=
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Date Range:')
         .$clsRpt->writeCell ($sRpt->strDateRange)
         .$clsRpt->closeRow  ();

      $strOut .=
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('View:')
         .$clsRpt->writeCell (
                      strLink_PDF_StaffReports('byStaff', $sRpt->reportID, 'View PDF Report', true, ' target="_blank" ').'&nbsp;'
                     .strLink_PDF_StaffReports('byStaff', $sRpt->reportID, 'View PDF Report', false, ' target="_blank" '))
         .$clsRpt->closeRow  ();

      $strOut .= $clsRpt->closeReport();
      $strOut .= strCloseBlock();
      return($strOut);
   }

   function strStaffStatRptOverview($sRpt, $displayData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $clsRpt = new generic_rpt($params);
      $clsRpt->strWidthLabel = '120pt';
      $clsRpt->bValueEscapeHTML = false;

      $cgroup = new mgroups;
      $cgroup->loadGroupInfo($sRpt->lStaffGroup);

      $strOut .= strOpenBlock('Consolidated Status Reports', '');

      $strOut .=
          $clsRpt->openReport();

      $strOut .=
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Staff Group:')
         .$clsRpt->writeCell (htmlspecialchars($cgroup->groupTable[0]->gp_strGroupName))
         .$clsRpt->closeRow  ();

      $strOut .=
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Date Range:')
         .$clsRpt->writeCell ($sRpt->strDateRange)
         .$clsRpt->closeRow  ();

      $strOut .=
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Sort:')
         .$clsRpt->writeCell (($sRpt->strSort == 'staff' ? 'Staff Member' : 'Report Date'))
         .$clsRpt->closeRow  ();

      $strOut .=
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('View:')
         .$clsRpt->writeCell (
                      strLink_PDF_StaffReports('byProj', $sRpt->reportID, 'View PDF Report', true, ' target="_blank" ').'&nbsp;'
                     .strLink_PDF_StaffReports('byProj', $sRpt->reportID, 'View PDF Report', false, ' target="_blank" '))
         .$clsRpt->closeRow  ();

      $strOut .= $clsRpt->closeReport();
      $strOut .= strCloseBlock();
      return($strOut);
   }

   function createStaffRptViaUserIDPDF($sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gpdf_Title, $gpdf_DateRange, $gpdf_PageWidth, $gpdf_PageHeight,
         $gpdf_MarginLR, $gpdf_MarginIndent, $gpdf_MarginTop;

         // some constants
      $gpdf_PageWidth  = 72 * 8.5;
      $gpdf_PageHeight = 72 * 11;
      $gpdf_MarginLR   = 60;
      $gpdf_MarginTop  = 40;
      $gpdf_MarginIndent = $gpdf_MarginLR + 20;

      $gpdf_DateRange = $sRpt->strDateRange;

      $pdf = new staffPDF('P', 'pt', 'Letter');
      $pdf->SetMargins($gpdf_MarginLR, $gpdf_MarginLR, $gpdf_MarginTop);

      $users = new muser_accts;

      $strUserIDs = implode(', ', $sRpt->userIDs);
      $users->sqlWhere = " AND us_lKeyID IN ($strUserIDs) ";
      $users->loadUserRecords();
      foreach ($users->userRec as $urec){
         $lUserID = $urec->us_lKeyID;

            // load the reports
         $this->sqlWhere = ' AND ss_bPublished '
                  ." AND ss_dteSubmitDate $sRpt->strBetween "
                  ." AND ss_lUserID = $lUserID ";
                  
         $this->loadStatusReports();
         if ($this->lNumSReports == 0){
            $pdf->AddPage();
            $pdf->SetFont('Times','I',14);
            $pdf->Ln(10);
            $pdf->Cell($gpdf_MarginLR,10,'There are no status reports for '.$urec->us_strFirstName.' '.$urec->us_strLastName);
         }else {
            foreach ($this->sreports as $statRpt){
               $lRptID = $statRpt->lKeyID;
               $this->loadReviewsViaRptID($lRptID, $statRpt->lNumReviews, $statRpt->reviewLog);

               $pdf->AddPage();
               $pdf->writeNamePublishedDate(
                          $statRpt->strRptFName.' '.$statRpt->strRptLName,
                          date('l, F jS, Y', $statRpt->dteSubmitDate));
               $pdf->writeCurrentProjects ($statRpt->sections[CENUM_STATCAT_CURRENTPROJECTS]);
               $pdf->writeSingleSection   ($statRpt->sections[CENUM_STATCAT_CURRENTACTIVITIES], 'Activities',      'Activity');
               $pdf->writeSingleSection   ($statRpt->sections[CENUM_STATCAT_UPCOMINGEVENTS],    'Upcoming Events', 'Event');
               $pdf->writeUpcomingFndRqst ($statRpt->sections[CENUM_STATCAT_UPCOMINGFUNDRQST]);
               $pdf->writeConcernsIssues  ($statRpt->sections[CENUM_STATCAT_CONCERNSISSUES]);

               $pdf->writeManagementReview($statRpt->lNumReviews, $statRpt->reviewLog);
            }
         }
      }
      $pdf->Output();
   }

   function createStaffRptPDF($sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gpdf_Title, $gpdf_DateRange, $gpdf_PageWidth, $gpdf_PageHeight,
         $gpdf_MarginLR, $gpdf_MarginIndent, $gpdf_MarginTop;

         // some constants
      $gpdf_PageWidth  = 72 * 8.5;
      $gpdf_PageHeight = 72 * 11;
      $gpdf_MarginLR   = 60;
      $gpdf_MarginTop  = 40;
      $gpdf_MarginIndent = $gpdf_MarginLR + 20;

      $gpdf_DateRange = $sRpt->strDateRange;

      $pdf = new staffPDF('P', 'pt', 'Letter');
      $pdf->SetMargins($gpdf_MarginLR, $gpdf_MarginLR, $gpdf_MarginTop);

         // load the group members
      $cgrp = new mgroups;
      foreach ($sRpt->lStaffGroup as $lGroupID){

            // load the group title
         $cgrp->loadGroupInfo($lGroupID);
         $gpdf_Title = $cgrp->groupTable[0]->gp_strGroupName;

         $cgrp->loadGroupMembership(CENUM_CONTEXT_STAFF, $lGroupID);
         if ($cgrp->lCntMembersInGroup == 0){
            $strUsersIn = ' -1 ';  // force and EOF
         }else {
            $lUsers = array();
            foreach ($cgrp->groupMembers as $gmem){
               $lUsers[] = $gmem->lKeyID;
            }
            $strUsersIn = implode(',', $lUsers);
         }


            // load the reports
         $this->sqlWhere = ' AND ss_bPublished '
                  ." AND ss_dteSubmitDate $sRpt->strBetween "
                  ." AND ss_lUserID IN ($strUsersIn) ";

         if ($sRpt->strSort == 'date'){
            $this->sqlOrder = ' ss_dteSubmitDate, rpt.us_strLastName, rpt.us_strFirstName ';
         }else {
            $this->sqlOrder = ' rpt.us_strLastName, rpt.us_strFirstName, ss_dteSubmitDate ';
         }
         $this->loadStatusReports();

         if ($this->lNumSReports == 0){
            $pdf->AddPage();
            $pdf->SetFont('Times','I',14);
            $pdf->Ln(10);
            $pdf->Cell($gpdf_MarginLR,10,'There are no weekly reports that meet your search criteria.');
//            $pdf->Output();
//            return;
         }else {

            foreach ($this->sreports as $statRpt){
               $lRptID = $statRpt->lKeyID;
               $this->loadReviewsViaRptID($lRptID, $statRpt->lNumReviews, $statRpt->reviewLog);

               $pdf->AddPage();
               $pdf->writeNamePublishedDate(
                          $statRpt->strRptFName.' '.$statRpt->strRptLName,
                          date('l, F jS, Y', $statRpt->dteSubmitDate));
               $pdf->writeCurrentProjects ($statRpt->sections[CENUM_STATCAT_CURRENTPROJECTS]);
               $pdf->writeSingleSection   ($statRpt->sections[CENUM_STATCAT_CURRENTACTIVITIES], 'Activities',      'Activity');
               $pdf->writeSingleSection   ($statRpt->sections[CENUM_STATCAT_UPCOMINGEVENTS],    'Upcoming Events', 'Event');
               $pdf->writeUpcomingFndRqst ($statRpt->sections[CENUM_STATCAT_UPCOMINGFUNDRQST]);
               $pdf->writeConcernsIssues  ($statRpt->sections[CENUM_STATCAT_CONCERNSISSUES]);

               $pdf->writeManagementReview($statRpt->lNumReviews, $statRpt->reviewLog);
            }
         }
      }
      $pdf->Output();
   }
}

require_once('./application/libraries/fpdf/fpdf.php');

class staffPDF extends FPDF {

   function writeManagementReview($lNumReviews, $reviewLog){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gpdf_Title, $gpdf_DateRange, $gpdf_PageWidth, $gpdf_PageHeight,
         $gpdf_MarginLR, $gpdf_MarginIndent, $gpdf_MarginTop;

      $this->SetY($this->GetY()+14);
      $this->SetFont('Times', 'BU', 12);
      $this->Cell(0, 15, 'Management Reviews:', 0, 1);

      if ($lNumReviews == 0){
         $this->SetFont('Times', 'I', 11);
         $this->SetX($gpdf_MarginIndent);
         $this->Cell(0, 15, 'No management reviews are available for this status report.', 0, 1);
         return;
      }

      $this->SetFillColor(250, 250, 245);
      foreach ($reviewLog as $review){
         $this->SetX($gpdf_MarginIndent);
         $this->SetFont('Times', 'BU', 11);
         $this->Cell(0, 15, 'Reviewed by '
                  .$review->strUCFName.' '.$review->strUCLName.' on '.date('m/d/Y', $review->dteReviewed),
                  0, 1);

         if ($review->strPublicNotes != ''){
            $this->SetX($gpdf_MarginIndent);
            $this->SetFont('Times', '', 11);
            $this->MultiCell(0, 11.4, $review->strPublicNotes, 0, 'L', true);
         }
         $this->SetY($this->GetY()+10);
      }
   }

   function writeConcernsIssues($issues){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gpdf_Title, $gpdf_DateRange, $gpdf_PageWidth, $gpdf_PageHeight,
         $gpdf_MarginLR, $gpdf_MarginIndent, $gpdf_MarginTop;

      $this->SetY($this->GetY()+14);
      $this->SetFont('Times', 'BU', 12);
      $this->Cell(0, 15, 'Issues / Concerns:', 0, 1);

      $lNumEntries = $issues->lNumEntries;
      if ($lNumEntries == 0){
         $this->SetFont('Times', 'I', 11);
         $this->SetX($gpdf_MarginIndent);
         $this->Cell(0, 15, 'No issues/concerns are reported in this status report.', 0, 1);
         return;
      }

      $idx = 0;
      foreach ($issues->entries as $entry){
         $this->SetX($gpdf_MarginIndent);
         $this->SetFont('Times', 'BU', 11);
         $this->Cell(0, 15, 'Issue / Concern', 0, 1);

         $this->SetX($gpdf_MarginIndent);
         $this->SetFont('Times', '', 11);
         $this->MultiCell(0, 11.4, $entry->strText01, 0);

         $this->SetFont('Times', 'BU', 11);
         $this->SetXY($gpdf_MarginIndent, $this->GetY()+10);
         $this->Cell(0, 15, 'Urgency', 0, 1);

         $this->SetX($gpdf_MarginIndent);
         $this->SetFont('Times', '', 11);
         $this->MultiCell(0, 11.4, $entry->strUrgency, 0);

         ++$idx;
         if ($idx < $lNumEntries){
            $y = $this->GetY() + 10;
            $this->SetLineWidth(0.5);
            $this->Line($gpdf_MarginIndent, $y, $gpdf_MarginIndent+200, $y);
            $this->SetY($y+12);
         }
      }
   }

   function writeUpcomingFndRqst($fundRqsts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gpdf_Title, $gpdf_DateRange, $gpdf_PageWidth, $gpdf_PageHeight,
         $gpdf_MarginLR, $gpdf_MarginIndent, $gpdf_MarginTop;

      $this->SetY($this->GetY()+14);
      $this->SetFont('Times', 'BU', 12);
      $this->Cell(0, 15, 'Upcoming Funding Requests:', 0, 1);

      $lNumEntries = $fundRqsts->lNumEntries;
      if ($lNumEntries == 0){
         $this->SetFont('Times', 'I', 11);
         $this->SetX($gpdf_MarginIndent);
         $this->Cell(0, 15, 'No upcoming funding requests are reported in this status report.', 0, 1);
         return;
      }

      $idx = 0;
      foreach ($fundRqsts->entries as $entry){
         $this->SetX($gpdf_MarginIndent);
         $this->SetFont('Times', 'BU', 11);
         $this->Cell(0, 15, 'Funding Request', 0, 1);

         $this->SetX($gpdf_MarginIndent);
         $this->SetFont('Times', '', 11);
         $this->MultiCell(0, 11.4, $entry->strText01, 0);

         $this->SetFont('Times', 'BU', 11);
         $this->SetXY($gpdf_MarginIndent, $this->GetY()+10);
         $this->Cell(0, 15, 'Amount: ', 0, 0);
         $this->SetFont('Times', '', 11);
         $this->SetX($gpdf_MarginIndent+60);
         $this->Cell(0, 15, '$ '.number_format($entry->curEstAmnt, 2), 0, 1);

         ++$idx;
         if ($idx < $lNumEntries){
            $y = $this->GetY() + 10;
            $this->SetLineWidth(0.5);
            $this->Line($gpdf_MarginIndent, $y, $gpdf_MarginIndent+200, $y);
            $this->SetY($y+12);
         }
      }
   }

   function writeSingleSection($section, $strLabel1, $strLabel2){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gpdf_Title, $gpdf_DateRange, $gpdf_PageWidth, $gpdf_PageHeight,
         $gpdf_MarginLR, $gpdf_MarginIndent, $gpdf_MarginTop;

      $this->SetY($this->GetY()+14);
      $this->SetFont('Times', 'BU', 12);
      $this->Cell(0, 15, $strLabel1.':', 0, 1);

      $lNumEntries = $section->lNumEntries;
      if ($lNumEntries == 0){
         $this->SetFont('Times', 'I', 11);
         $this->SetX($gpdf_MarginIndent);
         $this->Cell(0, 15, 'No '.strtolower($strLabel1).' are reported in this status report.', 0, 1);
         return;
      }

      foreach ($section->entries as $statText){
         $this->SetX($gpdf_MarginIndent);
         $this->SetFont('Times', 'BU', 11);
         $this->Cell(0, 15, $strLabel2, 0, 1);

         $this->SetX($gpdf_MarginIndent);
         $this->SetFont('Times', '', 11);
         $this->MultiCell(0, 11.4, $statText->strText01, 0);
      }
   }

   function writeCurrentProjects($projects){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gpdf_Title, $gpdf_DateRange, $gpdf_PageWidth, $gpdf_PageHeight,
         $gpdf_MarginLR, $gpdf_MarginIndent, $gpdf_MarginTop;

      $this->SetFont('Times', 'BU', 12);
      $this->Cell(0, 15, 'Current Projects:', 0, 1);

      $lNumEntries = $projects->lNumEntries;
      if ($lNumEntries == 0){
         $this->SetFont('Times', 'I', 11);
         $this->SetX($gpdf_MarginIndent);
         $this->Cell(0, 15, 'No current projects are reported in this status report.', 0, 1);
         return;
      }

      $idx = 0;
      foreach ($projects->entries as $entry){
         $this->SetX($gpdf_MarginIndent);
         $this->SetFont('Times', 'BU', 11);
         $this->Cell(0, 15, 'Project', 0, 1);

         $this->SetX($gpdf_MarginIndent);
         $this->SetFont('Times', '', 11);
         $this->MultiCell(0, 11.4, $entry->strText01, 0);

         $this->SetFont('Times', 'BU', 11);
         $this->SetXY($gpdf_MarginIndent, $this->GetY()+10);
         $this->Cell(0, 15, 'Status', 0, 1);

         $this->SetX($gpdf_MarginIndent);
         $this->SetFont('Times', '', 11);
         $this->MultiCell(0, 11.4, $entry->strText02, 0);

         ++$idx;
         if ($idx < $lNumEntries){
            $y = $this->GetY() + 10;
            $this->SetLineWidth(0.5);
            $this->Line($gpdf_MarginIndent, $y, $gpdf_MarginIndent+200, $y);
            $this->SetY($y+12);
         }
      }
   }

   function writeNamePublishedDate($strName, $strDate){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gpdf_Title, $gpdf_DateRange, $gpdf_PageWidth, $gpdf_PageHeight,
         $gpdf_MarginLR, $gpdf_MarginTop;

//      $this->SetY($gpdf_MarginTop);
      $this->SetFont('Times','U', 14);
      $strOut = $strName.'      '.$strDate;
      $this->Cell(0, 28, $strOut, 0, 1, 'L', false);

   }

   function Header(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gpdf_Title, $gpdf_DateRange, $gpdf_PageWidth, $gpdf_PageHeight,
         $gpdf_MarginLR, $gpdf_MarginTop;

          // Times bold 15

          // Calculate width of title and position
      $w = 0;

         // Title
      $this->SetY($gpdf_MarginTop);
      $this->SetFont('Times','B', 18);
      $this->Cell($w, 20, $gpdf_Title, 0, 1, 'C', false);

         // Date Range
      $this->SetFont('Times','', 13);
      $this->Cell($w, 15, $gpdf_DateRange, 0, 1, 'C', false);

      $this->SetFont('Times','', 11);
      $this->Cell($w, 13, 'AAYHF Status Reports', 0, 1, 'C', false);

      $this->SetLineWidth(0.8);
      $this->SetDrawColor(0, 0, 0);
      $y = $this->GetY()+3;
      $this->Line($gpdf_MarginLR, $y, $gpdf_PageWidth-$gpdf_MarginLR, $y);

      $this->SetY($y+20);

          // Line break
       //$this->Ln(10);
   }

   function Footer() {
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
          // Position at 1.5 cm from bottom
       $this->SetY(-55);

          // Arial italic 8
       $this->SetFont('Arial','I',8);

          // Text color in gray
       $this->SetTextColor(128);

          // Page number
       $this->Cell(0, 10, 'Page '.$this->PageNo(),0,0,'C');
   }

}
