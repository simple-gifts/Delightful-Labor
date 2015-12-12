<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pledge_record extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($lPledgeID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gclsChapterACO, $gbDateFormatUS;

      if (!bTestForURLHack('showFinancials')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPledgeID, 'pledge ID');

      $displayData = array();
      $displayData['lPledgeID'] = $lPledgeID = (integer)$lPledgeID;
      $displayData['js']        = '';

         // load models
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper ('dl_util/web_layout');
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper ('gifts/pledge');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $this->load->model  ('people/mpeople',         'clsPeople');
      $this->load->model  ('biz/mbiz',               'clsBiz');
      $this->load->model  ('donations/maccts_camps', 'clsAC');
      $this->load->model  ('donations/mpledges',     'clsPledges');
      $this->load->model  ('donations/mdonations',   'clsGifts');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

      $this->clsPledges->loadPledgeViaPledgeID($lPledgeID);
      $displayData['pledge'] = $pledge = &$this->clsPledges->pledges[0];
      $displayData['lFID']   = $lFID   = $pledge->lForeignID;
      $bPeople = !$pledge->bBiz;

      $displayData['pledge']->curTotFulfill = $this->clsPledges->curTotalFulfillmentViaPledgeID($pledge->lKeyID, $pledge->lACOID);
      $this->clsPledges->pledgeSchedule($pledge, $displayData['schedule']);
      $this->clsPledges->curFillmentViaPledgeID($pledge->lKeyID, $pledge->lACOID, $this->clsGifts);

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['mainTemplate']   = 'donations/pledge_record_view';
      if ($bPeople){
         $displayData['pageTitle']   = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                                .' | '.anchor('people/people_record/view/'.$lFID, 'Record', 'class="breadcrumb"')
                                .' | View Pledge Record';
         $this->clsPeople->loadPeopleViaPIDs($lFID, false, false);
         $displayData['contextSummary'] = $this->clsPeople->peopleHTMLSummary(0);
      }else {
         $displayData['pageTitle']   = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                                .' | '.anchor('biz/biz_record/view/'.$lFID, 'Record', 'class="breadcrumb"')
                                .' | View Pledge Record';
         $this->clsBiz->loadBizRecsViaBID($lFID);
         $displayData['contextSummary'] = $this->clsBiz->strBizHTMLSummary(0);
      }

      $displayData['title']          = CS_PROGNAME.' | Pledges';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
  }

}
