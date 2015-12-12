<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class clients_via_prog extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($lProgID){
      if (!bTestForURLHack('showClients')) return;
      $displayData = array();
      $displayData['lProgID'] = $lProgID = (integer)$lProgID;

      $this->load->library('util/dl_date_time', '', 'clsDataTime');
      $this->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->load->model('clients/mclients', 'clsClients');
      $this->load->helper('clients/client_sponsor');

      $this->clsSponProg->loadSponProgsViaSPID($lProgID);
      $this->clsSponProg->clientsViaSponProg($lProgID, true, $this->clsClients);

      $displayData['clientInfo'] = $this->clsClients->clients;
      $displayData['lNumClients'] = count($this->clsClients->clients);
      
      $displayData['showFields'] = new stdClass;
      $displayData['showFields']->bLocationDDL    = false; 
      $displayData['showFields']->bClientID       = true; 
      $displayData['showFields']->bName           = true; 
      $displayData['showFields']->bAgeGender      = true; 
      $displayData['showFields']->bLocation       = true; 
      $displayData['showFields']->bStatus         = true; 
      $displayData['showFields']->bSponsors       = true; 
      
         //-----------------------------------
         // set up client report
         //-----------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

      $displayData['strRptTitle'] = 'Sponsors of the <b>"'
                       .htmlspecialchars($this->clsSponProg->sponProgs[0]->strProg).'"</b> program';

      $displayData['showFields']->bSponsorID    = true;
      $displayData['showFields']->bName         = true;
      $displayData['showFields']->bSponsorInfo  = true;
      $displayData['showFields']->bClient       = true;
      $displayData['showFields']->bLocation     = true;
      $displayData['showFields']->bSponAddr     = true;
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']    = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                              .' | '.anchor('client/reports/showReports', 'Reports', 'class="breadcrumb" ')
                              .' | Clients Via Sponsorship Program';

      $displayData['title']        = CS_PROGNAME.' | Clients';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = 'client/rpt_generic_client_list';

      $this->load->vars($displayData);
      $this->load->view('template');
   }
}