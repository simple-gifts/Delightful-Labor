<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pledge_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEdit($lPledgeID, $lFID='0'){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gclsChapterACO, $gbDateFormatUS;

      $this->load->helper('dl_util/verify_id');
      if ($lPledgeID.'' != '0') verifyID($this, $lPledgeID, 'pledge ID');
      if ($lFID.''      != '0') verifyID($this, $lFID,      'people/business ID');

      $displayData = array();
      $displayData['lPledgeID'] = $lPledgeID = (integer)$lPledgeID;
      $displayData['lFID']      = $lFID    = (integer)$lFID;
      $displayData['js']        = '';

      $displayData['bNew']    = $bNew = $lPledgeID <= 0;
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
//      $this->load->model  ('donations/mdonations',   'clsGift');
      $this->load->model  ('donations/mpledges', 'clsPledges');


      $this->clsPledges->loadPledgeViaPledgeID($lPledgeID);
      $pledge = &$this->clsPledges->pledges[0];

      if ($bNew){
         $bPeople                = !$this->clsPeople->bBizRec($lFID);
         $pledge->lAccountID     = -1;
         $pledge->dteStart       = $gdteNow;
         $pledge->curCommitment  = 0.0;

         if ($bPeople){
            $this->clsPeople->loadPeopleViaPIDs($lFID, false, false);
            $pledge->lACOID = $this->clsPeople->people[0]->lACO;
         }else {
            $this->clsBiz->loadBizRecsViaBID($lFID);
            $pledge->lACOID = $this->clsBiz->bizRecs[0]->lACO;
         }
      }else {
         $lFID    =  $pledge->lForeignID;
         $bPeople = !$pledge->bBiz;
         if ($bPeople){
            $this->clsPeople->loadPeopleViaPIDs($lFID, false, false);
         }else {
            $this->clsBiz->loadBizRecsViaBID($lFID);
         }
      }

      $this->clsAC->loadAccounts(false, false, null);

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtCommit',    'Commitment Amount',   'trim|required|callback_stripCommas|numeric|callback_pledgeVerifyCommitment');
		$this->form_validation->set_rules('txtNumPay',    'Number of Payments',  'trim|required|callback_stripCommas|numeric|greater_than[0]');
      $this->form_validation->set_rules('rdoACO',       'Accounting Country',  'trim|required');
		$this->form_validation->set_rules('ddlAccount',   'Account',             'trim|required|callback_pledgeVerifyAccountValid');
		$this->form_validation->set_rules('ddlCamps',     'Campaign',            'trim|callback_pledgeVerifyCampValid');
		$this->form_validation->set_rules('ddlAttrib',    'Attributed To',       'trim');
		$this->form_validation->set_rules('ddlFreq',      'Payment Frequency',   'trim|callback_pledgeFreqValid');
      $this->form_validation->set_rules('txtStartDate', 'Start date',          'trim|required|callback_pledgeVerifyDateValid');
      $this->form_validation->set_rules('txtNotes',     'Notes',               'trim');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;

            //-------------------------------
            // Acct/Camp and ajax interface
            //-------------------------------
         $clsAjax = new ajax_support;
         $displayData['js'] .= $clsAjax->showCreateXmlHTTPObject();
         $displayData['js'] .= $clsAjax->showCampaignLoadViaAcctID();

         $this->load->library('generic_form');

         $displayData['js'] .= strDatePicker('datepickerFuture', true);

         $displayData['formData']->strStaffCFName = $pledge->strStaffCFName;
         $displayData['formData']->strStaffCLName = $pledge->strStaffCLName;
         $displayData['formData']->dteOrigin      = $pledge->dteOrigin;
         $displayData['formData']->strStaffLFName = $pledge->strStaffLFName;
         $displayData['formData']->strStaffLLName = $pledge->strStaffLLName;
         $displayData['formData']->dteLastUpdate  = $pledge->dteLastUpdate;

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $this->clsAC->loadCampaigns(false, true, $pledge->lAccountID, false, null);
            $displayData['formData']->txtCommit     = number_format($pledge->curCommitment, 2);
            $displayData['formData']->strACORadio   = $this->clsACO->strACO_Radios ($pledge->lACOID, 'rdoACO');
            $displayData['formData']->strDDLAccts   = $this->clsAC->strDDLAccts    ($pledge->lAccountID, true, true);
            $displayData['formData']->strDDLCamps   = $this->clsAC->strDDLCampaigns($pledge->lCampaignID, false);
            $displayData['formData']->strDDLFreq    = $this->clsPledges->strDDLPledgeFrequecy('ddlFreq', $pledge->enumFreq, true);
            $displayData['formData']->txtNumPay     = $pledge->lNumCommit;

            $displayData['formData']->strNotes      = htmlspecialchars($pledge->strNotes);
            if (is_null($pledge->mdteStart)){
               $displayData['formData']->txtStartDate = '';
            }else {
               $displayData['formData']->txtStartDate = strNumericDateViaMysqlDate($pledge->mdteStart, $gbDateFormatUS);
            }

            $this->clsList->strBlankDDLName = 'n/a';
            $this->clsList->enumListType = CENUM_LISTTYPE_ATTRIB;
            $displayData['formData']->strDDLAttrib      = $this->clsList->strLoadListDDL('ddlAttrib',  true, $pledge->lAttributedTo);

         }else {
            setOnFormError($displayData);
            $this->clsAC->loadCampaigns(false, true, set_value('ddlAccount'), false, null);
            $displayData['formData']->txtStartDate  = set_value('txtStartDate');
            $displayData['formData']->txtCommit     = set_value('txtCommit');
            $displayData['formData']->txtNumPay     = set_value('txtNumPay');
            $displayData['formData']->strACORadio   = $this->clsACO->strACO_Radios (set_value('rdoACO'), 'rdoACO');
            $displayData['formData']->strDDLAccts   = $this->clsAC->strDDLAccts    (set_value('ddlAccount'), true, true);
            $displayData['formData']->strDDLCamps   = $this->clsAC->strDDLCampaigns(set_value('ddlCamps'), false);
            $displayData['formData']->strNotes      = set_value('txtNotes');

            $this->clsList->strBlankDDLName = '&nbsp;';
            $this->clsList->enumListType = CENUM_LISTTYPE_ATTRIB;
            $displayData['formData']->strDDLAttrib  = $this->clsList->strLoadListDDL('ddlAttrib',  true, set_value('ddlAttrib'));
            $displayData['formData']->strDDLFreq    = $this->clsPledges->strDDLPledgeFrequecy('ddlFreq', set_value('ddlFreq'), true);
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['mainTemplate']   = 'donations/pledge_add_edit_view';
         if ($bPeople){
            $displayData['pageTitle']   = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                                   .' | '.anchor('people/people_record/view/'.$lFID, 'Record', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').' Pledge Record';
            $this->clsPeople->loadPeopleViaPIDs($lFID, false, false);
            $displayData['contextSummary'] = $this->clsPeople->peopleHTMLSummary(0);
         }else {
            $displayData['pageTitle']   = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                                   .' | '.anchor('biz/biz_record/view/'.$lFID, 'Record', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').' Pledge Record';
            $this->clsBiz->loadBizRecsViaBID($lFID);
            $displayData['contextSummary'] = $this->clsBiz->strBizHTMLSummary(0);
         }

         $displayData['title']          = CS_PROGNAME.' | Pledges';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $pledge->curCommitment = (float)trim($_POST['txtCommit']);
         $pledge->lNumCommit    = (integer)trim($_POST['txtNumPay']);
         $pledge->enumFreq      = trim($_POST['ddlFreq']);
         $pledge->lCampaignID   = (integer)trim($_POST['ddlCamps']);
         $pledge->lForeignID    = $lFID;
         $pledge->lACOID        = (integer)trim($_POST['rdoACO']);
         $pledge->strNotes      = trim($_POST['txtNotes']);

         $pledge->lAttributedTo = (integer)trim($_POST['ddlAttrib']);
         if ($pledge->lAttributedTo <= 0) $pledge->lAttributedTo = null;

         $strDate   = trim($_POST['txtStartDate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $pledge->mdteStart = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);

            //------------------------------------
            // update db tables and return
            //------------------------------------
         if ($bNew){
            $lPledgeID = $this->clsPledges->lAddNewPledgeRecord();
            $this->session->set_flashdata('msg', 'Pledge added');
         }else {
            $this->clsPledges->updatePledgeRecord($pledge->lKeyID);
            $this->session->set_flashdata('msg', 'Pledge record updated');
         }
         redirect('donations/pledge_record/view/'.$lPledgeID);
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
   function pledgeVerifyDateValid($strDate){
      if (!bValidVerifyDate($strDate)){
         $this->form_validation->set_message('pledgeVerifyDateValid', 'The date you entered is not valid.');
         return(false);
      }else {
         return(true);
      }
   }

   function pledgeVerifyAccountValid($lAcctID){
      if (((integer)$lAcctID) > 0){
         return(true);
      }else {
         $this->form_validation->set_message('pledgeVerifyAccountValid', 'Please select an account.');
         return(false);
      }
   }

   function pledgeVerifyCampValid($lCampID){
      if (((integer)$lCampID) > 0){
         return(true);
      }else {
         $this->form_validation->set_message('pledgeVerifyCampValid', 'Please select a campaign.');
         return(false);
      }
   }

   function pledgeFreqValid($enumFreq){
      if (trim($enumFreq)=='-1'){
         $this->form_validation->set_message('pledgeFreqValid', 'Please select a pledge frequency.');
         return(false);
      }else {

            // test for case of one-time committment, but # payments != 1
         if ($enumFreq == 'one-time'){
            $lNumPay = @$_POST['txtNumPay'];
            if (!is_numeric($lNumPay)) return(true);  // caught elsewhere
            $lNumPay = (int)$lNumPay;
            if ($lNumPay <= 0) return(true);  // caught elsewhere
            if ($lNumPay != 1){
               $this->form_validation->set_message('pledgeFreqValid', 'If you specify a one-time commitment, the # of payments must equal 1.');
               return(false);
            }else {
               return(true);
            }
         }else {
            return(true);
         }
      }
   }

   function pledgeVerifyPayType($lPayType){
      $lPayType = (integer)$lPayType;
      if ($lPayType <= 0){
         $this->form_validation->set_message('pledgeVerifyPayType', 'Please select a payment type.');
         return(false);
      }else {
         return(true);
      }
   }

   function pledgeVerifyCommitment($curAmnt){
      $curAmnt = (float)$curAmnt;
      if ($curAmnt < 0.001){
         $this->form_validation->set_message('pledgeVerifyCommitment', 'The pledge amount must be greater than 0.00.');
         return(false);
      }else {
         return(true);
      }
   }

   function stripCommas(&$strAmount){
      $strAmount = str_replace (',', '', $strAmount);
      return(true);
   }


   function addEditPayment($lPledgeID, $lGiftID, $dtePledge){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gbDateFormatUS, $gstrFormatDatePicker;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPledgeID, 'pledge ID');

      $displayData = array();
      $displayData['lPledgeID'] = $lPledgeID   = (integer)$lPledgeID;
      $displayData['dtePledge'] = $dtePledge   = (integer)$dtePledge;
      $displayData['lGiftID']   = $lGiftID     = (integer)$lGiftID;
      $displayData['js']        = '';

      if (!bTestForURLHack('dataEntryGifts')) return;

      $bNew = $lGiftID <= 0;

         // load models
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper ('dl_util/web_layout');
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper ('dl_util/time_date');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $this->load->model  ('people/mpeople',         'clsPeople');
      $this->load->model  ('biz/mbiz',               'clsBiz');
      $this->load->model  ('donations/mdonations',   'clsGift');
      $this->load->model  ('util/mlist_generic',     'clsList');
      $this->load->model  ('donations/mpledges',     'clsPledges');

         // load pledge
      $this->clsPledges->loadPledgeViaPledgeID($lPledgeID);
      $displayData['pledge'] = $pledge = &$this->clsPledges->pledges[0];
      $bPeople = !$pledge->bBiz;
      $lFID    =  $pledge->lForeignID;

         // load gift associated with pledge
      $this->clsGift->loadGiftViaGID($lGiftID);
      $gifts = &$this->clsGift->gifts[0];
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$gifts   <pre>');
echo(htmlspecialchars( print_r($gifts, true))); echo('</pre></font><br>');
// ------------------------------------- */

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtAmount',    'Amount',          'trim|required|callback_stripCommas|numeric|callback_pledgeVerifyCommitment');
      $this->form_validation->set_rules('txtDDate',     'Date',            'trim|required|callback_pledgeVerifyDateValid');
      $this->form_validation->set_rules('txtNotes',     'Notes',               'trim');
      $this->form_validation->set_rules('txtCheckNum',  'Check Number',        'trim');
		$this->form_validation->set_rules('ddlPayType',   'Payment Type',        'callback_pledgeVerifyPayType');
		$this->form_validation->set_rules('ddlGiftCat',   'Gift Category',       'trim');
		$this->form_validation->set_rules('ddlAttrib',    'Attributed To',       'trim');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

         $displayData['js'] .= strDatePicker('datepickerFuture', true);
         if (validation_errors()==''){
            if ($bNew){
               $displayData['formData']->strNotes      = '';
               $displayData['formData']->strCheckNum   = '';
               $displayData['formData']->txtDDate      = date($gstrFormatDatePicker, $dtePledge);
               $displayData['formData']->txtAmount     = number_format($pledge->curCommitment, 2, '.', '');

               $this->clsList->strBlankDDLName = '&nbsp;';
               $this->clsList->enumListType = CENUM_LISTTYPE_GIFTPAYTYPE;
               $displayData['formData']->strDDLPayType     = $this->clsList->strLoadListDDL('ddlPayType', true, -1);
               $this->clsList->enumListType = CENUM_LISTTYPE_MAJORGIFTCAT;
               $displayData['formData']->strDDLMajGiftType = $this->clsList->strLoadListDDL('ddlGiftCat', true, -1);
            }else {
               $displayData['formData']->strNotes      = htmlspecialchars($gifts->strNotes);
               $displayData['formData']->strCheckNum   = htmlspecialchars($gifts->gi_strCheckNum);
               $displayData['formData']->txtDDate      = date($gstrFormatDatePicker, $gifts->gi_dteDonation);
               $displayData['formData']->txtAmount     = number_format($gifts->gi_curAmnt, 2, '.', '');

               $this->clsList->strBlankDDLName = '&nbsp;';
               $this->clsList->enumListType = CENUM_LISTTYPE_GIFTPAYTYPE;
               $displayData['formData']->strDDLPayType     = $this->clsList->strLoadListDDL('ddlPayType', true, $gifts->gi_lPaymentType);
               $this->clsList->enumListType = CENUM_LISTTYPE_MAJORGIFTCAT;
               $displayData['formData']->strDDLMajGiftType = $this->clsList->strLoadListDDL('ddlGiftCat', true, $gifts->gi_lMajorGiftCat);
            }
         }else {
            setOnFormError($displayData);

            $displayData['formData']->txtDDate      = set_value('txtDDate');
            $displayData['formData']->txtAmount     = set_value('txtAmount');
            $displayData['formData']->strNotes      = set_value('txtNotes');
            $displayData['formData']->strCheckNum   = set_value('txtCheckNum');

            $this->clsList->strBlankDDLName = '&nbsp;';
            $this->clsList->enumListType = CENUM_LISTTYPE_GIFTPAYTYPE;
            $displayData['formData']->strDDLPayType     = $this->clsList->strLoadListDDL('ddlPayType', true, set_value('ddlPayType'));
            $this->clsList->enumListType = CENUM_LISTTYPE_MAJORGIFTCAT;
            $displayData['formData']->strDDLMajGiftType = $this->clsList->strLoadListDDL('ddlGiftCat', true, set_value('ddlGiftCat'));
         }
            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['mainTemplate']   = 'donations/pledge_payment_add_edit_view';
         if ($bPeople){
            $displayData['pageTitle']   = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                                   .' | '.anchor('people/people_record/view/'.$lFID, 'Record', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').' Pledge Payment';
            $this->clsPeople->loadPeopleViaPIDs($lFID, false, false);
            $displayData['contextSummary'] = $this->clsPeople->peopleHTMLSummary(0);
         }else {
            $displayData['pageTitle']   = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                                   .' | '.anchor('biz/biz_record/view/'.$lFID, 'Record', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').' Pledge Payment';
            $this->clsBiz->loadBizRecsViaBID($lFID);
            $displayData['contextSummary'] = $this->clsBiz->strBizHTMLSummary(0);
         }

         $displayData['title']          = CS_PROGNAME.' | Pledges';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $this->load->vars($displayData);
         $this->load->view('template');

      }else {
         $this->load->model ('personalization/muser_fields',        'clsUF');
         $this->load->model ('personalization/muser_fields_create', 'clsUFC');
         $this->load->model ('admin/mpermissions',                  'perms');
         $this->load->helper('dl_util/util_db');

/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$pledge   <pre>');
echo(htmlspecialchars( print_r($pledge, true))); echo('</pre></font><br>');
// -------------------------------------*/


         $gifts->gi_curAmnt       = $curAmnt = trim((float)$_POST['txtAmount']);
         $gifts->gc_lKeyID        = $pledge->lCampaignID;
         $gifts->gi_lSponsorID    = null;
         $gifts->lACOID           = $pledge->lACOID;
         $gifts->strNotes         = trim($_POST['txtNotes']);
         $gifts->gi_strCheckNum   = trim($_POST['txtCheckNum']);
         $gifts->gi_lAttributedTo = $pledge->lAttributedTo; // trim((integer)$_POST['ddlAttrib']);
         $gifts->gi_lGIK_ID       = null;
         $gifts->gi_lPaymentType  = trim((integer)$_POST['ddlPayType']);
         $gifts->gi_lMajorGiftCat = trim((integer)$_POST['ddlGiftCat']);
         $gifts->gi_lForeignID    = $lFID;
         $gifts->lPledgeID        = $lPledgeID;

//         if ($gifts->gi_lAttributedTo <= 0) $gifts->gi_lAttributedTo = null;
//         if ($gifts->gi_lGIK_ID       <= 0) $gifts->gi_lGIK_ID       = null;
//         $gifts->gi_bGIK = !is_null($gifts->gi_lGIK_ID);

         $strDate   = trim($_POST['txtDDate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $gifts->mdteDonation = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);

         $this->session->set_flashdata('msg', 'A pledge payment of '
                         .$pledge->strACOCurSymbol.number_format($curAmnt, 2)
                         .' was '.($bNew ? 'added.' : 'updated.'));
         if ($bNew){
            $lGiftID = $this->clsGift->lAddNewGiftRecord();
         }else {
            $this->clsGift->updateGiftRecord();
         }
         redirect('donations/pledge_record/view/'.$lPledgeID);
      }
   }



}
