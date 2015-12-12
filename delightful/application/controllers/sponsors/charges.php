<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class charges extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }


   function addEditCharge($lSponID, $lChargeID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gclsChapterACO, $gbDateFormatUS, $gstrFormatDatePicker;
      
      if (!bTestForURLHack('showSponsorFinancials')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lSponID,   'sponsor ID');
      if ($lChargeID.'' != '0') verifyID($this, $lChargeID, 'sponsorship charge ID');

      $displayData = array();
      $displayData['lSponID']   = $lSponID   = (integer)$lSponID;
      $displayData['lChargeID'] = $lChargeID = (integer)$lChargeID;
      $displayData['bNew']      = $bNew      = $lChargeID <= 0;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
      $this->load->model('sponsorship/msponsorship',      'clsSpon');
      $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->model('admin/madmin_aco',               'clsACO');
      $this->load->helper ('dl_util/time_date');

      $this->clsSCP->loadChargeRecordViaCRID($lChargeID);
      $cRec = &$this->clsSCP->chargeRec[0];

      $this->clsSpon->sponsorInfoViaID($lSponID);

         //--------------------------
         // validation rules
         //--------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
 		$this->form_validation->set_rules('txtAmount', 'Amount',  'trim|required|callback_stripCommas|numeric|greater_than[-0.01]');
      $this->form_validation->set_rules('rdoACO',  'Accounting Country', 'trim');
      $this->form_validation->set_rules('txtChargeDate',  'Date of Charge',
                                                             'trim|required|callback_sponChargeAddEditDateValid');

      if ($this->form_validation->run() == FALSE){
         $displayData['clsACO'] = &$this->clsACO;
         $this->load->library('generic_form');
         $this->load->helper('dl_util/web_layout');
         $displayData['clsForm']     = &$this->generic_form;

         if (validation_errors()==''){
            if ($bNew){
               $displayData['strChargeDate']  = date($gstrFormatDatePicker, $gdteNow);
               $displayData['lChargeACO']     = $this->clsSpon->sponInfo[0]->lCommitACO;
               $displayData['strAmount']      = number_format($this->clsSpon->sponInfo[0]->curCommitment, 2);
            }else {
               $displayData['strChargeDate']  = date($gstrFormatDatePicker, $cRec->dteCharge);
               $displayData['lChargeACO']     = $cRec->lACOID;
               $displayData['strAmount']      = number_format($cRec->curChargeAmnt, 2);
            }
         }else {
            setOnFormError($displayData);
            $displayData['strChargeDate'] = set_value('txtChargeDate');
            $displayData['lChargeACO']    = set_value('rdoACO');
            $displayData['strAmount']     = set_value('txtAmount');
         }
            //------------------------------------------------
            // breadcrumbs / page setup
            //------------------------------------------------
         $displayData['contextSummary'] = $this->clsSpon->sponsorshipHTMLSummary();

         $displayData['mainTemplate'] = array('sponsorship/charge_add_edit_view');
         $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                                 .' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$lSponID, 'Sponsorship Record', 'class="breadcrumb"')
                                 .' | '.($bNew ? 'Add ' : 'Update ').'Sponsorship Charge';


         $displayData['title']        = CS_PROGNAME.' | Sponsorship';
         $displayData['nav']          = $this->mnav_brain_jar->navData();
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->load->helper('dl_util/util_db');

         $strChargeDate   = trim($_POST['txtChargeDate']);
         MDY_ViaUserForm($strChargeDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $cRec->dteCharge      = strtotime($lMon.'/'.$lDay.'/'.$lYear);
         $cRec->curChargeAmnt  = (float)trim($_POST['txtAmount']);
         $cRec->lACOID         = (integer)$_REQUEST['rdoACO'];
         $cRec->lSponsorshipID = $lSponID;

         if ($bNew){
            $lChargeID = $this->clsSCP->lAddNewChargeRec();
            $this->session->set_flashdata('msg', 'Sponsorship charge was added');
         }else {
            $this->clsSCP->updateChargeRec($lChargeID);
            $this->session->set_flashdata('msg', 'Sponsorship charge was updated');
         }
         redirect_SponsorshipChargeRec($lChargeID);
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
   function sponChargeAddEditDateValid($strDate){
      return(bValidVerifyDate($strDate));
   }
   
   function stripCommas(&$strAmount){
      $strAmount = str_replace (',', '', $strAmount);
      return(true);
   }

   function viewChargeRec($lChargeID){
      if (!bTestForURLHack('showSponsors')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lChargeID, 'sponsorship charge ID');
   
      $displayData = array();
      $displayData['lChargeID']   = $lChargeID = (integer)$lChargeID;

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('sponsorship/msponsorship',      'clsSpon');
      $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->model('admin/madmin_aco',               'clsACO');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/record_view');
      $this->load->helper('img_docs/link_img_docs');
      $this->load->helper('dl_util/time_date');

      $this->clsSCP->loadChargeRecordViaCRID($lChargeID);
      $displayData['cRec']   = $cRec = &$this->clsSCP->chargeRec[0];
      $displayData['lSponID']   = $lSponID = $cRec->lSponsorshipID;

      $this->clsSpon->sponsorInfoViaID($lSponID);
      $displayData['contextSummary'] = $this->clsSpon->sponsorshipHTMLSummary();

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                              .' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$lSponID, 'Sponsorship Record', 'class="breadcrumb"')
                              .' | Charge Record';
      $displayData['title']          = CS_PROGNAME.' | Sponsorship';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'sponsorship/charge_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function remove($lSponID, $lChargeID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      if (!bTestForURLHack('showSponsorFinancials')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lSponID,   'sponsor ID');
      verifyID($this, $lChargeID, 'sponsorship charge ID');
   
      $lSponID = (integer)$lSponID;
      $lChargeID  = (integer)$lChargeID;
   
      $this->load->model('sponsorship/msponsorship',      'clsSpon');
      $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->model('admin/madmin_aco',               'clsACO');
   
//      $clsSCP->lPaymentID = $lPayID;
//      $this->clsSCP->loadPayRecordViaPayID($lPayID);
   
      $this->clsSCP->removeChargeRecord($lChargeID);
      $strMsg = 'Sponsor charge record '.str_pad($lChargeID, 5, '0', STR_PAD_LEFT)
               .' was removed.';
      
      $this->session->set_flashdata('msg', $strMsg);   
      redirect_SponsorshipRecord($lSponID);   
   
   }







}