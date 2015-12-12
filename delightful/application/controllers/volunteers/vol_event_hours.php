<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class vol_event_hours extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewHoursViaEvent($strViaShift, $lEventID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lEventID, 'event ID');
      
      $displayData = array();
      $displayData['lEventID']  = $lEventID  = (integer)$lEventID;
      $displayData['bViaShift'] = $bViaShift = strtoupper($strViaShift) == 'TRUE';

         //------------------------------------------------
         // libraries / models / utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->model('vols/mvol_event_dates',           'clsVolEventDates');
      $this->load->model('vols/mvol_event_hours',           'clsVolHours');
      $this->load->model('vols/mvol_event_dates_shifts',     'clsShifts');
      $this->load->model('vols/mvol_event_dates_shifts_vols', 'clsSV');
      $this->load->model('vols/mvol_events',               'clsVolEvents');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/time_date');

      $displayData['lNumShifts'] = $lNumShifts = $this->clsShifts->lNumShifsTotViaEventID($lEventID);
      $displayData['lNumVols']   = $lNumVols   = $this->clsSV->lTotVolsAssignedViaEventID($lEventID);

      $displayData['mainTemplate']   = 'vols/vol_hours_via_event_shift';
      if ($lNumShifts > 0 && $lNumVols > 0){
         if ($bViaShift){
            $this->clsShifts->loadShiftsViaEventID($lEventID);
            $idx = 0;
            foreach ($this->clsShifts->shifts as $shift){
               $lShiftID = $shift->lKeyID;
               $this->clsVolHours->volEventHoursViaShift($lShiftID);
               $shift->lNumVolsInShift = $this->clsVolHours->lNumVolsInShift;
               if ($shift->lNumVolsInShift > 0){
                  $shift->vols = array();
                  $jIdx = 0;
                  foreach ($this->clsVolHours->volEHrs as $vHours){
                     $shift->vols[$jIdx] = new stdClass;
                     $shift->vols[$jIdx]->dHoursWorked= $vHours->dHoursWorked;
                     $shift->vols[$jIdx]->lVolShiftAssignID = $vHours->lVolShiftAssignID;
                     $shift->vols[$jIdx]->lPeopleID         = $vHours->lPeopleID;
                     $shift->vols[$jIdx]->lVolID            = $vHours->lVolID;
                     $shift->vols[$jIdx]->strFName          = $vHours->strFName;
                     $shift->vols[$jIdx]->strLName          = $vHours->strLName;
                     $shift->vols[$jIdx]->strAddr1          = $vHours->strAddr1;
                     $shift->vols[$jIdx]->strAddr2          = $vHours->strAddr2;
                     $shift->vols[$jIdx]->strCity           = $vHours->strCity;
                     $shift->vols[$jIdx]->strState          = $vHours->strState;
                     $shift->vols[$jIdx]->strCountry        = $vHours->strCountry;
                     $shift->vols[$jIdx]->strZip            = $vHours->strZip;

                     ++$jIdx;
                  }
               }
               ++$idx;
            }
            $displayData['shifts'] = &$this->clsShifts->shifts;
         }else {
whereAmI(); die;
         }
      }

      $this->clsVolEvents->loadEventsViaEID($lEventID);
      $displayData['strEventName']   = $this->clsVolEvents->events[0]->strEventName;
      $displayData['contextSummary'] = $this->clsVolEvents->volEventHTMLSummary(0);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['title']        = CS_PROGNAME.' | Volunteer Hours';
      $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers',                            'class="breadcrumb"')
                              .' | '.anchor('volunteers/events_schedule/viewEventsList', 'Event List', 'class="breadcrumb"')
                              .' | '.anchor('volunteers/events_record/viewEvent/'.$lEventID, 'Event',  'class="breadcrumb"')
                              .' | Volunteer Hours';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function editShiftHrsAsVol($lVolID, $lEventID, $lShiftID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbVolLogin, $glVolPeopleID, $gdteNow;
      if (!bTestForURLHack('volEditHours')) return;
      
      $this->load->model ('vols/mvol', 'clsVol');
      $lVolID = (int)$lVolID;
      $lVolSessionID = $this->clsVol->lVolIDViaPeopleID($glVolPeopleID);
      if ($lVolSessionID != $lVolID) {
         bTestForURLHack('forceFail');
         return;
      }
      $this->editShiftHrsCommon(true, $lVolID, $lEventID, $lShiftID, 'volunteerUser');
   }
   
   function editShiftHrs($lEventID, $lShiftID, $strReturn=''){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('editPeopleBizVol')) return;
      $this->editShiftHrsCommon(false, null, $lEventID, $lShiftID, $strReturn);
   }
   
   function editShiftHrsCommon($bAsVol, $lVolID, $lEventID, $lShiftID, $strReturn){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------      
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lEventID, 'event ID');
      verifyID($this, $lShiftID, 'shift ID');
      
      $displayData = array();
      $displayData['lEventID']   = $lEventID  = (integer)$lEventID;
      $displayData['lShiftID']   = $lShiftID  = (integer)$lShiftID;
      $displayData['strReturn']  = trim($strReturn);
      $displayData['bAsVol']     = $bAsVol;
      $displayData['lVolID']     = $lVolID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('dl_util/time_date');  // for date verification
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

      $this->load->model('vols/mvol_event_dates',             'clsVolEventDates');
      $this->load->model('vols/mvol_event_hours',             'clsVolHours');
      $this->load->model('vols/mvol_event_dates_shifts',      'clsShifts');
      $this->load->model('vols/mvol_event_dates_shifts_vols', 'clsSV');
      $this->load->model('vols/mvol_events',                  'clsVolEvents');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/time_date');

         //---------------------------------
         // event and shift information
         //---------------------------------
      $this->clsVolHours->volEventHoursViaShift($lShiftID, $bAsVol, $lVolID);
      $displayData['lNumVols'] = $lNumVols = $this->clsVolHours->lNumVolsInShift;

      $this->clsShifts->loadShiftsViaEventShiftID($lShiftID, $bAsVol, $lVolID);
      $this->clsVolEvents->loadEventsViaEID($lEventID);
      
      $lEventDateID = $this->clsShifts->shifts[0]->lEventDateID;
      
      $displayData['shift']    = $shift = &$this->clsShifts->shifts[0];
      $displayData['strShiftLabel'] = $strShiftLabel =
                date('F j Y', $shift->dteEvent).'&nbsp&nbsp;'.date('g:i a', $shift->dteEventStartTime).': '
              .'<b>'.htmlspecialchars($shift->strShiftName)
              .'</b> ('.$shift->enumDuration.')';

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      if ($lNumVols > 0){
         foreach ($this->clsVolHours->volEHrs as $vol){
            $this->form_validation->set_rules('txtHR_'.$vol->lVolShiftAssignID, 'Volunteer Hours ',
                                              'trim|required|greater_than[-0.01]');
         }
      }

		if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');

         $displayData['contextSummary'] = $this->clsVolEvents->volEventHTMLSummary(0);

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($lNumVols > 0){
               foreach ($this->clsVolHours->volEHrs as $vol){
                  $vol->strHours = number_format($vol->dHoursWorked, 2);
               }
            }
         }else {
            setOnFormError($displayData);
            foreach ($this->clsVolHours->volEHrs as $vol){
               $vol->strHours = @$_POST['txtHR_'.$vol->lVolShiftAssignID];
            }
         }

         $displayData['vols']     = &$this->clsVolHours->volEHrs;

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['title']        = CS_PROGNAME.' | Volunteer Hours';
         if ($bAsVol){
            $displayData['pageTitle']    = 'Edit Volunteer Hours';
         }else {
            $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers',                           'class="breadcrumb"')
                                    .' | '.anchor('volunteers/events_schedule/viewEventsList', 'Event List', 'class="breadcrumb"')
                                    .' | '.anchor('volunteers/events_record/viewEvent/'.$lEventID, 'Event',  'class="breadcrumb"')
                                    .' | '.anchor('volunteers/vol_event_hours/viewHoursViaEvent/true/'.$lEventID, 'Volunteer Hours',  'class="breadcrumb"')
                                    .' | Edit Hours';
         }
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'vols/vol_shift_hours_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         foreach ($this->clsVolHours->volEHrs as $vol){
            $lVolShiftAssignID = $vol->lVolShiftAssignID;
            $dHours = (float)@$_POST['txtHR_'.$lVolShiftAssignID];
            $this->clsVolHours->setVolHours($lVolShiftAssignID, $dHours);
         }
         switch ($strReturn){
            case 'event':
               redirect_VolEvent($lEventID);
               break;
            case 'eventDate':
               redirect_VolEventDate($lEventDateID);
               break;
            case 'volunteerUser':
               $this->session->set_flashdata('msg', 'Your volunteer hours were updated for <b>'.$strShiftLabel.'</b>');
               redirect('vol_reg/vol_hours/view');
               break;
            default:
               redirect_VolEventHours($lEventID);
               break;
         }
      }
   }



}