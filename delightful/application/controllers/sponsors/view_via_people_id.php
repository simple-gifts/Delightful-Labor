<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class view_via_people_id extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($lPID){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      if (!bTestForURLHack('showSponsors')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPID, 'people/business ID');

      $displayData = array();
      $displayData['showFields'] = new stdClass;
      $lPID = (integer)$lPID;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->helper ('people/people');
      $this->load->helper ('people/people_display');
      $this->load->model  ('sponsorship/msponsorship',          'clsSpon');
      $this->load->model  ('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->load->model  ('people/mpeople',                    'clsPeople');
      $this->load->model  ('biz/mbiz',                          'clsBiz');
      $this->load->library('util/dl_date_time', '',            'clsDateTime');
      $this->load->helper ('sponsors/sponsorship');
      $this->load->helper ('dl_util/directory');
      $this->load->helper ('dl_util/rs_navigate');
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('img_docs/link_img_docs');

      $bBizRec = $this->clsPeople->bBizRec($lPID);

      $this->clsSpon->sponsorshipInfoViaPID($lPID);
      $displayData['sponInfo'] = &$this->clsSpon->sponInfo;
      $displayData['lNumSpon'] = count($this->clsSpon->sponInfo);

      $displayData['showFields']->bSponsorID    = true;
      $displayData['showFields']->bName         = true;
      $displayData['showFields']->bSponsorInfo  = true;
      $displayData['showFields']->bClient       = true;
      $displayData['showFields']->bLocation     = true;
      $displayData['showFields']->bSponAddr     = true;
/*
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<pre>'); print_r($this->clsSpon->sponInfo); echo('</pre></font><br>');
*/
      if ($bBizRec){
         $this->clsBiz->lBID = $lPID;
         $this->clsBiz->bizInfoLight();
         $displayData['strRptTitle'] = 'Sponsorships for '.$this->clsBiz->strSafeName;
      }else {
         $this->clsPeople->lPeopleID = $lPID;
         $this->clsPeople->peopleInfoLight();
         $displayData['strRptTitle'] = 'Sponsorships for '.$this->clsPeople->strSafeName;
      }

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;


         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['mainTemplate'] = array('sponsorship/rpt_basic_sponsor_list');
      $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                              .' | Directory';

      $displayData['title']        = CS_PROGNAME.' | Sponsorship';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');


   }











}
