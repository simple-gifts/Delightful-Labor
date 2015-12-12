<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class mgr_preview extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEditReview($lReviewID, $lSRptID){
   //------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------
      global $glUserID, $gstrSafeName;
      
/*----------------------------
echo(__FILE__.' '.__LINE__.'<br>'."\n"); $this->output->enable_profiler(TRUE);
//-----------------------------*/
   
      $this->load->helper('dl_util/permissions');    // in autoload
      if (!bAllowAccess('management')) return('');
   
      $displayData = array();
      $displayData['js'] = '';
      
      $displayData['lReviewID'] = $lReviewID = (int) $lReviewID;
      $displayData['lSRptID']   = $lSRptID   = (int) $lSRptID;
      
      $displayData['bNewReview'] = $bNewReview = $lReviewID <= 0;
      
         //-------------------------------------
         // models, libraries, and helpers
         //-------------------------------------
      $this->load->model('staff/mstaff_status', 'cstat');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper('staff/status_report');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();
      
         // load the status report
      $this->cstat->loadStatusReportViaRptID($lSRptID);
      $displayData['sreport'] = $sreport = &$this->cstat->sreports[0];
      
         // load the review
      $this->cstat->loadReviewsViaReviewID($lReviewID, $lNumReviews, $reviewLog);
      $displayData['mgrReview'] = $mgrReview = &$reviewLog[0];
      
      if ($bNewReview){
         $mgrReview->lKeyID              = $lReviewID;
         $mgrReview->lStatusID           = $lSRptID;
         $mgrReview->bReviewed           = false;
         $mgrReview->strMgrNotes         = '';
         $mgrReview->strPublicNotes      = '';
         $mgrReview->lReviewerID         = $glUserID;
         $mgrReview->lOriginID           = $glUserID;
         $mgrReview->strReviewerSafeName = $gstrSafeName;      
      }
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$mgrReview   <pre>');
echo(htmlspecialchars( print_r($mgrReview, true))); echo('</pre></font><br>');
// ------------------------------------- */
   
         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtPrivate',   'Private Review', 'trim');
		$this->form_validation->set_rules('txtPublic',    'Public Review',  'trim');
		$this->form_validation->set_rules('chkPublished', 'Published?',     'trim');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');
         
         $displayData['dteReviewed'] = $mgrReview->dteReviewed;
         $displayData['dteReviewed'] = $mgrReview->bReviewed;

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['formData']->txtPrivate   = htmlspecialchars($mgrReview->strMgrNotes.'');
            $displayData['formData']->txtPublic    = htmlspecialchars($mgrReview->strPublicNotes.'');
            $displayData['formData']->bPublished   = $mgrReview->bReviewed;
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtPrivate   = set_value('txtPrivate');
            $displayData['formData']->txtPublic    = set_value('txtPrivate');
            $displayData['formData']->bPublished   = set_value('chkPublished')=='true';
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      =
                                       anchor('aayhf/main/aayhfMenu', 'AAYHF', 'class="breadcrumb"')
                                .' | '.anchor('staff/mgr_performance/review', 'Status Report Review', 'class="breadcrumb"')
                                .' | '.anchor('staff/mgr_performance/mgrViewLog/'.$sreport->lUserID, 'Status Reports for '.$sreport->strRptSafeName, 'class="breadcrumb"')
                                .' | '.($bNewReview ? 'Add New' : 'Edit').'  Status Review';

         $displayData['title']          = CS_PROGNAME.' | Status Report';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'aayhf/aayhf_staff/mgr_review_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $mgrReview->strMgrNotes    = trim($_POST['txtPrivate']);
         $mgrReview->strPublicNotes = trim($_POST['txtPublic']);
         $mgrReview->bReviewed      = $bReviewed = trim(@$_POST['chkPublished']) == 'true';

            //------------------------------------
            // update db tables and return
            //------------------------------------
         $this->session->set_flashdata('msg', 'Your status report review was '
                .($bReviewed ? '<b>published</b>' : 'saved as a draft').'.');
         if ($bNewReview){
            $lReviewID = $this->cstat->lAddNewStatusReview($mgrReview);
         }else {
            $this->cstat->updateStatusReview($lReviewID, $mgrReview);
         }
         redirect('staff/mgr_performance/mgrViewLog/'.$sreport->lUserID);
      }
   }
   
}




