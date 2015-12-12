<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class uf_debug extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }


   function ufFieldDump($lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $displayData['js'] = '';
      
      $displayData['lTableID'] = $lTableID = (int) $lTableID;
      
         //-----------------------------
         // models and helpers
         //-----------------------------
      $this->load->helper ('dl_util/web_layout');
      $this->load->model('personalization/muser_schema', 'cUFSchema');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      
      $this->cUFSchema->loadUFSchemaSingleTable($lTableID);
      
      $displayData['schema'] = &$this->cUFSchema->schema;
      
         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;
      
      
//      $this->cUFSchema->tableInfoViaUserTableID($lTableID, $tableInfo);
/* -------------------------------------      
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$this->cUFSchema->schema   <pre>');
echo(htmlspecialchars( print_r($this->cUFSchema->schema, true))); echo('</pre></font><br>');
// ------------------------------------- */
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Debug';

      $displayData['title']          = CS_PROGNAME.' | Debug';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'debug/debug_uftable_view';
      $this->load->vars($displayData);
      $this->load->view('template');
      
   }
   
   
   
   
}