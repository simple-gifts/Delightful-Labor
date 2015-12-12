<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class deposits_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addDeposit(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gbDateFormatUS, $glChapterID;

      if (!bTestForURLHack('showFinancials')) return;
      
      $displayData = array();
      $displayData['formData'] = new stdClass;

         // models/helpers
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/web_layout');
      $this->load->model('admin/madmin_aco',     'clsACO');
      $this->load->model('admin/morganization',  'clsChapter');
      $this->load->model('financials/mdeposits', 'clsDeposits');

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('rdoACO',   'Accounting Country');
      $this->form_validation->set_rules('txtBank',    'Bank',       'trim');
      $this->form_validation->set_rules('txtAccount', 'Account',    'trim');
      $this->form_validation->set_rules('txtNotes',   'Notes',      'trim');
      $this->form_validation->set_rules('txtSDate',   'Start Date', 'trim|required|callback_depositVerifySDateValid');
      $this->form_validation->set_rules('txtEDate',   'End Date',   'trim|required|callback_depositVerifyEDateValid'
                                                                              .'|callback_depositCBH');
		if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');

         $this->clsDeposits->strLimit = 'LIMIT 0, 5';
         $this->clsDeposits->loadDepositReports();
         $displayData['lNumDeposits'] = $lNumDeposits = $this->clsDeposits->lNumDeposits;
         if ($lNumDeposits > 0){
            foreach ($this->clsDeposits->deposits as $deposit){
               $deposit->lNumGifts = $this->clsDeposits->lNumGiftsViaDeposit($deposit->lKeyID, $curTot);
               $deposit->curTot    = $curTot;
            }
         }
         $displayData['deposits']     = &$this->clsDeposits->deposits;
         if (validation_errors()==''){
            $this->clsChapter->lChapterID = $glChapterID;
            $this->clsChapter->loadChapterInfo();

            $displayData['formData']->txtBank     =
            $displayData['formData']->txtAccount  =
            $displayData['formData']->txtNotes    = '';
            $displayData['formData']->strACORadio = $this->clsACO->strACO_Radios(
                                                       $this->clsChapter->chapterRec->lDefaultACO, 'rdoACO');

            $dteStart = null;
            $dteEnd   = $gdteNow;
            if ($gbDateFormatUS){
               $displayData['txtSDate'] = '';
               $displayData['txtEDate'] = date('m/d/Y', $dteEnd);
            }else {
               $displayData['txtSDate'] = '';
               $displayData['txtEDate'] = date('d/m/Y', $dteEnd);
            }
         }else {
            setOnFormError($displayData);

            $displayData['formData']->strACORadio = $this->clsACO->strACO_Radios(set_value('rdoACO'), 'rdoACO');
            $displayData['formData']->txtBank     = set_value('txtBank');
            $displayData['formData']->txtAccount  = set_value('txtAccount');
            $displayData['formData']->txtNotes    = set_value('txtNotes');
            $displayData['txtSDate']              = set_value('txtSDate');
            $displayData['txtEDate']              = set_value('txtEDate');
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/financials',        'Financials/Grants', 'class="breadcrumb"')
                                   .' | '.anchor('financials/deposit_log/view', 'Deposit Log',       'class="breadcrumb"')
                                   .' | Add Deposit Report';

         $displayData['title']          = CS_PROGNAME.' | Financials';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'financials/deposit_opts_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->clsDeposits->strWhereExtra = ' AND dl_lKeyID=-1 ';
         $this->clsDeposits->loadDepositReports();
         $deposit = &$this->clsDeposits->deposits[0];

         $strSDate   = trim($_POST['txtSDate']);
         MDY_ViaUserForm($strSDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $deposit->dteStart = strtotime($lMon.'/'.$lDay.'/'.$lYear);

         $strEDate = trim($_POST['txtEDate']);
         MDY_ViaUserForm($strEDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $deposit->dteEnd = strtotime($lMon.'/'.$lDay.'/'.$lYear.' 23:59:59');
         $deposit->lACOID = (integer)$_POST['rdoACO'];

         $deposit->strBank    = trim($_POST['txtBank']);
         $deposit->strAccount = trim($_POST['txtAccount']);
         $deposit->strNotes   = trim($_POST['txtNotes']);
         $lDepositID          = $this->clsDeposits->lAddNewDeposit();
         redirect('financials/deposits_add_edit/setGiftsToDeposit/'.$lDepositID);
      }
   }

   function setGiftsToDeposit($lDepositID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showFinancials')) return;
      $displayData = array();

         // models/helpers
      $this->load->model('donations/mdonations', 'clsGifts');
      $this->load->model('financials/mdeposits', 'clsDeposits');
      $this->load->model('admin/madmin_aco', 'clsACO');
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);

         // load deposit
      $this->clsDeposits->strWhereExtra = " AND dl_lKeyID=$lDepositID ";
      $this->clsDeposits->loadDepositReports();
      $displayData['deposit'] = $deposit = &$this->clsDeposits->deposits[0];

         // load qualifying donations
      $this->clsGifts->sqlExtraWhere =
           " AND gi_lACOID=$deposit->lACOID
             AND gi_lDepositLogID IS NULL
             AND NOT gi_bGIK
             AND gi_dteDonation BETWEEN ".strPrepDate($deposit->dteStart).' AND '.strPrepDateTime($deposit->dteEnd).' ';

      $this->clsGifts->sqlExtraSort = ' ORDER BY listPayType.lgen_strListItem, gi_dteDonation, gi_curAmnt, gi_lKeyID ';

      $this->clsGifts->loadGifts();
      $displayData['gifts']      = &$this->clsGifts->gifts;
      $displayData['lNumGifts']  = $this->clsGifts->lNumGifts;
      $displayData['lDepositID'] = $lDepositID;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['contextSummary'] = $this->clsDeposits->depositHTMLSummary();
      $displayData['pageTitle']      = anchor('main/menu/financials',                    'Financials/Grants',  'class="breadcrumb"')
                                .' | '.anchor('financials/deposit_log/view',             'Deposit Log',        'class="breadcrumb"')
                                .' | '.anchor('financials/deposits_add_edit/addDeposit', 'Add Deposit Report', 'class="breadcrumb"')
                                .' | Select Donations';

      $displayData['title']          = CS_PROGNAME.' | Financials';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'financials/deposit_gifts_select_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

      //-----------------------------
      // verification routines
      //-----------------------------
   function depositVerifySDateValid($strSDate){
      return(bValidVerifyDate($strSDate));
   }

   function depositVerifyEDateValid($strEDate){
      return(bValidVerifyDate($strEDate));
   }

   function depositCBH($strEDate){
   // CBH: cart before horse
      return(bVerifyCartBeforeHorse(trim($_POST['txtSDate']), $strEDate));
   }

   function addC2Deposit($lDepositID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lDepositID, 'deposit ID');

      $this->load->model('financials/mdeposits', 'clsDeposits');

      foreach ($_POST['chkGift'] as $lGiftID){
         $this->clsDeposits->setGiftToDeposit($lGiftID, $lDepositID);
      }
      redirect('financials/deposit_log/viewEntry/'.$lDepositID);
   }

   function removeEntry($lDepositID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showFinancials')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lDepositID, 'deposit ID');

         // models/helpers
      $this->load->model('donations/mdonations', 'clsGifts');
      $this->load->model('financials/mdeposits', 'clsDeposits');

      $this->clsDeposits->removeDeposit($lDepositID);

      $this->session->set_flashdata('msg', 'Deposit '.str_pad($lDepositID, 5, '0', STR_PAD_LEFT).' was removed.');
      redirect('financials/deposit_log/view');
   }

   function removeGiftEntry($lGiftID, $lDepositID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showFinancials')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lGiftID,    'donation ID');
      verifyID($this, $lDepositID, 'deposit ID');

         // models/helpers
      $this->load->model('financials/mdeposits', 'clsDeposits');
      $this->clsDeposits->removeGiftFromDeposit($lGiftID);

      $this->session->set_flashdata('msg', 'Donation '.str_pad($lGiftID, 5, '0', STR_PAD_LEFT).' was removed from this deposit.');
      redirect('financials/deposit_log/viewEntry/'.$lDepositID);
   }

   function editDeposit($lDepositID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showFinancials')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lDepositID, 'deposit ID');
      
      $displayData = array();
      $displayData['lDepositID'] = (integer)$lDepositID;
      $displayData['formData'] = new stdClass;

         // models/helpers
      $this->load->model('financials/mdeposits', 'clsDeposits');
      $this->load->model('admin/madmin_aco', 'clsACO');
      $this->load->helper('dl_util/web_layout');
      $this->load->library('generic_form');
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);

         // load deposit
      $this->clsDeposits->strWhereExtra = " AND dl_lKeyID=$lDepositID ";
      $this->clsDeposits->loadDepositReports();
      $displayData['deposit'] = $deposit = &$this->clsDeposits->deposits[0];

      $displayData['formData']->txtBank     = htmlspecialchars($deposit->strBank);
      $displayData['formData']->txtAccount  = htmlspecialchars($deposit->strAccount);
      $displayData['formData']->txtNotes    = htmlspecialchars($deposit->strNotes);      

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['contextSummary'] = $this->clsDeposits->depositHTMLSummary();
      $displayData['pageTitle']      = anchor('main/menu/financials',                          'Financials/Grants', 'class="breadcrumb"')
                                .' | '.anchor('financials/deposit_log/view',                   'Deposit Log',       'class="breadcrumb"')
                                .' | '.anchor('financials/deposit_log/viewEntry/'.$lDepositID, 'View Deposit',      'class="breadcrumb"')
                                .' | Edit Deposit';

      $displayData['title']          = CS_PROGNAME.' | Financials';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'financials/deposit_edit_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   function editDepositUpdate($lDepositID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showFinancials')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lDepositID, 'deposit ID');
      
         // models/helpers
      $this->load->model('financials/mdeposits', 'clsDeposits');
      $this->load->model('admin/madmin_aco', 'clsACO');
      
      $this->clsDeposits->strWhereExtra = ' AND dl_lKeyID=-1 ';
      $this->clsDeposits->loadDepositReports();
      $deposit = &$this->clsDeposits->deposits[0];

      $deposit->strBank    = trim($_POST['txtBank']);
      $deposit->strAccount = trim($_POST['txtAccount']);
      $deposit->strNotes   = trim($_POST['txtNotes']);
      $this->clsDeposits->updateDeposit($lDepositID);
      
      redirect('financials/deposit_log/viewEntry/'.$lDepositID);
   }


}
