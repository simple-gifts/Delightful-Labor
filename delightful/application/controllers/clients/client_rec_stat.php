<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class client_rec_stat extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewStatusHistory($lClientID){
   //--------------------------------------------------------------------------
   //
   //--------------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      $this->load->helper('dl_util/verify_id');
      verifyIDsViaType($this, CENUM_CONTEXT_CLIENT, $lClientID, true);
   
      $displayData = array();
      $displayData['lClientID'] = $lClientID = (integer)$lClientID;

         //-------------------------------------
         // load the client info
         //-------------------------------------
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('clients/mclients', 'clsClients');
      $this->load->helper('clients/client');      
      $this->clsClients->loadClientsViaClientID($lClientID);
      $displayData['client'] = $this->clsClients->clients[0];

         //-------------------------------------
         // load the client's status
         //-------------------------------------
      $this->load->model('clients/mclient_status', 'clsClientStat');
      $this->clsClientStat->lClientID = $lClientID;
      $this->clsClientStat->fullStatusHistory(false, null, false);
      $displayData['clientStatus']     = $this->clsClientStat->clientStatus;
      $displayData['lNumClientStatus'] = $this->clsClientStat->lNumClientStatus;

         /*-------------------------------------
            load the client summary block
         -------------------------------------*/
      $this->load->model  ('img_docs/mimage_doc',   'clsImgDoc');
      $this->load->helper ('img_docs/image_doc');      
      $this->load->helper ('img_docs/link_img_docs');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params, 'generic_rpt');
      $displayData['clsRpt'] = $this->generic_rpt;
      $displayData['contextSummary'] = $this->clsClients->strClientHTMLSummary(0);

         //-------------------------------------
         // stripes
         //-------------------------------------
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

         //-------------------------------------
         // breadcrumbs and page layout
         //-------------------------------------
      $displayData['pageTitle']  = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                            .' | '.anchor('clients/client_record/view/'.$lClientID, 'Client Record', 'class="breadcrumb"')
                            .' | Status History';
      $displayData['title']       = CS_PROGNAME.' | Client Status';
      $displayData['nav']         = $this->mnav_brain_jar->navData();


      $displayData['mainTemplate'] = 'client/status_history_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addEditStatusEntry($lClientID, $lStatusID=0){
   //--------------------------------------------------------------------------
   //
   //--------------------------------------------------------------------------
      global $gdteNow, $gbDateFormatUS, $gstrFormatDatePicker;
      
      if (!bTestForURLHack('showClients')) return;
      $this->load->helper('dl_util/verify_id');
      verifyIDsViaType($this, CENUM_CONTEXT_CLIENT, $lClientID, true);
      if ($lStatusID.'' !='0') verifyID($this, $lStatusID, 'status entry ID');

      $displayData = array();
      $displayData['lClientID'] = $lClientID = (integer)$lClientID;
      $displayData['lStatusID'] = $lStatusID = (integer)$lStatusID;
      $displayData['bNew']      = $bNew      = $lStatusID <= 0;

      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/web_layout');

         //-------------------------------------
         // load the client info
         //-------------------------------------
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('clients/mclients', 'clsClients');
      $this->clsClients->loadClientsViaClientID($lClientID);
      $displayData['client'] = $client = $this->clsClients->clients[0];

         //-------------------------------------
         // load single client status record
         //-------------------------------------
      $this->load->model('clients/mclient_status', 'clsClientStat');
      $this->clsClientStat->lClientID = $lClientID;
      $this->clsClientStat->fullStatusHistory(true, $lStatusID);
      $displayData['clsEntry']  = $clsEntry = $this->clsClientStat->clientStatus[0];
      $displayData['statCatID'] = $statCatID = $client->lStatusCatID;     // status category of this client
      
         //-------------------------------------
         // validation rules
         //-------------------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('ddlStatus',  'Status Entry', 'trim|callback_clientStatEntryDDL');
      $this->form_validation->set_rules('txtDate',    'Status date', 'trim|required|callback_clientStatVerifyDateValid'
                                                                                 .'|callback_clientStatVerifyDatePast');
      $this->form_validation->set_rules('txtNotes',  'Notes', 'trim');
      $this->form_validation->set_rules('chkPacket', 'Packet Notes');

      if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');

         if (validation_errors()==''){
            $displayData['statDDL'] =
                   $this->clsClientStat->strClientStatEntriesDDL($statCatID, true, $clsEntry->lStatusID, true);
            if ($bNew){
               $displayData['txtDate'] = date($gstrFormatDatePicker, $gdteNow);
            }else {
               $displayData['txtDate'] = date($gstrFormatDatePicker, $clsEntry->dteStatus);
            }
            $displayData['txtNotes']      = $clsEntry->strStatusTxt;
            $displayData['bPacketStatus'] = $clsEntry->bIncludeNotesInPacket;
         }else {
            setOnFormError($displayData);
            $displayData['statDDL'] =
                   $this->clsClientStat->strClientStatEntriesDDL($statCatID, true, set_value('ddlStatus'), true);
            $displayData['txtDate']       = set_value('txtDate');
            $displayData['txtNotes']      = set_value('txtNotes');
            $displayData['bPacketStatus'] = set_value('chkPacket')=='TRUE';
            
         }

            /*-------------------------------------
               load the client summary block
            -------------------------------------*/
         $this->load->model  ('img_docs/mimage_doc',   'clsImgDoc');
         $this->load->helper ('img_docs/image_doc');      
            
         $params = array('enumStyle' => 'terse');
         $this->load->library('generic_rpt', $params, 'generic_rpt');
         $displayData['clsRpt']         = $this->generic_rpt;
         $displayData['contextSummary'] = $this->clsClients->strClientHTMLSummary(0);

            //-------------------------------------
            // breadcrumbs and page layout
            //-------------------------------------
         $displayData['pageTitle']  = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                               .' | '.anchor('clients/client_record/view/'.$lClientID, 'Client Record', 'class="breadcrumb"')
                               .' | '.anchor('clients/client_rec_stat/viewStatusHistory/'.$lClientID, 'Status History', 'class="breadcrumb"')
                               .' | '.($bNew ? 'Add ' : 'Edit ').'Status';
         $displayData['title']       = CS_PROGNAME.' | Client Status';
         $displayData['nav']         = $this->mnav_brain_jar->navData();
         
         $displayData['mainTemplate'] = 'client/status_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $clsEntry->lStatusID             = (integer)$_POST['ddlStatus'];
         $clsEntry->bIncludeNotesInPacket = @$_POST['chkPacket']=='TRUE';
         MDY_ViaUserForm(trim($_POST['txtDate']), $lMon, $lDay, $lYear, $gbDateFormatUS);
         $clsEntry->dteStatus = strtotime($lMon.'/'.$lDay.'/'.$lYear);
         $clsEntry->strStatusTxt = trim($_POST['txtNotes']);

         if ($bNew){
            $clsEntry->lClientID = $lClientID;
            $this->clsClientStat->lInsertClientStatus();
            $this->session->set_flashdata('msg', 'A client status entry was added');

         }else {
            $clsEntry->lKeyID = $lStatusID;
            $this->clsClientStat->updateClientStatus();
            $this->session->set_flashdata('msg', 'The client status entry was updated');
         }
         redirect('clients/client_rec_stat/viewStatusHistory/'.$client->lKeyID);
      }
   }


      //-----------------------------
      // verification routines
      //-----------------------------
   function clientStatVerifyDateValid($strDate){
      return(bValidVerifyDate($strDate));
   }

   function clientStatVerifyDatePast($strDate){
      return(bValidVerifyNotFuture($strDate));
   }

   function clientStatEntryDDL($strDDL){
      return((integer)$strDDL > 0);
   }

   function remove($lClientID, $lStatRecID){
      if (!bTestForURLHack('showClients')) return;
      $this->load->helper('dl_util/verify_id');
      verifyIDsViaType($this, CENUM_CONTEXT_CLIENT, $lClientID, true);
      verifyID($this, $lStatRecID, 'status entry ID');
   
      $lClientID  = (integer)$lClientID;
      $lStatRecID = (integer)$lStatRecID;
      $this->load->model('clients/mclient_status', 'clsClientStat');
      $this->clsClientStat->removeClientStatusEntry($lStatRecID);

      $this->session->set_flashdata('msg', 'The client status entry was removed');
      redirect('clients/client_rec_stat/viewStatusHistory/'.$lClientID);
   }


   function addEditStatCat($lClientID){
   //--------------------------------------------------------------------------
   // the user can change the status category associated with a client
   //--------------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      $displayData = array();
      $displayData['lClientID'] = $lClientID = (integer)$lClientID;

         //-------------------------------------
         // load the client info
         //-------------------------------------
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('clients/mclients', 'clsClients');
      $this->load->model('clients/mclient_status', 'clsClientStat');
      $this->clsClients->loadClientsViaClientID($lClientID);
      $displayData['client'] = $client = $this->clsClients->clients[0];

         //-------------------------------------
         // validation rules
         //-------------------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('ddlStatCats',  'Client Status Categories', 'trim|callback_clientVocDDL');

      if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');
         $this->load->helper('clients/client');

            /*-------------------------------------
               load the client summary block
            -------------------------------------*/
         $this->load->model  ('img_docs/mimage_doc',   'clsImgDoc');
         $this->load->helper ('img_docs/image_doc');      
         $params = array('enumStyle' => 'terse');
         $this->load->library('generic_rpt', $params, 'generic_rpt');
         $displayData['clsRpt'] = $this->generic_rpt;
         $displayData['contextSummary'] = $this->clsClients->strClientHTMLSummary(0);

            //-------------------------------------
            // stripes
            //-------------------------------------
         $this->load->model('util/mbuild_on_ready', 'clsOnReady');
         $this->clsOnReady->addOnReadyTableStripes();
         $this->clsOnReady->closeOnReady();
         $displayData['js'] = $this->clsOnReady->strOnReady;

            //-------------------------------------
            // status category info
            //-------------------------------------
         $this->clsClientStat->loadClientStatCats(true, false, null);
         $displayData['numStatCat']   = $this->clsClientStat->lNumStatCats;
         $displayData['statCats']     = $this->clsClientStat->statCats;

         foreach ($displayData['statCats'] as $clsCat){
            $lKeyID = $clsCat->lKeyID;
            $this->clsClientStat->loadClientStatCatsEntries(
                                  true,  $lKeyID,
                                  false, null,
                                  true,  false);

            $displayData['statCatsEntries'][$lKeyID] = $this->clsClientStat->catEntries;
         }

         if (validation_errors()==''){
            $displayData['ddlStatCats'] = $this->clsClientStat->strClientStatCatDDL(false, $client->lStatusCatID);
         }else {
            setOnFormError($displayData);
            $displayData['ddlStatCats'] = $this->clsClientStat->cstrClientStatCatDDL(false, set_value('ddlStatCats'));
         }
         
            //-------------------------------------
            // breadcrumbs and page layout
            //-------------------------------------
         $displayData['pageTitle']  = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                               .' | '.anchor('clients/client_record/view/'.$lClientID, 'Client Record', 'class="breadcrumb"')
                               .' | Status Category';
         $displayData['title']       = CS_PROGNAME.' | Client Status';
         $displayData['nav']         = $this->mnav_brain_jar->navData();


         $displayData['mainTemplate'] = 'client/status_cat_change_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $lCStatID = (integer)$_POST['ddlStatCats'];
         $this->clsClientStat->updateClientStatAssociation($lClientID, $lCStatID);      
         $this->session->set_flashdata('msg', 'The client\'s status category was updated');
         redirect('clients/client_record/view/'.$lClientID);
      }
   }



}