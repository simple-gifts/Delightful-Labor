<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_vol_job_skills extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function showOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('vols/mvol_skills', 'clsVolSkills');
//      $this->load->helper('dl_util/context');

         //------------------------------------
         // load the check/uncheck support
         //------------------------------------
      $this->load->helper('js/set_check_boxes');
      $displayData['js'] = insertCheckSet();
      $this->load->helper('js/verify_check_set');
      $displayData['js'] .= verifyCheckSet();

      $this->clsVolSkills->lVolID = -1;
      $this->clsVolSkills->loadVolSkills(true);
      $displayData['lNumSkillsList'] = $lNumSkillsList = $this->clsVolSkills->lNumSingleVolSkills;

      if ($lNumSkillsList > 0){
         $displayData['skillsList'] = &$this->clsVolSkills->singleVolSkills;
      }
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Volunteer Skills';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_vol_skills_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function saveOpts(){
      //---------------------------------------------------------------------
      //
      //---------------------------------------------------------------------
      $bShowAny = $_POST['rdoAnyAll'] == 'any';
      $bIncludeInactive = @$_POST['chkInactive'] == 'TRUE';

      $this->load->model('reports/mreports',    'clsReports');
      
      $groupList = array();
      $idx = 0;
      foreach ($_POST['chkSkill'] as $skillItem){
         $skillList[$idx] = (integer)$skillItem;
         ++$idx;
      }
  
      $reportAttributes = array(
                             'rptType'          => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'          => CENUM_REPORTNAME_VOLJOBSKILL,
                             'rptDestination'   => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'        => 0,
                             'lRecsPerPage'     => 50,
                             'bShowRecNav'      => true,
                             'viewFile'         => 'pre_generic_rpt_view',
                             'skillIDs'         => arrayCopy($skillList),
                             'bShowAny'         => $bShowAny,
                             'bIncludeInactive' => $bIncludeInactive);
   
      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }
























}