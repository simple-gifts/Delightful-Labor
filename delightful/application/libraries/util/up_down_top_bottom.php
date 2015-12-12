<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2011-2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
/*---------------------------------------------------------------------
      $this->load->library('util/up_down_top_bottom');
---------------------------------------------------------------------*/

class up_down_top_bottom{

   public $strLinkBase, $lKeyID, $lMax,
          $strUp, $strDown, $strTop, $strBottom;

   public $enumMove, $enumRecType,
          $strTable, $strKeyField, $strSortField, $strRetiredField,
          $strUfieldDDL, $strUfieldDDLKey, $strUfieldDDLSort,
          $strUfieldDDLRetired, $lTableID;

   public $bDebug;

   private $strExtraWhere;

   function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strLinkBase = $this->lKeyID = $this->lMax =
      $this->strUp = $this->strDown = $this->strTop = $this->strBottom = null;

      $this->enumMove = $this->enumRecType = null;

      $this->lTableID =
      $this->strTable = $this->strKeyField = $this->strSortField =
      $this->strRetiredField   = $this->strUfieldDDL        = $this->strUfieldDDLKey =
      $this->strUfieldDDLSort  = $this->strUfieldDDLRetired =
      $this->strUfieldDDLQual1 = $this->lUfieldDDLQual1Val  =
      $this->strExtraWhere     = null;

      $this->bDebug            = false;

