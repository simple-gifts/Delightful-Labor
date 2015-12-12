<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class add_edit_sponsorship extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function addNewS1($lFID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      if (!bTestForURLHack('showSponsors')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lFID, 'people/business ID');

      $lFID = (integer)$lFID;

      $this->load->model('sponsorship/msponsorship', 'clsSpon');

      $this->clsSpon->sponsorInfoViaID(-1);
      $this->clsSpon->sponInfo[0]->lForeignID = $lFID;
      $this->clsSpon->sponInfo[0]->lKeyID     = -1;

      $this->addEditSponsor($this->clsSpon);
   }

   function editRec($lSponID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showSponsors')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lSponID, 'sponsor ID');

      $lSponID = (integer)$lSponID;

      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('sponsorship/msponsorship', 'clsSpon');
      $this->clsSpon->sponsorInfoViaID($lSponID);
      $this->addEditSponsor($this->clsSpon);
   }

   function addEditSponsor(&$clsSpon){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gstrFormatDatePicker, $gdteNow, $gbDateFormatUS;

      if (!bTestForURLHack('showSponsors')) return;
      $displayData = array();

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',            'clsDateTime');
      $this->load->model  ('people/mpeople',                    'clsPeople');
      $this->load->model  ('biz/mbiz',                          'clsBiz');
      $this->load->model  ('admin/madmin_aco',                  'clsACO');
      $this->load->model  ('sponsorship/msponsorship_programs', 'clsSponProg');
      $this->load->model  ('admin/madmin_aco',                  'clsACO');
      $this->load->model  ('donations/mdonations');
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper ('dl_util/people_biz_common');
      $this->load->helper ('dl_util/time_date');

      $displayData['clsACO'] = &$this->clsACO;

      $displayData['sponRec'] = $sponRec = $clsSpon->sponInfo[0];
      $displayData['lFID']    = $lFID    = $sponRec->lForeignID;
      $displayData['lSponID'] = $lSponID = $sponRec->lKeyID;
      $displayData['bNew']    = $bNew    = $lSponID <= 0;

      bizOrPeopleViaFID($lFID, $bBiz, $this->clsPeople, $this->clsBiz, $strSafeName);

         //--------------------------
         // validation rules
         //--------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtStartDate',  'Sponsorship Start Date',
                                                             'trim|required|callback_sponAddEditStartValid');
      $this->form_validation->set_rules('ddlAttrib',  'Attributed to');
      if ($bNew){
         $this->form_validation->set_rules('ddlSponProg',  'Sponsorship Program',
                                                             'trim|required|callback_sponAddEditProgSelect');
      }else {
         $this->form_validation->set_rules('rdoACO',  'Accounting Country', 'trim');
   		$this->form_validation->set_rules('txtAmount', 'Amount',  'trim|required|callback_stripCommas|numeric|greater_than[-0.01]');
      }

      if ($this->form_validation->run() == FALSE){
         $displayData['js'] = '';
         $this->load->library('generic_form');
         $this->load->helper('dl_util/web_layout');

         $this->load->model  ('util/mlist_generic',     'clsList');
         $displayData['clsForm']     = &$this->generic_form;
         $displayData['strSafeName'] = $strSafeName;

         $this->clsList->enumListType = CENUM_LISTTYPE_ATTRIB;

         if (validation_errors()==''){
            if ($bNew){
               $displayData['strStartDate']   = date($gstrFormatDatePicker, $gdteNow);
            }else {
               $displayData['strStartDate']   = date($gstrFormatDatePicker, $sponRec->dteStart);
               $displayData['lCommitACO']     = $sponRec->lCommitACO;
               $displayData['strAmount']      = number_format($sponRec->curCommitment, 2);
            }
            $displayData['strSponProgDDL'] = $this->clsSponProg->strSponProgramDDL($sponRec->lSponsorProgID);
            $displayData['strAttribDDL']   = $this->clsList->strLoadListDDL('ddlAttrib', true, $sponRec->lAttributedTo);

         }else {
            setOnFormError($displayData);
            $displayData['strStartDate']   = set_value('txtStartDate');
            $displayData['strAttribDDL']   = $this->clsList->strLoadListDDL('ddlAttrib', true, set_value('ddlAttrib'));
            if ($bNew){
               $displayData['strSponProgDDL'] = $this->clsSponProg->strSponProgramDDL(set_value('ddlSponProg'));
            }else {
               $displayData['lCommitACO']     = set_value('rdoACO');
               $displayData['strAmount']      = set_value('txtAmount');
            }
         }

         if ($bBiz){
            $displayData['contextSummary'] = $this->clsBiz->strBizHTMLSummary();
         }else {
            $displayData['contextSummary'] = $this->clsPeople->peopleHTMLSummary(0);
         }

            //------------------------------------------------
            // breadcrumbs / page setup
            //------------------------------------------------
         $displayData['mainTemplate'] = array('sponsorship/add_new_spon_s1_view');
         if ($bBiz){
            $displayData['pageTitle']    = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"');
            if ($bNew){
               $displayData['pageTitle'] .=
                                     ' | '.anchor('biz/biz_record/view/'.$lFID, 'Record', 'class="breadcrumb"');
            }else {
               $displayData['pageTitle'] .=
                                     ' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$lSponID, 'Sponsorship Record', 'class="breadcrumb"');
            }
         }else {
            $displayData['pageTitle']    = anchor('main/menu/people', 'People', 'class="breadcrumb"');
            if ($bNew){
               $displayData['pageTitle'] .=
                                     ' | '.anchor('people/people_record/view/'.$lFID, 'Record', 'class="breadcrumb"');
            }else {
               $displayData['pageTitle'] .=
                                     ' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$lSponID, 'Sponsorship Record', 'class="breadcrumb"');
            }
         }
         $displayData['pageTitle'] .=
                                  ' | '.($bNew ? 'New ' : 'Update ').'Sponsorship';

         $displayData['title']        = CS_PROGNAME.' | Sponsorship';
         $displayData['nav']          = $this->mnav_brain_jar->navData();
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->load->helper('dl_util/util_db');

         $strStartDate   = trim($_POST['txtStartDate']);
         MDY_ViaUserForm($strStartDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $sponRec->dteStart = strtotime($lMon.'/'.$lDay.'/'.$lYear);

         $lAttrib = (integer)$_REQUEST['ddlAttrib'];
         if ($lAttrib <= 0){
            $sponRec->lAttributedTo = null;
         }else {
            $sponRec->lAttributedTo = $lAttrib;
         }

         if ($bNew){
            $sponRec->lForeignID = $lFID;
            $sponRec->lSponsorProgID = (integer)$_REQUEST['ddlSponProg'];
            $this->clsSponProg->loadSponProgsViaSPID($sponRec->lSponsorProgID);
            $sponRec->curCommitment = $this->clsSponProg->sponProgs[0]->curDefMonthlyCommit;
            $sponRec->lCommitACO    = $this->clsSponProg->sponProgs[0]->lACO;
         }else {
            $sponRec->curCommitment = (float)trim($_POST['txtAmount']);
            $sponRec->lCommitACO    = (integer)$_REQUEST['rdoACO'];
         }

         if ($bNew){
            $lSponID = $clsSpon->lAddNewSponsorship();
            $this->session->set_flashdata('msg', 'New sponsorship added');
//            $strMsg = 'New sponsorship added';
         }else {
            $clsSpon->updateSponsorship();
            $this->session->set_flashdata('msg', 'Sponsorship updated');
//            $strMsg = 'Sponsorship updated';
         }
         redirect_SponsorshipRecord($sponRec->lKeyID);
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
   function sponAddEditStartValid($strDate){
      return(bValidVerifyDate($strDate));
   }

   function sponAddEditProgSelect($lProgId){
      return((integer)$lProgId > 0);
   }

   function stripCommas(&$strAmount){
      $strAmount = str_replace (',', '', $strAmount);
      return(true);
   }

   function deactivate($lSponID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gstrFormatDatePicker, $gdteNow, $gbDateFormatUS;

      if (!bTestForURLHack('showSponsors')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lSponID, 'sponsor ID');

      $displayData = array();
      $displayData['formData'] = new stdClass;
      $displayData['lSponID'] = $lSponID = (integer)$lSponID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('dl_util/time_date');  // for date verification
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->model('sponsorship/msponsorship', 'clsSpon');
      $this->load->model('admin/madmin_aco',               'clsACO');
      $this->load->helper('dl_util/web_layout');
      $this->load->library('util/dl_date_time', '',        'clsDateTime');

      $this->clsSpon->sponsorInfoViaID($lSponID);

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('ddlTermReason', 'Termination reason', 'trim|callback_sponDeactivateTermType');
      $this->form_validation->set_rules('txtDate',   'My date',  'trim|required'
                                                                    .'|callback_sponDeactivateDateValid');

		if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');
         $displayData['clsForm']     = &$this->generic_form;

         $this->load->model  ('util/mlist_generic',     'clsList');
         $this->clsList->enumListType = CENUM_LISTTYPE_SPONTERMCAT;

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['txtDate']      = date($gstrFormatDatePicker, $gdteNow);
            $displayData['strTermList']  = $this->clsList->strLoadListDDL('ddlTermReason', true, -1);
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtField1    = set_value('txtField1');
            $displayData['txtDate']      = set_value('txtDate');
            $displayData['strTermList']  = $this->clsList->strLoadListDDL('ddlTermReason', true, set_value('ddlTermReason'));
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['contextSummary'] = $this->clsSpon->sponsorshipHTMLSummary();
         $displayData['pageTitle']    = anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                                 .' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$lSponID, 'Sponsorship Record', 'class="breadcrumb"')
                                 .' |  Terminate Sponsorship';


         $displayData['title']          = CS_PROGNAME.' | Sponsorship';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'sponsorship/deactivate_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $lDeactivateReason = (integer)trim($_POST['ddlTermReason']);
         $strDate   = trim($_POST['txtDate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $dteDeactivate = strtotime($lMon.'/'.$lDay.'/'.$lYear);

         $this->clsSpon->inactivateSponsorship($lSponID, $dteDeactivate, $lDeactivateReason);
         $this->session->set_flashdata('msg', 'The sponsorship was deactivated.');
         redirect_SponsorshipRecord($lSponID);
      }
   }

      //-----------------------------
      // verification routines
      //-----------------------------
   function sponDeactivateDateValid($strDate){
      return(bValidVerifyDate($strDate));
   }
   function sponDeactivateTermType($lTermType){
      return(((integer)$lTermType) > 0);
   }

   function remove($lSponID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showSponsors')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lSponID, 'sponsor ID');

      $this->load->model('util/mrecycle_bin',               'clsRecycle');
      $this->load->model('sponsorship/msponsorship',        'clsSpon');
      $this->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
      $this->load->model('personalization/muser_fields',    'clsUF');

      $this->clsSCP->removePaymentsChargesViaSponID($lSponID);
      $vNull = null;
      $this->clsSpon->retireSingleSponsorship($lSponID, $vNull);

      $this->session->set_flashdata('msg', 'The sponsorship <b>'.str_pad($lSponID, 5, '0', STR_PAD_LEFT).'</b> was removed.');
      redirect('main/menu/sponsorship');
   }


}