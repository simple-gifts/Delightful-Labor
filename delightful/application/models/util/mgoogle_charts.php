<?php
/*---------------------------------------------------------------------
// Delightful Labor
// copyright (c) 2012 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
  ---------------------------------------------------------------------
      $this->load->model('util/mgoogle_charts', 'cGoogleRpts');
---------------------------------------------------------------------
  Interface to the javascript APIs provided by google.com

  https://developers.google.com/chart/
---------------------------------------------------------------------*/


class mgoogle_charts extends CI_Model{

   public $options, $values, $strHeader, $strDiv, $pieColors;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

   }

   public function initPieChart(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->options = new stdClass;
      $this->values  = array();
      $this->strHeader = $this->strDiv = '';
      $this->pieColors = 
               ', colors: [\'#b30e0e\', \'#36ac02\', \'#0093c3\', \'#003cc3\', 
                           \'#b1005c\', \'#30e366\', \'#00b366\', \'#004c66\' ]  ';
   }

   public function initColumnChart(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->options = new stdClass;
      $this->values  = array();
      $this->strHeader = $this->strDiv = '';
      $this->pieColors = 
               ', colors: [\'#b30e0e\', \'#36ac02\', \'#0093c3\', \'#003cc3\', 
                           \'#b1005c\', \'#30e366\', \'#00b366\', \'#004c66\' ]  ';
   }

   public function openPieChart(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->openChartGeneric();
   }

   public function openChartGeneric(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strHeader .= "\n<!-- ++++++++++   openChart   +++++++++  -->\n";
      $this->strHeader .= '
       <script type="text/javascript" src="https://www.google.com/jsapi"></script>
       <script type="text/javascript">
         google.load("visualization", "1", {packages:["corechart"]});
         google.setOnLoadCallback(drawChart);
         function drawChart() {
      ';
   }

   public function closePieChart(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->closeChartGeneric();
   }
   
   public function closeChartGeneric(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strHeader .= "\n<!-- ++++++++++   closeChart   ++++++++++  -->\n".'
            }
          </script>
          ';
   }

   public function addPieChart($strVarID, $strHeader1, $strHeader2,
                               $dataVars, $optionVars, $strDiv){
   /*---------------------------------------------------------------------
      sample call:
         $dataVars   = array();
         $dataVars[0]['label']  = 'Work';
         $dataVars[0]['lValue'] = 11;
         $dataVars[1]['label']  = 'Sleep';
         $dataVars[1]['lValue'] = 8;
         $dataVars[2]['label']  = 'Eat';
         $dataVars[2]['lValue'] = 1.5;
         $dataVars[3]['label']  = 'Surf';
         $dataVars[3]['lValue'] = 3;

         $optionVars = array('title' => '\'My little chart\'',
                             'is3D'  => 'true');
         $this->cGoogleRpts->addPieChart('_01', 'Task', 'Hours Per Day',
                                         $dataVars, $optionVars, 'chart_01_div');
   --------------------------------------------------------------------- */
      $this->strHeader .= "\n<!-- +++++++++++++++   $strDiv   +++++++++++++++++++  -->\n";

      $strHeaderHold = '
                    var data'.$strVarID.' = google.visualization.arrayToDataTable([
                       ['.strPrepStr($strHeader1).', '.strPrepStr($strHeader2).'] ';

      $strVars = '';
      $this->bAllZero = true;
      foreach ($dataVars as $dataVar){
         $lValue = $dataVar['lValue'];
         if (is_null($lValue)) $lValue = 0;
         if ($lValue != 0) $this->bAllZero = false;
         $strHeaderHold .= ', ['.strPrepStr($dataVar['label']).', '.$lValue.'] '."\n";
      }
      if ($this->bAllZero){
         return;
      }else {
         $this->strHeader .= $strHeaderHold;
      }

      $this->strHeader .= ']);'."\n\n";


      $this->strHeader .= '
                    var options'.$strVarID.' = { ';

      $strOpts = '';
      foreach ($optionVars as $optName=>$optValue){
               $strOpts .= ', '.$optName.': '.$optValue."\n";
      }
      $this->strHeader .= substr($strOpts, 1)."\n".$this->pieColors.' }; '."\n\n";

      $this->strHeader .=
                 ' var chart'.$strVarID.' = new google.visualization.PieChart(document.getElementById('.strPrepStr($strDiv).'));
                   chart'.$strVarID.'.draw(data'.$strVarID.', options'.$strVarID.'); '."\n";

   }
   
   
   public function addColumnChart($strVarID, $strHeader1, $strHeader2,
                               $dataVars, $optionVars, $strDiv){
   /*---------------------------------------------------------------------
   
      NOT DEBUGGED !!!!!!!!!!!!
      
      
      sample call:
         
   --------------------------------------------------------------------- */
      $this->strHeader .= "\n<!-- +++++++++++++++   $strDiv   +++++++++++++++++++  -->\n";

      $strHeaderHold = '
                    var data'.$strVarID.' = google.visualization.arrayToDataTable([
                       ['.strPrepStr($strHeader1).', '.strPrepStr($strHeader2).'] ';

      $strVars = '';
      $this->bAllZero = true;
      foreach ($dataVars as $dataVar){
         $lValue = $dataVar['lValue'];
         if (is_null($lValue)) $lValue = 0;
         if ($lValue != 0) $this->bAllZero = false;
         $strHeaderHold .= ', ['.strPrepStr($dataVar['label']).', '.$lValue.'] '."\n";
      }
      if ($this->bAllZero){
         return;
      }else {
         $this->strHeader .= $strHeaderHold;
      }

      $this->strHeader .= ']);'."\n\n";


      $this->strHeader .= '
                    var options'.$strVarID.' = { ';

      $strOpts = '';
      foreach ($optionVars as $optName=>$optValue){
               $strOpts .= ', '.$optName.': '.$optValue."\n";
      }
      $this->strHeader .= substr($strOpts, 1)."\n".$this->pieColors.' }; '."\n\n";

      $this->strHeader .=
                 ' var chart'.$strVarID.' = new google.visualization.ColumnChart(document.getElementById('.strPrepStr($strDiv).'));
                   chart'.$strVarID.'.draw(data'.$strVarID.', options'.$strVarID.'); '."\n";

   }
   
   
   
   function strDefaultColumnChartOpts($strTitle1, $strTitle2, $strChartDivID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      return('
        var options = {
          title: \''.$strTitle1.'\',
          legend: { position: \'top\', maxLines: 3, fontSize: 18 },
          titlePosition: \'out\',
          bar: {groupWidth: "85%"},
          colors: [\'#0e60b3\', \'#36ac02\', \'#0093c3\', \'#003cc3\', 
                   \'#b1005c\', \'#30e366\', \'#00b366\', \'#004c66\' ],
          hAxis: {title: \''.$strTitle2.'\', titleTextStyle: {color: \'black\', fontSize: 16}}
        };

        var chart = new google.visualization.ColumnChart(
                             document.getElementById(\''.$strChartDivID.'\'));
        chart.draw(data, options);
        
        <!-- ----- -->
        ');
   }
   
   public function initMonthlyDataArray($lNumCols=2){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      $data = array(
                1 => array('Jan', 0),
                2 => array('Feb', 0),
                3 => array('Mar', 0),
                4 => array('Apr', 0),
                5 => array('May', 0),
                6 => array('Jun', 0),
                7 => array('Jul', 0),
                8 => array('Aug', 0),
                9 => array('Sep', 0),
               10 => array('Oct', 0),
               11 => array('Nov', 0),
               12 => array('Dec', 0),
                    );
      if ($lNumCols > 2){
         for ($idx=1; $idx<=12; ++$idx){
            for ($jidx=2; $jidx<$lNumCols; ++$jidx){
               $data[$idx][$jidx] = 0;
            }
         }
      }
      return($data);
   }
   

}

/*
          colors: [\'#4e5363\', \'#72798c\', \'#727fa6\', \'#8096d2\', \'#9ea8c6\', \'#a9b0c3\'], 
$this->strHeader = '
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
google.load(\'visualization\', \'1\', {\'packages\':[\'corechart\']});
google.setOnLoadCallback(drawChart);
function drawChart()
{
    var data = new google.visualization.DataTable();
    data.addColumn(\'string\', \'Name\');
    data.addColumn(\'number\', \'Count\');

    data.addRows([ [\'Value A\',5 ],[\'Value B\',61 ],[\'Value C\',53 ],[\'Value D\',22 ] ]);
    var chart_1 = new google.visualization.PieChart(document.getElementById(\'chart_01_div\'));
    chart_1.draw(data, {width: 400, height: 280, is3D: true, title: \'\'});

    google.visualization.events.addListener(chart_1, \'select\', selectHandler);

    function selectHandler(e)     {
        alert(data.getValue(chart_1.getSelection()[0].row, 0));
    }

}

</script>
          ';
*/
