<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class attendance extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function cloneAttOpts($lCProgID, $lARecID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $genumDateFormat, $gbDateFormatUS;

      $displayData = array();
      $displayData['js'] = '';

      $displayData['lCProgID'] = $lCProgID = (int)$lCProgID;
      $displayData['lARecID']  = $lARecID  = (int)$lARecID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model('admin/mpermissions',           'perms');
      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->model('personalization/muser_schema', 'cUFSchema');
      $this->load->model('client_features/mcprograms',   'cprograms');
      $this->load->helper('dl_util/time_date');  // for date verification
      $this->load->helper ('dl_util/web_layout');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

         //------------------------------------
         // load the check/uncheck support
         //------------------------------------
      $this->load->helper('js/set_check_boxes');
      $displayData['js'] = insertCheckSet();
      $this->load->helper('js/verify_check_set');
      $displayData['js'] .= verifyCheckSet();


         // load client program
      $this->cprograms->loadClientProgramsViaCPID($lCProgID);
      $displayData['cprog'] = $cprog = &$this->cprograms->cprogs[0];
      $strProg = $cprog->strProgramName;
      $lATableID = $cprog->lAttendanceTableID;

      $this->cprograms->loadBaseARecViaARecID($cprog, $lARecID, $lNumARecs, $arecs);
      $displayData['arec'] = $arec = &$arecs[0];
      $lClientID = $arec->lClientID;
      $lERecID = $arec->lEnrollID;

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtADate',    'Attendance Date', 'trim|required'
                                                                    .'|callback_verifyDateValid');
      $this->form_validation->set_rules('chkSkipDups', 'Skip Duplicates', 'trim');

         // clients enrolled in this program - potential destinations of the clone
      $this->cprograms->clientsEnrolledViaProgID(
                     $lCProgID, $cprog, true, $lNumClients, $displayData['clients'],
                     true);

      if ($lNumClients > 0){
         foreach ($displayData['clients'] as $client){
            $client->bSelected = false;
         }
      }

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['bSkipDups'] = true;
            $displayData['formData']->txtADate = '';
         }else {
            setOnFormError($displayData);
            $displayData['bSkipDups'] = set_value('chkSkipDups')=='true';
            $displayData['formData']->txtADate     = set_value('txtADate');

            if (isset($_POST['chkClient'])){
               if ($lNumClients > 0){
                  foreach ($displayData['clients'] as $client){
                     $client->bSelected = in_array($client->lClientID, $_POST['chkClient']);
                  }
               }
            }
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      =
                          anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                .' | '.anchor('clients/client_record/view/'.$lClientID, 'Client Record', 'class="breadcrumb"')
                                .' | '.anchor('admin/uf_multirecord_view/viewMRViaFID/'.$lATableID.'/'.$lClientID.'/'.$lERecID,
                                            'Attendance: '.htmlspecialchars($strProg), 'class="breadcrumb"')
                                .' | Clone Attendance Record';

         $displayData['title']          = CS_PROGNAME.' | Client Program';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'cprograms/arec_clone_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $cloneOpts = new stdClass;
         $strADate   = trim($_POST['txtADate']);
         MDY_ViaUserForm($strADate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $cloneOpts->mdteAttendance = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);
         $cloneOpts->bSkipDups  = @$_POST['chkSkipDups']=='true';

         $cloneOpts->clients = array();
         $idx = 0;
         foreach ($_POST['chkClient'] as $strIDs){
            $cloneOpts->clients[$idx] = new stdClass;
            $IDs = explode('_', $strIDs);
            $cloneOpts->clients[$idx]->lClientID = (int)$IDs[0];
            $cloneOpts->clients[$idx]->lEnrollID = (int)$IDs[1];
            ++$idx;
         }

         $cloneOpts->lATableID = $cprog->lAttendanceTableID;
         $cloneOpts->lARecID   = $lARecID;
         $cloneOpts->lCProgID  = $lCProgID;

         $this->load->model('personalization/muser_fields',        'clsUF');
         $this->load->model('personalization/muser_fields_create', 'clsUFC');
         $this->load->model('personalization/muser_clone',         'cUFClone');
         $this->cUFClone->cloneAttendance($cloneOpts);

         $this->session->set_flashdata('msg', 'The specified attendance record was cloned.');
         redirect('cprograms/cprog_dir/viewEnroll/'.$lCProgID.'/true');
      }
   }

   function verifyDateValid($strDate){
      if (bValidVerifyDate($strDate)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyDateValid', 'The date you entered is not valid.');
         return(false);
      }
   }


}




