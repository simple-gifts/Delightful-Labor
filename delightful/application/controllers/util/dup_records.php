<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class dup_records extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function opts($enumContext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $displayData['js'] = '';
      $displayData['enumContext'] = $enumContext;

         // models and helpers
      $this->load->helper('dl_util/web_layout');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');

      $displayData['strLabel'] = $strLabel = $this->strContextLabel($enumContext);
      switch ($enumContext){
         case CENUM_CONTEXT_CLIENT:
            if (!bTestForURLHack('showClients')) return;
            $this->load->model('clients/mclients', 'clsClients');
            $displayData['pageTitle'] = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                   .' | Client Duplicates';
            $displayData['title'] = CS_PROGNAME.' | Clients';
            break;

         case CENUM_CONTEXT_PEOPLE:
            if (!bTestForURLHack('editPeopleBizVol')) return;
//            $this->load->helper('dl_util/email_web');
            $this->load->model('people/mpeople', 'clsPeople');
            $displayData['pageTitle'] = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                                   .' | Duplicates People Records';
            $displayData['title'] = CS_PROGNAME.' | People';
            break;

         case CENUM_CONTEXT_BIZ:
            if (!bTestForURLHack('editPeopleBizVol')) return;
            $this->load->model('admin/madmin_aco', 'clsACO');
            $this->load->model('people/mpeople', 'clsPeople');
            $this->load->model('biz/mbiz', 'clsBiz');
            $displayData['pageTitle'] = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                                   .' | Duplicates Business/Organization Records';
            $displayData['title'] = CS_PROGNAME.' | Business';
            break;

         default:
            screamForHelp($enumContext.': invalid context for duplicate record consolidation<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtGoodID', '"Correct" '.$strLabel.' ID',  'trim|required|callback_verifyGoodID['.$enumContext.']');
      $this->form_validation->set_rules('txtBadIDs', 'Duplicate '.$strLabel.' IDs', 'trim|required|callback_verifyDupID['.$enumContext.']');

      if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;

         $this->load->library('generic_form');

         if (validation_errors()==''){
            $displayData['formData']->txtGoodID = '';
            $displayData['formData']->txtBadIDs = '';
         }else{
            setOnFormError($displayData);
            $displayData['formData']->txtGoodID      = set_value('txtGoodID');
            $displayData['formData']->txtBadIDs      = set_value('txtBadIDs');
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'util/dup_rec_opts_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $reportID = $this->strDupRpt($enumContext);
         redirect('util/dup_records/review/'.$reportID);
      }
   }

   function strContextLabel($enumContext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumContext){
         case CENUM_CONTEXT_CLIENT:
            $strOut = 'Client';  break;

         case CENUM_CONTEXT_PEOPLE:
            $strOut = 'People';  break;

         case CENUM_CONTEXT_BIZ:
            $strOut = 'Bus./Org.';  break;

         default:
            screamForHelp($enumContext.': invalid context for duplicate record consolidation<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($strOut);
   }

   function strDupRpt($enumContext){
   //---------------------------------------------------------------------
   // return reportID
   //---------------------------------------------------------------------
      global $gbDateFormatUS;
      $this->load->model('reports/mreports',    'clsReports');

      $dupIDs = explode(',', $_POST['txtBadIDs']);
      foreach ($dupIDs as $idx=>$DCID) $dupIDs[$idx] = (int)(trim($DCID));

      $reportAttributes = array(
                             'rptType'      => CENUM_REPORTTYPE_DATAHIDDEN,
                             'rptName'      => 'Duplicate Record Review',
                             'enumContext'  => $enumContext,
                             'goodID'       => (int)$_POST['txtGoodID'],
                             'dupIDs'       => $dupIDs);

      $this->clsReports->createReportSessionEntry($reportAttributes);
      return($this->clsReports->sRpt->reportID);
   }

      // verification
   function verifyGoodID($strID, $enumContext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbValidGoodID;

      $gbValidGoodID = false;
      if (!is_numeric($strID)){
         $this->form_validation->set_message('verifyGoodID', 'The record ID "'.htmlspecialchars($strID).'" is not valid.');
         return(false);
      }

      switch ($enumContext){
         case CENUM_CONTEXT_CLIENT:
            if ($this->clsClients->bValidClientID((int)$strID)){
               $gbValidGoodID = true;
               return(true);
            }else {
               $this->form_validation->set_message('verifyGoodID', 'The client ID you entered is not valid.');
               return(false);
            }
            break;

         case CENUM_CONTEXT_PEOPLE:
            if ($this->clsPeople->bValidBizPeopleID((int)$strID, false)){
               $gbValidGoodID = true;
               return(true);
            }else {
               $this->form_validation->set_message('verifyGoodID', 'The people ID you entered is not valid.');
               return(false);
            }
            break;

         case CENUM_CONTEXT_BIZ:
            if ($this->clsPeople->bValidBizPeopleID((int)$strID, true)){
               $gbValidGoodID = true;
               return(true);
            }else {
               $this->form_validation->set_message('verifyGoodID', 'The business ID you entered is not valid.');
               return(false);
            }
            break;
         default:
            screamForHelp($enumContext.': invalid context for duplicate record consolidation<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function verifyDupID($strIDs, $enumContext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbValidGoodID;

      $bFailed = false;
      if ($gbValidGoodID){
         $lGoodID = (int)$_POST['txtGoodID'];
      }else {
         $lGoodID = null;
      }

      $strErr = '';
      $dupIDs = explode(',', $strIDs);
      foreach ($dupIDs as $DID){
         $DID = trim($DID);
         if (!is_numeric($DID)){
            $bFailed = true;
            $strErr .= '<li>The record ID <b>"'.htmlspecialchars($DID).'"</b> is not valid.</li>'."\n";
         }else {
            if ($DID == $lGoodID){
               $bFailed = true;
               $strErr .= '<li>You specified a record ID (<b>"'.htmlspecialchars($DID).'"</b>) as both the good and the duplicate ID!</li>'."\n";
            }
         }
      }

      if (!$bFailed){
         foreach ($dupIDs as $DID){
            $DID = (int)$DID;
            switch ($enumContext){
               case CENUM_CONTEXT_CLIENT:
                  if (!$this->clsClients->bValidClientID($DID)){
                     $bFailed = true;
                     $strErr .= '<li>The Client ID <b>'.htmlspecialchars($DID).'</b> is not valid.</li>'."\n";
                  }
                  break;
               case CENUM_CONTEXT_PEOPLE:
                  if (!$this->clsPeople->bValidBizPeopleID($DID, false)){
                     $bFailed = true;
                     $strErr .= '<li>The People ID <b>'.htmlspecialchars($DID).'</b> is not valid.</li>'."\n";
                  }
                  break;
               case CENUM_CONTEXT_BIZ:
                  if (!$this->clsPeople->bValidBizPeopleID($DID, true)){
                     $bFailed = true;
                     $strErr .= '<li>The Business ID <b>'.htmlspecialchars($DID).'</b> is not valid.</li>'."\n";
                  }
                  break;
               default:
                  screamForHelp($enumContext.': invalid context for duplicate record consolidation<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }
         }
      }

      if ($bFailed){
         $this->form_validation->set_message('verifyDupID',
                     'There were problems with the duplicate IDs:'
                    .'<ul style="margin-top: 0px;">'.$strErr.'</ul>');
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
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('reports/report_util');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

      if (!isset($_SESSION[CS_NAMESPACE.'Reports'][$reportID])){
         $this->session->set_flashdata('error', 'The report you requested is no longer available. Please run the report again.');
         redirect_Reports();
      }

      $sRpt = $_SESSION[CS_NAMESPACE.'Reports'][$reportID];
      $displayData['enumContext'] = $enumContext = $sRpt->enumContext;
      $displayData['strLabel']    = $strLabel = $this->strContextLabel($enumContext);
      switch ($enumContext){
         case CENUM_CONTEXT_CLIENT:
            $this->load->model  ('clients/mclients');
            $cGood = new mclients;
            $cGood->loadClientsViaClientID($sRpt->goodID);
            $displayData['lNumGoodClients'] = $cGood->lNumClients;
            $displayData['goodClient']      = $cGood->clients;

            $cDup  = new mclients;
            $cDup->loadClientsViaClientID($sRpt->dupIDs);
            $displayData['lNumDupClients'] = $cDup->lNumClients;
            $displayData['dupClients']     = $cDup->clients;

            $displayData['title']          = CS_PROGNAME.' | Clients';
            $displayData['pageTitle']      = anchor('main/menu/client',         'Clients',           'class="breadcrumb"')
                                      .' | '.anchor('util/dup_records/opts/'.CENUM_CONTEXT_CLIENT, 'Client Duplicates', 'class="breadcrumb"')
                                      .' | Client Duplicates: Review';
            break;

         case CENUM_CONTEXT_PEOPLE:
            $this->load->model('admin/madmin_aco', 'clsACO');
            $this->load->model('people/mpeople',   'clsPeople');
            $cGood = new mpeople;
            $cGood->loadPeopleViaPIDs($sRpt->goodID, false, false);
            $displayData['lNumGoodPeople'] = $cGood->lNumPeople;
            $displayData['goodPeople']     = $cGood->people;

            $cDup  = new mpeople;
            $cDup->loadPeopleViaPIDs($sRpt->dupIDs, false, false);
            $displayData['lNumDupPeople']  = $cDup->lNumPeople;
            $displayData['dupPeople']      = $cDup->people;

            $displayData['title']          = CS_PROGNAME.' | People';
            $displayData['pageTitle']      = anchor('main/menu/people',         'People',           'class="breadcrumb"')
                                      .' | '.anchor('util/dup_records/opts/'.CENUM_CONTEXT_PEOPLE, 'People Duplicates', 'class="breadcrumb"')
                                      .' | Duplicate People Records: Review';
            break;

         case CENUM_CONTEXT_BIZ:
            $this->load->model('admin/madmin_aco', 'clsACO');
            $this->load->model('biz/mbiz', 'clsBiz');
            $cGood = new mbiz;
            $cGood->loadBizRecsViaBID($sRpt->goodID, false, false);
            $displayData['lNumGoodBiz'] = $cGood->lNumBizRecs;
            $displayData['goodBiz']     = $cGood->bizRecs;

            $cDup  = new mbiz;
            $cDup->loadBizRecsViaBID($sRpt->dupIDs, false, false);
            $displayData['lNumDupBiz']  = $cDup->lNumBizRecs;
            $displayData['dupBiz']      = $cDup->bizRecs;

            $displayData['title']          = CS_PROGNAME.' | Businesses/Organizations';
            $displayData['pageTitle']      = anchor('main/menu/biz',         'Business/Organizations',           'class="breadcrumb"')
                                      .' | '.anchor('util/dup_records/opts/'.CENUM_CONTEXT_BIZ, 'Business Duplicates', 'class="breadcrumb"')
                                      .' | Duplicate Business Records: Review';
            break;
         default:
            screamForHelp($enumContext.': invalid context for duplicate record consolidation<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }


         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'util/dup_rec_review_view';

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
      $this->load->model('util/mdup_records',            'cDups');
      $this->load->model('img_docs/mimage_doc',              'clsImgDoc');
      $this->load->helper('img_docs/image_doc');
      $this->load->helper('img_docs/link_img_docs');

      $this->load->helper('reports/report_util');

      if (!isset($_SESSION[CS_NAMESPACE.'Reports'][$reportID])){
         $this->session->set_flashdata('error', 'The report you requested is no longer available. Please run the report again.');
         redirect_Reports();
      }

      $sRpt = $_SESSION[CS_NAMESPACE.'Reports'][$reportID];
      $enumContext = $sRpt->enumContext;

      switch ($enumContext){
         case CENUM_CONTEXT_CLIENT:
            $this->load->model  ('clients/mclients',      'cclients');
            break;
         case CENUM_CONTEXT_PEOPLE:
            $this->load->model('admin/madmin_aco', 'clsACO');
            $this->load->model('people/mpeople', 'clsPeople');
            break;
         case CENUM_CONTEXT_BIZ:
            $this->load->model('admin/madmin_aco', 'clsACO');
            $this->load->model('biz/mbiz', 'clsBiz');
            break;
         default:
            screamForHelp($enumContext.': invalid context for duplicate record consolidation<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

      $lGoodID = $sRpt->goodID;
      $this->cDups->consolidateDup($enumContext, $lGoodID, $sRpt->dupIDs);
      redirect('util/dup_records/complete/'.$reportID);
   }

   function complete($reportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->helper ('reports/report_util');
      $this->load->helper ('dl_util/web_layout');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

      if (!isset($_SESSION[CS_NAMESPACE.'Reports'][$reportID])){
         $this->session->set_flashdata('error', 'The report you requested is no longer available. Please run the report again.');
         redirect_Reports();
      }

      $sRpt = $_SESSION[CS_NAMESPACE.'Reports'][$reportID];
      $enumContext = $sRpt->enumContext;

      switch ($enumContext){
         case CENUM_CONTEXT_CLIENT:
            $this->load->model  ('clients/mclients',      'cclients');
            $this->cclients->loadClientsViaClientID($sRpt->goodID);
            $displayData['client'] = $this->cclients->clients[0];
            $displayData['dupCIDs'] = $sRpt->dupIDs;
            $displayData['pageTitle']      = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                .' | '.anchor('util/dup_records/opts/'.CENUM_CONTEXT_CLIENT, 'Client Duplicates', 'class="breadcrumb"')
                                .' | Client Duplicates: Completed';
            $displayData['title']          = CS_PROGNAME.' | Clients';
            $displayData['mainTemplate']   = 'util/dup_complete_clients_view';
            break;

         case CENUM_CONTEXT_PEOPLE:
//            $this->load->helper('dl_util/email_web');
            $this->load->model('admin/madmin_aco', 'clsACO');
            $this->load->model('people/mpeople', 'clsPeople');
            $this->clsPeople->loadPeopleViaPIDs($sRpt->goodID, false, false);
            $displayData['people'] = $this->clsPeople->people[0];
            $displayData['dupIDs'] = $sRpt->dupIDs;
            $displayData['pageTitle'] = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                                .' | '.anchor('util/dup_records/opts/'.CENUM_CONTEXT_PEOPLE, 'Duplicate People Records', 'class="breadcrumb"')
                                .' | People Duplicates: Completed';
            $displayData['title']          = CS_PROGNAME.' | People';
            $displayData['mainTemplate']   = 'util/dup_complete_people_view';
            break;

         case CENUM_CONTEXT_BIZ:
//            $this->load->helper('dl_util/email_web');
            $this->load->model('admin/madmin_aco', 'clsACO');
            $this->load->model('biz/mbiz', 'clsBiz');
            $this->clsBiz->loadBizRecsViaBID($sRpt->goodID, false, false);
            $displayData['biz'] = $this->clsBiz->bizRecs[0];
            $displayData['dupIDs'] = $sRpt->dupIDs;
            $displayData['pageTitle'] = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                                .' | '.anchor('util/dup_records/opts/'.CENUM_CONTEXT_BIZ, 'Duplicate Business Records', 'class="breadcrumb"')                                .' | Business Duplicates: Completed';
            $displayData['title']          = CS_PROGNAME.' | Businesses';
            $displayData['mainTemplate']   = 'util/dup_complete_biz_view';
            break;
         default:
            screamForHelp($enumContext.': invalid context for duplicate record consolidation<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

}