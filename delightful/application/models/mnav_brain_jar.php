<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*---------------------------------------------------------------------
// Delightful Labor
// copyright (c) 2012-2015 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------*/

class mnav_brain_jar extends CI_Model{

   function __construct(){
      parent::__construct();
   }

   function navData(){
      global $gbAdmin, $gbVolLogin;
      if ($gbVolLogin){
         return(strTrimAllLines($this->navDataVolunteer()));
      }else {
         return(strTrimAllLines($this->navDataStandard()));
      }
   }

   private function navDataStandard(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbAdmin, $gbStandardUser, $gUserPerms, $gbDev;
      $navData = "\n".'<ul id="navDD" class="dropdown dropdown-horizontal">'."\n";

         // custom navigation
      if (@$_SESSION[CS_NAMESPACE.'nav']->lCnt > 0){
         foreach ($_SESSION[CS_NAMESPACE.'nav']->navFiles as $strNav){
            $this->load->model('custom_navigation/'.$strNav, $strNav);
//            $navData .= $strNav::strNavigation();
            $navData .= $this->$strNav->strNavigation();
         }
      }

         //----------------------------
         // Reports/Exports
         //----------------------------
      if (bAllowAccess('showReports')){
         $navData .=
            '<li class="dir">Reports
               '.$this->navMenu_Reports().'
             </li>'."\n";
      }

         //----------------------------
         // People
         //----------------------------
      if (bAllowAccess('showPeople')){
         $navData .=
            '<li class="dir">People
               '.$this->navMenu_People().'
             </li>'."\n";
         $navData .=
            '<li class="dir">Bus./Org.
               '.$this->navMenu_Biz().'
             </li>'."\n";
      }

            //----------------------------
            // Volunteers
            //----------------------------
      if (bAllowAccess('showPeople')){
         $navData .=
            '<li class="dir">Vol.
               '.$this->navMenu_Vols().'
             </li>'."\n";
      }

         //----------------------------
         // Financials
         //----------------------------
      if (bAllowAccess('showFinancials') || bAllowAccess('showGrants')){
         if ($gbDev){
            $navData .=
               '<li class="dir">Financials / Grants
                  '.$this->navMenu_Financials().'
                </li>'."\n";
         }else {
            $navData .=
               '<li class="dir">Financials
                  '.$this->navMenu_Financials().'
                </li>'."\n";
         }
      }

         //----------------------------
         // Clients
         //----------------------------
      if (bAllowAccess('showClients')){
         $navData .=
            '<li class="dir">Clients
               '.$this->navMenu_Clients().'
             </li>'."\n";
      }

         //----------------------------
         // Sponsors
         //----------------------------
      if (bAllowAccess('showSponsors')){
         $navData .=
            '<li class="dropdown-vertical-rtl dir">Sponsors
               '.$this->navMenu_Sponsors().'
             </li>'."\n";
      }

         //----------------------------
         // Admin
         //----------------------------
      if ($gbAdmin){
         $navData .=
            '<li class="dropdown-vertical-rtl dir">Admin
               '.$this->navMenu_Admin().'
             </li>'."\n";
      }

         //----------------------------
         // More
         //----------------------------
      $navData .=
            '<li>&nbsp;</li>
             <li class="dropdown-vertical-rtl dir">More...
               '.$this->navMenu_More().'
             </li>'."\n";

      $navData .= '</ul>'."\n";
      return($navData);
   }

