<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class gift_record extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($lGiftID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      if (!bTestForURLHack('showFinancials')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lGiftID, 'donation ID');

      $displayData = array();
      $displayData['lGiftID'] = $lGiftID = (integer)$lGiftID;
      $displayData['js'] = '';

      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $this->load->model('people/mpeople',                  'clsPeople');
      $this->load->model('biz/mbiz',                        'clsBiz');
      $this->load->model('admin/madmin_aco',                'clsACO');
      $this->load->model('sponsorship/msponsorship',        'clsSpon');
      $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->model('donations/mdonations',            'clsGift');
      $this->load->model('donations/mhon_mem',              'clsHonMem');
      $this->load->model('biz/mbiz',                        'clsBiz');
      $this->load->model('admin/muser_accts',               'clsUser');
      $this->load->model('personalization/muser_fields',    'clsUF');
      $this->load->library('util/dl_date_time', '',         'clsDateTime');
      $this->load->helper('dl_util/web_layout');
//      $this->load->helper('dl_util/email_web');
      $this->load->helper('dl_util/record_view');
      $this->load->helper('img_docs/link_img_docs');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('personalization/ptable');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

      $this->clsGift->loadGiftViaGID($lGiftID);
      $displayData['gifts']        = $gifts = &$this->clsGift->gifts[0];
      $displayData['lFID']         = $lFID  = $gifts->gi_lForeignID;
      $displayData['bSponPayment'] = $bSponPayment = !is_null($gifts->gi_lSponsorID);

      if ($bSponPayment){
         redirect('sponsors/payments/viewPaymentRec/'.$lGiftID);
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
      $displayData['strPT'] = strPTableDisplay(CENUM_CONTEXT_GIFT, $lGiftID,
                                  $this->clsUFD, $this->perms, $acctAccess,
                                  $displayData['strFormDataEntryAlert'],
                                  $displayData['lNumPTablesAvail']);


      $this->clsHonMem->lGID = $lGiftID;
      $this->clsHonMem->loadHonMem('via GiftID');
      $displayData['honMemTable'] = $this->clsHonMem->honMemTable;
      $displayData['lNumHonMem']  = $this->clsHonMem->lNumHonMem;

      $displayData['bBiz'] = $bBiz = $this->clsPeople->bBizRec($lFID);
      if ($bBiz){
         $this->clsBiz->loadBizRecsViaBID($lFID);
         $displayData['biz']      = $biz = &$this->clsBiz->bizRecs[0];
         $displayData['people']   = null;
         $displayData['strDonor'] = $strDonor = $biz->strSafeName;
         $strAnchorBase = anchor('main/menu/biz', 'Business/Organizations', 'class="breadcrumb"');
      }else {
         $this->clsPeople->loadPeopleViaPIDs($lFID, false, false);
         $displayData['people'] = $people = &$this->clsPeople->people[0];
         $displayData['biz'] = null;
         $displayData['strDonor'] = $strDonor = $people->strSafeName;
         $strAnchorBase = anchor('main/menu/people', 'People', 'class="breadcrumb"');
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      if (bAllowAccess('showGiftHistory')){
         $displayData['pageTitle']      = $strAnchorBase
                                .' | '.anchor('donations/gift_history/view/'.$lFID, 'Gift History', 'class="breadcrumb"')
                                .' | Gift Record';
      }else {
         $displayData['pageTitle']      = $strAnchorBase
                                .' | Gift Record';
      }

      $displayData['title']          = CS_PROGNAME.' | Gifts';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'donations/donation_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');

   }

   function remove($lGiftID){
      if (!bTestForURLHack('editGifts')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lGiftID, 'donation ID');

      $lGiftID = (integer)$lGiftID;

         //------------------------------------------------
         // models, libraries and utilities
         //------------------------------------------------
      $this->load->model('people/mpeople',                'clsPeople');
      $this->load->model('biz/mbiz',                      'clsBiz');
      $this->load->model('admin/madmin_aco',               'clsACO');
      $this->load->model('sponsorship/msponsorship',      'clsSpon');
      $this->load->model('util/mrecycle_bin',              'clsRecycle');
      $this->load->model('donations/mdonations',          'clsGift');
      $this->load->model('personalization/muser_fields',         'clsUF');

      $this->clsGift->loadGiftViaGID($lGiftID);

      $gift = &$this->clsGift->gifts[0];
      $strGID = str_pad($lGiftID, 5, '0', STR_PAD_LEFT);
      $this->session->set_flashdata('msg',
                   ($gift->pe_bBiz ? 'Business' : 'Individual').' donation '.$strGID.' for '
                         .$gift->strFormattedAmnt.' from '.$gift->strSafeName.' was removed.');

      $this->clsGift->retireSingleGift($lGiftID, null);
      if ($gift->pe_bBiz){
         redirect_Biz($gift->gi_lForeignID);
      }else {
         redirect_People($gift->gi_lForeignID);
      }
   }


}