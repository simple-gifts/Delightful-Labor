<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class provider_directory extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewProDirectory($bHide=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showGrants')) return;

      $displayData = array();
      $displayData['js'] = '';

         //--------------------------------
         // models/helpers
         //--------------------------------
//      $this->load->model ('admin/madmin_aco', 'cACO');
      $this->load->model ('grants/mgrants',   'cgrants');
      $this->load->model ('admin/madmin_aco', 'clsACO');
      $this->load->helper('grants/link_grants');

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;


      if ($bHide){
         $this->cgrants->loadGrantProvidersNotHidden('', '', $displayData['lNumProviders'], $displayData['providers']);
      }else {
         $this->cgrants->loadGrants('', '', $displayData['lNumProviders'], $displayData['providers']);
      }
      $lNumProviders = $displayData['lNumProviders'];
      
      
      
      
      if ($lNumProviders > 0){
         for ($idx = 0; $idx < $lNumProviders; ++$idx){
            $lProviderID = $displayData['providers'][$idx]->lKeyID;
            $this->cgrants->loadGrantsViaProviderID($lProviderID, '', '', 
                      $displayData['providers'][$idx]->lNumGrants, 
                      $displayData['providers'][$idx]->grants);
         }
      }
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$displayData[providers]   <pre>');
echo(htmlspecialchars( print_r($displayData['providers'], true))); echo('</pre></font><br>');
/* -------------------------------------
// ------------------------------------- */

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/financials',        'Financials/Grants', 'class="breadcrumb"')
                                .' | Grant Provider Directory';

      $displayData['title']          = CS_PROGNAME.' | Grants';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'grants/provider_directory_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }




}