<?php
class event_dates_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEditDate($lEventID, $lDateID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gbDateFormatUS;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lEventID, 'event ID');
      if ($lDateID.'' !='0') verifyID($this, $lDateID, 'event date ID');

      $displayData = array();
      $displayData['lEventID'] = $lEventID = (integer)$lEventID;
      $displayData['lDateID']  = $lDateID  = (integer)$lDateID;

      $this->load->model('vols/mvol_event_dates', 'clsVolEventDates');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/web_layout');

      $this->clsVolEventDates->loadEventDateViaDateID($lDateID);

      $displayData['bNew']     = $bNew     = $lDateID <= 0;
      $displayData['edate']    = $edate    = $this->clsVolEventDates->dates[0];

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtEDate', 'Event date', 'trim|required|callback_edateVerifyDateValid');

      if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $params = array('enumStyle' => 'terse');
         $this->load->library('generic_rpt', $params);
         $this->load->model('vols/mvol_events', 'clsVolEvents');
         $this->clsVolEvents->loadEventsViaEID($lEventID);
         $displayData['contextSummary'] = $this->clsVolEvents->volEventHTMLSummary(0);

         $this->load->library('generic_form');
         if (validation_errors()==''){
            if (is_null($edate->dteEvent)){
               $displayData['formData']->txtEDate = '';
            }else {
               $displayData['formData']->txtEDate = strNumericDateViaMysqlDate($edate->mDteEvent, $gbDateFormatUS);
            }
         }else{
            setOnFormError($displayData);
            $displayData['formData']->txtEDate      = set_value('txtEDate');
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                                   .' | '.anchor('volunteers/events_schedule/viewEventsList', 'Event List', 'class="breadcrumb"')
                                   .' | '.anchor('volunteers/events_record/viewEvent/'.$lEventID, 'Event', 'class="breadcrumb"');
         if (!$bNew) $displayData['pageTitle'] .=
                                    ' | '.anchor('volunteers/event_dates_view/viewDates/'.$lDateID, 'Event Date', 'class="breadcrumb"');
         $displayData['pageTitle'] .=
                                    ' | '.($bNew ? 'Add New' : 'Edit').' Event Date';

         $displayData['title']          = CS_PROGNAME.' | Volunteers';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'vols/vol_event_date_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $strEDate   = trim($_POST['txtEDate']);
         MDY_ViaUserForm($strEDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $strEDate = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);
         if ($bNew){
            $lDateID = $this->clsVolEventDates->lInsertEventDate($lEventID, $strEDate, true);
         }else {
            $this->clsVolEventDates->updateEventDate($lDateID, $strEDate, true);
         }
         $this->session->set_flashdata('msg', 'The event date was '.($bNew ? 'added' : 'updated'));
         redirect('volunteers/event_dates_view/viewDates/'.$lDateID);
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
   function edateVerifyDateValid($strEDate){
      return(bValidVerifyDate($strEDate));
   }


   function remove($lEventID, $lDateID){
   //---------------------------------------------------------------------
   // remove date from event
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lEventID, 'event ID');
      verifyID($this, $lDateID,  'event date ID');
   
      $lEventID = (integer)$lEventID;
      $lDateID  = (integer)$lDateID;

      $this->load->model('vols/mvol_event_dates', 'clsVolEventDates');

      $this->clsVolEventDates->deleteEventDate($lDateID);
      $this->session->set_flashdata('msg', 'The specified date was removed from this event');
      redirect_VolEvent($lEventID);

   }


}