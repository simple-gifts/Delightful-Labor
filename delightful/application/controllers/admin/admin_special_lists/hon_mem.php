<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class hon_mem extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }
   
   function honView(){
      $this->honMemView(true);
   }
   
   function memView(){
      $this->honMemView(false);
   }
   
   function honMemView($bHon){
      if (!bTestForURLHack('adminOnly')) return;
      
      $displayData = array();
      $displayData['strLabel']     = $strLabel = $bHon ? 'Honorarium' : 'Memorial';
      $displayData['bHon']         = $bHon;

      $displayData['title']        = CS_PROGNAME.' | Honorariums/Memorials';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                              .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                              .' | '.$strLabel.'s';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      
      $this->load->model('donations/mhon_mem', 'clsHonMem');
      $this->clsHonMem->loadHonMem( ($bHon ? 'all - Hon Only' : 'all - Mem Only'));
      $displayData['lNumHonMem'] = $lNumHonMem = $this->clsHonMem->lNumHonMem;
      if ($lNumHonMem > 0){
         $displayData['honMemTable'] = $this->clsHonMem->honMemTable;
         $idx = 0;
         foreach($this->clsHonMem->honMemTable as $clsHM){
            $lHMID   = $clsHM->ghm_lKeyID;         
            $displayData['numGifts'][$idx] = $this->clsHonMem->lNumGiftsViaHMID($lHMID);
            ++$idx;
         }
         
      }
   
         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;
   
      $displayData['mainTemplate'] = 'admin/alist_hon_mem_view';
      $this->load->vars($displayData);
      $this->load->view('template');

   }
   
}