   private function navMenu_Reports(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbAdmin, $gbDev, $glUserID;

      $strOut = '<ul>'."\n";

      $strOut .= '<li>'.anchor('main/menu/reports', 'Reports: Home', 'id="mb_rpts_home"').'</li>'."\n";

         //-----------------
         // custom
         //-----------------
      $strOut .=
        '<li class="dir">Custom Reports'."\n";
      $strOut .= '
                  <ul>
                     <li>'.anchor('creports/add_edit_crpt/add_edit/0',         'Add New',          'id="mb_rpts_cus_ae"').'</li>
                     <li>'.anchor('creports/custom_directory/view/'.$glUserID, 'Report Directory', 'id="mb_rpts_cus_dir"').'</li>
                  </ul>';
      $strOut .= '
           </li>';

         //------------------------
         // Reports => Predefined
         //------------------------
      $strOut .=
        '<li class="dir">Predefined
            <ul>'."\n";

         //-----------------
         // admin
         //-----------------
      if ($gbAdmin){
         $strOut .=
              '<li class="dir">Admin
                  <ul>
                     <li>'.anchor('reports/pre_admin/usageOpts',  'Usage Log', 'id="mb_rpts_ul"').'</li>
                     <li>'.anchor('reports/pre_admin/loginLog/0', 'User Login History', 'id="mb_rpts_ulh"').'</li>
                  </ul>
               </li>';
      }

         //-----------------
         // business
         //-----------------
      if (bAllowAccess('showReports') && bAllowAccess('showPeople')){
         $strOut .=
              '<li class="dir">Business
                  <ul>
                     <li>'.anchor('reports/pre_groups/showOpts/'.CENUM_CONTEXT_BIZ, 'Groups', 'id="mb_rpts_biz_grp"').'</li>
                  </ul>
               </li>';
      }

         //-----------------
         // clients
         //-----------------
      if (bAllowAccess('showClients')){
         $strOut .=
              '<li class="dir">Clients
                  <ul>
                     <li>'.anchor('reports/pre_groups/showOpts/'.CENUM_CONTEXT_CLIENT, 'Groups', 'id="mb_rpts_client_grp"').'</li>
                     <li>'.anchor('clients/client_search/searchOpts/true', 'Clients Available for Sponsorship', 'id="mb_rpts_client_avail"').'</li>
                     <li>'.anchor('reports/pre_clients/bDay',              'Birthday Reports', 'id="mb_rpts_client_bday"').'</li>
                     <li class="dir">Pre/Post Tests
                        <ul>
                           <li>'.anchor('reports/pre_client_pre_post/opts',       'Answer Breakdown', 'id="mb_rpts_pp_opts"')  .'</li>
                           <li>'.anchor('reports/pre_client_pre_post/dirViaTest', 'Directory Via Test', 'id="mb_rpts_pp_dir"').'</li>
                        </ul>
                     </li>
                     <li class="dir">Client Programs
                        <ul>
                           <li>'.anchor('cprograms/cprog_enrollees/opts',       'Enrollees', 'id="mb_rpts_cp_enrollees"')  .'</li>
                        </ul>
                     </li>
                     <li class="dir">Aggregate
                        <ul>
                           <li>'.anchor('reports/pre_clients/aggRptsLocAge', 'Location / Age', 'id="mb_rpts_cagg_age"').'</li>
                           <li>'.anchor('reports/pre_clients/aggRptsStatus/true',  'Status (active clients)', 'id="mb_rpts_cagg_stat"').'</li>
                           <li>'.anchor('reports/pre_clients/aggRptsStatus/false', 'Status (inactive clients)', 'id="mb_rpts_cagg_stati"').'</li>
                        </ul>
                  </ul>
               </li>';
      }

         //-----------------
         // donations
         //-----------------
      if (bAllowAccess('showReports') && bAllowAccess('showFinancials')){
         $strOut .=
              '<li class="dir">Donations
                  <ul>
                     <li>'.anchor('reports/pre_gifts/recentOpts',    'Recent donations',       'id="mb_rpts_d_recent"' ).'</li>
                     <li>'.anchor('reports/pre_gifts/yearEndOpts',   'Year-end Report',        'id="mb_rpts_d_ye"'     ).'</li>
                     <li>'.anchor('reports/pre_gifts/timeframeOpts', 'Donations by Timeframe', 'id="mb_rpts_d_tf"'     ).'</li>
                     <li>'.anchor('reports/pre_gifts/accountOpts',   'Donations by Account',   'id="mb_rpts_d_acct"'   ).'</li>
                     <li>'.anchor('reports/pre_gifts/campOpts',      'Donations by Campaign',  'id="mb_rpts_d_camp"'   ).'</li>

                     <li class="dir">Aggregate
                        <ul>
                           <li>'.anchor('reports/pre_gifts/agOpts',        'Donations',            'id="mb_rpts_d_agg"'  ).'</li>
                           <li>'.anchor('reports/pre_gifts/agSponOpts',    'Sponsorship Payments', 'id="mb_rpts_d_aggsp"').'</li>
                        </ul>
                     </li>

                     <li class="dir">Acknowledgments
                        <ul>
                           <li>'.anchor('reports/pre_gift_ack/showOpts/gifts', 'Gift Acknowledgments',       'id="mb_rpts_d_gack"').'</li>
                           <li>'.anchor('reports/pre_gift_ack/showOpts/hon',   'Honorarium Acknowledgments', 'id="mb_rpts_d_hon"').'</li>
                           <li>'.anchor('reports/pre_gift_ack/showOpts/mem',   'Memorial Acknowledgments',   'id="mb_rpts_d_mem"').'</li>
                        </ul>
                     </li>
                  </ul>
               </li>';
      }

         //-----------------
         // Images/Documents
         //-----------------
      if (bAllowAccess('showReports')){
         $strOut .=
              '<li class="dir">Images/Documents
                  <ul>
                     <li>'.anchor('reports/image_doc/id_overview/overview', 'Overview', 'id="mb_rpts_imgdoc"').'</li>
                  </ul>
               </li>';
      }

         //-----------------
         // people
         //-----------------
      if (bAllowAccess('showReports') && bAllowAccess('showPeople')){
         $strOut .=
              '<li class="dir">People
                  <ul>
                     <li>'.anchor('reports/pre_groups/showOpts/'.CENUM_CONTEXT_PEOPLE, 'Groups', 'id="mb_rpts_p_grp"').'</li>
                  </ul>
               </li>';
      }

         //-----------------
         // sponsorship
         //-----------------
      if (bAllowAccess('showSponsors')){
         $strOut .=
              '<li class="dir">Sponsors
                  <ul>
                     <li>'.anchor('reports/pre_groups/showOpts/'.CENUM_CONTEXT_SPONSORSHIP, 'Groups',      'id="mb_rpts_sp_grp"')     .'</li>
                     <li>'.anchor('reports/pre_spon_income/showOpts',      'Income',                       'id="mb_rpts_sp_inc"')                      .'</li>
                     <li>'.anchor('reports/pre_spon_past_due/showOpts',    'Payment Past Due',             'id="mb_rpts_sp_pd"')            .'</li>
                     <li>'.anchor('reports/pre_spon_via_prog/showOptsLoc', 'Sponsors Via Client Location', 'id="mb_rpts_sp_loc"').'</li>
                     <li>'.anchor('reports/pre_spon_via_prog/showOpts',    'Sponsors Via Program',         'id="mb_rpts_sp_prog"')        .'</li>
                     <li>'.anchor('reports/pre_spon_wo_clients/rpt',       'Sponsors Without Clients',     'id="mb_rpts_sp_noc"')    .'</li>
                  </ul>
               </li>';
      }

         //-----------------
         // Staff
         //-----------------
      if (bAllowAccess('showReports') && bAllowAccess('timeSheetAdmin')){
         $strOut .=
              '<li class="dir">Staff
                  <ul>
                     <li>'.anchor('reports/pre_timesheets/showOpts', 'Time Sheet Reports', 'id="mb_rpts_st_ts"').'</li>
                  </ul>
               </li>';
      }

         //-----------------
         // volunteers
         //-----------------
      if (bAllowAccess('showReports') && bAllowAccess('showPeople')){
         $strOut .=
              '<li class="dir">Volunteers
                  <ul>
                     <li>'.anchor('reports/pre_groups/showOpts/'.CENUM_CONTEXT_VOLUNTEER, 'Groups',                                   'id="mb_rpts_vol_grp"').'</li>
                     <li>'.anchor('reports/pre_vol_job_skills/showOpts', 'Job Skills',                               'id="mb_rpts_vol_js"').'</li>
                     <li>'.anchor('reports/pre_vol_hours/showOptsYear',  'Volunteer Hours by Year',                  'id="mb_rpts_vol_yrhrs"').'</li>
                     <li>'.anchor('reports/pre_vol_hours/showOptsTFSum', 'Volunteer Hours (Summary / By Time Frame)','id="mb_rpts_vol_sum"').'</li>
                     <li>'.anchor('reports/pre_vol_hours/showOpts',      'Volunteer Hours (Scheduled)',              'id="mb_rpts_vol_sch"').'</li>
                     <li>'.anchor('reports/pre_vol_hours/showOptsPVA',   'Volunteer Hours - Scheduled vs. Actual',   'id="mb_rpts_vol_sva"').'</li>
                     <li>'.anchor('reports/pre_vol_schedule/past',       'Past Events',                              'id="mb_rpts_vol_pa"').'</li>
                     <li>'.anchor('reports/pre_vol_schedule/current',    'Current and Future Events',                'id="mb_rpts_vol_cfe"').'</li>
                     <li>'.anchor('reports/pre_vol_jobcodes/showOpts',   'Job Codes',                                'id="mb_rpts_vol_jcode"').'</li>
                  </ul>
               </li>';
      }

         //-----------------
         // miscellaneous
         //-----------------
      if (bAllowAccess('showReports')){
         $strOut .=
              '<li class="dir">Miscellaneous
                  <ul>
                     <li>'.anchor('reports/pre_attrib/attrib',         'Attributed To',  'id="mb_rpts_misc_att"').'</li>
                     <li>'.anchor('reports/pre_log_search/searchOpts', 'Log Search',     'id="mb_rpts_misc_ls"').'</li>
                     <li>'.anchor('reports/pre_data_entry/daOpts',     'Data Entry Log', 'id="mb_rpts_misc_del"').'</li>
                  </ul>
               </li>';
      }
      $strOut .= '</ul>';

         //-----------------
         // exports
         //-----------------
      if (bAllowAccess('allowExports')){
         $strOut .=
              '<li>'.anchor('reports/exports/showTableOpts', 'Exports', 'id="mb_rpts_exp"').'</li>'."\n";
      }

      $strOut .= '</ul>'."\n";
      return($strOut);
   }

