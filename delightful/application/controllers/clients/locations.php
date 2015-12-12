<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Locations extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function index(){

   }

   public function addEdit($idViaURL=0){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      if (!bTestForURLHack('showClients')) return;
      
      $id = (integer)$idViaURL;
      $bNew = $id <= 0;

      if (!$bNew){      
         $this->load->helper('dl_util/verify_id');
         verifyID($this, $idViaURL, 'client location ID');
      }      

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtLoc',      'Location', 'trim|required|'
                                                            .'callback_verifyUniqueLocation['.$id.']');
		$this->form_validation->set_rules('txtAddr1');
		$this->form_validation->set_rules('txtAddr2');
		$this->form_validation->set_rules('txtCity');
		$this->form_validation->set_rules('txtState');
		$this->form_validation->set_rules('txtZip');
		$this->form_validation->set_rules('chkAllowEMR');
		$this->form_validation->set_rules('txtDescription');
		$this->form_validation->set_rules('txtCountry');
      $this->form_validation->set_rules('chkSponProgs[]');

      $this->load->model('clients/mclient_locations', 'clsLoc');

		if ($this->form_validation->run() == FALSE){

         $displayData = array();
         $displayData['formD'] = new stdClass;
         $this->load->library('generic_form');
         $this->clsLoc->loadLocationRec($id);

            // load all available sponsorship programs
         $this->load->model('sponsorship/msponsorship_programs', 'clsSP');
         $this->clsSP->loadSponProgsGeneric(false);
         $displayData['sponProgs'] = $this->clsSP->sponProgs;

            // sponsor programs associated with this location
         $this->clsLoc->loadSupportedSponCats($id, $this->clsSP);
         $displayData['sponProgForLoc'] = $this->clsLoc->supportedSponProgs($id);

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                   .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb" ')
                                   .' | '.anchor('admin/admin_special_lists/clients/locationView', 'Client Locations', 'class="breadcrumb" ')
                                   .' | '.($bNew ? 'Add new location' : 'Edit location');

         $displayData['title']          = CS_PROGNAME.' | Lists';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['id']             = $id;
         $displayData['bNew']           = $bNew;

         if (validation_errors()==''){
            $displayData['formD']->txtLoc         = htmlspecialchars($this->clsLoc->strLocation);
            $displayData['formD']->txtAddr1       = htmlspecialchars($this->clsLoc->strAddress1);
            $displayData['formD']->txtAddr2       = htmlspecialchars($this->clsLoc->strAddress2);
            $displayData['formD']->txtCity        = htmlspecialchars($this->clsLoc->strCity);
            $displayData['formD']->txtState       = htmlspecialchars($this->clsLoc->strState);
            $displayData['formD']->txtZip         = htmlspecialchars($this->clsLoc->strPostalCode);
            $displayData['formD']->txtCountry     = htmlspecialchars($this->clsLoc->strCountry);
            $displayData['formD']->txtDescription = htmlspecialchars($this->clsLoc->strDescription);
            $displayData['formD']->chkAllowEMR    = $this->clsLoc->bEnableEMR;

            foreach ($displayData['sponProgs'] as $clsSP){
               $lSPID = $clsSP->lKeyID;
               $displayData['progInUse'][$lSPID] = (in_array($lSPID, $displayData['sponProgForLoc']) ? 'checked' : '');
            }

         }else {
            setOnFormError($displayData);
            $displayData['formD']->txtLoc         = set_value('txtLoc');
            $displayData['formD']->txtAddr1       = set_value('txtAddr1');
            $displayData['formD']->txtAddr2       = set_value('txtAddr2');
            $displayData['formD']->txtCity        = set_value('txtCity');
            $displayData['formD']->txtState       = set_value('txtState');
            $displayData['formD']->txtZip         = set_value('txtZip');
            $displayData['formD']->txtCountry     = set_value('txtCountry');
            $displayData['formD']->txtDescription = set_value('txtDescription');
            $displayData['formD']->chkAllowEMR    = set_value('txtDescription')=='true';

            foreach ($displayData['sponProgs'] as $clsSP){
               $lSPID = $clsSP->lKeyID;
               $displayData['progInUse'][$lSPID] = $this->form_validation->set_checkbox('chkSponProgs[]', $lSPID);
            }
         }

         $displayData['mainTemplate']   = 'client/client_loc_add_edit';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->clsLoc->strLocation    = trim($_POST['txtLoc']);
         $this->clsLoc->strCountry     = trim($_POST['txtCountry']);
         $this->clsLoc->strAddress1    = trim($_POST['txtAddr1']);
         $this->clsLoc->strAddress2    = trim($_POST['txtAddr2']);
         $this->clsLoc->strCity        = trim($_POST['txtCity']);
         $this->clsLoc->strState       = trim($_POST['txtState']);
         $this->clsLoc->strPostalCode  = trim($_POST['txtZip']);
         $this->clsLoc->strDescription = trim($_POST['txtDescription']);
         $this->clsLoc->bEnableEMR     = @$_POST['chkAllowEMR']=='true';
         $this->clsLoc->strWebLink     = '';
         $this->clsLoc->strNotes       = '';

         if ($id==0){
            $id = $this->clsLoc->lAddNewLocationRec();
            $this->session->set_flashdata('msg', 'Your client location was added');
         }else {
            $this->clsLoc->updateLocationRec($id);
            $this->session->set_flashdata('msg', 'Your client location was updated');
         }
            // save sponsorship programs associated with this location
         $this->clsLoc->clearSupportedSponProgs($id);
         if (isset($_POST['chkSponProgs'])){
            foreach($_POST['chkSponProgs'] as $lSPID){
               $this->clsLoc->setSupportedSponProgs($id, $lSPID);
            }
         }
         redirect('clients/locations/view/'.$id);
      }
   }
   
   function verifyUniqueLocation($strName, $strID){
      $lLocID = (int)$strID;
      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                trim($strName), 'cl_strLocation',
                $lLocID, 'cl_lKeyID',
                true,    'cl_bRetired',
                false, null, null,
                false, null, null,
                'client_location')){
         $this->form_validation->set_message('verifyUniqueLocation',
                   'This <b>Client Location</b> is already being used.');
         return(false);
      }else {
         return(true);
      }
   }
   

   public function remove($id){
      if (!bTestForURLHack('showClients')) return;
      $id          = (integer)$id;
      $this->load->model('clients/mclient_locations', 'clsLoc');
      $this->clsLoc->retireLocationRec($id);
      $this->session->set_flashdata('msg', 'The location was removed');
      redirect('admin/admin_special_lists/clients/locationView');
   }

   public function view($lLocID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
   
      if (!bTestForURLHack('showClients')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lLocID, 'client location ID');

      $displayData = array();
      $displayData['js'] = '';

      $lLocID = (integer)$lLocID;

      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->model  ('clients/mclient_locations', 'clsLoc');
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/img_doc_tags');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('personalization/ptable');
      $this->load->helper ('dl_util/web_layout');
      $this->load->model  ('img_docs/mimage_doc',     'clsImgDoc');
      $this->load->model  ('img_docs/mimg_doc_tags',  'cidTags');
      $this->load->model  ('admin/madmin_aco');
      $this->load->model  ('admin/muser_accts');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',  'clsDateTime');

      $this->clsLoc->loadLocationRec($lLocID);
      $displayData['cLoc'] = &$this->clsLoc;

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //-------------------------------
         // personalized tables
         //-------------------------------
      $this->load->model('personalization/muser_fields',         'clsUF');
      $this->load->model('personalization/muser_fields_display', 'clsUFD');
      $this->load->model('admin/mpermissions',                   'perms');

      $this->perms->loadUserAcctInfo($glUserID, $acctAccess);
      $displayData['strPT'] = strPTableDisplay(CENUM_CONTEXT_LOCATION, $lLocID,
                                  $this->clsUFD, $this->perms, $acctAccess,
                                  $displayData['strFormDataEntryAlert'],
                                  $displayData['lNumPTablesAvail']);


      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/record_view');
      $this->load->helper('img_docs/link_img_docs');

         //-------------------------------
         // images and documents
         //-------------------------------
      loadImgDocRecView($displayData, CENUM_CONTEXT_LOCATION, $lLocID);

      $this->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->clsLoc->loadSupportedSponCats($lLocID, $this->clsSponProg);
      $displayData['sponProgs']        = &$this->clsSponProg->sponProgs;
      $displayData['lNumSponPrograms'] = $this->clsSponProg->lNumSponPrograms;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/admin',         'Admin', 'class="breadcrumb"')
                                .' | '.anchor('admin/alists/showLists',  'Lists', 'class="breadcrumb" ')
                                .' | '.anchor('admin/admin_special_lists/clients/locationView', 'Client Locations', 'class="breadcrumb" ')
                                .' | Location Record';

      $displayData['title']          = CS_PROGNAME.' | Client Locations';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['lLocID']         = $lLocID;

      $displayData['mainTemplate']   = 'client/client_loc_rec_view';
      $this->load->vars($displayData);
      $this->load->view('template');

   }

}