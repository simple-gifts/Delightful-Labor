<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_spon_via_prog extends CI_Controller {

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
      $this->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->load->model('admin/madmin_aco', 'clsACO');
      $this->load->helper('dl_util/web_layout');
      $this->load->library('generic_form');


      $this->clsSponProg->loadSponProgs();
      $displayData['lNumProgs'] = $this->clsSponProg->lNumSponPrograms;
      $displayData['sponProgs'] = &$this->clsSponProg->sponProgs;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Sponsors Via Program';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_spon_via_prog_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function run(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lSponProgID   = (integer)trim($_POST['ddlSponProg']);
      $bIncludeInactive = @$_POST['chkInactive']=='true';
      $this->runViaProgRpt($lSponProgID, $bIncludeInactive);
   }

   function viaList($lSponProgID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lSponProgID = (integer)$lSponProgID;
      $this->runViaProgRpt($lSponProgID, true);
   }

   function runViaProgRpt($lSponProgID, $bIncludeInactive){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->model('reports/mreports',    'clsReports');
      $reportAttributes = array(
                             'rptType'          => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'          => CENUM_REPORTNAME_SPONVIAPROG,
                             'rptDestination'   => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'        => 0,
                             'lRecsPerPage'     => 50,
                             'bShowRecNav'      => true,
                             'viewFile'         => 'pre_basic_sponsor_list_view',
                             'lSponProg'        => $lSponProgID,
                             'bIncludeInactive' => $bIncludeInactive);

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }

   function viaLocationID($lLocID, $bShowInactive=false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lLocID, 'client location ID');
      $this->load->model('reports/mreports',    'clsReports');
      $reportAttributes = array(
                             'rptType'          => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'          => CENUM_REPORTNAME_SPONVIALOCID,
                             'rptDestination'   => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'        => 0,
                             'lRecsPerPage'     => 50,
                             'bShowRecNav'      => true,
                             'viewFile'         => 'pre_basic_sponsor_list_view',
                             'bShowInactive'    => $bShowInactive,
                             'lLocID'           => $lLocID);

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }

   function runViaLoc(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
/*
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<pre>'); print_r($_POST); echo('</pre></font><br>');
*/
    $this->viaLocationID((integer)$_POST['ddlClientLoc'], @$_POST['chkInactive']=='true');
   }

   function showOptsLoc(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('clients/mclient_locations', 'clsLoc');
      $this->load->helper('dl_util/web_layout');
      $this->load->library('generic_form');


      $this->clsLoc->loadAllLocations();
      $displayData['lNumLocs']        = $this->clsLoc->lNumLocations;
      $displayData['clientLocations'] = &$this->clsLoc->clientLocations;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Sponsors Via Client Location';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_spon_via_loc_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }



}