   private function navMenu_People(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '<ul>'."\n";

         //------------------------
         // People => Home
         //------------------------
      if (bAllowAccess('dataEntryPeopleBizVol')){
         $strOut .= '<li>'.anchor('main/menu/people', 'People: Home', 'id="mb_p_home"').'</li>';
      }

         //------------------------
         // People => Add New
         //------------------------
      if (bAllowAccess('dataEntryPeopleBizVol')){
         $strOut .= '<li>'.anchor('people/people_add_new/selCon', 'Add New', 'id="mb_p_an"').'</li>';
      }

         //------------------------
         // People => Directories
         //------------------------
      if (bAllowAccess('viewPeopleBizVol')){
         $strOut .=
              '<li class="dir">Directories
                  <ul>
                     <li>'.anchor('people/people_dir/view/A', 'Directory by Last Name',        'id="mb_p_dir_ln"') .'</li>
                     <li>'.anchor('people/people_dir/relView/A', 'Relationship Directory',     'id="mb_p_dir_rel"').'</li>
                     <li>'.anchor('people/people_household_dir/view/A', 'Household Directory', 'id="mb_p_dir_hh"') .'</li>
                  </ul>
               </li>';
      }

         //-----------------------------------
         // Utilities:
         //   * consolodate duplicates
         //   * search
         //-----------------------------------
      $strOut .= '
            <li class="dir">Utilities
               <ul>';
      if (bAllowAccess('editPeopleBizVol')){
         $strOut .= '<li>'.anchor('util/dup_records/opts/'.CENUM_CONTEXT_PEOPLE, 'Consolidate Duplicates', 'id="mb_p_condup"').'</li>'."\n";
      }
      if (bAllowAccess('viewPeopleBizVol')){
         $strOut .= '<li>'.anchor('people/people_search/searchOpts', 'Search', 'id="mb_p_search"').'</li>';
      }
      $strOut .= '
               </ul>
            </li>';

      $strOut .= '</ul>'."\n";
      return($strOut);
   }

