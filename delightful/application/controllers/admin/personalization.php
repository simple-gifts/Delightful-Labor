<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class personalization extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);      
   }

	public function overview($enumOpenBlock='')	{
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      if (!bTestForURLHack('adminOnly')) return;

      $displayData = array();
      $displayData['js'] = '';
      
         //------------------------------------------------
         // models & helpers
         //------------------------------------------------
      $this->load->model ('personalization/muser_fields', 'clsUF');
      $this->load->model ('admin/mpermissions',           'perms');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('js/div_hide_show');
      $displayData['js'] .= showHideDiv();
      $displayData['enumOpenBlock'] = trim($enumOpenBlock);
      
      $idx = 0;
      $displayData['uf'] = array();
      foreach ($this->clsUF->enumTableTypes as $enumTType){
         
         $this->clsUF->setTType($enumTType);
         $displayData['uf'][$idx] = new stdClass;
         $displayData['uf'][$idx]->strTTypeLabel = $this->clsUF->strTTypeLabel;
         $displayData['uf'][$idx]->enumTType     = $enumTType;
         
         $this->clsUF->loadTablesViaTType(false);
         $displayData['uf'][$idx]->lNumTables = $lNumTables = $this->clsUF->lNumTables;
         if ($lNumTables > 0){
            $displayData['uf'][$idx]->userTables = $this->clsUF->userTables;
            $jIdx = 0;
            foreach ($this->clsUF->userTables as $clsUTable){
               $displayData['uf'][$idx]->userTables[$jIdx]->lNumFields = 
                                  $this->clsUF->lNumUF_TableFields($clsUTable->lKeyID);
                 
               ++$jIdx;
            }
         }
         ++$idx;
      }      
      
      $displayData['title']        = CS_PROGNAME.' | Personalization';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"').' | Personalization';
      $displayData['mainTemplate'] = 'personalization/overview';
      $displayData['nav'] = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
	}
}

/* End of file personalization.php */
/* Location: ./application/controllers/admin/personalization.php */