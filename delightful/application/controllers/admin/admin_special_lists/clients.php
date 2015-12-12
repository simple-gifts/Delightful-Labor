<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class clients extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function index(){
   }

   public function locationView(){
      if (!bTestForURLHack('adminOnly')) return;
      
      $displayData = array();
      $displayData['title']        = CS_PROGNAME.' | Client Locations';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                              .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                              .' | Client Locations';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->model('clients/mclient_locations',     'clsLocation');
      $this->load->model('sponsorship/msponsorship_programs', 'clsSP');
      $this->load->helper('clients/client');
      
         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

      $this->clsLocation->loadAllLocations();
      $displayData['locations'] = $this->clsLocation->clientLocations;
      $displayData['lNumLocs']  = $lNumLocs = $this->clsLocation->lNumLocations;
      
      
      if ($lNumLocs > 0){
         foreach ($displayData['locations'] as $clsLoc){
            $lCLID = $clsLoc->lKeyID;
            $clsLoc->lNumSponsors = $this->clsLocation->lNumActiveSponsViaLocID($lCLID);

            $this->clsLocation->loadSupportedSponCats($lCLID, $this->clsSP);
            $clsLoc->strSponProg = '';
            if ($this->clsSP->lNumSponPrograms == 0){
               $clsLoc->strSponProg .= '<i>Not participating in any sponsorship programs</i>';
            }else {
               $clsLoc->strSponProg .=
                          '<i>participating in these sponsorship programs:<ul>';
               foreach ($this->clsSP->sponProgs as $clsSupportedSC){
                  $clsLoc->strSponProg .=  '<li>'.htmlspecialchars($clsSupportedSC->strProg).'</li>';
               }
               $clsLoc->strSponProg .=  '</ul>';
            }
            $clsLoc->lNumberClients = $this->clsLocation->lNumClientsViaLocation($lCLID);
         }
      }

      $displayData['mainTemplate'] = 'admin/alist_client_loc_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   public function statCatView(){
      if (!bTestForURLHack('adminOnly')) return;
      
      $displayData = array();

         //----------------------------
         // the stripe-a-tizer
         //----------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

         //----------------------------------
         // client and status category info
         //----------------------------------
      $this->load->helper('clients/client');
      $this->load->model('clients/mclient_status', 'clsClientStat');
      $this->clsClientStat->loadClientStatCats(true, false, null);
      $displayData['numStatCat']   = $this->clsClientStat->lNumStatCats;
      $displayData['statCats']     = $this->clsClientStat->statCats;
      foreach ($displayData['statCats'] as $clsCat){
         $lKeyID = $clsCat->lKeyID;
         $this->clsClientStat->loadClientStatCatsEntries(
                               true,  $lKeyID,
                               false, null,
                               true,  false);

         $displayData['statCatsEntries'][$lKeyID] = $this->clsClientStat->catEntries;
      }

      $displayData['title']        = CS_PROGNAME.' | Client Status Categories';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                              .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                              .' | Client Status Categories';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate'] = 'admin/alist_client_status_cat_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   public function statCatEntries($id){
   //-------------------------------------------------------------------
   // display the individual status entries that belong to a status
   // category ($id is the key ID of the status category)
   //-------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      
      $displayData = array();
      $displayData['id']           = $id;
      $displayData['title']        = CS_PROGNAME.' | Client Status Category Entries';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                              .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                              .' | '.anchor('admin/admin_special_lists/clients/statCatView',
                                              'Client Status Categories', 'class="breadcrumb"')
                              .' | Status Entries';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

         //----------------------------
         // the stripe-a-tizer
         //----------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;
      
      $this->load->model('clients/mclient_status', 'clsClientStat');
      $this->clsClientStat->loadClientStatCats(false, true, $id);
      $displayData['strStatCat'] = htmlspecialchars($this->clsClientStat->statCats[0]->strCatName);

      $this->clsClientStat->loadClientStatCatsEntries(
                            true,  $id,
                            false, null,
                            true,  false);
      $displayData['lNumCatEntries'] = $this->clsClientStat->lNumCatEntries;

      $displayData['statCatsEntries'] = $this->clsClientStat->catEntries;

      $displayData['mainTemplate'] = 'admin/alist_client_status_entry_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   public function vocView(){
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();
      $displayData['title']        = CS_PROGNAME.' | Client Vocabulary';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                              .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                              .' | Client Vocabulary';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->model('util/mbuild_on_ready', 'clsOnReady');

      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

      $this->load->model('clients/mclient_vocabulary', 'clsClientV');
      $this->clsClientV->loadClientVocabulary(false, true);

      $displayData['lNumVocs'] = $lNum = $this->clsClientV->lNumVocs;
      if ($lNum > 0) {
         $displayData['clsVocs'] = $this->clsClientV->vocs;
         $this->load->helper('clients/client_voc');
      }

      $displayData['mainTemplate'] = 'admin/alist_client_voc_view';
      $this->load->vars($displayData);
      $this->load->view('template');

   }
}