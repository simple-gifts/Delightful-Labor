<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cprog_debug extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function showDebugDetails(){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $glUserID;
/*----------------------------
echo(__FILE__.' '.__LINE__.'<br>'."\n"); $this->output->enable_profiler(TRUE);
//----------------------------- */
      if (!bTestForURLHack('devOnly')) return;
      
      $displayData = array();
      $displayData['js'] = '';
      
         //-------------------------------------
         // models, libraries, and helpers
         //-------------------------------------
      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->model('client_features/mcprograms',   'cprograms');
      $this->load->model('admin/mpermissions', 'perms');
      $this->load->helper ('dl_util/web_layout');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();
          
      $this->cprograms->loadClientPrograms(true);
      $displayData['cprogs'] = $cprogs = &$this->cprograms->cprogs;
      $idx = 0;
      foreach ($cprogs as $cp){
         $lETableID = $cp->lEnrollmentTableID;
         $this->clsUF->lTableID = $lETableID;
         $this->clsUF->loadTableFields();
         $cp->efields = arrayCopy($this->clsUF->fields);
         
         $lATableID = $cp->lAttendanceTableID;
         $this->clsUF->lTableID = $lATableID;
         $this->clsUF->loadTableFields();
         $cp->afields = arrayCopy($this->clsUF->fields);
         
      }
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                .' | Client Programs';

      $displayData['title']          = CS_PROGNAME.' | Admin';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'cprograms/cprograms_debug_detail_view';
      $this->load->vars($displayData);
      $this->load->view('template');

   }
   
   
}


