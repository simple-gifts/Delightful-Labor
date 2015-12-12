<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('creports/mcrpt_run', 'crptRun');
---------------------------------------------------------------------

---------------------------------------------------------------------*/


class mcrpt_run extends mcrpt_search_terms{

   public $crpt, $tableIDs, $uf, $cvUF;
   public $strWhere, $strJoins, $strSelect, $strOrder, $strSQL;


   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
   }

   function strBuildCReportSQL(&$report, $strLimit='', $bTerminateSQL=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lReportID = $report->lKeyID;

         // create select aliases
      $this->buildSelectAsNames($report->fields);

         // load search terms
      $this->loadSearchTermViaReportID($lReportID);

         // load sort terms
      $this->loadSortFieldsViaReportID($lReportID, $lNumSortTerms, $sortTerms);

      $this->uf = new muser_fields;

         // tables needed for the report
      crptTables\tablesUsed($report, $this->terms, $sortTerms, $this->tableIDs);
      
      $stTableIDs = array();
      crptTables\tableIDsSearchTerms($this->terms, $stTableIDs);
      $stTableIDs = array_keys($stTableIDs);      
      $this->singleEntryPTableReview($stTableIDs, $strSingleEntryWriteWhere);

      $strFrom = $strSelect = $strWhere = $strOrder = array();

      switch ($report->enumRptType){
         case CE_CRPT_CLIENTS:
            $lNumFrom = 1;
            $strFNKeyID = 'cr_lKeyID';
            $strFrom[0] = 'FROM client_records';
            $strWhereContext      = ' NOT cr_bRetired AND (';
            $strWhereContextClose = ' )';
            $strDefaultOrder = 'cr_lKeyID ';
            break;
         default:
            screamForHelp($report->enumRptType.': report not available<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

      $this->buildPTableJoins($strFNKeyID, $this->tableIDs, $lNumFrom, $strFrom);
      $this->buildPTableJoinsForDDLS($report->fields, $lNumFrom, $strFrom, $strSelect);
      $this->strJoins = '';
      foreach ($strFrom as $sf){
         $this->strJoins .= $sf."\n";
      }

      $this->strSelect = '';
      foreach ($strSelect as $ss){
         $this->strSelect .= $ss.",\n";
      }
      $this->strSelect = substr($this->strSelect, 0, strlen($this->strSelect)-2);

      $this->strWhere = $this->strWhereViaTerms($this->lNumTerms, $this->terms);
      $this->strOrder = $this->strOrderViaTerms($strDefaultOrder, $lNumSortTerms, $sortTerms);

      $this->strSQL =
          'SELECT DISTINCT '.$this->strSelect."\n"
         .$this->strJoins."\n"
         .'WHERE '.$strSingleEntryWriteWhere."\n".$strWhereContext."\n".$this->strWhere."\n".$strWhereContextClose."\n"
         .'ORDER BY '.$this->strOrder."\n"
         .$strLimit;
      if ($bTerminateSQL) $this->strSQL .= ';';
   }

   function loadCReportRecords($fields, &$lNumRec, &$crecs){
   //---------------------------------------------------------------------
   // call $this->strBuildCReportSQL($report, $strLimits, true) to create sql first.
   //---------------------------------------------------------------------
      $lNumRec = 0; $crecs = array();
      $query = $this->db->query($this->strSQL);
      $lNumRec = $query->num_rows();
      if ($lNumRec > 0){
         $idx = 0;
         foreach  ($query->result_array() as $row){
            $crecs[$idx] = array();
            $crec = &$crecs[$idx];
            foreach ($fields as $field){
               $crec[$field->strSelectAsName] = $row[$field->strSelectAsName];
            }
            ++$idx;
         }
      }
   }

   function lCountRecs($bUseLimits, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   // call $this->strBuildCReportSQL($report, $strLimits, false) to create sql first.
   //
   // Thanks to
   //    http://stackoverflow.com/questions/4688814/count-distinct-values
   //    http://stackoverflow.com/questions/1888779/every-derived-table-must-have-its-own-alias
   //---------------------------------------------------------------------
      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }
      $sqlStr =
          'SELECT COUNT(*) AS lNumRecs
           FROM ('.$this->strSQL.$strLimit.') AS myLittleAlias ;';
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs);
   }

   function strOrderViaTerms($strDefaultOrder, $lNumSortTerms, $sortTerms){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($lNumSortTerms == 0){
         return($strDefaultOrder);
      }else {
         $order = array();
         foreach ($sortTerms as $term){
            $order[] = $term->strFieldID.' '.($term->bLarkAscending ? 'ASC' : 'DESC');
         }
         return(implode(",\n", $order));
      }
   }

   function strWhereViaTerms($lNumTerms, $terms){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';

      if ($lNumTerms == 0){
         $strOut = ' 1 ';
      }else {
         $lIDX = 1;
         foreach ($terms as $term){
            $strOut .= str_repeat('( ', $term->lNumLParen);

            switch ($term->enumFieldType){
               case CS_FT_DDL:
                  $strOut .= $term->strFieldID.' = '.strPrepStr($term->lCompVal).' ';
                  break;

               case CS_FT_DDL_SPECIAL:
                  $strOut .= $term->strFieldID.' = '.strPrepStr($term->strCompVal).' ';
                  break;

               case CS_FT_DATE:
                  $strOut .= $this->strDateTerm($term->strFieldID, $term->lCompareOpt, $term->mdteCompVal);
                  break;

               case CS_FT_TEXTLONG:
               case CS_FT_TEXT255:
               case CS_FT_TEXT80:
               case CS_FT_TEXT20:
               case CS_FT_TEXT:
                  $strOut .= $this->strTextTerm($term->strFieldID, $term->lCompareOpt, $term->strCompVal);
                  break;

               case CS_FT_ID:
               case CS_FT_INTEGER:
                  $strOut .= $this->strIntegerTerm($term->strFieldID, $term->lCompareOpt, $term->lCompVal);
                  break;

               case CS_FT_CURRENCY:
                  $strOut .= $this->strCurTerm($term->strFieldID, $term->lCompareOpt, $term->curCompVal);
                  break;

               case CS_FT_CHECKBOX:
                  $strOut .= ' '.($term->bCompareBool ? '' : ' NOT ').$term->strFieldID.' ';
                  break;

               default:
                  screamForHelp($term->enumFieldType.': unknown field type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }

            $strOut .= str_repeat(') ', $term->lNumRParen);

            if ($lIDX != $lNumTerms){
               $strOut .= ($term->bNextTermBoolAND ? ' AND ' : ' OR ')."\n";
            }

            ++$lIDX;
         }
      }
      return($strOut);
   }

   private function strTextTerm($strFieldID, $enumCompareOpt, $strCompVal){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      $lCompLength = strlen($strCompVal);

      switch ($enumCompareOpt){
         case CL_SRCH_EQ:
            $strOut .= " $strFieldID = ".strPrepStr($strCompVal).' ';
            break;

         case CL_SRCH_NEQ:
            $strOut .= " $strFieldID != ".strPrepStr($strCompVal).' ';
            break;

         case CL_SRCH_STR_START:
            $strOut .= " LEFT($strFieldID, $lCompLength) = ".strPrepStr($strCompVal).' ';
            break;

         case CL_SRCH_STR_NOTSTART:
            $strOut .= " LEFT($strFieldID, $lCompLength) != ".strPrepStr($strCompVal).' ';
            break;

         case CL_SRCH_STR_END:
            $strOut .= " RIGHT($strFieldID, $lCompLength) = ".strPrepStr($strCompVal).' ';
            break;

         case CL_SRCH_STR_NOTEND:
            $strOut .= " RIGHT($strFieldID, $lCompLength) != ".strPrepStr($strCompVal).' ';
            break;

         case CL_SRCH_STR_CONTAINS:
            $strOut .= " INSTR($strFieldID, ".strPrepStr($strCompVal).") > 0 ";
            break;

         case CL_SRCH_STR_NOTCONTAIN:
            $strOut .= " INSTR($strFieldID, ".strPrepStr($strCompVal).") = 0 ";
            break;

         default:
            screamForHelp($enumCompareOpt.': unknown string comparitor<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($strOut);
   }

   private function strIntegerTerm($strFieldID, $enumCompareOpt, $lCompVal){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = $strFieldID.' ';
      $lCompVal = (int)$lCompVal;

      switch ($enumCompareOpt){
         case CL_SRCH_EQ:
            $strOut .= " = $lCompVal ";
            break;
         case CL_SRCH_NEQ:
            $strOut .= " != $lCompVal ";
            break;
         case CL_SRCH_GT:
            $strOut .= " > $lCompVal ";
            break;
         case CL_SRCH_GE:
            $strOut .= " >= $lCompVal ";
            break;
         case CL_SRCH_LT:
            $strOut .= " < $lCompVal ";
            break;
         case CL_SRCH_LE:
            $strOut .= " <= $lCompVal ";
            break;
         default:
            screamForHelp($enumCompareOpt.': unknown integer comparitor<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($strOut);
   }

   private function strCurTerm($strFieldID, $enumCompareOpt, $curCompVal){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = $strFieldID.' ';
      $curCompVal = number_format($curCompVal, 2, '.', '');

      switch ($enumCompareOpt){
         case CL_SRCH_EQ:
            $strOut .= " = $curCompVal ";
            break;
         case CL_SRCH_NEQ:
            $strOut .= " != $curCompVal ";
            break;
         case CL_SRCH_GT:
            $strOut .= " > $curCompVal ";
            break;
         case CL_SRCH_GE:
            $strOut .= " >= $curCompVal ";
            break;
         case CL_SRCH_LT:
            $strOut .= " < $curCompVal ";
            break;
         case CL_SRCH_LE:
            $strOut .= " <= $curCompVal ";
            break;
         default:
            screamForHelp($enumCompareOpt.': unknown currency comparitor<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($strOut);
   }

   private function strDateTerm($strFieldID, $enumCompareOpt, $mdteCompVal){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = $strFieldID.' ';

      switch ($enumCompareOpt){

         case CL_SRCH_DEQ:
            $strOut .= " = '$mdteCompVal' ";
            break;
         case CL_SRCH_DNEQ:
            $strOut .= " != '$mdteCompVal' ";
            break;
         case CL_SRCH_DGT:
            $strOut .= " > '$mdteCompVal' ";
            break;
         case CL_SRCH_DGE:
            $strOut .= " >= '$mdteCompVal' ";
            break;
         case CL_SRCH_DLT:
            $strOut .= " < '$mdteCompVal' ";
            break;
         case CL_SRCH_DLE:
            $strOut .= " <= '$mdteCompVal' ";
            break;
         default:
            screamForHelp($enumCompareOpt.': unknown date comparitor<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($strOut);
   }

   private function buildPTableJoins($strFNKeyID, $tableIDs, &$lNumFrom, &$strFrom){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (count($tableIDs) > 0){
         foreach ($tableIDs as $TID){
            $strFrom[$lNumFrom] =
                 'LEFT JOIN '.$this->uf->strGenUF_TableName($TID)
                .' ON '.$strFNKeyID.' = '.$this->uf->strGenUF_ForeignIDFN($TID);
            ++$lNumFrom;
         }
      }
   }

   function buildSelectAsNames(&$fields){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (count($fields)==0) return;

      foreach ($fields as $field){
         $field->strSelectAsName = crptFields\strSelectTermViaFieldInfo($field);
         if ($field->enumType == CS_FT_CURRENCY){
            $field->strSelectAsName .= ' ('.$field->ACO->strName.')';
         }
      }
   }

   function buildPTableJoinsForDDLS($fields, &$lNumFrom, &$strFrom, &$strSelect){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (count($fields)==0) return;

      foreach ($fields as $field){
         if ($field->enumType == CS_FT_DDL){
            $strAsTable = 'ufddl'.$field->lFieldID;
            $strFrom[] = 'LEFT JOIN uf_ddl AS '.$strAsTable.' ON '.$strAsTable.'.ufddl_lKeyID = '.$field->strFieldName;
            $strSelect[] = $strAsTable.'.ufddl_strDDLEntry AS `'.$field->strSelectAsName.'`';
            ++$lNumFrom;
         }elseif ($field->enumType == CS_FT_DDL_SPECIAL){
            crptDDL\ddl2sqlSpecial($field, $strSelect[], $strFrom[]);
         }else {
            $strSelect[] = $field->strFieldName.' AS `'.$field->strSelectAsName.'`';
         }
      }
   }

   function lCountDDLJoins($fields){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumJoins = 0;
      foreach ($fields as $field){
         if ($field->enumType == CS_FT_DDL || $field->enumType == CS_FT_DDL_SPECIAL) ++$lNumJoins;
      }
      return($lNumJoins);
   }



      /*------------------------------------------------
                    V A L I D A T I O N
      ------------------------------------------------*/
   function verifyTerms_Init(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->cvUF = new muser_fields;
   }

   function bVerifyTerms_Display($report, &$lNumBad, &$badFields){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumBad = 0; $badFields = array();

      if ($report->lNumFields == 0) return(true);

      foreach ($report->fields as $field){
         if ($field->lTableID > 0){
            if (!$this->cvUF->bFieldExists($field->lFieldID)){
               $badFields[$lNumBad] = new stdClass;
               $bf = &$badFields[$lNumBad];
               $bf->lFieldID         = $field->lFieldID;
               $bf->lTableID         = $field->lTableID;
               $bf->strFieldName     = $field->strFieldName;
               $bf->strUserFN        = $field->strUserFN;
               $bf->enumType         = $field->enumType;
               $bf->strUserTableName = $field->strUserTableName;
               $bf->enumParentTable  = $field->enumParentTable;

               ++$lNumBad;
            }
         }
      }
      return($lNumBad==0);
   }

   function bVerifyTerms_Search(&$lNumBad, &$badFields){
   //---------------------------------------------------------------------
   // search terms in $this->lNumTerms and $this->terms
   //---------------------------------------------------------------------
      $lNumBad = 0; $badFields = array();

      if ($this->lNumTerms == 0) return(true);

      foreach ($this->terms as $term){
         if ($term->lTableID > 0){
            if (!$this->cvUF->bFieldExists($term->lFieldID)){
               $badFields[$lNumBad] = new stdClass;
               $bf = &$badFields[$lNumBad];
               $bf->lFieldID         = $term->lFieldID;
               $bf->lTableID         = $term->lTableID;
               $bf->strFieldName     = $term->strFieldID;
               $bf->strUserFN        = $term->strFieldNameUser;
               $bf->enumType         = $term->enumFieldType;
               $bf->strUserTableName = $term->strUserTableName;
               $bf->enumParentTable  = $term->enumAttachType;

               ++$lNumBad;
            }
         }
      }
      return($lNumBad==0);
   }

   function bVerifyTerms_Sort($lNumSortTerms, $sortTerms, &$lNumBad, &$badFields){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumBad = 0; $badFields = array();

      if ($lNumSortTerms == 0) return(true);

      foreach ($sortTerms as $term){
         if ($term->lTableID > 0){
            if (!$this->cvUF->bFieldExists($term->lFieldID)){
               $badFields[$lNumBad] = new stdClass;
               $bf = &$badFields[$lNumBad];
               $bf->lFieldID         = $term->lFieldID;
               $bf->lTableID         = $term->lTableID;
               $bf->strFieldName     = $term->strFieldID;
               $bf->strUserFN        = $term->strFieldNameUser;
               $bf->enumType         = $term->enumFieldType;
               $bf->strUserTableName = $term->strUserTableName;
               $bf->enumParentTable  = $term->enumAttachType;

               ++$lNumBad;
            }
         }
      }
      return($lNumBad==0);
   }

   function creportReviewUtility(&$lReportID, &$displayData, &$bFail, &$fails, &$bFieldsOK,
                        &$bTablePermissionOK, &$lNumTablePermFails, &$failTablePerms, &$lNumDDLJoins){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         //------------------------------------------------
         // load report
         //------------------------------------------------
      $displayData['cRptTypes'] = loadCReportTypeArray();
      $this->crptRun->loadReportViaID($lReportID, true);
      $report = &$this->crptRun->reports[0];

      if (!$report->bUserHasReadAccess){
         vid_bTestFail($this, false, 'Custom Report', $lReportID);
         die;
      }

      $bFail = false; $fails = array();
      if ($report->lNumFields == 0){
         $bFail = true;
         $fails[] = 'Please define one or more fields to display.';
      }

         // load search terms
      $this->crptRun->loadSearchTermViaReportID($lReportID);

         // load sort terms
      $this->crptRun->loadSortFieldsViaReportID($lReportID, $lNumSortTerms, $sortTerms);

         // max of 61 joins - mysql limit
      $lNumDDLJoins = $this->lCountDDLJoins($report->fields);

         // verify personalized terms still exist
      $this->crptRun->verifyTerms_Init();
      $bFieldsOK_Display = $this->crptRun->bVerifyTerms_Display($report,                    $displayData['lNumBad_Display'], $displayData['badFields_Display']);
      $bFieldsOK_Search  = $this->crptRun->bVerifyTerms_Search (                            $displayData['lNumBad_Search'],  $displayData['badFields_Search']);
      $bFieldsOK_Sort    = $this->crptRun->bVerifyTerms_Sort   ($lNumSortTerms, $sortTerms, $displayData['lNumBad_Sort'],    $displayData['badFields_Sort']);
      $bFieldsOK = $bFieldsOK_Display && $bFieldsOK_Search && $bFieldsOK_Sort;


         // verify user has access to all tables referenced in report
      $bTablePermissionOK = $this->bVerifyUserAccessToReport($report, $lNumTablePermFails, $failTablePerms);
   }

   function singleEntryPTableReview($tableIDs, &$strSingleEntryWriteWhere){
   //---------------------------------------------------------------------
   // if single-entry personalized tables are used, add the
   // ufxxxxxx_bRecordEntered flag to the where statement
   //---------------------------------------------------------------------
      $strSingleEntryWriteWhere = '';
      if (count($tableIDs)==0) return;

      $sqlStr =
           'SELECT pft_lKeyID
            FROM uf_tables
            WHERE pft_lKeyID IN ('.implode(',', $tableIDs).')
               AND NOT pft_bMultiEntry;';

      $query = $this->db->query($sqlStr);
      $lNumTabs = $query->num_rows();
      if ($lNumTabs > 0){
         $strQualSETables = array();
         foreach ($query->result() as $row){
            $strQualSETables[] = $this->uf->strGenUF_KeyFieldPrefix((int)$row->pft_lKeyID).'_bRecordEntered';
         }
         $strSingleEntryWriteWhere = implode(' AND ', $strQualSETables).' AND ';
      }
   }


}
