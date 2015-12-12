<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class status_cat extends CI_Controller {
//----------------------------------------------------------------------

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function index(){

   }

   public function addEdit($id){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      $this->load->helper('dl_util/verify_id');
      verifyIDsViaType($this, CENUM_CONTEXT_STATUSCAT, $id, true);

      $id = (integer)$id;

      $bNew = $id <= 0;
      $this->load->model('clients/mclient_status', 'clsClientStat');
      $this->clsClientStat->loadClientStatCats(true, true, $id);

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtStatCatName',      'Status Category', 'trim|required|'
                                                                .'callback_verifyUniqueStatCat['.$id.']');
		$this->form_validation->set_rules('txtNotes');

		if ($this->form_validation->run() == FALSE){
         $displayData = array();
         $displayData['formD'] = new stdClass;
         $this->load->library('generic_form');

         if ($bNew){
            $this->clsClientStat->loadClientStatCats(true, true, -1);
            $this->clsClientStat->statCats[0]->lKeyID     = -1;
            $this->clsClientStat->statCats[0]->strCatName = '';
         }

         if (validation_errors()==''){
            $displayData['formD']->txtStatCatName = htmlspecialchars($this->clsClientStat->statCats[0]->strCatName);
            $displayData['formD']->txtNotes       = htmlspecialchars($this->clsClientStat->statCats[0]->strDescription);
         }else {
            setOnFormError($displayData);
            $displayData['formD']->txtStatCatName = set_value('txtStatCatName');
            $displayData['formD']->txtNotes       = set_value('txtNotes');
         }

               //--------------------------
               // breadcrumbs
               //--------------------------
            $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                      .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb" ')
                                      .' | '.anchor('admin/admin_special_lists/clients/statCatView', 'Client Status Categories', 'class="breadcrumb" ')
                                      .' | '.($bNew ? 'Add new status category' : 'Edit status category');

            $displayData['title']          = CS_PROGNAME.' | Lists';
            $displayData['nav']            = $this->mnav_brain_jar->navData();

            $displayData['id']             = $id;
            $displayData['bNew']           = $bNew;
            $displayData['statCats']       = $this->clsClientStat->statCats;

            $displayData['mainTemplate']   = 'client/client_status_cat_add_edit';
            $this->load->vars($displayData);
            $this->load->view('template');
      }else {
         $this->clsClientStat->statCats[0]->strCatName     = trim($_POST['txtStatCatName']);
         $this->clsClientStat->statCats[0]->strDescription = trim($_POST['txtNotes']);

         if ($id==0){
            $id = $this->clsClientStat->addNewClientStatusCat();
            $this->session->set_flashdata('msg', 'Your status category was added');
         }else {
            $this->clsClientStat->updateClientStatusCat($id);
            $this->session->set_flashdata('msg', 'Your status category was updated');
         }
         redirect('admin/admin_special_lists/clients/statCatView');
      }
   }

   function verifyUniqueStatCat($strName, $strID){
      $lID = (int)$strID;
      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                trim($strName), 'csc_strCatName',
                $lID, 'csc_lKeyID',
                true, 'csc_bRetired',
                false, null, null,
                false, null, null,
                'client_status_cats')){
         $this->form_validation->set_message('verifyUniqueStatCat',
                   'This <b>Client Status Category</b> is already being used.');
         return(false);
      }else {
         return(true);
      }
   }

   function removeCat($id){
      if (!bTestForURLHack('showClients')) return;
      $lCatID      = (integer)$id;
      $this->load->model('clients/mclient_status', 'clsClientStat');
      $this->clsClientStat->removeCategory($lCatID);
      $this->session->set_flashdata('msg', 'Your selected status category was removed');
      redirect('admin/admin_special_lists/clients/statCatView');
   }

}