<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class auto_charge extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function applyChargesOpts(){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      if (!bTestForURLHack('showSponsorFinancials')) return;
      $displayData = array();

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->helper('dl_util/time_date');

      $this->clsSCP->twelveMonthsOfAutoCharge();
      $displayData['autoCharges12Mo'] = &$this->clsSCP->autoCharges12Mo;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                                .' | Auto-Charges';

      $displayData['title']          = CS_PROGNAME.' | Sponsorship';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'sponsorship/auto_charge_opts_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addAutoCharge($lMonth, $lYear){
      if (!bTestForURLHack('showSponsorFinancials')) return;
      $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->helper('dl_util/time_date');

      $lMonth = (integer)$lMonth;
      $lYear  = (integer)$lYear;

      $lAutoChargeID = $this->clsSCP->lApplyAutoCharges($lMonth, $lYear);
      $this->session->set_flashdata('msg', 'Auto-charges were applied for '.$lMonth.'/'.$lYear);

      redirect_SponsorAutoChargeEntry($lAutoChargeID);
   }

   function autoChargeRecord($lAutoChargeID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showSponsorFinancials')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lAutoChargeID, 'autocharge ID');
   
      $displayData = array();

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('sponsorship/msponsorship',      'clsSpon');
      $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->helper('dl_util/web_layout');
      $this->load->model('admin/madmin_aco',               'clsACO');

      $clsACEntry = new stdClass;
      $this->clsSCP->loadAutoChargeViaACID($lAutoChargeID, $clsACEntry);
      $displayData['lMonth'] = $clsACEntry->lMonth;
      $displayData['lYear']  = $clsACEntry->lYear;

      $this->clsSCP->loadChargesViaACID($lAutoChargeID, $displayData['autoChargeInfo'], $displayData['lNumAutoCharges']);

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['contextSummary'] = $this->clsSCP->autoChargeHTMLSummary($clsACEntry);

      $displayData['mainTemplate'] = array('sponsorship/auto_charge_record_view');
      $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                              .' | AutoCharge Record';

      $displayData['title']        = CS_PROGNAME.' | Sponsorship';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }


}