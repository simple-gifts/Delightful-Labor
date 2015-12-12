<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_attrib extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function attrib(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData = array();
      $displayData['js'] = '';

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model ('util/mattrib', 'clsAttrib');
      $this->load->helper('dl_util/web_layout');
      $this->load->model ('util/mgoogle_charts', 'cGoogleRpts');

      $this->clsAttrib->countAttribs();
      $displayData['attribCounts'] = &$this->clsAttrib->attribCount;

         //-------------------------------------------------------
         // create pie charts for each attribution category
         //-------------------------------------------------------
      $this->cGoogleRpts->initPieChart();
      $this->cGoogleRpts->openPieChart();
      $idx = 0;
      foreach ($this->clsAttrib->attribCount as $at){
         $optionVars = array('title'     => '\''.$at->strEnumType.'\'',
                             'chartArea' => '{left:0,top:20,width:320,height:180}',
                           //  'chartArea' => '{left:0,top:10,width:600,height:250}',
                           //  'legend'    => '{position: \'right\',fontSize: 11, alignment:\'center\'}',
                             'is3D'      => 'true'
                             );
         $dataVars   = array();
         $jidx = 0;
         foreach ($at->attribList as $atl){
            $strLabel = $atl->strAttrib;
            if ($strLabel.'' == '') $strLabel = 'n/a';
            $dataVars[$jidx]['label']  = $strLabel;
            $dataVars[$jidx]['lValue'] = $atl->lCount;
            ++$jidx;
         }
         $strIdx = str_pad($idx, 2, '0', STR_PAD_LEFT);
         $this->cGoogleRpts->addPieChart('_'.$strIdx, 'Attributed To', 'Count',
                                         $dataVars, $optionVars, 'chart_'.$strIdx.'_div');

         ++$idx;
      }
      $this->cGoogleRpts->closePieChart();
      $displayData['js'] .= $this->cGoogleRpts->strHeader;
      
         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                                .' | Attributed To';

      $displayData['title']          = CS_PROGNAME.' | Reports';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reports/pre_attrib_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function attribList($enumContext, $lAttribID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      if ($lAttribID.'' != '0') verifyID($this, $lAttribID, 'attributed to ID');

      $this->load->model('reports/mreports',    'clsReports');

      $reportAttributes = array(
                             'rptType'        => CENUM_REPORTTYPE_PREDEFINED,
                             'rptName'        => CENUM_REPORTNAME_ATTRIB,
                             'rptDestination' => CENUM_REPORTDEST_SCREEN,
                             'lStartRec'      => 0,
                             'lRecsPerPage'   => 50,
                             'bShowRecNav'    => true,
                             'viewFile'       => 'pre_generic_rpt_view',
                             'enumContext'    => $enumContext,
                             'lAttribID'      => $lAttribID,
                             );

      $this->clsReports->createReportSessionEntry($reportAttributes);
      $reportID = $this->clsReports->sRpt->reportID;
      redirect('reports/reports/run/'.$reportID);
   }

}