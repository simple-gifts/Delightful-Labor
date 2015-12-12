<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class client_to_sponsor extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function add($lSponID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      if (!bTestForURLHack('showSponsors')) return;
      $displayData = array();

         //------------------------------------------------
         // libraries / models / utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->model('sponsorship/msponsorship',         'clsSpon');
      $this->load->model('clients/mclients',                 'clsClients');
      $this->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',           'clsDateTime');
      $this->load->library('clients/client_search_util', '',  'clsCSearch');
      
      $clsCSearch = new client_search_util;

      $this->clsSpon->sponsorInfoViaID($lSponID);
      $strSponProg = $this->clsSpon->sponInfo[0]->strSponProgram;
      $clsCSearch->lSponProgID = $this->clsSpon->sponInfo[0]->lSponsorProgID;
      $clsCSearch->clientsAvailableForSponsorship();

      $displayData['contextSummary'] = $this->clsSpon->sponsorshipHTMLSummary();

      if ($clsCSearch->lNumAvail > 0){
         $clsCSearch->strExtraClientWhere = ' AND (cr_lKeyID IN ('.implode(',', $clsCSearch->lAvailList).')) ';
         $clsCSearch->loadClientsGeneric();
         $cDir = &$clsCSearch->dir;
         $cDir->strTableClassStyle  = ' class="enpRptC" ';
         $cDir->strTitle            = 'Select a Client for '.$this->clsSpon->sponInfo[0]->strSponSafeNameFL;
         $cDir->strTitleClassStyle  = ' class="enpRptTitle" ';
         $cDir->strHeaderClass      = ' class="enpRptLabel" ';
         $cDir->strRowTRClass       = ' class="makeStripe" ';

            // the client select link
         $cDir->clsSelLink = new stdClass;

         $cDir->clsSelLink->strLinkPath    = 'sponsors/client_to_sponsor/clientSelected/'.$lSponID;
         $cDir->clsSelLink->strAnchorExtra = '';
         $cDir->clsSelLink->bShowImage     = true;
         $cDir->clsSelLink->bShowText      = false;
         $cDir->clsSelLink->enumImage      = IMGLINK_SELECT;
         $cDir->clsSelLink->strLinkText    = 'Select this client';

         $cDir->cols = array();
         for ($idx=0; $idx<=5; ++$idx) $cDir->cols[$idx] = new stdClass;
         $cDir->cols[0]->label = 'Select Link';   $cDir->cols[0]->width = '';      $cDir->cols[0]->tdClass=' class="enpRpt" ';
         $cDir->cols[1]->label = 'Client ID';     $cDir->cols[1]->width = '50pt';  $cDir->cols[1]->tdClass=' class="enpRpt" ';
         $cDir->cols[2]->label = 'Name';          $cDir->cols[2]->width = '150pt'; $cDir->cols[2]->tdClass=' class="enpRpt" ';
         $cDir->cols[3]->label = 'Location';      $cDir->cols[3]->width = '150pt'; $cDir->cols[3]->tdClass=' class="enpRpt" ';
         $cDir->cols[4]->label = 'Birthday/Age';  $cDir->cols[4]->width = '';      $cDir->cols[4]->tdClass=' class="enpRpt" ';
         $cDir->cols[5]->label = 'Gender';        $cDir->cols[5]->width = '';      $cDir->cols[5]->tdClass=' class="enpRpt" ';

         $displayData['clientTable']   = $clsCSearch->clientDirectory();
      }else {
         $displayData['clientTable']   = '<i>There are no clients available in sponsorship program <b>'
                           .htmlspecialchars($strSponProg).'</b>.</i>';
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                                .' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$lSponID, 'Sponsorship Record', 'class="breadcrumb"')
                                .' | Add Client';

      $displayData['title']          = CS_PROGNAME.' | Sponsorship';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'sponsorship/add_client_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   function clientSelected($lSponID, $lClientID){
      $lSponID   = (integer)$lSponID;
      $lClientID = (integer)$lClientID;
      
      $this->load->model('sponsorship/msponsorship', 'clsSpon');
      $this->session->set_flashdata('msg', 'Client added to this sponsorship');
      $this->clsSpon->addClientToSponsor($lSponID, $lClientID);
      redirect_SponsorshipRecord($lSponID);
   }
   

}
