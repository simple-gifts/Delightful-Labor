<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class registration extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('editPeopleBizVol')) return;

      $displayData = array();
      $displayData['js'] = '';

         /*------------------------------------------------
             models/libraries/helpers
         ------------------------------------------------*/
      $this->load->helper('vols/vol_links');
      $this->load->model ('vol_reg/mvol_reg', 'volReg');

      $this->volReg->loadVolRegForms();
      $displayData['lNumRegRecs']    = $lNumRR = $this->volReg->lNumRegRecs;
      $displayData['regRecs']        = &$this->volReg->regRecs;
      if ($lNumRR > 0){
         foreach ($displayData['regRecs'] as $rRec){
            $rRec->lNumVols = $this->volReg->lNumVolRecsViaRegFormID($rRec->lKeyID);
         }
      }

         /*-------------------------------------
            stripes
         -------------------------------------*/
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         /*--------------------------
           breadcrumbs
         --------------------------*/
      $displayData['title']        = CS_PROGNAME.' | Vol Registration';
      $displayData['pageTitle']    = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                              .' | Registration Forms';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'vol_reg/registration_form_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addEditRegForm($lRegFormID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapter, $glChapterID;

      if (!bTestForURLHack('editPeopleBizVol')) return;
      $this->load->helper('dl_util/verify_id');
      if ($lRegFormID.'' !='0') verifyID($this, $lRegFormID, 'vol. registration ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lRegFormID'] = $lRegFormID = (integer)$lRegFormID;

      $displayData['bNew'] = $bNew = $lRegFormID <= 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('vol_reg/mvol_reg',     'volReg');
      $this->load->model ('img_docs/mimage_doc',  'cImgDoc');
      $this->load->model ('admin/morganization',  'clsChapter');
      $this->load->model ('admin/madmin_aco',     'clsACO');
      $this->load->model ('groups/mgroups',       'groups');
      
      $this->load->helper('img_docs/image_doc');
      $this->load->helper('img_docs/link_img_docs');      
      $this->load->helper('js/clear_set_check_on_check');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/custom_forms');
      $this->load->helper('groups/groups');

      $displayData['js'] .= clearCheckOnUnCheck();
      $displayData['js'] .= setCheckOnCheck();

      $this->volReg->loadVolRegFormsViaRFID($lRegFormID);
      $rRec = &$this->volReg->regRecs[0];

         //-----------------------------
         // validation rules
         //-----------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtRegFormName',    'Name of the Registration Form', 'trim|required');
		$this->form_validation->set_rules('txtDescription',    'Description', 'trim');
		$this->form_validation->set_rules('txtBannerOrg',      'Banner Text: Organization Name', 'trim|required');
		$this->form_validation->set_rules('txtBannerTitle',    'Banner Text: Registration Form Title', 'trim|required');
		$this->form_validation->set_rules('txtIntro',          'Registration Form Introductory Text', 'trim|required');
		$this->form_validation->set_rules('txtSubmissionText', 'Text Shown After Submission', 'trim|required');
		$this->form_validation->set_rules('txtVolCoordinatorEmail', 'Volunteer Coordinator Email', 'trim|required|valid_email');

      $this->form_validation->set_rules('chkAddrShow',        'Show',     'trim');

      $this->form_validation->set_rules('chkPermContactInfo',    'Show',     'trim');
      $this->form_validation->set_rules('chkPermPassReset',      'Show',     'trim');
      $this->form_validation->set_rules('chkPermGiftHistory',    'Show',     'trim');
      $this->form_validation->set_rules('chkPermJobSkills',      'Show',     'trim');
      $this->form_validation->set_rules('chkPermViewVolLog',     'Show',     'trim');
      $this->form_validation->set_rules('chkPermAddVolHrs',      'Show',     'trim');
      $this->form_validation->set_rules('chkPermVolShiftSignup', 'Show',     'trim');

      $this->form_validation->set_rules('chkAddrShow',        'Show',     'trim');
      $this->form_validation->set_rules('chkAddrReq',         'Required', 'trim');
      $this->form_validation->set_rules('chkEmailShow',       'Show',     'callback_validateEmailPerms');
      $this->form_validation->set_rules('chkEmailReq',        'Required', 'trim');
      $this->form_validation->set_rules('chkPhoneShow',       'Show',     'trim');
      $this->form_validation->set_rules('chkPhoneReq',        'Required', 'trim');
      $this->form_validation->set_rules('chkCellShow',        'Show',     'trim');
      $this->form_validation->set_rules('chkCellReq',         'Required', 'trim');
      $this->form_validation->set_rules('chkBDayShow',        'Show',     'trim');
      $this->form_validation->set_rules('chkBDayReq',         'Required', 'trim');

      $this->form_validation->set_rules('rdoLogo',            'LogoID',   'trim');
      $this->form_validation->set_rules('ddlVolGroup',        'Volunteer Group ID',   'trim');

      $this->form_validation->set_rules('chkShowDisclaimer', 'Show Disclaimer', 'trim');
      $this->form_validation->set_rules('txtDisclaimerAck',  'Disclaimer Acknowledgement', 'callback_verifyDisclaimerAck');
      $this->form_validation->set_rules('txtDisclaimer',     'Disclaimer', 'callback_verifyDisclaimer');

      $this->form_validation->set_rules('ddlCSS',            'Stylesheet File', 'trim');

         /*
            load stylesheet files
         */
      $this->volReg->availableStyleSheets();
      $displayData['lNumStyleSheets'] = $this->volReg->lNumStyleSheets;
      $displayData['styleSheets']     = arrayCopy($this->volReg->styleSheets);

         /*
            job skills
         */
      $this->volReg->loadVolRegFormSkills($lRegFormID, true);
      $displayData['lNumSkills'] = $lNumSkills = $this->volReg->lNumSkillTot;

      if ($lNumSkills > 0){
         $displayData['jobSkills'] = $jobSkills = &$this->volReg->skills;
         foreach ($jobSkills as $skill){
            $lSkillID = $skill->lSkillID;
            $skill->chkShow = $chkShow = 'chkSkillShow_'.$lSkillID;
            $skill->bShow = false;
            $this->form_validation->set_rules($chkShow, 'Job Skill', '');
         }

      }else {
         $displayData['jobSkills'] = $jobSkills = null;
      }

         /*
            personalized volunteer tables
         */
      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->model('admin/mpermissions',           'perms');
      $this->clsUF->enumTType = CENUM_CONTEXT_VOLUNTEER;
      $this->clsUF->loadTablesViaTType();
      $displayData['lNumTables'] = $lNumTables = $this->clsUF->lNumTables;
      if ($lNumTables > 0){
         $displayData['userTables'] = $userTables = &$this->clsUF->userTables;

         foreach ($userTables as $utable){
            $this->clsUF->lTableID = $lTableID = $utable->lKeyID;
            $this->clsUF->loadTableFields();

               // exclude log fields
            if ($this->clsUF->lNumFields > 0){
               foreach ($this->clsUF->fields as $field){
                  if ($field->enumFieldType==CS_FT_LOG){
                     --$this->clsUF->lNumFields;
                  }else {
                     $lFieldID = $field->pff_lKeyID;
                     $field->strFNShow     = 'chkUFShow_'.$lTableID.'_'.$lFieldID;
                     $field->strFNRequired = 'chkUFReq_' .$lTableID.'_'.$lFieldID;
                     $this->volReg->bShowRequiredUFFields($lRegFormID, $lTableID, $lFieldID, $field->bShow, $field->bRequired);
                     $this->form_validation->set_rules($field->strFNShow, 'Personalized Field/Show', '');
                     $this->form_validation->set_rules($field->strFNRequired, 'Personalized Field/Required', '');
                  }
               }
            }

            $utable->lNumFields = $lNumFields = $this->clsUF->lNumFields;
            if ($lNumFields > 0){
               $utable->fields = arrayCopy($this->clsUF->fields);
            }
            $utable->strTableLabel = $this->volReg->strPublicUFTable($lTableID, $lRegFormID, $utable->strUserTableName);
         }
      }else {
      }

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');
         $this->load->model('util/mlist_generic', 'clsList');
         $this->load->helper('dl_util/web_layout');

         $displayData['rRec'] = $rRec;

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($bNew){
               $displayData['formData']->txtRegFormName         = '';
               $displayData['formData']->txtDescription         = '';
               $displayData['formData']->txtBannerOrg           = htmlspecialchars($gclsChapter->strChapterName);
               $displayData['formData']->txtBannerTitle         = 'Volunteer Registration';
               $displayData['formData']->txtIntro               = '';
               $displayData['formData']->txtSubmissionText      = '';
               $displayData['formData']->txtVolCoordinatorEmail = '';
               $displayData['formData']->bCaptchaRequired       = true;

               $displayData['formData']->bPermContactInfo = true;
               $displayData['formData']->bPermPassReset   = true;
               $displayData['formData']->bPermGiftHistory = true;
               $displayData['formData']->bPermJobSkills   = true;
               $displayData['formData']->bPermViewVolLog  = true;
               $displayData['formData']->bPermAddVolHrs   = true;
               $displayData['formData']->bVolShiftSignup  = true;

               $displayData['formData']->bShowAddr        = true;
               $displayData['formData']->bAddrRequired    = true;
               $displayData['formData']->bShowEmail       = true;
               $displayData['formData']->bEmailRequired   = true;
               $displayData['formData']->bShowPhone       = true;
               $displayData['formData']->bPhoneRequired   = true;
               $displayData['formData']->bShowCell        = true;
               $displayData['formData']->bCellRequired    = true;
               $displayData['formData']->bShowBDay        = true;
               $displayData['formData']->bBDayRequired    = false;

               $displayData['formData']->bShowDisclaimer  = false;
               $displayData['formData']->txtDisclaimer    = '';
               $displayData['formData']->txtDisclaimerAck = 'I agree to the terms stated below.';
               $displayData['formData']->strDDLCSS        = $this->volReg->strCSSDropDown('ddlCSS', 'default.css', false);

                  // organization logos
               $this->cImgDoc->loadProfileImage(CENUM_CONTEXT_ORGANIZATION, $glChapterID);
               if ($this->cImgDoc->lNumImageDocs == 0){
                  $lLogoID = null;
               }else {
                  $lLogoID = $this->cImgDoc->imageDocs[0]->lKeyID;
               }

            }else {
               $displayData['formData']->txtRegFormName         = htmlspecialchars($rRec->strFormName);
               $displayData['formData']->txtDescription         = htmlspecialchars($rRec->strDescription);
               $displayData['formData']->txtBannerOrg           = htmlspecialchars($rRec->strBannerOrg);
               $displayData['formData']->txtBannerTitle         = htmlspecialchars($rRec->strBannerTitle);
               $displayData['formData']->txtIntro               = htmlspecialchars($rRec->strIntro);
               $displayData['formData']->txtSubmissionText      = htmlspecialchars($rRec->strSubmissionText);
               $displayData['formData']->txtVolCoordinatorEmail = htmlspecialchars($rRec->strContactEmail);
               $displayData['formData']->bCaptchaRequired       = $rRec->bCaptchaRequired;

               $displayData['formData']->bPermContactInfo = $rRec->bPermEditContact;
               $displayData['formData']->bPermPassReset   = $rRec->bPermPassReset;
               $displayData['formData']->bPermGiftHistory = $rRec->bPermViewGiftHistory;
               $displayData['formData']->bPermJobSkills   = $rRec->bPermEditJobSkills;
               $displayData['formData']->bPermViewVolLog  = $rRec->bPermViewHrsHistory;
               $displayData['formData']->bPermAddVolHrs   = $rRec->bPermAddVolHours;
               $displayData['formData']->bVolShiftSignup  = $rRec->bVolShiftSignup;

               $displayData['formData']->bShowAddr       = $rRec->bShowAddr;
               $displayData['formData']->bAddrRequired   = $rRec->bAddrRequired;
               $displayData['formData']->bShowEmail      = $rRec->bShowEmail;
               $displayData['formData']->bEmailRequired  = $rRec->bEmailRequired;

               $displayData['formData']->bShowPhone       = $rRec->bShowPhone;
               $displayData['formData']->bPhoneRequired   = $rRec->bPhoneRequired;
               $displayData['formData']->bShowCell        = $rRec->bShowCell;
               $displayData['formData']->bCellRequired    = $rRec->bCellRequired;

               $displayData['formData']->bShowBDay        = $rRec->bShowBDay;
               $displayData['formData']->bBDayRequired    = $rRec->bBDateRequired;

               $displayData['formData']->bShowDisclaimer  = $rRec->bShowDisclaimer;
               $displayData['formData']->txtDisclaimer    = $rRec->strDisclaimer;
               $displayData['formData']->txtDisclaimerAck = $rRec->strDisclaimerAck;

               $displayData['formData']->strDDLCSS        = $this->volReg->strCSSDropDown('ddlCSS', $rRec->strCSSFN, false);

               $lLogoID = $rRec->lLogoImageID;

               if ($lNumSkills > 0){
                  foreach ($jobSkills as $skill){
                     $skill->bShow = $skill->bOnForm;
                  }
               }
            }

               // volunteer group ddl
            $displayData['formData']->ddlVolGroup =
                       $this->groups->strDDLActiveGroupEntries('ddlVolGroup', CENUM_CONTEXT_VOLUNTEER, $rRec->lVolGroupID, true);
         }else {
            setOnFormError($displayData);

            $displayData['formData']->txtRegFormName         = set_value('txtRegFormName');
            $displayData['formData']->txtDescription         = set_value('txtDescription');
            $displayData['formData']->txtBannerOrg           = set_value('txtBannerOrg');
            $displayData['formData']->txtBannerTitle         = set_value('txtBannerTitle');
            $displayData['formData']->txtIntro               = set_value('txtIntro');
            $displayData['formData']->txtSubmissionText      = set_value('txtSubmissionText');
            $displayData['formData']->txtVolCoordinatorEmail = set_value('txtVolCoordinatorEmail');
            $displayData['formData']->bCaptchaRequired       = set_value('chkCaptchaRequired') =='true';
            $lLogoID = set_value('rdoLogo');

            $displayData['formData']->bPermContactInfo = set_value('chkPermContactInfo')    =='true';
            $displayData['formData']->bPermPassReset   = set_value('chkPermPassReset')      =='true';
            $displayData['formData']->bPermGiftHistory = set_value('chkPermGiftHistory')    =='true';
            $displayData['formData']->bPermJobSkills   = set_value('chkPermJobSkills')      =='true';
            $displayData['formData']->bPermViewVolLog  = set_value('chkPermViewVolLog')     =='true';
            $displayData['formData']->bPermAddVolHrs   = set_value('chkPermAddVolHrs')      =='true';
            $displayData['formData']->bVolShiftSignup  = set_value('chkPermVolShiftSignup') =='true';

            $displayData['formData']->bShowAddr        = set_value('chkAddrShow') =='true';
            $displayData['formData']->bAddrRequired    = set_value('chkAddrReq')  =='true';
            $displayData['formData']->bShowEmail       = set_value('chkEmailShow')=='true';
            $displayData['formData']->bEmailRequired   = set_value('chkEmailReq') =='true';

            $displayData['formData']->bShowPhone       = set_value('chkPhoneShow')=='true';
            $displayData['formData']->bPhoneRequired   = set_value('chkPhoneReq') =='true';
            $displayData['formData']->bShowCell        = set_value('chkCellShow') =='true';
            $displayData['formData']->bCellRequired    = set_value('chkCellReq')  =='true';

            $displayData['formData']->bShowBDay        = set_value('chkBDayShow') =='true';
            $displayData['formData']->bBDayRequired    = set_value('chkBDayReq')  =='true';

            $displayData['formData']->bShowDisclaimer  = set_value('chkShowDisclaimer') =='true';
            $displayData['formData']->txtDisclaimer    = set_value('txtDisclaimer');
            $displayData['formData']->txtDisclaimerAck = set_value('txtDisclaimerAck');

               // volunteer group ddl
            $displayData['formData']->ddlVolGroup =
                       $this->groups->strDDLActiveGroupEntries('ddlVolGroup', CENUM_CONTEXT_VOLUNTEER, set_value('ddlVolGroup'), true);

            $displayData['formData']->strDDLCSS = $this->volReg->strCSSDropDown('ddlCSS', set_value('ddlCSS'), false);


            if ($lNumSkills > 0){
               foreach ($jobSkills as $skill){
                  $skill->bShow = set_value($skill->chkShow) =='true';
               }
            }

            if ($lNumTables > 0){
               foreach ($userTables as $utable){
                  if ($utable->lNumFields > 0){

                        // exclude log fields
                     foreach ($utable->fields as $field){
                        if ($field->enumFieldType!=CS_FT_LOG){
                           $field->bShow     = set_value($field->strFNShow) =='true';
                           $field->bRequired = set_value($field->strFNRequired) =='true';
                        }
                     }
                  }
               }
            }
         }

            // logo selection table
         $logoOpts = new stdClass;
         $logoOpts->enumEntryType     = CENUM_IMGDOC_ENTRY_IMAGE;
         $logoOpts->enumContextType   = CENUM_CONTEXT_ORGANIZATION;
         $logoOpts->lFID              = $glChapterID;
         $logoOpts->lCellWidth        = 90;
         $logoOpts->lCellHeight       = 90;
         $logoOpts->lBorderWidth      = 1;
         $logoOpts->lCellsPerRow      = 4;
         $logoOpts->bShowCaption      = false;
         $logoOpts->bShowDescription  = false;
         $logoOpts->bShowDate         = false;
         $logoOpts->bShowOriginalFN   = false;
         $logoOpts->bAddRadioSelect   = true;
         $logoOpts->strRadioFieldName = 'rdoLogo';
         $logoOpts->lMatchID          = $lLogoID;
         $logoOpts->bShowNone         = true;
         $logoOpts->strShowNoneLabel  = 'No logo image';

         $displayData['formData']->rdoLogo = $this->cImgDoc->strImageDocTerseTable($logoOpts, $displayData['lNumLogoImages']);

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/vols', 'Volunteers',   'class="breadcrumb"')
                                   .' | '.anchor('volunteers/registration/view', 'Registration', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').'  Form';

         $displayData['title']          = CS_PROGNAME.' | Volunteers';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'vol_reg/reg_form_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
            // internal fields
         $rRec->strFormName          = trim($_POST['txtRegFormName']);
         $rRec->strContactEmail      = trim($_POST['txtVolCoordinatorEmail']);
         $rRec->strDescription       = trim($_POST['txtDescription']);
         $rRec->bCaptchaRequired     = trim(@$_POST['chkCaptchaRequired']) == 'true';
         $rRec->lLogoImageID         = (int)@$_POST['rdoLogo'];
         if ($rRec->lLogoImageID <= 0) $rRec->lLogoImgID = null;
         $rRec->lVolGroupID          = (int)@$_POST['ddlVolGroup'];
         if ($rRec->lVolGroupID <= 0) $rRec->lVolGroupID = null;
         $rRec->strCSSFN             = trim(@$_POST['ddlCSS']);

            // form registration permissions
         $rRec->bPermEditContact     = trim(@$_POST['chkPermContactInfo'])    == 'true';
         $rRec->bPermPassReset       = trim(@$_POST['chkPermPassReset'])      == 'true';
         $rRec->bPermViewGiftHistory = trim(@$_POST['chkPermGiftHistory'])    == 'true';
         $rRec->bPermEditJobSkills   = trim(@$_POST['chkPermJobSkills'])      == 'true';
         $rRec->bPermViewHrsHistory  = trim(@$_POST['chkPermViewVolLog'])     == 'true';
         $rRec->bPermAddVolHours     = trim(@$_POST['chkPermAddVolHrs'])      == 'true';
         $rRec->bVolShiftSignup      = trim(@$_POST['chkPermVolShiftSignup']) == 'true';

            // top banner
         $rRec->strBannerOrg          = trim($_POST['txtBannerOrg']);
         $rRec->strBannerTitle        = trim($_POST['txtBannerTitle']);
         $rRec->strIntro              = trim($_POST['txtIntro']);
         $rRec->strSubmissionText     = trim($_POST['txtSubmissionText']);

            // Standard Fields - show
         $rRec->bShowFName       = true;
         $rRec->bShowLName       = true;
         $rRec->bShowAddr        = trim(@$_POST['chkAddrShow'])  == 'true';
         $rRec->bShowEmail       = trim(@$_POST['chkEmailShow']) == 'true';
         $rRec->bShowPhone       = trim(@$_POST['chkPhoneShow']) == 'true';
         $rRec->bShowCell        = trim(@$_POST['chkCellShow'])  == 'true';
         $rRec->bShowBDay        = trim(@$_POST['chkBDayShow'])  == 'true';

            // Standard Fields - required
         $rRec->bFNameRequired    = true;
         $rRec->bLNameRequired    = true;
         $rRec->bAddrRequired     = trim(@$_POST['chkAddrReq'])  == 'true';
         $rRec->bEmailRequired    = trim(@$_POST['chkEmailReq']) == 'true';
         $rRec->bPhoneRequired    = trim(@$_POST['chkPhoneReq']) == 'true';
         $rRec->bCellRequired     = trim(@$_POST['chkCellReq'])  == 'true';
         $rRec->bBDateRequired    = trim(@$_POST['chkBDayReq'])  == 'true';

            // Disclaimer
         $rRec->bShowDisclaimer    =
         $rRec->bDisclaimerAckRqrd = trim(@$_POST['chkShowDisclaimer'])  == 'true';
         $rRec->strDisclaimer      = trim($_POST['txtDisclaimer']);
         $rRec->strDisclaimerAck   = trim($_POST['txtDisclaimerAck']);

            // Job Skills
         if ($lNumSkills > 0){
            foreach ($jobSkills as $skill){
               $skill->bShow = trim(@$_POST[$skill->chkShow]) == 'true';
            }
         }

            // personalized tables
         if ($lNumTables > 0){
            foreach ($userTables as $utable){
               if ($utable->lNumFields > 0){

                  foreach ($utable->fields as $field){
                     if ($field->enumFieldType!=CS_FT_LOG){
                        $field->bShow     = trim(@$_POST[$field->strFNShow]) =='true';
                        $field->bRequired = trim(@$_POST[$field->strFNRequired]) =='true';
                     }
                  }
               }
            }
         }

         if ($bNew){
              $lRegFormID = addNewCustomForm(CENUM_CONTEXT_VOLUNTEER, $rRec);
         }else {
            updateCustomForm(CENUM_CONTEXT_VOLUNTEER, $lRegFormID, $rRec);
         }
         $this->volReg->updateJobSkillFields($lRegFormID, $lNumSkills, $jobSkills);
         updateFormPTableFields(CENUM_CONTEXT_VOLUNTEER, $lRegFormID, $lNumTables, $userTables);

         $this->session->set_flashdata('msg', 'The registration form was '.($bNew ? 'added' : 'updated').'.');
         redirect('volunteers/registration/view');
      }
   }

   function validateEmailPerms($strChecked){
   //---------------------------------------------------------------------
   // if the volunteer can create a log-in, then the email field is
   // required (will act as user log account)
   //---------------------------------------------------------------------
      $bShowEmail = @$_POST['chkEmailShow'] == 'true';
      $bEmailReq  = @$_POST['chkEmailReq']  == 'true';

      if ($bShowEmail && $bEmailReq) return(true);

      $bAnyLoginPerms =
               @$_POST['chkPermContactInfo']    == 'true' ||
               @$_POST['chkPermPassReset']      == 'true' ||
               @$_POST['chkPermGiftHistory']    == 'true' ||
               @$_POST['chkPermJobSkills']      == 'true' ||
               @$_POST['chkPermViewVolLog']     == 'true' ||
               @$_POST['chkPermAddVolHrs']      == 'true' ||
               @$_POST['chkPermVolShiftSignup'] == 'true';

      if ($bAnyLoginPerms){
         $this->form_validation->set_message('validateEmailPerms',
                   'The <b>email</b> field is required if any log-in permissions are set.
                   The volunteer\'s email acts as their account user ID.');
         return(false);
      }else {
         return(true);
      }
   }

   function verifyDisclaimerAck($strAck){
      $strAck = trim($strAck);
      $bShowDis = @$_REQUEST['chkShowDisclaimer']=='true';
      if ($bShowDis && $strAck==''){
         $this->form_validation->set_message('verifyDisclaimerAck',
                   'The disclaimer acknowledgement is required if you are displaying the an acknowlegement.');
         return(false);
      }else {
         return(true);
      }
   }

   function verifyDisclaimer($strDis){
      $strDis = trim($strDis);
      $bShowDis = @$_REQUEST['chkShowDisclaimer']=='true';

      if ($bShowDis && $strDis==''){
         $this->form_validation->set_message('verifyDisclaimer',
                   'Please enter your disclaimer text or uncheck the <b>Show Disclaimer</b> checkbox.');
         return(false);
      }elseif (!$bShowDis && $strDis!=''){
         $this->form_validation->set_message('verifyDisclaimer',
                   'Please click the <b>"Show Disclaimer"</b> checkbox if entering disclaimer text.');
         return(false);
      }else {
         return(true);
      }
   }

   function remove($lVolRegFormID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('editPeopleBizVol')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lVolRegFormID, 'vol. registration ID');

         /*------------------------------------------------
             models/libraries/helpers
         ------------------------------------------------*/
      $this->load->helper('vols/vol_links');
      $this->load->helper('dl_util/custom_forms');
      $this->load->model ('vol_reg/mvol_reg', 'volReg');

      $this->volReg->loadVolRegFormsViaRFID($lVolRegFormID);
      $strFormName = $this->volReg->regRecs[0]->strFormName;

      removeCustomForm(CENUM_CONTEXT_VOLUNTEER, $lVolRegFormID);
      $this->volReg->deleteJobSkillsFields($lVolRegFormID);

      $this->session->set_flashdata('msg', 'The volunteer registration form <b>'.htmlspecialchars($strFormName).'</b> was removed.');
      redirect_VolRegForms();
   }




}
