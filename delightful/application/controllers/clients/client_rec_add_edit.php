<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class client_rec_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function index(){

   }

   public function addNewS1(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;

      $this->load->helper ('clients/client');
      $this->load->model  ('img_docs/mimage_doc',   'clsImgDoc');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      clientBaseInfoForm($this, 'addNew', null);
   }

      /*-------------------------
         Verification
      -------------------------*/
   function addClientVerifyLoc($strValue){
      $lLocID = (integer)$strValue;
      return($lLocID > 0);
   }

   function addClientVerifySCat($strValue){
      $lStatCatID = (integer)$strValue;
      return($lStatCatID > 0);
   }

   function addClientVerifyVoc($strValue){
      $lVocID = (integer)$strValue;
      return($lVocID > 0);
   }

   function addNewClientS2($lClientID, $lLocID, $lStatCatID, $lVocID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;

      $this->load->helper('dl_util/verify_id');
      verifyIDsViaType($this, CENUM_CONTEXT_CLIENT, $lClientID, true);
      verifyID($this, $lLocID,     'client location ID');
      verifyID($this, $lStatCatID, 'status category ID');
      verifyID($this, $lVocID,     'client vocabulary ID');

      $lClientID  = (integer)$lClientID;
      $lLocID     = (integer)$lLocID;
      $lStatCatID = (integer)$lStatCatID;
      $lVocID     = (integer)$lVocID;

      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('clients/mclients', 'clsClients');
      $this->clsClients->loadClientsViaClientID($lClientID);
      $client = $this->clsClients->clients[0];

      $client->lLocationID  = $lLocID;
      $client->lStatusCatID = $lStatCatID;
      $client->lVocID       = $lVocID;

      $this->addEditClient($lClientID, $this->clsClients);
   }

   function addEdit($lClientID){
   //---------------------------------------------------------------
   // user wishes to edit existing client record
   //---------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;

      $this->load->helper('dl_util/verify_id');
      verifyIDsViaType($this, CENUM_CONTEXT_CLIENT, $lClientID, true);

      $lClientID = (integer)$lClientID;
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('clients/mclients', 'clsClients');
      $this->clsClients->loadClientsViaClientID($lClientID);

      $this->addEditClient($lClientID, $this->clsClients);
   }

   function addEditClient($lClientID, $clsClients){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      global $gbDateFormatUS, $gstrFormatDatePicker, $gdteNow;
      global $gstrDuplicateWarning;

      if (!bTestForURLHack('showClients')) return;
      $displayData = array();

      $gstrDuplicateWarning = '';

      $displayData['lClientID'] = $lClientID = (integer)$lClientID;
      $displayData['bNew']      = $bNew      = $lClientID <= 0;
      $displayData['client']    = $client    = $clsClients->clients[0];

      if (is_null($client->lLocationID)){
         $client->lLocationID  = (integer)$_POST['lLocationID'];
         $client->lStatusCatID = (integer)$_POST['lStatusCatID'];
         $client->lVocID       = (integer)$_POST['lVocID'];
      }

         // models associated with the client record
      $this->load->model ('clients/mclient_locations',         'clsLocation');
      $this->load->model ('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->load->model ('clients/mclient_status',            'clsClientStat');
      $this->load->model ('clients/mclient_vocabulary',        'clsClientV');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/web_layout');

      if ($bNew){
         $this->load->model ('util/mdup_checker',           'cDupChecker');
         $this->load->model('admin/mpermissions',           'perms');
         $this->load->model('personalization/muser_fields', 'clsUF');
         $this->load->model('personalization/muser_schema', 'cUFSchema');
         $this->load->model('client_features/mcprograms',   'cprograms');
      }

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtFName',      'Client\'s First Name',  'trim|required');
      $this->form_validation->set_rules('txtMName',      'Client\'s Middle Name', 'trim');
      $this->form_validation->set_rules('txtLName',      'Client\'s Last Name',   'trim');
      $this->form_validation->set_rules('txtBDate',      'Birth date', 'trim|required|callback_clientRecVerifyBDateValid'
                                                                                   .'|callback_clientRecVerifyBDatePast');
      $this->form_validation->set_rules('txtPEntryDate', 'Enrollment date', 'trim|required|callback_clientRecVerifyEDateValid'
                                                                                        .'|callback_clientRecVerifyEDatePast');
      $this->form_validation->set_rules('txtMaxSpon',    'Max # of Sponsors',   'trim|required|is_natural');
      $this->form_validation->set_rules('rdoGender',     'Gender',   'required');
      $this->form_validation->set_rules('txtBio',        'Bio',      'trim');
      $this->form_validation->set_rules('ddlAttrib',     'Attributed to');

      $this->form_validation->set_rules('txtAddr1',      '',  'trim');
      $this->form_validation->set_rules('txtAddr2',      '',  'trim');
      $this->form_validation->set_rules('txtCity',       '',  'trim');
      $this->form_validation->set_rules('txtState',      '',  'trim');
      $this->form_validation->set_rules('txtZip',        '',  'trim');
      $this->form_validation->set_rules('txtCountry',    '',  'trim');
      $this->form_validation->set_rules('txtEmail',      'Email',  'trim|valid_email');
      $this->form_validation->set_rules('txtPhone',      '',  'trim');
      $this->form_validation->set_rules('txtCell',       '',  'trim');

         // test for duplicate clients
      if ($bNew){
         $this->form_validation->set_rules('hiddenTestDup', 'dummy',  'callback_verifyNoDups');
         $this->form_validation->set_rules('ddlStatus',  '',      'required|callback_clientRecVerifyInitStat');
         $displayData['bHiddenNewTestDup'] = true;
      }

      if ($this->form_validation->run() == FALSE){
         $this->load->model  ('util/mlist_generic',     'clsList');
         $this->clsList->enumListType = CENUM_LISTTYPE_ATTRIB;

         $displayData['formData'] = new stdClass;
         $displayData['lLocationID'] = $lLocationID = $this->clsLocation->cl_lKeyID = $client->lLocationID;
         $this->clsLocation->loadLocationRec($lLocationID);

         $displayData['lVocID'] = $lVocID = $this->clsClientV->lVocID = $client->lVocID;
         $this->clsClientV->loadClientVocabulary(true, false);
         $displayData['clsClientV']  = $this->clsClientV;
         $displayData['clsLocation'] = $this->clsLocation;
         $this->clsSponProg->loadSponProgsViaLocID($lLocationID);
         $displayData['clsSponProg']   = $this->clsSponProg;
         $displayData['clsClientStat'] = $this->clsClientStat;

         $lSCID = $client->lStatusCatID;
         $this->clsClientStat->loadClientStatCatsEntries(
                            true,  $lSCID,
                            false, null,
                            true,  false);

         $strMessage = '<font color="red">One or more client status entries must be established before
                             adding clients.</font>';
         $displayData['bNoStatEntries'] = $bNoStatEntries = $this->clsClientStat->bTestNoStatEntries(
                                           $lSCID, true, $strMessage, $displayData['strNoStatErrMsg']);


         $this->load->library('generic_form');
         $displayData['strDuplicateWarning'] = $gstrDuplicateWarning;


         if (validation_errors()==''){
            $displayData['formData']->txtFName = htmlspecialchars($client->strFName);
            $displayData['formData']->txtMName = htmlspecialchars($client->strMName);
            $displayData['formData']->txtLName = htmlspecialchars($client->strLName);
            $displayData['strAttribDDL'] = $this->clsList->strLoadListDDL('ddlAttrib', true, $client->lAttribID);

            if (is_null($client->dteBirth)){
               $displayData['formData']->txtBDate = '';
            }else {
               $displayData['formData']->txtBDate = strNumericDateViaMysqlDate($client->dteBirth, $gbDateFormatUS);
            }
            if (is_null($client->dteEnrollment)){
               $displayData['formData']->txtPEntryDate = '';
            }else {
               $displayData['formData']->txtPEntryDate = date($gstrFormatDatePicker, $client->dteEnrollment);
            }
            if ($bNew) $displayData['formData']->lInitStatID = -1;

            if ($this->clsSponProg->lNumSponPrograms > 0){
               foreach ($this->clsSponProg->sponProgs as $clsLocSP){
                  $lSCID = $clsLocSP->lKeyID;
                  $clsLocSP->bSupported = $this->clsSponProg->bClientInSponProgram($lClientID, $lSCID, $lcsp_lKeyID);
               }
            }
            $displayData['formData']->txtMaxSpon = $client->lMaxSponsors;
            $displayData['formData']->rdoGender  = $client->enumGender;
            $displayData['formData']->txtBio     = htmlspecialchars($client->strBio);

            $displayData['formData']->txtAddr1    = htmlspecialchars($client->strAddr1);
            $displayData['formData']->txtAddr2    = htmlspecialchars($client->strAddr2);
            $displayData['formData']->txtCity     = htmlspecialchars($client->strCity);
            $displayData['formData']->txtState    = htmlspecialchars($client->strState);
            $displayData['formData']->txtZip      = htmlspecialchars($client->strZip);
            $displayData['formData']->txtCountry  = htmlspecialchars($client->strCountry);
            $displayData['formData']->txtEmail    = htmlspecialchars($client->strEmail);
            $displayData['formData']->txtPhone    = htmlspecialchars($client->strPhone);
            $displayData['formData']->txtCell     = htmlspecialchars($client->strCell);

         }else{
            setOnFormError($displayData);
            $displayData['strAttribDDL']            = $this->clsList->strLoadListDDL('ddlAttrib', true, set_value('ddlAttrib'));
            $displayData['formData']->txtFName      = set_value('txtFName');
            $displayData['formData']->txtMName      = set_value('txtMName');
            $displayData['formData']->txtLName      = set_value('txtLName');
            $displayData['formData']->txtBDate      = set_value('txtBDate');
            $displayData['formData']->txtPEntryDate = set_value('txtPEntryDate');

            if ($bNew) $displayData['formData']->lInitStatID = set_value('ddlStatus');

               // couldn't find a ci form validation way to do this....
            if ($this->clsSponProg->lNumSponPrograms > 0){
               if (isset($_POST['chkSP'])){
                  foreach ($this->clsSponProg->sponProgs as $clsLocSP){
                     $lSCID = $clsLocSP->lKeyID;
                     $clsLocSP->bSupported = in_array($lSCID, $_POST['chkSP']);
                  }
               }
            }

            $displayData['formData']->txtMaxSpon = set_value('txtMaxSpon');
            $displayData['formData']->rdoGender  = set_value('rdoGender');
            $displayData['formData']->txtBio     = set_value('txtBio');

            $displayData['formData']->txtAddr1   = set_value('txtAddr1');
            $displayData['formData']->txtAddr2   = set_value('txtAddr2');
            $displayData['formData']->txtCity    = set_value('txtCity');
            $displayData['formData']->txtState   = set_value('txtState');
            $displayData['formData']->txtZip     = set_value('txtZip');
            $displayData['formData']->txtCountry = set_value('txtCountry');
            $displayData['formData']->txtEmail   = set_value('txtEmail');
            $displayData['formData']->txtPhone   = set_value('txtPhone');
            $displayData['formData']->txtCell    = set_value('txtCell');
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         if ($lClientID > 0){
            $displayData['pageTitle']      = anchor('main/menu/client',                      'Clients', 'class="breadcrumb"')
                                      .' | '.anchor('clients/client_record/view/'.$lClientID, 'Client Record', 'class="breadcrumb"')
                                      .' | Edit Client Record';
         }else {
            $displayData['pageTitle']      = anchor('main/menu/client',                      'Clients', 'class="breadcrumb"')
                                      .' | Add New Client Record';
         }

         $displayData['title']          = CS_PROGNAME.' | Clients';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'client/client_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $strBDate   = trim($_POST['txtBDate']);
         MDY_ViaUserForm($strBDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $client->dteBirth = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);

         $strEnrollDate = trim($_POST['txtPEntryDate']);
         MDY_ViaUserForm($strEnrollDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $client->dteEnrollment = strtotime($lMon.'/'.$lDay.'/'.$lYear);

         $client->strFName      = trim($_POST['txtFName']);
         $client->strMName      = trim($_POST['txtMName']);
         $client->strLName      = trim($_POST['txtLName']);
         $client->strBio        = trim($_POST['txtBio']);
         $client->enumGender    = trim($_POST['rdoGender']);
         $client->lMaxSponsors  = (integer)trim($_POST['txtMaxSpon']);

         $client->strAddr1      = trim($_POST['txtAddr1']);
         $client->strAddr2      = trim($_POST['txtAddr2']);
         $client->strCity       = trim($_POST['txtCity']);
         $client->strState      = trim($_POST['txtState']);
         $client->strZip        = trim($_POST['txtZip']);
         $client->strCountry    = trim($_POST['txtCountry']);
         $client->strEmail      = trim($_POST['txtEmail']);
         $client->strPhone      = trim($_POST['txtPhone']);
         $client->strCell       = trim($_POST['txtCell']);

         $lAttrib = (integer)$_REQUEST['ddlAttrib'];
         if ($lAttrib <= 0){
            $client->lAttribID = null;
         }else {
            $client->lAttribID = $lAttrib;
         }

         if ($bNew){
            $this->load->model('personalization/muser_fields',        'clsUF');
            $this->load->model('personalization/muser_fields_create', 'clsUFC');
            $this->load->model('admin/mpermissions',                  'perms');
            $lClientID = $this->clsClients->lAddNewClient();
            $this->clsClientStat->clientStatus = array();

            $status = $this->clsClientStat->clientStatus[0] = new stdClass;
            $status->lClientID             = $lClientID;
            $status->lStatusID             = (integer)trim($_POST['ddlStatus']);
            $status->bIncludeNotesInPacket = false;
            $status->dteStatus             = $gdteNow;
            $status->strStatusTxt          = 'Initial status';
            $this->clsClientStat->lInsertClientStatus();
            $this->session->set_flashdata('msg', 'The client record was added');
         }else {
            $clsClients->updateClientRec();
            $this->session->set_flashdata('msg', 'The client record was updated');
         }

            //----------------------------------------
            // update supported sponsor categories
            //----------------------------------------
         $this->clsSponProg->clearClientSponPrograms($lClientID);
         if (isset($_POST['chkSP'])){
            foreach ($_POST['chkSP'] as $lSCID){
               $this->clsSponProg->setClientSponProgram($lClientID, (integer)$lSCID);
            }
         }
         redirect('clients/client_record/view/'.$client->lKeyID);
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
   function clientRecVerifyBDateValid($strBDate){
      return(bValidVerifyDate($strBDate));
   }

   function clientRecVerifyBDatePast($strBDate){
      return(bValidVerifyNotFuture($strBDate));
   }

   function clientRecVerifyEDateValid($strEDate){
      return(bValidVerifyDate($strEDate));
   }

   function clientRecVerifyEDatePast($strEDate){
      return(bValidVerifyNotFuture($strEDate));
   }

   function clientRecVerifyInitStat($lStatID){
      return( ((integer)$lStatID > 0));
   }

      //---------------------------------------
      // remove client - if not sponsored
      //---------------------------------------
   function remove($lClientID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      $displayData = array();

      $lClientID = (integer)$lClientID;

      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->model('clients/mclients', 'clsClients');
      $this->load->model('groups/mgroups', 'groups');

      $this->clsClients->loadClientsViaClientID($lClientID);
      $this->clsClients->loadSponsorshipInfo($lClientID, true, $lNumSponsors, $displayData['sponsors']);
      $strClientName = $this->clsClients->clients[0]->strSafeName;

      $strDirType = @$_SESSION[CS_NAMESPACE.'clientLastDir'].'';
      if ($strDirType=='view'){
         $strAnchor = 'clients/client_dir/view/'.$this->clsClients->clients[0]->lLocationID;
      }else {
         $strAnchor = 'clients/client_dir/name/N/'.strtoupper(substr(($this->clsClients->clients[0]->strLName.'A'), 0, 1));
      }

      if ($lNumSponsors > 0){
         $displayData['title']        = CS_PROGNAME.' | Clients';
         $displayData['pageTitle']    = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                 .' | '.anchor($strAnchor, 'Directory', 'class="breadcrumb"')
                                 .' | Remove Client Record';
         $displayData['nav']          = $this->mnav_brain_jar->navData();

         $params = array('enumStyle' => 'terse');
         $this->load->library('generic_rpt', $params, 'generic_rpt');
         $displayData['clsRpt']         = $this->generic_rpt;
         $displayData['contextSummary'] = $this->clsClients->strClientHTMLSummary(0);

         $displayData['mainTemplate'] = 'client/err_cant_remove_view';
         $this->load->vars($displayData);
         $this->load->view('template');

      }else {
         $this->clsClients->removeClient($lClientID);
         $this->session->set_flashdata('msg', 'The client record for '.$strClientName.' was removed');

         $strDirType = @$_SESSION[CS_NAMESPACE.'clientLastDir'].'';
         if ($strDirType=='view'){
            redirect('clients/client_dir/view/'.$this->clsClients->clients[0]->lLocationID);
         }else {
            redirect('clients/client_dir/name/N/'.strtoupper(substr(($this->clsClients->clients[0]->strLName.'A'), 0, 1)));
         }
      }
   }

   function verifyNoDups($strValue){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gstrDuplicateWarning;
      $gstrDuplicateWarning = '';

      if (validation_errors() !='') return;

         // did the user acknowledge the duplicates and wish to continue
         // anyhow?
      if (isset($_POST['chkImCoolWithDups'])){
         if ($_POST['chkImCoolWithDups']=='true') return(true);
      }

      $strFName = trim($_POST['txtFName']);
      $strLName = trim($_POST['txtLName']);
      $this->cDupChecker->findSimilarClientNames($strFName, $strLName, $lNumMatches, $matches);

      if ($lNumMatches > 0){
         $gstrDuplicateWarning = $this->cDupChecker->strClientDupWarning(
                        $this->cprograms, 'chkImCoolWithDups', $lNumMatches, $matches);
         return(false);
      }else {
         return(true);
      }
   }

}