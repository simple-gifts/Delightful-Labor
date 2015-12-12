<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class uf_ddl_clone extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function clone01($lTableID, $lFieldID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();
      $displayData['js'] = '';

      $displayData['lTableID'] = $lTableID = (integer)$lTableID;
      $displayData['lFieldID'] = $lFieldID = (integer)$lFieldID;

         //---------------------------------
         // models and helpers
         //---------------------------------
      $params = array('enumStyle' => 'enpRpt');
      $this->load->library('generic_rpt', $params);
      $this->load->model('personalization/muser_fields',    'clsUF');
      $this->load->model('personalization/muser_ddl_clone', 'cClone');
      $this->load->model ('admin/mpermissions',             'perms');
      $this->load->helper('clients/client_program');
      $this->load->helper('personalization/link_personalization');
      $this->load->helper('dl_util/web_layout');

         //-------------------------------------
         // stripes
         //-------------------------------------
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $this->clsUF->lTableID = $lTableID;
      $this->clsUF->loadTableViaTableID();
      $enumTType = $this->clsUF->userTables[0]->enumTType;
      $this->clsUF->uf_ddl_info($lFieldID);
      $this->clsUF->loadSingleField($lFieldID);

      $displayData['targetDDLInfo'] = &$this->clsUF->clsDDL_Info;

         // http://stackoverflow.com/questions/21498038/php-pointer-returns-null
      setClientProgFields($displayData, $bClientProg, $lCProgID, $cprog, $enumTType, $lTableID);

      $this->cClone->loadAllDDLMultDDL($displayData['lNumDDLs'], $displayData['ddls']);

         //-----------------------------
         // breadcrumbs and headers
         //-----------------------------
      if ($bClientProg){
         $displayData['title']        = CS_PROGNAME.' | Client Programs';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                 .' | '.anchor('cprograms/cprograms/overview', 'Client Programs', 'class="breadcrumb"')
                                 .' | '.anchor('cprograms/cprog_record/view/'.$lCProgID, htmlspecialchars($cprog->strProgramName), 'class="breadcrumb"')
                                 .' | '.anchor('admin/uf_fields/view/'.$lTableID, 'Fields', 'class="breadcrumb"')
                                 .' | Clone Drop-down List';
      }else {
         $displayData['title']        = CS_PROGNAME.' | Personalization';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                 .' | '.anchor('admin/personalization/overview', 'Personalization', 'class="breadcrumb"')
                                 .' | '.anchor('admin/uf_fields/view/'.$lTableID, 'Fields', 'class="breadcrumb"')
                                 .' | Clone Drop-down List';
      }
      $displayData['mainTemplate'] = 'personalization/ddl_clone_selection_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function clone02($lTableID, $lFieldID, $lSourceDDLFID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;

      $lTableID      = (integer)$lTableID;
      $lFieldID      = (integer)$lFieldID;
      $lSourceDDLFID = (integer)$lSourceDDLFID;

         //---------------------------------
         // models and helpers
         //---------------------------------
      $this->load->model('personalization/muser_ddl_clone', 'cClone');
      
      $this->cClone->cloneDDL($lFieldID, $lSourceDDLFID);
      
      $this->session->set_flashdata('msg', 'The list was cloned!');
      redirect('admin/uf_ddl/configDDL/'.$lTableID.'/'.$lFieldID);


   }


}



