<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class enroll_attend_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function addEditE($lEnrollID, $lClientID, $lCProgID, $bUseReturnPath='false'){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      $bUseReturnPath = $bUseReturnPath == 'true';
      
         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('personalization/muser_fields',          'clsUF');
      $this->load->model ('client_features/mcprograms', 'cprograms');
      $this->load->model ('admin/mpermissions',                    'perms');
      $this->load->helper('img_docs/image_doc');

         // ensure the client isn't currently enrolled in this program
      $lClientID = (int)$lClientID;
      $this->cprograms->loadClientProgramsViaCPID((int)$lCProgID);
      $cprog = &$this->cprograms->cprogs[0];
      if ($this->cprograms->bIsClientActivelyEnrolledInProgram(
                 $lClientID, $cprog, $lNumEnrollments, $erecs)){
         $this->session->set_flashdata('error', 'Unable to enroll client in <b>'
                   .htmlspecialchars($cprog->strProgramName).'</b>. The client is
                   currently enrolled (active).');
         redirect_Client($lClientID);
      }else {
         $this->addEditCommon(true, $lEnrollID, $lClientID, $lCProgID, null, $bUseReturnPath);
      }
   }

   public function addEditA($lAttendID, $lClientID, $lCProgID, $lERecID, $bUseReturnPath){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      $bUseReturnPath = $bUseReturnPath == 'true';
      $this->addEditCommon(false, $lAttendID, $lClientID, $lCProgID, $lERecID, $bUseReturnPath);
   }

   private function addEditCommon($bEnroll, $lFID, $lClientID, $lCProgID, 
                            $lERecID=null, $bUseReturnPath=false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lClientID, 'client ID');
      verifyID($this, $lCProgID,  'client program ID');

      $lFID      = (integer)$lFID;
      $lClientID = (integer)$lClientID;
      $lCProgID  = (integer)$lCProgID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('client_features/mcprograms',            'cprograms');
      $this->load->model ('personalization/muser_fields',          'clsUF');
      $this->load->model ('admin/mpermissions',                    'perms');
      $this->load->helper('img_docs/image_doc');

         // load the client program
      $this->cprograms->loadClientProgramsViaCPID($lCProgID);
      $cprog = &$this->cprograms->cprogs[0];
      if ($bEnroll){
         $lTableID = $cprog->lEnrollmentTableID;
      }else {
         $lTableID = $cprog->lAttendanceTableID;
      }
      
      redirect('admin/uf_multirecord/addEditMultiRecord/'
              .$lTableID.'/'.$lClientID.'/'.$lFID.'/'.(is_null($lERecID) ? '0' : $lERecID)
                .'/'.($bUseReturnPath ? 'true' : 'false'));
   }

   function qAddAttend($lCProgID, $lTableID, $lClientID, $lEnrollRecID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $displayData['js'] = '';

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('client_features/mcprograms',   'cprograms');
      $this->load->model  ('personalization/muser_fields', 'clsUF');
      $this->load->model  ('admin/mpermissions',           'perms');
      $this->load->model  ('img_docs/mimage_doc',          'clsImgDoc');
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
      $this->load->model  ('clients/mclients',             'clsClients');
      $this->load->helper ('clients/link_client_features');
      $this->load->helper ('img_docs/image_doc');
      $params = array('enumStyle' => 'enpRpt');
      $this->load->library('generic_rpt', $params);

      $displayData['lCProgID']     = $lCProgID     = (integer)$lCProgID;
      $displayData['lTableID']     = $lTableID     = (integer)$lTableID;
      $displayData['lClientID']    = $lClientID    = (integer)$lClientID;
      $displayData['lEnrollRecID'] = $lEnrollRecID = (integer)$lEnrollRecID;

         // load the client program
      $this->cprograms->loadClientProgramsViaCPID($lCProgID);
      $displayData['cprog'] = &$this->cprograms->cprogs[0];

         // client info
      $this->clsClients->loadClientsViaClientID($lClientID);
      $displayData['client'] = &$this->clsClients->clients[0];

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['contextSummary'] = $this->clsClients->strClientHTMLSummary(0);
      $displayData['pageTitle']      = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                .' | '.anchor('clients/client_record/view/'.$lClientID, 'Client Record', 'class="breadcrumb"');

      $displayData['title']          = CS_PROGNAME.' | Client Programs';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'cprograms/cprograms_enroll_nextstep_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }



}


