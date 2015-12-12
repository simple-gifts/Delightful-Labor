<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class view_spon_rec extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function viewViaSponID($lSponID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $glUserID;

      if (!bTestForURLHack('showSponsors')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lSponID, 'sponsor ID');

      $displayData = array();
      $displayData['lSponID'] = $lSponID = (integer)$lSponID;
      $displayData['js'] = '';

      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      $clsRpt = new generic_rpt($params);

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $this->load->library('util/dl_date_time', '',           'clsDateTime');
      $this->load->model  ('sponsorship/msponsorship',        'clsSpon');
      $this->load->model  ('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->model  ('clients/mclients',                'clsClient');
      $this->load->model  ('people/mpeople',                  'clsPeople');
      $this->load->model  ('admin/madmin_aco',                'clsACO');
      $this->load->model  ('admin/muser_accts',               'clsUser');
      $this->load->model  ('img_docs/mimage_doc',             'clsImgDoc');
      $this->load->model  ('img_docs/mimg_doc_tags',          'cidTags');
      $this->load->model  ('biz/mbiz',                        'clsBiz');
      $this->load->model  ('groups/mgroups',                  'groups');

      $this->load->helper ('personalization/ptable');
      $this->load->helper ('groups/groups');
      $this->load->helper ('dl_util/web_layout');
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('img_docs/img_doc_tags');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

      $this->clsSpon->sponsorInfoViaID($lSponID);
      $displayData['sponRec'] = $sponRec = &$this->clsSpon->sponInfo[0];
      $lFID = $sponRec->lForeignID;
      $displayData['strWidthLabel'] = $strWidthLabel = '130pt;';
      $displayData['financialSummary'] = strSponsorFinancialSummary($clsRpt, $this->clsSCP, $lSponID, $strWidthLabel);
      if ($sponRec->bSponBiz){
         $this->clsBiz->loadBizRecsViaBID($lFID);
         $displayData['contextSummary'] = $this->clsBiz->strBizHTMLSummary();
      }else {
         $this->clsPeople->lPeopleID = $lFID;
         $this->clsPeople->loadPeopleViaPIDs($lFID, false, false);
         $displayData['contextSummary'] = $this->clsPeople->peopleHTMLSummary(0);
      }

         //-------------------------------
         // reminders
         //-------------------------------
      $this->load->model('reminders/mreminders', 'clsReminders');
      $displayData['clsRem'] = &$this->clsReminders;

         //-------------------------------
         // personalized tables
         //-------------------------------
      $this->load->model('personalization/muser_fields',         'clsUF');
      $this->load->model('personalization/muser_fields_display', 'clsUFD');
      $this->load->model('admin/mpermissions',                   'perms');

      $this->perms->loadUserAcctInfo($glUserID, $acctAccess);
      $this->load->model('personalization/muser_fields',         'clsUF');
      $this->load->model('personalization/muser_fields_display', 'clsUFD');
      $displayData['strPT'] = strPTableDisplay(CENUM_CONTEXT_SPONSORSHIP, $lSponID,
                                  $this->clsUFD, $this->perms, $acctAccess,
                                  $displayData['strFormDataEntryAlert'],
                                  $displayData['lNumPTablesAvail']);

         //-------------------------------
         // groups
         //-------------------------------
      $this->groups->groupMembershipViaFID(CENUM_CONTEXT_SPONSORSHIP, $lSponID);
      $displayData['inGroups']            = $this->groups->arrMemberInGroups;
      $displayData['lCntGroupMembership'] = $this->groups->lNumMemInGroups;
      $displayData['lNumGroups']          = $this->groups->lCntActiveGroupsViaType(CENUM_CONTEXT_SPONSORSHIP);
      $this->groups->loadActiveGroupsViaType(CENUM_CONTEXT_SPONSORSHIP, 'groupName', $this->groups->strMemListIDs, false, null);
      $displayData['groupList']           = $this->groups->arrGroupList;

         //-------------------------------
         // client info
         //-------------------------------
      $displayData['lClientID'] = $lClientID = $sponRec->lClientID;
      $displayData['clsClient'] = &$this->clsClient;
      if (!is_null($lClientID)){
         $this->clsClient->loadClientsViaClientID($lClientID);
      }

         //-------------------------------
         // images and documents
         //-------------------------------
      loadImgDocRecView($displayData, CENUM_CONTEXT_SPONSORSHIP, $lSponID);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                                .' | Sponsorship Record';

      $displayData['title']          = CS_PROGNAME.' | Sponsorship';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'sponsorship/sponsor_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');


   }

}