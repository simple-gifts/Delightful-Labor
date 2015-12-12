<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('reports/mufield_stats', 'cUFStats');
---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class mufield_stats extends CI_Model{
   public $strTmpTable;


   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
   }


   function addStatRptViaField($utableSchema, $lTableID, $fieldInfo, $strFNSIClientID, &$fieldRptInfo, &$idx){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $fieldRptInfo[$idx] = new stdClass;
      $strSITable = $utableSchema->schema[$lTableID]->strDataTableName;

      $fieldRptInfo[$idx]->strLabel = $fieldInfo->strFieldNameUser;

      switch ($fieldInfo->enumFieldType){
         case CS_FT_CHECKBOX:
            $fieldRptInfo[$idx]->rptType  = 'yesNo';
            $fieldRptInfo[$idx]->bLeftAlign = false;
            $this->checkboxEnumerator($strSITable, $strFNSIClientID,
                                 $fieldInfo->strFieldNameInternal,
                                 $fieldRptInfo[$idx]->lNumYes);
            break;

         case CS_FT_DDL:
            $fieldRptInfo[$idx]->rptType  = 'ddl';
            $fieldRptInfo[$idx]->bLeftAlign = true;
            $this->ddlEnumerator($strSITable, $strFNSIClientID,
                                 $fieldInfo->strFieldNameInternal,
                                 $fieldRptInfo[$idx]->lNumGroups, $fieldRptInfo[$idx]->lTot, $fieldRptInfo[$idx]->enumGroups);

            break;

         case CS_FT_DDLMULTI:
            $fieldRptInfo[$idx]->rptType  = 'multiDDL';
            $fieldRptInfo[$idx]->bLeftAlign = true;
            $this->multiDDLEnumerator($utableSchema, $lTableID, $strSITable, $strFNSIClientID, $fieldInfo->lFieldID, $fieldRptInfo[$idx]->multiDDL);
            break;

         case CS_FT_INTEGER:
            $fieldRptInfo[$idx]->rptType  = 'intCnt';
            $fieldRptInfo[$idx]->bLeftAlign = true;
            $this->intCountEnumerator($strSITable, $strFNSIClientID,
                                 $fieldInfo->strFieldNameInternal,
                                 $fieldRptInfo[$idx]->lMin, $fieldRptInfo[$idx]->lMax, $fieldRptInfo[$idx]->sngAvg, $fieldRptInfo[$idx]->sngStdDev);
            break;
            
         case CS_FT_TEXT255:
         case CS_FT_TEXT80:
         case CS_FT_TEXT20:
         case CS_FT_TEXTLONG:
         case CS_FT_TEXT:
         case CS_FT_DATE:
         case CS_FT_HEADING:
            --$idx;
            break;

         default:
            screamForHelp($fieldInfo->enumFieldType.': invalid field type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      ++$idx;
   }

   function ddlEnumerator($strPTableName, $strFNDataTableFID, $strDDLFN,
                          &$lNumGroups, &$lNumTot, &$enumGroups){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "SELECT COUNT(*) AS lNumRecs, ufddl_strDDLEntry
          FROM $this->strTmpTable
             INNER JOIN client_records ON cr_lKeyID=tmpsa_lClientID
             INNER JOIN $strPTableName ON cr_lKeyID=$strFNDataTableFID
             LEFT  JOIN uf_ddl ON $strDDLFN = ufddl_lKeyID
          WHERE tmpsa_bIntake
          GROUP BY ufddl_strDDLEntry
          ORDER BY ufddl_strDDLEntry;";

      $lNumTot = 0;
      $enumGroups = array();
      $query = $this->db->query($sqlStr);
      $lNumGroups = $query->num_rows();
      if ($lNumGroups > 0) {
         foreach ($query->result() as $row){
            $enumGroups[$row->ufddl_strDDLEntry] = (int)$row->lNumRecs;
            $lNumTot += (int)$row->lNumRecs;
         }
      }
   }

   function intCountEnumerator($strPTableName, $strFNDataTableFID, $strIntFN,
                           &$lMin, &$lMax, &$sngAvg, &$sngStdDev){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "SELECT AVG($strIntFN) AS sngAvg, MIN($strIntFN) AS lMin, MAX($strIntFN) AS lMax,
            STD($strIntFN) AS sngStdDev
          FROM $this->strTmpTable
             INNER JOIN client_records ON cr_lKeyID=tmpsa_lClientID
             INNER JOIN $strPTableName ON cr_lKeyID=$strFNDataTableFID
          WHERE tmpsa_bIntake;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      $lMin      = (int)$row->lMin;
      $lMax      = (int)$row->lMax;
      $sngAvg    = (float)$row->sngAvg;
      $sngStdDev = (float)$row->sngStdDev;
   }

   function checkboxEnumerator($strPTableName, $strFNDataTableFID, $strChkFN,
                               &$lNumYes){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "SELECT COUNT(*) AS lNumRecs
          FROM $this->strTmpTable
             INNER JOIN client_records ON cr_lKeyID=tmpsa_lClientID
             INNER JOIN $strPTableName ON cr_lKeyID=$strFNDataTableFID
          WHERE tmpsa_bIntake AND $strChkFN;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      $lNumYes = (int)$row->lNumRecs;
   }

   function multiDDLEnumerator($utableSchema, $lTableID, $strPTableName, $strFNDataTableFID, $lFieldID, &$multiDDL){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $multiDDL = array();
      $lFieldIDX = $utableSchema->lFieldIdxViaFieldID($lTableID, $lFieldID, true);
      $utable = &$utableSchema->schema[$lTableID];
      $field = &$utable->fields[$lFieldIDX];

      if ($field->lNumDDL > 0){
         $idx = 0;
         foreach ($field->ddlInfo as $ddlInfo){
            $multiDDL[$idx] = new stdClass;
            $multiDDL[$idx]->strDDLEntry = $ddlInfo->strDDLEntry;
            $multiDDL[$idx]->lCount =
                  $this->lCountMultiSelections($ddlInfo->lKeyID,
                                   $utable->strDataTableName,
                                   $utable->strDataTableFID,
                                   $utable->strDataTableKeyID);
            ++$idx;
         }
      }
   }

   function lCountMultiSelections($lMultiDDLKeyID, $strPTableName, $strFNDataTableFID, $strFNPTableID){
   //---------------------------------------------------------------------
   // $strPTableName - personalized table internal name
   // strFNDataTableFID - foreign ID field name of the personalized table
   // $strFNPTableID - field name of the keyID of the personalized table
   //---------------------------------------------------------------------
      $sqlStr =
         "SELECT COUNT(*) AS lNumRecs
          FROM $this->strTmpTable
             INNER JOIN client_records ON cr_lKeyID=tmpsa_lClientID
             INNER JOIN $strPTableName ON cr_lKeyID=$strFNDataTableFID
             INNER JOIN uf_ddl_multi   ON pdm_lUTableRecID = $strFNPTableID
          WHERE pdm_lDDLID=$lMultiDDLKeyID;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs);
   }



}

