<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class event_date_shift_vols_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function addEditVolsForShift($lDateID, $lShiftID, $skillList=null){
   //-------------------------------------------------------------------------
   // is skillList is not null, limit display of volunteers to those
   // possessing skills in the list
   //-------------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lDateID,  'event date ID');
      verifyID($this, $lShiftID, 'shift ID');
   
      $displayData = array();
      $displayData['lDateID']  = $lDateID  = (integer)$lDateID;
      $displayData['lShiftID'] = $lShiftID = (integer)$lShiftID;

         //--------------------------
         // models, y todos
         //--------------------------
      $this->load->model('vols/mvol_skills',                  'clsVolSkills');
      $this->load->model('vols/mvol_events',                  'clsVolEvents');
      $this->load->model('vols/mvol_event_dates',             'clsVolEventDates');
      $this->load->model('vols/mvol_event_dates_shifts',      'clsShifts');
      $this->load->model('vols/mvol_event_dates_shifts_vols', 'clsSV');
      $this->load->model('util/mlist_generic',                'listGeneric');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper('dl_util/time_date');
      $this->clsVolEventDates->loadEventDateViaDateID($lDateID);
      $lEventID = $this->clsVolEventDates->dates[0]->lVolEventID;

          // load the job skills list
      $this->listGeneric->initializeListManager('generic', CENUM_LISTTYPE_VOLSKILLS);
      $this->listGeneric->loadList();
      $displayData['skills']  = arrayCopy($this->listGeneric->listItems);
      
         //---------------------
         // shift summary
         //---------------------
      $this->clsShifts->loadShiftsViaEventShiftID($lShiftID);
      $displayData['contextSummary'] = $this->clsShifts->volEventDateShiftHTMLSummary();

         //----------------------------------------------------
         // find volunteers already assigned to this shift
         //----------------------------------------------------
      $this->clsSV->loadVolsViaShiftID($lShiftID);
      $strExcludeIDs = $this->clsSV->strVolIDsAsStr();

         //----------------------------------------------------------
         // load all available volunteers, excluding those already
         // assigned to this shift (populates ->volsA[]
         //----------------------------------------------------------
      $this->clsSV->loadAvailVols($strExcludeIDs, false);
/*      
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<pre>'); print_r($this->clsSV->volsA); echo('</pre></font><br>');      
*/
      $displayData['volsA']         = $volsA = &$this->clsSV->volsA;
      $displayData['lNumVolsAvail'] = $lNumVolsAvail = $this->clsSV->lNumVolsAvail;
      $bSkillTest = !is_null($skillList);

      if ($lNumVolsAvail > 0){
         foreach ($volsA as $vol){
            $this->clsVolSkills->lVolID = $lVolID = $vol->lVolID;
            $this->clsVolSkills->loadSingleVolSkills();
            $vol->lNumVolSkills = $this->clsVolSkills->lNumSingleVolSkills;
            $vol->volSkills = $this->clsVolSkills->singleVolSkills;
            if ($bSkillTest){
               $skillTest = array();
               foreach ($vol->volSkills as $vSkill){
                  $skillTest[] = $vSkill->lSkillID;
               }
               $vol->bEligible = $this->allSkills($skillTest, $skillList);
            }else {
               $vol->bEligible = true;
            }
         }
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']  = anchor('main/menu/vols',                                'Volunteers', 'class="breadcrumb"')
                            .' | '.anchor('volunteers/events_schedule/viewEventsList',      'Event List', 'class="breadcrumb"')
                            .' | '.anchor('volunteers/events_record/viewEvent/'.$lEventID,  'Event',      'class="breadcrumb"')
                            .' | '.anchor('volunteers/event_dates_view/viewDates/'.$lDateID, 'Event Date', 'class="breadcrumb"')
                            .' | Add Volunteers to Shift';

      $displayData['title']          = CS_PROGNAME.' | Volunteers';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'vols/vol_event_shift_add_vols_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   function allSkills(&$skillTest, &$skillList){
      foreach ($skillList as $lNeededSkill){
         if (!in_array($lNeededSkill, $skillTest)) return(false);
      }
      return(true);
   }

   public function addVolsViaSkills($lDateID, $lShiftID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------      
      $skillList = null;
      if (isset($_POST['ddlSkills'])){
         $skillList = array();
         foreach ($_POST['ddlSkills'] as $lSkill){
            $lSkill = (integer)$lSkill;
            if ($lSkill <= 0) {
               $skillList = null;
               break;
            }
            $skillList[] = $lSkill;
         }
      }
      $this->addEditVolsForShift($lDateID, $lShiftID, $skillList);
   }
   
   function updateVolsForShift($lEventDateID, $lEventShiftID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lEventDateID,  'event date ID');
      verifyID($this, $lEventShiftID, 'shift ID');
   
      $lNumVolsSel = 0;

      $this->load->model('vols/mvol_event_dates_shifts', 'clsShifts');
      $this->load->model('vols/mvol_event_dates_shifts_vols', 'clsSV');

      $this->clsShifts->loadShiftsViaEventShiftID($lEventShiftID);
      if (isset($_REQUEST['chkVol'])){
         foreach ($_REQUEST['chkVol'] as $lVolID){
            $this->clsSV->lAddVolToShift($lEventShiftID, (integer)$lVolID, '');
            ++$lNumVolsSel;
         }
      }
      $this->session->set_flashdata('msg', $lNumVolsSel
                         .' Volunteer'.($lNumVolsSel==1 ? '' : 's')
                         .' added to shift <b>'.htmlspecialchars($this->clsShifts->shifts[0]->strShiftName).'</b>');

      redirect('volunteers/event_dates_view/viewDates/'.$lEventDateID);
   }

   function removeVolsFromShift($lVolAssignID, $lEventDateID, $lShiftID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lVolAssignID,  'volunteer assignment ID');
      verifyID($this, $lEventDateID,  'event date ID');
      verifyID($this, $lShiftID, 'shift ID');
      
      $lVolAssignID = (integer)$lVolAssignID;
      $lEventDateID = (integer)$lEventDateID;
      $lShiftID     = (integer)$lShiftID;
      
      $this->load->model('vols/mvol_event_dates_shifts', 'clsShifts');
      $this->clsShifts->loadShiftsViaEventShiftID($lShiftID);

      $this->load->model('vols/mvol_event_dates_shifts_vols', 'clsSV');
      $this->clsSV->removeVolFromShift($lVolAssignID);
      
      $this->session->set_flashdata('msg', 'The selected volunteer was removed from shift <b>'
                                    .htmlspecialchars($this->clsShifts->shifts[0]->strShiftName).'</b>');

      redirect('volunteers/event_dates_view/viewDates/'.$lEventDateID);      
   }

}