   private function navMenu_Biz(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '<ul>'."\n";

         //------------------------
         // Biz => Home
         //------------------------
      if (bAllowAccess('dataEntryPeopleBizVol')){
         $strOut .= '<li>'.anchor('main/menu/biz', 'Business/Org: Home', 'id="mb_b_home"').'</li>';
      }

         //-------------------
         // add new
         //-------------------
      $strOut .=
           '<li>'.anchor('biz/biz_add_edit/addEditBiz/0', 'Add New', 'id="mb_b_ae"').'</li>';

         //-------------------
         // directories
         //-------------------
      $strOut .=
           '<li class="dir">Directories
               <ul>
                  <li>'.anchor('biz/biz_directory/view/A',         'Business Directory',   'id="mb_b_dir"')  .'</li>
                  <li>'.anchor('biz/biz_directory/viewCName/A',    'Contacts by Name',     'id="mb_b_dircn"').'</li>
                  <li>'.anchor('biz/biz_directory/viewCBizName/A', 'Contacts by Business', 'id="mb_b_dircb"').'</li>
               </ul>
            </li>';

         //-----------------------------------
         // Utilities:
         //   * consolodate duplicates
         //   * search
         //-----------------------------------
      $strOut .= '
            <li class="dir">Utilities
               <ul>';

      if (bAllowAccess('editPeopleBizVol')){
         $strOut .= '<li>'.anchor('util/dup_records/opts/'.CENUM_CONTEXT_BIZ, 'Consolidate Duplicates', 'id="mb_b_cdup"').'</li>'."\n";
      }
      if (bAllowAccess('viewPeopleBizVol')){
         $strOut .= '<li>'.anchor('biz/biz_search/searchOpts', 'Search', 'id="mb_b_search"').'</li>';
      }
      $strOut .= '
               </ul>
            </li>';

      $strOut .= '</ul>'."\n";
      return($strOut);
   }

