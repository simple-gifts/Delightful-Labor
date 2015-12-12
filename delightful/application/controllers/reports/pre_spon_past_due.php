<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_spon_past_due extends CI_Controller {

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
                                .' | Sponsor Past Due Report';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_spon_past_due_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function run(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->model('reports/mreports',    'clsReports');

      $lMonthsPastDue   = (integer)trim($_POST['ddlPastDue']);
      $lACOID           = (integer)trim($_POST['rdoACO']);
      $bIncludeInactive = @$_POST['chkInactive']=='true';

      $reportAttributes = array(
                             'rptType'          => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'          => CENUM_REPORTNAME_SPONPASTDUE,
                             'rptDestination'   => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'        => 0,
                             'lRecsPerPage'     => 50,
                             'bShowRecNav'      => true,
                             'viewFile'         => 'pre_spon_past_due_rpt_view',
                             'lMonthsPastDue'   => $lMonthsPastDue,
                             'lACOID'           => $lACOID,
                             'bIncludeInactive' => $bIncludeInactive);

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);

   }






}