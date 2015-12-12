<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class uf_multirecord_view extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function viewMRViaFID($lTableID, $lFID, $lEnrollRecID=0){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $genumDateFormat;

      $displayData = array();
      $displayData['js'] = '';

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('dl_util/time_date');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->model  ('admin/madmin_aco',    'clsACO');
      $this->load->model  ('personalization/muser_fields',         'clsUF');
      $this->load->model  ('personalization/muser_fields_display', 'clsUFD');
      $this->load->model  ('admin/mpermissions',                   'perms');
      $this->load->helper ('dl_util/context');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('clients/link_client_features');
      $this->load->helper ('clients/client_program');
      $this->load->library('util/dl_date_time', '',              'clsDateTime');

         // Stripes
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         //---------------------------------------------------
         // load personalized table and field definitions
         //---------------------------------------------------
      $displayData['lTableID']     = $this->clsUFD->lTableID   = (int)$lTableID;
      $displayData['lEnrollRecID'] = $lEnrollRecID             = (int)$lEnrollRecID;
      $displayData['lFID']         = $this->clsUFD->lForeignID = $lFID;
      $this->clsUFD->loadTableViaTableID();
      $displayData['utable']      = $utable = &$this->clsUFD->userTables[0];

      $displayData['bMultiEntry'] = $bMultiEntry = $utable->bMultiEntry;

      $this->clsUFD->loadFieldsGeneric(true, $lTableID, null);
      $utable->lNumFields = $lNumFields = $this->clsUFD->lNumFields;
      $utable->ufields = &$this->clsUFD->fields;

         // set the ACO country for currency fields
      if ($lNumFields > 0){
         foreach ($utable->ufields as $field){
            if ($field->enumFieldType == CS_FT_CURRENCY){
               $this->clsACO->setACOClassViaFieldID($field->pff_lKeyID, $field->ACO);
            }
         }
      }

      $enumTType = $utable->enumTType;
      loadSupportModels($enumTType, $lFID);

         // Client program? (special type of multi-record personalized table, always associated with client)
      $displayData['bCProg'] = $displayData['utable']->bCProg = $bCProg = bTypeIsClientProg($enumTType);

      if ($bCProg){
         $this->load->model('client_features/mcprograms', 'cprograms');
         $displayData['bEnrollment'] = $bEnrollment = $utable->bEnrollment = $enumTType==CENUM_CONTEXT_CPROGENROLL;
         if ($bEnrollment){
            $this->cprograms->loadClientProgramsViaETableID($lTableID);
         }else {
            $this->cprograms->loadClientProgramsViaATableID($lTableID);
         }
         $utable->cprog = $cprog = &$this->cprograms->cprogs[0];
         $displayData['strTableLabel'] = ($bEnrollment ? $cprog->strSafeEnrollLabel : $cprog->strSafeAttendLabel).' records: '
                                               .htmlspecialchars($cprog->strProgramName);
      }else {
         $displayData['strTableLabel'] = htmlspecialchars($utable->strUserTableName);
      }

         // load records associated with this FID
      if ($bCProg && !$bEnrollment){
         $this->clsUFD->loadMRRecsViaFID($lFID, $lEnrollRecID);
      }else {
         $this->clsUFD->loadMRRecsViaFID($lFID);
      }
      $displayData['lNumMRRecs'] = $lNumMRRecs = $this->clsUFD->lNumMRRecs;
      $displayData['mRecs']      = $mRecs = &$this->clsUFD->mrRecs;

         // if there are any multi-select ddls, create an ordered list
      foreach ($utable->ufields as $uf){
         if ($uf->enumFieldType == CS_FT_DDLMULTI){
            $strMDDLFN = $uf->strFieldNameInternal.'_ddlMulti';

            if ($lNumMRRecs > 0){
               foreach ($mRecs as $dmr){
                  if (!isset($dmr->$strMDDLFN)) $dmr->$strMDDLFN = new stdClass;
                  $dmr->$strMDDLFN->strUL = $this->clsUFD->strMultiDDLUL($dmr->$strMDDLFN);
               }
            }
         }
      }

      if ($bCProg && !$bEnrollment){
         if ($lNumMRRecs > 0){
            $this->cprograms->loadBaseERecViaERecID($cprog, $lEnrollRecID, $lNumERecs, $erecs);
            $erec = &$erecs[0];
            $displayData['strTableLabel'] .= '<br><span style="font-size: 9pt; font-weight: normal;">'
                 .'(Enrollment: '.date($genumDateFormat, $erec->dteStart).' - '
                 .(is_null($erec->dteMysqlEnd) ? 'ongoing' : date($genumDateFormat, $erec->dteEnd)).')</span>';
         }
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $this->clsUFD->tableContext(0);
      $this->clsUFD->tableContextRecView(0);
      $displayData['strHTMLSummary'] = $this->clsUFD->strHTMLSummary;

      if ($bCProg){
         $displayData['pageTitle']      =
                                       anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                .' | '.anchor('clients/client_record/view/'.$lFID, 'Client Record', 'class="breadcrumb"')
                                .' | '.htmlspecialchars($cprog->strProgramName);

      }else {
         $displayData['pageTitle']   = $this->clsUFD->strBreadcrumbsTableDisplay(0);
      }

      $displayData['title']          = CS_PROGNAME.' | Personalization';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'personalization/uf_multi_rec_fid_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function viewSingleMR($lTableID, $lFID, $lRecID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $displayData = array();
      $displayData['js'] = '';

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('dl_util/time_date');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->model  ('admin/madmin_aco',    'clsACO');
      $this->load->model  ('personalization/muser_fields',         'clsUF');
      $this->load->model  ('personalization/muser_fields_display', 'clsUFD');
      $this->load->model  ('admin/mpermissions',                   'perms');
      $this->load->model  ('clients/mclients',                     'clsClients');
      $this->load->helper ('dl_util/context');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('clients/client_program');
      $this->load->helper ('clients/link_client_features');
      $this->load->library('util/dl_date_time', '',              'clsDateTime');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //---------------------------------------------------
         // load personalized table and field definitions
         //---------------------------------------------------
      $displayData['lTableID'] = $this->clsUFD->lTableID   = $lTableID;
      $displayData['lFID']     = $this->clsUFD->lForeignID = $lFID;
      $displayData['lRID']     = $lRecID;
      $this->clsUFD->loadTableViaTableID();
      $displayData['utable'] = $utable = &$this->clsUFD->userTables[0];
      $displayData['bCollapseHeadings']    = $utable->bCollapsibleHeadings;
      $displayData['bCollapseDefaultHide'] = $utable->bCollapseDefaultHide;
      $displayData['bMultiEntry']          = $bMultiEntry = $utable->bMultiEntry;

         // load field schema info
      $this->clsUFD->loadFieldsGeneric(true, $lTableID, null);
      $utable->lNumFields = $this->clsUFD->lNumFields;
      $utable->ufields = $ufields = &$this->clsUFD->fields;

      $enumTType = $utable->enumTType;
      loadSupportModels($enumTType, $lFID);

         // Client program? (special type of multi-record personalized table, always associated with client)
      $displayData['lEnrollRecID'] = 0;
      $displayData['bCProg'] = $displayData['utable']->bCProg = $bCProg = bTypeIsClientProg($enumTType);
      if ($bCProg){
         $this->load->model('client_features/mcprograms', 'cprograms');
         $displayData['bEnrollment'] = $displayData['utable']->bEnrollment = $bEnrollment = $enumTType==CENUM_CONTEXT_CPROGENROLL;
         $displayData['cprogType']   = $cprogType = $enumTType;
         $enumTType = CENUM_CONTEXT_CLIENT;
         if ($bEnrollment){
            $this->cprograms->loadClientProgramsViaETableID($lTableID);
            $displayData['cprog'] = $cprog = &$this->cprograms->cprogs[0];
            $this->cprograms->loadBaseERecViaERecID($cprog, $lRecID, $displayData['lNumERecs'], $displayData['erecs']);
         }else {
            $this->cprograms->loadClientProgramsViaATableID($lTableID);
            $displayData['cprog'] = $cprog = &$this->cprograms->cprogs[0];

            $this->cprograms->loadBaseARecViaARecID($cprog, $lRecID, $displayData['lNumARecs'], $displayData['arecs']);
            $displayData['lEnrollRecID'] = $lEnrollRecID = $displayData['arecs'][0]->lEnrollID;
            $this->cprograms->loadBaseERecViaERecID($cprog, $lEnrollRecID, $lNumERecs, $erecs);
            $erec = &$erecs[0];
         }
         $displayData['strTableLabel'] = ($bEnrollment ? $cprog->strSafeEnrollLabel : $cprog->strSafeAttendLabel).' record: '
                                               .htmlspecialchars($cprog->strProgramName);
         if (!$bEnrollment){
            $displayData['strTableLabel'] .= '<br><span style="font-size: 9pt; font-weight: normal;">'
                    .'(Enrollment: '.date($genumDateFormat, $erec->dteStart).' - '
                    .(is_null($erec->dteMysqlEnd) ? 'ongoing' : date($genumDateFormat, $erec->dteEnd)).')</span>';
         }
      }else {
         $displayData['strTableLabel'] = htmlspecialchars($utable->strUserTableName);
      }

         // load single data record
      $this->clsUFD->loadMRRecsViaRID($lRecID);
      $displayData['lNumMRRecs'] = $this->clsUFD->lNumMRRecs;
      $displayData['mRec']       = $mRec = &$this->clsUFD->mrRecs[0];

         // if there are any multi-select ddls, create an ordered list
         // for clientID's load the client's name
      $displayData['clientNames'] = array();
      foreach ($ufields as $uf){
         if ($uf->enumFieldType == CS_FT_DDLMULTI){
            $strMDDLFN = $uf->strFieldNameInternal.'_ddlMulti';
            $mRec->$strMDDLFN->strUL = $this->clsUFD->strMultiDDLUL($mRec->$strMDDLFN);
         }elseif ($uf->enumFieldType == CS_FT_CLIENTID){
            $strFN = $uf->strFieldNameInternal;
            $lClientID = (int)$mRec->$strFN;
            if ($lClientID > 0){
               $this->clsClients->loadClientsViaClientID($lClientID);
               $client = &$this->clsClients->clients[0];
               $displayData['clientNames'][$lClientID] = $client->strFName.' '.$client->strLName;
            }
         }
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $this->clsUFD->tableContext(0);
      $this->clsUFD->tableContextRecView(0);
      $displayData['strHTMLSummary'] = $this->clsUFD->strHTMLSummary;

      $displayData['pageTitle']    = $this->clsUFD->strBreadcrumbsTableDisplay(0);

      $displayData['title']          = CS_PROGNAME.' | Personalization';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'personalization/uf_multi_rec_single_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }


}



