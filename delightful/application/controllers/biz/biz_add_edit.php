<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class biz_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }


   function addEditBiz($lBID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterACO;
      global $gstrDuplicateWarning;

      $gstrDuplicateWarning = '';

      $this->load->helper('dl_util/verify_id');
      if ($lBID.'' !='0') verifyID($this, $lBID, 'business ID');

      $displayData = array();
      $displayData['lBID'] = $lBID = (integer)$lBID;

      $displayData['bNew']    = $bNew = $lBID <= 0;
      if ($bNew){
         if (!bTestForURLHack('dataEntryPeopleBizVol')) return;
      }else {
         if (!bTestForURLHack('editPeopleBizVol')) return;
      }

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('admin/madmin_aco', 'clsACO');
      $this->load->model ('biz/mbiz',         'clsBiz');
//      $this->load->helper('dl_util/email_web');
      $this->load->helper('dl_util/web_layout');

      $this->clsBiz->loadBizRecsViaBID($lBID);
      $biz = &$this->clsBiz->bizRecs[0];
      if ($bNew){
         $this->load->model ('util/mdup_checker',  'cDupChecker');
      }

         //-----------------------------
         // validation rules
         //-----------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtBizName',  'Name of Business/Organization', 'trim|required');

		$this->form_validation->set_rules('txtAddr1',   'Address Line 1', 'trim');
		$this->form_validation->set_rules('txtAddr2',   'Address Line 2', 'trim');
		$this->form_validation->set_rules('txtCity',    'City',           'trim');
		$this->form_validation->set_rules('txtState',   'State',          'trim');
		$this->form_validation->set_rules('txtZip',     'Zip',            'trim');
		$this->form_validation->set_rules('txtCountry', 'Country',        'trim');

		$this->form_validation->set_rules('txtEmail',   'Email', 'trim|valid_email');
		$this->form_validation->set_rules('txtPhone',   'Phone', 'trim');
		$this->form_validation->set_rules('txtCell',    'Cell',  'trim');
		$this->form_validation->set_rules('txtFax',     'Fax',  'trim');
		$this->form_validation->set_rules('txtWebSite', 'Web Site',  'trim');
      $this->form_validation->set_rules('txtNotes',   'Notes', 'trim');

		$this->form_validation->set_rules('rdoACO',     'Accounting Country', 'trim|required');
		$this->form_validation->set_rules('ddlBizCat',  'Business Category',  'trim|required');

		$this->form_validation->set_rules('txtBizName', 'Name of Business/Organization', 'trim|required');
      $this->form_validation->set_rules('ddlAttrib',  'Attributed to');

         // test for duplicate biz
      if ($bNew){
         $this->form_validation->set_rules('hiddenTestDup', 'dummy',  'callback_verifyNoDups');
         $displayData['bHiddenNewTestDup'] = true;
      }

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');
         $this->load->model('util/mlist_generic', 'clsList');
         $displayData['strDuplicateWarning'] = $gstrDuplicateWarning;

         $this->clsList->enumListType = 'bizCat';
         $displayData['bizCatCnt'] = $bizCatCnt = $this->clsList->lListCnt();
         $clsAttrib = new mlist_generic;
         $clsAttrib->enumListType = 'attrib';

         $displayData['biz'] = &$biz;

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($bNew){
               $displayData['formData']->txtBizName =

               $displayData['formData']->txtAddr1   =
               $displayData['formData']->txtAddr2   =
               $displayData['formData']->txtCity    =
               $displayData['formData']->txtState   =
               $displayData['formData']->txtZip     =
               $displayData['formData']->txtCountry =

               $displayData['formData']->txtNotes   =
               $displayData['formData']->txtEmail   =
               $displayData['formData']->txtPhone   =
               $displayData['formData']->txtCell    =
               $displayData['formData']->txtFax     =
               $displayData['formData']->txtWebSite = '';

               $displayData['formData']->rdoACO = $this->clsACO->strACO_Radios($gclsChapterACO->lKeyID, 'rdoACO');

               if ($bizCatCnt > 0){
                  $displayData['formData']->strBizList = $this->clsList->strLoadListDDL('ddlBizCat', true, -1);
               }
               $displayData['strAttribDDL']          = $clsAttrib->strLoadListDDL('ddlAttrib', true, -1);
            }else {
               $displayData['formData']->txtBizName = htmlspecialchars($biz->strBizName);

               $displayData['formData']->txtAddr1   = htmlspecialchars($biz->strAddr1)  ;
               $displayData['formData']->txtAddr2   = htmlspecialchars($biz->strAddr2)  ;
               $displayData['formData']->txtCity    = htmlspecialchars($biz->strCity)   ;
               $displayData['formData']->txtState   = htmlspecialchars($biz->strState)  ;
               $displayData['formData']->txtZip     = htmlspecialchars($biz->strCountry);
               $displayData['formData']->txtCountry = htmlspecialchars($biz->strZip)    ;

               $displayData['formData']->txtNotes   = htmlspecialchars($biz->strNotes);
               $displayData['formData']->txtEmail   = htmlspecialchars($biz->strEmail);
               $displayData['formData']->txtPhone   = htmlspecialchars($biz->strPhone);
               $displayData['formData']->txtCell    = htmlspecialchars($biz->strCell);
               $displayData['formData']->txtFax     = htmlspecialchars($biz->strFax);
               $displayData['formData']->txtWebSite = htmlspecialchars($biz->strWebSite);

               $displayData['formData']->rdoACO = $this->clsACO->strACO_Radios($biz->lACO, 'rdoACO');

               if ($bizCatCnt > 0){
                  $displayData['formData']->strBizList = $this->clsList->strLoadListDDL('ddlBizCat', true, $biz->lIndustryID);
               }
               $displayData['strAttribDDL']     = $clsAttrib->strLoadListDDL('ddlAttrib', true, $biz->lAttributedTo);
            }
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtBizName = set_value('txtBizName');

            $displayData['formData']->txtAddr1   = set_value('txtAddr1');
            $displayData['formData']->txtAddr2   = set_value('txtAddr2');
            $displayData['formData']->txtCity    = set_value('txtCity');
            $displayData['formData']->txtState   = set_value('txtState');
            $displayData['formData']->txtZip     = set_value('txtZip');
            $displayData['formData']->txtCountry = set_value('txtCountry');

            $displayData['formData']->txtNotes   = set_value('txtNotes');
            $displayData['formData']->txtEmail   = set_value('txtEmail');
            $displayData['formData']->txtPhone   = set_value('txtPhone');
            $displayData['formData']->txtCell    = set_value('txtCell');
            $displayData['formData']->txtFax     = set_value('txtFax');
            $displayData['formData']->txtWebSite = set_value('txtWebSite');

            $displayData['formData']->rdoACO = $this->clsACO->strACO_Radios((integer)set_value('rdoACO'), 'rdoACO');
            $displayData['strAttribDDL']     = $clsAttrib->strLoadListDDL('ddlAttrib', true, set_value('ddlAttrib'));
            if ($bizCatCnt > 0){
               $displayData['formData']->strBizList = $this->clsList->strLoadListDDL('ddlBizCat', true,
                                      (integer)set_value('ddlBizCat'));
            }
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/biz', 'Businesses/Organizations',   'class="breadcrumb"')
                                   .' | '.anchor('biz/biz_record/view/'.$lBID, 'Business Record', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').'  Record';

         $displayData['title']          = CS_PROGNAME.' | Businesses';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'biz/biz_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->load->model ('personalization/muser_fields',        'clsUF');
         $this->load->model ('personalization/muser_fields_create', 'clsUFC');
         $this->load->model ('admin/mpermissions',                  'perms');
         $this->load->helper('dl_util/util_db');

         $biz = new stdClass;
         $biz->strBizName  = trim($_POST['txtBizName']);

         $biz->strAddr1    = trim($_POST['txtAddr1']);
         $biz->strAddr2    = trim($_POST['txtAddr2']);
         $biz->strCity     = trim($_POST['txtCity']);
         $biz->strState    = trim($_POST['txtState']);
         $biz->strCountry  = trim($_POST['txtCountry']);
         $biz->strZip      = trim($_POST['txtZip']);

         $biz->strNotes    = trim($_POST['txtNotes']);
         $biz->strEmail    = trim($_POST['txtEmail']);
         $biz->strPhone    = trim($_POST['txtPhone']);
         $biz->strCell     = trim($_POST['txtCell']);
         $biz->strFax      = trim($_POST['txtFax']);
         $biz->strWebSite  = trim($_POST['txtWebSite']);

         $biz->lACO        = (integer)$_POST['rdoACO'];
         $biz->lIndustryID = (integer)$_POST['ddlBizCat'];

         $lAttrib = (integer)$_REQUEST['ddlAttrib'];
         if ($lAttrib <= 0){
            $biz->lAttributedTo = null;
         }else {
            $biz->lAttributedTo = $lAttrib;
         }

         if ($bNew){
            $lBID = $this->clsBiz->lCreateNewBizRec();
            $this->session->set_flashdata('msg', 'The business/organization record was added');
         }else {
            $this->clsBiz->updateBizRec($lBID);
            $this->session->set_flashdata('msg', 'The business/organization record was updated');
         }
         redirect_Biz($lBID);
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

      $strBizName = trim($_POST['txtBizName']);
      $this->cDupChecker->findSimilarBizNames($strBizName, $lNumMatches, $matches);

      if ($lNumMatches > 0){
         $gstrDuplicateWarning = $this->cDupChecker->strPeopleBizDupWarning(
                        true, 'chkImCoolWithDups', $lNumMatches, $matches);
         return(false);
      }else {
         return(true);
      }
   }

   function remove($lBizID, $strReturnPath=null, $lReturnID=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('editPeopleBizVol')) return;

      $lBizID = (integer)$lBizID;

      $this->load->model ('people/mpeople',               'clsPeople');
      $this->load->model ('biz/mbiz',                     'clsBiz');
      $this->load->model ('donations/mdonations',         'clsGifts');
      $this->load->model ('util/mrecycle_bin',            'clsRecycle');
      $this->load->model ('sponsorship/msponsorship',     'clsSpon');
      $this->load->model ('personalization/muser_fields', 'clsUF');
      $this->load->model ('groups/mgroups',               'groups');
      $this->load->helper('groups/groups');

      $this->clsBiz->lBID = $this->clsPeople->lPeopleID = $lBizID;
      $this->clsBiz->bizInfoLight();
      $this->clsPeople->strSafeName = $this->clsBiz->strSafeName;

      $this->clsPeople->removePersonBiz(true);

      $this->session->set_flashdata('msg', 'The record for '.$this->clsBiz->strSafeName.' (bizID '.str_pad($lBizID, 5, '0', STR_PAD_LEFT).') was removed.');
      $strBizLetter = strtoupper(substr($this->clsBiz->strSafeName, 0, 1));
      if (is_null($strReturnPath)){
         redirect_BizDirectory($strBizLetter);
      }elseif ($strReturnPath=='importLog'){
         redirect_ImportLog($lReturnID);
      }
   }
}
