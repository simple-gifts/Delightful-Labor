<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ACO extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function index(){
   }

	public function view()	{
      if (!bTestForURLHack('adminOnly')) return;
      
      $displayData = array();
      $displayData['title']        = CS_PROGNAME.' | Accounting Countries';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                              .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                              .' | ACO';
      $displayData['mainTemplate'] = 'admin/alist_aco_view';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->model('admin/madmin_aco', 'aco');
      $this->aco->loadCountries(false, false, false, null);
      $displayData['countries'] = $this->aco->countries;
      $displayData['info'] = 'You can define the countries supported by the '.CS_PROGNAME.' here.';

      $this->load->vars($displayData);
      $this->load->view('template');

	}

   public function update(){
      if (!bTestForURLHack('adminOnly')) return;
      $this->load->model('admin/madmin_aco', 'aco');

      if (!isset($_POST['chkACO'])){
         $this->session->set_flashdata('error', '<b>ERROR:</b> No countries were selected. Please select one or more countries.</font>');
         redirect('admin/admin_special_lists/aco/view');
      }

      $lDefaultACO = (integer)$_POST['rdoDefault'];
      $lInUseIDs = array(); $bValidDef = false;
      foreach ($_REQUEST['chkACO'] as $lACOID){
         array_push ($lInUseIDs, $lACOID);
         if ($lDefaultACO==$lACOID) $bValidDef = true;
      }

      if (!$bValidDef){
         $this->session->set_flashdata('error', '<b>ERROR:</b> No default country was selected. Please try again.');
         redirect('admin/admin_special_lists/aco/view');
      }
      
      $this->load->model('admin/madmin_aco', 'aco');
      $this->aco->clearInUse();      

      foreach ($lInUseIDs as $lInUseID){
         $this->aco->addInUse($lInUseID, $lInUseID==$lDefaultACO);
      }
      $this->session->set_flashdata('msg', 'Update successful!');
      redirect('admin/admin_special_lists/aco/view');
   }
   
   
   
}

/* End of file lists.php */
/* Location: ./application/controllers/admin/lists.php */