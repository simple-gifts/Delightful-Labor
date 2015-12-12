<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_spon_wo_clients extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function rpt(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_SPONWOCLIENT,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => true,
                             'viewFile'       => 'pre_basic_sponsor_list_view',
                             );

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }

}
