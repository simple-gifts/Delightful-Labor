<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class campaigns extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewCampsViaAcctID($lAcctID){
      if (!bTestForURLHack('adminOnly')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lAcctID, 'account ID');
      
      $displayData = array();

      $displayData['lAcctID'] = $lAcctID = (integer)$lAcctID;
      $this->load->model('donations/maccts_camps', 'clsAC');
      $this->clsAC->loadAccounts(false, true, $lAcctID);
      $displayData['strAcctName'] = $strAcctName = $this->clsAC->accounts[0]->strSafeName;

      $this->clsAC->loadCampaigns(false, true, $lAcctID, false, null);
      $displayData['campaigns'] = $this->clsAC->campaigns;

      $this->load->model('donations/mdonations', 'clsGifts');
      $this->clsGifts->bUseDateRange = false;
      $this->clsGifts->enumCumulativeSource = 'campaign';

      $this->load->model ('admin/madmin_aco', 'clsACO');
      $this->load->helper('dl_util/record_view');
      $this->load->helper('img_docs/link_img_docs');

      $idx = 0;
      foreach($this->clsAC->campaigns as $clsCamp){
         $displayData['lCampID'][$idx] = $lCampID = $clsCamp->lKeyID;

            //-------------------
            // cumulative gifts
            //-------------------
         $this->clsGifts->lCampID = $lCampID;
         $this->clsGifts->cumulativeOpts = new stdClass;
         $this->clsGifts->cumulativeOpts->enumCumulativeSource = 'campaign';
         $this->clsGifts->cumulativeOpts->bSoft                = false;
         $this->clsGifts->cumulativeOpts->enumMoneySet         = 'all';
         $this->clsGifts->cumulativeDonation($this->clsACO, $lTotHardGifts);
         $displayData['strCumGiftsNonSoft'][$idx] =
                   strBuildCumlativeTable($this->clsGifts->lNumCumulative, $this->clsGifts->cumulative, true);
         if ($this->clsGifts->lNumCumulative > 0){
            $displayData['strLinkGifts'][$idx] =
                            strLinkView_GiftListCamps($lCampID, 'View gifts for this campaign', true).' '
                           .strLinkView_GiftListCamps($lCampID, 'View gifts', false);
         }else {
            $displayData['strLinkGifts'][$idx] = '';
         }
         ++$idx;
      }

      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);
      $displayData['clsRpt'] = $this->generic_rpt;

      $this->load->model('donations/mdonations', 'clsGifts');
      $this->clsGifts->bUseDateRange = false;
      $this->clsGifts->enumCumulativeSource = 'campaign';

      $displayData['title']        = CS_PROGNAME.' | Campaigns';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"') 
                              .' | '.anchor('admin/alists/showLists',   'Lists', 'class="breadcrumb"')
                              .' | '.anchor('accts_camp/accounts/view', 'Accounts & Campaigns', 'class="breadcrumb"')
                              .' |  '.$strAcctName.': View Campaigns';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate'] = 'accts_camp/camp_via_acctid_view';
      $this->load->vars($displayData);
      $this->load->view('template');

   }
   
   function addEdit($lAcctID, $lCampID){
      if (!bTestForURLHack('adminOnly')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lAcctID, 'account ID');
      verifyIDsViaType($this, CENUM_CONTEXT_CAMPAIGN, $lCampID, true);   
      
      $displayData = array();

      $displayData['lAcctID'] = $lAcctID = (integer)$lAcctID;
      $displayData['lCampID'] = $lCampID = (integer)$lCampID;
      $displayData['bNew']    = $bNew = $lCampID <= 0;

         // load account name
      $this->load->model('donations/maccts_camps', 'clsAC');
      $this->clsAC->loadAccounts(false, true, $lAcctID);
      $displayData['strAcctName'] = $strAcct = $this->clsAC->accounts[0]->strSafeName;
      
         // load campaign info
      $this->clsAC->loadCampaigns(false, false, null, true, $lCampID);
      $displayData['bProtected'] = $this->clsAC->campaigns[0]->bProtected;
      $displayData['bAnyGifts']  = $this->clsAC->bAnyGiftsViaCampID($lCampID);
      
      
         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtCamp', 'Campaign Name', 
                                'callback_campNameDupTest['.$lAcctID.','.$lCampID.']');
      
      if ($this->form_validation->run() == FALSE){

         $displayData['title']        = CS_PROGNAME.' | Campaigns';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"') 
                                 .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                                 .' | '.anchor('accts_camp/accounts/view', 'Accounts & Campaigns', 'class="breadcrumb"')
                                 .' | '.anchor('accts_camp/campaigns/viewCampsViaAcctID/'.$lAcctID, $strAcct.': View Campaigns', 'class="breadcrumb"')                                       
                                 .' |  '.($bNew ? 'Add New Campaign' : 'Update Campaign');
         $displayData['nav']          = $this->mnav_brain_jar->navData();

         $this->load->library('generic_form');
         $displayData['clsForm'] = $this->generic_form;

         if (validation_errors()==''){
            $displayData['strCampaign'] = $this->clsAC->campaigns[0]->strCampaign;
         }else {
            setOnFormError($displayData);
            $displayData['strCampaign'] = set_value('txtCamp');
         }

         $displayData['mainTemplate'] = 'accts_camp/camp_add_edit';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->clsAC->campaigns[0]->strCampaign = $strCamp = xss_clean(trim($_POST['txtCamp']));
         $this->clsAC->campaigns[0]->lAcctID     = $lAcctID;
         if ($bNew){
            $this->clsAC->lAddNewCampaign();
            $this->session->set_flashdata('msg', 'Your new campaign was added');            
         }else {
            $this->clsAC->campaigns[0]->bRetired    = @$_POST['chkRetire']=='TRUE';
            $this->clsAC->campaigns[0]->lKeyID   = $lCampID;
            $this->clsAC->updateCampaign();
            $this->session->set_flashdata('msg', 'Your campaign was updated');
         }
         redirect('accts_camp/campaigns/viewCampsViaAcctID/'.$lAcctID);
      }
   }

   function campNameDupTest($strCamp, $strParams){  //$params){
      $params = explode(',', $strParams);
      $lAcctID = (integer)$params[0];
      $lCampID = (integer)$params[1];

      $strCamp = xss_clean(trim($strCamp));
      if ($strCamp==''){
         $this->form_validation->set_message('campNameDupTest', 'The campaign name can not be blank.');
         return(false);
      }else {
         $this->load->model('util/mverify_unique', 'clsUnique');
         if (!$this->clsUnique->bVerifyUniqueText(
                   $strCamp, 'gc_strCampaign',
                   $lCampID,   'gc_lKeyID',
                   true,  'gc_bRetired',
                   true,  $lAcctID, 'gc_lAcctID',
                   false, null, null,
                   'gifts_campaigns')){
            $this->form_validation->set_message('campNameDupTest', 'The campaign name "'
                        .htmlspecialchars($strCamp).'" is already defined for this account.');
            return(false);
         }else {
            return(true);
         }
      }
   }
      
   function xferCampaign($lCampID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lCampID, 'campaign ID');
      $displayData = array();

         //---------------------------
         // models & helpers
         //---------------------------
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/web_layout');
      $this->load->model('donations/maccts_camps', 'clsAC');
      $this->load->library('generic_form');
      
      $this->clsAC->loadCampaigns(false, false, null, true, $lCampID);
      $this->clsAC->loadAccounts(false, false, null);

      $camp = &$this->clsAC->campaigns[0];
      $displayData['lCampID'] = $lCampID;
      $displayData['lAcctID'] = $lAcctID = $camp->lAcctID;
      $displayData['strCamp'] = $camp->strSafeName;      
      $displayData['strAcct'] = $strAcct = $camp->strAcctSafeName;
      $displayData['accts']   = &$this->clsAC->accounts;
      
         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtCamp', 'Campaign Name', 
                                'callback_campNameDupTest['.$lAcctID.','.$lCampID.']');
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/admin',                                   'Admin', 'class="breadcrumb"')
                                .' | '.anchor('admin/alists/showLists',                            'Lists', 'class="breadcrumb"')
                                .' | '.anchor('accts_camp/accounts/view',                          'Accounts & Campaigns', 'class="breadcrumb"')
                                .' | '.anchor('accts_camp/campaigns/viewCampsViaAcctID/'.$lAcctID, $strAcct.': View Campaigns', 'class="breadcrumb"')                                       
                                .' | Transfer Campaign';

      $displayData['title']          = CS_PROGNAME.' | Clients';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'accts_camp/camp_xfer';
      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   
   function xferCampaignSubmit($lCampID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      if (!bTestForURLHack('adminOnly')) return;
      $lAcctID = trim($_POST['ddlAcct']);
   
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lCampID, 'campaign ID');
      verifyID($this, $lAcctID, 'account ID');
      $lAcctID = (integer)$lAcctID;
   
         //---------------------------
         // models & helpers
         //---------------------------
      $this->load->model('donations/maccts_camps', 'clsAC');
   
      $this->clsAC->loadCampaigns(false, false, null, true, $lCampID);
      $this->clsAC->loadAccounts(false, true, $lAcctID);
      $this->clsAC->changeCampAcct($lCampID, $lAcctID);
      
      $this->session->set_flashdata('msg', 'Your campaign '.$this->clsAC->campaigns[0]->strSafeName
                               .' was moved to account '
                               .$this->clsAC->accounts[0]->strSafeName);

      redirect('accts_camp/campaigns/viewCampsViaAcctID/'.$lAcctID);
   }
   
   
   
}
