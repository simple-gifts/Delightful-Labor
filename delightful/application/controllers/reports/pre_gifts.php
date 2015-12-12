<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_gifts extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function recentOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showFinancials')) return;
      $displayData = array();

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper('dl_util/web_layout');
      $this->load->library('generic_form');

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Recent Donations';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_gift_recent_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function recentRun(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showFinancials')) return;
      $this->load->model('reports/mreports',    'clsReports');

      $lDaysPast = (integer)$_POST['ddlPast'];
      $enumSort  = trim($_POST['rdoSort']);
      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_GIFTRECENT,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => true,
                             'viewFile'       => 'pre_generic_rpt_view',
                             'enumSort'       => $enumSort,
                             'lDaysPast'      => $lDaysPast
                             );

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }

   function timeframeOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gbDateFormatUS, $glChapterID;
      if (!bTestForURLHack('showFinancials')) return;
      $displayData = array();
      $displayData['js'] = '';

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper ('dl_util/web_layout');
      $this->load->library('generic_form');
      $this->load->model  ('admin/madmin_aco',    'clsACO');
      $this->load->model  ('admin/morganization', 'clsChapter');
      $this->load->helper ('dl_util/time_date');

         // time frame support
      $this->load->helper ('reports/date_range_def');
      $this->load->helper ('reports/date_range');
      $this->load->library('js_build/java_joe_radio');
      $this->load->model  ('util/mserial_objects', 'cSO');
      $displayData['js'] .= $this->java_joe_radio->insertJavaJoeRadio().$this->java_joe_radio->insertSetDateRadio();
      
      tf_initDateRangeMenu($displayData['viewOpts']);      
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$displayData[viewOpts]   <pre>');
echo(htmlspecialchars( print_r($displayData['viewOpts'], true))); echo('</pre></font><br>');
// ------------------------------------- */
      
      $this->initViewOpts($displayData['viewOpts']);
      $displayData['viewOpts']->bShowAggregateDonor = true;

         // validation rules
      $this->setValidationRules($displayData['viewOpts']);

      if ($this->form_validation->run() == FALSE){
         $displayData['frmLink'] = 'reports/pre_gifts/timeframeOpts';
         $this->load->library('generic_form');
         $displayData['viewOpts']->blockLabel = 'Donations by Time Frame';

         if (validation_errors()==''){
            $this->clsChapter->lChapterID = $glChapterID;
            $this->clsChapter->loadChapterInfo();

            $this->setInitialRptVal($displayData['viewOpts'], $displayData['formData']);
         }else {
            setOnFormError($displayData);
            $this->setErrRptVal($displayData['viewOpts'], $displayData['formData']);
            
               // time frame support
            $this->tf_setTFOptsOnFormError($displayData['viewOpts']);            
         }
            // time frame support
         $displayData['dateRanges'] = $strRange = tf_strDateRangeMenu($displayData['viewOpts']);         

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                   .' | Donations by Timeframe';

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'reports/pre_gift_time_frame_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $reportID = $this->strLoadPostGiftRpt($displayData['viewOpts'], CENUM_REPORTNAME_GIFTTIMEFRAME);
         redirect('reports/reports/run/'.$reportID);
      }
   }

   function strLoadPostGiftRpt(&$viewOpts, $enumRptType){
   //---------------------------------------------------------------------
   // return reportID
   //---------------------------------------------------------------------
      global $gbDateFormatUS;
//      if (!bTestForURLHack('showFinancials')) return;
      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => $enumRptType,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => ($enumRptType!=CENUM_REPORTNAME_GIFTAGG));
      if ($enumRptType == CENUM_REPORTNAME_GIFTAGG){
         $reportAttributes['viewFile'] = 'pre_gift_agg_rpt_view';
      }elseif ($enumRptType == CENUM_REPORTNAME_GIFTSPONAGG){
         $reportAttributes['viewFile'] = 'pre_spon_pay_agg_rpt_view';
      }else {
         $reportAttributes['viewFile'] = 'pre_generic_rpt_view';
      }
      if ($viewOpts->bShowTimeFrame){
         tf_getDateRanges($viewOpts, $formDates);
         $reportAttributes['dteStart']     = $formDates->dteStart;
         $reportAttributes['dteEnd']       = $formDates->dteEnd;
         $reportAttributes['strDateRange'] = $formDates->strDateRange;
         $reportAttributes['strBetween']   = $formDates->strBetween;
      }

      if ($viewOpts->bShowMinAmnt){
         $reportAttributes['curMin'] = (float)trim($_POST['txtMinAmount']);
      }
      if ($viewOpts->bShowMaxAmnt){
         $reportAttributes['curMax'] = (float)trim($_POST['txtMaxAmount']);
      }
      if ($viewOpts->bShowIncludes){
         $reportAttributes['enumInc'] = trim($_POST['rdoInc']);
      }
      if ($viewOpts->bShowACO){
         $reportAttributes['lACO']  = (integer)$_POST['rdoACO'];
      }
      if ($viewOpts->bShowYear){
         $reportAttributes['lYear']  = (integer)$_POST['ddlYear'];
      }
      if ($viewOpts->bShowAggregateDonor){
         $reportAttributes['bAggregateDonor']  = $_POST['rdoAggDonor']=='group';
      }
      if ($viewOpts->bShowAcct){
         $reportAttributes['lNumAccts'] = $lNumAccts = count($_POST['chkAccts']);
         $reportAttributes['acctIDs'] = array();
         for ($idx=0; $idx<$lNumAccts; ++$idx){
            $reportAttributes['acctIDs'][$idx] = (integer)$_POST['chkAccts'][$idx];
         }
      }
      if ($viewOpts->bShowCamp){
         $reportAttributes['lNumCamps'] = $lNumCamps = count($_POST['chkCamps']);
         $reportAttributes['campIDs'] = array();
         for ($idx=0; $idx<$lNumCamps; ++$idx){
            $reportAttributes['campIDs'][$idx] = (integer)$_POST['chkCamps'][$idx];
         }
      }

      if ($viewOpts->bShowSortBy){
         $enumSort = trim($_POST['rdoSort']);
         $reportAttributes['enumSort'] = $enumSort;
      }
      
      $this->clsReports->createReportSessionEntry($reportAttributes);
      return($this->clsReports->sRpt->reportID);
   }

   private function setValidationRules(&$viewOpts){
   //---------------------------------------------------------------------
   // validation rules
   //---------------------------------------------------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');

      if ($viewOpts->bShowTimeFrame){
         $this->tf_setValidationTFRules($viewOpts);
      }
      if ($viewOpts->bShowMinAmnt){
         $this->form_validation->set_rules('txtMinAmount', 'Minimum Donation Amount', 'trim|required|callback_stripCommas|numeric');
      }
      if ($viewOpts->bShowMaxAmnt){
         $this->form_validation->set_rules('txtMaxAmount', 'Maximum Donation Amount',
                                       'trim|required|callback_stripCommas|numeric|callback_rptCBHAmount');
      }
      if ($viewOpts->bShowIncludes){
         $this->form_validation->set_rules('rdoInc',      'Included donations');
      }
      if ($viewOpts->bShowACO){
         $this->form_validation->set_rules('rdoACO', 'Accounting Country');
      }
      if ($viewOpts->bShowSortBy){
         $this->form_validation->set_rules('rdoSort', 'Sort');
      }
      if ($viewOpts->bShowAggregateDonor){
         $this->form_validation->set_rules('rdoAggDonor', 'Grouping');
      }
   }

   private function setErrRptVal(&$viewOpts, &$formData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS, $gdteNow;

      $formData = new stdClass;
      if ($viewOpts->bShowACO){
         $formData->strACORadio = $this->clsACO->strACO_Radios(set_value('rdoACO'),
                          'rdoACO', $viewOpts->bShowACOAll);
      }
      if ($viewOpts->bShowSortBy){
         $formData->enumSort = set_value('rdoSort');
      }
      if ($viewOpts->bShowIncludes){
         $formData->enumInc = set_value('rdoInc');
      }
      if ($viewOpts->bShowMinAmnt){
         $formData->strMinAmount = set_value('txtMinAmount');
      }
      if ($viewOpts->bShowMaxAmnt){
         $formData->strMaxAmount = number_format(999999.99, 2);
         $formData->strMaxAmount = set_value('txtMaxAmount');
      }
      if ($viewOpts->bShowTimeFrame){
         $formData->txtSDate = set_value('txtSDate');
         $formData->txtEDate = set_value('txtEDate');
      }
      if ($viewOpts->bShowAggregateDonor){
         $formData->enumAgg = set_value('rdoAggDonor');
      }
   }

   private function setInitialRptVal(&$viewOpts, &$formData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS, $gdteNow;

      $formData = new stdClass;
      if ($viewOpts->bShowACO){
         $formData->strACORadio = $this->clsACO->strACO_Radios(
                                                $this->clsChapter->chapterRec->lDefaultACO,
                                                'rdoACO', $viewOpts->bShowACOAll);
      }
      if ($viewOpts->bShowSortBy){
         $formData->enumSort = 'date';
      }
      if ($viewOpts->bShowIncludes){
         $formData->enumInc  = 'all';
      }
      if ($viewOpts->bShowMinAmnt){
         $formData->strMinAmount = number_format(0, 2);
      }
      if ($viewOpts->bShowMaxAmnt){
         $formData->strMaxAmount = number_format(999999.99, 2);
      }
      if ($viewOpts->bShowAggregateDonor){
         $formData->enumAgg = 'all';
      }

      if ($viewOpts->bShowTimeFrame){
         $dteStart                = strtotime('1/1/2000');
         $dteEnd                  = $gdteNow;
         if ($gbDateFormatUS){
            $formData->txtSDate = date('m/d/Y', $dteStart);
            $formData->txtEDate = date('m/d/Y', $dteEnd);
         }else {
            $formData->txtSDate = date('d/m/Y', $dteStart);
            $formData->txtEDate = date('d/m/Y', $dteEnd);
         }
      }

      if ($viewOpts->bShowYear){
         $formData->lYearSel = (integer)date('Y', $gdteNow);
      }
   }

   private function initViewOpts(&$viewOpts){
      $viewOpts->bShowACO       =
      $viewOpts->bShowTimeFrame =
      $viewOpts->bShowIncludes  =
      $viewOpts->bShowMinAmnt   =
      $viewOpts->bShowMaxAmnt   =
      $viewOpts->bShowSortBy    = true;
      $viewOpts->bShowAggregateDonor =
      $viewOpts->bShowACOAll         =
      $viewOpts->bShowAcct           =
      $viewOpts->bShowCamp           =
      $viewOpts->bShowYear           = false;
      $viewOpts->blockLabel          = 'Donations';
   }

      /*-----------------------------
         verification routines
      -----------------------------*/
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
   
   function stripCommas(&$strAmount){
      $strAmount = str_replace (',', '', $strAmount);
      return(true);
   }

   function rptCBHAmount($strMaxAmount){
      $curMin = (float)trim($_POST['txtMinAmount']);
      $curMax = (float)trim($strMaxAmount);
      return($curMin <= $curMax);
   }

   function accountOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->acctCampOpts(true);
   }

   function campOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->acctCampOpts(false);
   }

   function acctCampOpts($bAcct){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gbDateFormatUS, $glChapterID;
      if (!bTestForURLHack('showFinancials')) return;
      $displayData = array();
      $displayData['js'] = '';

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model  ('donations/maccts_camps', 'clsAC');
      $this->load->helper ('dl_util/web_layout');
      $this->load->library('generic_form');
      $this->load->helper ('dl_util/time_date');
      $this->load->model  ('admin/morganization', 'clsChapter');
      $this->load->model  ('admin/madmin_aco', 'clsACO');

         // time frame support
      $this->load->helper ('reports/date_range_def');
      $this->load->helper ('reports/date_range');
      $this->load->library('js_build/java_joe_radio');
      $this->load->model  ('util/mserial_objects', 'cSO');
      $displayData['js'] .= $this->java_joe_radio->insertJavaJoeRadio().$this->java_joe_radio->insertSetDateRadio();
      tf_initDateRangeMenu($displayData['viewOpts']);

      $this->initViewOpts($displayData['viewOpts']);
      if ($bAcct){
         $displayData['viewOpts']->bShowAcct  = true;
         $displayData['viewOpts']->blockLabel = 'Donations by Account';
      }else {
         $displayData['viewOpts']->bShowCamp  = true;
         $displayData['viewOpts']->blockLabel = 'Donations by Campaign';
      }
      $displayData['viewOpts']->bShowAggregateDonor = true;

      $this->setValidationRules($displayData['viewOpts']);

      if ($this->form_validation->run() == FALSE){
         if ($bAcct){
            $displayData['frmLink'] = 'reports/pre_gifts/accountOpts';
         }else{
            $displayData['frmLink'] = 'reports/pre_gifts/campOpts';
         }

         $this->load->library('generic_form');

            //------------------------------------
            // load the check/uncheck support
            //------------------------------------
         $this->load->helper('js/set_check_boxes');
         $displayData['js'] .= insertCheckSet();
         $this->load->helper('js/verify_check_set');
         $displayData['js'] .= verifyCheckSet();

         if ($bAcct){
            $this->clsAC->loadAccounts(false, false, null);
            $displayData['lNumAccts'] = $this->clsAC->lNumAccts;
         }else {
            $this->clsAC->loadCampaigns(false, false, null, false, null);
            $displayData['lNumCamps'] = $this->clsAC->lNumCamps;
         }

         if (validation_errors()==''){
            $this->clsChapter->lChapterID = $glChapterID;
            $this->clsChapter->loadChapterInfo();

            $this->setInitialRptVal($displayData['viewOpts'], $displayData['formData']);
            if ($bAcct){
               foreach ($this->clsAC->accounts as $acct){
                  $acct->bSel = false;
               }
            }else {
               foreach ($this->clsAC->campaigns as $camp){
                  $camp->bSel = false;
               }
            }
         }else {
            setOnFormError($displayData);
            $this->setErrRptVal($displayData['viewOpts'], $displayData['formData']);
            if ($bAcct){
               foreach ($this->clsAC->accounts as $acct){
                  $acct->bSel = (array_search($acct->lKeyID, $_POST['chkAccts'])!==false);
               }
            }else {
               foreach ($this->clsAC->campaigns as $camp){
                  $camp->bSel = (array_search($camp->lKeyID, $_POST['chkCamps'])!==false);
               }
            }
               // time frame support
            $this->tf_setTFOptsOnFormError($displayData['viewOpts']);
         }
         
            // time frame support
         $displayData['dateRanges'] = $strRange = tf_strDateRangeMenu($displayData['viewOpts']);         

         if ($bAcct){
            $displayData['accts'] = &$this->clsAC->accounts;
         }else {
            $displayData['camps'] = &$this->clsAC->campaigns;
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                   .' | Donations by Account';

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'reports/pre_gift_time_frame_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $reportID = $this->strLoadPostGiftRpt($displayData['viewOpts'],
                             ($bAcct ? CENUM_REPORTNAME_GIFTACCOUNT : CENUM_REPORTNAME_GIFTCAMP));
         redirect('reports/reports/run/'.$reportID);
      }
   }
   
   function yearEndOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gbDateFormatUS, $glChapterID;
      if (!bTestForURLHack('showFinancials')) return;
      $displayData = array();

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper ('dl_util/web_layout');
      $this->load->library('generic_form');
      $this->load->helper ('dl_util/time_date');
      $this->load->model  ('admin/morganization', 'clsChapter');
      $this->load->model  ('admin/madmin_aco', 'clsACO');

      $displayData['viewOpts'] = new stdClass;
      $this->initViewOpts($displayData['viewOpts']);
      $displayData['viewOpts']->bShowYear      =
      $displayData['viewOpts']->bShowAggregateDonor = true;

      $displayData['viewOpts']->bShowTimeFrame =
      $displayData['viewOpts']->bShowMinAmnt   =
      $displayData['viewOpts']->bShowMaxAmnt   = false;
      $displayData['viewOpts']->strFormName     = 'frmTFRpt';
      $displayData['viewOpts']->strID           = 'idTFRpt';

      $displayData['viewOpts']->blockLabel = 'Year-End Donation Report';

      $this->setValidationRules($displayData['viewOpts']);

      if ($this->form_validation->run() == FALSE){
         $displayData['frmLink'] = 'reports/pre_gifts/yearEndOpts';

         $this->load->library('generic_form');

         if (validation_errors()==''){
            $this->clsChapter->lChapterID = $glChapterID;
            $this->clsChapter->loadChapterInfo();
            $this->setInitialRptVal($displayData['viewOpts'], $displayData['formData']);
         }else {
//            setOnFormError($displayData);
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                   .' | Year-End Donation Report';

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'reports/pre_gift_time_frame_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $reportID = $this->strLoadPostGiftRpt($displayData['viewOpts'], CENUM_REPORTNAME_GIFTYEAREND);
         redirect('reports/reports/run/'.$reportID);
      }
   }

   function agOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showFinancials')) return;
      $this->agCommon(CENUM_REPORTNAME_GIFTAGG);
   }

   function agSponOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showSponsorFinancials')) return;
      $this->agCommon(CENUM_REPORTNAME_GIFTSPONAGG);
   }

   function agCommon($enumAgType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $gbDateFormatUS, $glChapterID;
//      if (!bTestForURLHack('showFinancials')) return;
      $displayData = array();
      $displayData['js'] = '';

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper('dl_util/web_layout');
      $this->load->library('generic_form');
      $this->load->helper('dl_util/time_date');
      $this->load->model('admin/morganization', 'clsChapter');
      $this->load->model('admin/madmin_aco', 'clsACO');
      
         // time frame support
      $this->load->helper ('reports/date_range_def');
      $this->load->helper ('reports/date_range');
      $this->load->library('js_build/java_joe_radio');
      $this->load->model  ('util/mserial_objects', 'cSO');
      $displayData['js'] .= $this->java_joe_radio->insertJavaJoeRadio().$this->java_joe_radio->insertSetDateRadio();
      tf_initDateRangeMenu($displayData['viewOpts']);

      $this->initViewOpts($displayData['viewOpts']);

      $displayData['viewOpts']->bShowACOAll    =
      $displayData['viewOpts']->bShowMinAmnt   =
      $displayData['viewOpts']->bShowMaxAmnt   =
      $displayData['viewOpts']->bShowSortBy    =
      $displayData['viewOpts']->bShowAcct      =
      $displayData['viewOpts']->bShowCamp      =
      $displayData['viewOpts']->bShowIncludes  =
      $displayData['viewOpts']->bShowYear      = false;

      switch ($enumAgType){
         case CENUM_REPORTNAME_GIFTAGG:
            $displayData['viewOpts']->blockLabel     = 'Donations Aggregate Report';
            $displayData['frmLink']   = 'reports/pre_gifts/agOpts';
            $displayData['pageTitle'] = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                     .' | Donation Aggregate Report';
            break;
         case CENUM_REPORTNAME_GIFTSPONAGG:
            $displayData['viewOpts']->blockLabel     = 'Sponsorship Payments Aggregate Report';
            $displayData['frmLink'] = 'reports/pre_gifts/agSponOpts';
            $displayData['pageTitle'] = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                     .' | Sponsorship Payment Aggregate Report';
            break;
         default:
            screamForHelp($enumAgType.': unknow aggregate type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

      $this->setValidationRules($displayData['viewOpts']);
      if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');

         if (validation_errors()==''){
            $this->clsChapter->lChapterID = $glChapterID;
            $this->clsChapter->loadChapterInfo();
            $this->setInitialRptVal($displayData['viewOpts'], $displayData['formData']);

         }else {
            setOnFormError($displayData);
            $this->setErrRptVal($displayData['viewOpts'], $displayData['formData']);
            
               // time frame support
            $this->tf_setTFOptsOnFormError($displayData['viewOpts']);
         }
         
            // time frame support
         $displayData['dateRanges'] = $strRange = tf_strDateRangeMenu($displayData['viewOpts']);        

            //--------------------------
            // breadcrumbs
            //--------------------------

         $displayData['title']          = CS_PROGNAME.' | Reports';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'reports/pre_gift_time_frame_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $reportID = $this->strLoadPostGiftRpt($displayData['viewOpts'], $enumAgType);
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$displayData[viewOpts]   <pre>');
echo(htmlspecialchars( print_r($displayData['viewOpts'], true))); echo('</pre></font><br>');
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$reportID = $reportID <br></font>\n");

die;
// ------------------------------------- */
         
         redirect('reports/reports/run/'.$reportID);
      }
   }


   function giftsViaHMID($lHMID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showFinancials')) return;
      
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lHMID, 'honorarium/memorial ID');

      $this->load->model('reports/mreports',   'clsReports');
      $this->load->model('donations/mhon_mem', 'cHM');
      $lHMID = $this->cHM->lHMID = (integer)$lHMID;
      $this->cHM->loadHonMem('via HMID');
      $cHMTable = &$this->cHM->honMemTable[0];

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_HONMEMGIFTLIST,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lHMID'          => $lHMID,
                             'bHon'           => $cHMTable->ghm_bHon,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => true,
                             'viewFile'       => 'pre_generic_rpt_view'
                             );

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }









}