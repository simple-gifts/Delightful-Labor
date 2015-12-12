<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class deposit_log extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewEntry($lDepositID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showFinancials')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lDepositID, 'deposit ID');
   
      $displayData = array();
      $displayData['lDepositID'] = $lDepositID = (integer)$lDepositID;

         // models/helpers
      $this->load->helper('dl_util/web_layout');
      $this->load->model('admin/madmin_aco',     'clsACO');
      $this->load->model('financials/mdeposits', 'clsDeposits');
      $this->load->model('donations/mdonations', 'clsGifts');
      
         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;
      
      $this->clsDeposits->strWhereExtra = " AND dl_lKeyID = $lDepositID ";
      $this->clsDeposits->loadDepositReports();
      $displayData['deposit']   = $this->clsDeposits->deposits[0];
      $displayData['lNumGifts'] = $this->clsDeposits->lNumGiftsViaDeposit($lDepositID, $curTot);
      $displayData['curTot']    = $curTot;
      
      $this->clsDeposits->loadGroupedDepositReportsViaDepositID((integer)$lDepositID);
      $displayData['lNumDepositSummary'] = $lNumDepositSummary = $this->clsDeposits->lNumDepositSummary;
      $displayData['depositSummary']     = &$this->clsDeposits->depositSum;
      $displayData['reportID']           = $this->strExportID($lDepositID);
      
         // load gifts based on payment type
      if ($lNumDepositSummary > 0){
         $displayData['gifts'] = array();
         $idx = 0;
         foreach ($this->clsDeposits->depositSum as $dep){
            $displayData['gifts'][$idx] = new stdClass;
            $gInfo = &$displayData['gifts'][$idx];
            $gInfo->safePayType = $dep->strSafePaymentType;
            $this->clsDeposits->loadGiftsViaDIDPayID($lDepositID, $dep->lPaymentType, $gInfo->lNumGifts, $gInfo->gifts);
            
            ++$idx;
         }
      }
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/financials',        'Financials/Grants', 'class="breadcrumb"')
                                .' | '.anchor('financials/deposit_log/view', 'Deposit Log',       'class="breadcrumb"')
                                .' | Deposit Record';

      $displayData['title']          = CS_PROGNAME.' | Financials';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'financials/deposit_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   function view(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showFinancials')) return;
      if (!bTestForURLHack('showReports')) return;
      
      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_DEPOSITLOG,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => true,
                             'viewFile'       => 'pre_generic_rpt_view'
                             );

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);   
   }
   
   private function strExportID($lDepositID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_DEPOSITENTRY,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => true,
                             'viewFile'       => 'pre_generic_rpt_view',
                             'lDepositID'     => $lDepositID
                             );

      $this->clsReports->createReportSessionEntry($reportAttributes);
      return($this->clsReports->sRpt->reportID);
   }
   
   
}