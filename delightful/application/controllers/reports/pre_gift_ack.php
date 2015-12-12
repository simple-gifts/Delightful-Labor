<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_gift_ack extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function showOpts($enumAckType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gbDateFormatUS, $glChapterID;
      if (!bTestForURLHack('showFinancials')) return;
      $displayData = array();
      $displayData['js'] = '';
      $displayData['formData'] = new stdClass;
      $displayData['enumAckType'] = $enumAckType;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('reports/mreports',    'clsReports');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/form_verification');
      $this->load->model('admin/madmin_aco',    'clsACO');
      $this->load->model('admin/morganization', 'clsChapter');

         // time frame support
      $this->load->helper ('reports/date_range_def');
      $this->load->helper ('reports/date_range');
      $this->load->library('js_build/java_joe_radio');
      $this->load->model  ('util/mserial_objects', 'cSO');
      $displayData['js'] .= $this->java_joe_radio->insertJavaJoeRadio().$this->java_joe_radio->insertSetDateRadio();
      tf_initDateRangeMenu($displayData['viewOpts']);
      $displayData['viewOpts']->bShowAggregateDonor = true;

      $displayData['viewOpts']->strFormName = 'frmGiftAckRpt';
      $displayData['viewOpts']->strID       = 'giftAckRpt';

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->tf_setValidationTFRules($displayData['viewOpts']);

      $this->form_validation->set_rules('rdoAck',      'Not Acknowledged');
      $this->form_validation->set_rules('chkSpon',     'Include sponsorships');
      $this->form_validation->set_rules('chkMarkAck',  'Mark as Acknowledged');
      $this->form_validation->set_rules('rdoSort',     'Sort');
      $this->form_validation->set_rules('rdoACO',      'Accounting Country');

		if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');

         switch ($enumAckType){
            case 'gifts':
               $strLabel = 'Donations';
               break;
            case 'hon':
               $strLabel = 'Honorarium Recipient';
               break;
            case 'mem':
               $strLabel = 'Memorial Mail Contact';
               break;
            default:
               screamForHelp($enumAckType.': ack type error<br>error on line <b>'.__LINE__.'</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }
         $displayData['strLabel'] = $strLabel;

         if (validation_errors()==''){
            $this->clsChapter->lChapterID = $glChapterID;
            $this->clsChapter->loadChapterInfo();
            $displayData['formData']->strACORadio = $this->clsACO->strACO_Radios(
                                                      $this->clsChapter->chapterRec->lDefaultACO, 'rdoACO');
            $displayData['bNotAck']      = true;
            $displayData['bIncludeSpon'] = false;
            $displayData['bMarkAck']     = false;
            $displayData['enumSort']     = 'date';
            $dteStart = strtotime('1/1/2000');
            $dteEnd   = $gdteNow;
            if ($gbDateFormatUS){
               $displayData['txtSDate'] = date('m/d/Y', $dteStart);
               $displayData['txtEDate'] = date('m/d/Y', $dteEnd);
            }else {
               $displayData['txtSDate'] = date('d/m/Y', $dteStart);
               $displayData['txtEDate'] = date('d/m/Y', $dteEnd);
            }
         }else {
            setOnFormError($displayData);
            $displayData['bNotAck']      = set_value('rdoAck')     == 'notAck';
            $displayData['bIncludeSpon'] = set_value('chkSpon')    == 'true';
            $displayData['bMarkAck']     = set_value('chkMarkAck') == 'true';
            $displayData['enumSort']     = set_value('rdoSort');

            $displayData['txtSDate']     = set_value('txtSDate');
            $displayData['txtEDate']     = set_value('txtEDate');

            $displayData['formData']->strACORadio = $this->clsACO->strACO_Radios(set_value('rdoACO'), 'rdoACO');

               // time frame support
            $this->tf_setTFOptsOnFormError($displayData['viewOpts']);
         }
            // time frame support
         $displayData['dateRanges'] = $strRange = tf_strDateRangeMenu($displayData['viewOpts']);

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                   .' | Donation Acknowledgments';

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'reports/pre_gift_ack_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $bFindNonAck  = $_POST['rdoAck']      == 'notAck';
         $bIncludeSpon = @$_POST['chkSpon']    == 'true';
         $bMarkAsAck   = @$_POST['chkMarkAck'] == 'true';
         $enumSort     = trim($_POST['rdoSort']);
         $lACO         = (integer)$_POST['rdoACO'];

         tf_getDateRanges($displayData['viewOpts'], $formDates);
         $reportAttributes = array(
                                'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                                'rptName'        => CENUM_REPORTNAME_GIFTACK,
                                'rptDestination' => CENUM_REPORTDEST_SCREEN,
                                'lStartRec'      => 0,
                                'lRecsPerPage'   => 50,
                                'bShowRecNav'    => true,
                                'viewFile'       => 'pre_generic_rpt_view',
                                'bFindNonAck'    => $bFindNonAck,
                                'bIncludeSpon'   => $bIncludeSpon,
                                'bMarkAsAck'     => $bMarkAsAck,
                                'enumSort'       => $enumSort,
                                'enumAckType'    => $enumAckType,
                                'lACO'           => $lACO,

                                'dteStart'       => $formDates->dteStart,
                                'dteEnd'         => $formDates->dteEnd,
                                'strDateRange'   => $formDates->strDateRange,
                                'strBetween'     => $formDates->strBetween
                                );
         $this->clsReports->createReportSessionEntry($reportAttributes);
         $reportID = $this->clsReports->sRpt->reportID;
         if ($bMarkAsAck){
            switch ($enumAckType){
               case 'gifts': $strLabel = 'gifts';                  break;
               case 'hon':   $strLabel = 'honorees';               break;
               case 'mem':   $strLabel = 'memorial mail contacts'; break;
            }
            $this->clsReports->sRpt->strExportLabel = 'Export / mark '.$strLabel.' as acknowledged';
         }
         redirect('reports/reports/run/'.$reportID);
      }
   }


      //-----------------------------
      // verification routines
      //-----------------------------
   function rptStart($strSDate, $strRadioFN){
      if ($_REQUEST[$strRadioFN] != 'User') return(true);
      if(bValidVerifyDate($strSDate)){
         return(true);
      }else{
         $this->form_validation->set_message('rptStart', 'The report start date is not valid');
         return(false);
      }
   }
   function rptEnd($strEDate, $strRadioFN){
      if ($_REQUEST[$strRadioFN] != 'User') return(true);
      if(bValidVerifyDate($strEDate)){
         return(true);
      }else{
         $this->form_validation->set_message('rptEnd', 'The report end date is not valid');
         return(false);
      }
   }
   function rptCBH($strEDate, $strRadioFN){
   // CBH: cart before horse
      if ($_REQUEST[$strRadioFN] != 'User') return(true);
      if (bVerifyCartBeforeHorse(trim($_POST['txtSDate']), $strEDate)){
         return(true);
      }else {
         $this->form_validation->set_message('rptCBH', 'The end date is before the start date!');
         return(false);
      }
   }
   function tf_setValidationTFRules($opts){
      $strRadioFN = $opts->strRadioFN;
      $this->form_validation->set_rules($strRadioFN,       'Report Range Option', 'trim|required');
      $this->form_validation->set_rules($opts->strSDateFN, 'Report start date',   'trim|required|callback_rptStart['.$strRadioFN.']');
      $this->form_validation->set_rules($opts->strEDateFN, 'Report end  date',    'trim|required'
                                                 .'|callback_rptEnd['.$strRadioFN.']|callback_rptCBH['.$strRadioFN.']');
   }
   function tf_setTFOptsOnFormError(&$opts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $opts->txtSDate = set_value($opts->strSDateFN);
      $opts->txtEDate = set_value($opts->strEDateFN);

      $strRdoVal = set_value($opts->strRadioFN);
      $opts->bDatePreDefined = $strRdoVal == 'DDL';
      $opts->bDateRange      = $strRdoVal == 'User';
      $opts->bNoDateRange    = $strRdoVal == 'None';

      $opts->strDatePredefinedID = $_REQUEST[$opts->strCTRL_Prefix.'ddl_TimeFrame'];
   }


/*
   function rptStart($strSDate){
      return(bValidVerifyDate($strSDate));
   }

   function rptEnd($strEDate){
      return(bValidVerifyDate($strEDate));
   }

   function rptCBH($strEDate){
   // CBH: cart before horse
      return(bVerifyCartBeforeHorse(trim($_POST['txtSDate']), $strEDate));
   }

*/















}