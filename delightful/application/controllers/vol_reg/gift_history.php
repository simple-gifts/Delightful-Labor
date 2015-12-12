<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class gift_history extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function view(){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $gbVolLogin, $gVolPerms, $glVolPeopleID;
      
      $lPID = $glVolPeopleID;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPID, 'people ID');
      
      $displayData = array();
      $displayData['lPID'] = $lPID = (integer)$lPID;
      $displayData['js'] = '';
      
         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model ('people/mpeople',          'clsPeople');
      $this->load->model ('donations/mhon_mem');
      $this->load->model ('admin/madmin_aco',        'clsACO');
      $this->load->model ('util/mbuild_on_ready',    'clsOnReady');
      $this->load->model ('admin/muser_accts',       'clsUser');      
      $this->load->model ('donations/mdonations',    'clsGifts');
      
         // load people record
      $this->clsPeople->loadPeopleViaPIDs($lPID, false, false);
      $displayData['pRec'] = &$this->clsPeople->people[0];
      
         // Stripes
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;
      
      
         // load donation history via ACO
      $idx = 0;
      $this->clsACO->loadCountries(false, true, false, null);
      $displayData['ghACO'] = $displayData['lNumGiftsGH'] = $displayData['giftHistory'] = array();
      $displayData['lTotGifts'] = 0;
      foreach ($this->clsACO->countries as $clsCountry){
         $lACOID     = $clsCountry->lKeyID;
         $strFlagImg = $clsCountry->strFlagImg;

         $this->clsGifts->loadGiftHistory($lPID, 'date', $lACOID, $this->clsACO,
                                          $displayData['lNumGiftsGH'][$idx],
                                          $displayData['giftHistory'][$idx]);
         $displayData['lTotGifts'] += $displayData['lNumGiftsGH'][$idx];
         $displayData['ghACO'][$idx] = new stdClass;
         $displayData['ghACO'][$idx]->strFlag      = $this->clsACO->countries[0]->strFlagImg;
         $displayData['ghACO'][$idx]->strCurSymbol = $this->clsACO->countries[0]->strCurrencySymbol;
         $displayData['ghACO'][$idx]->strCountry   = $this->clsACO->countries[0]->strName;

         if ($displayData['lNumGiftsGH'][$idx] > 0) $displayData['bAnyGifts'] = true;
         ++$idx;
      }
   
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$displayData   <pre>');
echo(htmlspecialchars( print_r($displayData, true))); echo('</pre></font><br>');
// ------------------------------------- */
   
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = 'Donation History';
      $displayData['title']          = CS_PROGNAME.' | Donation History';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'vol_reg/gift_history_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   
   
   
   }
   
   
}


