<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cprog_dir extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function cprogList(){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $glUserID;

      if (!bTestForURLHack('showClients')) return;

      $displayData = array();
      $displayData['js'] = '';

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('client_features/mcprograms',       'cprograms');
      $this->load->model ('admin/mpermissions',               'perms');
      $this->load->model ('personalization/muser_fields',     'clsUF');
      $this->load->model ('util/mbuild_on_ready',             'clsOnReady');
      $this->load->helper('clients/link_client_features');
      $this->load->helper('dl_util/web_layout');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //--------------------------
         // load the client programs
         //--------------------------
      $this->cprograms->loadClientPrograms(false);
      $lNumCProgs = $this->cprograms->lNumCProgs;
      $displayData['cprogs'] = &$this->cprograms->cprogs;
      $displayData['lNumCProgs'] = 0;
      $this->perms->loadUserAcctInfo($glUserID, $acctAccess);

      if ($lNumCProgs > 0){
         foreach ($this->cprograms->cprogs as $cprog){
            $cprog->bShowCProgLink = $this->perms->bDoesUserHaveAccess(
                                          $acctAccess, $cprog->lNumPerms, $cprog->perms);
            if ($cprog->bShowCProgLink){
               ++$displayData['lNumCProgs'];
               $cprog->lNumEnrolledTot     = $this->cprograms->lNumEnrollmentsViaCProg($cprog, false);
               $cprog->lNumEnrolledCurrent = $this->cprograms->lNumEnrollmentsViaCProg($cprog, true);

               $cprog->lNumClientsTot      = $this->cprograms->lNumClientsViaCProg($cprog, false);
               $cprog->lNumClientsCurrent  = $this->cprograms->lNumClientsViaCProg($cprog, true);
               $cprog->dTotHrsLogged       = $this->cprograms->dHoursLogged($cprog, true, $cprog->hourInfo);
            }
         }
      }

         //--------------------------
         // Stripes
         //--------------------------
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/client',              'Clients',           'class="breadcrumb"')
                                .' | Client Programs';

      $displayData['title']          = CS_PROGNAME.' | Client Programs';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'cprograms/cprograms_directory_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function viewEnroll($lCProgID, $bActiveOnly){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      if (!bTestForURLHack('showClients')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lCProgID, 'client program ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lCProgID']    = $lCProgID = (int)$lCProgID;
      $displayData['bActiveOnly'] = $bActiveOnly = $bActiveOnly=='true';

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('client_features/mcprograms',      'cprograms');
      $this->load->model ('personalization/muser_fields',    'clsUF');
      $this->load->model ('admin/mpermissions',              'perms');
      $this->load->model ('util/mbuild_on_ready',            'clsOnReady');
      $this->load->model ('clients/mclients',                'clsClients');
      $this->load->helper('clients/link_client_features');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
            
         //--------------------------
         // Stripes
         //--------------------------
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;      

         //-------------------------
         // load the client program
         //-------------------------
      $this->cprograms->loadClientProgramsViaCPID($lCProgID);
      $displayData['cprog'] = $cprog = &$this->cprograms->cprogs[0];
      $displayData['strHTMLSummary'] = $this->cprograms->strHTMLProgramSummaryDisplay(CENUM_CONTEXT_CPROGENROLL);

      $this->perms->loadUserAcctInfo($glUserID, $acctAccess);
      if (!$this->perms->bDoesUserHaveAccess($acctAccess, $cprog->lNumPerms, $cprog->perms)){
         badBoyRedirect('You do not have access to this client program.');
         return;      
      }
      
      
         // load the clients associated with this program
      $this->cprograms->clientsEnrolledViaProgID($lCProgID, $cprog, $bActiveOnly,
               $displayData['lNumClients'], $displayData['clients']);
      if ($displayData['lNumClients'] > 0){
         foreach ($displayData['clients'] as $client){
            $lCID = $client->lClientID;
            $this->cprograms->loadBaseERecViaProgClientID($lCID, $cprog, $client->lNumEnrollments, $client->erecs);
            
               // load sponsorship info
            $this->clsClients->loadSponsorshipInfo($lCID, false, $client->lNumSponsors, $client->sponsors);
            
               // load cumulative days and hours
            if ($client->lNumEnrollments > 0){
               foreach ($client->erecs as $erec){
                  $lEnrollID = $erec->lKeyID;
                  $erec->lNumDaysAttended = $this->cprograms->lDaysViaEnrollID($cprog, $lEnrollID);
                  $erec->sngNumHours      = $this->cprograms->sngHoursViaEnrollID($cprog, $lEnrollID);
               }
            }
         }
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/client',  'Clients',   'class="breadcrumb"')
                                .' | '.anchor('cprograms/cprog_dir/cprogList',  'Client Programs',  'class="breadcrumb"')
                                .' | '.htmlspecialchars($cprog->strProgramName);

      $displayData['title']          = CS_PROGNAME.' | Client Programs';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'cprograms/cprograms_enrollment_dir_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }




}
