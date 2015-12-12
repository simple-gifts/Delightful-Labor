<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*---------------------------------------------------------------------
 copyright (c) 2011-2014
 Austin, Texas 78759

 Serving the Children of India

 author: John Zimmerman

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
   __construct      ()
   setBreadCrumbs   ()
   openReport       ($strWidth='', $strStyleExtra='')
   closeReport      ($bAddBR=true)

   openRow          ($bStripes=false)
   closeRow         ()

   writeTitle       ($strValue, $strWidth='', $strStyleExtra='', $lColSpan=1, $lRowSpan=1, $strTagExtra=''){

   writeCell        ($strValue, $strWidth='', $strStyleExtra='', $lColSpan=1, $lRowSpan=1, $strTagExtra=''){
   writeLabel       ($strValue, $strWidth='', $strStyleExtra='', $lColSpan=1, $lRowSpan=1, $strTagExtra=''){
   writeCellGeneric (...

-----------------------------------------------------------------------
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);


Sample usage:
      // in controller
   $this->load->model('util/mbuild_on_ready',    'clsOnReady');
   $this->clsOnReady->addOnReadyTableStripes();
   $this->clsOnReady->closeOnReady();
   $displayData['js'] = $this->clsOnReady->strOnReady;

   $params = array('enumStyle' => 'enpRptC');
   $this->load->library('generic_rpt', $params);

      // in view
   $params = array('enumStyle' => 'enpRptC');
   $clsRpt = new generic_rpt($params);

   echoT($clsRpt->openReport(400));
   echoT($clsRpt->writeTitle('Donation Accounts', 400, '', 3));
   echoT($clsRpt->openRow(true));
   echoT($clsRpt->writeLabel('My little label'));
   echoT($clsRpt->writeCell('abc', 30, $strStyleExtra='text-align: center;'));
   echoT($clsRpt->writeCell('xyz'));
   echoT($clsRpt->closeRow());
   echoT($clsRpt->closeReport());

---------------------------------------------------------------------*/

class generic_rpt{

   public $enumStyle,
      $strTableClass, $strTitleClass, $strLabelClass, $strCellClass,
      $strWidthLabel, $strWidthCell, $strWidthTitle, $divBlock;

   function __construct($params){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      $this->divBlock = new stdClass;
      $this->divBlock->bTableOpen = false;
      $this->divBlock->bDivStarted = false;
      $this->divBlock->strDivID    = '';
      $this->divBlock->strDivImgID = '';      
      
      $this->enumStyle = $params['enumStyle'];

      switch ($this->enumStyle){
         case 'terse':
            $this->strTableClass = 'enpView';
            $this->strTitleClass = 'enpViewTitle';
            $this->strLabelClass = 'enpViewLabel';
            $this->strCellClass  = 'enpView';
            break;

         case 'enpRpt':
         case 'enpRptC':
         default:
            $this->strTableClass = $this->enumStyle;
            $this->strTitleClass = 'enpRptTitle';
            $this->strLabelClass = 'enpRptLabel';
            $this->strCellClass  = 'enpRpt';
            break;
      }
      $this->strWidthLabel = $this->strWidthCell  = $this->strWidthTitle = '';
   }

   public function setEntrySummary   (){   // setBreadCrumbs
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      $this->strTableClass = $this->strCellClass = 'entrySummary';
      $this->strLabelClass = 'entrySummaryLabel';
   }

   function openReport($strWidth='', $strStyleExtra=''){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      $this->divBlock->bTableOpen = true;
      $strWidth = ($strWidth==''      ? '' : ' width="'.$strWidth   .'" ');
      $strStyle = ($strStyleExtra=='' ? '' : ' style="'.$strStyleExtra.'" ');
      return('<table border="0" class="'.$this->strTableClass.'" '.$strStyle.$strWidth.'>'."\n");
   }

   function closeReport($strExtra=''){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      $strOut = '';
      if ($this->divBlock->bDivStarted){
         $attributes = new stdClass;
         $attributes->bCloseDiv = true;
         $strOut .= strCloseBlock($attributes);      
         $strOut .= "\n\n<!-- ------------- End Div Block ------------- -->\n";
      }
      $this->divBlock->bDivStarted = false;
      $this->divBlock->bTableOpen  = false;
      
      return('</table>'.$strExtra.$strOut."\n");
   }
   
   public function openDivBlock($strText, $bCollapseHeadings, $bCollapseDefaultHide, $strDivID, $strDivImgID, $lWidth){
   //---------------------------------------------------------------------
   // note: width must be less than the container table, or things
   // dance around when expanded
   //---------------------------------------------------------------------   
      $strOut = '';
      if ($bCollapseHeadings){
         if ($this->divBlock->bTableOpen) $strOut .= $this->closeReport();
            $attributes = new stdClass;
            $attributes->lTableWidth  = $lWidth;
            $attributes->lMarginLeft  = 15;
            $attributes->divID        = $strDivID;
            $attributes->divImageID   = $strDivImgID;
            $attributes->bStartOpen   = !$bCollapseDefaultHide;
            $strOut .= strOpenBlock($strText, '', $attributes); 
            $strOut .= $this->openReport();         
            $this->divBlock->bDivStarted = true;
      }else {
         $strOut .=
              $this->openRow()
             .$this->writeLabel('<br>'.$strText,
                       'x', 'text-align: left; border-bottom: 1px solid black;', 2)
             .$this->closeRow();
      }
      return($strOut);
   }

   public function openRow($bStripes=false){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      return('<tr '.($bStripes? 'class="makeStripe"' : '').'>');
   }

   public function closeRow(){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      return('</tr>');
   }

   public function writeTitle($strValue, $strWidth='', $strStyleExtra='', $lColSpan=1, $lRowSpan=1, $strTagExtra=''){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      return($this->writeCellGeneric(
                           'title',        $strValue, $strWidth,
                           $strStyleExtra, $lColSpan, $lRowSpan,
                           $strTagExtra));
   }

   public function writeCell($strValue, $strWidth='', $strStyleExtra='', $lColSpan=1, $lRowSpan=1, $strTagExtra=''){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      return($this->writeCellGeneric(
                           'cell',         $strValue, $strWidth,
                           $strStyleExtra, $lColSpan, $lRowSpan,
                           $strTagExtra));
   }

   public function writeLabel($strValue,   $strWidth='', $strStyleExtra='', 
                              $lColSpan=1, $lRowSpan=1,  $strTagExtra=''){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      return($this->writeCellGeneric(
                           'label',        $strValue, $strWidth,
                           $strStyleExtra, $lColSpan, $lRowSpan,
                           $strTagExtra));
   }

   private function writeCellGeneric(
                           $enumCellType,  $strValue, $strWidth,
                           $strStyleExtra, $lColSpan, $lRowSpan,
                           $strTagExtra){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      if ($strWidth.''==''){
         switch ($enumCellType){
            case 'label':  $strWidth = $this->strWidthLabel;  break;
            case 'cell':   $strWidth = $this->strWidthCell;   break;
            case 'title':  $strWidth = $this->strWidthTitle;  break;
            default:
               screamForHelp($enumCellType.': invalid cell type</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
               break;
         }
      }

      $strWidth   = ($strWidth==''      ? '' : ' width="'  .$strWidth     .'" ');
      $strColSpan = ($lColSpan==1       ? '' : ' colspan="'.$lColSpan     .'" ');
      $strRowSpan = ($lRowSpan==1       ? '' : ' rowspan="'.$lRowSpan     .'" ');
      $strStyle   = ($strStyleExtra=='' ? '' : ' style="'  .$strStyleExtra.'" ');

      switch ($enumCellType){
         case 'label':  $strClass = $this->strLabelClass;  break;
         case 'cell':   $strClass = $this->strCellClass;   break;
         case 'title':  $strClass = $this->strTitleClass;  break;
         default:
            screamForHelp($enumCellType.': invalid cell type</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
            break;
      }

      return(('<td class="'.$strClass.'" '.$strWidth.$strColSpan.$strRowSpan.$strStyle.$strTagExtra.'>'
         .$strValue.'</td>'));
   }



   function showRecordStats($dteOrigin, $strOriginName, $dteLastUpdate, $strLastUpdateName, $strLabWidth){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      return(
          $this->openReport()

         .$this->openRow   ()
         .$this->writeLabel('Created:', $strLabWidth)
         .$this->writeCell (date($genumDateFormat.' H:i:s', $dteOrigin).' by '
                             .htmlspecialchars($strOriginName))
         .$this->closeRow  ()

         .$this->openRow   ()
         .$this->writeLabel('Last Updated:', $strLabWidth)
         .$this->writeCell (date($genumDateFormat.' H:i:s', $dteLastUpdate).' by '
                             .htmlspecialchars($strLastUpdateName))
         .$this->closeRow  ()

         .$this->closeReport());
   }


}