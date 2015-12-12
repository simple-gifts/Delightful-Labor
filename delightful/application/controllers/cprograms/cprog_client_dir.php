<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cprog_client_dir extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function optsA($lCProgID){
   //--------------------------------------------------------------------
   //
   //--------------------------------------------------------------------
      global $gdteNow, $gUserPerms, $glUserID;

      $displayData = array();
      $displayData['js'] = '';

      $displayData['lCProgID'] = $lCProgID = (int)$lCProgID;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      if (CB_AAYHF) $this->load->helper('aayhf/aayhf_links');         
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('js/jq_month_picker');
      $this->load->model ('admin/mpermissions',           'perms');
      $this->load->model ('client_features/mcprograms',   'cprograms');
      $this->load->model ('personalization/muser_fields', 'clsUF');

      $this->loadClientProgram($displayData, $lCProgID);
      $cprog = &$displayData['cprog'];
      
      $this->perms->loadUserAcctInfo($glUserID, $acctAccess);
      if (!$this->perms->bDoesUserHaveAccess($acctAccess, $cprog->lNumPerms, $cprog->perms)){
         badBoyRedirect('You do not have access to this client program.');
         return;      
      }

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtMonth',    'Report Month', 'trim|required|callback_reportMonth');
      $this->form_validation->set_rules('chkCNotes',   'Case Notes');
      $this->form_validation->set_rules('chkDuration', 'Duration');
      $this->form_validation->set_rules('chkActivity', 'Activity');
      $this->form_validation->set_rules('chkALink',    $cprog->strSafeAttendLabel.' Link');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;

         $this->load->library('generic_form');
         if (validation_errors()==''){
            $displayData['txtMonth']  = date('m/Y', $gdteNow);
            $displayData['formData']->bShowCNotes   = false;
            $displayData['formData']->bShowDuration = false;
            $displayData['formData']->bShowActivity = false;
            $displayData['formData']->bShowALink    = true;
         }else {
            setOnFormError($displayData);
            $displayData['txtMonth']  = set_value('txtMonth');
            $displayData['formData']->bShowCNotes   = set_value('chkCNotes')  =='true';
            $displayData['formData']->bShowDuration = set_value('chkDuration')=='true';
            $displayData['formData']->bShowActivity = set_value('chkActivity')=='true';
            $displayData['formData']->bShowALink    = set_value('chkALink')   =='true';
         }

            //------------------------------------------------
            // breadcrumbs / page setup
            //------------------------------------------------
         $displayData['js'] .= strMonthPicker(true);

         $displayData['mainTemplate'] = 'cprograms/cprog_attend_dir_opts_view';
         $displayData['pageTitle']    =
                                           anchor('main/menu/client',              'Clients',         'class="breadcrumb"')
                                    .' | '.anchor('cprograms/cprog_dir/cprogList', 'Client Programs', 'class="breadcrumb"')
                                    .' | '.$cprog->strSafeAttendLabel.' Directory Options';

         $displayData['title']        = CS_PROGNAME.' | Client Programs';
         $displayData['nav']          = $this->mnav_brain_jar->navData();

         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
            // show attendance report
         $strDate = trim($_POST['txtMonth']);
         $monYr = explode('/', $strDate);
         redirect('cprograms/cprog_client_dir/attend/'.$lCProgID
                          .'/'.(int)$monYr[0].'/'.(int)$monYr[1]
                          .'/'.(@$_POST['chkCNotes']  =='true' ? 'true' : 'false')
                          .'/'.(@$_POST['chkDuration']=='true' ? 'true' : 'false')
                          .'/'.(@$_POST['chkActivity']=='true' ? 'true' : 'false')
                          .'/'.(@$_POST['chkALink']   =='true' ? 'true' : 'false'));
      }
   }

   function attend($lCProgID, $lMonth, $lYear, $bCNotes, $bDuration, $bActivity, $bALink){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
   
      $lCProgID  = (int)$lCProgID;
      $lMonth    = (int)$lMonth;
      $lYear     = (int)$lYear;
      $bCNotes   = $bCNotes   == 'true';
      $bDuration = $bDuration == 'true';
      $bActivity = $bActivity == 'true';
      $bALink    = $bALink    == 'true';

      if ($lMonth <= 0){
         $lMonth = 12;
         --$lYear;
      }
      if ($lMonth > 12){
         $lMonth = 1;
         ++$lYear;
      }

      $displayData = array();
      $displayData['js'] = '';
      
      $displayData['lCProgID'] = $lCProgID = (int)$lCProgID;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      if (CB_AAYHF) $this->load->helper('aayhf/aayhf_links');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/time_date');
      $this->load->model ('admin/mpermissions',           'perms');
      $this->load->helper('clients/client_program');
      $this->load->helper('clients/link_client_features');
      $this->load->helper('dl_util/string');
      $this->load->model ('client_features/mcprograms',   'cprograms');
      $this->load->model ('personalization/muser_fields', 'clsUF');
      $this->load->model ('util/mbuild_on_ready',         'clsOnReady');
      $this->load->helper('reports/month_at_a_glance');

         //--------------------------
         // Stripes
         //--------------------------
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $this->loadClientProgram($displayData, $lCProgID);
      $cprog = &$displayData['cprog'];
      
      $this->perms->loadUserAcctInfo($glUserID, $acctAccess);
      if (!$this->perms->bDoesUserHaveAccess($acctAccess, $cprog->lNumPerms, $cprog->perms)){
         badBoyRedirect('You do not have access to this client program.');
         return;      
      }      

      $this->cprograms->enrollmentsActiveInMonth(
                    $cprog, $lMonth, $lYear, $displayData['lNumEnroll'], $enrollIDs);

      if ($displayData['lNumEnroll'] > 0){
         $strETable    = $cprog->strEnrollmentTable;
         $strEFNPrefix = $cprog->strETableFNPrefix;
         $strATable    = $cprog->strAttendanceTable;
         $strAFNPrefix = $cprog->strATableFNPrefix;

         $sqlWhere = 'AND ETable.'.$strEFNPrefix.'_lKeyID IN ('.implode(',', $enrollIDs).') ';
         $strOrder = 'cr_strLName, cr_strFName, cr_lKeyID ';
         $this->cprograms->loadBaseEFieldRecs($cprog,
                   $cprog->lNumERecs, $cprog->erecs, $sqlWhere, $strOrder);

            // load attendance for month
         $sqlWhereABase =
            ' AND YEAR ('.$strAFNPrefix.'_dteAttendance)='.$lYear
           .' AND MONTH('.$strAFNPrefix.'_dteAttendance)='.$lMonth."
              AND $strAFNPrefix".'_lEnrollID=';
         foreach ($cprog->erecs as $erec){
            $lEnrollID = $erec->lKeyID;
            $sqlWhereA = $sqlWhereABase.$lEnrollID.' ';
            $strOrderA = $strAFNPrefix.'_dteAttendance, '.$strAFNPrefix.'_lKeyID ';
            $this->cprograms->loadBaseAFieldRecs(
                     $cprog, $erec->lNumARecs, $erec->arecs, $sqlWhereA, $strOrderA);
         }
      }else {
         $cprog->lNumERecs = 0;
      }
      
         // the tulsa turn-around; create attendance table for month
      $attrib = new stdClass;
      $attrib->lMonth    = $lMonth;
      $attrib->lYear     = $lYear;
      $attrib->bCNotes   = $bCNotes;
      $attrib->bDuration = $bDuration;
      $attrib->bActivity = $bActivity;
      $attrib->bALink    = $bALink;

      $strBase1 = 'cprograms/cprog_client_dir/attend/'.$lCProgID.'/';
      $strBase2 =
                     '/'.($bCNotes   ? 'true' : 'false')
                    .'/'.($bDuration ? 'true' : 'false')
                    .'/'.($bActivity ? 'true' : 'false')
                    .'/'.($bALink    ? 'true' : 'false');

      maagNextPrevLinks($strBase1, $strBase2, $lMonth, $lYear, $maagInfo);
      $attrib->strLinkPrev = $maagInfo->strPrevMonth; 
      $attrib->strLinkNext = $maagInfo->strNextMonth;
      $displayData['strAttendance'] = strAttendanceTable($cprog, $attrib);

//      }else {
      if ($displayData['lNumEnroll'] == 0){

         $displayData['strAttendance'] .=
               '<br><br>There are no clients enrolled in <b>'
               .htmlspecialchars($cprog->strProgramName).'</b> during '
               .$lMonth.'/'.$lYear;
      }

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['mainTemplate'] = 'cprograms/cprog_attend_dir_view';
      $displayData['pageTitle']    =
                                        anchor('main/menu/client',              'Clients',         'class="breadcrumb"')
                                 .' | '.anchor('cprograms/cprog_dir/cprogList', 'Client Programs', 'class="breadcrumb"')
                                 .' | '.anchor('cprograms/cprog_client_dir/optsA/'.$lCProgID, $cprog->strSafeAttendLabel.' Directory Options', 'class="breadcrumb"')
                                 .' | Monthly '.$cprog->strSafeAttendLabel;

      $displayData['title']        = CS_PROGNAME.' | Client Programs';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }


   function reportMonth($strMonth){
      if (bValidPickerMonth($strMonth, $strErr)){
         return(true);
      }else {
         $this->form_validation->set_message('reportMonth', $strErr);
         return(false);
      }
   }

   public function optsE($lCProgID, $bActive='true'){
   //--------------------------------------------------------------------
   //
   //--------------------------------------------------------------------
      $bActive = $bActive=='true';
      $this->optsEandA($lCProgID, true, $bActive);
   }

   function optsEandA($lCProgID, $bEnroll, $bActive){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

      if (!bTestForURLHack('showClients')) return;

      if ($bEnroll){
         $strView  = 'cprog_enroll_dir_opts_view';
         $strLabel = 'Enrollment';
      }else {
         $strView  = 'cprog_attend_dir_opts_view';
         $strLabel = 'Attendance';
      }


      $displayData = array();
      $displayData['js'] = '';

      $displayData['lCProgID']    = $lCProgID = (int)$lCProgID;
      $displayData['bActive'] = $bActive;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('client_features/mcprograms',       'cprograms');
      $this->load->model ('admin/mpermissions',               'perms');
      $this->load->model ('personalization/muser_fields',     'clsUF');
      $this->load->helper('clients/link_client_features');
      $this->load->helper('dl_util/web_layout');

      $this->loadClientProgram($displayData, $lCProgID);
      if (!$this->perms->bDoesUserHaveAccess(
                                          $acctAccess, $cprog->lNumPerms, $cprog->perms)){
         badBoyRedirect('Your account settings do not allow you to access this client program.');
         return;
      }
      
      $cprog = &$displayData['cprog'];

      if ($bEnroll){
         $strETable    = $cprog->strEnrollmentTable;
         $strEFNPrefix = $cprog->strETableFNPrefix;

         $this->cprograms->loadBaseEFieldRecs($cprog, $cprog->lNumERecs,
                         $cprog->erecs,
                         ' AND '.($bActive ? '' : 'NOT ').' ETable.'.$strEFNPrefix.'_bCurrentlyEnrolled ',
                         'cr_strLName, cr_strFName, '.$strEFNPrefix.'_dteStart');
      }
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/client',              'Clients', 'class="breadcrumb"')
                                .' | '.anchor('cprograms/cprog_dir/cprogList', 'Client Programs', 'class="breadcrumb"')
                                .' | '.$strLabel;

      $displayData['title']          = CS_PROGNAME.' | Client Programs';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'cprograms/'.$strView;
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function loadClientProgram(&$displayData, $lCProgID){
   //----------------------------------------------------------
   // load the client programs
   //----------------------------------------------------------
      global $glUserID;

      $this->cprograms->loadClientProgramsViaCPID($lCProgID);
      $displayData['cprog'] = $cprog = &$this->cprograms->cprogs[0];
      $this->perms->loadUserAcctInfo($glUserID, $acctAccess);

   }


}


