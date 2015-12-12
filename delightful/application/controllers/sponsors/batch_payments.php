<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class batch_payments extends CI_Controller {
   public $validation;

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function batchSelectOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showSponsorFinancials')) return;
      $displayData = array();

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->load->model('admin/madmin_aco',                  'clsACO');

         //--------------------------
         // validation rules
         //--------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
 		$this->form_validation->set_rules('ddlSponProgs', 'Sponsorship Program',  'trim|required');
 		$this->form_validation->set_rules('rdoSort', 'Sorting Option',  'trim|required');

      if ($this->form_validation->run() == FALSE){
         $displayData['clsACO'] = &$this->clsACO;
         $this->load->library('generic_form');
         $this->load->helper('dl_util/web_layout');
         $displayData['clsForm']     = &$this->generic_form;

         if (validation_errors()==''){
            $displayData['strSort'] = 'name';
            $displayData['strSponProgs'] =
                '<select name="ddlSponProgs">
                    <option value="-1">(all programs)</option>'
                    .$this->clsSponProg->strSponProgramDDL(-1)
               .'</select>';
         }else {
            $displayData['strSort'] = @$_REQUEST['rdoSort'];
            $displayData['strSponProgs'] =
                '<select name="ddlSponProgs">
                    <option value="-1">(all programs)</option>'
                    .$this->clsSponProg->strSponProgramDDL((int)@$_REQUEST['ddlSponProgs'])
               .'</select>';
         }

            //------------------------------------------------
            // breadcrumbs / page setup
            //------------------------------------------------
         $displayData['mainTemplate'] = array('sponsorship/batch_payment_options_view');
         $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                                 .' | '.anchor('sponsors/batch_payments/batchSelectOpts', 'Batch Payments', 'class="breadcrumb"')
                                 .' | Options';


         $displayData['title']        = CS_PROGNAME.' | Sponsorship';
         $displayData['nav']          = $this->mnav_brain_jar->navData();
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $strSort = trim(@$_REQUEST['rdoSort']);
         $lSponProgramID = (int)@$_REQUEST['ddlSponProgs'];
         redirect('sponsors/batch_payments/showSponsors/'.$strSort.'/'.$lSponProgramID);
      }
   }

   function showSponsors($strSort, $lSponProgID, $lStartRec=0, $lRecsPerPage=50){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $genumDateFormat;

      if (!bTestForURLHack('showSponsorFinancials')) return;
      $displayData = array();
      $displayData['linkOpts'] = new stdClass;
      $displayData['linkOpts']->strSort      = $strSort      = trim($strSort);
      $displayData['linkOpts']->lSponProgID  = $lSponProgID  = (int)$lSponProgID;
      $displayData['linkOpts']->lStartRec    = $lStartRec    = (int)$lStartRec;
      $displayData['linkOpts']->lRecsPerPage = $lRecsPerPage = (int)$lRecsPerPage;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model  ('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->load->model  ('admin/madmin_aco',                  'clsACO');
      $this->load->model  ('sponsorship/msponsorship',          'clsSpon');
      $this->load->model  ('sponsorship/msponsor_charge_pay',   'clsSCP');
      $this->load->model  ('util/mlist_generic',                'clsList');
      $this->load->model('donations/maccts_camps',              'clsAC');
      
      $this->load->helper ('dl_util/rs_navigate');
      $this->load->helper ('js/batch_spon_pay');
      $this->load->library('util/dl_date_time', '',             'clsDateTime');
      $this->load->helper ('dl_util/time_date');

         //--------------------------
         // validation rules
         //--------------------------
      $this->validation = new stdClass;
      $this->validation->requiresVerification = array();
      $this->validation->paymentType = array();
      $this->validation->amount = array();

      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('hdnFormVerify', 'Verify Form',       'callback_verifyBatchInput');


      if ($this->form_validation->run() == FALSE){
         $displayData['js'] = batchSponPay();
         $displayData['lNumRecsTot'] = $lNumRecsTot =
                       $this->clsSpon->lNumSponsorsViaProgram($lSponProgID, false, '');

         $this->load->library('generic_form');
         $this->load->helper('dl_util/web_layout');
         $displayData['clsForm'] = &$this->generic_form;

            //------------------------------------------------
            // stripes
            //------------------------------------------------
         $this->load->model('util/mbuild_on_ready', 'clsOnReady');
         $this->clsOnReady->addOnReadyTableStripes();
         $this->clsOnReady->closeOnReady();
         $displayData['js'] .= $this->clsOnReady->strOnReady;

         if ($lNumRecsTot > 0){
            $displayData['directoryRecsPerPage'] = $lRecsPerPage;
            $displayData['directoryStartRec']    = $lStartRec;
            $displayData['strLinkBase']  = $strLinkBase = 'sponsors/batch_payments/showSponsors/'.$strSort.'/'.$lSponProgID.'/';
            $strLimit =  "LIMIT  $lStartRec, $lRecsPerPage "; // strLoadRecSpecs($lRecsPerPage, $lStartRec);

            $strWhereExtra = '';
            if ($lSponProgID > 0) {
               $strWhereExtra .= " AND sp_lSponsorProgramID=$lSponProgID ";
               $displayData['strTitle'] = $this->clsSponProg->strSponProgsViaID($lSponProgID);
            }else {
               $displayData['strTitle'] = 'All sponsorship programs';
            }

            $strWhereExtra .= ' AND NOT sp_bInactive ';

            switch ($strSort){
               case 'acoName':
                  $this->clsSpon->strOrderBy = ' aco_strName,
                                                 tblPeopleSpon.pe_strLName, tblPeopleSpon.pe_strFName,
                                                 tblPeopleSpon.pe_lKeyID, sp_lKeyID ';
                  break;
               case 'id':
                  $this->clsSpon->strOrderBy = ' sp_lKeyID ';
                  break;
               case 'client':
                  $this->clsSpon->strOrderBy = ' cr_strLName, cr_strFName, sp_lClientID,
                                    tblPeopleSpon.pe_strLName, tblPeopleSpon.pe_strFName,
                                    tblPeopleSpon.pe_lKeyID, sp_lKeyID  ';
                  break;
               case 'name':
               default:
                  $this->clsSpon->strOrderBy = ' tblPeopleSpon.pe_strLName, tblPeopleSpon.pe_strFName,
                                                 tblPeopleSpon.pe_lKeyID, sp_lKeyID ';
                  break;
            }

            $this->clsList->enumListType = CENUM_LISTTYPE_GIFTPAYTYPE;

            $this->clsSpon->sponsorInfoGenericViaWhere($strWhereExtra, $strLimit);
            $displayData['sponInfo']  = $sponInfo = &$this->clsSpon->sponInfo;
            $displayData['lNumRecsThisPage'] = $lNumRecsThisPage = $this->clsSpon->lNumSponsors;

            if ($lNumRecsThisPage > 0){
               foreach ($sponInfo as $spon){
                  $lSponID = $spon->lKeyID;
                  $spon->strRadioNameFN = 'rdoPayType['.$lSponID.']';
                  $spon->strCheckFN     = 'txtCheck['.$lSponID.']';
                  $spon->strAmountFN    = 'txtAmount['.$lSponID.']';
                  $this->clsSCP->mostRecentPayment($lSponID, true);
                  if ($this->clsSCP->lNumPayRecs == 0){
                     $spon->strLastPay = 'Last payment: n/a';
                  }else {
                     $payRec = &$this->clsSCP->paymentRec[0];
                     $spon->strLastPay = 'Last payment of '.$payRec->strCurSymbol.' '.number_format($payRec->curPaymentAmnt, 2)
                                 .'<br>recorded '.date($genumDateFormat, $payRec->dteOrigin);
                     $strLastRec = date('Y-m-d', $payRec->dteOrigin);
                     $strToday   = date('Y-m-d', $gdteNow);

                        // it's the little things that make a house a home
                     if ($strLastRec == $strToday) {
                        $spon->strLastPay = '<font color="#006d57">'.$spon->strLastPay.' <b>(today)</b></font>';
                     }
                  }
               }

               if (validation_errors()==''){
                  foreach ($sponInfo as $spon){
                     $lSponID = $spon->lKeyID;

                     $spon->txtAmount   = '';
                     $spon->txtCheckNum = '';
                     $spon->rdoPayType  = '';
                     $spon->txtPayDate  = '';
                     $spon->strDDLPayType = $this->clsList->strLoadListDDL('ddlPayType['.$lSponID.']', true, -1);

                     $this->validation->paymentType[$lSponID] = 
                     $this->validation->amount[$lSponID]      = 
                     $this->validation->paymentDate[$lSponID] = '';
                  }

               }else {
                  $displayData['js'] .= batchSponPayForceDirty();
                  foreach ($sponInfo as $spon){
                     $lSponID = $spon->lKeyID;

                     $spon->txtAmount   = $_REQUEST['txtAmount'][$lSponID];
                     $spon->txtCheckNum = $_REQUEST['txtCheck'][$lSponID];
                     $spon->rdoPayType  = (int)$_REQUEST['ddlPayType'][$lSponID];
                     $spon->txtPayDate  = $_POST['txtPayDate'.$lSponID];
                     $spon->strDDLPayType = $this->clsList->strLoadListDDL('ddlPayType['.$lSponID.']', true, $spon->rdoPayType);
                  }
               }
            }
         }
         $displayData['lNumDisplayRows'] = $this->clsSpon->lNumSponsors;
         $displayData['validation']      = &$this->validation;

            //------------------------------------------------
            // breadcrumbs / page setup
            //------------------------------------------------
         $displayData['mainTemplate'] = 'sponsorship/batch_payments_form_view';
         $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                                 .' | '.anchor('sponsors/batch_payments/batchSelectOpts', 'Batch Payments', 'class="breadcrumb"')
                                 .' | Payment Entry';

         $displayData['title']        = CS_PROGNAME.' | Sponsorship';
         $displayData['nav']          = $this->mnav_brain_jar->navData();

         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $lNumProcessed = $this->lProcessBatch();
         if ($lNumProcessed==0){
            $this->session->set_flashdata('error', 'WARNING: No payment amounts were specified. No payments were recorded.<br><br>Please try again.');         
         }else {
            $this->session->set_flashdata('msg', $lNumProcessed.' sponsorship payment'
                     .($lNumProcessed==1 ? ' was' : 's were').' added.');         
         }
         redirect('sponsors/batch_payments/showSponsors/'.$strSort.'/'.$lSponProgID.'/'.$lStartRec.'/'.$lRecsPerPage);
      }
   }

   function lProcessBatch(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gbDateFormatUS;

      if (!bTestForURLHack('showSponsorFinancials')) return;
      $lNumProcessed = 0;

      if (!isset($_REQUEST['txtAmount'])) return(0);

         // load payment record template
      $this->clsSCP->loadPayRecordViaPayID(-1);
      $payRec = &$this->clsSCP->paymentRec[0];

      foreach ($_REQUEST['txtAmount'] as $lSponID => $txtAmount){
         if ($this->validation->requiresVerification[$lSponID]){
            $txtAmount  = $this->stripCommas(trim($txtAmount));
            $lPayTypeID = (int)$_REQUEST['ddlPayType'][$lSponID];
            $lAOCID     = (int)$_REQUEST['hdnAOCID'][$lSponID];
            $lFID       = (int)$_REQUEST['hdnFID'][$lSponID];
            $sngAmount  = number_format((float)$txtAmount, 2, '.', '');
            $txtPayDate = trim($_POST['txtPayDate'.$lSponID]);

            $payRec->lDonorID       = $lFID;
            $payRec->lSponsorshipID = $lSponID;
            $payRec->curPaymentAmnt = $sngAmount;
            $payRec->strCheckNum    = substr(trim($_REQUEST['txtCheck'][$lSponID]), 0, 255);
            $payRec->lPaymentType   = $lPayTypeID;
            $payRec->lACOID         = $lAOCID;
            
            MDY_ViaUserForm($txtPayDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
            $payRec->dtePayment = mktime(0, 0, 0, $lMon, $lDay, $lYear);
            
            $this->clsSCP->lAddNewPayment();
            
            ++$lNumProcessed;
         }
      }
      return($lNumProcessed);
   }

   function verifyBatchInput($dummy){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $bFormOkay = true;

      if (!isset($_REQUEST['txtAmount'])) return(true);

      foreach ($_REQUEST['txtAmount'] as $lSponID => $txtAmount){
         $this->validation->paymentType[$lSponID] = 
         $this->validation->paymentDate[$lSponID] = 
         $this->validation->amount[$lSponID]      = '';

         $txtAmount = $this->stripCommas(trim($txtAmount));

         if ($txtAmount==''){
            $this->validation->requiresVerification[$lSponID] = false;
         }elseif (is_numeric($txtAmount) && (abs((float)($txtAmount))<0.01)) {
            $this->validation->requiresVerification[$lSponID] = false;
         }else {
            $this->validation->requiresVerification[$lSponID] = true;
         }
      }

      foreach ($_REQUEST['txtAmount'] as $lSponID => $txtAmount){
         if ($this->validation->requiresVerification[$lSponID]){
            $txtAmount  = $this->stripCommas(trim($txtAmount));
            $txtPayDate = trim($_POST['txtPayDate'.$lSponID]);
            $lPayTypeID = (int)$_REQUEST['ddlPayType'][$lSponID];
            
            if (!is_numeric($txtAmount)){
               $this->validation->amount[$lSponID] =
                        '<br><div class="formError" style="width: 170px;">The <b>Payment Amount</b> field must contain only numbers.</div>';
               $bFormOkay = false;
            }
            
            $sngAmount = (float)$txtAmount;
            if ($sngAmount < 0.0){
               $this->validation->amount[$lSponID] =
                        '<br><div class="formError" style="width: 170px;">The <b>Payment Amount</b> can not be a negative number.</div>';
               $bFormOkay = false;
            }

            if ($lPayTypeID <= 0){
               $this->validation->paymentType[$lSponID] =
                        '<br><div class="formError" style="width: 170px;">Please select a <b>Payment Type</b>.</div>';
               $bFormOkay = false;
            }
            
            if ($txtPayDate == ''){
               $this->validation->paymentDate[$lSponID] =
                        '<br><div class="formError" style="width: 170px;">Please enter a <b>Payment Date</b>.</div>';
               $bFormOkay = false;
            }elseif (!bValidVerifyDate($txtPayDate)){
               $this->validation->paymentDate[$lSponID] =
                        '<br><div class="formError" style="width: 170px;">The <b>Payment Date</b> is not valid.</div>';
               $bFormOkay = false;
            }
         }
      }

      return($bFormOkay);
   }

   function stripCommas($strAmount){
      return(str_replace (',', '', $strAmount));
   }



}