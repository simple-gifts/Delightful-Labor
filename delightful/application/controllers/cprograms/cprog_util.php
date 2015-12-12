<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class cprog_util extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function moveAttendRecs($lClientID, $lCProgID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      $displayData = array();
      $displayData['lCID'] = $lClientID = (integer)$lClientID;
      $displayData['js'] = '';

      $displayData['lClientID'] = $lClientID = (int)$lClientID;
      $displayData['lCProgID']  = $lCProgID  = (int)$lCProgID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('client_features/mcprograms', 'cprograms');

         //-------------------------------------
         // load the client info
         //-------------------------------------
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('clients/mclients', 'clsClients');
      $this->load->helper('clients/client');
      $this->clsClients->loadClientsViaClientID($lClientID);

         /*-------------------------------------
            load the client summary block
         -------------------------------------*/
      $this->load->model  ('img_docs/mimage_doc',   'clsImgDoc');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params, 'generic_rpt');
      $displayData['clsRpt'] = $this->generic_rpt;
      $displayData['contextSummary'] = $this->clsClients->strClientHTMLSummary(0);

      $this->cprograms->loadClientProgramsViaCPID($lCProgID);
      $cprog = &$this->cprograms->cprogs[0];

      $cprog->bEnrolled = $this->cprograms->bIsClientInProgram(
                               $lClientID, $cprog, $cprog->lNumEnrollments, $cprog->erecs);

      if ($cprog->lNumEnrollments > 0){
         foreach ($cprog->erecs as $erec){
            $lERecID = $erec->lKeyID;
            $this->cprograms->loadBaseARecViaERecID($cprog, $lERecID,
                                                 $erec->lNumARecs, $erec->arecs);
         }
      }
      $displayData['cprog'] = $cprog;


         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                .' | '.anchor('clients/client_record/view/'.$lClientID, 'Client Record', 'class="breadcrumb"')
                                .' | Move attendance records';

      $displayData['title']          = CS_PROGNAME.' | Clients';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'cprograms/move_attendance_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function moveARecs($lClientID, $lCProgID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lClientID = (int)$lClientID;
      $lCProgID  = (int)$lCProgID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('client_features/mcprograms',           'cprograms');

      $this->cprograms->loadClientProgramsViaCPID($lCProgID);
      $cprog = &$this->cprograms->cprogs[0];

      $lSourceERecID = (int)$_POST['lERecID'];
      $bAnyXfer = false;
      foreach ($_POST as $strVar=>$post){
         if (substr($strVar, 0, 7)=='ddlXfer'){
            $lSourceARecID = (int)substr($strVar, 8);
            $lDestERecID   = (int)$post;
            if ($lDestERecID > 0){
               $this->cprograms->moveAttendance(
                      $cprog->strAttendanceTable, $cprog->strATableFNPrefix, $lSourceARecID, $lDestERecID);
               $bAnyXfer = true;
            }
         }
      }

      if ($bAnyXfer){
         $this->session->set_flashdata('msg', 'Your selected attendance records were transferred!');
      }else {
         $this->session->set_flashdata('error', 'No attendance recorded were selected for transfer.');
      }
      redirect('cprograms/cprog_util/moveAttendRecs/'.$lClientID.'/'.$lCProgID);
  }
  
  

}