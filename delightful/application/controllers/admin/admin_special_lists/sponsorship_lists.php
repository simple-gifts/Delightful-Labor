<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class sponsorship_lists extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function sponProgView(){
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->load->model('admin/madmin_aco',                  'clsACO');
      $this->load->model('sponsorship/msponsorship',         'clsSpon');

      $this->clsSponProg->loadSponProgs();
      $displayData['lNumSponProg'] = $lNumSponProg = $this->clsSponProg->lNumSponPrograms;

      if ($lNumSponProg > 0){
         $idx = 0;
         $displayData['sponProg'] = array();
         foreach ($this->clsSponProg->sponProgs as $clsSingleProg){
            $displayData['sponProg'][$idx] = new stdClass;
            $displayData['sponProg'][$idx]->lSPID = $lSPID = $clsSingleProg->lKeyID;
            $this->clsACO->loadCountries(false, false, true, $clsSingleProg->lACO);
            $displayData['sponProg'][$idx]->strProg             = $clsSingleProg->strProg;
            $displayData['sponProg'][$idx]->strCurrencySymbol   = $this->clsACO->countries[0]->strCurrencySymbol;
            $displayData['sponProg'][$idx]->curDefMonthlyCommit = $clsSingleProg->curDefMonthlyCommit;
            $displayData['sponProg'][$idx]->strFlagImg          = $this->clsACO->countries[0]->strFlagImg;
            $displayData['sponProg'][$idx]->lNumSponsors        = $this->clsSpon->lNumSponsorsViaProgram($lSPID, false, '');

            ++$idx;
         }
      }

         //----------------------
         // set breadcrumbs
         //----------------------
      $displayData['title']        = CS_PROGNAME.' | Sponsorship Programs';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                              .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                              .' | Sponsorship Programs';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate'] = 'admin/alist_spon_progs';

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addEditProg($lSPID){
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();

      $displayData['lSPID'] = $lSPID = (integer)$lSPID;
      $displayData['bNew']  = $bNew = $lSPID <= 0;

        //----------------------------
        // validation rules
        //----------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtProg', 'Program Name',
                          'trim|callback_sponProgBlankTest|callback_sponProgDupTest['.$lSPID.']');
		$this->form_validation->set_rules('txtCommit', 'Commitment Amount',  'trim|required|callback_stripCommas|numeric|greater_than[-0.01]');
		$this->form_validation->set_rules('rdoACO',    'Accounting Country', 'trim|callback_sponProgAcctCountry');


      $this->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->load->model('admin/madmin_aco', 'clsACO');

      $this->clsSponProg->lSPID = $lSPID;
      $this->clsSponProg->loadSponProgsGeneric(true);

      if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');

         if (validation_errors()==''){
            $displayData['strSponProg']         = htmlspecialchars($this->clsSponProg->sponProgs[0]->strProg);
            $displayData['strACORadio']         = $this->clsACO->strACO_Radios($this->clsSponProg->sponProgs[0]->lACO, 'rdoACO');
            $displayData['curDefMonthlyCommit'] = $this->clsSponProg->sponProgs[0]->curDefMonthlyCommit;
         }else {
            setOnFormError($displayData);
            $displayData['strSponProg']         = set_value('txtProg');
            $displayData['strACORadio']         = $this->clsACO->strACO_Radios(set_value('rdoACO'), 'rdoACO');
            $displayData['curDefMonthlyCommit'] = set_value('txtCommit');
         }

            //----------------------
            // set breadcrumbs
            //----------------------
         $displayData['nav']           = $this->mnav_brain_jar->navData();
         $displayData['title']        = CS_PROGNAME.' | Groups';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                 .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                                 .' | '.anchor('admin/admin_special_lists/sponsorship_lists/sponProgView', 'Sponsorship Programs', 'class="breadcrumb"')
                                         .' | '.($bNew ? 'Add New' : 'Edit');

         $displayData['mainTemplate'] = 'sponsorship/spon_prog_add_edit';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->clsSponProg->sponProgs[0]->bDefault            = false;
         $this->clsSponProg->sponProgs[0]->strProg             = xss_clean($_POST['txtProg']);
         $this->clsSponProg->sponProgs[0]->strNotes            = '';
         $this->clsSponProg->sponProgs[0]->curDefMonthlyCommit = (float)$_POST['txtCommit'];
         $this->clsSponProg->sponProgs[0]->lACO                = (integer)$_POST['rdoACO'];

         if ($bNew){
            $id = $this->clsSponProg->addNewSponProgram();
            $this->session->set_flashdata('msg', 'Your sponsorship program was added');
         }else {
            $this->clsSponProg->sponProgs[0]->lKeyID = $lSPID;
            $this->clsSponProg->updateSponProgram();
            $this->session->set_flashdata('msg', 'Your sponsorship program was updated');
         }
         redirect('admin/admin_special_lists/sponsorship_lists/sponProgView');
      }
   }


   //-------------------------------------------------------------------------------
   // verification callbacks
   //-------------------------------------------------------------------------------
   function sponProgBlankTest($strField){
      return($strField != '');
   }
   
   function stripCommas(&$strAmount){
      $strAmount = str_replace (',', '', $strAmount);
      return(true);
   }   

   function sponProgDupTest($strProg, $strSPID){
      $lSPID = (integer)$strSPID;

      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                $strProg,  'sc_strProgram',
                $lSPID,    'sc_lKeyID',
                true,      'sc_bRetired',
                false,      null,   null,
                false,      null,   null,
                'lists_sponsorship_programs')){
         return(false);
      }else {
         return(true);
      }
   }

   function sponProgAcctCountry($strField){
      return($strField != '');
   }

   function retireProg($lProgID){
      if (!bTestForURLHack('adminOnly')) return;
      $lProgID = (integer)$lProgID;

      $this->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->clsSponProg->loadSponProgsViaSPID($lProgID);
      $clsProgram = $this->clsSponProg->sponProgs[0];

      $lNumClients  = $this->clsSponProg->lNumClientsViaSponProg($lProgID);
      $lNumSponsors = $this->clsSponProg->lNumSponsorsViaSponProg($lProgID);

      if ($lNumClients > 0 || $lNumSponsors > 0){
         $displayData = array();
         $displayData['lNumClients']  = $lNumClients;
         $displayData['lNumSponsors'] = $lNumSponsors;
         $displayData['lProgID']      = $lProgID;
         $displayData['clsProgram']   = $clsProgram;

            //----------------------
            // set breadcrumbs
            //----------------------
         $displayData['title']        = CS_PROGNAME.' | Sponsorship';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                 .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                                 .' | '.anchor('admin/admin_special_lists/sponsorship_lists/sponProgView', 'Sponsorship Programs', 'class="breadcrumb"')
                                 .' | Remove Sponsorship';
         $displayData['nav']          = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate'] = 'admin/alist_spon_progs_rem_error';

         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->clsSponProg->retireSponProgram($lProgID);
         $this->session->set_flashdata('msg', 'The sponsorship program <b>'
                          .htmlspecialchars($clsProgram->strProg).'</b> was removed');
         redirect('admin/admin_special_lists/sponsorship_lists/sponProgView');
      }
   }
   
   
   
}