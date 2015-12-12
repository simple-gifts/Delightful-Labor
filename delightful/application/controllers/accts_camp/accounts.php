<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class accounts extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      if (!bTestForURLHack('adminOnly')) return;

      $displayData = array();
      $displayData['title']        = CS_PROGNAME.' | Accounts';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"') 
                              .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                              .' | Accounts & Campaigns';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->model('donations/maccts_camps', 'clsAC');
      $this->load->model('donations/mdonations',  'clsGifts');
      $this->load->model('admin/madmin_aco',       'clsACO');
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      

      $this->load->helper('dl_util/record_view');
      $this->load->helper('img_docs/link_img_docs');

      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);

      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

      $this->clsGifts->bUseDateRange = false;
      $this->clsGifts->enumCumulativeSource = 'account';

      $this->clsAC->loadAccounts(false, false, null);
      $displayData['lNumAccts'] = $this->clsAC->lNumAccts;
      $displayData['clsAC']     = $this->clsAC;
      if ($displayData['lNumAccts'] > 0){
         $displayData['accts'] = $this->clsAC->accounts;

         $idx = 0;
         foreach ($this->clsAC->accounts as $clsAcct){
               //-------------------
               // cumulative gifts
               //-------------------
            $lAID = $clsAcct->lKeyID;
            $this->clsGifts->lAcctID = $lAID;

            $this->clsGifts->cumulativeOpts = new stdClass;
            $this->clsGifts->cumulativeOpts->enumCumulativeSource = 'account';
            $this->clsGifts->cumulativeOpts->bSoft                = false;
            $this->clsGifts->cumulativeOpts->enumMoneySet         = 'all';
            $this->clsGifts->cumulativeDonation($this->clsACO, $lTotHardGifts);

            $displayData['strCumGiftsNonSoft'][$idx] =
                      strBuildCumlativeTable($this->clsGifts->lNumCumulative, $this->clsGifts->cumulative, true);

            if ($this->clsGifts->lNumCumulative > 0){
               $displayData['strLinkGifts'][$idx] = '';
            }else {
               $displayData['strLinkGifts'][$idx] = '';
            }
            ++$idx;
         }
      }

      $displayData['mainTemplate'] = 'accts_camp/account_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addEdit($id){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      if (!bTestForURLHack('adminOnly')) return;
      $this->load->helper('dl_util/verify_id');
      verifyIDsViaType($this, CENUM_CONTEXT_ACCOUNT, $id, true);   
   
      $displayData = array();

      $id = (integer)$id;
      $displayData['bNew'] = $bNew = $id <= 0;

      $displayData['lAcctID']      = $id;

      $this->load->model('donations/maccts_camps', 'clsAC');
      
         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtAcct', 'Account Name', 'callback_acctNameDupTest['.$id.']');

      if ($this->form_validation->run() == FALSE){

         $displayData['title']        = CS_PROGNAME.' | Accounts';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"') 
                                 .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                                 .' | '.anchor('accts_camp/accounts/view', 'Accounts & Campaigns', 'class="breadcrumb"')
                                 .' |  '.($bNew ? 'Add New Account' : 'Update Account');
         $displayData['nav']          = $this->mnav_brain_jar->navData();
         $this->clsAC->loadAccounts(false, true, $id);
         $displayData['clsAcct'] = $this->clsAC->accounts[0];
         $displayData['bAnyGifts'] = $this->clsAC->bAnyGiftsViaAcctID($id);

         $this->load->library('generic_form');
         $displayData['clsForm'] = $this->generic_form;

         if (validation_errors()==''){
            $displayData['strAccount'] = $this->clsAC->accounts[0]->strSafeName;
         }else {
            setOnFormError($displayData);
            $displayData['strAccount'] = set_value('txtAcct');
         }

         $displayData['mainTemplate'] = 'accts_camp/account_add_edit';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->clsAC->accounts[0]->strAccount = $strAcct = xss_clean(trim($_POST['txtAcct']));
         if ($bNew){
            $this->clsAC->lAddNewAccount();
            $this->session->set_flashdata('msg', 'Your new account was added');            
         }else {
            $this->clsAC->accounts[0]->bRetired = @$_POST['chkRetire']=='TRUE';
            $this->clsAC->accounts[0]->lKeyID   = $id;
            $this->clsAC->updateAccount();
            $this->session->set_flashdata('msg', 'Your account was updated');
         }
         redirect('accts_camp/accounts/view');
      }
   }

   function acctNameDupTest($strAcct, $id){
      $strAcct = xss_clean(trim($strAcct));
      if ($strAcct==''){
         $this->form_validation->set_message('acctNameDupTest', 'The account name can not be blank.');
         return(false);
      }else {
         $this->load->model('util/mverify_unique', 'clsUnique');
         if (!$this->clsUnique->bVerifyUniqueText(
                   $strAcct, 'ga_strAccount',
                   $id,   'ga_lKeyID',
                   true,  'ga_bRetired',
                   false, null, null,
                   false, null, null,
                   'gifts_accounts')){
            $this->form_validation->set_message('acctNameDupTest', 'The account name "'.htmlspecialchars($strAcct).'" is already defined.');
            return(false);
         }else {
            return(true);
         }
      }
   }
   

}
