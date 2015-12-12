<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class payments extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addNewPayment($lSponID){
      if (!bTestForURLHack('showSponsorFinancials')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lSponID,   'sponsor ID');

      $displayData = array();
      $displayData['lSponID'] = $lSponID = (integer)$lSponID;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
      $this->load->library('js_build/js_verify');
      $this->load->library('js_build/java_joe_radio');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper ('js/verify_spon_pay_search_form');
      $this->load->model('sponsorship/msponsorship',      'clsSpon');
      $this->jsRadio   = new java_joe_radio;
      $this->js_verify = new js_verify;
      $this->js_verify->clearEmbedOpts();
      $this->js_verify->bShow_bVerifyString    =
      $this->js_verify->bShow_bVerifyRadio     =
      $this->js_verify->bShow_strRadioSelValue =
      $this->js_verify->bShow_trim             = true;
      $displayData['js'] =
                  $this->js_verify->loadJavaVerify()."\n"
                 .$this->jsRadio->insertJavaJoeRadio()."\n"
                 .verifySelSponPayForm();

      $this->clsSpon->sponsorInfoViaID($lSponID);
      $displayData['sponRec']        = &$this->clsSpon->sponInfo[0];
      $displayData['contextSummary'] = $this->clsSpon->sponsorshipHTMLSummary();

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['contextSummary'] = $this->clsSpon->sponsorshipHTMLSummary();

      $displayData['mainTemplate'] = array('sponsorship/payment_add_s1_view');
      $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                              .' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$lSponID, 'Sponsorship Record', 'class="breadcrumb"')
                              .' | Add Sponsorship Payment';


      $displayData['title']        = CS_PROGNAME.' | Sponsorship';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function payerList($lSponID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showSponsorFinancials')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lSponID,   'sponsor ID');

      $displayData = array();
      $displayData['lSponID'] = $lSponID = (integer)$lSponID;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
      $this->load->model('sponsorship/msponsorship',      'clsSpon');

      $this->clsSpon->sponsorInfoViaID($lSponID);
      $enumPayType = $_REQUEST['rdoSP'];
      switch ($enumPayType){
         case 'sponsor':
            redirect('sponsors/payments/addEditPayment/'.$lSponID.'/'.$this->clsSpon->sponInfo[0]->lForeignID.'/0');
            break;
         case 'person':
            $this->searchSponPay($lSponID, $_REQUEST['txtSPP'], true, $this->clsSpon);
            break;
         case 'biz':
            $this->searchSponPay($lSponID, $_REQUEST['txtSPB'], false, $this->clsSpon);
            break;
         default:
            screamForHelp($enumPayType.': UNRECOGNIZED PROCESSING OPTION</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
            break;
      }
   }

   function searchSponPay($lSponID, $strSearch, $bPerson, &$clsSpon){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showSponsorFinancials')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lSponID,   'sponsor ID');

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
      $this->load->model('util/msearch_single_generic', 'clsSearch');
      $this->load->model('people/mpeople', 'clsPeople');

      $displayData['contextSummary'] = $clsSpon->sponsorshipHTMLSummary();

      if ($bPerson){
         $this->clsSearch->strSearchTerm = trim($_POST['txtSPP']);
      }else {
         $this->clsSearch->strSearchTerm = trim($_POST['txtSPB']);
      }

         //-----------------------------
         // search display setup
         //-----------------------------
      $this->clsSearch->enumSearchType      = $bPerson ? CENUM_CONTEXT_PEOPLE : CENUM_CONTEXT_BIZ;
      $this->clsSearch->strSearchLabel      = $bPerson ? 'Individuals' : 'Business/Organizations';
      $this->clsSearch->bShowKeyID          = true;
      $this->clsSearch->bShowSelect         = true;
      $this->clsSearch->strIDLabel          = $bPerson ? 'peopleID: ' : 'businessID';
      $this->clsSearch->bShowLink           = false;
      $this->clsSearch->bShowEnumSearchType = false;
      $this->clsSearch->strDisplayTitle     =
                '<br>Please select the '.($bPerson ? 'individual' : 'business/organization').' making this sponsor payment:<br>';

         // landing page for selection
      $this->clsSearch->strPathSelection  = 'sponsors/payments/payerSelect/'.$lSponID.'/';
      $this->clsSearch->strTitleSelection = 'Select '.($bPerson ? 'person' : 'business');

         // landing page for "back"
      $this->clsSearch->strPathSearchAgain  = 'sponsors/payments/addNewPayment/'.$lSponID;
      $this->clsSearch->strTitleSearchAgain = 'Search again...';

      $lLeftCnt = strlen($strSearch);
      $this->clsSearch->strWhereExtra = " AND LEFT(pe_strLName, $lLeftCnt)=".strPrepStr($strSearch).' ';

         // run search
      $displayData['strSearchLabel'] =
                          'Searching for '.$this->clsSearch->enumSearchType.' that begin with <b><i>"'
                          .htmlspecialchars($strSearch).'"</b></i><br>';
      if ($bPerson){
         $this->clsSearch->searchPeople();
      }else {
         $this->clsSearch->searchBiz();
      }
      $displayData['strHTMLSearchResults'] = $this->clsSearch->strHTML_SearchResults();

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Sponsorship';
      $displayData['mainTemplate'] = array('sponsorship/payment_add_s2_view');
      $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                              .' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$lSponID, 'Sponsorship Record', 'class="breadcrumb"')
                              .' | Sponsorship Payment';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function payerSelect($lSponID, $lFPayerID){
      $lSponID   = (integer)$lSponID;
      $lFPayerID = (integer)$lFPayerID;

      $this->addEditPayment($lSponID, $lFPayerID, 0);
   }

   function addEditPayment($lSponID, $lFPayerID, $lPayID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gclsChapterACO, $gbDateFormatUS, $gstrFormatDatePicker;

      if (!bTestForURLHack('showSponsorFinancials')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lSponID,   'sponsor ID');
      verifyID($this, $lFPayerID, 'people/business ID');
      if ($lPayID.'' != '0') verifyID($this, $lPayID, 'sponsor payment ID');

      $displayData = array();
      $displayData['formData']  = new stdClass;
      $displayData['lSponID']   = $lSponID   = (integer)$lSponID;
      $displayData['lFPayerID'] = $lFPayerID = (integer)$lFPayerID;
      $displayData['lPayID']    = $lPayID    = (integer)$lPayID;
      $displayData['bNew']      = $bNew      = $lPayID <= 0;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',           'clsDateTime');
      $this->load->model  ('sponsorship/msponsorship',        'clsSpon');
      $this->load->model  ('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->model  ('admin/madmin_aco',                'clsACO');
      $this->load->model  ('util/mlist_generic',              'clsList');
      $this->load->helper ('dl_util/time_date');
//      $this->load->helper ('dl_util/email_web');


      $this->load->model('people/mpeople', 'clsPeople');

      $bBizPayer = $this->clsPeople->bBizRec($lFPayerID);
      if ($bBizPayer){
         $this->load->model('biz/mbiz', 'clsBiz');

         $this->clsBiz->lBID = $lFPayerID;
         $this->clsBiz->loadBasicBizInfo();
         $displayData['strPayer'] = strLinkView_BizRecord($lFPayerID, 'View business/organization record', true).' '
             .str_pad($lFPayerID, 5, '0', STR_PAD_LEFT).' '
             .$this->clsBiz->strSafeName;
      }else {
         $this->clsPeople->lPeopleID = $lFPayerID;
         $this->clsPeople->peopleInfoLight();
         $displayData['strPayer'] = strLinkView_PeopleRecord($lFPayerID, 'View people record', true).' '
             .str_pad($lFPayerID, 5, '0', STR_PAD_LEFT).' '
             .$this->clsPeople->strSafeName;
      }

      $this->clsSCP->loadPayRecordViaPayID($lPayID);
      $pRec = &$this->clsSCP->paymentRec[0];

      $this->clsSpon->sponsorInfoViaID($lSponID);

      if ($bNew){
         $pRec->lACOID         = $this->clsSpon->sponInfo[0]->lCommitACO;
         $pRec->curPaymentAmnt = 0.0;
         $pRec->dtePayment     = $gdteNow;
      }else {
//         if (!$this->clsSCP->bPaymentExists($lHoldPayID)){
//            $this->session->set_flashdata('error', '<b>ERROR:</b> The payment ID '.htmlspecialchars($lHoldPayID).' is not valid.</font>');
//            redirect('main/menu/home');
//         }
      }

         //--------------------------
         // validation rules
         //--------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
 		$this->form_validation->set_rules('txtAmount',  'Amount',             'trim|required|callback_stripCommas|numeric');
      $this->form_validation->set_rules('rdoACO',     'Accounting Country', 'trim|callback_verifyACOSet');
      $this->form_validation->set_rules('txtCheck',   'Check Number',       'trim');
		$this->form_validation->set_rules('ddlPayType', 'Payment Type',       'trim|callback_sponPayVerifyPayType');
      $this->form_validation->set_rules('txtPayDate', 'Date of Payment',
                                                             'trim|required|callback_sponPaymentAddEditDateValid');

      if ($this->form_validation->run() == FALSE){
         $displayData['clsACO'] = &$this->clsACO;
         $this->load->library('generic_form');
         $this->load->helper('dl_util/web_layout');
         $displayData['clsForm']     = &$this->generic_form;

         if (validation_errors()==''){
            if ($bNew){
               $displayData['strPayDate']    = date($gstrFormatDatePicker, $gdteNow);
               $displayData['lPayACO']       = $this->clsSpon->sponInfo[0]->lCommitACO;
               $displayData['strAmount']     = '0.00';
               $displayData['strCheckNum']   = '';
            }else {
               $displayData['strPayDate']  = date($gstrFormatDatePicker, $pRec->dtePayment);
               $displayData['lPayACO']     = $pRec->lACOID;
               $displayData['strAmount']   = number_format($pRec->curPaymentAmnt, 2);
               $displayData['strCheckNum'] = $pRec->strCheckNum;
            }
            $this->clsList->enumListType = CENUM_LISTTYPE_GIFTPAYTYPE;
            $displayData['formData']->strDDLPayType     = $this->clsList->strLoadListDDL('ddlPayType', true, $pRec->lPaymentType);
         }else {
            setOnFormError($displayData);
            $displayData['strPayDate']   = set_value('txtPayDate');
            $displayData['lPayACO']      = set_value('rdoACO');
            $displayData['strAmount']    = set_value('txtAmount');
            $displayData['strCheckNum']  = set_value('txtCheck');
            $this->clsList->enumListType = CENUM_LISTTYPE_GIFTPAYTYPE;
            $displayData['formData']->strDDLPayType     = $this->clsList->strLoadListDDL('ddlPayType', true, set_value('ddlPayType'));
         }
            //------------------------------------------------
            // breadcrumbs / page setup
            //------------------------------------------------
         $displayData['contextSummary'] = $this->clsSpon->sponsorshipHTMLSummary();

         $displayData['mainTemplate'] = array('sponsorship/payment_add_edit_view');
         $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                                 .' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$lSponID, 'Sponsorship Record', 'class="breadcrumb"')
                                 .' | '.($bNew ? 'Add ' : 'Update ').'Sponsorship Payment';


         $displayData['title']        = CS_PROGNAME.' | Sponsorship';
         $displayData['nav']          = $this->mnav_brain_jar->navData();
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->load->helper('dl_util/util_db');
         $this->load->model('donations/maccts_camps', 'clsAC');

         $strChargeDate   = trim($_POST['txtPayDate']);
         MDY_ViaUserForm($strChargeDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $pRec->dtePayment     = strtotime($lMon.'/'.$lDay.'/'.$lYear);
         $pRec->curPaymentAmnt = (float)trim($_POST['txtAmount']);
         $pRec->lACOID         = (integer)$_POST['rdoACO'];
         $pRec->lSponsorshipID = $lSponID;
         $pRec->lDonorID       = $lFPayerID;
         $pRec->lPaymentType   = (integer)$_POST['ddlPayType'];
         $pRec->strCheckNum    = trim($_POST['txtCheck']);

         if ($bNew){
            $lPayID = $this->clsSCP->lAddNewPayment();
            $this->session->set_flashdata('msg', 'Sponsorship payment was added');
         }else {
            $this->clsSCP->updatePayment($lPayID);
            $this->session->set_flashdata('msg', 'Sponsorship payment was updated');
         }
         redirect_SponsorshipPaymentRec($lPayID);
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
   function verifyACOSet($strACO){
      $lACO = (int)$strACO;
      if ($lACO <= 0){
         $this->form_validation->set_message('verifyACOSet',
                   'Please select an accounting country association with this payment.');
         return(false);
      }else {
         return(true);
      }
   }

   function sponPaymentAddEditDateValid($strDate){
      return(bValidVerifyDate($strDate));
   }

   function sponPayVerifyPayType($lPayType){
      return(((integer)$lPayType) > 0);
   }

   function stripCommas(&$strAmount){
      $strAmount = str_replace (',', '', $strAmount);
      return(true);
   }

   function viewPaymentRec($lPayID){
      if (!bTestForURLHack('showSponsorFinancials')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPayID,   'sponsor payment ID');

      $displayData = array();
      $displayData['lPayID']   = $lPayID = (integer)$lPayID;

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model  ('sponsorship/msponsorship',        'clsSpon');
      $this->load->model  ('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->model  ('admin/madmin_aco',                'clsACO');
      $this->load->model  ('admin/muser_accts',               'clsUser');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('dl_util/time_date');

      $this->clsSCP->loadPayRecordViaPayID($lPayID);
      $displayData['pRec']    = $pRec = &$this->clsSCP->paymentRec[0];
      $displayData['lSponID'] = $lSponID = $pRec->lSponsorshipID;
      $displayData['lFPayerID'] = $lFPayerID = $pRec->lDonorID;

      $this->clsSpon->sponsorInfoViaID($lSponID);
      $displayData['contextSummary'] = $this->clsSpon->sponsorshipHTMLSummary();

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                              .' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$lSponID, 'Sponsorship Record', 'class="breadcrumb"')
                              .' | Payment Record';
      $displayData['title']          = CS_PROGNAME.' | Sponsorship';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'sponsorship/payment_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function remove($lSponID, $lPayID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showSponsorFinancials')) return;

      $lSponID = (integer)$lSponID;
      $lPayID  = (integer)$lPayID;

      $this->load->model('sponsorship/msponsorship',      'clsSpon');
      $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->model('admin/madmin_aco',               'clsACO');

      $this->clsSCP->loadPayRecordViaPayID($lPayID);

      $strMsg = 'Sponsor payment record '.str_pad($lPayID, 5, '0', STR_PAD_LEFT)
               .' (payer '.$this->clsSCP->paymentRec[0]->strDonorSafeNameFL.') was removed.';

      $this->clsSCP->removePaymentRecord($lPayID);
      $this->session->set_flashdata('msg', $strMsg);
      redirect_SponsorshipRecord($lSponID);
   }


}