      $this->CI =& get_instance();
   }

   public function upDownLinks($idx){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strUp = $this->strDown = $this->strTop = $this->strBottom =
                            '<img src="'.DL_IMAGEPATH.'/misc/'.IMGLINK_ARROWBLANK.'" border="0">';

      if (is_null($this->strLinkBase)) screamForHelp('Class not initialized<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);

      if ($this->bDebug){
         echoT(__FILE__.': '.__LINE__.":\$this->lMax=$this->lMax, \$idx=$idx <br>\n");
      }

      if ($this->lMax <= 1) return;

      if ($idx < ($this->lMax-1)){
         $this->strDown = $this->strDownLink();
      }

      if ($idx  > 0){
         $this->strUp = $this->strUpLink();
      }

      if ($this->lMax > 2) {
         if ($idx < ($this->lMax-1)) {
            $this->strBottom = $this->strBottomLink();
         }
         if ($idx > 0) {
            $this->strTop = $this->strTopLink();
         }
      }
   }

   function strDownLink(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return($this->strLinkBase.'down'
                    .'"><img src="'.DL_IMAGEPATH.'/misc/'.IMGLINK_DOWN.'"
                                    height="18px"
                                    title="move down"
                                    border="0"></a>');
   }

   function strBottomLink(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return($this->strLinkBase.'bottom'
                    .'"><img src="'.DL_IMAGEPATH.'/misc/'.IMGLINK_BOTTOM.'"
                                    height="18px"
                                    title="move to bottom"
                                    border="0"></a>');
   }

   function strUpLink(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return($this->strLinkBase.'up'
                    .'"><img src="'.DL_IMAGEPATH.'/misc/'.IMGLINK_UP.'"
                                    height="18px"
                                    title="move up"
                                    border="0"></a>');
   }

   function strTopLink(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return($this->strLinkBase.'top'
                    .'"><img src="'.DL_IMAGEPATH.'/misc/'.IMGLINK_TOP.'"
                                    height="18px"
                                    title="move to top"
                                    border="0"></a>');
   }

   public function setTableFields(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($this->enumRecType){
         case 'admin fields':
            $this->strTable        = 'uf_fields';
            $this->strKeyField     = 'pff_lKeyID';
            $this->strSortField    = 'pff_lSortIDX';
            $this->strRetiredField = '';
            break;

         case 'ufield ddl':
            $this->strTable        = $this->strUfieldDDL;
            $this->strKeyField     = $this->strUfieldDDLKey;
            $this->strSortField    = $this->strUfieldDDLSort;
            $this->strRetiredField = $this->strUfieldDDLRetired;
            break;

         case 'creport fields':
            $this->strTable        = $this->strUfieldDDL;
            $this->strKeyField     = $this->strUfieldDDLKey;
            $this->strSortField    = $this->strUfieldDDLSort;
            $this->strRetiredField = $this->strUfieldDDLRetired;
            break;

         case 'pptest question':
            $this->strTable        = $this->strUfieldDDL;
            $this->strKeyField     = $this->strUfieldDDLKey;
            $this->strSortField    = $this->strUfieldDDLSort;
            $this->strRetiredField = $this->strUfieldDDLRetired;
            break;

         case 'img doc tag':
            $this->strTable        = $this->strUfieldDDL;
            $this->strKeyField     = $this->strUfieldDDLKey;
            $this->strSortField    = $this->strUfieldDDLSort;
            $this->strRetiredField = $this->strUfieldDDLRetired;
            break;

         default:
            screamForHelp($this->enumRecType.': Invalid record type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   public function moveRecs(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->setTableFields();

      if (!is_null($this->strUfieldDDLQual1)){
         if (is_numeric($this->strUfieldDDLQual1)){
            $this->strExtraWhere = " AND ($this->strUfieldDDLQual1 = $this->lUfieldDDLQual1Val) ";
         }else{
            $this->strExtraWhere = " AND ($this->strUfieldDDLQual1 = ".strPrepStr($this->lUfieldDDLQual1Val).') ';
         }
      }else {
         $this->strExtraWhere .= " AND (pff_lTableID = $this->lTableID) ";
      }
      if ($this->strRetiredField.'' != ''){
         $this->strExtraWhere .= " AND NOT $this->strRetiredField ";
      }

      if ($this->bDebug){
         echoT(__FILE__.': '.__LINE__.":\$this->enumMove=$this->enumMove <br>\n");
      }

      switch ($this->enumMove){
         case 'bottom':
            $this->moveRecToBottomTop(false);
            break;

         case 'top':
            $this->moveRecToBottomTop(true);
            break;

         case 'up':
            $this->moveRecUpDownOne(true);
            break;

         case 'down':
            $this->moveRecUpDownOne(false);
            break;

         default:
            screamForHelp($this->enumMove.': Invalid move type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function moveRecUpDownOne($bMoveUp){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lCurrentSortIDX = $this->lSortIDX($this->lKeyID);
      if ($this->bDebug){
         echoT(__FILE__.': '.__LINE__.":\$lCurrentSortIDX=$lCurrentSortIDX <br>\n");
      }
      $sqlStr =
         "SELECT $this->strKeyField, $this->strSortField
          FROM $this->strTable
          WHERE $this->strSortField ".($bMoveUp ? '<' : '>')." $lCurrentSortIDX
             $this->strExtraWhere
          ORDER BY $this->strSortField ".($bMoveUp ? 'DESC' : 'ASC').'
          LIMIT 0, 1;';
      if ($this->bDebug){
         echo('<font face="monospace" style="font-size: 8pt;">'.__FILE__.' Line: <b>'.__LINE__.":</b><br><b>\$sqlStr=</b><br>".nl2br(htmlspecialchars($sqlStr))."<br><br></font>\n");
      }

      $query = $this->CI->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0) {
         return;
      }else {
         $row = $query->row_array();
         $lSwitchKeyID   = $row[$this->strKeyField];
         $lSwitchSortIDX = $row[$this->strSortField];

         $this->setSortIDX($this->lKeyID, $lSwitchSortIDX);
         $this->setSortIDX($lSwitchKeyID, $lCurrentSortIDX);
      }
   }

   function lSortIDX($lKeyID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "SELECT $this->strSortField
          FROM $this->strTable
          WHERE $this->strKeyField=$lKeyID $this->strExtraWhere ;";

      $query = $this->CI->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0) {
         return(null);
      }else {
         $row = $query->row_array();
         return($row[$this->strSortField]);
      }
   }

   function moveRecToBottomTop($bToTop){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lUpdateIDX = (integer)(($bToTop ? '-' : '').'999999');
      $this->setSortIDX($this->lKeyID, $lUpdateIDX);

      $this->reorderTable();
   }

   function setSortIDX($lKeyValue, $lNewSortIDX){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "UPDATE $this->strTable
          SET $this->strSortField = $lNewSortIDX
          WHERE $this->strKeyField=$lKeyValue;";
      $query = $this->CI->db->query($sqlStr);
   }

   function reorderTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "SELECT $this->strKeyField, $this->strSortField
          FROM $this->strTable
          WHERE 1 ";

      if ($this->strRetiredField != ''){
         $sqlStr .= " AND (NOT $this->strRetiredField) ";
      }
      $sqlStr .= $this->strExtraWhere;

      $sqlStr .= "ORDER BY $this->strSortField;";
      $query = $this->CI->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows > 0){
         $idx = 0;
         foreach ($query->result_array() as $row){
            $this->setSortIDX($row[$this->strKeyField], $idx);
            ++$idx;
         }
      }
   }

   function lMaxSortIDX($opts=null){
   //---------------------------------------------------------------------
   // if not null, opts can be used to qualify the table lookup
   /* i.e.
        $opts = array();
        $opts[0] = new stdClass;
        $opts[0]->strFN = 'cpq_lPrePostID'
        $opts[0]->lQualValue = 123;
   --------------------------------------------------------------------- */
//      $CI =& get_instance();

      $strWhere = '';
      if (!is_null($opts)){
         foreach ($opts as $opt){
            $strWhere .= " AND $opt->strFN = $opt->lQualValue \n";
         }
      }
      $sqlStr =
         "SELECT $this->strKeyField, MAX($this->strSortField) AS lMax
          FROM $this->strTable
          WHERE 1 $strWhere";
      $query = $this->CI->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lMax);
   }



}

?>