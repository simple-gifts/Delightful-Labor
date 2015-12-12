<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class exports extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function run($reportID, $lStartRec=null, $lRecsPerPage=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('allowExports')) return;
      $displayData = array();
      $this->load->helper('reports/report_util');
      $this->load->dbutil();
      $this->load->helper('download');

      if (!isset($_SESSION[CS_NAMESPACE.'Reports'][$reportID])){
         $this->session->set_flashdata('error', 'The report you requested is no longer available. Please run the report again.');
         redirect_Reports();
      }

      $sRpt = $_SESSION[CS_NAMESPACE.'Reports'][$reportID];
      $sRpt->timeStamp = time();

      modelLoadViaRptType($this, $sRpt);

      $strExport = $this->strExportPage($sRpt);
      force_download($this->strExportFN($sRpt), $strExport);
   }

   function strExportPage(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('allowExports')) return;
      switch ($sRpt->rptName){
         case CENUM_REPORTNAME_GROUP:
            return($this->groups->strGroupReportPage(
                                   $sRpt->enumContext, $sRpt->groupIDs, $sRpt->bShowAny,
                                   false,              null,            null));
            break;

         case CENUM_REPORTNAME_VOLJOBSKILL:
            return($this->clsVolSkills->strJobSkillsReportPage(
                                   $sRpt->skillIDs, $sRpt->bShowAny, $sRpt->bIncludeInactive,
                                   false,           null,            null));
            break;

         case CENUM_REPORTNAME_VOLHOURSVIAVID:
            return($this->clsVolHours->strHoursViaVIDReport(
                                                $sRpt,
                                                false, null, null, null));
            break;

         case CENUM_REPORTNAME_VOLHOURSTFSUM:
            if (bAllowAccess('showReports')){
               return($this->clsVolHours->strVolHoursTFSumReportExport(
                                   $sRpt,
                                   false, null, null));
            }else {
               return('');
            }
            break;

         case CENUM_REPORTNAME_SPONPASTDUE:
            $dummy = null;
            return($this->clsSCP->strSponsorPastDueReport($this, $sRpt, $sRpt->reportID, false, $dummy));
            break;

         case CENUM_REPORTNAME_GIFTACK:
            return($this->clsGifts->strGiftAckReportExport(
                                        $sRpt, null,
                                        false, null, null));
            break;

         case CENUM_REPORTNAME_GIFTRECENT:
            return($this->clsGifts->strGiftRecentReportExport(
                                        $sRpt, null,
                                        false, null, null));
            break;

         case CENUM_REPORTNAME_HONMEMGIFTLIST:
            return($this->clsGifts->strHonMemReportExport(
                                        $sRpt,
                                        false, null, null));
            break;

         case CENUM_REPORTNAME_GIFTTIMEFRAME:
         case CENUM_REPORTNAME_GIFTACCOUNT:
         case CENUM_REPORTNAME_GIFTCAMP:
         case CENUM_REPORTNAME_GIFTYEAREND:
            return($this->clsGifts->strGiftTimeFrameReportExport(
                                        $sRpt, null,
                                        false, null, null));
            break;

         case CENUM_REPORTNAME_SPONVIAPROG:
            $dummy = null;
            return($this->clsSponProg->strSponViaProgReportExport(
                                                $sRpt, $dummy,
                                                false,  null, null));
            break;

         case CENUM_REPORTNAME_SPONVIALOCID:
            $dummy = null;
            return($this->clsSpon->strSponViaLocReportExport(
                                                $sRpt, $dummy,
                                                false,  null, null));
            break;

         case CENUM_REPORTNAME_SPONWOCLIENT:
            $dummy = null;
            return($this->clsSpon->strSponWOClientReportExport(
                                                $sRpt, $dummy,
                                                false,  null, null));
            break;

         case CENUM_REPORTNAME_ATTRIB:
            $dummy = null;
            return($this->clsAttrib->strAttribReportExport(
                                                $sRpt, $dummy,
                                                false,  null, null));
            break;

         case CENUM_REPORTNAME_SPONINCOMEMONTH:
            $dummy = null;
            return($this->clsSpon->strSponIncomeMonthReportExport(
                                                $sRpt, $dummy,
                                                false,  null, null));
            break;

         case CENUM_REPORTNAME_CLIENTAGE:
            return($this->cCRpt->strClientAgeReportExport(
                                        $sRpt,
                                        false, null, null));
            break;

         case CENUM_REPORTNAME_CLIENTVIASTATUS:
            return($this->cCRpt->strCViaStatIDReportExport(
                                        $sRpt,
                                        false, null, null));
            break;

         case CENUM_REPORTNAME_CLIENTVIASTATCAT:
            return($this->cCRpt->strCViaStatCatIDReportExport(
                                        $sRpt,
                                        false, null, null));
            break;

         case CENUM_REPORTNAME_CLIENTBDAY:
            return($this->cCRpt->strClientBDayReportExport(
                                        $sRpt,
                                        false, null, null));
            break;

         case CENUM_REPORTNAME_VOLEVENTSCHEDULE:
            $dummy = null;
            return($this->clsVolEvents->scheduleReportExport($sRpt, $dummy, false));
            break;

         case CENUM_REPORTNAME_VOLHRS_PVA:
            return($this->clsVolHours->strVolHoursPVAReportExport(
                                   $sRpt,
                                   false, null, null));
            break;

         case CENUM_REPORTNAME_VOLHRSDETAIL_PVA:
            return($this->clsVolHours->strVolHoursPVADetailReportExport(
                                   $sRpt,
                                   false, null, null));
            break;

         case CENUM_REPORTNAME_VOL_HRS_SUM:
            return($this->clsVolHours->strVolHoursSumReportExport(
                                   $sRpt,
                                   false, null, null));
            break;

         case CENUM_REPORTNAME_VOL_HRS_MON:
            return($this->clsVolHours->strVolHoursViaMonthReportExport(
                                   $sRpt,
                                   false, null, null));
            break;

         case CENUM_REPORTNAME_VOL_HRS_DETAIL:
            return($this->clsVolHours->strVolHoursDetailReportExport(
                                   $sRpt,
                                   false, null, null));
            break;

         case CENUM_REPORTNAME_DEPOSITLOG:
            return($this->clsDeposits->strDepositLogReportExport(
                                   $sRpt,
                                   false, null, null));
            break;

         case CENUM_REPORTNAME_DEPOSITENTRY:
            return($this->clsDeposits->strDepositEntryExport($sRpt));
            break;

         default:
            $this->session->set_flashdata('error', 'This report is currenly not available for export.');
            redirect_Reports();
            break;
      }
   }

   function strExportFN(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateTime = date('Ymd_His');

      switch ($sRpt->rptName){
         case CENUM_REPORTNAME_GROUP:
            $strFN = 'rptGroup_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_VOLJOBSKILL:
            $strFN = 'rptVolSkills_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_VOLHOURSVIAVID:
            $strFN = 'rptVolHours_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_VOLHOURSTFSUM:
            $strFN = 'rptVolHoursTFSummary_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_SPONPASTDUE:
            $strFN = 'rptSponsorsPastDue_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_HONMEMGIFTLIST:
            $bHon = $sRpt->bHon;
            $strFN = 'rpt'.($bHon ? 'Hon' : 'Mem').'Gifts_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_GIFTACK:
            switch ($sRpt->enumAckType){
               case 'gifts': $strFN = 'rptGiftAck_'      .$strDateTime.'.csv'; break;
               case 'hon'  : $strFN = 'rptHonorariumAck_'.$strDateTime.'.csv'; break;
               case 'mem'  : $strFN = 'rptMemorialAck_'  .$strDateTime.'.csv'; break;
               default: screamForHelp($sRpt->enumAckType.': Unknow report type<br>error on line <b>'.__LINE__.'</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__); break;
            }
            break;

         case CENUM_REPORTNAME_GIFTRECENT:
            $strFN = 'rptGiftsRecent_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_GIFTTIMEFRAME:
            $strFN = 'rptGiftsTimeframe_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_GIFTACCOUNT:
            $strFN = 'rptGiftsAccount_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_GIFTCAMP:
            $strFN = 'rptGiftsCampaign_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_GIFTYEAREND:
            $strFN = 'rptGiftsYearEnd_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_SPONVIAPROG:
            $strFN = 'rptSponViaProgram_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_SPONVIALOCID:
            $strFN = 'rptSponViaClientLocation_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_SPONWOCLIENT:
            $strFN = 'rptSponWOClients_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_ATTRIB:
            $strFN = 'rptAttrib_'.$sRpt->enumContext.'_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_SPONINCOMEMONTH:
            $strFN = 'rptSponIncome_'.$sRpt->lYear.'_'.str_pad($sRpt->lMonth, 2, '0', STR_PAD_LEFT)
                         .'_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_CLIENTAGE:
            $strFN = 'rptClientAge_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_CLIENTBDAY:
            $strFN = 'rptClientBirthday_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_CLIENTVIASTATUS:
            $strFN = 'rptClientsViaStatus_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_CLIENTVIASTATCAT:
            $strFN = 'rptClientsViaStatusCategory_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_VOLEVENTSCHEDULE:
            $strFN = 'rptVolSchedule_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_VOLHRS_PVA:
            $strFN = 'rptVolHoursPVA_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_VOL_HRS_MON:
            $strFN = 'rptVolHoursMonth_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_VOL_HRS_SUM:
            if (is_null($sRpt->lMon)){
               $strFN = 'rptVolHoursSumYear_'.$strDateTime.'.csv';
            }else {
               $strFN = 'rptVolHoursSumMonth_'.$strDateTime.'.csv';
            }
            break;

         case CENUM_REPORTNAME_VOL_HRS_DETAIL:
            if (is_null($sRpt->lMon)){
               $strFN = 'rptVolHoursDetailYear_'.$strDateTime.'.csv';
            }else {
               $strFN = 'rptVolHoursDetailMonth_'.$strDateTime.'.csv';
            }
            break;

         case CENUM_REPORTNAME_VOLHRSDETAIL_PVA:
            $strFN = 'rptVolHoursPVADetail_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_DEPOSITLOG:
            $strFN = 'rptDepositReports_'.$strDateTime.'.csv';
            break;

         case CENUM_REPORTNAME_DEPOSITENTRY:
            $strFN = 'rptDeposit_'.$strDateTime.'.csv';
            break;

         default:
            screamForHelp($sRpt->rptName.': Unknow report type<br>error on line <b>-- '.__LINE__.'</b> -- <br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($strFN);
   }

   function showTableOpts(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      if (!bTestForURLHack('allowExports')) return;

      $this->load->model ('personalization/muser_fields', 'clsUF');
      $this->load->model ('admin/mpermissions',           'perms');
      $this->load->model ('vol_reg/mvol_reg',             'volReg');
      $this->load->helper('img_docs/link_img_docs');
      $this->load->helper('vols/vol_links');

         // volunteer registration
      $this->volReg->loadVolRegForms();
      $displayData['lNumVolRegForms'] = $this->volReg->lNumRegRecs;
      $displayData['volRegForms']     = &$this->volReg->regRecs;

         // personalized tables
      $idx = $lNumTabsTot = 0; $pTabs = array();
      $this->perms->loadUserAcctInfo($glUserID, $acctAccess);

      foreach ($this->clsUF->enumTableTypes as $enumTType){
         $pTabs[$idx] = new stdClass;
         $this->clsUF->setTType($enumTType);
         $pTabs[$idx]->strTTypeLabel = $this->clsUF->strTTypeLabel;
         $pTabs[$idx]->enumTType     = $enumTType;

         $this->clsUF->loadTablesViaTType();
         $pTabs[$idx]->lNumTables = $lNumTables = $this->clsUF->lNumTables;
//         $lNumTabsTot += $lNumTables;
         if ($lNumTables > 0){
            $pTabs[$idx]->userTables = $this->clsUF->userTables;
            $jIdx = 0;
            foreach ($this->clsUF->userTables as $clsUTable){
               if ($this->perms->bDoesUserHaveAccess($acctAccess, $clsUTable->lNumConsolidated, $clsUTable->cperms)){

                  $pTabs[$idx]->userTables[$jIdx]->lNumFields =
                              $this->clsUF->lNumUF_TableFields($clsUTable->lKeyID);
                  ++$lNumTabsTot;
                  ++$jIdx;
               }
            }
         }
         ++$idx;
      }
      $displayData['lNumTabsTot'] = $lNumTabsTot;
      $displayData['pTabs'] = &$pTabs;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Exports';

      $displayData['title']          = CS_PROGNAME.' | Exports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/export_opts_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }



   function exportVolReg($lVolRegID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('allowExports')) return;
      $lVolRegID = (int)$lVolRegID;

      $this->load->model  ('vol_reg/mvol_reg',             'volReg');
      $this->load->model  ('admin/mpermissions');
      $this->load->model  ('personalization/muser_fields');
      $this->load->model  ('personalization/muser_fields_display');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');      
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('personalization/field_display');
      $this->load->helper ('download');

      $strDateTime = date('Ymd_His');

      $strExport = $this->volReg->strExportVolReg($lVolRegID);  
      force_download('volReg_'.str_pad($lVolRegID, 5, '0', STR_PAD_LEFT).'_'.$strDateTime.'.csv', $strExport);
   }



}