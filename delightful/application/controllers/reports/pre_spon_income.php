<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_spon_income extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function showOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glChapterID;

      $displayData = array();
      $displayData['formData'] = new stdClass;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->model('admin/madmin_aco', 'clsACO');
      $this->load->helper('dl_util/web_layout');
      $this->load->library('generic_form');
      $this->load->model('admin/morganization', 'clsChapter');

      $this->clsChapter->lChapterID = $glChapterID;
      $this->clsChapter->loadChapterInfo();
      $displayData['formData']->strACORadio   = $this->clsACO->strACO_Radios ($this->clsChapter->chapterRec->lDefaultACO, 'rdoACO');

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Sponsorship Income';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_spon_income_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function run(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();

      $lYear   = $displayData['lYear'] = (integer)trim($_POST['ddlYear']);
      $lACOID  = (integer)trim($_POST['rdoACO']);

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('sponsorship/msponsorship',       'clsSpon');
      $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->model('admin/madmin_aco',                'clsACO');
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/web_layout');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

         //------------------------------------------------
         // ACO Info
         //------------------------------------------------
      $this->clsACO->loadCountries(false, true, true, $lACOID);
      $displayData['cACO'] = &$this->clsACO->countries[0];

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

         //------------------------------------------------
         // load a year's worth of charges/payments
         //------------------------------------------------
      $this->clsSCP->bUseDateRange = true;
      $income = array();
      for ($idx=1; $idx<=12; ++$idx){
         $income[$idx] = new stdClass;
         $this->clsSCP->dteStart = dteMonthStart($idx, $lYear);
         $this->clsSCP->dteEnd   = dteMonthEnd  ($idx, $lYear);

         $income[$idx]->curCharge = $this->clsSCP->curCumulativeChargeVia_ACOID(null, $lACOID);
         $income[$idx]->curPay    = $this->clsSCP->curCumulativeSponVia_ACOID(false, null, false, null, $lACOID);
      }
      $displayData['income'] = &$income;
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | '.anchor('reports/pre_spon_income/showOpts', 'Sponsorship Income',
                                               'class="breadcrumb"')
                                .' | View Report';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_spon_income_rpt_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function monthlyPayment($lMonth, $lYear, $lACOID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->monthlyPaymentCharge($lMonth, $lYear, $lACOID, true);
   }
   function monthlyCharge($lMonth, $lYear, $lACOID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->monthlyPaymentCharge($lMonth, $lYear, $lACOID, false);   
   }
   
   function monthlyPaymentCharge($lMonth, $lYear, $lACOID, $bPayment){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lMonth = (integer)$lMonth;
      $lYear  = (integer)$lYear;
      $lACOID = (integer)$lACOID;
      
      $enumRpt = $bPayment ? CENUM_REPORTNAME_SPONINCOMEMONTH : CENUM_REPORTNAME_SPONCHARGEMONTH;

      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'          => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'          => $enumRpt,
                             'rptDestination'   => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'        => 0,
                             'lRecsPerPage'     => 50,
                             'bShowRecNav'      => true,
                             'viewFile'         => 'pre_generic_rpt_view',
                             'lYear'            => $lYear,
                             'lMonth'           => $lMonth,
                             'lACOID'           => $lACOID);

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }



}