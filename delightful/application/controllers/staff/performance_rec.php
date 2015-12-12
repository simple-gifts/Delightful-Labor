<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class performance_rec extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewRec($lSReportID, $lReviewID=0){
   //------------------------------------------------------------------------
   // if $lReviewID == -1, load all reviews
   //------------------------------------------------------------------------
      global $glUserID, $gdteNow;
/*----------------------------
echo(__FILE__.' '.__LINE__.'<br>'."\n"); $this->output->enable_profiler(TRUE);
//----------------------------- */

      if (!bTestForURLHack('notVolunteer')) return;

      $displayData = array();
      $displayData['js']         = '';
      $displayData['lSReportID'] = $lSReportID = (integer)$lSReportID;
      $displayData['lReviewID']  = $lReviewID  = (integer)$lReviewID;
      $displayData['bAddReview'] = $bAddReview = $lReviewID != 0;
      
         //-------------------------------------
         // models, libraries, and helpers
         //-------------------------------------
      $this->load->model('staff/mstaff_status', 'cstat');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper ('staff/status_report');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('staff/link_staff');

         // load the status report
      $this->cstat->loadStatusReportViaRptID($lSReportID);
      $displayData['sreport'] = $sreport = &$this->cstat->sreports[0];
      
         // if tagged with a review, load the review
      if ($bAddReview){
         if ($lReviewID == -1){
            $this->cstat->loadReviewsViaRptID($lSReportID, $displayData['lNumReviews'], $displayData['reviews']);
         }else {
            $this->cstat->loadReviewsViaReviewID($lReviewID, $displayData['lNumReviews'], $displayData['reviews']);
         }
      }
      
      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();
      

      $bAsTheMan = false;
      if ($glUserID != $sreport->lUserID){
         if (!bAllowAccess('management'))return;
         $bAsTheMan = true;
      }  
      $displayData['bAsTheMan'] = $bAsTheMan;
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      if ($bAsTheMan){
         $displayData['pageTitle']      =
                                    anchor('aayhf/main/aayhfMenu', 'AAYHF', 'class="breadcrumb"')
                             .' | '.anchor('staff/mgr_performance/review', 'Status Report Review', 'class="breadcrumb"')
                             .' | '.anchor('staff/mgr_performance/mgrViewLog/'.$sreport->lUserID, 
                                           'Status Log for '.$sreport->strRptSafeName, 'class="breadcrumb"')
                             .' | Status Record';
      }else {
         $displayData['pageTitle']      =
                                    anchor('aayhf/main/aayhfMenu', 'AAYHF', 'class="breadcrumb"')
                             .' | '.anchor('staff/performance/log', 'Status Report Log', 'class="breadcrumb"')
                             .' | Status Record';
      }

      $displayData['title']          = CS_PROGNAME.' | Status Report';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'aayhf/aayhf_staff/status_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   
   
}   
