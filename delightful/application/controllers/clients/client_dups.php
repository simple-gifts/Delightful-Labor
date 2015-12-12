<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class client_dups extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function opts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;

         // models and helpers
      $this->load->helper('dl_util/web_layout');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('clients/mclients', 'clsClients');

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtGoodCID', '"Correct" Client ID',  'trim|required|callback_verifyGoodCID');
      $this->form_validation->set_rules('txtBadCIDs', 'Duplicate Client IDs', 'trim|required|callback_verifyDupCID');

      if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;

         $this->load->library('generic_form');

         if (validation_errors()==''){
            $displayData['formData']->txtGoodCID = '';
            $displayData['formData']->txtBadCIDs = '';
         }else{
            setOnFormError($displayData);
            $displayData['formData']->txtGoodCID      = set_value('txtGoodCID');
            $displayData['formData']->txtBadCIDs      = set_value('txtBadCIDs');
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                   .' | Client Duplicates';

         $displayData['title']          = CS_PROGNAME.' | Clients';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'client/client_dup_opts_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $reportID = $this->strClientDupRpt();
         redirect('clients/client_dups/review/'.$reportID);
      }
   }
   
   function strClientDupRpt(){
   //---------------------------------------------------------------------
   // return reportID
   //---------------------------------------------------------------------
      global $gbDateFormatUS;
      $this->load->model('reports/mreports',    'clsReports');
      
      $dupCIDs = explode(',', $_POST['txtBadCIDs']);
      foreach ($dupCIDs as $DCID) $DCID = (int)(trim($DCID));

      $reportAttributes = array(
                             'rptType'  => CENUM_REPORTTYPE_DATAHIDDEN,
                             'rptName'  => 'Duplicate Client Review',
                             'goodCID'  => (int)$_POST['txtGoodCID'],
                             'dupCIDs'  => $dupCIDs);

      $this->clsReports->createReportSessionEntry($reportAttributes);
      return($this->clsReports->sRpt->reportID);
   }
   

      // verification
   function verifyGoodCID($strCID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbValidGoodCID;
      if ($this->clsClients->bValidClientID((int)$strCID)){
         $gbValidGoodCID = true;
         return(true);
      }else {
         $gbValidGoodCID = false;
         $this->form_validation->set_message('verifyGoodCID', 'The client ID you entered is not valid.');
         return(false);
      }
   }

   function verifyDupCID($strIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbValidGoodCID;

      $bFailed = false;
      if ($gbValidGoodCID){
         $lGoodCID = (int)$_POST['txtGoodCID'];
      }else {
         $lGoodCID = null;
      }
      $strErr = '';
      $clientIDs = explode(',', $strIDs);
      foreach ($clientIDs as $CID){
         $CID = trim($CID);
         if (!is_numeric($CID)){
            $bFailed = true;
            $strErr .= '* The Client ID "'.htmlspecialchars($CID).'" is not valid.<br>'."\n";
         }else {
            $lCID = (int)$CID;
            if (!$this->clsClients->bValidClientID($lCID)){
               $bFailed = true;
               $strErr .= '* The Client ID '.htmlspecialchars($CID).' is not valid.<br>'."\n";
            }else {
               if ($lCID == $lGoodCID){
                  $bFailed = true;
                  $strErr .= '* You specified a client ID as both the good and the duplicate ID!<br>'."\n";
               }
            }
         }
      }
      if ($bFailed){
         $this->form_validation->set_message('verifyDupCID', 
                     'There were problems with the duplicate Client IDs:<br>'.$strErr);
         return(false);      
      }else {
         return(true);
      }
   }

   function review($reportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $displayData['js'] = '';
      $displayData['reportID'] = $reportID;

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $this->load->model  ('reports/mreports', 'clsReports');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model  ('clients/mclients');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper('reports/report_util');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

      if (!isset($_SESSION[CS_NAMESPACE.'Reports'][$reportID])){
         $this->session->set_flashdata('error', 'The report you requested is no longer available. Please run the report again.');
         redirect_Reports();
      }

      $sRpt = $_SESSION[CS_NAMESPACE.'Reports'][$reportID];

      $cGood = new mclients;
      $cGood->loadClientsViaClientID($sRpt->goodCID);
      $displayData['lNumGoodClients'] = $cGood->lNumClients;
      $displayData['goodClient']      = $cGood->clients;
      
      $cDup  = new mclients;
      $cDup->loadClientsViaClientID($sRpt->dupCIDs);
      $displayData['lNumDupClients'] = $cDup->lNumClients;
      $displayData['dupClients']     = $cDup->clients;
 
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/client',         'Clients',           'class="breadcrumb"')
                                .' | '.anchor('clients/client_dups/opts', 'Client Duplicates', 'class="breadcrumb"')
                                .' | Client Duplicates: Review';

      $displayData['title']          = CS_PROGNAME.' | Clients';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'client/client_dup_review_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   function consolidate($reportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $displayData['js'] = '';
      $displayData['reportID'] = $reportID;

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $this->load->model('reports/mreports',             'clsReports');      
      $this->load->model('personalization/muser_schema', 'cUFSchema');      
      $this->load->model('clients/mclient_dups',         'cDups');
      
      $this->load->helper('reports/report_util');

      if (!isset($_SESSION[CS_NAMESPACE.'Reports'][$reportID])){
         $this->session->set_flashdata('error', 'The report you requested is no longer available. Please run the report again.');
         redirect_Reports();
      }

      $sRpt = $_SESSION[CS_NAMESPACE.'Reports'][$reportID];
      
      $lGoodCID = $sRpt->goodCID;
      foreach ($sRpt->dupCIDs as $lDupCID){
         $this->cDups->consolidateDup($lGoodCID, $lDupCID);
      }
      redirect('clients/client_dups/complete/'.$reportID);
   }
   
   function complete($reportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model  ('clients/mclients',      'cclients');
      $this->load->helper ('reports/report_util');
      $this->load->helper ('dl_util/web_layout');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

      if (!isset($_SESSION[CS_NAMESPACE.'Reports'][$reportID])){
         $this->session->set_flashdata('error', 'The report you requested is no longer available. Please run the report again.');
         redirect_Reports();
      }

      $sRpt = $_SESSION[CS_NAMESPACE.'Reports'][$reportID];
      
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$sRpt   <pre>');
echo(htmlspecialchars( print_r($sRpt, true))); echo('</pre></font><br>');
// ------------------------------------- */

      $this->cclients->loadClientsViaClientID($sRpt->goodCID);
      $displayData['client'] = $this->cclients->clients[0];
      $displayData['dupCIDs'] = $sRpt->dupCIDs;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                .' | '.anchor('clients/client_dups/opts', 'Client Duplicates', 'class="breadcrumb"')
                                .' | Client Duplicates: Completed';

      $displayData['title']          = CS_PROGNAME.' | Clients';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'client/client_dup_complete_view';
      $this->load->vars($displayData);
      $this->load->view('template');


   }

}