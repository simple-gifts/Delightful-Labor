<?php
/*
      $this->load->helper('reports/report_util');
*/

   function modelLoadViaRptType(&$clsLocal, &$sRpt){
   /*---------------------------------------------------------------------
      another way... Note that get_instance is a CI function, defined in
      system/core/CodeIgniter.php

      from http://stackoverflow.com/questions/4740430/explain-ci-get-instance

      $CI =& get_instance(); // use get_instance, it is less prone to failure in this context.

   ---------------------------------------------------------------------*/
      if ($sRpt->rptType == CENUM_REPORTTYPE_PREDEFINED){
         $clsLocal->load->helper('dl_util/rs_navigate');
         switch ($sRpt->rptName){
            case CENUM_REPORTNAME_GROUP:
               $clsLocal->load->model  ('groups/mgroups', 'groups');
               $clsLocal->load->helper ('dl_util/context');
               $clsLocal->load->helper ('groups/groups');
               $clsLocal->load->library('util/dl_date_time', '', 'clsDateTime');
               break;

            case CENUM_REPORTNAME_VOLJOBSKILL:
               $clsLocal->load->model('vols/mvol_skills', 'clsVolSkills');
               break;

            case CENUM_REPORTNAME_VOLHOURS:
               $clsLocal->load->model('vols/mvol_event_hours', 'clsVolHours');
               break;
               
            case CENUM_REPORTNAME_VOLHOURSTFSUM:
               $clsLocal->load->model('vols/mvol_event_hours', 'clsVolHours');
               $clsLocal->clsVolHours->buildVolHoursTempTable($sRpt->strBetween, $sRpt->tmpTable);
               break;

            case CENUM_REPORTNAME_VOL_JOBCODE_YEAR:
               $clsLocal->load->helper('dl_util/time_date');
               $clsLocal->load->helper('vols/vol_links');
               $clsLocal->load->model('util/mlist_generic', 'cList');
               $clsLocal->load->model('vols/mvol_job_codes', 'cVJobCodes');
               break;

            case CENUM_REPORTNAME_VOL_JOBCODE_MONTH:
               $clsLocal->load->helper('dl_util/time_date');
               $clsLocal->load->helper('vols/vol_links');
               $clsLocal->load->model('util/mlist_generic', 'cList');
               $clsLocal->load->model('vols/mvol_job_codes', 'cVJobCodes');
               break;

            case CENUM_REPORTNAME_VOLHRS_PVA:
               $clsLocal->load->helper('vols/vol');
               $clsLocal->load->helper('dl_util/time_duration_helper');
               $clsLocal->load->model('vols/mvol_event_hours',   'clsVolHours');
               $clsLocal->load->model('vols/mvol',               'clsVol');
               break;

            case CENUM_REPORTNAME_VOLHRSDETAIL_PVA:
               $clsLocal->load->helper('vols/vol');
               $clsLocal->load->helper('dl_util/time_duration_helper');
               $clsLocal->load->model('vols/mvol',               'clsVol');
               $clsLocal->load->model('people/mpeople',          'clsPeople');
               $clsLocal->load->model('vols/mvol_event_hours',   'clsVolHours');
               $params = array('enumStyle' => 'terse', 'clsRpt');
               $clsLocal->load->library('generic_rpt', $params);
               break;

            case CENUM_REPORTNAME_VOLHOURSDETAIL:
               $clsLocal->load->model('vols/mvol_event_hours',   'clsVolHours');
               $clsLocal->load->model('vols/mvol_events',        'clsVolEvents');
               $clsLocal->load->model('vols/mvol_event_dates',   'clsVolEventDates');
               $clsLocal->load->model('vols/mvol',               'clsVol');
               $clsLocal->load->model('people/mpeople',          'clsPeople');
//               $clsLocal->load->helper('dl_util/email_web');
               $clsLocal->load->helper('dl_util/time_date');
               break;

            case CENUM_REPORTNAME_VOLHOURSVIAVID:
               $clsLocal->load->model('vols/mvol_event_hours', 'clsVolHours');
               $clsLocal->load->model('vols/mvol',           'clsVol');
               $clsLocal->load->model('people/mpeople',      'clsPeople');
//               $clsLocal->load->helper('dl_util/email_web');
               $clsLocal->load->helper('dl_util/time_date');
               break;

            case CENUM_REPORTNAME_VOL_HRS_YEAR:
            case CENUM_REPORTNAME_VOL_HRS_MON:
               $clsLocal->load->model('vols/mvol_event_hours', 'clsVolHours');
               $clsLocal->load->helper('dl_util/time_date');
               break;

            case CENUM_REPORTNAME_VOL_HRS_SUM:
               $clsLocal->load->model('vols/mvol_event_hours', 'clsVolHours');
               $clsLocal->load->model('vols/mvol',             'clsVol');
               $clsLocal->load->helper('dl_util/time_date');
               break;

            case CENUM_REPORTNAME_VOL_HRS_DETAIL:
               $clsLocal->load->model('vols/mvol_event_hours', 'clsVolHours');
               $clsLocal->load->model('vols/mvol',             'clsVol');
               $clsLocal->load->model('people/mpeople',            'clsPeople');
//               $clsLocal->load->helper('dl_util/email_web');
               $clsLocal->load->helper('dl_util/time_date');
               break;

            case CENUM_REPORTNAME_VOLHOURSCHEDULE:
               $clsLocal->load->model('vols/mvol_events',           'clsVolEvents');
               $clsLocal->load->model('vols/mvol_event_dates_shifts', 'clsShifts');
               $clsLocal->load->model('vols/mvol',                 'clsVol');
               $clsLocal->load->model('people/mpeople',            'clsPeople');
//               $clsLocal->load->helper('dl_util/email_web');
               $clsLocal->load->helper('dl_util/time_date');
               break;

            case CENUM_REPORTNAME_VOLEVENTSCHEDULE:
               $clsLocal->load->model('vols/mvol_events',                  'clsVolEvents');
               $clsLocal->load->model('vols/mvol_event_dates',             'clsVolEventDates');
               $clsLocal->load->model('vols/mvol_event_dates_shifts',      'clsShifts');
               $clsLocal->load->model('vols/mvol_event_dates_shifts_vols', 'clsSV');
               $clsLocal->load->model('vols/mvol',                         'clsVol');
               $clsLocal->load->model('people/mpeople',                    'clsPeople');
//               $clsLocal->load->helper('dl_util/email_web');
               $clsLocal->load->helper('dl_util/time_date');
               break;

            case CENUM_REPORTNAME_SPONPASTDUE:
               $clsLocal->load->model('sponsorship/msponsorship',      'clsSpon');
               $clsLocal->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
               $clsLocal->load->model('admin/madmin_aco',               'clsACO');
               $clsLocal->load->library('util/dl_date_time', '',        'clsDateTime');
               $clsLocal->load->helper('dl_util/web_layout');
               $params = array('enumStyle' => 'terse', 'clsRpt');
               $clsLocal->load->library('generic_rpt', $params);
               break;

            case CENUM_REPORTNAME_GIFTACK:
               $clsLocal->load->model('donations/mdonations', 'clsGifts');
               $clsLocal->load->model('admin/madmin_aco',               'clsACO');
               break;

            case CENUM_REPORTNAME_GIFTRECENT:
               $clsLocal->load->model('admin/madmin_aco',      'clsACO');
               $clsLocal->load->model('donations/mdonations', 'clsGifts');
               break;

            case CENUM_REPORTNAME_HONMEMGIFTLIST:
               $clsLocal->load->model('admin/madmin_aco',      'clsACO');
               $clsLocal->load->model('donations/mdonations', 'clsGifts');
               $clsLocal->load->model('donations/mhon_mem', 'clsHonMem');
               break;

            case CENUM_REPORTNAME_GIFTTIMEFRAME:
            case CENUM_REPORTNAME_GIFTYEAREND:
               $clsLocal->load->model('admin/madmin_aco',      'clsACO');
               $clsLocal->load->model('donations/mdonations', 'clsGifts');
               break;

            case CENUM_REPORTNAME_GIFTAGG:
               $clsLocal->load->model('admin/madmin_aco',        'clsACO');
               $clsLocal->load->model('donations/mdonation_agg', 'clsAgg');
               $clsLocal->load->helper('dl_util/web_layout');
               $params = array('enumStyle' => 'terse', 'clsRpt');
               $clsLocal->load->library('generic_rpt', $params);
               $clsLocal->load->model ('util/mgoogle_charts', 'cGoogleRpts');
               $clsLocal->load->model('donations/maccts_camps', 'clsAC');
               break;

            case CENUM_REPORTNAME_GIFTSPONAGG:
               $clsLocal->load->model('admin/madmin_aco',        'clsACO');
               $clsLocal->load->model('donations/mdonation_agg', 'clsAgg');
               $clsLocal->load->helper('dl_util/web_layout');
               $params = array('enumStyle' => 'terse', 'clsRpt');
               $clsLocal->load->library('generic_rpt', $params);
               $clsLocal->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
               $clsLocal->load->model('clients/mclient_locations', 'clsLoc');
               break;

            case CENUM_REPORTNAME_GIFTACCOUNT:
            case CENUM_REPORTNAME_GIFTCAMP:
               $clsLocal->load->model('admin/madmin_aco',       'clsACO');
               $clsLocal->load->model('donations/mdonations',   'clsGifts');
               $clsLocal->load->model('donations/maccts_camps', 'clsAC');
               break;

            case CENUM_REPORTNAME_SPONVIAPROG:
               $clsLocal->load->library('util/dl_date_time', '', 'clsDataTime');
               $clsLocal->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
               $clsLocal->load->model('sponsorship/msponsorship', 'clsSpon');
               $params = array('enumStyle' => 'terse', 'clsRpt');
               $clsLocal->load->library('generic_rpt', $params);
               break;

            case CENUM_REPORTNAME_SPONVIALOCID:
               $clsLocal->load->library('util/dl_date_time', '', 'clsDataTime');
               $clsLocal->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
               $clsLocal->load->model('sponsorship/msponsorship', 'clsSpon');
               $clsLocal->load->model('clients/mclient_locations', 'clsLoc');
               $params = array('enumStyle' => 'terse', 'clsRpt');
               $clsLocal->load->library('generic_rpt', $params);
               break;

            case CENUM_REPORTNAME_SPONWOCLIENT:
               $clsLocal->load->model('sponsorship/msponsorship', 'clsSpon');
               $clsLocal->load->model('admin/madmin_aco',         'clsACO');
               $clsLocal->load->helper('dl_util/time_date');
               $params = array('enumStyle' => 'terse', 'clsRpt');
               $clsLocal->load->library('generic_rpt', $params);
               break;

            case CENUM_REPORTNAME_ADMINUSAGE:
            case CENUM_REPORTNAME_ADMINUSERLOGIN:
               $clsLocal->load->model('admin/muser_log', 'clsUserLog');
               $params = array('enumStyle' => 'terse', 'clsRpt');
               $clsLocal->load->library('generic_rpt', $params);
               break;

            case CENUM_REPORTNAME_ATTRIB:
               $clsLocal->load->model ('util/mattrib',        'clsAttrib');
               $clsLocal->load->model('reports/mexports',     'clsExports');
               $params = array('enumStyle' => 'terse', 'clsRpt');
               $clsLocal->load->library('generic_rpt', $params);
               $clsLocal->load->library('util/dl_date_time', '', 'clsDateTime');
               break;

            case CENUM_REPORTNAME_CLIENTAGE:
               $clsLocal->load->model('clients/mclients', 'clsClients');
               $clsLocal->load->model('reports/mclient_reports', 'cCRpt');
               $clsLocal->load->helper('clients/client');
               $clsLocal->load->helper('dl_util/time_date');
               $clsLocal->load->library('util/dl_date_time', '', 'clsDateTime');
               $params = array('enumStyle' => 'terse', 'clsRpt');
               $clsLocal->load->library('generic_rpt', $params);
               break;

            case CENUM_REPORTNAME_CLIENTVIASTATUS:
            case CENUM_REPORTNAME_CLIENTVIASTATCAT:
               $clsLocal->load->model('clients/mclients', 'clsClients');
               $clsLocal->load->model('reports/mclient_reports', 'cCRpt');
               $clsLocal->load->helper('clients/client');
               $clsLocal->load->model('clients/mclient_status', 'clsClientStat');
               $clsLocal->load->helper('dl_util/time_date');
               $clsLocal->load->library('util/dl_date_time', '', 'clsDateTime');
               $params = array('enumStyle' => 'terse', 'clsRpt');
               $clsLocal->load->library('generic_rpt', $params);
               break;

            case CENUM_REPORTNAME_CLIENTBDAY:
               $clsLocal->load->model('clients/mclients', 'clsClients');
               $clsLocal->load->model('reports/mclient_reports', 'cCRpt');
               $clsLocal->load->model('clients/mclient_locations', 'clsLoc');
               $clsLocal->load->helper('clients/client');
               $clsLocal->load->helper('dl_util/time_date');
               $clsLocal->load->library('util/dl_date_time', '', 'clsDateTime');
               $params = array('enumStyle' => 'terse', 'clsRpt');
               $clsLocal->load->library('generic_rpt', $params);
               break;

            case CENUM_REPORTNAME_SPONINCOMEMONTH:
            case CENUM_REPORTNAME_SPONCHARGEMONTH:
               $clsLocal->load->model('sponsorship/msponsorship',       'clsSpon');
               $clsLocal->load->model('sponsorship/msponsor_charge_pay', 'clsSCP');
               $clsLocal->load->model('admin/madmin_aco',                'clsACO');
               $clsLocal->load->library('util/dl_date_time', '',        'clsDateTime');
               $clsLocal->load->helper('dl_util/time_date');
               $clsLocal->load->helper('dl_util/web_layout');
               $params = array('enumStyle' => 'terse', 'clsRpt');
               $clsLocal->load->library('generic_rpt', $params);
               break;

            case CENUM_REPORTNAME_DEPOSITLOG:
            case CENUM_REPORTNAME_DEPOSITENTRY:
               $clsLocal->load->model('financials/mdeposits', 'clsDeposits');
               $clsLocal->load->model('admin/madmin_aco',     'clsACO');
               $clsLocal->load->helper('dl_util/web_layout');
               $params = array('enumStyle' => 'terse', 'clsRpt');
               $clsLocal->load->library('generic_rpt', $params);
               break;

            case CENUM_REPORTNAME_CLIENT_PREPOST:
            case CENUM_REPORTNAME_CLIENT_PREPOSTDIR:
               $clsLocal->load->model('client_features/mcpre_post_tests', 'cpptests');
               $clsLocal->load->model('admin/mpermissions',               'perms');
               $clsLocal->load->model('personalization/muser_schema',     'cUFSchema');
               $clsLocal->load->model('clients/mclients',                 'clsClients');
               $clsLocal->load->model('clients/mclient_search',           'cClientSearch');
               break;

            default:
               screamForHelp($sRpt->rptName.': Unknow report type<br>error on line <b> -- '.__LINE__.' -- </b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }
      }else {
      }

   }

   function strRptBreadCrumb(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($sRpt->rptType == CENUM_REPORTTYPE_PREDEFINED){
         switch ($sRpt->rptName){
            case CENUM_REPORTNAME_GROUP:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_groups/showOpts/'.$sRpt->enumContext,
                       strLabelViaContextType($sRpt->enumContext, true, false).' Groups',
                             'class="breadcrumb"');
               break;

            case CENUM_REPORTNAME_VOLJOBSKILL:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_vol_job_skills/showOpts', 'Volunteer Skills', 'class="breadcrumb"');
               break;
               
            case CENUM_REPORTNAME_VOL_JOBCODE_YEAR:
            case CENUM_REPORTNAME_VOL_JOBCODE_MONTH:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_vol_jobcodes/showOpts', 'Volunteer Job Codes', 'class="breadcrumb"');
               break;

            case CENUM_REPORTNAME_VOLHOURSTFSUM:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_vol_hours/showOptsTFSum', 'Volunteer Summary by Time Frame', 'class="breadcrumb"');
               break;
               
            case CENUM_REPORTNAME_VOLHOURS:
            case CENUM_REPORTNAME_VOLHOURSDETAIL:
            case CENUM_REPORTNAME_VOLHOURSVIAVID:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_vol_hours/showOpts', 'Volunteer Hours', 'class="breadcrumb"');
               break;

            case CENUM_REPORTNAME_VOL_HRS_MON:
               $strCrumb =
                           anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                    .' | '.anchor('reports/pre_vol_hours/showOptsYear', 'Volunteer Hours by Year', 'class="breadcrumb"')
                    .' | '.anchor('reports/pre_vol_hours/yearRunY/'.$sRpt->lYear, 'Year '.$sRpt->lYear, 'class="breadcrumb"')
                    .' | Monthly Details';
               break;

            case CENUM_REPORTNAME_VOL_HRS_SUM:
               if (is_null($sRpt->lMon)){
                  $strCrumb =
                              anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                       .' | '.anchor('reports/pre_vol_hours/showOptsYear', 'Volunteer Hours by Year', 'class="breadcrumb"')
                       .' | Yearly Summary';
               }else {
                  $strCrumb =
                              anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                       .' | '.anchor('reports/pre_vol_hours/showOptsYear', 'Volunteer Hours by Year', 'class="breadcrumb"')
                       .' | '.anchor('reports/pre_vol_hours/yearRunY/'.$sRpt->lYear, 'Year '.$sRpt->lYear, 'class="breadcrumb"')
                       .' | Monthly Summary';
               }
               break;

            case CENUM_REPORTNAME_VOL_HRS_DETAIL:
               if (is_null($sRpt->lMon)){
                  $strCrumb =
                              anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                       .' | '.anchor('reports/pre_vol_hours/showOptsYear', 'Volunteer Hours by Year', 'class="breadcrumb"')
                       .' | Yearly Details';
               }else {
                  $strCrumb =
                              anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                       .' | '.anchor('reports/pre_vol_hours/showOptsYear', 'Volunteer Hours by Year', 'class="breadcrumb"')
                       .' | '.anchor('reports/pre_vol_hours/yearRunY/'.$sRpt->lYear, 'Year '.$sRpt->lYear, 'class="breadcrumb"')
                       .' | Monthly Details';
               }
               break;


            case CENUM_REPORTNAME_VOLHRS_PVA:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_vol_hours/showOptsPVA', 'Volunteer Hours - Scheduled vs. Actual', 'class="breadcrumb"');
               break;

            case CENUM_REPORTNAME_VOL_HRS_YEAR:
               $strCrumb =
                           anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                    .' | '.anchor('reports/pre_vol_hours/showOptsYear', 'Volunteer Hours by Year', 'class="breadcrumb"')
                    .' | Year '.$sRpt->lYear;
               break;

            case CENUM_REPORTNAME_VOLHRSDETAIL_PVA:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_vol_hours/showOptsPVA', 'Volunteer Hours - Scheduled vs. Actual', 'class="breadcrumb"')
                   .' | Details';
               break;

            case CENUM_REPORTNAME_VOLHOURSCHEDULE:
               $strCrumb =
                    anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"').' | '
                   .anchor('volunteers/vol_record/volRecordView/'.$sRpt->lVolID,
                            'Record', 'class="breadcrumb"').' | Volunteer Schedule';
               break;

            case CENUM_REPORTNAME_VOLEVENTSCHEDULE:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .'Event Schedule';
               break;

            case CENUM_REPORTNAME_SPONPASTDUE:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_spon_past_due/showOpts', 'Sponsor Past Due Report', 'class="breadcrumb"');
               break;

            case CENUM_REPORTNAME_HONMEMGIFTLIST:
               $bHon = $sRpt->bHon;
               $strCrumb =
                    anchor('main/menu/admin',        'Admin', 'class="breadcrumb"').' | '
                   .anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"').' | '
                   .anchor('admin/admin_special_lists/hon_mem/'.($bHon ? 'honView' : 'memView'),
                            ($bHon ? 'Honorariums' : 'Memorials'), 'class="breadcrumb"');
               break;


            case CENUM_REPORTNAME_GIFTACK:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_gift_ack/showOpts/'.$sRpt->enumAckType, 'Donation Acknowledgments', 'class="breadcrumb"');
               break;

            case CENUM_REPORTNAME_GIFTRECENT:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_gifts/recentOpts', 'Recent Donations', 'class="breadcrumb"');
               break;

            case CENUM_REPORTNAME_GIFTTIMEFRAME:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_gifts/timeframeOpts', 'Donations by Timeframe', 'class="breadcrumb"');
               break;

            case CENUM_REPORTNAME_GIFTACCOUNT:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_gifts/accountOpts', 'Donations by Account', 'class="breadcrumb"');
               break;

            case CENUM_REPORTNAME_GIFTCAMP:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_gifts/campOpts', 'Donations by Campaign', 'class="breadcrumb"');
               break;

            case CENUM_REPORTNAME_GIFTYEAREND:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_gifts/yearEndOpts', 'Year-End Donation Report', 'class="breadcrumb"');
               break;

            case CENUM_REPORTNAME_GIFTAGG:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_gifts/agOpts', 'Donation Aggregate Report', 'class="breadcrumb"');
               break;

            case CENUM_REPORTNAME_GIFTSPONAGG:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_gifts/agSponOpts', 'Sponsorship Payment Aggregate Report', 'class="breadcrumb"');
               break;

            case CENUM_REPORTNAME_SPONVIAPROG:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"').' | '
                   .anchor('reports/pre_spon_via_prog/showOpts', 'Sponsors Via Program', 'class="breadcrumb"');
               break;

            case CENUM_REPORTNAME_SPONVIALOCID:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                    .' | Sponsors Via Location';
               break;

            case CENUM_REPORTNAME_SPONWOCLIENT:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                    .' | Sponsors Without Clients';
               break;

            case CENUM_REPORTNAME_SPONINCOMEMONTH:
            case CENUM_REPORTNAME_SPONCHARGEMONTH:
               $strCrumb =
                           anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                    .' | '.anchor('reports/pre_spon_income/showOpts', 'Sponsorship Income', 'class="breadcrumb"')
                    .' | Sponsors Income via Month';
               break;

            case CENUM_REPORTNAME_ADMINUSAGE:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                   .' | Admin | User Logins';
               break;

            case CENUM_REPORTNAME_ADMINUSERLOGIN:
               $strCrumb =
                    anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                   .' | Admin | Login Log';
               break;

            case CENUM_REPORTNAME_ATTRIB:
               $strCrumb =
                          anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                   .' | '.anchor('reports/pre_attrib/attrib', 'Attribute', 'class="breadcrumb"')
                   .' | Details';
               break;

            case CENUM_REPORTNAME_CLIENTAGE:
               $strCrumb =
                          anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                   .' | '.anchor('reports/pre_clients/aggRptsLocAge', 'Client Aggregate Report', 'class="breadcrumb"')
                   .' | Client Age Details';
               break;

            case CENUM_REPORTNAME_CLIENTVIASTATUS:
               $strCrumb =
                          anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                   .' | '.anchor('reports/pre_clients/aggRptsStatus/'.($sRpt->bActive ? 'true' : 'false'),
                                 'Client Aggregate Report', 'class="breadcrumb"')
                   .' | Clients Via Status';
               break;

            case CENUM_REPORTNAME_CLIENTVIASTATCAT:
               $strCrumb =
                          anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                   .' | '.anchor('reports/pre_clients/aggRptsStatus/'.($sRpt->bActive ? 'true' : 'false'),
                                 'Client Aggregate Report', 'class="breadcrumb"')
                   .' | Client Via Status Category';
               break;


            case CENUM_REPORTNAME_CLIENTBDAY:
               $strCrumb =
                          anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                   .' | '.anchor('reports/pre_clients/bDay', 'Client Birthdays', 'class="breadcrumb"')
                   .' | Birthday Report';
               break;

            case CENUM_REPORTNAME_DEPOSITLOG:
               $strCrumb =
                          anchor('main/menu/financials', 'Financials', 'class="breadcrumb"')
                     .' | Deposit Log';
               break;

            case CENUM_REPORTNAME_CLIENT_PREPOST:
               $strCrumb =
                          anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                   .' | '.anchor('reports/pre_client_pre_post/opts', 'Client Pre/Post Tests', 'class="breadcrumb"')
                   .' | Client Pre/Post Test Report';
               break;

            case CENUM_REPORTNAME_CLIENT_PREPOSTDIR:
               $strCrumb =
                          anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                   .' | '.anchor('reports/pre_client_pre_post/dirViaTest', 'Client Pre/Post Test Directory', 'class="breadcrumb"')
                   .' | Client Pre/Post Test Directory';
               break;

            default:
               screamForHelp($sRpt->rptName.': Unknow report type<br>error on line <b>-- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }
      }else {
$strCrumb = 'TBD';
      }
   return($strCrumb);
   }

   function strExportFields_Vol(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return('
             vol_lKeyID   AS volunteerID, '.strExportFields_People());
   }

   function strExportFields_Sponsor(){
   /*---------------------------------------------------------------------
            FROM sponsor
               INNER JOIN people_names AS sponpeep   ON sponpeep.pe_lKeyID   = sp_lForeignID
               INNER JOIN admin_aco AS commitACO     ON commitACO.aco_lKeyID = sp_lCommitmentACO
               INNER JOIN lists_sponsorship_programs ON sc_lKeyID            = sp_lSponsorProgramID
               LEFT  JOIN client_records             ON cr_lKeyID            = sp_lClientID
               LEFT  JOIN client_location            ON cr_lLocationID       = cl_lKeyID
               LEFT  JOIN lists_client_vocab         ON cr_lVocID            = cv_lKeyID
               LEFT  JOIN people_names AS honpeep    ON honpeep.pe_lKeyID    = sp_lHonoreeID
               LEFT  JOIN lists_generic AS atab      ON sp_lAttributedTo     = lgen_lKeyID

   ---------------------------------------------------------------------*/
      global $gclsChapterVoc;
      
      $strDateFormat = strMysqlDateFormat(false);
      $strOut = "
             sp_lKeyID   AS `sponsorID`,
             sponpeep.pe_lKeyID     AS peopleID,
             sponpeep.pe_strLName   AS `Last Name`,  sponpeep.pe_strFName AS `First Name`,
             sponpeep.pe_strAddr1   AS `Address 1`,  sponpeep.pe_strAddr2 AS `Address 2`,
             sponpeep.pe_strCity    AS City,         
             sponpeep.pe_strState   AS `$gclsChapterVoc->vocState`,
             sponpeep.pe_strCountry AS Country,      
             sponpeep.pe_strZip     AS `$gclsChapterVoc->vocZip`,
             sponpeep.pe_strPhone   AS Phone,
             sponpeep.pe_strCell    AS Cell,
             sponpeep.pe_strEmail   AS Email,
             IF(sponpeep.pe_bBiz, 'Business', 'Person') AS `Sponsor Type`,
             IF(sp_bInactive, 'No', 'Yes')     AS `Sponsorship Active`,

             DATE_FORMAT(sp_dteStartMoYr, $strDateFormat) AS `Sponsorship Start Date`,
             DATE_FORMAT(sp_dteInactive,  $strDateFormat) AS `Sponsorship Inactive Date`,

             FORMAT(sp_curCommitment, 2) AS `Commitment`,
             commitACO.aco_strName  AS `Accounting Country`,
             sp_lSponsorProgramID   AS `Sponsorship Program ID`,
             sc_strProgram          AS `Sponsorship Program`,
             sp_lHonoreeID          AS `honoree peopleID`,
             honpeep.pe_strLName    AS `Honoree Last Name`,
             honpeep.pe_strFName    AS `Honoree First Name`
             ";
      if (bAllowAccess('showClients')){
            $strOut .= "
             ,
             cr_lKeyID              AS clientID,
             cr_strFName            AS `Client First Name`,
             cr_strLName            AS `Client Last Name`,
             cr_enumGender          AS `Client Gender`,
             cr_lLocationID         AS `Client Location ID`,
             cl_strLocation         AS `Client Location`,
             cl_strCountry          AS `Location Country`,
             cl_strAddress1         AS `Location Address 1`,
             cl_strAddress2         AS `Location Address 2`,
             cl_strCity             AS `Location City`, cl_strState AS `Location State`,
             cl_strPostalCode       AS `Location Postal Code`,

             cr_lVocID              AS `Vocabulary ID`,
             cv_strVocTitle         AS `Vocabulary Title`,
             cv_strVocClientS       AS `Voc: Client (singular)`,
             cv_strVocClientP       AS `Voc: Client (plural)`,
             cv_strVocSponsorS      AS `Voc: Sponsor (singular)`,
             cv_strVocSponsorP      AS `Voc: Sponsor (plural)`,
             cv_strVocLocS          AS `Voc: Location (singular)`,
             cv_strVocLocP          AS `Voc: Location (plural)`,
             cv_strVocSubLocS       AS `Voc: Sub-location (singular)`,
             cv_strVocSubLocP       AS `Voc: Sub-location (plural)`,

             atab.lgen_strListItem  AS `Attributed To`,

             DATE_FORMAT(cr_dteEnrollment, $strDateFormat) AS `Client Enrollment Date`,
             DATE_FORMAT(cr_dteBirth,      $strDateFormat) AS `Client Birth Date`
             ";
      }
      return($strOut);
   }

   function strExportFields_SponsorPayments(){
   /*---------------------------------------------------------------------
         FROM gifts
            INNER JOIN people_names AS payer       ON gi_lForeignID          = payer.pe_lKeyID
            INNER JOIN sponsor                     ON gi_lSponsorID          = sp_lKeyID
            INNER JOIN people_names AS spon        ON sp_lForeignID          = spon.pe_lKeyID
            INNER JOIN admin_aco                   ON gi_lACOID              = aco_lKeyID
            INNER JOIN lists_sponsorship_programs  ON sc_lKeyID              = sp_lSponsorProgramID

            LEFT  JOIN client_records              ON cr_lKeyID              = sp_lClientID
            LEFT  JOIN client_location             ON cr_lLocationID         = cl_lKeyID
            LEFT  JOIN lists_generic AS giftAttrib ON giftAttrib.lgen_lKeyID = gi_lAttributedTo
            LEFT  JOIN lists_generic AS payCat     ON payCat.lgen_lKeyID     = gi_lPaymentType

   ---------------------------------------------------------------------*/
      $strDateFormat     = strMysqlDateFormat(false);
      return("
            gi_lKeyID                                   AS `Payment ID`,
            gi_lSponsorID                               AS `Sponsor ID`,
            DATE_FORMAT(gi_dteDonation, $strDateFormat) AS `Payment Date`,
            FORMAT(gi_curAmnt, 2)                       AS `Amount`,

            gi_lACOID                                   AS `Accounting Country ID`,
            aco_strName                                 AS `Accounting Country`,

            gi_strCheckNum                              AS `Check Number`,
            gi_lPaymentType                             AS `paymentTypeID`,
            payCat.lgen_strListItem                     AS `Payment Type`,
            giftAttrib.lgen_strListItem                 AS `Attributed To`,
            gi_lDepositLogID                            AS `Deposit ID`,

            IF(payer.pe_bBiz, payer.pe_lKeyID, '')      AS `Payer Business ID`,
            IF(payer.pe_bBiz, '', payer.pe_lKeyID)      AS `Payer People ID`,


            IF(payer.pe_bBiz, 'Yes', 'No')              AS `Business Payment`,
            IF(payer.pe_bBiz, payer.pe_strLName, '')    AS `Payer Business Name`,
            IF(payer.pe_bBiz, '', payer.pe_strLName)    AS `Payer Last Name`,
            IF(payer.pe_bBiz, '', payer.pe_strFName)    AS `Payer First Name`,
            IF(payer.pe_bBiz, '', payer.pe_strMName)    AS `Payer Middle Name`,

            payer.pe_strAddr1                           AS `Payer Address 1`,
            payer.pe_strAddr2                           AS `Payer Address 2`,
            payer.pe_strCity                            AS `Payer City`,
            payer.pe_strState                           AS `Payer State`,
            payer.pe_strCountry                         AS `Payer Country`,
            payer.pe_strZip                             AS `Payer Zip`,
            payer.pe_strPhone                           AS `Payer Phone`,
            payer.pe_strCell                            AS `Payer Cell`,
            payer.pe_strEmail                           AS `Payer Email`,

            IF(spon.pe_bBiz, sp_lForeignID, '')         AS `Sponsor Business ID`,
            IF(spon.pe_bBiz, '', sp_lForeignID)         AS `Sponsor People ID`,

            IF(spon.pe_bBiz, 'Yes', 'No')               AS `Business Sponsor`,
            IF(spon.pe_bBiz, spon.pe_strLName, '')      AS `Sponsor Business Name`,
            IF(spon.pe_bBiz, '', spon.pe_strLName)      AS `Sponsor Last Name`,
            IF(spon.pe_bBiz, '', spon.pe_strFName)      AS `Sponsor First Name`,
            IF(spon.pe_bBiz, '', spon.pe_strMName)      AS `Sponsor Middle Name`,

            spon.pe_strAddr1                            AS `Sponsor Address 1`,
            spon.pe_strAddr2                            AS `Sponsor Address 2`,
            spon.pe_strCity                             AS `Sponsor City`,
            spon.pe_strState                            AS `Sponsor State`,
            spon.pe_strCountry                          AS `Sponsor Country`,
            spon.pe_strZip                              AS `Sponsor Zip`,
            spon.pe_strPhone                            AS `Sponsor Phone`,
            spon.pe_strCell                             AS `Sponsor Cell`,
            spon.pe_strEmail                            AS `Sponsor Email`,

            IF(spon.pe_bBiz, 'Business', 'Person')      AS `Sponsor Type`,
            IF(sp_bInactive, 'No', 'Yes')               AS `Sponsorship Active`,

            DATE_FORMAT(sp_dteStartMoYr, $strDateFormat) AS `Sponsorship Start Date`,
            DATE_FORMAT(sp_dteInactive,  $strDateFormat) AS `Sponsorship Inactive Date`,

            FORMAT(sp_curCommitment, 2) AS `Commitment`,
            sp_lSponsorProgramID   AS `Sponsorship Program ID`,
            sc_strProgram          AS `Sponsorship Program`,
            cr_lKeyID              AS clientID,
            cr_strFName            AS `Client First Name`,
            cr_strLName            AS `Client Last Name`,
            cr_enumGender          AS `Client Gender`,
            cr_lLocationID         AS `Client Location ID`,
            cl_strLocation         AS `Client Location`,
            cl_strCountry          AS `Location Country`,
            cl_strAddress1         AS `Location Address 1`,
            cl_strAddress2         AS `Location Address 2`,
            cl_strCity             AS `Location City`, cl_strState AS `Location State`,
            cl_strPostalCode       AS `Location Postal Code`,

            DATE_FORMAT(cr_dteEnrollment, $strDateFormat) AS `Client Enrollment Date`,
            DATE_FORMAT(cr_dteBirth,      $strDateFormat) AS `Client Birth Date`
             ");
   }

   function strExportFields_People(){
      global $gclsChapterVoc;

      return('
             peepTab.pe_lKeyID     AS peopleID,
             peepTab.pe_strTitle   AS Title,
             peepTab.pe_strLName   AS `Last Name`,  peepTab.pe_strFName AS `First Name`,
             peepTab.pe_strMName   AS `Middle Name`,
             peepTab.pe_enumGender AS `Gender`,
             peepTab.pe_strAddr1   AS `Address 1`,  peepTab.pe_strAddr2 AS `Address 2`,
             peepTab.pe_strCity    AS City,         peepTab.pe_strState AS `'.$gclsChapterVoc->vocState.'`,
             peepTab.pe_strCountry AS Country,      peepTab.pe_strZip   AS `'.$gclsChapterVoc->vocZip.'`,
             peepTab.pe_strPhone   AS Phone,        peepTab.pe_strCell  AS Cell,
             peepTab.pe_strEmail   AS Email ');
   }

   function strExportFields_Biz(){
      global $gclsChapterVoc;
      return('
             bizTab.pe_lKeyID     AS businessID,
             bizTab.pe_strLName   AS `Business Name`,
             bizTab.pe_strAddr1   AS `Address 1`,  bizTab.pe_strAddr2 AS `Address 2`,
             bizTab.pe_strCity    AS City,         
             bizTab.pe_strState   AS `'.$gclsChapterVoc->vocState.'`,
             bizTab.pe_strCountry AS Country,    
             bizTab.pe_strZip     AS `'.$gclsChapterVoc->vocZip.'`,
             bizTab.pe_strPhone   AS Phone,        
             bizTab.pe_strCell    AS Cell,
             bizTab.pe_strEmail   AS Email ');
   }

   function strExportFields_GiftsHonMem($bHon){
   /*---------------------------------------------------------------------
         FROM gifts_hon_mem_links
            INNER JOIN lists_hon_mem               ON ghm_lKeyID        = ghml_lHonMemID
            INNER JOIN gifts                       ON gi_lKeyID         = ghml_lGiftID
            INNER JOIN gifts_campaigns             ON gi_lCampID        = gc_lKeyID
            INNER JOIN gifts_accounts              ON gc_lAcctID        = ga_lKeyID
            INNER JOIN admin_aco                   ON gi_lACOID         = aco_lKeyID
            INNER JOIN people_names AS peepDonor   ON gi_lForeignID     = peepDonor.pe_lKeyID
            INNER JOIN people_names AS peepHM      ON ghm_lFID          = peepHM.pe_lKeyID

            LEFT  JOIN lists_generic AS payCat    ON payCat.lgen_lKeyID  = gi_lPaymentType
            LEFT  JOIN lists_generic AS giftCat   ON giftCat.lgen_lKeyID = gi_lMajorGiftCat
            LEFT  JOIN lists_generic AS gik       ON gi_lGIK_ID          = gik.lgen_lKeyID
            LEFT  JOIN people_names  AS peepMC    ON ghm_lMailContactID  = peepMC.pe_lKeyID
            LEFT JOIN admin_users    AS ackUser   ON ackUser.us_lKeyID   = ghml_lAckByID
   ---------------------------------------------------------------------*/
      $strLabel = $bHon ? 'Honorarium' : 'Memorial';

      $strDateFormat     = strMysqlDateFormat(false);

      $strOut = "
               gi_lKeyID                    AS giftID,
               gi_lCampID                   AS campaignID,
               gc_strCampaign               AS Campaign,

               ga_lKeyID                    AS accountID,
               ga_strAccount                AS Account,

               gi_lACOID                    AS accountingCountryID,
               aco_strName                  AS `Accounting Country`,

               IF(gi_bGIK, 'Yes', 'No')     AS `In-Kind`,
               gi_lGIK_ID                   AS inKindID,
               gik.lgen_strListItem         AS `In Kind Category`,

               DATE_FORMAT(gi_dteDonation, $strDateFormat) AS `Date of Donation`,
               FORMAT(gi_curAmnt, 2)        AS `Amount`,
               gi_strCheckNum               AS `Check Number`,
               gi_lPaymentType              AS `paymentTypeID`,
               payCat.lgen_strListItem      AS `Payment Type`,
               gi_lMajorGiftCat             AS `majorGiftCategoryID`,
               giftCat.lgen_strListItem     AS `Major Gift Category`,
               ghml_lKeyID                  AS `honMemLinkID`,
               ghml_lHonMemID               AS `honMemListID`,
               IF(ghm_bHon, 'Honorarium', 'Memorial')     AS `Honorarium Memorial`,
               ghm_lFID                     AS `$strLabel peopleID`,
               peepHM.pe_strFName           AS `$strLabel First Name`,
               peepHM.pe_strLName           AS `$strLabel Last Name`, ";

      if (!$bHon){
         $strOut .= "
            ghm_lMailContactID           AS `Mail Contact peopleID`,
            peepMC.pe_strFName           AS `Mail Contact First Name`,
            peepMC.pe_strLName           AS `Mail Contact Last Name`, ";
      }

      $strOut .= "
            IF((peepDonor.pe_bBiz*gi_lForeignID)=0, null, gi_lForeignID) AS `Donor businessID`,
            IF((peepDonor.pe_bBiz*gi_lForeignID)=0, gi_lForeignID, null) AS `Donor peopleID`,
            IF (peepDonor.pe_bBiz, peepDonor.pe_strLName, null)          AS `Donor Business Name`,
            IF (peepDonor.pe_bBiz, null, peepDonor.pe_strLName)          AS `Donor Last Name`,
            IF (peepDonor.pe_bBiz, null, peepDonor.pe_strFName)          AS `Donor First Name`,

            IF(ghml_bAck, 'Yes', 'No')   AS `$strLabel Acknowledged`,

            IF(ghml_bAck, DATE_FORMAT(ghml_dteAck, $strDateFormat), NULL) AS `Date $strLabel Acknowledged`,

            IF(ghml_bAck, ghml_lAckByID, NULL)                AS `Acknowledged By userID`,
            IF(ghml_bAck, CONCAT(ackUser.us_strFirstName, ' ', ackUser.us_strLastName), NULL) AS `Acknowledged By` ";
      return($strOut);
   }

   function strExportFields_GiftsDonorAggregate($bShowAccounts, $bShowCamps){
   /*---------------------------------------------------------------------
   /* The following joins are needed:
            FROM gifts
               INNER JOIN people_names ON pe_lKeyID = gi_lForeignID
               INNER JOIN admin_aco    ON gi_lACOID = aco_lKeyID

      if ($bShowAccounts || $bShowCamps), also include
               INNER JOIN gifts_campaigns ON gi_lCampID = gc_lKeyID
               INNER JOIN gifts_accounts  ON gc_lAcctID = ga_lKeyID

   ---------------------------------------------------------------------*/
      $strOut = "
         FORMAT(SUM(gi_curAmnt), 2) AS `Total Amount`,

         IF(pe_bBiz, pe_lKeyID, null)   AS businessID,
         IF(pe_bBiz, null, pe_lKeyID)   AS peopleID,
         IF(pe_bBiz, 'Yes', 'No')       AS `Business Donation`,
         IF(pe_bBiz, pe_strLName, null) AS `Donor Business Name`,
         IF(pe_bBiz, null, pe_strLName) AS `Donor Last Name`,
         IF(pe_bBiz, null, pe_strFName) AS `Donor First Name`,
         IF(pe_bBiz, null, pe_strMName) AS `Donor Middle Name`,
         pe_strAddr1   AS `Address 1`,
         pe_strAddr2   AS `Address 2`,
         pe_strCity    AS City,
         pe_strState   AS State,
         pe_strCountry AS Country,
         pe_strZip     AS Zip,
         pe_strPhone   AS Phone,
         pe_strCell    AS Cell,
         pe_strEmail   AS Email,

         gi_lACOID   AS accountingCountryID,
         aco_strName AS `Accounting Country`  ";

      if ($bShowAccounts || $bShowCamps){
         $strOut .= ', ga_strAccount AS `Account` ';
      }

      if ($bShowCamps){
         $strOut .= ', gc_strCampaign AS `Campaign` ';
      }

      return($strOut);
   }

   function strExportFields_Gifts(){
   /*---------------------------------------------------------------------
   /* The following joins are needed:
            FROM gifts
               INNER JOIN gifts_campaigns             ON gi_lCampID             = gc_lKeyID
               INNER JOIN gifts_accounts              ON gc_lAcctID             = ga_lKeyID
               INNER JOIN people_names   AS donor     ON donor.pe_lKeyID        = gi_lForeignID
               INNER JOIN admin_aco                   ON gi_lACOID              = aco_lKeyID

               LEFT  JOIN lists_generic AS payCat     ON payCat.lgen_lKeyID     = gi_lPaymentType
               LEFT  JOIN lists_generic AS giftCat    ON giftCat.lgen_lKeyID    = gi_lMajorGiftCat
               LEFT  JOIN lists_generic AS giftAttrib ON giftAttrib.lgen_lKeyID = gi_lAttributedTo
               LEFT  JOIN sponsor                     ON sp_lKeyID              = gi_lSponsorID
               LEFT  JOIN people_names   AS spon      ON spon.pe_lKeyID         = sp_lForeignID
               LEFT  JOIN lists_generic  AS gik       ON gi_lGIK_ID             = gik.lgen_lKeyID
               LEFT  JOIN admin_users AS ackUser      ON gi_lAckByID            = ackUser.us_lKeyID
   ---------------------------------------------------------------------*/
      global $gclsChapterVoc;
      
      $strDateFormat     = strMysqlDateFormat(false);

      return("
               gi_lKeyID                AS giftID,

               DATE_FORMAT(gi_dteDonation, $strDateFormat) AS `Date of Donation`,
               FORMAT(gi_curAmnt, 2)    AS `Amount`,

               IF(donor.pe_bBiz, donor.pe_lKeyID, null) AS businessID,
               IF(donor.pe_bBiz, null, donor.pe_lKeyID) AS peopleID,
               IF(donor.pe_bBiz, 'Yes', 'No')           AS `Business Donation`,
               IF(donor.pe_bBiz, donor.pe_strLName, null) AS `Donor Business Name`,
               IF(donor.pe_bBiz, null, donor.pe_strLName) AS `Donor Last Name`,
               IF(donor.pe_bBiz, null, donor.pe_strFName) AS `Donor First Name`,
               IF(donor.pe_bBiz, null, donor.pe_strMName) AS `Donor Middle Name`,
               donor.pe_strAddr1                    AS `Address 1`,
               donor.pe_strAddr2                    AS `Address 2`,
               donor.pe_strCity                     AS City,
               donor.pe_strState                    AS `$gclsChapterVoc->vocState`,
               donor.pe_strCountry                  AS Country,
               donor.pe_strZip                      AS `$gclsChapterVoc->vocZip`,
               donor.pe_strPhone                    AS Phone,
               donor.pe_strCell                     AS Cell,
               donor.pe_strEmail                    AS Email,

               ga_lKeyID                   AS accountID,
               ga_strAccount               AS Account,

               gi_lCampID                  AS campaignID,
               gc_strCampaign              AS Campaign,

               gi_lACOID                   AS accountingCountryID,
               aco_strName                 AS `Accounting Country`,

               gi_lDepositLogID            AS `Deposit ID`,

               IF(gi_bGIK, 'Yes', 'No')    AS `In-Kind`,
               gi_lGIK_ID                  AS inKindID,
               gik.lgen_strListItem        AS `In Kind Category`,

               gi_strCheckNum              AS `Check Number`,
               gi_lPaymentType             AS `paymentTypeID`,
               payCat.lgen_strListItem     AS `Payment Type`,
               gi_lMajorGiftCat            AS `majorGiftCategoryID`,
               giftCat.lgen_strListItem    AS `Major Gift Category`,
               giftAttrib.lgen_strListItem AS `Attributed To`,
               gi_strNotes                 AS `Notes`,

               IF(gi_bAck, 'Yes', 'No') AS `Acknowledged`,
               CONCAT(ackUser.us_strFirstName, ' ', ackUser.us_strLastName) AS `Acknowledged By`,
               IF(gi_bAck, DATE_FORMAT(gi_dteAck, $strDateFormat), NULL) AS `Date Acknowledged`,

               IF (gi_lSponsorID IS NULL, 'No', 'Yes') AS `Sponsorship Payment`,
               gi_lSponsorID     AS sponsorID,
               sp_lClientID      AS clientID,
               IF (gi_lSponsorID IS NULL, '',
               IF(sp_lForeignID=gi_lForeignID, 'No', 'Yes')) AS `3rd Party Payment`,

               IF(spon.pe_bBiz, spon.pe_lKeyID, null)   AS `Sponsor businessID`,
               IF(spon.pe_bBiz, null, spon.pe_lKeyID)   AS `Sponsor peopleID`,
               IF(spon.pe_bBiz, 'Yes', 'No')            AS `Business Payment`,
               IF(spon.pe_bBiz, spon.pe_strLName, null) AS `Sponsor Business Name`,
               IF(spon.pe_bBiz, null, spon.pe_strLName) AS `Sponsor Last Name`,
               IF(spon.pe_bBiz, null, spon.pe_strFName) AS `Sponsor First Name`,
               IF(spon.pe_bBiz, null, spon.pe_strMName) AS `Sponsor Middle Name`
            ");
   }

   function strExportFields_Client(){
   /*---------------------------------------------------------------------
          FROM client_records
             INNER JOIN client_location             ON cr_lLocationID         = cl_lKeyID
             INNER JOIN client_status_cats          ON cr_lStatusCatID        = csc_lKeyID
             INNER JOIN lists_client_vocab          ON cr_lVocID              = cv_lKeyID
             INNER JOIN client_status               ON csh_lClientID          = cr_lKeyID
             INNER JOIN lists_client_status_entries ON csh_lStatusID          = cst_lKeyID
             LEFT  JOIN lists_generic               ON cr_lAttributedTo       = lgen_lKeyID

   ---------------------------------------------------------------------*/
      global $gclsChapterVoc;
      
      $strDateFormat     = strMysqlDateFormat(false);
      $strDateTimeFormat = strMysqlDateFormat(true);

      return("
             cr_lKeyID   AS clientID,
             cr_strLName AS `Last Name`,  cr_strFName AS `First Name`,

             DATE_FORMAT(cr_dteEnrollment, $strDateFormat    )    AS `Client Enrollment Date`,
             DATE_FORMAT(cr_dteBirth,      $strDateFormat    )    AS `Client Birth Date`,

             cr_enumGender    AS Gender,
             cr_lMaxSponsors  AS `Max # Sponsors`,
             cr_strBio        AS Biography,
             cr_lVocID        AS `vocabulary ID`,

             cr_lStatusCatID  AS `Status Category ID`,
             csc_strCatName   AS `Status Category`,

               -- ------------------------
               -- current status
               -- ------------------------
             csh_lKeyID    AS `Client Status ID`,
             cst_strStatus                          AS `Client Status`,
             csh_strStatusTxt                       AS `Status Notes`,
             DATE_FORMAT(csh_dteStatusDate,      $strDateFormat    ) AS `Date of Status`,
--             IF(csh_bIncludeNotesInPacket, 'Yes', 'No') AS `Include Notes In Packet`,

--             cst_lClientStatusCatID                 AS `Client Status Category ID`,
--             csh_lStatusID                        AS `Status Entry ID`,
             IF(cst_bAllowSponsorship, 'Yes', 'No') AS `Allows Sponsorship`,
             IF(cst_bShowInDir,        'Yes', 'No') AS `Show In Directory`,
             IF(cst_bDefault,          'Yes', 'No') AS `Default Status`,
          --   curStatSCat.csc_strCatName             AS `Status Category Name`,
          --   defSCat.csc_strCatName               AS `Default Status Category Name`,

             cr_lLocationID    AS `location ID`,
             cl_strLocation    AS `Location`,
             cl_strCountry     AS `Location Country`,
             cl_strAddress1    AS `Location Address 1`,
             cl_strAddress2    AS `Location Address 2`,
             cl_strCity        AS `Location City`,
             cl_strState       AS `Location $gclsChapterVoc->vocState`,
             cl_strPostalCode  AS `Location $gclsChapterVoc->vocZip`,

             cr_strAddr1       AS `Address 1`,
             cr_strAddr2       AS `Address 2`,
             cr_strCity        AS City,
             cr_strState       AS `$gclsChapterVoc->vocState`,
             cr_strCountry     AS Country,
             cr_strZip         AS `$gclsChapterVoc->vocZip`,
             cr_strPhone       AS Phone,
             cr_strCell        AS Cell,
             cr_strEmail       AS Email,

             cr_lVocID         AS `Vocabulary ID`,
             cv_strVocTitle    AS `Vocabulary Title`,
             cv_strVocClientS  AS `Voc: Client (singular)`,
             cv_strVocClientP  AS `Voc: Client (plural)`,
             cv_strVocSponsorS AS `Voc: Sponsor (singular)`,
             cv_strVocSponsorP AS `Voc: Sponsor (plural)`,
             cv_strVocLocS     AS `Voc: Location (singular)`,
             cv_strVocLocP     AS `Voc: Location (plural)`,
             cv_strVocSubLocS  AS `Voc: Sub-location (singular)`,
             cv_strVocSubLocP  AS `Voc: Sub-location (plural)`,
             lgen_strListItem  AS `Attributed To`,

             DATE_FORMAT(cr_dteOrigin,     $strDateTimeFormat) AS `Record Creation Date`,
             DATE_FORMAT(cr_dteLastUpdate, $strDateTimeFormat) AS `Record Last Update`
             ");
   }

   function strMysqlDateFormat($bIncludeTime){
      global $gbDateFormatUS;
      if ($bIncludeTime){
         $strFormat = ($gbDateFormatUS ? " '%m/%d/%Y %T' " : " '%d/%m/%Y %T' ");
      }else {
         $strFormat = ($gbDateFormatUS ? " '%m/%d/%Y' "    : " '%d/%m/%Y' ");
      }
      return($strFormat);
   }

   function strMysqlTimeFormat(){
      return("' %l:%i %p' ");
   }




