<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class import extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

	public function ptables() {
      if (!bTestForURLHack('adminOnly')) return;
      $this->ptableParentSelect();
   }

	public function people() {
      if (!bTestForURLHack('adminOnly')) return;
      $this->importPrep(CENUM_CONTEXT_PEOPLE);
   }

	public function review() {
      if (!bTestForURLHack('adminOnly')) return;
      $this->importPrep('review');
   }

   public function business(){
      if (!bTestForURLHack('adminOnly')) return;
      $this->importPrep(CENUM_CONTEXT_BIZ);
   }

   public function gift(){
      if (!bTestForURLHack('adminOnly')) return;
      $this->importPrep(CENUM_CONTEXT_GIFT);
   }

   public function sponsorPayment(){
      if (!bTestForURLHack('adminOnly')) return;
      $this->importPrep(CENUM_CONTEXT_SPONSORPAY);
   }

   public function importPrep($enumImportType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();
      $displayData['enumImportType'] = $enumImportType;
      $displayData['ddlGroups']      = '';

         //------------------------------------------------
         // models, libraries, and utilities
         //------------------------------------------------
      $this->load->model  ('groups/mgroups', 'groups');
      $this->load->helper ('groups/groups');

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('userfile', 'File Name', 'callback_upImportFN');

		if ($this->form_validation->run() == FALSE){
         $this->load->helper('dl_util/web_layout');
         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['ddlGroups'] = $this->strLoadGroups($enumImportType, false, $lMatchIDs);
         }else {
            $displayData['ddlGroups'] = $this->strLoadGroups($enumImportType, true, $lMatchIDs);

               // if errors other than those related to file upload, delete
               // temporary upload file
            $strTempFN = @$_SESSION[CS_NAMESPACE.'clientUploadEncryptFN'];

            if ($strTempFN != ''){
               unlink('./upload/'.$strTempFN);
            }
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                     .' | Import '.$enumImportType.' records';

         $displayData['title']          = CS_PROGNAME.' | Import '.$enumImportType.' records';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'admin/import_opts_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->load->library('util/CSVReader');
         $this->load->model('admin/mimport', 'clsImport');
         $this->load->model('admin/madmin_aco', 'clsACO');
         $this->load->helper('dl_util/time_date');

         $this->clsImport->initImportClass($enumImportType);
         $this->clsImport->initACOTable($this->clsACO);

         $uploadResults = $_SESSION[CS_NAMESPACE.'uploadResults'];
         $strUserFN     = $uploadResults['orig_name'];
         $strFN         = $uploadResults['file_name'];
         $strPathFN     = $uploadResults['full_path'];

         $userCSV = $this->csvreader->parse_file($strPathFN, true, true);
         if ($userCSV === false){
            $this->session->set_flashdata('error', 'Your CSV file contains no records!'.'<br><b>Import canceled due to errors</b>');
            redirect('admin/import/'.$enumImportType);
         }

         switch ($enumImportType){
            case CENUM_CONTEXT_PEOPLE:
               $this->loadFieldTypes_People();
               break;
            case CENUM_CONTEXT_BIZ:
               $this->loadFieldTypes_Biz();
               break;
            case CENUM_CONTEXT_GIFT:
               $this->loadFieldTypes_Gift();
               break;
            case CENUM_CONTEXT_SPONSORPAY:
               $this->loadFieldTypes_SponPay();
               break;
            case 'review':
               $this->loadFieldTypes_Review();
               break;
            default:
               screamForHelp($enumImportType.': invalid import type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }

         if ($this->clsImport->lNumFields == 0){
            $this->session->set_flashdata('error', 'Your input file does not contain any valid '.$enumImportType.' fields!'.'<br><b>Import canceled due to errors</b>');
            redirect('admin/import/'.$enumImportType);
         }

         $this->clsImport->setHeaderFields($this->csvreader->fields);
         if ($this->clsImport->bError){
            $this->session->set_flashdata('error', $this->clsImport->strErrMsg.'<br><b>Import canceled due to errors</b>');
            redirect('admin/import/'.$enumImportType);
         }


         if ($enumImportType == 'review'){
            $this->load->dbutil();
            $this->load->helper('download');
//            $this->clsImport->reviewPeopleBizCSV($userCSV);
            force_download('importReview.csv', $this->clsImport->reviewPeopleBizCSV($userCSV));

         }else {
            $this->clsImport->verifyCSV($userCSV);
            if ($this->clsImport->bError){
               $this->session->set_flashdata('error', $this->clsImport->strErrMsg.'<br><b>Import canceled due to errors</b>');
               redirect('admin/import/'.$enumImportType);
            }
            $this->strLoadGroups($enumImportType, true, $lGroupIDs);
            $lNumRecs = $this->clsImport->lInsertImport($userCSV, $lGroupIDs);
            redirect('admin/import/log');
         }
      }
   }

   function strLoadGroups($enumImportType, $bReload, &$lMatchIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strGroupsDDL = '';
      $lNumGroups = $this->groups->lCntActiveGroupsViaType($enumImportType);
      if ((($enumImportType == CENUM_CONTEXT_PEOPLE) ||
           ($enumImportType == CENUM_CONTEXT_BIZ))
           && ($lNumGroups > 0)){
         $lMatchIDs = array();
         if ($bReload && isset($_POST['ddlGroups'])){
            foreach ($_POST['ddlGroups'] as $lGroupID){
               $lMatchIDs[] = (int)$lGroupID;
            }
         }
         $strGroupsDDL =
            $this->groups->strDDLActiveGroupEntries(
                      'ddlGroups', $enumImportType, $lMatchIDs,
                      true, true);
      }
      return($strGroupsDDL);
   }

   function upImportFN(){
   //---------------------------------------------------------------------
   // notes on file upload callback at
   // http://codeigniter.com/forums/viewthread/74624/#780154
   //
   // Form validation is a little different when uploading a file
   // as part of the form. This validation routine actually uploads
   // the file. If any errors, return the upload errors as the
   // validation error. If no upload errors, set session variables with the
   // file upload info. Upon returning, if other validation errors
   // are detected, delete the temporary upload file.
   //---------------------------------------------------------------------
      $config['upload_path']   = './upload/';
         // see note at http://stackoverflow.com/questions/4731071/uploading-a-csv-into-codeigniter
         // regarding mime types for csv; also needed to change the mime type setting in config/mimes.php
      $config['allowed_types'] = 'csv|text/csv|text/plain|text/comma-separated-values|application/csv|text/anytext';
      $config['max_size']      = CI_IMPORT_MAXUPLOADKB;
      $config['encrypt_name']  = true;
      $this->load->library('upload', $config);

      if (!$this->upload->do_upload('userfile')) {
          $this->form_validation->set_message('upImportFN', $this->upload->display_errors());
          return(false);
      } else {
         $results = $this->upload->data();
          $_SESSION[CS_NAMESPACE.'uploadResults'] = $results;
          return(true);
      }
   }

   private function loadFieldTypes_Review(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterACO, $glUserID;
      $this->load->model('people/mpeople', 'clsPeople');
      $this->load->helper('email');

      $this->clsImport->strImportTable = 'people_names';
      $this->clsImport->addImportField('Title',              'text',        80, '', false, 'strTitle');
      $this->clsImport->addImportField('Last Name',          'text',        80, '', false, 'strLName');
      $this->clsImport->addImportField('First Name',         'text',        80, '', false, 'strFName');
      $this->clsImport->addImportField('Business Name',      'text',        80, '', false, 'strBizName');
      $this->clsImport->addImportField('Address 1',          'text',        80, '', false, 'strAddr1');
      $this->clsImport->addImportField('Address 2',          'text',        80, '', false, 'strAddr2');
      $this->clsImport->addImportField('City',               'text',        80, '', false, 'strCity');
      $this->clsImport->addImportField('State',              'text',        40, '', false, 'strState');
      $this->clsImport->addImportField('Country',            'text',        40, '', false, 'strCountry');
      $this->clsImport->addImportField('Zip',                'text',        40, '', false, 'strZip');
      $this->clsImport->addImportField('Phone',              'text',        40, '', false, 'strPhone');
      $this->clsImport->addImportField('Cell',               'text',        40, '', false, 'strCell');
      $this->clsImport->addImportField('Email',              'email',      140, '', false, 'strEmail');

//      $this->clsImport->addImportField('Notes',              'text',        -1, '', false, 'pe_strNotes');
//      $this->clsImport->addImportField('Middle Name',        'text',        80, '', false, 'pe_strMName');
//      $this->clsImport->addImportField('Preferred Name',     'text',        80, '#First Name', false, 'pe_strPreferredName');
//      $this->clsImport->addImportField('Salutation',         'text',        80, '#First Name', false, 'pe_strSalutation');
//      $this->clsImport->addImportField('Gender',             'enumGender',  20, 'Unknown',     false, 'pe_enumGender');
//      $this->clsImport->addImportField('Import ID',          'text',        40, '', false, 'pe_strImportID');
//      $this->clsImport->addImportField('Accounting Country', 'aco',         40, strtolower($gclsChapterACO->strName),
//                                                                                    false, 'pe_lACO');
//      $this->clsImport->addImportField('Birth Date',         'mysqlDate',   40, '', false, 'pe_dteBirthDate');
   }

   private function loadFieldTypes_People(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterACO, $glUserID;
      $this->load->model('people/mpeople', 'clsPeople');
      $this->load->helper('email');

      $this->clsImport->forceSet = array();
      $this->clsImport->strImportTable = 'people_names';

      $this->clsImport->forceSet[0] = array('fn' => 'pe_lOriginID',      'value' => $glUserID.'');
      $this->clsImport->forceSet[1] = array('fn' => 'pe_lLastUpdateID',  'value' => $glUserID.'');
      $this->clsImport->forceSet[2] = array('fn' => 'pe_dteOrigin',      'value' => 'NOW()');
      $this->clsImport->forceSet[3] = array('fn' => 'pe_dteLastUpdate',  'value' => 'NOW()');
      $this->clsImport->forceSet[4] = array('fn' => 'pe_bBiz',           'value' => '0');
      $this->clsImport->forceSet[5] = array('fn' => 'pe_lBizIndustryID', 'value' => 'NULL');

      $this->clsImport->addImportField('Title',              'text',        80, '', false, 'pe_strTitle');
      $this->clsImport->addImportField('Last Name',          'text',        80, '', true,  'pe_strLName');
      $this->clsImport->addImportField('First Name',         'text',        80, '', true,  'pe_strFName');
      $this->clsImport->addImportField('Middle Name',        'text',        80, '', false, 'pe_strMName');
      $this->clsImport->addImportField('Preferred Name',     'text',        80, '#First Name', false, 'pe_strPreferredName');
      $this->clsImport->addImportField('Salutation',         'text',        80, '#First Name', false, 'pe_strSalutation');
      $this->clsImport->addImportField('Gender',             'enumGender',  20, 'Unknown',     false, 'pe_enumGender');
      $this->clsImport->addImportField('Address 1',          'text',        80, '', false, 'pe_strAddr1');
      $this->clsImport->addImportField('Address 2',          'text',        80, '', false, 'pe_strAddr2');
      $this->clsImport->addImportField('City',               'text',        80, '', false, 'pe_strCity');
      $this->clsImport->addImportField('State',              'text',        40, '', false, 'pe_strState');
      $this->clsImport->addImportField('Country',            'text',        40, '', false, 'pe_strCountry');
      $this->clsImport->addImportField('Zip',                'text',        40, '', false, 'pe_strZip');
      $this->clsImport->addImportField('Notes',              'text',        -1, '', false, 'pe_strNotes');

      $this->clsImport->addImportField('Phone',              'text',        40, '', false, 'pe_strPhone');
      $this->clsImport->addImportField('Cell',               'text',        40, '', false, 'pe_strCell');
      $this->clsImport->addImportField('Email',              'email',      140, '', false, 'pe_strEmail');
      $this->clsImport->addImportField('Import ID',          'text',        40, '', false, 'pe_strImportID');
      $this->clsImport->addImportField('Accounting Country', 'aco',         40, strtolower($gclsChapterACO->strName),
                                                                                    false, 'pe_lACO');
      $this->clsImport->addImportField('Birth Date',         'mysqlDate',   40, '', false, 'pe_dteBirthDate');
   }

   private function loadFieldTypes_Biz(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterACO, $glUserID;
      $this->load->model('people/mpeople', 'clsPeople');
      $this->load->model('util/mlist_generic', 'clsList');
      $this->load->helper('email');

      $this->clsImport->forceSet = array();
      $this->clsImport->strImportTable = 'people_names';

      $this->clsImport->initGenericListTable(0, CENUM_LISTTYPE_BIZCAT, $this->clsList);

      $this->clsImport->forceSet[ 0] = array('fn' => 'pe_lOriginID',        'value' => $glUserID.'');
      $this->clsImport->forceSet[ 1] = array('fn' => 'pe_lLastUpdateID',    'value' => $glUserID.'');
      $this->clsImport->forceSet[ 2] = array('fn' => 'pe_dteOrigin',        'value' => 'NOW()');
      $this->clsImport->forceSet[ 3] = array('fn' => 'pe_dteLastUpdate',    'value' => 'NOW()');
      $this->clsImport->forceSet[ 4] = array('fn' => 'pe_bBiz',             'value' => '1');

      $this->clsImport->forceSet[ 5] = array('fn' => 'pe_strTitle',         'value' => '""');
      $this->clsImport->forceSet[ 6] = array('fn' => 'pe_strFName',         'value' => '""');
      $this->clsImport->forceSet[ 7] = array('fn' => 'pe_strMName',         'value' => '""');
      $this->clsImport->forceSet[ 8] = array('fn' => 'pe_enumGender',       'value' => '"Unknown"');
      $this->clsImport->forceSet[ 9] = array('fn' => 'pe_strPreferredName', 'value' => '""');
      $this->clsImport->forceSet[10] = array('fn' => 'pe_strSalutation',    'value' => '""');
      $this->clsImport->forceSet[11] = array('fn' => 'pe_dteBirthDate',     'value' => 'null');

      $this->clsImport->addImportField('Business Name',      'text',        80, '', true,    'pe_strLName');
      $this->clsImport->addImportField('Address 1',          'text',        80, '', false,   'pe_strAddr1');
      $this->clsImport->addImportField('Address 2',          'text',        80, '', false,   'pe_strAddr2');
      $this->clsImport->addImportField('City',               'text',        80, '', false,   'pe_strCity');
      $this->clsImport->addImportField('State',              'text',        40, '', false,   'pe_strState');
      $this->clsImport->addImportField('Country',            'text',        40, '', false,   'pe_strCountry');
      $this->clsImport->addImportField('Zip',                'text',        40, '', false,   'pe_strZip');

      $this->clsImport->addImportField('Notes',              'text',        -1, '', false,   'pe_strNotes');
      $this->clsImport->addImportField('Phone',              'text',        40, '', false,   'pe_strPhone');
      $this->clsImport->addImportField('Email',              'email',      140, '', false,   'pe_strEmail');
      $this->clsImport->addImportField('Import ID',          'text',        40, '', false,   'pe_strImportID');
      $this->clsImport->addImportField('Accounting Country', 'aco',         40, strtolower($gclsChapterACO->strName),
                                                                                    false,   'pe_lACO');
      $this->clsImport->addImportField('Business Category',  'genList',      40, null, false, 'pe_lBizIndustryID', 0);
   }

   private function loadFieldTypes_SponPay(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterACO, $glUserID;
      $this->load->model('people/mpeople', 'clsPeople');
      $this->load->model('util/mlist_generic', 'clsList');
      $this->load->model('donations/maccts_camps', 'clsAC');
      $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');

      $lSponCamp = $this->clsSCP->lSponCampID();

      $this->clsImport->forceSet = array();
      $this->clsImport->strImportTable = 'gifts';

      $this->clsImport->initGenericListTable(0, CENUM_LISTTYPE_GIFTPAYTYPE,  $this->clsList);

      $this->clsImport->forceSet[ 0] = array('fn' => 'gi_lOriginID',        'value' => $glUserID.'');
      $this->clsImport->forceSet[ 1] = array('fn' => 'gi_lLastUpdateID',    'value' => $glUserID.'');
      $this->clsImport->forceSet[ 2] = array('fn' => 'gi_dteOrigin',        'value' => 'NOW()');
      $this->clsImport->forceSet[ 3] = array('fn' => 'gi_dteLastUpdate',    'value' => 'NOW()');
      $this->clsImport->forceSet[ 4] = array('fn' => 'gi_lCampID',          'value' => $lSponCamp);

      $this->clsImport->addImportField('Donor ID',            'int',         0, '', true,    'gi_lForeignID');
      $this->clsImport->addImportField('Sponsor ID',          'int',         0, '', true,    'gi_lSponsorID');
      $this->clsImport->addImportField('Amount',              'currency',   20, '', true,    'gi_curAmnt');
      $this->clsImport->addImportField('Accounting Country',  'aco',        40,  strtolower($gclsChapterACO->strName),
                                                                                     false,   'gi_lACOID');

      $this->clsImport->addImportField('Payment Date',        'mysqlDate',   40, '',     true,    'gi_dteDonation');
      $this->clsImport->addImportField('Payment Type',        'genList',     40, '',     true,    'gi_lPaymentType',  0);
      $this->clsImport->addImportField('Check Number',        'text',       255, '',     false,   'gi_strCheckNum');
      $this->clsImport->addImportField('Import ID',           'text',        40, '',     false,   'gi_strImportID');
   }

   private function loadFieldTypes_Gift(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterACO, $glUserID;
      $this->load->model('people/mpeople', 'clsPeople');
      $this->load->model('util/mlist_generic', 'clsList');

      $this->clsImport->forceSet = array();
      $this->clsImport->strImportTable = 'gifts';

      $this->clsImport->initGenericListTable(0, CENUM_LISTTYPE_INKIND,       $this->clsList);
      $this->clsImport->initGenericListTable(1, CENUM_LISTTYPE_MAJORGIFTCAT, $this->clsList);
      $this->clsImport->initGenericListTable(2, CENUM_LISTTYPE_GIFTPAYTYPE,  $this->clsList);

      $this->clsImport->forceSet[ 0] = array('fn' => 'gi_lOriginID',        'value' => $glUserID.'');
      $this->clsImport->forceSet[ 1] = array('fn' => 'gi_lLastUpdateID',    'value' => $glUserID.'');
      $this->clsImport->forceSet[ 2] = array('fn' => 'gi_dteOrigin',        'value' => 'NOW()');
      $this->clsImport->forceSet[ 3] = array('fn' => 'gi_dteLastUpdate',    'value' => 'NOW()');
      $this->clsImport->forceSet[ 4] = array('fn' => 'gi_lSponsorID',       'value' => 'null');

      $this->clsImport->addImportField('Donor ID',            'int',         0, '', true,    'gi_lForeignID');
      $this->clsImport->addImportField('Account',             'Account',    80, '', true,    null);
      $this->clsImport->addImportField('Campaign',            'Campaign',   80, '', true,    'gi_lCampID');
      $this->clsImport->addImportField('Amount',              'currency',   20, '', true,    'gi_curAmnt');
      $this->clsImport->addImportField('Accounting Country',  'aco',        40,  strtolower($gclsChapterACO->strName),
                                                                                     false,   'gi_lACOID');

      $this->clsImport->addImportField('Notes',               'text',        -1, '',     false,   'gi_strNotes');
      $this->clsImport->addImportField('Date of Donation',    'mysqlDate',   40, '',     true,    'gi_dteDonation');
      $this->clsImport->addImportField('In-Kind Type',        'genList',     40, 'null', false,   'gi_lGIK_ID',       0);
      $this->clsImport->addImportField('Major Gift Category', 'genList',     40, 'null', false,   'gi_lMajorGiftCat', 1);
      $this->clsImport->addImportField('Payment Type',        'genList',     40, '',     true,    'gi_lPaymentType',  2);
      $this->clsImport->addImportField('Check Number',        'text',       255, '',     false,   'gi_strCheckNum');
      $this->clsImport->addImportField('Import ID',           'text',        40, '',     false,   'gi_strImportID');
   }

   function log(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();

         //-------------------------
         // load models and helpers
         //-------------------------
      $this->load->model('admin/mimport', 'clsImport');

      $this->clsImport->loadImportLog(false, null);
      $displayData['logEntries']     = &$this->clsImport->logEntries;
      $displayData['lNumLogEntries'] = $this->clsImport->lNumLogEntries;

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                .' | Import Log';

      $displayData['title']          = CS_PROGNAME.' | Import Log';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'admin/import_log_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function logDetails($lImportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;

      $displayData = array();
      $displayData['lImportID'] = $lImportID = (integer)$lImportID;

         //-------------------------
         // load models and helpers
         //-------------------------
      $this->load->model('admin/mimport', 'clsImport');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->helper('dl_util/web_layout');

      $this->clsImport->loadImportLog(true, $lImportID);
      $displayData['logEntry']       = &$this->clsImport->logEntries[0];
      $displayData['lNumLogEntries'] = $this->clsImport->lNumLogEntries;

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

      $enumImportType = $this->clsImport->logEntries[0]->enumImportType;
      $displayData['importLogEntry'] = &$this->clsImport->logEntries[0];

      $this->loadLogDetailModels($enumImportType);

      $this->loadImportDetails($lImportID, $enumImportType, $displayData);

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['pageTitle']    = anchor('main/menu/admin',  'Admin',      'class="breadcrumb"')
                              .' | '.anchor('admin/import/log', 'Import Log', 'class="breadcrumb"')
                              .' | Import Details';

      $displayData['title']        = CS_PROGNAME.' | Import Log';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function loadLogDetailModels($enumImportType){
      switch ($enumImportType){
         case CENUM_CONTEXT_PEOPLE:
//            $this->load->helper('dl_util/email_web');
            $this->load->model('people/mpeople',   'clsPeople');
            $this->load->model('admin/madmin_aco', 'clsACO');
            break;
         case CENUM_CONTEXT_BIZ:
//            $this->load->helper('dl_util/email_web');
            $this->load->model('biz/mbiz',         'clsBiz');
            $this->load->model('admin/madmin_aco', 'clsACO');
            break;
         case CENUM_CONTEXT_GIFT:
//            $this->load->helper('dl_util/email_web');
            $this->load->model('admin/madmin_aco', 'clsACO');
            $this->load->model('donations/mdonations', 'clsGifts');
            break;
         case CENUM_CONTEXT_SPONSORPAY:
//            $this->load->helper('dl_util/email_web');
            $this->load->model('admin/madmin_aco', 'clsACO');
            $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
            break;
         default:
            screamForHelp($enumImportType.': invalid import type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }


   public function loadImportDetails($lImportID, $enumImportType, &$displayData){
   //---------------------------------------------------------------------
   // caller must first call $this->loadImportLog(true, $lImportID);
   //---------------------------------------------------------------------
      $this->importDetails = array();
      $this->clsImport->loadForeignIDs($lImportID);

//      $this->clsImport->loadImportLog(true, $lImportID);
      switch ($enumImportType){
         case CENUM_CONTEXT_PEOPLE:
            $this->importDetailsPeople($lImportID, $displayData);
            break;
         case CENUM_CONTEXT_BIZ:
            $this->importDetailsBiz($lImportID, $displayData);
            break;
         case CENUM_CONTEXT_GIFT:
            $this->importDetailsGifts($lImportID, $displayData);
            break;
         case CENUM_CONTEXT_SPONSORPAY:
            $this->importDetailsSponPay($lImportID, $displayData);
            break;
         default:
            screamForHelp($enumImportType.': invalid import type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   private function importDetailsSponPay($lImportID, &$displayData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($this->clsImport->lNumFIDs > 0){
         $this->clsSCP->loadPayRecordViaPayID($this->clsImport->foreignIDs);
         $displayData['lNumSponPay'] = $this->clsSCP->lNumPayRecs;
         $displayData['paymentRecs'] = &$this->clsSCP->paymentRec;
      }else {
         $displayData['lNumSponPay'] = 0;
      }

      $displayData['strRptTitle']   = 'Import Log Details';
      $displayData['mainTemplate'] = array('admin/import_summary_view',
                                           'admin/import_details_spon_pay');
   }

   private function importDetailsGifts($lImportID, &$displayData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($this->clsImport->lNumFIDs > 0){
         $this->clsGifts->loadGiftViaGID($this->clsImport->foreignIDs);
         $displayData['lNumGifts'] = $this->clsGifts->lNumGifts;
         $displayData['gifts']     = &$this->clsGifts->gifts;
      }else {
         $displayData['lNumGifts'] = 0;
      }

      $displayData['strRptTitle']   = 'Import Log Details';
      $displayData['mainTemplate'] = array('admin/import_summary_view',
                                           'admin/import_details_gifts');
   }

   private function importDetailsPeople($lImportID, &$displayData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('people/people');

      initPeopleReportDisplay($displayData);
      $displayData['showFields']->bGiftSummary     = false;
      $displayData['showFields']->bSponsor         = false;
      $displayData['showFields']->bImportID        = true;
      $displayData['showFields']->deleteReturnPath = 'importLog';
      $displayData['showFields']->lReturnPathID    = $lImportID;

      $displayData['strPeopleType'] = CENUM_CONTEXT_PEOPLE;
      $displayData['strRptTitle']   = 'Import Log Details';
      $displayData['mainTemplate'] = array('admin/import_summary_view',
                                           'people/rpt_generic_people_list');

      if ($this->clsImport->lNumFIDs > 0){

         $strPIDs = implode(',', $this->clsImport->foreignIDs);
         $this->clsPeople->sqlWhereExtra = "AND pe_lKeyID IN ($strPIDs) ";
         $this->clsPeople->loadPeople(false, false, true);
         $displayData['lNumPeople'] = $this->clsPeople->lNumPeople;
         $displayData['people']     = &$this->clsPeople->people;
      }else {
         $displayData['lNumPeople'] = 0;
      }
   }

   private function importDetailsBiz($lImportID, &$displayData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('biz/biz');

      initBizReportDisplay($displayData);
      $displayData['showFields']->bGiftSummary     = false;
      $displayData['showFields']->bSponsor         = false;
      $displayData['showFields']->bContacts        = false;
      $displayData['showFields']->bImportID        = true;
      $displayData['showFields']->deleteReturnPath = 'importLog';
      $displayData['showFields']->lReturnPathID    = $lImportID;

      $displayData['strRptTitle']   = 'Import Log Details';
      $displayData['mainTemplate'] = array('admin/import_summary_view',
                                           'biz/rpt_generic_biz_list');

      if ($this->clsImport->lNumFIDs > 0){
         $strPIDs = implode(',', $this->clsImport->foreignIDs);
         $this->clsBiz->sqlWhereExtra = "AND pe_lKeyID IN ($strPIDs) ";
         $this->clsBiz->loadBizRecs(false, false);
         $displayData['lNumDisplayRows'] = $this->clsBiz->lNumBizRecs;
         $displayData['bizRecs']         = &$this->clsBiz->bizRecs;
      }else {
         $displayData['lNumDisplayRows'] = 0;
      }
   }



      /* -----------------------------------------------------------------
               Personalized table import
         ----------------------------------------------------------------- */
   function ptableParentSelect(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $displayData['js']      = '';
      
         //------------------------------------------------
         // models, libraries, and utilities
         //------------------------------------------------
      $this->load->library('generic_form');
      $this->load->library('js_build/ajax_support');
      $this->load->model  ('admin/mimport', 'clsImport');
      $this->load->helper ('dl_util/web_layout');

      
         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('ddlParent', 'Parent Table',        'trim|required|callback_verifyParentSel');
		$this->form_validation->set_rules('ddlPTable', 'Personalized Table',  'trim|callback_verifyPTableSel');
      
		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
      
      
            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['formData']->strDDLParent = $this->clsImport->strParentTableDDL('');
         }else {
            $displayData['formData']->strDDLParent = $this->clsImport->strParentTableDDL(set_value('ddlParent'));
         }
      
            //------------------------------------------------
            // breadcrumbs / page setup
            //------------------------------------------------
         $displayData['pageTitle']    = anchor('main/menu/admin',  'Admin',      'class="breadcrumb"')
                                 .' | Personalized Table Import';

         $displayData['title']        = CS_PROGNAME.' | Import';
         $displayData['nav']          = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'admin/import_ptable_sel_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
      }
   }

   function parentSel(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();
      $displayData['enumPTableType'] = $enumAttachType = trim($_POST['ddlParentSel']);
//      $displayData['ddlGroups']      = '';

         //------------------------------------------------
         // models, libraries, and utilities
         //------------------------------------------------
      $this->load->library('generic_form');
      $this->load->helper ('dl_util/web_layout');
      $this->load->model  ('personalization/muser_schema', 'cUFSchema');

         // load the schema for the selected parent table type
      $this->cUFSchema->loadUFSchemaViaAttachType($enumAttachType, false);
      $displayData['bNoTables'] = $this->cUFSchema->lNumTables == 0;
      
// -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$this->cUFSchema->schema   <pre>');
echo(htmlspecialchars( print_r($this->cUFSchema->schema, true))); echo('</pre></font><br>');
// -------------------------------------
      

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['pageTitle']    = anchor('main/menu/admin',  'Admin',      'class="breadcrumb"')
                              .' | Personalized Table Import';

      $displayData['title']        = CS_PROGNAME.' | Import';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'admin/import_ptable_opts_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }







}
