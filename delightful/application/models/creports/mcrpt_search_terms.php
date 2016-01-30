<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2014-2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('creports/mcrpt_search_terms', 'crptTerms');
---------------------------------------------------------------------

---------------------------------------------------------------------*/


class mcrpt_search_terms extends mcreports{

   public $strWhereExtra, $lNumTerms, $terms;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->strWhereExtra = '';
      $this->lNumTerms     = 0;
      $this->terms         = null;
   }

   function loadSearchTermViaTermID($lTermID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strWhereExtra = " AND crs_lKeyID=$lTermID ";
      $this->loadSearchTerms();
   }

   function loadSearchTermViaReportID($lReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strWhereExtra = " AND crs_lReportID=$lReportID ";
      $this->loadSearchTerms();
   }

   function loadSearchTerms(){
   //---------------------------------------------------------------------
   // data loaded in $this->lNumTerms and $this->terms
   //---------------------------------------------------------------------
      $this->lNumTerms     = 0;
      $this->terms         = array();
      $cACO = new madmin_aco;

      $sqlStr =
           "SELECT
               crs_lKeyID, crs_lReportID, crs_lFieldID, crs_strFieldID,
               crs_lTableID, crs_lNumLParen, crs_lNumRParen, crs_lSortIDX,
               crs_lCompareOpt,
               crs_bCompareBool, crs_lCompVal, crs_strCompVal, crs_curCompVal, crs_dteCompVal,
               crs_bNextTermBoolAND,
               crs_lOriginID, crs_lLastUpdateID,

               pff_lCurrencyACO,
               pff_strFieldNameUser, pff_enumFieldType, pft_strUserTableName, pft_enumAttachType
            FROM creport_search
               LEFT JOIN uf_fields ON pff_lKeyID   = crs_lFieldID
               LEFT JOIN uf_tables ON pff_lTableID = pft_lKeyID
            WHERE 1 $this->strWhereExtra
            ORDER BY crs_lSortIDX, crs_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumTerms = $lNumTerms = $query->num_rows();
      if ($lNumTerms==0){
         $this->terms[0] = new stdClass;
         $term = &$this->terms[0];

         $term->lKeyID            =
         $term->lReportID         =
         $term->lTableID          =
         $term->lFieldID          =
         $term->strFieldID        =
         $term->enumFieldType     =
         $term->enumAttachType    =

         $term->strFieldNameUser  =
         $term->strUserTableName  =

         $term->lNumLParen        =
         $term->lNumRParen        =
         $term->lSortIDX          =
         $term->lCompareOpt       =

         $term->bCompareBool      =
         $term->lCompVal          =
         $term->curCompVal        =
         $term->lCurrencyACO      =
         $term->strCompVal        =
         $term->mdteCompVal       =

         $term->bNextTermBoolAND  =
         $term->lOriginID         =
         $term->lLastUpdateID     = null;
      }else {
         $idx = 0;
         foreach  ($query->result() as $row){
            $this->terms[$idx] = new stdClass;
            $term = &$this->terms[$idx];

            $term->lKeyID            = (int)$row->crs_lKeyID;
            $term->lReportID         = (int)$row->crs_lReportID;
            $term->lTableID          = (int)$row->crs_lTableID;
            $term->lFieldID          = $lFieldID = (int)$row->crs_lFieldID;
            $term->strFieldID        = $row->crs_strFieldID;
            if ($term->lTableID <=0 ){
               crptFieldPropsParentTable($term->lTableID, $term->strFieldID,
                         $term->strUserTableName, $term->enumFieldType, $term->strFieldNameUser, $term->enumAttachType);
            }else {
               $term->enumFieldType     = $row->pff_enumFieldType;
               $term->strUserTableName  = $row->pft_strUserTableName;
               $term->strFieldNameUser  = $row->pff_strFieldNameUser;
               $term->enumAttachType    = $row->pft_enumAttachType;
            }

            $term->lNumLParen        = (int)$row->crs_lNumLParen;
            $term->lNumRParen        = (int)$row->crs_lNumRParen;
            $term->lSortIDX          = (int)$row->crs_lSortIDX;
            $term->lCompareOpt       = (int)$row->crs_lCompareOpt;

            $term->bCompareBool      = (boolean)$row->crs_bCompareBool;
            $term->lCompVal          = (int)$row->crs_lCompVal;
            $term->lCurrencyACO      = (int)$row->pff_lCurrencyACO;
            if ($term->lCurrencyACO > 0){
               $cACO->loadCountries(false, true, true, $term->lCurrencyACO);
               $term->ACO = clone($cACO->countries[0]);
            }
            if ($lFieldID < 0){  // indicates a "record written" field
               $term->enumFieldType = CS_FT_CHECKBOX;
               $term->strFieldNameUser  = 'Record written?';
               $term->enumAttachType    = enumCRptAttachViaUTableID(-$lFieldID);
               $clsUF = new muser_fields;
               $clsUF->lTableID = -$lFieldID;
               $clsUF->loadTableViaTableID();
               $utable = &$clsUF->userTables[0];
               $term->strUserTableName  = $utable->strUserTableName;
            }
            if ($term->lTableID > 0 ){
               $term->strAttachLabel    = strLabelViaContextType($term->enumAttachType, true, false);
            }

            $term->curCompVal        = $row->crs_curCompVal;
            $term->strCompVal        = $row->crs_strCompVal;
            $term->mdteCompVal       = $row->crs_dteCompVal;

            $term->bNextTermBoolAND  = $row->crs_bNextTermBoolAND;
            $term->lOriginID         = $row->crs_lOriginID;
            $term->lLastUpdateID     = $row->crs_lLastUpdateID;

            ++$idx;
         }
      }
   }

   function loadFieldTermOpts($lReportID, &$term, &$field){ // &$tables, $lTableIDX, $lFieldIDX){
   //---------------------------------------------------------------------
   // $vSelVal1 - the comparison key
   // $vSelVal2 - the first comparison value
   //---------------------------------------------------------------------
      global $gdteNow;

      $enumFieldType = $field->enumType;

      $lTermID = $term->lKeyID;
         // new term
      if (is_null($lTermID)){
         $term->strFieldID    = $field->internalName;
         $term->lReportID     = $lReportID;
         $term->lSortIDX      = 0;
         $term->lNumRParen    = 0;
         $term->lNumLParen    = 0;
         $term->enumFieldType = $enumFieldType;
         $term->lCompareOpt   = 0;

         $term->bCompareBool      = false;
         $term->lCompVal          = 0;
         $term->curCompVal        = 0.0;
         $term->strCompVal        = '';
         if ($enumFieldType==CS_FT_DATE){
            numsMoDaYr($gdteNow, $lMonth, $lDay, $lYear);
            $term->mdteCompVal = strMoDaYr2MySQLDate($lMonth, $lDay, $lYear);
         }
      }

      switch ($enumFieldType) {
         case CS_FT_CHECKBOX:
            $this->setCheckBoxOptions($term);
            break;

         case CS_FT_INTEGER:
         case CS_FT_CURRENCY:
            $this->setNumberOptions($term, $enumFieldType==CS_FT_INTEGER);
            break;

         case CS_FT_DATE:
            $this->setDateOptions($term);
            break;

         case CS_FT_TEXTLONG:
         case CS_FT_TEXT255:
         case CS_FT_TEXT80:
         case CS_FT_TEXT20:
         case CS_FT_TEXT:
            $this->setTextOptions($term);
            break;

         case CS_FT_DDL:
         case CS_FT_DDLMULTI:
            $this->setDDLOptions($term, $field);
            break;

         case CS_FT_DDL_SPECIAL:
            $this->setSpecialDDLOptions($term, $field);
            break;

         case CS_FT_ID:
            $this->setNumberOptions($term, true);
            break;

         case CS_FT_LOG:
         case CS_FT_DATETIME:
         case CS_FT_HEADING:
//         case CS_FT_EMAIL:
//         case CS_FT_HLINK:
            checkBackSoon($enumFieldType, __LINE__, __FUNCTION__, __FILE__);
            echo('field Type:<b>'.strXlateFieldType($enumFieldType).'</b><br><b>line:</b> '.__LINE__
                 .'<br><b>file:</b> '.__FILE__
                 .'<br><b>function:</b> '.__FUNCTION__.'<br><br>');
            die;
            break;

         default:
            screamForHelp('INVALID FIELD TYPE "'.$enumFieldType.'", error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__);
            break;
      }
   }

   function setCheckBoxOptions(&$term){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $term->ddlSelectValue_1 = array();
      $term->ddlSelectValue_1[0] = new stdClass;
      $term->ddlSelectValue_1[0]->name   = 'Yes';
      $term->ddlSelectValue_1[0]->bSel   = $term->bCompareBool;
      $term->ddlSelectValue_1[0]->optVal = CL_SRCH_CHK_YES;

      $term->ddlSelectValue_1[1] = new stdClass;
      $term->ddlSelectValue_1[1]->name = 'No';
      $term->ddlSelectValue_1[1]->bSel = !$term->bCompareBool;
      $term->ddlSelectValue_1[1]->optVal = CL_SRCH_CHK_NO;
   }

   function setSpecialDDLOptions(&$term, $field){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $term->ddlCompare = array();
      $term->ddlCompare[0] = new stdClass;
      $term->ddlCompare[0]->name   = CS_SRCH_EQ;
      $term->ddlCompare[0]->bSel   = $term->lCompareOpt == CL_SRCH_EQ;
      $term->ddlCompare[0]->optVal = CL_SRCH_EQ;

      $term->ddlCompare[1] = new stdClass;
      $term->ddlCompare[1]->name   = CS_SRCH_NEQ;
      $term->ddlCompare[1]->bSel   = $term->lCompareOpt == CL_SRCH_NEQ;
      $term->ddlCompare[1]->optVal = CL_SRCH_NEQ;

         // load ddl entries
      sddl\loadSpecialDDL($term->strFieldID, $term->lNumDDLEntries, $term->ddlEntries);
   }

   function setDDLOptions(&$term, $field){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $term->ddlCompare = array();
      $term->ddlCompare[0] = new stdClass;
      $term->ddlCompare[0]->name   = CS_SRCH_EQ;
      $term->ddlCompare[0]->bSel   = $term->lCompareOpt == CL_SRCH_EQ;
      $term->ddlCompare[0]->optVal = CL_SRCH_EQ;

      $term->ddlCompare[1] = new stdClass;
      $term->ddlCompare[1]->name   = CS_SRCH_NEQ;
      $term->ddlCompare[1]->bSel   = $term->lCompareOpt == CL_SRCH_NEQ;
      $term->ddlCompare[1]->optVal = CL_SRCH_NEQ;

         // load ddl entries
      $cddl = new muser_fields;
      $cddl->loadDDLEntries($field->lFieldID);
      $term->lNumDDLEntries = $lNumEntries = $cddl->clsDDL_Info->lNumEntries;
      $term->ddlEntries = arrayCopy($cddl->clsDDL_Info->clsEntries);
   }

   function setTextOptions(&$term){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $term->ddlCompare = array();
      $term->ddlCompare[0] = new stdClass;
      $term->ddlCompare[0]->name   = CS_SRCH_EQ;
      $term->ddlCompare[0]->bSel   = $term->lCompareOpt == CS_SRCH_EQ;
      $term->ddlCompare[0]->optVal = CL_SRCH_EQ;

      $term->ddlCompare[1] = new stdClass;
      $term->ddlCompare[1]->name   = CS_SRCH_NEQ;
      $term->ddlCompare[1]->bSel   = $term->lCompareOpt == CL_SRCH_NEQ;
      $term->ddlCompare[1]->optVal = CL_SRCH_NEQ;

         // text-specific
      $term->ddlCompare[2] = new stdClass;
      $term->ddlCompare[2]->name   = CS_SRCH_STR_START;
      $term->ddlCompare[2]->bSel   = $term->lCompareOpt == CL_SRCH_STR_START;
      $term->ddlCompare[2]->optVal = CL_SRCH_STR_START;

      $term->ddlCompare[3] = new stdClass;
      $term->ddlCompare[3]->name   = CS_SRCH_STR_NOTSTART;
      $term->ddlCompare[3]->bSel   = $term->lCompareOpt == CL_SRCH_STR_NOTSTART;
      $term->ddlCompare[3]->optVal = CL_SRCH_STR_NOTSTART;

      $term->ddlCompare[4] = new stdClass;
      $term->ddlCompare[4]->name   = CS_SRCH_STR_END;
      $term->ddlCompare[4]->bSel   = $term->lCompareOpt == CL_SRCH_STR_END;
      $term->ddlCompare[4]->optVal = CL_SRCH_STR_END;

      $term->ddlCompare[5] = new stdClass;
      $term->ddlCompare[5]->name   = CS_SRCH_STR_NOTEND;
      $term->ddlCompare[5]->bSel   = $term->lCompareOpt == CL_SRCH_STR_NOTEND;
      $term->ddlCompare[5]->optVal = CL_SRCH_STR_NOTEND;

      $term->ddlCompare[6] = new stdClass;
      $term->ddlCompare[6]->name   = CS_SRCH_STR_CONTAINS;
      $term->ddlCompare[6]->bSel   = $term->lCompareOpt == CL_SRCH_STR_CONTAINS;
      $term->ddlCompare[6]->optVal = CL_SRCH_STR_CONTAINS;

      $term->ddlCompare[7] = new stdClass;
      $term->ddlCompare[7]->name   = CS_SRCH_STR_NOTCONTAIN;
      $term->ddlCompare[7]->bSel   = $term->lCompareOpt == CL_SRCH_STR_NOTCONTAIN;
      $term->ddlCompare[7]->optVal = CL_SRCH_STR_NOTCONTAIN;
   }

   function setDateOptions(&$term){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      $term->strDteCompVal = strNumericDateViaMysqlDate($term->mdteCompVal, $gbDateFormatUS);

      $term->ddlCompare = array();
      $term->ddlCompare[0] = new stdClass;
      $term->ddlCompare[0]->name   = CS_SRCH_DEQ;
      $term->ddlCompare[0]->bSel   = $term->lCompareOpt == CL_SRCH_DEQ;
      $term->ddlCompare[0]->optVal = CL_SRCH_DEQ;

      $term->ddlCompare[1] = new stdClass;
      $term->ddlCompare[1]->name   = CS_SRCH_DNEQ;
      $term->ddlCompare[1]->bSel   = $term->lCompareOpt == CL_SRCH_DNEQ;
      $term->ddlCompare[1]->optVal = CL_SRCH_DNEQ;

      $term->ddlCompare[2] = new stdClass;
      $term->ddlCompare[2]->name   = CS_SRCH_DGT;
      $term->ddlCompare[2]->bSel   = $term->lCompareOpt == CL_SRCH_DGT;
      $term->ddlCompare[2]->optVal = CL_SRCH_DGT;

      $term->ddlCompare[3] = new stdClass;
      $term->ddlCompare[3]->name   = CS_SRCH_DGE;
      $term->ddlCompare[3]->bSel   = $term->lCompareOpt == CL_SRCH_DGE;
      $term->ddlCompare[3]->optVal = CL_SRCH_DGE;

      $term->ddlCompare[4] = new stdClass;
      $term->ddlCompare[4]->name   = CS_SRCH_DLT;
      $term->ddlCompare[4]->bSel   = $term->lCompareOpt == CL_SRCH_DLT;
      $term->ddlCompare[4]->optVal = CL_SRCH_DLT;

      $term->ddlCompare[5] = new stdClass;
      $term->ddlCompare[5]->name   = CS_SRCH_DLE;
      $term->ddlCompare[5]->bSel   = $term->lCompareOpt == CL_SRCH_DLE;
      $term->ddlCompare[5]->optVal = CL_SRCH_DLE;
   }

   function setNumberOptions(&$term, $bInt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $term->ddlCompare = array();
      $term->ddlCompare[0] = new stdClass;
      $term->ddlCompare[0]->name   = CS_SRCH_EQ;
      $term->ddlCompare[0]->bSel   = $term->lCompareOpt == CL_SRCH_EQ;
      $term->ddlCompare[0]->optVal = CL_SRCH_EQ;

      $term->ddlCompare[1] = new stdClass;
      $term->ddlCompare[1]->name   = CS_SRCH_NEQ;
      $term->ddlCompare[1]->bSel   = $term->lCompareOpt == CL_SRCH_NEQ;
      $term->ddlCompare[1]->optVal = CL_SRCH_NEQ;

      $term->ddlCompare[2] = new stdClass;
      $term->ddlCompare[2]->name   = CS_SRCH_GT;
      $term->ddlCompare[2]->bSel   = $term->lCompareOpt == CL_SRCH_GT;
      $term->ddlCompare[2]->optVal = CL_SRCH_GT;

      $term->ddlCompare[3] = new stdClass;
      $term->ddlCompare[3]->name   = CS_SRCH_GE;
      $term->ddlCompare[3]->bSel   = $term->lCompareOpt == CL_SRCH_GE;
      $term->ddlCompare[3]->optVal = CL_SRCH_GE;

      $term->ddlCompare[4] = new stdClass;
      $term->ddlCompare[4]->name   = CS_SRCH_LT;
      $term->ddlCompare[4]->bSel   = $term->lCompareOpt == CL_SRCH_LT;
      $term->ddlCompare[4]->optVal = CL_SRCH_LT;

      $term->ddlCompare[5] = new stdClass;
      $term->ddlCompare[5]->name   = CS_SRCH_LE;
      $term->ddlCompare[5]->bSel   = $term->lCompareOpt == CL_SRCH_LE;
      $term->ddlCompare[5]->optVal = CL_SRCH_LE;
   }

       /* ------------------------------------------------------------
                A D D  /  U P D A T E   S E A R C H   T E R M S
          ------------------------------------------------------------ */

   function lAddNewSearchTerm(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $term = &$this->terms[0];

      $sqlStr =
         'INSERT INTO creport_search
          SET '.$this->sqlCommonTermAddEdit().',
            crs_strFieldID = '.strPrepStr($term->strFieldID).",
            crs_lReportID  = $term->lReportID,
            crs_lFieldID   = $term->lFieldID,
            crs_lTableID   = $term->lTableID,
            crs_lNumLParen = $term->lNumLParen,
            crs_lNumRParen = $term->lNumRParen,
            crs_bNextTermBoolAND = 0,

            crs_lOriginID = $glUserID,
            crs_dteOrigin = NOW();";

      $this->db->query($sqlStr);
      $term->lKeyID = $lKeyID = $this->db->insert_id();
      return($lKeyID);
   }

   function updateSearchTerm($lTermID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         'UPDATE creport_search
          SET '.$this->sqlCommonTermAddEdit()."
          WHERE crs_lKeyID=$lTermID;";
      $this->db->query($sqlStr);
      $term->lKeyID = $lKeyID = $this->db->insert_id();
   }

   function sqlCommonTermAddEdit(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $term = &$this->terms[0];

      $strOut = '
            crs_bCompareBool  = '.strDBValueConvert_BOOL($term->bCompareBool).',
            crs_lCompVal      = '.strDBValueConvert_INT($term->lCompVal).',
            crs_curCompVal    = '.strDBValueConvert_SNG($term->curCompVal, 2).',
            crs_strCompVal    = '.strDBValueConvert_String($term->strCompVal).',
            crs_dteCompVal    = '.strDBValueConvert_String($term->mdteCompVal).",
            crs_lCompareOpt   = $term->lCompareOpt,
            crs_lSortIDX      = $term->lSortIDX,
            crs_lLastUpdateID = $glUserID,
            crs_dteLastUpdate = NOW()
            ";
      return($strOut);
   }

   function removeTerm($lTermID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "DELETE FROM creport_search WHERE crs_lKeyID=$lTermID;";
      $this->db->query($sqlStr);
   }

   function removeTermViaRptIDFieldID($lReportID, $lFieldID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
           "DELETE FROM creport_search
            WHERE crs_lReportID=$lReportID AND crs_lFieldID=$lFieldID;";
      $this->db->query($sqlStr);
   }



       /* ------------------------------------------------------------
                T E R M   S O R T I N G
          ------------------------------------------------------------ */

   function lMaxSortIDXViaReport($lReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT MAX(crs_lSortIDX) AS lMaxSortIDX
         FROM creport_search
         WHERE crs_lReportID=$lReportID;";

      $query = $this->db->query($sqlStr);

      $row = $query->row();
      $lMaxSortIDX = $row->lMaxSortIDX;
      if (is_null($lMaxSortIDX)){
         return(0);
      }else {
         return((int)$lMaxSortIDX);
      }
   }


       /* ------------------------------------------------------------
                P A R E N T H E S E S
          ------------------------------------------------------------ */

   function bAreParensBalanced(){
      if ($this->lNumTerms == 0) return(true);

      $lTotLeft = $lTotRight = 0;
      foreach ($this->terms as $term){
         $lTotLeft  += $term->lNumLParen;
         $lTotRight += $term->lNumRParen;
         if ($lTotRight > $lTotLeft) return(false);  // are we in the hole?
      }
      return($lTotLeft == $lTotRight);
   }

   function updateParens($parenTerms){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      foreach ($parenTerms as $lTermID=>$pt){
         $sqlStr =
           'UPDATE creport_search
            SET
               crs_lNumLParen = '.$pt->left.',
               crs_lNumRParen = '.$pt->right.',
               crs_bNextTermBoolAND = '.($pt->bAnd ? '1' : '0')."
            WHERE crs_lKeyID=$lTermID;";
         $query = $this->db->query($sqlStr);
      }
   }
}
