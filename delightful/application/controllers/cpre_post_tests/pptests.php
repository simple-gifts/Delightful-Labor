<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pptests extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function overview(){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $glUserID;

      if (!bTestForURLHack('showClients')) return;

      $displayData = array();
      $displayData['js'] = '';

         //-------------------------------------
         // models, libraries, and helpers
         //-------------------------------------
      $this->load->model ('personalization/muser_fields',     'clsUF');
      $this->load->model ('admin/mpermissions',               'perms');
      $this->load->model ('client_features/mcpre_post_tests', 'cpptests');
      $this->load->model ('util/mlist_generic',               'clsList');
      $this->load->helper('clients/link_client_features');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //--------------------------
         // Stripes
         //--------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $this->cpptests->loadPPCatsAndTests($displayData['lNumCats'], $displayData['ppcats'], false);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                .' | Client Pre/Post Tests';

      $displayData['title']          = CS_PROGNAME.' | Clients';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'cpre_post_tests/pp_overview_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }



}