   private function navMenu_Vols(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '<ul>'."\n";
         //------------------------
         // Vols => Home
         //------------------------
      if (bAllowAccess('dataEntryPeopleBizVol')){
         $strOut .= '<li>'.anchor('main/menu/vols', 'Volunteers: Home', 'id="mb_v_home"').'</li>';
      }

         //--------------------------------
         //   Volunteers => Add New
         //--------------------------------
      $strOut .= '<li>'.anchor('volunteers/vol_add_edit/addEditS1', 'Add New', 'id="mb_v_new"').'</li>'."\n";

         //--------------------------------
         //   Volunteers => Directory
         //--------------------------------
      $strOut .= '<li>'.anchor('volunteers/vol_directory/view/A', 'Directory', 'id="mb_v_dir"').'</li>'."\n";

         //------------------------
         // Volunteers => Events
         //------------------------
      $strOut .=
           '<li class="dir">Events
               <ul>'."\n";

      if (bAllowAccess('editPeopleBizVol')){
         $strOut .=
              '<li>'.anchor('volunteers/events_add_edit/addEditEvent/0', 'Add Event', 'id="mb_v_adde"').'</li>'."\n";
      }
      $strOut .=
              '<li>'.anchor('volunteers/events_cal/viewEventsCalendar/current', 'Event Calendar',        'id="mb_v_ecal"').'</li>
               <li>'.anchor('volunteers/events_schedule/viewEventsList',        'Event Schedule (list)', 'id="mb_v_elist"').'</li>
            </ul>
         </li>';

         //------------------------------
         // Volunteers => Registration
         //------------------------------
      $strOut .=
           '<li class="dir">Registration
               <ul>
                  <li>'.anchor('volunteers/registration/view', 'Registration Forms', 'id="mb_v_reg"').'</li>
               </ul>
            </li>';

         //--------------------------------
         // Volunteers => Search
         //--------------------------------
      $strOut .= '<li>'.anchor('volunteers/vol_search/searchOpts', 'Search', 'id="mb_v_search"').'</li>';

      $strOut .= '</ul>'."\n";
      return($strOut);
   }

   private function navMenu_Financials(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $gbDev;

      $strOut = '<ul>'."\n";
         //------------------------
         // Financials => Home
         //------------------------
      if ($gbDev){
         $strOut .= '<li>'.anchor('main/menu/financials', 'Financials / Grants: Home', 'id="mb_f_home"').'</li>';
      }else {
         $strOut .= '<li>'.anchor('main/menu/financials', 'Financials: Home', 'id="mb_f_home1"').'</li>';
      }

      if (bAllowAccess('showFinancials')){
         $strOut .=
             '<li class="dir">Financials
                 <ul>
                    <li>'.anchor('donations/add_edit/new_gift', 'Add new donation', 'id="mb_f_addg"').'</li>
                    <li class="dir">Deposits
                        <ul>
                           <li>'.anchor('financials/deposits_add_edit/addDeposit', 'Add Deposit', 'id="mb_f_addd"').'</li>
                           <li>'.anchor('financials/deposit_log/view',             'Deposit Log', 'id="mb_f_dlog"').'</li>
                        </ul>
                     </li>
                  </ul>
               </li>';
      }

if ($gbDev){
      if (bAllowAccess('showGrants')){
         $strOut .=
             '<li class="dir">Grants
                 <ul>
                    <li>'.anchor('grants/provider_directory/viewProDirectory/true', 'Provider Directory', 'id="mb_fg_pdir"').'</li>
                    <li>'.anchor('grants/grants_calendar/viewCal',                  'Calendar',           'id="mb_fg_cal"') .'</li>
                 </ul>
              </li>';
      }
}

      $strOut .= '</ul>'."\n";
      return($strOut);
   }

   private function navMenu_Clients(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '<ul>'."\n";
         //------------------------
         // Clients => Home
         //------------------------
      $strOut .= '<li>'.anchor('main/menu/client', 'Clients: Home', 'id="mb_cl_home"').'</li>';

         //---------------------------------------
         // Clients => Add New Client
         //---------------------------------------
      $strOut .=
           '<li>'.anchor('clients/client_rec_add_edit/addNewS1', 'Add New Client', 'id="mb_cl_new"').'</li>
            <li class="dir">Directories
               <ul>
                  <li>'.anchor('clients/client_dir/name/N/A', 'By Client Last Name',   'id="mb_cl_dirln"')  .'</li>
                  <li>'.anchor('clients/client_dir/view/0',   'By Location',            'id="mb_cl_dirloc"').'</li>
                  <li>'.anchor('clients/client_dir/sProg/0',  'By Sponsorship Program', 'id="mb_cl_dirsp"') .'</li>
               </ul>
            </li>
            <li>'.anchor('cprograms/cprog_dir/cprogList',    'Client Programs', 'id="mb_cl_sp"').'</li>
            <li>'.anchor('cpre_post_tests/pptests/overview', 'Pre/Post Tests',  'id="mb_cl_prepost"').'</li>
            <li class="dir">Utilities
               <ul>
                  <li>'.anchor('util/dup_records/opts/'.CENUM_CONTEXT_CLIENT, 'Consolidate Duplicates', 'id="mb_cl_dup"')   .'</li>
                  <li>'.anchor('clients/client_search/searchOpts', 'Search',                            'id="mb_cl_search"').'</li>
                  <li>'.anchor('clients/client_verify/status',     'Verify Status',                     'id="mb_cl_vstat"') .'</li>
               </ul>
            </li>';
      $strOut .= '</ul>'."\n";
      return($strOut);
   }

