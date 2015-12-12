<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class performance_entry extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEditEntry($lSReportID, $lEntryID, $enumSRType){
   //------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------
      global $glUserID, $gdteNow;

      if (!bTestForURLHack('notVolunteer')) return;

      $displayData = array();
      $displayData['js']         = '';
      $displayData['lSReportID'] = $lSReportID = (integer)$lSReportID;
      $displayData['lEntryID']   = $lEntryID   = (integer)$lEntryID;
      $displayData['bNew']       = $bNew = $lEntryID <= 0;
      $displayData['formData']   = new stdClass;
      $displayData['enumSRType'] = $enumSRType;

         //-------------------------------------
         // models, libraries, and helpers
         //-------------------------------------
      $this->load->model('staff/mstaff_status', 'cstat');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper('staff/link_staff');

         // load the status report
      $this->cstat->loadStatusReportViaRptID($lSReportID, false);
      $displayData['sreport'] = $sreport = &$this->cstat->sreports[0];

      $displayData['formData']->bPublished = $bPublished = $sreport->bPublished;
      if ($bPublished) return;
      
      $strTypeLabel = $sreport->sections[$enumSRType]->strLabel1;      

         // test for url hack into another's report
      if (!$bNew){
         if ($glUserID != $sreport->lUserID) return;
      }

      if ($bNew){
         $this->cstat->sreports[0]->sections[$enumSRType]->entries = array();
         $this->cstat->sreports[0]->sections[$enumSRType]->entries[0] = new stdClass;
         $entry = &$sreport->sections[$enumSRType]->entries[0];
         $entry->lKeyID         = 0;
         $entry->lStatusID      = $lSReportID;
         $entry->enumStatusType = $enumSRType;
         $entry->strText01      = '';
         $entry->strText02      = '';
         $entry->curEstAmnt     = 0.0;
         $entry->strUrgency     = '';
      }else {
         $this->cstat->loadSectionsViaSectionEntryID($lEntryID, $this->cstat->sreports[0]->sections);
         $entry = &$sreport->sections[$enumSRType]->entries[0];
      }

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      switch ($enumSRType){
         case CENUM_STATCAT_CURRENTPROJECTS:
            $this->form_validation->set_rules('txtCurrentProject', 'Current Project', 'trim|required');
            $this->form_validation->set_rules('txtStatus',         'Status',          'trim|required');
            break;
         case CENUM_STATCAT_CURRENTACTIVITIES:
            $this->form_validation->set_rules('txtCurrentActivities', 'Current Activities', 'trim|required');
            break;
         case CENUM_STATCAT_UPCOMINGEVENTS:
            $this->form_validation->set_rules('txtUpcomingEvents', 'Upcoming Events', 'trim|required');
            break;
         case CENUM_STATCAT_UPCOMINGFUNDRQST:
            $this->form_validation->set_rules('txtUpcomingFundRequest', 'Upcoming Fund Requests', 'trim|required');
            $this->form_validation->set_rules('txtAmount',              'Estimated Amount',       'trim|required|callback_stripCommas|numeric');
            break;
         case CENUM_STATCAT_CONCERNSISSUES:
            $this->form_validation->set_rules('txtConcerns',            'Concerns / Issues', 'trim|required');
            $this->form_validation->set_rules('txtUrgency',             'Urgency',           'trim|required');
            break;
         default:
            screamForHelp($enumSRType.': invalid status report entry type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      $this->form_validation->set_rules('chkPublished',  'Published?',            'trim|callback_verifyFormForPub');

      if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($bNew){
               switch ($enumSRType){
                  case CENUM_STATCAT_CURRENTPROJECTS:
                     $displayData['formData']->txtCurrentProject = '';
                     $displayData['formData']->txtStatus         = '';
                     break;
                  case CENUM_STATCAT_CURRENTACTIVITIES:
                     $displayData['formData']->txtCurrentActivities = '';
                     break;
                  case CENUM_STATCAT_UPCOMINGEVENTS:
                     $displayData['formData']->txtUpcomingEvents = '';
                     break;
                  case CENUM_STATCAT_UPCOMINGFUNDRQST:
                     $displayData['formData']->txtUpcomingFundRequest = '';
                     $displayData['formData']->txtAmount              = '0.00';
                     break;
                  case CENUM_STATCAT_CONCERNSISSUES:
                     $displayData['formData']->txtConcerns = '';
                     $displayData['formData']->txtUrgency  = '';
                     break;
                  default:
                     screamForHelp($enumSRType.': invalid status report entry type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                     break;
               }
            }else {
               switch ($enumSRType){
                  case CENUM_STATCAT_CURRENTPROJECTS:
                     $displayData['formData']->txtCurrentProject = htmlspecialchars($entry->strText01);
                     $displayData['formData']->txtStatus         = htmlspecialchars($entry->strText02);
                     break;
                  case CENUM_STATCAT_CURRENTACTIVITIES:
                     $displayData['formData']->txtCurrentActivities = htmlspecialchars($entry->strText01);
                     break;
                  case CENUM_STATCAT_UPCOMINGEVENTS:
                     $displayData['formData']->txtUpcomingEvents = htmlspecialchars($entry->strText01);
                     break;
                  case CENUM_STATCAT_UPCOMINGFUNDRQST:
                     $displayData['formData']->txtUpcomingFundRequest = htmlspecialchars($entry->strText01);
                     $displayData['formData']->txtAmount              = number_format($entry->curEstAmnt, 2, '.', '');
                     break;
                  case CENUM_STATCAT_CONCERNSISSUES:
                     $displayData['formData']->txtConcerns = htmlspecialchars($entry->strText01);
                     $displayData['formData']->txtUrgency  = htmlspecialchars($entry->strUrgency);
                     break;
                  default:
                     screamForHelp($enumSRType.': invalid status report entry type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                     break;
               }
            }
         
         }else {
            setOnFormError($displayData);
            switch ($enumSRType){
               case CENUM_STATCAT_CURRENTPROJECTS:
                  $displayData['formData']->txtCurrentProject = set_value('txtCurrentProject');
                  $displayData['formData']->txtStatus         = set_value('txtStatus');
                  break;
               case CENUM_STATCAT_CURRENTACTIVITIES:
                  $displayData['formData']->txtCurrentActivities = set_value('txtCurrentActivities');
                  break;
               case CENUM_STATCAT_UPCOMINGEVENTS:
                  $displayData['formData']->txtUpcomingEvents = set_value('txtUpcomingEvents');
                  break;
               case CENUM_STATCAT_UPCOMINGFUNDRQST:
                  $displayData['formData']->txtUpcomingFundRequest = set_value('txtUpcomingFundRequest');
                  $displayData['formData']->txtAmount              = set_value('txtAmount');
                  break;
               case CENUM_STATCAT_CONCERNSISSUES:
                  $displayData['formData']->txtConcerns = set_value('txtConcerns');
                  $displayData['formData']->txtUrgency  = set_value('txtUrgency');
                  break;
               default:
                  screamForHelp($enumSRType.': invalid status report entry type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }
         }
         
            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      =
                                       anchor('aayhf/main/aayhfMenu',                          'AAYHF',         'class="breadcrumb"')
                                .' | '.anchor('staff/performance/addEditPR/'.$lSReportID, 'Status Report', 'class="breadcrumb"')
                                .' | '.($bNew ? 'Add New' : 'Edit').' <b>'.$strTypeLabel.'</b>  Entry';

         $displayData['title']          = CS_PROGNAME.' | Status Report';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'aayhf/aayhf_staff/status_entry_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
      
         switch ($enumSRType){
            case CENUM_STATCAT_CURRENTPROJECTS:
               $entry->strText01 = trim($_POST['txtCurrentProject']);
               $entry->strText02 = trim($_POST['txtStatus']);
               break;
            case CENUM_STATCAT_CURRENTACTIVITIES:
               $entry->strText01 = trim($_POST['txtCurrentActivities']);
               break;
            case CENUM_STATCAT_UPCOMINGEVENTS:
               $entry->strText01 = trim($_POST['txtUpcomingEvents']);
               break;
            case CENUM_STATCAT_UPCOMINGFUNDRQST:
               $entry->strText01  = trim($_POST['txtUpcomingFundRequest']);
               $entry->curEstAmnt = (float)trim($_POST['txtAmount']);
               break;
            case CENUM_STATCAT_CONCERNSISSUES:
               $entry->strText01  = trim($_POST['txtConcerns']);
               $entry->strUrgency = trim($_POST['txtUrgency']);
               break;
            default:
               screamForHelp($enumSRType.': invalid status report entry type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }

         if ($bNew){
            $lEntryID = $this->cstat->lInsertStatusEntry($lSReportID, $entry);
         }else {
            $this->cstat->updateStatusEntry($lEntryID, $entry);
         }
         $this->session->set_flashdata('msg', 'Your '.$strTypeLabel.' entry was '.($bNew ? 'added' : 'updated').'.');
         redirect('staff/performance/addEditPR/'.$lSReportID);
      }
   }

   function stripCommas(&$strAmount){
      $strAmount = str_replace (',', '', $strAmount);
      return(true);
   }

   function removeEntry($lSReportID, $lEntryID, $enumSRType){
   //------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------
      global $glUserID;
      
      if (!bTestForURLHack('notVolunteer')) return;
      
      $lSReportID = (integer)$lSReportID;
      $lEntryID   = (integer)$lEntryID;      
   
         //-------------------------------------
         // models, libraries, and helpers
         //-------------------------------------
      $this->load->model('staff/mstaff_status', 'cstat');
   
         // load the status report
      $this->cstat->loadStatusReportViaRptID($lSReportID, false);
      $sreport = &$this->cstat->sreports[0];
      $strTypeLabel = $sreport->sections[$enumSRType]->strLabel1;      
   
         // test for url hack into another's report
      if ($glUserID != $sreport->lUserID) return;
      if ($sreport->bPublished) return;
   
      $this->cstat->removeStatusEntry($lEntryID);
   
      $this->session->set_flashdata('msg', 'Your '.$strTypeLabel.' entry was removed.');
      redirect('staff/performance/addEditPR/'.$lSReportID);   
   }






}
