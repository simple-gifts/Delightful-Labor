<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class client_dir extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($lLocationID, $showInactive='N', $strLookupLetter='A', $lStartRec=0, $lRecsPerPage=50){
   //------------------------------------------------------------------------------
   //  by location
   //------------------------------------------------------------------------------
      $lLocationID = (int)$lLocationID;
      $this->clientDirectory($lLocationID, null,
                 $showInactive, $strLookupLetter, $lStartRec, $lRecsPerPage);
   }
   function sProg($lSponProgID, $showInactive='N', $strLookupLetter='A', $lStartRec=0, $lRecsPerPage=50){
   //------------------------------------------------------------------------------
   //  by location
   //------------------------------------------------------------------------------
      $lSponProgID = (int)$lSponProgID;
      $this->clientDirectory(null, $lSponProgID,
                 $showInactive, $strLookupLetter, $lStartRec, $lRecsPerPage);
   }

   function loadDirectoryViaLocation(&$displayData, $clsLocation, $lLocationID, $bShowInactive){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      $_SESSION[CS_NAMESPACE.'clientLastDir'] = 'view';
      if ($lLocationID <= 0){
         $lLocationID = $clsLocation->clientLocations[0]->lKeyID;
      }
      $clsLocation->cl_lKeyID = $lLocationID;
      $clsLocation->loadLocationRec($lLocationID);
      
      $displayData = array();
      $displayData['js'] = '';
      $displayData['ddlLocations'] = $clsLocation->strDDLAllLocations($lLocationID);

      $this->load->helper('clients/client_sponsor');
      $this->load->helper('clients/client');
      $this->load->model('clients/mclients', 'clsClients');
      $this->clsClients->loadClientsViaLocID($lLocationID, $bShowInactive);
      $displayData['clientInfo'] = $this->clsClients->clients;

         //-----------------------------------
         // set up client report
         //-----------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

         //-------------------------------------
         // stripes
         //-------------------------------------
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $displayData['lNumClients'] = $this->clsClients->lNumClients;
      initClientReportDisplay($displayData);
      $displayData['showFields']->bLocation       = true;
      $displayData['showFields']->bSubmitOnClick  = true;
      $displayData['showFields']->bShowInactive   = $bShowInactive;
      $displayData['showFields']->formName        = 'frmClientDir';
      $displayData['showFields']->locDDLDest      = 'clients/client_dir/setLocation';

      $displayData['strRptTitle'] = 'Directory of clients at <b>'
                       .htmlspecialchars($clsLocation->strLocation).'</b>';

   }

   function setLocation(){
      $lLocID = (integer)$_POST['ddlLocation'];
      $bShowInactive = @$_POST['chkShowAll']=='TRUE';
      redirect('clients/client_dir/view/'.$lLocID.'/'.($bShowInactive ? 'Y' : 'N'));
   }

   function name($strIncludeInactive='N', $strLookupLetter='A', $lStartRec=0, $lRecsPerPage=50){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      $this->clientDirectory(null, null,
                 $strIncludeInactive, $strLookupLetter, $lStartRec, $lRecsPerPage);
   }

   function clientDirectory($lLocationID, $lSponProgID,
                 $strIncludeInactive, $strLookupLetter, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showClients')) return;
      $_SESSION[CS_NAMESPACE.'clientLastDir'] = 'name';
      $strLookupLetter = urldecode($strLookupLetter);
      $displayData = array();

      $displayData['bIncludeInactive'] = $bIncludeInactive = $strIncludeInactive=='Y';
      $displayData['bViaLocation'] = $bViaLocation = !is_null($lLocationID);
      $displayData['bViaSponProg'] = $bViaSponProg = !is_null($lSponProgID);

         //--------------------------------------
         // models, helpers, libraries
         //--------------------------------------
      $this->load->helper('dl_util/directory');
      $this->load->helper('dl_util/rs_navigate');
      $this->load->helper('clients/client');
      $this->load->helper('clients/client_sponsor');
      $this->load->model ('clients/mclient_locations',        'clsLocation');
      $this->load->model ('clients/mclients',                 'clsClients');
      $this->load->library('util/dl_date_time', '',           'clsDataTime');
      $this->load->model('sponsorship/msponsorship_programs', 'clsSponProg');

      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

      $displayData['strRptTitle']  = 'Client Directory';
      $strWhereExtra = '';

         //------------------------------------------------
         // location
         //------------------------------------------------
      if ($bViaLocation){
         $this->clsLocation->loadAllLocations();
         $displayData['lNumLocations'] = $lNumLocations = $this->clsLocation->lNumLocations;
         if ($lLocationID <= 0){
            $lLocationID = $this->clsLocation->clientLocations[0]->lKeyID;
         }
         $this->clsLocation->cl_lKeyID = $lLocationID;
         $this->clsLocation->loadLocationRec($lLocationID);
         $displayData['strRptTitle'] .= ': '.htmlspecialchars($this->clsLocation->strLocation);

         $strWhereExtra = " AND cr_lLocationID=$lLocationID ";

         $displayData['ddlLocations'] = $this->clsLocation->strDDLAllLocations($lLocationID);
         $displayData['locChange'] = new stdClass;
         $displayData['locChange']->frmName = 'frmLocation';
         $displayData['locChange']->frmDest =
                                'clients/client_dir/changeLocation/'
                               .$strIncludeInactive.'/'.urlencode($strLookupLetter).'/'.$lStartRec.'/'.$lRecsPerPage;
      }

         //------------------------------------------------
         // program
         //------------------------------------------------
      if ($bViaSponProg){
         $this->clsSponProg->loadSponProgsGeneric(false);
         
         $displayData['lNumSponProg'] = $lNumSponProg = $this->clsSponProg->lNumSponPrograms;
         if ($lSponProgID <= 0){
            $lSponProgID = $this->clsSponProg->sponProgs[0]->lKeyID;
         }

         $strDDL = '<select name="ddlSponProg">'."\n";
         foreach ($this->clsSponProg->sponProgs as $sprog){
            if ($sprog->lKeyID==$lSponProgID){
               $strSel =  'selected';
               $displayData['strRptTitle'] .= ': '.htmlspecialchars($sprog->strProg);
            }else {
               $strSel = '';
            }
            $strDDL .= '<option value="'.$sprog->lKeyID.'" '.$strSel.'>'.htmlspecialchars($sprog->strProg).'</option>'."\n";
         }
         $strDDL .= '</select>'."\n";
         $displayData['ddlSProgs'] = $strDDL;

         $strWhereExtra = " AND csp_lSponProgID=$lSponProgID ";
         $this->clsClients->strInnerExtra = ' INNER JOIN client_supported_sponprogs ON cr_lKeyID=csp_lClientID ';

         $displayData['progChange'] = new stdClass;
         $displayData['progChange']->frmName = 'frmSProg';
         $displayData['progChange']->frmDest =
                                'clients/client_dir/changeSProg/'
                               .$strIncludeInactive.'/'.urlencode($strLookupLetter).'/'.$lStartRec.'/'.$lRecsPerPage;
      }

         //------------------------------------------------
         // sanitize the lookup letter
         //------------------------------------------------
      $displayData['strDirLetter'] = $strLookupLetter = strSanitizeLetter($strLookupLetter);
      initClientReportDisplay($displayData);
      $displayData['showFields']->bLocation = true;

      $displayData['strDirLetter'] = $strLookupLetter;
      $strLinkEnd = urlencode($strLookupLetter).'/'.$lStartRec.'/'.$lRecsPerPage;
      $strLabelToggle = ($bIncludeInactive ? 'Hide' : 'Show').' inactive clients';
      if ($bViaLocation){
         $displayData['strLinkBase']  = $strLinkBase = 'clients/client_dir/view/'.$lLocationID.'/'.($bIncludeInactive ? 'Y' : 'N').'/';
         $displayData['strToggleLink'] = 
                anchor('clients/client_dir/view/'.$lLocationID.'/'.($bIncludeInactive ? 'N' : 'Y').'/'.$strLinkEnd, $strLabelToggle);
      }elseif ($bViaSponProg) {
         $displayData['strLinkBase']  = $strLinkBase = 'clients/client_dir/sProg/'.$lSponProgID.'/'.($bIncludeInactive ? 'Y' : 'N').'/';
         $displayData['strToggleLink'] = 
                anchor('clients/client_dir/sProg/'.$lSponProgID.'/'.($bIncludeInactive ? 'N' : 'Y').'/'.$strLinkEnd, $strLabelToggle);
      }else {
         $displayData['strLinkBase']  = $strLinkBase = 'clients/client_dir/name/'.($bIncludeInactive ? 'Y' : 'N').'/';
         $displayData['strToggleLink'] = 
                anchor('clients/client_dir/name/'.($bIncludeInactive ? 'N' : 'Y').'/'.$strLinkEnd, $strLabelToggle);
      }
      $displayData['strDirTitle']  = strDisplayDirectory(
                                         $strLinkBase, ' class="directoryLetters" ', $strLookupLetter,
                                         true, $lStartRec, $lRecsPerPage);

         //------------------------------------------------
         // total # clients for this letter
         //------------------------------------------------
      $displayData['lNumRecsTot'] = $lNumRecsTot =
                                        $this->clsClients->lNumClientsViaLetter($strLookupLetter, $bIncludeInactive, $strWhereExtra);
      $displayData['lNumClients'] = $lNumRecsTot;

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['mainTemplate'] = array('client/client_directory_view', 'client/rpt_generic_client_list');
      $displayData['pageTitle']    = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                              .' | Client Directory';

      $displayData['title']        = CS_PROGNAME.' | Clients';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

         //------------------------------------------------
         // load client directory page
         //------------------------------------------------
      $strWhereExtra .= $this->clsClients->strWhereByLetter($strLookupLetter, $bIncludeInactive);
      $this->clsClients->loadClientDirectoryPage($strWhereExtra, $lStartRec, $lRecsPerPage);
      $displayData['lNumDisplayRows']      = $this->clsClients->lNumClients;
      $displayData['directoryRecsPerPage'] = $lRecsPerPage;
      $displayData['directoryStartRec']    = $lStartRec;
      $displayData['clientInfo'] = $this->clsClients->clients;

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function changeLocation($showInactive, $strLookupLetter, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lLocationID = (int)$_POST['ddlLocation'];
      redirect('clients/client_dir/view/'.$lLocationID.'/'.$showInactive
               .'/'.urlencode($strLookupLetter).'/'.$lStartRec.'/'.$lRecsPerPage);
   }

   function changeSProg($showInactive, $strLookupLetter, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lProgID = (int)$_POST['ddlSponProg'];
      redirect('clients/client_dir/sProg/'.$lProgID.'/'.$showInactive
               .'/'.urlencode($strLookupLetter).'/'.$lStartRec.'/'.$lRecsPerPage);
   }


}