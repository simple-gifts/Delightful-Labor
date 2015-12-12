<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class vol_directory extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($strLookupLetter='A', $lStartRec=0, $lRecsPerPage=50,
                 $strShowInactive='true'){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      $this->view_generic($strLookupLetter, $lStartRec, $lRecsPerPage,
                 $strShowInactive, false, null);
   }

   function viewViaRegFormID($lRegFormID,
                 $strLookupLetter='A', $lStartRec=0, $lRecsPerPage=50,
                 $strShowInactive='true'){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      $this->view_generic($strLookupLetter, $lStartRec, $lRecsPerPage,
                 $strShowInactive, true, $lRegFormID);
   }

   function view_generic($strLookupLetter='A', $lStartRec=0, $lRecsPerPage=50,
                 $strShowInactive='true', $bViaRegFormID, $lRegFormID=null){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      if (!bTestForURLHack('showPeople')) return;

      $strLookupLetter = urldecode($strLookupLetter);
      $bShowInactive = strtoupper($strShowInactive)=='TRUE';
      $displayData = array();

      if ($bViaRegFormID) $lRegFormID = (int)$lRegFormID;

         //------------------------------------------------
         // models / libraries / helpers
         //------------------------------------------------
      $this->load->helper('people/people');
      $this->load->helper('people/people_display');
      $this->load->model ('vols/mvol',        'clsVol');
      $this->load->model ('vols/mvol_skills', 'clsVolSkills');
      $this->load->helper('vols/vol');
      $this->load->helper('dl_util/time_duration_helper');
      $this->load->helper('dl_util/directory');
      $this->load->helper('dl_util/rs_navigate');
//      $this->load->helper('dl_util/email_web');
      $this->load->helper('dl_util/record_view');
      $this->load->helper('img_docs/link_img_docs');

      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('people/mpeople', 'clsPeople');
      $this->load->model('admin/madmin_aco', 'clsACO');

         //------------------------------------------------
         // sanitize the lookup letter
         //------------------------------------------------
      $displayData['strDirLetter'] = $strLookupLetter = strSanitizeLetter($strLookupLetter);

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

         //------------------------------------------------
         // define columns to display
         //------------------------------------------------
      initVolReportDisplay($displayData);
      $displayData['showFields']->bSkills = true;

         //------------------------------------------------
         // set up directory display
         //------------------------------------------------
      if ($bViaRegFormID){
         $this->load->model('vol_reg/mvol_reg', 'volReg');
         $this->volReg->loadVolRegFormsViaRFID($lRegFormID);
         $rRec = &$this->volReg->regRecs[0];
         $displayData['strRptTitle']  = 'Volunteer Directory By Registration Form ('.htmlspecialchars($rRec->strFormName).')';
         $displayData['strLinkBase']  = $strLinkBase = 'volunteers/vol_directory/viewViaRegFormID/'.$lRegFormID.'/';
         $strWhereExtraReg = " AND vol_lRegFormID = $lRegFormID ";
      }else {
         $displayData['strRptTitle']  = 'Volunteer Directory';
         $displayData['strLinkBase']  = $strLinkBase = 'volunteers/vol_directory/view/';
         $strWhereExtraReg = '';
      }

      $displayData['strDirLetter'] = $strLookupLetter;
      $displayData['strDirTitle']  = strDisplayDirectory(
                                         $strLinkBase, ' class="directoryLetters" ', $strLookupLetter,
                                         true, $lStartRec, $lRecsPerPage);

         //------------------------------------------------
         // total # people for this letter
         //------------------------------------------------
      $displayData['lNumRecsTot']   = $lNumRecsTot = lNumPeopleRecsViaLetter(
                           $strLookupLetter, CENUM_CONTEXT_VOLUNTEER, $bShowInactive, $strWhereExtraReg);
      $displayData['lNumVols']      = $lNumRecsTot;
      $displayData['strPeopleType'] = 'volunteer';

         //------------------------------------------------
         // load volunteer directory page
         //------------------------------------------------
      $strWhereExtra = $this->clsPeople->strWhereByLetter($strLookupLetter, CENUM_CONTEXT_PEOPLE)
                       .$strWhereExtraReg;
      if (!$bShowInactive){
         $strWhereExtra .= ' AND NOT vol_bInactive ';
      }

      $this->clsVol->loadVolDirectoryPage($strWhereExtra, $lStartRec, $lRecsPerPage);
      $displayData['lNumDisplayRows']      = $lNumVols = $this->clsVol->lNumVolRecs;
      $displayData['directoryRecsPerPage'] = $lRecsPerPage;
      $displayData['directoryStartRec']    = $lStartRec;
      if ($lNumVols){
         foreach ($this->clsVol->volRecs as $volRec){
            $this->clsVolSkills->lVolID = $lVolID = $volRec->lKeyID;
            $this->clsVolSkills->loadSingleVolSkills();
            $volRec->lNumJobSkills = $lNumSkills = $this->clsVolSkills->lNumSingleVolSkills;
            if ($lNumSkills > 0) {
               $volRec->volSkills     = arrayCopy($this->clsVolSkills->singleVolSkills);
            }
         }
      }
      $displayData['vols']                 = &$this->clsVol->volRecs;

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['mainTemplate'] = array('vols/vol_directory_view', 'vols/rpt_generic_vol_list');
      $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                              .' | Directory';

      $displayData['title']        = CS_PROGNAME.' | Volunteers';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function viewRecentViaRegFormID($lRegFormID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showPeople')) return;

      $displayData = array();
      $displayData['js'] = '';

      $lRegFormID = (int)$lRegFormID;

         //------------------------------------------------
         // models / libraries / helpers
         //------------------------------------------------
      $this->load->helper('people/people');
      $this->load->helper('people/people_display');
      $this->load->model ('vols/mvol',        'clsVol');
      $this->load->model ('vols/mvol_skills', 'clsVolSkills');
      $this->load->model ('vol_reg/mvol_reg', 'volReg');
      $this->load->model ('people/mpeople',   'clsPeople');
      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->helper('vols/vol');
      $this->load->helper('dl_util/time_duration_helper');
//      $this->load->helper('dl_util/email_web');

      $this->volReg->loadVolRegFormsViaRFID($lRegFormID);
      $displayData['rRec']  = $rRec = &$this->volReg->regRecs[0];
      $displayData['strRptTitle']  = 'Volunteer Directory By Registration Form ('.htmlspecialchars($rRec->strFormName).')';
      $displayData['strLinkBase']  = $strLinkBase = 'volunteers/vol_directory/viewViaRegFormID/'.$lRegFormID.'/';
      $strWhereExtra = " AND vol_lRegFormID = $lRegFormID ";
      $this->clsVol->strOrderExtra = 'ORDER BY vol_dteOrigin DESC, pe_strLName, pe_strFName, pe_strMName, vol_lKeyID ';
      $this->clsVol->loadVolDirectoryPage($strWhereExtra, 0, 50);

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $displayData['lNumVols']      = $lNumVols = $this->clsVol->lNumVolRecs;
      if ($lNumVols > 0){
         foreach ($this->clsVol->volRecs as $volRec){
            $this->clsVolSkills->lVolID = $lVolID = $volRec->lKeyID;
            $this->clsVolSkills->loadSingleVolSkills();
            $volRec->lNumJobSkills = $lNumSkills = $this->clsVolSkills->lNumSingleVolSkills;
            if ($lNumSkills > 0) {
               $volRec->volSkills     = arrayCopy($this->clsVolSkills->singleVolSkills);
            }
         }
      }
      $displayData['vols'] = $vols = &$this->clsVol->volRecs;

         // personalized tables used in the registration form
      $this->volReg->loadPTablesForDisplay($lRegFormID, $this->clsUF, false);
      $displayData['utables']     = &$this->volReg->utables;
      $displayData['lNumUTables'] = $this->volReg->lNumTables;

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['mainTemplate'] = 'vols/recent_registrations_view';
      $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                              .' | '.anchor('volunteers/registration/view', 'Registration Forms', 'class="breadcrumb"')
                              .' | Recent Registrations';

      $displayData['title']        = CS_PROGNAME.' | Volunteers';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }


}


