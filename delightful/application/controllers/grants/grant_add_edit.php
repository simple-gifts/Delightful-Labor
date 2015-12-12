<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class grant_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function addEdit($lProviderID, $lGrantID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $gclsChapterACO; // $gstrFormatDatePicker, $gbDateFormatUS;

      if (!bTestForURLHack('showGrants')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lProviderID, 'provider ID');
      if ($lGrantID.'' != '0') verifyID($this, $lGrantID, 'grant ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lProviderID'] = $lProviderID = (integer)$lProviderID;
      $displayData['lGrantID']    = $lGrantID    = (integer)$lGrantID;
      $displayData['bNew']        = $bNew        = $lGrantID <= 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('grants/mgrants',   'cgrants');
      $this->load->helper ('dl_util/web_layout');
      $this->load->model  ('admin/madmin_aco', 'clsACO');
      $this->load->model  ('util/mlist_generic', 'clsList');
      $this->clsList->enumListType = CENUM_LISTTYPE_ATTRIB;
      $this->load->helper ('grants/link_grants');      
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params, 'generic_rpt');      

         // load the grant provider
      $this->cgrants->loadGrantProviderViaGPID($lProviderID, $lNumProviders, $providers);
      $provider = $displayData['provider'] = &$providers[0];
      
         // load the grant
      $this->cgrants->loadGrantViaGID($lGrantID, $lNumGrants, $grants);
      $grant = $displayData['grant'] = &$grants[0];
      
         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
//		$this->form_validation->set_rules('txtGrantOrg',    'Granting Organization',  'trim|required|callback_verifyUniqueGrantProviderName['.$lProviderID.']');
		$this->form_validation->set_rules('txtGrantName',   'Grant Name',  'trim|required|callback_verifyUniqueGrantName['.$lProviderID.']');
      $this->form_validation->set_rules('rdoACO',     '',  'trim');
      $this->form_validation->set_rules('ddlAttrib',  'Attributed to');
      $this->form_validation->set_rules('txtNotes',        '',  'trim');

//      $this->form_validation->set_rules('txtPhone',      '',  'trim');
//      $this->form_validation->set_rules('txtCell',       '',  'trim');
//      $this->form_validation->set_rules('txtEmail',      'Email',  'trim|valid_email');
//      $this->form_validation->set_rules('txtWebSite',    'Web Site',  'trim');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
//            $displayData['formData']->txtGrantOrg   = htmlspecialchars($provider->strGrantOrg.'');
            $displayData['formData']->txtGrantName  = htmlspecialchars($grant->strGrantName.'');
            $lACO = $gclsChapterACO->lKeyID;
            $displayData['formData']->rdoACO      = $this->clsACO->strACO_Radios($lACO, 'rdoACO');
            $displayData['strAttribDDL']          = $this->clsList->strLoadListDDL('ddlAttrib', true, $grant->lAttributedTo);


            $displayData['formData']->txtNotes    = htmlspecialchars($grant->strNotes);

         }else {
            setOnFormError($displayData);
//            $displayData['formData']->txtGrantOrg   = set_value('txtGrantOrg');
            $displayData['formData']->txtGrantName  = set_value('txtGrantName');
            $displayData['formData']->rdoACO        = $this->clsACO->strACO_Radios(set_value('rdoACO'), 'rdoACO');
            $displayData['strAttribDDL']            = $this->clsList->strLoadListDDL('ddlAttrib', true, set_value('ddlAttrib'));
            $displayData['formData']->txtNotes      = set_value('txtNotes');

         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['contextSummary'] = $this->cgrants->providerHTMLSummary($provider);
            
         $displayData['pageTitle']   = anchor('main/menu/financials', 'Financials/Grants', 'class="breadcrumb"')
                                .' | '.anchor('grants/provider_directory/viewProDirectory', 'Provider Directory', 'class="breadcrumb"')
                                .' | '.($bNew ? 'Add New ' : 'Edit ').'Grant';

         $displayData['title']          = CS_PROGNAME.' | Grants';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'grants/grant_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
//         $provider->strGrantOrg     = trim($_POST['txtGrantOrg']);
         $grant->strGrantName    = trim($_POST['txtGrantName']);
         $grant->lACO            = (int)$_POST['rdoACO'];
         $grant->strNotes        = trim($_POST['txtNotes']);


         $lAttrib = (integer)$_POST['ddlAttrib'];
         if ($lAttrib <= 0){
            $grant->lAttributedTo = null;
         }else {
            $grant->lAttributedTo = $lAttrib;
         }

         if ($bNew){
            $lGrantID = $this->cgrants->lAddGrant($lProviderID, $grant);
            $this->session->set_flashdata('msg', 'Grant added.');
         }else {
            $this->cgrants->updateGrant($lGrantID, $grant);
            $this->session->set_flashdata('msg', 'Grant updated.');
         }
         redirect('grants/grant_record/viewGrant/'.$lGrantID);
      }
   }
   
   
}
