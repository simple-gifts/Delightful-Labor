<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class client_rec_voc extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEdit($lClientID){
   //--------------------------------------------------------------------------
   // the user can change the vocabulary associated with a client
   //--------------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      $displayData = array();
      $displayData['lClientID'] = $lClientID = (integer)$lClientID;

         //-------------------------------------
         // load the client info
         //-------------------------------------
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('clients/mclients', 'clsClients');
      $this->clsClients->loadClientsViaClientID($lClientID);
      $displayData['client'] = $client = $this->clsClients->clients[0];
      $this->load->model('clients/mclient_vocabulary', 'clsClientV');

         //-------------------------------------
         // validation rules
         //-------------------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('ddlVoc',  'Client Vocabularies', 'trim|callback_clientVocDDL');


      if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');

         $this->load->model('util/mbuild_on_ready', 'clsOnReady');
         $this->clsOnReady->addOnReadyTableStripes();
         $this->clsOnReady->closeOnReady();
         $displayData['js'] = $this->clsOnReady->strOnReady;

         if (validation_errors()==''){
            $displayData['ddlVoc'] = $this->clsClientV->strClientVocDDL(false, $client->lVocID);
         }else {
            setOnFormError($displayData);
            $displayData['ddlVoc'] = $this->clsClientV->strClientVocDDL(false, set_value('ddlVoc'));
         }

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

            //---------------------------------------------------
            // for user's reference - all defined vocabularies
            //---------------------------------------------------
         $this->load->helper('clients/client_voc');
         $this->clsClientV->loadClientVocabulary(false, true);
         $displayData['vocs'] = $this->clsClientV->vocs;        
         
            //-------------------------------------
            // breadcrumbs and page layout
            //-------------------------------------
         $displayData['pageTitle']  = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                               .' | '.anchor('clients/client_record/view/'.$lClientID, 'Client Record', 'class="breadcrumb"')
                               .' | Vocabulary';
         $displayData['title']       = CS_PROGNAME.' | Client Status';
         $displayData['nav']         = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate'] = 'client/vocab_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $lVocID = (integer)$_POST['ddlVoc'];
         $this->clsClientV->updateClientVocAssociation($lClientID, $lVocID);
         $this->session->set_flashdata('msg', 'The client\'s vocabulary was updated');
         redirect('clients/client_record/view/'.$lClientID);
      }


   }


}
