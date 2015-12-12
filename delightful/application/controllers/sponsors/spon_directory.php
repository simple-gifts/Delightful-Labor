<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class spon_directory extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($strShowInactive='true', $lSponProgID=-1, $strLookupLetter='A', $lStartRec=0, $lRecsPerPage=50){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      if (!bTestForURLHack('showSponsors')) return;
      $strLookupLetter = urldecode($strLookupLetter);

      $displayData = array();

      $lStartRec    = (integer)$lStartRec;
      $lRecsPerPage = (integer)$lRecsPerPage;

      $lSponProgID   = (integer)$lSponProgID;
      $bShowAllProgs = $lSponProgID <= 0;
      $displayData['bShowInactive'] = $bShowInactive = strtoupper($strShowInactive)=='TRUE';

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper('people/people');
      $this->load->helper('people/people_display');
      $this->load->model ('sponsorship/msponsorship',          'clsSpon');
      $this->load->model ('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->load->helper('sponsors/sponsorship');
      $this->load->helper('dl_util/directory');
      $this->load->helper('dl_util/rs_navigate');
//      $this->load->helper('dl_util/email_web');
      $this->load->helper('dl_util/record_view');
      $this->load->helper('img_docs/link_img_docs');

      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('people/mpeople',  'clsPeople');
      $this->load->model('admin/madmin_aco', 'clsACO');

         //------------------------------------------------
         // sponsorship program
         //------------------------------------------------
      if ($lSponProgID > 0){
         $this->clsSponProg->loadSponProgsViaSPID($lSponProgID);
         $sprog = &$this->clsSponProg->sponProgs[0];
         $strProgName = ': '.htmlspecialchars($sprog->strProg);
      }else {
         $strProgName = '';
      }

         //------------------------------------------------
         // sanitize the lookup letter and inputs
         //------------------------------------------------
      $displayData['strDirLetter'] = $strLookupLetter = strSanitizeLetter($strLookupLetter);
      $displayData['lStartRec']    = $lStartRec;
      $displayData['lRecsPerPage'] = $lRecsPerPage;

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

         //------------------------------------------------
         // define columns to display
         //------------------------------------------------
      initSponReportDisplay($displayData);

         //------------------------------------------------
         // set up directory display
         //------------------------------------------------
      $displayData['strRptTitle']  = 'Sponsorship Directory'.$strProgName;

      $displayData['strDirLetter'] = $strLookupLetter;
      $displayData['strLinkBase']  = $strLinkBase = 'sponsors/spon_directory/view/'.$strShowInactive.'/'.$lSponProgID.'/';
      $displayData['strLinkToggleActive'] =
              strLinkView_SponDir(!$bShowInactive, $lSponProgID, $strLookupLetter,
                             0, $lRecsPerPage,
                             ($bShowInactive ? 'Hide' : 'Show').' inactive', false).'<br>';
      $displayData['strDirTitle']  = strDisplayDirectory(
                                         $strLinkBase, ' class="directoryLetters" ', $strLookupLetter,
                                         true, $lStartRec, $lRecsPerPage);

         //-------------------------------------------------------------
         // make sure one or more sponsorship programs exist
         //-------------------------------------------------------------
      $displayData['lNumSponProgs']  = $lNumSponProgs = $this->clsSponProg->lNumSponPrograms();
      if ($lNumSponProgs > 0){
         $displayData['lNumRecsTot'] = $lNumRecsTot = $this->clsSpon->lNumSponsorsViaProgram(
                                            $lSponProgID, $bShowInactive, $strLookupLetter);

            // sponsor program ddl
         $displayData['strSponProgDDL'] =
            '<select name="ddlSponProg">
               <option value="-1" '.($lSponProgID <= 0 ? 'selected' : '').'>(all programs)</option>'."\n"
               .$this->clsSponProg->strSponProgramDDL($lSponProgID, false)."\n"
               .'</select>'."\n";

         if ($lNumRecsTot > 0){
            $displayData['directoryRecsPerPage'] = $lRecsPerPage;
            $displayData['directoryStartRec']    = $lStartRec;

            $strLimit =  "LIMIT  $lStartRec, $lRecsPerPage "; // strLoadRecSpecs($lRecsPerPage, $lStartRec);

            $strWhereExtra = '';
            if ($lSponProgID > 0) {
               $strWhereExtra .= " AND sp_lSponsorProgramID=$lSponProgID ";
            }

            if (!$bShowInactive){
               $strWhereExtra .= ' AND NOT sp_bInactive ';
            }

            if ($strLookupLetter.'' != ''){
               $strWhereExtra .= strNameWhereClauseViaLetter('tblPeopleSpon.pe_strLName', $strLookupLetter);
            }

            $this->clsSpon->sponsorInfoGenericViaWhere($strWhereExtra, $strLimit);
            $displayData['sponInfo']  = &$this->clsSpon->sponInfo;
         }
         $displayData['lNumDisplayRows']    = $this->clsSpon->lNumSponsors;
      }


         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['mainTemplate'] = array('sponsorship/sponsor_directory_view',
                                           'sponsorship/rpt_generic_spon_list');
      $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                              .' | Directory';

      $displayData['title']        = CS_PROGNAME.' | Sponsorship';
      $displayData['nav']          = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function setProg($strShowInactive, $strLookupLetter, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lProgID = (int)$_POST['ddlSponProg'];
      $this->view($strShowInactive, $lProgID, $strLookupLetter, $lStartRec, $lRecsPerPage);
   }


}