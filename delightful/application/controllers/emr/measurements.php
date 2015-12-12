<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class measurements extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function index(){
   }

   function viewMeasurement($lClientID){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      global $gbMetric;

      $this->load->helper('dl_util/verify_id');
      verifyIDsViaType($this, CENUM_CONTEXT_CLIENT, $lClientID, true);

      $displayData = array();

      $displayData['lClientID'] = $lClientID;
      $displayData['js'] = '';


         /*------------------------------------------------
             models/libraries/helpers
         ------------------------------------------------*/
      $this->load->model  ('emr/mmeasurements', 'emrMeas');
      $this->load->model  ('img_docs/mimage_doc',   'clsImgDoc');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('emr/link_emr');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model  ('emr/mpercentiles', 'percentiles');
      $this->load->model  ('clients/mclients', 'clsClients');

      $this->clsClients->loadClientsViaClientID($lClientID);
      $displayData['client'] = $client = &$this->clsClients->clients[0];

         /*-------------------------------------
             client summary
         -------------------------------------*/
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params, 'generic_rpt');
      $displayData['clsRpt'] = $this->generic_rpt;
      $displayData['contextSummary'] = $this->clsClients->strClientHTMLSummary(0);

         /*-------------------------------------
            stripes
         -------------------------------------*/
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         /*-------------------------------------
             measurements
         -------------------------------------*/
      $this->emrMeas->loadMeasurementViaClientID($lClientID);
      $displayData['lNumMeasure']  = $this->emrMeas->lNumMeasure;
      $displayData['measurements'] = $measurements = &$this->emrMeas->measurements;

/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$displayData[measurements]   <pre>');
echo(htmlspecialchars( print_r($displayData['measurements'], true))); echo('</pre></font><br>');
// ------------------------------------- */

