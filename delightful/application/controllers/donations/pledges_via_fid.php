<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pledges_via_fid extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($lFID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gclsChapterACO, $gbDateFormatUS;

      if (!bTestForURLHack('showFinancials')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lFID, 'people/business ID');

      $displayData = array();
      $displayData['lFID'] = $lFID = (integer)$lFID;
      $displayData['js']   = '';

         // load models
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $this->load->model  ('people/mpeople',         'clsPeople');
      $this->load->model  ('biz/mbiz',               'clsBiz');
      $this->load->model  ('donations/maccts_camps', 'clsAC');
      $this->load->model  ('donations/mpledges',     'clsPledges');
      $this->load->model  ('donations/mdonations',   'clsGifts');
      $this->load->helper ('dl_util/web_layout');
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper ('gifts/pledge');

         // load people/biz record
      $bPeople = !$this->clsPeople->bBizRec($lFID);
      if ($bPeople){
         $this->clsPeople->loadPeopleViaPIDs($lFID, false, false);
         $displayData['strSafeName'] = $this->clsPeople->people[0]->strSafeName;
      }else {
         $this->clsBiz->loadBizRecsViaBID($lFID);
         $displayData['strSafeName'] = $this->clsBiz->people[0]->strSafeName;
      }

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         //--------------------------
         // load pledges
         //--------------------------
      $this->clsPledges->loadPledgeViaFID($lFID);
      $displayData['lNumPledges'] = $lNumPledges = $this->clsPledges->lNumPledges;
      $displayData['pledges']     = $pledges     = &$this->clsPledges->pledges;
      if ($lNumPledges > 0){
         foreach ($pledges as $pledge){
            $lPledgeID = $pledge->lKeyID;
            $this->clsPledges->pledgeSchedule($pledge, $pledge->schedule);
            $pledge->curTotFulfill = $this->clsPledges->curTotalFulfillmentViaPledgeID($lPledgeID, $pledge->lACOID);
         }
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['mainTemplate']   = 'donations/pledges_via_fid_view';
      if ($bPeople){
         $displayData['pageTitle']   = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                                .' | '.anchor('people/people_record/view/'.$lFID, 'Record', 'class="breadcrumb"')
                                .' | Pledges';
         $this->clsPeople->loadPeopleViaPIDs($lFID, false, false);
         $displayData['contextSummary'] = $this->clsPeople->peopleHTMLSummary(0);
      }else {
         $displayData['pageTitle']   = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                                .' | '.anchor('biz/biz_record/view/'.$lFID, 'Record', 'class="breadcrumb"')
                                .' | Pledges';
         $this->clsBiz->loadBizRecsViaBID($lFID);
         $displayData['contextSummary'] = $this->clsBiz->strBizHTMLSummary(0);
      }

      $displayData['title']          = CS_PROGNAME.' | Pledges';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }


}