   private function navMenu_Sponsors(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '<ul>'."\n";

      $strOut .= '<li>'.anchor('main/menu/sponsorship', 'Sponsors: Home').'</li>';

      if (bAllowAccess('showSponsorFinancials')){
         $strOut .=
           '<li class="dir">Accounting Features
               <ul>
                  <li>'.anchor('sponsors/auto_charge/applyChargesOpts',   'Apply Charges',  'id="mb_sponApplyCharges"').'</li>
                  <li>'.anchor('sponsors/batch_payments/batchSelectOpts', 'Batch Payments', 'id="mb_sponBatchPay"').'</li>
               </ul>
            </li>';
      }

         //---------------------------------------
         // Sponsorship => Directories
         //---------------------------------------
      $strOut .= '<li>'.anchor('sponsors/spon_directory/view/false/-1/A/0/50', 'Directory',  'id="mb_sponDir"').'</li>';

         //---------------------------------------
         // Sponsorship => Searches
         //---------------------------------------
      $strOut .=
           '<li class="dir">Utilities
               <ul>
                  <li>'.anchor('sponsors/spon_search/opts',   'Search').'</li>
               </ul>
            </li>';


/*
//
//         //---------------------------------------
//         // Sponsorship => Add New Sponsorship
//         //---------------------------------------
//      $strLinks .=
//          '<a class="menuItem"
//              style="color: #cccccc; font-style:italic;"
//              href="../main/mainOpts.php'
//                   .'?type=sponsorships'
//                   .'&st=sponsor'
//                   .'&sst=addNew'
//               .'">Add New Sponsorship</a>';
//
//         //---------------------------------------
//         // Sponsorship => Communications
//         //---------------------------------------
//      $strLinks .=
//          '<a class="menuItem" href=""
//              style="color: #cccccc; font-style:italic;"
//              onclick="return false;"
//              onmouseover="menuItemMouseover(event, \'sponCom\');" >
//              <span class="menuItemText">Communications</span>
//              <span class="menuItemArrow">&#9654;</span></a>';
//
//
//         //--------------------------------------------------------------------
//         // Sponsorship => Audits => Report: Sponsors with / without audits
//         //--------------------------------------------------------------------
//      $strLinks .=
//          '<div id="sponCom" class="menu" onmouseover="menuMouseover(event)">';
//      $strLinks .=
//          '<a class="menuItem"
//              style="color: #cccccc; font-style:italic;"
//              href="../main/mainOpts.php'
//                     .'?type=sponsorships'
//                     .'&st=comm'
//                     .'&sst=mainLog'
//                .'">Log a Communication</a>';
//
//         //--------------------------------------------------------------------
//         // Sponsorship => Communications => Communication Inventory
//         //--------------------------------------------------------------------
//      $strLinks .=
//          '<a class="menuItem"
//              style="color: #cccccc; font-style:italic;"
//              href="../main/mainOpts.php'
//                     .'?type=sponsorships'
//                     .'&st=comm'
//                     .'&sst=inventory'
//                     .'&ssst=manage'
//                     .'&sssst=main'
//                .'">Communication Inventory</a>';
//      $strLinks .=
//          '</div>';
//
//
//         //----------------------------------
//         // Sub-Menu, sponsorship utilities
//         //----------------------------------
//      $strLinks .=
//          '<div id="sponsorUtil" class="menu">'
//         .'<a class="menuItem"
//              style="color: #cccccc; font-style:italic;"
//              href="../main/mainOpts.php'
//                        .'?type=sponsorships'
//                        .'&st=sponsor'
//                        .'&sst=utilS'
//                        .'&ssst=xferClient'
//                        .'&sssst=step1">'
//             .'TBD 1</a>'
//
//         .'<a class="menuItem"
//              style="color: #cccccc; font-style:italic;"
//              href="../main/mainOpts.php'
//                        .'?type=sponsorships'
//                        .'&st=sponsor'
//                        .'&sst=utilS'
//                        .'&ssst=refreshClientDefImg01'
//                        .'&sssst=step1">'
//             .'TBD 2</a>'
//
//         .'</div>';
//      return($strLinks);
*/
      $strOut .= '</ul>'."\n";
      return($strOut);
  }

