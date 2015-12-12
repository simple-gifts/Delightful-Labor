<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class client_voc extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function addEdit($lVocID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      $this->load->helper('dl_util/verify_id');
      if ($lVocID != '0') verifyID($this, $lVocID, 'client vocabulary ID');

      $lVocID = (integer)$lVocID;
      $bNew        = $lVocID <= 0;

      $this->load->model('clients/mclient_vocabulary', 'clsCVoc');
      $this->clsCVoc->lVocID = $lVocID;
      $this->clsCVoc->loadClientVocabulary(true, true);

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtVocName', 'Vocabulary Name',     'xss_clean|trim|required|'
                                                .'callback_verifyUniqueVoc['.$lVocID.']');
		$this->form_validation->set_rules('txtVClS',    'Client (singular)',   'xss_clean|trim|required');
		$this->form_validation->set_rules('txtVClP',    'Client (plural)',     'xss_clean|trim|required');
		$this->form_validation->set_rules('txtVSpS',    'Sponsor (singular)',  'xss_clean|trim|required');
		$this->form_validation->set_rules('txtVSpP',    'Sponsor (plural)',    'xss_clean|trim|required');
		$this->form_validation->set_rules('txtLocS',    'Location (singular)', 'xss_clean|trim|required');
		$this->form_validation->set_rules('txtLocP',    'Location (plural)',   'xss_clean|trim|required');

		if ($this->form_validation->run() == FALSE){
         $displayData = array();
         $displayData['formD'] = new stdClass;
         $this->load->library('generic_form');
         $displayData['vocs'] = $this->clsCVoc->vocs[0];

         if (validation_errors()==''){
            $displayData['formD']->txtVocName    = htmlspecialchars($this->clsCVoc->vocs[0]->strVocTitle);
            $displayData['formD']->txtVClS       = htmlspecialchars($this->clsCVoc->vocs[0]->strClientS);
            $displayData['formD']->txtVClP       = htmlspecialchars($this->clsCVoc->vocs[0]->strClientP);
            $displayData['formD']->txtVSpS       = htmlspecialchars($this->clsCVoc->vocs[0]->strSponsorS);
            $displayData['formD']->txtVSpP       = htmlspecialchars($this->clsCVoc->vocs[0]->strSponsorP);
            $displayData['formD']->txtLocS       = htmlspecialchars($this->clsCVoc->vocs[0]->strLocS);
            $displayData['formD']->txtLocP       = htmlspecialchars($this->clsCVoc->vocs[0]->strLocP);
         }else {
            setOnFormError($displayData);
            $displayData['formD']->txtVocName    = set_value('txtVocName');
            $displayData['formD']->txtVClS       = set_value('txtVClS');
            $displayData['formD']->txtVClP       = set_value('txtVClP');
            $displayData['formD']->txtVSpS       = set_value('txtVSpS');
            $displayData['formD']->txtVSpP       = set_value('txtVSpP');
            $displayData['formD']->txtLocS       = set_value('txtLocS');
            $displayData['formD']->txtLocP       = set_value('txtLocP');

         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                   .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb" ')
                                   .' | '.anchor('admin/admin_special_lists/clients/vocView', 'Client Vocabulary', 'class="breadcrumb" ')
                                   .' | '.($bNew ? 'Add new vocabulary' : 'Edit vocabulary');

         $displayData['title']          = CS_PROGNAME.' | Lists';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['lVocID']         = $lVocID;
         $displayData['bNew']           = $bNew;
         $displayData['mainTemplate']   = 'client/client_voc_add_edit_view';

         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
            $this->clsCVoc->vocs[0]->strVocTitle = xss_clean(trim($_POST['txtVocName']));
            $this->clsCVoc->vocs[0]->strClientS  = xss_clean(trim($_POST['txtVClS']));
            $this->clsCVoc->vocs[0]->strClientP  = xss_clean(trim($_POST['txtVClP']));
            $this->clsCVoc->vocs[0]->strSponsorS = xss_clean(trim($_POST['txtVSpS']));
            $this->clsCVoc->vocs[0]->strSponsorP = xss_clean(trim($_POST['txtVSpP']));
            $this->clsCVoc->vocs[0]->strLocS     = xss_clean(trim($_POST['txtLocS']));
            $this->clsCVoc->vocs[0]->strLocP     = xss_clean(trim($_POST['txtLocP']));
            $this->clsCVoc->vocs[0]->strSubLocS  = '';
            $this->clsCVoc->vocs[0]->strSubLocP  = '';
            $this->clsCVoc->vocs[0]->bProtected  = false;

         if ($lVocID==0){
            $lVocID = $this->clsCVoc->addNewClientVoc();
            $this->session->set_flashdata('msg', 'Your vocabulary entry was added');
         }else {
            $this->clsCVoc->vocs[0]->lKeyID   = $lVocID;
            $this->clsCVoc->vocs[0]->bRetired = false;
            $this->clsCVoc->updateClientVoc();
            $this->session->set_flashdata('msg', 'Your vocabulary entry was updated');
         }
         redirect('admin/admin_special_lists/clients/vocView');
      }
   }

   function verifyUniqueVoc($strName, $strID){
      $lID = (int)$strID;
      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                trim($strName), 'cv_strVocTitle',
                $lID, 'cv_lKeyID',
                true, 'cv_bRetired',
                false, null, null,
                false, null, null,
                'lists_client_vocab')){
         $this->form_validation->set_message('verifyUniqueVoc',
                   'This <b>Client Vocabulary</b> is already being used.');
         return(false);
      }else {
         return(true);
      }
   }

   function removeVoc($id){
      if (!bTestForURLHack('showClients')) return;
      $id = (integer)$id;
      $this->load->model('clients/mclient_vocabulary', 'clsCVoc');
      $this->clsCVoc->retireVoc($id);
      $this->session->set_flashdata('msg', 'The selected vocabulary was removed');
      redirect('admin/admin_special_lists/clients/vocView');
   }

}