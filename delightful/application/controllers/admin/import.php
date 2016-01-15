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

   public function client(){
      if (!bTestForURLHack('adminOnly')) return;
      $this->importPrep(CENUM_CONTEXT_CLIENT);
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

   public function pTableImportPrep($enumType, $lTableID){
      if (!bTestForURLHack('adminOnly')) return;

         // redirect required because of an idiosychracy in codeIgniter validation
      redirect('admin/import/importPrep/'.$enumType.'/'.$lTableID);
   }

   public function personalizedTable($lTableID){
      if (!bTestForURLHack('adminOnly')) return;
      $this->importPrep(CENUM_CONTEXT_PTABLE, (int)$lTableID);
   }

   public function importPrep($enumImportType, $lTableID=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();
      $displayData['enumImportType'] = $enumImportType;
      $displayData['ddlGroups']      = '';
      $displayData['bPTableImport']  = $bPTableImport = $enumImportType == CENUM_CONTEXT_PTABLE;
      $displayData['lTableID'] = $lTableID = (int)$lTableID;

         //------------------------------------------------
         // models, libraries, and utilities
         //------------------------------------------------
      $this->load->model  ('groups/mgroups', 'groups');
      $this->load->helper ('groups/groups');

      if ($bPTableImport){
         $this->load->model  ('personalization/muser_schema', 'cSchema');
         $this->cSchema->loadUFSchemaSingleTable($lTableID);
         $displayData['utable'] = $utable = &$this->cSchema->schema[$lTableID];
         $enumAttachType = $utable->enumAttachType;
      }else {
         $enumAttachType = null;
         $utable         = null;
      }

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
         $this->load->model  ('clients/mclients');
         $this->load->model  ('admin/mimport', 'clsImport');
         $this->load->model  ('admin/madmin_aco', 'clsACO');
         $this->load->helper ('dl_util/time_date');

         if ($enumImportType==CENUM_CONTEXT_CLIENT){
            $this->load->helper('email');
            $this->load->library('util/dl_date_time', '', 'clsDateTime');
            $this->load->model('clients/mclient_status');
            $this->load->model('clients/mclient_vocabulary');
            $this->load->model('sponsorship/msponsorship_programs');
         }

         $this->clsImport->initImportClass($enumImportType);
         $this->clsImport->initACOTable($this->clsACO);

         $uploadResults = $_SESSION[CS_NAMESPACE.'uploadResults'];
         $strUserFN     = $uploadResults['orig_name'];
         $strFN         = $uploadResults['file_name'];
         $strPathFN     = $uploadResults['full_path'];

         $userCSV = $this->csvreader->parse_file($strPathFN, true, true);

         if ($userCSV === false){
            $this->session->set_flashdata('error', 'Your CSV file contains no records!'.'<br><b>Import canceled due to errors</b>');
            if ($bPTableImport){
               redirect('admin/import/importPrep/pTableImportPrep/'.$lTableID);
            }else {
               redirect('admin/import/'.$enumImportType.'/'.$lTableID);
            }
         }

         switch ($enumImportType){
            case CENUM_CONTEXT_PEOPLE:
               $this->load->model('vols/mvol', 'clsVol');
               $this->load->model('admin/mpermissions', 'perms');
               $this->load->model('personalization/muser_fields',        'clsUF');
               $this->load->model('personalization/muser_fields_create', 'clsUFC');
               $this->loadFieldTypes_People();
               break;
            case CENUM_CONTEXT_BIZ:
               $this->loadFieldTypes_Biz();
               break;
            case CENUM_CONTEXT_CLIENT:
               $this->loadFieldTypes_Client();
               break;
            case CENUM_CONTEXT_GIFT:
               $this->loadFieldTypes_Gift();
               break;
            case CENUM_CONTEXT_SPONSORPAY:
               $this->loadFieldTypes_SponPay();
               break;
            case CENUM_CONTEXT_PTABLE:
               $this->load->model('personalization/muser_schema', 'cUFSchema');
               $this->cUFSchema->loadUFSchemaSingleTable($lTableID);
               $utable = &$this->cUFSchema->schema[$lTableID];
               $this->loadFieldTypes_pTable($utable);
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
            redirect('admin/import/'.$enumImportType.'/'.$lTableID);
         }

         $this->clsImport->setHeaderFields($this->csvreader->fields);
         if ($this->clsImport->bError){
            $this->session->set_flashdata('error', $this->clsImport->strErrMsg.'<br><b>Import canceled due to errors</b>');
            redirect('admin/import/'.$enumImportType.'/'.$lTableID);
         }


         if ($enumImportType == 'review'){
            $this->load->dbutil();
            $this->load->helper('download');
            force_download('importReview.csv', $this->clsImport->reviewPeopleBizCSV($userCSV));

         }else {
            $this->clsImport->verifyCSV($userCSV, $enumAttachType);
            if ($this->clsImport->bError){
               $this->session->set_flashdata('error', $this->clsImport->strErrMsg.'<br><b>Import canceled due to errors</b>');
               redirect('admin/import/'.$enumImportType.'/'.$lTableID);
            }
            $this->strLoadGroups($enumImportType, true, $lGroupIDs);
            $lNumRecs = $this->clsImport->lInsertImport($userCSV, $lGroupIDs, $utable);
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

      $this->clsImport->addImportField('Volunteer',          'YesNo',       40, false, false,   'isVolunteer');
   }

   private function loadFieldTypes_Client(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $this->clsImport->forceSet = array();
      $this->clsImport->strImportTable = 'client_records';

      $this->clsImport->forceSet[0] = array('fn' => 'cr_lOriginID',      'value' => $glUserID.'');
      $this->clsImport->forceSet[1] = array('fn' => 'cr_lLastUpdateID',  'value' => $glUserID.'');
      $this->clsImport->forceSet[2] = array('fn' => 'cr_dteOrigin',      'value' => 'NOW()');
      $this->clsImport->forceSet[3] = array('fn' => 'cr_dteLastUpdate',  'value' => 'NOW()');

      $this->clsImport->addImportField('Last Name',          'text',        80, '', true,  'cr_strLName');
      $this->clsImport->addImportField('First Name',         'text',        80, '', true,  'cr_strFName');
      $this->clsImport->addImportField('Middle Name',        'text',        80, '', false, 'cr_strMName');

      $this->clsImport->addImportField('Gender',             'enumGender',  20, 'Unknown',     true, 'cr_enumGender');
      $this->clsImport->addImportField('Address 1',          'text',        80, '', false, 'cr_strAddr1');
      $this->clsImport->addImportField('Address 2',          'text',        80, '', false, 'cr_strAddr2');
      $this->clsImport->addImportField('City',               'text',        80, '', false, 'cr_strCity');
      $this->clsImport->addImportField('State',              'text',        40, '', false, 'cr_strState');
      $this->clsImport->addImportField('Country',            'text',        40, '', false, 'cr_strCountry');
      $this->clsImport->addImportField('Zip',                'text',        40, '', false, 'cr_strZip');

      $this->clsImport->addImportField('Phone',              'text',        40, '', false, 'cr_strPhone');
      $this->clsImport->addImportField('Cell',               'text',        40, '', false, 'cr_strCell');
      $this->clsImport->addImportField('Email',              'email',      140, '', false, 'cr_strEmail');
      $this->clsImport->addImportField('Import ID',          'text',        40, '', false, 'cr_strImportID');
      $this->clsImport->addImportField('Max # Sponsors',     'int',          0, '', true,  'cr_lMaxSponsors');

      $this->clsImport->addImportField('Birth Date',              'mysqlDate',     40, '', true,  'cr_dteBirth');
      $this->clsImport->addImportField('Date Entered in Program', 'mysqlDate',     40, '', true,  'cr_dteEnrollment');
      $this->clsImport->addImportField('Bio',                     'text',           0, '', false, 'cr_strBio');

      $this->clsImport->addImportField('Location',             'clientSpecial',   80, '', true,  'cr_lLocationID');
      $this->clsImport->addImportField('Status Category',      'clientSpecial',   80, '', true,  'cr_lStatusCatID');
      $this->clsImport->addImportField('Vocabulary',           'clientSpecial',   80, '', true,  'cr_lVocID');
      $this->clsImport->addImportField('Sponsorship Programs', 'clientSpecial',   80, '', false, 'sponProg');
      $this->clsImport->addImportField('Initial Status',       'clientSpecial',   80, '', true,  'initialStatus');
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
      $displayData['importLogEntry'] = $logEntry = &$this->clsImport->logEntries[0];

      $this->loadLogDetailModels($enumImportType, $logEntry);

      $this->loadImportDetails($lImportID, $enumImportType, $displayData, $logEntry);

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

   function loadLogDetailModels($enumImportType, $logEntry){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumImportType){
         case CENUM_CONTEXT_PEOPLE:
            $this->load->model('people/mpeople',   'clsPeople');
            $this->load->model('admin/madmin_aco', 'clsACO');
            break;
         case CENUM_CONTEXT_BIZ:
            $this->load->model('biz/mbiz',         'clsBiz');
            $this->load->model('admin/madmin_aco', 'clsACO');
            break;
         case CENUM_CONTEXT_CLIENT:
            $this->load->library('util/dl_date_time', '', 'clsDateTime');
            $this->load->model('clients/mclients', 'clsClients');
            break;
         case CENUM_CONTEXT_GIFT:
            $this->load->model('admin/madmin_aco', 'clsACO');
            $this->load->model('donations/mdonations', 'clsGifts');
            break;
         case CENUM_CONTEXT_SPONSORPAY:
            $this->load->model('admin/madmin_aco', 'clsACO');
            $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
            break;

         case CENUM_CONTEXT_PTABLE:
            $this->load->model('personalization/muser_fields', 'clsUF');
            switch ($logEntry->enumAttachType){
               case CENUM_CONTEXT_PEOPLE:
                  $this->load->model('people/mpeople',   'clsPeople');
                  $this->load->model('admin/madmin_aco', 'clsACO');
                  break;
               case CENUM_CONTEXT_BIZ:
                  $this->load->model('biz/mbiz',         'clsBiz');
                  $this->load->model('admin/madmin_aco', 'clsACO');
                  break;
               case CENUM_CONTEXT_CLIENT:
                  $this->load->library('util/dl_date_time', '', 'clsDateTime');
                  $this->load->model('clients/mclients', 'clsClients');
                  break;
               case CENUM_CONTEXT_GIFT:
                  $this->load->model('admin/madmin_aco', 'clsACO');
                  $this->load->model('donations/mdonations', 'clsGifts');
                  break;
               case CENUM_CONTEXT_USER:
                  $this->load->model ('admin/muser_accts',  'clsUsers');
                  $this->load->model ('admin/mpermissions', 'perms');
                  break;
               case CENUM_CONTEXT_SPONSORSHIP:
                  $this->load->helper('people/people');
                  $this->load->helper('people/people_display');
                  $this->load->model ('sponsorship/msponsorship',          'clsSpon');
                  $this->load->model ('sponsorship/msponsorship_programs', 'clsSponProg');
                  $this->load->helper('sponsors/sponsorship');
                  break;
               case CENUM_CONTEXT_VOLUNTEER:
                  $this->load->model('people/mpeople',   'clsPeople');
                  $this->load->model('vols/mvol',        'clsVol');
                  $this->load->model ('vols/mvol_skills', 'clsVolSkills');
                  $this->load->helper('vols/vol');
//                  $this->load->model('admin/madmin_aco', 'clsACO');
                  break;

               default:
                  screamForHelp($logEntry->enumAttachType.': invalid import type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }
            break;
         default:
            screamForHelp($enumImportType.': invalid import type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   public function loadImportDetails($lImportID, $enumImportType, &$displayData, $logEntry){
   //---------------------------------------------------------------------
   // caller must first call $this->loadImportLog(true, $lImportID);
   //---------------------------------------------------------------------
      $this->importDetails = array();
      $this->clsImport->loadForeignIDs($lImportID);
      $displayData['bPTable'] = false;

      switch ($enumImportType){
         case CENUM_CONTEXT_PEOPLE:
            $this->importDetailsPeople($lImportID, $displayData);
            break;
         case CENUM_CONTEXT_BIZ:
            $this->importDetailsBiz($lImportID, $displayData);
            break;
         case CENUM_CONTEXT_CLIENT:
            $this->importDetailsClient($lImportID, $displayData);
            break;
         case CENUM_CONTEXT_GIFT:
            $this->importDetailsGifts($lImportID, $displayData);
            break;
         case CENUM_CONTEXT_SPONSORPAY:
            $this->importDetailsSponPay($lImportID, $displayData);
            break;
         case CENUM_CONTEXT_PTABLE:
            $displayData['bPTable'] = true;
            switch ($logEntry->enumAttachType){
               case CENUM_CONTEXT_PEOPLE:
                  $this->importDetailsPeople($lImportID, $displayData, true, $logEntry);
                  break;
               case CENUM_CONTEXT_BIZ:
                  $this->importDetailsBiz($lImportID, $displayData, true, $logEntry);
                  break;
               case CENUM_CONTEXT_CLIENT:
                  $this->importDetailsClient($lImportID, $displayData, true, $logEntry);
                  break;
               case CENUM_CONTEXT_GIFT:
                  $this->importDetailsGifts($lImportID, $displayData, true, $logEntry);
                  break;
               case CENUM_CONTEXT_SPONSORSHIP:
                  $this->importDetailsSponsors($lImportID, $displayData, true, $logEntry);
                  break;
               case CENUM_CONTEXT_USER:
                  $this->importDetailsUsers($lImportID, $displayData, true, $logEntry);
                  break;
               case CENUM_CONTEXT_VOLUNTEER:
                  $this->importDetailsVolunteers($lImportID, $displayData, true, $logEntry);
                  break;

               default:
                  screamForHelp($logEntry->enumAttachType.': invalid import type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }
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

   private function importDetailsGifts($lImportID, &$displayData, $bViaPTable=false, $logEntry=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($this->clsImport->lNumFIDs > 0){
         if ($bViaPTable){
            $lGIDs = $this->clsImport->pTableForeignIDsViaImportID(
                                       $lImportID, $logEntry->lUTableID, $lNumFIDs);
         }else {
            $lGIDs = $this->clsImport->foreignIDs;
         }

         $this->clsGifts->loadGiftViaGID($lGIDs);
         $displayData['lNumGifts'] = $this->clsGifts->lNumGifts;
         $displayData['gifts']     = &$this->clsGifts->gifts;
      }else {
         $displayData['lNumGifts'] = 0;
      }

      $displayData['strRptTitle']   = 'Import Log Details';
      $displayData['mainTemplate'] = array('admin/import_summary_view',
                                           'admin/import_details_gifts');
   }

   private function importDetailsSponsors($lImportID, &$displayData, $bViaPTable=false, $logEntry=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData['lNumSponProgs'] = $lNumSponProgs = $this->clsSponProg->lNumSponPrograms();
      $displayData['strRptTitle']   = 'Import Log Details';
      initSponReportDisplay($displayData);

      if ($this->clsImport->lNumFIDs > 0){
         if ($bViaPTable){
            $strPIDs = implode(',',
                          $this->clsImport->pTableForeignIDsViaImportID(
                                       $lImportID, $logEntry->lUTableID, $lNumFIDs));
            $displayData['lNumDisplayRows'] = $lNumFIDs;
         }else {
            screamForHelp('Sponsorship: invalid import type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         }
         $strWhereExtra = " AND sp_lKeyID IN ($strPIDs) ";
         $this->clsSpon->sponsorInfoGenericViaWhere($strWhereExtra, '');
         $displayData['sponInfo']  = &$this->clsSpon->sponInfo;
         $displayData['mainTemplate'] = array('admin/import_summary_view',
                                              'sponsorship/rpt_generic_spon_list');
      }else {
         $displayData['lNumDisplayRows'] = 0;
      }
   }

   private function importDetailsUsers($lImportID, &$displayData, $bViaPTable=false, $logEntry=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData['strRptTitle']   = 'Import Log Details';
      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

      if ($this->clsImport->lNumFIDs > 0){
         if ($bViaPTable){
            $strUIDs = implode(',',
                          $this->clsImport->pTableForeignIDsViaImportID(
                                       $lImportID, $logEntry->lUTableID, $lNumFIDs));
            $displayData['lNumDisplayRows'] = $lNumFIDs;
         }else {
            screamForHelp('Users: invalid import type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         }
         $strWhereExtra = " AND us_lKeyID IN ($strUIDs) ";
         $this->clsUsers->loadUserDirectoryPage($strWhereExtra, 0, 99999);
         $displayData['userList'] = &$this->clsUsers->directory;

         $displayData['mainTemplate'] = array('admin/import_summary_view',
                                              'admin/user_import_list');
      }else {
         $displayData['lNumDisplayRows'] = 0;
      }
   }

   private function importDetailsVolunteers($lImportID, &$displayData, $bViaPTable=false, $logEntry=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         //------------------------------------------------
         // define columns to display
         //------------------------------------------------
      initVolReportDisplay($displayData);
      $displayData['showFields']->bSkills = true;
      
      if ($this->clsImport->lNumFIDs > 0){
         if ($bViaPTable){
            $strVIDs = implode(',',
                          $this->clsImport->pTableForeignIDsViaImportID(
                                       $lImportID, $logEntry->lUTableID, $lNumFIDs));
         }else {
            screamForHelp('Volunteers: invalid import type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         }
         $strWhereExtra = " AND vol_lKeyID IN ($strVIDs) ";
         
         $this->clsVol->loadVolDirectoryPage($strWhereExtra, 0, 99999);
         $displayData['lNumDisplayRows'] = $displayData['lNumVols'] = $lNumVols = $this->clsVol->lNumVolRecs;
      }
      if ($lNumVols){
         foreach ($this->clsVol->volRecs as $volRec){
            $this->clsVolSkills->lVolID = $lVolID = $volRec->lKeyID;
            $this->clsVolSkills->loadSingleVolSkills();
            $volRec->lNumJobSkills = $lNumSkills = $this->clsVolSkills->lNumSingleVolSkills;
            if ($lNumSkills > 0) {
               $volRec->volSkills     = arrayCopy($this->clsVolSkills->singleVolSkills);
            }
         }
      }
      $displayData['vols']                 = &$this->clsVol->volRecs;

      $displayData['strRptTitle']   = 'Import Log Details';
      $displayData['mainTemplate'] = array('admin/import_summary_view',
                                           'vols/rpt_generic_vol_list');
   }

   private function importDetailsPeople($lImportID, &$displayData, $bViaPTable=false, $logEntry=null){
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
         if ($bViaPTable){
            $strPIDs = implode(',',
                          $this->clsImport->pTableForeignIDsViaImportID(
                                       $lImportID, $logEntry->lUTableID, $lNumFIDs));
         }else {
            $strPIDs = implode(',', $this->clsImport->foreignIDs);
         }
         $this->clsPeople->sqlWhereExtra = "AND pe_lKeyID IN ($strPIDs) ";
         $this->clsPeople->loadPeople(false, false, true);
         $displayData['lNumPeople'] = $this->clsPeople->lNumPeople;
         $displayData['people']     = &$this->clsPeople->people;
      }else {
         $displayData['lNumPeople'] = 0;
      }
   }

   private function importDetailsClient($lImportID, &$displayData, $bViaPTable=false, $logEntry=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('clients/client');
      $this->load->helper('clients/client_sponsor');

      initClientReportDisplay($displayData);
      $displayData['strRptTitle'] = 'Client Import';


         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['mainTemplate'] = array('admin/import_summary_view', 'client/rpt_generic_client_list');
      $displayData['pageTitle']   = 'Import Log Details';

      $displayData['title']        = CS_PROGNAME.' | Client Import';
      $displayData['nav']          = $this->mnav_brain_jar->navData();



      if ($this->clsImport->lNumFIDs > 0){
         if ($bViaPTable){
            $lCIDs = $this->clsImport->pTableForeignIDsViaImportID(
                                       $lImportID, $logEntry->lUTableID, $lNumFIDs);
         }else {
            $lCIDs = $this->clsImport->foreignIDs;
         }
         $this->clsClients->loadClientsViaClientID($lCIDs);
         $displayData['lNumClients'] = $this->clsClients->lNumClients;
         $displayData['clientInfo'] = $this->clsClients->clients;
      }else {
         $displayData['lNumClients'] = 0;
      }
   }

   private function importDetailsBiz($lImportID, &$displayData, $bViaPTable=false, $logEntry=null){
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
         if ($bViaPTable){
            $strPIDs = implode(',',
                          $this->clsImport->pTableForeignIDsViaImportID(
                                       $lImportID, $logEntry->lUTableID, $lNumFIDs));
         }else {
            $strPIDs = implode(',', $this->clsImport->foreignIDs);
         }

//         $strPIDs = implode(',', $this->clsImport->foreignIDs);
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
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();
      $displayData['js']      = '';

         //------------------------------------------------
         // models, libraries, and utilities
         //------------------------------------------------
      $this->load->library('generic_form');
//      $this->load->library('js_build/ajax_support');
      $this->load->model  ('admin/mimport', 'clsImport');
      $this->load->model  ('personalization/muser_schema');
      $this->load->helper ('dl_util/web_layout');

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('ddlTables', 'Personalized Table', 'trim|required|callback_verifyPTableSel');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['formData']->strDDLParent = $this->clsImport->strParentTableDDL('', $displayData['lNumTables']);
         }else {
            $displayData['formData']->strDDLParent = $this->clsImport->strParentTableDDL(set_value('ddlTables'), $displayData['lNumTables']);
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
         redirect('admin/import/pTableReview/'.(int)$_POST['ddlTables']);
      }
   }

   function verifyPTableSel($strTableID){
      $lTableID = (int)$strTableID;
      if ($lTableID <= 0){
         $this->form_validation->set_message('verifyPTableSel', 'Please select a personalized table.');
         return(false);
      }else {
         return(true);
      }
   }

   function pTableReview($lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();

         //------------------------------------------------
         // models, libraries, and utilities
         //------------------------------------------------
      $this->load->library('generic_form');
      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->model('admin/mpermissions',           'perms');
      $this->load->model('admin/madmin_aco', 'clsACO');

      $displayData['lTableID']   = $this->clsUF->lTableID = (int)$lTableID;
      $this->clsUF->loadTableViaTableID();
      $this->clsUF->loadTableFields(true);
      $enumTType = $this->clsUF->userTables[0]->enumTType;
      $this->clsUF->setTType($enumTType);

      $displayData['userTable']     = $userTable = $this->clsUF->userTables[0];
      $displayData['strTTypeLabel'] = $this->clsUF->strTTypeLabel;
      $displayData['lNumFields']    = $this->clsUF->lNumFields;
      $displayData['fields']        = $fields = &$this->clsUF->fields;

      foreach ($fields as $field){
         if ($field->enumFieldType==CS_FT_CURRENCY){
            $this->clsACO->loadCountries(false, false, true, $field->pff_lCurrencyACO);
            $field->strLabelExtra = ' '.$this->clsACO->countries[0]->strFlagImg;
         }else {
            $field->strLabelExtra = '';
         }
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                  .' | Import | Personalized Table';

      $displayData['title']          = CS_PROGNAME.' | Import ';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'admin/import_ptable_fields_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

/*
   function parentSel(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();
      $displayData['enumAttachType'] = $enumAttachType = trim($_POST['ddlParent']);
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
      $displayData['schema']    = &$this->cUFSchema->schema;

// -------------------------------------
$zzzlPos = @strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, @strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$enumAttachType = $enumAttachType <br></font>\n");
$zzzlPos = @strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, @strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$this->cUFSchema->lNumTables = ".$this->cUFSchema->lNumTables." <br></font>\n");

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

*/

   function loadFieldTypes_pTable($utable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

         // prepare the ddl and mddl verification
      $this->load->model('util/mlist_generic', 'clsList');
      $idx = 0;
      foreach ($utable->fields as $field){
         if (($field->enumFieldType == CS_FT_DDL) || ($field->enumFieldType == CS_FT_DDLMULTI)){
            $this->clsImport->initGenericListTable($idx, CENUM_LISTTYPE_USERTABLE, $this->clsList, $field->lFieldID);
            ++$idx;
         }
      }

      $this->clsImport->forceSet = array();
      $this->clsImport->strImportTable = $utable->strDataTableName;

      $strFP      = $utable->strFieldPrefix;
      $bMulti     = $utable->bMultiEntry;
      $enumAttach = $utable->enumAttachType;

      $this->clsImport->forceSet[0] = array('fn' => $strFP.'_lOriginID',      'value' => $glUserID.'');
      $this->clsImport->forceSet[1] = array('fn' => $strFP.'_lLastUpdateID',  'value' => $glUserID.'');
      $this->clsImport->forceSet[2] = array('fn' => $strFP.'_dteOrigin',      'value' => 'NOW()');
      $this->clsImport->forceSet[3] = array('fn' => $strFP.'_dteLastUpdate',  'value' => 'NOW()');

      if (!$bMulti){
         $this->clsImport->forceSet[4] = array('fn' => $strFP.'_bRecordEntered',  'value' => '1');
      }

      switch ($enumAttach){
         case CENUM_CONTEXT_PEOPLE:
            $this->clsImport->addImportField('people ID',  'ID',   0, '', true, $strFP.'_lForeignKey');
            break;
         case CENUM_CONTEXT_BIZ:
            $this->clsImport->addImportField('business ID',  'ID',   0, '', true, $strFP.'_lForeignKey');
            break;
         case CENUM_CONTEXT_CLIENT:
            $this->clsImport->addImportField('client ID',  'ID',   0, '', true, $strFP.'_lForeignKey');
            break;
         case CENUM_CONTEXT_GIFT:
            $this->clsImport->addImportField('gift ID',  'ID',   0, '', true, $strFP.'_lForeignKey');
            break;
         case CENUM_CONTEXT_SPONSORSHIP:
            $this->clsImport->addImportField('sponsorship ID',  'ID',   0, '', true, $strFP.'_lForeignKey');
            break;
         case CENUM_CONTEXT_USER:
            $this->clsImport->addImportField('user ID',  'ID',   0, '', true, $strFP.'_lForeignKey');
            break;
         case CENUM_CONTEXT_VOLUNTEER:
            $this->clsImport->addImportField('volunteer ID',  'ID',   0, '', true, $strFP.'_lForeignKey');
            break;
         default:
            screamForHelp($enumAttach.': unsupported import type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

      $lListIDX = 0;
      foreach ($utable->fields as $field){
         $enumFType = $field->enumFieldType;
         if (($enumFType != CS_FT_HEADING) && ($enumFType != CS_FT_LOG)){
            $bList = ($enumFType == CS_FT_DDL) || ($enumFType == CS_FT_DDLMULTI);
            $this->clsImport->xlateUField2IField($enumFType, $enumIType, $lSize, $strDefault);
            $this->clsImport->addImportField($field->strFieldNameUser,  $enumIType,  $lSize, $strDefault,
                                   $field->bRequired, $field->strFieldNameInternal, ($bList ? $lListIDX : 0),
                                   $field->lFieldID);
            if ($bList) ++$lListIDX;
         }
      }
   }



}
