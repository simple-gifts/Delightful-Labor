<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class people_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function contact($lPID='', $lHID=0){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $gclsChapterACO, $gbDateFormatUS, $gbVolLogin, $gVolPerms, $glVolPeopleID, $glUserID;
      global $gstrDuplicateWarning;

      $gstrDuplicateWarning = '';

      if ($gbVolLogin) $lPID = $glVolPeopleID;

      $this->load->helper('dl_util/verify_id');
      if ($lPID.'' != '0') verifyID($this, $lPID, 'people ID');
      if ($lHID.'' != '0') verifyID($this, $lHID, 'household ID');

      $displayData = array();
      $displayData['formData'] = new stdClass;
      $displayData['lPID'] = $lPID = (integer)$lPID;

      $displayData['bNew'] = $bNew = $lPID <= 0;
      $bExistingHousehold = $lHID > 0;

      if ($bNew){
         if (!bTestForURLHack('dataEntryPeopleBizVol')) return;
      }else {
         $bOkayToEdit = bAllowAccess('dataEntryPeopleBizVol') || bAllowAccess('volEditContact');
         if (!$bOkayToEdit){
            bTestForURLHack('forceFail');
            return;
         }
      }

         // models associated with the people record
      $this->load->model('people/mpeople', 'clsPeople');
      $this->load->model('admin/madmin_aco', 'clsACO');
      if (!$gbVolLogin){
         $this->load->model('sponsorship/msponsorship', 'clsSpon');
         $this->load->model('donations/mdonations', 'clsGifts');
      }

      if ($bNew){
         $this->load->model ('util/mdup_checker',  'cDupChecker');
      }
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/web_layout');
//      $this->load->helper('dl_util/email_web');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');

         //--------------------------
         // load people record
         //--------------------------
      $this->clsPeople->sqlWhereExtra = " AND pe_lKeyID = $lPID ";
      if ($gbVolLogin){
         $this->clsPeople->loadPeople(false, false, true);
      }else {
         $this->clsPeople->loadPeople(true, true, true);
      }
      $people = $this->clsPeople->people[0];

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtFName',      'First Name',  'trim|required');
      $this->form_validation->set_rules('txtTitle',      '',  'trim');
      $this->form_validation->set_rules('txtMName',      '',  'trim');
      $this->form_validation->set_rules('txtLName',      'Last Name',  'trim|required');
      $this->form_validation->set_rules('txtPName',      '',  'trim');
      $this->form_validation->set_rules('txtSal',        '',  'trim');

      $this->form_validation->set_rules('txtAddr1',        '',  'trim');
      $this->form_validation->set_rules('txtAddr2',        '',  'trim');
      $this->form_validation->set_rules('txtCity',         '',  'trim');
      $this->form_validation->set_rules('txtState',        '',  'trim');
      $this->form_validation->set_rules('txtZip',          '',  'trim');
      $this->form_validation->set_rules('txtCountry',      '',  'trim');
      $this->form_validation->set_rules('txtNotes',        '',  'trim');

      $this->form_validation->set_rules('txtPhone',      '',  'trim');
      $this->form_validation->set_rules('txtCell',       '',  'trim');

      $this->form_validation->set_rules('rdoGender',      '',  'trim');
      $this->form_validation->set_rules('txtBDate',       '',  'trim|callback_peopleRecVerifyBDateValid'
                                                                 .'|callback_peopleRecVerifyBDatePast');
      if ($gbVolLogin){
         $this->form_validation->set_rules('txtEmail',      'Email',  'trim|required|valid_email|callback_verifyUniqueUserID');
      }else {
         $this->form_validation->set_rules('txtEmail',   'Email',  'trim|valid_email');
         $this->form_validation->set_rules('rdoACO',     '',  'trim');
         $this->form_validation->set_rules('ddlAttrib',  'Attributed to');
      }

         // test for duplicate people
      if ($bNew){
         $this->form_validation->set_rules('hiddenTestDup', 'dummy',  'callback_verifyNoDups');
         $displayData['bHiddenNewTestDup'] = true;
      }

      if ($bNew){
         $people->lHouseholdID = $lHID;
         if ($lHID <= 0){
            $people->strHouseholdName = '<i>new</i>';
         }else {
            $people->strHouseholdName = $this->clsPeople->strHouseholdNameViaHID($lHID);
         }
      }

      if ($this->form_validation->run() == FALSE){
         $displayData['js'] = '';
         $this->load->library('generic_form');
         $this->load->model  ('util/mlist_generic',     'clsList');
         $this->clsList->enumListType = CENUM_LISTTYPE_ATTRIB;

         $displayData['strDuplicateWarning'] = $gstrDuplicateWarning;
         if (validation_errors()==''){
            if ($bNew){
               $displayData['formData']->strBDay     =
               $displayData['formData']->txtTitle    =
               $displayData['formData']->txtFName    =
               $displayData['formData']->txtMName    =
               $displayData['formData']->txtLName    =
               $displayData['formData']->txtPName    =
               $displayData['formData']->txtSal      =
               $displayData['formData']->txtNotes    =

               $displayData['formData']->txtEmail    =
               $displayData['formData']->txtCell     = '';

               $displayData['formData']->enumGender  = 'Unknown';
               $displayData['formData']->dteMysqlBirthDate = '';

               if ($bExistingHousehold){
                  $clsHouse = new mpeople;
                  $clsHouse->loadPeopleViaPIDs($lHID, false, false);
                  $house    = $clsHouse->people[0];

                  $displayData['formData']->txtAddr1    = htmlspecialchars($house->strAddr1);
                  $displayData['formData']->txtAddr2    = htmlspecialchars($house->strAddr2);
                  $displayData['formData']->txtCity     = htmlspecialchars($house->strCity);
                  $displayData['formData']->txtState    = htmlspecialchars($house->strState);
                  $displayData['formData']->txtZip      = htmlspecialchars($house->strZip);
                  $displayData['formData']->txtCountry  = htmlspecialchars($house->strCountry);
                  $displayData['formData']->txtPhone    = htmlspecialchars($house->strPhone);
                  $lACO = $house->lACO;
               }else {
                  $displayData['formData']->txtAddr1    =
                  $displayData['formData']->txtAddr2    =
                  $displayData['formData']->txtCity     =
                  $displayData['formData']->txtState    =
                  $displayData['formData']->txtZip      =
                  $displayData['formData']->txtCountry  =
                  $displayData['formData']->txtPhone    = '';
                  $lACO = $gclsChapterACO->lKeyID;
               }
               if (!$gbVolLogin){
                  $displayData['formData']->rdoACO      = $this->clsACO->strACO_Radios($lACO, 'rdoACO');
                  $displayData['strAttribDDL']          = $this->clsList->strLoadListDDL('ddlAttrib', true, -1);
               }
            }else {
               $displayData['formData']->txtTitle    = $people->strTitle;
               $displayData['formData']->txtFName    = htmlspecialchars($people->strFName);
               $displayData['formData']->txtMName    = htmlspecialchars($people->strMName);
               $displayData['formData']->txtLName    = htmlspecialchars($people->strLName);
               $displayData['formData']->txtPName    = htmlspecialchars($people->strPreferredName);
               $displayData['formData']->txtSal      = htmlspecialchars($people->strSalutation);
               $displayData['formData']->txtNotes    = htmlspecialchars($people->strNotes);

               $displayData['formData']->txtAddr1    = htmlspecialchars($people->strAddr1);
               $displayData['formData']->txtAddr2    = htmlspecialchars($people->strAddr2);
               $displayData['formData']->txtCity     = htmlspecialchars($people->strCity);
               $displayData['formData']->txtState    = htmlspecialchars($people->strState);
               $displayData['formData']->txtZip      = htmlspecialchars($people->strZip);
               $displayData['formData']->txtCountry  = htmlspecialchars($people->strCountry);

               $displayData['formData']->txtEmail    = htmlspecialchars($people->strEmail);
               $displayData['formData']->txtPhone    = htmlspecialchars($people->strPhone);
               $displayData['formData']->txtCell     = htmlspecialchars($people->strCell);

               $displayData['formData']->enumGender  = $people->enumGender;
               if (is_null($people->dteMysqlBirthDate)){
                  $displayData['formData']->strBDay = '';
               }else {
                  $displayData['formData']->strBDay = strNumericDateViaMysqlDate($people->dteMysqlBirthDate, $gbDateFormatUS);
               }
               if (!$gbVolLogin){
                  $displayData['formData']->rdoACO = $this->clsACO->strACO_Radios($people->lACO, 'rdoACO');
                  $displayData['strAttribDDL']     = $this->clsList->strLoadListDDL('ddlAttrib', true, $people->lAttributedTo);
               }
            }
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtTitle          = set_value('txtTitle');
            $displayData['formData']->txtFName          = set_value('txtFName');
            $displayData['formData']->txtMName          = set_value('txtMName');
            $displayData['formData']->txtLName          = set_value('txtLName');
            $displayData['formData']->txtPName          = set_value('txtPName');
            $displayData['formData']->txtSal            = set_value('txtSal');
            $displayData['formData']->txtNotes          = set_value('txtNotes');

            $displayData['formData']->txtAddr1          = set_value('txtAddr1');
            $displayData['formData']->txtAddr2          = set_value('txtAddr2');
            $displayData['formData']->txtCity           = set_value('txtCity');
            $displayData['formData']->txtState          = set_value('txtState');
            $displayData['formData']->txtZip            = set_value('txtZip');
            $displayData['formData']->txtCountry        = set_value('txtCountry');

            $displayData['formData']->txtEmail          = set_value('txtEmail');
            $displayData['formData']->txtPhone          = set_value('txtPhone');
            $displayData['formData']->txtCell           = set_value('txtCell');

            $displayData['formData']->enumGender        = set_value('rdoGender');
            $displayData['formData']->strBDay           = set_value('txtBDate');

            if (!$gbVolLogin){
               $displayData['formData']->rdoACO            = $this->clsACO->strACO_Radios(set_value('rdoACO'), 'rdoACO');
               $displayData['strAttribDDL']                = $this->clsList->strLoadListDDL('ddlAttrib', true, set_value('ddlAttrib'));
            }
         }
         $displayData['people'] = $people;

            //--------------------------
            // breadcrumbs
            //--------------------------
         if ($gbVolLogin){
            $displayData['pageTitle']  =
                                 anchor('people/people_record/view/'.$lPID, 'Record', 'class="breadcrumb"').' | Edit';
         }else {
            $displayData['pageTitle']  = anchor('main/menu/people',                'People', 'class="breadcrumb"')
                               .' | '.($bNew ? 'Record' : anchor('people/people_record/view/'.$lPID, 'Record', 'class="breadcrumb"'))
                               .' | '.($bNew ? 'Add New' : 'Edit');
         }

         $displayData['title']          = CS_PROGNAME.' | People';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'people/people_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->load->model('personalization/muser_fields',        'clsUF');
         $this->load->model('personalization/muser_fields_create', 'clsUFC');
         $this->load->model('admin/mpermissions',                  'perms');
         $this->load->helper('dl_util/util_db');

         $people= &$this->clsPeople->people[0];
         $people->lHouseholdID = (integer)$lHID;
         $people->strTitle        = trim($_POST['txtTitle']);

         $people->strFName         = trim($_POST['txtFName']);
         $people->strMName         = trim($_POST['txtMName']);
         $people->strLName         = trim($_POST['txtLName']);
         $people->strPreferredName = trim($_POST['txtPName']);
         $people->strSalutation    = trim($_POST['txtSal']);
         $people->strNotes         = trim($_POST['txtNotes']);
         if ($people->strPreferredName == '') $people->strPreferredName = $people->strFName;
         if ($people->strSalutation == '')    $people->strSalutation    = $people->strFName;

         $people->strAddr1        = trim($_POST['txtAddr1']);
         $people->strAddr2        = trim($_POST['txtAddr2']);
         $people->strCity         = trim($_POST['txtCity']);
         $people->strState        = trim($_POST['txtState']);
         $people->strZip          = trim($_POST['txtZip']);
         $people->strCountry      = trim($_POST['txtCountry']);

         if ($gbVolLogin){
            $strOriginalEmail = $people->strEmail;
         }

         $people->strEmail        = trim($_POST['txtEmail']);
         $people->strPhone        = trim($_POST['txtPhone']);
         $people->strCell         = trim($_POST['txtCell']);
         $people->enumGender      = trim($_POST['rdoGender']);

         if ($gbVolLogin){
            $this->load->model('admin/muser_accts', 'clsUser');
            $this->clsUser->updateUserAcctViaPeopleInfo($glUserID, $people);
         }

         if (!$gbVolLogin){
            $people->lACO = (integer)$_POST['rdoACO'];
            $lAttrib = (integer)$_REQUEST['ddlAttrib'];
            if ($lAttrib <= 0){
               $people->lAttributedTo = null;
            }else {
               $people->lAttributedTo = $lAttrib;
            }
         }

         $strBDate   = trim($_POST['txtBDate']);
         if ($strBDate==''){
            $people->dteMysqlBirthDate = null;
         }else {
            MDY_ViaUserForm($strBDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
            $people->dteMysqlBirthDate = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);
         }

         if ($bNew){
            $people->dteExpire = $people->dteMysqlDeath = null;
            $lPID = $this->clsPeople->lCreateNewPeopleRec();
            $this->session->set_flashdata('msg', 'The people record was added');
         }else {
            $this->clsPeople->updatePeopleRec($lPID);
            if ($gbVolLogin){
               $this->session->set_flashdata('msg', 'Your contact information was updated.');
            }else {
               $this->session->set_flashdata('msg', 'The people record was updated');
            }
         }
         redirect('people/people_record/view/'.$lPID);
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
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
      $this->cDupChecker->findSimilarPeopleNames($strFName, $strLName, $lNumMatches, $matches);

      if ($lNumMatches > 0){
         $gstrDuplicateWarning = $this->cDupChecker->strPeopleBizDupWarning(
                        true, 'chkImCoolWithDups', $lNumMatches, $matches);
         return(false);
      }else {
         return(true);
      }
   }


   function peopleRecVerifyBDateValid($strBDate){
      if ($strBDate=='') return(true);
      return(bValidVerifyDate($strBDate));
   }

   function peopleRecVerifyBDatePast($strBDate){
      if ($strBDate=='') return(true);
      return(bValidVerifyNotFuture($strBDate));
   }

   function verifyUniqueUserID($strEmail){
      global $glUserID;
      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                $strEmail, 'us_strUserName',
                $glUserID, 'us_lKeyID',
                false, null,
                false, null, null,
                false, null, null,
                'admin_users')){
         $this->form_validation->set_message('verifyUniqueUserID',
                        'This <b>email address</b> is currently in use. Please enter a different email or contact your volunteer coordinator.');
         return(false);
      }
      return(true);
   }

   function remove($lPID, $strReturnPath=null, $lReturnID=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('editPeopleBizVol')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPID, 'people ID');

      $lPID = (integer)$lPID;

      $this->load->model ('people/mpeople',               'clsPeople');
      $this->load->model ('biz/mbiz',                     'clsBiz');
      $this->load->model ('donations/mdonations',         'clsGifts');
      $this->load->model ('util/mrecycle_bin',            'clsRecycle');
      $this->load->model ('sponsorship/msponsorship',     'clsSpon');
      $this->load->model ('personalization/muser_fields', 'clsUF');
      $this->load->model ('groups/mgroups',               'groups');
      $this->load->helper('groups/groups');

      $this->clsPeople->lPeopleID = $lPID;
      $this->clsPeople->peopleInfoLight();

      $this->clsPeople->removePersonBiz(false);

      $this->session->set_flashdata('msg', 'The record for '.$this->clsPeople->strSafeName.' (peopleID '.str_pad($lPID, 5, '0', STR_PAD_LEFT).') was removed.');
      $strBizLetter = strtoupper(substr($this->clsPeople->strLName, 0, 1));

      if (is_null($strReturnPath)){
         redirect_PeopleDirectory($strBizLetter);
      }elseif ($strReturnPath=='importLog'){
         redirect_ImportLog($lReturnID);
      }
   }



}

