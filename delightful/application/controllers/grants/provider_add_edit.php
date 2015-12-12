<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class provider_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function addEdit($lProviderID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $gclsChapterACO; // $gstrFormatDatePicker, $gbDateFormatUS;

      if (!bTestForURLHack('showGrants')) return;

      $this->load->helper('dl_util/verify_id');
      if ($lProviderID.'' != '0') verifyID($this, $lProviderID, 'provider ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lProviderID'] = $lProviderID = (integer)$lProviderID;
      $displayData['bNew']     = $bNew     = $lProviderID <= 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('grants/mgrants',   'cgrants');
      $this->load->helper ('dl_util/web_layout');
//      $this->load->model('admin/madmin_aco', 'clsACO');
      $this->load->model  ('util/mlist_generic',     'clsList');
      $this->clsList->enumListType = CENUM_LISTTYPE_ATTRIB;

         // load the grant provider
      $this->cgrants->loadGrantProviderViaGPID($lProviderID, $lNumProviders, $providers);
      $provider = $displayData['provider'] = &$providers[0];

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtGrantOrg',    'Granting Organization',  'trim|required|callback_verifyUniqueGrantProviderName['.$lProviderID.']');
//		$this->form_validation->set_rules('txtGrantName',   'Grant Name',  'trim|required|callback_verifyUniqueGrantName['.$lProviderID.']');
//      $this->form_validation->set_rules('rdoACO',     '',  'trim');
      $this->form_validation->set_rules('ddlAttrib',  'Attributed to');
      $this->form_validation->set_rules('txtAddr1',        '',  'trim');
      $this->form_validation->set_rules('txtAddr2',        '',  'trim');
      $this->form_validation->set_rules('txtCity',         '',  'trim');
      $this->form_validation->set_rules('txtState',        '',  'trim');
      $this->form_validation->set_rules('txtZip',          '',  'trim');
      $this->form_validation->set_rules('txtCountry',      '',  'trim');
      $this->form_validation->set_rules('txtNotes',        '',  'trim');

      $this->form_validation->set_rules('txtPhone',      '',  'trim');
      $this->form_validation->set_rules('txtCell',       '',  'trim');
      $this->form_validation->set_rules('txtEmail',      'Email',  'trim|valid_email');
      $this->form_validation->set_rules('txtWebSite',    'Web Site',  'trim');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['formData']->txtGrantOrg   = htmlspecialchars($provider->strGrantOrg.'');
//            $displayData['formData']->txtGrantName  = htmlspecialchars($provider->strGrantName.'');
//            $lACO = $gclsChapterACO->lKeyID;
//            $displayData['formData']->rdoACO      = $this->clsACO->strACO_Radios($lACO, 'rdoACO');
            $displayData['strAttribDDL']          = $this->clsList->strLoadListDDL('ddlAttrib', true, $provider->lAttributedTo);

            $displayData['formData']->txtAddr1    = htmlspecialchars($provider->strAddr1);
            $displayData['formData']->txtAddr2    = htmlspecialchars($provider->strAddr2);
            $displayData['formData']->txtCity     = htmlspecialchars($provider->strCity);
            $displayData['formData']->txtState    = htmlspecialchars($provider->strState);
            $displayData['formData']->txtZip      = htmlspecialchars($provider->strZip);
            $displayData['formData']->txtCountry  = htmlspecialchars($provider->strCountry);

            $displayData['formData']->txtEmail    = htmlspecialchars($provider->strEmail);
            $displayData['formData']->txtWebSite  = htmlspecialchars($provider->strWebSite);
            $displayData['formData']->txtPhone    = htmlspecialchars($provider->strPhone);
            $displayData['formData']->txtCell     = htmlspecialchars($provider->strCell);

            $displayData['formData']->txtNotes    = htmlspecialchars($provider->strNotes);

         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtGrantOrg   = set_value('txtGrantOrg');
//            $displayData['formData']->txtGrantName  = set_value('txtGrantName');
//            $displayData['formData']->rdoACO        = $this->clsACO->strACO_Radios(set_value('rdoACO'), 'rdoACO');
            $displayData['strAttribDDL']            = $this->clsList->strLoadListDDL('ddlAttrib', true, set_value('ddlAttrib'));
            $displayData['formData']->txtNotes      = set_value('txtNotes');

            $displayData['formData']->txtAddr1      = set_value('txtAddr1');
            $displayData['formData']->txtAddr2      = set_value('txtAddr2');
            $displayData['formData']->txtCity       = set_value('txtCity');
            $displayData['formData']->txtState      = set_value('txtState');
            $displayData['formData']->txtZip        = set_value('txtZip');
            $displayData['formData']->txtCountry    = set_value('txtCountry');

            $displayData['formData']->txtEmail      = set_value('txtEmail');
            $displayData['formData']->txtWebSite    = set_value('txtWebSite');
            $displayData['formData']->txtPhone      = set_value('txtPhone');
            $displayData['formData']->txtCell       = set_value('txtCell');
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']   = anchor('main/menu/financials', 'Financials/Grants', 'class="breadcrumb"')
                                .' | '.anchor('grants/provider_directory/viewProDirectory', 'Provider Directory', 'class="breadcrumb"')
                                .' | '.($bNew ? 'Add New ' : 'Edit ').'Grant Provider';

         $displayData['title']          = CS_PROGNAME.' | Grants';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'grants/provider_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $provider->strGrantOrg     = trim($_POST['txtGrantOrg']);
//         $provider->strGrantName    = trim($_POST['txtGrantName']);
//         $provider->lACO            = (int)$_POST['rdoACO'];
         $provider->strNotes        = trim($_POST['txtNotes']);

         $provider->strAddr1        = trim($_POST['txtAddr1']);
         $provider->strAddr2        = trim($_POST['txtAddr2']);
         $provider->strCity         = trim($_POST['txtCity']);
         $provider->strState        = trim($_POST['txtState']);
         $provider->strZip          = trim($_POST['txtZip']);
         $provider->strCountry      = trim($_POST['txtCountry']);

         $provider->strWebSite      = trim($_POST['txtWebSite']);
         $provider->strEmail        = trim($_POST['txtEmail']);
         $provider->strPhone        = trim($_POST['txtPhone']);
         $provider->strCell         = trim($_POST['txtCell']);

         $lAttrib = (integer)$_POST['ddlAttrib'];
         if ($lAttrib <= 0){
            $provider->lAttributedTo = null;
         }else {
            $provider->lAttributedTo = $lAttrib;
         }

         if ($bNew){
            $lProviderID = $this->cgrants->lAddGrantProvider($provider);
         }else {
            $this->cgrants->updateGrantProvider($lProviderID, $provider);
         }
         redirect('grants/provider_record/viewProvider/'.$lProviderID);
      }
   }
   
   function verifyUniqueGrantProviderName($strProviderName, $strID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      $lProviderID = (int)$strID;

      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                trim($strProviderName), 'gpr_strGrantOrg',
                $lProviderID, 'gpr_lKeyID',
                true,        'gpr_bRetired',
                false, null, null,
                false, null, null,
                'grant_providers')){
         $this->form_validation->set_message('verifyUniqueGrantProviderName',
                   'This Provider Name is already in the system.');
         return(false);
      }else {
         return(true);
      }
   }
   


}


