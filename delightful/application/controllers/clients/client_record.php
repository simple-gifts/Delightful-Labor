<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class client_record extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function view($lClientID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $glUserID;
      
      if (!bTestForURLHack('showClients')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lClientID, 'client ID');

         //-------------------------
         // models & helpers
         //-------------------------
      $displayData = array();
      $displayData['lCID'] = $lClientID = (integer)$lClientID;
      $displayData['js'] = '';

      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

      $this->load->library('util/dl_date_time', '',                'clsDateTime');
      $this->load->helper ('img_docs/img_doc_tags');               
      $this->load->model  ('clients/mclients',                     'clsClient');
      $this->load->model  ('img_docs/mimage_doc',                  'clsImgDoc');
      $this->load->model  ('img_docs/mimg_doc_tags',               'cidTags');
      $this->load->model  ('groups/mgroups',                       'groups');
      $this->load->model  ('clients/mclient_locations',            'clsLoc');
      $this->load->model  ('admin/madmin_aco');                    
      $this->load->model  ('custom_forms/mcustom_forms',           'cForm');
      $this->load->model  ('client_features/mcprograms',           'cprograms');
      $this->load->model  ('client_features/mcpre_post_tests',     'cpptests');
      $this->load->model  ('admin/muser_accts');                   
      $this->load->model  ('admin/mpermissions',                   'perms');

      $this->load->model  ('personalization/muser_fields',         'clsUF');
      $this->load->model  ('personalization/muser_fields_display', 'clsUFD');
      $this->load->model  ('util/mlist_generic',                   'clsList');

      $this->load->helper ('groups/groups');
      $this->load->helper ('clients/client');
      $this->load->helper ('personalization/ptable');
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('clients/link_client_features');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //----------------------------------------------------
         // set up links to the custom forms (if any) and
         // associated permissions
         //----------------------------------------------------
      $this->cForm->loadCustomFormsViaType(CENUM_CONTEXT_CLIENT);
      $displayData['lNumCustomForms'] = 0;
      $lNumCForms = $this->cForm->lNumCustomForms;
      $this->perms->loadUserAcctInfo($glUserID, $acctAccess);
      if ($lNumCForms > 0){
         foreach ($this->cForm->customForms as $cform){
            $cform->bShowCFormLink = $this->perms->bDoesUserHaveAccess(
                                          $acctAccess, $cform->lNumConsolidated, $cform->cperms);
            if ($cform->bShowCFormLink) ++$displayData['lNumCustomForms'];

               // form history log
            $this->cForm->formLogViaCFID_FID($cform->lKeyID, $lClientID, $cform->lNumLogEntries, $cform->formLog);
         }
      }
      
      $displayData['cForms'] = &$this->cForm->customForms;

         //----------------------------------------------------
         // set up links to the custom programs (if any) and
         // associated permissions
         //----------------------------------------------------
      $this->cprograms->loadClientPrograms(false);
      $displayData['lNumCProgs'] = 0;
      $lNumCProgs = $this->cprograms->lNumCProgs;

      if ($lNumCProgs > 0){
         foreach ($this->cprograms->cprogs as $cprog){
            $cprog->bShowCProgLink = $this->perms->bDoesUserHaveAccess(
                                          $acctAccess, $cprog->lNumPerms, $cprog->perms);
            if ($cprog->bShowCProgLink){
               ++$displayData['lNumCProgs'];
               $cprog->bEnrolled = $this->cprograms->bIsClientInProgram(
                                        $lClientID, $cprog, $cprog->lNumEnrollments, $cprog->erecs);
               if ($cprog->lNumEnrollments > 0){
                  $cprog->lTotAttend = 0;
                  foreach ($cprog->erecs as $erec){
                     $lERecID = $erec->lKeyID;
                     $erec->lNumAttend = $this->cprograms->lNumAttendanceViaEnrollID($lERecID, $cprog);
                     $cprog->lTotAttend += $erec->lNumAttend;
                  }
               }
            }
         }
      }
      $displayData['cProgs'] = &$this->cprograms->cprogs;

         //----------------------------------------------------
         // set up links to the client pre/post test (if any) and
         // associated permissions
         //----------------------------------------------------
      $this->cpptests->loadPPCatsAndTests($displayData['lNumCats'], $displayData['ppcats'], true);

      $displayData['lTotTests'] = 0;
      foreach ($displayData['ppcats'] as $ppcat){

         $lNumPPTests = $ppcat->lNumPPTests;

         if ($lNumPPTests > 0){
            foreach ($ppcat->pptests as $pptest){
               $pptest->bShowTest = $this->perms->bDoesUserHaveAccess(
                                             $acctAccess, $pptest->lNumPerms, $pptest->perms);
               if ($pptest->bShowTest){
                  $lPPTestID = $pptest->lKeyID;
                  $this->cpptests->clientTestsViaTestID($lClientID, $lPPTestID, $pptest->lNumTests, $pptest->testInfo);

                  ++$ppcat->lNumPPTests;
                  ++$displayData['lTotTests'];
               }
            }
         }
      }

      $this->clsClient->loadClientsViaClientID($lClientID);
      $displayData['strName']   = $this->clsClient->clients[0]->strSafeName;
      $displayData['clsClient'] = &$this->clsClient;

      $bShowEMR = $displayData['bShowEMR'] = $this->clsClient->clients[0]->bEnableEMR;
      if ($bShowEMR){
         $this->load->model('emr/mmeasurements', 'emrMeas');
         $this->load->helper('emr/link_emr');
         $this->load->library('util/dl_date_time', '', 'clsDateTime');
         $displayData['emr'] = new stdClass;
         $displayData['emr']->lNumMeasure = $this->emrMeas->lNumMeasureViaCID($lClientID, true, true, true);
      }

      $this->load->model('reminders/mreminders', 'clsRem');
      $displayData['clsRem'] = $this->clsRem;

         //-------------------------------
         // personalized tables
         //-------------------------------
      $displayData['strPT'] = strPTableDisplay(CENUM_CONTEXT_CLIENT, $lClientID,
                                  $this->clsUFD, $this->perms, $acctAccess,
                                  $displayData['strFormDataEntryAlert'],
                                  $displayData['lNumPTablesAvail']);

         //-------------------------------
         // client status history
         //-------------------------------
      $this->load->model('clients/mclient_status', 'clsClientStat');
      $this->clsClientStat->lClientID = $lClientID;
      $this->clsClientStat->fullStatusHistory(false, null);
      $displayData['clientStatus']     = $this->clsClientStat->clientStatus;
      $displayData['lNumClientStatus'] = $this->clsClientStat->lNumClientStatus;

         //-------------------------------
         // client transfers
         //-------------------------------
      $this->clsLoc->loadClientXfersViaClientID($lClientID);
      $displayData['lNumClientXfers'] = $this->clsLoc->lNumClientXfers;
      $displayData['clientXfers']     = &$this->clsLoc->clientXfers;

         //-------------------------------
         // client groups
         //-------------------------------
      $this->groups->groupMembershipViaFID(CENUM_CONTEXT_CLIENT, $lClientID);
      $displayData['inGroups']            = $this->groups->arrMemberInGroups;
      $displayData['lCntGroupMembership'] = $this->groups->lNumMemInGroups;
      $displayData['lNumGroups']          = $this->groups->lCntActiveGroupsViaType(CENUM_CONTEXT_CLIENT);
      $this->groups->loadActiveGroupsViaType(CENUM_CONTEXT_CLIENT, 'groupName', $this->groups->strMemListIDs, false, null);
      $displayData['groupList']           = $this->groups->arrGroupList;

         //-------------------------------
         // images and documents
         //-------------------------------
      loadImgDocRecView($displayData, CENUM_CONTEXT_CLIENT, $lClientID);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                .' | Client Record';

      $displayData['title']          = CS_PROGNAME.' | Clients';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'client/client_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   public function xfer1($lClientID){
      $lClientID = (integer)$lClientID;
      $this->load->helper('clients/client');
      $this->load->model  ('img_docs/mimage_doc',   'clsImgDoc');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper('img_docs/link_img_docs');
      clientBaseInfoForm($this, 'clientXfer', $lClientID);
   }

      //-------------------------
      // Verification
      //-------------------------
   function addClientVerifyLoc($strValue){
      $lLocID = (integer)$strValue;
      return($lLocID > 0);
   }

   function addClientVerifySCat($strValue){
      $lStatCatID = (integer)$strValue;
      return($lStatCatID > 0);
   }

   function addClientVerifyVoc($strValue){
      $lVocID = (integer)$strValue;
      return($lVocID > 0);
   }

   function clientRecVerifyEffDateValid($strEDate){
      return(bValidVerifyDate($strEDate));
   }

   function xfer2($lClientID, $lNewLocID, $lStatCatID, $lVocID, $dteEDate){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      $lClientID    = (integer)$lClientID;
      $lNewLocID    = (integer)$lNewLocID;
      $lStatCatID   = (integer)$lStatCatID;
      $lVocID       = (integer)$lVocID;
      $dteEDate     = (integer)$dteEDate;

      $this->load->library('util/dl_date_time', '',           'clsDateTime');
      $this->load->model('clients/mclients',                 'clsClients');
      $this->load->model('sponsorship/msponsorship',         'clsSpon');
      $this->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->load->model('clients/mclient_locations', 'clsLoc');

      $this->clsClients->loadClientsViaClientID($lClientID);
      $lOriginalLoc = $this->clsClients->clients[0]->lLocationID;

         //----------------------------------------------------------------
         // if the client is sponsored under a program not supported by
         // the new location, ask user what to do
         //----------------------------------------------------------------
      $this->clsSpon->sponsorsViaClientID($lClientID, true);
      if (($this->clsSpon->lNumSponsors==0) || ($lOriginalLoc==$lNewLocID)){
         $this->setNewLoc($lClientID, $lNewLocID, $lStatCatID, $lVocID, $dteEDate);
      }else {
         $this->findLocProgProbs($lNewLocID, $lNumProblemos);

         if ($lNumProblemos==0){
            $this->setNewLoc($lClientID, $lNewLocID, $lStatCatID, $lVocID, $dteEDate);
         }else {

            $displayData['lNumProblemos'] = $lNumProblemos;
            $displayData['bOneProblemo']  = $lNumProblemos==1;
            $displayData['lClientID']     = $lClientID;
            $displayData['lNewLocID']     = $lNewLocID;
            $displayData['lStatCatID']    = $lStatCatID;
            $displayData['lVocID']        = $lVocID;
            $displayData['dteEDate']      = $dteEDate;
            $displayData['sponProgs']     = &$this->clsSponProg->sponProgs;

            $this->clsLoc->loadLocationRec($lNewLocID);
            $displayData['strLocName'] = htmlspecialchars($this->clsLoc->strLocation);

               /*-------------------------------------
                  load the client summary block
               -------------------------------------*/
            $this->load->model  ('img_docs/mimage_doc',   'clsImgDoc');
            $this->load->helper ('img_docs/image_doc');
            $this->load->helper('img_docs/link_img_docs');

            $params = array('enumStyle' => 'terse');
            $this->load->library('generic_rpt', $params, 'generic_rpt');
            $this->clsClients->loadClientsViaClientID($lClientID);
            $displayData['contextSummary'] = $this->clsClients->strClientHTMLSummary(0);

               //--------------------------
               // breadcrumbs
               //--------------------------
            $displayData['pageTitle'] = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                 .' | '.anchor('clients/client_record/view/'.$lClientID, 'Client Record', 'class="breadcrumb"')
                                 .' | Transfer Client';

            $displayData['title']          = CS_PROGNAME.' | Clients';
            $displayData['nav']            = $this->mnav_brain_jar->navData();

            $displayData['mainTemplate']   = 'client/client_xfer_prob_view';
            $this->load->vars($displayData);
            $this->load->view('template');
         }
      }
   }

   function findLocProgProbs($lNewLocID, &$lNumProblemos){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->clsSponProg->loadSponProgs();
      foreach ($this->clsSponProg->sponProgs as $prog){
         $prog->bOldLoc = false;
         $prog->bNewLoc = false;
      }

         // sponsorship programs the client is currently associated with
      foreach ($this->clsSpon->sponInfo as $spon){
         $lProgID = $spon->lSponsorProgID;
         foreach ($this->clsSponProg->sponProgs as $prog){
            if ($prog->lKeyID == $lProgID){
               $prog->bOldLoc = true;
               break;
            }
         }
      }

         // sponsorship programs available at the new location
      $cNewLoc = new msponsorship_programs;
      $cNewLoc->loadSponProgsViaLocID($lNewLocID);
      foreach ($this->clsSponProg->sponProgs as $prog){
         $lProgID = $prog->lKeyID;
         foreach ($cNewLoc->sponProgs as $newprog){
            if ($newprog->lKeyID == $lProgID){
               $prog->bNewLoc = true;
               break;
            }
         }
      }

         // any programs not available at new location?
      $lNumProblemos = 0;
      foreach ($this->clsSponProg->sponProgs as $prog){
         if ($prog->bOldLoc && !$prog->bNewLoc){
            ++$lNumProblemos;
         }
      }
   }

   function xfer3($lClientID, $lNewLocID, $lStatCatID, $lVocID, $dteEDate){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      $lClientID  = (integer)$lClientID;
      $lNewLocID  = (integer)$lNewLocID;
      $lStatCatID = (integer)$lStatCatID;
      $lVocID     = (integer)$lVocID;
      $dteEDate   = (integer)$dteEDate;

      $this->load->library('util/dl_date_time', '',           'clsDateTime');
      $this->load->model('clients/mclients',                 'clsClients');
      $this->load->model('sponsorship/msponsorship',         'clsSpon');
      $this->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->load->model('clients/mclient_locations', 'clsLoc');

      $this->clsClients->loadClientsViaClientID($lClientID);
      $this->clsSpon->sponsorsViaClientID($lClientID, true);

      $this->findLocProgProbs($lNewLocID, $lNumProblemos);

      if ($lNumProblemos==0){
         $this->session->set_flashdata('error', '<b>ERROR:</b> Unable to transfer client. Please try again.</font>');
         redirect_Client($lClientID);
      }

      foreach ($this->clsSponProg->sponProgs as $prog){
         if ($prog->bOldLoc && !$prog->bNewLoc){
            $this->clsLoc->setSupportedSponProgs($lNewLocID, $prog->lKeyID);
         }
      }
      $this->setNewLoc($lClientID, $lNewLocID, $lStatCatID, $lVocID, $dteEDate);
   }

   function setNewLoc($lClientID, $lNewLocID, $lNewStatCatID, $lNewVocID, $dteEDate){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      $client = &$this->clsClients->clients[0];
      $this->clsLoc->clientXfer($lClientID,
                              $client->lLocationID, $client->lStatusCatID, $client->lVocID,
                              $lNewLocID,           $lNewStatCatID,        $lNewVocID,
                              $dteEDate);
      $this->session->set_flashdata('msg', 'The client record was updated.');
      redirect_Client($lClientID);
   }



}