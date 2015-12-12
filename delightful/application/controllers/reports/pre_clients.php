<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_clients extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function aggRptsStatus($bActive){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

      $displayData = array();
      $displayData['bActive'] = $bActive = $bActive=='true';
      $displayData['js']      = '';
      $displayData['activeLabel'] = $strActiveLabel = ($bActive ? 'active' : 'inactive');

      $lNumCharts = 0;
      $charts = array();
      
         /*----------------------------
          the stripe-a-tizer
         ----------------------------*/
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;
      

         /*-------------------------------------------------
            models associated with the client agg report
           -------------------------------------------------*/
      $this->load->model  ('clients/mclients',                  'clsClients');
      $this->load->model  ('clients/mclient_status',            'clsClientStat');
      $this->load->model  ('reports/mclient_reports',           'cCRpt');
      $this->load->helper ('clients/client');
      $this->load->helper ('dl_util/web_layout');
      $this->load->model  ('util/mgoogle_charts', 'cGoogleRpts');

      $this->cGoogleRpts->initPieChart();
      $this->cGoogleRpts->openPieChart();

          /*-------------------------------------------------
            pie chart - Clients per status category
           -------------------------------------------------*/
      $optionVars = array(
                 'chartArea' => '{left:0,top:0,width:500,height:250}',
                 'legend'    => '{position: \'right\',fontSize: 11, alignment:\'center\'}',
                 'is3D'      => 'true');


      $charts[$lNumCharts] = new stdClass;
      $chart = &$charts[$lNumCharts];
      $chart->sectionLabel = ($bActive ? 'Active' : 'Inactive').' Clients by Status Category';
      $chart->pieChart = new stdClass;
      $chart->pieChart->label1 = 'Category';
      $chart->pieChart->label2 = '# Clients';
      $chart->bError = false;
      $jidx = 0;
      $chart->pieChart->dataTable = array();

      $this->clsClientStat->loadClientStatCats(true, false, null);
      $lTotClients = 0;
      $chart->bShowDetails = true;
      $chart->enumDetails  = 'statCat';
      $chart->details = array();
      foreach ($this->clsClientStat->statCats as $cat){
         $chart->details[$jidx] = new stdClass;
         $detail = &$chart->details[$jidx];
         
         $lStatCatID = $cat->lKeyID;
         $cat->lNumClients = $this->clsClientStat->lNumClientsViaClientStatCat($lStatCatID, true, $bActive);
         $lTotClients += $cat->lNumClients;
         
         $detail->lStatusCatID       = $lStatCatID;
         $detail->lNumClients        = $cat->lNumClients;
         $detail->strCatName         = $cat->strCatName;

         $chart->pieChart->dataTable[$jidx]['label']  = $cat->strCatName;
         $chart->pieChart->dataTable[$jidx]['lValue'] = $cat->lNumClients;
         ++$jidx;
      }
      $this->cGoogleRpts->addPieChart('_'.$lNumCharts, 'Status Category', '# Clients',
                           $chart->pieChart->dataTable, $optionVars, 'chart_'.$lNumCharts.'_div');

      $chart->lNumRecs     = $lTotClients;
      if ($chart->lNumRecs == 0) $chart->nobodyHomeMsg = 'There are no '.$strActiveLabel.' clients in your database';
      ++$lNumCharts;


          /*-------------------------------------------------
            pie chart - Clients per status
           -------------------------------------------------*/
      $lTotClients = 0;
      foreach ($this->clsClientStat->statCats as $cat){
         $lStatCatID = $cat->lKeyID;
         $charts[$lNumCharts] = new stdClass;
         $chart = &$charts[$lNumCharts];
         $chart->bError = false;
         $chart->sectionLabel = '<b>'.htmlspecialchars($cat->strCatName).':</b> '
                     .($bActive ? 'Active' : 'Inactive').' Clients by Status';

         $lTotClients = 0;
         if ($cat->lNumClients == 0) {
            $chart->lNumRecs = 0;
            $chart->nobodyHomeMsg = 'There are no '.$strActiveLabel.' clients in the <b>'
                  .htmlspecialchars($cat->strCatName).'</b> status category';
         }else {
            $chart->lNumRecs = $cat->lNumClients;

            $chart->pieChart = new stdClass;
            $chart->pieChart->label1 = 'Status';
            $chart->pieChart->label2 = '# Clients';
            $chart->bShowDetails = true;

            $jidx = 0;
            $chart->pieChart->dataTable = array();

            $this->clsClientStat->loadClientStatCatsEntries(
                                  true,  $lStatCatID,
                                  false, null,
                                  true,  false);

               // count the entries for each status
            $chart->details = array();
            $chart->enumDetails  = 'status';            
            foreach ($this->clsClientStat->catEntries as $catEntry){
               $chart->details[$jidx] = new stdClass;
               $detail = &$chart->details[$jidx];
               $detail->lStatusID          = $lStatusID = $catEntry->lKeyID;
               $detail->lNumClients        = $this->clsClientStat->lNumClientsViaClientStatID($lStatusID, true, $bActive);
               $detail->bAllowSponsorship  = $catEntry->bAllowSponsorship;
               $detail->bShowInDir         = $catEntry->bShowInDir;
               $detail->bDefault           = $catEntry->bDefault;
               $detail->status             = $catEntry->strStatusEntry;
               
               $chart->pieChart->dataTable[$jidx]['label']  = $catEntry->strStatusEntry;
               $chart->pieChart->dataTable[$jidx]['lValue'] = $detail->lNumClients;
               ++$jidx;
            }

            $this->cGoogleRpts->addPieChart('_'.$lNumCharts, 'Status', '# Clients',
                           $chart->pieChart->dataTable, $optionVars, 'chart_'.$lNumCharts.'_div');
         }
         ++$lNumCharts;
      }

         // close google charts
      $this->cGoogleRpts->closePieChart();
      $displayData['js'] .= $this->cGoogleRpts->strHeader;
      $displayData['lNumCharts']  = $lNumCharts;
      $displayData['charts']      = &$charts;
      $displayData['lTotClients'] = $lTotClients;

         /*--------------------------
            breadcrumbs
         --------------------------*/
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Client Aggregate Report / Status';

      $displayData['title']          = CS_PROGNAME.' | Clients';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_client_aggstat_rpt_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function aggRptsLocAge(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $displayData['js'] = '';
      $lNumCharts = 0;
      $charts = array();

         /*-------------------------------------------------
            models associated with the client agg report
           -------------------------------------------------*/
      $this->load->model  ('clients/mclients',                  'clsClients');
      $this->load->model  ('clients/mclient_locations',         'clsLocation');
      $this->load->model  ('clients/mclient_status',            'clsClientStat');
      $this->load->model  ('reports/mclient_reports',           'cCRpt');
      $this->load->helper ('clients/client');
      $this->load->helper ('dl_util/time_date');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->helper ('dl_util/web_layout');
      $this->load->model  ('util/mgoogle_charts', 'cGoogleRpts');

      $this->cGoogleRpts->initPieChart();
      $this->cGoogleRpts->openPieChart();

         /*-------------------------------------------------
            pie chart #1 & 2 - Clients by Location
           -------------------------------------------------*/
      $bActive = true;
      $optionVars = array(
                 'chartArea' => '{left:0,top:0,width:500,height:250}',
                 'legend'    => '{position: \'right\',fontSize: 11, alignment:\'center\'}',
                 'is3D'      => 'true');
      for ($idx=0; $idx<=1; ++$idx){
         $charts[$lNumCharts] = new stdClass;
         $chart = &$charts[$lNumCharts];
         $chart->sectionLabel = 'Clients by Location ('.($bActive ? 'active' : 'active + inactive').')';
         $this->cCRpt->aggStatsViaLoc($bActive);
         $chart->bError = $bError = (($this->cCRpt->lNumLocs == 0) || ($this->cCRpt->lNumClients == 0));
         if ($bError){
            if ($this->cCRpt->lNumLocs == 0){
               $chart->strError = 'No client locations defined in your database!';
            }elseif ($this->cCRpt->lNumClients == 0){
               $chart->strError = 'No clients at any location!';
            }
         }else {
            $chart->pieChart = new stdClass;
            $chart->pieChart->label1 = 'Location/Country';
            $chart->pieChart->label2 = '# Clients';
            $jidx = 0;
            $chart->pieChart->dataTable = array();
            foreach ($this->cCRpt->locAggStats as $loc){
               $chart->pieChart->dataTable[$jidx]['label']  = $loc->strLocation.'/'.$loc->strCountry;
               $chart->pieChart->dataTable[$jidx]['lValue'] = $loc->lNumClients;
               ++$jidx;
            }

            $this->cGoogleRpts->addPieChart('_'.$lNumCharts, 'Location', '# Clients',
                                   $chart->pieChart->dataTable, $optionVars, 'chart_'.$lNumCharts.'_div');
         }
         $bActive = !$bActive;
         ++$lNumCharts;
      }

         /*-------------------------------------------------
            pie chart #3 - Client age
           -------------------------------------------------*/
      $this->cCRpt->aggStatsViaAge($ageRanges);
      $charts[2] = new stdClass;
      $chart = &$charts[2];
      $chart->sectionLabel = 'Clients by Age';
      $chart->pieChart = new stdClass;
      $chart->pieChart->label1 = 'Age';
      $chart->pieChart->label2 = '# Clients';
      $chart->bError = false;
      $jidx = 0;
      $chart->pieChart->dataTable = array();
      foreach ($ageRanges as $ageGroup){
         $chart->pieChart->dataTable[$jidx]['label']  =
                        $ageGroup['label'];
         $chart->pieChart->dataTable[$jidx]['lValue'] = $ageGroup['numClients'];
         ++$jidx;
      }

      $this->cGoogleRpts->addPieChart('_'.$lNumCharts, 'Age', '# Clients',
                             $chart->pieChart->dataTable, $optionVars, 'chart_'.$lNumCharts.'_div');
      ++$lNumCharts;

      $displayData['charts']     = &$charts;
      $displayData['ageRanges']  = &$ageRanges;


         // close google charts
      $this->cGoogleRpts->closePieChart();
      $displayData['js'] .= $this->cGoogleRpts->strHeader;
      $displayData['lNumCharts'] = $lNumCharts;

         /*--------------------------
            breadcrumbs
         --------------------------*/
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Client Aggregate Report';

      $displayData['title']          = CS_PROGNAME.' | Clients';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_client_agg_rpt_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function ageRpt($lAgeIDX){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lAgeIDX = (integer)$lAgeIDX;

      $this->load->model('reports/mreports', 'clsReports');
      $this->load->model('clients/mclients', 'clsClients');
      $this->clsClients->clientAgeRanges($ageRanges);

      $reportAttributes = array(
                             'rptType'          => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'          => CENUM_REPORTNAME_CLIENTAGE,
                             'rptDestination'   => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'        => 0,
                             'lRecsPerPage'     => 50,
                             'bShowRecNav'      => true,
                             'viewFile'         => 'pre_generic_rpt_view',
                             'lStartAge'        => $ageRanges[$lAgeIDX]['start'],
                             'lEndAge'          => $ageRanges[$lAgeIDX]['end'],
                             'label'            => $ageRanges[$lAgeIDX]['label']);

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }

   function bDay(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('clients/mclient_locations', 'clsLoc');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/time_date');
      $this->load->library('generic_form');

      $this->clsLoc->loadAllLocations();
      $displayData['lNumLocations'] = $lNumLocations = $this->clsLoc->lNumLocations;
      if ($lNumLocations > 0){
         $displayData['ddlLocs'] = $this->clsLoc->strDDLAllLocations(-1);
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Client Birthdays';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_client_bday_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function bDay_run(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lMonth = (integer)$_POST['ddlMonth'];
      $lLocID = (integer)$_POST['ddlLoc'];

      $this->load->model('reports/mreports', 'clsReports');
      $this->load->model('clients/mclients', 'clsClients');

      $reportAttributes = array(
                             'rptType'          => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'          => CENUM_REPORTNAME_CLIENTBDAY,
                             'rptDestination'   => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'        => 0,
                             'lRecsPerPage'     => 50,
                             'bShowRecNav'      => true,
                             'viewFile'         => 'pre_generic_rpt_view',
                             'lMonth'           => $lMonth,
                             'lLocID'           => $lLocID);

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }
   
   function clientViaStatusID($lStatusID, $bActive){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lStatusID, 'status ID');
   
      $bActive = $bActive == 'true';
   
      $this->load->model('reports/mreports', 'clsReports');
      $this->load->model('clients/mclients', 'clsClients');

      $reportAttributes = array(
                             'rptType'          => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'          => CENUM_REPORTNAME_CLIENTVIASTATUS,
                             'rptDestination'   => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'        => 0,
                             'lRecsPerPage'     => 50,
                             'bShowRecNav'      => true,
                             'bActive'          => $bActive,
                             'viewFile'         => 'pre_generic_rpt_view',
                             'lStatusID'        => $lStatusID);

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }

   function clientViaStatCatID($lStatCatID, $bActive){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lStatCatID, 'status category ID');
      
      $bActive = $bActive == 'true';
   
      $this->load->model('reports/mreports', 'clsReports');
      $this->load->model('clients/mclients', 'clsClients');

      $reportAttributes = array(
                             'rptType'          => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'          => CENUM_REPORTNAME_CLIENTVIASTATCAT,
                             'rptDestination'   => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'        => 0,
                             'lRecsPerPage'     => 50,
                             'bShowRecNav'      => true,
                             'bActive'          => $bActive,
                             'viewFile'         => 'pre_generic_rpt_view',
                             'lStatCatID'       => $lStatCatID);

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }
   
   
}