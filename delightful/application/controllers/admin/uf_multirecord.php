<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class uf_multirecord extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function addEditMultiRecord($lTableID, $lFID, $lRecID=0,
                       $lEnrollRecID=0, $bUseReturnPath='false'){
   //-------------------------------------------------------------------------
   // $lEnrollRecID is only used when working with client program attendance
   // records. It links to the parent enrollment table entry.
   //-------------------------------------------------------------------------
      global $gbDateFormatUS, $gErrMessages, $gbShowHiddenVerifyError;
      global $genumDateFormat;
      global $glTableID;

      $displayData = array();
      $displayData['js'] = '';
      $gErrMessages = array();
      $bEnrollment  = false;
      $gbShowHiddenVerifyError = false;

      $bUseReturnPath = $bUseReturnPath=='true';

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('dl_util/time_date');  // for date verification
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->model  ('admin/madmin_aco',    'clsACO');
      $this->load->model  ('personalization/muser_fields',         'clsUF');
      $this->load->model  ('personalization/muser_fields_display', 'clsUFD');
      $this->load->model  ('personalization/muser_schema', 'cUFSchema');
      $this->load->model  ('admin/mpermissions',                   'perms');
      $this->load->helper ('dl_util/context');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('clients/client_program');
      $this->load->library('util/dl_date_time', '',                'clsDateTime');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

      $this->load->helper('dl_util/verify_id');
      if (!vid_bUserTableIDExists($this, $lTableID, $enumTType)) vid_bTestFail($this, false, 'user table ID', $lTableID);
      $glTableID = $lTableID;

         // Client program? (special type of multi-record personalized table, always associated with client)
      $displayData['bCProg'] = $bCProg = bTypeIsClientProg($enumTType);
      if ($bCProg){
         $this->load->model('client_features/mcprograms', 'cprograms');
         $displayData['bEnrollment'] = $bEnrollment = $enumTType==CENUM_CONTEXT_CPROGENROLL;
         $displayData['cprogType']   = $cprogType = $enumTType;
         $enumTType = CENUM_CONTEXT_CLIENT;
      }

      verifyIDsViaType($this, $enumTType, $lFID, false);

      $displayData['lTableID']       = $lTableID     = (integer)$lTableID;
      $displayData['lFID']           = $lFID         = (integer)$lFID;
      $displayData['lRecID']         = $lRecID       = (integer)$lRecID;
      $displayData['lEnrollRecID']   = $lEnrollRecID = (integer)$lEnrollRecID;
      $displayData['bNew']           = $bNew         = $lRecID==0;
      $displayData['bUseReturnPath'] = $bUseReturnPath;

         //---------------------------------------------------
         // load personalized table and field definitions
         //---------------------------------------------------
      $this->clsUFD->lTableID   = $lTableID;
      $this->clsUFD->lForeignID = $lFID;
      $this->clsUFD->loadTableViaTableID();
      $displayData['utable'] = $utable = &$this->clsUFD->userTables[0];

      $this->clsUFD->loadFieldsGeneric(true, $lTableID, null);
      $utable->lNumFields         = $lNumFields         = $this->clsUFD->lNumFields;
      $utable->lNumEditableFields = $lNumEditableFields = $this->clsUFD->lNumEditableFields;
      $utable->bAnyPrefill        = $bAnyPrefill        = $this->clsUFD->bAnyPrefillFields();

      $utable->bCProg      = $bCProg;
      $utable->bEnrollment = $bEnrollment;
      $displayData['bCusVerification'] = $bCusVerification = false;

      $displayData['strSafeAttendLabel'] = $displayData['strSafeEnrollLabel'] = '';
      if ($bCProg){
         if ($bEnrollment){
            $this->cprograms->loadClientProgramsViaETableID($lTableID);
         }else {
            $utable->bHideDuration = false;
            $this->cprograms->loadClientProgramsViaATableID($lTableID);
            $utable->lEnrollRecID = $lEnrollRecID;
         }
         $cprog = &$this->cprograms->cprogs[0];
         $displayData['strSafeAttendLabel'] = $cprog->strSafeAttendLabel;
         $displayData['strSafeEnrollLabel'] = $cprog->strSafeEnrollLabel;

         $lCProgID = $cprog->lKeyID;
         if ($bEnrollment){
            $this->cprograms->loadBaseERecViaERecID($cprog, $lRecID, $utable->lNumERecs, $utable->erecs);
            $displayData['bCusVerification'] = $bCusVerification =
                            $this->bSetVerificationFields($cprog->strE_VerificationModule, $cprog->strE_VModEntryPoint,
                                             $strVerificationModule, $strEntryPoint);
         }else {
            $this->cprograms->loadBaseARecViaARecID($cprog, $lRecID, $utable->lNumARecs, $utable->arecs);
            $this->cprograms->loadBaseERecViaERecID($cprog, $lEnrollRecID, $lNumERecs, $erecs);
            $erec = &$erecs[0];

            $displayData['bCusVerification'] = $bCusVerification =
                            $this->bSetVerificationFields($cprog->strA_VerificationModule, $cprog->strA_VModEntryPoint,
                                             $strVerificationModule, $strEntryPoint);

               // the Kennetta transformation
            if (CB_AAYHF){
               $strCProgName = $cprog->strProgramName;
               if (strtoupper(substr($strCProgName, 0, 15))=='SHIFT ASCENSION'){
                  $utable->bHideDuration = true;
               }
            }
         }

         $utable->strTableLabel = ($bEnrollment ? $cprog->strSafeEnrollLabel : $cprog->strSafeAttendLabel).' record: '
                                               .htmlspecialchars($cprog->strProgramName);
         if (!$bEnrollment){
            $utable->strTableLabel .= '<br><span style="font-size: 9pt;">'
                    .'&nbsp;(Enrollment: '.date($genumDateFormat, $erec->dteStart).' - '
                    .(is_null($erec->dteMysqlEnd) ? 'ongoing' : date($genumDateFormat, $erec->dteEnd)).')</span>';
         }
      }else {
         $utable->strTableLabel = htmlspecialchars($utable->strUserTableName);
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$utable   <pre>');
echo(htmlspecialchars( print_r($utable, true))); echo('</pre></font><br>');
die;
// ------------------------------------- */
         
         $displayData['bCusVerification'] = $bCusVerification =
                  $this->bSetVerificationFields($utable->strVerificationModule, $utable->strVModEntryPoint,
                                   $strVerificationModule, $strEntryPoint);
         
      }

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      if ($bCusVerification){
         $this->load->helper('path');
         $this->form_validation->set_rules('hVerify',      'Hidden verification',
                   'required|callback_hiddenVerify['.$strVerificationModule.','.$strEntryPoint.']');
      }

         // hidden value to force submission
      $this->form_validation->set_rules('hidForceSubmit', 'force', 'trim');

      if ($bCProg){
         if ($bEnrollment){
            $this->form_validation->set_rules('chkEnrolled', 'Enrolled', 'trim');
            $this->form_validation->set_rules('txtEStart',   'Enrollment Starting Date', 'trim|required'
                                                                          .'|callback_verifyCProgEStartDate');
            $this->form_validation->set_rules('txtEEnd',     'Enrollment Ending Date',   'callback_verifyCProgEEndDate');
         }else {
            $this->form_validation->set_rules('txtADate',     'Attendance Date', 'trim|required|callback_verifyADate');
            $this->form_validation->set_rules('txtCaseNotes', 'Case Notes',      'trim');
            if (!$utable->bHideDuration){
               $this->form_validation->set_rules('ddlDuration',  'Duration',        'trim|required|callback_verifyADuration');
            }
         }
      }

      loadSupportModels($enumTType, $lFID);
      if ($lNumEditableFields > 0){   // || $bCProg){
         $utable->ufields = &$this->clsUFD->fields;

            // load single data record
         $this->clsUFD->loadMRRecsViaRID($lRecID);

         $displayData['lNumMRRecs'] = $this->clsUFD->lNumMRRecs;

         $displayData['mRec']       = $mRec = &$this->clsUFD->mrRecs[0];

            // do we need to pre-fill certain fields from a recent record?
         if ($bNew && $bAnyPrefill){
            $lPrevRecID = $this->clsUFD->lMostRecentRecID_ViaTID_FID($lTableID, $lFID);
            if (!is_null($lPrevRecID)){
               $this->prefillRec($lTableID, $mRec, $lPrevRecID, $this->clsUFD->lNumFields, $this->clsUFD->fields);
            }
         }
         $this->setValidationUMTables($displayData['js'], $utable, $bNew, $mRec);
      }

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');
         $this->initUTableDates($displayData['js'], $utable, $bNew, $mRec);
         $this->initUTableDDLs($utable);

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($bCProg){
               if ($bEnrollment){
                  $this->initCProgE($bNew, $displayData, $utable);
               }else {
                  $this->initCProgA($bNew, $displayData, $utable);
               }
            }

               // personalized tables
            $this->setUTableDDLs($utable, $bNew, $mRec);
         }else {
            setOnFormError($displayData);

            if ($bCProg){
               if ($bEnrollment){
                  $this->reloadCProgE($utable);
               }else {
                  $this->reloadCProgA($utable);
               }
            }

               // personalized tables
            $this->repopulateMultiTable($utable);
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $this->clsUFD->tableContext(0);
         $this->clsUFD->tableContextRecView(0);
         $displayData['strHTMLSummary'] = $this->clsUFD->strHTMLSummary;
         $displayData['errMessages']    = arrayCopy($gErrMessages);

         $displayData['pageTitle']      = $this->clsUFD->strBreadcrumbsTableDisplay(0);

         $displayData['title']          = CS_PROGNAME.' | Personalized Tables';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'personalization/uf_multi_rec_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $lRecID = $this->clsUFD->lSaveMultiRecViaPost($bNew, $lFID, $lRecID, $utable);
         $this->session->set_flashdata('msg', 'A record was '.($bNew ? 'added' : 'updated')
                        .' in table <b>'.$utable->strTableLabel.'</b>');

//            // flashdata got hosed... using session instead;
//            // also - the unset on session variables is not working....
//         if (isset($_SESSION[CS_NAMESPACE.'rpAttendanceAddEdit']) &&
//             $_SESSION[CS_NAMESPACE.'rpAttendanceAddEdit'] != ''){
//            $strReturnPath = $_SESSION[CS_NAMESPACE.'rpAttendanceAddEdit'];
//            $_SESSION[CS_NAMESPACE.'rpAttendanceAddEdit'] = '';
//            unset($GLOBALS[_SESSION][CS_NAMESPACE.'rpAttendanceAddEdit']);
////            unset($_SESSION[CS_NAMESPACE.'rpAttendanceAddEdit']);

            // if a custom return path is requested, and if the associated session
            // variable return path is set, return to that location
         if ($bUseReturnPath && isset($_SESSION[CS_NAMESPACE.'rpAttendanceAddEdit']) &&
                                      $_SESSION[CS_NAMESPACE.'rpAttendanceAddEdit'] != ''){
               redirect($_SESSION[CS_NAMESPACE.'rpAttendanceAddEdit']);
         }elseif (
             $bUseReturnPath && isset($_SESSION[CS_NAMESPACE.'rpEnrollAddEdit']) &&
                                      $_SESSION[CS_NAMESPACE.'rpEnrollAddEdit'] != ''){
               redirect($_SESSION[CS_NAMESPACE.'rpEnrollAddEdit']);
         }else {
            if ($bNew && $bCProg && $bEnrollment){
               redirect('cprograms/enroll_attend_add_edit/qAddAttend/'.$lCProgID.'/'.$lTableID.'/'.$lFID.'/'.$lRecID);
            }else {
               redirect('admin/uf_multirecord_view/viewMRViaFID/'.$lTableID.'/'.$lFID.'/'.$lEnrollRecID);
            }
         }
      }
   }

   function prefillRec($lTableID, &$mRec, $lPrevRecID, $lNumFields, $fields){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cPrefillUFD = new muser_fields_display;

      $cPrefillUFD->lTableID   = $lTableID;
      $cPrefillUFD->loadTableViaTableID();
      $prevtable = &$cPrefillUFD->userTables[0];
      $prevtable->lNumFields = $lNumFields;

      $prevtable->ufields = &$fields;

      $cPrefillUFD->loadMRRecsViaRID($lPrevRecID);
      $prevRec = &$cPrefillUFD->mrRecs[0];

      $mRec = new stdClass;
      foreach ($fields as $field){
         if ($field->bPrefilled){
            $strFN = $field->strFieldNameInternal;
            $mRec->$strFN = $prevRec->$strFN;
            if ($field->enumFieldType == CS_FT_DDLMULTI){
               $mRecMDDLFN = $strFN.'_ddlMulti';
               $mRec->$mRecMDDLFN = new stdClass;
               $mRec->$mRecMDDLFN->lNumEntries = $prevRec->$mRecMDDLFN->lNumEntries;
               $mRec->$mRecMDDLFN->entries = arrayCopy($prevRec->$mRecMDDLFN->entries);
            }
         }
      }
   }

   function hiddenVerify($strVal, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('personalization/validate_custom_verification');
      return(customVal\hiddenVerify($strVal, $strOpts));
   }

   function bSetVerificationFields($strTestVModule, $strTestEPoint,
                                  &$strVerificationModule, &$strEntryPoint){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $bCustomVerify = $strTestVModule != '';
      if ($bCustomVerify){
         $strVerificationModule = $strTestVModule;
         $strEntryPoint         = $strTestEPoint;
      }else {
         $strVerificationModule = $strEntryPoint = null;
      }
      return($bCustomVerify);
   }

   function verifyAActivity($strDDL){
echo(__FILE__.' '.__LINE__.'<br>'."\n");
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$strDDL = $strDDL <br></font>\n");

die;
return(false);
   }

   function verifyADate($strADate){
      if (bValidVerifyDate($strADate)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyADate', 'The attendance date you entered is not valid.');
         return(false);
      }
   }

   function verifyADuration($strDDLValue){
      $dDuration = (float)trim($strDDLValue);
      if ($dDuration <= 0.0){
         $this->form_validation->set_message('verifyADuration', 'Please select a duration.');
         return(false);
      }else {
         return(true);
      }
   }

   function verifyCProgEStartDate($strStartDate){
      if (bValidVerifyDate($strStartDate)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyCProgEStartDate', 'The starting date you entered is not valid.');
         return(false);
      }
   }

   function verifyCProgEEndDate($strEndDate){
      if (trim($strEndDate)=='') return(true);
      if (!bValidVerifyDate($strEndDate)){
         $this->form_validation->set_message('verifyCProgEEndDate', 'The ending date you entered is not valid.');
         return(false);
      }
         // at this point we know the end date is valid. If the start
         // date is not valid, return true, otherwise make sure the
         // end date is on or after the start date
      $strStartDate = trim($_POST['txtEStart']);
      if (!bValidVerifyDate($strStartDate)) return(true);
      if (bVerifyCartBeforeHorse($strStartDate, $strEndDate)){
         return(true);
      }else {
         $this->form_validation->set_message('verifyCProgEEndDate', 'The end date is before the start date!');
         return(false);
      }
   }

   private function initCProgE($bNew, &$displayData, &$utable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      $utable->cProgE = new stdClass;
      $displayData['js'] .= strDatePicker('txtEStart', true);
      $displayData['js'] .= strDatePicker('txtEEnd', true);
      if ($bNew){
         $utable->cProgE->txtEStart = '';
         $utable->cProgE->txtEEnd = '';
         $utable->cProgE->bEnrolled = true;
      }else {
         $erec = $utable->erecs[0];
         $utable->cProgE->txtEStart = strNumericDateViaMysqlDate($erec->dteMysqlStart, $gbDateFormatUS);
         if (is_null($erec->dteMysqlEnd)){
            $utable->cProgE->txtEEnd = '';
         }else {
            $utable->cProgE->txtEEnd = strNumericDateViaMysqlDate($erec->dteMysqlEnd, $gbDateFormatUS);
         }
         $utable->cProgE->bEnrolled = $erec->bCurrentlyEnrolled;
      }
   }

   private function initCProgA($bNew, &$displayData, &$utable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      $utable->cProgA = new stdClass;
      $displayData['js'] .= strDatePicker('txtADate', true);
      if ($bNew){
         $utable->cProgA->txtADate       = '';
         $utable->cProgA->strCaseNotes   = '';
         $utable->cProgA->dDuration      = -1;
         $utable->cProgA->strDDLDuration = strDDLDuration('ddlDuration', true, -1);
      }else {
         $arec = $utable->arecs[0];
         $utable->cProgA->txtADate       = strNumericDateViaMysqlDate($arec->dteMysqlAttendance, $gbDateFormatUS);
         $utable->cProgA->strCaseNotes   = htmlspecialchars($arec->strCaseNotes);
         $utable->cProgA->dDuration      = number_format($arec->dDuration, 2, '.', '');
         $utable->cProgA->strDDLDuration = strDDLDuration('ddlDuration', true, $utable->cProgA->dDuration);
      }
   }

   function reloadCProgA(&$utable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $utable->cProgA = new stdClass;
      $utable->cProgA->txtADate       = set_value('txtADate');
      $utable->cProgA->strCaseNotes   = set_value('txtCaseNotes');
      $utable->cProgA->strDDLDuration = strDDLDuration('ddlDuration', true, set_value('ddlDuration'));
   }

   function reloadCProgE(&$utable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $utable->cProgE = new stdClass;
      $utable->cProgE->txtEStart = set_value('txtEStart');
      $utable->cProgE->txtEEnd   = set_value('txtEEnd');
      $utable->cProgE->bEnrolled = set_value('chkEnrolled')=='true';
   }

   function setValidationUMTables(&$js, &$utable, $bNew, &$mRec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      foreach ($utable->ufields as $ufield){
         $enumType   = $ufield->enumFieldType;
         $strFN      = $ufield->strFieldNameInternal;
         $strFNLabel = str_replace(',', '&#44;', htmlspecialchars($ufield->pff_strFieldNameUser));
         $bRequired  = $ufield->pff_bRequired;

         switch ($enumType){
            case CS_FT_CHECKBOX:
               $this->form_validation->set_rules($strFN, $strFNLabel, 'trim');
               if (!isset($mRec->$strFN)){
                  $ufield->bChecked = $ufield->pff_bCheckDef;
               }else {
                  $ufield->bChecked = $mRec->$strFN;
               }
               break;

            case CS_FT_DATE:
               $this->setValDate($bRequired, $strFN,  $strFNLabel, true);
               break;

            case CS_FT_TEXT255:
            case CS_FT_TEXT80:
            case CS_FT_TEXT20:
            case CS_FT_TEXTLONG:
               $this->form_validation->set_rules($strFN,
                            $strFNLabel, 'trim'.($bRequired ? '|required' : ''));
               if (!isset($mRec->$strFN)){
                  $ufield->txtValue = $ufield->pff_strTxtDef;
               }else {
                  $ufield->txtValue = htmlspecialchars($mRec->$strFN);
               }
               break;

            case CS_FT_INTEGER:
               $this->form_validation->set_rules($strFN, $strFNLabel, 'trim|callback_stripCommas|integer'.($bRequired ? '|required' : ''));
               if (!isset($mRec->$strFN)){
                  $ufield->txtValue = $ufield->pff_lDef;
               }else {
                  $ufield->txtValue = number_format($mRec->$strFN);
               }
               break;

            case CS_FT_CLIENTID:
               $this->form_validation->set_rules($strFN, $strFNLabel,
                                   'trim|callback_verifyClientID['.$strFN.','.$strFNLabel
                                                    .','.($bRequired ? 'true' : 'false').']');
               if (!isset($mRec->$strFN)){
                  $ufield->txtValue = '';
               }else {
                  $ufield->txtValue = number_format($mRec->$strFN);
               }
               break;

            case CS_FT_CURRENCY:
               $this->clsACO->loadCountries(true, true, true, $ufield->pff_lCurrencyACO);
               $country = $this->clsACO->countries[0];
               $ufield->pff_strCurrencySymbol = $country->strCurrencySymbol;
               $ufield->pff_strFlagImg        = $country->strFlagImg;
               $this->form_validation->set_rules($strFN, $strFNLabel,
                      'callback_verifyCurrency['.($bRequired ? 'true' : 'false')
                                 .', '.$strFN.', '.$strFNLabel.']');
               if (!isset($mRec->$strFN)){
                  $ufield->txtValue = number_format($ufield->pff_curDef, 2, '.', '');
               }else {
                  $ufield->txtValue = number_format($mRec->$strFN, 2, '.', '');
               }
               break;

            case CS_FT_DDL:
               $this->form_validation->set_rules($strFN, $strFNLabel,
                      'callback_verifyDDLSelect['.($bRequired ? 'true' : 'false')
                                 .', '.$strFN.', '.$strFNLabel.']');
               break;

            case CS_FT_DDLMULTI:
               $this->form_validation->set_rules($strFN, $strFNLabel,
                      'callback_verifyDDLMultiSelect['.($bRequired ? 'true' : 'false')
                                 .', '.$strFN.', '.$strFNLabel.']');
               break;

            case CS_FT_LOG:
            case CS_FT_HEADING:
               break;

            default:
               screamForHelp($enumType.': invalid field type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }
      }
   }

   function setValDate($bRequired,   $strDateFN,  $strLabel, $bAllowFuture){
      $this->form_validation->set_rules($strDateFN, $strLabel,
                     'callback_verifyMultiDate['.$strDateFN.','.$strLabel
                              .','.($bAllowFuture ? 'true' : 'false').','.($bRequired ? 'true' : 'false').']');
   }

   function initUTableDates(&$js, &$utable, $bNew, &$mRec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      if ($utable->lNumEditableFields > 0){
         foreach ($utable->ufields as $ufield){
            $enumType = $ufield->enumFieldType;
            if ($enumType==CS_FT_DATE){

               $strFN = $ufield->strFieldNameInternal;
               $js .= strDatePicker($strFN, true);
               if ($bNew && !isset($mRec->$strFN)){
                  $ufield->txtValue = '';
               }else {
                  $vDateVal = $mRec->$strFN;
                  if (is_null($vDateVal)){
                     $ufield->txtValue = '';
                  }else {
                     $ufield->txtValue = strNumericDateViaMysqlDate($vDateVal, $gbDateFormatUS);
                  }
               }
            }
         }
      }
   }

   function initUTableDDLs(&$utable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($utable->lNumEditableFields > 0){
         foreach ($utable->ufields as $ufield){
            $enumType = $ufield->enumFieldType;
            if ($enumType==CS_FT_DDL){
               $ufield->lMatch = -1;
               $ufield->strDDL = '';
            }
            if ($enumType==CS_FT_DDLMULTI){
               $ufield->lMatch = array();
               $ufield->strDDL = '';
            }
         }
      }
   }

   function stripCommas(&$strValue){
      $strValue = str_replace (',', '', $strValue);
      return(true);
   }

   function verifyClientID($strClientID, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gErrMessages;

      $opts = explode(',', $strOpts);

      $strFN        = trim($opts[0]);
      $strLabel     = trim($opts[1]);
      $bRequired    = $opts[2]=='true';
      if (!isset($gErrMessages[$strFN])) $gErrMessages[$strFN] = '';

      if ($bRequired){
         if ($strClientID==''){
            $gErrMessages[$strFN] .= '<div class="formError">'.'The '.$strLabel.' field is required.</div>';
            return(false);
         }
      }elseif ($strClientID=='') {
         return(true);
      }

      if (!is_numeric($strClientID)){
         $gErrMessages[$strFN] .= '<div class="formError">'.'The client ID is not valid.</div>';
         return(false);
      }

      $lClientID = (int)$strClientID;
      if ($lClientID <= 0){
         $gErrMessages[$strFN] .= '<div class="formError">'.'The client ID is not valid.</div>';
         return(false);
      }

      if (!verifyID($this, $lClientID, 'client ID', false)){
         $gErrMessages[$strFN] .= '<div class="formError">'.'The client ID you specified is not associated with a client in the database.</div>';
         return(false);
      }else {
         return(true);
      }
   }

   function verifyMultiDate($strFieldValue, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gErrMessages, $gdteNow;

      $strFieldValue = trim($strFieldValue);

      $opts = explode(',', $strOpts);

      $strFN        = trim($opts[0]);
      $strLabel     = '<b>"'.trim($opts[1]).'"</b>';
      $bAllowFuture = $opts[2]=='true';
      $bRequired    = $opts[3]=='true';
      if (!isset($gErrMessages[$strFN])) $gErrMessages[$strFN] = '';

      if ($bRequired){
         if ($strFieldValue==''){
            $gErrMessages[$strFN] .= '<div class="formError">'.'The '.$strLabel.' field is required.</div>';
            return(false);
         }
      }elseif ($strFieldValue=='') {
         return(true);
      }

      if (!bValidVerifyDate($strFieldValue)){
         $gErrMessages[$strFN] .= '<div class="formError">'.'The '.$strLabel.' field is not valid.</div>';
         return(false);
      }

      if (!$bAllowFuture){
         if (!bValidVerifyNotFuture($strFieldValue)){
            $gErrMessages[$strFN] .= '<div class="formError">'.'The '.$strLabel.' is in the future.</div>';
            return(false);
         }
      }
      return(true);
   }

   function verifyCurrency($strVal, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gErrMessages;

      $strCurrency = trim($strVal);
      $this->stripCommas($strCurrency);

      $opts = explode(',', $strOpts);
      $bRequired    = $opts[0]=='true';
      $strFN        = trim($opts[1]);
      $strLabel     = trim($opts[2]);
      if (!isset($gErrMessages[$strFN])) $gErrMessages[$strFN] = '';

      if ($bRequired){
         if ($strCurrency==''){
            $gErrMessages[$strFN] .= '<div class="formError">'.'The '.$strLabel.' field is required.</div>';
            return(false);
         }
      }elseif ($strVal=='') {
         return('');
      }

      if (!is_numeric($strCurrency)){
         $gErrMessages[$strFN] .= '<div class="formError">'.'The '.$strLabel.' amount is not valid.</div>';
         return(false);
      }else {
         return($strCurrency);
      }
   }

   function setUTableDDLs(&$utable, $bNew, $mRec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($utable->lNumEditableFields > 0){
         foreach ($utable->ufields as $ufield){
            $enumType = $ufield->enumFieldType;
            $lFieldID = $ufield->pff_lKeyID;
            $strFN    = $ufield->strFieldNameInternal;
            if ($enumType==CS_FT_DDL){
               if (!isset($mRec->$strFN)){
                  $lMatchID = $ufield->lMatch;
               }else {
                  $lMatchID = $mRec->$strFN;
               }
               $ufield->strDDL =
                      '<select name="'.$strFN.'">
                       <option value="-1">&nbsp;</option>'."\n"
                     .$this->clsUF->strDisplayUF_DDL($lFieldID, $lMatchID)
                     .'</select>'."\n";
            }elseif ($enumType==CS_FT_DDLMULTI){
               $strMDDLFN = $strFN.'_ddlMulti';
               $lMatchIDs = array();
               if ($bNew && !isset($mRec->$strMDDLFN->lNumEntries)){
                  $lMatchIDs = arrayCopy($ufield->lMatch);
               }else {
                  if ($mRec->$strMDDLFN->lNumEntries > 0){
                     foreach ($mRec->$strMDDLFN->entries as $en){
                        $lMatchIDs[] = $en->lDDLID;
                     }
                  }
               }

               $ufield->strDDLMulti =
                      '<select multiple size=5 name="'.$strFN.'[]">
                       <option value="-1">&nbsp;</option>'."\n"
                     .$this->clsUF->strDisplayUF_DDL($lFieldID, $lMatchIDs)
                     .'</select>'."\n";
            }
         }
      }
   }

   function repopulateMultiTable(&$utable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!isset($utable->ufields)) return;  // special case of Client Program table with no additional fields
      foreach ($utable->ufields as $ufield){
         $enumType = $ufield->enumFieldType;
         $strFN    = $ufield->strFieldNameInternal;

         switch ($enumType){
            case CS_FT_CHECKBOX:
               $ufield->bChecked = set_value($strFN)=='true';
               break;
            case CS_FT_DATE:
               $ufield->txtValue = set_value($strFN);
               break;

            case CS_FT_TEXT255:
            case CS_FT_TEXT80:
            case CS_FT_TEXT20:
            case CS_FT_TEXTLONG:
               $ufield->txtValue = set_value($strFN);
               break;
            case CS_FT_INTEGER:
               $ufield->txtValue = set_value($strFN);
               break;
            case CS_FT_CLIENTID:
               $ufield->txtValue = set_value($strFN);
               break;
            case CS_FT_CURRENCY:
               $ufield->txtValue = set_value($strFN);
               break;
            case CS_FT_DDL:
               $ufield->lMatch = (int)$_POST[$strFN];
               break;
            case CS_FT_DDLMULTI:
               $ufield->lMatch = array();
               if (isset($_POST[$strFN])){
                  foreach ($_POST[$strFN] as $lDDLID){
                     $ufield->lMatch[] = (int)$lDDLID;
                  }
               }
               break;
            case CS_FT_HEADING:
               break;
            default:
               screamForHelp($enumType.': invalid field type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }
      }

      $this->setUTableDDLs($utable, true, null);
   }

   function verifyDDLSelect($strVal, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gErrMessages;

      $lDDLVal = (int)trim($strVal);
      $opts = explode(',', $strOpts);
      $bRequired    = $opts[0]=='true';
      $strFN        = trim($opts[1]);
      $strLabel     = trim($opts[2]);
      if (!isset($gErrMessages[$strFN])) $gErrMessages[$strFN] = '';

      if (!$bRequired) return(true);
      if ($lDDLVal <= 0){
         $gErrMessages[$strFN] .= '<div class="formError">'.'Please make a selection for '.$strLabel.'.</div>';
         return(false);
      }else {
         return(true);
      }
   }

   function verifyDDLMultiSelect($strVal, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gErrMessages;

      $opts = explode(',', $strOpts);
      $bRequired    = $opts[0]=='true';
      $strFN        = trim($opts[1]);
      $strLabel     = trim($opts[2]);
      if (!isset($gErrMessages[$strFN])) $gErrMessages[$strFN] = '';

      if (!$bRequired) return(true);
      if (!is_array($strVal)){
         $gErrMessages[$strFN] .= '<div class="formError">'.'Please select one or more entries for '.$strLabel.'.</div>';
         return(false);
      }
      if (count($strVal)==1 && ((int)$strVal[0])<=0){
         $gErrMessages[$strFN] .= '<div class="formError">'.'Please select one or more entries for '.$strLabel.'.</div>';
         return(false);
      }else {
         return(true);
      }
   }

   function removeRecord($lTableID, $lFID, $lRecID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      if (!vid_bUserTableIDExists($this, $lTableID, $enumTabType)) vid_bTestFail($this, false, 'user table ID', $lTableID);
      verifyIDsViaType($this, $enumTabType, $lFID, false);

      $lEnrollID = 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('personalization/muser_fields',         'clsUF');
      $this->load->model  ('personalization/muser_fields_display', 'clsUFD');
      $this->load->model  ('admin/mpermissions',                   'perms');
      $this->load->helper ('clients/client_program');

      $this->clsUFD->lTableID   = $lTableID;
      $this->clsUFD->loadTableViaTableID();
      $utable = &$this->clsUFD->userTables[0];
      $enumTType   = $utable->enumTType;
      $bCProg      = bTypeIsClientProg($enumTType);
      $bEnrollment = $enumTType==CENUM_CONTEXT_CPROGENROLL;

         // if deleting an enrollment record, we must also delete
         // any associated attendance records
      if ($bCProg){
         $this->load->model('client_features/mcprograms', 'cprograms');
         if ($bEnrollment){
            $this->cprograms->loadClientProgramsViaETableID($lTableID);
            $cprog = &$this->cprograms->cprogs[0];
            $strATable = $cprog->strAttendanceTable;
            $strATableFNPre = $cprog->strATableFNPrefix;
            $this->cprograms->deleteATableRecsViaEnrollRecID($strATable, $strATableFNPre, $lRecID);
         }else {
               // load the enrollment record to provide the proper return path
            $this->cprograms->loadClientProgramsViaATableID($lTableID);
            $cprog = &$this->cprograms->cprogs[0];
            $this->cprograms->loadBaseARecViaARecID($cprog, $lRecID, $lNumARecs, $arecs);
            $lEnrollID = $arecs[0]->lEnrollID;
         }

         $strTableLabel = 'An '.($bEnrollment ? 'enrollment' : 'attendance')
               .' record was removed from client program <b>'
               .htmlspecialchars($cprog->strProgramName).'</b>.';
      }else {
         $strTableLabel = 'The selected record was removed from '
                     .'table <b>'.htmlspecialchars($utable->strUserTableName).'</b>';
      }

      $this->clsUFD->removeMRRecord($lRecID);

      $this->session->set_flashdata('msg', $strTableLabel);
      redirect('admin/uf_multirecord_view/viewMRViaFID/'.$lTableID.'/'.$lFID.'/'.$lEnrollID);
   }


}