   private function navMenu_Admin(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDev;

      $strOut = '<ul>'."\n";

         // Lists
      $strOut .= '
            <li>'.anchor('main/menu/admin',                'Admin: Home', 'id="mb_ad_home"') .'</li>
            <li>'.anchor('admin/alists/showLists',         'Lists',       'id="mb_ad_lists"').'</li>';

         // Delightful Labor Accounts
      $strOut .= '
            <li class="dir">Delightful Labor Accounts
               <ul>
                  <li>'.anchor('admin/accts/userAcctDir/A', 'Accounts Directory', 'id="mb_ad_adir"').'</li>
                  <li>'.anchor('admin/accts/userAccess',    'Users Via Access',   'id="mb_ad_ua"')
                .'</li>
               </ul>
            </li>';

         // Your Organization
//                  <li>'.anchor('admin/timesheets/ts_projects/viewTSProjects',    'Staff Time Sheet Projects').'</li>
      $strOut .= '
            <li class="dir">Your Organization
               <ul>
                  <li>'.anchor('admin/org/orgView',                              'Organization Record',      'id="mb_ad_org"')   .'</li>
                  <li>'.anchor('admin/timesheets/view_tst_record/viewTSTList', 'Staff Time Sheet Templates', 'id="mb_ad_tslist"').'</li>
               </ul>';

         // Personalization
      $strOut .= '
            <li class="dir">Personalization
               <ul>
                  <li>'.anchor('cprograms/cprograms/overview',       'Client Programs', 'id="mb_ad_cp"')  .'</li>
                  <li>'.anchor('admin/personalization/overview',     'Tables',          'id="mb_ad_tabs"').'</li>
               </ul>
            </li>';

         // Custom Forms
      $strOut .= '
            <li class="dir">Custom Forms
               <ul>
                  <li>'.anchor('custom_forms/custom_form_add_edit/view/'.CENUM_CONTEXT_CLIENT,  'Client Custom Forms', 'id="mb_ad_cforms"').'</li>
               </ul>
            </li>';

         // Database Utilities
      $strOut .= '
            <li class="dir">Database Utilities
               <ul>
                  <li>'.anchor('admin/db_zutil/opt',    'Optimize your database', 'id="mb_ad_dbopt"') .'</li>
                  <li>'.anchor('admin/db_zutil/backup', 'Backup your database',   'id="mb_ad_dbback"').'</li>';

      if ($gbDev){
         $strOut .= '
                  <li>'.anchor('admin/db_zutil/utables', 'DEV: Personalized Tables', 'id="mb_ad_dev01"')  .'</li>';
/*
         $strOut .= '
                  <li>'.anchor('admin/dev/schema_examples/test01', 'DEV: mschema - single table load')  .'</li>
                  <li>'.anchor('admin/dev/schema_examples/test02', 'DEV: mschema - single tables via parent type')  .'</li>
                  <li>'.anchor('admin/dev/schema_examples/test03', 'DEV: mschema - single table via name')  .'</li>
                  <li>'.anchor('admin/dev/schema_examples/test04', 'DEV: mschema - field IDX')  .'</li>
                  <li>'.anchor('admin/dev/schema_examples/test05', 'DEV: mschema - table field scalars')  .'</li>
                  <li>'.anchor('admin/dev/schema_examples/test06', 'DEV: mschema - field IDX via Field ID')  .'</li>
                  <li>'.anchor('admin/dev/schema_examples/test07', 'DEV: mschema - field essentials')  .'</li>
                  <li>'.anchor('admin/dev/schema_examples/test08', 'DEV: mschema - ddl')  .'</li>

                  ';
*/
      }

/*
      if ($gbDev){
         $strOut .= '
                  <li>'.anchor('admin/dev/patch_reroute/run', 'DEV: Patch ReRoute 6/1/14')  .'</li>';
      }
      if ($gbDev){
         $strOut .= '
                  <li>'.anchor('admin/dev/patch_cform_log/createCFormLog', 'DEV: Build Custom Form Log')  .'</li>';
      }
      if ($gbDev){
         $strOut .= '
                  <li>'.anchor('admin/dev/upgrade_dev_1007/upgrade', 'DEV: Upgrade UF Tables for 1.007')  .'</li>';
      }
*/



      $strOut .= '
               </ul>
            </li>';

         // Import
      $strOut .= '
            <li class="dir">Import
               <ul>
                  <li>'.anchor('admin/import/'.CENUM_CONTEXT_PEOPLE,     'People Records',                'id="mb_ad_in_p"')  .'</li>
                  <li>'.anchor('admin/import/'.CENUM_CONTEXT_BIZ,        'Business/Organization Records', 'id="mb_ad_in_b"')  .'</li>
                  <li>'.anchor('admin/import/'.CENUM_CONTEXT_GIFT,       'Gift Records',                  'id="mb_ad_in_g"')  .'</li>
                  <li>'.anchor('admin/import/'.CENUM_CONTEXT_SPONSORPAY, 'Sponsor Payments',              'id="mb_ad_in_sp"') .'</li>
                  <li>'.anchor('admin/import/ptables',                   'Personalized Tables',           'id="mb_ad_in_pt"').'</li>
                  <li>'.anchor('admin/import/review',                    'Review/Analyze Import File',    'id="mb_ad_in_rev"').'</li>
                  <li>'.anchor('admin/import/log',                       'Import Log',                    'id="mb_ad_in_log"').'</li>
               </ul>
            </li>';

      $strOut .= '</ul>'."\n";
      return($strOut);
   }

