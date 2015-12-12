<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function new_gift(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('dataEntryGifts')) return('');

      $displayData = array();
      $displayData['js']      = '';

      $this->load->library('js_build/ajax_support');
      $this->load->helper ('dl_util/web_layout');

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('ddlNames',  'Donor',     'trim|callback_checkDDLSel');


		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;

            //-------------------------------
            // people/biz ajax interface
            //-------------------------------
         $clsAjax = new ajax_support;
         $displayData['js'] .= $clsAjax->showCreateXmlHTTPObject();
         $displayData['js'] .= $clsAjax->peopleBizNames('showResult', 'selNames');

         $displayData['js'] .= $clsAjax->strPopulateTextFromDDL('selNames', 'donorName');

         $this->load->library('generic_form');

         if (validation_errors()==''){
         }else {
            setOnFormError($displayData);
         }

         $displayData['pageTitle']      = 'Donations | Add New';
         $displayData['mainTemplate']   = array('donations/new_gift_sel_donor_view');

         $displayData['title']          = CS_PROGNAME.' | Donations';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $lDonorID = (integer)$_POST['ddlNames'];
         redirect('donations/add_edit/addEditGift/0/'.$lDonorID);
      }
   }

   function checkDDLSel($strValue){
      if (($strValue.'' == '' ) || ((int)$strValue <= 0)){
         $this->form_validation->set_message('checkDDLSel',
                   'Please enter the first few letters of the donor\'s last name (or business name), '
                  .'then select the entry from the drop-down list.');
         return(false);
      }else {
         return(true);
      }
   }

   function addEditGift($lGiftID, $lFID=0){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gclsChapterACO, $gbDateFormatUS;

      if (!bTestForURLHack('dataEntryGifts')) return('');
      $this->load->helper('dl_util/verify_id');
      if ($lGiftID.'' != '0') verifyID($this, $lGiftID, 'donation ID');
      if ($lFID.''    != '0') verifyID($this, $lFID,    'people/business ID');

      $displayData = array();
      $displayData['lGiftID'] = $lGiftID = (integer)$lGiftID;
      $displayData['lFID']    = $lFID    = (integer)$lFID;
      $displayData['js']      = '';

      $displayData['bNew']    = $bNew = $lGiftID <= 0;
      if ($bNew){
         if (!bTestForURLHack('dataEntryGifts')) return;
      }else {
         if (!bTestForURLHack('editGifts')) return;
      }

         // load models
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('js_build/ajax_support');
      $this->load->helper ('dl_util/web_layout');
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper ('dl_util/time_date');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $this->load->model  ('util/mlist_generic',     'clsList');
      $this->load->model  ('people/mpeople',         'clsPeople');
      $this->load->model  ('biz/mbiz',               'clsBiz');
      $this->load->model  ('donations/maccts_camps', 'clsAC');
      $this->load->model  ('donations/mdonations',   'clsGift');

      $this->clsGift->loadGiftViaGID($lGiftID);
      $gifts = &$this->clsGift->gifts[0];

      if ($bNew){
         $bPeople                 = !$this->clsPeople->bBizRec($lFID);
         $gifts->ga_lKeyID        = -1;
         $gifts->gi_dteDonation   = $gdteNow;
//         $gifts->lACOID           = $gclsChapterACO->lKeyID;
         $gifts->gi_curAmnt       = 0.0;

         if ($bPeople){
            $this->clsPeople->loadPeopleViaPIDs($lFID, false, false);
            $gifts->lACOID = $this->clsPeople->people[0]->lACO;
         }else {
            $this->clsBiz->loadBizRecsViaBID($lFID);
            $gifts->lACOID = $this->clsBiz->bizRecs[0]->lACO;
         }
      }else {
         $lFID    =  $this->clsGift->gifts[0]->gi_lForeignID;
         $bPeople = !$this->clsGift->gifts[0]->pe_bBiz;
      }

      $this->clsAC->loadAccounts(false, false, null);

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtAmount',  'Donation Amount',     'trim|required|callback_stripCommas|numeric');
      $this->form_validation->set_rules('rdoACO',     'Accounting Country',  'trim|required');
		$this->form_validation->set_rules('ddlAccount', 'Account',             'trim|required|callback_giftVerifyAccountValid');
		$this->form_validation->set_rules('ddlCamps',   'Campaign',            'trim|callback_giftVerifyCampValid');
		$this->form_validation->set_rules('ddlInKind',  'In-Kind',             'trim');
		$this->form_validation->set_rules('ddlPayType', 'Payment Type',        'trim|callback_giftVerifyPayType');
		$this->form_validation->set_rules('ddlGiftCat', 'Gift Category',       'trim');
		$this->form_validation->set_rules('ddlAttrib',  'Attributed To',       'trim');
		$this->form_validation->set_rules('txtCheck',   'Check #',             'trim');
      $this->form_validation->set_rules('txtDDate',   'Donation date',       'trim|required|callback_giftVerifyDDateValid');
      $this->form_validation->set_rules('txtNotes',   'Notes',               'trim');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $lSponID = $this->clsGift->gifts[0]->gi_lSponsorID;
         $displayData['bSponPayment'] = $bSponPayment = !is_null($lSponID);

            //-------------------------------
            // Acct/Camp and ajax interface
            //-------------------------------
         if ($bSponPayment){
            redirect('sponsors/payments/addEditPayment/'.$lSponID.'/'.$lFID.'/'.$lGiftID);
         }else {
            $clsAjax = new ajax_support;
            $displayData['js'] .= $clsAjax->showCreateXmlHTTPObject();
            $displayData['js'] .= $clsAjax->showCampaignLoadViaAcctID();
         }

         $this->load->library('generic_form');

         $displayData['js'] .= strDatePicker('datepickerFuture', true);

         $displayData['formData']->strStaffCFName = $gifts->strStaffCFName;
         $displayData['formData']->strStaffCLName = $gifts->strStaffCLName;
         $displayData['formData']->dteOrigin      = $gifts->dteOrigin;
         $displayData['formData']->strStaffLFName = $gifts->strStaffLFName;
         $displayData['formData']->strStaffLLName = $gifts->strStaffLLName;
         $displayData['formData']->dteLastUpdate  = $gifts->dteLastUpdate;

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $this->clsAC->loadCampaigns(false, true, $gifts->ga_lKeyID, false, null);
            $displayData['formData']->txtAmount     = number_format($gifts->gi_curAmnt, 2);
            $displayData['formData']->strACORadio   = $this->clsACO->strACO_Radios ($gifts->lACOID, 'rdoACO');
            if (!$bSponPayment){
               $displayData['formData']->strDDLAccts   = $this->clsAC->strDDLAccts    ($gifts->ga_lKeyID, true, true);
               $displayData['formData']->strDDLCamps   = $this->clsAC->strDDLCampaigns($gifts->gc_lKeyID, false);
            }
            $displayData['formData']->strNotes      = htmlspecialchars($gifts->strNotes);
            $displayData['formData']->strCheckNum   = htmlspecialchars($gifts->gi_strCheckNum);
            if (is_null($gifts->mdteDonation)){
               $displayData['formData']->txtDDate = '';
            }else {
               $displayData['formData']->txtDDate = strNumericDateViaMysqlDate($gifts->mdteDonation, $gbDateFormatUS);
            }

            $this->clsList->strBlankDDLName = 'n/a';
            $this->clsList->enumListType = CENUM_LISTTYPE_INKIND;
            $displayData['formData']->strDDLGIK         = $this->clsList->strLoadListDDL('ddlInKind',  true, $gifts->gi_lGIK_ID);
            $this->clsList->strBlankDDLName = '&nbsp;';
            $this->clsList->enumListType = CENUM_LISTTYPE_GIFTPAYTYPE;
            $displayData['formData']->strDDLPayType     = $this->clsList->strLoadListDDL('ddlPayType', true, $gifts->gi_lPaymentType);
            $this->clsList->enumListType = CENUM_LISTTYPE_MAJORGIFTCAT;
            $displayData['formData']->strDDLMajGiftType = $this->clsList->strLoadListDDL('ddlGiftCat', true, $gifts->gi_lMajorGiftCat);
            $this->clsList->enumListType = CENUM_LISTTYPE_ATTRIB;
            $displayData['formData']->strDDLAttrib      = $this->clsList->strLoadListDDL('ddlAttrib',  true, $gifts->gi_lAttributedTo);

         }else {
            setOnFormError($displayData);
            $this->clsAC->loadCampaigns(false, true, set_value('ddlAccount'), false, null);
            $displayData['formData']->txtDDate      = set_value('txtDDate');
            $displayData['formData']->txtAmount     = set_value('txtAmount');
            $displayData['formData']->strACORadio   = $this->clsACO->strACO_Radios (set_value('rdoACO'), 'rdoACO');
            if (!$bSponPayment){
               $displayData['formData']->strDDLAccts   = $this->clsAC->strDDLAccts    (set_value('ddlAccount'), true, true);
               $displayData['formData']->strDDLCamps   = $this->clsAC->strDDLCampaigns(set_value('ddlCamps'), false);
            }
            $displayData['formData']->strNotes      = set_value('txtNotes');
            $displayData['formData']->strCheckNum   = set_value('txtCheck');

            $this->clsList->strBlankDDLName = 'n/a';
            $this->clsList->enumListType = CENUM_LISTTYPE_INKIND;
            $displayData['formData']->strDDLGIK         = $this->clsList->strLoadListDDL('ddlInKind',  true, set_value('ddlInKind'));
            $this->clsList->strBlankDDLName = '&nbsp;';
            $this->clsList->enumListType = CENUM_LISTTYPE_GIFTPAYTYPE;
            $displayData['formData']->strDDLPayType     = $this->clsList->strLoadListDDL('ddlPayType', true, set_value('ddlPayType'));
            $this->clsList->enumListType = CENUM_LISTTYPE_MAJORGIFTCAT;
            $displayData['formData']->strDDLMajGiftType = $this->clsList->strLoadListDDL('ddlGiftCat', true, set_value('ddlGiftCat'));
            $this->clsList->enumListType = CENUM_LISTTYPE_ATTRIB;
            $displayData['formData']->strDDLAttrib      = $this->clsList->strLoadListDDL('ddlAttrib',  true, set_value('ddlAttrib'));
         }
         $displayData['gift'] = &$gifts;

            //--------------------------
            // breadcrumbs
            //--------------------------
         if ($bPeople){
            $displayData['pageTitle']   = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                                   .' | '.anchor('people/people_record/view/'.$lFID, 'Record', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').' Donation Record';
            $displayData['mainTemplate']   = array('donations/gift_add_edit_view');
            $this->clsPeople->loadPeopleViaPIDs($lFID, false, false);
            $displayData['contextSummary'] = $this->clsPeople->peopleHTMLSummary(0);
         }else {
            $displayData['pageTitle']   = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                                   .' | '.anchor('biz/biz_record/view/'.$lFID, 'Record', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').' Donation Record';
            $displayData['mainTemplate']   = array('donations/gift_add_edit_view');
            $this->clsBiz->loadBizRecsViaBID($lFID);
            $displayData['contextSummary'] = $this->clsBiz->strBizHTMLSummary(0);
         }

         $displayData['title']          = CS_PROGNAME.' | Donations';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->load->model('personalization/muser_fields',        'clsUF');
         $this->load->model('personalization/muser_fields_create', 'clsUFC');
         $this->load->model('admin/mpermissions',                  'perms');
         $this->load->helper('dl_util/util_db');

         $gifts->gi_curAmnt       = (float)trim($_POST['txtAmount']);
         $gifts->gc_lKeyID        = trim((integer)$_POST['ddlCamps']);
         $gifts->gi_lSponsorID    = null;
         $gifts->lACOID           = (integer)trim($_POST['rdoACO']);
         $gifts->strNotes         = trim($_POST['txtNotes']);
         $gifts->gi_strCheckNum   = trim($_POST['txtCheck']);
         $gifts->gi_lAttributedTo = (integer)trim($_POST['ddlAttrib']);
         $gifts->gi_lGIK_ID       = (integer)trim($_POST['ddlInKind']);
         $gifts->gi_lPaymentType  = (integer)trim($_POST['ddlPayType']);
         $gifts->gi_lMajorGiftCat = (integer)trim($_POST['ddlGiftCat']);
         $gifts->gi_lForeignID    = $lFID;

         if ($gifts->gi_lAttributedTo <= 0) $gifts->gi_lAttributedTo = null;
         if ($gifts->gi_lGIK_ID       <= 0) $gifts->gi_lGIK_ID       = null;
         $gifts->gi_bGIK = !is_null($gifts->gi_lGIK_ID);

         $strDate   = trim($_POST['txtDDate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $gifts->mdteDonation = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);

            //------------------------------------
            // update db tables and return
            //------------------------------------
         if ($bNew){
            $lGiftID = $this->clsGift->lAddNewGiftRecord();
            $this->session->set_flashdata('msg', 'Gift added');
         }else {
            $this->clsGift->updateGiftRecord();
            $this->session->set_flashdata('msg', 'Gift record updated');
         }
         redirect('donations/gift_record/view/'.$lGiftID);
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
   function giftVerifyDDateValid($strDate){
      return(bValidVerifyDate($strDate));
   }

   function giftVerifyAccountValid($lAcctID){
      return(((integer)$lAcctID) > 0);
   }

   function giftVerifyCampValid($lCampID){
      return(((integer)$lCampID) > 0);
   }

   function giftVerifyPayType($lPayType){
      return(((integer)$lPayType) > 0);
   }

   function stripCommas(&$strAmount){
      $strAmount = str_replace (',', '', $strAmount);
      return(true);
   }

   function setAck($lGiftID, $strSet){
      if (!bTestForURLHack('editGifts')) return;
      $this->load->helper('dl_util/verify_id');
      if ($lGiftID.'' != '0') verifyID($this, $lGiftID, 'donation ID');

      $bSet = $strSet == 'set';
      $this->load->model  ('donations/mdonations',   'clsGift');
      $this->clsGift->setGiftAck($lGiftID, $bSet);

      $this->session->set_flashdata('msg', 'Gift marked as '.($bSet ? 'acknowledged.' : 'unacknowledged.'));
      redirect('donations/gift_record/view/'.$lGiftID);
   }


}
