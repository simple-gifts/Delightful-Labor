<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_vol_jobcodes extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function showOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper ('dl_util/web_layout');
      $this->load->library('generic_form');
      $this->load->model('util/mlist_generic',    'clsList');

      $this->clsList->enumListType = CENUM_LISTTYPE_VOLJOBCODES;
      $this->clsList->strBlankDDLName = '(all job codes)';
      $displayData['strDDLJobCode'] = $this->clsList->strLoadListDDL('ddlJobCode', true, -1);


         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Volunteer Job Codes by Year';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_vol_jobcode_year_view.php';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function jcYearRun(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lYear = (int)$_POST['ddlYear'];
      $lJobCodeID = (int)$_POST['ddlJobCode'];

      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_VOL_JOBCODE_YEAR,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => false,
                             'lYear'          => $lYear,
                             'lJobCodeID'     => $lJobCodeID,
                             'viewFile'       => 'pre_generic_rpt_view');

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }
   
   function jcMonthDetailRun($lYear, $lMonth, $lJobCodeID){
   //---------------------------------------------------------------------
   // 
   //---------------------------------------------------------------------   
      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_VOL_JOBCODE_MONTH,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => false,
                             'lYear'          => (int)$lYear,
                             'lMonth'         => (int)$lMonth,
                             'lJobCodeID'     => (int)$lJobCodeID,
                             'viewFile'       => 'pre_generic_rpt_view');

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);   
   }

}