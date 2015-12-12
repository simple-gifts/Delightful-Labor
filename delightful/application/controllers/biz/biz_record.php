<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class biz_record extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function view($lBID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $glUserID;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lBID, 'business ID');

      $displayData = array();
      $displayData['lBID'] = $lBID = (integer)$lBID;
      $displayData['js'] = '';

      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper ('img_docs/img_doc_tags');
      $this->load->model  ('people/mpeople',                  'clsPeople');
      $this->load->model  ('biz/mbiz',                        'clsBiz');
      $this->load->model  ('admin/madmin_aco',                'clsACO');
      $this->load->model  ('admin/muser_accts',               'clsUser');
      $this->load->model  ('sponsorship/msponsorship',        'clsSpon');
      $this->load->model  ('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->model  ('donations/mdonations',            'clsGifts');
      $this->load->model  ('donations/mpledges',              'clsPledges');
      $this->load->model  ('vols/mvol',                       'clsVol');
      $this->load->model  ('people/mrelationships',           'clsRel');
      $this->load->model  ('groups/mgroups',                  'groups');
      $this->load->model  ('img_docs/mimage_doc',             'clsImgDoc');
      $this->load->model  ('img_docs/mimg_doc_tags',          'cidTags');
      $this->load->library('util/dl_date_time', '',           'clsDateTime');

      $this->load->helper ('personalization/ptable');
      $this->load->helper ('dl_util/web_layout');
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('groups/groups');

      $this->load->model('reminders/mreminders', 'clsReminders');
      $displayData['clsRem'] = $this->clsReminders;

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //-------------------------------
         // business record
         //-------------------------------
      $this->clsBiz->loadBizRecsViaBID($lBID);
      $displayData['biz'] = &$this->clsBiz->bizRecs[0];

         //-------------------------------
         // associated contacts
         //-------------------------------
      $this->clsBiz->contactList(true, false, false);
      $displayData['contacts']     = &$this->clsBiz->contacts;
      $displayData['lNumContacts'] = $this->clsBiz->lNumContacts;

         //-------------------------------
         // personalized tables
         //-------------------------------
      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->model('personalization/muser_fields_display', 'clsUFD');
      $this->load->model('admin/mpermissions',                   'perms');
      $this->perms->loadUserAcctInfo($glUserID, $acctAccess);
      $this->load->model('personalization/muser_fields',         'clsUF');
      $this->load->model('personalization/muser_fields_display', 'clsUFD');
      $displayData['strPT'] = strPTableDisplay(CENUM_CONTEXT_BIZ, $lBID, $this->clsUFD,
                                  $this->perms, $acctAccess,
                                  $displayData['strFormDataEntryAlert'],
                                  $displayData['lNumPTablesAvail']);

         //-------------------------------
         // groups
         //-------------------------------
      $this->groups->groupMembershipViaFID(CENUM_CONTEXT_BIZ, $lBID);
      $displayData['inGroups']            = $this->groups->arrMemberInGroups;
      $displayData['lCntGroupMembership'] = $this->groups->lNumMemInGroups;
      $displayData['lNumGroups']          = $this->groups->lCntActiveGroupsViaType(CENUM_CONTEXT_BIZ);
      $this->groups->loadActiveGroupsViaType(CENUM_CONTEXT_BIZ, 'groupName', $this->groups->strMemListIDs, false, null);
      $displayData['groupList']           = $this->groups->arrGroupList;

         //-------------------------------
         // images and documents
         //-------------------------------
      loadImgDocRecView($displayData, CENUM_CONTEXT_BIZ, $lBID);

         //-------------------
         // donation summary
         //-------------------
      $this->clsGifts->lPeopleID = $lBID;

      $displayData['lTotGifts'] = 0;
         // monetary
      $this->clsGifts->cumulativeOpts = new stdClass;
      $this->clsGifts->cumulativeOpts->enumCumulativeSource = 'biz';
      $this->clsGifts->cumulativeOpts->enumMoneySet = 'monetaryOnly';
      $this->clsGifts->cumulativeOpts->bSoft        = false;
      $this->clsGifts->cumulativeDonation($this->clsACO, $displayData['lTotHard']);
      $displayData['strCumGiftsNonSoftMon'] = strBuildCumlativeTable($this->clsGifts->lNumCumulative, $this->clsGifts->cumulative, true);
//      $displayData['lTotGifts'] += $this->clsGifts->lNumCumulative;

         // in-kind
      $this->clsGifts->cumulativeOpts->enumMoneySet = 'gikOnly';
      $this->clsGifts->cumulativeDonation($this->clsACO, $displayData['lTotInKind']);
      $displayData['strCumGiftsNonSoftInKind'] = strBuildCumlativeTable($this->clsGifts->lNumCumulative, $this->clsGifts->cumulative, true);
//      $displayData['lTotGifts'] += $this->clsGifts->lNumCumulative;

         // soft
      $this->clsGifts->cumulativeOpts->enumMoneySet = 'all';
      $this->clsGifts->cumulativeOpts->bSoft        = true;
      $this->clsGifts->cumulativeDonation($this->clsACO, $displayData['lTotSoft']);
      $displayData['strCumGiftsSoft'] = strBuildCumlativeTable($this->clsGifts->lNumCumulative, $this->clsGifts->cumulative, true);
//      $displayData['lTotGifts'] += $this->clsGifts->lNumCumulative;

         // sponsorship payments
      $this->clsSCP->cumulativeSponsorshipViaPeopleID($this->clsACO, $lBID);
      $displayData['strCumSpon'] = strBuildCumlativeTable($this->clsSCP->lNumSponPayCumulative, $this->clsSCP->sponPayCumulative, true);
      $displayData['lNumSponPay'] = $this->clsSCP->lNumSponPayCumulative;

      $displayData['lNumPledges'] = $this->clsPledges->lNumPledgesViaFID($lBID);

         //-------------------------------
         // sponsorship info
         //-------------------------------
      $this->clsSpon->sponsorshipInfoViaPID($lBID);
      $displayData['sponInfo']     = $this->clsSpon->sponInfo;
      $displayData['lNumSponsors'] = $this->clsSpon->lNumSponsors;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                                .' | Business Record';

      $displayData['title']          = CS_PROGNAME.' | Businesses';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'biz/biz_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

}
