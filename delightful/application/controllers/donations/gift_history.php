<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class gift_history extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($lFID, $enumSortType='date'){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showGiftHistory')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lFID, 'people/business ID');

      $displayData = array();
      $displayData['lFID'] = $lFID = (integer)$lFID;
      $displayData['js'] = '';

         //-----------------------------------------------
         // Models, helpers, libraries
         //-----------------------------------------------
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper('dl_util/web_layout');
      $this->load->model('people/mpeople',         'clsPeople');
      $this->load->model('donations/maccts_camps', 'clsAC');
      $this->load->model('donations/mdonations',   'clsGifts');
      $this->load->model('donations/mpledges',     'clsPledges');
      $this->load->model('donations/mhon_mem');
      $this->load->model('admin/madmin_aco',       'clsACO');
      $this->load->model('util/mbuild_on_ready',   'clsOnReady');

      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);

         // Stripes
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

      $this->clsACO->loadCountries(false, true, false, null);

      $bPeople = !$this->clsPeople->bBizRec($lFID);
      if ($bPeople){
         $this->clsPeople->loadPeopleViaPIDs($lFID, false, false);
         $displayData['strName'] = $this->clsPeople->people[0]->strSafeName;
         $displayData['pageTitle']    = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                                 .' | '.anchor('people/people_record/view/'.$lFID, 'Record', 'class="breadcrumb"')
                                 .' | Gift History';
         $displayData['contextSummary'] = $this->clsPeople->peopleHTMLSummary(0);
      }else{
         $this->load->model('biz/mbiz', 'clsBiz');
         $this->clsBiz->loadBizRecsViaBID($lFID);
         $displayData['strName'] = $this->clsBiz->bizRecs[0]->strSafeName;
         $displayData['pageTitle']    = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                                 .' | '.anchor('biz/biz_record/view/'.$lFID, 'Record', 'class="breadcrumb"')
                                 .' | Gift History';
         $displayData['contextSummary'] = $this->clsBiz->strBizHTMLSummary();
      }

         // donations
      $displayData['bAnyGifts'] = false;
      $idx = 0;
      $displayData['ghACO'] = $displayData['lNumGiftsGH'] = $displayData['giftHistory'] = array();
      foreach ($this->clsACO->countries as $clsCountry){
         $lACOID     = $clsCountry->lKeyID;
         $strFlagImg = $clsCountry->strFlagImg;

         $this->clsGifts->loadGiftHistory($lFID, $enumSortType, $lACOID, $this->clsACO,
                                          $displayData['lNumGiftsGH'][$idx],
                                          $displayData['giftHistory'][$idx]);
         $displayData['ghACO'][$idx] = new stdClass;
         $displayData['ghACO'][$idx]->strFlag      = $this->clsACO->countries[0]->strFlagImg;
         $displayData['ghACO'][$idx]->strCurSymbol = $this->clsACO->countries[0]->strCurrencySymbol;
         $displayData['ghACO'][$idx]->strCountry   = $this->clsACO->countries[0]->strName;

         if ($displayData['lNumGiftsGH'][$idx] > 0) $displayData['bAnyGifts'] = true;
         ++$idx;
      }

         // pledges
      $this->clsPledges->loadPledgeViaFID($lFID);
      $displayData['pledges']     = &$this->clsPledges->pledges;
      $displayData['lNumPledges'] = $this->clsPledges->lNumPledges;

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['mainTemplate'] = array('donations/gift_history_view');
      $displayData['title']        = CS_PROGNAME.' | Gifts';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }

}
