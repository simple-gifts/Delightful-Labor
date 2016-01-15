<?php
//---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012-2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
/*---------------------------------------------------------------------
      $this->load->helper('user/user_acct');
---------------------------------------------------------------------*/

   function commonAcctView($lUserID, $bAsAdmin, &$displayData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $gbAdmin;

      $local =& get_instance();
      $local->load->helper('dl_util/time_date');

      if ($bAsAdmin){
         if (!bTestForURLHack('adminOnly')) return;
      }else {
         if ($glUserID != $lUserID) {
            bTestForURLHack('forceFail');
            return;
         }
      }

      $local->load->helper('dl_util/verify_id');
      verifyID($local, $lUserID, 'user ID');

      $displayData = array();
      $displayData['lUserID'] = (integer)$lUserID;
      $displayData['bAsAdmin'] = $bAsAdmin;
      $displayData['js'] = '';

      $params = array('enumStyle' => 'terse');
      $local->load->library('generic_rpt', $params, 'generic_rpt');
      $displayData['clsRpt'] = $local->generic_rpt;

      $local->load->model('admin/muser_accts', 'clsUsers');
      $local->clsUsers->us_lKeyID = $lUserID;
      $local->clsUsers->loadUserRec();
      $userRec = $local->clsUsers->userRec;

      $displayData['userRec'] = $local->clsUsers->userRec;

         //-----------------------------
         // models and helpers
         //-----------------------------
      $local->load->library('generic_form');
      $local->load->library('util/dl_date_time', '',                'clsDateTime');
      $local->load->model  ('people/mpeople',                       'clsPeople');
      $local->load->model  ('donations/mdonations',                 'clsGifts');
      $local->load->model  ('admin/madmin_aco',                     'clsACO');
      $local->load->model  ('clients/mclients',                     'clsClients');
      $local->load->model  ('biz/mbiz',                             'clsBiz');
      $local->load->model  ('sponsorship/msponsorship',             'clsSpon');
      $local->load->model  ('groups/mgroups',                       'groups');
      $local->load->model  ('personalization/muser_fields',         'clsUF');
      $local->load->model  ('personalization/muser_fields_display', 'clsUFD');
      $local->load->model  ('admin/mpermissions',                   'perms');
      $local->load->helper ('groups/groups');
      $local->load->helper ('personalization/ptable');

      $local->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         // user permissions
      $local->perms->loadUserAcctInfo($glUserID, $acctAccess);


      if ($bAsAdmin){
            //-------------------------------
            // images and documents
            //-------------------------------
         $local->load->helper ('img_docs/image_doc');
         $local->load->helper ('img_docs/img_doc_tags');
         $local->load->model  ('img_docs/mimage_doc',               'clsImgDoc');
         $local->load->model  ('img_docs/mimg_doc_tags',            'cidTags');
         loadImgDocRecView($displayData, CENUM_CONTEXT_STAFF, $lUserID);

            // reminders
//         $local->load->model('reminders/mreminders', 'clsRem');
//         $displayData['clsRem'] = $local->clsRem;
      }

      $local->load->helper('dl_util/web_layout');
      $local->load->helper('dl_util/record_view');
      $local->load->helper('img_docs/link_img_docs');

         //-------------------------------
         // user groups
         //-------------------------------
      if ($userRec->bStandardUser){
         $local->groups->groupMembershipViaFID(CENUM_CONTEXT_USER, $lUserID);
         $displayData['inGroups']            = $local->groups->arrMemberInGroups;
         $displayData['lCntGroupMembership'] = $local->groups->lNumMemInGroups;
         $displayData['lNumGroups']          = $local->groups->lCntActiveGroupsViaType(CENUM_CONTEXT_USER);
         $local->groups->loadActiveGroupsViaType(CENUM_CONTEXT_USER, 'groupName', $local->groups->strMemListIDs, false, null);
         $displayData['groupList']           = $local->groups->arrGroupList;
      }

         //-------------------------------
         // personalized tables
         //-------------------------------
      if ($userRec->bStandardUser || $gbAdmin){
         $displayData['strPT'] = strPTableDisplay(CENUM_CONTEXT_USER, $lUserID,
                                     $local->clsUFD, $local->perms, $acctAccess,
                                     $displayData['strFormDataEntryAlert'],
                                     $displayData['lNumPTablesAvail']);
      }

         //-------------------------------
         // staff groups
         //-------------------------------
      if ($userRec->bStandardUser || $gbAdmin){
         $local->groups->groupMembershipViaFID(CENUM_CONTEXT_STAFF, $lUserID);
         $displayData['inGroupsStaff']            = $local->groups->arrMemberInGroups;
         $displayData['lCntGroupMembershipStaff'] = $local->groups->lNumMemInGroups;
         $displayData['lNumGroupsStaff']          = $local->groups->lCntActiveGroupsViaType(CENUM_CONTEXT_STAFF);
         $local->groups->loadActiveGroupsViaType(CENUM_CONTEXT_STAFF, 'groupName', $local->groups->strMemListIDs, false, null);
         $displayData['groupListStaff']           = $local->groups->arrGroupList;
      }

      if ($bAsAdmin){
         $strLetter = strtoupper(substr($userRec->us_strLastName, 0, 1));
         if ($strLetter < 'A' || $strLetter > 'Z') $strLetter = '%23';
         $displayData['title']          = CS_PROGNAME.' | User Accounts | View Account';
         $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                   .' | '.anchor('admin/accts/userAcctDir/'.$strLetter, 'User Accounts', 'class="breadcrumb"')
                                   .' | View Account';
      }else{
         $displayData['title']          = CS_PROGNAME.' | More | Account Preferences';
         $displayData['pageTitle']      = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                                   .' | Your Account';
      }
      $displayData['nav']            = $local->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = 'admin/user_rec_view';
      $local->load->vars($displayData);
      $local->load->view('template');
   }

   function commonAcctEdit($lUserID, $bAsAdmin){
   /*---------------------------------------------------------------------
      another way... Note that get_instance is a CI function, defined in
      system/core/CodeIgniter.php

      from http://stackoverflow.com/questions/4740430/explain-ci-get-instance

      $CI =& get_instance(); // use get_instance, it is less prone to failure in this context.
   ---------------------------------------------------------------------*/
      global $glUserID;

      $bSelfSame = $glUserID == $lUserID;
      if ($bAsAdmin){
         if (!bTestForURLHack('adminOnly')) return;
      }else {
         if (!$bSelfSame) {
            bTestForURLHack('forceFail');
            return;
         }
      }

      $local =& get_instance();
      $local->load->helper('dl_util/verify_id');
      verifyIDsViaType($local, CENUM_CONTEXT_USER, $lUserID, true);

      $local->load->model('admin/mpermissions', 'perms');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['userRec'] = new stdClass;

      $displayData['lUserID']  = $lUserID = (integer)$lUserID;
      $displayData['bNew']     = $bNew    = $lUserID <= 0;
      $displayData['bAsAdmin'] = $bAsAdmin;

      $local->load->helper('js/hide_show_div');
      $displayData['js'] .= insertHideSetDiv();

      $local->load->model('admin/muser_accts', 'clsUser');
      $local->load->helper('dl_util/web_layout');

         //-----------------------------
         // load account record
         //-----------------------------
      $local->clsUser->loadSingleUserRecord($lUserID);
      $userRec = $local->clsUser->userRec[0];

         // validation rules
      $local->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $local->form_validation->set_rules('txtFName',  'User\'s First Name', 'trim|required');
      $local->form_validation->set_rules('txtLName',  'User\'s Last Name',  'trim|required');
      $local->form_validation->set_rules('txtUN',     'User Name',          'trim|required|callback_verifyUniqueUserID['.$lUserID.']');
      if ($bAsAdmin){
         $local->form_validation->set_rules('txtPWord1', 'Password',         'trim|callback_verifyPWordRequired['.$lUserID.']');
         $local->form_validation->set_rules('txtPWord2', 'Password (again)', 'trim|callback_verifyPWordsMatch');
         $local->form_validation->set_rules('chkAdmin');
      }

      $local->form_validation->set_rules('rdoAcctType');
//      $local->form_validation->set_rules('rdoAcctType');
      $local->form_validation->set_rules('rdoDebug');

         // volunteer permissions
      $local->form_validation->set_rules('chkVolEditContactInfo');
      $local->form_validation->set_rules('chkVolVolPassReset');
      $local->form_validation->set_rules('chkVolViewGiftHistory');
      $local->form_validation->set_rules('chkVolEditJobSkills');
      $local->form_validation->set_rules('chkVolViewHrsHistory');
      $local->form_validation->set_rules('chkVolAddVolHours');
      $local->form_validation->set_rules('chkVolShiftSignup');
      $local->form_validation->set_rules('txtPID', 'PeopleID', 'trim|is_natural_no_zero|callback_verifyPID');

      $local->form_validation->set_rules('chkUserDataEntryPeople');
      $local->form_validation->set_rules('chkUserDataEntryGifts');
      $local->form_validation->set_rules('chkUserEditPeople');
      $local->form_validation->set_rules('chkUserEditGifts');
      $local->form_validation->set_rules('chkUserViewGiftHistory');
      $local->form_validation->set_rules('chkUserViewPeople');
      $local->form_validation->set_rules('chkUserViewReports');

      $local->form_validation->set_rules('chkUserAllowSponsorship');
      $local->form_validation->set_rules('chkUserAllowSponFinancial');
      $local->form_validation->set_rules('chkUserAllowClient');
      $local->form_validation->set_rules('chkUserAllowAuctions');
      $local->form_validation->set_rules('chkUserAllowGrants');
      $local->form_validation->set_rules('chkUserAllowInventory');
      $local->form_validation->set_rules('chkUserVolManager');

      $local->form_validation->set_rules('chkUserAllowExports');

      $local->form_validation->set_rules('rdoDateFormat', 'Date Format', 'required');
      $local->form_validation->set_rules('rdoMeasureFormat', 'Measurement Preference', 'required');
      $local->form_validation->set_rules('txtPhone');
      $local->form_validation->set_rules('txtCell');
      $local->form_validation->set_rules('txtEmail', 'User\'s Email', 'required|valid_email');
      $local->form_validation->set_rules('txtAddr1');
      $local->form_validation->set_rules('txtAddr2');
      $local->form_validation->set_rules('txtCity');
      $local->form_validation->set_rules('txtState');
      $local->form_validation->set_rules('txtCountry');
      $local->form_validation->set_rules('txtZip');

      if ($local->form_validation->run() == FALSE){
         if ($bNew){
            $strAnchorExtra = '';
         }else {
            $strAnchorExtra = ' | '.anchor('admin/accts/view/'.$lUserID, $userRec->strSafeName, 'class="breadcrumb"');
         }

         $displayData['title']        = CS_PROGNAME.' | User Accounts';
         if ($bAsAdmin){
            $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                    .' | '.anchor('admin/accts/userAcctDir/A', 'User Accounts', 'class="breadcrumb"')
                                    .$strAnchorExtra
                                    .' |  '.($bNew ? 'Add New Account' : 'Edit Account');
         }else {
            $displayData['pageTitle']    = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                                    .' | '.anchor('more/user_acct/view/'.$glUserID, 'Your Account', 'class="breadcrumb"');
         }
         $displayData['nav']          = $local->mnav_brain_jar->navData();

         $local->load->library('generic_form');

         $displayData['userRec']->strSafeName    = $userRec->strSafeName;
         $displayData['userRec']->lKeyID         = $userRec->us_lKeyID;

         if (validation_errors()==''){
            $displayData['userRec']->strFName             = htmlspecialchars($userRec->us_strFirstName);
            $displayData['userRec']->strLName             = htmlspecialchars($userRec->us_strLastName);
            $displayData['userRec']->strUserName          = htmlspecialchars($userRec->us_strUserName);
            $displayData['userRec']->enumDateFormat       = htmlspecialchars($userRec->us_enumDateFormat);
            $displayData['userRec']->enumMeasurePref      = htmlspecialchars($userRec->us_enumMeasurePref);

            $displayData['userRec']->bAdmin               = $userRec->us_bAdmin;
            $displayData['userRec']->bDebugger            = $userRec->us_bDebugger;
            $displayData['userRec']->bStandardUser        = $userRec->bStandardUser;
            $displayData['userRec']->bVolAccount          = $userRec->bVolAccount;

            $displayData['userRec']->bVolEditContact      = $userRec->bVolEditContact;
            $displayData['userRec']->bVolPassReset        = $userRec->bVolPassReset;
            $displayData['userRec']->bVolViewGiftHistory  = $userRec->bVolViewGiftHistory;
            $displayData['userRec']->bVolEditJobSkills    = $userRec->bVolEditJobSkills;
            $displayData['userRec']->bVolViewHrsHistory   = $userRec->bVolViewHrsHistory;
            $displayData['userRec']->bVolAddVolHours      = $userRec->bVolAddVolHours;
            $displayData['userRec']->bVolShiftSignup      = $userRec->bVolShiftSignup;
            $displayData['userRec']->txtPID               = $userRec->lPeopleID;

            $displayData['userRec']->bUserDataEntryPeople = $userRec->bUserDataEntryPeople;
            $displayData['userRec']->bUserDataEntryGifts  = $userRec->bUserDataEntryGifts;
            $displayData['userRec']->bUserEditPeople      = $userRec->bUserEditPeople;
            $displayData['userRec']->bUserEditGifts       = $userRec->bUserEditGifts;
            $displayData['userRec']->bUserViewPeople      = $userRec->bUserViewPeople;
            $displayData['userRec']->bUserViewGiftHistory = $userRec->bUserViewGiftHistory;
            $displayData['userRec']->bUserViewReports     = $userRec->bUserViewReports;
            $displayData['userRec']->bUserAllowExports    = $userRec->bUserAllowExports;

            $displayData['userRec']->bUserAllowSponsorship   = $userRec->bUserAllowSponsorship;
            $displayData['userRec']->bUserAllowSponFinancial = $userRec->bUserAllowSponFinancial;
            $displayData['userRec']->bUserAllowClient        = $userRec->bUserAllowClient;
            $displayData['userRec']->bUserAllowAuctions      = $userRec->bUserAllowAuctions;
            $displayData['userRec']->bUserAllowGrants        = $userRec->bUserAllowGrants;
            $displayData['userRec']->bUserAllowInventory     = $userRec->bUserAllowInventory;
            $displayData['userRec']->bUserVolManager         = $userRec->bUserVolManager;

            $displayData['userRec']->strPhone             = htmlspecialchars($userRec->us_strPhone);
            $displayData['userRec']->strCell              = htmlspecialchars($userRec->us_strCell);
            $displayData['userRec']->strEmail             = htmlspecialchars($userRec->us_strEmail);
            $displayData['userRec']->strAddr1             = htmlspecialchars($userRec->us_strAddr1);
            $displayData['userRec']->strAddr2             = htmlspecialchars($userRec->us_strAddr2);
            $displayData['userRec']->strCity              = htmlspecialchars($userRec->us_strCity);
            $displayData['userRec']->strState             = htmlspecialchars($userRec->us_strState);
            $displayData['userRec']->strCountry           = htmlspecialchars($userRec->us_strCountry);
            $displayData['userRec']->strZip               = htmlspecialchars($userRec->us_strZip);
         }else {
            setOnFormError($displayData);

            $displayData['userRec']->strFName        = set_value('txtFName');
            $displayData['userRec']->strLName        = set_value('txtLName');
            $displayData['userRec']->strUserName     = set_value('txtUN');
            $displayData['userRec']->enumDateFormat  = set_value('rdoDateFormat');
            $displayData['userRec']->enumMeasurePref = set_value('rdoMeasureFormat');

            $displayData['userRec']->bDebugger           = set_value('rdoDebug')=='true';
            $displayData['userRec']->bAdmin              = set_value('rdoAcctType')=='admin';
            $displayData['userRec']->bStandardUser       = set_value('rdoAcctType')=='user';
            $displayData['userRec']->bVolAccount         = set_value('rdoAcctType')=='vol';

            $displayData['userRec']->bVolEditContact     = set_value('chkVolEditContactInfo')=='true';
            $displayData['userRec']->bVolPassReset       = set_value('chkVolVolPassReset')=='true';
            $displayData['userRec']->bVolViewGiftHistory = set_value('chkVolViewGiftHistory')=='true';
            $displayData['userRec']->bVolEditJobSkills   = set_value('chkVolEditJobSkills')=='true';
            $displayData['userRec']->bVolViewHrsHistory  = set_value('chkVolViewHrsHistory')=='true';
            $displayData['userRec']->bVolAddVolHours     = set_value('chkVolAddVolHours')=='true';
            $displayData['userRec']->bVolShiftSignup     = set_value('chkVolShiftSignup')=='true';
            $displayData['userRec']->txtPID              = set_value('txtPID');

            $displayData['userRec']->bUserDataEntryPeople = set_value('chkUserDataEntryPeople')=='true';
            $displayData['userRec']->bUserDataEntryGifts  = set_value('chkUserDataEntryGifts' )=='true';
            $displayData['userRec']->bUserEditPeople      = set_value('chkUserEditPeople'     )=='true';
            $displayData['userRec']->bUserEditGifts       = set_value('chkUserEditGifts'      )=='true';
            $displayData['userRec']->bUserViewPeople      = set_value('chkUserViewPeople'     )=='true';
            $displayData['userRec']->bUserViewGiftHistory = set_value('chkUserViewGiftHistory')=='true';
            $displayData['userRec']->bUserViewReports     = set_value('chkUserViewReports'    )=='true';
            $displayData['userRec']->bUserAllowExports    = set_value('chkUserAllowExports'   )=='true';

            $displayData['userRec']->bUserAllowSponsorship   = set_value('chkUserAllowSponsorship'   )=='true';
            $displayData['userRec']->bUserAllowSponFinancial = set_value('chkUserAllowSponFinancial' )=='true';
            $displayData['userRec']->bUserAllowClient        = set_value('chkUserAllowClient'        )=='true';
            $displayData['userRec']->bUserAllowAuctions      = set_value('chkUserAllowAuctions'      )=='true';
            $displayData['userRec']->bUserAllowGrants        = set_value('chkUserAllowGrants'        )=='true';
            $displayData['userRec']->bUserAllowInventory     = set_value('chkUserAllowInventory'     )=='true';
            $displayData['userRec']->bUserVolManager         = set_value('chkUserVolManager'         )=='true';

            $displayData['userRec']->strPhone        = set_value('txtPhone');
            $displayData['userRec']->strCell         = set_value('txtCell');
            $displayData['userRec']->strEmail        = set_value('txtEmail');
            $displayData['userRec']->strAddr1        = set_value('txtAddr1');
            $displayData['userRec']->strAddr2        = set_value('txtAddr2');
            $displayData['userRec']->strCity         = set_value('txtCity');
            $displayData['userRec']->strState        = set_value('txtState');
            $displayData['userRec']->strCountry      = set_value('txtCountry');
            $displayData['userRec']->strZip          = set_value('txtZip');
         }

         $displayData['mainTemplate'] = 'admin/user_acct_add_edit_view';
         $local->load->vars($displayData);
         $local->load->view('template');

      }else {
         $userRec->us_strFirstName    = xss_clean(trim($_POST['txtFName']));
         $userRec->us_strLastName     = xss_clean(trim($_POST['txtLName']));
         $userRec->us_strUserName     = xss_clean(trim($_POST['txtUN']));
         $userRec->us_enumDateFormat  = xss_clean(trim($_POST['rdoDateFormat']));
         $userRec->us_enumMeasurePref = xss_clean(trim($_POST['rdoMeasureFormat']));

         if ($bAsAdmin){
            $userRec->us_bDebugger       = $_POST['rdoDebug']=='true';
            $userRec->us_bAdmin          = $_POST['rdoAcctType']=='admin';
            $userRec->bStandardUser      = $_POST['rdoAcctType']=='user';
            $userRec->bVolAccount        = $_POST['rdoAcctType']=='vol';

            $userRec->bVolEditContact       =
            $userRec->bVolPassReset         =
            $userRec->bVolViewGiftHistory   =
            $userRec->bVolEditJobSkills     =
            $userRec->bVolViewHrsHistory    =
            $userRec->bVolAddVolHours       =
            $userRec->bVolShiftSignup       = false;

            $userRec->bUserDataEntryPeople =
            $userRec->bUserDataEntryGifts  =
            $userRec->bUserEditPeople      =
            $userRec->bUserEditGifts       =
            $userRec->bUserViewPeople      =
            $userRec->bUserViewGiftHistory =
            $userRec->bUserViewReports     =

            $userRec->bUserAllowSponsorship   =
            $userRec->bUserAllowSponFinancial =
            $userRec->bUserAllowClient        =
            $userRec->bUserAllowAuctions      =
            $userRec->bUserAllowGrants        =
            $userRec->bUserAllowInventory     =
            $userRec->bUserVolManager         =

            $userRec->bUserAllowExports       = false;

            if ($userRec->bVolAccount){
               $userRec->bVolEditContact       = @$_POST['chkVolEditContactInfo'] == 'true';
               $userRec->bVolPassReset         = @$_POST['chkVolVolPassReset']    == 'true';
               $userRec->bVolViewGiftHistory   = @$_POST['chkVolViewGiftHistory'] == 'true';
               $userRec->bVolEditJobSkills     = @$_POST['chkVolEditJobSkills']   == 'true';
               $userRec->bVolViewHrsHistory    = @$_POST['chkVolViewHrsHistory']  == 'true';
               $userRec->bVolAddVolHours       = @$_POST['chkVolAddVolHours']     == 'true';
               $userRec->bVolShiftSignup       = @$_POST['chkVolShiftSignup']     == 'true';
               $userRec->lPeopleID             = (int)@$_POST['txtPID'];
            }elseif ($userRec->bStandardUser) {
               $userRec->bUserDataEntryPeople = @$_POST['chkUserDataEntryPeople']=='true';
               $userRec->bUserDataEntryGifts  = @$_POST['chkUserDataEntryGifts' ]=='true';
               $userRec->bUserEditPeople      = @$_POST['chkUserEditPeople'     ]=='true';
               $userRec->bUserEditGifts       = @$_POST['chkUserEditGifts'      ]=='true';
               $userRec->bUserViewPeople      = @$_POST['chkUserViewPeople'     ]=='true';
               $userRec->bUserViewGiftHistory = @$_POST['chkUserViewGiftHistory']=='true';
               $userRec->bUserViewReports     = @$_POST['chkUserViewReports'    ]=='true';
               $userRec->bUserAllowExports    = @$_POST['chkUserAllowExports'   ]=='true';

               $userRec->bUserAllowSponsorship   = @$_POST['chkUserAllowSponsorship']   == 'true';
               $userRec->bUserAllowSponFinancial = @$_POST['chkUserAllowSponFinancial'] == 'true';
               $userRec->bUserAllowClient        = @$_POST['chkUserAllowClient']        == 'true';
               $userRec->bUserAllowAuctions      = @$_POST['chkUserAllowAuctions']      == 'true';
               $userRec->bUserAllowGrants        = @$_POST['chkUserAllowGrants']        == 'true';
               $userRec->bUserAllowInventory     = @$_POST['chkUserAllowInventory']     == 'true';
               $userRec->bUserVolManager         = @$_POST['chkUserVolManager']         == 'true';
            }
         }

         $userRec->us_strPhone     = xss_clean(trim($_POST['txtPhone']));
         $userRec->us_strCell      = xss_clean(trim($_POST['txtCell']));
         $userRec->us_strEmail     = xss_clean(trim($_POST['txtEmail']));
         $userRec->us_strAddr1     = xss_clean(trim($_POST['txtAddr1']));
         $userRec->us_strAddr2     = xss_clean(trim($_POST['txtAddr2']));
         $userRec->us_strCity      = xss_clean(trim($_POST['txtCity']));
         $userRec->us_strState     = xss_clean(trim($_POST['txtState']));
         $userRec->us_strCountry   = xss_clean(trim($_POST['txtCountry']));
         $userRec->us_strZip       = xss_clean(trim($_POST['txtZip']));
         $userRec->us_bInactive    = 0;

         if ($bAsAdmin) {
            $userRec->us_strUserPWord = xss_clean(trim($_POST['txtPWord1']));
            $_SESSION[CS_NAMESPACE.'user']->bDebugger = $userRec->us_bDebugger;
         }else {
            $userRec->us_strUserPWord = '';
         }
         if (!$bAsAdmin || $bSelfSame){
            $_SESSION[CS_NAMESPACE.'user']->enumDateFormat  = $userRec->us_enumDateFormat;
            $_SESSION[CS_NAMESPACE.'user']->enumMeasurePref = $userRec->us_enumMeasurePref;
            $_SESSION[CS_NAMESPACE.'user']->strFirstName    = $userRec->us_strFirstName;
            $_SESSION[CS_NAMESPACE.'user']->strLastName     = $userRec->us_strLastName;
            $_SESSION[CS_NAMESPACE.'user']->strUserName     = $userRec->us_strUserName;
            $_SESSION[CS_NAMESPACE.'user']->strSafeName     = htmlspecialchars($userRec->us_strFirstName.' '.$userRec->us_strLastName);
         }

         if ($bNew){
            $lUserID = $local->clsUser->addUserAccount();
            $local->session->set_flashdata('msg', 'The new user was added');
         }else {
            $userRec->us_lKeyID   = $lUserID;
            $local->clsUser->updateUserAccount();
            $local->session->set_flashdata('msg', 'The user account was updated');
         }
         if ($bAsAdmin) {
            redirect('admin/accts/view/'.$lUserID);
         }else {
            redirect_userAcct();
         }
      }
   }



