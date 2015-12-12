<?php
class event_date_shifts_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEditShift($lEventDateID, $lShiftID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lEventDateID, 'event date ID');
      if ($lShiftID.''!='0') verifyID($this, $lShiftID,     'shift ID');

      $displayData = array();

         //-----------------------------
         // models and helpers
         //-----------------------------
      $this->load->model('vols/mvol_event_dates_shifts', 'clsShifts');
      $this->load->model('vols/mvol_event_dates',        'clsVolEventDates');
      $this->load->model('util/mlist_generic',           'cList');
      $this->load->helper('dl_util/time_date');

      $displayData['lEventDateID'] = $lEventDateID = (integer)$lEventDateID;
      $displayData['lShiftID']     = $lShiftID     = (integer)$lShiftID;
      $displayData['bNew']         = $bNew = $lShiftID <= 0;

      $this->clsShifts->loadShiftsViaEventShiftID($lShiftID);
      $displayData['shift'] = $shift = &$this->clsShifts->shifts[0];
      $this->cList->enumListType = CENUM_LISTTYPE_VOLJOBCODES;
      $this->cList->strBlankDDLName = '(no job code)';

      $this->clsVolEventDates->loadEventDateViaDateID($lEventDateID);
      $edate    = &$this->clsVolEventDates->dates[0];
      $lEventID = $edate->lVolEventID;
      $displayData['dteEvent'] = $edate->dteEvent;

      if ($bNew){
         $shift->lKeyID            = -1;
         $shift->lEventDateID      = $lEventDateID;
         $shift->dteEventStartTime = strtotime('12:00:00 PM');
      }

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtShiftName',     'Shift Name',          'trim|required');
      $this->form_validation->set_rules('txtShiftNote',     'Shift Notes',         'trim');
      $this->form_validation->set_rules('txtNumVols',       '# Volunteers Needed', 'trim|required|callback_stripCommas|numeric');
      $this->form_validation->set_message('is_natural_no_zero', 'Please select an entry for %s');
      $this->form_validation->set_rules('ddlShiftStart',    'Shift Starting Time', 'trim|required|is_natural_no_zero');
      $this->form_validation->set_rules('ddlShiftDuration', 'Shift Duration',      'trim|required');
      $this->form_validation->set_rules('ddlJobCode',       'Job Code',            'trim');

      if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $params = array('enumStyle' => 'terse');
         $this->load->library('generic_rpt', $params);
         $this->load->model('vols/mvol_events', 'clsVolEvents');
         $this->clsVolEvents->loadEventsViaEID($lEventID);
         $displayData['contextSummary'] = $this->clsVolEvents->volEventHTMLSummary(0);

         $this->load->library('generic_form');
         if (validation_errors()==''){
            $displayData['formData']->txtShiftName    = $shift->strShiftName;
            $displayData['formData']->txtShiftNotes   = $shift->strDescription;
            $displayData['formData']->txtNumVols      = $shift->lNumVolsNeeded;
            $displayData['formData']->lEventStartTime = strDisplayTimeDDL($shift->dteEventStartTime, true);
            $displayData['formData']->enumDuration    = strDurationDDL   (true, true, $shift->enumDuration);

               // load job code ddl
            $displayData['strDDLJobCode'] = $this->cList->strLoadListDDL('ddlJobCode', true, $shift->lJobCode);
         }else{
            setOnFormError($displayData);
            $displayData['formData']->txtShiftName    = set_value('txtShiftName');
            $displayData['formData']->txtShiftNotes   = set_value('txtShiftNotes');
            $displayData['formData']->txtNumVols      = set_value('txtNumVols');
            $displayData['formData']->lEventStartTime = strDisplayTimeDDL(set_value('ddlShiftStart'), true);
            $displayData['formData']->enumDuration    = strDurationDDL   (true, true, set_value('ddlShiftDuration'));

               // load job code ddl
            $displayData['strDDLJobCode'] = $this->cList->strLoadListDDL('ddlJobCode', true, set_value('ddlJobCode'));
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']   = anchor('main/menu/vols',                                     'Volunteers', 'class="breadcrumb"')
                                .' | '.anchor('volunteers/events_schedule/viewEventsList',           'Event List', 'class="breadcrumb"')
                                .' | '.anchor('volunteers/events_record/viewEvent/'.$lEventID,       'Event', 'class="breadcrumb"')
                                .' | '.anchor('volunteers/event_dates_view/viewDates/'.$lEventDateID, 'Event Date',   'class="breadcrumb"')
                                .' | '.($bNew ? 'Add New' : 'Edit').' Event Shift';

         $displayData['title'] = CS_PROGNAME.' | Volunteers';
         $displayData['nav']   = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'vols/vol_event_date_shift_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $shift->strShiftName      = trim($_POST['txtShiftName']);
         $shift->strDescription    = trim($_POST['txtShiftNotes']);
         $shift->dteEventStartTime = (integer)$_POST['ddlShiftStart'];
         $shift->enumDuration      = $_POST['ddlShiftDuration'];
         $shift->lNumVolsNeeded    = (integer)$_POST['txtNumVols'];

         $shift->lJobCode = (integer)$_POST['ddlJobCode'];
         if ($shift->lJobCode <= 0) $shift->lJobCode = null;

         if ($bNew){
            $lShiftID = $this->clsShifts->lAddNewEventShift();
         }else {
            $shift->lKeyID = $lShiftID;
            $this->clsShifts->updateEventShift();
         }
         $this->session->set_flashdata('msg', 'The event shift was '.($bNew ? 'added' : 'updated'));
         redirect('/volunteers/event_dates_view/viewDates/'.$lEventDateID);
      }
   }

   function stripCommas(&$strAmount){
      $strAmount = str_replace (',', '', $strAmount);
      return(true);
   }

   function remove($lEventDateID, $lShiftID){
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lEventDateID, 'event date ID');
      verifyID($this, $lShiftID,     'shift ID');

      $lEventDateID = (integer)$lEventDateID;
      $lShiftID     = (integer)$lShiftID;

      $this->load->model('vols/mvol_event_dates_shifts', 'clsShifts');

      $this->clsShifts->deleteEventDateShift($lShiftID);

      $this->session->set_flashdata('msg', 'The requested event shift was removed');
      redirect('/volunteers/event_dates_view/viewDates/'.$lEventDateID);
   }

   function cloneShiftOpts($lEventDateID, $lShiftID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lEventDateID, 'event date ID');
      verifyID($this, $lShiftID,     'shift ID');

      $displayData = array();
      $displayData['lEventDateID'] = $lEventDateID = (integer)$lEventDateID;
      $displayData['lShiftID']     = $lShiftID     = (integer)$lShiftID;

         //----------------------------
         // load models and helpers
         //----------------------------
      $this->load->model('vols/mvol_event_dates_shifts', 'clsShifts');
      $this->load->model('vols/mvol_event_dates', 'clsVolEventDates');
      $this->load->model('vols/mvol_events', 'clsVolEvents');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper('dl_util/time_date');

         //------------------------------------
         // load the check/uncheck support
         //------------------------------------
      $this->load->helper('js/set_check_boxes');
      $displayData['js'] = insertCheckSet();
      $this->load->helper('js/verify_check_set');
      $displayData['js'] .= verifyCheckSet();

      $this->clsShifts->loadShiftsViaEventShiftID($lShiftID);
      $displayData['lEventID'] = $lEventID = $this->clsShifts->shifts[0]->lVolEventID;

      $this->clsVolEventDates->loadEventDates($lEventID);
      $displayData['dates'] = $this->clsVolEventDates->dates;

      $displayData['contextSummary'] = $this->clsShifts->volEventDateShiftHTMLSummary();

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']   = anchor('main/menu/vols',                                     'Volunteers', 'class="breadcrumb"')
                             .' | '.anchor('volunteers/events_schedule/viewEventsList',           'Event List', 'class="breadcrumb"')
                             .' | '.anchor('volunteers/events_record/viewEvent/'.$lEventID,       'Event',      'class="breadcrumb"')
                             .' | '.anchor('volunteers/event_dates_view/viewDates/'.$lEventDateID, 'Event Date', 'class="breadcrumb"')
                             .' | Clone Shift';

      $displayData['title'] = CS_PROGNAME.' | Volunteers';
      $displayData['nav']   = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'vols/clone_shift_opts_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function cloneShift($lEventID, $lEventDateID, $lShiftID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lEventID,     'event ID');
      verifyID($this, $lEventDateID, 'event date ID');
      verifyID($this, $lShiftID,     'shift ID');


      $lEventDateID = (integer)$lEventDateID;
      $lShiftID     = (integer)$lShiftID;
      $lEventID     = (integer)$lEventID;

      $this->load->model('vols/mvol_events',               'clsVolEvents');
      $this->load->model('vols/mvol_event_dates',           'clsVolEventDates');
      $this->load->model('vols/mvol_event_dates_shifts',     'clsShifts');
      $this->load->model('vols/mvol_event_dates_shifts_vols', 'clsSV');

      $bCloneVols = @$_POST['chkVols']=='TRUE';
      $this->clsVolEvents->loadEventsViaEID($lEventID);

      $lNumClones = 0;

      if (isset($_REQUEST['chkDate'])){
         foreach ($_REQUEST['chkDate'] as $strDateID){
            $this->clsShifts->cloneShift($lShiftID, (integer)$strDateID, $bCloneVols);
            ++$lNumClones;
         }
      }

      $this->session->set_flashdata('msg', $lNumClones
                               .' cloned shift'.($lNumClones==1 ? '' : 's')
                               .' added to event <b>'.htmlspecialchars($this->clsShifts->shifts[0]->strShiftName).'</b>');
      redirect_VolEvent($lEventID);
   }



}