<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cprograms extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function overview(){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;

      $displayData = array();
      $displayData['js'] = '';

         //-------------------------------------
         // models, libraries, and helpers
         //-------------------------------------
      $this->load->model('client_features/mcprograms',   'cprograms');
      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->model ('admin/mpermissions', 'perms');
      $this->load->helper('clients/link_client_features');

      $this->cprograms->loadClientPrograms();
      $displayData['lNumCProgs'] = $lNumCProgs = $this->cprograms->lNumCProgs;
      $displayData['cprogs']     = &$this->cprograms->cprogs;
      
      if ($lNumCProgs > 0){
         foreach ($this->cprograms->cprogs as $cprog){
            $cprog->lNumEFields = $this->clsUF->lNumUF_TableFields($cprog->lEnrollmentTableID);
            $cprog->lNumAFields = $this->clsUF->lNumUF_TableFields($cprog->lAttendanceTableID);
         }
      }

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                .' | Client Programs';

      $displayData['title']          = CS_PROGNAME.' | Admin';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'cprograms/cprograms_overview_view';
      $this->load->vars($displayData);
      $this->load->view('template');
  }

}
