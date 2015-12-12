<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ALists extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
//      setGlobals($this);      
      setGlobals($this);
   }

	public function showLists()	{
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();
      $displayData['title']        = CS_PROGNAME.' | Lists';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"') .' | Lists';
      $displayData['mainTemplate'] = 'admin/alist';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');

	}
}

/* End of file lists.php */
/* Location: ./application/controllers/admin/lists.php */