<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class people_record extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function view($lPID=0){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $gbVolLogin, $gVolPerms, $glVolPeopleID, $glUserID;
      if ($gbVolLogin){
         $lPID = $glVolPeopleID;
      }else {
         if (!bTestForURLHack('viewPeopleBizVol')) return;
      }

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lPID, 'people ID');

      $displayData = array();
      $displayData['lPID'] = $lPID = (integer)$lPID;
      $displayData['js'] = '';

      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
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
      $this->load->model  ('admin/mpermissions',              'perms');

      $this->load->library('util/dl_date_time', '',           'clsDateTime');

      $this->load->helper ('groups/groups');
      $this->load->helper ('dl_util/web_layout');
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('personalization/ptable');
      $this->load->helper ('img_docs/img_doc_tags');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

//      $this->load->model('reminders/mreminders', 'clsReminders');
//      $displayData['clsRem'] = $this->clsReminders;

      $this->clsPeople->sqlWhereExtra = " AND pe_lKeyID = $lPID ";
      $this->clsPeople->loadPeople(true, true, true);
      $displayData['people'] = &$this->clsPeople->people[0];

         //-------------------------------
         // volunteer info
         //-------------------------------
      $displayData['vol'] = new stdClass;
      $displayData['vol']->bVol = $this->clsVol->bVolStatusViaPID($lPID,
                                         $displayData['vol']->lVolID,
                                         $displayData['vol']->bInactive,
                                         $displayData['vol']->dteInactive,
                                         $displayData['vol']->dteVolStart);

         //-------------------------------
         // sponsorship info
         //-------------------------------
      if (!$gbVolLogin){
         $this->clsSpon->sponsorshipInfoViaPID($lPID);
         $displayData['sponInfo']     = $this->clsSpon->sponInfo;
         $displayData['lNumSponsors'] = $this->clsSpon->lNumSponsors;
      }

         //-------------------------------
         // personalized tables
         //-------------------------------
      if (!$gbVolLogin){
         $this->perms->loadUserAcctInfo($glUserID, $acctAccess);
         $this->load->model('personalization/muser_fields',         'clsUF');
         $this->load->model('personalization/muser_fields_display', 'clsUFD');
         $displayData['strPT'] = strPTableDisplay(CENUM_CONTEXT_PEOPLE, $lPID,
                                   $this->clsUFD, $this->perms, $acctAccess,
                                   $displayData['strFormDataEntryAlert'],
                                   $displayData['lNumPTablesAvail']);
      }

         //-------------------------------
         // household
         //-------------------------------
      if (!$gbVolLogin){
         $this->clsPeople->lHouseholdID = $this->clsPeople->people[0]->lHouseholdID;
         $this->clsPeople->loadPIDsViaHouseholdHID();
         $displayData['arrHouseholds'] = $this->clsPeople->arrHouseholds;
      }

         //-------------------------------
         // relationships
         //-------------------------------
      if (!$gbVolLogin){
         $this->clsRel->lPID = $lPID;
         $this->clsRel->loadFromRelViaPID();
         $displayData['arrRelAB']  = $this->clsRel->arrRelAB;
         $displayData['lNumRelAB'] = $this->clsRel->lNumRelAB;
         $this->clsRel->loadToRelViaPID();
         $displayData['arrRelBA']  = $this->clsRel->arrRelAB;
         $displayData['lNumRelBA'] = $this->clsRel->lNumRelAB;
      }

         //-------------------------------
         // groups
         //-------------------------------
      if (!$gbVolLogin){
         $this->groups->groupMembershipViaFID(CENUM_CONTEXT_PEOPLE, $lPID);
         $displayData['inGroups']            = $this->groups->arrMemberInGroups;
         $displayData['lCntGroupMembership'] = $this->groups->lNumMemInGroups;
         $displayData['lNumGroups']          = $this->groups->lCntActiveGroupsViaType(CENUM_CONTEXT_PEOPLE);
         $this->groups->loadActiveGroupsViaType(CENUM_CONTEXT_PEOPLE, 'groupName', $this->groups->strMemListIDs, false, null);
         $displayData['groupList']           = $this->groups->arrGroupList;
      }

         //-------------------------------
         // images and documents
         //-------------------------------
      if (!$gbVolLogin){
         loadImgDocRecView($displayData, CENUM_CONTEXT_PEOPLE, $lPID);
      }

         //-------------------
         // donation summary
         //-------------------
      if (!$gbVolLogin){
         $this->clsGifts->lPeopleID = $lPID;

         $displayData['lTotGifts'] = 0;
         $this->clsGifts->cumulativeOpts = new stdClass;
         $this->clsGifts->cumulativeOpts->enumCumulativeSource = 'people';
         $this->clsGifts->cumulativeOpts->enumMoneySet = 'monetaryOnly';
         $this->clsGifts->cumulativeOpts->bSoft        = false;
         $this->clsGifts->cumulativeDonation($this->clsACO, $displayData['lTotHard']);
         $displayData['strCumGiftsNonSoftMon'] = strBuildCumlativeTable($this->clsGifts->lNumCumulative, $this->clsGifts->cumulative, true);
//         $displayData['lTotGifts'] += $this->clsGifts->lNumCumulative;

            // in-kind donations
         $this->clsGifts->cumulativeOpts->enumMoneySet = 'gikOnly';
         $this->clsGifts->cumulativeDonation($this->clsACO, $displayData['lTotInKind']);
         $displayData['strCumGiftsNonSoftInKind'] = strBuildCumlativeTable($this->clsGifts->lNumCumulative, $this->clsGifts->cumulative, true);
//         $displayData['lTotGifts'] += $this->clsGifts->lNumCumulative;

            // soft donations
         $this->clsGifts->cumulativeOpts->enumMoneySet = 'all';
         $this->clsGifts->cumulativeOpts->bSoft        = true;
         $this->clsGifts->cumulativeDonation($this->clsACO, $displayData['lTotSoft']);
         $displayData['strCumGiftsSoft'] = strBuildCumlativeTable($this->clsGifts->lNumCumulative, $this->clsGifts->cumulative, true);
//         $displayData['lTotGifts'] += $this->clsGifts->lNumCumulative;

            // sponsorship payments
         $this->clsSCP->cumulativeSponsorshipViaPeopleID($this->clsACO, $lPID);
         $displayData['strCumSpon'] = strBuildCumlativeTable($this->clsSCP->lNumSponPayCumulative, $this->clsSCP->sponPayCumulative, true);
         $displayData['lNumSponPay'] = $this->clsSCP->lNumSponPayCumulative;

         $displayData['lNumPledges'] = $this->clsPledges->lNumPledgesViaFID($lPID);
      }

         //-------------------
         // business contacts
         //-------------------
      if (!$gbVolLogin){
         $this->clsBiz->lPID = $lPID;
         $this->clsBiz->contactList(false, true, false);
         $displayData['lNumContacts'] = $this->clsBiz->lNumContacts;
         $displayData['contacts']     = $this->clsBiz->contacts;
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      if ($gbVolLogin){
         $displayData['pageTitle']      = 'Contact Info';
         $displayData['title']          = CS_PROGNAME.' | Contact Info';
      }else {
         $displayData['pageTitle']      = anchor('main/menu/people', 'People', 'class="breadcrumb"')
                                   .' | People Record';
         $displayData['title']          = CS_PROGNAME.' | People';
      }
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'people/people_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }


}