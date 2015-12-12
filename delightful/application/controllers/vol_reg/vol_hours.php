<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class vol_hours extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function view($lNumMonths = '6'){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $gbVolLogin, $glVolPeopleID, $gdteNow;
      if (!bTestForURLHack('volViewHours')) return;

      $displayData = array();
      $displayData['js'] = '';

         //--------------------------------
         // Models & Helpers
         //--------------------------------
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/time_duration_helper');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('vols/vol');
      $this->load->helper('dl_util/time_duration_helper');
      $this->load->model ('vols/mvol',                         'clsVol');
      $this->load->model ('vols/mvol_event_dates',             'clsVolEventDates');
      $this->load->model ('vols/mvol_event_hours',             'clsVolHours');
      $this->load->model ('vols/mvol_event_dates_shifts_vols', 'clsSV');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

      $displayData['lVolID'] = $lVolID = $this->clsVol->lVolIDViaPeopleID($glVolPeopleID);

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         // cumulative stats
      $displayData['dTotScheduledHrs']   = $this->clsVolHours->volEventHoursViaVolID($lVolID);
      $displayData['dTotUnscheduledHrs'] = $this->clsVolHours->dTotUnscheduledHoursViaVolID($lVolID);


      $lNumMonths = (int)$lNumMonths;
      numsMoDaYr($gdteNow, $lMonth, $lDay, $lYear);
      $events = array();
      for ($idx = 0; $idx<$lNumMonths; ++$idx){
         $this->clsVolEventDates->eventIDsViaMonth($lMonth, $lYear, $eventsMonths[$idx]);
         $eMonth = &$eventsMonths[$idx];

            // unscheduled activities
         $this->clsVolHours->unscheduledHoursViaVolIDMonthYear($lVolID, $lMonth, $lYear,
                                   $eMonth->lNumUnscheduled, $eMonth->unscheduled);

         if ($eMonth->lNumEvents > 0){
            foreach ($eMonth->events as $event){
               $lEventID = $event->lEventID;
               $strWhere = " AND ved_lVolEventID=$lEventID
                             AND MONTH(ved_dteEvent)=$lMonth
                             AND YEAR(ved_dteEvent)=$lYear ";
               $this->clsVolEventDates->loadEventDatesArray($strWhere);
               $event->dates = arrayCopy($this->clsVolEventDates->dates);

                  // load shifts for each date
               if ($event->lNumDates > 0){
                  foreach ($event->dates as $eDate){
                     $lDateID = $eDate->lKeyID;
                     $this->clsVolEventDates->shiftsByDateID($lDateID, $eDate->shifts, $eDate->lNumShifts);

                     if ($eDate->lNumShifts > 0){
                        foreach ($eDate->shifts as $shift){
                           $lShiftID = $shift->lShiftID;
                           $this->clsVolHours->volHoursViaVolIDShiftID(
                                     $lVolID, $lShiftID, $shift->enumHrsScheduled, $shift->dHrsScheduled, $shift->dHrsWorked);
                        }
                     }
                  }
               }
            }
         }

         --$lMonth;
         if ($lMonth <=0){
            $lMonth = 12;
            --$lYear;
         }
      }

      $displayData['eventsMonths'] = &$eventsMonths;

         // breadcrumbs and navigation
      $displayData['pageTitle']    = 'Volunteer Hours';
      $displayData['mainTemplate'] = 'vols/vol_login_hrs_view';
      $displayData['title']        = CS_PROGNAME.' | Volunteer Hours';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   public function shifts($lNumMonths = '4'){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $gbVolLogin, $glVolPeopleID, $gdteNow;
      if (!bTestForURLHack('volViewHours')) return;

      $displayData = array();
      $displayData['js'] = '';

         //--------------------------------
         // Models & Helpers
         //--------------------------------
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('vols/vol');
      $this->load->helper('dl_util/time_duration_helper');
      $this->load->model ('vols/mvol',                         'clsVol');
      $this->load->model ('vols/mvol_event_dates',             'clsVolEventDates');
      $this->load->model ('vols/mvol_event_hours',             'clsVolHours');
      $this->load->model ('vols/mvol_event_dates_shifts_vols', 'clsSV');
      $this->load->model ('vols/mvol_event_dates_shifts',      'clsShifts');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

      $displayData['lVolID'] = $lVolID = $this->clsVol->lVolIDViaPeopleID($glVolPeopleID);

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $lNumMonths = (int)$lNumMonths;
      numsMoDaYr($gdteNow, $lMonth, $lDay, $lYear);
      $events = array();
      for ($idx = 0; $idx<$lNumMonths; ++$idx){
         $this->clsVolEventDates->eventIDsViaMonth($lMonth, $lYear, $eventsMonths[$idx]);
         $eMonth = &$eventsMonths[$idx];
         if ($eMonth->lNumEvents > 0){
            foreach ($eMonth->events as $event){
               $lEventID = $event->lEventID;
               $strWhere = " AND ved_lVolEventID=$lEventID
                             AND MONTH(ved_dteEvent)=$lMonth
                             AND YEAR(ved_dteEvent)=$lYear ";
               $this->clsVolEventDates->loadEventDatesArray($strWhere);
               $event->dates = arrayCopy($this->clsVolEventDates->dates);

                  // load shifts for each date
               if ($event->lNumDates > 0){
                  foreach ($event->dates as $eDate){
                     $lDateID = $eDate->lKeyID;
                     $this->clsVolEventDates->shiftsByDateID($lDateID, $eDate->shifts, $eDate->lNumShifts);
                     if ($eDate->lNumShifts > 0){
                        foreach ($eDate->shifts as $shift){
                           $shift->bCurrentVolRegistered = false;
                           if ($shift->lNumVols > 0){
                              foreach ($shift->vols as $registeredVol){
                                 if ($registeredVol->lVolID == $lVolID){
                                    $shift->bCurrentVolRegistered = true;
                                    $shift->lCurVolAssignID = $registeredVol->lAssignID;
                                    break;
                                 }
                              }
                           }
                        }
                     }
                  }
               }
            }
         }

         ++$lMonth;
         if ($lMonth >12){
            $lMonth = 1;
            ++$lYear;
         }
      }

      $displayData['eventsMonths'] = &$eventsMonths;

         // breadcrumbs and navigation
      $displayData['pageTitle']    = 'Volunteer Registration';
      $displayData['mainTemplate'] = 'vols/register_upcoming_view';
      $displayData['title']        = CS_PROGNAME.' | Upcoming Events';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function regUnreg($bRegister, $lShiftID, $lAssignID=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat, $glVolPeopleID;

      $bRegister = $bRegister=='true';
      $lShiftID = (int)$lShiftID;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lShiftID, 'shift ID');

         //--------------------------------
         // Models & Helpers
         //--------------------------------
      $this->load->model('vols/mvol_event_dates_shifts',      'clsShifts');
      $this->load->model('vols/mvol_event_dates_shifts_vols', 'clsSV');
      $this->load->model ('vols/mvol',                        'clsVol');
      
      $lVolID = $this->clsVol->lVolIDViaPeopleID($glVolPeopleID);
      
      $this->clsShifts->loadShiftsViaEventShiftID($lShiftID);
      $shift = &$this->clsShifts->shifts[0];

      $strRegInfo = 
            '<table>
               <tr>
                  <td><b>Event:</td>     
                  <td><b>'.htmlspecialchars($shift->strEventName).'</b></td>
               </tr>
               <tr>
                  <td ><b>date:</b></td>
                  <td>'.date($genumDateFormat, $shift->dteEvent).'</td>
               </tr>
               <tr>
                  <td><b>time:</b></td>
                  <td>'.date('g:i a', $shift->dteEventStartTime).'</td>
               </tr>
               <tr>
                  <td><b>duration:</b></td>
                  <td>'.$shift->enumDuration.'</td>
               </tr>
               <tr>
                  <td><b>shift:</b></td>
                  <td>'.htmlspecialchars($shift->strShiftName).'</td>
               </tr>
            </table>';

      $this->clsShifts->loadShiftsViaEventShiftID($lShiftID);
      if ($bRegister){
         $this->clsSV->lAddVolToShift($lShiftID, $lVolID, 'Self-registration');
         $this->session->set_flashdata('msg', 'Thank you for registering for '.$strRegInfo);
      }else {
         $this->clsSV->removeVolFromShift($lAssignID);
         $this->session->set_flashdata('msg', 'Your registration was cancelled for<br>'.$strRegInfo);
      }
      redirect('vol_reg/vol_hours/shifts');
   }
   
   function cal($lNumMonths = '4'){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      global $gbVolLogin, $glVolPeopleID, $gdteNow;
      if (!bTestForURLHack('volFeatures')) return;

      $displayData = array();
      $displayData['js'] = '';
      
         //--------------------------------
         // Models & Helpers
         //--------------------------------
      $this->load->helper('dl_util/web_layout');
      $this->load->model ('vols/mvol_event_dates',             'clsVolEventDates');
      $this->load->model ('vols/mvol',                         'clsVol');
      $this->load->helper('dl_util/time_date');
      $this->load->model ('vols/mvol_event_dates_shifts_vols', 'clsSV');
      
      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

      $displayData['lVolID'] = $lVolID = $this->clsVol->lVolIDViaPeopleID($glVolPeopleID);

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $lNumMonths = (int)$lNumMonths;
      numsMoDaYr($gdteNow, $lMonth, $lDay, $lYear);
      $events = array();
      for ($idx = 0; $idx<$lNumMonths; ++$idx){
         $this->clsVolEventDates->eventIDsViaMonth($lMonth, $lYear, $eventsMonths[$idx]);
         $eMonth = &$eventsMonths[$idx];
         if ($eMonth->lNumEvents > 0){
            $this->clsSV->loadShiftsViaVolIDMonth($lMonth, $lYear, $lVolID, $eMonth->lNumShifts, $eMonth->shifts);
         }            

         ++$lMonth;
         if ($lMonth >12){
            $lMonth = 1;
            ++$lYear;
         }
      }
      
      $displayData['eventsMonths'] = &$eventsMonths;

         // breadcrumbs and navigation
      $displayData['pageTitle']    = 'Volunteer Calendar';
      $displayData['mainTemplate'] = 'vols/cal_upcoming_view';
      $displayData['title']        = CS_PROGNAME.' | Upcoming Events';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }



}