/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$displayData   <pre>');
echo(htmlspecialchars( print_r($displayData, true))); echo('</pre></font><br>');
// ------------------------------------- */

         /*-------------------------------------
             percentiles
         -------------------------------------*/
      foreach ($measurements as $measure){
         $measure->sngHeightP = null;
         $measure->sngWeightP = null;
         $measure->sngOFCP    = null;
         $measure->sngBMIP    = null;
      }


         /*--------------------------
            breadcrumbs
         --------------------------*/
      $displayData['pageTitle']      = anchor('main/menu/client',                       'Clients', 'class="breadcrumb"')
                                .' | '.anchor('clients/client_record/view/'.$lClientID, 'Client Record', 'class="breadcrumb"')
                                .' |  Measurements';

      $displayData['title']          = CS_PROGNAME.' | Clients';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'emr/measurements_view';
      $this->load->vars($displayData);
      $this->load->view('template');

   }

   function addEditMeasurement($lClientID, $lMeasureID='0'){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      global $gbDateFormatUS, $gstrFormatDatePicker, $gdteNow, $genumMeasurePref, $gbMetric;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lClientID, 'client ID');

      $displayData = array();

      $displayData['lClientID']  = $lClientID;
      $displayData['lMeasureID'] = $lMeasureID = (int)$lMeasureID;
      $displayData['bNew']       = $bNew      = $lMeasureID <= 0;
      $displayData['js'] = '';

         /*------------------------------------------------
             models/libraries/helpers
         ------------------------------------------------*/
      $this->load->model  ('emr/mmeasurements',     'emrMeas');
      $this->load->model  ('clients/mclients',      'clsClients');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('emr/link_emr');

      $this->clsClients->loadClientsViaClientID($lClientID);
      $displayData['client']   = $client = &$this->clsClients->clients[0];
      $displayData['bShowOFC'] = $bShowOFC = $client->lAgeYears < 3;

      $this->emrMeas->loadMeasurementViaMeasID($lMeasureID);
      $measure = &$this->emrMeas->measurements[0];

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtNotes',   'Notes',      'trim');
//		$this->form_validation->set_rules('txtHeight',  'Height',     'trim|callback_stripCommas|numeric|callback_measVerifyHeight');
		$this->form_validation->set_rules('txtHeight',  'Height',     'callback_measVerifyHeight');
		$this->form_validation->set_rules('txtWeight',  'Weight',     'trim|callback_stripCommas|numeric|callback_measVerifyWeight');
		$this->form_validation->set_rules('txtOFC',     'OFC/Head Circumference', 'trim|callback_stripCommas|numeric|callback_measVerifyOFC');
      $this->form_validation->set_rules('txtDate',    'Measurement Date',  'trim|required'
                                                           .'|callback_measRecVerifyDateValid'
                                                           .'|callback_measRecVerifyDatePast');

      if ($this->form_validation->run() == FALSE){
          $this->emrMeas->lNumMeasureViaCID($lClientID, true, true, true);

            /*-------------------------------------
                client summary
            -------------------------------------*/
         $displayData['formData'] = new stdClass;

         $this->load->model ('img_docs/mimage_doc',   'clsImgDoc');
         $this->load->helper('img_docs/image_doc');
         $this->load->helper('img_docs/link_img_docs');
         $params = array('enumStyle' => 'terse');
         $this->load->library('generic_rpt', $params, 'generic_rpt');
         $displayData['clsRpt'] = $this->generic_rpt;
         $displayData['contextSummary'] = $this->clsClients->strClientHTMLSummary(0);

            /*-------------------------------------
               stripes
            -------------------------------------*/
         $this->load->model('util/mbuild_on_ready',    'clsOnReady');
         $this->clsOnReady->addOnReadyTableStripes();
         $this->clsOnReady->closeOnReady();
         $displayData['js'] .= $this->clsOnReady->strOnReady;

         $this->load->library('generic_form');

         if (validation_errors()==''){
            if ($bNew){
               $displayData['formData']->strDate = strNumericDateViaUTS($gdteNow, $gbDateFormatUS);
               $displayData['formData']->txtHeight = '';
               $displayData['formData']->txtWeight = '';
               $displayData['formData']->txtOFC    = '';
               $displayData['formData']->txtNotes  = '';;
            }else {
               $displayData['formData']->strDate = strNumericDateViaUTS($measure->dteMeasurement, $gbDateFormatUS);
               if ($gbMetric){
                  $this->setMeasure($measure->sngHeightCM,    $displayData['formData']->txtHeight, 1);
                  $this->setMeasure($measure->sngWeightKilos, $displayData['formData']->txtWeight, 1);
                  $this->setMeasure($measure->sngHeadCircCM,  $displayData['formData']->txtOFC,    1);
               }else {
                  $this->setMeasure($measure->sngHeightIn,    $displayData['formData']->txtHeight, 1);
                  $this->setMeasure($measure->sngWeightLBS,   $displayData['formData']->txtWeight, 1);
                  $this->setMeasure($measure->sngHeadCircIn,  $displayData['formData']->txtOFC,    1);
                  $displayData['formData']->txtNotes  = htmlspecialchars($measure->strNotes);
               }
            }
         }else {
            setOnFormError($displayData);

            $displayData['formData']->strDate   = set_value('txtDate');
            $displayData['formData']->txtHeight = set_value('txtHeight');
            $displayData['formData']->txtWeight = set_value('txtWeight');
            $displayData['formData']->txtOFC    = set_value('txtOFC');
            $displayData['formData']->txtNotes  = set_value('txtNotes');            
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/client',                       'Clients', 'class="breadcrumb"')
                                   .' | '.anchor('clients/client_record/view/'.$lClientID, 'Client Record', 'class="breadcrumb"')
                                   .' | '.anchor('emr/measurements/viewMeasurement/'.$lClientID, 'Measurements', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add' : 'Edit').' Measurements';

         $displayData['title']          = CS_PROGNAME.' | Clients';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'emr/measurements_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $measure->lClientID = $lClientID;
         $strDate   = trim($_POST['txtDate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $measure->mySQLDateMeasure = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);

         $measure->sngHeightCM    = $this->strPrepMeasureForDB(trim($_POST['txtHeight']), ($gbMetric ? CSTR_UNITS_CM : CSTR_UNITS_IN));
         $measure->sngWeightKilos = $this->strPrepMeasureForDB(trim($_POST['txtWeight']), ($gbMetric ? CSTR_UNITS_KG : CSTR_UNITS_LB));
         $measure->sngHeadCircCM   = $this->strPrepMeasureForDB(trim($_POST['txtOFC']), ($gbMetric ? CSTR_UNITS_CM : CSTR_UNITS_IN));
         $measure->strNotes        = trim($_POST['txtNotes']);
         
         if ($bNew){
            $this->emrMeas->addNewMeasure();
            $this->session->set_flashdata('msg', 'The client measurements were added.');
         }else {
            $this->emrMeas->updateMeasure($lMeasureID);
            $this->session->set_flashdata('msg', 'The client measurements were updated.');
         }
         redirect_ClientMeasure($lClientID);
      }

/*

      $displayData['client']    = $client    = $clsClients->clients[0];

      if (is_null($client->lLocationID)){
         $client->lLocationID  = (integer)$_POST['lLocationID'];
         $client->lStatusCatID = (integer)$_POST['lStatusCatID'];
         $client->lVocID       = (integer)$_POST['lVocID'];
      }

         // models associated with the client record
      $this->load->model('clients/mclient_locations',         'clsLocation');
      $this->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->load->model('clients/mclient_status',            'clsClientStat');
      $this->load->model('clients/mclient_vocabulary',        'clsClientV');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/web_layout');

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

      if ($bNew){
         $this->form_validation->set_rules('ddlStatus',  '',      'required|callback_clientRecVerifyInitStat');
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
            $this->load->model('personalization/muser_fields', 'clsUF');
            $this->load->model('personalization/muser_fields_create', 'clsUFC');
            $this->load->model ('admin/mpermissions',           'perms');
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
*/
   }

   function setMeasure($sngMeasure, &$strMeasure, $lNumDec){
      if (is_null($sngMeasure)){
         $strMeasure = '';
      }else{
         $strMeasure = number_format($sngMeasure, $lNumDec);
      }
   }


   function strPrepMeasureForDB($strMeasure, $enumUnit){
      if ($strMeasure=='') return('null');

      $sngMeasure = (float)$strMeasure;
      switch ($enumUnit){
         case CSTR_UNITS_LB:
            return(number_format($sngMeasure/CSNG_KILOS_TO_LBS, 6, '.', ''));
            break;
         case CSTR_UNITS_IN:
            return(number_format($sngMeasure/CSNG_CM_TO_INCHES, 6, '.', ''));
            break;
         case CSTR_UNITS_KG:
         case CSTR_UNITS_CM:
            return(number_format($sngMeasure, 6, '.', ''));
            break;
         default:
            screamForHelp($enumUnit.': invalid measurement unit detected<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);         
            break;
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
   function measRecVerifyDateValid($strDate){
      if ($strDate==''){
         $this->form_validation->set_message('measRecVerifyDateValid',
                   'A measurement date is required.');
         return(false);
      }
      if (!bValidVerifyDate($strDate)){
         $this->form_validation->set_message('measRecVerifyDateValid',
                   'This date is not valid.');
         return(false);
      }elseif (!bValidVerifyUnixRange($strDate)){
         $this->form_validation->set_message('measRecVerifyDateValid',
                   'This date is out of range.');
         return(false);
      }else {
         return(true);
      }
   }

   function measRecVerifyDatePast($strDate){
      if ($strDate==''){
         $this->form_validation->set_message('measRecVerifyDatePast',
                   'A measurement date is required.');
         return(false);
      }
      if (!bValidVerifyNotFuture($strDate)){
         $this->form_validation->set_message('measRecVerifyDatePast',
                   'This date is in the future!');
         return(false);
      }else {
         return(true);
      }
   }

   function measVerifyHeight($strHeight){
      global $gbMetric;
      $this->stripCommas($strHeight);
      $strHeight = trim($strHeight);

      if ($strHeight != ''){
         if (!is_numeric($strHeight)){
            $this->form_validation->set_message('measVerifyHeight',
                   'The Height field must contain only numbers.');
            return(false);
         }
      }
            // at least one measurement is required
      $strWeight = trim(@$_REQUEST['txtWeight']);
      $strOFC    = trim(@$_REQUEST['txtOFC']);

      if ( ($strHeight=='' && $strWeight=='' && $strOFC=='') ||
           ((int)$strHeight==0 && (int)$strWeight==0 && (int)$strOFC==0)){
         $this->form_validation->set_message('measVerifyHeight',
                   'Please specify one or more measurements.');
         return(false);
      }

      if ($strHeight=='') return(true);
      $sngHeight = (float)$strHeight;
      if (abs($sngHeight) < 0.01){
         $strHeight = '0';
         return(true);
      }
      $bOutOfRange = false;
      if ($gbMetric){
         if ($sngHeight < 10 || $sngHeight > 350) $bOutOfRange = true;
      }else {
         if ($sngHeight < 5 || $sngHeight > 120) $bOutOfRange = true;
      }

      if ($bOutOfRange){
         $this->form_validation->set_message('measVerifyHeight',
                   'The height measurement is out of range.');
         return(false);
      }else {
         return(true);
      }
   }
   function measVerifyWeight($strWeight){
      return($this->strMeasureTest($strWeight, 0.5, 400, 1.0, 999, 'measVerifyWeight', 'weight'));
   }
   function measVerifyOFC($strOFC){
      return($this->strMeasureTest($strOFC, 0.5, 40, 1.0, 60, 'measVerifyOFC', 'head circumference'));
   }
   function strMeasureTest($strMeasure, $sngMinMetric, $sngMaxMetric, $sngMinE, $sngMaxE, $strFromRoutine, $strLabel){
      global $gbMetric;

      if ($strMeasure=='') return(true);
      $strMeasure = (float)$strMeasure;
      if (abs($strMeasure) < 0.01){
         $strMeasure = '0';
         return(true);
      }
      $bOutOfRange = false;
      if ($gbMetric){
         if ($strMeasure < $sngMinMetric || $strMeasure > $sngMaxMetric) $bOutOfRange = true;
      }else {
         if ($strMeasure < $sngMinE      || $strMeasure > $sngMaxE)      $bOutOfRange = true;
      }

      if ($bOutOfRange){
         $this->form_validation->set_message($strFromRoutine,
                   "The $strLabel measurement is out of range.");
         return(false);
      }else {
         return(true);
      }
   }

   function stripCommas(&$strValue){
      $strValue = str_replace (',', '', $strValue);
      return(true);
   }


/*
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
*/


}