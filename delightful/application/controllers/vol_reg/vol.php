<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class vol extends CI_Controller {

   function __construct(){
      global $gbDev;
      parent::__construct();
      session_start();

      $this->load->model('admin/morganization', 'clsChapter');
      $this->load->model('admin/madmin_aco',  'clsACO');

      setVolRegGlobals($this->clsACO, $this->clsChapter);
   }


   function register($strHash){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strHash = trim($strHash);

         // end any standard Delightful Labor session
      unset($_SESSION[CS_NAMESPACE.'user']);
      $this->session->sess_destroy();
      redirect('vol_reg/vol/regForm/'.$strHash);
   }

   function regForm($strHash){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDev;
      global $gclsChapter, $glChapterID, $gbDateFormatUS, $gclsChapterACO;
      global $gErrMessages;
      global $gclsChapterVoc;

      $displayData = array();
      $displayData['js'] = '';
      $gErrMessages = array();

         // load models & helpers
      $this->load->model  ('vol_reg/mvol_reg',             'volReg');
      $this->load->model  ('admin/madmin_aco',             'clsACO');
      $this->load->model  ('img_docs/mimage_doc',          'clsImgDoc');
      $this->load->model  ('personalization/muser_fields', 'clsUF');
      $this->load->model  ('admin/mpermissions',           'perms');
      $this->load->model  ('util/mcaptcha',                'clsCaptcha');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('email');
      $this->load->helper ('captcha');
      $this->load->helper ('dl_util/custom_forms');

      $displayData['formData'] = new stdClass;
      $displayData['title']    = 'Volunteer Registration';

      if (!$this->verifyHash($strHash, $displayData, $lFormID)) return;
      $rRec = &$displayData['rRec'];

      $displayData['bCreateAcct'] = $bCreateAcct =
            $rRec->bPermEditContact     ||
            $rRec->bPermPassReset       ||
            $rRec->bPermViewGiftHistory ||
            $rRec->bPermEditJobSkills   ||
            $rRec->bPermViewHrsHistory  ||
            $rRec->bPermAddVolHours     ||
            $rRec->bVolShiftSignup;

         //-----------------------------
         // validation rules
         //-----------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->setValText($rRec->bShowFName, $rRec->bFNameRequired,  'txtFName',   'First Name');
      $this->setValText($rRec->bShowLName, $rRec->bLNameRequired,  'txtLName',   'Last Name');
      $this->setValText($rRec->bShowAddr,  $rRec->bAddrRequired,   'txtAddr1',   'Address');
      $this->setValText($rRec->bShowAddr,  false,                  'txtAddr2',   'Address 2');
      $this->setValText($rRec->bShowAddr,  $rRec->bAddrRequired,   'txtCity',    'City'   );
      $this->setValText($rRec->bShowAddr,  $rRec->bAddrRequired,   'txtState',   'State'  );
      $this->setValText($rRec->bShowAddr,  $rRec->bAddrRequired,   'txtZip',     'Zip'    );
      $this->setValText($rRec->bShowAddr,  $rRec->bAddrRequired,   'txtCountry', 'Country');

      if ($rRec->bShowEmail){
         if ($rRec->bEmailRequired){
            $this->form_validation->set_rules('txtEmail',  'Email', 'required|callback_verifyEmail');
         }else {
            $this->form_validation->set_rules('txtEmail',  'Email', 'callback_verifyEmail');
         }
      }
      $this->setValText($rRec->bShowPhone, $rRec->bPhoneRequired,  'txtPhone', 'Phone');
      $this->setValText($rRec->bShowCell,  $rRec->bCellRequired,   'txtCell',  'Cell');

      cusFormSetValDate($rRec->bShowBDay,  $rRec->bBDateRequired,  'txtBDate',  'Birth Date', false);

      if ($rRec->bDisclaimerAckRqrd){
         $this->form_validation->set_rules('chkDisclaimer', 'Disclaimer', 'callback_verifyDisclaimer');
      }
      if ($rRec->bCaptchaRequired){
         $this->form_validation->set_rules('txtCaptcha', 'Captcha', 'callback_verifyCaptcha');
      }

      if ($bCreateAcct){
         $this->form_validation->set_rules('txtPWord1', 'Password', 'callback_verifyPWords');
         $this->form_validation->set_rules('txtPWord2', 'Password', 'trim');
      }

         // basic info
      $displayData['basicInfo'] = array();
      $this->setBasicInfo($displayData['basicInfo'][ 0], 'First Name', $rRec->bShowFName, $rRec->bFNameRequired, 'txtFName',   40, 80);
      $this->setBasicInfo($displayData['basicInfo'][ 1], 'Last Name',  $rRec->bShowLName, $rRec->bLNameRequired, 'txtLName',   40, 80);
      $this->setBasicInfo($displayData['basicInfo'][ 2], 'Address',    $rRec->bShowAddr,  $rRec->bAddrRequired,  'txtAddr1',   40, 80);
      $this->setBasicInfo($displayData['basicInfo'][ 3], 'Address 2',  $rRec->bShowAddr,  false,                 'txtAddr2',   40, 80);
      $this->setBasicInfo($displayData['basicInfo'][ 4], 'City',       $rRec->bShowAddr,  $rRec->bAddrRequired,  'txtCity',    20, 80);
      $this->setBasicInfo($displayData['basicInfo'][ 5], $gclsChapterVoc->vocState,  $rRec->bShowAddr,  $rRec->bAddrRequired,  'txtState',   20, 80);
      $this->setBasicInfo($displayData['basicInfo'][ 6], $gclsChapterVoc->vocZip,    $rRec->bShowAddr,  $rRec->bAddrRequired,  'txtZip',     20, 40);
      $this->setBasicInfo($displayData['basicInfo'][ 7], 'Country',    $rRec->bShowAddr,  $rRec->bAddrRequired,  'txtCountry', 20, 80);
      $this->setBasicInfo($displayData['basicInfo'][ 8], 'Email',      $rRec->bShowEmail, $rRec->bEmailRequired, 'txtEmail',   40, 120);

      $this->setBasicInfo($displayData['basicInfo'][ 9], 'Phone',      $rRec->bShowPhone, $rRec->bPhoneRequired, 'txtPhone',   20, 40);
      $this->setBasicInfo($displayData['basicInfo'][10], 'Cell',       $rRec->bShowCell,  $rRec->bCellRequired,  'txtCell',    20, 40);

         // job skills
      $this->volReg->loadVolRegFormSkills($lFormID, false);
      $displayData['lNumSkills'] = $lNumSkills = $this->volReg->lOnFormSkills;
      $displayData['skills']     = $jobSkills = arrayCopy($this->volReg->skills);
      $skills = &$displayData['skills'];
      if ($lNumSkills > 0){
         foreach ($skills as $skill){
            $skill->bChecked = false;
            $skill->checkFN = $strChkFN = 'chkJobSkill_'.$skill->lKeyID;
            $this->form_validation->set_rules($strChkFN,  'Job Skill', 'trim');
            $displayData['formData']->txtPWord1 = $displayData['formData']->txtPWord2 = '';
         }
      }

         // personalized tables and associated fields
      $this->volReg->loadPTablesForDisplay($lFormID, $this->clsUF);
      $displayData['utables']    = $utables = &$this->volReg->utables;
      $displayData['lNumTables'] = $lNumTables = $this->volReg->lNumTables;
      setValidationUTables($displayData['js'], $lNumTables, $utables);

		if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');
         $this->load->model  ('util/mlist_generic', 'clsList');
         $this->load->helper ('dl_util/web_layout');

         $displayData['strHash'] = $strHash;

         $this->volReg->setLogoImageTag();

            // captcha setup
         if ($rRec->bCaptchaRequired){
            $this->initCaptcha($displayData['imgCaptcha']);
         }

         if ($rRec->bShowBDay){
            $displayData['js'] .= strDatePicker('datepickerBDate', false);
         }

         initUTableDates($displayData['js'], $lNumTables, $utables);
         initUTableDDLs($lNumTables, $utables);

         if (validation_errors()==''){

               // basic info
            $displayData['basicInfo'][ 0]->value =
            $displayData['basicInfo'][ 1]->value =
            $displayData['basicInfo'][ 2]->value =
            $displayData['basicInfo'][ 3]->value =
            $displayData['basicInfo'][ 4]->value =
            $displayData['basicInfo'][ 5]->value =
            $displayData['basicInfo'][ 6]->value =
            $displayData['basicInfo'][ 7]->value =
            $displayData['basicInfo'][ 8]->value =
            $displayData['basicInfo'][ 9]->value =
            $displayData['basicInfo'][10]->value = '';

            $displayData['formData']->txtBDate = '';

            if ($rRec->bDisclaimerAckRqrd) $rRec->bDisclaimerChecked = false;

               // job skills
            if ($lNumSkills > 0){
               foreach ($skills as $skill){
                  $skill->bChecked = false;
               }
            }

               // personalized tables
            setCustomUTableDDLs($lNumTables, $utables);
         }else {
            setOnFormError($displayData);

            $displayData['basicInfo'][ 0]->value = set_value('txtFName');
            $displayData['basicInfo'][ 1]->value = set_value('txtLName');
            $displayData['basicInfo'][ 2]->value = set_value('txtAddr1');
            $displayData['basicInfo'][ 3]->value = set_value('txtAddr2');
            $displayData['basicInfo'][ 4]->value = set_value('txtCity');
            $displayData['basicInfo'][ 5]->value = set_value('txtState');
            $displayData['basicInfo'][ 6]->value = set_value('txtZip');
            $displayData['basicInfo'][ 7]->value = set_value('txtCountry');
            $displayData['basicInfo'][ 8]->value = set_value('txtEmail');
            $displayData['basicInfo'][ 9]->value = set_value('txtPhone');
            $displayData['basicInfo'][10]->value = set_value('txtCell');

            $displayData['formData']->txtBDate = set_value('txtBDate');

            if (form_error('txtPWord1')==''){
               $displayData['formData']->txtPWord1 = $displayData['formData']->txtPWord2 = set_value('txtPWord1');
            }else {
               $displayData['formData']->txtPWord1 = $displayData['formData']->txtPWord2 = '';
            }

            if ($rRec->bDisclaimerAckRqrd) $rRec->bDisclaimerChecked = set_value('chkDisclaimer')=='true';

               // job skills
            if ($lNumSkills > 0){
               foreach ($skills as $skill){
                  $skill->bChecked = set_value($skill->checkFN)=='true';
               }
            }

               // personalized tables
            repopulateCustomTables($lNumTables, $utables);
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['strOrg']         = $this->clsChapter->chapterRec->strChapterName;

         $displayData['errMessages'] = arrayCopy($gErrMessages);
         $this->load->vars($displayData);
         $this->load->view('vol_reg/register');
      }else {
         $this->saveVolReg($rRec, $lNumTables, $utables, $lNumSkills, $jobSkills, $bCreateAcct);
         redirect('vol_reg/vol/submitted/'.$strHash);
      }
   }

   function verifyDDLMultiSelect($strFieldValue, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return(verifyCustomFormMultiDDL($strFieldValue, $strOpts));
   }

   function verifyHash($strHash, &$displayData, &$lFormID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->volReg->loadVolRegFormsViaHash($strHash);
      $displayData['rRec'] = $rRec = &$this->volReg->regRecs[0];
      $lFormID = $rRec->lKeyID;

      if ($this->volReg->lNumRegRecs != 1){
            //--------------------------
            // breadcrumbs - 404
            //--------------------------
         $displayData['strOrg']         = $this->clsChapter->chapterRec->strChapterName;
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $this->load->vars($displayData);
         $this->load->view('vol_reg/form404');
         return(false);
      }else {
         return(true);
      }
   }

   function setBasicInfo(&$basicInfo, $strLabel, $bShow, $bRequired, $strFormFN, $width, $maxLength){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $basicInfo = new stdClass;
      $basicInfo->strLabel  = $strLabel;
      $basicInfo->bShow     = $bShow;
      $basicInfo->bRequired = $bRequired;
      $basicInfo->strFormFN = $strFormFN;
      $basicInfo->value     = null;
      $basicInfo->width     = $width;
      $basicInfo->maxLength = $maxLength;
   }

   function setValText($bShow, $bRequired, $strTxtFN, $strLabel, $strExtraVal=''){
      if ($bShow){
         $this->form_validation->set_rules($strTxtFN,  $strLabel, 'trim'.($bRequired ? '|required' : '').$strExtraVal);
      }
   }

   function verifyEmail($strEmail){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strEmail = trim($strEmail);
      if ($strEmail == '') return(true);     // required test has already occurred
      if (!valid_email($strEmail)){
         $this->form_validation->set_message('verifyEmail',
                        'The <b>email address</b> you entered does not appear to be valid. Please try again.');
         return(false);
      }

      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                $strEmail, 'us_strUserName',
                -1,    'us_lKeyID',
                false, null,
                false, null, null,
                false, null, null,
                'admin_users')){
         $this->form_validation->set_message('verifyEmail',
                        'The <b>email address</b> is currently in use. Please enter a different email or contact your volunteer coordinator.');
         return(false);
      }

      return(true);
   }

   function verifyPWords($strPWord1){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strPWord1 = trim($strPWord1);
      $strPWord2 = trim($_POST['txtPWord2']);

      if ($strPWord1 == '' && $strPWord2 == ''){
         $this->form_validation->set_message('verifyPWords',
                   'A <b>password</b> is required.');
         return(false);
      }

      if ($strPWord1 != $strPWord2){
         $this->form_validation->set_message('verifyPWords',
                   'The <b>passwords</b> don\'t match.');
         return(false);
      }

      if (strlen($strPWord1) < 6){
         $this->form_validation->set_message('verifyPWords',
                   'Your <b>password</b> must be at least 6 characters.');
         return(false);
      }
      return(true);
   }

   function verifyCurrency($strVal, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return(verifyCustomFormCurrency($strVal, $strOpts));
   }

   function verifyDDLSelect($strVal, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return(verifyCustomFormDDL($strVal, $strOpts));
   }

   function verifyCustomDate($strFieldValue, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gErrMessages, $gdteNow;
      return(verifyCustomFormDate($strFieldValue, $strOpts));
   }

   function stripCommas(&$strValue){
      $strValue = str_replace (',', '', $strValue);
      return(true);
   }

   function initCaptcha(&$strImage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow;

      $vals = array(
          'word'	     => $this->clsCaptcha->strRandomWord(),
          'img_path'	  => './images/captcha/',
          'img_url'	  => DL_IMAGEPATH.'/captcha/',
          'time'       => $gdteNow,
          'font_path'  => './fonts/Lato-Regular.ttf',
          'img_width'  => '500',
          'img_height' => 100,
          'expiration' => 7200
          );

      $this->clsCaptcha->setCaptchaDBEntry($vals, $cap);
      $strImage = $cap['image'];
   }

   function verifyCaptcha($strText){
      if ($this->clsCaptcha->bVerifyCaptchaEntry($strText)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyCaptcha',
                   'The <b>captcha text</b> you entered did not match. Please try again.');
         return(false);
      }
   }

   function verifyDisclaimer($strChk){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($strChk=='true'){
         return(true);
      }else {
         $this->form_validation->set_message('verifyDisclaimer',
                   'You must acknowledge this disclaimer to submit your volunteer registration.');
         return(false);
      }
   }

   function saveVolReg(&$rRec, $lNumTables, &$utables, $lNumSkills, &$jobSkills, $bCreateAcct){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterACO, $glUserID, $gbDateFormatUS, $gdteNow,
           $gclsChapter;

      $this->load->model ('people/mpeople',                       'clsPeople');
      $this->load->model ('vols/mvol',                            'clsVol');
      $this->load->model ('vol_reg/mvol_reg',                     'volReg');
      $this->load->model ('personalization/muser_fields_create',  'clsUFC');
      $this->load->model ('admin/muser_accts',                    'clsUser');
      $this->load->model ('personalization/muser_fields_display', 'clsUFD');
      $this->load->model ('vols/mvol_skills',                     'clsVolSkills');
      $this->load->helper('dl_util/util_db');
      $this->load->helper('personalization/field_display');

         // make the default Delightful Labor user the person who created the vol registration form
      $glUserID = $rRec->lOriginID;

      $strChapter = $gclsChapter->strChapterName;
      $lRegFormID = $rRec->lKeyID;

         //----------------------------
         // add a people record
         //----------------------------
      $this->clsPeople->loadPeopleViaPIDs(-1, false, false);
      $pRec = &$this->clsPeople->people[0];
      if ($rRec->bShowFName){
         $pRec->strFName = $pRec->strPreferredName = $pRec->strSalutation = trim($_POST['txtFName']);
      }
      if ($rRec->bShowLName) $pRec->strLName = trim($_POST['txtLName']);

      $pRec->strMName     = '';
      $pRec->enumGender   = 'Unknown';
      $pRec->lACO         = $gclsChapterACO->lKeyID;
      $pRec->lHouseholdID = 0;   // new volunteer will become head of household

      if ($rRec->bShowAddr){
         $pRec->strAddr1   = trim($_POST['txtAddr1']);
         $pRec->strAddr2   = trim($_POST['txtAddr2']);
         $pRec->strCity    = trim($_POST['txtCity']);
         $pRec->strState   = trim($_POST['txtState']);
         $pRec->strCountry = trim($_POST['txtCountry']);
         $pRec->strZip     = trim($_POST['txtZip']);
         $pRec->strNotes   = 'Auto-created via volunteer registration';
      }
      if ($rRec->bShowPhone) $pRec->strPhone = trim($_POST['txtPhone']);
      if ($rRec->bShowCell)  $pRec->strCell  = trim($_POST['txtCell']);
      if ($rRec->bShowEmail) $pRec->strEmail = trim($_POST['txtEmail']);

      $pRec->dteMysqlDeath = null;
      if ($rRec->bShowBDay){
         $strBDate = trim($_POST['txtBDate']);
         if ($strBDate==''){
            $pRec->dteMysqlBirthDate = null;
         }else {
            MDY_ViaUserForm($strBDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
            $pRec->dteMysqlBirthDate = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);
         }
      }
      $lPeopleID = $this->clsPeople->lCreateNewPeopleRec();

         //----------------------------
         // add volunteer record
         //----------------------------
      $this->clsVol->loadVolRecsViaPeopleID(-1, true);
      $vRec = &$this->clsVol->volRecs[0];
      $vRec->lPeopleID = $lPeopleID;

      $vRec->lRegFormID  = $rRec->lKeyID;

      $vRec->Notes = 'Self-registered via form "'.$rRec->strFormName.'" on '.date('l, F jS, Y H:i:s', $gdteNow);
      $lVolID = $this->clsVol->lAddNewVolunteer();

         //----------------------------
         // job skills
         //----------------------------
      $this->clsVolSkills->lVolID = $lVolID;
      if ($lNumSkills > 0){
         foreach ($jobSkills as $skill){
            if (@$_POST[$skill->checkFN]=='true'){
               $this->clsVolSkills->setVolSkill($skill->lSkillID);
            }
         }
      }

         //----------------------------
         // personalized tables
         //----------------------------
      saveCustomPTables($lVolID, $lNumTables, $utables);

         //----------------------------
         // create volunteer account
         //----------------------------
      if ($bCreateAcct){
         $this->clsUser->sqlWhere = ' AND us_lKeyID=-1 ';
         $this->clsUser->loadUserRecords();
         $uRec = &$this->clsUser->userRec[0];

         $uRec->us_strUserName             = $uRec->us_strEmail = $pRec->strEmail.'';
         $uRec->us_strFirstName            = $pRec->strFName.'';
         $uRec->us_strLastName             = $pRec->strLName.'';
         $uRec->us_strUserPWord            = trim($_POST['txtPWord1']);
         $uRec->us_strTitle                = 'Volunteer';
         $uRec->us_strPhone                = $pRec->strPhone.'';
         $uRec->us_strCell                 = $pRec->strCell.'';

         $uRec->us_strAddr1                = $pRec->strAddr1.'';
         $uRec->us_strAddr2                = $pRec->strAddr2.'';
         $uRec->us_strCity                 = $pRec->strCity.'';
         $uRec->us_strState                = $pRec->strState.'';
         $uRec->us_strCountry              = $pRec->strCountry.'';
         $uRec->us_strZip                  = $pRec->strZip.'';
         $uRec->lPeopleID                  = $lPeopleID;

         if ($gbDateFormatUS){
            $uRec->us_enumDateFormat       = 'm/d/Y';
            $uRec->us_enumMeasurePref      = 'English';
         }else {
            $uRec->us_enumDateFormat       = 'd/m/Y';
            $uRec->us_enumMeasurePref      = 'metric';
         }
         $uRec->us_bAdmin               = false;
         $uRec->bVolAccount             = true;
         $uRec->bVolEditContact         = $rRec->bPermEditContact;
         $uRec->bVolPassReset           = $rRec->bPermPassReset;
         $uRec->bVolViewGiftHistory     = $rRec->bPermViewGiftHistory;
         $uRec->bVolEditJobSkills       = $rRec->bPermEditJobSkills;
         $uRec->bVolViewHrsHistory      = $rRec->bPermViewHrsHistory;
         $uRec->bVolAddVolHours         = $rRec->bPermAddVolHours;
         $uRec->bVolShiftSignup         = $rRec->bVolShiftSignup;

         $lUserID = $this->clsUser->addUserAccount();
      }

         //----------------------------
         // add volunteer to group
         //----------------------------
      if (!is_null($rRec->lVolGroupID)){
         $this->load->model('groups/mgroups', 'groups');
         $this->load->helper('groups/groups');
         $this->groups->lForeignID = $lVolID;
         $this->groups->lGroupID   = $rRec->lVolGroupID;
         $this->groups->addGroupMembership();
      }

         // load vol registration form
      $this->volReg->loadVolRegFormsViaRFID($lRegFormID);
      $regForm = &$this->volReg->regRecs[0];

      $this->volReg->loadRegistrationViaRegFormIDVolID($lVolID, $lRegFormID, $lNumRegs, $regTable);
      $regRec = &$regTable[$lVolID];

      $strRegHTML = $this->volReg->strVolReg2HTML($regRec);

         //-----------------------------
         // email the vol. coordinator
         //-----------------------------
      $to = $regForm->strContactEmail;
      $subject = 'New volunteer registration: '.$regForm->strFormName;
      $headers = "From: " . $regForm->strContactEmail . "\r\n";
      $headers .= "Reply-To: ". $regForm->strContactEmail . "\r\n";
      $headers .= "MIME-Version: 1.0\r\n";
      $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
      $message = '<html><body>'
          .'You have a new registration for <b>'.htmlspecialchars($regForm->strFormName).'</b><br><br>'
          .'Here are the details:<br>'
          .$strRegHTML.'<br>'
          .'This message was automatically generated via your installation of Delightful Labor.';
      $message .= "</body></html>";
      @mail($to, $subject, $message, $headers);
      
            //-----------------------------
            // email the volunteer
            //-----------------------------
      if ($regRec->strEmail.'' != ''){
         $to = $regRec->strEmail;
         $subject = 'Thank you for registering for '.$regForm->strFormName;
         $headers = "From: " . $regForm->strContactEmail . "\r\n";
         $headers .= "Reply-To: ". $regForm->strContactEmail . "\r\n";
         $headers .= "MIME-Version: 1.0\r\n";
         $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
         $message = '<html><body>'
             .'You have been registered for <b>'.htmlspecialchars($regForm->strFormName).'</b><br><br>'
             .'Here are the details:<br>'
             .$strRegHTML
             .'Please contact the volunteer coordinator at '.htmlspecialchars($strChapter).' if you have any questions.';
         $message .= "</body></html>";
         @mail($to, $subject, $message, $headers);      
      }
   }

   function submitted($strHash){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDev;
      global $gclsChapter, $glChapterID, $gbDateFormatUS, $gclsChapterACO;

      $displayData = array();
      $displayData['js']    = '';
      $displayData['title'] = 'Volunteer Registration';

         // load models & helpers
      $this->load->model  ('vol_reg/mvol_reg',    'volReg');
      $this->load->model  ('img_docs/mimage_doc',     'clsImgDoc');

         // load organization info
      $this->clsChapter->lChapterID = CL_DEFAULT_CHAPTERID;
      $this->clsChapter->loadChapterInfo();

      if (!$this->verifyHash($strHash, $displayData, $lFormID)) return;
      $rRec = &$displayData['rRec'];
      $this->volReg->setLogoImageTag();

      $displayData['strOrg'] = $this->clsChapter->chapterRec->strChapterName;
      $this->load->vars($displayData);
      $this->load->view('vol_reg/register_submitted');
   }


}
