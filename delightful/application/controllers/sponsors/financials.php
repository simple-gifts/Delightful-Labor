<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class financials extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewSponsorFinancials($lSponID){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      if (!bTestForURLHack('showSponsorFinancials')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lSponID,   'sponsor ID');
   
      $displayData = array();
      $displayData['lSponID'] = $lSponID = (integer)$lSponID;
      
         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      $clsRpt = new generic_rpt($params);
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('sponsorship/msponsorship',      'clsSpon');
      $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->model('admin/madmin_aco',               'clsACO');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/record_view');
      $this->load->helper('img_docs/link_img_docs');
      $this->load->helper('dl_util/time_date');
      
      $this->clsSpon->sponsorInfoViaID($lSponID);
      $displayData['lSponFID']       = $this->clsSpon->sponInfo[0]->lForeignID;
      $displayData['contextSummary'] = $this->clsSpon->sponsorshipHTMLSummary();

      
         //--------------------------
         // load financial history
         //--------------------------
      $this->clsSCP->bUseDateRange = false;
      $this->clsSCP->lSponID       = $lSponID;

      $this->clsSCP->cumulativeChargeHistory($lSponID);
      $displayData['lNumCharges'] = $this->clsSCP->lNumCharges;
      $displayData['charges']     = &$this->clsSCP->charges;
      
      $this->clsSCP->sponsorPaymentHistory(true);
      $displayData['lNumPayments']    = $this->clsSCP->lPayTot;
      $displayData['payHistory']      = &$this->clsSCP->payHistory;
      
      $displayData['strFinancialSum'] = strSponsorFinancialSummary($clsRpt, $this->clsSCP, $lSponID, '70pt');
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                              .' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$lSponID, 'Sponsorship Record', 'class="breadcrumb"')
                              .' | Financials';
      $displayData['title']          = CS_PROGNAME.' | Sponsorship';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'sponsorship/financials_view';
      $this->load->vars($displayData);
      $this->load->view('template');
      
      
   }
   
   
   
}
