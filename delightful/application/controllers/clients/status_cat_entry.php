<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class status_cat_entry extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEdit($lCatID, $lCatEntryID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lCatID, 'status category ID');
      if ($lCatEntryID != '0') verifyID($this, $lCatEntryID, 'status entry list ID');

      $lCatID      = (integer)$lCatID;
      $lCatEntryID = (integer)$lCatEntryID;
      $bNew        = $lCatEntryID <= 0;
      $this->load->model('clients/mclient_status', 'clsClientStat');

      $this->clsClientStat->loadClientStatCats(true, true, $lCatID);
      $strCatName = htmlspecialchars($this->clsClientStat->statCats[0]->strCatName);

         // load record for status category entry
      $this->clsClientStat->loadClientStatCatsEntries(
                                        false, $lCatID,
                                        true,  $lCatEntryID,
                                        true,  true);

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtStatEntry', 'Status Entry', 'xss_clean|trim|required|'
                                                .'callback_verifyUniqueStatCatE['.$lCatEntryID.','.$lCatID.']');

		$this->form_validation->set_rules('chkAllowSpon');
		$this->form_validation->set_rules('chkShowInDir');
		$this->form_validation->set_rules('chkDefault');

		$this->form_validation->set_rules('txtNotes');

		if ($this->form_validation->run() == FALSE){
         $displayData = array();
         $displayData['formD'] = new stdClass;
         $this->load->library('generic_form');

         if (validation_errors()==''){
            $displayData['formD']->txtStatEntry      = $this->clsClientStat->catEntries[0]->strStatusEntry;
            $displayData['formD']->bAllowSponsorship = $this->clsClientStat->catEntries[0]->bAllowSponsorship;
            $displayData['formD']->bShowInDir        = $this->clsClientStat->catEntries[0]->bShowInDir;
            $displayData['formD']->bDefault          = $this->clsClientStat->catEntries[0]->bDefault;
         }else {
            setOnFormError($displayData);
            $displayData['formD']->txtStatEntry      = set_value('txtStatEntry');
            $displayData['formD']->bAllowSponsorship = set_value('chkAllowSpon')=='TRUE';
            $displayData['formD']->bShowInDir        = set_value('chkShowInDir')=='TRUE';
            $displayData['formD']->bDefault          = set_value('chkDefault')=='TRUE';
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                   .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb" ')
                                   .' | '.anchor('admin/admin_special_lists/clients/statCatView', 'Client Status Categories', 'class="breadcrumb" ')
                                   .' | '.anchor('admin/admin_special_lists/clients/statCatEntries/'.$lCatID, $strCatName, 'class="breadcrumb" ')
                                   .' | '.($bNew ? 'Add new status entry' : 'Edit status entry');

         $displayData['title']          = CS_PROGNAME.' | Lists';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['lCatID']         = $lCatID;
         $displayData['lCatEntryID']    = $lCatEntryID;
         $displayData['bNew']           = $bNew;
         $displayData['strCatName']     = $strCatName;
         $displayData['clsCatEntry']    = $this->clsClientStat->catEntries[0];
         $displayData['mainTemplate']   = 'client/client_status_entry_add_edit_view';

         $this->load->vars($displayData);
         $this->load->view('template');

      }else {
            $this->clsClientStat->catEntries[0]->lClientStatusCatID = $lCatID;
            $this->clsClientStat->catEntries[0]->strStatusEntry     = xss_clean($_POST['txtStatEntry']);
            $this->clsClientStat->catEntries[0]->bAllowSponsorship  = @$_POST['chkAllowSpon']=='TRUE';
            $this->clsClientStat->catEntries[0]->bShowInDir         = @$_POST['chkShowInDir']=='TRUE';
            $this->clsClientStat->catEntries[0]->bDefault           = $bDefault = @$_POST['chkDefault']=='TRUE';

         if ($bDefault){
            $this->clsClientStat->clearClientStatEntryDefForSCID($lCatID);
         }

         if ($lCatEntryID==0){
            $lCatEntryID = $this->clsClientStat->addNewClientStatusEntry();
            $this->session->set_flashdata('msg', 'Your status entry was added');
         }else {
            $this->clsClientStat->catEntries[0]->lKeyID = $lCatEntryID;
            $this->clsClientStat->updateClientStatusEntry();
            $this->session->set_flashdata('msg', 'Your status entry was updated');
         }
         redirect('admin/admin_special_lists/clients/statCatEntries/'.$lCatID);
      }
   }

   function verifyUniqueStatCatE($strName, $strID){
      $idArray = explode(',', $strID);
      $lID = (int)$idArray[0];
      $lCatID = (int)$idArray[1];
      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                trim($strName), 'cst_strStatus',
                $lID, 'cst_lKeyID',
                true,    'cst_bRetired',
                true, $lCatID, 'cst_lClientStatusCatID',
                false, null, null,
                'lists_client_status_entries')){
         $this->form_validation->set_message('verifyUniqueStatCatE',
                   'This <b>Client Status Category Entry</b> is already being used.');
         return(false);
      }else {
         return(true);
      }
   }


   function remove($lCatID, $lCatEntryID){
      if (!bTestForURLHack('showClients')) return;
      $lCatID      = (integer)$lCatID;
      $lCatEntryID = (integer)$lCatEntryID;
      $this->load->model('clients/mclient_status', 'clsClientStat');
      $this->clsClientStat->removeEntry($lCatEntryID);
      $this->session->set_flashdata('msg', 'Your selected status entry was removed');
      redirect('admin/admin_special_lists/clients/statCatEntries/'.$lCatID);
   }

}