   private function navMenu_More(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $gbDev, $gbAdmin, $gbStandardUser, $gUserPerms;

      $strOut = '<ul>'."\n";
      $strOut .= '<li>'.anchor('main/menu/more', 'More: Home').'</li>';

//            <li>'.anchor('reminders/reminder_record/viewViaUser/'.$glUserID, 'Your Reminders').'</li>
      $strOut .= '
            <li class="dir">Your Account
               <ul>
                  <li>'.anchor('more/user_acct/view/'.$glUserID,  'Your Profile and Preferences').'</li>
                  <li>'.anchor('more/user_acct/pw',               'Change Password')             .'</li>
               </ul>
            </li>';
      $strOut .= '
            <li>'.anchor('staff/timesheets/ts_log/viewLog', 'Time Sheets').'</li>'."\n";

      if (bAllowAccess('timeSheetAdmin')){
         $strOut .= '
                  <li>'.anchor('staff/timesheets/ts_admin/viewUsers', 'Time Sheet Administration').'</li>'."\n";
      }

      if (bAllowAccess('inventoryMgr')){
         $strOut .= '
            <li class="dir">Inventory Management
               <ul>
                  <li>'.anchor('staff/inventory/icat/viewICats', 'Categories').'</li>
                  <li class="dir">Items
                     <ul>
                        <li>'.anchor('staff/inventory/icat/viewICatsRemOnly',    'Removed Items').'</li>
                        <li>'.anchor('staff/inventory/icat/viewICatsLostOnly',   'Lost Items').'</li>
                        <li>'.anchor('staff/inventory/icat/itemsCheckedOutList', 'Checked-Out Items').'</li>
                        <li>'.anchor('staff/inventory/icat/itemsAllList',        'All Items').'</li>
                     </ul>
               </ul>
            </li>';
//                  <li>'.anchor('staff/inventory/reports', 'Reports').'</li>
//                        <li>'.anchor('staff/inventory/itemsSearchOpts',     'Item Search').'</li>
      }

      if (bAllowAccess('showAuctions')){
         $strOut .= '
            <li class="dir">Silent Auctions
               <ul>
                  <li>'.anchor('auctions/auctions/auctionEvents', 'Silent Auction Events').'</li>
                  <li>'.anchor('auctions/bid_templates/main',     'Bid Sheets')           .'</li>
               </ul>
            </li>';
      }

      $strOut .=
          '<li>'.anchor('more/about/aboutDL', 'About...').'</li>
           <li>'.anchor('login/signout',      'Sign Out').'</li>';

      $strOut .= '</ul>'."\n";
      return($strOut);
   }

   private function navDataVolunteer(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbAdmin, $gbVolLogin, $gVolPerms, $gstrSafeName;

      $navData = '&nbsp;&nbsp;<span style="vertical-align: center;">Volunteer Account: <b>'.$gstrSafeName.'</b></span>';
      $navData .= "\n".'<ul id="navDD" class="dropdown dropdown-horizontal">'."\n";

      if (bAllowAccess('volEditContact')){
         $navData .= '<li>'.anchor('people/people_record/view', 'Contact Info', 'id="mb_vv_ci"').'</li>';
      }

      if (bAllowAccess('volResetPassword')){
         $navData .= '<li>'.anchor('more/user_acct/pw', 'Reset Password', 'id="mb_vv_rp"').'</li>';
      }

      if (bAllowAccess('volViewGiftHistory')){
         $navData .= '<li>'.anchor('vol_reg/gift_history/view', 'Donation History', 'id="mb_vv_dh"').'</li>';
      }

      if (bAllowAccess('volJobSkills')){
         $navData .= '<li>'.anchor('volunteers/vol_add_edit/editVolSkills', 'Skills', 'id="mb_vv_js"').'</li>';
      }

         //----------------------------
         // Scheduling Fetures
         //----------------------------
      $bAnyVolPerms = bAllowAccess('volFeatures');
      if ($bAnyVolPerms){
         $navData .=
            '<li class="dir">Scheduling Features
                <ul>';
         if (bAllowAccess('volViewHours')){
            $navData .= '<li>'.anchor('vol_reg/vol_hours/view',
                           'View '.(bAllowAccess('volEditHours') ? ' / edit / update' : '').' hours', 'id="mb_vv_hrs"').'</li>';
         }
         if (bAllowAccess('volShiftSignup')){
            $navData .= '<li>'.anchor('vol_reg/vol_hours/shifts',
                         'View / register for upcoming volunteer opportunities', 'id="mb_vv_shift"').'</li>';
         }
         $navData .= '<li>'.anchor('vol_reg/vol_hours/cal', 'Your upcoming volunteer calendar', 'id="mb_vv_cal"').'</li>';

         $navData .= '
                </ul>
             </li>';
      }

      $navData .= '<li>&nbsp;</li><li>'.anchor('login/signout', 'Sign Out', 'id="mb_vv_so"').'</li>';

      $navData .= '</ul>';
      return($navData);
   }



}